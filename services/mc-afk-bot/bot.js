const mineflayer = require('mineflayer')
const zlib = require('zlib')

// silencia o spam de "chunk failed to load" do prismarine (nao afeta nada)
for (const fn of ['warn', 'info', 'log']) {
  const orig = console[fn].bind(console)
  console[fn] = (...a) => { if (!String(a[0] || '').includes('chunk failed to load')) orig(...a) }
}

// ---------- config ----------
const HOST = process.env.MC_HOST || 'localhost'
const PORT = parseInt(process.env.MC_PORT || '25565', 10)
const USERNAME = process.env.MC_USERNAME || 'AFKBot'
const PASSWORD = process.env.MC_PASSWORD || ''
const VERSION = process.env.MC_VERSION || '1.16.5'
const RECONNECT_MS = parseInt(process.env.RECONNECT_MS || '20000', 10)
const HOME_CMD = process.env.MC_HOME_CMD || '/home casa'
const SELECTOR_MATCH = (process.env.MC_SELECTOR_MATCH || 'surviv').toLowerCase()
const SELECTOR_SLOT = process.env.MC_SELECTOR_SLOT

// visao (GLM Coding Plan)
const GLM_URL = process.env.GLM_URL || 'https://api.z.ai/api/coding/paas/v4/chat/completions'
const GLM_KEY = process.env.GLM_API_KEY || ''
const GLM_MODEL = process.env.GLM_MODEL || 'glm-4.5v'
const VISION_PROMPT =
  'Esta imagem e um mapa de Minecraft mostrando um CODIGO de verificacao (letras e/ou numeros). ' +
  'Leia o codigo exatamente como aparece. Responda APENAS com o codigo, sem espacos, sem pontuacao, ' +
  'sem explicacao. Se nao conseguir ler, responda UNKNOWN.'

const RECONNECT_MAX_MS = parseInt(process.env.RECONNECT_MAX_MS || '180000', 10)

const sleep = (ms) => new Promise((r) => setTimeout(r, ms))
const log = (...a) => console.log(...a)

// backoff: se cair antes de logar (antibot throttle), aumenta o intervalo
let backoff = RECONNECT_MS

// ---------- paleta de cores do mapa MC ----------
const BASE_COLORS = [
  [0,0,0],[127,178,56],[247,233,163],[199,199,199],[255,0,0],[160,160,255],[167,167,167],
  [0,124,0],[255,255,255],[164,168,184],[151,109,77],[112,112,112],[64,64,255],[143,119,72],
  [255,252,245],[216,127,51],[178,76,216],[102,153,216],[229,229,51],[127,204,25],[242,127,165],
  [76,76,76],[153,153,153],[76,127,153],[127,63,178],[51,76,178],[102,76,51],[102,127,51],
  [153,51,51],[25,25,25],[250,238,77],[92,219,213],[74,128,255],[0,217,58],[129,86,49],[112,2,0],
  [209,177,161],[159,82,36],[149,87,108],[112,108,138],[186,133,36],[103,117,53],[160,77,78],
  [57,41,35],[135,107,98],[87,92,92],[122,73,88],[76,62,92],[76,50,35],[76,82,42],[142,60,46],
  [37,22,16],[189,48,49],[148,63,97],[92,25,29],[22,126,134],[58,142,140],[86,44,62],[20,180,133],
  [100,100,100],[216,175,147],[127,167,150],
]
const SHADE = [180, 220, 255, 135]
function mapColor(idx) {
  if (idx < 4) return [255, 255, 255] // transparente -> fundo branco
  const base = BASE_COLORS[idx >> 2] || [(idx * 73) & 255, (idx * 151) & 255, (idx * 199) & 255]
  const m = SHADE[idx & 3]
  return [(base[0] * m / 255) | 0, (base[1] * m / 255) | 0, (base[2] * m / 255) | 0]
}

