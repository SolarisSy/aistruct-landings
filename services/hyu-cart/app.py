"""
HYU (hyuoficial.com) -> Paggins SDK bridge: carrinho real multi-item.

O site e estatico (nginx); a sk_live da Paggins nao pode ir pro browser.
Este servico recebe o carrinho, valida contra o catalogo SERVER-SIDE
(preco nunca vem do cliente) e cria a checkout session na Paggins
(POST /v1/sdk/checkout-sessions, header Idempotency-Key obrigatorio),
devolvendo a checkoutUrl hospedada.

POST /checkout         <- site: {items:[{flavor,tier,qty}], meta:{utm_*...}}
                          -> {checkoutUrl, sessionId, totalAmount}
GET  /healthz          <- liveness
GET  /                 <- info
GET  /debug/stats      <- contadores
GET  /debug/recent?n=  <- ultimas N sessoes criadas (sem dados sensiveis)

Env:
  PAGGINS_API_KEY  - obrigatorio (sk_live_...) — setar no Easypanel, NUNCA no git
  PAGGINS_API_URL  - default https://api.paggins.com
  SITE_BASE        - default https://hyuoficial.com (success/cancel/imagens)
  LOG_LEVEL        - default INFO
"""
from __future__ import annotations

import logging
import os
import uuid
from collections import deque
from datetime import datetime, timezone
from typing import Any

import httpx
from fastapi import FastAPI, HTTPException, Request
from fastapi.middleware.cors import CORSMiddleware

logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("hyu-cart")

PAGGINS_API_KEY = os.environ.get("PAGGINS_API_KEY", "").strip()
PAGGINS_API_URL = os.environ.get("PAGGINS_API_URL", "https://api.paggins.com").rstrip("/")
SITE_BASE = os.environ.get("SITE_BASE", "https://hyuoficial.com").rstrip("/")

app = FastAPI(title="HYU cart -> Paggins bridge", version="1.0.0")
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "https://hyuoficial.com",
        "https://www.hyuoficial.com",
        "https://hyuoficial.tiectu.easypanel.host",
        "http://localhost:4321", "http://localhost:4322", "http://localhost:4323",
        "http://127.0.0.1:4321", "http://127.0.0.1:4322", "http://127.0.0.1:4323",
    ],
    allow_methods=["POST", "GET", "OPTIONS"],
    allow_headers=["Content-Type"],
)

# ── catalogo server-side (fonte da verdade de preco/nome) ───────────────
# productId = UUID do produto correspondente na Paggins (mesmo do checkout fixo).
FLAVORS: dict[str, dict[str, str]] = {
    "hot-lemon":       {"name": "HYU Soda Protein Hot Lemon", "sku": "HOTLEMON",
                        "desc": "15g de proteína · 3g de fibras · zero açúcar · 269ml"},
    "maca-verde":      {"name": "HYU Soda Protein Maçã Verde", "sku": "MACAVERDE",
                        "desc": "15g de proteína · 3g de fibras · zero açúcar · 269ml"},
    "pessego-morango": {"name": "HYU Soda Protein Pêssego com Morango", "sku": "PESSEGOMORANGO",
                        "desc": "15g de proteína · 3g de fibras · zero açúcar · 269ml"},
    "tropical":        {"name": "HYU Energy Protein Tropical", "sku": "TROPICAL",
                        "desc": "15g de proteína · 85mg de cafeína natural · zero açúcar · 269ml"},
    "maca-vermelha":   {"name": "HYU Energy Protein Maçã Vermelha", "sku": "MACAVERMELHA",
                        "desc": "15g de proteína · 85mg de cafeína natural · zero açúcar · 269ml"},
}
TIERS: dict[str, dict[str, Any]] = {
    "kit6":  {"label": "Kit 6 (6 latas)",   "cents": 6690,  "sku": "K6"},
    "kit12": {"label": "Kit 12 (12 latas)", "cents": 11990, "sku": "K12"},
}
PRODUCT_IDS: dict[tuple[str, str], str] = {
    ("hot-lemon", "kit6"):        "8534bbc5-5a43-46e2-9551-89d26d84c22f",
    ("hot-lemon", "kit12"):       "95e0f88f-6fca-4a19-89a1-9583c93e09df",
    ("maca-verde", "kit6"):       "a585f927-16c4-47ae-a13b-980b3c55601e",
    ("maca-verde", "kit12"):      "909baad1-bd89-4e28-ab14-4f3793fbabea",
    ("pessego-morango", "kit6"):  "1cf46530-8d6a-4feb-a1c9-cacfd0b93481",
    ("pessego-morango", "kit12"): "7ff35585-7f89-4c11-b67a-b564e601a820",
    ("tropical", "kit6"):         "e3fc8cae-ed54-4b22-8d67-2e914eadb14e",
    ("tropical", "kit12"):        "ee27af7e-cc0f-4212-b162-692776fd7f09",
    ("maca-vermelha", "kit6"):    "6595de07-23c2-44b7-bf99-d2f00919be96",
    ("maca-vermelha", "kit12"):   "d8d1b025-bb9f-4b80-92c0-76845a01453c",
}

MAX_LINES = 10      # combinacoes distintas por pedido
MAX_QTY = 20        # kits por linha
META_KEYS = ("utm_source", "utm_medium", "utm_campaign", "utm_term",
             "utm_content", "gclid", "fbclid", "ref", "src")

