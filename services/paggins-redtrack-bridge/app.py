"""
Paggins -> RedTrack bridge.

Recebe o webhook do Paggins (order.paid) e dispara o postback S2S do RedTrack:
    GET https://tbxgj.ttrk.io/postback?clickid=<rtkcid>&sum=<valor>&status=approved

O clickid (rtkcid) viaja no checkout Paggins como **utm_term={clickid}** — padrão
validado nesta conta (offer "Paggins - LotoApp"). Este bridge acha o utm_term em
QUALQUER lugar do payload (_walk_find) e valida que é rtkcid 24-hex antes de postar.

Por que este bridge existe (e não posta direto): Paggins manda POST/JSON, RedTrack
quer GET/querystring — igual ao kirvano-bridge. Lições herdadas:
  - VALOR = total_price (BRL), nunca busca recursiva cega (bloco fiscal infla o valor).
  - Valida rtkcid ^[0-9a-fA-F]{24}$ → ignora venda de outra fonte (webhook é da LOJA toda).
  - Filtro de evento WHITELIST (só 'paid'); PIX gerado/expirado/abandonado são skipados.
  - Dedup por order_id (TTL 24h) → Paggins retenta o webhook.
  - PRODUCT_ID_FILTER: a Bernardo Store é compartilhada — só encaminha a oferta loto.

Endpoints: POST /postback (Paggins cola aqui) · GET /healthz · GET / · /debug/stats · /debug/recent

Env:
  REDTRACK_POSTBACK   default https://tbxgj.ttrk.io/postback
  PRODUCT_ID_FILTER   opcional; só posta pedidos que referenciem este productId
  PAGGINS_WEBHOOK_SECRET  opcional; verifica x-paggins-signature (HMAC-SHA256, tolerante)
  RTKCID_FIELD        default utm_term (onde o clickid viaja no payload Paggins)
  LOG_LEVEL           default INFO
"""
from __future__ import annotations

import hashlib
import hmac
import logging
import os
import re
import time
from collections import deque
from typing import Any

import httpx
from fastapi import FastAPI, Header, HTTPException, Request

logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("bridge")

REDTRACK_POSTBACK = os.environ.get("REDTRACK_POSTBACK", "https://tbxgj.ttrk.io/postback").rstrip("/")
PRODUCT_ID_FILTER = os.environ.get("PRODUCT_ID_FILTER", "").strip() or None
PAGGINS_WEBHOOK_SECRET = os.environ.get("PAGGINS_WEBHOOK_SECRET", "").strip() or None
RTKCID_FIELD = os.environ.get("RTKCID_FIELD", "utm_term").strip()

RTKCID_RE = re.compile(r"^[0-9a-fA-F]{24}$")
DEDUP_TTL = 24 * 3600

app = FastAPI(title="Paggins -> RedTrack Bridge", version="1.0.0")

RECENT: deque = deque(maxlen=80)
STATS = {"received": 0, "skipped_event": 0, "skipped_product": 0, "skipped_dedup": 0,
         "bad_sig": 0, "no_clickid": 0, "bad_clickid": 0, "forwarded": 0, "rt_error": 0}
_seen: dict[str, float] = {}


def _walk_find(data: Any, key: str) -> Any:
    """Primeira ocorrência de `key` em qualquer nível do payload."""
    if isinstance(data, dict):
        if key in data and data[key] not in (None, ""):
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


def _extract_clickid(payload: dict) -> str | None:
    """rtkcid do checkout: viaja em utm_term (padrão validado). Fallbacks defensivos."""
    for k in (RTKCID_FIELD, "utm_term", "utmTerm", "src", "clickid", "cid"):
        v = _walk_find(payload, k)
        if v and RTKCID_RE.match(str(v).strip()):
            return str(v).strip()
    return None


def _extract_amount(payload: dict) -> float | None:
    """Valor da venda em BRL. total_price primeiro (bloco fiscal infla busca cega)."""
    for k in ("total_price", "totalPrice", "amount", "value"):
        v = _walk_find(payload, k)
        if v is None:
            continue
        try:
            n = float(v)
        except (TypeError, ValueError):
            continue
        # heurística centavos: campos *InCents ou inteiros grandes
        if "cent" in k.lower() or (isinstance(v, int) and n > 1000):
            n = n / 100.0
        if n > 0:
            return round(n, 2)
    return None


