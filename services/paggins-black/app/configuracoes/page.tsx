import { Topbar } from '@/components/shell';
import { Button, Card, CardBody, CardHeader, CardTitle, Input, Label, Switch } from '@/components/ui';

export default function ConfiguracoesPage() {
  return (
    <>
      <Topbar crumbs={['Configurações']} />
      <main className="px-8 pb-14">
        <div className="mx-auto grid max-w-4xl grid-cols-1 gap-6">
          <Card>
            <CardHeader><CardTitle>Dados da loja</CardTitle></CardHeader>
            <CardBody className="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div><Label htmlFor="loja">Nome da loja</Label><Input id="loja" defaultValue="Minha loja 1" /></div>
              <div><Label htmlFor="cnpj">CNPJ</Label><Input id="cnpj" defaultValue="45.***.***/0001-**" /></div>
              <div><Label htmlFor="email">E-mail de contato</Label><Input id="email" defaultValue="contato@minhaloja.com" /></div>
              <div><Label htmlFor="tel">Telefone</Label><Input id="tel" defaultValue="(11) 90000-0000" /></div>
            </CardBody>
          </Card>

          <Card>
            <CardHeader><CardTitle>Preferências</CardTitle></CardHeader>
            <CardBody className="space-y-4">
              {[
                { t: 'Notificar cada nova venda por e-mail', on: true },
                { t: 'Ativar agentes de IA por padrão em novos produtos', on: true },
                { t: 'Exigir 2FA no login', on: true },
                { t: 'Modo de teste (sandbox)', on: false },
              ].map((p) => (
                <div key={p.t} className="flex items-center justify-between border-b border-border/60 pb-4 last:border-0 last:pb-0">
                  <span className="text-sm">{p.t}</span>
                  <Switch checked={p.on} />
                </div>
              ))}
            </CardBody>
          </Card>

          <div className="flex justify-end"><Button>Salvar alterações</Button></div>
        </div>
      </main>
    </>
  );
}
