"""
topperpay-bridge — webhook do gateway TopperPay/asegupag -> conversao no Google Ads.

CONTEXTO (funil CRR/roxo do bomb): as ofertas rodam Google Ads com Adspect. O Adspect
captura {p:gclid} e passa adiante (arg_passthru) -> o gclid chega no chat/Typebot e o
Code block do checkout manda `?src=<gclid>&utm_content=<gclid>`.

⚠️ POR QUE utm_content TAMBEM: o webhook "Compra aprovada" do TopperPay tem 15 campos
(paymentId, status, paymentMethod, totalValue, pixCode, customer, offer, product,
utm_campaign, utm_content, utm_medium, utm_source, utm_term, createdAt, updatedAt) e
NENHUM `src` — provado no payload real 27/07/2026. O `src` chega no checkout mas nao
volta no webhook; os utm_* voltam. Entao o click id viaja em utm_content.

Este serviço NAO poe pixel em pagina nenhuma (a money e cloakada; pixel client-side
exporia a money ao Google). Em vez disso faz OFFLINE CONVERSION IMPORT por gclid.

DOIS caminhos de entrega (podem coexistir):
  A) CSV servido por este bridge  -> Google Ads "Importacoes programadas" (HTTPS).
     Nao precisa de Sheet nem Apps Script. Endpoint: GET /conversions.csv?token=...
  B) Google Sheet via Apps Script -> setar GADS_SHEET_WEBHOOK_URL.

Rotas:
  POST /webhook           <- o TopperPay cola aqui (evento "Compra aprovada")
  GET  /conversions.csv   <- CSV no formato do Google Ads (offline import por gclid)
  GET  /healthz  /info    <- saude e config
  GET  /debug/stats       <- contadores (memoria)
  GET  /debug/recent      <- ultimos eventos crus (contem PII do comprador — uso interno)
  GET  /debug/sales       <- vendas persistidas SEM PII (auditoria)

ENV:
  TOPPERPAY_TOKEN        - se setado, exige ?token=<x> (ou header X-Token) no /webhook e no CSV
  GCLID_FIELDS           - CSV de campos onde procurar o click id
                           (default: src,gclid,utm_content,utm_term,utm_src,utm.src,tracking)
  DB_PATH                - default /data/bridge.db (volume). Fallback /tmp/bridge.db.
  CSV_WINDOW_DAYS        - janela do /conversions.csv (default 30). O Google deduplica reimport.
  GADS_SHEET_WEBHOOK_URL - opcional: URL /exec do Apps Script (caminho B)
  GADS_SHEET_SECRET      - secret compartilhado com o Apps Script
  GADS_CONVERSION_NAME   - nome EXATO da conversion action no Google Ads (ex: "Compra")
  GADS_CURRENCY          - default BRL
  TZ_OFFSET              - default -03:00
"""
from __future__ import annotations

import csv
import io
import logging
import os
import re
import sqlite3
import threading
from collections import deque
from datetime import datetime, timedelta, timezone
from typing import Any

import httpx
from fastapi import FastAPI, Request
from fastapi.responses import JSONResponse, PlainTextResponse

logging.basicConfig(level=os.environ.get("LOG_LEVEL", "INFO"),
                    format="%(asctime)s %(levelname)s %(message)s")
log = logging.getLogger("topperpay-bridge")

TOKEN = os.environ.get("TOPPERPAY_TOKEN", "").strip() or None
GCLID_FIELDS = [f.strip() for f in os.environ.get(
    "GCLID_FIELDS",
    "src,gclid,utm_content,utm_term,utm_src,utm.src,tracking").split(",") if f.strip()]
SHEET_URL = os.environ.get("GADS_SHEET_WEBHOOK_URL", "").strip() or None
SHEET_SECRET = os.environ.get("GADS_SHEET_SECRET", "").strip() or None
CONVERSION_NAME = os.environ.get("GADS_CONVERSION_NAME", "Compra").strip()
CURRENCY = os.environ.get("GADS_CURRENCY", "BRL").strip()
TZ_OFFSET = os.environ.get("TZ_OFFSET", "-03:00").strip()
CSV_WINDOW_DAYS = int(os.environ.get("CSV_WINDOW_DAYS", "30"))

# eventos que contam como venda (o TopperPay manda status APPROVED / "Compra aprovada")
PAID_HINTS = re.compile(r"aprovad|approved|paid|pago|compra", re.I)
# click id do Google: longo, alfanumerico + -_ . Nao vazio, nao "None", nao " "
GCLID_RE = re.compile(r"^[A-Za-z0-9_\-\.]{20,}$")

