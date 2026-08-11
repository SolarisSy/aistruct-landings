import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody } from '@/components/ui';
import { IntegrationLogo } from '@/components/integration-logo';
import { INTEGRACOES } from '@/lib/integrations';

export default function ExtensoesPage() {
  const conectadas = INTEGRACOES.filter((a) => a.conectado).length;
  return (
    <>
      <Topbar crumbs={['Extensões', 'Apps e Integrações']} />
      <main className="px-8 pb-14">
        <p className="mb-6 text-sm text-muted-foreground">
          Conecte a Paggins às ferramentas que você já usa. {conectadas} de {INTEGRACOES.length} conectadas.
        </p>
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
          {INTEGRACOES.map((a) => (
            <Card key={a.nome}>
              <CardBody className="flex items-center gap-4 pt-5">
                <IntegrationLogo item={a} />
                <div className="min-w-0 flex-1">
                  <p className="font-medium">{a.nome}</p>
                  <p className="text-xs text-muted-foreground">{a.cat}</p>
                </div>
                {a.conectado ? (
                  <Badge tone="success">Conectado</Badge>
                ) : (
                  <Button variant="outline" size="sm">Conectar</Button>
                )}
              </CardBody>
            </Card>
          ))}
        </div>
      </main>
    </>
  );
}
