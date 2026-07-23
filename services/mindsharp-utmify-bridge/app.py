"""
MindSharp (rede/afiliado externo) -> UTMify bridge.

Contexto: a oferta `dailyclarity.sbs` (perfil sip) manda o trafego qualificado
direto pro funil externo `trymindsharp.com`. Nao temos checkout proprio, entao a
venda so chega ate nos por POSTBACK da rede. Este servico recebe esse postback
(GET *ou* POST, JSON ou form) e reposta o pedido na API de pedidos da UTMify
(`POST https://api.utmify.com.br/api-credentials/orders`, header `x-api-token`).

Por que GET tambem: redes de afiliado quase sempre disparam postback S2S como GET
com query string (`?subid={}&payout={}&status={}`), diferente de gateway BR.

Atribuicao: a bridge page `/r.html` da landing carimba os utm_* + `gclid` na URL
que vai pro funil externo; a rede devolve o que conseguir preservar (subid/s1/
aff_sub/clickid). O bridge normaliza tudo pra `trackingParameters` da UTMify.

Rotas:
  GET|POST /postback   <- a rede cola aqui (aceita ?token= ou header X-Bridge-Token)
  GET  /healthz        <- liveness
  GET  /               <- info + mapa de parametros aceitos
  GET  /debug/stats    <- contadores
  GET  /debug/recent   <- ultimos N postbacks (payload + desfecho)
  POST /debug/simulate <- injeta um postback sintetico (isTest=true) pra provar o elo

Env:
  UTMIFY_API_TOKEN   - obrigatorio (UTMify -> Webhooks -> Credencial de API)
  UTMIFY_ORDERS_URL  - default https://api.utmify.com.br/api-credentials/orders
  BRIDGE_TOKEN       - opcional; se setado, exige ?token= ou X-Bridge-Token
  PLATFORM_NAME      - default "MindSharp"
  CURRENCY           - default USD (oferta US)
  DEFAULT_AMOUNT     - fallback em centavos quando a rede nao manda valor (default 0)
  LOG_LEVEL          - default INFO
"""
from __future__ import annotations

import json as _json
import logging
import os
import re
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
UTMIFY_ORDERS_URL = os.environ.get(
    "UTMIFY_ORDERS_URL", "https://api.utmify.com.br/api-credentials/orders").rstrip("/")
BRIDGE_TOKEN = os.environ.get("BRIDGE_TOKEN", "").strip() or None
PLATFORM_NAME = os.environ.get("PLATFORM_NAME", "MindSharp").strip()
CURRENCY = os.environ.get("CURRENCY", "USD").strip().upper()
DEFAULT_AMOUNT = int(os.environ.get("DEFAULT_AMOUNT", "0") or 0)

app = FastAPI(title="MindSharp -> UTMify Bridge", version="1.0.0")

RECENT: deque = deque(maxlen=80)
STATS = {"received": 0, "skipped_status": 0, "skipped_dedup": 0,
         "forwarded": 0, "attributed": 0, "utmify_error": 0, "bad_token": 0}

_seen: dict[str, float] = {}
DEDUP_TTL = 86400

# aliases aceitos por campo (redes divergem bastante na nomenclatura)
A_ORDER = ("order_id", "orderId", "transaction_id", "txn_id", "conversion_id", "id", "oid")
A_AMOUNT = ("amount", "payout", "value", "revenue", "sale_amount", "total", "price", "sum")
A_STATUS = ("status", "event", "type", "state", "conversion_status")
A_CLICK = ("subid", "sub_id", "s1", "aff_sub", "aff_sub1", "clickid", "click_id",
           "gclid", "cid", "tid")
A_EMAIL = ("email", "customer_email", "buyer_email")
A_NAME = ("name", "customer_name", "buyer_name", "first_name")
A_IP = ("ip", "customer_ip", "ip_address", "user_ip")
A_PRODUCT = ("product", "product_name", "offer", "offer_name", "campaign_name")
PAID_WORDS = ("approved", "aprovad", "paid", "sale", "confirmed", "complete",
              "conversion", "purchase", "success")


