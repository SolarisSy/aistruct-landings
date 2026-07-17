/* HYU (teste) — checkout via SHOPIFY (draft order -> checkout hospedado de alta conversão).
 *
 * GATE: ativa só com ?gw=shopify (persiste em localStorage hyu-gw). ?gw=paggins desativa.
 * Intercepta o "Finalizar compra" do drawer -> POST no bridge /shopify/checkout com os
 * itens do carrinho -> redireciona pra invoice_url (checkout Shopify). Zero impacto no
 * fluxo Paggins de quem não tem o gate ligado.
 */
(function () {
  "use strict";

  var GW = null;
  try {
    var qs = new URLSearchParams(location.search);
    if (qs.get("gw") === "shopify") localStorage.setItem("hyu-gw", "shopify");
    if (qs.get("gw") === "paggins") localStorage.removeItem("hyu-gw");
    GW = localStorage.getItem("hyu-gw");
  } catch (e) {}
  if (GW !== "shopify") return;

  var API = "https://hyu-cart.tiectu.easypanel.host";
  var LS_CART = "hyu-cart-v2", LS_COUPON = "hyu_coupon";
  var COMBOS = { "kit-energy": 1, "kit-soda": 1, "super-kit": 1, "kit24": 1 };

  function cartLines() {
    try {
      var c = JSON.parse(localStorage.getItem(LS_CART) || "");
      if (!c || !Array.isArray(c.items)) return [];
      return c.items.filter(function (i) { return i && i.tier !== "sub" && i.qty >= 1; });
    } catch (e) { return []; }
  }
  function payloadItems(lines) {
    return lines.map(function (i) {
      if (i.mix) return { mix: i.mix, tier: i.tier, qty: i.qty };
      return COMBOS[i.flavor] ? { combo: i.flavor, qty: i.qty }
                              : { flavor: i.flavor, tier: i.tier, qty: i.qty };
    });
  }
  function cartMeta() {
    var m = {}, q = new URLSearchParams(location.search);
    ["utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content",
     "gclid", "fbclid", "ref", "src"].forEach(function (k) {
      var v = q.get(k); if (v) m[k] = v;
    });
    m.page = location.pathname;
    return m;
  }
  function couponCode() {
    try { return localStorage.getItem(LS_COUPON) || ""; } catch (e) { return ""; }
  }

  var going = false;
  function go(btn) {
    if (going) return;
    var lines = cartLines();
    if (!lines.length) return;
    going = true;
    var old = btn ? btn.textContent : "";
    if (btn) { btn.classList.add("is-loading"); btn.textContent = "Abrindo checkout…"; }
    var body = { items: payloadItems(lines), meta: cartMeta() };
    var code = couponCode(); if (code) body.coupon = code;
    fetch(API + "/shopify/checkout", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body)
    }).then(function (r) {
      if (!r.ok) return r.json().then(function (j) {
        throw new Error(j && j.detail ? j.detail : "bridge " + r.status);
      });
      return r.json();
    }).then(function (d) {
      if (!d || !d.url) throw new Error("checkout indisponível");
      location.href = d.url;
    }).catch(function (e) {
      going = false;
      if (btn) { btn.classList.remove("is-loading"); btn.textContent = old; }
      alert((e && e.message) || "Não consegui abrir o checkout — tenta de novo em instantes.");
    });
  }
  /* bfcache: voltar do checkout restaura a página congelada — reseta o botão */
  window.addEventListener("pageshow", function () { going = false; });

  /* captura ANTES do handler do drawer (Paggins) e substitui o destino */
  document.addEventListener("click", function (e) {
    var btn = e.target && e.target.closest && e.target.closest("[data-cd-checkout]");
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    go(btn);
  }, true);
})();
