/*
 * Captura real de site pra clonagem fiel (modelo same.new): abre a URL num
 * browser de verdade (Chromium/Playwright, executa JS), tira screenshot
 * desktop + mobile, extrai o DOM/CSS já renderizado e NAVEGA o funil seguindo
 * o CTA principal. Sem isso o modelo só adivinha o site pela URL.
 *
 * Retorna { pages: [{ url, title, description, html, text, shotDesktop,
 * shotMobile }] } — screenshots em data URL JPEG.
 */
const { chromium } = require('playwright');

const NAV_TIMEOUT = 25000;
const SETTLE_MS = 1500;
const MAX_HTML = 120000; // por página
const MAX_TEXT = 8000;
const SHOT_QUALITY = 55;
const MAX_SHOT_HEIGHT = 6000; // landing muito longa vira screenshot gigante/lento
const GLOBAL_TIMEOUT = 75000; // teto duro do capture inteiro

// palavras que marcam o CTA de avanço num funil de DR (pt + en)
const CTA_WORDS = [
  'comprar', 'compre', 'quero', 'garantir', 'garanta', 'continuar', 'continue',
  'avancar', 'avançar', 'proximo', 'próximo', 'sim', 'acessar', 'acesso',
  'inscrever', 'assinar', 'começar', 'comecar', 'liberar', 'ver oferta',
  'finalizar', 'checkout', 'add to cart', 'buy', 'get', 'start', 'continue',
  'next', 'yes', 'claim', 'order', 'join',
];

function shrinkHtml(html) {
  // tira <script>/<svg> inline pra caber; mantém <style> (o design mora nele)
  return html
    .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
    .replace(/<svg\b[^<]*(?:(?!<\/svg>)<[^<]*)*<\/svg>/gi, '<svg/>')
    .slice(0, MAX_HTML);
}

async function grabPage(page) {
  const title = await page.title().catch(() => '');
  const description = await page
    .$eval('meta[name="description"]', (m) => m.content)
    .catch(() => '');
  const rawHtml = await page.content().catch(() => '');
  const text = await page
    .evaluate(() => document.body?.innerText || '')
    .catch(() => '');

  const shot = async (w, h) => {
    await page.setViewportSize({ width: w, height: h });
    await page.waitForTimeout(300);

    // landing longa: limita a altura pra não gerar screenshot gigante/lento
    const docH = await page.evaluate(() => document.body?.scrollHeight || 0).catch(() => 0);
    const opts =
      docH > MAX_SHOT_HEIGHT
        ? { clip: { x: 0, y: 0, width: w, height: MAX_SHOT_HEIGHT } }
        : { fullPage: true };

    return page
      .screenshot({ type: 'jpeg', quality: SHOT_QUALITY, ...opts })
      .then((b) => `data:image/jpeg;base64,${b.toString('base64')}`)
      .catch(() => null);
  };

  const shotDesktop = await shot(1440, 900);
  const shotMobile = await shot(390, 844);

  await page.setViewportSize({ width: 1440, height: 900 });

  return {
    url: page.url(),
    title,
    description,
    html: shrinkHtml(rawHtml),
    text: text.replace(/\s+/g, ' ').trim().slice(0, MAX_TEXT),
    shotDesktop,
    shotMobile,
  };
}

/** acha o melhor CTA de avanço visível e retorna um handle clicável, ou null */
async function findCta(page) {
  const handle = await page.evaluateHandle((words) => {
    const cands = Array.from(document.querySelectorAll('a, button, [role="button"], input[type="submit"]'));
    let best = null;
    let bestScore = 0;

    for (const el of cands) {
      const rect = el.getBoundingClientRect();

      if (rect.width < 40 || rect.height < 20 || rect.bottom < 0) continue;

      const style = getComputedStyle(el);

      if (style.display === 'none' || style.visibility === 'hidden' || Number(style.opacity) < 0.3) continue;

      const txt = (el.innerText || el.value || '').trim().toLowerCase();

      if (!txt || txt.length > 60) continue;

      let score = 0;

      if (words.some((w) => txt.includes(w))) score += 100;
      score += Math.min(rect.width * rect.height, 40000) / 1000; // tamanho
      if (rect.top < window.innerHeight) score += 20; // acima da dobra

      if (score > bestScore) {
        bestScore = score;
        best = el;
      }
    }

    return bestScore >= 100 ? best : null; // só segue se houver texto de CTA
  }, CTA_WORDS);

  const el = handle.asElement();

  return el || null;
}

async function captureInner(url, { followFunnel = true, maxSteps = 4 } = {}) {
  const launchOpts = { args: ['--no-sandbox', '--disable-dev-shm-usage'] };

  // proxy residencial opcional (funil de DR com cloaker só serve money page
  // pra tráfego BR mobile real); setar CAPTURE_PROXY=http://user:pass@host:port
  if (process.env.CAPTURE_PROXY) {
    launchOpts.proxy = { server: process.env.CAPTURE_PROXY };
  }

  const browser = await chromium.launch(launchOpts);

  try {
    const ctx = await browser.newContext({
      userAgent:
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
      viewport: { width: 1440, height: 900 },
      locale: 'pt-BR',
    });
    const page = await ctx.newPage();
    page.setDefaultNavigationTimeout(NAV_TIMEOUT);

    const pages = [];
    const baseHost = new URL(url).host;

    await page.goto(url, { waitUntil: 'load' }).catch(() => {});
    await page.waitForTimeout(SETTLE_MS);

    for (let step = 0; step < (followFunnel ? maxSteps : 1); step++) {
      pages.push(await grabPage(page));

      if (!followFunnel) break;

      const cta = await findCta(page);

      if (!cta) break;

      const before = page.url();
      await Promise.all([
        page.waitForLoadState('load', { timeout: NAV_TIMEOUT }).catch(() => {}),
        cta.click({ timeout: 5000 }).catch(() => {}),
      ]);
      await page.waitForTimeout(SETTLE_MS);

      if (page.url() === before) break; // não avançou

      // saiu do domínio = provável checkout externo: captura 1x e encerra
      if (new URL(page.url()).host !== baseHost) {
        pages.push(await grabPage(page));
        break;
      }
    }

    return { pages };
  } finally {
    await browser.close().catch(() => {});
  }
}

// teto duro: se uma etapa pendurar, devolve o que já tiver em vez de estourar
async function capture(url, opts = {}) {
  let timer;
  const guard = new Promise((_, rej) => {
    timer = setTimeout(() => rej(new Error('captura excedeu o tempo limite')), GLOBAL_TIMEOUT);
  });

  try {
    return await Promise.race([captureInner(url, opts), guard]);
  } finally {
    clearTimeout(timer);
  }
}

module.exports = { capture };