def _now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def _now_utmify() -> str:
    return datetime.now(timezone.utc).strftime("%Y-%m-%d %H:%M:%S")


def _s(v: Any) -> str | None:
    if v is None:
        return None
    v = str(v).strip()
    return v or None


def _pick(d: dict, keys: tuple[str, ...]) -> Any:
    low = {str(k).lower(): v for k, v in d.items()}
    for k in keys:
        v = low.get(k.lower())
        if v not in (None, "", []):
            return v
    return None


def _amount_cents(raw: Any) -> int:
    """Aceita '19.90', '19,90', 1990 (centavos ja) nao — sempre trata como unidade
    monetaria, exceto inteiro >= 1000 sem separador, que continua sendo unidade."""
    if raw is None:
        return DEFAULT_AMOUNT
    if isinstance(raw, (int, float)):
        return round(float(raw) * 100)
    t = re.sub(r"[^\d,.-]", "", str(raw))
    if not t:
        return DEFAULT_AMOUNT
    if "," in t and "." in t:
        t = t.replace(".", "").replace(",", ".")
    elif "," in t:
        t = t.replace(",", ".")
    try:
        return round(float(t) * 100)
    except ValueError:
        return DEFAULT_AMOUNT


def _record(outcome: str, payload: Any, **extra) -> None:
    try:
        raw = _json.dumps(payload)[:1400]
    except Exception:
        raw = str(payload)[:1400]
    RECENT.appendleft({"ts": _now_iso(), "outcome": outcome, "payload": raw, **extra})


def _tracking(d: dict) -> dict:
    click = _pick(d, A_CLICK)
    return {
        "src": _s(_pick(d, ("src",))) or _s(click),
        "sck": _s(_pick(d, ("sck",))),
        "utm_source": _s(_pick(d, ("utm_source",))) or "google",
        "utm_campaign": _s(_pick(d, ("utm_campaign", "campaignid", "campaign_id"))),
        "utm_medium": _s(_pick(d, ("utm_medium", "adgroupid"))),
        "utm_content": _s(_pick(d, ("utm_content", "creative"))),
        "utm_term": _s(_pick(d, ("utm_term", "keyword"))),
    }


async def _forward(d: dict, is_test: bool = False) -> dict:
    status_raw = str(_pick(d, A_STATUS) or "").lower()
    # postback de rede as vezes vem sem status (so dispara em venda) -> trata como pago
    if status_raw and not any(w in status_raw for w in PAID_WORDS):
        STATS["skipped_status"] += 1
        _record("skipped_status", d, status=status_raw)
        return {"ok": True, "skipped": "status_not_paid", "status": status_raw}

    now = time.time()
    global _seen
    _seen = {k: v for k, v in _seen.items() if now - v < DEDUP_TTL}
    order_id = _s(_pick(d, A_ORDER)) or f"mindsharp-{int(now * 1000)}"
    if order_id in _seen:
        STATS["skipped_dedup"] += 1
        _record("skipped_dedup", d, order_id=order_id)
        return {"ok": True, "skipped": "duplicate_order", "orderId": order_id}
    _seen[order_id] = now

    cents = _amount_cents(_pick(d, A_AMOUNT))
    order = {
        "orderId": order_id,
        "platform": PLATFORM_NAME,
        "paymentMethod": "credit_card",
        "status": "paid",
        "createdAt": _now_utmify(),
        "approvedDate": _now_utmify(),
        "refundedAt": None,
        "customer": {
            "name": _s(_pick(d, A_NAME)) or "Customer",
            "email": _s(_pick(d, A_EMAIL)) or "no-email@example.com",
            "phone": None,
            "document": None,
            "country": _s(_pick(d, ("country",))) or "US",
            # UTMify rejeita ip=null apesar da doc dizer opcional (licao utmify.md)
            "ip": _s(_pick(d, A_IP)) or "0.0.0.0",
        },
        "products": [{
            "id": _s(_pick(d, ("offer_id", "product_id"))) or "mindsharp",
            "name": _s(_pick(d, A_PRODUCT)) or "MindSharp",
            "planId": None, "planName": None, "quantity": 1,
            "priceInCents": cents,
        }],
        "trackingParameters": _tracking(d),
        "commission": {
            "totalPriceInCents": cents,
            "gatewayFeeInCents": 0,
            "userCommissionInCents": cents,
            "currency": CURRENCY,
        },
        "isTest": bool(is_test),
    }

    if not UTMIFY_API_TOKEN:
        _record("no_token", d, order_id=order_id)
        raise HTTPException(status_code=500, detail="UTMIFY_API_TOKEN not configured")

    try:
        async with httpx.AsyncClient(timeout=20) as c:
            r = await c.post(UTMIFY_ORDERS_URL,
                             headers={"x-api-token": UTMIFY_API_TOKEN,
                                      "Content-Type": "application/json"},
                             json=order)
        STATS["forwarded"] += 1
        STATS["attributed" if r.status_code < 300 else "utmify_error"] += 1
        _record("forwarded", d, order_id=order_id, utmify_status=r.status_code,
                utmify_body=r.text[:300], amount_cents=cents)
        log.info("order %s -> utmify %s %s", order_id, r.status_code, r.text[:200])
        return {"ok": r.status_code < 300, "orderId": order_id,
                "utmify_status": r.status_code, "utmify_response": r.text[:300]}
    except Exception as e:  # noqa: BLE001
        STATS["utmify_error"] += 1
        _record("utmify_exception", d, order_id=order_id, error=str(e)[:200])
        log.exception("utmify post failed")
        raise HTTPException(status_code=502, detail=f"utmify error: {e}") from e


