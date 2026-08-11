'use client';

import * as React from 'react';
import { ChevronDown, Plus, Check } from 'lucide-react';
import { LOJAS, type Loja } from '@/lib/lojas';
import { cn } from '@/lib/utils';

function Avatar({ loja, size = 24 }: { loja: Loja; size?: number }) {
  return (
    <span
      className={cn('flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-[11px] font-bold text-white', loja.grad)}
      style={{ width: size, height: size }}
    >
      {loja.inicial}
    </span>
  );
}

/** Seletor de loja com dropdown multi-loja (avatar próprio por loja) + "Criar loja". */
export function StoreSwitcher() {
  const [open, setOpen] = React.useState(false);
  const [ativa, setAtiva] = React.useState<Loja>(LOJAS[0]);
  const ref = React.useRef<HTMLDivElement>(null);

  React.useEffect(() => {
    function onClickOut(e: MouseEvent) {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    document.addEventListener('mousedown', onClickOut);
    return () => document.removeEventListener('mousedown', onClickOut);
  }, []);

  return (
    <div ref={ref} className="relative mx-4">
      <button
        onClick={() => setOpen((o) => !o)}
        className="flex w-full items-center gap-2.5 rounded-[var(--radius-field)] px-2 py-2 text-sm text-foreground/90 transition-colors hover:bg-sidebar-active"
      >
        <Avatar loja={ativa} />
        <span className="flex-1 truncate text-left font-medium">{ativa.nome}</span>
        <ChevronDown size={16} className={cn('text-muted-foreground transition-transform', open && 'rotate-180')} />
      </button>

      {open && (
        <div className="absolute left-0 right-0 top-full z-40 mt-1.5 overflow-hidden rounded-[var(--radius-card)] border border-border-strong bg-elevated shadow-2xl">
          <div className="max-h-[280px] overflow-y-auto p-1.5">
            {LOJAS.map((l) => (
              <button
                key={l.id}
                onClick={() => {
                  setAtiva(l);
                  setOpen(false);
                }}
                className="flex w-full items-center gap-2.5 rounded-[var(--radius-field)] px-2 py-2 text-sm transition-colors hover:bg-sidebar-active"
              >
                <Avatar loja={l} />
                <span className="flex-1 truncate text-left">{l.nome}</span>
                {l.id === ativa.id && <Check size={15} className="text-primary" />}
              </button>
            ))}
          </div>
          <button className="flex w-full items-center justify-center gap-2 border-t border-border py-3 text-sm font-medium text-primary transition-colors hover:bg-sidebar-active">
            <Plus size={16} /> Criar loja
          </button>
        </div>
      )}
    </div>
  );
}
