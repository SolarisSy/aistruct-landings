import { Topbar } from '@/components/shell';
import { Card, CardBody, CardHeader, CardTitle, Table, Td, Th } from '@/components/ui';
import { KpiRow } from '@/components/kpi';
import { CLIENTES } from '@/lib/data';
import { brl } from '@/lib/utils';
import { Search } from 'lucide-react';

export default function ClientesPage() {
  const total = CLIENTES.length;
  const receita = CLIENTES.reduce((s, c) => s + c.gasto, 0);
  const ltv = total ? receita / total : 0;
  const recorrentes = CLIENTES.filter((c) => c.pedidos > 1).length;
  return (
    <>
      <Topbar crumbs={['Vendas', 'Clientes']} />
      <main className="px-8 pb-14">
        <KpiRow items={[
          { label: 'Clientes', value: total.toLocaleString('pt-BR') },
          { label: 'Receita total', value: brl(receita) },
          { label: 'LTV médio', value: brl(ltv) },
          { label: 'Recorrentes', value: `${recorrentes} (${Math.round((recorrentes / total) * 100)}%)` },
        ]} />
        <Card>
          <CardHeader className="flex items-center justify-between">
            <CardTitle>Clientes</CardTitle>
            <div className="relative w-[220px]">
              <Search size={15} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
              <input placeholder="Nome ou e-mail" className="h-10 w-full rounded-[var(--radius-pill)] border border-border bg-input pl-10 pr-4 text-sm outline-none focus:border-primary/50" />
            </div>
          </CardHeader>
          <CardBody className="px-2">
            <Table>
              <thead><tr>
                <Th className="pl-4">Cliente</Th><Th>Telefone</Th><Th>UF</Th>
                <Th className="text-right">Pedidos</Th><Th className="text-right">Total gasto</Th><Th>Cliente desde</Th>
              </tr></thead>
              <tbody>
                {CLIENTES.slice(0, 30).map((c) => (
                  <tr key={c.id} className="transition-colors hover:bg-elevated/40">
                    <Td className="pl-4">
                      <p className="font-medium text-foreground">{c.nome}</p>
                      <p className="text-xs text-muted-foreground/70">{c.email}</p>
                    </Td>
                    <Td>{c.telefone}</Td>
                    <Td>{c.uf}</Td>
                    <Td className="text-right">{c.pedidos}</Td>
                    <Td className="text-right font-medium text-foreground">{brl(c.gasto)}</Td>
                    <Td>{c.desde}</Td>
                  </tr>
                ))}
              </tbody>
            </Table>
            <div className="pt-6 pb-2 pl-4 text-xs text-muted-foreground">Exibindo 30 de {total} clientes</div>
          </CardBody>
        </Card>
      </main>
    </>
  );
}
