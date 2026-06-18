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
import hashlib
import json as _json
import logging
import os
import re
import time
from collections import deque
from datetime import datetime, timedelta, timezone
from pathlib import Path
from typing import Any

import httpx
from fastapi import FastAPI, HTTPException, Request
from fastapi.responses import FileResponse, HTMLResponse, JSONResponse
from pydantic import BaseModel

logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("payshark-checkout")

# Gateway = StreetPays (api.streetpays.com.br/v1). Substituiu o PayShark (mesma URL do serviço).
STREETPAYS_KEY = os.environ.get("STREETPAYS_API_KEY", "")
GATEWAY_API = os.environ.get("STREETPAYS_API_BASE", "https://api.streetpays.com.br").rstrip("/")
REDTRACK_POSTBACK = os.environ.get("REDTRACK_POSTBACK", "https://hegqp.ttrk.io/postback").rstrip("/")
PRICE_CENTAVOS = int(os.environ.get("PRICE_CENTAVOS", "9700"))
PRODUCT_TITLE = os.environ.get("PRODUCT_TITLE", "LotoApp")
# descrição enviada AO GATEWAY (extrato/fatura) — desacoplada do que o cliente vê na página.
GATEWAY_DESC = os.environ.get("GATEWAY_DESC", "Taxa Correios")
SELF_BASE_URL = os.environ.get("SELF_BASE_URL", "").rstrip("/")
try:
    OFFER_PRICES = _json.loads(os.environ.get("OFFER_PRICES", "{}"))
except Exception:
    OFFER_PRICES = {}

# ── Upsell prices / titles ─────────────────────────────────────────────────────
UPSELL1_PRICE = int(os.environ.get("UPSELL1_PRICE", "4700"))   # R$47
UPSELL2_PRICE = int(os.environ.get("UPSELL2_PRICE", "2700"))   # R$27
UPSELL1_TITLE = os.environ.get("UPSELL1_TITLE", "LotoApp VIP")
UPSELL2_TITLE = os.environ.get("UPSELL2_TITLE", "LotoApp Regional")
# injeta upsells no mapa de preços (sobreposição por env OFFER_PRICES se quiser)
if "upsell1" not in OFFER_PRICES:
    OFFER_PRICES["upsell1"] = UPSELL1_PRICE
if "upsell2" not in OFFER_PRICES:
    OFFER_PRICES["upsell2"] = UPSELL2_PRICE

# ── Enhanced Conversions for Leads (Google Ads via Google Sheet) ───────────────
# Liga so quando EC_SHEET_WEBHOOK_URL setado (URL do Apps Script /exec que grava na
# Sheet). Manda email/telefone HASHEADO (SHA-256); o Google Ads auto-importa e casa
# por email (sem gclid). EC_CONVERSION_NAME = nome da conversion action no Google Ads.
EC_SHEET_WEBHOOK_URL = os.environ.get("EC_SHEET_WEBHOOK_URL", "").strip()
EC_SHEET_SECRET = os.environ.get("EC_SHEET_SECRET", "").strip()
EC_CONVERSION_NAME = os.environ.get("EC_CONVERSION_NAME", "Compra").strip()
EC_DEFAULT_CC = os.environ.get("EC_DEFAULT_CC", "55").strip()      # DDI Brasil
EC_TZ_OFFSET = os.environ.get("EC_TZ_OFFSET", "-03:00").strip()    # Brasilia
EC_ENABLED = bool(EC_SHEET_WEBHOOK_URL)

_PAY_HEADERS = {"Authorization": f"Bearer {STREETPAYS_KEY}",
                "Content-Type": "application/json", "Accept": "application/json"}

app = FastAPI(title="StreetPays PIX Checkout + RedTrack bridge", version="2.1.0")

# rtkcid do RedTrack = ObjectId 24-hex. Só marca conversão se o externalRef tiver essa cara
# (ignora tráfego de outras fontes que por acaso caia no mesmo checkout).
_RTKCID_RE = re.compile(r"^[0-9a-fA-F]{24}$")

