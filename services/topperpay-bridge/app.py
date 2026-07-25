"""
topperpay-bridge — webhook do gateway TopperPay/asegupag -> conversao no Google Ads.

CONTEXTO (funil CRR/roxo do bomb): as ofertas rodam Google Ads com Adspect. O Adspect
captura {p:gclid} e passa adiante (arg_passthru) -> o gclid chega no checkout TopperPay
via ?src=<gclid>. Quando a venda e aprovada, o TopperPay dispara este webhook.

Este serviço NAO poe pixel em pagina nenhuma (a money e cloakada; pixel client-side
exporia a money ao Google). Em vez disso faz OFFLINE CONVERSION IMPORT por gclid:
  webhook "Compra aprovada" -> extrai gclid+valor -> grava numa Google Sheet ->
  Google Ads importa a Sheet (agendado) -> casa por gclid -> atribui a campanha.
O Google nunca ve a money — so recebe (gclid, valor, hora).

Rotas:
  POST /webhook          <- o TopperPay cola aqui (evento "Compra aprovada")
  GET  /healthz          <- 200 ok
  GET  /info             <- config (enabled/dormente)
  GET  /debug/stats      <- contadores
  GET  /debug/recent     <- ultimos eventos (payload cru — pra confirmar que o gclid vem)

ENV:
  TOPPERPAY_TOKEN        - se setado, exige ?token=<x> (ou header X-Token) no /webhook
  GCLID_FIELDS           - CSV de campos onde procurar o gclid (default: src,gclid,utm_src,utm.src)
  GADS_SHEET_WEBHOOK_URL - URL /exec do Apps Script da Sheet. SEM ela = DORMENTE (so loga/conta).
  GADS_SHEET_SECRET      - secret compartilhado com o Apps Script
  GADS_CONVERSION_NAME   - nome EXATO da conversion action no Google Ads (ex: "Compra")
  GADS_CURRENCY          - default BRL
  TZ_OFFSET              - default -03:00
"""
from __future__ import annotations

import logging
import os
import re
from collections import deque
from datetime import datetime, timezone, timedelta
from typing import Any

import httpx
from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse, PlainTextResponse

logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("topperpay-bridge")

TOKEN = os.environ.get("TOPPERPAY_TOKEN", "").strip() or None
GCLID_FIELDS = [f.strip() for f in os.environ.get(
    "GCLID_FIELDS", "src,gclid,utm_src,utm.src,tracking,utm_source").split(",") if f.strip()]
SHEET_URL = os.environ.get("GADS_SHEET_WEBHOOK_URL", "").strip() or None
SHEET_SECRET = os.environ.get("GADS_SHEET_SECRET", "").strip() or None
CONVERSION_NAME = os.environ.get("GADS_CONVERSION_NAME", "Compra").strip()
CURRENCY = os.environ.get("GADS_CURRENCY", "BRL").strip()
TZ_OFFSET = os.environ.get("TZ_OFFSET", "-03:00").strip()

# eventos que contam como venda (o TopperPay manda "Compra aprovada")
PAID_HINTS = re.compile(r"aprovad|approved|paid|pago|compra", re.I)
# gclid do Google: longo, alfanumerico + -_ . Nao vazio, nao "None"
GCLID_RE = re.compile(r"^[A-Za-z0-9_\-\.]{20,}$")

app = FastAPI(title="topperpay-bridge")

STATS = {
    "received": 0,        # POSTs no /webhook
    "paid": 0,            # eventos de compra aprovada
    "with_gclid": 0,      # tinham gclid
    "no_gclid": 0,        # sem gclid (nao atribui)
    "sheet_sent": 0,      # gravado na Sheet
    "sheet_ok": 0,
    "sheet_error": 0,
    "sheet_disabled": 0,  # dormente (sem GADS_SHEET_WEBHOOK_URL)
    "forbidden": 0,
    "ignored": 0,         # evento nao-compra (pix gerado, etc)
}
RECENT: deque = deque(maxlen=40)


def _now() -> str:
    return datetime.now(timezone.utc).astimezone().isoformat(timespec="seconds")


def _conv_time() -> str:
    # "YYYY-MM-DD HH:MM:SS-03:00" — formato do Google Ads offline import
    off = TZ_OFFSET.replace(":", "")
    sign = 1 if off.startswith("+") else -1
    hh = int(off[1:3]); mm = int(off[3:5]) if len(off) >= 5 else 0
    tz = timezone(sign * timedelta(hours=hh, minutes=mm))
    return datetime.now(tz).strftime("%Y-%m-%d %H:%M:%S") + TZ_OFFSET


def _record(outcome: str, **extra) -> None:
    RECENT.appendleft({"at": _now(), "outcome": outcome, **extra})


def _walk_find(data: Any, key: str) -> str | None:
    """Busca recursiva por uma chave (case-insensitive) em dict/list aninhado."""
    kl = key.lower()
    if isinstance(data, dict):
        for k, v in data.items():
            if str(k).lower() == kl and isinstance(v, (str, int, float)) and str(v).strip():
                return str(v).strip()
        for v in data.values():
            r = _walk_find(v, key)
            if r:
                return r
    elif isinstance(data, list):
        for it in data:
            r = _walk_find(it, key)
            if r:
                return r
    return None


