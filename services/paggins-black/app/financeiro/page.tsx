import { Topbar } from '@/components/shell';
import { Button, Card, CardBody, CardHeader, CardTitle } from '@/components/ui';
import { KpiRow } from '@/components/kpi';
import { SalesChart } from '@/components/sales-chart';
import { METRICAS, MRR_SERIE } from '@/lib/data';
import { brl } from '@/lib/utils';
import { ArrowUpRight } from 'lucide-react';

export default function FinanceiroPage() {
  return (
    <>
      <Topbar crumbs={['Financeiro', 'Visão Geral']} />
      <main className="px-8 pb-14">
        <KpiRow items={[
          { label: 'Disponível para saque', value: brl(METRICAS.disponivelSaque) },
          { label: 'A receber', value: brl(METRICAS.faturamentoTotal * 0.15), hint: 'liberação em D+2' },
          { label: 'Em análise', value: brl(METRICAS.faturamentoTotal * 0.03) },
          { label: 'Reservado', value: brl(0) },
        ]} />
        <div className="mb-6 flex justify-end">
          <Button><ArrowUpRight size={16} /> Solicitar saque</Button>
        </div>
        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-normal text-muted-foreground">Receita líquida · 6 meses</CardTitle>
            <p className="mt-1 text-[30px] font-bold tracking-tight">{brl(METRICAS.faturamentoTotal)}</p>
          </CardHeader>
          <CardBody><SalesChart data={MRR_SERIE} /></CardBody>
        </Card>
      </main>
    </>
  );
}
