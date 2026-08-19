/*
 * Sessão de browser VIVA (modelo same.new): mantém um Chromium aberto por
 * sessão, transmite o viewport ao vivo via CDP screencast (WebSocket) e aceita
 * ações — do usuário (painel) e da IA (tools). A sessão sobrevive entre
 * requests; a IA dirige a MESMA página que o usuário vê.
 */
const { chromium } = require('playwright');
const { WebSocketServer } = require('ws');

const IDLE_MS = 5 * 60 * 1000; // fecha sessão ociosa em 5min
const NAV_TIMEOUT = 45000; // proxy residencial tem latência variável

const MOBILE = {
  viewport: { width: 390, height: 844 },
  userAgent:
    'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
};
const DESKTOP = {
  viewport: { width: 1280, height: 800 },
  userAgent:
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
};

/*
 * Proxy opcional (CAPTURE_PROXY=http://user:pass@host:porta). Funil de DR com
 * bloqueio geográfico/anti-bot só responde pra tráfego residencial BR — o IP do
 * runner (datacenter) leva timeout. O Playwright quer server + auth separados.
 */
function parseProxy() {
  const raw = process.env.CAPTURE_PROXY;

  if (!raw) {
    return null;
  }

  try {
    const u = new URL(raw);
    const proxy = { server: `${u.protocol}//${u.host}` };

    if (u.username) {
      proxy.username = decodeURIComponent(u.username);
      proxy.password = decodeURIComponent(u.password);
    }

    return proxy;
  } catch {
    return { server: raw };
  }
}

/** @type {Map<string, Session>} */
const sessions = new Map();
let seq = 0;

function newId() {
  seq += 1;
  return `s${seq}_${Buffer.from(String(process.hrtime.bigint())).toString('base64url').slice(-6)}`;
}

class Session {
  constructor(id) {
    this.id = id;
    this.browser = null;
    this.context = null;
    this.page = null;
    this.cdp = null;
    this.mode = 'desktop';
    this.clients = new Set(); // WebSockets assistindo
    this.lastActivity = Date.now();
    this.screencasting = false;
  }

  touch() {
    this.lastActivity = Date.now();
  }

  async start() {
    const launchOpts = { args: ['--no-sandbox', '--disable-dev-shm-usage'] };
    const proxy = parseProxy();

    if (proxy) {
      launchOpts.proxy = proxy;
    }

    this.browser = await chromium.launch(launchOpts);
    await this._makeContext(DESKTOP);
  }

  async _makeContext(profile) {
    if (this.context) {
      await this.context.close().catch(() => {});
    }

    this.context = await this.browser.newContext({
      viewport: profile.viewport,
      userAgent: profile.userAgent,
      locale: 'pt-BR',
    });
    this.page = await this.context.newPage();
    this.page.setDefaultNavigationTimeout(NAV_TIMEOUT);
    this.page.on('framenavigated', (f) => {
      if (f === this.page.mainFrame()) {
        this._broadcast({ type: 'url', url: this.page.url() });
      }
    });
    await this._startScreencast();
  }

  async _startScreencast() {
    this.cdp = await this.context.newCDPSession(this.page);
    this.cdp.on('Page.screencastFrame', async (frame) => {
      this._broadcast({ type: 'frame', data: frame.data });
      this.cdp.send('Page.screencastFrameAck', { sessionId: frame.sessionId }).catch(() => {});
    });
    await this.cdp.send('Page.startScreencast', {
      format: 'jpeg',
      quality: 45,
      maxWidth: this.mode === 'mobile' ? 390 : 1280,
      maxHeight: this.mode === 'mobile' ? 844 : 800,
      // metade dos frames já basta pro olho e alivia a aba (SPA/VSL fazem ~40fps)
      everyNthFrame: 2,
    });
    this.screencasting = true;
  }

  _broadcast(msg) {
    const s = JSON.stringify(msg);

    for (const ws of this.clients) {
      if (ws.readyState === 1) {
        ws.send(s);
      }
    }
  }

  addClient(ws) {
    this.clients.add(ws);
    this.touch();
    ws.send(JSON.stringify({ type: 'url', url: this.page ? this.page.url() : '' }));
    ws.send(JSON.stringify({ type: 'mode', mode: this.mode }));
  }

  removeClient(ws) {
    this.clients.delete(ws);
  }

  /* ---- ações (usuário e IA usam as mesmas) ---- */

  async navigate(url) {
    this.touch();
    this._broadcast({ type: 'loading', url });

    try {
      // domcontentloaded é mais permissivo que 'load' (não trava em asset lento)
      await this.page.goto(url, { waitUntil: 'domcontentloaded', timeout: NAV_TIMEOUT });
    } catch (err) {
      // timeout/bloqueio: avisa o painel em vez de deixar spinner eterno
      this._broadcast({
        type: 'nav-error',
        error: 'não consegui abrir o site (timeout ou bloqueio geográfico/anti-bot)',
      });
      return this._state();
    }

    await this.page.waitForTimeout(1200);

    return this._state();
  }

