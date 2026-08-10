'use client';

import {
  Area, AreaChart, Bar, BarChart, CartesianGrid, Cell, ResponsiveContainer, Tooltip, XAxis, YAxis,
} from 'recharts';
import { brl } from '@/lib/utils';

/* ---------------- vendas por hora (área) ---------------- */

function AreaTip({ active, label, payload }: any) {
  if (!active || !payload?.length) return null;
  const v = payload[0].value as number;
  return (
    <div className="min-w-[180px] rounded-[var(--radius-field)] border border-border-strong bg-elevated px-3.5 py-3 text-xs shadow-xl">
      <div className="mb-2 flex items-center justify-between gap-6">
        <span className="font-semibold text-foreground">{label}</span>
        <span className="font-semibold text-foreground">{brl(v)}</span>
      </div>
      <div className="space-y-1 text-muted-foreground">
        <div className="flex justify-between gap-6"><span>Loja online</span><span>{brl(v)}</span></div>
        <div className="flex justify-between gap-6"><span>Outros</span><span>{brl(0)}</span></div>
      </div>
    </div>
  );
}

export function SalesChart({ data }: { data: { h: string; v: number }[] }) {
  const max = Math.max(...data.map((d) => d.v), 100);
  const step = Math.ceil(max / 4 / 500) * 500 || 500;
  const ticks = [0, step, step * 2, step * 3, step * 4];

  return (
    <div className="h-[300px] w-full">
      <ResponsiveContainer width="100%" height="100%">
        <AreaChart data={data} margin={{ top: 8, right: 8, left: 4, bottom: 0 }}>
          <defs>
            <linearGradient id="fillSales" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stopColor="var(--chart-1)" stopOpacity={0.42} />
              <stop offset="100%" stopColor="var(--chart-1)" stopOpacity={0.02} />
            </linearGradient>
          </defs>
          <CartesianGrid stroke="var(--chart-grid)" strokeDasharray="4 4" vertical={false} />
          <XAxis
            dataKey="h" tickLine={false} axisLine={false} interval={0}
            tick={{ fill: 'var(--muted-foreground)', fontSize: 10 }}
          />
          <YAxis
            tickLine={false} axisLine={false} width={72} ticks={ticks} domain={[0, ticks[4]]}
            tickFormatter={(v) => `R$ ${Number(v).toLocaleString('pt-BR')}`}
            tick={{ fill: 'var(--muted-foreground)', fontSize: 10 }}
          />
          <Tooltip content={<AreaTip />} cursor={{ stroke: 'var(--border-strong)', strokeWidth: 1 }} />
          <Area
            type="monotone" dataKey="v" stroke="var(--chart-1)" strokeWidth={2}
            fill="url(#fillSales)"
            activeDot={{ r: 4, fill: 'var(--chart-1)', stroke: '#000', strokeWidth: 2 }}
          />
        </AreaChart>
      </ResponsiveContainer>
    </div>
  );
}

/* ---------------- receita por método (barras) ---------------- */

const CORES = ['var(--chart-1)', 'var(--chart-2)', '#8b8b96'];

export function MetodoChart({ data }: { data: { metodo: string; receita: number }[] }) {
  return (
    <div className="h-[190px] w-full">
      <ResponsiveContainer width="100%" height="100%">
        <BarChart data={data} margin={{ top: 8, right: 8, left: -14, bottom: 0 }}>
          <CartesianGrid stroke="var(--chart-grid)" strokeDasharray="4 4" vertical={false} />
          <XAxis dataKey="metodo" tickLine={false} axisLine={false} tick={{ fill: 'var(--muted-foreground)', fontSize: 11 }} />
          <YAxis
            tickLine={false} axisLine={false} width={64}
            tickFormatter={(v) => `${Math.round(Number(v) / 1000)}k`}
            tick={{ fill: 'var(--muted-foreground)', fontSize: 10 }}
          />
          <Tooltip
            cursor={{ fill: 'rgba(255,255,255,0.03)' }}
            contentStyle={{
              background: 'var(--elevated)', border: '1px solid var(--border-strong)',
              borderRadius: 8, fontSize: 12,
            }}
            labelStyle={{ color: 'var(--foreground)' }}
            formatter={(v: any) => [brl(Number(v)), 'Receita']}
          />
          <Bar dataKey="receita" radius={[6, 6, 0, 0]} maxBarSize={54}>
            {data.map((_, i) => (
              <Cell key={i} fill={CORES[i % CORES.length]} />
            ))}
          </Bar>
        </BarChart>
      </ResponsiveContainer>
    </div>
  );
}
