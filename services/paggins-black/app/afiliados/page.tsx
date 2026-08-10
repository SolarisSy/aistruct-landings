import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody, CardHeader, CardTitle, Table, Td, Th } from '@/components/ui';
import { KpiRow } from '@/components/kpi';
import { AFILIADOS_LISTA } from '@/lib/data';
import { brl } from '@/lib/utils';
import { Plus } from 'lucide-react';

export default function AfiliadosPage() {
  const ativos = AFILIADOS_LISTA.filter((a) => a.status === 'Ativo').length;
  const vendas = AFILIADOS_LISTA.reduce((s, a) => s + a.vendas, 0);
  const comissao = AFILIADOS_LISTA.reduce((s, a) => s + a.comissao, 0);
  return (
    <>
      <Topbar crumbs={['Afiliados']} />
      <main className="px-8 pb-14">
        <KpiRow items={[
          { label: 'Afiliados ativos', value: String(ativos) },
          { label: 'Vendas por afiliados', value: vendas.toLocaleString('pt-BR') },
          { label: 'Comissões pagas', value: brl(comissao) },
          { label: 'Comissão média', value: `${Math.round(AFILIADOS_LISTA.reduce((s, a) => s + a.taxa, 0) / AFILIADOS_LISTA.length)}%` },
        ]} />
        <div className="mb-5 flex justify-end"><Button><Plus size={16} /> Convidar afiliado</Button></div>
        <Card>
          <CardHeader><CardTitle>Afiliados</CardTitle></CardHeader>
          <CardBody className="px-2">
            <Table>
              <thead><tr>
                <Th className="pl-4">Afiliado</Th><Th className="text-right">Vendas</Th>
                <Th className="text-right">Comissão %</Th><Th className="text-right">Comissão gerada</Th><Th>Status</Th>
              </tr></thead>
              <tbody>
                {AFILIADOS_LISTA.map((a) => (
                  <tr key={a.id} className="transition-colors hover:bg-elevated/40">
                    <Td className="pl-4">
                      <p className="font-medium text-foreground">{a.nome}</p>
                      <p className="text-xs text-muted-foreground/70">{a.email}</p>
                    </Td>
                    <Td className="text-right">{a.vendas}</Td>
                    <Td className="text-right"><Badge tone="primary">{a.taxa}%</Badge></Td>
                    <Td className="text-right font-medium text-foreground">{brl(a.comissao)}</Td>
                    <Td><Badge tone={a.status === 'Ativo' ? 'success' : 'warning'}>{a.status}</Badge></Td>
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
