import Link from 'next/link';
import { Topbar } from '@/components/shell';
import { Button, Card, CardBody, Input, Label, Select, Textarea } from '@/components/ui';
import { ChevronLeft, Upload } from 'lucide-react';

export default function NovoProdutoPage() {
  return (
    <>
      <Topbar crumbs={['Todos os produtos', 'Criar novo produto']} />

      <main className="px-8 pb-14">
        <Link
          href="/produtos"
          className="mb-8 inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
        >
          <ChevronLeft size={16} /> Voltar
        </Link>

        <div className="mx-auto max-w-[640px]">
          <div className="mb-7 text-center">
            <span className="inline-block rounded-md bg-elevated px-2.5 py-1 text-xs text-muted-foreground">
              2/8
            </span>
            <h1 className="mt-4 text-[28px] font-bold leading-tight tracking-tight">
              Conte-nos mais sobre o seu
              <br />
              <span className="text-primary">produto físico</span>
            </h1>
          </div>

          <Card>
            <CardBody className="space-y-5 pt-6">
              <div>
                <Label htmlFor="nome">Nome do produto</Label>
                <Input id="nome" placeholder="Insira o nome do seu produto" />
              </div>

              <div>
                <Label htmlFor="desc">Descrição</Label>
                <Textarea id="desc" placeholder="Insira a descrição do seu produto" />
              </div>

              <div>
                <Label htmlFor="cat">Categoria</Label>
                <Select id="cat" defaultValue="">
                  <option value="" disabled>Selecione</option>
                  <option>Casa e cozinha</option>
                  <option>Eletrônicos</option>
                  <option>Suplementos</option>
                </Select>
              </div>

              <div>
                <Label htmlFor="idioma">Idioma</Label>
                <Select id="idioma" defaultValue="">
                  <option value="" disabled>Selecione</option>
                  <option>Português (BR)</option>
                  <option>English (US)</option>
                  <option>Español</option>
                </Select>
              </div>

              <div>
                <Label>Imagens</Label>
                <div className="flex flex-col items-center justify-center gap-2 rounded-[var(--radius-field)] border border-dashed border-border-strong bg-input/50 px-6 py-9 text-center">
                  <Upload size={22} className="text-muted-foreground" />
                  <p className="text-sm text-muted-foreground">
                    Arraste e solte aqui ou{' '}
                    <button className="text-primary underline-offset-2 hover:underline">
                      Clique para carregar
                    </button>
                  </p>
                  <p className="text-xs text-muted-foreground/70">
                    Arquivos suportados: .png, e .jpg
                  </p>
                </div>
              </div>
            </CardBody>
          </Card>

          <div className="mt-7 flex items-center justify-end gap-7">
            <Button variant="link">Voltar</Button>
            <Button size="lg" className="min-w-[180px]">Avançar</Button>
          </div>
        </div>
      </main>
    </>
  );
}