  async click(x, y) {
    this.touch();
    await this.page.mouse.click(x, y).catch(() => {});
    await this.page.waitForTimeout(1000);
    return this._state();
  }

  async clickText(text) {
    this.touch();
    const loc = this.page.getByText(text, { exact: false }).first();
    await loc.click({ timeout: 5000 }).catch(() => {});
    await this.page.waitForTimeout(1000);
    return this._state();
  }

  async scroll(dy) {
    this.touch();
    await this.page.mouse.wheel(0, dy).catch(() => {});
    await this.page.waitForTimeout(400);
    return this._state();
  }

  async back() {
    this.touch();
    await this.page.goBack({ waitUntil: 'load' }).catch(() => {});
    return this._state();
  }

  async setMode(mode) {
    this.touch();
    this.mode = mode === 'mobile' ? 'mobile' : 'desktop';

    const url = this.page.url();
    await this._makeContext(this.mode === 'mobile' ? MOBILE : DESKTOP);

    if (url && url !== 'about:blank') {
      await this.page.goto(url, { waitUntil: 'load' }).catch(() => {});
    }

    this._broadcast({ type: 'mode', mode: this.mode });

    return this._state();
  }

  async _state() {
    return {
      url: this.page.url(),
      title: await this.page.title().catch(() => ''),
      mode: this.mode,
    };
  }

  /** snapshot pro modelo VER e copiar: screenshot + DOM renderizado */
  async snapshot() {
    this.touch();

    /*
     * Screenshot ENXUTO: full-page em resolução total vira imagem de MBs que
     * trava a aba do usuário ao renderizar/persistir (medido: 1.1s de bloqueio
     * por página). Limita a altura e usa qualidade menor — o modelo enxerga o
     * layout do mesmo jeito, com uma fração do peso.
     */
    const docH = await this.page.evaluate(() => document.body?.scrollHeight || 0).catch(() => 0);
    const w = this.mode === 'mobile' ? 390 : 1280;
    const shotOpts =
      docH > 4000 ? { clip: { x: 0, y: 0, width: w, height: 4000 } } : { fullPage: true };
    const shot = await this.page
      .screenshot({ type: 'jpeg', quality: 40, ...shotOpts })
      .then((b) => `data:image/jpeg;base64,${b.toString('base64')}`)
      .catch(() => null);
    const html = await this.page
      .content()
      .then((h) =>
        h
          .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
          .replace(/<svg\b[^<]*(?:(?!<\/svg>)<[^<]*)*<\/svg>/gi, '<svg/>')
          .slice(0, 120000),
      )
      .catch(() => '');
    const text = await this.page
      .evaluate(() => document.body?.innerText || '')
      .then((t) => t.replace(/\s+/g, ' ').trim().slice(0, 8000))
      .catch(() => '');

    return { url: this.page.url(), title: await this.page.title().catch(() => ''), shot, html, text };
  }

  async close() {
    this.screencasting = false;

    for (const ws of this.clients) {
      try {
        ws.close();
      } catch {
        // ignore
      }
    }

    await this.browser?.close().catch(() => {});
    sessions.delete(this.id);
  }
}

async function createSession() {
  const s = new Session(newId());
  await s.start();
  sessions.set(s.id, s);
  return s;
}

function getSession(id) {
  return sessions.get(id) || null;
}

// varredura de sessões ociosas
setInterval(() => {
  const now = Date.now();

  for (const s of sessions.values()) {
    if (now - s.lastActivity > IDLE_MS) {
      s.close().catch(() => {});
    }
  }
}, 60000);

/** liga o WebSocket de screencast no server HTTP */
function attachWs(server) {
  const wss = new WebSocketServer({ noServer: true });

  server.on('upgrade', (req, socket, head) => {
    const url = new URL(req.url, 'http://x');

    if (url.pathname !== '/ws/browser') {
      return;
    }

    wss.handleUpgrade(req, socket, head, (ws) => {
      const id = url.searchParams.get('session');
      const session = id ? getSession(id) : null;

      if (!session) {
        ws.send(JSON.stringify({ type: 'error', error: 'sessão inexistente' }));
        ws.close();

        return;
      }

      session.addClient(ws);
      ws.on('message', async (buf) => {
        let msg;

        try {
          msg = JSON.parse(buf.toString());
        } catch {
          return;
        }

        try {
          if (msg.type === 'navigate') await session.navigate(msg.url);
          else if (msg.type === 'click') await session.click(msg.x, msg.y);
          else if (msg.type === 'scroll') await session.scroll(msg.dy);
          else if (msg.type === 'back') await session.back();
          else if (msg.type === 'mode') await session.setMode(msg.mode);
        } catch (err) {
          ws.send(JSON.stringify({ type: 'error', error: String(err && err.message) }));
        }
      });
      ws.on('close', () => session.removeClient(ws));
    });
  });
}

module.exports = { createSession, getSession, attachWs };