app = FastAPI(title="topperpay-bridge")

STATS = {
    "received": 0,        # POSTs no /webhook
    "paid": 0,            # eventos de compra aprovada
    "with_gclid": 0,      # tinham click id
    "no_gclid": 0,        # sem click id (nao atribui)
    "persisted": 0,       # gravado no sqlite
    "db_error": 0,
    "sheet_sent": 0,      # gravado na Sheet (caminho B)
    "sheet_ok": 0,
    "sheet_error": 0,
    "sheet_disabled": 0,
    "csv_served": 0,      # downloads do CSV (o Google importando)
    "forbidden": 0,
    "ignored": 0,         # evento nao-compra (pix gerado, etc)
}
RECENT: deque = deque(maxlen=40)

# ---------------------------------------------------------------- persistencia
DB_LOCK = threading.Lock()
DB_PATH = os.environ.get("DB_PATH", "/data/bridge.db").strip()

DDL = """
CREATE TABLE IF NOT EXISTS conversions (
  payment_id   TEXT PRIMARY KEY,
  gclid        TEXT,
  amount       REAL,
  currency     TEXT,
  conv_name    TEXT,
  conv_time    TEXT,
  offer_id     TEXT,
  offer_title  TEXT,
  utm_content  TEXT,
  utm_source   TEXT,
  status       TEXT,
  paid         INTEGER DEFAULT 0,
  created_at   TEXT
);
CREATE INDEX IF NOT EXISTS ix_conv_time ON conversions(conv_time);
"""

# migracao: bancos criados antes da coluna 'paid'
MIGRATIONS = ["ALTER TABLE conversions ADD COLUMN paid INTEGER DEFAULT 0"]


def _db() -> sqlite3.Connection | None:
    global DB_PATH
    for path in (DB_PATH, "/tmp/bridge.db"):
        try:
            d = os.path.dirname(path)
            if d:
                os.makedirs(d, exist_ok=True)
            con = sqlite3.connect(path, timeout=10)
            con.executescript(DDL)
            for sql in MIGRATIONS:
                try:
                    con.execute(sql)
                except sqlite3.OperationalError:
                    pass          # coluna ja existe
            con.commit()
            if path != DB_PATH:
                log.warning("DB_PATH %s indisponivel — usando %s (NAO persiste em recreate)",
                            DB_PATH, path)
                DB_PATH = path
            return con
        except Exception as e:  # noqa: BLE001
            log.error("sqlite %s: %s", path, e)
    return None


def _lookup_gclid(payment_id: str) -> str | None:
    """Click id já gravado pra esse pedido (ex.: veio no 'PIX gerado' e o evento de
    aprovação chegou sem tracking)."""
    with DB_LOCK:
        con = _db()
        if con is None:
            return None
        try:
            r = con.execute("SELECT gclid FROM conversions WHERE payment_id = ?",
                            (payment_id,)).fetchone()
            return r[0] if r and r[0] else None
        except Exception:  # noqa: BLE001
            return None
        finally:
            con.close()


def _persist(row: dict) -> None:
    with DB_LOCK:
        con = _db()
        if con is None:
            STATS["db_error"] += 1
            return
        try:
            con.execute(
                """INSERT INTO conversions
                   (payment_id, gclid, amount, currency, conv_name, conv_time, offer_id,
                    offer_title, utm_content, utm_source, status, paid, created_at)
                   VALUES (:payment_id, :gclid, :amount, :currency, :conv_name, :conv_time,
                           :offer_id, :offer_title, :utm_content, :utm_source, :status,
                           :paid, :created_at)
                   ON CONFLICT(payment_id) DO UPDATE SET
                     -- o PIX gerado ja traz os utm_*; se o evento de aprovacao vier sem
                     -- tracking, o click id do pendente salva a atribuicao
                     gclid=COALESCE(excluded.gclid, conversions.gclid),
                     utm_content=COALESCE(excluded.utm_content, conversions.utm_content),
                     amount=excluded.amount,
                     status=excluded.status,
                     paid=MAX(excluded.paid, conversions.paid),
                     -- a hora da conversao e a da APROVACAO, nao a do PIX gerado
                     conv_time=CASE WHEN excluded.paid=1 THEN excluded.conv_time
                                    ELSE conversions.conv_time END""",
                row,
            )
            con.commit()
            STATS["persisted"] += 1
        except Exception as e:  # noqa: BLE001
            STATS["db_error"] += 1
            log.error("persist: %s", e)
        finally:
            con.close()


