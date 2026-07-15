/* obrigado v4 (hyustripe2) — confirma Paggins (session_id=cs_...), Stripe
 * EMBEDDED (payment_intent=pi_...) e Stripe HOSTED (gw=stripe + session_id=
 * cs_... — mesma cara do id Paggins, desambiguado pelo gw). PIX assíncrono:
 * "aguardando confirmação". /_astro/ é immutable — arquivo NOVO (base: hyustripe1). */
import { m as clearCart, C as API, f as fmt } from "./cart.D0a-thWQ.js";

clearCart();
localStorage.removeItem("hyu-lead-ok");

const q = new URLSearchParams(location.search);
const sid = q.get("session_id");
const pi = q.get("payment_intent");
const ref = q.get("ref");
const isStripe = q.get("gw") === "stripe";
const t = document.querySelector("[data-thanks-ref]");
const validSid = !!sid && /^cs_[A-Za-z0-9_]+$/.test(sid);
const validPi = !!pi && /^pi_[A-Za-z0-9]+$/.test(pi);
const show = ref || (validSid ? sid.replace(/^cs_/, "").slice(0, 12) : "");

if (t && show) {
  t.textContent = `Referência do pedido: ${show}`;
  t.hidden = false;
}

function render(e) {
  if (!e || !t) return;
  const paid = e.paymentStatus === "paid" || e.status === "completed";
  const waiting = e.status === "processing" || e.status === "requires_action"
    || e.paymentStatus === "unpaid";
  const n = typeof e.totalAmount == "number" ? ` · ${fmt(e.totalAmount)}` : "";
  const r = show ? ` · ref ${show}` : "";
  t.textContent = paid ? `✓ Pagamento confirmado${n}${r}`
    : waiting ? `Pedido recebido — aguardando a confirmação do pagamento${n}${r}`
    : `Pedido recebido${n}${r}`;
}

const path = isStripe && validSid ? `/stripe/csession/${encodeURIComponent(sid)}`
  : validPi ? `/stripe/session/${encodeURIComponent(pi)}`
  : validSid ? `/session/${encodeURIComponent(sid)}`
  : null;
if (path) {
  fetch(API + path).then((e) => (e.ok ? e.json() : null)).then(render).catch(() => {});
}
