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
      e.dataTransfer.setData("text/plain", card.dataset.id);
    });
    card.addEventListener("dragend", () => {
      card.classList.remove("dragging"); dragEl = null;
      board.querySelectorAll(".col.over").forEach((c) => c.classList.remove("over"));
    });
    // clicar abre a campanha — a não ser que tenha acabado de arrastar
    card.addEventListener("click", () => {
      if (card.dataset.dragged) { card.dataset.dragged = ""; return; }
      location = card.dataset.href;
    });
  });

  board.querySelectorAll(".col").forEach((col) => {
    col.addEventListener("dragover", (e) => { e.preventDefault(); e.dataTransfer.dropEffect = "move"; col.classList.add("over"); });
    col.addEventListener("dragleave", (e) => { if (!col.contains(e.relatedTarget)) col.classList.remove("over"); });
    col.addEventListener("drop", (e) => {
      e.preventDefault(); col.classList.remove("over");
      if (!dragEl) return;
      const from = dragEl.closest(".col");
      if (from === col) return;
      col.querySelector(".col-body").appendChild(dragEl);
      dragEl.dataset.dragged = "1"; // evita disparar o clique de navegação
      refreshColumns();
      const id = dragEl.dataset.id, status = col.dataset.status, label = col.dataset.label;
      const body = new URLSearchParams(); body.set("novo", status);
      fetch("/campanha/" + id + "/status", {
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