def _check_token(qs_token: str | None, hdr_token: str | None) -> None:
    if BRIDGE_TOKEN and qs_token != BRIDGE_TOKEN and hdr_token != BRIDGE_TOKEN:
        STATS["bad_token"] += 1
        _record("bad_token", {})
        raise HTTPException(status_code=401, detail="invalid token")


@app.get("/")
def root():
    return {
        "service": "mindsharp-utmify-bridge",
        "version": "1.0.0",
        "offer": "dailyclarity.sbs -> trymindsharp.com (money externa)",
        "token_required": BRIDGE_TOKEN is not None,
        "utmify_token_configured": bool(UTMIFY_API_TOKEN),
        "utmify_orders_url": UTMIFY_ORDERS_URL,
        "currency": CURRENCY,
        "accepts": {"methods": ["GET", "POST"], "content": ["query", "json", "form"]},
        "aliases": {"order": A_ORDER, "amount": A_AMOUNT, "status": A_STATUS,
                    "click": A_CLICK},
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


@app.get("/postback")
async def postback_get(request: Request, token: str | None = None,
                       x_bridge_token: str | None = Header(default=None)):
    STATS["received"] += 1
    _check_token(token, x_bridge_token)
    d = dict(request.query_params)
    d.pop("token", None)
    log.info("GET postback: %s", str(d)[:600])
    return await _forward(d)


@app.post("/postback")
async def postback_post(request: Request, token: str | None = None,
                        x_bridge_token: str | None = Header(default=None)):
    STATS["received"] += 1
    _check_token(token, x_bridge_token)
    raw = await request.body()
    try:
        d = _json.loads(raw)
    except Exception:
        try:
            d = dict(await request.form())
        except Exception:
            d = {}
    if not isinstance(d, dict):
        d = {"_value": d}
    d = {**dict(request.query_params), **d}
    d.pop("token", None)
    log.info("POST postback: %s", str(d)[:600])
    return await _forward(d)


@app.post("/debug/simulate")
async def simulate(request: Request):
    """Prova o elo bridge->UTMify sem depender da rede. Marca isTest=true."""
    try:
        d = _json.loads(await request.body())
    except Exception:
        d = {}
    d = {"order_id": f"sim-{int(time.time() * 1000)}", "amount": "1.00",
         "status": "approved", "subid": "simulated", **(d if isinstance(d, dict) else {})}
    return await _forward(d, is_test=True)
