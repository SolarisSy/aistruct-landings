"""
HYU (hyuoficial.com) -> Paggins SDK bridge + CAPTURA DE LEADS (paliativo).

O site e estatico (nginx); este servico:
  1. cria checkout sessions na Paggins (SDK) p/ o carrinho multi-item (sabores + combos).
     Fisico+frete confirmado funcionando 15/06 (scripts/paggins_gate.py; frete GRATIS auto).
  2. confirma pagamento: GET /session (status) + POST /webhook/paggins (eventos -> tabela orders).
  3. captura LEADS (paliativo p/ carrinho abandonado): form -> SQLite no volume da VPS.

POST /frete            <- {cep, items} -> opcoes de frete Mandaê (gratis se >=12 latas)
POST /checkout         <- carrinho {items:[{flavor,tier,qty}|{combo,qty}], meta} -> session
                          + fluxo completo: {customer, address, shipping} -> frete embutido
                          no unitAmount + pedido completo salvo (dados p/ NF-e no Bling)
GET  /session/{id}     <- status do pedido (pro obrigado)
POST /webhook/paggins  <- eventos Paggins (assinatura HMAC) -> marca orders/pedidos pagos
                          -> dispara criacao do pedido de venda no Bling (bling.py)
GET  /login /logout    <- login do painel (cookie HMAC 7d; senha LEADS_PASSWORD)
GET  /pedidos          <- painel HTML dos COMPRADORES (cookie ou Basic) + status Bling
                          (aba "Aguardando" = carrinho abandonado com dados completos;
                          o antigo /leads foi aposentado 06/07 — historico exportado)
GET  /pedidos.xlsx     <- export Excel (openpyxl, mesma auth)
GET  /pedidos.csv      <- export CSV (mesma auth)
POST /pedidos/{id}/bling <- re-tenta criar o pedido no Bling (mesma auth)
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
  MANDAE_TOKEN     - token da API Mandaê (cálculo de frete) — setar no Easypanel
  MANDAE_CUSTOMER_ID - customerId Mandaê (referência; envio é via Bling)
  BLING_CLIENT_ID / BLING_CLIENT_SECRET / BLING_REFRESH_TOKEN - ver bling.py
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

import asyncio
import time

import httpx
from fastapi import FastAPI, HTTPException, Request, Response
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import HTMLResponse, StreamingResponse

from bling import BlingClient, BlingError

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
MANDAE_TOKEN = os.environ.get("MANDAE_TOKEN", "").strip()
MANDAE_RATES_URL = "https://api.mandae.com.br/v2/postalcodes/{cep}/rates"
FREE_SHIPPING_CANS = 12          # política: frete grátis a partir de 12 latas
BLING = BlingClient()


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

app = FastAPI(title="HYU cart -> Paggins bridge", version="1.2.0")
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
                        "short": "Hot Lemon", "code": "HL",
                        "desc": "15g de proteína · 3g de fibras · zero açúcar · 269ml"},
    "maca-verde":      {"name": "HYU Soda Protein Maçã Verde", "sku": "MACAVERDE",
                        "short": "Maçã Verde", "code": "MV",
                        "desc": "15g de proteína · 3g de fibras · zero açúcar · 269ml"},
    "pessego-morango": {"name": "HYU Soda Protein Pêssego com Morango", "sku": "PESSEGOMORANGO",
                        "short": "Pêssego com Morango", "code": "PM",
                        "desc": "15g de proteína · 3g de fibras · zero açúcar · 269ml"},
    "tropical":        {"name": "HYU Energy Protein Tropical", "sku": "TROPICAL",
                        "short": "Tropical", "code": "TP",
                        "desc": "15g de proteína · 85mg de cafeína natural · zero açúcar · 269ml"},
    "maca-vermelha":   {"name": "HYU Energy Protein Maçã Vermelha", "sku": "MACAVERMELHA",
                        "short": "Maçã Vermelha", "code": "MA",
                        "desc": "15g de proteína · 85mg de cafeína natural · zero açúcar · 269ml"},
}
TIERS: dict[str, dict[str, Any]] = {
    "kit6":  {"label": "Kit 6 (6 latas)",   "cents": 6990,  "sku": "K6",  "cans": 6},
    "kit12": {"label": "Kit 12 (12 latas)", "cents": 11990, "sku": "K12", "cans": 12},
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
# ── kit PERSONALIZADO (mix de sabores lata a lata) ───────────────────────────
# Item do carrinho: {mix:{slug:latas,...}, tier, qty}. A soma das latas DEVE fechar
# o kit (6 ou 12). Preço = kit + taxa de personalização. Mix de 1 sabor só colapsa
# pra kit normal SEM taxa. A composição vai no NAME (único campo que a Paggins
# renderiza) e no SKU (fulfillment lê a receita: HYU-MIXK6-MA3TP2HL1).
# productId: reusa um produto Paggins existente (name/unitAmount são sobrescritos
# por sessão — mesmo padrão provado do cupom/frete embutido). Trocar por produto
# dedicado quando criado no painel: MIX_PRODUCT_ID_KIT6 / MIX_PRODUCT_ID_KIT12.
MIX_FEE_CENTS = int(os.environ.get("MIX_FEE_CENTS", "490"))   # R$4,90 por kit — manter = front (cart.hyumix)
MIX_PRODUCT_IDS: dict[str, str] = {
    "kit6":  os.environ.get("MIX_PRODUCT_ID_KIT6",
                            "62ed2805-089e-454f-afa0-f28f6dfa6abc"),   # HYU Kit Soda (6)
    "kit12": os.environ.get("MIX_PRODUCT_ID_KIT12",
                            "972ac99f-2800-4db9-88af-d34246cbd3ed"),   # HYU Super Kit (12)
}

# combos = produtos Paggins proprios (1 productId cada). Assinaturas NAO entram (links fixos).
COMBOS: dict[str, dict[str, Any]] = {
    "kit-energy": {"productId": "5287a35b-9a4c-4307-a929-599ab5e2791d", "cents": 6990,
                   "name": "HYU Kit Energy (6 latas)", "sku": "KITENERGY", "img": "kit-energy",
                   "cans": 6},
    "kit-soda":   {"productId": "62ed2805-089e-454f-afa0-f28f6dfa6abc", "cents": 6990,
                   "name": "HYU Kit Soda (6 latas)", "sku": "KITSODA", "img": "kit-soda",
                   "cans": 6},
    "super-kit":  {"productId": "972ac99f-2800-4db9-88af-d34246cbd3ed", "cents": 11990,
                   "name": "HYU Super Kit (12 latas)", "sku": "SUPERKIT", "img": "super-kit",
                   "cans": 12},
    "kit24":      {"productId": "35818f74-5267-46d6-98f7-c19badd7cea7", "cents": 21990,
                   "name": "HYU Kit 24 (24 latas)", "sku": "KIT24", "img": "super-kit",
                   "cans": 24},
}

# ── frete Mandaê ──────────────────────────────────────────────────────────────
# Peso/caixa por faixa de latas (lata 269ml cheia ~300g + caixa). Kit 6 cai na
# faixa 1501-2000g da tabela Mandaê (planilha FontesLog). Limites Mandaê:
# 120cm/lado, 50kg, valor declarado R$5.000.
def _package_for(cans: int) -> dict[str, float]:
    if cans <= 6:
        return {"weight": 1.9, "height": 13, "width": 17, "length": 25}
    if cans <= 12:
        return {"weight": 3.8, "height": 14, "width": 25, "length": 33}
    if cans <= 24:
        return {"weight": 7.5, "height": 15, "width": 33, "length": 40}
    return {"weight": min(round(0.31 * cans + 0.3, 1), 50.0),
            "height": 30, "width": 33, "length": 40}


_FRETE_CACHE: dict[tuple, tuple[float, list[dict[str, Any]]]] = {}
_FRETE_TTL = 600  # 10min


async def _mandae_rates(cep: str, cans: int, declared_cents: int) -> list[dict[str, Any]]:
    """Cota na Mandaê; retorna [{service, name, days, cents}] (Econômico/Rápido)."""
    if not MANDAE_TOKEN:
        raise HTTPException(503, "frete indisponivel (MANDAE_TOKEN ausente)")
    key = (cep, min(cans, 48), min(declared_cents // 10000, 50))
    hit = _FRETE_CACHE.get(key)
    if hit and time.time() - hit[0] < _FRETE_TTL:
        return hit[1]
    body = dict(_package_for(cans))
    body["declaredValue"] = min(declared_cents / 100, 5000.0)
    try:
        async with httpx.AsyncClient(timeout=15) as c:
            r = await c.post(MANDAE_RATES_URL.format(cep=cep),
                             headers={"Authorization": MANDAE_TOKEN,
                                      "Content-Type": "application/json"},
                             json=body)
    except httpx.HTTPError as e:
        raise HTTPException(502, f"mandae indisponivel: {str(e)[:80]}")
    if r.status_code != 200:
        log.error("mandae %s cep=%s: %s", r.status_code, cep, r.text[:200])
        raise HTTPException(502, "cotacao de frete falhou")
    services = (r.json() or {}).get("shippingServices") or []
    options = []
    for s in services:
        name = str(s.get("name", "")).strip()
        slug = ("economico" if "econ" in name.lower()
                else "rapido" if "ráp" in name.lower() or "rap" in name.lower()
                else name.lower()[:20])
        options.append({"service": slug, "name": name,
                        "days": int(s.get("days") or 0),
                        "cents": int(round(float(s.get("price") or 0) * 100))})
    if not options:
        raise HTTPException(502, "mandae sem opcoes p/ este CEP")
    _FRETE_CACHE[key] = (time.time(), options)
    if len(_FRETE_CACHE) > 500:  # poda simples
        for k in list(_FRETE_CACHE)[:100]:
            _FRETE_CACHE.pop(k, None)
    return options

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
    conn.execute(
        "CREATE TABLE IF NOT EXISTS pedidos ("
        "id INTEGER PRIMARY KEY AUTOINCREMENT, ts TEXT, order_id TEXT, session_id TEXT, "
        "status TEXT, paid_ts TEXT, "
        "name TEXT, document TEXT, email TEXT, phone TEXT, "
        "cep TEXT, street TEXT, number TEXT, complement TEXT, neighborhood TEXT, "
        "city TEXT, state TEXT, "
        "items TEXT, subtotal_cents INTEGER, frete_cents INTEGER, frete_service TEXT, "
        "total_cents INTEGER, coupon TEXT, meta TEXT, "
        "bling_status TEXT, bling_order_id TEXT, bling_error TEXT, tracking TEXT)"
    )
    return conn


def _cart_lines(raw_items: Any) -> list[dict[str, Any]]:
    """Valida/mescla o carrinho; retorna linhas normalizadas com preco do catalogo:
    [{qty, sku, name, cents, cans, productId, img, desc?}] (cents = unitario)."""
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
        mix = it.get("mix")
        if combo is not None:
            if combo not in COMBOS:
                raise HTTPException(400, f"combo desconhecido: {combo}")
            key: tuple = ("combo", combo)
        elif mix is not None:
            tier = it.get("tier")
            if tier not in TIERS:
                raise HTTPException(400, f"tier de mix desconhecido: {tier}")
            if not isinstance(mix, dict) or not mix:
                raise HTTPException(400, "mix invalido")
            counts: dict[str, int] = {}
            for slug, n in mix.items():
                if slug not in FLAVORS:
                    raise HTTPException(400, f"sabor desconhecido no mix: {slug}")
                try:
                    n = int(n)
                except (TypeError, ValueError):
                    raise HTTPException(400, "mix com quantidade invalida")
                if n < 1:
                    raise HTTPException(400, "mix com quantidade invalida")
                counts[slug] = counts.get(slug, 0) + n
            if sum(counts.values()) != TIERS[tier]["cans"]:
                raise HTTPException(
                    400, f"mix nao fecha o kit: soma {sum(counts.values())} != "
                         f"{TIERS[tier]['cans']} latas ({tier})")
            if len(counts) == 1:
                # 1 sabor só = kit normal (sem taxa de personalização)
                key = ("flavor", next(iter(counts)), tier)
            else:
                key = ("mix", tier, tuple(sorted(counts.items())))
        else:
            flavor, tier = it.get("flavor"), it.get("tier")
            if flavor not in FLAVORS or tier not in TIERS:
                raise HTTPException(400, f"combinacao desconhecida: {flavor}/{tier}")
            key = ("flavor", flavor, tier)
        merged[key] = min(merged.get(key, 0) + qty, MAX_QTY)
    if len(merged) > MAX_LINES:
        raise HTTPException(400, f"mais de {MAX_LINES} combinacoes")
    lines = []
    for key, qty in merged.items():
        if key[0] == "combo":
            c = COMBOS[key[1]]
            lines.append({"qty": qty, "sku": f"HYU-{c['sku']}", "name": c["name"],
                          "cents": c["cents"], "cans": c["cans"] * qty,
                          "productId": c["productId"], "img": c["img"]})
        elif key[0] == "mix":
            _, tier, comp = key
            t = TIERS[tier]
            # composição: mais latas primeiro (desempate por nome estável)
            ordered = sorted(comp, key=lambda kv: (-kv[1], kv[0]))
            comp_txt = " + ".join(f"{n} {FLAVORS[s]['short']}" for s, n in ordered)
            kit_lbl = t["label"].split(" (")[0]          # "Kit 6"
            lines.append({
                "qty": qty,
                "sku": "HYU-MIX" + t["sku"] + "-"
                       + "".join(f"{FLAVORS[s]['code']}{n}" for s, n in ordered),
                "name": f"HYU {kit_lbl} Personalizado — {comp_txt}",
                "cents": t["cents"] + MIX_FEE_CENTS,
                "cans": t["cans"] * qty,
                "productId": MIX_PRODUCT_IDS[tier],
                "img": "super-kit",
                "desc": "Kit montado lata a lata pelo cliente "
                        "(taxa de personalização inclusa)",
            })
        else:
            _, flavor, tier = key
            f, t = FLAVORS[flavor], TIERS[tier]
            lines.append({"qty": qty, "sku": f"HYU-{f['sku']}-{t['sku']}",
                          "name": f"{f['name']} — {t['label']}", "cents": t["cents"],
                          "cans": t["cans"] * qty,
                          "productId": PRODUCT_IDS[(flavor, tier)],
                          "img": flavor, "desc": f["desc"]})
    return lines


def _build_items(lines: list[dict[str, Any]]) -> list[dict[str, Any]]:
    """Monta os items da Paggins a partir das linhas normalizadas."""
    items = []
    for ln in lines:
        base = {
            "productId": ln["productId"], "name": ln["name"], "type": "physical",
            "unitAmount": ln["cents"], "quantity": 1, "sku": ln["sku"],
            "imageUrl": f"{SITE_BASE}/img/kits/{ln['img']}.webp",
        }
        if ln.get("desc"):
            base["description"] = ln["desc"]
        # ⚠️ SDK Paggins exige quantity=1 por item → duplica a entrada `qty` vezes
        items.extend(dict(base) for _ in range(ln["qty"]))
    return items


def _build_metadata(raw_meta: Any) -> dict[str, str]:
    if not isinstance(raw_meta, dict):
        return {}
    return {k: str(raw_meta[k])[:200] for k in META_KEYS if raw_meta.get(k)}


def _valid_cpf(doc: str) -> bool:
    d = re.sub(r"\D", "", doc)
    if len(d) != 11 or d == d[0] * 11:
        return False
    for n in (9, 10):
        s = sum(int(d[i]) * ((n + 1) - i) for i in range(n))
        dv = (s * 10) % 11 % 10
        if dv != int(d[n]):
            return False
    return True


def _parse_customer_address(payload: dict) -> tuple[dict, dict] | tuple[None, None]:
    """Extrai/valida customer+address do fluxo completo; (None, None) = fluxo legado.

    Passo-1 enxuto (desde 07/07): o site coleta só nome/celular/e-mail + CEP.
    CPF e endereço completo são OPCIONAIS aqui — a Paggins coleta no passo 2 e o
    webhook enriquece o pedido (GET session) pra NF-e. Payload antigo (com tudo)
    continua aceito."""
    cust, addr = payload.get("customer"), payload.get("address")
    if not (isinstance(cust, dict) and isinstance(addr, dict) and addr.get("cep")):
        return None, None
    name = str(cust.get("name", "")).strip()[:120]
    document = re.sub(r"\D", "", str(cust.get("document", "")))
    email = str(cust.get("email", "")).strip()[:160].lower()
    phone = re.sub(r"\D", "", str(cust.get("phone", "")))[:13]
    if len(name.split()) < 2:
        raise HTTPException(400, "nome completo obrigatorio")
    if document and not _valid_cpf(document):
        raise HTTPException(400, "CPF invalido")
    if not _EMAIL_RE.match(email):
        raise HTTPException(400, "e-mail invalido")
    if phone and not _PHONE_RE.match(phone):
        raise HTTPException(400, "whatsapp invalido (DDD + numero)")
    cep = re.sub(r"\D", "", str(addr.get("cep", "")))
    if len(cep) != 8:
        raise HTTPException(400, "CEP invalido")
    street = str(addr.get("street", "")).strip()[:120]
    number = str(addr.get("number", "")).strip()[:12]
    city = str(addr.get("city", "")).strip()[:80]
    state = str(addr.get("state", "")).strip()[:2].upper()
    if (street or number or city or state) and not (street and number and city and len(state) == 2):
        raise HTTPException(400, "endereco incompleto (rua/numero/cidade/UF)")
    return (
        {"name": name, "document": document, "email": email, "phone": phone},
        {"cep": cep, "street": street, "number": number,
         "complement": str(addr.get("complement", "")).strip()[:100],
         "neighborhood": str(addr.get("neighborhood", "")).strip()[:80],
         "city": city, "state": state},
    )


@app.post("/frete")
async def cotar_frete(request: Request):
    """Cotação Mandaê pro carrinho: {cep, items} -> opções (grátis se >=12 latas)."""
    try:
        payload = await request.json()
    except Exception:
        raise HTTPException(400, "JSON invalido")
    cep = re.sub(r"\D", "", str(payload.get("cep", "")))
    if len(cep) != 8:
        raise HTTPException(400, "CEP invalido")
    lines = _cart_lines(payload.get("items"))
    cans = sum(ln["cans"] for ln in lines)
    subtotal = sum(ln["cents"] * ln["qty"] for ln in lines)
    free = cans >= FREE_SHIPPING_CANS
    try:
        options = await _mandae_rates(cep, cans, subtotal)
        if free:
            options = [{**o, "cents": 0} for o in options]
    except HTTPException:
        # frete GRÁTIS (>=12 latas) não depende da Mandaê — não deixa a cotação
        # indisponível travar a loja. Kit 6 avulso (pago) ainda exige a Mandaê.
        if not free:
            raise
        options = [{"service": "economico", "name": "Frete Grátis",
                    "days": 7, "cents": 0}]
    return {"cep": cep, "cans": cans, "free": free, "options": options}


@app.post("/checkout")
async def create_checkout(request: Request):
    STATS["received"] += 1
    try:
        payload = await request.json()
    except Exception:
        STATS["bad_request"] += 1
        raise HTTPException(400, "JSON invalido")

    try:
        lines = _cart_lines(payload.get("items"))
        customer, address = _parse_customer_address(payload)
    except HTTPException:
        STATS["bad_request"] += 1
        raise
    items = _build_items(lines)
    subtotal = sum(ln["cents"] * ln["qty"] for ln in lines)
    cans = sum(ln["cans"] for ln in lines)

    # cupom de influencer → desconta o unitAmount de cada item (centavos, arredonda)
    coupon_code, discount_pct = _coupon_discount(payload.get("coupon"))
    if discount_pct:
        # half-up em centavos inteiros — bate exatamente com o display do front (JS Math.round)
        # + selo no NOME (único campo que renderiza no resumo da Paggins; description/linha
        #   negativa/item R$0 são rejeitados) p/ o cliente ver que o desconto já está aplicado.
        #   O SKU permanece limpo (fulfillment lê o SKU).
        tag = f" · {discount_pct}% OFF cupom {coupon_code}"
        for it in items:
            it["unitAmount"] = max(1, (it["unitAmount"] * (100 - discount_pct) + 50) // 100)
            it["name"] = (str(it.get("name", ""))[:255 - len(tag)] + tag)

    # fluxo completo (pré-checkout do site): re-cota o frete server-side (não confia
    # no front) e EMBUTE no unitAmount do 1º item — a SDK Paggins não tem frete
    # dinâmico (linha separada/R$0 é rejeitada). Grátis a partir de 12 latas.
    frete_cents, frete_service = 0, ""
    if customer:
        if cans >= FREE_SHIPPING_CANS:
            frete_service = "gratis"
        else:
            options = await _mandae_rates(address["cep"], cans, subtotal)
            wanted = str((payload.get("shipping") or {}).get("service") or "economico")
            opt = next((o for o in options if o["service"] == wanted), options[0])
            frete_cents, frete_service = opt["cents"], opt["service"]
            if frete_cents:
                tag_f = f" · inclui frete R$ {frete_cents / 100:.2f}".replace(".", ",")
                items[0]["unitAmount"] += frete_cents
                items[0]["name"] = str(items[0]["name"])[:255 - len(tag_f)] + tag_f

    order_id = f"hyu-{uuid.uuid4().hex[:12]}"
    origin = str(payload.get("origin") or "").rstrip("/")
    base = origin if origin in HYU_ORIGINS else SITE_BASE  # volta pro domínio de origem
    # ⚠️ a SDK Paggins passou a EXIGIR customer.email valido (24/06; ainda "optional" na doc) —
    # sem ele toda sessao volta 400 "Dados da requisicao sao invalidos". No fluxo legado o
    # site nao coleta email antes do checkout → placeholder editavel na pagina da Paggins.
    # No fluxo completo o pré-checkout coleta o email real (validado).
    cust_email = str((customer or payload.get("customer") or {}).get("email") or "").strip()
    if not _EMAIL_RE.match(cust_email):
        cust_email = PLACEHOLDER_EMAIL
    pag_customer: dict[str, str] = {"email": cust_email}
    if customer:
        pag_customer["name"] = customer["name"]
        # ⚠️ phone NÃO vai no customer: o create-session RECUSA (400) em qualquer
        # formato (provado 07/07, scripts/paggins_phone_probe.py — a doc mente).
        # O prefill do telefone vai por query param no checkoutUrl (doc
        # "Parâmetros da URL de Checkout", 07/07).
    body = {
        "currency": "BRL",
        "items": items,
        # ⚠️ SEMPRE true p/ produto físico. Com false a Paggins CRIA a sessão mas
        # RECUSA o pagamento ("Endereço de entrega é obrigatório quando há produtos
        # físicos") — é o "beco sem saída" da lesson paggins.md. O cliente reconfirma
        # o endereço na Paggins; nós já temos o dado salvo p/ a NF-e no Bling.
        "requireShippingInfo": True,
        # ref = nosso order_id (SEMPRE correto). session_id usa o placeholder da
        # Paggins ({CHECKOUT_SESSION_ID}) — se ela não substituir, a página usa a ref.
        "successUrl": f"{base}/obrigado/?ref={order_id}&session_id={{CHECKOUT_SESSION_ID}}",
        "cancelUrl": f"{base}/?checkout=cancelado",
        "externalOrderId": order_id,
        "customer": pag_customer,
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
                if customer:
                    # pedido completo (dados p/ NF-e) — casado depois pelo webhook
                    conn = _db()
                    try:
                        conn.execute(
                            "INSERT INTO pedidos (ts, order_id, session_id, status, "
                            "name, document, email, phone, cep, street, number, "
                            "complement, neighborhood, city, state, items, "
                            "subtotal_cents, frete_cents, frete_service, total_cents, "
                            "coupon, meta, bling_status) "
                            "VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
                            (datetime.now(timezone.utc).isoformat(timespec="seconds"),
                             order_id, sess.get("id"), "created",
                             customer["name"], customer["document"],
                             customer["email"], customer["phone"],
                             address["cep"], address["street"], address["number"],
                             address["complement"], address["neighborhood"],
                             address["city"], address["state"],
                             json.dumps([{k: ln[k] for k in
                                          ("sku", "name", "qty", "cents", "cans")}
                                         for ln in lines], ensure_ascii=False),
                             subtotal, frete_cents, frete_service, total,
                             coupon_code, json.dumps(metadata, ensure_ascii=False),
                             ""))
                        conn.commit()
                    finally:
                        conn.close()
                log.info("checkout %s -> %s total=%s frete=%s/%s coupon=%s", order_id,
                         sess.get("id"), total, frete_service or "-", frete_cents,
                         coupon_code or "-")
                # prefill do telefone no passo 2 via query param (?phone=+55…)
                checkout_url = sess.get("checkoutUrl") or ""
                ph = re.sub(r"\D", "", (customer or {}).get("phone") or "")
                if checkout_url and 10 <= len(ph) <= 11:
                    sep = "&" if "?" in checkout_url else "?"
                    checkout_url += f"{sep}phone=%2B55{ph}"
                return {"checkoutUrl": checkout_url,
                        "sessionId": sess.get("id"), "totalAmount": total,
                        "freteCents": frete_cents, "freteService": frete_service,
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
        pedido_id = None
        conn = _db()
        try:
            conn.execute(
                "INSERT INTO orders (ts, session_id, order_id, event, status, amount_cents, verified, payload) "
                "VALUES (?,?,?,?,?,?,?,?)",
                (ts, sid, order_id, event, "paid", pay.get("amount"), int(verified),
                 json.dumps(ev, ensure_ascii=False)[:4000]))
            if sid:
                row = conn.execute(
                    "SELECT id FROM pedidos WHERE session_id=? ORDER BY id DESC LIMIT 1",
                    (sid,)).fetchone()
                if row:
                    pedido_id = row[0]
                    conn.execute(
                        "UPDATE pedidos SET status='paid', paid_ts=? WHERE id=?",
                        (ts, pedido_id))
            conn.commit()
        finally:
            conn.close()
        STATS["webhook_paid"] += 1
        log.info("webhook PAGO(verified=%s): %s session=%s order=%s amount=%s pedido=%s",
                 verified, event, sid, order_id, pay.get("amount"), pedido_id)
        if pedido_id:
            asyncio.create_task(_enrich_then_bling(pedido_id, sid))
    return {"received": True}


async def _enrich_then_bling(pid: int, sid: str) -> None:
    await _enrich_from_paggins(pid, sid)
    await _bling_dispatch(pid)


async def _enrich_from_paggins(pid: int, sid: str) -> None:
    """Completa CPF/telefone/endereço do pedido com o que a Paggins devolver na
    sessão pós-pagamento. Necessário desde o passo-1 enxuto (07/07): o site só
    coleta contato+CEP; o comprador digita CPF e endereço na página da Paggins.
    Loga o JSON cru da sessão (prova de campo: o que a Paggins realmente expõe)."""
    p = _pedido_get(pid)
    if not p or not sid:
        return
    missing = [k for k in ("document", "street", "number", "city", "state")
               if not str(p.get(k) or "").strip()]
    if not missing:
        return
    try:
        async with httpx.AsyncClient(timeout=20) as c:
            r = await c.get(
                f"{PAGGINS_API_URL}/v1/sdk/checkout-sessions/{sid}?countryCode=BR",
                headers={"Authorization": f"Bearer {PAGGINS_API_KEY}"})
    except httpx.HTTPError as e:
        log.warning("enrich pedido %s: paggins indisponivel (%s)", pid, str(e)[:80])
        return
    if r.status_code != 200:
        log.warning("enrich pedido %s: GET session %s -> %s", pid, sid, r.status_code)
        return
    s = r.json()
    log.info("enrich RAW session %s: %s", sid, json.dumps(s, ensure_ascii=False)[:1800])
    cust = s.get("customer") or {}
    addr = (s.get("shippingAddress") or cust.get("shippingAddress")
            or (s.get("shippingInfo") or {}).get("address") or {})
    updates: dict[str, str] = {}

    def put(col: str, *vals: Any, digits: bool = False, maxlen: int = 120) -> None:
        if str(p.get(col) or "").strip():
            return
        for v in vals:
            v = str(v or "").strip()
            if digits:
                v = re.sub(r"\D", "", v)
            if v:
                updates[col] = v[:maxlen]
                return

    put("document", cust.get("document"), cust.get("cpf"), digits=True, maxlen=14)
    put("phone", cust.get("phone"), cust.get("phoneNumber"), digits=True, maxlen=13)
    put("cep", addr.get("zipCode"), addr.get("cep"), digits=True, maxlen=8)
    put("street", addr.get("street"), addr.get("address"))
    put("number", addr.get("number"), maxlen=12)
    put("complement", addr.get("complement"), maxlen=100)
    put("neighborhood", addr.get("neighborhood"), maxlen=80)
    put("city", addr.get("city"), maxlen=80)
    put("state", addr.get("state"), addr.get("uf"), maxlen=2)
    if updates:
        _pedido_set(pid, **updates)
        log.info("enrich: pedido %s completado da Paggins: %s", pid, sorted(updates))
    else:
        log.warning("enrich: sessao %s sem dados novos (faltando %s)", sid, missing)


# ═══════════════════ Bling (pedido de venda pós-pagamento) ═══════════════════

def _pedido_get(pid: int) -> dict[str, Any] | None:
    conn = _db()
    try:
        conn.row_factory = sqlite3.Row
        row = conn.execute("SELECT * FROM pedidos WHERE id=?", (pid,)).fetchone()
    finally:
        conn.close()
    return dict(row) if row else None


def _pedido_set(pid: int, **cols: Any) -> None:
    conn = _db()
    try:
        sets = ", ".join(f"{k}=?" for k in cols)
        conn.execute(f"UPDATE pedidos SET {sets} WHERE id=?", (*cols.values(), pid))
        conn.commit()
    finally:
        conn.close()


async def _bling_dispatch(pid: int) -> None:
    """Cria contato+pedido de venda no Bling p/ um pedido pago. Falha → bling_status
    'error'/'pending' (re-tenta pelo painel POST /pedidos/{id}/bling)."""
    p = _pedido_get(pid)
    if not p or p.get("bling_status") == "created":
        return
    faltam = [k for k in ("document", "street", "number", "city", "state")
              if not str(p.get(k) or "").strip()]
    if faltam:
        _pedido_set(pid, bling_status="pending",
                    bling_error="dados p/ NF-e incompletos: " + ", ".join(faltam)
                    + " (Paggins não devolveu — completar via POST /pedidos/{id}/bling"
                    " com os campos no body; dado está no painel Paggins)")
        log.warning("bling pedido %s aguardando dados: %s", pid, faltam)
        return
    if not BLING.configured:
        _pedido_set(pid, bling_status="pending",
                    bling_error="BLING_* env ausente (rodar bling_oauth_bootstrap)")
        log.warning("bling nao configurado; pedido %s fica pending", pid)
        return
    try:
        contato_id = await BLING.ensure_contato(p)
        bling_id = await BLING.create_pedido(contato_id, {
            "order_id": p["order_id"],
            "customer_name": p["name"],
            "itens": json.loads(p["items"] or "[]"),
            "frete_cents": p["frete_cents"] or 0,
            "frete_service": p["frete_service"] or "",
            "coupon": p["coupon"] or "",
            "address": {k: p[k] for k in
                        ("cep", "street", "number", "complement",
                         "neighborhood", "city", "state")},
        })
        _pedido_set(pid, bling_status="created", bling_order_id=str(bling_id),
                    bling_error="")
        log.info("bling OK: pedido %s -> bling #%s", p["order_id"], bling_id)
    except Exception as e:  # noqa: BLE001 — nunca derrubar o webhook
        _pedido_set(pid, bling_status="error", bling_error=str(e)[:400])
        log.error("bling FALHOU pedido %s: %s", p["order_id"], str(e)[:200])


@app.post("/pedidos/{pid}/bling")
async def bling_retry(pid: int, request: Request):
    """Retry do Bling. Body JSON opcional completa dados do pedido antes de
    disparar (p/ pedidos 'pending' do passo-1 enxuto): document, phone, cep,
    street, number, complement, neighborhood, city, state, name, email."""
    denied = _leads_auth(request)
    if denied:
        return denied
    if not _pedido_get(pid):
        raise HTTPException(404, "pedido nao encontrado")
    try:
        body = await request.json()
    except Exception:
        body = {}
    if isinstance(body, dict) and body:
        allowed = {"document", "phone", "cep", "street", "number", "complement",
                   "neighborhood", "city", "state", "name", "email"}
        updates = {}
        for k in allowed & set(body):
            v = str(body[k] or "").strip()
            if k in ("document", "phone", "cep"):
                v = re.sub(r"\D", "", v)
            if k == "document" and v and not _valid_cpf(v):
                raise HTTPException(400, "CPF invalido")
            if v:
                updates[k] = v[:120]
        if updates:
            _pedido_set(pid, **updates)
    await _bling_dispatch(pid)
    p = _pedido_get(pid)
    return {"id": pid, "bling_status": p["bling_status"],
            "bling_order_id": p["bling_order_id"], "bling_error": p["bling_error"]}


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


# ═══════════════════ AUTH DO PAINEL ═══════════════════
# (o /lead + painel /leads foram APOSENTADOS 06/07 — o site não postava mais
#  leads e a aba "Aguardando" do /pedidos cobre carrinho abandonado com dados
#  completos. Histórico exportado: _tmp/leads-hyu-historico-2026-07-06.xlsx.
#  A tabela `leads` permanece no SQLite como arquivo morto.)

_PHONE_RE = re.compile(r"^\d{10,13}$")
_EMAIL_RE = re.compile(r"^[^@\s]+@[^@\s]+\.[^@\s]+$")


PANEL_COOKIE = "hyu_panel"
PANEL_TTL = 7 * 86400  # sessão do painel: 7 dias


def _cookie_sig(ts: str) -> str:
    return hmac.new(LEADS_PASSWORD.encode(), f"hyu-panel:{ts}".encode(),
                    hashlib.sha256).hexdigest()[:32]


def _cookie_ok(request: Request) -> bool:
    if not LEADS_PASSWORD:
        return False
    raw = request.cookies.get(PANEL_COOKIE, "")
    ts, _, sig = raw.partition(".")
    if not (ts.isdigit() and sig):
        return False
    if time.time() - int(ts) > PANEL_TTL:
        return False
    return hmac.compare_digest(_cookie_sig(ts), sig)


def _leads_auth(request: Request) -> Response | None:
    """Cookie de sessão do painel OU Basic auth (senha LEADS_PASSWORD) — p/ scripts.
    None = autorizado."""
    if not LEADS_PASSWORD:
        return Response("painel desativado (LEADS_PASSWORD nao configurada)", status_code=503)
    if _cookie_ok(request):
        return None
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


def _panel_auth(request: Request, next_path: str) -> Response | None:
    """Auth das PÁGINAS do painel: sem popup do browser — manda pro /login."""
    from fastapi.responses import RedirectResponse
    if not LEADS_PASSWORD:
        return Response("painel desativado (LEADS_PASSWORD nao configurada)", status_code=503)
    if _cookie_ok(request):
        return None
    auth = request.headers.get("authorization", "")
    if auth.startswith("Basic "):
        try:
            dec = base64.b64decode(auth[6:]).decode("utf-8", "replace")
        except Exception:
            dec = ""
        if dec.split(":", 1)[-1] == LEADS_PASSWORD:
            return None
    return RedirectResponse(f"/login?next={next_path}", status_code=303)


_LOGIN_HTML = """<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>HYU — Painel</title>
<style>
 *{{box-sizing:border-box}}
 body{{font:15px/1.5 system-ui,sans-serif;margin:0;min-height:100vh;display:flex;
      align-items:center;justify-content:center;background:#111318;color:#eef0f3}}
 .card{{width:min(92vw,380px);background:#1a1d24;border:1px solid #262a33;border-radius:18px;
       padding:2.2rem 2rem;box-shadow:0 18px 50px rgba(0,0,0,.5)}}
 .logo{{display:flex;align-items:center;gap:.6rem;margin-bottom:1.4rem}}
 .logo .dot{{width:38px;height:38px;border-radius:11px;background:#A8CC30;color:#111318;
            display:flex;align-items:center;justify-content:center;font-weight:900;font-size:1.15rem}}
 .logo b{{font-size:1.12rem;letter-spacing:.02em}}
 .logo span{{display:block;font-size:.74rem;color:#8b93a1;font-weight:400}}
 label{{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;
       letter-spacing:.07em;color:#8b93a1;margin:0 0 .4rem}}
 .pw{{position:relative}}
 input[type=password],input[type=text]{{width:100%;padding:.85rem 3rem .85rem 1rem;font-size:1.05rem;
      background:#111318;color:#eef0f3;border:1px solid #333947;border-radius:12px;outline:none}}
 input:focus{{border-color:#A8CC30;box-shadow:0 0 0 3px rgba(168,204,48,.18)}}
 .eye{{position:absolute;right:.5rem;top:50%;transform:translateY(-50%);border:0;background:none;
      color:#8b93a1;cursor:pointer;font-size:1.1rem;padding:.4rem}}
 button.go{{width:100%;margin-top:1.1rem;padding:.9rem;font-size:1rem;font-weight:800;
      background:#A8CC30;color:#111318;border:0;border-radius:12px;cursor:pointer}}
 button.go:hover{{filter:brightness(1.08)}}
 .err{{background:#3a1d1f;color:#ff9d9d;border:1px solid #5b2a2e;border-radius:10px;
      padding:.6rem .8rem;font-size:.85rem;margin-bottom:1rem}}
 .hint{{margin-top:1.1rem;font-size:.75rem;color:#6b7280;text-align:center}}
</style></head><body>
<form class="card" method="post" action="/login">
 <div class="logo"><div class="dot">H</div><div><b>HYU · Painel</b><span>pedidos &amp; leads</span></div></div>
 {err}
 <label for="pw">Senha de acesso</label>
 <div class="pw">
  <input id="pw" type="password" name="password" autocomplete="current-password" autofocus required>
  <button class="eye" type="button" onclick="var i=document.getElementById('pw');i.type=i.type==='password'?'text':'password';this.textContent=i.type==='password'?'👁':'🙈'">👁</button>
 </div>
 <input type="hidden" name="next" value="{next}">
 <button class="go" type="submit">Entrar →</button>
 <div class="hint">Sessão fica ativa por 7 dias neste dispositivo.</div>
</form></body></html>"""


@app.get("/login", response_class=HTMLResponse)
async def login_page(request: Request):
    nxt = request.query_params.get("next", "/pedidos")
    if not nxt.startswith("/"):
        nxt = "/pedidos"
    if _cookie_ok(request):
        from fastapi.responses import RedirectResponse
        return RedirectResponse(nxt, status_code=303)
    return HTMLResponse(_LOGIN_HTML.format(err="", next=_esc(nxt)))


@app.post("/login")
async def login_submit(request: Request):
    from fastapi.responses import RedirectResponse
    from urllib.parse import parse_qs
    # form urlencoded parseado na mão (evita dependência python-multipart)
    raw = (await request.body()).decode("utf-8", "replace")
    form = {k: v[0] for k, v in parse_qs(raw, keep_blank_values=True).items()}
    pwd = str(form.get("password") or "")
    nxt = str(form.get("next") or "/pedidos")
    if not nxt.startswith("/"):
        nxt = "/pedidos"
    if not LEADS_PASSWORD:
        return Response("painel desativado", status_code=503)
    if not hmac.compare_digest(pwd, LEADS_PASSWORD):
        await asyncio.sleep(0.7)  # freio anti-bruteforce
        return HTMLResponse(_LOGIN_HTML.format(
            err='<div class="err">Senha incorreta.</div>', next=_esc(nxt)),
            status_code=401)
    ts = str(int(time.time()))
    resp = RedirectResponse(nxt, status_code=303)
    resp.set_cookie(PANEL_COOKIE, f"{ts}.{_cookie_sig(ts)}", max_age=PANEL_TTL,
                    httponly=True, secure=True, samesite="lax", path="/")
    return resp


@app.get("/logout")
async def logout():
    from fastapi.responses import RedirectResponse
    resp = RedirectResponse("/login", status_code=303)
    resp.delete_cookie(PANEL_COOKIE, path="/")
    return resp


def _brl(cents: int) -> str:
    return f"R$ {cents / 100:,.2f}".replace(",", "X").replace(".", ",").replace("X", ".")


def _esc(s: str) -> str:
    return (str(s).replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")
            .replace('"', "&quot;").replace("'", "&#39;"))


# ═══════════════════ PEDIDOS (compradores — painel + Excel) ═══════════════════

def _fetch_pedidos() -> list[dict[str, Any]]:
    conn = _db()
    try:
        conn.row_factory = sqlite3.Row
        rows = conn.execute("SELECT * FROM pedidos ORDER BY id DESC").fetchall()
    finally:
        conn.close()
    out = []
    for r in rows:
        p = dict(r)
        p["itens"] = json.loads(p.get("items") or "[]")
        p["meta_d"] = json.loads(p.get("meta") or "{}")
        out.append(p)
    return out


def _itens_str(itens: list[dict]) -> str:
    return " + ".join(f"{i['qty']}x {i['name']}" if i.get("qty", 1) > 1 else i["name"]
                      for i in itens)


def _endereco_str(p: dict) -> str:
    comp = f" {p['complement']}" if p.get("complement") else ""
    return (f"{p['street']}, {p['number']}{comp} — {p['neighborhood']}, "
            f"{p['city']}/{p['state']} — CEP {p['cep']}")


# SKU ausente (histórico Paggins não tinha SKU cadastrado) → deriva pelo nome
_SKU_FLAVORS = [("hot lemon", "HOTLEMON"), ("maçã verde", "MACAVERDE"),
                ("maca verde", "MACAVERDE"), ("pêssego", "PESSEGOMORANGO"),
                ("pessego", "PESSEGOMORANGO"), ("maçã vermelha", "MACAVERMELHA"),
                ("maca vermelha", "MACAVERMELHA"), ("tropical", "TROPICAL")]
_SKU_COMBOS = [("super kit", "SUPERKIT"), ("kit energy", "KITENERGY"),
               ("kit soda", "KITSODA"), ("kit 24", "KIT24"), ("kit24", "KIT24")]


def _derive_sku(name: str) -> str:
    n = (name or "").lower()
    sub = "assinatura" in n
    for hint, sku in _SKU_COMBOS:
        if hint in n:
            return f"HYU-SUB-{sku}" if sub else f"HYU-{sku}"
    flavor = next((sku for hint, sku in _SKU_FLAVORS if hint in n), "")
    tier = "K12" if ("kit 12" in n or "12 latas" in n) else \
           "K6" if ("kit 6" in n or "6 latas" in n) else ""
    if sub:
        return f"HYU-SUB-{flavor}" if flavor else "HYU-SUB"
    if flavor and tier:
        return f"HYU-{flavor}-{tier}"
    return ""


def _item_sku(it: dict) -> str:
    return it.get("sku") or _derive_sku(it.get("name", ""))


def _skus_str(itens: list[dict]) -> str:
    return " + ".join(f"{i.get('qty', 1)}x {_item_sku(i) or '?'}" for i in itens)


_PEDIDOS_COLS = ["id", "data_utc", "pago_em_utc", "status", "nome", "cpf", "whatsapp",
                 "email", "endereco", "itens", "skus", "frete_servico", "frete_reais",
                 "subtotal_reais", "total_reais", "cupom", "bling_status",
                 "bling_pedido", "rastreio", "utm_source", "utm_campaign", "gclid"]


def _pedido_export_row(p: dict) -> list:
    m = p["meta_d"]
    return [p["id"], p["ts"], p.get("paid_ts") or "", p["status"], p["name"],
            p["document"], p["phone"], p["email"], _endereco_str(p),
            _itens_str(p["itens"]), _skus_str(p["itens"]),
            p.get("frete_service") or "",
            round((p.get("frete_cents") or 0) / 100, 2),
            round((p.get("subtotal_cents") or 0) / 100, 2),
            round((p.get("total_cents") or 0) / 100, 2),
            p.get("coupon") or "", p.get("bling_status") or "",
            p.get("bling_order_id") or "", p.get("tracking") or "",
            m.get("utm_source", ""), m.get("utm_campaign", ""), m.get("gclid", "")]


@app.get("/pedidos", response_class=HTMLResponse)
async def pedidos_panel(request: Request):
    denied = _panel_auth(request, "/pedidos")
    if denied:
        return denied
    pedidos = _fetch_pedidos()
    pagos = [p for p in pedidos if p["status"] == "paid"]
    n_wait = sum(1 for p in pedidos if p["status"] in ("created", "open"))
    n_err = len(pedidos) - len(pagos) - n_wait
    total_pago = sum(p.get("total_cents") or 0 for p in pagos)
    rows = []
    for p in pedidos:
        st = p["status"]
        grp = "paid" if st == "paid" else ("wait" if st in ("created", "open") else "err")
        badge = {"paid": "<span class='ok'>PAGO</span>",
                 "wait": "<span class='pend'>aguardando</span>",
                 "err": f"<span class='err'>{_esc(st)}</span>"}[grp]
        bl = p.get("bling_status") or "—"
        if bl == "created":
            bl = f"<span class='ok'>#{_esc(p.get('bling_order_id') or '')}</span>"
        elif bl in ("error", "pending"):
            bl = (f"<span class='err' title='{_esc(p.get('bling_error') or '')}'>{bl}</span> "
                  f"<button onclick=\"blingRetry({p['id']},this)\">↻</button>")
        frete = ("grátis" if p.get("frete_service") == "gratis"
                 else f"{_esc(p.get('frete_service') or '—')} {_brl(p.get('frete_cents') or 0)}"
                 if p.get("frete_cents") or p.get("frete_service") else "—")
        itens_html = "".join(
            f"<div>{_esc((str(i.get('qty', 1)) + 'x ') if i.get('qty', 1) > 1 else '')}{_esc(i.get('name', '?'))}"
            f"<br><span class='sku'>{_esc(_item_sku(i) or 'sem SKU')}</span></div>"
            for i in p["itens"]) or "—"
        search = " ".join(str(x) for x in (
            p["id"], p["name"], p["document"], p["phone"], p["email"],
            p.get("coupon"), _itens_str(p["itens"]), _skus_str(p["itens"]),
            p.get("city"), p.get("state"), p.get("cep"),
            p["meta_d"].get("paggins_order", ""))).lower()
        rows.append(
            f"<tr data-grp='{grp}' data-q=\"{_esc(search)}\"><td>{p['id']}</td>"
            f"<td data-ts='{p['ts']}'></td>"
            f"<td>{badge}</td>"
            f"<td><strong>{_esc(p['name'])}</strong><br><span class='muted'>{_esc(p['document'])}</span></td>"
            f"<td><a href='https://wa.me/55{_esc(p['phone'])}' target='_blank'>{_esc(p['phone'])}</a><br>"
            f"<a href='mailto:{_esc(p['email'])}'>{_esc(p['email'])}</a></td>"
            f"<td class='addr'>{_esc(_endereco_str(p))}</td>"
            f"<td class='itens'>{itens_html}</td>"
            f"<td>{frete}</td>"
            f"<td class='r'>{_brl(p.get('total_cents') or 0)}</td>"
            f"<td>{_esc(p.get('coupon') or '—')}</td>"
            f"<td>{bl}</td>"
            f"<td>{_esc(p.get('tracking') or '—')}</td></tr>"
        )
    html = f"""<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
<title>Pedidos HYU ({len(pagos)} pagos)</title>
<style>
 body{{font:14px/1.5 system-ui,sans-serif;margin:0;background:#f5f6f8;color:#16191f}}
 header{{display:flex;align-items:center;gap:1rem;padding:.9rem 1.2rem;background:#16191f;color:#fff;position:sticky;top:0;z-index:5}}
 h1{{font-size:1rem;margin:0}} .pill{{background:#A8CC30;color:#16191f;font-weight:800;border-radius:999px;padding:.1rem .6rem}}
 .grow{{flex:1}} a.btn{{background:#fff;color:#16191f;font-weight:700;text-decoration:none;border-radius:8px;padding:.35rem .8rem;margin-left:.4rem;font-size:.85rem}}
 a.out{{background:none;color:#9aa3b0;border:1px solid #3a3f4a}}
 .bar{{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;padding:.8rem 1.2rem 0}}
 .tab{{border:1px solid #d7dbe0;background:#fff;border-radius:999px;padding:.32rem .85rem;font-size:.82rem;font-weight:700;cursor:pointer;color:#445}}
 .tab.on{{background:#16191f;color:#fff;border-color:#16191f}}
 .tab small{{font-weight:800;opacity:.65;margin-left:.25rem}}
 #q{{flex:1;min-width:220px;max-width:380px;padding:.45rem .8rem;border:1px solid #d7dbe0;border-radius:999px;font:inherit;font-size:.88rem}}
 #q:focus{{outline:2px solid #A8CC30;border-color:#A8CC30}}
 main{{padding:.6rem 1.2rem 1.2rem;overflow-x:auto}}
 table{{border-collapse:collapse;width:100%;background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.08)}}
 th,td{{padding:.55rem .7rem;border-bottom:1px solid #eceef1;text-align:left;vertical-align:top;font-size:.82rem}}
 th{{background:#fafbfc;font-size:.7rem;text-transform:uppercase;letter-spacing:.05em;color:#667}}
 tr:hover td{{background:#fcfde8}} .r{{text-align:right;white-space:nowrap;font-weight:700}}
 .muted{{color:#889;font-size:.75rem}} .total{{margin:.6rem 0 .2rem;color:#445;font-size:.85rem}}
 .addr{{max-width:230px;font-size:.76rem;color:#556}}
 .itens div{{margin-bottom:.3rem}} .itens div:last-child{{margin-bottom:0}}
 .sku{{font-family:ui-monospace,monospace;font-size:.7rem;background:#f0f2f5;color:#556;border-radius:5px;padding:.02rem .35rem}}
 .ok{{background:#e3f5d4;color:#3a6b12;font-weight:700;border-radius:6px;padding:.05rem .4rem;font-size:.72rem}}
 .pend{{background:#fff3d6;color:#8a6100;border-radius:6px;padding:.05rem .4rem;font-size:.72rem;font-weight:700}}
 .err{{background:#fde3e3;color:#a11;font-weight:700;border-radius:6px;padding:.05rem .4rem;font-size:.72rem}}
 button{{cursor:pointer;border:1px solid #ccd;border-radius:6px;background:#fff}}
</style></head><body>
<header><h1>📦 Pedidos HYU</h1><span class="pill">{len(pagos)} pagos</span><span class="grow"></span>
<a class="btn" href="/pedidos.xlsx">⬇ Excel</a><a class="btn" href="/pedidos.csv">⬇ CSV</a>
<a class="btn out" href="/logout">Sair</a></header>
<div class="bar">
 <button class="tab on" data-f="all">Todos <small>{len(pedidos)}</small></button>
 <button class="tab" data-f="paid">Pagos <small>{len(pagos)}</small></button>
 <button class="tab" data-f="wait">Aguardando <small>{n_wait}</small></button>
 <button class="tab" data-f="err">Erro/outros <small>{n_err}</small></button>
 <input id="q" type="search" placeholder="Buscar: nome, CPF, e-mail, SKU, cidade, cupom…">
</div>
<main><p class="total">Total pago: <strong>{_brl(total_pago)}</strong> · <span id="showing"></span></p>
<table><thead><tr><th>#</th><th>Quando</th><th>Status</th><th>Cliente / CPF</th><th>Contato</th>
<th>Endereço</th><th>Itens / SKU</th><th>Frete</th><th>Total</th><th>Cupom</th><th>Bling</th><th>Rastreio</th></tr></thead>
<tbody id="tb">{''.join(rows) or '<tr><td colspan=12 style="text-align:center;padding:2rem;color:#889">Nenhum pedido ainda.</td></tr>'}</tbody></table></main>
<script>
document.querySelectorAll('[data-ts]').forEach(td=>{{
 td.textContent=new Date(td.dataset.ts).toLocaleString('pt-BR',{{timeZone:'America/Sao_Paulo',day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'}});
}});
var F='all',Q='';
function apply(){{
 var n=0;
 document.querySelectorAll('#tb tr[data-grp]').forEach(function(tr){{
  var ok=(F==='all'||tr.dataset.grp===F)&&(!Q||tr.dataset.q.indexOf(Q)>-1);
  tr.style.display=ok?'':'none'; if(ok)n++;
 }});
 document.getElementById('showing').textContent='mostrando '+n+' pedido'+(n===1?'':'s');
}}
document.querySelectorAll('.tab').forEach(function(b){{
 b.addEventListener('click',function(){{
  document.querySelectorAll('.tab').forEach(function(x){{x.classList.remove('on')}});
  b.classList.add('on'); F=b.dataset.f; apply();
 }});
}});
document.getElementById('q').addEventListener('input',function(){{Q=this.value.trim().toLowerCase();apply();}});
apply();
function blingRetry(id,btn){{btn.disabled=true;btn.textContent='…';
 fetch('/pedidos/'+id+'/bling',{{method:'POST'}}).then(r=>r.json())
 .then(j=>{{btn.textContent=j.bling_status==='created'?'✅':'↻';alert('Bling: '+j.bling_status+(j.bling_error?' — '+j.bling_error:''));location.reload();}})
 .catch(()=>{{btn.disabled=false;btn.textContent='↻';}});}}
</script></body></html>"""
    return HTMLResponse(html)


@app.post("/pedidos/purge")
async def pedidos_purge(request: Request):
    """Remove pedidos por lista de ids (Basic auth). Body: {"ids":[1,2,...]}.
    Usado p/ limpar pedidos não-HYU (loja Paggins compartilhada) e testes."""
    denied = _leads_auth(request)
    if denied:
        return denied
    try:
        payload = await request.json()
    except Exception:
        raise HTTPException(400, "JSON invalido")
    ids = payload.get("ids")
    if not isinstance(ids, list) or not all(isinstance(i, int) for i in ids) or len(ids) > 2000:
        raise HTTPException(400, "ids invalido (lista de inteiros)")
    conn = _db()
    try:
        cur = conn.executemany("DELETE FROM pedidos WHERE id=?", [(i,) for i in ids])
        conn.commit()
        deleted = cur.rowcount if cur.rowcount is not None else len(ids)
    finally:
        conn.close()
    log.info("pedidos purge: %d ids -> removidos", len(ids))
    return {"requested": len(ids), "deleted": deleted}


@app.post("/pedidos/import")
async def pedidos_import(request: Request):
    """Importa pedidos históricos (ex.: export do painel Paggins). Basic auth.
    Body: {"rows":[{...colunas de pedidos...}]}. Dedupe por order_id e session_id.
    Importados NÃO disparam Bling automático (bling_status='')."""
    denied = _leads_auth(request)
    if denied:
        return denied
    try:
        payload = await request.json()
    except Exception:
        raise HTTPException(400, "JSON invalido")
    rows = payload.get("rows")
    if not isinstance(rows, list) or len(rows) > 2000:
        raise HTTPException(400, "rows invalido")
    cols = ["ts", "order_id", "session_id", "status", "paid_ts", "name", "document",
            "email", "phone", "cep", "street", "number", "complement",
            "neighborhood", "city", "state", "items", "subtotal_cents",
            "frete_cents", "frete_service", "total_cents", "coupon", "meta",
            "bling_status", "tracking"]
    ins = skip = 0
    conn = _db()
    try:
        for r in rows:
            if not isinstance(r, dict) or not r.get("order_id"):
                skip += 1
                continue
            dup = conn.execute(
                "SELECT 1 FROM pedidos WHERE order_id=? OR (session_id!='' AND session_id=?) LIMIT 1",
                (str(r["order_id"]), str(r.get("session_id") or ""))).fetchone()
            if dup:
                skip += 1
                continue
            vals = [str(r.get(c) or "") if c not in
                    ("subtotal_cents", "frete_cents", "total_cents")
                    else int(r.get(c) or 0) for c in cols]
            conn.execute(
                f"INSERT INTO pedidos ({', '.join(cols)}) "
                f"VALUES ({', '.join('?' * len(cols))})", vals)
            ins += 1
        conn.commit()
    finally:
        conn.close()
    log.info("pedidos import: %d inseridos, %d pulados", ins, skip)
    return {"inserted": ins, "skipped": skip}


@app.get("/pedidos.csv")
async def pedidos_csv(request: Request):
    denied = _leads_auth(request)
    if denied:
        return denied
    buf = io.StringIO()
    buf.write("﻿")  # BOM pro Excel BR abrir certo
    w = csv.writer(buf, delimiter=";")
    w.writerow(_PEDIDOS_COLS)
    for p in _fetch_pedidos():
        row = _pedido_export_row(p)
        row = [str(v).replace(".", ",") if isinstance(v, float) else v for v in row]
        w.writerow(row)
    buf.seek(0)
    return StreamingResponse(iter([buf.getvalue()]), media_type="text/csv; charset=utf-8",
                             headers={"Content-Disposition": "attachment; filename=pedidos-hyu.csv"})


@app.get("/pedidos.xlsx")
async def pedidos_xlsx(request: Request):
    denied = _leads_auth(request)
    if denied:
        return denied
    from openpyxl import Workbook
    from openpyxl.styles import Font
    wb = Workbook()
    ws = wb.active
    ws.title = "Pedidos HYU"
    ws.append(_PEDIDOS_COLS)
    for c in ws[1]:
        c.font = Font(bold=True)
    for p in _fetch_pedidos():
        ws.append(_pedido_export_row(p))
    widths = {"A": 5, "B": 20, "C": 20, "D": 8, "E": 24, "F": 13, "G": 13, "H": 26,
              "I": 46, "J": 40, "K": 26, "L": 12, "M": 10, "N": 12, "O": 10, "P": 10,
              "Q": 12, "R": 12, "S": 16, "T": 12, "U": 14, "V": 20}
    for col, wd in widths.items():
        ws.column_dimensions[col].width = wd
    out = io.BytesIO()
    wb.save(out)
    out.seek(0)
    return StreamingResponse(
        out,
        media_type="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        headers={"Content-Disposition": "attachment; filename=pedidos-hyu.xlsx"})
