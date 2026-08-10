import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody, CardHeader, CardTitle, Table, Td, Th } from '@/components/ui';
import { APIKEYS } from '@/lib/data';
import { Plus, Copy } from 'lucide-react';

export default function ApiKeysPage() {
  return (
    <>
      <Topbar crumbs={['Extensões', 'API Keys']} />
      <main className="px-8 pb-14">
        <div className="mb-5 flex justify-end"><Button><Plus size={16} /> Gerar API Key</Button></div>
        <Card>
          <CardHeader><CardTitle>API Keys</CardTitle></CardHeader>
          <CardBody className="px-2">
            <Table>
              <thead><tr>
                <Th className="pl-4">Nome</Th><Th>Chave</Th><Th>Criada</Th><Th>Último uso</Th><Th className="w-16 pr-6"></Th>
              </tr></thead>
              <tbody>
                {APIKEYS.map((k) => (
                  <tr key={k.nome} className="transition-colors hover:bg-elevated/40">
                    <Td className="pl-4 font-medium text-foreground">
                      {k.nome}
                      {k.prefixo.includes('test') && <Badge tone="warning" className="ml-2">sandbox</Badge>}
                    </Td>
                    <Td className="font-mono text-xs">{k.prefixo}</Td>
                    <Td>{k.criada}</Td>
                    <Td className="text-muted-foreground">{k.ultimoUso}</Td>
                    <Td className="pr-6">
                      <button className="text-muted-foreground hover:text-foreground" aria-label="Copiar"><Copy size={15} /></button>
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