# páginas HTML carregadas uma vez no startup
def _load_html(name: str) -> str:
    p = Path(__file__).resolve().parent / name
    return p.read_text(encoding="utf-8") if p.exists() else ""

_HTML        = _load_html("checkout.html")
_UPSELL1_HTML = _load_html("upsell1.html")
_UPSELL2_HTML = _load_html("upsell2.html")
_OBRIGADO_HTML = _load_html("obrigado.html")

# ── detecção de pagamento instantânea ─────────────────────────────────────────
# Webhook popula _PAID_TX quando chega PAID/APPROVED. /api/status consulta aqui
# primeiro (O(1), sem chamar StreetPays) → latência máx = intervalo de poll (3s).
_PAID_TX: set[str] = set()
# rastreia tipo de transação para stats de upsell
_UPSELL_TX: dict[str, str] = {}  # tx_id -> "upsell1" | "upsell2"

# ── observabilidade em memória (zera no restart; suficiente p/ QA) ─────────────
RECENT: deque = deque(maxlen=80)
STATS = {
    "pix_requested": 0, "pix_created": 0, "pix_error": 0,
    "received": 0, "skipped_status": 0, "no_clickid": 0,
    "forwarded": 0, "attributed": 0, "rt_error": 0, "duplicate": 0,
    "ec_sent": 0, "ec_skipped_no_email": 0, "ec_error": 0, "ec_disabled": 0,
    "upsell1_started": 0, "upsell1_paid": 0,
    "upsell2_started": 0, "upsell2_paid": 0,
}
_SEEN_TX: dict[str, float] = {}   # dedupe de webhooks por tx id -> ts (TTL p/ nao vazar memoria)
DEDUP_TTL = 86400  # 24h — cobre a janela de retry (at-least-once) do gateway


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


# ── Enhanced Conversions for Leads (Google Ads) ───────────────────────────────
# payer capturado no /api/pix, indexado por tx_id, p/ enriquecer a conversao no
# webhook (que so traz externalRef+amount). Memoria zera no restart -> fallback
# re-busca a transacao no gateway. So manda EC p/ vendas com rtkcid valido (mesmo
# filtro do postback) -> nao manda email de trafego de outras fontes pro Google.
_PAYER_BY_TX: dict[str, dict] = {}


def _sha256_hex(s: str) -> str:
    return hashlib.sha256(s.encode("utf-8")).hexdigest()


def _norm_email(email: str | None) -> str:
    """Normalizacao do Google p/ EC: trim + lowercase."""
    return (email or "").strip().lower()


def _norm_phone_e164(phone: str | None, default_cc: str = "55") -> str:
    """E.164 (+55...). '' se vazio."""
    d = re.sub(r"\D", "", phone or "")
    if not d:
        return ""
    if not d.startswith(default_cc) and len(d) <= 11:   # numero BR sem DDI
        d = default_cc + d
    return "+" + d


def _ec_now() -> str:
    """Agora no offset configurado: 'YYYY-MM-DD HH:MM:SS-03:00' (formato aceito pelo Google)."""
    sign = -1 if EC_TZ_OFFSET.startswith("-") else 1
    hh, mm = int(EC_TZ_OFFSET[1:3]), int(EC_TZ_OFFSET[4:6])
    tz = timezone(sign * timedelta(hours=hh, minutes=mm))
    return datetime.now(tz).strftime("%Y-%m-%d %H:%M:%S") + EC_TZ_OFFSET


