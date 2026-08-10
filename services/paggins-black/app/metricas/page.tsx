import { Topbar } from '@/components/shell';
import { Card, CardBody, CardHeader, CardTitle } from '@/components/ui';
import { KpiRow } from '@/components/kpi';
import { MetodoChart, SalesChart } from '@/components/sales-chart';
import { METRICAS, POR_METODO, TOP_PRODUTOS, VENDAS_POR_HORA } from '@/lib/data';
import { brl } from '@/lib/utils';

export default function MetricasPage() {
  return (
    <>
      <Topbar crumbs={['Relatórios', 'Métricas']} />
      <main className="px-8 pb-14">
        <KpiRow items={[
          { label: 'Faturamento', value: brl(METRICAS.faturamentoTotal) },
          { label: 'Taxa de aprovação', value: `${METRICAS.aprovacao.toFixed(1)}%` },
          { label: 'Ticket médio', value: brl(METRICAS.ticketMedio) },
          { label: 'Visitantes', value: METRICAS.visitantes.toLocaleString('pt-BR') },
        ]} />

        <Card className="mb-6">
          <CardHeader><CardTitle className="text-sm font-normal text-muted-foreground">Vendas por hora</CardTitle></CardHeader>
          <CardBody><SalesChart data={VENDAS_POR_HORA} /></CardBody>
        </Card>

        <div className="grid grid-cols-1 gap-5 lg:grid-cols-[1.6fr_1fr]">
          <Card>
            <CardHeader><CardTitle>Produtos mais vendidos</CardTitle></CardHeader>
            <CardBody className="space-y-3.5">
              {TOP_PRODUTOS.slice(0, 7).map((t) => {
                const share = (t.receita / TOP_PRODUTOS[0].receita) * 100;
                return (
                  <div key={t.prod.id} className="flex items-center gap-3.5">
                    <span className={`h-8 w-8 shrink-0 rounded-lg bg-gradient-to-br ${t.prod.cor}`} />
                    <div className="min-w-0 flex-1">
                      <div className="flex items-baseline justify-between gap-4">
                        <p className="truncate text-sm font-medium">{t.prod.nome}</p>
                        <p className="shrink-0 text-sm font-semibold">{brl(t.receita)}</p>
                      </div>
                      <div className="mt-1.5 h-1.5 overflow-hidden rounded-full bg-elevated">
                        <div className="h-full rounded-full bg-primary" style={{ width: `${share}%` }} />
                      </div>
                    </div>
                  </div>
                );
              })}
            </CardBody>
          </Card>
          <Card>
            <CardHeader><CardTitle>Receita por método</CardTitle></CardHeader>
            <CardBody><MetodoChart data={POR_METODO} /></CardBody>
          </Card>
        </div>
      </main>
    </>
  );
}
