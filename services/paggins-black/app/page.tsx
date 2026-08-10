import { Topbar } from '@/components/shell';
import { Badge, Card, CardBody, CardHeader, CardTitle } from '@/components/ui';
import { MetodoChart, SalesChart } from '@/components/sales-chart';
import { ProductThumb } from '@/components/thumb';
import {
  METRICAS, PEDIDOS, POR_METODO, TOP_PRODUTOS, VENDAS_POR_HORA, produtoDe,
} from '@/lib/data';
import { brl } from '@/lib/utils';
import {
  Wallet, ShoppingBag, Users, Radio, ChevronRight, ChevronDown, Package,
  RotateCcw, BadgeDollarSign, TrendingUp, TrendingDown,
} from 'lucide-react';

const pct = (n: number) => `${n.toFixed(1)}%`;

const STATS = [
  { label: 'Disponível para saque', value: brl(METRICAS.disponivelSaque), icon: Wallet, delta: +12.4 },
  { label: 'Pedidos hoje', value: String(METRICAS.pedidosHoje), icon: ShoppingBag, delta: +8.1 },
  { label: 'Total de visitantes', value: METRICAS.visitantes.toLocaleString('pt-BR'), icon: Users, delta: -3.2 },
  { label: 'Visitantes ao vivo', value: String(METRICAS.aoVivo), icon: Radio, delta: +21.7 },
];

const ALERTS = [
  { n: METRICAS.pendentes, text: 'pedidos para processar', icon: Package },
  { n: METRICAS.reembolsos, text: 'reembolsos solicitados', icon: RotateCcw },
  { n: METRICAS.chargebacks, text: 'chargebacks abertos', icon: BadgeDollarSign },
  { n: 1, text: 'saque antecipado aprovado', icon: BadgeDollarSign },
];

const ULTIMAS = PEDIDOS.filter((p) => p.status === 'Aprovado').slice(0, 6);