def _extract_gclid(payload: dict) -> str | None:
    """Acha o gclid no payload. O checkout recebe ?src=<gclid>; o TopperPay deve
    ecoar em src/utm. Testa varios campos e valida o formato (evita lixo)."""
    for field in GCLID_FIELDS:
        # suporta 'utm.src' -> procura 'src' dentro de 'utm'
        parts = field.split(".")
        v = _walk_find(payload, parts[-1])
        if v and GCLID_RE.match(v) and v.lower() not in ("none", "null", "undefined"):
            return v
    return None


def _parse_brl(s: Any) -> float | None:
    if s is None:
        return None
    if isinstance(s, (int, float)):
        return float(s)
    txt = re.sub(r"[^\d,.-]", "", str(s))
    if not txt:
        return None
    if "," in txt and "." in txt:      # 1.234,56 -> 1234.56
        txt = txt.replace(".", "").replace(",", ".")
    elif "," in txt:                    # 27,30 -> 27.30
        txt = txt.replace(",", ".")
    try:
        return float(txt)
    except ValueError:
        return None


def _extract_amount(payload: dict) -> float | None:
    for k in ("total_price", "amount", "value", "total", "valor", "price", "net_amount"):
        v = _walk_find(payload, k)
        a = _parse_brl(v)
        if a is not None:
            # centavos? gateways as vezes mandam int em centavos
            if isinstance(v, (int, float)) and float(v).is_integer() and a >= 1000:
                a = a / 100.0
            return a
    return None


def _extract_order(payload: dict) -> str | None:
    for k in ("transaction_id", "order_id", "id", "sale_id", "code", "hash"):
        v = _walk_find(payload, k)
        if v:
            return v
    return None


def _is_paid(payload: dict) -> bool:
    for k in ("event", "status", "type", "situacao", "payment_status"):
        v = _walk_find(payload, k)
        if v and PAID_HINTS.search(v):
            return True
    return False


def _send_to_sheet(gclid: str, amount: float | None, order: str | None) -> None:
    if not SHEET_URL:
        STATS["sheet_disabled"] += 1
        _record("sheet_disabled", gclid=gclid[:12] + "…", amount=amount, order=order)
        return
    body = {
        "secret": SHEET_SECRET or "",
        "gclid": gclid,
        "conversion_name": CONVERSION_NAME,
        "conversion_time": _conv_time(),
        "conversion_value": f"{(amount or 0):.2f}",
        "conversion_currency": CURRENCY,
        "order_id": order or "",
    }
    STATS["sheet_sent"] += 1
    try:
        r = httpx.post(SHEET_URL, json=body, timeout=20, follow_redirects=True)
        if r.status_code < 300:
            STATS["sheet_ok"] += 1
            _record("sheet_ok", gclid=gclid[:12] + "…", amount=amount, order=order)
        else:
            STATS["sheet_error"] += 1
            _record("sheet_error", status=r.status_code, body=r.text[:120])
    except Exception as e:
        STATS["sheet_error"] += 1
        _record("sheet_error", err=str(e)[:120])


@app.post("/webhook")
async def webhook(request: Request):
    STATS["received"] += 1
    if TOKEN:
        got = request.query_params.get("token") or request.headers.get("x-token")
        if got != TOKEN:
            STATS["forbidden"] += 1
            _record("forbidden")
            return JSONResponse({"ok": False, "error": "forbidden"}, status_code=403)
    try:
        payload = await request.json()
    except Exception:
        payload = {"_raw": (await request.body()).decode("utf-8", "ignore")[:2000]}

    if not _is_paid(payload):
        STATS["ignored"] += 1
        _record("ignored", event=_walk_find(payload, "event") or _walk_find(payload, "status"))
        return {"ok": True, "handled": "ignored (nao e compra aprovada)"}

    STATS["paid"] += 1
    gclid = _extract_gclid(payload)
    amount = _extract_amount(payload)
    order = _extract_order(payload)

    # sempre guarda o payload cru (pra inspecionar o formato real do TopperPay)
    _record("paid", gclid=(gclid[:14] + "…") if gclid else None, amount=amount,
            order=order, keys=list(payload.keys())[:15], raw=payload)

    if not gclid:
        STATS["no_gclid"] += 1
        log.warning("compra aprovada SEM gclid — order=%s amount=%s keys=%s",
                    order, amount, list(payload.keys())[:15])
        return {"ok": True, "handled": "paid_no_gclid", "order": order}

    STATS["with_gclid"] += 1
    _send_to_sheet(gclid, amount, order)
    return {"ok": True, "handled": "paid", "gclid_tail": gclid[-6:], "amount": amount}


@app.get("/healthz")
def healthz():
    return PlainTextResponse("ok")


@app.get("/info")
def info():
    return {
        "service": "topperpay-bridge",
        "conversion_enabled": bool(SHEET_URL),
        "state": "ativo (grava na Sheet)" if SHEET_URL else "DORMENTE (sem GADS_SHEET_WEBHOOK_URL — so loga)",
        "conversion_name": CONVERSION_NAME,
        "currency": CURRENCY,
        "gclid_fields": GCLID_FIELDS,
        "token_required": bool(TOKEN),
    }


@app.get("/debug/stats")
def debug_stats():
    return STATS


@app.get("/debug/recent")
def debug_recent():
    return list(RECENT)
