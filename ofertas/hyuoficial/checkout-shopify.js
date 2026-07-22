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
  var LS_CPF = "hyu-cpf", LS_WPP = "hyu-wpp", LS_MAIL = "hyu-mail";
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

  /* ---- validação de CPF (mesmo algoritmo do backend) ---- */
  function validCpf(doc) {
    var d = (doc || "").replace(/\D/g, "");
    if (d.length !== 11 || /^(\d)\1{10}$/.test(d)) return false;
    for (var n = 9; n <= 10; n++) {
      var s = 0;
      for (var i = 0; i < n; i++) s += parseInt(d[i], 10) * (n + 1 - i);
      var dv = ((s * 10) % 11) % 10;
      if (dv !== parseInt(d[n], 10)) return false;
    }
    return true;
  }

  /* ---- modal mínimo de CPF (só o que o checkout Shopify não coleta) ---- */
  function askCpf(cb) {
    var saved = "";
    try { saved = localStorage.getItem(LS_CPF) || ""; } catch (e) {}
    if (validCpf(saved)) return cb({ cpf: saved,
      wpp: (localStorage.getItem(LS_WPP) || ""), mail: (localStorage.getItem(LS_MAIL) || "") });

    var ov = document.createElement("div");
    ov.setAttribute("style", "position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,.55);" +
      "display:flex;align-items:center;justify-content:center;padding:20px");
    ov.innerHTML =
      '<div style="background:#fff;max-width:380px;width:100%;border-radius:16px;padding:24px;' +
      'font-family:Archivo,system-ui,sans-serif;box-shadow:0 20px 60px rgba(0,0,0,.3)">' +
      '<h3 style="margin:0 0 4px;font-family:Anton,sans-serif;font-size:22px;color:#111">Falta só o CPF</h3>' +
      '<p style="margin:0 0 16px;font-size:13px;color:#666">Pra emitir a nota fiscal do seu pedido.</p>' +
      '<input id="hyu-cpf-in" inputmode="numeric" placeholder="CPF" ' +
      'style="width:100%;box-sizing:border-box;padding:12px;font-size:16px;border:1px solid #ccc;' +
      'border-radius:10px;margin-bottom:8px" />' +
      '<input id="hyu-wpp-in" inputmode="numeric" placeholder="WhatsApp (opcional)" ' +
      'style="width:100%;box-sizing:border-box;padding:12px;font-size:16px;border:1px solid #ccc;' +
      'border-radius:10px;margin-bottom:8px" />' +
      '<p id="hyu-cpf-err" style="min-height:16px;margin:0 0 8px;font-size:12px;color:#d33"></p>' +
      '<button id="hyu-cpf-ok" style="width:100%;padding:13px;border:0;border-radius:10px;' +
      'background:#c6ff00;color:#111;font-weight:700;font-size:15px;cursor:pointer">Continuar pro pagamento</button>' +
      '<button id="hyu-cpf-cancel" style="width:100%;padding:10px;border:0;background:none;' +
      'color:#999;font-size:13px;cursor:pointer;margin-top:4px">Cancelar</button></div>';
    document.body.appendChild(ov);
    var inp = ov.querySelector("#hyu-cpf-in"), wpp = ov.querySelector("#hyu-wpp-in"),
        err = ov.querySelector("#hyu-cpf-err");
    if (saved) inp.value = saved;
    inp.focus();
    function close() { ov.remove(); }
    ov.querySelector("#hyu-cpf-cancel").onclick = function () { close(); cb(null); };
    ov.addEventListener("click", function (e) { if (e.target === ov) { close(); cb(null); } });
    ov.querySelector("#hyu-cpf-ok").onclick = function () {
      var cpf = (inp.value || "").replace(/\D/g, "");
      if (!validCpf(cpf)) { err.textContent = "CPF inválido — confere os números."; return; }
      var w = (wpp.value || "").replace(/\D/g, "");
      try {
        localStorage.setItem(LS_CPF, cpf);
        if (w) localStorage.setItem(LS_WPP, w);
      } catch (e) {}
      close();
      cb({ cpf: cpf, wpp: w, mail: (localStorage.getItem(LS_MAIL) || "") });
    };
  }

  var going = false;
  function go(btn) {
    if (going) return;
    var lines = cartLines();
    if (!lines.length) return;
    askCpf(function (info) {
      if (!info) return;   // cancelado
      going = true;
      var old = btn ? btn.textContent : "";
      if (btn) { btn.classList.add("is-loading"); btn.textContent = "Abrindo checkout…"; }
      var body = { items: payloadItems(lines), meta: cartMeta(),
                   customer: { document: info.cpf } };
      if (info.wpp) body.customer.phone = info.wpp;
      if (info.mail) body.customer.email = info.mail;
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
