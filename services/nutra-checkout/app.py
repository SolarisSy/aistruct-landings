"""
Checkout PROPRIO (PIX via StreetPays) para as ofertas nutra (safe que converte).

Um unico servico serve os 2 dominios; o produto/preco/tema sao resolvidos pelo
Host (pay.vivamaissaude.site = prostata, pay.vidaleveja.store = emagrecimento).
Sem RedTrack (essas ofertas sao UTMify/FB) — o clickid/fbclid viaja em externalRef
so para rastreabilidade no painel StreetPays. Dinheiro cai na loja "Solas".

Fluxo:
  GET  /            -> checkout.html (tema por Host)
  GET  /api/config  -> {brand,title,subtitle,price_centavos,price_brl,theme...}  (por Host)
  POST /api/pix     -> cria PIX StreetPays {name,email,cpf,phone,src} -> {transaction_id,qrcode}
  GET  /api/status/{id} -> {status, paid}   (polling; O(1) via _PAID_TX, fallback API)
  POST /webhook     -> StreetPays notifica; marca PAID (deteccao instantanea no polling)
  GET  /obrigado    -> obrigado.html
  GET  /healthz /info /debug/stats /debug/recent

Env:
  STREETPAYS_API_KEY   (obrigatorio) — key de pagamento da loja Solas
  STREETPAYS_API_BASE  default https://api.streetpays.com.br
  SELF_BASE_URL        URL https publica deste servico (p/ notificationUrl) — opcional
  NUTRA_PRICE_CENTAVOS default 14700 (R$147,00) — override global de preco
  LOG_LEVEL            default INFO
"""
from __future__ import annotations

import json as _json
import logging
import os
import re
import time
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
log = logging.getLogger("nutra-checkout")

STREETPAYS_KEY = os.environ.get("STREETPAYS_API_KEY", "")
GATEWAY_API = os.environ.get("STREETPAYS_API_BASE", "https://api.streetpays.com.br").rstrip("/")
SELF_BASE_URL = os.environ.get("SELF_BASE_URL", "").rstrip("/")
PRICE_CENTAVOS = int(os.environ.get("NUTRA_PRICE_CENTAVOS", "14700"))

_PAY_HEADERS = {"Authorization": f"Bearer {STREETPAYS_KEY}",
                "Content-Type": "application/json", "Accept": "application/json"}

# ── Config por OFERTA — produto/preco/tema ─────────────────────────────────────
# Resolvida por ?offer=<slug> (primario, robusto atras do Bunny que reescreve Host)
# com fallback pelo Host/X-Forwarded-Host. checkout.html passa ?offer= explicito.
OFFERS: dict[str, dict[str, Any]] = {
    "prostata": {
        "slug": "prostata",
        "brand": "Viva Mais Saúde",
        "title": "Protocolo Bem-Estar Masculino",
        "subtitle": "Fórmula natural de apoio ao bem-estar do homem após os 40",
        "desc": "Viva Mais Saude - Protocolo Bem-Estar",
        "primary": "#1f7a53", "primary2": "#2e9e6c", "bg": "#f7faf8", "ink": "#1a2b24",
    },
    "emagrecimento": {
        "slug": "emagrecimento",
        "brand": "Vida Leve",
        "title": "Programa Vida Leve",
        "subtitle": "Fórmula natural de apoio à sua rotina de bem-estar",
        "desc": "Vida Leve - Programa",
        "primary": "#c96a2b", "primary2": "#e08a3c", "bg": "#fbf7f2", "ink": "#2a221c",
    },
}
HOST2SLUG = {"vivamaissaude.site": "prostata", "vidaleveja.store": "emagrecimento"}
DEFAULT_SLUG = "prostata"


def _resolve_slug(request: Request, offer: str | None) -> str:
    if offer and offer in OFFERS:
        return offer
    h = (request.headers.get("x-forwarded-host") or request.headers.get("host") or "").split(",")[0].strip().lower()
    h = h.split(":")[0]
    for pref in ("pay.", "www."):
        if h.startswith(pref):
            h = h[len(pref):]
    return HOST2SLUG.get(h, DEFAULT_SLUG)


def _cfg(request: Request, offer: str | None = None) -> dict[str, Any]:
    return OFFERS[_resolve_slug(request, offer)]


app = FastAPI(title="Nutra Checkout (StreetPays PIX)", version="1.0.0")

# ── observabilidade + deteccao de pagamento ────────────────────────────────────
_PAID_TX: set[str] = set()
_SEEN_TX: dict[str, float] = {}
DEDUP_TTL = 86400
RECENT: deque = deque(maxlen=80)
STATS = {"pix_requested": 0, "pix_created": 0, "pix_error": 0,
         "received": 0, "paid": 0, "skipped_status": 0, "duplicate": 0}


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


def _record(outcome: str, payload: Any, **extra) -> None:
    try:
        raw = _json.dumps(payload)[:1200]
    except Exception:
        raw = str(payload)[:1200]
    RECENT.appendleft({"ts": _now(), "outcome": outcome, "payload": raw, **extra})


def _load_html(name: str) -> str:
    p = Path(__file__).resolve().parent / name
    return p.read_text(encoding="utf-8") if p.exists() else ""


_CHECKOUT_HTML = _load_html("checkout.html")
_OBRIGADO_HTML = _load_html("obrigado.html")


# ── paginas ────────────────────────────────────────────────────────────────────
@app.get("/", response_class=HTMLResponse)
@app.get("/checkout", response_class=HTMLResponse)
def checkout_page():
    if not _CHECKOUT_HTML:
        return HTMLResponse("<h1>checkout.html ausente</h1>", status_code=500)
    return HTMLResponse(_CHECKOUT_HTML)