export default function DashboardPage() {
  const receitaMetodoTotal = POR_METODO.reduce((s, m) => s + m.receita, 0);

  return (
    <>
      <Topbar crumbs={['Dashboard']} />

      <main className="px-8 pb-14">
        <div className="mb-7 flex items-end justify-between">
          <div>
            <h1 className="text-[30px] font-bold tracking-tight">Bem-vindo(a), Arthur!</h1>
            <p className="mt-1.5 text-sm text-muted-foreground">
              Confira o que está acontecendo na sua loja hoje.
            </p>
          </div>
          <button className="flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
            Hoje <ChevronDown size={15} />
          </button>
        </div>

        {/* KPIs */}
        <div className="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
          {STATS.map((s) => (
            <Card key={s.label} className="flex items-center justify-between px-5 py-4">
              <div>
                <p className="text-xs text-muted-foreground">{s.label}</p>
                <p className="mt-1.5 text-[22px] font-bold tracking-tight">{s.value}</p>
                <p className={`mt-1 flex items-center gap-1 text-xs ${s.delta >= 0 ? 'text-success' : 'text-danger'}`}>
                  {s.delta >= 0 ? <TrendingUp size={13} /> : <TrendingDown size={13} />}
                  {Math.abs(s.delta)}% <span className="text-muted-foreground">vs ontem</span>
                </p>
              </div>
              <span className="flex h-11 w-11 items-center justify-center rounded-[var(--radius-field)] bg-primary text-white">
                <s.icon size={19} />
              </span>
            </Card>
          ))}
        </div>

        {/* gráfico principal */}
        <Card className="mb-6">
          <CardHeader className="flex items-start justify-between">
            <div>
              <CardTitle className="text-sm font-normal text-muted-foreground">Total de vendas · hoje</CardTitle>
              <p className="mt-1 text-[32px] font-bold tracking-tight">{brl(METRICAS.faturamentoHoje)}</p>
            </div>
            <div className="flex gap-8 pt-1 text-right">
              <div>
                <p className="text-xs text-muted-foreground">Ticket médio</p>
                <p className="mt-0.5 text-base font-semibold">{brl(METRICAS.ticketMedio)}</p>
              </div>
              <div>
                <p className="text-xs text-muted-foreground">Aprovação</p>
                <p className="mt-0.5 text-base font-semibold text-success">{pct(METRICAS.aprovacao)}</p>
              </div>
            </div>
          </CardHeader>
          <CardBody>
            <SalesChart data={VENDAS_POR_HORA} />
          </CardBody>
        </Card>

        {/* top produtos + método */}
        <div className="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-[1.6fr_1fr]">
          <Card>
            <CardHeader className="flex items-center justify-between">
              <CardTitle>Produtos mais vendidos</CardTitle>
              <span className="text-xs text-muted-foreground">receita aprovada</span>
            </CardHeader>
            <CardBody className="space-y-3.5">
              {TOP_PRODUTOS.slice(0, 6).map((t) => {
                const share = (t.receita / TOP_PRODUTOS[0].receita) * 100;
                return (
                  <div key={t.prod.id} className="flex items-center gap-3.5">
                    <ProductThumb nome={t.prod.nome} cor={t.prod.cor} tags={t.prod.tags} />
                    <div className="min-w-0 flex-1">
                      <div className="flex items-baseline justify-between gap-4">
                        <p className="truncate text-sm font-medium">{t.prod.nome}</p>
                        <p className="shrink-0 text-sm font-semibold">{brl(t.receita)}</p>
                      </div>
                      <div className="mt-1.5 flex items-center gap-3">
                        <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-elevated">
                          <div className="h-full rounded-full bg-primary" style={{ width: `${share}%` }} />
                        </div>
                        <span className="shrink-0 text-xs text-muted-foreground">{t.qtd} vendas</span>
                      </div>
                    </div>
                  </div>
                );
              })}
            </CardBody>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Receita por método</CardTitle>
            </CardHeader>
            <CardBody>
              <MetodoChart data={POR_METODO} />
              <div className="mt-4 space-y-2">
                {POR_METODO.map((m) => (
                  <div key={m.metodo} className="flex items-center justify-between text-xs">
                    <span className="text-muted-foreground">{m.metodo}</span>
                    <span className="font-medium">
                      {((m.receita / receitaMetodoTotal) * 100).toFixed(0)}% · {brl(m.receita)}
                    </span>
                  </div>
                ))}
              </div>
            </CardBody>
          </Card>
        </div>

        {/* últimas vendas */}
        <Card className="mb-6">
          <CardHeader className="flex items-center justify-between">
            <CardTitle>Últimas vendas aprovadas</CardTitle>
            <button className="text-xs text-primary hover:underline">Ver todos os pedidos</button>
          </CardHeader>
          <CardBody className="space-y-1">
            {ULTIMAS.map((p) => {
              const prod = produtoDe(p.produtoId);
              return (
                <div
                  key={p.id}
                  className="flex items-center gap-4 rounded-[var(--radius-field)] px-2 py-2.5 transition-colors hover:bg-elevated/50"
                >
                  <ProductThumb nome={prod.nome} cor={prod.cor} tags={prod.tags} size={32} />
                  <div className="min-w-0 flex-1">
                    <p className="truncate text-sm font-medium">{p.cliente}</p>
                    <p className="truncate text-xs text-muted-foreground">{prod.nome}</p>
                  </div>
                  <Badge tone="neutral" className="hidden sm:inline-flex">
                    {p.metodo}{p.parcelas > 1 ? ` ${p.parcelas}x` : ''}
                  </Badge>
                  <span className="hidden w-10 text-xs text-muted-foreground md:block">{p.uf}</span>
                  <span className="w-24 text-right text-sm font-semibold">{brl(p.valor)}</span>
                  <span className="w-14 text-right text-xs text-muted-foreground">
                    {String(p.hora).padStart(2, '0')}:{String(p.minuto).padStart(2, '0')}
                  </span>
                </div>
              );
            })}
          </CardBody>
        </Card>

        {/* pendências */}
        <div className="space-y-3.5">
          {ALERTS.map((a, i) => (
            <button
              key={i}
              className="flex w-full items-center gap-3 rounded-[var(--radius-card)] border border-border bg-card px-6 py-4 text-left transition-colors hover:border-border-strong hover:bg-elevated"
            >
              <a.icon size={18} className="text-muted-foreground" />
              <span className="flex-1 text-sm">
                <span className="font-semibold text-foreground">{a.n}</span>{' '}
                <span className="text-muted-foreground">{a.text}</span>
              </span>
              <ChevronRight size={18} className="text-muted-foreground" />
            </button>
          ))}
        </div>
      </main>
    </>
  );
}
