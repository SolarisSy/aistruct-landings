// Radar — camada de interação (progressive enhancement)

// ---- toasts ----
(function () {
  let wrap;
  function ensure() {
    if (!wrap) { wrap = document.createElement("div"); wrap.className = "toasts"; document.body.appendChild(wrap); }
    return wrap;
  }
  window.toast = function (msg, kind) {
    const t = document.createElement("div");
    t.className = "toast " + (kind || "");
    t.textContent = msg;
    ensure().appendChild(t);
    requestAnimationFrame(() => t.classList.add("show"));
    setTimeout(() => { t.classList.remove("show"); setTimeout(() => t.remove(), 300); }, 2600);
  };
  // toast diferido (mostra após o reload que segue um POST)
  const pend = sessionStorage.getItem("radar-toast");
  if (pend) { sessionStorage.removeItem("radar-toast"); window.addEventListener("DOMContentLoaded", () => toast(pend, "ok")); }
})();

// ---- estado "salvando" + toast diferido no submit ----
document.addEventListener("submit", (e) => {
  if (e.defaultPrevented) return; // confirm() cancelado
  const f = e.target;
  const act = f.getAttribute("action") || "";
  if (/salvar|\/editar/.test(act)) sessionStorage.setItem("radar-toast", "Salvo");
  else if (/deletar/.test(act)) sessionStorage.setItem("radar-toast", "Excluído");
  const btn = f.querySelector('button[type="submit"], button:not([type])');
  if (btn) { btn.classList.add("loading"); setTimeout(() => { btn.disabled = true; }, 0); }
});

// ---- kanban drag & drop ----
function initKanban() {
  const board = document.querySelector(".kan");
  if (!board) return;
  let dragEl = null;

  board.querySelectorAll(".kcard").forEach((card) => {
    card.addEventListener("dragstart", (e) => {
      dragEl = card; card.classList.add("dragging");
      e.dataTransfer.effectAllowed = "move";
      e.dataTransfer.setData("text/plain", card.dataset.id || card.dataset.bid || "");
    });
    card.addEventListener("dragend", () => {
      card.classList.remove("dragging"); dragEl = null;
      board.querySelectorAll(".col.over").forEach((c) => c.classList.remove("over"));
    });
    // clicar abre (campanha ou rascunho da esteira) — a não ser que tenha acabado de arrastar
    card.addEventListener("click", () => {
      if (card.dataset.dragged) { card.dataset.dragged = ""; return; }
      if (card.dataset.href) location = card.dataset.href;
    });
  });

  board.querySelectorAll(".col").forEach((col) => {
    if (!col.dataset.status) return;  // esteira não recebe drop (só a origem)
    col.addEventListener("dragover", (e) => { e.preventDefault(); e.dataTransfer.dropEffect = "move"; col.classList.add("over"); });
    col.addEventListener("dragleave", (e) => { if (!col.contains(e.relatedTarget)) col.classList.remove("over"); });
    col.addEventListener("drop", (e) => {
      e.preventDefault(); col.classList.remove("over");
      if (!dragEl) return;
      const status = col.dataset.status, label = col.dataset.label;
      const body = new URLSearchParams(); body.set("novo", status);

      // ESTEIRA → vira campanha na coluna solta
      if (dragEl.classList.contains("bl-card")) {
        dragEl.dataset.dragged = "1";
        fetch("/backlog/" + dragEl.dataset.bid + "/promover", {
          method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: body.toString(),
        }).then((r) => {
          if (r.ok) { toast("Virou campanha em " + label, "ok"); setTimeout(() => location.reload(), 450); }
          else toast("Não consegui promover", "err");
        }).catch(() => toast("Sem conexão", "err"));
        return;
      }

      // campanha muda de status
      const from = dragEl.closest(".col");
      if (from === col) return;
      col.querySelector(".col-body").appendChild(dragEl);
      dragEl.dataset.dragged = "1";
      refreshColumns();
      fetch("/campanha/" + dragEl.dataset.id + "/status", {
        method: "POST", headers: { "Content-Type": "application/x-www-form-urlencoded" }, body: body.toString(),
      }).then((r) => toast(r.ok ? "Movido para " + label : "Não consegui mover", r.ok ? "ok" : "err"))
        .catch(() => toast("Sem conexão", "err"));
    });
  });

  function refreshColumns() {
    board.querySelectorAll(".col").forEach((c) => {
      const n = c.querySelectorAll(".kcard").length;
      const cnt = c.querySelector(".cnt"); if (cnt) cnt.textContent = n;
      const empty = c.querySelector(".col-empty"); if (empty) empty.hidden = n > 0;
    });
  }
}
document.addEventListener("DOMContentLoaded", initKanban);

