import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody, CardHeader, CardTitle, Table, Td, Th } from '@/components/ui';
import { WEBHOOKS } from '@/lib/data';
import { Plus } from 'lucide-react';

export default function WebhooksPage() {
  return (
    <>
      <Topbar crumbs={['Extensões', 'Webhooks']} />
      <main className="px-8 pb-14">
        <div className="mb-5 flex justify-end"><Button><Plus size={16} /> Novo webhook</Button></div>
        <Card>
          <CardHeader><CardTitle>Webhooks</CardTitle></CardHeader>
          <CardBody className="px-2">
            <Table>
              <thead><tr>
                <Th className="pl-4">URL</Th><Th>Evento</Th><Th className="pr-6">Status</Th>
              </tr></thead>
              <tbody>
                {WEBHOOKS.map((w, i) => (
                  <tr key={i} className="transition-colors hover:bg-elevated/40">
                    <Td className="pl-4 font-mono text-xs text-foreground/90">{w.url}</Td>
                    <Td>{w.evento}</Td>
                    <Td className="pr-6"><Badge tone={w.status === 'Ativo' ? 'success' : 'danger'}>{w.status}</Badge></Td>
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
