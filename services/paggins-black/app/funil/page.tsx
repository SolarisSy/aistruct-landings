import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody, Input, Label, Select, Switch } from '@/components/ui';
import { ChevronLeft, Eye, Info, Plus, ShoppingCart, TrendingUp, PartyPopper } from 'lucide-react';

const ETAPAS = [
  { label: 'Gatilho de entrada', icon: ShoppingCart, cor: 'bg-primary' },
  { label: 'Upsell', icon: TrendingUp, cor: 'bg-success', add: true },
  { label: 'Obrigado', icon: PartyPopper, cor: 'bg-violet' },
];

export default function FunilPage() {
  return (
    <>
      <Topbar crumbs={['Checkout', 'Configurar Funis de vendas']} />

      <main className="px-8 pb-14">
        <button className="mb-6 inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
          <ChevronLeft size={16} /> Voltar
        </button>

        <Card className="mb-5 flex items-center justify-between px-6 py-4">
          <p className="text-sm text-muted-foreground">Gerencie suas métricas de funil.</p>
          <Button>Métricas de funil</Button>
        </Card>

        <Card className="mb-5">
          <CardBody className="pt-5">
            <h2 className="mb-5 text-sm font-semibold">Configurações gerais do funil</h2>

            <div className="grid grid-cols-1 gap-5 lg:grid-cols-[1fr_auto_auto_auto] lg:items-end">
              <div>
                <Label htmlFor="fname">Nome do funil</Label>
                <Input id="fname" placeholder="Insira um nome para o funil de vendas" />
              </div>

              <div>
                <Label>Contexto</Label>
                <div className="flex h-11 items-center gap-2">
                  <span className="rounded-[var(--radius-field)] border border-primary/40 bg-primary/10 px-3 py-2 text-sm text-primary">
                    Funil Externo
                  </span>
                  <Info size={15} className="text-muted-foreground" />
                </div>
              </div>

              <div>
                <Label>Status</Label>
                <div className="flex h-11 items-center gap-2.5">
                  <Switch checked />
                  <span className="text-sm text-muted-foreground">Ativo</span>
                </div>
              </div>

              <Button variant="outline" className="h-11">
                <Eye size={15} /> Visualizar links
              </Button>
            </div>

            <div className="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
              <div>
                <Label htmlFor="prods">Produtos aceitos</Label>
                <Select id="prods" defaultValue="">
                  <option value="" disabled>Selecione</option>
                  <option>Cafeteira Expressa</option>
                  <option>PowerMax Laptop</option>
                </Select>
              </div>
              <div>
                <Label htmlFor="tags">Tags</Label>
                <Input id="tags" placeholder="Digite e pressione Enter" />
              </div>
            </div>
          </CardBody>
        </Card>

        <div className="grid grid-cols-1 gap-5 lg:grid-cols-2">
          <Card>
            <CardBody className="pt-5">
              <h2 className="text-sm font-semibold">Fluxo</h2>
              <p className="mt-1 mb-5 text-xs text-muted-foreground">
                Selecione uma etapa abaixo para configurar
              </p>

              <div className="relative space-y-3 pl-6">
                <span className="absolute left-[7px] top-4 bottom-4 w-px bg-border-strong" />
                {ETAPAS.map((e) => (
                  <div key={e.label} className="relative">
                    <span className="absolute -left-6 top-1/2 h-2 w-2 -translate-y-1/2 rounded-full bg-border-strong" />
                    <button className="flex w-full items-center gap-3 rounded-[var(--radius-field)] border border-border bg-elevated/60 px-4 py-3.5 text-left transition-colors hover:border-border-strong hover:bg-elevated">
                      <span className={`flex h-7 w-7 items-center justify-center rounded-full ${e.cor} text-white`}>
                        <e.icon size={14} />
                      </span>
                      <span className="flex-1 text-sm">{e.label}</span>
                      {e.add && (
                        <span className="inline-flex items-center gap-1 text-xs text-muted-foreground">
                          <Plus size={13} /> Adicionar
                        </span>
                      )}
                    </button>
                  </div>
                ))}
              </div>
            </CardBody>
          </Card>

          <Card className="flex min-h-[300px] items-center justify-center">
            <p className="text-sm text-muted-foreground">Selecione uma etapa</p>
          </Card>
        </div>

        <div className="mt-6 flex justify-end">
          <Button size="lg" className="min-w-[180px]">Salvar</Button>
        </div>
      </main>
    </>
  );
}