# ---------------------------------------------------------------- helpers
def _now() -> str:
    return datetime.now(timezone.utc).astimezone().isoformat(timespec="seconds")


def _tz() -> timezone:
    off = TZ_OFFSET.replace(":", "")
    sign = 1 if off.startswith("+") else -1
    hh = int(off[1:3])
    mm = int(off[3:5]) if len(off) >= 5 else 0
    return timezone(sign * timedelta(hours=hh, minutes=mm))


def _conv_time(when: datetime | None = None) -> str:
    """'YYYY-MM-DD HH:MM:SS-03:00' — formato aceito no offline import do Google Ads."""
    dt = (when or datetime.now(timezone.utc)).astimezone(_tz())
    return dt.strftime("%Y-%m-%d %H:%M:%S") + TZ_OFFSET


def _record(outcome: str, **extra) -> None:
    RECENT.appendleft({"at": _now(), "outcome": outcome, **extra})


def _walk_find(data: Any, key: str) -> str | None:
    """Busca recursiva por uma chave (case-insensitive) em dict/list aninhado."""
    kl = key.lower()
    if isinstance(data, dict):
        for k, v in data.items():
            if str(k).lower() == kl and isinstance(v, (str, int, float)) and str(v).strip():
                return str(v).strip()
        for v in data.values():
            r = _walk_find(v, key)
            if r:
                return r
    elif isinstance(data, list):
        for it in data:
            r = _walk_find(it, key)
            if r:
                return r
    return None


def _extract_gclid(payload: dict) -> tuple[str | None, str | None]:
    """Acha o click id e diz em QUAL campo achou (pra saber se o carrier funcionou)."""
    for field in GCLID_FIELDS:
        parts = field.split(".")           # 'utm.src' -> procura 'src'
        v = _walk_find(payload, parts[-1])
        if v and GCLID_RE.match(v) and v.lower() not in ("none", "null", "undefined"):
            return v, field
    return None, None


def _parse_brl(s: Any) -> float | None:
    if s is None:
        return None
    if isinstance(s, (int, float)):
        return float(s)
    txt = re.sub(r"[^\d,.-]", "", str(s))
    if not txt:
        return None
    if "," in txt and "." in txt:      # 1.234,56 -> 1234.56
        txt = txt.replace(".", "").replace(",", ".")
    elif "," in txt:                    # 27,30 -> 27.30
        txt = txt.replace(",", ".")
    try:
        return float(txt)
    except ValueError:
        return None


def _extract_amount(payload: dict) -> float | None:
    for k in ("totalValue", "total_price", "amount", "value", "total", "valor", "price", "net_amount"):
        v = _walk_find(payload, k)
        a = _parse_brl(v)
        if a is not None:
            # TopperPay manda CENTAVOS em totalValue (6790 = R$ 67,90)
            if a >= 1000 and float(a).is_integer():
                a = a / 100.0
            return a
    return None


def _extract_order(payload: dict) -> str | None:
    for k in ("paymentId", "transaction_id", "order_id", "id", "sale_id", "code", "hash"):
        v = _walk_find(payload, k)
        if v:
            return v
    return None


def _is_paid(payload: dict) -> bool:
    for k in ("event", "status", "type", "situacao", "payment_status"):
        v = _walk_find(payload, k)
        if v and PAID_HINTS.search(v):
            return True
    return False


def _send_to_sheet(gclid: str, amount: float | None, order: str | None, conv_time: str) -> None:
    """Caminho B (opcional): Apps Script -> Google Sheet."""
    if not SHEET_URL:
        STATS["sheet_disabled"] += 1
        return
    body = {
        "secret": SHEET_SECRET or "",
        "gclid": gclid,
        "conversion_name": CONVERSION_NAME,
        "conversion_time": conv_time,
        "conversion_value": f"{(amount or 0):.2f}",
        "conversion_currency": CURRENCY,
        "order_id": order or "",
    }
    STATS["sheet_sent"] += 1
    try:
        r = httpx.post(SHEET_URL, json=body, timeout=20, follow_redirects=True)
        if r.status_code < 300:
            STATS["sheet_ok"] += 1
        else:
            STATS["sheet_error"] += 1
            _record("sheet_error", status=r.status_code, body=r.text[:120])
    except Exception as e:  # noqa: BLE001
        STATS["sheet_error"] += 1
        _record("sheet_error", err=str(e)[:120])