RECENT: deque = deque(maxlen=80)
STATS = {"received": 0, "created": 0, "bad_request": 0, "paggins_error": 0}


def _build_items(raw_items: Any) -> list[dict[str, Any]]:
    """Valida o carrinho e monta os items da Paggins com preco do catalogo."""
    if not isinstance(raw_items, list) or not raw_items:
        raise HTTPException(400, "items vazio")
    merged: dict[tuple[str, str], int] = {}
    for it in raw_items:
        if not isinstance(it, dict):
            raise HTTPException(400, "item invalido")
        flavor, tier = it.get("flavor"), it.get("tier")
        if flavor not in FLAVORS or tier not in TIERS:
            raise HTTPException(400, f"combinacao desconhecida: {flavor}/{tier}")
        try:
            qty = int(it.get("qty", 1))
        except (TypeError, ValueError):
            raise HTTPException(400, "qty invalida")
        if not 1 <= qty <= MAX_QTY:
            raise HTTPException(400, f"qty fora de 1..{MAX_QTY}")
        merged[(flavor, tier)] = min(merged.get((flavor, tier), 0) + qty, MAX_QTY)
    if len(merged) > MAX_LINES:
        raise HTTPException(400, f"mais de {MAX_LINES} combinacoes")
    items = []
    for (flavor, tier), qty in merged.items():
        f, t = FLAVORS[flavor], TIERS[tier]
        items.append({
            "productId": PRODUCT_IDS[(flavor, tier)],
            "name": f"{f['name']} — {t['label']}",
            "type": "physical",
            "unitAmount": t["cents"],
            "quantity": qty,
            "sku": f"HYU-{f['sku']}-{t['sku']}",
            "description": f["desc"],
            "imageUrl": f"{SITE_BASE}/img/kits/{flavor}.webp",
        })
    return items


def _build_metadata(raw_meta: Any) -> dict[str, str]:
    if not isinstance(raw_meta, dict):
        return {}
    return {k: str(raw_meta[k])[:200] for k in META_KEYS if raw_meta.get(k)}


@app.post("/checkout")
async def create_checkout(request: Request):
    STATS["received"] += 1
    try:
        payload = await request.json()
    except Exception:
        STATS["bad_request"] += 1
        raise HTTPException(400, "JSON invalido")

    try:
        items = _build_items(payload.get("items"))
    except HTTPException:
        STATS["bad_request"] += 1
        raise

    order_id = f"hyu-{uuid.uuid4().hex[:12]}"
    body = {
        "currency": "BRL",
        "items": items,
        "requireShippingInfo": True,
        "successUrl": f"{SITE_BASE}/obrigado/?session_id={{CHECKOUT_SESSION_ID}}",
        "cancelUrl": f"{SITE_BASE}/?checkout=cancelado",
        "externalOrderId": order_id,
    }
    metadata = _build_metadata(payload.get("meta"))
    if metadata:
        body["metadata"] = metadata

    headers = {
        "Authorization": f"Bearer {PAGGINS_API_KEY}",
        "Content-Type": "application/json",
        "Idempotency-Key": str(uuid.uuid4()),
    }
    # 1 retry em erro de rede/5xx — mesma Idempotency-Key garante que nao duplica
    last_err = "?"
    for attempt in (1, 2):
        try:
            async with httpx.AsyncClient(timeout=30) as c:
                r = await c.post(f"{PAGGINS_API_URL}/v1/sdk/checkout-sessions",
                                 headers=headers, json=body)
            if r.status_code in (200, 201):
                sess = r.json()
                total = sum(i["unitAmount"] * i["quantity"] for i in items)
                STATS["created"] += 1
                RECENT.append({
                    "ts": datetime.now(timezone.utc).isoformat(),
                    "order_id": order_id, "session_id": sess.get("id"),
                    "lines": [(i["sku"], i["quantity"]) for i in items],
                    "total": total, "meta": list(metadata),
                })
                log.info("checkout %s -> %s total=%s", order_id, sess.get("id"), total)
                return {"checkoutUrl": sess.get("checkoutUrl"),
                        "sessionId": sess.get("id"), "totalAmount": total}
            if r.status_code < 500:
                STATS["paggins_error"] += 1
                log.error("paggins %s: %s", r.status_code, r.text[:300])
                raise HTTPException(502, "checkout indisponivel")
            last_err = f"HTTP {r.status_code}"
        except httpx.HTTPError as e:
            last_err = str(e)[:120]
        log.warning("tentativa %d falhou (%s)", attempt, last_err)
    STATS["paggins_error"] += 1
    raise HTTPException(502, "checkout indisponivel")


@app.get("/healthz")
async def healthz():
    return {"ok": True, "has_key": bool(PAGGINS_API_KEY)}


@app.get("/")
async def root():
    return {"service": "hyu-cart", "version": app.version,
            "flavors": sorted(FLAVORS), "tiers": sorted(TIERS), "stats": STATS}


@app.get("/debug/stats")
async def debug_stats():
    return STATS


@app.get("/debug/recent")
async def debug_recent(n: int = 20):
    return list(RECENT)[-max(1, min(n, 80)):]
