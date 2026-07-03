"""
Kirvano -> UTMify bridge.

Recebe POST JSON da Kirvano (webhook "Compra Aprovada") e envia o pedido pra
API de pedidos custom da UTMify (POST https://api.utmify.com.br/api-credentials/orders).

Atribuicao: SEM rtkcid/clickid (esse fluxo nao usa RedTrack). A presell/frontyt
passam os utm_* padrao (utm_source/campaign/medium/content/term) + src/sck na
URL do checkout Kirvano; a Kirvano preserva esses params em `marketing.*` no
webhook. O bridge só repassa `marketing.*` -> `trackingParameters` da UTMify.

POST /postback         <- Kirvano cola aqui
GET  /healthz          <- liveness
GET  /                 <- info
GET  /debug/stats      <- contadores (received/forwarded/attributed/no_utm/skipped)
GET  /debug/recent?n=  <- ultimos N webhooks recebidos (payload + desfecho)

Env:
  UTMIFY_API_TOKEN      - obrigatorio (Integrações -> Webhooks -> Credenciais de API na UTMify)
  UTMIFY_ORDERS_URL     - default https://api.utmify.com.br/api-credentials/orders
  KIRVANO_SECRET        - opcional; se setado, exige header X-Kirvano-Token = esse valor
  PLATFORM_NAME         - default "Kirvano" (campo `platform` do pedido UTMify)
  LOG_LEVEL             - default INFO
"""
from __future__ import annotations

import logging
import os
import re
import time
from collections import deque
from datetime import datetime, timezone
from typing import Any

import httpx
from fastapi import FastAPI, Header, HTTPException, Request
from fastapi.responses import JSONResponse


logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("bridge")

KIRVANO_SECRET   = os.environ.get("KIRVANO_SECRET", "").strip() or None
UTMIFY_API_TOKEN = os.environ.get("UTMIFY_API_TOKEN", "").strip()
UTMIFY_ORDERS_URL = os.environ.get("UTMIFY_ORDERS_URL", "https://api.utmify.com.br/api-credentials/orders").rstrip("/")
PLATFORM_NAME    = os.environ.get("PLATFORM_NAME", "Kirvano").strip()

app = FastAPI(title="Kirvano -> UTMify Bridge", version="1.0.0")

# --- observabilidade em memoria (zera no restart; suficiente pra QA ao vivo) ---
RECENT: deque = deque(maxlen=80)
STATS = {
    "received": 0,       # POSTs recebidos no /postback
    "skipped_event": 0,  # evento != aprovado
    "skipped_dedup": 0,  # order_id já processado (retry/duplicate da Kirvano)
    "forwarded": 0,       # pedido enviado pra UTMify
    "attributed": 0,      # UTMify respondeu 2xx
    "utmify_error": 0,    # UTMify respondeu != 2xx ou exception
}

# Deduplicação de webhooks: guarda order_ids já processados por DEDUP_TTL segundos.
# Mesmo problema/solução do kirvano-bridge original (Kirvano reenvia em timeout).
_seen_orders: dict[str, float] = {}
DEDUP_TTL = 86400  # 24h


def _now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def _now_utmify() -> str:
    """Formato exigido pela UTMify: 'YYYY-MM-DD HH:MM:SS'."""
    return datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M:%S")


def _record(outcome: str, payload: Any, **extra) -> None:
    import json as _json
    try:
        raw = _json.dumps(payload)[:1400]
    except Exception:
        raw = str(payload)[:1400]
    RECENT.appendleft({"ts": _now_iso(), "outcome": outcome, "payload": raw, **extra})


def _walk_find(data: Any, key: str) -> Any:
    """Busca recursiva por uma chave em dict/list, retorna primeiro valor encontrado."""
    if isinstance(data, dict):
        if key in data and data[key] not in (None, "", []):
            return data[key]
        for v in data.values():
            found = _walk_find(v, key)
            if found is not None:
                return found
    elif isinstance(data, list):
        for item in data:
            found = _walk_find(item, key)
            if found is not None:
                return found
    return None


def _s(v: Any) -> str | None:
    if v is None:
        return None
    v = str(v).strip()
    return v or None


def _parse_brl(s: Any) -> float | None:
    """Parseia valor monetario BR: 'R$ 197,00' / '1.234,56' -> float em reais."""
    if s is None:
        return None
    if isinstance(s, (int, float)):
        return float(s)
    t = re.sub(r"[^\d,.-]", "", str(s))
    if not t:
        return None
    if "," in t:
        t = t.replace(".", "").replace(",", ".")
    try:
        return float(t)
    except ValueError:
        return None


def _extract_amount_cents(payload: dict) -> int:
    """Mesma lógica canônica do kirvano-bridge original — NUNCA usar busca
    recursiva (bloco `fiscal` infla o valor)."""
    v = _parse_brl(payload.get("total_price"))
    if v is not None:
        return round(v * 100)
    tx = payload.get("transaction")
    if isinstance(tx, dict):
        tot = (tx.get("values") or {}).get("total")
        if isinstance(tot, (int, float)):
            return int(tot)
    for prod in (payload.get("products") or []):
        if isinstance(prod, dict) and not prod.get("is_order_bump"):
            pv = _parse_brl(prod.get("price"))
            if pv is not None:
                return round(pv * 100)
    return 0


def _extract_tracking(payload: dict) -> dict:
    """`marketing.*` da Kirvano (preenchido a partir dos ?utm_*&src=&sck= na URL
    de checkout) -> trackingParameters da UTMify."""
    marketing = payload.get("marketing") if isinstance(payload.get("marketing"), dict) else {}
    def pick(*keys: str) -> str | None:
        for k in keys:
            v = marketing.get(k)
            if v not in (None, ""):
                return _s(v)
            v = _walk_find(payload, k)
            if v not in (None, ""):
                return _s(v)
        return None
    return {
        "src": pick("src"),
        "sck": pick("sck"),
        "utm_source": pick("utm_source"),
        "utm_campaign": pick("utm_campaign"),
        "utm_medium": pick("utm_medium"),
        "utm_content": pick("utm_content"),
        "utm_term": pick("utm_term"),
    }


