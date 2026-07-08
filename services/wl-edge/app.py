"""
Edge-relay — o coração do white-label.

Reverse-proxy fino que recebe o POST do snippet (rebrandado) do cliente e repassa
transparentemente para o RPC do provedor de cloaking, traduzindo os headers.

O snippet do cliente aponta SÓ para este edge (nunca para o provedor), usa um TOKEN
opaco (não o stream real) e headers neutros (X-Fwd-IP/UA). Este edge:
  - resolve TOKEN -> stream real (mapa interno, nunca exposto);
  - traduz X-Fwd-IP/UA -> os headers que o provedor espera;
  - repassa método, query e body sem tocar no conteúdo;
  - devolve a resposta sem nenhum header que identifique o provedor.

Config por env:
  EDGE_UPSTREAM  base do RPC do provedor (default aponta pro provedor)
  EDGE_TOKENS    JSON {"tk_xxx": "stream-uuid", ...}
  EDGE_TIMEOUT   timeout em segundos (default 60)
"""

from __future__ import annotations

import json
import os

import httpx
from fastapi import FastAPI, HTTPException, Request, Response

UPSTREAM = os.environ.get("EDGE_UPSTREAM", "https://rpc.adspect.net/v2").rstrip("/")
TIMEOUT = float(os.environ.get("EDGE_TIMEOUT", "60"))

# headers que o provedor de cloaking espera receber (traduzidos a partir dos neutros)
_H_IP = os.environ.get("EDGE_UPSTREAM_IP_HEADER", "Adspect-IP")
_H_UA = os.environ.get("EDGE_UPSTREAM_UA_HEADER", "Adspect-UA")


def _load_tokens() -> dict[str, str]:
    raw = (os.environ.get("EDGE_TOKENS") or "").strip() or "{}"
    try:
        data = json.loads(raw)
        return data if isinstance(data, dict) else {}
    except Exception:
        return {}


TOKENS: dict[str, str] = _load_tokens()

app = FastAPI(title="edge", docs_url=None, redoc_url=None, openapi_url=None)

# client httpx criado sob demanda (dentro do event loop do uvicorn/uvloop) —
# criar no import quebra sob uvloop no Linux (não reproduz no Windows local)
_client: httpx.AsyncClient | None = None


def _http() -> httpx.AsyncClient:
    global _client
    if _client is None:
        _client = httpx.AsyncClient(timeout=TIMEOUT)
    return _client


def _real_ip(req: Request) -> str:
    """IP real do visitante: o snippet manda em X-Fwd-IP; fallbacks de CDN/proxy."""
    for h in ("x-fwd-ip", "cf-connecting-ip", "x-real-ip"):
        v = req.headers.get(h)
        if v:
            return v.split(",")[0].strip()
    xff = req.headers.get("x-forwarded-for")
    if xff:
        return xff.split(",")[-1].strip()
    return req.client.host if req.client else ""


@app.get("/healthz")
async def healthz():
    return {"ok": True, "tokens": len(TOKENS)}


@app.api_route("/r/{token}", methods=["GET", "POST"])
async def relay(token: str, request: Request):
    sid = TOKENS.get(token)
    if not sid:
        raise HTTPException(404, "not found")

    body = await request.body()
    q = request.url.query
    target = f"{UPSTREAM}/{sid}" + (f"?{q}" if q else "")

    fwd = {
        "User-Agent": "",
        _H_IP: _real_ip(request),
        _H_UA: request.headers.get("x-fwd-ua", request.headers.get("user-agent", "")),
        "Content-Type": request.headers.get("content-type", "application/x-www-form-urlencoded"),
    }
    try:
        up = await _http().request("POST", target, content=body, headers=fwd)
    except httpx.HTTPError:
        raise HTTPException(502, "upstream error")

    # devolve só o essencial — nenhum header que identifique o provedor
    resp = Response(
        content=up.content,
        status_code=up.status_code,
        media_type=up.headers.get("content-type", "application/json"),
    )
    resp.headers["Cache-Control"] = "no-store"
    return resp
