import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody, CardHeader, CardTitle, Table, Td, Th } from '@/components/ui';
import { KpiRow } from '@/components/kpi';
import { CUPONS } from '@/lib/data';
import { Plus } from 'lucide-react';

export default function DescontosPage() {
  const ativos = CUPONS.filter((c) => c.status === 'Ativo').length;
  const usos = CUPONS.reduce((s, c) => s + c.usos, 0);
  return (
    <>
      <Topbar crumbs={['Produtos', 'Descontos']} />
      <main className="px-8 pb-14">
        <KpiRow items={[
          { label: 'Cupons ativos', value: String(ativos) },
          { label: 'Usos totais', value: usos.toLocaleString('pt-BR') },
          { label: 'Cupons cadastrados', value: String(CUPONS.length) },
          { label: 'Maior desconto', value: '40%' },
        ]} />
        <div className="mb-5 flex justify-end">
          <Button><Plus size={16} /> Criar cupom</Button>
        </div>
        <Card>
          <CardHeader><CardTitle>Descontos</CardTitle></CardHeader>
          <CardBody className="px-2">
            <Table>
              <thead><tr>
                <Th className="pl-4">Código</Th><Th>Tipo</Th><Th className="text-right">Valor</Th>
                <Th className="text-right">Usos</Th><Th className="text-right">Limite</Th><Th>Status</Th>
              </tr></thead>
              <tbody>
                {CUPONS.map((c) => (
                  <tr key={c.codigo} className="transition-colors hover:bg-elevated/40">
                    <Td className="pl-4 font-mono font-medium text-foreground">{c.codigo}</Td>
                    <Td>{c.tipo}</Td>
                    <Td className="text-right font-medium text-foreground">{c.tipo === 'Percentual' ? `${c.valor}%` : `R$ ${c.valor}`}</Td>
                    <Td className="text-right">{c.usos.toLocaleString('pt-BR')}</Td>
                    <Td className="text-right">{c.limite === 0 ? 'Ilimitado' : c.limite}</Td>
                    <Td><Badge tone={c.status === 'Ativo' ? 'success' : 'neutral'}>{c.status}</Badge></Td>
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
