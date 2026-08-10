import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody, CardHeader, CardTitle, Table, Td, Th } from '@/components/ui';
import { KpiRow } from '@/components/kpi';
import { ORDERBUMPS } from '@/lib/data';
import { brl } from '@/lib/utils';
import { Plus } from 'lucide-react';

export default function OrderBumpPage() {
  const ativos = ORDERBUMPS.filter((o) => o.ativo).length;
  const media = ORDERBUMPS.reduce((s, o) => s + o.conversao, 0) / ORDERBUMPS.length;
  return (
    <>
      <Topbar crumbs={['Produtos', 'Order Bumps']} />
      <main className="px-8 pb-14">
        <KpiRow items={[
          { label: 'Order bumps ativos', value: String(ativos) },
          { label: 'Conversão média', value: `${media.toFixed(1)}%` },
          { label: 'Cadastrados', value: String(ORDERBUMPS.length) },
          { label: 'Melhor conversão', value: `${Math.max(...ORDERBUMPS.map((o) => o.conversao))}%` },
        ]} />
        <div className="mb-5 flex justify-end"><Button><Plus size={16} /> Criar order bump</Button></div>
        <Card>
          <CardHeader><CardTitle>Order Bumps</CardTitle></CardHeader>
          <CardBody className="px-2">
            <Table>
              <thead><tr>
                <Th className="pl-4">Oferta</Th><Th>Produto principal</Th>
                <Th className="text-right">Preço</Th><Th className="text-right">Conversão</Th><Th>Status</Th>
              </tr></thead>
              <tbody>
                {ORDERBUMPS.map((o) => (
                  <tr key={o.nome} className="transition-colors hover:bg-elevated/40">
                    <Td className="pl-4 font-medium text-foreground">{o.nome}</Td>
                    <Td>{o.produto}</Td>
                    <Td className="text-right">{brl(o.preco)}</Td>
                    <Td className="text-right font-medium text-foreground">{o.conversao}%</Td>
                    <Td><Badge tone={o.ativo ? 'success' : 'neutral'}>{o.ativo ? 'Ativo' : 'Inativo'}</Badge></Td>
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
