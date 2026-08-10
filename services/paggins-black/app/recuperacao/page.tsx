import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody, CardHeader, CardTitle, Table, Td, Th } from '@/components/ui';
import { KpiRow } from '@/components/kpi';
import { ABANDONADOS } from '@/lib/data';
import { brl } from '@/lib/utils';
import { Send } from 'lucide-react';

export default function RecuperacaoPage() {
  const total = ABANDONADOS.length;
  const recuperados = ABANDONADOS.filter((a) => a.recuperado).length;
  const potencial = ABANDONADOS.filter((a) => !a.recuperado).reduce((s, a) => s + a.valor, 0);
  return (
    <>
      <Topbar crumbs={['Vendas', 'Recuperação de Vendas']} />
      <main className="px-8 pb-14">
        <KpiRow items={[
          { label: 'Carrinhos abandonados', value: String(total) },
          { label: 'Recuperados', value: `${recuperados} (${Math.round((recuperados / total) * 100)}%)` },
          { label: 'Receita a recuperar', value: brl(potencial) },
          { label: 'Agente de recuperação', value: 'Ativo', hint: 'IA aborda automaticamente' },
        ]} />
        <Card>
          <CardHeader><CardTitle>Carrinhos abandonados</CardTitle></CardHeader>
          <CardBody className="px-2">
            <Table>
              <thead><tr>
                <Th className="pl-4">Cliente</Th><Th>Produto</Th><Th className="text-right">Valor</Th>
                <Th>Parou em</Th><Th>Quando</Th><Th>Status</Th><Th className="w-28 pr-6 text-right">Ação</Th>
              </tr></thead>
              <tbody>
                {ABANDONADOS.map((a) => (
                  <tr key={a.id} className="transition-colors hover:bg-elevated/40">
                    <Td className="pl-4">
                      <p className="text-foreground/90">{a.cliente}</p>
                      <p className="text-xs text-muted-foreground/70">{a.email}</p>
                    </Td>
                    <Td className="max-w-[190px] truncate">{a.produto}</Td>
                    <Td className="text-right font-medium text-foreground">{brl(a.valor)}</Td>
                    <Td><Badge tone={a.etapa === 'Pagamento' ? 'warning' : 'neutral'}>{a.etapa}</Badge></Td>
                    <Td>{a.quando}</Td>
                    <Td><Badge tone={a.recuperado ? 'success' : 'danger'}>{a.recuperado ? 'Recuperado' : 'Aberto'}</Badge></Td>
                    <Td className="pr-6 text-right">
                      {!a.recuperado && (
                        <Button variant="outline" size="sm"><Send size={13} /> Recuperar</Button>
                      )}
                    </Td>
                  </tr>
                ))}
              </tbody>
            </Table>
          </CardBody>
        </Card>
      </main>
    </>
  );
}
