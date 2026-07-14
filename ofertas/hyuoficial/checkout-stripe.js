/* HYU — checkout 100% NOSSO (Stripe Payment Element) v2 "street".
 *
 * Página full-screen no NOSSO domínio com o DNA visual do site (brutalist/
 * street: Anton display, Space Mono kickers, lime #c4f439, hard-shadow 4px,
 * cantos retos, sticker rotacionado, thumb com cor do sabor). A Stripe entra
 * só como o cofre (Payment Element: cartão/PIX/wallets/3DS). Total SEMPRE do
 * backend (POST /stripe/checkout devolve o clientSecret).
 *
 * UX (Baymard/refs 2026): CTA sticky no rodapé mobile (+5-12% conclusão),
 * selos de confiança junto do campo de pagamento (+15-30% marca nova),
 * resumo recolhível, campos mínimos, steps 01 DADOS → 02 PAGAMENTO.
 *
 * GATE (paralelo ao Paggins): ativa só com ?gw=stripe (localStorage hyu-gw);
 * ?gw=paggins desativa. Virar a chave = remover o gate abaixo.
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
  /* HOSTED=true: após o passo 1 redireciona pro checkout NATIVO da Stripe
     (checkout.stripe.com — confiança da marca converte mais). false = volta
     pro Payment Element embutido (passo 2 na nossa página). */
  var HOSTED = true;
  var LS_CART = "hyu-cart-v2";
  var LS_FORM = "hyu-precheckout-v1";
  var LS_COUPON = "hyu_coupon";
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
  /* cor do sabor no thumb (espelha :root do site) */
  var COLORS = {
    "hot-lemon": "#A8CC30", "maca-verde": "#3CC0E4", "pessego-morango": "#EE7C3C",
    "tropical": "#E4A83C", "maca-vermelha": "#CC5454",
    "kit-energy": "#E4A83C", "kit-soda": "#3CC0E4", "super-kit": "#c4f439",
    "kit24": "#c4f439"
  };
  var FREE_CANS = 12; /* manter = bridge FREE_SHIPPING_CANS */

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
  function lineCans(i) {
    var per = i.tier === "kit12" ? 12 : i.tier === "kit24" ? 24
      : (i.flavor === "super-kit" ? 12 : i.flavor === "kit24" ? 24 : 6);
    return per * i.qty;
  }
  function subtotalCents(lines) {
    return lines.reduce(function (s, i) { return s + lineCents(i) * i.qty; }, 0);
  }
  function totalCans(lines) {
    return lines.reduce(function (s, i) { return s + lineCans(i); }, 0);
  }
  function brl(cents) {
    return (cents / 100).toLocaleString("pt-BR", { style: "currency", currency: "BRL" });
  }
  function mixComp(mix) {
    return Object.keys(mix).sort(function (a, b) { return mix[b] - mix[a]; })
      .map(function (s) { return mix[s] + "× " + (TITLES[s] || s); }).join(" · ");
  }
  function lineTitle(i) {
    if (i.mix) return (MIX_LABEL[i.tier] || "Kit Personalizado");
    var t = TITLES[i.flavor] || i.flavor;
    if (!COMBOS[i.flavor] && TIER_LABEL[i.tier]) t += " — " + TIER_LABEL[i.tier];
    else if (COMBOS[i.flavor] && TIER_LABEL[i.tier]) t += " · " + TIER_LABEL[i.tier];
    return t;
  }
  function lineSub(i) {
    if (i.mix) return mixComp(i.mix);
    return "Quantidade: " + i.qty;
  }
  function lineImg(i) {
    if (i.mix) return "/img/kits/super-kit.webp";
    return "/img/kits/" + (IMGS[i.flavor] || i.flavor) + ".webp";
  }
  function lineColor(i) {
    if (i.mix) return "#c4f439";
    return COLORS[i.flavor] || "#c4f439";
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

  /* ── estética HYU street: ink + lime, cantos retos, hard shadow ──────── */
  var css = "" +
    ".hyst{position:fixed;inset:0;z-index:9999;background:#0c0c0c;overflow-y:auto;-webkit-overflow-scrolling:touch;color:#f3efe4;font-family:'Archivo',ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,Arial,sans-serif;display:none}" +
    ".hyst.open{display:block}" +
    ".hyst *{box-sizing:border-box;font-family:inherit}" +
    ".hyst-glow{position:absolute;top:-140px;left:50%;transform:translateX(-50%);width:520px;height:340px;background:radial-gradient(closest-side,rgba(196,244,57,.14),transparent 70%);pointer-events:none}" +
    /* marquee topo (street) */
    ".hyst-marq{position:sticky;top:0;z-index:5;background:#c4f439;color:#0c0c0c;overflow:hidden;white-space:nowrap;border-bottom:2px solid #0c0c0c}" +
    ".hyst-marq span{display:inline-block;padding:5px 0;font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.68rem;letter-spacing:.14em;text-transform:uppercase;animation:hystmarq 22s linear infinite}" +
    "@keyframes hystmarq{from{transform:translateX(0)}to{transform:translateX(-50%)}}" +
    ".hyst-wrap{position:relative;max-width:560px;margin:0 auto;padding:14px 16px calc(110px + env(safe-area-inset-bottom,0px))}" +
    /* topo */
    ".hyst-top{display:flex;align-items:center;gap:.7rem;padding:8px 0 4px}" +
    ".hyst-back{display:inline-flex;align-items:center;gap:.4rem;background:none;border:1.5px solid rgba(255,255,255,.16);border-radius:999px;padding:7px 14px 7px 10px;font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:#8b93a1;cursor:pointer;transition:color .15s,border-color .15s}" +
    ".hyst-back:hover{color:#f3efe4;border-color:rgba(255,255,255,.4)}" +
    ".hyst-stick{margin-left:auto;font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.62rem;letter-spacing:.08em;text-transform:uppercase;background:#c4f439;color:#0c0c0c;padding:5px 10px;border-radius:3px;border:2px solid #0c0c0c;box-shadow:2px 2px 0 rgba(255,255,255,.14);transform:rotate(2.5deg)}" +
    ".hyst-h1{font-family:'Anton',Impact,sans-serif;font-weight:400;text-transform:uppercase;line-height:.9;letter-spacing:.005em;font-size:clamp(2.1rem,9vw,2.9rem);margin:14px 0 4px}" +
    ".hyst-h1 i{font-style:normal;color:#c4f439}" +
    /* steps */
    ".hyst-steps{display:flex;align-items:center;gap:.7rem;margin:10px 0 18px;font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.66rem;letter-spacing:.14em;text-transform:uppercase;color:#565e6b}" +
    ".hyst-steps .st{display:inline-flex;align-items:center;gap:.45em;transition:color .2s}" +
    ".hyst-steps .st b{font-weight:700;color:inherit}" +
    ".hyst-steps .st.on{color:#c4f439}" +
    ".hyst-steps .ln{flex:1;height:1.5px;background:rgba(255,255,255,.14)}" +
    ".hyst-steps .st.done{color:#8b93a1;text-decoration:line-through;text-decoration-thickness:1.5px}" +
    /* kicker de seção */
    ".hyst-k{display:flex;align-items:center;gap:.6em;font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.68rem;letter-spacing:.2em;text-transform:uppercase;color:#c4f439;margin:22px 2px 10px}" +
    ".hyst-k:after{content:'';flex:1;height:1.5px;background:rgba(255,255,255,.1)}" +
    /* cards */
    ".hyst-card{background:#141412;border:1.5px solid rgba(255,255,255,.12);border-radius:6px;padding:16px;margin:0 0 10px;box-shadow:5px 5px 0 rgba(0,0,0,.55)}" +
    /* resumo */
    ".hyst-sumhead{width:100%;display:flex;align-items:center;gap:.6rem;background:none;border:0;padding:0;cursor:pointer;color:#f3efe4}" +
    ".hyst-sumhead .lbl{font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.68rem;letter-spacing:.14em;text-transform:uppercase;color:#8b93a1}" +
    ".hyst-sumhead .ct{font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.62rem;letter-spacing:.06em;border:1.5px solid rgba(255,255,255,.18);border-radius:999px;padding:3px 9px;color:#f3efe4}" +
    ".hyst-sumhead b{margin-left:auto;font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.95rem;color:#c4f439;font-variant-numeric:tabular-nums}" +
    ".hyst-sumhead .chev{flex:none;transition:transform .2s;color:#8b93a1}" +
    ".hyst-sum.open .chev{transform:rotate(180deg)}" +
    ".hyst-sumbody{display:none;margin-top:14px;border-top:1.5px dashed rgba(255,255,255,.14);padding-top:6px}" +
    ".hyst-sum.open .hyst-sumbody{display:block}" +
    ".hyst-it{display:flex;align-items:center;gap:.8rem;padding:.6rem 0;border-bottom:1px solid rgba(255,255,255,.07)}" +
    ".hyst-it:last-of-type{border-bottom:0}" +
    ".hyst-it .pic{flex:none;width:52px;height:52px;border-radius:4px;border:2px solid #0c0c0c;box-shadow:2.5px 2.5px 0 rgba(0,0,0,.6);display:grid;place-items:center;overflow:hidden}" +
    ".hyst-it .pic img{width:100%;height:100%;object-fit:cover;object-position:left center}" +
    ".hyst-it .nm{flex:1;min-width:0;font-size:.9rem;font-weight:800;line-height:1.25;letter-spacing:.01em}" +
    ".hyst-it .nm small{display:block;font-family:'Space Mono',ui-monospace,monospace;font-weight:400;color:#8b93a1;font-size:.68rem;margin-top:2px;letter-spacing:.03em}" +
    ".hyst-it .pr{font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.82rem;white-space:nowrap;font-variant-numeric:tabular-nums}" +
    ".hyst-tot{margin-top:10px;font-family:'Space Mono',ui-monospace,monospace;font-size:.78rem;color:#8b93a1}" +
    ".hyst-tot div{display:flex;justify-content:space-between;align-items:baseline;padding:.22rem 0}" +
    ".hyst-tot .off{color:#c4f439}" +
    ".hyst-tot .tt{border-top:1.5px dashed rgba(255,255,255,.14);margin-top:.4rem;padding-top:.6rem;color:#f3efe4}" +
    ".hyst-tot .tt span:first-child{font-weight:700;letter-spacing:.14em;text-transform:uppercase;font-size:.68rem}" +
    ".hyst-tot .tt span:last-child{font-family:'Anton',Impact,sans-serif;font-size:1.5rem;color:#c4f439;letter-spacing:.01em}" +
    /* cupom */
    ".hyst-cpn{display:inline-flex;align-items:center;gap:.5em;font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;color:#0c0c0c;background:#c4f439;border:2px solid #0c0c0c;border-radius:3px;box-shadow:2.5px 2.5px 0 rgba(255,255,255,.14);padding:7px 12px;margin:4px 0 0;transform:rotate(-1.2deg)}" +
    /* campos */
    ".hyst-f{margin:0 0 13px}" +
    ".hyst-f:last-child{margin-bottom:2px}" +
    ".hyst-f label{display:block;font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.62rem;letter-spacing:.14em;text-transform:uppercase;color:#8b93a1;margin:0 0 6px}" +
    ".hyst-f input{width:100%;height:48px;padding:0 13px;font-size:16px;font-weight:600;color:#f3efe4;background:#0c0c0c;border:1.5px solid rgba(255,255,255,.16);border-radius:4px;outline:none;transition:border-color .15s,box-shadow .15s}" +
    ".hyst-f input::placeholder{color:#565e6b;font-weight:400}" +
    ".hyst-f input:focus{border-color:#c4f439;box-shadow:3px 3px 0 rgba(196,244,57,.22)}" +
    ".hyst-f .sub{font-family:'Space Mono',ui-monospace,monospace;font-size:.64rem;color:#565e6b;margin:6px 2px 0;letter-spacing:.03em}" +
    ".hyst-row{display:flex;gap:9px}" +
    ".hyst-row .hyst-f{flex:1;min-width:0}" +
    ".hyst-row .hyst-f.sm{flex:0 0 96px}" +
    ".hyst-phone{display:flex;gap:9px}" +
    ".hyst-phone .ddi{flex:none;display:flex;align-items:center;gap:6px;height:48px;padding:0 12px;border:1.5px solid rgba(255,255,255,.16);border-radius:4px;background:#0c0c0c;font-family:'Space Mono',ui-monospace,monospace;font-size:.8rem;color:#8b93a1}" +
    ".hyst-phone input{flex:1}" +
    /* frete */
    ".hyst-ship{margin-top:6px;border:1.5px solid rgba(255,255,255,.12);border-radius:4px;overflow:hidden}" +
    ".hyst-opt{display:flex;align-items:center;gap:.8rem;padding:14px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,.09);background:#0c0c0c;border-left:3px solid transparent;transition:border-color .15s,background .15s}" +
    ".hyst-opt:last-child{border-bottom:0}" +
    ".hyst-opt:hover{background:#111110}" +
    ".hyst-opt input{display:none}" +
    ".hyst-opt .dot{flex:none;width:17px;height:17px;border-radius:50%;border:2px solid #40485a;display:grid;place-items:center;transition:border-color .15s}" +
    ".hyst-opt .dot:before{content:'';width:8px;height:8px;border-radius:50%;background:transparent;transition:background .15s}" +
    ".hyst-opt.sel{border-left-color:#c4f439;background:#141412}" +
    ".hyst-opt.sel .dot{border-color:#c4f439}" +
    ".hyst-opt.sel .dot:before{background:#c4f439}" +
    ".hyst-opt .inf{flex:1;min-width:0;font-size:.88rem;font-weight:800}" +
    ".hyst-opt .inf small{display:block;font-family:'Space Mono',ui-monospace,monospace;font-weight:400;font-size:.66rem;color:#8b93a1;margin-top:2px;letter-spacing:.03em}" +
    ".hyst-opt .prc{font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.82rem;white-space:nowrap}" +
    ".hyst-opt .prc.free{color:#c4f439}" +
    ".hyst-freenote{display:flex;align-items:center;gap:.5em;font-family:'Space Mono',ui-monospace,monospace;font-size:.62rem;letter-spacing:.08em;text-transform:uppercase;color:#565e6b;margin:8px 2px 0}" +
    ".hyst-calc{display:flex;align-items:center;gap:.6rem;font-family:'Space Mono',ui-monospace,monospace;font-size:.72rem;color:#8b93a1;padding:12px 2px;letter-spacing:.03em}" +
    ".hyst-spin{width:14px;height:14px;border:2px solid rgba(196,244,57,.25);border-top-color:#c4f439;border-radius:50%;animation:hystspin .7s linear infinite;flex:none}" +
    "@keyframes hystspin{to{transform:rotate(360deg)}}" +
    ".hyst-hint{font-family:'Space Mono',ui-monospace,monospace;font-size:.7rem;color:#8b93a1;padding:9px 2px 0;letter-spacing:.02em}" +
    ".hyst-hint.err{color:#ff8f8f}" +
    /* erro */
    ".hyst-err{font-family:'Space Mono',ui-monospace,monospace;color:#ff8f8f;font-size:.74rem;min-height:1.2em;margin:12px 2px 0;letter-spacing:.02em}" +
    ".hyst-err:not(:empty):before{content:'! ';font-weight:700}" +
    /* barra sticky (CTA sempre visível — mobile-first) */
    ".hyst-bar{position:fixed;left:0;right:0;bottom:0;z-index:6;background:rgba(12,12,12,.92);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border-top:1.5px solid rgba(255,255,255,.14);padding:10px 16px calc(10px + env(safe-area-inset-bottom,0px))}" +
    ".hyst-bar-in{max-width:560px;margin:0 auto;display:flex;align-items:center;gap:14px}" +
    ".hyst-bar .tot{min-width:0}" +
    ".hyst-bar .tot .l{font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.58rem;letter-spacing:.16em;text-transform:uppercase;color:#8b93a1;display:block}" +
    ".hyst-bar .tot .v{font-family:'Anton',Impact,sans-serif;font-size:1.45rem;line-height:1.1;color:#c4f439;letter-spacing:.01em;white-space:nowrap}" +
    ".hyst-cta{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:.5rem;height:52px;background:#c4f439;color:#0c0c0c;font-family:'Archivo',system-ui,sans-serif;font-size:.95rem;font-weight:800;letter-spacing:.01em;text-transform:uppercase;border:2px solid #0c0c0c;border-radius:4px;cursor:pointer;box-shadow:4px 4px 0 rgba(0,0,0,.9);transition:transform .15s ease,box-shadow .15s ease,opacity .15s}" +
    ".hyst-cta:hover{transform:translate(-2px,-2px);box-shadow:6px 6px 0 rgba(0,0,0,.9)}" +
    ".hyst-cta:active{transform:translate(2px,2px);box-shadow:1px 1px 0 rgba(0,0,0,.9)}" +
    ".hyst-cta:disabled{opacity:.55;cursor:default;transform:none;box-shadow:4px 4px 0 rgba(0,0,0,.9)}" +
    /* passo pagamento */
    ".hyst-payel{min-height:220px}" +
    ".hyst-trust{display:flex;flex-wrap:wrap;gap:7px;margin:12px 0 0}" +
    ".hyst-trust .tc{display:inline-flex;align-items:center;gap:.45em;font-family:'Space Mono',ui-monospace,monospace;font-weight:700;font-size:.6rem;letter-spacing:.08em;text-transform:uppercase;color:#8b93a1;border:1.5px solid rgba(255,255,255,.14);border-radius:999px;padding:5px 11px}" +
    ".hyst-trust .tc svg{color:#c4f439}" +
    ".hyst-editlink{display:block;margin:14px auto 0;background:none;border:0;color:#8b93a1;font-family:'Space Mono',ui-monospace,monospace;font-size:.68rem;letter-spacing:.08em;text-transform:uppercase;cursor:pointer;text-decoration:underline;text-underline-offset:3px;padding:6px}" +
    ".hyst-editlink:hover{color:#f3efe4}" +
    /* nada rola pra "debaixo" da barra sticky (scrollIntoView/focus respeitam) */
    ".hyst input,.hyst-editlink,.hyst-err,.hyst-foot,.hyst-opt,.hyst-note{scroll-margin-bottom:130px}" +
    ".hyst-note{text-align:center;font-family:'Space Mono',ui-monospace,monospace;font-size:.64rem;color:#565e6b;margin:14px 6px 0;line-height:1.6;letter-spacing:.03em}" +
    ".hyst-foot{display:flex;align-items:center;justify-content:center;gap:.5em;font-family:'Space Mono',ui-monospace,monospace;font-size:.6rem;letter-spacing:.1em;text-transform:uppercase;color:#565e6b;margin:22px 0 0}" +
    ".hyst-foot b{color:#8b93a1;font-weight:700}" +
    /* animação de entrada dos passos — SÓ opacity: transform animado (mesmo
       com fill-mode) vira containing block e quebra o position:fixed da barra */
    ".hyst-anim{animation:hystin .3s ease both}" +
    "@keyframes hystin{from{opacity:0}to{opacity:1}}" +
    "@media (prefers-reduced-motion:reduce){.hyst-marq span,.hyst-anim{animation:none}.hyst-cta{transition:none}}" +
    "@media(min-width:640px){.hyst-wrap{padding-top:26px}}";

  var CHEV = '<svg class="chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>';
  var LOCK = '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" style="vertical-align:-1px"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>';
  var TRUCK = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 3h15v13H1zM16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>';
  var ARROW = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
  var MARQ = "HYU &nbsp;●&nbsp; COMPRA SEGURA &nbsp;●&nbsp; ENVIO RASTREADO &nbsp;●&nbsp; 15G DE PROTEÍNA &nbsp;●&nbsp; ZERO AÇÚCAR &nbsp;●&nbsp; ";

  var html = "" +
    '<div class="hyst-marq" aria-hidden="true"><span>' + MARQ + MARQ + "</span></div>" +
    '<div class="hyst-glow"></div>' +
    '<div class="hyst-wrap">' +
    '<div class="hyst-top">' +
    '<button type="button" class="hyst-back" data-hyst-close>' +
    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>Voltar</button>' +
    '<span class="hyst-stick">' + LOCK + " compra segura</span>" +
    "</div>" +
    '<h1 class="hyst-h1">Finalizar <i>compra.</i></h1>' +
    '<div class="hyst-steps" data-hyst-steps>' +
    '<span class="st on" data-st="1"><b>01</b> dados</span><span class="ln"></span>' +
    '<span class="st" data-st="2"><b>02</b> pagamento</span>' +
    "</div>" +
    '<div data-hyst-cpn hidden></div>' +
    /* resumo */
    '<section class="hyst-card hyst-sum" data-hyst-sum>' +
    '<button type="button" class="hyst-sumhead" data-hyst-sumtoggle>' +
    '<span class="lbl">Sua sacola</span><span class="ct" data-hyst-count></span>' +
    "<b data-hyst-sumtotal></b>" + CHEV + "</button>" +
    '<div class="hyst-sumbody" data-hyst-sumbody></div>' +
    "</section>" +
    /* ── passo 1: dados ── */
    '<div data-hyst-step1>' +
    '<form data-hyst-form novalidate>' +
    '<div class="hyst-k">01 / contato</div>' +
    '<section class="hyst-card">' +
    '<div class="hyst-f"><label>Nome completo</label><input name="name" autocomplete="name" placeholder="Seu nome completo"></div>' +
    '<div class="hyst-f"><label>Celular / WhatsApp</label><div class="hyst-phone"><span class="ddi">+55</span><input name="phone" type="tel" inputmode="tel" autocomplete="tel-national" placeholder="(11) 99999-9999"></div></div>' +
    '<div class="hyst-f"><label>E-mail</label><input name="email" type="email" autocomplete="email" placeholder="voce@email.com"><p class="sub">confirmação + rastreio do pedido vão nesse e-mail</p></div>' +
    '<div class="hyst-f"><label>CPF</label><input name="cpf" inputmode="numeric" autocomplete="off" placeholder="000.000.000-00"><p class="sub">usado só pra emitir a nota fiscal</p></div>' +
    "</section>" +
    '<div class="hyst-k">02 / entrega</div>' +
    '<section class="hyst-card">' +
    '<div class="hyst-f"><label>CEP</label><input name="cep" inputmode="numeric" autocomplete="postal-code" placeholder="00000-000"></div>' +
    '<div data-hyst-addr hidden>' +
    '<div class="hyst-row"><div class="hyst-f"><label>Rua</label><input name="street" autocomplete="address-line1" placeholder="Rua / Avenida"></div><div class="hyst-f sm"><label>Número</label><input name="number" inputmode="numeric" placeholder="Nº"></div></div>' +
    '<div class="hyst-f"><label>Complemento <span style="text-transform:none;letter-spacing:.02em">(opcional)</span></label><input name="complement" placeholder="Apto, bloco…"></div>' +
    '<div class="hyst-f"><label>Bairro</label><input name="neighborhood" placeholder="Bairro"></div>' +
    '<div class="hyst-row"><div class="hyst-f"><label>Cidade</label><input name="city" placeholder="Cidade"></div><div class="hyst-f sm"><label>UF</label><input name="state" maxlength="2" placeholder="UF" style="text-transform:uppercase"></div></div>' +
    "</div>" +
    '<div data-hyst-frete><div class="hyst-hint">digite o CEP acima pra ver prazo e valor da entrega</div></div>' +
    '<div class="hyst-freenote" data-hyst-freenote hidden>' + TRUCK + "<span>frete grátis a partir de 12 latas</span></div>" +
    "</section>" +
    '<p class="hyst-err" data-hyst-err></p>' +
    '<p class="hyst-note">na próxima etapa você paga na página segura da Stripe —<br>cartão de crédito, Google Pay e Apple Pay</p>' +
    '<div class="hyst-bar"><div class="hyst-bar-in">' +
    '<div class="tot"><span class="l">Total</span><span class="v" data-hyst-bartotal>—</span></div>' +
    '<button type="submit" class="hyst-cta">Continuar ' + ARROW + "</button>" +
    "</div></div>" +
    "</form></div>" +
    /* ── passo 2: pagamento ── */
    '<div data-hyst-step2 hidden>' +
    '<div class="hyst-k">03 / pagamento</div>' +
    '<section class="hyst-card">' +
    '<div class="hyst-payel" data-hyst-payel></div>' +
    '<div class="hyst-trust">' +
    '<span class="tc">' + LOCK + " criptografado</span>" +
    '<span class="tc">' + TRUCK + " envio rastreado</span>" +
    '<span class="tc">stripe&trade;</span>' +
    '<span class="tc">nota fiscal</span>' +
    "</div></section>" +
    '<p class="hyst-err" data-hyst-payerr></p>' +
    '<button type="button" class="hyst-editlink" data-hyst-edit>&larr; voltar e editar meus dados</button>' +
    '<div class="hyst-bar"><div class="hyst-bar-in">' +
    '<div class="tot"><span class="l">Total</span><span class="v" data-hyst-paytotal>—</span></div>' +
    '<button type="button" class="hyst-cta" data-hyst-pay>Pagar agora</button>' +
    "</div></div>" +
    "</div>" +
    '<p class="hyst-foot">' + LOCK + "&nbsp; pagamento processado por <b>Stripe</b> &middot; dados criptografados</p>" +
    "</div>";

  var page = null, form = null, freteBox = null, errEl = null;
  var sumEl = null, sumTotalEl = null, sumBodyEl = null, addrEl = null, cpnEl = null;
  var step1 = null, step2 = null, payTotalEl = null, payErrEl = null, payElBox = null;
  var countEl = null, barTotalEl = null, freeNoteEl = null, stepsEl = null;
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
    countEl = page.querySelector("[data-hyst-count]");
    barTotalEl = page.querySelector("[data-hyst-bartotal]");
    freeNoteEl = page.querySelector("[data-hyst-freenote]");
    stepsEl = page.querySelector("[data-hyst-steps]");

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
      cpnEl.innerHTML = '<div class="hyst-cpn">🎟️ cupom ' + code +
        " ativo — desconto no total</div>";
    }
  }

  function setStep(n) {
    if (!stepsEl) return;
    stepsEl.querySelectorAll(".st").forEach(function (s) {
      var me = Number(s.dataset.st);
      s.classList.toggle("on", me === n);
      s.classList.toggle("done", me < n);
    });
  }

  function openPage() {
    ensurePage();
    renderSummary();
    showStep1();
    sumEl.classList.add("open");
    page.classList.add("open");
    document.body.style.overflow = "hidden";
    page.scrollTop = 0;
    if (!HOSTED) loadStripeJs().catch(function () {}); /* pré-carrega (embedded) */
  }
  function closePage() {
    if (page && page.classList.contains("open")) {
      page.classList.remove("open");
      document.body.style.overflow = "";
    }
  }
  function showStep1() {
    step1.hidden = false;
    step1.classList.add("hyst-anim");
    step2.hidden = true;
    payErrEl.textContent = "";
    if (payState) { try { payState.elements = null; } catch (e) {} payState = null; }
    payElBox.innerHTML = "";
    setStep(1);
  }

  /* ── resumo ──────────────────────────────────────────────────────────── */
  function renderSummary() {
    var lines = cartLines();
    var sub = subtotalCents(lines);
    var cans = totalCans(lines);
    var frete = freteState.options ? (freteState.free ? 0 : chosenCents()) : null;
    var total = sub + (frete || 0);
    sumTotalEl.textContent = brl(total);
    if (barTotalEl) barTotalEl.textContent = frete === null ? brl(sub) + " + frete" : brl(total);
    if (countEl) countEl.textContent = cans + (cans === 1 ? " lata" : " latas");
    if (freeNoteEl) freeNoteEl.hidden = !(freteState.options && !freteState.free && cans < FREE_CANS);
    var rows = lines.map(function (i) {
      return '<div class="hyst-it">' +
        '<span class="pic" style="background:' + lineColor(i) + '"><img src="' + lineImg(i) + '" alt="" loading="lazy" onerror="this.style.display=\'none\'"></span>' +
        '<div class="nm">' + lineTitle(i) + "<small>" + lineSub(i) +
        (i.mix ? " · personalização inclusa" : "") + "</small></div>" +
        '<div class="pr">' + brl(lineCents(i) * i.qty) + "</div></div>";
    }).join("");
    var tot = '<div class="hyst-tot"><div><span>Subtotal</span><span>' + brl(sub) + "</span></div>";
    if (frete !== null) {
      tot += "<div><span>Frete</span><span" + (frete === 0 ? ' class="off"' : "") + ">" +
        (frete === 0 ? "GRÁTIS" : brl(frete)) + "</span></div>";
      tot += '<div class="tt"><span>Total</span><span>' + brl(total) + "</span></div>";
    } else {
      tot += '<div class="tt"><span>Total</span><span>' + brl(sub) + "</span></div>" +
        '<div><span>+ frete (calculado pelo CEP)</span><span></span></div>';
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
    freteBox.innerHTML = '<div class="hyst-calc"><span class="hyst-spin"></span>calculando a entrega pro seu CEP…</div>';
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
      freteBox.innerHTML = '<div class="hyst-hint err">não consegui calcular a entrega — confere o CEP e tenta de novo</div>';
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
      '<span class="prc' + (cents === 0 ? " free" : "") + '">' + (cents === 0 ? "GRÁTIS" : brl(cents)) + "</span></label>";
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
    var old = btn.innerHTML;
    btn.textContent = "Preparando…";

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

    function postBridge(path) {
      return fetch(API + path, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body)
      }).then(function (r) {
        if (!r.ok) return r.json().then(function (j) {
          throw new Error(j && j.detail ? j.detail : "bridge " + r.status);
        });
        return r.json();
      });
    }

    if (HOSTED) {
      /* checkout NATIVO da Stripe: cria a sessão e redireciona */
      btn.textContent = "Abrindo pagamento seguro…";
      postBridge("/stripe/hosted").then(function (d) {
        if (!d || !d.url) throw new Error("checkout indisponível");
        location.href = d.url;
      }).catch(function (e) {
        btn.disabled = false;
        btn.innerHTML = old;
        errEl.textContent = (e && e.message) || "Não consegui abrir o pagamento — tenta de novo.";
      });
      return;
    }

    Promise.all([loadStripeJs(), postBridge("/stripe/checkout")]).then(function (res) {
      var d = res[1];
      if (!d || !d.clientSecret || !d.publishableKey) throw new Error("checkout indisponível");
      mountPayment(d);
      btn.disabled = false;
      btn.innerHTML = old;
    }).catch(function (e) {
      btn.disabled = false;
      btn.innerHTML = old;
      errEl.textContent = (e && e.message) || "Não consegui preparar o pagamento — tenta de novo.";
    });
  }

  /* ── passo 2: Payment Element (tema HYU street) + confirmPayment ─────── */
  function mountPayment(d) {
    var stripe = window.Stripe(d.publishableKey);
    var appearance = {
      theme: "night",
      variables: {
        colorPrimary: "#c4f439",
        colorBackground: "#0c0c0c",
        colorText: "#f3efe4",
        colorTextSecondary: "#8b93a1",
        colorDanger: "#ff8f8f",
        borderRadius: "4px",
        fontFamily: "Archivo, ui-sans-serif, system-ui, sans-serif",
        focusOutline: "none",
        focusBoxShadow: "3px 3px 0 rgba(196,244,57,.22)"
      },
      rules: {
        ".Input": { border: "1.5px solid rgba(255,255,255,.16)", fontWeight: "600" },
        ".Input:focus": { borderColor: "#c4f439" },
        ".Label": { fontFamily: "'Space Mono', ui-monospace, monospace",
                    textTransform: "uppercase", letterSpacing: ".14em",
                    fontSize: "10px", fontWeight: "700", color: "#8b93a1" },
        ".Tab": { border: "1.5px solid rgba(255,255,255,.16)" },
        ".Tab--selected": { borderColor: "#c4f439", color: "#c4f439" }
      }
    };
    var elements = stripe.elements({ clientSecret: d.clientSecret, appearance: appearance, locale: "pt-BR" });
    payState = { stripe: stripe, elements: elements, orderId: d.orderId };
    payElBox.innerHTML = "";
    elements.create("payment").mount(payElBox);
    payTotalEl.textContent = brl(d.totalCents);
    step1.hidden = true;
    step2.hidden = false;
    step2.classList.remove("hyst-anim");
    void step2.offsetWidth;
    step2.classList.add("hyst-anim");
    payErrEl.textContent = "";
    setStep(2);
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