// ---------- PNG encoder (sem deps nativas) ----------
const CRC_TABLE = (() => {
  const t = new Uint32Array(256)
  for (let n = 0; n < 256; n++) {
    let c = n
    for (let k = 0; k < 8; k++) c = c & 1 ? 0xEDB88320 ^ (c >>> 1) : c >>> 1
    t[n] = c >>> 0
  }
  return t
})()
function crc32(buf) {
  let c = 0xFFFFFFFF
  for (let i = 0; i < buf.length; i++) c = CRC_TABLE[(c ^ buf[i]) & 0xFF] ^ (c >>> 8)
  return (c ^ 0xFFFFFFFF) >>> 0
}
function pngChunk(type, data) {
  const len = Buffer.alloc(4); len.writeUInt32BE(data.length, 0)
  const t = Buffer.from(type, 'ascii')
  const crc = Buffer.alloc(4); crc.writeUInt32BE(crc32(Buffer.concat([t, data])), 0)
  return Buffer.concat([len, t, data, crc])
}
function pngEncode(w, h, rgb) {
  const stride = w * 3
  const raw = Buffer.alloc(h * (stride + 1))
  for (let y = 0; y < h; y++) {
    raw[y * (stride + 1)] = 0
    rgb.copy(raw, y * (stride + 1) + 1, y * stride, y * stride + stride)
  }
  const sig = Buffer.from([137, 80, 78, 71, 13, 10, 26, 10])
  const ihdr = Buffer.alloc(13)
  ihdr.writeUInt32BE(w, 0); ihdr.writeUInt32BE(h, 4); ihdr[8] = 8; ihdr[9] = 2
  const idat = zlib.deflateSync(raw, { level: 9 })
  return Buffer.concat([sig, pngChunk('IHDR', ihdr), pngChunk('IDAT', idat), pngChunk('IEND', Buffer.alloc(0))])
}
function canvasHasContent(canvas) {
  for (let i = 0; i < canvas.length; i++) if (canvas[i] >= 4) return true
  return false
}
function renderCanvas(canvas) {
  const S = 4, W = 128 * S, H = 128 * S
  const rgb = Buffer.alloc(W * H * 3)
  for (let y = 0; y < H; y++) {
    const sy = (y / S) | 0
    for (let x = 0; x < W; x++) {
      const sx = (x / S) | 0
      const [r, g, b] = mapColor(canvas[sy * 128 + sx])
      const o = (y * W + x) * 3
      rgb[o] = r; rgb[o + 1] = g; rgb[o + 2] = b
    }
  }
  return pngEncode(W, H, rgb)
}

// ---------- visao GLM ----------
async function readCaptcha(pngB64) {
  const r = await fetch(GLM_URL, {
    method: 'POST',
    headers: { Authorization: `Bearer ${GLM_KEY}`, 'Content-Type': 'application/json' },
    body: JSON.stringify({
      model: GLM_MODEL,
      messages: [{
        role: 'user',
        content: [
          { type: 'text', text: VISION_PROMPT },
          { type: 'image_url', image_url: { url: `data:image/png;base64,${pngB64}` } },
        ],
      }],
    }),
  })
  const j = await r.json()
  if (!r.ok) throw new Error(`GLM ${r.status}: ${JSON.stringify(j).slice(0, 150)}`)
  return j.choices?.[0]?.message?.content || ''
}
function extractCode(text) {
  const cleaned = (text || '').replace(/\s+/g, '')
  if (/unknown/i.test(cleaned)) return ''
  const m = cleaned.match(/[A-Za-z0-9]{3,12}/)
  return m ? m[0] : ''
}