def _authorized(request: Request) -> bool:
    if not TOKEN:
        return True
    got = request.query_params.get("token") or request.headers.get("x-token")
    if got == TOKEN:
        return True
    # Google Ads scheduled import aceita usuario/senha (HTTP Basic) — senha = token
    auth = request.headers.get("authorization", "")
    if auth.lower().startswith("basic "):
        import base64
        try:
            raw = base64.b64decode(auth.split(" ", 1)[1]).decode("utf-8", "ignore")
            return raw.split(":", 1)[-1] == TOKEN
        except Exception:  # noqa: BLE001
            return False
    return False


# ---------------------------------------------------------------- rotas
@app.post("/webhook")
async def webhook(request: Request):
    STATS["received"] += 1
    if not _authorized(request):
        STATS["forbidden"] += 1
        _record("forbidden")
        return JSONResponse({"ok": False, "error": "forbidden"}, status_code=403)
    try:
        payload = await request.json()
    except Exception:  # noqa: BLE001
        payload = {"_raw": (await request.body()).decode("utf-8", "ignore")[:2000]}

    paid = _is_paid(payload)
    gclid, found_in = _extract_gclid(payload)
    amount = _extract_amount(payload)
    order = _extract_order(payload)
    conv_time = _conv_time()

    # ⚠️ evento nao-pago (PIX gerado / abandono) NAO vira conversao, mas e guardado:
    #    (a) e o jeito de testar o webhook sem pagar nada;
    #    (b) o PIX gerado ja carrega os utm_* — se o evento de aprovacao vier sem
    #        tracking, o click id do pendente salva a atribuicao.
    _persist({
        "payment_id": order or f"noid-{_now()}",
        "gclid": gclid,
        "amount": amount,
        "currency": CURRENCY,
        "conv_name": CONVERSION_NAME,
        "conv_time": conv_time,
        "offer_id": _walk_find(payload.get("offer") or {}, "id"),
        "offer_title": _walk_find(payload.get("offer") or {}, "title"),
        "utm_content": _walk_find(payload, "utm_content"),
        "utm_source": _walk_find(payload, "utm_source"),
        "status": _walk_find(payload, "status"),
        "paid": 1 if paid else 0,
        "created_at": _now(),
    })

    if not paid:
        STATS["ignored"] += 1
        _record("pending", event=_walk_find(payload, "event"),
                status=_walk_find(payload, "status"),
                gclid=(gclid[:14] + "…") if gclid else None, gclid_field=found_in,
                amount=amount, order=order, keys=list(payload.keys())[:15], raw=payload)
        return {"ok": True, "handled": "nao-pago (guardado, nao vai pro CSV)",
                "status": _walk_find(payload, "status"), "gclid_field": found_in,
                "gclid_tail": gclid[-6:] if gclid else None}

    STATS["paid"] += 1

    # o evento de aprovacao pode vir sem tracking — nesse caso vale o click id que o
    # 'PIX gerado' do MESMO pedido ja gravou (o _persist acima fez o COALESCE)
    if not gclid and order:
        herdado = _lookup_gclid(order)
        if herdado:
            gclid, found_in = herdado, "herdado do evento anterior (PIX gerado)"

    _record("paid", gclid=(gclid[:14] + "…") if gclid else None, gclid_field=found_in,
            amount=amount, order=order, keys=list(payload.keys())[:15], raw=payload)

    if not gclid:
        STATS["no_gclid"] += 1
        log.warning("compra aprovada SEM click id — order=%s amount=%s utm_content=%r",
                    order, amount, _walk_find(payload, "utm_content"))
        return {"ok": True, "handled": "paid_no_gclid", "order": order}

    STATS["with_gclid"] += 1
    _send_to_sheet(gclid, amount, order, conv_time)
    return {"ok": True, "handled": "paid", "gclid_field": found_in,
            "gclid_tail": gclid[-6:], "amount": amount}


