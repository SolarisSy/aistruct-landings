"""
Kirvano -> RedTrack bridge.

Recebe POST JSON da Kirvano (webhook Compra Aprovada) e dispara GET pro
RedTrack postback. Atribuicao via macro `sub15={clickid}` injetado na
offer URL do RedTrack (que vira ?sub15=<rtkcid> no checkout Kirvano).

POST /postback         <- Kirvano cola aqui
GET  /healthz          <- liveness
GET  /                 <- info
GET  /debug/stats      <- contadores (received/forwarded/attributed/no_clickid/skipped)
GET  /debug/recent?n=  <- ultimos N webhooks recebidos (payload + desfecho)

Env:
  REDTRACK_API_KEY     - nao usado (postback do RedTrack e publico)
  KIRVANO_SECRET       - opcional; se setado, exige header X-Kirvano-Token = esse valor
  CLICKID_FIELD        - default "sub15" (campo nos custom_fields da Kirvano)
  REDTRACK_POSTBACK    - default https://ohjzb.ttrk.io/postback (NAO api.redtrack.io -> 404)
  LOG_LEVEL            - default INFO
"""
from __future__ import annotations

import logging
import os
import re
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
CLICKID_FIELD    = os.environ.get("CLICKID_FIELD", "src").strip()
REDTRACK_POSTBACK = os.environ.get("REDTRACK_POSTBACK", "https://ohjzb.ttrk.io/postback").rstrip("/")

app = FastAPI(title="Kirvano -> RedTrack Bridge", version="1.2.0")

# rtkcid do RedTrack = ObjectId de 24 hex. Usado pra aceitar SO o trafego do
# fluxo Google/RedTrack e ignorar vendas de outras fontes (ex: Facebook de
# terceiros no mesmo produto Kirvano) que chegam pelo mesmo webhook.
_RTKCID_RE = re.compile(r"^[0-9a-fA-F]{24}$")

# --- observabilidade em memoria (zera no restart; suficiente pra QA ao vivo) ---
RECENT: deque = deque(maxlen=80)
STATS = {
    "received": 0,       # POSTs recebidos no /postback
    "skipped_event": 0,  # evento != aprovado
    "no_clickid": 0,     # sub15/clickid ausente no payload
    "forwarded": 0,      # postback disparado pro RedTrack
    "attributed": 0,     # RedTrack respondeu 200 (status:1 OK)
    "rt_error": 0,       # RedTrack respondeu != 200 ou exception
}


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


def _record(outcome: str, payload: Any, **extra) -> None:
    """Guarda um resumo do request no ring buffer (lido em GET /debug/recent)."""
    import json as _json
    try:
        raw = _json.dumps(payload)[:1400]
    except Exception:
        raw = str(payload)[:1400]
    RECENT.appendleft({"ts": _now(), "outcome": outcome, "payload": raw, **extra})


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


def _looks_like_rtkcid(v: str | None) -> bool:
    """rtkcid valido = 24 hex. Filtra valores de outras fontes (fbclid, utm texto)."""
    return bool(v) and bool(_RTKCID_RE.match(v.strip()))


def _extract_clickid(payload: dict) -> str | None:
    """Acha o rtkcid (24-hex) no payload Kirvano.

    Transporte oficial (fluxo Google Ads): a offer URL do RedTrack manda
    ?src={clickid} -> a Kirvano preserva em utm.src. O ?sub15= ela DESCARTA
    (param nao-UTM), por isso migramos pra src. sub15/rtkcid/etc ficam de fallback.
    SO retorna se o valor tiver cara de rtkcid -> vendas de Facebook/outras fontes
    no mesmo produto (src vazio, ou utm_* com texto) sao ignoradas: nao mistura.
    """
    seen: list[str] = []
    for k in (CLICKID_FIELD, "src", "rtkcid", "clickid", "click_id", "cid", "sub15"):
        if k in seen:
            continue
        seen.append(k)
        v = _walk_find(payload, k)
        if _looks_like_rtkcid(v):
            return v.strip()
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
        "version": "1.2.0",
        "secret_required": KIRVANO_SECRET is not None,
        "clickid_field": CLICKID_FIELD,
        "clickid_validation": "24-hex rtkcid only (ignora trafego nao-Google, ex: FB)",
        "redtrack_postback": REDTRACK_POSTBACK,
    }


@app.get("/healthz")
def health():
    return {"ok": True}


@app.get("/debug/stats")
def debug_stats():
    """Contadores acumulados desde o ultimo restart do bridge."""
    return {"stats": STATS, "recent_count": len(RECENT), "note": "zera no restart"}


@app.get("/debug/recent")
def debug_recent(n: int = 30):
    """Ultimos webhooks recebidos (payload + desfecho). Evidencia do que a Kirvano manda."""
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

    # Parse robusto: JSON -> form-encoded -> raw. A Kirvano manda JSON, mas
    # registramos QUALQUER coisa pra nao perder evidencia se o content-type diferir.
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
    # Aceita variacoes do nome do evento aprovado
    is_approved = any(k in event for k in ("approved", "aprovad", "purchase_paid", "sale_paid"))
    if event and not is_approved:
        STATS["skipped_event"] += 1
        _record("skipped_event", payload, content_type=ctype, event=event)
        log.info("ignored event=%s (only approved purchases trigger postback)", event)
        return {"ok": True, "skipped": "event_not_approved", "event": event}

    clickid = _extract_clickid(payload)
    if not clickid:
        STATS["no_clickid"] += 1
        _record("no_clickid", payload, content_type=ctype, event=event)
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
        STATS["forwarded"] += 1
        STATS["attributed" if r.status_code == 200 else "rt_error"] += 1
        _record("forwarded", payload, content_type=ctype, event=event,
                clickid=clickid, amount=amount,
                redtrack_status=r.status_code, redtrack_body=r.text[:200])
        log.info("redtrack postback: clickid=%s sum=%s -> %s",
                 clickid, params["sum"], r.status_code)
        return {"ok": True, "clickid": clickid, "payout": amount,
                "redtrack_status": r.status_code,
                "redtrack_body": r.text[:200]}
    except Exception as e:
        STATS["rt_error"] += 1
        _record("rt_exception", payload, content_type=ctype, error=str(e))
        log.error("redtrack postback failed: %s", e)
        raise HTTPException(status_code=502, detail=f"redtrack postback failed: {e!s}")
