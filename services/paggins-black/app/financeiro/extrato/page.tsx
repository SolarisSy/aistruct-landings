import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody, CardHeader, CardTitle, Table, Td, Th } from '@/components/ui';
import { EXTRATO, type Movimento } from '@/lib/data';
import { brl } from '@/lib/utils';
import { Download } from 'lucide-react';

const tone = (t: Movimento['tipo']) =>
  t === 'Entrada' ? 'success' : t === 'Saque' ? 'primary' : t === 'Reembolso' ? 'danger' : 'neutral';

export default function ExtratoPage() {
  return (
    <>
      <Topbar crumbs={['Financeiro', 'Extrato']} />
      <main className="px-8 pb-14">
        <Card>
          <CardHeader className="flex items-center justify-between">
            <CardTitle>Extrato</CardTitle>
            <Button variant="outline"><Download size={15} /> Exportar</Button>
          </CardHeader>
          <CardBody className="px-2">
            <Table>
              <thead><tr>
                <Th className="pl-4">Data</Th><Th>Descrição</Th><Th>Tipo</Th><Th className="pr-6 text-right">Valor</Th>
              </tr></thead>
              <tbody>
                {EXTRATO.map((m, i) => (
                  <tr key={i} className="transition-colors hover:bg-elevated/40">
                    <Td className="pl-4">{m.data}</Td>
                    <Td className="text-foreground/90">{m.descricao}</Td>
                    <Td><Badge tone={tone(m.tipo)}>{m.tipo}</Badge></Td>
                    <Td className={`pr-6 text-right font-medium ${m.valor >= 0 ? 'text-success' : 'text-foreground'}`}>
                      {m.valor >= 0 ? '+' : ''}{brl(m.valor)}
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
