import { Card } from '@/components/ui';

/** Linha de KPIs reutilizável (4 colunas no desktop). */
export function KpiRow({ items }: { items: { label: string; value: string; hint?: string }[] }) {
  return (
    <div className="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
      {items.map((k) => (
        <Card key={k.label} className="px-5 py-4">
          <p className="text-xs text-muted-foreground">{k.label}</p>
          <p className="mt-1.5 text-[22px] font-bold tracking-tight">{k.value}</p>
          {k.hint && <p className="mt-1 text-xs text-muted-foreground">{k.hint}</p>}
        </Card>
      ))}
    </div>
  );
}
