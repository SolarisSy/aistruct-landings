import * as React from 'react';
import { cn } from '@/lib/utils';

/* ---------- Card ---------- */
export function Card({ className, ...p }: React.ComponentProps<'div'>) {
  return (
    <div
      className={cn(
        'rounded-[var(--radius-card)] border border-border bg-card shadow-[0_1px_0_0_rgba(255,255,255,0.03)_inset]',
        className
      )}
      {...p}
    />
  );
}

export function CardHeader({ className, ...p }: React.ComponentProps<'div'>) {
  return <div className={cn('px-6 pt-5 pb-3', className)} {...p} />;
}

export function CardTitle({ className, ...p }: React.ComponentProps<'h3'>) {
  return <h3 className={cn('text-base font-semibold tracking-tight', className)} {...p} />;
}

export function CardBody({ className, ...p }: React.ComponentProps<'div'>) {
  return <div className={cn('px-6 pb-6', className)} {...p} />;
}

/* ---------- Button ---------- */
type BtnProps = React.ComponentProps<'button'> & {
  variant?: 'primary' | 'outline' | 'ghost' | 'link';
  size?: 'sm' | 'md' | 'lg';
};

export function Button({ className, variant = 'primary', size = 'md', ...p }: BtnProps) {
  return (
    <button
      className={cn(
        'inline-flex items-center justify-center gap-2 rounded-[var(--radius-pill)] font-medium transition-colors',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 disabled:opacity-50',
        size === 'sm' && 'h-8 px-3.5 text-xs',
        size === 'md' && 'h-10 px-5 text-sm',
        size === 'lg' && 'h-12 px-8 text-sm',
        variant === 'primary' && 'bg-primary text-primary-foreground hover:bg-primary-hover',
        variant === 'outline' &&
          'border border-border-strong bg-transparent text-foreground hover:bg-elevated',
        variant === 'ghost' && 'text-muted-foreground hover:bg-elevated hover:text-foreground',
        variant === 'link' && 'h-auto px-0 text-foreground underline underline-offset-4',
        className
      )}
      {...p}
    />
  );
}

/* ---------- Badge / pills de status ---------- */
type BadgeTone = 'success' | 'neutral' | 'primary' | 'warning' | 'danger';

export function Badge({
  tone = 'neutral',
  className,
  ...p
}: React.ComponentProps<'span'> & { tone?: BadgeTone }) {
  return (
    <span
      className={cn(
        'inline-flex items-center rounded-[var(--radius-pill)] px-3 py-1 text-xs font-medium',
        tone === 'success' && 'bg-success/12 text-success',
        tone === 'neutral' && 'bg-elevated text-muted-foreground',
        tone === 'primary' && 'bg-primary/12 text-primary',
        tone === 'warning' && 'bg-warning/12 text-warning',
        tone === 'danger' && 'bg-danger/12 text-danger',
        className
      )}
      {...p}
    />
  );
}

/* ---------- Campos ---------- */
export function Label({ className, ...p }: React.ComponentProps<'label'>) {
  return <label className={cn('mb-2 block text-sm text-foreground/90', className)} {...p} />;
}

const fieldBase =
  'w-full rounded-[var(--radius-field)] border border-border bg-input px-4 text-sm text-foreground ' +
  'transition-colors outline-none focus:border-primary/60 focus:ring-2 focus:ring-primary/15';

export function Input({ className, ...p }: React.ComponentProps<'input'>) {
  return <input className={cn(fieldBase, 'h-11', className)} {...p} />;
}

export function Textarea({ className, ...p }: React.ComponentProps<'textarea'>) {
  return <textarea className={cn(fieldBase, 'min-h-24 py-3 resize-y', className)} {...p} />;
}

export function Select({ className, children, ...p }: React.ComponentProps<'select'>) {
  return (
    <select
      className={cn(fieldBase, 'h-11 appearance-none pr-10 text-muted-foreground', className)}
      style={{
        backgroundImage:
          "url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%238b8b96' stroke-width='2'><path d='M4 6l4 4 4-4'/></svg>\")",
        backgroundRepeat: 'no-repeat',
        backgroundPosition: 'right 14px center',
      }}
      {...p}
    >
      {children}
    </select>
  );
}

/* ---------- Tabela ---------- */
export function Table({ className, ...p }: React.ComponentProps<'table'>) {
  return (
    <div className="w-full overflow-x-auto">
      <table className={cn('w-full border-collapse text-sm', className)} {...p} />
    </div>
  );
}

export function Th({ className, ...p }: React.ComponentProps<'th'>) {
  return (
    <th
      className={cn(
        'border-b border-border px-4 py-3 text-left text-sm font-semibold text-foreground',
        className
      )}
      {...p}
    />
  );
}

export function Td({ className, ...p }: React.ComponentProps<'td'>) {
  return (
    <td className={cn('border-b border-border/60 px-4 py-4 text-muted-foreground', className)} {...p} />
  );
}

/* ---------- Switch ---------- */
export function Switch({ checked = true }: { checked?: boolean }) {
  return (
    <span
      role="switch"
      aria-checked={checked}
      className={cn(
        'relative inline-flex h-6 w-11 items-center rounded-[var(--radius-pill)] transition-colors',
        checked ? 'bg-primary' : 'bg-elevated'
      )}
    >
      <span
        className={cn(
          'absolute h-4.5 w-4.5 rounded-full bg-white transition-transform',
          checked ? 'translate-x-[24px]' : 'translate-x-[4px]'
        )}
        style={{ height: 18, width: 18 }}
      />
    </span>
  );
}
