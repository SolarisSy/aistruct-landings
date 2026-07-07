/* HYU — checkout em 2 etapas, passo 1 com a estética do checkout Paggins
 * (layout Marketplace): fundo cinza, cards brancos, radios e CTA azuis.
 *
 * Intercepta o "Finalizar compra" do drawer e abre uma PÁGINA full-screen que
 * pede só o não-redundante: contato (nome/celular/e-mail — pré-preenchidos na
 * Paggins via customer) + CEP (cota o frete Mandaê no bridge). CPF e endereço
 * completo ficam pro passo 2 (a Paggins coleta; o bridge enriquece o pedido
 * pós-pagamento pra NF-e). POSTa /checkout — o bridge re-cota o frete, embute
 * no preço e devolve o checkoutUrl da Paggins (passo 2).
 *
 * Assinatura (tier "sub") continua no link nativo — não passa por aqui.
 * O cupom do coupon.js é injetado automaticamente (ele patcha window.fetch).
 */
(function () {
  "use strict";
  var API = "https://hyu-cart.tiectu.easypanel.host";
  var LS_CART = "hyu-cart-v2";
  var LS_FORM = "hyu-precheckout-v1";
  var COMBOS = { "kit-energy": 1, "kit-soda": 1, "super-kit": 1, "kit24": 1 };
  var TIER_CENTS = { kit6: 6990, kit12: 11990, kit24: 21990 };
  var TIER_LABEL = { kit6: "6 latas", kit12: "12 latas", kit24: "24 latas" };
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
      return c.items.filter(function (i) {
        return i && i.tier !== "sub" && i.qty >= 1;
      });
    } catch (e) { return []; }
  }
  function payloadItems(lines) {
    return lines.map(function (i) {
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
  function subtotalCents(lines) {
    return lines.reduce(function (s, i) {
      return s + (TIER_CENTS[i.tier] || 0) * i.qty;
    }, 0);
  }
  function brl(cents) {
    return (cents / 100).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  }
  function lineTitle(i) {
    var t = TITLES[i.flavor] || i.flavor;
    if (!COMBOS[i.flavor] && TIER_LABEL[i.tier]) t += " — " + TIER_LABEL[i.tier];
    else if (COMBOS[i.flavor] && TIER_LABEL[i.tier]) t += " · " + TIER_LABEL[i.tier];
    return t;
  }
  function lineImg(i) {
    return "/img/kits/" + (IMGS[i.flavor] || i.flavor) + ".webp";
  }

  /* ── página passo 1 (estética Paggins Marketplace) ───────────────────── */
  var css = "" +
    ".hyck2{position:fixed;inset:0;z-index:9999;background:#F2F4F7;overflow-y:auto;-webkit-overflow-scrolling:touch;color:#1A1A1A;font-family:ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif;display:none}" +
    ".hyck2.open{display:block}" +
    ".hyck2 *{box-sizing:border-box;font-family:inherit}" +
    ".hyck2-wrap{max-width:560px;margin:0 auto;padding:12px 14px calc(28px + env(safe-area-inset-bottom,0px))}" +
    ".hyck2-top{display:flex;align-items:center;gap:.6rem;padding:6px 2px 12px}" +
    ".hyck2-back{display:flex;align-items:center;gap:.35rem;background:none;border:0;padding:6px 8px 6px 2px;font-size:14px;color:#4B5563;cursor:pointer;border-radius:8px}" +
    ".hyck2-back:hover{color:#1A1A1A}" +
    ".hyck2-top .ttl{flex:1;text-align:center;font-weight:700;font-size:15px;color:#111827;margin-right:64px}" +
    ".hyck2-card{background:#fff;border:1px solid #E8EAEE;border-radius:12px;padding:16px;margin:0 0 8px;box-shadow:0 1px 2px rgba(16,24,40,.04)}" +
    ".hyck2 h2{font-size:16px;font-weight:700;color:#111827;margin:18px 2px 10px;letter-spacing:-.01em}" +
    /* resumo */
    ".hyck2-sumhead{width:100%;display:flex;align-items:center;justify-content:space-between;gap:.6rem;background:none;border:0;padding:0;cursor:pointer;font-size:15px;font-weight:600;color:#111827}" +
    ".hyck2-sumhead b{font-weight:700;font-size:15px}" +
    ".hyck2-sumhead .chev{flex:none;margin-left:2px;transition:transform .18s;color:#6B7280}" +
    ".hyck2-sum.open .chev{transform:rotate(180deg)}" +
    ".hyck2-sumbody{display:none;margin-top:14px;border-top:1px solid #EEF0F3;padding-top:12px}" +
    ".hyck2-sum.open .hyck2-sumbody{display:block}" +
    ".hyck2-it{display:flex;align-items:center;gap:.7rem;padding:.45rem 0}" +
    ".hyck2-it img{flex:none;width:44px;height:44px;border-radius:8px;object-fit:cover;background:#F3F4F6;border:1px solid #EEF0F3}" +
    ".hyck2-it .nm{flex:1;min-width:0;font-size:13.5px;color:#111827;line-height:1.3}" +
    ".hyck2-it .nm small{display:block;color:#6B7280;font-size:12px}" +
    ".hyck2-it .pr{font-size:13.5px;font-weight:600;white-space:nowrap}" +
    ".hyck2-tot{border-top:1px solid #EEF0F3;margin-top:8px;padding-top:10px;font-size:13.5px;color:#4B5563}" +
    ".hyck2-tot div{display:flex;justify-content:space-between;padding:.18rem 0}" +
    ".hyck2-tot .tt{font-weight:700;color:#111827;font-size:15px;padding-top:.4rem}" +
    /* campos */
    ".hyck2-f{margin:0 0 12px}" +
    ".hyck2-f:last-child{margin-bottom:2px}" +
    ".hyck2-f label{display:block;font-size:13px;font-weight:500;color:#374151;margin:0 0 6px}" +
    ".hyck2-f input{width:100%;height:46px;padding:0 13px;font-size:16px;color:#1A1A1A;background:#fff;border:1px solid #D7DBE0;border-radius:8px;outline:none;transition:border-color .12s,box-shadow .12s}" +
    ".hyck2-f input::placeholder{color:#9CA3AF}" +
    ".hyck2-f input:focus{border-color:#1E6EF7;box-shadow:0 0 0 3px rgba(30,110,247,.12)}" +
    ".hyck2-f .sub{font-size:12px;color:#6B7280;margin:6px 2px 0}" +
    ".hyck2-phone{display:flex;gap:8px}" +
    ".hyck2-phone .ddi{flex:none;display:flex;align-items:center;gap:6px;height:46px;padding:0 12px;border:1px solid #D7DBE0;border-radius:8px;background:#fff;font-size:14.5px;color:#374151}" +
    ".hyck2-phone input{flex:1}" +
    /* frete */
    ".hyck2-shipwrap{margin-top:4px}" +
    ".hyck2-shiptag{display:flex;align-items:center;gap:.45rem;font-size:12.5px;color:#6B7280;margin:12px 2px 8px}" +
    ".hyck2-ship{border:1px solid #E8EAEE;border-radius:10px;overflow:hidden}" +
    ".hyck2-ship .hd{padding:10px 14px;font-size:13px;font-weight:600;color:#111827;background:#FAFBFC;border-bottom:1px solid #EEF0F3}" +
    ".hyck2-opt{display:flex;align-items:center;gap:.75rem;padding:13px 14px;cursor:pointer;border-bottom:1px solid #EEF0F3;background:#fff;transition:background .12s}" +
    ".hyck2-opt:last-child{border-bottom:0}" +
    ".hyck2-opt input{display:none}" +
    ".hyck2-opt .dot{flex:none;width:18px;height:18px;border-radius:50%;border:2px solid #C9CED6;display:grid;place-items:center;transition:border-color .12s}" +
    ".hyck2-opt .dot:before{content:'';width:9px;height:9px;border-radius:50%;background:transparent;transition:background .12s}" +
    ".hyck2-opt.sel .dot{border-color:#1E6EF7}" +
    ".hyck2-opt.sel .dot:before{background:#1E6EF7}" +
    ".hyck2-opt .inf{flex:1;min-width:0;font-size:14px;color:#111827}" +
    ".hyck2-opt .inf small{display:block;font-size:12px;color:#6B7280}" +
    ".hyck2-opt .prc{font-size:14px;font-weight:600;white-space:nowrap}" +
    ".hyck2-calc{display:flex;align-items:center;gap:.6rem;font-size:13.5px;color:#6B7280;padding:10px 2px}" +
    ".hyck2-spin{width:15px;height:15px;border:2px solid rgba(30,110,247,.25);border-top-color:#1E6EF7;border-radius:50%;animation:hyck2spin .7s linear infinite;flex:none}" +
    "@keyframes hyck2spin{to{transform:rotate(360deg)}}" +
    ".hyck2-hint{font-size:13px;color:#6B7280;padding:8px 2px 0}" +
    ".hyck2-hint.err{color:#DC2626}" +
    /* erro + CTA */
    ".hyck2-err{color:#DC2626;font-size:13px;min-height:1.2em;margin:10px 4px 0}" +
    ".hyck2-cta{width:100%;height:50px;margin-top:10px;background:#1E6EF7;color:#fff;font-size:15.5px;font-weight:700;border:0;border-radius:8px;cursor:pointer;transition:background .12s,opacity .12s}" +
    ".hyck2-cta:hover{background:#1a5fd8}" +
    ".hyck2-cta:disabled{opacity:.6;cursor:default}" +
    ".hyck2-note{text-align:center;font-size:12.5px;color:#6B7280;margin:12px 6px 0;line-height:1.45}" +
    ".hyck2-foot{text-align:center;font-size:11.5px;color:#9CA3AF;margin:18px 0 0}" +
    ".hyck2-foot b{color:#6B7280;font-weight:600}" +
    "@media(min-width:640px){.hyck2-wrap{padding-top:28px}}";

  var CHEV = '<svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
  var LOCK = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-1.5px"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
  var TRUCK = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M1 3h15v13H1zM16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>';

  var html = "" +
    '<div class="hyck2-wrap">' +
    '<div class="hyck2-top">' +
    '<button type="button" class="hyck2-back" data-hyck-close>' +
    '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>Voltar</button>' +
    '<span class="ttl">Finalizar compra</span>' +
    "</div>" +
    '<section class="hyck2-card hyck2-sum" data-hyck-sum>' +
    '<button type="button" class="hyck2-sumhead" data-hyck-sumtoggle>Resumo da compra<span style="flex:1"></span><b data-hyck-sumtotal></b>' + CHEV + "</button>" +
    '<div class="hyck2-sumbody" data-hyck-sumbody></div>' +
    "</section>" +
    '<form data-hyck-form novalidate>' +
    "<h2>Dados de contato</h2>" +
    '<section class="hyck2-card">' +
    '<div class="hyck2-f"><label>Nome Completo</label><input name="name" autocomplete="name" placeholder="Nome completo"></div>' +
    '<div class="hyck2-f"><label>Telefone de contato</label><div class="hyck2-phone"><span class="ddi">&#127463;&#127479; +55</span><input name="phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="Insira seu celular"></div></div>' +
    '<div class="hyck2-f"><label>E-mail</label><input name="email" type="email" autocomplete="email" placeholder="Insira seu e-mail"><p class="sub">Você recebe a confirmação e o rastreio do pedido nesse e-mail.</p></div>' +
    "</section>" +
    "<h2>Escolha quando sua compra chegará</h2>" +
    '<section class="hyck2-card">' +
    '<div class="hyck2-f"><label>CEP</label><input name="cep" inputmode="numeric" autocomplete="postal-code" placeholder="Digite seu CEP"></div>' +
    '<div class="hyck2-shipwrap">' +
    '<div class="hyck2-shiptag" data-hyck-shiptag hidden>' + TRUCK + "<span>Entrega calculada pro seu CEP</span></div>" +
    '<div data-hyck-frete><div class="hyck2-hint">Digite o CEP acima pra ver as opções e prazos de entrega.</div></div>' +
    "</div></section>" +
    '<p class="hyck2-err" data-hyck-err></p>' +
    '<button type="submit" class="hyck2-cta">Continuar para o pagamento</button>' +
    '<p class="hyck2-note">Na próxima etapa você confirma o endereço e escolhe como pagar:<br>Pix, cartão de crédito, PayPal ou Google Pay.</p>' +
    '<p class="hyck2-foot">' + LOCK + " Pagamento processado com segurança por <b>Paggins</b></p>" +
    "</form></div>";

  var page = null, form = null, freteBox = null, errEl = null;
  var sumEl = null, sumTotalEl = null, sumBodyEl = null, shipTagEl = null;
  var freteState = { cep: "", options: null, free: false, chosen: "economico" };

  function ensurePage() {
    if (page) return;
    var st = document.createElement("style");
    st.textContent = css;
    document.head.appendChild(st);
    page = document.createElement("div");
    page.className = "hyck2";
    page.setAttribute("role", "dialog");
    page.setAttribute("aria-modal", "true");
    page.setAttribute("aria-label", "Finalizar compra");
    page.innerHTML = html;
    document.body.appendChild(page);
    form = page.querySelector("[data-hyck-form]");
    freteBox = page.querySelector("[data-hyck-frete]");
    errEl = page.querySelector("[data-hyck-err]");
    sumEl = page.querySelector("[data-hyck-sum]");
    sumTotalEl = page.querySelector("[data-hyck-sumtotal]");
    sumBodyEl = page.querySelector("[data-hyck-sumbody]");
    shipTagEl = page.querySelector("[data-hyck-shiptag]");

    page.addEventListener("click", function (e) {
      if (e.target.closest("[data-hyck-close]")) closePage();
      if (e.target.closest("[data-hyck-sumtoggle]")) sumEl.classList.toggle("open");
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closePage();
    });

    /* máscaras leves */
    form.elements["cep"].addEventListener("input", function () {
      var d = this.value.replace(/\D/g, "").slice(0, 8);
      this.value = d.replace(/(\d{5})(\d)/, "$1-$2");
      if (d.length === 8) quoteFrete(d);
    });
    form.elements["phone"].addEventListener("input", function () {
      var d = this.value.replace(/\D/g, "").slice(0, 11);
      this.value = d.replace(/(\d{2})(\d)/, "($1) $2").replace(/(\d{5})(\d)/, "$1-$2");
    });
    form.addEventListener("submit", onSubmit);
    form.addEventListener("input", function () { errEl.textContent = ""; });

    /* restaura dados salvos (compat com o form antigo) */
    try {
      var saved = JSON.parse(localStorage.getItem(LS_FORM) || "{}");
      ["name", "phone", "email", "cep"].forEach(function (k) {
        if (saved[k]) form.elements[k].value = saved[k];
      });
      var cepd = (saved.cep || "").replace(/\D/g, "");
      if (cepd.length === 8) quoteFrete(cepd);
    } catch (e) {}
  }

  function openPage() {
    ensurePage();
    renderSummary();
    page.classList.add("open");
    document.body.style.overflow = "hidden";
    page.scrollTop = 0;
  }
  function closePage() {
    if (page && page.classList.contains("open")) {
      page.classList.remove("open");
      document.body.style.overflow = "";
    }
  }

  /* ── resumo da compra ────────────────────────────────────────────────── */
  function renderSummary() {
    var lines = cartLines();
    var sub = subtotalCents(lines);
    var frete = freteState.options ? (freteState.free ? 0 : chosenCents()) : null;
    sumTotalEl.textContent = brl(sub + (frete || 0));
    var rows = lines.map(function (i) {
      return '<div class="hyck2-it"><img src="' + lineImg(i) + '" alt="" loading="lazy" onerror="this.style.display=\'none\'">' +
        '<div class="nm">' + lineTitle(i) + "<small>Quantidade: " + i.qty + "</small></div>" +
        '<div class="pr">' + brl((TIER_CENTS[i.tier] || 0) * i.qty) + "</div></div>";
    }).join("");
    var tot = '<div class="hyck2-tot"><div><span>Subtotal</span><span>' + brl(sub) + "</span></div>";
    if (frete !== null) {
      tot += "<div><span>Frete</span><span>" + (frete === 0 ? "Grátis" : brl(frete)) + "</span></div>";
      tot += '<div class="tt"><span>Total</span><span>' + brl(sub + frete) + "</span></div>";
    } else {
      tot += '<div class="tt"><span>Total</span><span>' + brl(sub) + " + frete</span></div>";
    }
    tot += "</div>";
    sumBodyEl.innerHTML = rows + tot;
  }

  /* ── CEP → cotação Mandaê ────────────────────────────────────────────── */
  function quoteFrete(cep) {
    var lines = cartLines();
    if (!lines.length) return;
    freteState.cep = cep;
    freteState.options = null;
    shipTagEl.hidden = true;
    freteBox.innerHTML = '<div class="hyck2-calc"><span class="hyck2-spin"></span>Calculando a entrega pro seu CEP…</div>';
    fetch(API + "/frete", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ cep: cep, items: payloadItems(lines) })
    }).then(function (r) {
      if (!r.ok) throw new Error("frete " + r.status);
      return r.json();
    }).then(function (d) {
      if (freteState.cep !== cep) return; // resposta velha
      freteState.options = d.options || [];
      freteState.free = !!d.free;
      renderFrete();
      try { freteBox.scrollIntoView({ behavior: "smooth", block: "nearest" }); } catch (e) {}
    }).catch(function () {
      freteBox.innerHTML = '<div class="hyck2-hint err">Não consegui calcular a entrega — confere o CEP e tenta de novo.</div>';
    });
  }

  function chosenCents() {
    if (!freteState.options) return 0;
    var o = freteState.options.find(function (x) { return x.service === freteState.chosen; });
    return o ? o.cents : (freteState.options[0] ? freteState.options[0].cents : 0);
  }

  function optRow(name, days, cents, value, sel) {
    return '<label class="hyck2-opt' + (sel ? " sel" : "") + '">' +
      '<input type="radio" name="hyck-frete" value="' + value + '"' + (sel ? " checked" : "") + ">" +
      '<span class="dot"></span>' +
      '<span class="inf">' + name + (days ? "<small>chega em ~" + days + " dia" + (days > 1 ? "s" : "") + " úteis</small>" : "") + "</span>" +
      '<span class="prc">' + (cents === 0 ? "R$ 0,00" : brl(cents)) + "</span></label>";
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
    freteBox.innerHTML = '<div class="hyck2-ship"><div class="hd">Envio 1</div>' + rows + "</div>";
    shipTagEl.hidden = false;
    freteBox.querySelectorAll("input[name=hyck-frete]").forEach(function (r) {
      r.addEventListener("change", function () {
        freteState.chosen = this.value;
        freteBox.querySelectorAll(".hyck2-opt").forEach(function (l) {
          l.classList.toggle("sel", l.querySelector("input").checked);
        });
        renderSummary();
      });
    });
    renderSummary();
  }

  /* ── submit ──────────────────────────────────────────────────────────── */
  function onSubmit(ev) {
    ev.preventDefault();
    errEl.textContent = "";
    var lines = cartLines();
    if (!lines.length) { errEl.textContent = "Sacola vazia."; return; }
    var f = form.elements; // ⚠️ form.name é o atributo do <form>, não o input
    var name = f.name.value.trim();
    var phone = f.phone.value.replace(/\D/g, "");
    var email = f.email.value.trim();
    var cep = f.cep.value.replace(/\D/g, "");
    if (name.split(/\s+/).length < 2) { errEl.textContent = "Digite o nome completo."; f.name.focus(); return; }
    if (phone.length < 10) { errEl.textContent = "Celular inválido (DDD + número)."; f.phone.focus(); return; }
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { errEl.textContent = "E-mail inválido."; f.email.focus(); return; }
    if (cep.length !== 8) { errEl.textContent = "CEP inválido."; f.cep.focus(); return; }
    if (!freteState.options) { errEl.textContent = "Aguarde o cálculo da entrega."; quoteFrete(cep); return; }

    try {
      localStorage.setItem(LS_FORM, JSON.stringify({
        name: name, phone: phone, email: email, cep: f.cep.value
      }));
    } catch (e) {}

    var btn = form.querySelector(".hyck2-cta");
    btn.disabled = true;
    var old = btn.textContent;
    btn.textContent = "Só um instante…";

    fetch(API + "/checkout", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        items: payloadItems(lines),
        meta: cartMeta(),
        origin: location.origin,
        customer: { name: name, email: email, phone: phone },
        address: { cep: cep },
        shipping: { service: freteState.free ? "economico" : freteState.chosen }
      })
    }).then(function (r) {
      if (!r.ok) return r.json().then(function (j) {
        throw new Error(j && j.detail ? j.detail : "bridge " + r.status);
      });
      return r.json();
    }).then(function (d) {
      if (!d || !d.checkoutUrl) throw new Error("sem checkoutUrl");
      location.href = d.checkoutUrl;
    }).catch(function (e) {
      btn.disabled = false;
      btn.textContent = old;
      errEl.textContent = (e && e.message) || "Não consegui abrir o pagamento — tenta de novo.";
    });
  }

  /* ── intercepta o Finalizar compra (fase de captura, antes do handler
        original do drawer). Assinatura pura segue o fluxo nativo. ───────── */
  document.addEventListener("click", function (e) {
    var btn = e.target && e.target.closest && e.target.closest("[data-cd-checkout]");
    if (!btn) return;
    var lines = cartLines();
    if (!lines.length) return; // carrinho vazio ou só assinatura → fluxo original
    e.preventDefault();
    e.stopImmediatePropagation();
    e.stopPropagation();
    openPage();
    // sempre re-cota ao abrir: o carrinho pode ter mudado (o backend tem cache)
    var cep = form.elements["cep"].value.replace(/\D/g, "");
    if (cep.length === 8) quoteFrete(cep);
    else renderSummary();
  }, true);
})();
