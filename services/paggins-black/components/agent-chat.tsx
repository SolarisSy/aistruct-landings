'use client';

import * as React from 'react';
import { Sparkles, Send, X, MessageSquare } from 'lucide-react';
import { AGENTES_CFG, type AgenteTipo, type KbContext } from '@/lib/agent-kb';
import { cn } from '@/lib/utils';

type Msg = { role: 'user' | 'assistant'; content: string };

/**
 * Widget de chat do agente IA. `tipo` decide a persona (checkout/tutor).
 * `mode="inline"` renderiza fixo no fluxo; default é bolha flutuante.
 */
export function AgentChat({
  tipo = 'checkout',
  ctx,
  mode = 'floating',
}: {
  tipo?: AgenteTipo;
  ctx?: Partial<KbContext>;
  mode?: 'floating' | 'inline';
}) {
  const cfg = AGENTES_CFG[tipo];
  const [open, setOpen] = React.useState(mode === 'inline');
  const [msgs, setMsgs] = React.useState<Msg[]>([{ role: 'assistant', content: cfg.saudacao }]);
  const [input, setInput] = React.useState('');
  const [loading, setLoading] = React.useState(false);
  const scrollRef = React.useRef<HTMLDivElement>(null);

  React.useEffect(() => {
    scrollRef.current?.scrollTo({ top: scrollRef.current.scrollHeight, behavior: 'smooth' });
  }, [msgs, loading]);

  async function send(text: string) {
    const t = text.trim();
    if (!t || loading) return;
    const next = [...msgs, { role: 'user' as const, content: t }];
    setMsgs(next);
    setInput('');
    setLoading(true);
    try {
      const r = await fetch('/api/agent', {
        method: 'POST',
        headers: { 'content-type': 'application/json' },
        body: JSON.stringify({ tipo, ctx, messages: next.slice(-10) }),
      });
      const data = await r.json();
      setMsgs((m) => [...m, { role: 'assistant', content: data.reply ?? '...' }]);
    } catch {
      setMsgs((m) => [...m, { role: 'assistant', content: 'Falha de conexão, tenta de novo.' }]);
    } finally {
      setLoading(false);
    }
  }

  const panel = (
    <div
      className={cn(
        'flex flex-col overflow-hidden rounded-[var(--radius-card)] border border-border-strong bg-card',
        mode === 'floating' ? 'h-[520px] w-[380px] shadow-2xl' : 'h-[540px] w-full'
      )}
    >
      {/* header */}
      <div className="flex items-center gap-3 border-b border-border bg-elevated px-4 py-3">
        <span className="flex h-8 w-8 items-center justify-center rounded-full bg-primary/15 text-primary">
          <Sparkles size={16} />
        </span>
        <div className="flex-1">
          <p className="text-sm font-semibold leading-tight">{cfg.nome}</p>
          <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
            <span className="h-1.5 w-1.5 rounded-full bg-success" /> online agora
          </p>
        </div>
        {mode === 'floating' && (
          <button onClick={() => setOpen(false)} className="text-muted-foreground hover:text-foreground">
            <X size={18} />
          </button>
        )}
      </div>

      {/* mensagens */}
      <div ref={scrollRef} className="flex-1 space-y-3 overflow-y-auto px-4 py-4">
        {msgs.map((m, i) => (
          <div key={i} className={cn('flex', m.role === 'user' ? 'justify-end' : 'justify-start')}>
            <div
              className={cn(
                'max-w-[80%] rounded-2xl px-3.5 py-2 text-sm',
                m.role === 'user'
                  ? 'rounded-br-sm bg-primary text-primary-foreground'
                  : 'rounded-bl-sm bg-elevated text-foreground'
              )}
            >
              {m.content}
            </div>
          </div>
        ))}
        {loading && (
          <div className="flex justify-start">
            <div className="flex gap-1 rounded-2xl rounded-bl-sm bg-elevated px-4 py-3">
              <span className="h-1.5 w-1.5 animate-bounce rounded-full bg-muted-foreground [animation-delay:-0.3s]" />
              <span className="h-1.5 w-1.5 animate-bounce rounded-full bg-muted-foreground [animation-delay:-0.15s]" />
              <span className="h-1.5 w-1.5 animate-bounce rounded-full bg-muted-foreground" />
            </div>
          </div>
        )}
      </div>

      {/* input */}
      <form
        onSubmit={(e) => {
          e.preventDefault();
          send(input);
        }}
        className="flex items-center gap-2 border-t border-border px-3 py-3"
      >
        <input
          value={input}
          onChange={(e) => setInput(e.target.value)}
          placeholder="Escreva sua mensagem..."
          className="h-10 flex-1 rounded-[var(--radius-pill)] border border-border bg-input px-4 text-sm outline-none focus:border-primary/50"
        />
        <button
          type="submit"
          disabled={loading || !input.trim()}
          className="flex h-10 w-10 items-center justify-center rounded-full bg-primary text-white transition-colors hover:bg-primary-hover disabled:opacity-40"
        >
          <Send size={16} />
        </button>
      </form>
    </div>
  );

  if (mode === 'inline') return panel;

  return (
    <div className="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3">
      {open && panel}
      <button
        onClick={() => setOpen((o) => !o)}
        className="flex h-14 w-14 items-center justify-center rounded-full bg-primary text-white shadow-xl transition-transform hover:scale-105"
        aria-label="Abrir assistente"
      >
        {open ? <X size={22} /> : <MessageSquare size={22} />}
      </button>
    </div>
  );
}
