"""
Checkout PIX STANDALONE — produto "Loterias para aprender Excel".

Cópia DEDICADA e editável do payshark-checkout, SEM a parte de tracking:
não há postback pro RedTrack (este produto é standalone, não marca conversão
na campanha do Google). Faz só o essencial de um checkout PIX próprio:
  1. serve checkout.html (página editável; produto/preço/copy no código);
  2. cria a cobrança PIX via StreetPays POST /v1/payment;
  3. mostra QR / copia-e-cola e faz polling do status.

Gateway = StreetPays (api.streetpays.com.br/v1), Bearer STREETPAYS_API_KEY.

Endpoints:
  GET  /                 -> checkout.html
  GET  /checkout         -> checkout.html
  GET  /api/config       -> {title, price_centavos, price_brl}
  POST /api/pix          -> cria PIX {name,email,cpf,phone,src,offer} -> {transaction_id,qrcode}
  GET  /api/status/{id}  -> {status, paid}    (polling)
  GET  /healthz          -> {ok:true}
  GET  /info             -> config do serviço
  GET  /debug/recent     -> últimas requisições (memória; zera no restart)

Env:
  STREETPAYS_API_KEY     Bearer da StreetPays (obrigatório p/ criar PIX)
  STREETPAYS_API_BASE    default https://api.streetpays.com.br
  PRICE_CENTAVOS         default 9700   (R$97,00) — preço único, editável
  PRODUCT_TITLE          default "Loterias para aprender Excel"
  GATEWAY_DESC           descrição enviada AO GATEWAY (extrato/fatura). default "Curso Excel"
  SELF_BASE_URL          URL pública https deste serviço (p/ notificationUrl da StreetPays)
  OFFER_PRICES           JSON opcional {"slug":centavos} p/ preço por oferta
  LOG_LEVEL              default INFO
"""
from __future__ import annotations

import json as _json
import logging
import os
import re
from collections import deque
from datetime import datetime, timezone
from pathlib import Path
from typing import Any

import httpx
from fastapi import FastAPI, HTTPException
from fastapi.responses import HTMLResponse, JSONResponse
from pydantic import BaseModel

logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("excel-checkout")

STREETPAYS_KEY = os.environ.get("STREETPAYS_API_KEY", "")
GATEWAY_API = os.environ.get("STREETPAYS_API_BASE", "https://api.streetpays.com.br").rstrip("/")
PRICE_CENTAVOS = int(os.environ.get("PRICE_CENTAVOS", "9700"))
PRODUCT_TITLE = os.environ.get("PRODUCT_TITLE", "Loterias para aprender Excel")
# descrição enviada AO GATEWAY (extrato/fatura) — desacoplada do que o cliente vê.
GATEWAY_DESC = os.environ.get("GATEWAY_DESC", "Curso Excel")
SELF_BASE_URL = os.environ.get("SELF_BASE_URL", "").rstrip("/")
try:
    OFFER_PRICES = _json.loads(os.environ.get("OFFER_PRICES", "{}"))
except Exception:
    OFFER_PRICES = {}

_PAY_HEADERS = {"Authorization": f"Bearer {STREETPAYS_KEY}",
                "Content-Type": "application/json", "Accept": "application/json"}

app = FastAPI(title="Excel Checkout (PIX standalone)", version="1.0.0")

# checkout.html carregado uma vez
_HTML = ""
_html_path = Path(__file__).resolve().parent / "checkout.html"
if _html_path.exists():
    _HTML = _html_path.read_text(encoding="utf-8")

# observabilidade em memória (zera no restart; suficiente p/ QA)
RECENT: deque = deque(maxlen=80)


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


def _record(outcome: str, payload: Any, **extra) -> None:
    try:
        raw = _json.dumps(payload)[:1200]
    except Exception:
        raw = str(payload)[:1200]
    RECENT.appendleft({"ts": _now(), "outcome": outcome, "payload": raw, **extra})


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
        "service": "excel-checkout",
        "version": "1.0.0",
        "gateway": "streetpays",
        "gateway_api": GATEWAY_API,
        "gateway_key_set": bool(STREETPAYS_KEY),
        "tracking": "NONE (standalone — sem postback RedTrack)",
        "price_centavos": PRICE_CENTAVOS,
        "product_title": PRODUCT_TITLE,
        "self_base_url": SELF_BASE_URL or "(nao setado)",
    }


@app.get("/debug/recent")
def debug_recent(n: int = 30):
    return {"recent": list(RECENT)[: max(1, min(n, 80))]}


# ── criar PIX ─────────────────────────────────────────────────────────────────
class PixIn(BaseModel):
    name: str
    email: str
    cpf: str | None = None
    phone: str | None = None
    src: str = ""          # opcional; standalone ignora p/ tracking, mas guarda no externalRef
    offer: str | None = None


@app.post("/api/pix")
async def create_pix(body: PixIn):
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
        "description": GATEWAY_DESC,
        "externalRef": (body.src or "").strip(),
        "payer": payer,
        "items": [{"quantity": 1, "name": GATEWAY_DESC, "price": amount, "type": "DIGITAL"}],
    }
    if SELF_BASE_URL:
        payload["notificationUrl"] = f"{SELF_BASE_URL}/webhook"

    try:
        async with httpx.AsyncClient(timeout=30) as c:
            r = await c.post(f"{GATEWAY_API}/v1/payment", json=payload, headers=_PAY_HEADERS)
    except Exception as e:
        _record("pix_exception", {"offer": body.offer}, error=str(e)[:200])
        raise HTTPException(502, f"streetpays create exception: {e!s}")

    if r.status_code >= 400:
        _record("pix_create_error", {"offer": body.offer}, status=r.status_code, body=r.text[:400])
        log.error("streetpays create %s: %s", r.status_code, r.text[:300])
        raise HTTPException(502, f"streetpays create failed: {r.status_code}")

    tx = r.json()
    _record("pix_created", {"offer": body.offer}, tx_id=tx.get("id"), amount=amount)
    return {"transaction_id": tx.get("id"),
            "qrcode": (tx.get("data") or {}).get("copypaste", ""),
            "amount_centavos": amount}


@app.get("/api/status/{tx_id}")
async def status(tx_id: str):
    try:
        async with httpx.AsyncClient(timeout=20) as c:
            r = await c.get(f"{GATEWAY_API}/v1/payment/{tx_id}", headers=_PAY_HEADERS)
    except Exception as e:
        raise HTTPException(502, f"streetpays status exception: {e!s}")
    if r.status_code >= 400:
        raise HTTPException(502, f"streetpays status failed: {r.status_code}")
    tx = r.json()
    st = str(tx.get("status", "")).upper()
    return {"status": st, "paid": st in ("PAID", "APPROVED")}


# webhook aceito mas é NO-OP (standalone não dispara postback). Existe só p/ a
# StreetPays ter um notificationUrl válido (responde 200 e registra).
@app.post("/webhook")
async def webhook_noop(payload: dict | None = None):
    _record("webhook_noop", payload or {})
    return {"ok": True, "tracking": "disabled"}
