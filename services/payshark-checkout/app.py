"""
PayShark PIX checkout + RedTrack bridge (substitui o checkout hospedado da Kirvano).

PayShark é API-level (não tem página de checkout), então este serviço:
  1. serve a página de checkout PIX (checkout.html), que lê ?src=<clickid> da URL;
  2. cria a cobrança PIX via PayShark POST /v1/transactions com externalRef=<clickid>;
  3. mostra o QR / copia-e-cola e faz polling do status;
  4. recebe o webhook do PayShark e, se aprovado, dispara o postback do RedTrack
     (clickid vem do externalRef que a PayShark devolve) — atribui a venda ao gclid.

UM serviço serve os 3 domínios (difere só por ?offer=). Espelha a metade de
observabilidade + postback do kirvano-bridge (validador rtkcid 24-hex, STATS, /debug/*).

Endpoints:
  GET  /            -> checkout.html      (destino da offer do RedTrack: /?src=<clickid>)
  GET  /checkout    -> checkout.html
  GET  /api/config  -> {title, price_centavos}      (página renderiza preço/nome)
  POST /api/pix     -> cria PIX            {name,email,cpf,phone,src,offer} -> {transaction_id,qrcode}
  GET  /api/status/{id} -> {status, paid}  (polling; NÃO dispara postback)
  POST /webhook     -> PayShark postback -> RedTrack postback (a marcação acontece aqui)
  GET  /healthz     -> {ok:true}
  GET  /info        -> config do serviço
  GET  /debug/stats , /debug/recent

Env:
  PAYSHARK_API_PUBLIC_KEY / PAYSHARK_API_SECRET_KEY  (Basic auth)
  PAYSHARK_API_BASE   default https://api.paysharkgateway.com.br
  REDTRACK_POSTBACK   default https://kpved.ttrk.io/postback   (tracking domain do SOLARIS)
  PRICE_CENTAVOS      default 9700   (R$97,00)
  PRODUCT_TITLE       default "LotoApp"
  SELF_BASE_URL       URL pública https deste serviço (p/ postbackUrl da PayShark)
  OFFER_PRICES        JSON opcional {"slug":centavos} p/ preço por oferta
  LOG_LEVEL           default INFO
"""
from __future__ import annotations

import base64
import json as _json
import logging
import os
import re
from collections import deque
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import httpx
from fastapi import FastAPI, HTTPException, Request
from fastapi.responses import HTMLResponse, JSONResponse
from pydantic import BaseModel

logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("payshark-checkout")

PK = os.environ.get("PAYSHARK_API_PUBLIC_KEY", "")
SK = os.environ.get("PAYSHARK_API_SECRET_KEY", "")
PAYSHARK_API = os.environ.get("PAYSHARK_API_BASE", "https://api.paysharkgateway.com.br").rstrip("/")
REDTRACK_POSTBACK = os.environ.get("REDTRACK_POSTBACK", "https://kpved.ttrk.io/postback").rstrip("/")
PRICE_CENTAVOS = int(os.environ.get("PRICE_CENTAVOS", "9700"))
PRODUCT_TITLE = os.environ.get("PRODUCT_TITLE", "LotoApp")
SELF_BASE_URL = os.environ.get("SELF_BASE_URL", "").rstrip("/")
try:
    OFFER_PRICES = _json.loads(os.environ.get("OFFER_PRICES", "{}"))
except Exception:
    OFFER_PRICES = {}

_BASIC = base64.b64encode(f"{PK}:{SK}".encode()).decode()
_PAY_HEADERS = {"Authorization": f"Basic {_BASIC}",
                "Content-Type": "application/json", "Accept": "application/json"}

app = FastAPI(title="PayShark PIX Checkout + RedTrack bridge", version="1.0.0")

# rtkcid do RedTrack = ObjectId 24-hex. Só marca conversão se o externalRef tiver essa cara
# (ignora tráfego de outras fontes que por acaso caia no mesmo checkout).
_RTKCID_RE = re.compile(r"^[0-9a-fA-F]{24}$")

# checkout.html carregado uma vez
_HTML = ""
_html_path = Path(__file__).resolve().parent / "checkout.html"
if _html_path.exists():
    _HTML = _html_path.read_text(encoding="utf-8")

