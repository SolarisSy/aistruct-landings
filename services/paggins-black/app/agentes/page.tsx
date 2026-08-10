'use client';

import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody, CardHeader, CardTitle } from '@/components/ui';
import { Tabs } from '@/components/tabs';
import { AGENTE_METRICAS, AGENTES, BASES, PERSONAS } from '@/lib/agentes';
import { brl } from '@/lib/utils';
import {
  Sparkles, ShoppingCart, GraduationCap, RotateCcw, MessageSquare, BookOpen, User, Plus, Power,
} from 'lucide-react';

const ICON = { Checkout: ShoppingCart, Tutor: GraduationCap, 'Recuperação': RotateCcw } as const;

const KPIS = [
  { label: 'Receita gerada por agentes', value: brl(AGENTE_METRICAS.receita) },
  { label: 'Disparos', value: AGENTE_METRICAS.disparos.toLocaleString('pt-BR') },
  { label: 'Conversas', value: AGENTE_METRICAS.conversas.toLocaleString('pt-BR') },
  { label: 'Conversão', value: `${AGENTE_METRICAS.conversao.toFixed(1)}%` },
];

function Metricas() {
  return (
    <>
      <Card className="mb-6 border-primary/25 bg-gradient-to-br from-primary/[0.07] to-transparent">
        <CardBody className="flex flex-wrap items-center justify-between gap-4 pt-6">
          <div className="flex items-start gap-3">
            <span className="flex h-10 w-10 items-center justify-center rounded-[var(--radius-field)] bg-primary/15 text-primary">
              <Sparkles size={20} />
            </span>
            <div>
              <h2 className="text-base font-semibold">Seu time de vendas que opera 24/7</h2>
              <p className="mt-1 max-w-xl text-sm text-muted-foreground">
                Agentes de IA que abordam carrinhos abandonados, respondem objeções e fecham vendas
                — sem depender do seu horário.
              </p>
            </div>
          </div>
          <Button><Plus size={16} /> Criar agente</Button>
        </CardBody>
      </Card>

      <div className="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        {KPIS.map((k) => (
          <Card key={k.label} className="px-5 py-4">
            <p className="text-xs text-muted-foreground">{k.label}</p>
            <p className="mt-1.5 text-[22px] font-bold tracking-tight">{k.value}</p>
          </Card>
        ))}
      </div>

      <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
        {[
          { icon: '🕐', t: 'Horas trabalhadas pelos agentes', v: '318h' },
          { icon: '🌙', t: 'Respostas fora do horário comercial', v: '41%' },
          { icon: '📅', t: 'Respostas no fim de semana', v: '186' },
        ].map((c) => (
          <Card key={c.t} className="px-5 py-4">
            <p className="text-xs text-muted-foreground">{c.icon} {c.t}</p>
            <p className="mt-1.5 text-xl font-bold">{c.v}</p>
          </Card>
        ))}
      </div>
    </>
  );
}

function MeusAgentes() {
  return (
    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
      {AGENTES.map((a) => {
        const Icon = ICON[a.tipo];
        return (
          <Card key={a.id} className="flex flex-col">
            <CardBody className="pt-5">
              <div className="mb-4 flex items-start justify-between">
                <span className="flex h-10 w-10 items-center justify-center rounded-[var(--radius-field)] bg-elevated text-primary">
                  <Icon size={18} />
                </span>
                <Badge tone={a.ativo ? 'success' : 'neutral'}>
                  {a.ativo ? 'Ativo' : 'Inativo'}
                </Badge>
              </div>
              <h3 className="text-base font-semibold">{a.nome}</h3>
              <dl className="mt-4 space-y-2 text-sm">
                <div className="flex justify-between"><dt className="text-muted-foreground">Gatilho</dt><dd className="text-right">{a.gatilho}</dd></div>
                <div className="flex justify-between"><dt className="text-muted-foreground">Canal</dt><dd>{a.canal}</dd></div>
                <div className="flex justify-between"><dt className="text-muted-foreground">Persona</dt><dd>{a.persona}</dd></div>
                <div className="flex justify-between"><dt className="text-muted-foreground">Receita gerada</dt><dd className="font-medium text-foreground">{brl(a.receita)}</dd></div>
              </dl>
              <div className="mt-5 flex gap-2">
                <Button variant="outline" size="sm" className="flex-1">Configurar</Button>
                <Button variant="ghost" size="sm" aria-label="Ligar/desligar"><Power size={15} /></Button>
              </div>
            </CardBody>
          </Card>
        );
      })}
    </div>
  );
}

function Personas() {
  return (
    <div className="space-y-3">
      {PERSONAS.map((p) => (
        <Card key={p.id}>
          <CardBody className="flex items-center gap-4 pt-4">
            <span className="flex h-10 w-10 items-center justify-center rounded-full bg-elevated text-muted-foreground"><User size={18} /></span>
            <div className="min-w-0 flex-1">
              <p className="font-medium">{p.nome}</p>
              <p className="truncate text-sm text-muted-foreground">{p.tom}</p>
            </div>
            <Badge tone="neutral">{p.usadaPor} agente(s)</Badge>
          </CardBody>
        </Card>
      ))}
    </div>
  );
}

function Bases() {
  return (
    <div className="space-y-3">
      {BASES.map((b) => (
        <Card key={b.id}>
          <CardBody className="flex items-center gap-4 pt-4">
            <span className="flex h-10 w-10 items-center justify-center rounded-[var(--radius-field)] bg-elevated text-primary"><BookOpen size={18} /></span>
            <div className="min-w-0 flex-1">
              <p className="font-medium">{b.nome}</p>
              <p className="text-sm text-muted-foreground">{b.tipo}</p>
            </div>
            <Badge tone="primary">{b.itens} itens</Badge>
          </CardBody>
        </Card>
      ))}
    </div>
  );
}

export default function AgentesPage() {
  return (
    <>
      <Topbar crumbs={['Agentes IA']} />
      <main className="px-8 pb-14">
        <div className="mb-6 flex items-center gap-2">
          <h1 className="text-[26px] font-bold tracking-tight">Agentes IA</h1>
          <Badge tone="primary">Novo</Badge>
        </div>
        <Tabs
          tabs={[
            { key: 'metricas', label: 'Métricas' },
            { key: 'agentes', label: 'Meus agentes', count: AGENTES.length },
            { key: 'conversas', label: 'Conversas' },
            { key: 'personas', label: 'Personas', count: PERSONAS.length },
            { key: 'bases', label: 'Bases de conhecimento', count: BASES.length },
          ]}
        >
          {(active) =>
            active === 'metricas' ? <Metricas />
            : active === 'agentes' ? <MeusAgentes />
            : active === 'personas' ? <Personas />
            : active === 'bases' ? <Bases />
            : (
              <Card><CardBody className="flex flex-col items-center gap-2 py-16 text-center">
                <MessageSquare size={26} className="text-muted-foreground" />
                <p className="text-sm text-muted-foreground">Histórico de conversas dos agentes aparece aqui.</p>
              </CardBody></Card>
            )
          }
        </Tabs>
      </main>
    </>
  );
}
