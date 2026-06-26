"""
HYU (hyuoficial.com) -> Paggins SDK bridge + CAPTURA DE LEADS (paliativo).

O site e estatico (nginx); este servico:
  1. cria checkout sessions na Paggins (SDK) p/ o carrinho multi-item (sabores + combos).
     Fisico+frete confirmado funcionando 15/06 (scripts/paggins_gate.py; frete GRATIS auto).
  2. confirma pagamento: GET /session (status) + POST /webhook/paggins (eventos -> tabela orders).
  3. captura LEADS (paliativo p/ carrinho abandonado): form -> SQLite no volume da VPS.

POST /checkout         <- carrinho {items:[{flavor,tier,qty}|{combo,qty}], meta} -> session
GET  /session/{id}     <- status do pedido (pro obrigado)
POST /webhook/paggins  <- eventos Paggins (assinatura HMAC) -> marca orders pagos
POST /lead             <- site: {name, phone, email, items, meta} -> grava na VPS
GET  /leads            <- painel HTML (Basic auth: qualquer usuario + LEADS_PASSWORD)
GET  /leads.csv        <- export CSV (mesma auth)
GET  /healthz          <- liveness
GET  /                 <- info
GET  /debug/stats      <- contadores

Env:
  PAGGINS_API_KEY  - sk_live_... — setar no Easypanel, NUNCA no git
  PAGGINS_API_URL  - default https://api.paggins.com
  SITE_BASE        - default https://hyuoficial.com
  PAGGINS_WEBHOOK_SECRET - segredo p/ verificar x-paggins-signature do webhook; NUNCA no git
  LEADS_PASSWORD   - senha do /leads (obrigatoria pro painel; NUNCA no git)
  LEADS_DB         - default /data/leads.db (volume) com fallback ./leads.db
  LOG_LEVEL        - default INFO
"""
from __future__ import annotations

import base64
import csv
import hashlib
import hmac
import io
import json
import logging
import os
import re
import sqlite3
import uuid
from collections import deque
from datetime import datetime, timezone
from typing import Any

import httpx
from fastapi import FastAPI, HTTPException, Request, Response
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import HTMLResponse, StreamingResponse

logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("hyu-cart")

PAGGINS_API_KEY = os.environ.get("PAGGINS_API_KEY", "").strip()
PAGGINS_API_URL = os.environ.get("PAGGINS_API_URL", "https://api.paggins.com").rstrip("/")
SITE_BASE = os.environ.get("SITE_BASE", "https://hyuoficial.com").rstrip("/")
# email placeholder p/ a SDK Paggins (customer.email virou obrigatorio 24/06) — editavel no checkout
PLACEHOLDER_EMAIL = os.environ.get("CHECKOUT_PLACEHOLDER_EMAIL", "comprador@hyuoficial.com").strip()
# domínios do HYU (CORS + success/cancel por origem) — hyudrinks.com é o principal
HYU_ORIGINS = [
    "https://hyudrinks.com", "https://www.hyudrinks.com",
    "https://hyuoficial.com", "https://www.hyuoficial.com",
]
LEADS_PASSWORD = os.environ.get("LEADS_PASSWORD", "").strip()
PAGGINS_WEBHOOK_SECRET = os.environ.get("PAGGINS_WEBHOOK_SECRET", "").strip()
LEADS_DB = os.environ.get("LEADS_DB", "/data/leads.db")


# ── Cupons de influencer (desconto aplicado AQUI, no unitAmount) ──────────────
# O checkout EXTERNO (SDK) da Paggins NÃO aplica cupom (provado: campo de cupom não
# funciona em sessões SDK). Então o desconto é aplicado no preço que enviamos, e o
# código do influencer vai em metadata.coupon p/ atribuição/comissão.
# Override por env: INFLUENCER_COUPONS_JSON='{"ARTHURPC":5,...}'
_DEFAULT_COUPONS = {"ARTHURPC": 5, "THIAGO": 5, "ISA": 5, "NATHAN": 5, "DIGAO": 5}


def _parse_coupons() -> dict[str, int]:
    raw = os.environ.get("INFLUENCER_COUPONS_JSON", "").strip()
    if raw:
        try:
            return {str(k).strip().upper(): int(v) for k, v in json.loads(raw).items()
                    if 0 < int(v) < 100}
        except Exception:
            logging.getLogger("hyu-cart").error("INFLUENCER_COUPONS_JSON invalido; usando default")
    return dict(_DEFAULT_COUPONS)


