/*
 * identical-runner — build remoto + preview dos projetos do Identical AI.
 *
 * Por quê: o IDE builda no navegador (WebContainer/WASM) e trava a aba do
 * usuário em build pesado. Este serviço recebe os arquivos do projeto, roda
 * npm install + npm run build NA VPS e serve o resultado estático — a parte
 * que pesa sai do cliente (modelo same.new, fase 1 de docs/publicar-vps.md
 * do identical-ide).
 *
 * v0 interno: builds serializados (1 por vez, protege o nó compartilhado do
 * Easypanel), timeout duro, workspace por slug com node_modules reaproveitado,
 * cache npm compartilhado. Isolamento = o container deste serviço; NÃO expor
 * como produto multi-tenant sem sandbox por projeto (documentado no design).
 *
 * API (auth: header x-runner-token quando RUNNER_TOKEN estiver setado):
 *   POST /api/projects/:slug/build   {files: {"caminho/rel": "conteudo", ...}}
 *   GET  /api/projects/:slug/status  → {state, url?, log}
 *   GET  /p/:slug/*                  → estático do último build (fallback SPA)
 *   GET  /healthz
 */
const express = require('express');
const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');
const { capture } = require('./capture');

const DATA_DIR = process.env.DATA_DIR || '/data';
const PORT = Number(process.env.PORT || 8000);
const TOKEN = process.env.RUNNER_TOKEN || '';
const STEP_TIMEOUT_MS = Number(process.env.STEP_TIMEOUT_MS || 300000); // 5min por etapa
const MAX_LOG_CHARS = 20000;

const WS_DIR = path.join(DATA_DIR, 'ws');
const PUB_DIR = path.join(DATA_DIR, 'pub');
const CACHE_DIR = path.join(DATA_DIR, 'npm-cache');

for (const d of [WS_DIR, PUB_DIR, CACHE_DIR]) fs.mkdirSync(d, { recursive: true });

/** estado em memória por slug: {state, log, url, startedAt} */
const projects = new Map();

const SLUG_RE = /^[a-z0-9][a-z0-9-]{1,48}$/;

function proj(slug) {
  if (!projects.has(slug)) projects.set(slug, { state: 'idle', log: '', url: null });
  return projects.get(slug);
}

function appendLog(p, text) {
  p.log = (p.log + text).slice(-MAX_LOG_CHARS);
}

function run(p, cmd, args, cwd) {
  return new Promise((resolve, reject) => {
    appendLog(p, `\n$ ${cmd} ${args.join(' ')}\n`);

    const child = spawn(cmd, args, {
      cwd,
      env: { ...process.env, npm_config_cache: CACHE_DIR, CI: 'true' },
      // Windows (teste local): npm é npm.cmd e exige shell; no container é no-op
      shell: process.platform === 'win32',
    });
    const killer = setTimeout(() => {
      appendLog(p, `\n[runner] timeout de ${STEP_TIMEOUT_MS / 1000}s — processo morto\n`);
      child.kill('SIGKILL');
    }, STEP_TIMEOUT_MS);

    child.stdout.on('data', (d) => appendLog(p, d.toString()));
    child.stderr.on('data', (d) => appendLog(p, d.toString()));
    child.on('close', (code) => {
      clearTimeout(killer);
      if (code === 0) resolve();
      else reject(new Error(`${cmd} saiu com código ${code}`));
    });
    child.on('error', (err) => {
      clearTimeout(killer);
      reject(err);
    });
  });
}

/** grava os arquivos recebidos no workspace, preservando node_modules */
function writeWorkspace(ws, files) {
  // limpa tudo menos node_modules (reaproveita instalação anterior)
  if (fs.existsSync(ws)) {
    for (const entry of fs.readdirSync(ws)) {
      if (entry === 'node_modules') continue;
      fs.rmSync(path.join(ws, entry), { recursive: true, force: true });
    }
  } else {
    fs.mkdirSync(ws, { recursive: true });
  }

  let count = 0;

  for (const [rel, content] of Object.entries(files)) {
    const clean = path.normalize(rel).replace(/^([/\\])+/, '');

    if (clean.startsWith('..') || path.isAbsolute(clean) || clean.split(/[/\\]/).includes('node_modules')) {
      continue; // path traversal / lixo
    }

    const abs = path.join(ws, clean);

    if (!abs.startsWith(ws)) continue;
    fs.mkdirSync(path.dirname(abs), { recursive: true });
    fs.writeFileSync(abs, content ?? '', 'utf8');
    count += 1;
  }

  return count;
}

function findOutputDir(ws) {
  for (const cand of ['dist', 'build', 'out']) {
    const dir = path.join(ws, cand);

    if (fs.existsSync(path.join(dir, 'index.html'))) return dir;
  }

  // projeto estático puro (sem build): serve o próprio workspace se tiver index.html
  if (fs.existsSync(path.join(ws, 'index.html'))) return ws;

  return null;
}

