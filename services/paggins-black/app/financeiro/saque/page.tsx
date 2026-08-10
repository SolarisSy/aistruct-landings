import { Topbar } from '@/components/shell';
import { Badge, Button, Card, CardBody, CardHeader, CardTitle, Input, Label, Select } from '@/components/ui';
import { METRICAS } from '@/lib/data';
import { brl } from '@/lib/utils';
import { Landmark } from 'lucide-react';

export default function SaquePage() {
  return (
    <>
      <Topbar crumbs={['Financeiro', 'Configurações de Saque']} />
      <main className="px-8 pb-14">
        <div className="mx-auto grid max-w-4xl grid-cols-1 gap-6 lg:grid-cols-2">
          <Card>
            <CardHeader><CardTitle>Conta bancária</CardTitle></CardHeader>
            <CardBody className="space-y-4">
              <div className="flex items-center gap-3 rounded-[var(--radius-field)] border border-border bg-elevated px-4 py-3">
                <Landmark size={18} className="text-primary" />
                <div className="flex-1">
                  <p className="text-sm font-medium">Banco Inter · Ag 0001</p>
                  <p className="text-xs text-muted-foreground">Conta ****1234 · CNPJ 45.***.***/0001-**</p>
                </div>
                <Badge tone="success">Verificada</Badge>
              </div>
              <Button variant="outline" className="w-full">Alterar conta</Button>
            </CardBody>
          </Card>

          <Card>
            <CardHeader><CardTitle>Saque automático</CardTitle></CardHeader>
            <CardBody className="space-y-4">
              <div>
                <Label htmlFor="freq">Frequência</Label>
                <Select id="freq" defaultValue="diario">
                  <option value="diario">Diário</option>
                  <option value="semanal">Semanal</option>
                  <option value="manual">Manual</option>
                </Select>
              </div>
              <div>
                <Label htmlFor="min">Valor mínimo para saque</Label>
                <Input id="min" defaultValue="R$ 100,00" />
              </div>
              <div className="rounded-[var(--radius-field)] bg-elevated px-4 py-3 text-sm">
                <span className="text-muted-foreground">Disponível agora: </span>
                <span className="font-semibold">{brl(METRICAS.disponivelSaque)}</span>
              </div>
              <Button className="w-full">Salvar configurações</Button>
            </CardBody>
          </Card>
        </div>
      </main>
    </>
  );
}