INFLUENCER_COUPONS = _parse_coupons()


def _coupon_discount(raw_coupon: Any) -> tuple[str, int]:
    """Retorna (CODIGO, pct) se o cupom for válido/conhecido, senão ("", 0)."""
    code = str(raw_coupon or "").strip().upper()
    if code and code.isalnum() and code in INFLUENCER_COUPONS:
        return code, INFLUENCER_COUPONS[code]
    return "", 0
if not os.path.isdir(os.path.dirname(LEADS_DB) or "."):
    LEADS_DB = "./leads.db"  # dev local sem volume

app = FastAPI(title="HYU cart -> Paggins bridge", version="1.0.0")
app.add_middleware(
    CORSMiddleware,
    allow_origins=HYU_ORIGINS + [
        "https://hyuoficial.tiectu.easypanel.host",
        "http://localhost:4321", "http://localhost:4322", "http://localhost:4323",
        "http://127.0.0.1:4321", "http://127.0.0.1:4322", "http://127.0.0.1:4323",
    ],
    allow_methods=["POST", "GET", "OPTIONS"],
    allow_headers=["Content-Type"],
)

# ── catalogo server-side (fonte da verdade de preco/nome) ───────────────
# productId = UUID do produto na Paggins. ⚠️ os PREÇOS (cents) DEVEM casar com o
# front (src/data/products.ts: prices/combos). Hoje: kit6=6990 kit12=11990 kit24=21990 sub=9990.
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
    "kit6":  {"label": "Kit 6 (6 latas)",   "cents": 6990,  "sku": "K6"},
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
# combos = produtos Paggins proprios (1 productId cada). Assinaturas NAO entram (links fixos).
COMBOS: dict[str, dict[str, Any]] = {
    "kit-energy": {"productId": "5287a35b-9a4c-4307-a929-599ab5e2791d", "cents": 6990,
                   "name": "HYU Kit Energy (6 latas)", "sku": "KITENERGY", "img": "kit-energy"},
    "kit-soda":   {"productId": "62ed2805-089e-454f-afa0-f28f6dfa6abc", "cents": 6990,
                   "name": "HYU Kit Soda (6 latas)", "sku": "KITSODA", "img": "kit-soda"},
    "super-kit":  {"productId": "972ac99f-2800-4db9-88af-d34246cbd3ed", "cents": 11990,
                   "name": "HYU Super Kit (12 latas)", "sku": "SUPERKIT", "img": "super-kit"},
    "kit24":      {"productId": "35818f74-5267-46d6-98f7-c19badd7cea7", "cents": 21990,
                   "name": "HYU Kit 24 (24 latas)", "sku": "KIT24", "img": "super-kit"},
}

MAX_LINES = 10      # combinacoes distintas por pedido
MAX_QTY = 20        # kits por linha
META_KEYS = ("utm_source", "utm_medium", "utm_campaign", "utm_term",
             "utm_content", "gclid", "fbclid", "ref", "src")

RECENT: deque = deque(maxlen=80)
STATS = {"received": 0, "created": 0, "bad_request": 0, "paggins_error": 0,
         "leads_received": 0, "leads_saved": 0, "leads_bad": 0,
         "webhook_received": 0, "webhook_paid": 0, "webhook_bad_sig": 0}

# ── leads: SQLite no volume da VPS ───────────────────────────────────
def _db() -> sqlite3.Connection:
    conn = sqlite3.connect(LEADS_DB)
    conn.execute(
        "CREATE TABLE IF NOT EXISTS leads ("
        "id INTEGER PRIMARY KEY AUTOINCREMENT, ts TEXT, name TEXT, email TEXT, "
        "phone TEXT, pedido TEXT, total_cents INTEGER, items TEXT, meta TEXT, "
        "ip TEXT, ua TEXT)"
    )
    conn.execute(
        "CREATE TABLE IF NOT EXISTS orders ("
        "id INTEGER PRIMARY KEY AUTOINCREMENT, ts TEXT, session_id TEXT, order_id TEXT, "
        "event TEXT, status TEXT, amount_cents INTEGER, verified INTEGER, payload TEXT)"
    )
    return conn


