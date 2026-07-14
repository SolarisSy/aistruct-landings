/* HYU — checkout 100% NOSSO (Stripe Payment Element), dark + lime.
 *
 * Página full-screen no NOSSO domínio: resumo + contato + CPF + endereço
 * (ViaCEP) + frete Mandaê + cupom, e a Stripe entra só como o cofre do
 * pagamento (Payment Element embutido: cartão/PIX/wallets, QR inline, 3DS).
 * O total é SEMPRE do backend (POST /stripe/checkout devolve o clientSecret).
 *
 * GATE (paralelo ao Paggins, sem afetar quem compra hoje):
 *   ativa só com ?gw=stripe na URL (persiste em localStorage hyu-gw);
 *   ?gw=paggins desativa. Pra virar a chave: remover o gate abaixo.
 *
 * Assinatura (tier "sub") segue o fluxo nativo — recorrência Stripe é fase 2.
 * Cupom: lê o hyu_coupon do coupon.js e manda no body (validação = bridge).
 */
(function () {
  "use strict";

  /* ── GATE ────────────────────────────────────────────────────────────── */
  var GW = null;
  try {
    var qs = new URLSearchParams(location.search);
    if (qs.get("gw") === "stripe") localStorage.setItem("hyu-gw", "stripe");
    if (qs.get("gw") === "paggins") localStorage.removeItem("hyu-gw");
    GW = localStorage.getItem("hyu-gw");
  } catch (e) {}
  if (GW !== "stripe") return;

  var API = "https://hyu-cart.tiectu.easypanel.host";
  var LS_CART = "hyu-cart-v2";
  var LS_FORM = "hyu-precheckout-v1";
  var LS_COUPON = "hyu_coupon";
  var LIME = "#c4f439";
  var COMBOS = { "kit-energy": 1, "kit-soda": 1, "super-kit": 1, "kit24": 1 };
  var TIER_CENTS = { kit6: 6990, kit12: 11990, kit24: 21990 };
  var TIER_LABEL = { kit6: "6 latas", kit12: "12 latas", kit24: "24 latas" };
  var MIX_FEE = 490; /* manter = bridge MIX_FEE_CENTS */
  var MIX_LABEL = { kit6: "Kit 6 Personalizado", kit12: "Kit 12 Personalizado" };
  var TITLES = {
    "kit-energy": "Kit Energy", "kit-soda": "Kit Soda", "super-kit": "Super Kit",
    "kit24": "Kit 24", "hot-lemon": "Hot Lemon", "maca-verde": "Maçã Verde",
    "pessego-morango": "Pêssego com Morango", "tropical": "Tropical",
    "maca-vermelha": "Maçã Vermelha"
  };
  var IMGS = { "kit24": "super-kit" };

  /* ── carrinho (mesma leitura do cart.js) ─────────────────────────────── */
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
  function lineCents(i) { return (TIER_CENTS[i.tier] || 0) + (i.mix ? MIX_FEE : 0); }
  function subtotalCents(lines) {
    return lines.reduce(function (s, i) { return s + lineCents(i) * i.qty; }, 0);
  }
  function brl(cents) {
    return (cents / 100).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  }
  function mixComp(mix) {
    return Object.keys(mix).sort(function (a, b) { return mix[b] - mix[a]; })
      .map(function (s) { return mix[s] + "× " + (TITLES[s] || s); }).join(" · ");
  }
  function lineTitle(i) {
    if (i.mix) return (MIX_LABEL[i.tier] || "Kit Personalizado") + " — " + mixComp(i.mix);
    var t = TITLES[i.flavor] || i.flavor;
    if (!COMBOS[i.flavor] && TIER_LABEL[i.tier]) t += " — " + TIER_LABEL[i.tier];
    else if (COMBOS[i.flavor] && TIER_LABEL[i.tier]) t += " · " + TIER_LABEL[i.tier];
    return t;
  }
  function lineImg(i) {
    if (i.mix) return "/img/kits/super-kit.webp";
    return "/img/kits/" + (IMGS[i.flavor] || i.flavor) + ".webp";
  }
  function validCPF(doc) {
    var d = (doc || "").replace(/\D/g, "");
    if (d.length !== 11 || /^(\d)\1{10}$/.test(d)) return false;
    for (var n = 9; n <= 10; n++) {
      var s = 0;
      for (var i = 0; i < n; i++) s += parseInt(d[i], 10) * ((n + 1) - i);
      if ((s * 10) % 11 % 10 !== parseInt(d[n], 10)) return false;
    }
    return true;
  }

  /* ── estética HYU: dark + lime ───────────────────────────────────────── */
  var css = "" +
    ".hyst{position:fixed;inset:0;z-index:9999;background:#0c0c0c;overflow-y:auto;-webkit-overflow-scrolling:touch;color:#f3efe4;font-family:'Archivo',ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif;display:none}" +
    ".hyst.open{display:block}" +
    ".hyst *{box-sizing:border-box;font-family:inherit}" +
    ".hyst-wrap{max-width:560px;margin:0 auto;padding:12px 14px calc(28px + env(safe-area-inset-bottom,0px))}" +
    ".hyst-top{display:flex;align-items:center;gap:.6rem;padding:6px 2px 12px}" +
    ".hyst-back{display:flex;align-items:center;gap:.35rem;background:none;border:0;padding:6px 8px 6px 2px;font-size:14px;color:#8b93a1;cursor:pointer;border-radius:8px}" +
    ".hyst-back:hover{color:#f3efe4}" +
    ".hyst-top .ttl{flex:1;text-align:center;font-weight:800;font-size:15px;color:#f3efe4;margin-right:64px;letter-spacing:.02em}" +
    ".hyst-card{background:#15171c;border:1px solid #262a33;border-radius:14px;padding:16px;margin:0 0 8px}" +
    ".hyst h2{font-size:13px;font-weight:800;color:#8b93a1;margin:18px 4px 10px;letter-spacing:.08em;text-transform:uppercase}" +
    /* resumo */
    ".hyst-sumhead{width:100%;display:flex;align-items:center;justify-content:space-between;gap:.6rem;background:none;border:0;padding:0;cursor:pointer;font-size:15px;font-weight:700;color:#f3efe4}" +
    ".hyst-sumhead b{font-weight:800;font-size:15px;color:" + LIME + "}" +
    ".hyst-sumhead .chev{flex:none;margin-left:2px;transition:transform .18s;color:#8b93a1}" +
    ".hyst-sum.open .chev{transform:rotate(180deg)}" +
    ".hyst-sumbody{display:none;margin-top:14px;border-top:1px solid #262a33;padding-top:12px}" +
    ".hyst-sum.open .hyst-sumbody{display:block}" +
    ".hyst-it{display:flex;align-items:center;gap:.7rem;padding:.45rem 0}" +
    ".hyst-it img{flex:none;width:44px;height:44px;border-radius:8px;object-fit:cover;background:#1e2128;border:1px solid #262a33}" +
    ".hyst-it .nm{flex:1;min-width:0;font-size:13.5px;color:#f3efe4;line-height:1.3}" +
    ".hyst-it .nm small{display:block;color:#8b93a1;font-size:12px}" +
    ".hyst-it .pr{font-size:13.5px;font-weight:700;white-space:nowrap}" +
    ".hyst-tot{border-top:1px solid #262a33;margin-top:8px;padding-top:10px;font-size:13.5px;color:#8b93a1}" +
    ".hyst-tot div{display:flex;justify-content:space-between;padding:.18rem 0}" +
    ".hyst-tot .off{color:" + LIME + "}" +
    ".hyst-tot .tt{font-weight:800;color:#f3efe4;font-size:15.5px;padding-top:.4rem}" +
    /* campos */
    ".hyst-f{margin:0 0 12px}" +
    ".hyst-f:last-child{margin-bottom:2px}" +
    ".hyst-f label{display:block;font-size:12.5px;font-weight:600;color:#8b93a1;margin:0 0 6px}" +
    ".hyst-f input{width:100%;height:46px;padding:0 13px;font-size:16px;color:#f3efe4;background:#0f1115;border:1px solid #333947;border-radius:10px;outline:none;transition:border-color .12s,box-shadow .12s}" +
    ".hyst-f input::placeholder{color:#565e6b}" +
    ".hyst-f input:focus{border-color:" + LIME + ";box-shadow:0 0 0 3px rgba(196,244,57,.14)}" +
    ".hyst-f .sub{font-size:12px;color:#6b7280;margin:6px 2px 0}" +
    ".hyst-row{display:flex;gap:8px}" +
    ".hyst-row .hyst-f{flex:1;min-width:0}" +
    ".hyst-row .hyst-f.sm{flex:0 0 96px}" +
    ".hyst-phone{display:flex;gap:8px}" +
    ".hyst-phone .ddi{flex:none;display:flex;align-items:center;gap:6px;height:46px;padding:0 12px;border:1px solid #333947;border-radius:10px;background:#0f1115;font-size:14.5px;color:#8b93a1}" +
    ".hyst-phone input{flex:1}" +
    /* frete */
    ".hyst-ship{border:1px solid #262a33;border-radius:12px;overflow:hidden;margin-top:4px}" +
    ".hyst-opt{display:flex;align-items:center;gap:.75rem;padding:13px 14px;cursor:pointer;border-bottom:1px solid #262a33;background:#15171c;transition:background .12s}" +
    ".hyst-opt:last-child{border-bottom:0}" +
    ".hyst-opt input{display:none}" +
    ".hyst-opt .dot{flex:none;width:18px;height:18px;border-radius:50%;border:2px solid #40485a;display:grid;place-items:center;transition:border-color .12s}" +
    ".hyst-opt .dot:before{content:'';width:9px;height:9px;border-radius:50%;background:transparent;transition:background .12s}" +
    ".hyst-opt.sel .dot{border-color:" + LIME + "}" +
    ".hyst-opt.sel .dot:before{background:" + LIME + "}" +
    ".hyst-opt .inf{flex:1;min-width:0;font-size:14px;color:#f3efe4}" +
    ".hyst-opt .inf small{display:block;font-size:12px;color:#8b93a1}" +
    ".hyst-opt .prc{font-size:14px;font-weight:700;white-space:nowrap}" +
    ".hyst-calc{display:flex;align-items:center;gap:.6rem;font-size:13.5px;color:#8b93a1;padding:10px 2px}" +
    ".hyst-spin{width:15px;height:15px;border:2px solid rgba(196,244,57,.25);border-top-color:" + LIME + ";border-radius:50%;animation:hystspin .7s linear infinite;flex:none}" +
    "@keyframes hystspin{to{transform:rotate(360deg)}}" +
    ".hyst-hint{font-size:13px;color:#8b93a1;padding:8px 2px 0}" +
    ".hyst-hint.err{color:#ff9d9d}" +
    /* cupom */
    ".hyst-cpn{display:flex;align-items:center;gap:8px;font-size:13px;color:#0c0c0c;background:" + LIME + ";border-radius:10px;padding:9px 12px;font-weight:700;margin:2px 0 0}" +
    /* erro + CTA */
    ".hyst-err{color:#ff9d9d;font-size:13px;min-height:1.2em;margin:10px 4px 0}" +
    ".hyst-cta{width:100%;height:52px;margin-top:10px;background:" + LIME + ";color:#0c0c0c;font-size:15.5px;font-weight:800;border:0;border-radius:12px;cursor:pointer;transition:filter .12s,opacity .12s;letter-spacing:.01em}" +
    ".hyst-cta:hover{filter:brightness(1.08)}" +
    ".hyst-cta:disabled{opacity:.55;cursor:default}" +
    ".hyst-note{text-align:center;font-size:12.5px;color:#6b7280;margin:12px 6px 0;line-height:1.45}" +
    ".hyst-foot{text-align:center;font-size:11.5px;color:#565e6b;margin:18px 0 0}" +
    ".hyst-foot b{color:#8b93a1;font-weight:600}" +
    /* passo pagamento */
    ".hyst-payhead{display:flex;align-items:baseline;justify-content:space-between;margin:0 2px 12px}" +
    ".hyst-payhead .lbl{font-size:13px;color:#8b93a1;font-weight:600}" +
    ".hyst-payhead .val{font-size:22px;font-weight:800;color:" + LIME + "}" +
    ".hyst-payel{min-height:220px}" +
    ".hyst-editlink{background:none;border:0;color:#8b93a1;font-size:13px;cursor:pointer;text-decoration:underline;padding:6px 2px;margin-top:2px}" +
    ".hyst-editlink:hover{color:#f3efe4}" +
    "@media(min-width:640px){.hyst-wrap{padding-top:28px}}";

  var CHEV = '<svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
  var LOCK = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1.5px"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';

  var html = "" +
    '<div class="hyst-wrap">' +
    '<div class="hyst-top">' +
    '<button type="button" class="hyst-back" data-hyst-close>' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>Voltar</button>' +
    '<span class="ttl">Finalizar compra</span>' +
    "</div>" +
    '<section class="hyst-card hyst-sum" data-hyst-sum>' +
    '<button type="button" class="hyst-sumhead" data-hyst-sumtoggle>Resumo da compra<span style="flex:1"></span><b data-hyst-sumtotal></b>' + CHEV + "</button>" +
    '<div class="hyst-sumbody" data-hyst-sumbody></div>' +
    "</section>" +
    '<div data-hyst-cpn hidden></div>' +
    /* ── passo 1: dados ── */
    '<div data-hyst-step1>' +
    '<form data-hyst-form novalidate>' +
    "<h2>Dados de contato</h2>" +
    '<section class="hyst-card">' +
    '<div class="hyst-f"><label>Nome Completo</label><input name="name" autocomplete="name" placeholder="Nome completo"></div>' +
    '<div class="hyst-f"><label>Telefone de contato</label><div class="hyst-phone"><span class="ddi">&#127463;&#127479; +55</span><input name="phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="Insira seu celular"></div></div>' +
    '<div class="hyst-f"><label>E-mail</label><input name="email" type="email" autocomplete="email" placeholder="Insira seu e-mail"></div>' +
    '<div class="hyst-f"><label>CPF</label><input name="cpf" inputmode="numeric" autocomplete="off" placeholder="000.000.000-00"><p class="sub">Usado só pra emitir a nota fiscal do pedido.</p></div>' +
    "</section>" +
    "<h2>Endereço de entrega</h2>" +
    '<section class="hyst-card">' +
    '<div class="hyst-f"><label>CEP</label><input name="cep" inputmode="numeric" autocomplete="postal-code" placeholder="Digite seu CEP"></div>' +
    '<div data-hyst-addr hidden>' +
    '<div class="hyst-row"><div class="hyst-f"><label>Rua</label><input name="street" autocomplete="address-line1" placeholder="Rua / Avenida"></div><div class="hyst-f sm"><label>Número</label><input name="number" inputmode="numeric" placeholder="Nº"></div></div>' +
    '<div class="hyst-f"><label>Complemento (opcional)</label><input name="complement" placeholder="Apto, bloco…"></div>' +
    '<div class="hyst-row"><div class="hyst-f"><label>Bairro</label><input name="neighborhood" placeholder="Bairro"></div></div>' +
    '<div class="hyst-row"><div class="hyst-f"><label>Cidade</label><input name="city" placeholder="Cidade"></div><div class="hyst-f sm"><label>UF</label><input name="state" maxlength="2" placeholder="UF" style="text-transform:uppercase"></div></div>' +
    "</div>" +
    '<div data-hyst-frete><div class="hyst-hint">Digite o CEP acima pra ver as opções e prazos de entrega.</div></div>' +
    "</section>" +
    '<p class="hyst-err" data-hyst-err></p>' +
    '<button type="submit" class="hyst-cta">Continuar para o pagamento</button>' +
    '<p class="hyst-note">Pagamento na próxima etapa, sem sair do site:<br>Pix, cartão de crédito, Google Pay ou Apple Pay.</p>' +
    "</form></div>" +
    /* ── passo 2: pagamento ── */
    '<div data-hyst-step2 hidden>' +
    "<h2>Pagamento</h2>" +
    '<section class="hyst-card">' +
    '<div class="hyst-payhead"><span class="lbl">Total a pagar</span><span class="val" data-hyst-paytotal></span></div>' +
    '<div class="hyst-payel" data-hyst-payel></div>' +
    "</section>" +
    '<p class="hyst-err" data-hyst-payerr></p>' +
    '<button type="button" class="hyst-cta" data-hyst-pay>Pagar agora</button>' +
    '<button type="button" class="hyst-editlink" data-hyst-edit>← Voltar e editar meus dados</button>' +
    "</div>" +
    '<p class="hyst-foot">' + LOCK + " Pagamento processado com segurança por <b>Stripe</b></p>" +
    "</div>";

  var page = null, form = null, freteBox = null, errEl = null;
  var sumEl = null, sumTotalEl = null, sumBodyEl = null, addrEl = null, cpnEl = null;
  var step1 = null, step2 = null, payTotalEl = null, payErrEl = null, payElBox = null;
  var freteState = { cep: "", options: null, free: false, chosen: "economico" };
  var payState = null; /* {stripe, elements, orderId} quando o passo 2 está montado */

  /* Stripe.js sob demanda (só quando o checkout abre) */
  var stripeJs = null;
  function loadStripeJs() {
    if (window.Stripe) return Promise.resolve();
    if (stripeJs) return stripeJs;
    stripeJs = new Promise(function (res, rej) {
      var s = document.createElement("script");
      s.src = "https://js.stripe.com/v3/";
      s.onload = res;
      s.onerror = function () { stripeJs = null; rej(new Error("stripe.js")); };
      document.head.appendChild(s);
    });
    return stripeJs;
  }

  function ensurePage() {
    if (page) return;
    var st = document.createElement("style");
    st.textContent = css;
    document.head.appendChild(st);
    page = document.createElement("div");
    page.className = "hyst";
    page.setAttribute("role", "dialog");
    page.setAttribute("aria-modal", "true");
    page.setAttribute("aria-label", "Finalizar compra");
    page.innerHTML = html;
    document.body.appendChild(page);
    form = page.querySelector("[data-hyst-form]");
    freteBox = page.querySelector("[data-hyst-frete]");
    errEl = page.querySelector("[data-hyst-err]");
    sumEl = page.querySelector("[data-hyst-sum]");
    sumTotalEl = page.querySelector("[data-hyst-sumtotal]");
    sumBodyEl = page.querySelector("[data-hyst-sumbody]");
    addrEl = page.querySelector("[data-hyst-addr]");
    cpnEl = page.querySelector("[data-hyst-cpn]");
    step1 = page.querySelector("[data-hyst-step1]");
    step2 = page.querySelector("[data-hyst-step2]");
    payTotalEl = page.querySelector("[data-hyst-paytotal]");
    payErrEl = page.querySelector("[data-hyst-payerr]");
    payElBox = page.querySelector("[data-hyst-payel]");

    page.addEventListener("click", function (e) {
      if (e.target.closest("[data-hyst-close]")) closePage();
      if (e.target.closest("[data-hyst-sumtoggle]")) sumEl.classList.toggle("open");
      if (e.target.closest("[data-hyst-edit]")) showStep1();
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closePage();
    });

    /* máscaras leves + ViaCEP */
    form.elements["cep"].addEventListener("input", function () {
      var d = this.value.replace(/\D/g, "").slice(0, 8);
      this.value = d.replace(/(\d{5})(\d)/, "$1-$2");
      if (d.length === 8) { fillFromViaCep(d); quoteFrete(d); }
    });
    form.elements["phone"].addEventListener("input", function () {
      var d = this.value.replace(/\D/g, "").slice(0, 11);
      this.value = d.replace(/(\d{2})(\d)/, "($1) $2").replace(/(\d{5})(\d)/, "$1-$2");
    });
    form.elements["cpf"].addEventListener("input", function () {
      var d = this.value.replace(/\D/g, "").slice(0, 11);
      this.value = d.replace(/(\d{3})(\d)/, "$1.$2").replace(/(\d{3})\.(\d{3})(\d)/, "$1.$2.$3")
                    .replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/, "$1.$2.$3-$4");
    });
    form.addEventListener("submit", onSubmit);
    form.addEventListener("input", function () { errEl.textContent = ""; });

    /* restaura dados salvos (compartilha o storage do form Paggins) */
    try {
      var saved = JSON.parse(localStorage.getItem(LS_FORM) || "{}");
      ["name", "phone", "email", "cep", "cpf", "street", "number", "complement",
       "neighborhood", "city", "state"].forEach(function (k) {
        if (saved[k] && form.elements[k]) form.elements[k].value = saved[k];
      });
      var cepd = (saved.cep || "").replace(/\D/g, "");
      if (cepd.length === 8) { addrEl.hidden = false; quoteFrete(cepd); }
    } catch (e) {}

    var code = couponCode();
    if (code) {
      cpnEl.hidden = false;
      cpnEl.innerHTML = '<div class="hyst-cpn">🎟️ Cupom <b>' + code +
        "</b> aplicado — desconto no total do pagamento</div>";
    }
  }

  function openPage() {
    ensurePage();
    renderSummary();
    showStep1();
    sumEl.classList.add("open");
    page.classList.add("open");
    document.body.style.overflow = "hidden";
    page.scrollTop = 0;
    loadStripeJs().catch(function () {}); /* pré-carrega em paralelo ao form */
  }
  function closePage() {
    if (page && page.classList.contains("open")) {
      page.classList.remove("open");
      document.body.style.overflow = "";
    }
  }
  function showStep1() {
    step1.hidden = false;
    step2.hidden = true;
    payErrEl.textContent = "";
    if (payState) { try { payState.elements = null; } catch (e) {} payState = null; }
    payElBox.innerHTML = "";
  }

  /* ── resumo ──────────────────────────────────────────────────────────── */
  function renderSummary() {
    var lines = cartLines();
    var sub = subtotalCents(lines);
    var frete = freteState.options ? (freteState.free ? 0 : chosenCents()) : null;
    sumTotalEl.textContent = brl(sub + (frete || 0));
    var rows = lines.map(function (i) {
      return '<div class="hyst-it"><img src="' + lineImg(i) + '" alt="" loading="lazy" onerror="this.style.display=\'none\'">' +
        '<div class="nm">' + lineTitle(i) + "<small>Quantidade: " + i.qty +
        (i.mix ? " · inclui personalização (R$ 4,90)" : "") + "</small></div>" +
        '<div class="pr">' + brl(lineCents(i) * i.qty) + "</div></div>";
    }).join("");
    var tot = '<div class="hyst-tot"><div><span>Subtotal</span><span>' + brl(sub) + "</span></div>";
    if (frete !== null) {
      tot += "<div><span>Frete</span><span>" + (frete === 0 ? "Grátis" : brl(frete)) + "</span></div>";
      tot += '<div class="tt"><span>Total</span><span>' + brl(sub + frete) + "</span></div>";
    } else {
      tot += '<div class="tt"><span>Total</span><span>' + brl(sub) + " + frete</span></div>";
    }
    tot += "</div>";
    sumBodyEl.innerHTML = rows + tot;
  }

  /* ── ViaCEP: auto-preenche o endereço ────────────────────────────────── */
  function fillFromViaCep(cep) {
    addrEl.hidden = false;
    fetch("https://viacep.com.br/ws/" + cep + "/json/")
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        if (!d || d.erro) return;
        var f = form.elements;
        if (!f.street.value && d.logradouro) f.street.value = d.logradouro;
        if (!f.neighborhood.value && d.bairro) f.neighborhood.value = d.bairro;
        if (!f.city.value && d.localidade) f.city.value = d.localidade;
        if (!f.state.value && d.uf) f.state.value = d.uf;
        if (!f.number.value) f.number.focus();
      }).catch(function () {});
  }

  /* ── CEP → cotação Mandaê ────────────────────────────────────────────── */
  function quoteFrete(cep) {
    var lines = cartLines();
    if (!lines.length) return;
    freteState.cep = cep;
    freteState.options = null;
    freteBox.innerHTML = '<div class="hyst-calc"><span class="hyst-spin"></span>Calculando a entrega pro seu CEP…</div>';
    fetch(API + "/frete", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ cep: cep, items: payloadItems(lines) })
    }).then(function (r) {
      if (!r.ok) throw new Error("frete " + r.status);
      return r.json();
    }).then(function (d) {
      if (freteState.cep !== cep) return;
      freteState.options = d.options || [];
      freteState.free = !!d.free;
      renderFrete();
    }).catch(function () {
      freteBox.innerHTML = '<div class="hyst-hint err">Não consegui calcular a entrega — confere o CEP e tenta de novo.</div>';
    });
  }
  function chosenCents() {
    if (!freteState.options) return 0;
    var o = freteState.options.find(function (x) { return x.service === freteState.chosen; });
    return o ? o.cents : (freteState.options[0] ? freteState.options[0].cents : 0);
  }
  function optRow(name, days, cents, value, sel) {
    return '<label class="hyst-opt' + (sel ? " sel" : "") + '">' +
      '<input type="radio" name="hyst-frete" value="' + value + '"' + (sel ? " checked" : "") + ">" +
      '<span class="dot"></span>' +
      '<span class="inf">' + name + (days ? "<small>chega em ~" + days + " dia" + (days > 1 ? "s" : "") + " úteis</small>" : "") + "</span>" +
      '<span class="prc">' + (cents === 0 ? "Grátis" : brl(cents)) + "</span></label>";
  }
  function renderFrete() {
    var rows;
    if (freteState.free) {
      freteState.chosen = "economico";
      var eco = (freteState.options || []).find(function (o) { return o.service === "economico"; });
      rows = optRow("Frete Grátis", eco && eco.days, 0, "economico", true);
    } else {
      if (!(freteState.options || []).some(function (x) { return x.service === freteState.chosen; })) {
        freteState.chosen = (freteState.options[0] || {}).service || "economico";
      }
      rows = (freteState.options || []).map(function (o) {
        return optRow(o.name, o.days, o.cents, o.service, o.service === freteState.chosen);
      }).join("");
    }
    freteBox.innerHTML = '<div class="hyst-ship">' + rows + "</div>";
    freteBox.querySelectorAll("input[name=hyst-frete]").forEach(function (r) {
      r.addEventListener("change", function () {
        freteState.chosen = this.value;
        freteBox.querySelectorAll(".hyst-opt").forEach(function (l) {
          l.classList.toggle("sel", l.querySelector("input").checked);
        });
        renderSummary();
      });
    });
    renderSummary();
  }

  /* ── passo 1 → cria o PaymentIntent e monta o Payment Element ────────── */
  function onSubmit(ev) {
    ev.preventDefault();
    errEl.textContent = "";
    var lines = cartLines();
    if (!lines.length) { errEl.textContent = "Sacola vazia."; return; }
    var f = form.elements;
    var name = f.name.value.trim();
    var phone = f.phone.value.replace(/\D/g, "");
    var email = f.email.value.trim();
    var cpf = f.cpf.value.replace(/\D/g, "");
    var cep = f.cep.value.replace(/\D/g, "");
    var street = f.street.value.trim();
    var number = f.number.value.trim();
    var city = f.city.value.trim();
    var state = f.state.value.trim().toUpperCase();
    if (name.split(/\s+/).length < 2) { errEl.textContent = "Digite o nome completo."; f.name.focus(); return; }
    if (phone.length < 10) { errEl.textContent = "Celular inválido (DDD + número)."; f.phone.focus(); return; }
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { errEl.textContent = "E-mail inválido."; f.email.focus(); return; }
    if (!validCPF(cpf)) { errEl.textContent = "CPF inválido."; f.cpf.focus(); return; }
    if (cep.length !== 8) { errEl.textContent = "CEP inválido."; f.cep.focus(); return; }
    if (!street) { errEl.textContent = "Digite a rua."; f.street.focus(); return; }
    if (!number) { errEl.textContent = "Digite o número."; f.number.focus(); return; }
    if (!city || state.length !== 2) { errEl.textContent = "Cidade/UF incompletos."; f.city.focus(); return; }
    if (!freteState.options) { errEl.textContent = "Aguarde o cálculo da entrega."; quoteFrete(cep); return; }

    try {
      localStorage.setItem(LS_FORM, JSON.stringify({
        name: name, phone: phone, email: email, cep: f.cep.value, cpf: f.cpf.value,
        street: street, number: number, complement: f.complement.value.trim(),
        neighborhood: f.neighborhood.value.trim(), city: city, state: state
      }));
    } catch (e) {}

    var btn = form.querySelector(".hyst-cta");
    btn.disabled = true;
    var old = btn.textContent;
    btn.textContent = "Preparando o pagamento…";

    var body = {
      items: payloadItems(lines),
      meta: cartMeta(),
      origin: location.origin,
      customer: { name: name, email: email, phone: phone, document: cpf },
      address: { cep: cep, street: street, number: number,
                 complement: f.complement.value.trim(),
                 neighborhood: f.neighborhood.value.trim(),
                 city: city, state: state },
      shipping: { service: freteState.free ? "economico" : freteState.chosen }
    };
    var code = couponCode();
    if (code) body.coupon = code;

    Promise.all([
      loadStripeJs(),
      fetch(API + "/stripe/checkout", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body)
      }).then(function (r) {
        if (!r.ok) return r.json().then(function (j) {
          throw new Error(j && j.detail ? j.detail : "bridge " + r.status);
        });
        return r.json();
      })
    ]).then(function (res) {
      var d = res[1];
      if (!d || !d.clientSecret || !d.publishableKey) throw new Error("checkout indisponível");
      mountPayment(d);
      btn.disabled = false;
      btn.textContent = old;
    }).catch(function (e) {
      btn.disabled = false;
      btn.textContent = old;
      errEl.textContent = (e && e.message) || "Não consegui preparar o pagamento — tenta de novo.";
    });
  }

  /* ── passo 2: Payment Element (tema HYU) + confirmPayment ────────────── */
  function mountPayment(d) {
    var stripe = window.Stripe(d.publishableKey);
    var appearance = {
      theme: "night",
      variables: {
        colorPrimary: LIME,
        colorBackground: "#0f1115",
        colorText: "#f3efe4",
        colorTextSecondary: "#8b93a1",
        colorDanger: "#ff9d9d",
        borderRadius: "10px",
        fontFamily: "Archivo, ui-sans-serif, system-ui, sans-serif",
        focusOutline: "none",
        focusBoxShadow: "0 0 0 3px rgba(196,244,57,.14)"
      },
      rules: {
        ".Input": { border: "1px solid #333947" },
        ".Input:focus": { borderColor: LIME },
        ".Tab--selected": { borderColor: LIME }
      }
    };
    var elements = stripe.elements({ clientSecret: d.clientSecret, appearance: appearance });
    payState = { stripe: stripe, elements: elements, orderId: d.orderId };
    payElBox.innerHTML = "";
    elements.create("payment").mount(payElBox);
    payTotalEl.textContent = brl(d.totalCents);
    step1.hidden = true;
    step2.hidden = false;
    payErrEl.textContent = "";
    page.scrollTop = 0;
  }

  document.addEventListener("click", function (e) {
    var btn = e.target && e.target.closest && e.target.closest("[data-hyst-pay]");
    if (!btn || !payState) return;
    btn.disabled = true;
    var old = btn.textContent;
    btn.textContent = "Processando…";
    payErrEl.textContent = "";
    payState.stripe.confirmPayment({
      elements: payState.elements,
      confirmParams: {
        return_url: location.origin + "/obrigado/?gw=stripe&ref=" + encodeURIComponent(payState.orderId)
      }
    }).then(function (res) {
      /* só chega aqui em erro imediato (validação/recusa) — sucesso redireciona */
      btn.disabled = false;
      btn.textContent = old;
      if (res && res.error) {
        payErrEl.textContent = res.error.message ||
          "Pagamento não aprovado — confere os dados e tenta de novo.";
      }
    }).catch(function () {
      btn.disabled = false;
      btn.textContent = old;
      payErrEl.textContent = "Não consegui processar — tenta de novo em instantes.";
    });
  });

  /* ── intercepta o Finalizar compra (capture, antes do handler do drawer).
        Assinatura pura segue o fluxo nativo (recorrência Stripe = fase 2). ── */
  document.addEventListener("click", function (e) {
    var btn = e.target && e.target.closest && e.target.closest("[data-cd-checkout]");
    if (!btn) return;
    var lines = cartLines();
    if (!lines.length) return; /* carrinho vazio ou só assinatura → fluxo original */
    e.preventDefault();
    e.stopImmediatePropagation();
    e.stopPropagation();
    openPage();
    var cep = form.elements["cep"].value.replace(/\D/g, "");
    if (cep.length === 8) quoteFrete(cep);
    else renderSummary();
  }, true);
})();
