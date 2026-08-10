'use client';

import * as React from 'react';
import { cn } from '@/lib/utils';

/** Abas controladas por estado local (client). */
export function Tabs({
  tabs,
  children,
}: {
  tabs: { key: string; label: string; count?: number }[];
  children: (active: string) => React.ReactNode;
}) {
  const [active, setActive] = React.useState(tabs[0].key);
  return (
    <>
      <div className="mb-6 flex gap-6 border-b border-border">
        {tabs.map((t) => (
          <button
            key={t.key}
            onClick={() => setActive(t.key)}
            className={cn(
              '-mb-px flex items-center gap-2 border-b-2 px-1 pb-3 text-sm transition-colors',
              active === t.key
                ? 'border-primary text-foreground'
                : 'border-transparent text-muted-foreground hover:text-foreground'
            )}
          >
            {t.label}
            {typeof t.count === 'number' && (
              <span
                className={cn(
                  'rounded-[var(--radius-pill)] px-2 py-0.5 text-xs',
                  active === t.key ? 'bg-primary/15 text-primary' : 'bg-elevated text-muted-foreground'
                )}
              >
                {t.count}
              </span>
            )}
          </button>
        ))}
      </div>
      {children(active)}
    </>
  );
}
