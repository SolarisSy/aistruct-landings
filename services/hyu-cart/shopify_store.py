"""
Cliente Storefront API (GraphQL) da Shopify — cria um Cart e devolve o checkoutUrl
(checkout PADRÃO/hospedado de alta conversão, onde o Carrier Service coteia o frete
Mandaê em tempo real — ao contrário do draft order, que só mostra "fallback rates").

Diferente de shopify.py (Admin REST, usado pra draft order/produtos): aqui é a
Storefront API pública, token X-Shopify-Storefront-Access-Token (só leitura de
catálogo + criação de cart). Inerte sem SHOPIFY_STOREFRONT_TOKEN (503).

Env:
  SHOPIFY_STORE            - <loja>.myshopify.com
  SHOPIFY_STOREFRONT_TOKEN - token da Storefront API (scripts/_shopify_storefront_token.py)
  SHOPIFY_API_VERSION      - default 2026-07
"""
from __future__ import annotations

import os
from typing import Any

import httpx

STORE = os.environ.get("SHOPIFY_STORE", "").strip()
TOKEN = os.environ.get("SHOPIFY_STOREFRONT_TOKEN", "").strip()
VERSION = os.environ.get("SHOPIFY_API_VERSION", "2026-07")

_CART_CREATE = """
mutation cartCreate($input: CartInput!) {
  cartCreate(input: $input) {
    cart { id checkoutUrl }
    userErrors { field message }
  }
}
"""


class StorefrontError(Exception):
    pass


def configured() -> bool:
    return bool(STORE and TOKEN)


def variant_gid(variant_id: int | str) -> str:
    """id numérico da variante -> GID que a Storefront API espera."""
    s = str(variant_id)
    return s if s.startswith("gid://") else f"gid://shopify/ProductVariant/{s}"


async def create_cart(lines: list[dict[str, Any]],
                      discount_codes: list[str] | None = None,
                      attributes: dict[str, str] | None = None,
                      buyer: dict[str, str] | None = None) -> str:
    """Cria um Cart e devolve o checkoutUrl.

    lines: [{"merchandiseId": gid, "quantity": int, "attributes": [{key,value}]?}]
    discount_codes: cupons (viram desconto nativo no checkout)
    attributes: cart-level (cpf/utm/gclid) -> viram note_attributes na order (pro Bling)
    buyer: {email?, phone?} -> pré-preenche o checkout
    """
    if not configured():
        raise StorefrontError("shopify storefront nao configurado (SHOPIFY_STOREFRONT_TOKEN ausente)")
    cart_input: dict[str, Any] = {"lines": lines}
    if discount_codes:
        cart_input["discountCodes"] = discount_codes
    if attributes:
        cart_input["attributes"] = [{"key": k, "value": str(v)[:255]}
                                    for k, v in attributes.items() if v]
    if buyer:
        bi = {k: v for k, v in buyer.items() if v}
        if bi:
            bi["countryCode"] = "BR"
            cart_input["buyerIdentity"] = bi

    url = f"https://{STORE}/api/{VERSION}/graphql.json"
    try:
        async with httpx.AsyncClient(timeout=30) as c:
            r = await c.post(url,
                             headers={"X-Shopify-Storefront-Access-Token": TOKEN,
                                      "Content-Type": "application/json"},
                             json={"query": _CART_CREATE, "variables": {"input": cart_input}})
    except httpx.HTTPError as e:
        raise StorefrontError(f"storefront indisponivel: {str(e)[:80]}")
    if r.status_code >= 400:
        raise StorefrontError(f"storefront HTTP {r.status_code}: {r.text[:200]}")
    body = r.json()
    if body.get("errors"):
        raise StorefrontError(f"graphql: {str(body['errors'])[:200]}")
    res = (body.get("data") or {}).get("cartCreate") or {}
    errs = res.get("userErrors") or []
    if errs:
        raise StorefrontError(f"cartCreate: {str(errs)[:200]}")
    cart = res.get("cart") or {}
    url_out = cart.get("checkoutUrl")
    if not url_out:
        raise StorefrontError("cartCreate sem checkoutUrl")
    return url_out
