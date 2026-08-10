'use client';

import { Topbar } from '@/components/shell';
import { Badge, Card, CardBody, CardHeader, CardTitle, Select, Table, Td, Th } from '@/components/ui';
import { Tabs } from '@/components/tabs';
import { MetodoChart, SalesChart } from '@/components/sales-chart';
import { ASSIN_METRICAS, ASSINATURAS, MRR_SERIE, type AssinStatus } from '@/lib/data';
import { brl } from '@/lib/utils';
import { Search } from 'lucide-react';

const tone = (s: AssinStatus) =>
  s === 'Ativa' ? 'success' : s === 'Cancelada' ? 'neutral' : s === 'Inadimplente' ? 'danger' : 'warning';

const KPIS = [
  { label: 'Assinaturas ativas', value: String(ASSIN_METRICAS.ativas), delta: +6.3 },
  { label: 'Novos assinantes', value: String(ASSIN_METRICAS.novos), delta: +11.0 },
  { label: 'Canceladas', value: String(ASSIN_METRICAS.canceladas), delta: -2.1 },
  { label: 'Inadimplentes', value: String(ASSIN_METRICAS.inadimplentes), delta: -0.8 },
];

function VisaoGeral() {
  return (
    <>
      <div className="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        {KPIS.map((k) => (
          <Card key={k.label} className="px-5 py-4">
            <p className="text-xs text-muted-foreground">{k.label}</p>
            <p className="mt-1.5 text-[22px] font-bold tracking-tight">{k.value}</p>
            <p className={`mt-1 text-xs ${k.delta >= 0 ? 'text-success' : 'text-danger'}`}>
              {k.delta >= 0 ? '▲' : '▼'} {Math.abs(k.delta)}% <span className="text-muted-foreground">vs período anterior</span>
            </p>
          </Card>
        ))}
      </div>

      <div className="grid grid-cols-1 gap-5 lg:grid-cols-[1.6fr_1fr]">
        <Card>
          <CardHeader>
            <CardTitle className="text-sm font-normal text-muted-foreground">Receita recorrente mensal (MRR)</CardTitle>
            <p className="mt-1 text-[30px] font-bold tracking-tight">{brl(ASSIN_METRICAS.mrr)}</p>
          </CardHeader>
          <CardBody>
            <SalesChart data={MRR_SERIE} />
          </CardBody>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle>Assinantes por plano</CardTitle>
          </CardHeader>
          <CardBody>
            <MetodoChart
              data={(['Mensal', 'Trimestral', 'Anual'] as const).map((plano) => ({
                metodo: plano,
                receita: ASSINATURAS.filter((a) => a.status === 'Ativa' && a.plano === plano).length,
              }))}
            />
          </CardBody>
        </Card>
      </div>
    </>
  );
}

function Lista() {
  return (
    <Card>
      <CardHeader className="flex flex-wrap items-center justify-between gap-4 pb-5">
        <CardTitle>Assinaturas</CardTitle>
        <div className="flex items-center gap-3">
          <Select defaultValue="todos" className="h-10 w-[150px]">
            <option value="todos">Todos os status</option>
            <option>Ativa</option>
            <option>Cancelada</option>
            <option>Inadimplente</option>
            <option>Trial</option>
          </Select>
          <div className="relative w-[220px]">
            <Search size={15} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
            <input
              placeholder="Assinante ou e-mail"
              className="h-10 w-full rounded-[var(--radius-pill)] border border-border bg-input pl-10 pr-4 text-sm outline-none transition-colors focus:border-primary/50"
            />
          </div>
        </div>
      </CardHeader>
      <CardBody className="px-2">
        <Table>
          <thead>
            <tr>
              <Th className="pl-4">Assinante</Th>
              <Th>Produto</Th>
              <Th>Plano</Th>
              <Th className="text-right">Valor</Th>
              <Th>Status</Th>
              <Th>Desde</Th>
              <Th>Próxima cobrança</Th>
            </tr>
          </thead>
          <tbody>
            {ASSINATURAS.slice(0, 30).map((a) => (
              <tr key={a.id} className="transition-colors hover:bg-elevated/40">
                <Td className="pl-4">
                  <p className="text-foreground/90">{a.assinante}</p>
                  <p className="text-xs text-muted-foreground/70">{a.email}</p>
                </Td>
                <Td className="max-w-[200px] truncate">{a.produto}</Td>
                <Td>{a.plano}</Td>
                <Td className="text-right font-medium text-foreground">{brl(a.valor)}</Td>
                <Td><Badge tone={tone(a.status)}>{a.status}</Badge></Td>
                <Td>{a.desde}</Td>
                <Td>{a.status === 'Ativa' ? a.proxima : '—'}</Td>
              </tr>
            ))}
          </tbody>
        </Table>
        <div className="pt-6 pb-2 pl-4 text-xs text-muted-foreground">
          Exibindo 30 de {ASSINATURAS.length} assinaturas
        </div>
      </CardBody>
    </Card>
  );
}

export default function AssinaturasPage() {
  return (
    <>
      <Topbar crumbs={['Assinaturas']} />
      <main className="px-8 pb-14">
        <h1 className="mb-6 text-[26px] font-bold tracking-tight">Assinaturas</h1>
        <Tabs
          tabs={[
            { key: 'geral', label: 'Visão geral' },
            { key: 'lista', label: 'Assinaturas', count: ASSINATURAS.length },
          ]}
        >
          {(active) => (active === 'geral' ? <VisaoGeral /> : <Lista />)}
        </Tabs>
      </main>
    </>
  );
}
