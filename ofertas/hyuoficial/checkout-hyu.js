/* HYU — pré-checkout com frete Mandaê (patch pós-build, padrão coupon.js).
 *
 * Intercepta o "Finalizar compra" do drawer e abre um modal que coleta os dados
 * do comprador (nome, CPF, WhatsApp, e-mail, endereço com ViaCEP) + cota o frete
 * no bridge (/frete, API Mandaê). Depois POSTa /checkout com tudo — o bridge
 * re-cota o frete, embute no preço (SDK Paggins não tem frete dinâmico), salva o
 * pedido completo p/ NF-e (Bling) e devolve o checkoutUrl da Paggins.
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

  /* ── validações ──────────────────────────────────────────────────────── */
  function validCpf(doc) {
    var d = (doc || "").replace(/\D/g, "");
    if (d.length !== 11 || /^(\d)\1{10}$/.test(d)) return false;
    for (var n = 9; n <= 10; n++) {
      var s = 0;
      for (var i = 0; i < n; i++) s += parseInt(d[i], 10) * ((n + 1) - i);
      if ((s * 10) % 11 % 10 !== parseInt(d[n], 10)) return false;
    }
    return true;
  }

  /* ── modal ───────────────────────────────────────────────────────────── */
  var css = "" +
    ".hyck .modal{max-width:520px;width:calc(100vw - 2rem);max-height:92vh;overflow-y:auto}" +
    ".hyck-grid{display:grid;grid-template-columns:1fr 1fr;gap:.55rem .6rem;margin:.7rem 0}" +
    ".hyck-grid .full{grid-column:1/-1}" +
    ".hyck-grid label{display:block;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#667;margin-bottom:.18rem}" +
    ".hyck-grid input{width:100%;box-sizing:border-box;padding:.55rem .65rem;border:1px solid #d7dbe0;border-radius:9px;font:inherit;font-size:.92rem}" +
    ".hyck-grid input:focus{outline:2px solid #A8CC30;border-color:#A8CC30}" +
    ".hyck-err{color:#c0392b;font-size:.8rem;min-height:1.1em;margin:.15rem 0}" +
    ".hyck-frete{border:1px dashed #cbd2d9;border-radius:12px;padding:.6rem .75rem;margin:.5rem 0}" +
    ".hyck-frete .opt{display:flex;align-items:center;gap:.55rem;padding:.35rem 0;cursor:pointer}" +
    ".hyck-frete .opt b{flex:1;font-size:.92rem}" +
    ".hyck-frete .opt span{font-size:.8rem;color:#667}" +
    ".hyck-free{background:#e9f7d3;color:#3a6b12;font-weight:800;border-radius:10px;padding:.55rem .8rem;font-size:.92rem}" +
    ".hyck-total{display:flex;justify-content:space-between;font-weight:800;margin:.6rem 0 .2rem;font-size:1.02rem}" +
    ".hyck-pay{width:100%;margin-top:.4rem}" +
    ".hyck-muted{font-size:.74rem;color:#889;margin:.3rem 0 0}";

  var html = "" +
    '<div class="modal" role="dialog" aria-modal="true" aria-label="Dados de entrega">' +
    '<div class="modal__head"><h3>Dados de entrega</h3>' +
    '<button class="modal__x" type="button" data-hyck-close aria-label="Fechar">&times;</button></div>' +
    '<form data-hyck-form novalidate>' +
    '<div class="hyck-grid">' +
    '<div class="full"><label>Nome completo</label><input name="name" autocomplete="name" required></div>' +
    '<div><label>CPF</label><input name="document" inputmode="numeric" autocomplete="off" placeholder="000.000.000-00" required></div>' +
    '<div><label>WhatsApp</label><input name="phone" inputmode="tel" autocomplete="tel" placeholder="(11) 99999-0000" required></div>' +
    '<div class="full"><label>E-mail</label><input name="email" type="email" autocomplete="email" required></div>' +
    '<div><label>CEP</label><input name="cep" inputmode="numeric" autocomplete="postal-code" placeholder="00000-000" required></div>' +
    '<div><label>N&uacute;mero</label><input name="number" autocomplete="address-line2" required></div>' +
    '<div class="full"><label>Endere&ccedil;o</label><input name="street" autocomplete="address-line1" required></div>' +
    '<div><label>Complemento</label><input name="complement" placeholder="opcional"></div>' +
    '<div><label>Bairro</label><input name="neighborhood"></div>' +
    '<div><label>Cidade</label><input name="city" required></div>' +
    '<div><label>UF</label><input name="state" maxlength="2" required style="text-transform:uppercase"></div>' +
    "</div>" +
    '<div data-hyck-frete class="hyck-frete" hidden></div>' +
    '<p class="hyck-err" data-hyck-err></p>' +
    '<div class="hyck-total" data-hyck-total hidden></div>' +
    '<button type="submit" class="btn btn--lime hyck-pay">Ir para o pagamento &rarr;</button>' +
    '<p class="hyck-muted">Pagamento seguro via Paggins &middot; PIX, cart&atilde;o ou PayPal.</p>' +
    "</form></div>";

  var back = null, form = null, freteBox = null, errEl = null, totalEl = null;
  var freteState = { cep: "", options: null, free: false, chosen: "economico" };

  function ensureModal() {
    if (back) return;
    var st = document.createElement("style");
    st.textContent = css;
    document.head.appendChild(st);
    back = document.createElement("div");
    back.className = "modal-back hyck";
    back.innerHTML = html;
    document.body.appendChild(back);
    form = back.querySelector("[data-hyck-form]");
    freteBox = back.querySelector("[data-hyck-frete]");
    errEl = back.querySelector("[data-hyck-err]");
    totalEl = back.querySelector("[data-hyck-total]");

    back.addEventListener("click", function (e) {
      if (e.target === back || e.target.closest("[data-hyck-close]")) closeModal();
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeModal();
    });

    /* máscaras leves */
    form.elements["document"].addEventListener("input", function () {
      var d = this.value.replace(/\D/g, "").slice(0, 11);
      this.value = d.replace(/(\d{3})(\d)/, "$1.$2").replace(/(\d{3})(\d)/, "$1.$2")
                    .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
    });
    form.elements["cep"].addEventListener("input", function () {
      var d = this.value.replace(/\D/g, "").slice(0, 8);
      this.value = d.replace(/(\d{5})(\d)/, "$1-$2");
      if (d.length === 8) onCep(d);
    });
    form.elements["phone"].addEventListener("input", function () {
      var d = this.value.replace(/\D/g, "").slice(0, 11);
      this.value = d.replace(/(\d{2})(\d)/, "($1) $2").replace(/(\d{5})(\d)/, "$1-$2");
    });
    form.addEventListener("submit", onSubmit);

    /* restaura dados salvos */
    try {
      var saved = JSON.parse(localStorage.getItem(LS_FORM) || "{}");
      ["name", "document", "phone", "email", "cep", "number", "street",
       "complement", "neighborhood", "city", "state"].forEach(function (k) {
        if (saved[k]) form.elements[k].value = saved[k];
      });
      var cepd = (saved.cep || "").replace(/\D/g, "");
      if (cepd.length === 8) onCep(cepd);
    } catch (e) {}
  }

  function openModal() {
    ensureModal();
    back.classList.add("open");
    document.body.style.overflow = "hidden";
    setTimeout(function () { form.elements["name"].focus(); }, 80);
  }
  function closeModal() {
    if (back && back.classList.contains("open")) {
      back.classList.remove("open");
      document.body.style.overflow = "";
    }
  }

  /* ── CEP → ViaCEP + cotação ──────────────────────────────────────────── */
  function onCep(cep) {
    fetch("https://viacep.com.br/ws/" + cep + "/json/")
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d && !d.erro) {
          if (d.logradouro && !form.elements["street"].value) form.elements["street"].value = d.logradouro;
          if (d.bairro && !form.elements["neighborhood"].value) form.elements["neighborhood"].value = d.bairro;
          if (d.localidade) form.elements["city"].value = d.localidade;
          if (d.uf) form.elements["state"].value = d.uf;
        }
      }).catch(function () {});
    quoteFrete(cep);
  }

  function quoteFrete(cep) {
    var lines = cartLines();
    if (!lines.length) return;
    freteState.cep = cep;
    freteState.options = null;
    freteBox.hidden = false;
    freteBox.innerHTML = '<span class="hyck-muted">Calculando frete…</span>';
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
    }).catch(function () {
      freteBox.innerHTML = '<span class="hyck-err">Não consegui calcular o frete — confere o CEP.</span>';
    });
  }

  function chosenCents() {
    if (!freteState.options) return 0;
    var o = freteState.options.find(function (x) { return x.service === freteState.chosen; });
    return o ? o.cents : (freteState.options[0] ? freteState.options[0].cents : 0);
  }

  function renderFrete() {
    var lines = cartLines();
    if (freteState.free) {
      freteState.chosen = "economico";
      var dias = "";
      var eco = (freteState.options || []).find(function (o) { return o.service === "economico"; });
      if (eco && eco.days) dias = " · chega em ~" + eco.days + " dia" + (eco.days > 1 ? "s" : "") + " úteis + despacho";
      freteBox.innerHTML = '<div class="hyck-free">🎉 Frete grátis' + dias + "</div>";
    } else {
      freteBox.innerHTML = (freteState.options || []).map(function (o, i) {
        return '<label class="opt"><input type="radio" name="hyck-frete" value="' + o.service + '"' +
          (o.service === freteState.chosen || (!i && !freteState.options.some(function (x) { return x.service === freteState.chosen; })) ? " checked" : "") +
          "><b>" + o.name + " — " + brl(o.cents) + "</b><span>~" + o.days + " dia" + (o.days > 1 ? "s" : "") + " úteis</span></label>";
      }).join("");
      freteBox.querySelectorAll("input[name=hyck-frete]").forEach(function (r) {
        r.addEventListener("change", function () { freteState.chosen = this.value; renderTotal(); });
      });
    }
    renderTotal();
  }

  function renderTotal() {
    var lines = cartLines();
    var sub = subtotalCents(lines);
    var frete = freteState.free ? 0 : chosenCents();
    totalEl.hidden = false;
    totalEl.innerHTML = "<span>Total com frete</span><span>" + brl(sub + frete) + "</span>";
  }

  /* ── submit ──────────────────────────────────────────────────────────── */
  function onSubmit(ev) {
    ev.preventDefault();
    errEl.textContent = "";
    var lines = cartLines();
    if (!lines.length) { errEl.textContent = "Sacola vazia."; return; }
    var f = form.elements; // ⚠️ form.name é o atributo do <form>, não o input
    var name = f.name.value.trim();
    var doc = f.document.value.replace(/\D/g, "");
    var phone = f.phone.value.replace(/\D/g, "");
    var email = f.email.value.trim();
    var cep = f.cep.value.replace(/\D/g, "");
    if (name.split(/\s+/).length < 2) { errEl.textContent = "Digite o nome completo."; f.name.focus(); return; }
    if (!validCpf(doc)) { errEl.textContent = "CPF inválido."; f.document.focus(); return; }
    if (phone.length < 10) { errEl.textContent = "WhatsApp inválido (DDD + número)."; f.phone.focus(); return; }
    if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { errEl.textContent = "E-mail inválido."; f.email.focus(); return; }
    if (cep.length !== 8) { errEl.textContent = "CEP inválido."; f.cep.focus(); return; }
    if (!f.street.value.trim() || !f.number.value.trim() || !f.city.value.trim() || f.state.value.trim().length !== 2) {
      errEl.textContent = "Complete o endereço (rua, número, cidade e UF)."; return;
    }
    if (!freteState.options) { errEl.textContent = "Aguarde o cálculo do frete."; quoteFrete(cep); return; }

    var data = {
      name: name, document: f.document.value, phone: phone, email: email,
      cep: f.cep.value, number: f.number.value.trim(), street: f.street.value.trim(),
      complement: f.complement.value.trim(), neighborhood: f.neighborhood.value.trim(),
      city: f.city.value.trim(), state: f.state.value.trim().toUpperCase()
    };
    try { localStorage.setItem(LS_FORM, JSON.stringify(data)); } catch (e) {}

    var btn = form.querySelector(".hyck-pay");
    btn.disabled = true;
    var old = btn.textContent;
    btn.textContent = "Processando…";

    fetch(API + "/checkout", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        items: payloadItems(lines),
        meta: cartMeta(),
        origin: location.origin,
        customer: { name: data.name, document: doc, email: data.email, phone: phone },
        address: {
          cep: cep, street: data.street, number: data.number,
          complement: data.complement, neighborhood: data.neighborhood,
          city: data.city, state: data.state
        },
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
    openModal();
    // sempre re-cota ao abrir: o carrinho pode ter mudado (o backend tem cache)
    var cep = form.elements["cep"].value.replace(/\D/g, "");
    if (cep.length === 8) quoteFrete(cep);
  }, true);
})();
