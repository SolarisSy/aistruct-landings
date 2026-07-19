"""
Paggins -> UTMify bridge.

Recebe o webhook do Paggins (checkout.session.completed / payment.succeeded) e
envia o pedido pra API de pedidos custom da UTMify
(POST https://api.utmify.com.br/api-credentials/orders).

Atribuição: SEM RedTrack. A presell/frontyt passam utm_*/src/gclid na URL do
checkout Paggins; ESTE bridge tenta achar esses params em QUALQUER lugar do
payload (_walk_find) e os manda em trackingParameters da UTMify.
⚠️ Se o Paggins NÃO repassar os params no webhook (link fixo), o tracking vem
vazio — o /debug/recent mostra o payload real pra decidir migrar pra SDK+metadata.

POST /postback   <- Paggins cola aqui (Integrações -> Webhooks)
GET  /healthz    <- liveness
GET  /           <- info
GET  /debug/stats
GET  /debug/recent?n=

Env:
  UTMIFY_API_TOKEN        - obrigatório
  UTMIFY_ORDERS_URL       - default https://api.utmify.com.br/api-credentials/orders
  PAGGINS_WEBHOOK_SECRET  - opcional; se setado, verifica x-paggins-signature (HMAC-SHA256, tolerante)
  PLATFORM_NAME           - default "Paggins"
  LOG_LEVEL               - default INFO
"""
from __future__ import annotations

import hashlib
import hmac
import logging
import os
import time
from collections import deque
from datetime import datetime, timezone
from typing import Any

import httpx
from fastapi import FastAPI, Header, HTTPException, Request

logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("bridge")

UTMIFY_API_TOKEN = os.environ.get("UTMIFY_API_TOKEN", "").strip()
UTMIFY_ORDERS_URL = os.environ.get("UTMIFY_ORDERS_URL", "https://api.utmify.com.br/api-credentials/orders").rstrip("/")
PAGGINS_WEBHOOK_SECRET = os.environ.get("PAGGINS_WEBHOOK_SECRET", "").strip() or None
PLATFORM_NAME = os.environ.get("PLATFORM_NAME", "Paggins").strip()

app = FastAPI(title="Paggins -> UTMify Bridge", version="1.0.0")

RECENT: deque = deque(maxlen=80)
STATS = {"received": 0, "skipped_event": 0, "skipped_dedup": 0, "bad_sig": 0,
         "forwarded": 0, "attributed": 0, "utmify_error": 0}

_seen: dict[str, float] = {}
DEDUP_TTL = 86400


def _now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def _now_utmify() -> str:
    return datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M:%S")


def _record(outcome: str, payload: Any, **extra) -> None:
    import json as _json
    try:
        raw = _json.dumps(payload)[:1600]
    except Exception:
        raw = str(payload)[:1600]
    RECENT.appendleft({"ts": _now_iso(), "outcome": outcome, "payload": raw, **extra})


def _walk_find(data: Any, key: str) -> Any:
    if isinstance(data, dict):
        if key in data and data[key] not in (None, "", []):
            return data[key]
        for v in data.values():
            f = _walk_find(v, key)
            if f is not None:
                return f
    elif isinstance(data, list):
        for item in data:
            f = _walk_find(item, key)
            if f is not None:
                return f
    return None


def _s(v: Any) -> str | None:
    if v is None:
        return None
    v = str(v).strip()
    return v or None


def _extract_amount_cents(payload: dict) -> int:
    """Paggins já manda em CENTAVOS. Prioriza payment.amount / totalAmount / amount."""
    for path in (("payment", "amount"), ("data", "payment", "amount")):
        node = payload
        for k in path:
            node = node.get(k) if isinstance(node, dict) else None
        if isinstance(node, (int, float)) and node > 0:
            return int(node)
    for k in ("totalAmount", "total_amount", "amount"):
        v = _walk_find(payload, k)
        if isinstance(v, (int, float)) and v > 0:
            return int(v)
    return 0


def _extract_tracking(payload: dict) -> dict:
    """Procura utm_*/src/gclid em qualquer lugar (metadata, query, marketing…)."""
    def pick(*keys: str) -> str | None:
        for k in keys:
            v = _walk_find(payload, k)
            if v not in (None, ""):
                return _s(v)
        return None
    src = pick("src", "gclid")
    return {
        "src": src,
        "sck": pick("sck"),
        "utm_source": pick("utm_source", "utmSource"),
        "utm_campaign": pick("utm_campaign", "utmCampaign"),
        "utm_medium": pick("utm_medium", "utmMedium"),
        "utm_content": pick("utm_content", "utmContent"),
        "utm_term": pick("utm_term", "utmTerm"),
    }


def _extract_customer(payload: dict) -> dict:
    c = _walk_find(payload, "customer")
    c = c if isinstance(c, dict) else {}
    return {
        "name": _s(c.get("name")) or "Cliente",
        "email": _s(c.get("email")) or "sem-email@example.com",
        "phone": _s(c.get("phone") or c.get("phoneNumber")),
        "document": _s(c.get("document") or c.get("cpf")),
        "country": _s(c.get("country")) or "BR",
        "ip": _s(c.get("ip") or _walk_find(payload, "ip")) or "0.0.0.0",
    }