// ---------- bot ----------
function createBot() {
  log(`[bot] conectando em ${HOST}:${PORT} como "${USERNAME}"...`)
  const bot = mineflayer.createBot({
    host: HOST, port: PORT, username: USERNAME,
    auth: 'offline', version: VERSION, checkTimeoutInterval: 60 * 1000,
  })

  const mapCanvas = new Uint8Array(128 * 128)
  let loggedIn = false
  let selecting = false
  let solving = false
  let lastSolve = 0
  let mapReceived = false
  let antiAfk = null

  // sniffer cru: mostra TODO pacote de mapa que chega (diagnostico)
  bot._client.on('packet', (data, meta) => {
    if (meta.name === 'map') {
      const dlen = data.data ? (data.data.length || (data.data.data && data.data.data.length)) : 0
      log(`[rawmap] keys=${Object.keys(data).join(',')} cols=${data.columns} rows=${data.rows} x=${data.x} z=${data.z} dlen=${dlen}`)
    }
  })

  // acumula pacotes de mapa no canvas 128x128
  bot._client.on('map', (packet) => {
    try {
      // cols/rows sao i8 (signed): 128 chega como -128 -> destransformar pra unsigned
      let cols = packet.columns; if (cols < 0) cols += 256
      let rows = packet.rows; if (rows < 0) rows += 256
      // data pode vir como Buffer OU como {type,data:Buffer}
      let data = packet.data
      if (data && !data.length && data.data) data = data.data
      if (!cols || !rows || !data || !data.length) return
      mapReceived = true
      let ox = packet.x != null ? packet.x : 0; if (ox < 0) ox += 256
      let oy = (packet.y != null ? packet.y : packet.z) || 0; if (oy < 0) oy += 256
      for (let c = 0; c < cols; c++) {
        for (let r = 0; r < rows; r++) {
          const px = ox + c, py = oy + r
          if (px < 0 || px >= 128 || py < 0 || py >= 128) continue
          mapCanvas[py * 128 + px] = data[c * rows + r] & 0xFF
        }
      }
    } catch (e) { log('[map] erro: ' + e.message) }
  })

  bot.on('login', () => { log('[bot] conectado ao servidor'); mapCanvas.fill(0); mapReceived = false; backoff = RECONNECT_MS })

  bot.on('messagestr', (msg) => {
    const m = (msg || '').trim()
    if (m) log('[chat]', m)
    const low = m.toLowerCase()
    // gatilho do captcha
    if (low.includes('verificac') || (low.includes('codigo') || low.includes('código')) && low.includes('mapa')
        || low.includes('mapa') && low.includes('chat')) {
      if (!solving && Date.now() - lastSolve > 8000) solveCaptcha()
    }
    // sucesso -> segue o fluxo
    if (low.includes('sucesso') || low.includes('verificad') || low.includes('liberad')
        || low.includes('continuar jogando')) {
      setTimeout(progress, 1500)
    }
  })

  bot.on('spawn', async () => {
    log('[bot] spawn')
    await sleep(3000)
    progress()
  })

  bot.on('kicked', (reason) => log('[bot] kickado:', JSON.stringify(reason)))
  bot.on('error', (err) => log('[bot] erro:', err.message))
  bot.on('end', (reason) => {
    if (antiAfk) clearInterval(antiAfk)
    // se caiu antes de logar (antibot), sobe o backoff; senao usa base
    const delay = loggedIn ? RECONNECT_MS : backoff
    if (!loggedIn) backoff = Math.min(Math.floor(backoff * 1.7), RECONNECT_MAX_MS)
    log(`[bot] desconectado (${reason}) — reconectando em ${delay}ms`)
    setTimeout(createBot, delay)
  })

  async function solveCaptcha() {
    if (solving) return
    solving = true
    lastSolve = Date.now()
    try {
      if (!GLM_KEY) { log('[captcha] GLM_API_KEY nao setada!'); return }
      log('[captcha] detectado — aguardando o mapa...')
      let waited = 0
      // espera um mapa chegar (com conteudo, ou pelo menos algum pacote de mapa)
      while (!canvasHasContent(mapCanvas) && waited < 6000) { await sleep(300); waited += 300 }
      if (!mapReceived && !canvasHasContent(mapCanvas)) {
        log('[captcha] nenhum pacote de mapa recebido — nao da pra ler')
        return
      }
      const png = renderCanvas(mapCanvas)
      log(`[captcha] enviando png (${png.length}b) -> ${GLM_MODEL}`)
      let raw = ''
      try { raw = await readCaptcha(png.toString('base64')) }
      catch (e) { log('[captcha] erro na API de visao: ' + e.message); return }
      const code = extractCode(raw)
      log(`[captcha] visao respondeu ${JSON.stringify(raw).slice(0, 80)} -> code="${code}"`)
      if (!code) { log('[captcha] nao consegui ler o codigo'); return }
      log('[captcha] digitando no chat: ' + code)
      bot.chat(code)
      setTimeout(progress, 6000) // se passou, segue
    } finally {
      lastSolve = Date.now()
      setTimeout(() => { solving = false }, 5000)
    }
  }

  async function progress() {
    if (solving) return
    // 1) /login (AuthMe)
    if (!loggedIn && PASSWORD) {
      log('[bot] enviando /login')
      bot.chat(`/login ${PASSWORD}`)
      loggedIn = true
      setTimeout(progress, 3500)
      return
    }
    // 2) bussola no inventario = lobby -> abrir seletor
    const compass = bot.inventory.items().find((i) => i.name.includes('compass'))
    if (compass && !selecting) {
      selecting = true
      try {
        log('[bot] lobby — abrindo bussola')
        await bot.equip(compass, 'hand')
        await sleep(500)
        bot.activateItem()
      } catch (e) { log('[bot] erro bussola: ' + e.message); selecting = false }
      return
    }
    // 3) survival -> /home + anti-afk
    if (!compass) {
      selecting = false
      log(`[bot] survival — ${HOME_CMD}`)
      bot.chat(HOME_CMD)
      await sleep(3000)
      startAntiAfk()
    }
  }

  bot.on('windowOpen', async (window) => {
    await sleep(1000)
    const items = []
    for (let slot = 0; slot < window.inventoryStart; slot++) {
      const it = window.slots[slot]
      if (it) {
        const label = (it.customName || it.displayName || it.name || '').toString()
        items.push({ slot, label })
        log(`    slot ${slot}: ${label} (${it.name})`)
      }
    }
    let target = items.find((i) => i.label.toLowerCase().includes(SELECTOR_MATCH))
    if (!target && SELECTOR_SLOT !== undefined) target = { slot: parseInt(SELECTOR_SLOT, 10), label: `slot ${SELECTOR_SLOT}` }
    if (!target && items.length) target = items[items.length - 1]
    if (target) {
      log(`[bot] clicando seletor slot ${target.slot} (${target.label})`)
      try { await bot.clickWindow(target.slot, 0, 0) } catch (e) { log('[bot] erro clique: ' + e.message) }
    }
    selecting = false
  })

  function startAntiAfk() {
    if (antiAfk) clearInterval(antiAfk)
    antiAfk = setInterval(() => {
      try {
        bot.look(Math.random() * Math.PI * 2, 0, false)
        bot.setControlState('jump', true)
        setTimeout(() => bot.setControlState('jump', false), 400)
        bot.swingArm('right')
      } catch (e) { /* ignore */ }
    }, 30 * 1000)
  }
}

createBot()
