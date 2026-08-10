import { AgentChat } from '@/components/agent-chat';
import { KB_DEMO } from '@/lib/agent-kb';
import { ShieldCheck, Lock, Zap } from 'lucide-react';

/** Checkout de exemplo — mostra o Agente IA (assistente de checkout) em ação. */
export default function CheckoutPage() {
  const p = KB_DEMO.checkout;
  return (
    <div className="min-h-screen bg-background">
      <div className="mx-auto grid max-w-5xl grid-cols-1 gap-8 px-6 py-12 lg:grid-cols-[1.3fr_1fr]">
        {/* resumo do produto */}
        <div>
          <div className="mb-8">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src="/paggins-logo.png" alt="Paggins" className="h-6 w-auto" />
          </div>

          <h1 className="text-2xl font-bold tracking-tight">Finalize sua compra</h1>
          <p className="mt-1 text-sm text-muted-foreground">Falta pouco para você ter acesso.</p>

          <div className="mt-6 rounded-[var(--radius-card)] border border-border bg-card p-5">
            <div className="flex items-center gap-4">
              <span className="h-14 w-14 shrink-0 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-800" />
              <div>
                <p className="font-semibold">{p.produto}</p>
                <p className="text-sm text-muted-foreground">{p.descricao}</p>
              </div>
            </div>
            <div className="mt-5 flex items-baseline justify-between border-t border-border pt-4">
              <span className="text-sm text-muted-foreground">Total</span>
              <span className="text-xl font-bold">{p.preco}</span>
            </div>
          </div>

          <div className="mt-4 flex flex-wrap gap-4 text-xs text-muted-foreground">
            <span className="flex items-center gap-1.5"><ShieldCheck size={14} className="text-success" /> 7 dias de garantia</span>
            <span className="flex items-center gap-1.5"><Lock size={14} /> pagamento seguro</span>
            <span className="flex items-center gap-1.5"><Zap size={14} className="text-warning" /> acesso imediato</span>
          </div>
        </div>

        {/* form de pagamento (estático) */}
        <div className="rounded-[var(--radius-card)] border border-border bg-card p-5">
          <p className="mb-4 text-sm font-semibold">Pagamento</p>
          <div className="space-y-3">
            <div className="flex gap-2">
              {['PIX', 'Cartão', 'Boleto'].map((m, i) => (
                <button
                  key={m}
                  className={
                    'flex-1 rounded-[var(--radius-field)] border px-3 py-2 text-sm transition-colors ' +
                    (i === 0 ? 'border-primary bg-primary/10 text-primary' : 'border-border text-muted-foreground hover:bg-elevated')
                  }
                >
                  {m}
                </button>
              ))}
            </div>
            <input placeholder="Nome completo" className="h-11 w-full rounded-[var(--radius-field)] border border-border bg-input px-4 text-sm outline-none focus:border-primary/50" />
            <input placeholder="E-mail" className="h-11 w-full rounded-[var(--radius-field)] border border-border bg-input px-4 text-sm outline-none focus:border-primary/50" />
            <button className="h-12 w-full rounded-[var(--radius-pill)] bg-primary text-sm font-semibold text-white transition-colors hover:bg-primary-hover">
              Pagar {p.preco?.split('(')[0]}
            </button>
            <p className="text-center text-xs text-muted-foreground">
              Dúvida antes de pagar? Fale com o assistente no canto da tela 👉
            </p>
          </div>
        </div>
      </div>

      {/* AGENTE IA — assistente de checkout (feature #1) */}
      <AgentChat tipo="checkout" />
    </div>
  );
}
