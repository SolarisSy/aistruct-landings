import { AgentChat } from '@/components/agent-chat';
import { KB_DEMO } from '@/lib/agent-kb';
import { PlayCircle, CheckCircle2, Lock } from 'lucide-react';

const MODULOS = [
  { t: 'Módulo 1 — Fundamentos', done: true },
  { t: 'Módulo 2 — Escolhendo seu primeiro produto', done: false, atual: true },
  { t: 'Módulo 3 — Estruturando a oferta', done: false },
  { t: 'Módulo 4 — Tráfego e escala', done: false, locked: true },
];

/** Área de membros de exemplo — mostra o Tutor IA (feature #2) inline na coluna. */
export default function MembrosPage() {
  const p = KB_DEMO.tutor;
  return (
    <div className="min-h-screen bg-background">
      <div className="mx-auto max-w-6xl px-6 py-10">
        <div className="mb-8 flex items-center gap-2">
          <span className="flex h-7 w-7 items-center justify-center rounded-lg bg-white text-sm font-bold text-black">P</span>
          <span className="text-lg font-bold tracking-[0.14em]">PAGGINS</span>
          <span className="ml-2 text-sm text-muted-foreground">· Área de membros</span>
        </div>

        <div className="grid grid-cols-1 gap-8 lg:grid-cols-[1.4fr_1fr]">
          {/* player + conteúdo */}
          <div>
            <div className="flex aspect-video items-center justify-center rounded-[var(--radius-card)] border border-border bg-elevated">
              <PlayCircle size={56} className="text-muted-foreground" />
            </div>
            <h1 className="mt-5 text-xl font-bold tracking-tight">{p.produto}</h1>
            <p className="mt-1 text-sm text-primary">{p.aula}</p>

            <div className="mt-6 space-y-2">
              {MODULOS.map((m) => (
                <div
                  key={m.t}
                  className={
                    'flex items-center gap-3 rounded-[var(--radius-field)] border px-4 py-3 text-sm ' +
                    (m.atual ? 'border-primary/40 bg-primary/5' : 'border-border')
                  }
                >
                  {m.done ? (
                    <CheckCircle2 size={18} className="text-success" />
                  ) : m.locked ? (
                    <Lock size={18} className="text-muted-foreground" />
                  ) : (
                    <PlayCircle size={18} className="text-primary" />
                  )}
                  <span className={m.locked ? 'text-muted-foreground' : ''}>{m.t}</span>
                  {m.atual && <span className="ml-auto text-xs text-primary">assistindo</span>}
                </div>
              ))}
            </div>
          </div>

          {/* TUTOR IA — feature #2, inline */}
          <div>
            <p className="mb-3 text-sm font-semibold">Tutor IA</p>
            <AgentChat tipo="tutor" mode="inline" />
          </div>
        </div>
      </div>
    </div>
  );
}