def _verify_sig(raw: bytes, sig: str | None) -> bool:
    if not PAGGINS_WEBHOOK_SECRET:
        return True
    if not sig:
        return False
    mac = hmac.new(PAGGINS_WEBHOOK_SECRET.encode(), raw, hashlib.sha256).hexdigest()
    got = sig.strip().lower().removeprefix("sha256=")
    return hmac.compare_digest(mac, got)


def _record(outcome: str, payload: dict, **extra):
    RECENT.appendleft({"t": int(time.time()), "outcome": outcome,
                       "event": extra.get("event"), "order_id": extra.get("order_id"),
                       "clickid": extra.get("clickid"), "sum": extra.get("sum"),
                       "keys": list(payload)[:12]})


@app.get("/")
def root():
    return {"service": "paggins-redtrack-bridge", "version": "1.0.0",
            "redtrack_postback": REDTRACK_POSTBACK, "rtkcid_field": RTKCID_FIELD,
            "product_filter": bool(PRODUCT_ID_FILTER), "sig_required": bool(PAGGINS_WEBHOOK_SECRET)}


@app.get("/healthz")
def healthz():
    return {"ok": True}


@app.get("/debug/stats")
def stats():
    return {"stats": STATS, "recent_count": len(RECENT), "note": "zera no restart"}


@app.get("/debug/recent")
def recent(n: int = 20):
    return {"stats": STATS, "recent": list(RECENT)[:max(1, min(n, 80))]}


@app.post("/postback")
async def postback(request: Request, x_paggins_signature: str | None = Header(default=None)):
    STATS["received"] += 1
    raw = await request.body()
    ctype = request.headers.get("content-type", "")

    if not _verify_sig(raw, x_paggins_signature):
        STATS["bad_sig"] += 1
        _record("bad_sig", {})
        raise HTTPException(status_code=401, detail="invalid signature")

    import json as _json
    try:
        payload = _json.loads(raw)
    except Exception:
        payload = {"_raw": raw.decode("utf-8", "replace")[:1500]}
    if not isinstance(payload, dict):
        payload = {"_value": payload}

    event = str(payload.get("event") or payload.get("type")
                or _walk_find(payload, "event") or _walk_find(payload, "type") or "").lower()
    if not any(k in event for k in ("paid", "approved", "completed", "succeeded", "fulfilled")):
        STATS["skipped_event"] += 1
        _record("skipped_event", payload, event=event)
        return {"ok": True, "skipped": "event", "event": event}

    if PRODUCT_ID_FILTER and PRODUCT_ID_FILTER not in _json.dumps(payload):
        STATS["skipped_product"] += 1
        _record("skipped_product", payload, event=event)
        return {"ok": True, "skipped": "product_filter"}

    now = time.time()
    global _seen
    _seen = {k: v for k, v in _seen.items() if now - v < DEDUP_TTL}
    order_id = (_walk_find(payload, "sessionId") or _walk_find(payload, "orderId")
                or _walk_find(payload, "id") or f"paggins-{int(now * 1000)}")
    if order_id in _seen:
        STATS["skipped_dedup"] += 1
        _record("skipped_dedup", payload, event=event, order_id=order_id)
        return {"ok": True, "skipped": "duplicate", "order_id": order_id}
    _seen[order_id] = now

    clickid = _extract_clickid(payload)
    if not clickid:
        STATS["no_clickid"] += 1
        _record("no_clickid", payload, event=event, order_id=order_id)
        log.info("sem rtkcid (venda de outra fonte?) order=%s", order_id)
        return {"ok": True, "skipped": "no_clickid", "order_id": order_id}

    amount = _extract_amount(payload)
    params = {"clickid": clickid, "status": "approved"}
    if amount is not None:
        params["sum"] = f"{amount:.2f}"

    try:
        r = httpx.get(REDTRACK_POSTBACK, params=params, timeout=20)
        ok = r.status_code < 400
        STATS["forwarded" if ok else "rt_error"] += 1
        _record("forwarded" if ok else "rt_error", payload, event=event,
                order_id=order_id, clickid=clickid, sum=params.get("sum"))
        log.info("postback rt clickid=%s sum=%s -> %s", clickid, params.get("sum"), r.status_code)
        return {"ok": ok, "clickid": clickid, "sum": params.get("sum"), "rt_status": r.status_code}
    except Exception as e:  # noqa: BLE001
        STATS["rt_error"] += 1
        _record("rt_error", payload, event=event, order_id=order_id, clickid=clickid)
        log.error("postback rt falhou: %s", e)
        raise HTTPException(status_code=502, detail="redtrack postback failed")