async def _send_enhanced_conversion(tx_id: str, clickid: str, reais: float) -> None:
    """Best-effort: grava a conversao hasheada na Google Sheet (via Apps Script).
    NUNCA levanta excecao — o postback RedTrack e o caminho primario do dinheiro."""
    if not EC_ENABLED:
        STATS["ec_disabled"] += 1
        return
    payer = _PAYER_BY_TX.get(tx_id) or {}
    email, phone = payer.get("email"), payer.get("phone")
    if not email and tx_id:                       # fallback: re-busca no gateway (sobrevive restart)
        try:
            async with httpx.AsyncClient(timeout=15) as c:
                rr = await c.get(f"{GATEWAY_API}/v1/payment/{tx_id}", headers=_PAY_HEADERS)
            if rr.status_code < 400:
                p = (rr.json() or {}).get("payer") or {}
                email, phone = email or p.get("email"), phone or p.get("phone")
        except Exception:
            pass
    email_n = _norm_email(email)
    if not email_n:
        STATS["ec_skipped_no_email"] += 1
        _record("ec_no_email", {"tx_id": tx_id, "clickid": clickid})
        return
    phone_e164 = _norm_phone_e164(phone, EC_DEFAULT_CC)
    row = {
        "secret": EC_SHEET_SECRET,
        "email_sha256": _sha256_hex(email_n),
        "phone_sha256": _sha256_hex(phone_e164) if phone_e164 else "",
        "conversion_name": EC_CONVERSION_NAME,
        "conversion_time": _ec_now(),
        "conversion_value": f"{reais:.2f}",
        "conversion_currency": "BRL",
        "order_id": tx_id or clickid,
    }
    try:
        async with httpx.AsyncClient(timeout=15) as c:
            rr = await c.post(EC_SHEET_WEBHOOK_URL, json=row)
        ok = rr.status_code < 400
        STATS["ec_sent" if ok else "ec_error"] += 1
        _record("ec_sent" if ok else "ec_error", {"order_id": row["order_id"]},
                ec_status=rr.status_code, ec_body=rr.text[:150])
        log.info("EC -> sheet order=%s status=%s", row["order_id"], rr.status_code)
    except Exception as e:
        STATS["ec_error"] += 1
        _record("ec_exception", {"order_id": row["order_id"]}, error=str(e)[:150])
        log.error("EC send failed: %s", e)


# ── páginas / config ──────────────────────────────────────────────────────────
@app.get("/", response_class=HTMLResponse)
@app.get("/checkout", response_class=HTMLResponse)
def checkout_page():
    if not _HTML:
        return HTMLResponse("<h1>checkout.html ausente</h1>", status_code=500)
    return HTMLResponse(_HTML)


@app.get("/banner.png")
def banner():
    p = Path(__file__).resolve().parent / "banner.png"
    if p.exists():
        return FileResponse(str(p), media_type="image/png")
    return JSONResponse({"error": "no banner"}, status_code=404)


@app.get("/icone.png")
def icone():
    p = Path(__file__).resolve().parent / "icone.png"
    if p.exists():
        return FileResponse(str(p), media_type="image/png")
    return JSONResponse({"error": "no icone"}, status_code=404)


@app.get("/upsell1", response_class=HTMLResponse)
def upsell1_page():
    return HTMLResponse(_UPSELL1_HTML or "<h1>upsell1.html ausente</h1>",
                        status_code=200 if _UPSELL1_HTML else 500)

@app.get("/upsell2", response_class=HTMLResponse)
def upsell2_page():
    return HTMLResponse(_UPSELL2_HTML or "<h1>upsell2.html ausente</h1>",
                        status_code=200 if _UPSELL2_HTML else 500)

@app.get("/obrigado", response_class=HTMLResponse)
def obrigado_page():
    return HTMLResponse(_OBRIGADO_HTML or "<h1>obrigado.html ausente</h1>",
                        status_code=200 if _OBRIGADO_HTML else 500)

@app.get("/api/config")
def api_config(offer: str | None = None):
    price = OFFER_PRICES.get(offer or "", PRICE_CENTAVOS)
    return {"title": PRODUCT_TITLE, "price_centavos": price,
            "price_brl": f"{price/100:.2f}".replace(".", ",")}