def _build_items(raw_items: Any) -> list[dict[str, Any]]:
    """Valida o carrinho e monta os items da Paggins com preco do catalogo."""
    if not isinstance(raw_items, list) or not raw_items:
        raise HTTPException(400, "items vazio")
    merged: dict[tuple, int] = {}
    for it in raw_items:
        if not isinstance(it, dict):
            raise HTTPException(400, "item invalido")
        try:
            qty = int(it.get("qty", 1))
        except (TypeError, ValueError):
            raise HTTPException(400, "qty invalida")
        if not 1 <= qty <= MAX_QTY:
            raise HTTPException(400, f"qty fora de 1..{MAX_QTY}")
        combo = it.get("combo")
        if combo is not None:
            if combo not in COMBOS:
                raise HTTPException(400, f"combo desconhecido: {combo}")
            key: tuple = ("combo", combo)
        else:
            flavor, tier = it.get("flavor"), it.get("tier")
            if flavor not in FLAVORS or tier not in TIERS:
                raise HTTPException(400, f"combinacao desconhecida: {flavor}/{tier}")
            key = ("flavor", flavor, tier)
        merged[key] = min(merged.get(key, 0) + qty, MAX_QTY)
    if len(merged) > MAX_LINES:
        raise HTTPException(400, f"mais de {MAX_LINES} combinacoes")
    items = []
    for key, qty in merged.items():
        if key[0] == "combo":
            c = COMBOS[key[1]]
            base = {
                "productId": c["productId"], "name": c["name"], "type": "physical",
                "unitAmount": c["cents"], "quantity": 1, "sku": f"HYU-{c['sku']}",
                "imageUrl": f"{SITE_BASE}/img/kits/{c['img']}.webp",
            }
        else:
            _, flavor, tier = key
            f, t = FLAVORS[flavor], TIERS[tier]
            base = {
                "productId": PRODUCT_IDS[(flavor, tier)],
                "name": f"{f['name']} — {t['label']}", "type": "physical",
                "unitAmount": t["cents"], "quantity": 1,
                "sku": f"HYU-{f['sku']}-{t['sku']}", "description": f["desc"],
                "imageUrl": f"{SITE_BASE}/img/kits/{flavor}.webp",
            }
        # ⚠️ SDK Paggins exige quantity=1 por item → duplica a entrada `qty` vezes
        items.extend(dict(base) for _ in range(qty))
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

    # cupom de influencer → desconta o unitAmount de cada item (centavos, arredonda)
    coupon_code, discount_pct = _coupon_discount(payload.get("coupon"))
    if discount_pct:
        # half-up em centavos inteiros — bate exatamente com o display do front (JS Math.round)
        for it in items:
            it["unitAmount"] = max(1, (it["unitAmount"] * (100 - discount_pct) + 50) // 100)

    order_id = f"hyu-{uuid.uuid4().hex[:12]}"
    origin = str(payload.get("origin") or "").rstrip("/")
    base = origin if origin in HYU_ORIGINS else SITE_BASE  # volta pro domínio de origem
    # ⚠️ a SDK Paggins passou a EXIGIR customer.email valido (24/06; ainda "optional" na doc) —
    # sem ele toda sessao volta 400 "Dados da requisicao sao invalidos". O site nao coleta
    # email antes do checkout, entao mandamos um placeholder; o campo fica EDITAVEL na pagina
    # da Paggins e o comprador digita o real. Se o front passar customer.email, usamos o real.
    cust_email = str((payload.get("customer") or {}).get("email") or "").strip()
    if not _EMAIL_RE.match(cust_email):
        cust_email = PLACEHOLDER_EMAIL
    body = {
        "currency": "BRL",
        "items": items,
        "requireShippingInfo": True,
        "successUrl": f"{base}/obrigado/?session_id={{CHECKOUT_SESSION_ID}}",
        "cancelUrl": f"{base}/?checkout=cancelado",
        "externalOrderId": order_id,
        "customer": {"email": cust_email},
    }
    metadata = _build_metadata(payload.get("meta"))
    if discount_pct:
        metadata["coupon"] = coupon_code
        metadata["discount_pct"] = str(discount_pct)
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
                log.info("checkout %s -> %s total=%s coupon=%s", order_id,
                         sess.get("id"), total, coupon_code or "-")
                return {"checkoutUrl": sess.get("checkoutUrl"),
                        "sessionId": sess.get("id"), "totalAmount": total,
                        "coupon": coupon_code, "discountPct": discount_pct}
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


@app.get("/session/{session_id}")
async def get_session(session_id: str):
    """Confirma o status do pedido (usado pelo obrigado.astro)."""
    if not re.match(r"^cs_[A-Za-z0-9]+$", session_id):
        raise HTTPException(400, "session_id invalido")
    headers = {"Authorization": f"Bearer {PAGGINS_API_KEY}"}
    try:
        async with httpx.AsyncClient(timeout=20) as c:
            r = await c.get(
                f"{PAGGINS_API_URL}/v1/sdk/checkout-sessions/{session_id}?countryCode=BR",
                headers=headers)
    except httpx.HTTPError as e:
        raise HTTPException(502, f"paggins indisponivel: {str(e)[:80]}")
    if r.status_code == 404:
        raise HTTPException(404, "sessao nao encontrada")
    if r.status_code != 200:
        raise HTTPException(502, f"paggins {r.status_code}")
    s = r.json()
    pay = s.get("payment") or {}
    return {"id": s.get("id"), "status": s.get("status"),
            "paymentStatus": pay.get("status"), "totalAmount": s.get("totalAmount"),
            "currency": s.get("currency")}


def _webhook_verified(raw: bytes, sig: str) -> bool:
    """Confere a assinatura HMAC-SHA256 tentando variações de chave/payload
    (com/sem prefixo whsec_, raw vs JSON compacto) — o esquema exato é confirmado
    no 1º webhook real."""
    if not (PAGGINS_WEBHOOK_SECRET and sig):
        return False
    keys = {PAGGINS_WEBHOOK_SECRET}
    if "_" in PAGGINS_WEBHOOK_SECRET:
        keys.add(PAGGINS_WEBHOOK_SECRET.split("_", 1)[1])
    bodies = {raw}
    try:
        bodies.add(json.dumps(json.loads(raw), separators=(",", ":")).encode())
    except Exception:
        pass
    for k in keys:
        for b in bodies:
            if hmac.compare_digest(hmac.new(k.encode(), b, hashlib.sha256).hexdigest(), sig):
                return True
    return False


@app.post("/webhook/paggins")
async def paggins_webhook(request: Request):
    """Recebe eventos da Paggins e marca pedidos pagos. Verifica a assinatura HMAC
    mas NÃO descarta em mismatch — registra `verified` e processa mesmo assim, pra
    não perder pedido caso o esquema de assinatura difira (apertar após o 1º real)."""
    raw = await request.body()
    STATS["webhook_received"] += 1
    verified = _webhook_verified(raw, request.headers.get("x-paggins-signature", ""))
    if not verified:
        STATS["webhook_bad_sig"] += 1
    try:
        ev = json.loads(raw or b"{}")
    except Exception:
        raise HTTPException(400, "JSON invalido")
    event = ev.get("event") or ev.get("type") or ""
    sid = ev.get("sessionId") or ev.get("session_id") or ""
    PAID = {"checkout.session.completed", "payment.succeeded", "order.fulfilled"}
    if event in PAID:
        pay = ev.get("payment") or {}
        order_id = ev.get("orderId") or ev.get("externalOrderId") or ""
        ts = datetime.now(timezone.utc).isoformat(timespec="seconds")
        conn = _db()
        try:
            conn.execute(
                "INSERT INTO orders (ts, session_id, order_id, event, status, amount_cents, verified, payload) "
                "VALUES (?,?,?,?,?,?,?,?)",
                (ts, sid, order_id, event, "paid", pay.get("amount"), int(verified),
                 json.dumps(ev, ensure_ascii=False)[:4000]))
            conn.commit()
        finally:
            conn.close()
        STATS["webhook_paid"] += 1
        log.info("webhook PAGO(verified=%s): %s session=%s order=%s amount=%s",
                 verified, event, sid, order_id, pay.get("amount"))
    return {"received": True}


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


# ═══════════════════ LEADS (paliativo sem adquirente) ═══════════════════

_PHONE_RE = re.compile(r"^\d{10,13}$")
_EMAIL_RE = re.compile(r"^[^@\s]+@[^@\s]+\.[^@\s]+$")


def _pedido_str(items: list[dict[str, Any]]) -> tuple[str, int]:
    """(descricao legivel, total em centavos) a partir das linhas validadas."""
    parts, total = [], 0
    for it in items:
        f, t = FLAVORS[it["flavor"]], TIERS[it["tier"]]
        qty = int(it.get("qty", 1))
        label = f"{t['label'].split(' (')[0]} · {f['name'].replace('HYU Soda Protein ', '').replace('HYU Energy Protein ', '')}"
        parts.append(f"{qty}x {label}" if qty > 1 else label)
        total += t["cents"] * qty
    return " + ".join(parts), total


@app.post("/lead")
async def create_lead(request: Request):
    STATS["leads_received"] += 1
    try:
        p = await request.json()
    except Exception:
        STATS["leads_bad"] += 1
        raise HTTPException(400, "JSON invalido")

    name = str(p.get("name", "")).strip()[:120]
    email = str(p.get("email", "")).strip()[:160].lower()
    phone = re.sub(r"\D", "", str(p.get("phone", "")))[:13]
    if len(name) < 3:
        STATS["leads_bad"] += 1
        raise HTTPException(400, "nome invalido")
    if not _EMAIL_RE.match(email):
        STATS["leads_bad"] += 1
        raise HTTPException(400, "e-mail invalido")
    if not _PHONE_RE.match(phone):
        STATS["leads_bad"] += 1
        raise HTTPException(400, "whatsapp invalido (DDD + numero)")

    raw_items = p.get("items")
    if not isinstance(raw_items, list) or not raw_items or len(raw_items) > 10:
        STATS["leads_bad"] += 1
        raise HTTPException(400, "pedido vazio")
    items = []
    for it in raw_items:
        if not isinstance(it, dict) or it.get("flavor") not in FLAVORS or it.get("tier") not in TIERS:
            STATS["leads_bad"] += 1
            raise HTTPException(400, "item desconhecido")
        try:
            qty = max(1, min(int(it.get("qty", 1)), 20))
        except (TypeError, ValueError):
            qty = 1
        items.append({"flavor": it["flavor"], "tier": it["tier"], "qty": qty})

    pedido, total = _pedido_str(items)
    meta = _build_metadata(p.get("meta"))
    if isinstance(p.get("meta"), dict) and p["meta"].get("page"):
        meta["page"] = str(p["meta"]["page"])[:300]
    ip = (request.headers.get("x-forwarded-for", "").split(",")[0].strip()
          or (request.client.host if request.client else ""))
    ua = request.headers.get("user-agent", "")[:200]
    ts = datetime.now(timezone.utc).isoformat(timespec="seconds")

    conn = _db()
    try:
        conn.execute(
            "INSERT INTO leads (ts, name, email, phone, pedido, total_cents, items, meta, ip, ua) "
            "VALUES (?,?,?,?,?,?,?,?,?,?)",
            (ts, name, email, phone, pedido, total,
             json.dumps(items, ensure_ascii=False), json.dumps(meta, ensure_ascii=False), ip, ua),
        )
        conn.commit()
    finally:
        conn.close()
    STATS["leads_saved"] += 1
    log.info("lead salvo: %s | %s | %s | R$ %.2f", name, phone, pedido, total / 100)
    return {"ok": True}


def _leads_auth(request: Request) -> Response | None:
    """Basic auth: QUALQUER usuario + senha LEADS_PASSWORD. None = autorizado."""
    if not LEADS_PASSWORD:
        return Response("painel desativado (LEADS_PASSWORD nao configurada)", status_code=503)
    auth = request.headers.get("authorization", "")
    if auth.startswith("Basic "):
        try:
            dec = base64.b64decode(auth[6:]).decode("utf-8", "replace")
        except Exception:
            dec = ""
        if dec.split(":", 1)[-1] == LEADS_PASSWORD:
            return None
    return Response("autentique-se", status_code=401,
                    headers={"WWW-Authenticate": 'Basic realm="HYU leads"'})


def _fetch_leads() -> list[dict[str, Any]]:
    conn = _db()
    try:
        rows = conn.execute(
            "SELECT id, ts, name, email, phone, pedido, total_cents, meta, ip FROM leads ORDER BY id DESC"
        ).fetchall()
    finally:
        conn.close()
    out = []
    for r in rows:
        meta = json.loads(r[7] or "{}")
        out.append({"id": r[0], "ts": r[1], "name": r[2], "email": r[3], "phone": r[4],
                    "pedido": r[5], "total": r[6], "meta": meta, "ip": r[8]})
    return out


def _brl(cents: int) -> str:
    return f"R$ {cents / 100:,.2f}".replace(",", "X").replace(".", ",").replace("X", ".")


def _esc(s: str) -> str:
    return (str(s).replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
            .replace('"', "&quot;").replace("'", "&#39;"))


@app.get("/leads", response_class=HTMLResponse)
async def leads_panel(request: Request):
    denied = _leads_auth(request)
    if denied:
        return denied
    leads = _fetch_leads()
    total_geral = sum(l["total"] for l in leads)
    rows = []
    for l in leads:
        utm = " ".join(f"{k.replace('utm_', '')}={_esc(v)}" for k, v in l["meta"].items()
                       if k.startswith("utm_") or k in ("gclid", "src")) or "—"
        rows.append(
            f"<tr><td>{l['id']}</td>"
            f"<td data-ts='{l['ts']}'></td>"
            f"<td><strong>{_esc(l['name'])}</strong></td>"
            f"<td><a href='https://wa.me/55{l['phone']}' target='_blank'>{l['phone']}</a></td>"
            f"<td><a href='mailto:{_esc(l['email'])}'>{_esc(l['email'])}</a></td>"
            f"<td>{_esc(l['pedido'])}</td>"
            f"<td class='r'>{_brl(l['total'])}</td>"
            f"<td class='muted'>{utm}</td></tr>"
        )
    html = f"""<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Leads HYU ({len(leads)})</title>
<style>
 body{{font:14px/1.5 system-ui,sans-serif;margin:0;background:#f5f6f8;color:#16191f}}
 header{{display:flex;align-items:center;gap:1rem;padding:.9rem 1.2rem;background:#16191f;color:#fff;position:sticky;top:0}}
 h1{{font-size:1rem;margin:0}} .pill{{background:#A8CC30;color:#16191f;font-weight:800;border-radius:999px;padding:.1rem .6rem}}
 .grow{{flex:1}} a.btn{{background:#fff;color:#16191f;font-weight:700;text-decoration:none;border-radius:8px;padding:.35rem .8rem}}
 main{{padding:1rem 1.2rem;overflow-x:auto}}
 table{{border-collapse:collapse;width:100%;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.08)}}
 th,td{{padding:.55rem .7rem;border-bottom:1px solid #eceef1;text-align:left;vertical-align:top;font-size:.85rem}}
 th{{background:#fafbfc;font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#667}}
 tr:hover td{{background:#fcfde8}} .r{{text-align:right;white-space:nowrap;font-weight:700}}
 .muted{{color:#889;font-size:.75rem}} .total{{margin:.8rem 0;color:#445}}
</style></head><body>
<header><h1>📋 Leads HYU</h1><span class="pill">{len(leads)}</span><span class="grow"></span>
<a class="btn" href="/leads.csv">⬇ Exportar CSV</a></header>
<main><p class="total">Valor somado dos pedidos: <strong>{_brl(total_geral)}</strong></p>
<table><thead><tr><th>#</th><th>Quando</th><th>Nome</th><th>WhatsApp</th><th>E-mail</th>
<th>Pedido</th><th>Total</th><th>Origem</th></tr></thead>
<tbody>{''.join(rows) or '<tr><td colspan=8 style="text-align:center;padding:2rem;color:#889">Nenhum lead ainda — eles aparecem aqui na hora.</td></tr>'}</tbody></table></main>
<script>document.querySelectorAll('[data-ts]').forEach(td=>{{
 td.textContent=new Date(td.dataset.ts).toLocaleString('pt-BR',{{timeZone:'America/Sao_Paulo',day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'}});
}});</script></body></html>"""
    return HTMLResponse(html)


@app.get("/leads.csv")
async def leads_csv(request: Request):
    denied = _leads_auth(request)
    if denied:
        return denied
    leads = _fetch_leads()
    buf = io.StringIO()
    buf.write("﻿")  # BOM pro Excel BR abrir certo
    w = csv.writer(buf, delimiter=";")
    w.writerow(["id", "data_hora_utc", "nome", "whatsapp", "email", "pedido",
                "total_reais", "utm_source", "utm_medium", "utm_campaign", "gclid", "pagina", "ip"])
    for l in leads:
        m = l["meta"]
        w.writerow([l["id"], l["ts"], l["name"], l["phone"], l["email"], l["pedido"],
                    f"{l['total'] / 100:.2f}".replace(".", ","),
                    m.get("utm_source", ""), m.get("utm_medium", ""), m.get("utm_campaign", ""),
                    m.get("gclid", ""), m.get("page", ""), l["ip"]])
    buf.seek(0)
    return StreamingResponse(iter([buf.getvalue()]), media_type="text/csv; charset=utf-8",
                             headers={"Content-Disposition": "attachment; filename=leads-hyu.csv"})
