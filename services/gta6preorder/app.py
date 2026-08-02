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
  GET  /debug/conversions -> vendas pagas COM identificador de clique (exige token)

ATRIBUIÇÃO DE VENDA (clique pago -> pedido):
  A loja não tem pixel nem tag de anunciante — pôr um exporia esta página ao
  Google. O identificador do clique (gclid/gbraid/wbraid) é capturado no browser
  por assets/session.js, viaja no CTA até checkout.html e chega aqui no POST
  /api/pagamento/pix. Daqui ele segue por DOIS caminhos redundantes:
    (a) vira o `externalRef` da cobrança — o StreetPays devolve esse campo ecoado
        no webhook, então a volta sobrevive até a um restart do container;
    (b) fica no mapa em memória _ATTR, indexado por externalRef e por id da
        transação, junto dos utm_*.
  No webhook PAID os dois são consultados e a venda vira uma linha em
  CONVERSIONS (identificador + valor + hora), pronta para importação offline.

Env:
  STREETPAYS_API_KEY      chave Bearer
  STREETPAYS_API_BASE     base da API (default api.streetpays.com.br) — trocar só em teste
  SELF_BASE_URL           URL pública deste serviço (para notificationUrl)
  LOG_LEVEL               INFO (default)
  DEBUG_TOKEN             segredo dos /debug/*. Sem ele os endpoints ficam FECHADOS.
  STREETPAYS_TOKEN        segredo do /webhook, viaja no notificationUrl (?token=).
                          VAZIO = aceita qualquer POST (comportamento historico).
  GADS_SHEET_WEBHOOK_URL  destino da conversão offline (Apps Script /exec). VAZIO = dormente:
                          a venda é registrada e nada é enviado ao Google.
  GADS_SHEET_SECRET       segredo compartilhado com o Apps Script
  GADS_CONVERSION_NAME    nome EXATO da ação de conversão no Google Ads (default "Compra")
  GADS_CURRENCY           default BRL
  TZ_OFFSET               default -03:00
"""
from __future__ import annotations

import json as _json
import logging
import os
import re
import time
from collections import deque
from datetime import datetime, timedelta, timezone
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
# Base sobrescrevível para conseguir exercitar o fluxo contra um gateway falso.
# Em produção nada é setado e o valor continua sendo o de sempre.
GATEWAY_API = os.environ.get("STREETPAYS_API_BASE", "https://api.streetpays.com.br").rstrip("/")
SELF_BASE_URL = os.environ.get("SELF_BASE_URL", "").rstrip("/")

DEBUG_TOKEN = os.environ.get("DEBUG_TOKEN", "").strip()
# Mesmo padrao do topperpay-bridge (<GATEWAY>_TOKEN, ?token= ou header X-Token).
# FAIL-OPEN: vazio = comportamento de sempre, nenhuma venda deixa de marcar.
STREETPAYS_TOKEN = os.environ.get("STREETPAYS_TOKEN", "").strip()
SHEET_URL = os.environ.get("GADS_SHEET_WEBHOOK_URL", "").strip()
SHEET_SECRET = os.environ.get("GADS_SHEET_SECRET", "").strip()
CONVERSION_NAME = os.environ.get("GADS_CONVERSION_NAME", "Compra").strip()
CURRENCY = os.environ.get("GADS_CURRENCY", "BRL").strip()
TZ_OFFSET = os.environ.get("TZ_OFFSET", "-03:00").strip()

PRICES = {
    "standard": 4499,   # R$44,99 — 1ª parcela
    "ultimate": 5499,   # R$54,99 — 1ª parcela
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
    "webhook_received": 0, "webhook_paid": 0, "webhook_skipped": 0, "webhook_recusado": 0,
    "duplicate": 0,
    # atribuição
    "pix_com_clique": 0, "pix_sem_clique": 0,
    "pago_com_clique": 0, "pago_sem_clique": 0,
    "conv_sem_coluna": 0, "sink_desligado": 0, "sink_ok": 0, "sink_erro": 0,
}

# ── Atribuição: ligar o pedido ao clique que o gerou ──────────────────────────
# Ordem de preferência do identificador: gbraid/wbraid substituem o gclid quando
# o clique vem de iOS/in-app e o gclid não é emitido.
CLICK_KEYS = ("gclid", "gbraid", "wbraid", "src", "clickid", "cid")
UTM_KEYS = ("utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content", "utm_id")
CLICK_RE = re.compile(r"^[A-Za-z0-9_.-]{6,400}$")
EXTREF_MAX = 190          # acima disso não arrisca o externalRef; o mapa cobre

_ATTR: dict[str, dict] = {}       # externalRef | tx_id -> origem do pedido
_ATTR_ORDER: deque = deque()      # poda FIFO
ATTR_MAX = 5000

CONVERSIONS: deque = deque(maxlen=500)   # vendas pagas com identificador de clique


def _clean_attr(attr: Any) -> dict:
    """Fica só com as chaves conhecidas — o corpo vem do browser, não é confiável."""
    if not isinstance(attr, dict):
        return {}
    out: dict[str, str] = {}
    for k in CLICK_KEYS + UTM_KEYS:
        v = attr.get(k)
        if v is None:
            continue
        v = str(v).strip()[:400]
        if v and v.lower() not in ("none", "null", "undefined"):
            out[k] = v
    return out


def _pick_click(attr: dict, click_id: str = "", click_type: str = "") -> tuple[str, str]:
    """Devolve (identificador, campo de origem). Vazio = visita sem clique pago."""
    cand = str(click_id or "").strip()
    if cand and CLICK_RE.match(cand):
        tipo = click_type if click_type in CLICK_KEYS else ""
        if not tipo:
            tipo = next((k for k in CLICK_KEYS if str(attr.get(k, "")).strip() == cand), "clickId")
        return cand, tipo
    for k in CLICK_KEYS:
        v = str(attr.get(k, "") or "").strip()
        if v and CLICK_RE.match(v):
            return v, k
    return "", ""


def _attr_put(keys: list[str], rec: dict) -> None:
    for k in keys:
        if not k:
            continue
        if k not in _ATTR:
            _ATTR_ORDER.append(k)
        _ATTR[k] = rec
    while len(_ATTR_ORDER) > ATTR_MAX:
        _ATTR.pop(_ATTR_ORDER.popleft(), None)


def _attr_get(*keys: str) -> dict | None:
    for k in keys:
        if k and k in _ATTR:
            return _ATTR[k]
    return None


def _conv_time(paid_at: str = "") -> str:
    """Hora da conversão como o import offline espera: 'AAAA-MM-DD HH:MM:SS-03:00'.

    Sai do pagamento quando o gateway informa — assim um reenvio do webhook gera a
    MESMA linha (o Google deduplica por identificador + ação + hora).
    """
    dt = None
    if paid_at:
        try:
            dt = datetime.fromisoformat(str(paid_at).replace("Z", "+00:00"))
        except Exception:  # noqa: BLE001
            dt = None
    if dt is None:
        dt = datetime.now(timezone.utc)
    if dt.tzinfo is None:
        dt = dt.replace(tzinfo=timezone.utc)
    sign = -1 if TZ_OFFSET.startswith("-") else 1
    try:
        hh, mm = TZ_OFFSET.lstrip("+-").split(":")
        delta = timedelta(hours=sign * int(hh), minutes=sign * int(mm))
    except Exception:  # noqa: BLE001
        delta = timedelta(hours=-3)
    return (dt.astimezone(timezone.utc) + delta).strftime("%Y-%m-%d %H:%M:%S") + TZ_OFFSET


async def _enviar_conversao(conv: dict) -> None:
    """Entrega da conversão ao Google (importação offline por gclid).

    DORMENTE por padrão: sem GADS_SHEET_WEBHOOK_URL nada sai daqui — a venda fica
    registrada em CONVERSIONS e visível em /debug/conversions. É proposital: dá
    para provar que o identificador está chegando ANTES de ligar o Google.
    """
    if conv.get("click_type") != "gclid":
        # gbraid/wbraid não entram na coluna "Google Click ID" — exigem coluna própria.
        STATS["conv_sem_coluna"] += 1
        log.warning("conversao com %s (nao gclid) — nao enviada: order=%s",
                    conv.get("click_type"), conv.get("order_id"))
        return
    if not SHEET_URL:
        STATS["sink_desligado"] += 1
        return
    body = {
        "secret": SHEET_SECRET,
        "gclid": conv["click_id"],
        "conversion_name": CONVERSION_NAME,
        "conversion_time": conv["conv_time"],
        "conversion_value": f"{conv['value']:.2f}",
        "conversion_currency": CURRENCY,
        "order_id": conv.get("order_id") or "",
    }
    try:
        async with httpx.AsyncClient(timeout=20) as c:
            r = await c.post(SHEET_URL, json=body, follow_redirects=True)
        if r.status_code < 300:
            STATS["sink_ok"] += 1
            conv["sent"] = True
        else:
            STATS["sink_erro"] += 1
            _record("sink_error", {"order_id": conv.get("order_id")},
                    status=r.status_code, body=r.text[:200])
    except Exception as e:  # noqa: BLE001
        STATS["sink_erro"] += 1
        _record("sink_error", {"order_id": conv.get("order_id")}, error=str(e)[:200])


def _now() -> str:
    return datetime.now(timezone.utc).isoformat()


# ── Dado pessoal: mascarar ANTES de guardar ───────────────────────────────────
# O webhook do gateway traz nome, e-mail, CPF e telefone do comprador. Guardar
# isso cru em memoria para depois filtrar na saida seria confiar no token: token
# vaza, e o dado do comprador nao tem por que estar ali de qualquer forma.
# Por isso a mascara e aplicada na ESCRITA — o que entra em RECENT ja e anonimo.
PII_KEYS = {
    "name", "nome", "fullname", "full_name", "firstname", "lastname",
    "email", "e_mail", "mail",
    "cpf", "taxid", "tax_id", "document", "documento", "cnpj",
    "phone", "telefone", "celular", "cellphone", "mobile", "whatsapp",
    "address", "endereco", "zipcode", "cep", "birthdate", "nascimento",
}
_RE_EMAIL = re.compile(r"[\w.+-]+@[\w-]+\.[\w.-]+")
_RE_LONGO = re.compile(r"\d{8,14}")           # CPF/CNPJ/telefone soltos no texto


def _mask_email(v: str) -> str:
    m = _RE_EMAIL.search(v)
    if not m:
        return "***"
    user, _, dom = m.group(0).partition("@")
    return (user[:1] or "*") + "***@" + dom


def _mask_digits(v: str) -> str:
    d = re.sub(r"\D", "", v)
    return ("*" * max(0, len(d) - 2)) + d[-2:] if d else "***"


def _mask_nome(v: str) -> str:
    partes = [x for x in str(v).split() if x]
    return " ".join((x[:1].upper() + ".") for x in partes[:4]) or "***"


def _mask_valor(chave: str, v: Any) -> Any:
    k = chave.lower().replace("-", "").replace("_", "")
    if not isinstance(v, str):
        return v
    if "mail" in k:
        return _mask_email(v)
    if k in ("name", "nome", "fullname", "firstname", "lastname"):
        return _mask_nome(v)
    return _mask_digits(v)


def _mask_texto(v: str) -> str:
    """Mascara e-mail/documento soltos em texto livre (ex.: erro cru do gateway)."""
    v = _RE_EMAIL.sub(lambda m: _mask_email(m.group(0)), v)
    return _RE_LONGO.sub(lambda m: _mask_digits(m.group(0)), v)


def _mask_pii(obj: Any, _prof: int = 0) -> Any:
    if _prof > 8:
        return "…"
    if isinstance(obj, dict):
        out = {}
        for k, v in obj.items():
            kk = str(k).lower().replace("-", "").replace("_", "")
            if kk in PII_KEYS and not isinstance(v, (dict, list)):
                out[k] = _mask_valor(str(k), v)
            elif kk in ("copypaste", "qrcode", "e2e", "barcode", "digitableline"):
                out[k] = "<omitido>"          # codigo pagavel, nao serve a diagnostico
            else:
                out[k] = _mask_pii(v, _prof + 1)
        return out
    if isinstance(obj, list):
        return [_mask_pii(v, _prof + 1) for v in obj[:20]]
    if isinstance(obj, str):
        return _mask_texto(obj)
    return obj


def _record(outcome: str, payload: Any, **extra) -> None:
    try:
        raw = _json.dumps(_mask_pii(payload), ensure_ascii=False)[:1400]
    except Exception:
        raw = _mask_texto(str(payload))[:1400]
    RECENT.appendleft({"ts": _now(), "outcome": outcome, "payload": raw,
                       **{k: (_mask_texto(v) if isinstance(v, str) else v)
                          for k, v in extra.items()}})


def _debug_liberado(token: str) -> bool:
    """Portao dos /debug/*. FAIL-CLOSED: sem DEBUG_TOKEN no ambiente, ninguem entra.

    Estes endpoints ficam expostos na internet junto com a loja (o nginx manda
    /debug para este processo), entao 'so quem sabe a URL' nao e protecao.
    """
    return bool(DEBUG_TOKEN) and token == DEBUG_TOKEN


def _webhook_autorizado(request: Request) -> bool:
    """Portao do /webhook. FAIL-OPEN de proposito.

    Sem STREETPAYS_TOKEN configurado aceita tudo, exatamente como antes: uma venda
    real que deixasse de marcar custaria mais do que o abuso que isto evita. Com o
    segredo setado, so passa quem o traz — o notificationUrl e montado com ele.

    Por que existe: o notificationUrl dinamico do StreetPays NAO vem assinado
    (ver .claude/skills/streetpays/SKILL.md). Sem portao, qualquer um posta um
    PAID forjado, vira linha em CONVERSIONS e, com o sink ligado, conversao falsa
    no Google Ads — que envenena o lance.
    """
    if not STREETPAYS_TOKEN:
        return True
    got = request.query_params.get("token") or request.headers.get("x-token") or ""
    return got == STREETPAYS_TOKEN


def _nao_existe():
    """404 em vez de 403: nao confirma que a rota existe para quem esta sondando."""
    return JSONResponse({"detail": "Not Found"}, status_code=404)


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
@app.get("/checkout.html", response_class=HTMLResponse)
def checkout():
    return HTMLResponse(_CHECKOUT_HTML)


@app.get("/obrigado", response_class=HTMLResponse)
@app.get("/obrigado.html", response_class=HTMLResponse)
def obrigado():
    return HTMLResponse(_OBRIGADO_HTML)


@app.get("/blog", response_class=HTMLResponse)
@app.get("/blog.html", response_class=HTMLResponse)
def blog():
    return HTMLResponse(_BLOG_HTML)


@app.get("/healthz")
def health():
    return {"ok": True}


@app.get("/debug/stats")
def debug_stats(token: str = ""):
    if not _debug_liberado(token):
        return _nao_existe()
    return {"stats": STATS, "recent_count": len(RECENT)}


@app.get("/debug/recent")
def debug_recent(token: str = "", n: int = 30):
    """Eventos recentes — ja gravados sem dado pessoal (ver _mask_pii).

    A segunda passada de mascara na saida e redundante de proposito: se um
    _record novo esquecer de passar pela mascara, ele nao vaza por aqui.
    """
    if not _debug_liberado(token):
        return _nao_existe()
    itens = [_mask_pii(x) for x in list(RECENT)[:max(1, min(n, 80))]]
    return {"stats": STATS, "recent": itens}


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
    attr: dict | None = None  # origem: gclid/gbraid/wbraid + utm_* (assets/session.js)
    clickId: str = ""         # identificador do clique já resolvido pelo checkout
    clickType: str = ""       # qual campo carregou o identificador


@app.post("/api/pagamento/pix")
async def create_pix(body: PixRequest):
    STATS["pix_requested"] += 1

    edition = body.edition if body.edition in PRICES else "standard"
    amount = PRICES[edition]
    product_name = EDITION_NAMES[edition]
    order_ref = body.externalRef or f"gta6-{int(time.time()*1000)}"

    # ── Atribuição ────────────────────────────────────────────────────────────
    # O externalRef é o ÚNICO campo que o StreetPays devolve ecoado no webhook.
    # Por isso é ele que carrega o identificador do clique: assim a venda volta ao
    # clique mesmo que este processo tenha reiniciado no meio (o mapa é memória).
    attr = _clean_attr(body.attr)
    click_id, click_type = _pick_click(attr, body.clickId, body.clickType)

    if click_id and len(click_id) <= EXTREF_MAX:
        external_ref = click_id
    else:
        external_ref = order_ref
        if click_id:
            log.warning("click id de %d chars — externalRef fica com o pedido, "
                        "atribuição só pelo mapa", len(click_id))

    attr_rec = {
        "click_id": click_id, "click_type": click_type, "attr": attr,
        "order_ref": order_ref, "external_ref": external_ref,
        "edition": edition, "amount": amount, "created_at": _now(),
    }
    _attr_put([external_ref, order_ref], attr_rec)
    STATS["pix_com_clique" if click_id else "pix_sem_clique"] += 1

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
        notif = f"{SELF_BASE_URL}/webhook"
        if STREETPAYS_TOKEN:
            notif += f"?token={STREETPAYS_TOKEN}"
        payload["notificationUrl"] = notif

    log.info("pix_create edition=%s amount=%s externalRef=%s click=%s(%s)",
             edition, amount, external_ref,
             (click_id[:12] + "…") if click_id else "-", click_type or "-")

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
                status=r.status_code, body=_mask_texto(r.text[:400]))
        log.error("streetpays %s: %s", r.status_code, _mask_texto(r.text[:300]))
        raise HTTPException(502, f"gateway error: {r.status_code}")

    tx = r.json()
    STATS["pix_created"] += 1
    tx_id = tx.get("id", "")
    pix_code = (tx.get("data") or {}).get("copypaste", "")
    if tx_id:
        attr_rec["tx_id"] = tx_id
        _attr_put([tx_id], attr_rec)      # webhook resolve por id da transação também
    _record("pix_created", {"externalRef": external_ref}, tx_id=tx_id, amount=amount,
            click_type=click_type or None,
            click_tail=click_id[-6:] if click_id else None)

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
    if not _webhook_autorizado(request):
        STATS["webhook_recusado"] += 1
        # mesma resposta de rota inexistente: nao confirma que ha validacao aqui
        log.warning("webhook recusado (segredo ausente/errado) de %s",
                    request.client.host if request.client else "?")
        return _nao_existe()
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

    # ── De volta ao clique ────────────────────────────────────────────────────
    # 1) mapa em memória (externalRef ou id da transação);
    # 2) o próprio externalRef ecoado — é o que sobrevive a um restart.
    rec = _attr_get(tx_id, ext_ref) or {}
    click_id = str(rec.get("click_id") or "")
    click_type = str(rec.get("click_type") or "")
    if not click_id and ext_ref and CLICK_RE.match(ext_ref) and not ext_ref.startswith("gta6-"):
        click_id, click_type = ext_ref, "externalRef"

    _record("paid", payload, tx_id=tx_id, externalRef=ext_ref, amount=reais,
            click_type=click_type or None,
            click_tail=click_id[-6:] if click_id else None)

    if not click_id:
        STATS["pago_sem_clique"] += 1
        log.warning("PAID SEM identificador de clique — tx_id=%s externalRef=%s amount=R$%.2f "
                    "(venda existe, mas não há clique a atribuir)", tx_id, ext_ref, reais)
        return {"ok": True, "tx_id": tx_id, "amount": reais, "atribuido": False}

    STATS["pago_com_clique"] += 1
    conv = {
        "click_id": click_id,
        "click_type": click_type,
        "value": reais,
        "currency": CURRENCY,
        "conv_name": CONVERSION_NAME,
        "conv_time": _conv_time(str(obj.get("paidAt") or "")),
        "order_id": tx_id or ext_ref,
        "external_ref": ext_ref,
        "utm": (rec.get("attr") or {}),
        "recorded_at": _now(),
        "sent": False,
    }
    CONVERSIONS.appendleft(conv)
    log.info("PAID tx_id=%s amount=R$%.2f %s=…%s", tx_id, reais, click_type, click_id[-6:])

    await _enviar_conversao(conv)

    return {"ok": True, "tx_id": tx_id, "amount": reais, "atribuido": True,
            "click_type": click_type, "click_tail": click_id[-6:],
            "enviado_ao_google": conv["sent"]}


@app.get("/debug/conversions")
def debug_conversions(token: str = "", n: int = 50):
    """Vendas pagas com identificador de clique — auditoria da atribuição.

    Fail-closed: sem DEBUG_TOKEN no ambiente ninguém entra. O identificador do
    clique só aparece pela cauda (os 8 últimos) — não é preciso expor o valor
    inteiro para conferir que a ponta está fechando.
    """
    if not _debug_liberado(token):
        return _nao_existe()
    itens = []
    for c in list(CONVERSIONS)[:max(1, min(n, 500))]:
        itens.append({
            "click_type": c["click_type"],
            "click_tail": c["click_id"][-8:],
            "value": c["value"], "currency": c["currency"],
            "conv_name": c["conv_name"], "conv_time": c["conv_time"],
            "order_id": c["order_id"], "sent": c["sent"],
            "utm_source": c["utm"].get("utm_source"),
            "utm_campaign": c["utm"].get("utm_campaign"),
        })
    return {
        "sink_ligado": bool(SHEET_URL),
        "conversion_name": CONVERSION_NAME,
        "total": len(CONVERSIONS),
        "stats": {k: STATS[k] for k in (
            "pix_com_clique", "pix_sem_clique", "pago_com_clique", "pago_sem_clique",
            "conv_sem_coluna", "sink_desligado", "sink_ok", "sink_erro")},
        "conversions": itens,
    }
