/* Origem da visita (1a parte) — Checkpoint BR.
 *
 * Guarda de onde o visitante veio na PRIMEIRA visita e mantem essa informacao
 * durante a navegacao interna, ate a pagina de pedido, para que um pedido possa
 * ser ligado a visita que o originou.
 *
 * - Nao carrega nada de terceiro, nao envia nada para lugar nenhum.
 * - So le a query string da propria pagina e escreve em localStorage + cookie
 *   de 1a parte deste dominio.
 * - Janela de 90 dias: passado esse prazo a informacao nao tem mais utilidade,
 *   entao nao ha motivo para guardar por mais tempo.
 * - PRIMEIRA GRAVACAO VENCE: navegacao interna (que costuma vir sem parametro)
 *   nao apaga a origem real. So preenche o que ainda esta vazio.
 */
(function (w, d) {
  'use strict';

  var KEY      = 'cb_origin';
  var MAX_AGE  = 90 * 24 * 60 * 60;              // 90 dias, em segundos
  var CLICK    = ['gclid', 'gbraid', 'wbraid'];   // formatos do identificador de origem
  var ALT      = ['src', 'clickid', 'cid'];       // apelidos equivalentes do mesmo campo
  var UTM      = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id'];
  var ALL      = CLICK.concat(ALT, UTM);
  var TOKEN_RE = /^[A-Za-z0-9_\-.]{6,400}$/;
  var LIXO     = { none: 1, null: 1, undefined: 1, '': 1, '0': 1 };

  function limpo(v) {
    if (v === null || v === undefined) return '';
    v = String(v).trim();
    if (LIXO[v.toLowerCase()]) return '';
    return v;
  }

  // ── storage (tolerante a ITP / cookie bloqueado / modo anonimo) ────────────
  function lsGet(k) { try { return w.localStorage.getItem(k); } catch (e) { return null; } }
  function lsSet(k, v) { try { w.localStorage.setItem(k, v); } catch (e) {} }

  function ckGet(k) {
    try {
      var m = d.cookie.match('(?:^|;\s*)' + k + '=([^;]*)');
      return m ? decodeURIComponent(m[1]) : null;
    } catch (e) { return null; }
  }
  function ckSet(k, v) {
    try {
      var s = k + '=' + encodeURIComponent(v) + ';path=/;max-age=' + MAX_AGE + ';SameSite=Lax';
      if (w.location.protocol === 'https:') s += ';Secure';
      d.cookie = s;
    } catch (e) {}
  }

  function ler() {
    var raw = lsGet(KEY) || ckGet(KEY);
    if (!raw) return null;
    try {
      var o = JSON.parse(raw);
      return (o && typeof o === 'object' && o.q) ? o : null;
    } catch (e) { return null; }
  }

  function gravar(rec) {
    var raw = JSON.stringify(rec);
    lsSet(KEY, raw);
    ckSet(KEY, raw);   // dois lugares: um sobrevive quando o outro e limpo
  }

  // ── o que veio na URL desta pagina ─────────────────────────────────────────
  function daUrl() {
    var q = {}, i, v, sp;
    try { sp = new w.URLSearchParams(w.location.search); } catch (e) { return q; }
    for (i = 0; i < ALL.length; i++) {
      v = limpo(sp.get(ALL[i]));
      if (!v) continue;
      // identificador de clique tem formato; utm_* e texto livre
      if ((CLICK.indexOf(ALL[i]) >= 0 || ALT.indexOf(ALL[i]) >= 0) && !TOKEN_RE.test(v)) continue;
      q[ALL[i]] = v;
    }
    return q;
  }

  function idDe(q) {
    var i;
    for (i = 0; i < CLICK.length; i++) if (q[CLICK[i]]) return { id: q[CLICK[i]], tipo: CLICK[i] };
    for (i = 0; i < ALT.length; i++)   if (q[ALT[i]])   return { id: q[ALT[i]],   tipo: ALT[i] };
    return { id: '', tipo: '' };
  }

  // ── estado: primeiro toque vence, campo vazio pode ser preenchido depois ───
  var atual = daUrl();
  var rec   = ler();

  if (!rec) {
    rec = { v: 1, ts: Date.now(), lp: w.location.pathname, q: {} };
  }
  var novo = false, k;
  for (k in atual) {
    if (!Object.prototype.hasOwnProperty.call(atual, k)) continue;
    if (rec.q[k]) continue;            // ja tinha valor -> nao sobrescreve
    rec.q[k] = atual[k]; novo = true;
  }
  var ident = idDe(rec.q);
  rec.id = ident.id;
  rec.tipo = ident.tipo;
  if (novo || !lsGet(KEY)) gravar(rec);

  // ── propagacao ────────────────────────────────────────────────────────────
  function decorate(url) {
    if (!url) return url;
    try {
      var s = String(url);
      if (/^(#|mailto:|tel:|javascript:)/i.test(s)) return s;
      var u = new w.URL(s, w.location.href);
      if (u.origin !== w.location.origin) return s;   // nunca vaza para fora
      var mudou = false, key;
      for (key in rec.q) {
        if (!Object.prototype.hasOwnProperty.call(rec.q, key)) continue;
        if (u.searchParams.get(key)) continue;
        u.searchParams.set(key, rec.q[key]); mudou = true;
      }
      if (!mudou) return s;
      return u.pathname + u.search + u.hash;          // mantem link relativo
    } catch (e) { return url; }
  }

  // Quais links carregam a origem adiante: os passos internos que levam a um
  // pedido. Todos precisam entrar — se um ficar de fora, a informacao se perde
  // no meio do caminho e o pedido chega sem referencia nenhuma.
  var ALVO_RE = /(^|\/)go\/|checkout|(^|\/)loja\//i;

  function alvo(a) {
    if (!a || !a.getAttribute) return false;
    var h = a.getAttribute('href') || '';
    if (!ALVO_RE.test(h)) return false;
    // Mesma origem, sempre: decorar link externo vazaria a origem para fora.
    try { return new w.URL(h, w.location.href).origin === w.location.origin; }
    catch (e) { return false; }
  }

  function varrer() {
    var as = d.querySelectorAll('a[href]'), i, a, h;
    for (i = 0; i < as.length; i++) {
      a = as[i];
      if (!alvo(a)) continue;
      h = a.getAttribute('href');
      a.setAttribute('href', decorate(h));
    }
  }

  // rede de seguranca: href reescrito por outro script depois da varredura
  function noClique(ev) {
    var a = ev.target && ev.target.closest ? ev.target.closest('a[href]') : null;
    if (!alvo(a)) return;
    a.setAttribute('href', decorate(a.getAttribute('href')));
  }

  w.__origin = {
    params: function () { var c = {}, key;
      for (key in rec.q) if (Object.prototype.hasOwnProperty.call(rec.q, key)) c[key] = rec.q[key];
      return c; },
    id: function () { return rec.id || ''; },
    tipo: function () { return rec.tipo || ''; },
    ts: function () { return rec.ts || 0; },
    decorate: decorate,
    varrer: varrer
  };

  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', varrer);
  else varrer();
  d.addEventListener('pointerdown', noClique, true);
  d.addEventListener('click', noClique, true);
  d.addEventListener('auxclick', noClique, true);
})(window, document);
