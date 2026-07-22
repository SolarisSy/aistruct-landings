/* HYU (teste) — checkout via SHOPIFY (Storefront Cart -> checkout PADRÃO de alta conversão).
 *
 * O checkout padrão coleta endereço/frete(Mandaê via Carrier Service)/pagamento(Appmax).
 * Aqui só coletamos o CPF (o checkout nativo Shopify não coleta) — vai como cart attribute
 * -> note_attributes da order -> Bling (NF-e/etiqueta/rastreio).
 *
 * GATE: Shopify é o DEFAULT no teste. ?gw=paggins desliga (opt-out persistente); ?gw=shopify religa.
 * Intercepta o "Finalizar compra" do drawer -> pede CPF -> POST /shopify/cart -> checkoutUrl.
 */
(function () {
  "use strict";

  /* ---- afiliado: ?ref=TAG persiste 30d (last-click) e vai como cart attribute ----
   * Roda ANTES do gate de gateway: a tag tem que ser capturada mesmo em ?gw=paggins. */
  var LS_REF = "hyu-ref", REF_TTL = 30 * 864e5;
  function saveRef() {
    try {
      var r = new URLSearchParams(location.search).get("ref");
      if (!r) return;
      r = r.replace(/[^A-Za-z0-9]/g, "").toUpperCase().slice(0, 20);
      if (r.length >= 3) localStorage.setItem(LS_REF, JSON.stringify({ t: r, at: Date.now() }));
    } catch (e) {}
  }
  function getRef() {
    try {
      var v = JSON.parse(localStorage.getItem(LS_REF) || "null");
      if (!v || !v.t || Date.now() - v.at > REF_TTL) return "";
      return v.t;
    } catch (e) { return ""; }
  }
  saveRef();

  var GW = "shopify";
  try {
    var qs = new URLSearchParams(location.search);
    if (qs.get("gw") === "paggins") localStorage.setItem("hyu-gw", "paggins");
    if (qs.get("gw") === "shopify") localStorage.removeItem("hyu-gw");
    if (localStorage.getItem("hyu-gw") === "paggins") GW = "paggins";
  } catch (e) {}
  if (GW !== "shopify") return;

  var API = "https://hyu-cart.tiectu.easypanel.host";
  var LS_CART = "hyu-cart-v2", LS_COUPON = "hyu_coupon";
  var LS_WPP = "hyu-wpp", LS_MAIL = "hyu-mail";
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
    var ref = getRef(); if (ref) m.ref = ref;   // storage vence a URL (persiste a navegação)
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
    /* CPF NÃO é pedido aqui: o checkout Shopify BR tem o campo nativo CPF/CNPJ
     * (localizedFields TAX_CREDENTIAL_BR) e a Appmax ainda injeta customer_document
     * nos note_attributes. Pedir de novo era só atrito antes do pagamento.
     * Se o site já capturou contato (lead), manda junto pra pré-preencher o checkout. */
    var body = { items: payloadItems(lines), meta: cartMeta(), customer: {} };
    try {
      var w = localStorage.getItem(LS_WPP) || "", m = localStorage.getItem(LS_MAIL) || "";
      if (w) body.customer.phone = w;
      if (m) body.customer.email = m;
    } catch (e) {}
    var code = couponCode(); if (code) body.coupon = code;
    fetch(API + "/shopify/cart", {
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
