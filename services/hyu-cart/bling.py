"""
Cliente Bling API v3 (OAuth) — cria contato + pedido de venda a partir de um
pedido pago do HYU. A integração nativa Bling↔Mandaê assume daí (NF-e, etiqueta,
rastreio HYU########).

Auth: refresh-token flow. O Bling ROTACIONA o refresh token a cada refresh, então
o token corrente é persistido em /data/bling_token.json (seed: env BLING_REFRESH_TOKEN).

Env:
  BLING_CLIENT_ID / BLING_CLIENT_SECRET  - app criado no painel Bling
  BLING_REFRESH_TOKEN                    - seed do bootstrap (scripts/bling_oauth_bootstrap.py)
  BLING_TOKEN_FILE                       - default /data/bling_token.json
"""
from __future__ import annotations

import base64
import json
import logging
import os
import time
from datetime import datetime, timezone
from typing import Any

import httpx

log = logging.getLogger("hyu-cart.bling")

API = "https://api.bling.com.br/Api/v3"
TOKEN_URL = f"{API}/oauth/token"


class BlingError(Exception):
    pass


class BlingClient:
    def __init__(self) -> None:
        self.client_id = os.environ.get("BLING_CLIENT_ID", "").strip()
        self.client_secret = os.environ.get("BLING_CLIENT_SECRET", "").strip()
        self.token_file = os.environ.get("BLING_TOKEN_FILE", "/data/bling_token.json")
        if not os.path.isdir(os.path.dirname(self.token_file) or "."):
            self.token_file = "./bling_token.json"
        self._access: str = ""
        self._access_exp: float = 0.0

    @property
    def configured(self) -> bool:
        return bool(self.client_id and self.client_secret and self._refresh_token())

    def _refresh_token(self) -> str:
        try:
            with open(self.token_file, encoding="utf-8") as f:
                return json.load(f).get("refresh_token", "")
        except (OSError, ValueError):
            return os.environ.get("BLING_REFRESH_TOKEN", "").strip()

    def _save_refresh(self, refresh: str) -> None:
        try:
            with open(self.token_file, "w", encoding="utf-8") as f:
                json.dump({"refresh_token": refresh, "ts": time.time()}, f)
        except OSError:
            log.error("nao consegui persistir refresh token em %s", self.token_file)

    async def _do_refresh(self, refresh: str) -> httpx.Response:
        basic = base64.b64encode(f"{self.client_id}:{self.client_secret}".encode()).decode()
        async with httpx.AsyncClient(timeout=30) as c:
            return await c.post(TOKEN_URL,
                                headers={"Authorization": f"Basic {basic}",
                                         "Content-Type": "application/x-www-form-urlencoded"},
                                data={"grant_type": "refresh_token", "refresh_token": refresh})

    async def _ensure_access(self) -> str:
        if self._access and time.time() < self._access_exp - 60:
            return self._access
        file_rt = ""
        try:
            with open(self.token_file, encoding="utf-8") as f:
                file_rt = json.load(f).get("refresh_token", "")
        except (OSError, ValueError):
            pass
        env_rt = os.environ.get("BLING_REFRESH_TOKEN", "").strip()
        # tenta o refresh do arquivo (rotacionado); se invalido, cai pro seed do env.
        # (re-autorizar no painel Bling invalida o token do /data → o seed do env recupera)
        candidates = [rt for rt in (file_rt, env_rt) if rt]
        if not candidates:
            raise BlingError("sem refresh token (rodar bling_oauth_bootstrap)")
        last = "?"
        for i, rt in enumerate(dict.fromkeys(candidates)):  # dedup preservando ordem
            r = await self._do_refresh(rt)
            if r.status_code == 200:
                tok = r.json()
                self._access = tok["access_token"]
                self._access_exp = time.time() + int(tok.get("expires_in", 21600))
                if tok.get("refresh_token"):
                    self._save_refresh(tok["refresh_token"])
                if i > 0:
                    log.warning("refresh do /data falhou; recuperado pelo BLING_REFRESH_TOKEN do env")
                return self._access
            last = f"{r.status_code}: {r.text[:200]}"
            log.warning("refresh candidato %d falhou (%s)", i, last)
        raise BlingError(f"refresh falhou (todos candidatos): {last}")

    async def _call(self, method: str, path: str, payload: dict | None = None,
                    params: dict | None = None) -> Any:
        token = await self._ensure_access()
        headers = {"Authorization": f"Bearer {token}", "Content-Type": "application/json"}
        async with httpx.AsyncClient(timeout=45) as c:
            r = await c.request(method, f"{API}{path}", headers=headers,
                                json=payload, params=params)
        if r.status_code == 401:
            # access token invalidado fora do prazo → força refresh e re-tenta 1x
            self._access = ""
            token = await self._ensure_access()
            headers["Authorization"] = f"Bearer {token}"
            async with httpx.AsyncClient(timeout=45) as c:
                r = await c.request(method, f"{API}{path}", headers=headers,
                                    json=payload, params=params)
        if r.status_code >= 400:
            raise BlingError(f"{method} {path} -> {r.status_code}: {r.text[:400]}")
        try:
            return r.json()
        except ValueError:
            return {}

    # ── alto nível ────────────────────────────────────────────────────────

    async def ensure_contato(self, p: dict[str, Any]) -> int:
        """Acha (por CPF) ou cria o contato; retorna o id Bling."""
        doc = "".join(ch for ch in str(p["document"]) if ch.isdigit())
        found = await self._call("GET", "/contatos", params={
            "numeroDocumento": doc, "pagina": 1, "limite": 1})
        rows = (found or {}).get("data") or []
        if rows:
            return int(rows[0]["id"])
        body = {
            "nome": p["name"][:120],
            "tipo": "F",
            "situacao": "A",
            "numeroDocumento": doc,
            "email": p.get("email", ""),
            "celular": p.get("phone", ""),
            "endereco": {"geral": {
                "endereco": p["street"][:100], "numero": str(p.get("number", "S/N"))[:10],
                "complemento": str(p.get("complement", ""))[:100],
                "bairro": p.get("neighborhood", "")[:60],
                "cep": p["cep"], "municipio": p.get("city", "")[:60],
                "uf": p.get("state", "")[:2].upper(),
            }},
        }
        created = await self._call("POST", "/contatos", payload=body)
        return int(created["data"]["id"])

    async def create_pedido(self, contato_id: int, pedido: dict[str, Any]) -> int:
        """Cria o pedido de venda; retorna o id Bling.

        pedido = {order_id, itens:[{sku, name, qty, cents}], frete_cents,
                  frete_service, address{...}, coupon?}
        """
        addr = pedido["address"]
        hoje = datetime.now(timezone.utc).astimezone().strftime("%Y-%m-%d")
        body = {
            "numeroLoja": pedido["order_id"],
            "data": hoje,
            "contato": {"id": contato_id},
            "itens": [{
                "codigo": it["sku"],
                "descricao": it["name"][:120],
                "quantidade": int(it["qty"]),
                "valor": round(it["cents"] / 100, 2),
            } for it in pedido["itens"]],
            "transporte": {
                "fretePorConta": 0,  # 0 = emitente (frete já cobrado do cliente no total)
                "frete": round(pedido.get("frete_cents", 0) / 100, 2),
                "etiqueta": {
                    "nome": pedido["customer_name"][:60],
                    "endereco": addr["street"][:100],
                    "numero": str(addr.get("number", "S/N"))[:10],
                    "complemento": str(addr.get("complement", ""))[:100],
                    "municipio": addr.get("city", "")[:60],
                    "uf": addr.get("state", "")[:2].upper(),
                    "cep": addr["cep"],
                    "bairro": addr.get("neighborhood", "")[:60],
                },
            },
            "observacoes": (f"HYU site — pedido {pedido['order_id']}"
                            + (f" — cupom {pedido['coupon']}" if pedido.get("coupon") else "")
                            + f" — frete {pedido.get('frete_service', '')}"),
        }
        created = await self._call("POST", "/pedidos/vendas", payload=body)
        return int(created["data"]["id"])
