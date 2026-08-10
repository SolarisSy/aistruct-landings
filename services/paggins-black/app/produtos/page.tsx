import Link from 'next/link';
import { Topbar } from '@/components/shell';
import { Badge, Card, CardBody, CardHeader, CardTitle, Select, Table, Td, Th } from '@/components/ui';
import { PRODUTOS } from '@/lib/data';
import { brl } from '@/lib/utils';
import { Eye, Pencil, Plus, Search } from 'lucide-react';

const RESUMO = [
  { label: 'Produtos cadastrados', value: String(PRODUTOS.length) },
  { label: 'Ativos', value: String(PRODUTOS.filter((p) => p.status === 'Ativo').length) },
  {
    label: 'Receita do catálogo',
    value: brl(PRODUTOS.reduce((s, p) => s + p.preco * p.vendas, 0)),
  },
  {
    label: 'Reembolso médio',
    value: `${(
      PRODUTOS.filter((p) => p.vendas > 0).reduce((s, p) => s + p.reembolso, 0) /
      PRODUTOS.filter((p) => p.vendas > 0).length
    ).toFixed(1)}%`,
  },
];

export default function ProdutosPage() {
  const ordenados = [...PRODUTOS].sort((a, b) => b.preco * b.vendas - a.preco * a.vendas);

  return (
    <>
      <Topbar crumbs={['Produtos', 'Todos os produtos']} />

      <main className="px-8 pb-14">
        <div className="mb-6 grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
          {RESUMO.map((r) => (
            <Card key={r.label} className="px-5 py-4">
              <p className="text-xs text-muted-foreground">{r.label}</p>
              <p className="mt-1.5 text-[22px] font-bold tracking-tight">{r.value}</p>
            </Card>
          ))}
        </div>

        <div className="mb-5 flex justify-end">
          <Link
            href="/produtos/novo"
            className="inline-flex h-10 items-center gap-2 rounded-[var(--radius-pill)] bg-primary px-5 text-sm font-medium text-white transition-colors hover:bg-primary-hover"
          >
            <Plus size={16} /> Criar novo produto
          </Link>
        </div>

        <Card>
          <CardHeader className="flex flex-wrap items-center justify-between gap-4 pb-5">
            <CardTitle>Meus produtos</CardTitle>
            <div className="flex items-center gap-3">
              <Select defaultValue="todos" className="h-10 w-[140px]">
                <option value="todos">Todos os tipos</option>
                <option>Físico</option>
                <option>Digital</option>
              </Select>
              <div className="relative w-[220px]">
                <Search size={15} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-muted-foreground" />
                <input
                  placeholder="Pesquisar produto"
                  className="h-10 w-full rounded-[var(--radius-pill)] border border-border bg-input pl-10 pr-4 text-sm outline-none transition-colors focus:border-primary/50"
                />
              </div>
            </div>
          </CardHeader>

          <CardBody className="px-2">
            <Table>
              <thead>
                <tr>
                  <Th className="pl-4">Produto</Th>
                  <Th>Tags</Th>
                  <Th className="text-right">Preço</Th>
                  <Th className="text-right">Vendas</Th>
                  <Th className="text-right">Receita</Th>
                  <Th className="text-right">Conv.</Th>
                  <Th className="text-right">Reemb.</Th>
                  <Th>Status</Th>
                  <Th>Tipo</Th>
                  <Th className="w-20 pr-6 text-right">Ações</Th>
                </tr>
              </thead>
              <tbody>
                {ordenados.map((p) => (
                  <tr key={p.id} className="transition-colors hover:bg-elevated/40">
                    <Td className="pl-4">
                      <div className="flex items-center gap-3">
                        <span className={`h-9 w-9 shrink-0 rounded-lg bg-gradient-to-br ${p.cor}`} />
                        <span className="font-medium text-foreground">{p.nome}</span>
                      </div>
                    </Td>
                    <Td>
                      {p.tags.length === 0 ? (
                        <span className="text-muted-foreground">-</span>
                      ) : (
                        <div className="flex flex-wrap gap-1.5">
                          {p.tags.map((t, i) => (
                            <Badge key={i} tone={t === 'Best seller' || t === 'High ticket' ? 'primary' : 'neutral'}>
                              {t}
                            </Badge>
                          ))}
                        </div>
                      )}
                    </Td>
                    <Td className="text-right text-foreground/90">{brl(p.preco)}</Td>
                    <Td className="text-right">{p.vendas.toLocaleString('pt-BR')}</Td>
                    <Td className="text-right font-medium text-foreground">{brl(p.preco * p.vendas)}</Td>
                    <Td className="text-right">{p.conversao.toFixed(1)}%</Td>
                    <Td className={`text-right ${p.reembolso >= 5 ? 'text-warning' : ''}`}>
                      {p.reembolso.toFixed(1)}%
                    </Td>
                    <Td>
                      <Badge
                        tone={p.status === 'Ativo' ? 'success' : p.status === 'Rascunho' ? 'warning' : 'neutral'}
                      >
                        {p.status}
                      </Badge>
                    </Td>
                    <Td>{p.tipo}</Td>
                    <Td className="pr-6">
                      <div className="flex items-center justify-end gap-3">
                        <button className="text-muted-foreground transition-colors hover:text-foreground" aria-label="Visualizar">
                          <Eye size={17} />
                        </button>
                        <button className="text-primary transition-colors hover:text-primary-hover" aria-label="Editar">
                          <Pencil size={16} />
                        </button>
                      </div>
                    </Td>
                  </tr>
                ))}
              </tbody>
            </Table>

            {/* catálogo inteiro cabe numa página — sem paginação falsa */}
            <div className="pt-6 pb-2 pl-4 text-xs text-muted-foreground">
              Exibindo {ordenados.length} produtos ·{' '}
              {ordenados.reduce((s, p) => s + p.vendas, 0).toLocaleString('pt-BR')} vendas acumuladas
            </div>
          </CardBody>
        </Card>
      </main>
    </>
  );
}
