"""
GTA VI Pre-Order — FastAPI backend com StreetPays PIX.

Endpoints:
  GET  /              -> index.html (landing page)
  GET  /checkout      -> checkout.html
  GET  /obrigado      -> obrigado.html
  GET  /assets/*      -> arquivos estáticos (imagens)
  POST /api/pagamento/pix -> cria PIX via StreetPays (mesma assinatura do site original)
  GET  /api/status/{id}   -> polling de status do pagamento
  POST /webhook           -> notificação StreetPays (PAID)
  GET  /healthz           -> {ok: true}
  GET  /debug/stats       -> estatísticas em memória

Env:
  STREETPAYS_API_KEY    chave Bearer
  SELF_BASE_URL         URL pública deste serviço (para notificationUrl)
  LOG_LEVEL             INFO (default)
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
from fastapi.responses import FileResponse, HTMLResponse, JSONResponse
from fastapi.staticfiles import StaticFiles
from pydantic import BaseModel

logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("gta6preorder")

STREETPAYS_KEY = os.environ.get("STREETPAYS_API_KEY", "")
GATEWAY_API = "https://api.streetpays.com.br"
SELF_BASE_URL = os.environ.get("SELF_BASE_URL", "").rstrip("/")

PRICES = {
    "standard": 44990,   # R$449,90
    "ultimate": 54990,   # R$549,90
}
EDITION_NAMES = {
    "standard": "GTA VI Edição Standard",
    "ultimate": "GTA VI Edição Ultimate",
}
PLATFORM_NAMES = {
    "ps5": "PlayStation 5",
    "xbox": "Xbox Series X|S",
}

_PAY_HEADERS = {
    "Authorization": f"Bearer {STREETPAYS_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
}

app = FastAPI(title="GTA VI Pre-Order Store", version="1.0.0")

# Detecção de pagamento instantânea (webhook → /api/status sem chamada extra)
_PAID_TX: set[str] = set()
_SEEN_TX: dict[str, float] = {}
DEDUP_TTL = 86400

RECENT: deque = deque(maxlen=80)
STATS = {
    "pix_requested": 0, "pix_created": 0, "pix_error": 0,
    "webhook_received": 0, "webhook_paid": 0, "webhook_skipped": 0,
    "duplicate": 0,
}


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


def _record(outcome: str, payload: Any, **extra) -> None:
    try:
        raw = _json.dumps(payload)[:1400]
    except Exception:
        raw = str(payload)[:1400]
    RECENT.appendleft({"ts": _now(), "outcome": outcome, "payload": raw, **extra})


# ── HTML pages ─────────────────────────────────────────────────────────────────

def _load_html(name: str) -> str:
    p = Path(__file__).resolve().parent / name
    return p.read_text(encoding="utf-8") if p.exists() else f"<h1>{name} não encontrado</h1>"


_INDEX_HTML    = _load_html("index.html")
_CHECKOUT_HTML = _load_html("checkout.html")
_OBRIGADO_HTML = _load_html("obrigado.html")
_BLOG_HTML     = _load_html("blog.html")


@app.get("/", response_class=HTMLResponse)
def index():
    return HTMLResponse(_INDEX_HTML)


@app.get("/checkout", response_class=HTMLResponse)
def checkout():
    return HTMLResponse(_CHECKOUT_HTML)


@app.get("/obrigado", response_class=HTMLResponse)
def obrigado():
    return HTMLResponse(_OBRIGADO_HTML)


@app.get("/blog", response_class=HTMLResponse)
def blog():
    return HTMLResponse(_BLOG_HTML)


@app.get("/healthz")
def health():
    return {"ok": True}


@app.get("/debug/stats")
def debug_stats():
    return {"stats": STATS, "recent_count": len(RECENT)}


@app.get("/debug/recent")
def debug_recent(n: int = 30):
    return {"stats": STATS, "recent": list(RECENT)[:max(1, min(n, 80))]}


# ── Montar assets estáticos ────────────────────────────────────────────────────
_assets_dir = Path(__file__).resolve().parent / "assets"
if _assets_dir.exists():
    app.mount("/assets", StaticFiles(directory=str(_assets_dir)), name="assets")


# ── Criar PIX ─────────────────────────────────────────────────────────────────

class PixRequest(BaseModel):
    customer: dict            # {name, email, cpf, phone}
    edition: str = "standard" # standard | ultimate
    platform: str = "ps5"    # ps5 | xbox
    price: float | None = None
    externalRef: str = ""


@app.post("/api/pagamento/pix")
async def create_pix(body: PixRequest):
    STATS["pix_requested"] += 1

    edition = body.edition if body.edition in PRICES else "standard"
    amount = PRICES[edition]
    product_name = EDITION_NAMES[edition]
    external_ref = body.externalRef or f"gta6-{int(time.time()*1000)}"

    customer = body.customer or {}
    cpf_digits = re.sub(r"\D", "", str(customer.get("cpf", "") or ""))
    phone_digits = re.sub(r"\D", "", str(customer.get("phone", "") or ""))

    payer: dict = {
        "name": str(customer.get("name", "")).strip() or "Cliente",
        "email": str(customer.get("email", "")).strip(),
    }
    if cpf_digits:
        payer["taxId"] = cpf_digits
    if phone_digits:
        payer["phone"] = phone_digits

    payload: dict = {
        "amount": amount,
        "currency": "BRL",
        "method": "PIX",
        "description": product_name,
        "externalRef": external_ref,
        "payer": payer,
        "items": [{"quantity": 1, "name": product_name, "price": amount, "type": "DIGITAL"}],
    }
    if SELF_BASE_URL:
        payload["notificationUrl"] = f"{SELF_BASE_URL}/webhook"

    log.info("pix_create edition=%s amount=%s externalRef=%s", edition, amount, external_ref)

    try:
        async with httpx.AsyncClient(timeout=30) as c:
            r = await c.post(f"{GATEWAY_API}/v1/payment", json=payload, headers=_PAY_HEADERS)
    except Exception as e:
        STATS["pix_error"] += 1
        _record("pix_exception", {"externalRef": external_ref}, error=str(e)[:200])
        raise HTTPException(502, f"gateway exception: {e!s}")

    if r.status_code >= 400:
        STATS["pix_error"] += 1
        _record("pix_create_error", {"externalRef": external_ref},
                status=r.status_code, body=r.text[:400])
        log.error("streetpays %s: %s", r.status_code, r.text[:300])
        raise HTTPException(502, f"gateway error: {r.status_code}")

    tx = r.json()
    STATS["pix_created"] += 1
    tx_id = tx.get("id", "")
    pix_code = (tx.get("data") or {}).get("copypaste", "")
    _record("pix_created", {"externalRef": external_ref}, tx_id=tx_id, amount=amount)

    # Resposta no formato esperado pelo frontend (compatível com o site original)
    return {
        "success": True,
        "data": {
            "pix": {
                "qrcode": pix_code,
                "expirationDate": (tx.get("data") or {}).get("expirationDate", ""),
            },
            "id": tx_id,
        },
    }


@app.get("/api/status/{tx_id}")
async def payment_status(tx_id: str):
    if tx_id in _PAID_TX:
        return {"status": "PAID", "paid": True}

    try:
        async with httpx.AsyncClient(timeout=20) as c:
            r = await c.get(f"{GATEWAY_API}/v1/payment/{tx_id}", headers=_PAY_HEADERS)
    except Exception as e:
        raise HTTPException(502, f"gateway status exception: {e!s}")

    if r.status_code >= 400:
        raise HTTPException(502, f"gateway status failed: {r.status_code}")

    tx = r.json()
    st = str(tx.get("status", "")).upper()
    paid = st in ("PAID", "APPROVED")
    if paid:
        _PAID_TX.add(tx_id)
    return {"status": st, "paid": paid}


# ── Webhook StreetPays ─────────────────────────────────────────────────────────

@app.post("/webhook")
async def webhook(request: Request):
    STATS["webhook_received"] += 1
    raw = await request.body()
    try:
        payload = _json.loads(raw)
    except Exception:
        payload = {"_raw": raw.decode("utf-8", "replace")[:1500]}

    if not isinstance(payload, dict):
        payload = {"_value": payload}

    obj = payload
    if not obj.get("status") and isinstance(payload.get("data"), dict):
        obj = payload["data"]

    status_ = str(obj.get("status", "")).upper()
    tx_id = str(obj.get("id") or "")
    ext_ref = str(obj.get("externalRef") or "")
    log.info("webhook status=%s tx_id=%s externalRef=%s", status_, tx_id, ext_ref)

    if status_ not in ("PAID", "APPROVED"):
        STATS["webhook_skipped"] += 1
        _record("skipped_status", payload, status=status_)
        return {"ok": True, "skipped": status_}

    # Dedup
    global _SEEN_TX
    now_ts = time.time()
    _SEEN_TX = {k: ts for k, ts in _SEEN_TX.items() if now_ts - ts < DEDUP_TTL}
    if tx_id and tx_id in _SEEN_TX:
        STATS["duplicate"] += 1
        return {"ok": True, "duplicate": tx_id}
    if tx_id:
        _SEEN_TX[tx_id] = now_ts
        _PAID_TX.add(tx_id)

    STATS["webhook_paid"] += 1
    cents = obj.get("amount") or 0
    reais = (cents / 100.0) if isinstance(cents, (int, float)) else 0.0
    _record("paid", payload, tx_id=tx_id, externalRef=ext_ref, amount=reais)
    log.info("PAID tx_id=%s externalRef=%s amount=R$%.2f", tx_id, ext_ref, reais)

    return {"ok": True, "tx_id": tx_id, "amount": reais}