@app.get("/api/upsell-config")
def api_upsell_config():
    return {
        "upsell1": {"title": UPSELL1_TITLE,
                    "price_centavos": UPSELL1_PRICE,
                    "price_brl": f"{UPSELL1_PRICE/100:.2f}".replace(".", ",")},
        "upsell2": {"title": UPSELL2_TITLE,
                    "price_centavos": UPSELL2_PRICE,
                    "price_brl": f"{UPSELL2_PRICE/100:.2f}".replace(".", ",")},
    }


@app.get("/healthz")
def health():
    return {"ok": True}


@app.get("/info")
def info():
    return {
        "service": "streetpays-checkout",
        "version": "2.1.0",
        "gateway": "streetpays",
        "gateway_api": GATEWAY_API,
        "gateway_key_set": bool(STREETPAYS_KEY),
        "redtrack_postback": REDTRACK_POSTBACK,
        "price_centavos": PRICE_CENTAVOS,
        "product_title": PRODUCT_TITLE,
        "self_base_url": SELF_BASE_URL or "(NAO setado!)",
        "clickid_validation": "externalRef 24-hex rtkcid only",
        "enhanced_conversions": {"enabled": EC_ENABLED,
                                 "conversion_name": EC_CONVERSION_NAME,
                                 "sheet_webhook_set": bool(EC_SHEET_WEBHOOK_URL)},
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
    payer: dict[str, Any] = {"name": body.name.strip(), "email": body.email.strip()}
    if cpf_digits:
        payer["taxId"] = cpf_digits
    if body.phone:
        payer["phone"] = re.sub(r"\D", "", body.phone)

    payload = {
        "amount": amount,
        "currency": "BRL",
        "method": "PIX",
        "description": GATEWAY_DESC,                    # ← gateway/extrato vê "Taxa Correios"
        "externalRef": (body.src or "").strip(),      # ← clickid viaja aqui; webhook devolve
        "payer": payer,
        "items": [{"quantity": 1, "name": GATEWAY_DESC, "price": amount, "type": "DIGITAL"}],
    }
    if SELF_BASE_URL:
        payload["notificationUrl"] = f"{SELF_BASE_URL}/webhook"

    try:
        async with httpx.AsyncClient(timeout=30) as c:
            r = await c.post(f"{GATEWAY_API}/v1/payment", json=payload, headers=_PAY_HEADERS)
    except Exception as e:
        STATS["pix_error"] += 1
        _record("pix_exception", {"externalRef": body.src, "offer": body.offer}, error=str(e)[:200])
        raise HTTPException(502, f"streetpays create exception: {e!s}")

    if r.status_code >= 400:
        STATS["pix_error"] += 1
        _record("pix_create_error", {"externalRef": body.src}, status=r.status_code, body=r.text[:400])
        log.error("streetpays create %s: %s", r.status_code, r.text[:300])
        raise HTTPException(502, f"streetpays create failed: {r.status_code}")

    tx = r.json()
    STATS["pix_created"] += 1
    offer_key = body.offer or ""
    soft = "" if _looks_like_rtkcid(body.src) else " (src nao-rtkcid)"
    _record("pix_created" + soft, {"externalRef": body.src, "offer": offer_key},
            tx_id=tx.get("id"), amount=amount)
    if tx.get("id"):                              # guarda payer p/ Enhanced Conversions no webhook
        tid = str(tx.get("id"))
        _PAYER_BY_TX[tid] = {"email": body.email.strip(),
                             "phone": body.phone, "name": body.name.strip()}
        if len(_PAYER_BY_TX) > 5000:
            for k in list(_PAYER_BY_TX)[:1000]:
                _PAYER_BY_TX.pop(k, None)
        # rastreia tipo upsell para stats
        if offer_key in ("upsell1", "upsell2"):
            _UPSELL_TX[tid] = offer_key
            STATS[f"{offer_key}_started"] += 1
    return {"transaction_id": tx.get("id"),
            "qrcode": (tx.get("data") or {}).get("copypaste", ""),
            "amount_centavos": amount}


@app.get("/api/status/{tx_id}")
async def status(tx_id: str):
    # verificação instantânea via webhook (O(1)) — sem chamar StreetPays
    if tx_id in _PAID_TX:
        return {"status": "PAID", "paid": True}
    # fallback: consulta StreetPays (cobre caso de restart onde _PAID_TX foi zerado)
    try:
        async with httpx.AsyncClient(timeout=20) as c:
            r = await c.get(f"{GATEWAY_API}/v1/payment/{tx_id}", headers=_PAY_HEADERS)
    except Exception as e:
        raise HTTPException(502, f"streetpays status exception: {e!s}")
    if r.status_code >= 400:
        raise HTTPException(502, f"streetpays status failed: {r.status_code}")
    tx = r.json()
    st = str(tx.get("status", "")).upper()
    paid = st in ("PAID", "APPROVED")
    if paid:
        _PAID_TX.add(tx_id)   # atualiza cache para próximos polls
    return {"status": st, "paid": paid}


# ── webhook StreetPays -> postback RedTrack (a MARCAÇÃO acontece aqui) ─────────
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

    # StreetPays manda o objeto de pagamento no TOPO. Fallback p/ "data" se vier aninhado.
    obj = payload
    if not obj.get("status") and isinstance(payload.get("data"), dict) and payload["data"].get("status"):
        obj = payload["data"]
    status_ = str(obj.get("status", "")).upper()
    log.info("webhook status=%s externalRef=%s amount=%s",
             status_, obj.get("externalRef"), obj.get("amount"))

    if status_ not in ("PAID", "APPROVED"):
        STATS["skipped_status"] += 1
        _record("skipped_status", payload, status=status_)
        return {"ok": True, "skipped": "status_not_paid", "status": status_}

    tx_id = str(obj.get("id") or "")
    # marca pagamento IMEDIATAMENTE para que /api/status retorne paid=True no próximo poll
    if tx_id:
        _PAID_TX.add(tx_id)

    # Dedup por transaction id, com TTL p/ nao vazar memoria. Protege contra retry
    # do gateway (at-least-once) re-disparando stats/postback p/ a MESMA transacao.
    global _SEEN_TX
    now_ts = time.time()
    _SEEN_TX = {k: ts for k, ts in _SEEN_TX.items() if now_ts - ts < DEDUP_TTL}
    if tx_id and tx_id in _SEEN_TX:
        STATS["duplicate"] += 1
        _record("duplicate", payload, tx_id=tx_id)
        return {"ok": True, "duplicate": tx_id}
    if tx_id:
        _SEEN_TX[tx_id] = now_ts

    # Upsell: contabiliza e PARA — NAO re-atribui no RedTrack nem manda EC. A conversao
    # de aquisicao ja foi marcada na venda principal (MESMO rtkcid). Re-postar no mesmo
    # clickid duplicaria (postback_mode=all) ou sobrescreveria (=update), e mandaria uma
    # 2a/3a "Compra" pro Google via Enhanced Conversions. A receita do upsell fica nos
    # STATS (upsellN_paid) + painel do gateway — nao se perde, so nao polui a atribuicao.
    upsell_type = _UPSELL_TX.get(tx_id)
    if upsell_type:
        STATS[f"{upsell_type}_paid"] += 1
        _record("upsell_paid_no_reattrib", payload, tx_id=tx_id, upsell=upsell_type)
        return {"ok": True, "upsell": upsell_type, "skipped": "no_reattribution"}

    clickid = str(obj.get("externalRef") or "").strip()
    if not _looks_like_rtkcid(clickid):
        STATS["no_clickid"] += 1
        _record("no_clickid", payload, externalRef=clickid)
        log.warning("externalRef nao e rtkcid 24-hex: %r", clickid)
        return JSONResponse({"ok": False, "error": "externalRef_not_rtkcid"}, status_code=200)

    cents = obj.get("amount") or 0
    reais = (cents / 100.0) if isinstance(cents, (int, float)) else 0.0
    await _send_enhanced_conversion(tx_id, clickid, reais)   # EC p/ Google (best-effort, nao bloqueia)
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