// ---- assistente (balão flutuante) ----
function initAssistant() {
  const fab = document.getElementById("asst-fab");
  const pop = document.getElementById("asst-pop");
  if (!fab || !pop) return;
  const log = document.getElementById("chat-log");
  const form = document.getElementById("chat-form");
  const input = document.getElementById("chat-text");

  function open() { pop.hidden = false; fab.classList.add("open"); setTimeout(() => input.focus(), 60); }
  function close() { pop.hidden = true; fab.classList.remove("open"); }
  fab.addEventListener("click", () => pop.hidden ? open() : close());
  document.getElementById("asst-close").addEventListener("click", close);
  document.addEventListener("keydown", (e) => { if (e.key === "Escape" && !pop.hidden) close(); });

  const greeting = log.innerHTML;  // guarda a saudação inicial
  const clearBtn = document.getElementById("asst-clear");
  if (clearBtn) clearBtn.addEventListener("click", async () => {
    try { await fetch("/assistente/limpar", { method: "POST", headers: { "Content-Type": "application/json" }, body: "{}" }); } catch (e) {}
    log.innerHTML = greeting;
    if (window.toast) toast("Nova conversa", "ok");
  });

  const esc = (s) => { const d = document.createElement("div"); d.textContent = s; return d.innerHTML; };
  function md(s) {  // markdown seguro: escapa, depois negrito/itálico/bullets/quebras
    s = esc(s);
    s = s.replace(/\*\*([^*]+)\*\*/g, "<strong>$1</strong>");
    s = s.replace(/(^|<br>|\n)\s*[-*•]\s+/g, "$1• ");
    s = s.replace(/\*([^*\n]+)\*/g, "<em>$1</em>");
    s = s.replace(/`([^`]+)`/g, "<code>$1</code>");
    return s.replace(/\n/g, "<br>");
  }
  function add(role, html) {
    const el = document.createElement("div");
    el.className = "msg " + role;
    el.innerHTML = html;
    log.appendChild(el); log.scrollTop = log.scrollHeight;
    return el;
  }
  async function post(url, body) {
    const r = await fetch(url, { method: "POST", headers: { "Content-Type": "application/json" }, body: JSON.stringify(body) });
    return r.json();
  }

  function renderPending(p) {
    const card = document.createElement("div");
    card.className = "confirm-card";
    card.innerHTML =
      '<div class="cc-h">⚠ Confirmar alteração</div>' +
      '<div class="cc-resumo">' + esc(p.resumo) + "</div>" +
      (p.aviso ? '<div class="cc-aviso">' + esc(p.aviso) + " Confira antes de gravar.</div>" : "") +
      '<div class="cc-acts"><button class="btn pri cc-ok">Confirmar e gravar</button>' +
      '<button class="btn cc-no">Cancelar</button></div>';
    log.appendChild(card); log.scrollTop = log.scrollHeight;
    card.querySelector(".cc-ok").addEventListener("click", async () => {
      card.querySelector(".cc-acts").innerHTML = '<span style="color:var(--ink-3);font-size:12px">Gravando…</span>';
      const res = await post("/assistente/confirmar", { id: p.id });
      card.remove(); add("bot", md(res.reply));
      if (res.ok) toast("Gravado", "ok");
    });
    card.querySelector(".cc-no").addEventListener("click", async () => {
      await post("/assistente/cancelar", { id: p.id });
      card.remove(); add("bot", "Cancelado. Sem alterações.");
    });
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const txt = input.value.trim();
    if (!txt) return;
    add("user", esc(txt)); input.value = "";
    const wait = add("bot typing", "•••");
    try {
      const res = await post("/assistente/mensagem", { texto: txt });
      wait.remove(); add("bot", md(res.reply));
      if (res.pending) renderPending(res.pending);
    } catch (err) { wait.remove(); add("bot", "Falhei ao responder. Tente de novo."); }
  });
}
document.addEventListener("DOMContentLoaded", initAssistant);