# ── observabilidade em memória (zera no restart; suficiente p/ QA) ─────────────
RECENT: deque = deque(maxlen=80)
STATS = {
    "pix_requested": 0, "pix_created": 0, "pix_error": 0,
    "received": 0, "skipped_status": 0, "no_clickid": 0,
    "forwarded": 0, "attributed": 0, "rt_error": 0, "duplicate": 0,
}
_SEEN_TX: set[str] = set()   # dedupe de webhooks por transaction id


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


def _record(outcome: str, payload: Any, **extra) -> None:
    try:
        raw = _json.dumps(payload)[:1400]
    except Exception:
        raw = str(payload)[:1400]
    RECENT.appendleft({"ts": _now(), "outcome": outcome, "payload": raw, **extra})


def _looks_like_rtkcid(v: str | None) -> bool:
    return bool(v) and bool(_RTKCID_RE.match(v.strip()))


# ── páginas / config ──────────────────────────────────────────────────────────
@app.get("/", response_class=HTMLResponse)
@app.get("/checkout", response_class=HTMLResponse)
def checkout_page():
    if not _HTML:
        return HTMLResponse("<h1>checkout.html ausente</h1>", status_code=500)
    return HTMLResponse(_HTML)


@app.get("/api/config")
def api_config(offer: str | None = None):
    price = OFFER_PRICES.get(offer or "", PRICE_CENTAVOS)
    return {"title": PRODUCT_TITLE, "price_centavos": price,
            "price_brl": f"{price/100:.2f}".replace(".", ",")}


@app.get("/healthz")
def health():
    return {"ok": True}


@app.get("/info")
def info():
    return {
        "service": "payshark-checkout",
        "version": "1.0.0",
        "payshark_api": PAYSHARK_API,
        "redtrack_postback": REDTRACK_POSTBACK,
        "price_centavos": PRICE_CENTAVOS,
        "product_title": PRODUCT_TITLE,
        "self_base_url": SELF_BASE_URL or "(NAO setado!)",
        "clickid_validation": "externalRef 24-hex rtkcid only",
    }


@app.get("/debug/stats")
def debug_stats():
    return {"stats": STATS, "recent_count": len(RECENT), "note": "zera no restart"}


@app.get("/debug/recent")
def debug_recent(n: int = 30):
    return {"stats": STATS, "recent": list(RECENT)[: max(1, min(n, 80))]}


# ── criar PIX ─────────────────────────────────────────────────────────────────
class PixIn(BaseModel):
    name: str
    email: str
    cpf: str | None = None
    phone: str | None = None
    src: str = ""          # clickid (rtkcid); pode vir vazio (visita direta) -> webhook filtra
    offer: str | None = None


@app.post("/api/pix")
async def create_pix(body: PixIn):
    STATS["pix_requested"] += 1
    amount = OFFER_PRICES.get(body.offer or "", PRICE_CENTAVOS)
    cpf_digits = re.sub(r"\D", "", body.cpf or "")
    customer: dict[str, Any] = {"name": body.name.strip(), "email": body.email.strip()}
    if body.phone:
        customer["phone"] = re.sub(r"\D", "", body.phone)
    if cpf_digits:
        customer["document"] = {"type": "cpf", "number": cpf_digits}

    payload = {
        "amount": amount,
        "paymentMethod": "pix",
        "customer": customer,
        "items": [{"title": PRODUCT_TITLE, "quantity": 1, "tangible": False, "unitPrice": amount}],
        "externalRef": (body.src or "").strip(),      # ← clickid viaja aqui; webhook devolve
    }
    if SELF_BASE_URL:
        payload["postbackUrl"] = f"{SELF_BASE_URL}/webhook"

    try:
        async with httpx.AsyncClient(timeout=30) as c:
            r = await c.post(f"{PAYSHARK_API}/v1/transactions", json=payload, headers=_PAY_HEADERS)
    except Exception as e:
        STATS["pix_error"] += 1
        _record("pix_exception", {"externalRef": body.src, "offer": body.offer}, error=str(e)[:200])
        raise HTTPException(502, f"payshark create exception: {e!s}")

    if r.status_code >= 400:
        STATS["pix_error"] += 1
        _record("pix_create_error", {"externalRef": body.src}, status=r.status_code, body=r.text[:400])
        log.error("payshark create %s: %s", r.status_code, r.text[:300])
        raise HTTPException(502, f"payshark create failed: {r.status_code}")

    tx = r.json()
    STATS["pix_created"] += 1
    soft = "" if _looks_like_rtkcid(body.src) else " (src nao-rtkcid)"
    _record("pix_created" + soft, {"externalRef": body.src, "offer": body.offer},
            tx_id=tx.get("id"), amount=amount)
    return {"transaction_id": tx.get("id"),
            "qrcode": (tx.get("pix") or {}).get("qrcode", ""),
            "amount_centavos": amount}


