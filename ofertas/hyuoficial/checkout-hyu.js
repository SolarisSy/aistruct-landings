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

  /* ── modal (identidade HYU: ink + lime, bottom-sheet no mobile) ───────── */
  var css = "" +
    ".hyck.modal-back{padding:20px}" +
    ".hyck .modal{width:min(540px,100%);max-height:min(92dvh,860px);padding:0;overflow:hidden;display:flex;flex-direction:column}" +
    ".hyck-head{display:flex;align-items:flex-start;justify-content:space-between;gap:.8rem;padding:22px 24px 14px}" +
    ".hyck-head h3{display:flex;align-items:center;gap:.55rem;font-family:inherit;font-weight:800;font-size:1.3rem;letter-spacing:-.01em;line-height:1.15;margin:0;color:var(--paper,#fff)}" +
    ".hyck-head h3:before{content:'';width:5px;height:1.25em;border-radius:3px;background:var(--lime,#A8CC30);flex:none}" +
    ".hyck-head p{margin:.45rem 0 0;font-size:.82rem;color:color-mix(in srgb,var(--paper,#fff) 55%,transparent)}" +
    ".hyck-x{flex:none;font-size:1.6rem;line-height:1;width:38px;height:38px;display:grid;place-items:center;border:0;border-radius:8px;background:none;color:var(--paper,#fff);cursor:pointer}" +
    ".hyck-x:hover{background:#ffffff14}" +
    ".hyck-form{display:flex;flex-direction:column;min-height:0;flex:1}" +
    ".hyck-body{overflow-y:auto;padding:0 24px 14px;min-height:0}" +
    ".hyck-sec{display:flex;align-items:center;gap:.6rem;margin:1.05rem 0 .55rem;font-family:var(--mono,monospace);font-size:.68rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--lime,#A8CC30)}" +
    ".hyck-sec:first-child{margin-top:.2rem}" +
    ".hyck-sec:after{content:'';flex:1;height:1px;background:color-mix(in srgb,var(--paper,#fff) 12%,transparent)}" +
    ".hyck-grid{display:grid;grid-template-columns:1fr 1fr;gap:.6rem}" +
    ".hyck-grid .full{grid-column:1/-1}" +
    ".hyck-duo{grid-column:1/-1;display:grid;gap:.6rem}" +
    ".hyck-duo.cepnum{grid-template-columns:1fr 108px}" +
    ".hyck-duo.ciduf{grid-template-columns:1fr 84px}" +
    ".hyck-grid label{display:block;font-family:var(--mono,monospace);font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:color-mix(in srgb,var(--paper,#fff) 48%,transparent);margin:0 0 .28rem .1rem}" +
    ".hyck-grid input{width:100%;box-sizing:border-box;padding:.72rem .85rem;font-size:16px;font-family:inherit;background:var(--ink,#111);color:var(--paper,#fff);border:1.5px solid color-mix(in srgb,var(--paper,#fff) 16%,transparent);border-radius:10px;transition:border-color .15s}" +
    ".hyck-grid input::placeholder{color:color-mix(in srgb,var(--paper,#fff) 30%,transparent)}" +
    ".hyck-grid input:focus{outline:none;border-color:var(--lime,#A8CC30);box-shadow:0 0 0 3px color-mix(in srgb,var(--lime,#A8CC30) 22%,transparent)}" +
    ".hyck-frete{display:flex;flex-direction:column;gap:.55rem;margin-top:.1rem}" +
    ".hyck-calc{display:flex;align-items:center;gap:.6rem;font-size:.85rem;color:color-mix(in srgb,var(--paper,#fff) 55%,transparent);padding:.7rem .2rem}" +
    ".hyck-spin{width:15px;height:15px;border:2px solid color-mix(in srgb,var(--lime,#A8CC30) 35%,transparent);border-top-color:var(--lime,#A8CC30);border-radius:50%;animation:hyckspin .7s linear infinite;flex:none}" +
    "@keyframes hyckspin{to{transform:rotate(360deg)}}" +
    ".hyck-hint{font-size:.8rem;color:color-mix(in srgb,var(--paper,#fff) 45%,transparent);padding:.5rem .2rem}" +
    ".hyck-opt{display:flex;align-items:center;gap:.75rem;padding:.75rem .9rem;border:1.5px solid color-mix(in srgb,var(--paper,#fff) 16%,transparent);border-radius:12px;cursor:pointer;transition:border-color .15s,background .15s}" +
    ".hyck-opt input{display:none}" +
    ".hyck-opt .dot{flex:none;width:17px;height:17px;border-radius:50%;border:2px solid color-mix(in srgb,var(--paper,#fff) 30%,transparent);display:grid;place-items:center}" +
    ".hyck-opt .dot:before{content:'';width:8px;height:8px;border-radius:50%;background:transparent}" +
    ".hyck-opt.sel{border-color:var(--lime,#A8CC30);background:color-mix(in srgb,var(--lime,#A8CC30) 9%,transparent)}" +
    ".hyck-opt.sel .dot{border-color:var(--lime,#A8CC30)}" +
    ".hyck-opt.sel .dot:before{background:var(--lime,#A8CC30)}" +
    ".hyck-opt .inf{flex:1;min-width:0}" +
    ".hyck-opt .inf b{display:block;font-size:.95rem;color:var(--paper,#fff)}" +
    ".hyck-opt .inf span{font-size:.76rem;color:color-mix(in srgb,var(--paper,#fff) 50%,transparent)}" +
    ".hyck-opt .prc{font-weight:800;font-size:1rem;white-space:nowrap;color:var(--paper,#fff)}" +
    ".hyck-opt.sel .prc{color:var(--lime,#A8CC30)}" +
    ".hyck-free{display:flex;align-items:center;gap:.6rem;padding:.8rem .9rem;border-radius:12px;background:color-mix(in srgb,var(--lime,#A8CC30) 14%,transparent);border:1.5px solid color-mix(in srgb,var(--lime,#A8CC30) 45%,transparent);color:var(--lime,#A8CC30);font-weight:800;font-size:.92rem}" +
    ".hyck-free small{display:block;font-weight:400;font-size:.76rem;color:color-mix(in srgb,var(--paper,#fff) 55%,transparent)}" +
    ".hyck-err{color:#ff8d7a;font-size:.82rem;min-height:1.15em;margin:.55rem .1rem 0}" +
    ".hyck-foot{flex:none;padding:14px 24px calc(18px + env(safe-area-inset-bottom,0px));border-top:1px solid color-mix(in srgb,var(--paper,#fff) 10%,transparent);background:color-mix(in srgb,var(--ink,#111) 55%,transparent)}" +
    ".hyck-total{display:flex;justify-content:space-between;align-items:baseline;margin:0 0 .7rem}" +
    ".hyck-total span{font-size:.82rem;color:color-mix(in srgb,var(--paper,#fff) 55%,transparent)}" +
    ".hyck-total b{font-size:1.35rem;color:var(--paper,#fff)}" +
    ".hyck-pay{width:100%;padding:.95rem;font-size:1rem;font-weight:800;font-family:inherit;background:var(--lime,#A8CC30);color:var(--ink,#111);border:0;border-radius:12px;cursor:pointer;box-shadow:4px 4px 0 #0009;transition:transform .12s,box-shadow .12s}" +
    ".hyck-pay:hover{transform:translate(-1px,-1px);box-shadow:5px 5px 0 #0009}" +
    ".hyck-pay:active{transform:translate(2px,2px);box-shadow:1px 1px 0 #0009}" +
    ".hyck-pay:disabled{opacity:.6;cursor:default;transform:none}" +
    ".hyck-muted{font-size:.72rem;color:color-mix(in srgb,var(--paper,#fff) 42%,transparent);margin:.6rem 0 0;text-align:center}" +
    "@media(max-width:560px){" +
    ".hyck.modal-back{padding:0;place-items:end stretch}" +
    ".hyck .modal{width:100%;max-width:none;max-height:96dvh;border-radius:18px 18px 0 0;border-left:0;border-right:0;border-bottom:0}" +
    ".hyck-head{padding:18px 18px 10px}" +
    ".hyck-head h3{font-size:1.45rem}" +
    ".hyck-body{padding:0 18px 12px}" +
    ".hyck-foot{padding:12px 18px calc(14px + env(safe-area-inset-bottom,0px))}" +
    ".hyck-grid{gap:.5rem}" +
    ".hyck-grid .m-full{grid-column:1/-1}" +
    "}";

  var html = "" +
    '<div class="modal" role="dialog" aria-modal="true" aria-label="Dados de entrega">' +
    '<div class="hyck-head"><div><h3>Dados de entrega</h3>' +
    "<p>Rapidinho: s&oacute; pra calcular o frete e emitir sua nota fiscal.</p></div>" +
    '<button class="hyck-x" type="button" data-hyck-close aria-label="Fechar">&times;</button></div>' +
    '<form class="hyck-form" data-hyck-form novalidate>' +
    '<div class="hyck-body">' +
    '<div class="hyck-sec">1 &middot; Contato</div>' +
    '<div class="hyck-grid">' +
    '<div class="full"><label>Nome completo</label><input name="name" autocomplete="name" placeholder="Como no seu documento" required></div>' +
    '<div class="m-full"><label>CPF</label><input name="document" inputmode="numeric" autocomplete="off" placeholder="000.000.000-00" required></div>' +
    '<div class="m-full"><label>WhatsApp</label><input name="phone" inputmode="tel" autocomplete="tel" placeholder="(11) 99999-0000" required></div>' +
    '<div class="full"><label>E-mail</label><input name="email" type="email" autocomplete="email" placeholder="voce@email.com" required></div>' +
    "</div>" +
    '<div class="hyck-sec">2 &middot; Endere&ccedil;o de entrega</div>' +
    '<div class="hyck-grid">' +
    '<div class="hyck-duo cepnum"><div><label>CEP</label><input name="cep" inputmode="numeric" autocomplete="postal-code" placeholder="00000-000" required></div>' +
    '<div><label>N&uacute;mero</label><input name="number" autocomplete="address-line2" placeholder="123" required></div></div>' +
    '<div class="full"><label>Endere&ccedil;o</label><input name="street" autocomplete="address-line1" placeholder="Preenche pelo CEP" required></div>' +
    '<div><label>Complemento</label><input name="complement" placeholder="Apto, bloco…"></div>' +
    '<div><label>Bairro</label><input name="neighborhood"></div>' +
    '<div class="hyck-duo ciduf"><div><label>Cidade</label><input name="city" required></div>' +
    '<div><label>UF</label><input name="state" maxlength="2" required style="text-transform:uppercase"></div></div>' +
    "</div>" +
    '<div class="hyck-sec">3 &middot; Frete</div>' +
    '<div data-hyck-frete class="hyck-frete"><div class="hyck-hint">Digite o CEP acima pra ver as op&ccedil;&otilde;es e prazos.</div></div>' +
    '<p class="hyck-err" data-hyck-err></p>' +
    "</div>" +
    '<div class="hyck-foot">' +
    '<div class="hyck-total" data-hyck-total hidden></div>' +
    '<button type="submit" class="hyck-pay">Ir para o pagamento &rarr;</button>' +
    '<p class="hyck-muted">&#128274; Pagamento seguro via Paggins &middot; PIX, cart&atilde;o ou PayPal</p>' +
    "</div></form></div>";

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
    freteBox.innerHTML = '<div class="hyck-calc"><span class="hyck-spin"></span>Calculando frete pro seu CEP…</div>';
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
      freteBox.innerHTML = '<div class="hyck-hint" style="color:#ff8d7a">Não consegui calcular o frete — confere o CEP e tenta de novo.</div>';
    });
  }

  function chosenCents() {
    if (!freteState.options) return 0;
    var o = freteState.options.find(function (x) { return x.service === freteState.chosen; });
    return o ? o.cents : (freteState.options[0] ? freteState.options[0].cents : 0);
  }

  function renderFrete() {
    if (freteState.free) {
      freteState.chosen = "economico";
      var dias = "";
      var eco = (freteState.options || []).find(function (o) { return o.service === "economico"; });
      if (eco && eco.days) dias = "<small>Chega em ~" + eco.days + " dia" + (eco.days > 1 ? "s" : "") + " úteis após o despacho</small>";
      freteBox.innerHTML = '<div class="hyck-free"><span style="font-size:1.2rem">🎉</span><div>Frete grátis pro seu pedido' + dias + "</div></div>";
    } else {
      if (!(freteState.options || []).some(function (x) { return x.service === freteState.chosen; })) {
        freteState.chosen = (freteState.options[0] || {}).service || "economico";
      }
      freteBox.innerHTML = (freteState.options || []).map(function (o) {
        var sel = o.service === freteState.chosen;
        return '<label class="hyck-opt' + (sel ? " sel" : "") + '"><input type="radio" name="hyck-frete" value="' + o.service + '"' + (sel ? " checked" : "") + ">" +
          '<span class="dot"></span>' +
          '<span class="inf"><b>' + o.name + "</b><span>chega em ~" + o.days + " dia" + (o.days > 1 ? "s" : "") + " úteis</span></span>" +
          '<span class="prc">' + brl(o.cents) + "</span></label>";
      }).join("");
      freteBox.querySelectorAll("input[name=hyck-frete]").forEach(function (r) {
        r.addEventListener("change", function () {
          freteState.chosen = this.value;
          freteBox.querySelectorAll(".hyck-opt").forEach(function (l) {
            l.classList.toggle("sel", l.querySelector("input").checked);
          });
          renderTotal();
        });
      });
    }
    renderTotal();
  }

  function renderTotal() {
    var sub = subtotalCents(cartLines());
    var frete = freteState.free ? 0 : chosenCents();
    totalEl.hidden = false;
    totalEl.innerHTML = "<span>Total com frete</span><b>" + brl(sub + frete) + "</b>";
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
