"""
Kirvano -> RedTrack bridge.

Recebe POST JSON da Kirvano (webhook Compra Aprovada) e dispara GET pro
RedTrack postback. Atribuicao via macro `src={clickid}` injetado na offer
URL do RedTrack (que vira ?src=<rtkcid> no checkout -> Kirvano poe em utm.src).
Valor da venda = `total_price` (NAO o bloco fiscal — ver _extract_amount).

POST /postback         <- Kirvano cola aqui
GET  /healthz          <- liveness
GET  /                 <- info
GET  /debug/stats      <- contadores (received/forwarded/attributed/no_clickid/skipped)
GET  /debug/recent?n=  <- ultimos N webhooks recebidos (payload + desfecho)

Env:
  REDTRACK_API_KEY     - nao usado (postback do RedTrack e publico)
  KIRVANO_SECRET       - opcional; se setado, exige header X-Kirvano-Token = esse valor
  CLICKID_FIELD        - default "src" (a Kirvano poe o ?src= da offer URL em utm.src)
  REDTRACK_POSTBACK    - default https://ohjzb.ttrk.io/postback (NAO api.redtrack.io -> 404)
  LOG_LEVEL            - default INFO
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
CLICKID_FIELD    = os.environ.get("CLICKID_FIELD", "src").strip()
REDTRACK_POSTBACK = os.environ.get("REDTRACK_POSTBACK", "https://ohjzb.ttrk.io/postback").rstrip("/")

# --- UTMify (ofertas migradas do RedTrack: ganhabr.lat) -----------------------
# A UTMify NÃO tem receiver nativo de Kirvano (os receivers são Cartpanda, Clickbank,
# Eduzz, Hotmart, Kiwify, Payt). Sem este POST, oferta em UTMify mostra clique/gasto e
# NUNCA a venda. Doc: https://docs.utmify.com.br/envio-de-vendas
UTMIFY_ORDERS_URL = os.environ.get(
    "UTMIFY_ORDERS_URL", "https://api.utmify.com.br/api-credentials/orders").rstrip("/")
UTMIFY_API_TOKEN  = os.environ.get("UTMIFY_API_TOKEN", "").strip() or None

app = FastAPI(title="Kirvano -> RedTrack/UTMify Bridge", version="1.5.0")

# rtkcid do RedTrack = ObjectId de 24 hex. Usado pra aceitar SO o trafego do
# fluxo Google/RedTrack e ignorar vendas de outras fontes (ex: Facebook de
# terceiros no mesmo produto Kirvano) que chegam pelo mesmo webhook.
_RTKCID_RE = re.compile(r"^[0-9a-fA-F]{24}$")

# --- observabilidade em memoria (zera no restart; suficiente pra QA ao vivo) ---
RECENT: deque = deque(maxlen=80)
STATS = {
    "received": 0,       # POSTs recebidos no /postback
    "skipped_event": 0,  # evento != aprovado
    "skipped_dedup": 0,  # order_id já processado (retry/duplicate da Kirvano)
    "no_clickid": 0,     # sub15/clickid ausente no payload
    "forwarded": 0,      # postback disparado pro RedTrack
    "attributed": 0,     # RedTrack respondeu 200 (status:1 OK)
    "rt_error": 0,       # RedTrack respondeu != 200 ou exception
    "utmify_sent": 0,    # venda postada na UTMify (oferta migrada, sem rtkcid)
    "utmify_ok": 0,      # UTMify respondeu 2xx
    "utmify_error": 0,   # UTMify respondeu != 2xx ou exception
}

# Deduplicação de webhooks: guarda order_ids já processados por DEDUP_TTL segundos.
# Resolve o loop retry da Kirvano (envia N vezes em timeout) sem precisar de Redis.
# Zera no restart — janela de risco é o restart coincidir com retry dentro do TTL.
_seen_orders: dict[str, float] = {}
DEDUP_TTL = 86400  # 24h; cobre janela de retry da Kirvano


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


def _parse_brl(s: Any) -> float | None:
    """Parseia valor monetario BR: 'R$ 197,00' / '1.234,56' -> float em reais."""
    if s is None:
        return None
    if isinstance(s, (int, float)):
        return float(s)
    t = re.sub(r"[^\d,.-]", "", str(s))  # tira 'R$', espacos,
    if not t:
        return None
    if "," in t:                          # formato BR: '.' = milhar, ',' = decimal
        t = t.replace(".", "").replace(",", ".")
    try:
        return float(t)
    except ValueError:
        return None


def _extract_amount(payload: dict) -> float | None:
    """Valor REAL da compra, em reais.

    Fonte canonica = `total_price` no topo do webhook (ex: 'R$ 197,00') = o
    valor efetivamente cobrado NAQUELA transacao (produto principal OU upsell).

    ⚠️ NUNCA usar busca recursiva (_walk_find): o payload da Kirvano tem um bloco
    `fiscal` (valor de nota fiscal / imposto / total financiado em 12x) que NAO e
    o valor da venda. A versao antiga pegava esse campo e inflava o postback
    (R$197 -> R$1224.99); causa do "RedTrack marca 10k com 2.4k real" (2026-06-03).
    """
    # 1) total_price (string BRL) — canonico, sempre presente
    v = _parse_brl(payload.get("total_price"))
    if v is not None:
        return v
    # 2) transaction.values.total (centavos int) — formato da API interna
    tx = payload.get("transaction")
    if isinstance(tx, dict):
        tot = (tx.get("values") or {}).get("total")
        if isinstance(tot, (int, float)):
            return tot / 100.0
    # 3) preco do produto principal (ignora order bump)
    for prod in (payload.get("products") or []):
        if isinstance(prod, dict) and not prod.get("is_order_bump"):
            pv = _parse_brl(prod.get("price"))
            if pv is not None:
                return pv
    return None


def _cents(v: float | None) -> int:
    return int(round((v or 0.0) * 100))


def _kirvano_to_utmify(payload: dict, amount: float | None) -> dict:
    """Mapeia o webhook da Kirvano pro schema da Orders API da UTMify.

    Schema oficial: https://docs.utmify.com.br/envio-de-vendas
    Tudo com .get()/fallback: a Kirvano varia o payload por evento e não versiona.
    Campos obrigatórios da UTMify que a Kirvano pode não mandar recebem default seguro
    (ex.: customer.email vazio) — melhor mandar a venda com campo pobre do que perder a
    venda inteira por KeyError.
    """
    cust = payload.get("customer") or {}
    utm = payload.get("utm") or {}

    # A Kirvano manda "PIX"/"CREDIT_CARD"/"BOLETO"; a UTMify aceita um enum minúsculo.
    pm_raw = str(payload.get("payment_method") or payload.get("payment") or "").lower()
    if "pix" in pm_raw:
        payment = "pix"
    elif "boleto" in pm_raw or "bank_slip" in pm_raw:
        payment = "boleto"
    elif "card" in pm_raw or "cartao" in pm_raw or "cartão" in pm_raw:
        payment = "credit_card"
    else:
        payment = "pix"   # a oferta vende por PIX; default menos errado que abortar

    now = time.strftime("%Y-%m-%d %H:%M:%S", time.gmtime())
    created = str(payload.get("created_at") or payload.get("createdAt") or now)[:19]
    approved = str(payload.get("approved_at") or payload.get("paid_at") or now)[:19]

    produtos = []
    for p in (payload.get("products") or []):
        if not isinstance(p, dict):
            continue
        preco = _parse_brl(p.get("price"))
        produtos.append({
            "id": str(p.get("id") or p.get("offer_id") or p.get("name") or "produto"),
            "name": str(p.get("name") or "produto"),
            "planId": None,
            "planName": None,
            "quantity": int(p.get("quantity") or 1),
            "priceInCents": _cents(preco if preco is not None else amount),
        })
    if not produtos:
        produtos = [{"id": "kirvano", "name": str(payload.get("product_name") or "produto"),
                     "planId": None, "planName": None, "quantity": 1,
                     "priceInCents": _cents(amount)}]

    total_cents = _cents(amount)
    return {
        "orderId": str(payload.get("order_id") or payload.get("id") or ""),
        "platform": "Kirvano",
        "paymentMethod": payment,
        "status": "paid",          # só chega aqui evento aprovado (whitelist acima)
        "createdAt": created,
        "approvedDate": approved,
        "refundedAt": None,
        "customer": {
            "name": str(cust.get("name") or ""),
            "email": str(cust.get("email") or ""),
            "phone": cust.get("phone_number") or cust.get("phone"),
            "document": cust.get("document") or cust.get("cpf"),
            "country": "BR",
            # ⚠️ customer.ip é OBRIGATÓRIO na Orders API, apesar de a doc listar como
            # opcional. Provado 17/07: com ip=null → 400 SCHEMA_VALIDATION_FAILED
            # ("customer.ip cannot be null"); com ip → 200 SUCCESS. A Kirvano nem sempre
            # manda o IP, então o fallback 0.0.0.0 evita perder a venda inteira por causa
            # de um campo que não afeta atribuição.
            "ip": cust.get("ip") or _walk_find(payload, "ip") or "0.0.0.0",
        },
        "products": produtos,
        # É daqui que a UTMify liga a venda ao clique (e ao gclid que o utms/latest.js
        # carimbou no checkout). Sem os utm_*, a venda entra como orgânica.
        "trackingParameters": {
            "src": utm.get("src"),
            "sck": utm.get("sck"),
            "utm_source": utm.get("utm_source"),
            "utm_campaign": utm.get("utm_campaign"),
            "utm_medium": utm.get("utm_medium"),
            "utm_content": utm.get("utm_content"),
            "utm_term": utm.get("utm_term"),
        },
        "commission": {
            "totalPriceInCents": total_cents,
            "gatewayFeeInCents": 0,      # a Kirvano não manda a fee no webhook
            "userCommissionInCents": total_cents,
        },
        "isTest": False,
    }


async def _send_utmify(payload: dict, amount: float | None) -> tuple[int, str]:
    body = _kirvano_to_utmify(payload, amount)
    async with httpx.AsyncClient(timeout=20) as c:
        r = await c.post(UTMIFY_ORDERS_URL, json=body,
                         headers={"x-api-token": UTMIFY_API_TOKEN or "",
                                  "Content-Type": "application/json"})
    return r.status_code, r.text[:200]


@app.get("/")
def root():
    return {
        "service": "kirvano-redtrack-bridge",
        "version": "1.4.0",
        "secret_required": KIRVANO_SECRET is not None,
        "clickid_field": CLICKID_FIELD,
        "clickid_validation": "24-hex rtkcid only (ignora trafego nao-Google, ex: FB)",
        "amount_source": "total_price (BRL) — NAO usa bloco fiscal",
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
    # Aceita variacoes do nome do evento aprovado.
    # IMPORTANTE: event vazio ("") também é rejeitado — whitelist, não blacklist.
    is_approved = any(k in event for k in ("approved", "aprovad", "purchase_paid", "sale_paid"))
    if not is_approved:
        STATS["skipped_event"] += 1
        _record("skipped_event", payload, content_type=ctype, event=event)
        log.info("ignored event=%r (only approved purchases trigger postback)", event)
        return {"ok": True, "skipped": "event_not_approved", "event": event}

    # Deduplicação por order_id — protege contra retries da Kirvano (at-least-once delivery).
    # Kirvano não documenta o limite de retries; sem este guard cada retry vira conversão extra.
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

    clickid = _extract_clickid(payload)
    amount = _extract_amount(payload) or 0.0

    # ------------------------------------------------------------------
    # ROTEAMENTO RedTrack x UTMify — MUTUAMENTE EXCLUSIVO (de propósito).
    #
    #   TEM rtkcid  -> oferta no fluxo RedTrack  -> postback RedTrack (como sempre)
    #   NÃO tem     -> oferta migrada p/ UTMify  -> Orders API da UTMify
    #
    # ⚠️ NUNCA mandar pros DOIS: o RedTrack importa a conversão pro Google Ads pelo
    # gclid E a UTMify também manda pro Google (pixel-google.js + conta conectada).
    # Postar nos dois = conversão CONTADA EM DOBRO no Google -> smart bidding aprende
    # errado e o ROI aparece inflado. O rtkcid é o discriminador natural: a página em
    # UTMify não tem unilpclick, logo nunca gera rtkcid.
    # ------------------------------------------------------------------
    if not clickid:
        if not UTMIFY_API_TOKEN:
            # Sem token não há como marcar a venda. Falha RUIDOSA: antes um alerta aqui
            # do que "clique sobe, venda some" descoberto no fim do mês.
            STATS["no_clickid"] += 1
            _record("no_clickid_no_utmify", payload, content_type=ctype, event=event)
            log.error("venda SEM rtkcid e UTMIFY_API_TOKEN ausente -> venda NAO marcada "
                      "em lugar nenhum (order_id=%s)", order_id)
            return JSONResponse(
                {"ok": False, "error": "no_clickid_and_no_utmify_token"}, status_code=200)

        try:
            status_code, body = await _send_utmify(payload, amount)
            STATS["utmify_sent"] += 1
            ok = 200 <= status_code < 300
            STATS["utmify_ok" if ok else "utmify_error"] += 1
            _record("utmify_forwarded", payload, content_type=ctype, event=event,
                    amount=amount, utmify_status=status_code, utmify_body=body)
            log.info("utmify order: order_id=%s sum=%s -> %s", order_id, amount, status_code)
            return {"ok": ok, "route": "utmify", "order_id": order_id,
                    "payout": amount, "utmify_status": status_code, "utmify_body": body}
        except Exception as e:
            STATS["utmify_error"] += 1
            _record("utmify_exception", payload, content_type=ctype, error=str(e))
            log.error("utmify order failed: %s", e)
            raise HTTPException(status_code=502, detail=f"utmify order failed: {e!s}")

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
