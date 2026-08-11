import { Topbar } from '@/components/shell';
import { Badge, Card, CardBody, CardHeader, CardTitle } from '@/components/ui';
import { Award, TrendingUp, ShoppingBag, CheckCircle2, ShieldAlert, Repeat } from 'lucide-react';

const CRITERIOS = [
  { icon: TrendingUp, label: 'GMV líquido aprovado', peso: 'Peso: 40%', tone: 'muted' },
  { icon: ShoppingBag, label: 'Volume de pedidos', peso: 'Peso: 20%', tone: 'muted' },
  { icon: CheckCircle2, label: 'Taxa de aprovação', peso: 'Peso: 15%', tone: 'muted' },
  { icon: ShieldAlert, label: 'Chargebacks', peso: 'Impacto Negativo', tone: 'danger' },
  { icon: Repeat, label: 'Constância', peso: 'Peso: 10%', tone: 'muted' },
];

const LEGADO = [
  { titulo: 'Financeiro', desc: 'Nenhum marco alcançado ainda.' },
  { titulo: 'Tempo', desc: 'Nenhum marco alcançado ainda.' },
  { titulo: 'Qualidade', desc: 'Nenhum marco alcançado ainda.' },
];

export default function ReconhecimentoPage() {
  return (
    <>
      <Topbar crumbs={['Reconhecimento']} />
      <main className="px-8 pb-14">
        {/* Status */}
        <section className="mb-10">
          <h1 className="text-[26px] font-bold tracking-tight">Status</h1>
          <p className="mt-1 text-sm text-muted-foreground">Constância. Autoridade. Reconhecimento.</p>

          <div className="mt-6 grid grid-cols-1 gap-5 lg:grid-cols-[340px_1fr]">
            {/* selo / faixa */}
            <Card className="relative overflow-hidden">
              <div className="pointer-events-none absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-primary/15 to-transparent" />
              <CardBody className="relative flex flex-col items-center pt-8 text-center">
                <span className="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-teal shadow-lg shadow-primary/20">
                  <Award size={40} className="text-white" strokeWidth={1.6} />
                </span>
                <p className="mt-4 text-xl font-bold">Pro</p>
                <p className="text-sm text-muted-foreground">Profissionalismo comprovado.</p>

                <dl className="mt-6 w-full space-y-3 border-t border-border pt-5 text-sm">
                  <div className="flex justify-between"><dt className="text-muted-foreground">Score do ciclo</dt><dd className="font-semibold">307</dd></div>
                  <div className="flex justify-between"><dt className="text-muted-foreground">Faixa Pro</dt><dd className="font-semibold">200 – 399</dd></div>
                  <div className="flex items-center justify-between"><dt className="text-muted-foreground">Manutenção</dt><dd><Badge tone="success">Estável</Badge></dd></div>
                  <div className="flex justify-between"><dt className="text-muted-foreground">Atividade no ciclo</dt><dd className="font-semibold">7 dias ativos</dd></div>
                </dl>
              </CardBody>
            </Card>

            {/* como é calculado */}
            <Card>
              <CardHeader><CardTitle>Como seu score é calculado</CardTitle></CardHeader>
              <CardBody className="pt-1">
                <div className="divide-y divide-border/60">
                  {CRITERIOS.map((c) => (
                    <div key={c.label} className="flex items-center gap-3 py-3.5">
                      <c.icon size={17} className={c.tone === 'danger' ? 'text-danger' : 'text-muted-foreground'} />
                      <span className="flex-1 text-sm">{c.label}</span>
                      <span className={`text-sm font-semibold ${c.tone === 'danger' ? 'text-danger' : 'text-foreground'}`}>{c.peso}</span>
                    </div>
                  ))}
                </div>
                <p className="mt-4 text-center text-xs text-muted-foreground">
                  Picos isolados não garantem status. Constância constrói legados.
                </p>
              </CardBody>
            </Card>
          </div>
        </section>

        {/* Consagração */}
        <section className="mb-10">
          <h2 className="text-[22px] font-bold tracking-tight">Consagração</h2>
          <p className="mt-1 text-sm text-muted-foreground">Reconhecimento construído com permanência.</p>
          <Card className="mt-5">
            <CardBody className="flex items-center justify-center py-10 text-center">
              <p className="max-w-md text-sm text-muted-foreground">
                Mantenha seu status atual por ciclos consecutivos para iniciar uma consagração.
              </p>
            </CardBody>
          </Card>
        </section>

        {/* Legado */}
        <section>
          <h2 className="text-[22px] font-bold tracking-tight">Legado</h2>
          <p className="mt-1 mb-5 text-sm text-muted-foreground">Marcos construídos ao longo da sua trajetória.</p>
          <div className="space-y-3">
            {LEGADO.map((l) => (
              <Card key={l.titulo}>
                <CardBody className="py-5">
                  <p className="font-semibold">{l.titulo}</p>
                  <p className="mt-1 text-sm text-muted-foreground">{l.desc}</p>
                </CardBody>
              </Card>
            ))}
          </div>
        </section>
      </main>
    </>
  );
}
