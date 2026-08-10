import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody } from '@/components/ui';
import { APPS } from '@/lib/data';

export default function ExtensoesPage() {
  return (
    <>
      <Topbar crumbs={['Extensões', 'Apps e Integrações']} />
      <main className="px-8 pb-14">
        <p className="mb-6 text-sm text-muted-foreground">
          Conecte a Paggins às ferramentas que você já usa. {APPS.filter((a) => a.conectado).length} de {APPS.length} conectadas.
        </p>
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
          {APPS.map((a) => (
            <Card key={a.nome}>
              <CardBody className="flex items-center gap-4 pt-5">
                <span className="flex h-11 w-11 items-center justify-center rounded-[var(--radius-field)] bg-elevated text-sm font-bold text-primary">
                  {a.nome.slice(0, 2).toUpperCase()}
                </span>
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