def _extract_customer(payload: dict) -> dict:
    customer = payload.get("customer") if isinstance(payload.get("customer"), dict) else {}
    return {
        "name": _s(customer.get("name")) or "Cliente",
        "email": _s(customer.get("email")) or "sem-email@example.com",
        "phone": _s(customer.get("phone") or customer.get("phone_number")),
        "document": _s(customer.get("document") or customer.get("cpf")),
        "country": _s(customer.get("country")) or "BR",
        # obrigatorio pra UTMify (rejeita null apesar da doc listar como opcional)
        "ip": _s(customer.get("ip") or _walk_find(payload, "ip_address")) or "0.0.0.0",
    }


def _extract_products(payload: dict, total_cents: int) -> list[dict]:
    raw_products = payload.get("products") or []
    products = []
    for prod in raw_products:
        if not isinstance(prod, dict):
            continue
        price = _parse_brl(prod.get("price"))
        products.append({
            "id": _s(prod.get("id") or prod.get("offer_id")) or "produto",
            "name": _s(prod.get("name") or prod.get("title")) or "Produto",
            "planId": None,
            "planName": None,
            "quantity": int(prod.get("quantity") or 1),
            "priceInCents": round(price * 100) if price is not None else total_cents,
        })
    if not products:
        products = [{
            "id": _s(payload.get("offer_id")) or "produto",
            "name": _s(payload.get("product_name")) or "Produto",
            "planId": None,
            "planName": None,
            "quantity": 1,
            "priceInCents": total_cents,
        }]
    return products


@app.get("/")
def root():
    return {
        "service": "kirvano-utmify-bridge",
        "version": "1.0.0",
        "secret_required": KIRVANO_SECRET is not None,
        "token_configured": bool(UTMIFY_API_TOKEN),
        "utmify_orders_url": UTMIFY_ORDERS_URL,
        "amount_source": "total_price (BRL) — NAO usa bloco fiscal",
        "tracking_source": "marketing.* (utm_source/campaign/medium/content/term/src/sck)",
    }


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
                   x_kirvano_token: str | None = Header(default=None)):
    STATS["received"] += 1
    ctype = request.headers.get("content-type", "")

    if KIRVANO_SECRET and x_kirvano_token != KIRVANO_SECRET:
        log.warning("rejected: bad/missing X-Kirvano-Token header")
        _record("bad_token", {}, content_type=ctype)
        raise HTTPException(status_code=401, detail="invalid token")

    raw_body = await request.body()
    import json as _json
    try:
        payload = _json.loads(raw_body)
    except Exception:
        try:
            form = await request.form()
            payload = dict(form)
        except Exception:
            payload = {"_raw": raw_body.decode("utf-8", "replace")[:1500]}
    if not isinstance(payload, dict):
        payload = {"_value": payload}

    log.info("payload received (ct=%s): %s", ctype, str(payload)[:800])

    event = (payload.get("event") or payload.get("type") or "").lower()
    is_approved = any(k in event for k in ("approved", "aprovad", "purchase_paid", "sale_paid"))
    if not is_approved:
        STATS["skipped_event"] += 1
        _record("skipped_event", payload, content_type=ctype, event=event)
        log.info("ignored event=%r (only approved purchases trigger postback)", event)
        return {"ok": True, "skipped": "event_not_approved", "event": event}

    now = time.time()
    global _seen_orders
    _seen_orders = {k: v for k, v in _seen_orders.items() if now - v < DEDUP_TTL}
    order_id = (
        payload.get("order_id")
        or payload.get("transaction_id")
        or (payload.get("transaction") or {}).get("id")
        or payload.get("id")
    )
    if order_id:
        if order_id in _seen_orders:
            STATS["skipped_dedup"] += 1
            _record("skipped_dedup", payload, content_type=ctype, event=event, order_id=order_id)
            log.info("duplicate order_id=%s skipped (kirvano retry?)", order_id)
            return {"ok": True, "skipped": "duplicate_order", "order_id": order_id}
        _seen_orders[order_id] = now
    else:
        order_id = f"kirvano-{int(now * 1000)}"

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
        log.error("UTMIFY_API_TOKEN não configurado — pedido NÃO enviado")
        raise HTTPException(status_code=500, detail="UTMIFY_API_TOKEN not configured")

    try:
        async with httpx.AsyncClient(timeout=15) as c:
            r = await c.post(
                UTMIFY_ORDERS_URL,
                headers={"x-api-token": UTMIFY_API_TOKEN, "Content-Type": "application/json"},
                json=order,
            )
        STATS["forwarded"] += 1
        STATS["attributed" if r.status_code < 300 else "utmify_error"] += 1
        _record("forwarded", payload, content_type=ctype, event=event,
                order_id=order_id, amount_cents=amount_cents,
                utmify_status=r.status_code, utmify_body=r.text[:300])
        log.info("utmify order: order_id=%s amount_cents=%s -> %s",
                 order_id, amount_cents, r.status_code)
        return {"ok": True, "order_id": order_id, "amount_cents": amount_cents,
                "utmify_status": r.status_code, "utmify_body": r.text[:300]}
    except Exception as e:
        STATS["utmify_error"] += 1
        _record("utmify_exception", payload, content_type=ctype, error=str(e))
        log.error("utmify order failed: %s", e)
        raise HTTPException(status_code=502, detail=f"utmify order failed: {e!s}")