// fila: 1 build por vez (o nó "sites" do Easypanel é compartilhado)
let queue = Promise.resolve();

async function buildProject(slug, files) {
  const p = proj(slug);
  const ws = path.join(WS_DIR, slug);

  p.state = 'syncing';
  p.log = '';
  p.url = null;

  const n = writeWorkspace(ws, files);
  appendLog(p, `[runner] ${n} arquivos gravados em ${slug}\n`);

  const hasPkg = fs.existsSync(path.join(ws, 'package.json'));

  if (hasPkg) {
    p.state = 'installing';
    await run(p, 'npm', ['install', '--no-audit', '--no-fund'], ws);

    const pkg = JSON.parse(fs.readFileSync(path.join(ws, 'package.json'), 'utf8'));

    if (pkg.scripts && pkg.scripts.build) {
      p.state = 'building';
      await run(p, 'npm', ['run', 'build'], ws);
    }
  }

  const outDir = findOutputDir(ws);

  if (!outDir) throw new Error('build terminou mas não achei index.html (dist/, build/, out/ ou raiz)');

  const pub = path.join(PUB_DIR, slug);
  fs.rmSync(pub, { recursive: true, force: true });
  fs.cpSync(outDir, pub, { recursive: true });

  p.state = 'ready';
  p.url = `/p/${slug}/`;
  appendLog(p, `[runner] publicado em ${p.url}\n`);
}

const app = express();
app.use(express.json({ limit: '30mb' }));

/*
 * CORS: o IDE roda noutra origem (localhost:5174 em dev, domínio próprio em
 * prod) e chama este runner cross-origin. Sem estes headers o browser bloqueia
 * com "Failed to fetch" (o preflight OPTIONS falha). Auth continua pelo token.
 */
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', req.headers.origin || '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Content-Type, x-runner-token');
  res.header('Access-Control-Max-Age', '86400');

  if (req.method === 'OPTIONS') {
    return res.sendStatus(204);
  }

  return next();
});

app.use('/api', (req, res, next) => {
  if (TOKEN && req.headers['x-runner-token'] !== TOKEN) {
    return res.status(401).json({ error: 'token inválido' });
  }

  return next();
});

app.get('/healthz', (_req, res) => res.json({ ok: true, queueBusy: false }));

// Captura real de site pra clonagem fiel (Playwright: screenshot desktop+mobile,
// DOM renderizado, navega o funil). O IDE injeta isso no contexto do modelo.
app.post('/api/capture', async (req, res) => {
  const url = req.body && req.body.url;

  if (!url || typeof url !== 'string' || !/^https?:\/\//i.test(url)) {
    return res.status(400).json({ error: 'url http(s) obrigatória' });
  }

  const followFunnel = req.body.followFunnel !== false;
  const maxSteps = Math.min(Number(req.body.maxSteps) || 4, 6);

  try {
    const result = await capture(url, { followFunnel, maxSteps });
    return res.json(result);
  } catch (err) {
    return res.status(500).json({ error: String(err && err.message ? err.message : err) });
  }
});

app.post('/api/projects/:slug/build', (req, res) => {
  const { slug } = req.params;

  if (!SLUG_RE.test(slug)) return res.status(400).json({ error: 'slug inválido' });

  const files = req.body && req.body.files;

  if (!files || typeof files !== 'object' || Array.isArray(files)) {
    return res.status(400).json({ error: 'body.files ausente' });
  }

  const p = proj(slug);

  if (p.state === 'syncing' || p.state === 'installing' || p.state === 'building' || p.state === 'queued') {
    return res.status(409).json({ error: 'build em andamento', state: p.state });
  }

  p.state = 'queued';
  p.startedAt = Date.now();

  queue = queue
    .then(() => buildProject(slug, files))
    .catch((err) => {
      p.state = 'error';
      appendLog(p, `\n[runner] ERRO: ${err.message}\n`);
    });

  return res.json({ ok: true, state: 'queued' });
});

app.get('/api/projects/:slug/status', (req, res) => {
  const { slug } = req.params;

  if (!SLUG_RE.test(slug)) return res.status(400).json({ error: 'slug inválido' });

  const p = proj(slug);
  res.json({ state: p.state, url: p.url, log: p.log.slice(-4000) });
});

// preview estático com fallback SPA por projeto
app.use('/p/:slug', (req, res, next) => {
  const { slug } = req.params;

  if (!SLUG_RE.test(slug)) return res.status(400).send('slug inválido');

  const pub = path.join(PUB_DIR, slug);

  if (!fs.existsSync(pub)) return res.status(404).send('projeto não publicado');

  return express.static(pub, { fallthrough: true })(req, res, () => {
    res.sendFile(path.join(pub, 'index.html'));
  });
});

app.listen(PORT, () => {
  console.log(`identical-runner na porta ${PORT} (data: ${DATA_DIR})`);
});
