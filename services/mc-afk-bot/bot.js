const mineflayer = require('mineflayer')

const HOST = process.env.MC_HOST || 'localhost'
const PORT = parseInt(process.env.MC_PORT || '25565', 10)
const USERNAME = process.env.MC_USERNAME || 'AFKBot'
const PASSWORD = process.env.MC_PASSWORD || ''            // senha do /login (AuthMe)
const VERSION = process.env.MC_VERSION || false            // false = auto-detecta
const RECONNECT_MS = parseInt(process.env.RECONNECT_MS || '15000', 10)
const HOME_CMD = process.env.MC_HOME_CMD || '/home casa'
// como achar o item do seletor na GUI da bussola:
const SELECTOR_MATCH = (process.env.MC_SELECTOR_MATCH || 'surviv').toLowerCase()
const SELECTOR_SLOT = process.env.MC_SELECTOR_SLOT          // fallback: numero fixo do slot

const sleep = (ms) => new Promise((r) => setTimeout(r, ms))

function createBot() {
  console.log(`[bot] conectando em ${HOST}:${PORT} como "${USERNAME}"...`)

  const bot = mineflayer.createBot({
    host: HOST,
    port: PORT,
    username: USERNAME,
    auth: 'offline',
    version: VERSION,
    checkTimeoutInterval: 60 * 1000,
  })

  let loggedIn = false    // /login so precisa 1x por conexao
  let selecting = false   // evita abrir a bussola 2x
  let antiAfk = null

  bot.on('login', () => console.log('[bot] conectado ao servidor'))

  // loga tudo que o server manda no chat (ajuda a debugar login/kick)
  bot.on('messagestr', (msg) => {
    const m = msg.trim()
    if (m) console.log('[chat]', m)
  })

  bot.on('spawn', async () => {
    console.log('[bot] spawn')
    await sleep(3000)

    // 1) /login (AuthMe) — uma vez por conexao
    if (!loggedIn && PASSWORD) {
      console.log('[bot] enviando /login')
      bot.chat(`/login ${PASSWORD}`)
      loggedIn = true
      await sleep(3000)
    }

    // 2) tem bussola no inventario? entao estamos no LOBBY → abrir seletor
    const compass = bot.inventory.items().find((i) => i.name.includes('compass'))
    if (compass && !selecting) {
      selecting = true
      try {
        console.log('[bot] lobby detectado — abrindo a bussola')
        await bot.equip(compass, 'hand')
        await sleep(500)
        bot.activateItem() // clica com a bussola → abre a GUI
      } catch (e) {
        console.log('[bot] erro ao usar bussola:', e.message)
        selecting = false
      }
      return // o windowOpen cuida do clique; o proximo spawn (pos-transfer) cai no else
    }

    // 3) sem bussola = ja estamos no survival → ir pra home e ficar AFK
    if (!compass) {
      selecting = false
      console.log(`[bot] survival detectado — enviando ${HOME_CMD}`)
      bot.chat(HOME_CMD)
      await sleep(3000)
      startAntiAfk()
    }
  })

  // GUI da bussola abriu → achar e clicar no item do survivor
  bot.on('windowOpen', async (window) => {
    await sleep(1000)
    console.log('[bot] GUI aberta — itens:')
    const items = []
    for (let slot = 0; slot < window.inventoryStart; slot++) {
      const it = window.slots[slot]
      if (it) {
        const label = (it.customName || it.displayName || it.name || '').toString()
        items.push({ slot, label })
        console.log(`    slot ${slot}: ${label} (${it.name})`)
      }
    }

    // procura por nome (ex: "survivor"); se nao achar, usa slot fixo do env
    let target = items.find((i) => i.label.toLowerCase().includes(SELECTOR_MATCH))
    if (!target && SELECTOR_SLOT !== undefined) {
      target = { slot: parseInt(SELECTOR_SLOT, 10), label: `slot fixo ${SELECTOR_SLOT}` }
    }
    if (!target && items.length > 0) {
      // fallback final: "o da direita" = ultimo item da GUI
      target = items[items.length - 1]
      console.log('[bot] nenhum match — usando o item mais a direita')
    }

    if (target) {
      console.log(`[bot] clicando no seletor: slot ${target.slot} (${target.label})`)
      try {
        await bot.clickWindow(target.slot, 0, 0)
      } catch (e) {
        console.log('[bot] erro no clique:', e.message)
      }
    } else {
      console.log('[bot] GUI vazia?! nada pra clicar')
    }
    selecting = false
  })

  bot.on('kicked', (reason) => console.log('[bot] kickado:', JSON.stringify(reason)))
  bot.on('error', (err) => console.log('[bot] erro:', err.message))

  bot.on('end', (reason) => {
    console.log(`[bot] desconectado (${reason}) — reconectando em ${RECONNECT_MS}ms`)
    if (antiAfk) clearInterval(antiAfk)
    setTimeout(createBot, RECONNECT_MS)
  })

  function startAntiAfk() {
    if (antiAfk) clearInterval(antiAfk)
    antiAfk = setInterval(() => {
      try {
        const yaw = Math.random() * Math.PI * 2
        bot.look(yaw, 0, false)
        bot.setControlState('jump', true)
        setTimeout(() => bot.setControlState('jump', false), 400)
        bot.swingArm('right')
      } catch (e) {
        console.log('[bot] anti-AFK falhou:', e.message)
      }
    }, 30 * 1000)
  }
}

createBot()