@app.get("/api/status/{tx_id}")
async def status(tx_id: int):
    try:
        async with httpx.AsyncClient(timeout=20) as c:
            r = await c.get(f"{PAYSHARK_API}/v1/transactions/{tx_id}", headers=_PAY_HEADERS)
    except Exception as e:
        raise HTTPException(502, f"payshark status exception: {e!s}")
    if r.status_code >= 400:
        raise HTTPException(502, f"payshark status failed: {r.status_code}")
    tx = r.json()
    st = str(tx.get("status", "")).lower()
    return {"status": st, "paid": st in ("approved", "paid")}


# ── webhook PayShark -> postback RedTrack (a MARCAÇÃO acontece aqui) ───────────
@app.post("/webhook")
async def webhook(request: Request):
    STATS["received"] += 1
    raw = await request.body()
    try:
        payload = _json.loads(raw)
    except Exception:
        payload = {"_raw": raw.decode("utf-8", "replace")[:1500]}
    if not isinstance(payload, dict):
        payload = {"_value": payload}

    data = payload.get("data") if isinstance(payload.get("data"), dict) else payload
    status_ = str(data.get("status", "")).lower()
    log.info("webhook status=%s externalRef=%s amount=%s",
             status_, data.get("externalRef"), data.get("amount"))

    if status_ not in ("approved", "paid"):
        STATS["skipped_status"] += 1
        _record("skipped_status", payload, status=status_)
        return {"ok": True, "skipped": "status_not_paid", "status": status_}

    clickid = str(data.get("externalRef") or "").strip()
    if not _looks_like_rtkcid(clickid):
        STATS["no_clickid"] += 1
        _record("no_clickid", payload, externalRef=clickid)
        log.warning("externalRef nao e rtkcid 24-hex: %r", clickid)
        return JSONResponse({"ok": False, "error": "externalRef_not_rtkcid"}, status_code=200)

    tx_id = str(data.get("id") or "")
    if tx_id and tx_id in _SEEN_TX:
        STATS["duplicate"] += 1
        _record("duplicate", payload, tx_id=tx_id, clickid=clickid)
        return {"ok": True, "duplicate": tx_id}
    if tx_id:
        _SEEN_TX.add(tx_id)

    cents = data.get("amount") or 0
    reais = (cents / 100.0) if isinstance(cents, (int, float)) else 0.0
    params = {"clickid": clickid, "sum": f"{reais:.2f}", "status": "approved"}
    try:
        async with httpx.AsyncClient(timeout=15) as c:
            rr = await c.get(REDTRACK_POSTBACK, params=params)
        STATS["forwarded"] += 1
        STATS["attributed" if rr.status_code == 200 else "rt_error"] += 1
        _record("forwarded", payload, clickid=clickid, amount=reais,
                redtrack_status=rr.status_code, redtrack_body=rr.text[:200])
        log.info("redtrack postback clickid=%s sum=%s -> %s", clickid, params["sum"], rr.status_code)
        return {"ok": True, "clickid": clickid, "payout": reais,
                "redtrack_status": rr.status_code, "redtrack_body": rr.text[:200]}
    except Exception as e:
        STATS["rt_error"] += 1
        _record("rt_exception", payload, error=str(e))
        log.error("redtrack postback failed: %s", e)
        raise HTTPException(502, f"redtrack postback failed: {e!s}")
