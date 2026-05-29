"""
Kirvano -> RedTrack bridge.

Recebe POST JSON da Kirvano (webhook Compra Aprovada) e dispara GET pro
RedTrack postback. Atribuicao via macro `sub15={clickid}` injetado na
offer URL do RedTrack (que vira ?sub15=<rtkcid> no checkout Kirvano).

POST /postback         <- Kirvano cola aqui
GET  /healthz          <- liveness
GET  /                 <- info

Env:
  REDTRACK_API_KEY     - nao usado (postback do RedTrack e publico)
  KIRVANO_SECRET       - opcional; se setado, exige header X-Kirvano-Token = esse valor
  CLICKID_FIELD        - default "sub15" (campo nos custom_fields da Kirvano)
  REDTRACK_POSTBACK    - default https://api.redtrack.io/postback
  LOG_LEVEL            - default INFO
"""
from __future__ import annotations

import logging
import os
from typing import Any

import httpx
from fastapi import FastAPI, Header, HTTPException, Request
from fastapi.responses import JSONResponse


logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("bridge")

KIRVANO_SECRET   = os.environ.get("KIRVANO_SECRET", "").strip() or None
CLICKID_FIELD    = os.environ.get("CLICKID_FIELD", "sub15").strip()
REDTRACK_POSTBACK = os.environ.get("REDTRACK_POSTBACK", "https://ohjzb.ttrk.io/postback").rstrip("/")

app = FastAPI(title="Kirvano -> RedTrack Bridge", version="1.0.0")


def _walk_find(data: Any, key: str) -> str | None:
    """Busca recursiva por uma chave em dict/list, retorna primeiro valor encontrado."""
    if isinstance(data, dict):
        if key in data and data[key] not in (None, "", []):
            return str(data[key])
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


def _extract_clickid(payload: dict) -> str | None:
    """Tenta achar o rtkcid em varios lugares plausiveis do payload Kirvano."""
    # 1. custom_fields.sub15 (padrao)
    candidates_keys = [CLICKID_FIELD, "rtkcid", "cid", "clickid", "click_id"]
    for k in candidates_keys:
        v = _walk_find(payload, k)
        if v:
            return v
    return None


def _extract_amount(payload: dict) -> float | None:
    """Acha o valor da compra (em reais).

    Kirvano padroniza enviando float em reais (ex: 97.00). Se vier int sem
    decimais, assume centavos e divide por 100.
    """
    for k in ("amount", "total", "total_value", "value", "price", "checkout_total", "net_amount"):
        v = _walk_find(payload, k)
        if v is None:
            continue
        try:
            if isinstance(v, int):
                # Kirvano sometimes sends total in centavos (int)
                return v / 100.0 if v > 100 else float(v)
            fv = float(str(v).replace(",", "."))
            return fv
        except (ValueError, TypeError):
            continue
    return None


@app.get("/")
def root():
    return {
        "service": "kirvano-redtrack-bridge",
        "version": "1.0.0",
        "secret_required": KIRVANO_SECRET is not None,
        "clickid_field": CLICKID_FIELD,
        "redtrack_postback": REDTRACK_POSTBACK,
    }


@app.get("/healthz")
def health():
    return {"ok": True}


@app.post("/postback")
async def postback(request: Request,
                   x_kirvano_token: str | None = Header(default=None)):
    if KIRVANO_SECRET and x_kirvano_token != KIRVANO_SECRET:
        log.warning("rejected: bad/missing X-Kirvano-Token header")
        raise HTTPException(status_code=401, detail="invalid token")

    try:
        payload = await request.json()
    except Exception as e:
        log.error("invalid JSON body: %s", e)
        raise HTTPException(status_code=400, detail="invalid JSON body")

    # logar payload (truncado) pra debug
    import json as _json
    log.info("payload received: %s", _json.dumps(payload)[:800])

    event = (payload.get("event") or payload.get("type") or "").lower()
    # Aceita variacoes do nome
    is_approved = any(k in event for k in ("approved", "aprovad", "purchase_paid", "sale_paid"))
    if event and not is_approved:
        log.info("ignored event=%s (only approved purchases trigger postback)", event)
        return {"ok": True, "skipped": "event_not_approved", "event": event}

    clickid = _extract_clickid(payload)
    if not clickid:
        log.warning("no clickid found in payload (field=%s)", CLICKID_FIELD)
        return JSONResponse(
            {"ok": False, "error": "clickid_not_found", "field": CLICKID_FIELD},
            status_code=200,
        )

    amount = _extract_amount(payload) or 0.0

    # Dispara GET pro RedTrack
    # Param name OBRIGATORIO: 'clickid' (nao 'cid'); endpoint no tracking domain.
    params = {"clickid": clickid, "sum": f"{amount:.2f}", "status": "approved"}
    try:
        async with httpx.AsyncClient(timeout=15) as c:
            r = await c.get(REDTRACK_POSTBACK, params=params)
        log.info("redtrack postback: clickid=%s sum=%s -> %s",
                 clickid, params["sum"], r.status_code)
        return {"ok": True, "clickid": clickid, "payout": amount,
                "redtrack_status": r.status_code,
                "redtrack_body": r.text[:200]}
    except Exception as e:
        log.error("redtrack postback failed: %s", e)
        raise HTTPException(status_code=502, detail=f"redtrack postback failed: {e!s}")