@app.get("/conversions.csv")
def conversions_csv(request: Request, days: int | None = None):
    """CSV no formato do Google Ads (Importacoes programadas -> HTTPS).

    Cabecalho oficial do offline click conversion import. O Google deduplica
    reimportacao da mesma (gclid, conversion name, conversion time).
    """
    if not _authorized(request):
        STATS["forbidden"] += 1
        return PlainTextResponse("forbidden", status_code=403)

    window = days or CSV_WINDOW_DAYS
    since = (datetime.now(_tz()) - timedelta(days=window)).strftime("%Y-%m-%d")
    rows: list[tuple] = []
    with DB_LOCK:
        con = _db()
        if con is not None:
            try:
                rows = con.execute(
                    """SELECT gclid, conv_name, conv_time, amount, currency
                       FROM conversions
                       WHERE paid = 1 AND gclid IS NOT NULL AND gclid <> ''
                             AND conv_time >= ?
                       ORDER BY conv_time""",
                    (since,),
                ).fetchall()
            finally:
                con.close()

    buf = io.StringIO()
    w = csv.writer(buf, lineterminator="\n")
    w.writerow([f"Parameters:TimeZone={TZ_OFFSET}"])
    w.writerow(["Google Click ID", "Conversion Name", "Conversion Time",
                "Conversion Value", "Conversion Currency"])
    for gclid, name, ctime, amount, cur in rows:
        w.writerow([gclid, name or CONVERSION_NAME, ctime,
                    f"{(amount or 0):.2f}", cur or CURRENCY])

    STATS["csv_served"] += 1
    return PlainTextResponse(buf.getvalue(), media_type="text/csv")


@app.get("/healthz")
def healthz():
    return PlainTextResponse("ok")


@app.get("/info")
def info():
    n = na = np_ = 0
    with DB_LOCK:
        con = _db()
        if con is not None:
            try:
                n = con.execute("SELECT COUNT(*) FROM conversions").fetchone()[0]
                na = con.execute(
                    """SELECT COUNT(*) FROM conversions
                       WHERE paid = 1 AND gclid IS NOT NULL AND gclid <> ''"""
                ).fetchone()[0]
                np_ = con.execute("SELECT COUNT(*) FROM conversions WHERE paid = 0").fetchone()[0]
            finally:
                con.close()
    return {
        "service": "topperpay-bridge",
        "conversion_enabled": True,
        "delivery": {
            "csv": "/conversions.csv (Google Ads -> Importacoes programadas, HTTPS)",
            "sheet": bool(SHEET_URL),
        },
        "conversion_name": CONVERSION_NAME,
        "currency": CURRENCY,
        "gclid_fields": GCLID_FIELDS,
        "csv_window_days": CSV_WINDOW_DAYS,
        "db_path": DB_PATH,
        "sales_stored": n,
        "paid_with_gclid": na,            # exatamente o que o CSV entrega ao Google
        "pending_stored": np_,            # PIX gerado/abandono — nao vai pro CSV
        "token_required": bool(TOKEN),
    }


@app.get("/debug/stats")
def debug_stats():
    return STATS


@app.get("/debug/recent")
def debug_recent(request: Request):
    """Payload cru (contem PII do comprador) — exige token."""
    if not _authorized(request):
        return JSONResponse({"ok": False, "error": "forbidden"}, status_code=403)
    return list(RECENT)


@app.post("/debug/forget")
def debug_forget(request: Request, payment_id: str):
    """Apaga uma venda do banco — usado pra tirar sonda de QA antes do import."""
    if not _authorized(request):
        return JSONResponse({"ok": False, "error": "forbidden"}, status_code=403)
    n = 0
    with DB_LOCK:
        con = _db()
        if con is not None:
            try:
                cur = con.execute("DELETE FROM conversions WHERE payment_id = ?", (payment_id,))
                con.commit()
                n = cur.rowcount
            finally:
                con.close()
    return {"ok": True, "deleted": n, "payment_id": payment_id}


@app.get("/debug/sales")
def debug_sales(request: Request, limit: int = 50):
    """Vendas persistidas SEM PII — auditoria da marcacao."""
    if not _authorized(request):
        return JSONResponse({"ok": False, "error": "forbidden"}, status_code=403)
    out = []
    with DB_LOCK:
        con = _db()
        if con is not None:
            try:
                con.row_factory = sqlite3.Row
                for r in con.execute(
                    """SELECT payment_id, paid, gclid, amount, conv_time, offer_title,
                              utm_content, utm_source, status
                       FROM conversions ORDER BY conv_time DESC LIMIT ?""", (limit,)
                ):
                    d = dict(r)
                    if d.get("gclid"):
                        d["gclid"] = d["gclid"][:10] + "…" + d["gclid"][-4:]
                    out.append(d)
            finally:
                con.close()
    return out
