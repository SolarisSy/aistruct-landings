/* obrigado v3 (hyustripe1) — confirma Paggins (session_id=cs_...) E Stripe
 * (payment_intent=pi_..., anexado pela Stripe no return_url). PIX Stripe é
 * assíncrono: status "processing" → mostra "aguardando confirmação".
 * /_astro/ é immutable — arquivo NOVO (base: hyufix2). */
import { m as clearCart, C as API, f as fmt } from "./cart.D0a-thWQ.js";

clearCart();
localStorage.removeItem("hyu-lead-ok");

const q = new URLSearchParams(location.search);
const sid = q.get("session_id");
const pi = q.get("payment_intent");
const ref = q.get("ref");
const t = document.querySelector("[data-thanks-ref]");
const validPag = !!sid && /^cs_[A-Za-z0-9]+$/.test(sid);
const validPi = !!pi && /^pi_[A-Za-z0-9]+$/.test(pi);
const show = ref || (validPag ? sid.replace(/^cs_/, "").slice(0, 12) : "");

if (t && show) {
  t.textContent = `Referência do pedido: ${show}`;
  t.hidden = false;
}

function render(e) {
  if (!e || !t) return;
  const paid = e.paymentStatus === "paid" || e.status === "completed";
  const waiting = e.status === "processing" || e.status === "requires_action";
  const n = typeof e.totalAmount == "number" ? ` · ${fmt(e.totalAmount)}` : "";
  const r = show ? ` · ref ${show}` : "";
  t.textContent = paid ? `✓ Pagamento confirmado${n}${r}`
    : waiting ? `Pedido recebido — aguardando a confirmação do pagamento${n}${r}`
    : `Pedido recebido${n}${r}`;
}

if (validPag) {
  fetch(`${API}/session/${encodeURIComponent(sid)}`)
    .then((e) => (e.ok ? e.json() : null)).then(render).catch(() => {});
} else if (validPi) {
  fetch(`${API}/stripe/session/${encodeURIComponent(pi)}`)
    .then((e) => (e.ok ? e.json() : null)).then(render).catch(() => {});
}