@app.get("/obrigado", response_class=HTMLResponse)
def obrigado_page():
    return HTMLResponse(_OBRIGADO_HTML or "<h1>obrigado.html ausente</h1>",
                        status_code=200 if _OBRIGADO_HTML else 500)


@app.get("/api/config")
def api_config(request: Request, offer: str | None = None):
    c = _cfg(request, offer)
    return {
        "brand": c["brand"], "title": c["title"], "subtitle": c["subtitle"],
        "price_centavos": PRICE_CENTAVOS,
        "price_brl": f"{PRICE_CENTAVOS/100:.2f}".replace(".", ","),
        "theme": {"primary": c["primary"], "primary2": c["primary2"], "bg": c["bg"], "ink": c["ink"]},
    }


@app.get("/healthz")
def health():
    return {"ok": True}


@app.get("/info")
def info():
    return {"service": "nutra-checkout", "version": "1.0.0", "gateway": "streetpays",
            "gateway_api": GATEWAY_API, "gateway_key_set": bool(STREETPAYS_KEY),
            "price_centavos": PRICE_CENTAVOS, "self_base_url": SELF_BASE_URL or "(nao setado)",
            "offers": {h: o["slug"] for h, o in OFFERS.items()}}


@app.get("/debug/stats")
def debug_stats():
    return {"stats": STATS, "recent_count": len(RECENT), "note": "zera no restart"}


@app.get("/debug/recent")
def debug_recent(n: int = 30):
    return {"stats": STATS, "recent": list(RECENT)[: max(1, min(n, 80))]}


# ── criar PIX ───────────────────────────────────────────────────────────────────
class PixIn(BaseModel):
    name: str
    email: str
    cpf: str | None = None
    phone: str | None = None
    src: str = ""            # fbclid/utm — so rastreabilidade (nao ha RedTrack aqui)
    offer: str | None = None  # slug da oferta (prostata|emagrecimento)


@app.post("/api/pix")
async def create_pix(body: PixIn, request: Request):
    STATS["pix_requested"] += 1
    if not STREETPAYS_KEY:
        raise HTTPException(500, "STREETPAYS_API_KEY nao configurada")
    c = _cfg(request, body.offer)
    amount = PRICE_CENTAVOS
    cpf_digits = re.sub(r"\D", "", body.cpf or "")
    payer: dict[str, Any] = {"name": body.name.strip(), "email": body.email.strip()}
    if cpf_digits:
        payer["taxId"] = cpf_digits
    if body.phone:
        payer["phone"] = re.sub(r"\D", "", body.phone)

    payload = {
        "amount": amount, "currency": "BRL", "method": "PIX",
        "description": c["desc"],
        "externalRef": (body.src or "").strip(),
        "payer": payer,
        "items": [{"quantity": 1, "name": c["title"], "price": amount, "type": "DIGITAL"}],
    }
    if SELF_BASE_URL:
        payload["notificationUrl"] = f"{SELF_BASE_URL}/webhook"

    try:
        async with httpx.AsyncClient(timeout=30) as cx:
            r = await cx.post(f"{GATEWAY_API}/v1/payment", json=payload, headers=_PAY_HEADERS)
    except Exception as e:
        STATS["pix_error"] += 1
        _record("pix_exception", {"slug": c["slug"], "src": body.src}, error=str(e)[:200])
        raise HTTPException(502, f"streetpays create exception: {e!s}")

    if r.status_code >= 400:
        STATS["pix_error"] += 1
        _record("pix_create_error", {"slug": c["slug"], "src": body.src},
                status=r.status_code, body=r.text[:400])
        log.error("streetpays create %s: %s", r.status_code, r.text[:300])
        raise HTTPException(502, f"streetpays create failed: {r.status_code}")

    tx = r.json()
    STATS["pix_created"] += 1
    _record("pix_created", {"slug": c["slug"], "src": body.src},
            tx_id=tx.get("id"), amount=amount)
    return {"transaction_id": tx.get("id"),
            "qrcode": (tx.get("data") or {}).get("copypaste", ""),
            "amount_centavos": amount}


@app.get("/api/status/{tx_id}")
async def status(tx_id: str):
    if tx_id in _PAID_TX:
        return {"status": "PAID", "paid": True}
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
        _PAID_TX.add(tx_id)
    return {"status": st, "paid": paid}


# ── webhook StreetPays (marca PAID; sem postback — atribuicao e UTMify/FB) ──────
@app.post("/webhook")
async def webhook(request: Request):
    STATS["received"] += 1
    raw = await request.body()
    try:
        payload = _json.loads(raw)
    except Exception:
        payload = {"_raw": raw.decode("utf-8", "replace")[:1200]}
    if not isinstance(payload, dict):
        payload = {"_value": payload}

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
    if tx_id:
        _PAID_TX.add(tx_id)

    now_ts = time.time()
    global _SEEN_TX
    _SEEN_TX = {k: ts for k, ts in _SEEN_TX.items() if now_ts - ts < DEDUP_TTL}
    if tx_id and tx_id in _SEEN_TX:
        STATS["duplicate"] += 1
        _record("duplicate", payload, tx_id=tx_id)
        return {"ok": True, "duplicate": tx_id}
    if tx_id:
        _SEEN_TX[tx_id] = now_ts

    STATS["paid"] += 1
    _record("paid", payload, tx_id=tx_id, externalRef=obj.get("externalRef"), amount=obj.get("amount"))
    return {"ok": True, "paid": tx_id}