def _extract_products(payload: dict, total_cents: int) -> list[dict]:
    items = _walk_find(payload, "items")
    out = []
    if isinstance(items, list):
        for it in items:
            if not isinstance(it, dict):
                continue
            ua = it.get("unitAmount") or it.get("unit_amount")
            out.append({
                "id": _s(it.get("productId") or it.get("id")) or "planejejaclick",
                "name": _s(it.get("name")) or "Planejamento Financeiro Premium",
                "planId": None, "planName": None,
                "quantity": int(it.get("quantity") or 1),
                "priceInCents": int(ua) if isinstance(ua, (int, float)) else total_cents,
            })
    if not out:
        out = [{"id": "planejejaclick", "name": "Planejamento Financeiro Premium",
                "planId": None, "planName": None, "quantity": 1, "priceInCents": total_cents}]
    return out


def _verify_sig(raw: bytes, sig: str | None) -> bool:
    if not PAGGINS_WEBHOOK_SECRET:
        return True  # sem secret configurado = não exige
    if not sig:
        return False
    expected = hmac.new(PAGGINS_WEBHOOK_SECRET.encode(), raw, hashlib.sha256).hexdigest()
    got = sig.strip().lower().replace("sha256=", "")
    return hmac.compare_digest(expected, got)


@app.get("/")
def root():
    return {"service": "paggins-utmify-bridge", "version": "1.0.0",
            "token_configured": bool(UTMIFY_API_TOKEN),
            "sig_required": PAGGINS_WEBHOOK_SECRET is not None,
            "utmify_orders_url": UTMIFY_ORDERS_URL,
            "tracking_source": "_walk_find(src/gclid/utm_*) — link fixo pode não repassar; ver /debug/recent"}


@app.get("/healthz")
def health():
    return {"ok": True}


@app.get("/debug/stats")
def debug_stats():
    return {"stats": STATS, "recent_count": len(RECENT), "note": "zera no restart"}


@app.get("/debug/recent")
def debug_recent(n: int = 30):
    return {"stats": STATS, "recent": list(RECENT)[: max(1, min(n, 80))]}


@app.post("/postback")
async def postback(request: Request,
                   x_paggins_signature: str | None = Header(default=None)):
    STATS["received"] += 1
    ctype = request.headers.get("content-type", "")
    raw_body = await request.body()

    if not _verify_sig(raw_body, x_paggins_signature):
        STATS["bad_sig"] += 1
        _record("bad_sig", {}, content_type=ctype)
        log.warning("rejected: bad x-paggins-signature")
        raise HTTPException(status_code=401, detail="invalid signature")

    import json as _json
    try:
        payload = _json.loads(raw_body)
    except Exception:
        payload = {"_raw": raw_body.decode("utf-8", "replace")[:1500]}
    if not isinstance(payload, dict):
        payload = {"_value": payload}

    log.info("payload (ct=%s): %s", ctype, str(payload)[:900])

    event = (payload.get("event") or payload.get("type")
             or _walk_find(payload, "event") or _walk_find(payload, "type") or "").lower()
    is_paid = any(k in event for k in
                  ("completed", "succeeded", "paid", "fulfilled", "approved"))
    if not is_paid:
        STATS["skipped_event"] += 1
        _record("skipped_event", payload, content_type=ctype, event=event)
        log.info("ignored event=%r", event)
        return {"ok": True, "skipped": "event", "event": event}

    now = time.time()
    global _seen
    _seen = {k: v for k, v in _seen.items() if now - v < DEDUP_TTL}
    order_id = (_walk_find(payload, "sessionId") or _walk_find(payload, "id")
                or _walk_find(payload, "orderId"))
    if order_id:
        if order_id in _seen:
            STATS["skipped_dedup"] += 1
            _record("skipped_dedup", payload, content_type=ctype, event=event, order_id=order_id)
            return {"ok": True, "skipped": "duplicate", "order_id": order_id}
        _seen[order_id] = now
    else:
        order_id = f"paggins-{int(now * 1000)}"

    amount_cents = _extract_amount_cents(payload)
    tracking = _extract_tracking(payload)
    customer = _extract_customer(payload)
    products = _extract_products(payload, amount_cents)

    order = {
        "orderId": str(order_id),
        "platform": PLATFORM_NAME,
        "paymentMethod": "pix",
        "status": "paid",
        "createdAt": _now_utmify(),
        "approvedDate": _now_utmify(),
        "refundedAt": None,
        "customer": customer,
        "products": products,
        "trackingParameters": tracking,
        "commission": {
            "totalPriceInCents": amount_cents,
            "gatewayFeeInCents": 0,
            "userCommissionInCents": amount_cents,
            "currency": "BRL",
        },
        "isTest": False,
    }

    if not UTMIFY_API_TOKEN:
        _record("no_token", payload, content_type=ctype, event=event, order_id=order_id)
        raise HTTPException(status_code=500, detail="UTMIFY_API_TOKEN not configured")

    try:
        async with httpx.AsyncClient(timeout=15) as c:
            r = await c.post(UTMIFY_ORDERS_URL,
                             headers={"x-api-token": UTMIFY_API_TOKEN, "Content-Type": "application/json"},
                             json=order)
        STATS["forwarded"] += 1
        STATS["attributed" if r.status_code < 300 else "utmify_error"] += 1
        _record("forwarded", payload, content_type=ctype, event=event, order_id=order_id,
                amount_cents=amount_cents, has_tracking=bool(tracking.get("src")),
                utmify_status=r.status_code, utmify_body=r.text[:300])
        log.info("utmify order=%s amount=%s track_src=%s -> %s",
                 order_id, amount_cents, tracking.get("src"), r.status_code)
        return {"ok": True, "order_id": order_id, "amount_cents": amount_cents,
                "tracking": tracking, "utmify_status": r.status_code}
    except Exception as e:
        STATS["utmify_error"] += 1
        _record("utmify_exception", payload, content_type=ctype, error=str(e))
        raise HTTPException(status_code=502, detail=f"utmify order failed: {e!s}")
