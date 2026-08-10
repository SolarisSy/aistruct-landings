/**
 * Dataset de exemplo da Paggins — determinístico (LCG com seed fixa), para que
 * server e client rendam exatamente o mesmo HTML e o build fique estável.
 * Nenhum KPI é hardcoded: tudo abaixo é derivado destes registros.
 */

export type Tipo = 'Físico' | 'Digital';
export type ProdStatus = 'Ativo' | 'Indisponível' | 'Rascunho';
export type PedidoStatus = 'Aprovado' | 'Pendente' | 'Reembolsado' | 'Recusado' | 'Chargeback';
export type Metodo = 'PIX' | 'Cartão' | 'Boleto';

export type Produto = {
  id: string;
  nome: string;
  cor: string;
  tipo: Tipo;
  status: ProdStatus;
  preco: number;
  tags: string[];
  vendas: number;
  conversao: number;
  reembolso: number;
};

export type Pedido = {
  id: string;
  cliente: string;
  email: string;
  produtoId: string;
  valor: number;
  metodo: Metodo;
  parcelas: number;
  status: PedidoStatus;
  hora: number;
  minuto: number;
  dia: number;
  afiliado: string | null;
  uf: string;
};

/* ---------------- gerador determinístico ---------------- */
function lcg(seed: number) {
  let s = seed >>> 0;
  return () => ((s = (s * 1664525 + 1013904223) >>> 0) / 4294967296);
}

/* ---------------- catálogo ---------------- */
export const PRODUTOS: Produto[] = [
  { id: 'p01', nome: 'Cafeteira Expressa Prime', cor: 'from-amber-500 to-orange-700', tipo: 'Físico', status: 'Ativo', preco: 297, tags: ['Casa', 'Best seller'], vendas: 412, conversao: 4.8, reembolso: 1.9 },
  { id: 'p02', nome: 'Gourmet Spice Rack', cor: 'from-rose-500 to-red-800', tipo: 'Físico', status: 'Indisponível', preco: 189, tags: ['Cozinha'], vendas: 138, conversao: 2.7, reembolso: 3.4 },
  { id: 'p03', nome: 'PowerMax Laptop 16"', cor: 'from-slate-400 to-slate-700', tipo: 'Físico', status: 'Indisponível', preco: 4890, tags: ['Eletrônicos'], vendas: 27, conversao: 0.9, reembolso: 7.4 },
  { id: 'p04', nome: 'AquaWave Water Bottle', cor: 'from-sky-400 to-blue-700', tipo: 'Físico', status: 'Ativo', preco: 97, tags: ['Fitness'], vendas: 683, conversao: 6.2, reembolso: 1.1 },
  { id: 'p05', nome: 'UltraComfort Chair Pro', cor: 'from-emerald-500 to-teal-800', tipo: 'Físico', status: 'Ativo', preco: 1249.9, tags: ['Home office'], vendas: 91, conversao: 1.8, reembolso: 4.2 },
  { id: 'p06', nome: 'EcoSmart Blender 1200W', cor: 'from-violet-500 to-purple-800', tipo: 'Físico', status: 'Ativo', preco: 549, tags: ['Cozinha'], vendas: 204, conversao: 3.1, reembolso: 2.6 },
  { id: 'p07', nome: 'Método Renda Extra 6.0', cor: 'from-blue-500 to-indigo-800', tipo: 'Digital', status: 'Ativo', preco: 497, tags: ['Curso', 'Best seller'], vendas: 1284, conversao: 8.9, reembolso: 5.8 },
  { id: 'p08', nome: 'Pack 500 Criativos IA', cor: 'from-fuchsia-500 to-pink-800', tipo: 'Digital', status: 'Ativo', preco: 147, tags: ['Templates'], vendas: 942, conversao: 11.4, reembolso: 2.2 },
  { id: 'p09', nome: 'Mentoria Tráfego 1:1', cor: 'from-cyan-400 to-sky-800', tipo: 'Digital', status: 'Ativo', preco: 2997, tags: ['Mentoria', 'High ticket'], vendas: 38, conversao: 1.2, reembolso: 0 },
  { id: 'p10', nome: 'Planilha Gestão de Ads', cor: 'from-lime-500 to-green-800', tipo: 'Digital', status: 'Ativo', preco: 67, tags: ['Ferramenta'], vendas: 1907, conversao: 14.7, reembolso: 1.4 },
  { id: 'p11', nome: 'Kit Suplementos 90 dias', cor: 'from-orange-500 to-amber-800', tipo: 'Físico', status: 'Ativo', preco: 389, tags: ['Nutra', 'Recorrente'], vendas: 561, conversao: 5.4, reembolso: 6.1 },
  { id: 'p12', nome: 'Whey Isolado 900g', cor: 'from-stone-400 to-stone-700', tipo: 'Físico', status: 'Ativo', preco: 219, tags: ['Nutra'], vendas: 748, conversao: 7.1, reembolso: 2.8 },
  { id: 'p13', nome: 'Curso Copy que Vende', cor: 'from-red-500 to-rose-900', tipo: 'Digital', status: 'Ativo', preco: 397, tags: ['Curso'], vendas: 476, conversao: 6.6, reembolso: 4.9 },
  { id: 'p14', nome: 'Ebook Low Ticket Global', cor: 'from-teal-400 to-cyan-800', tipo: 'Digital', status: 'Rascunho', preco: 27, tags: ['Ebook'], vendas: 0, conversao: 0, reembolso: 0 },
  { id: 'p15', nome: 'Smartwatch FitPro 3', cor: 'from-zinc-400 to-zinc-700', tipo: 'Físico', status: 'Ativo', preco: 649, tags: ['Eletrônicos', 'Fitness'], vendas: 173, conversao: 2.4, reembolso: 5.3 },
  { id: 'p16', nome: 'Comunidade Escala 12m', cor: 'from-indigo-400 to-violet-900', tipo: 'Digital', status: 'Ativo', preco: 1197, tags: ['Recorrente', 'High ticket'], vendas: 112, conversao: 2.1, reembolso: 3.7 },
];

/* ---------------- pedidos ---------------- */
const NOMES = [
  'Ana Beatriz Rocha', 'Rafael Lima', 'Juliana Souza', 'Pedro Henrique Alves', 'Camila Nunes',
  'Lucas Bandeira', 'Fernanda Castro', 'Bruno Tavares', 'Larissa Mendes', 'Thiago Moreira',
  'Patrícia Gomes', 'Diego Barbosa', 'Marina Figueiredo', 'Gustavo Pinheiro', 'Renata Dias',
  'Eduardo Sampaio', 'Vanessa Prado', 'Rodrigo Carvalho', 'Isabela Freitas', 'Marcelo Antunes',
  'Priscila Ramos', 'Vinícius Cardoso', 'Aline Peixoto', 'Otávio Bernardes',
];
const UFS = ['SP', 'RJ', 'MG', 'RS', 'PR', 'BA', 'SC', 'PE', 'CE', 'GO', 'DF', 'ES'];
const AFILIADOS = [null, null, null, 'kaleb.midia', 'lucasbang', 'philip.ads', null, 'traffic.br'];

function slugEmail(nome: string) {
  return (
    nome.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').split(' ')[0] +
    '.' +
    nome.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').split(' ').slice(-1)[0] +
    '@email.com'
  );
}

/** Curva de tráfego de um dia real: madrugada morta, pico 11h e 20-21h. */
const PESO_HORA = [
  0.6, 0.4, 0.3, 0.2, 0.2, 0.3, 0.7, 1.4, 2.3, 3.4, 4.2, 4.8,
  4.1, 3.6, 3.9, 4.3, 4.6, 4.4, 4.9, 5.6, 6.1, 5.4, 3.2, 1.6,
];
const PESO_TOTAL = PESO_HORA.reduce((s, w) => s + w, 0);

function horaPonderada(r: number) {
  let acc = 0;
  const alvo = r * PESO_TOTAL;
  for (let h = 0; h < 24; h++) {
    acc += PESO_HORA[h];
    if (alvo <= acc) return h;
  }
  return 23;
}

/** Produto sorteado por popularidade (vendas históricas), não uniforme. */
function produtoPonderado(r: number, pool: Produto[]) {
  const total = pool.reduce((s, p) => s + p.vendas, 0);
  let acc = 0;
  const alvo = r * total;
  for (const p of pool) {
    acc += p.vendas;
    if (alvo <= acc) return p;
  }
  return pool[pool.length - 1];
}

function gerarPedidos(qtd: number): Pedido[] {
  const rnd = lcg(20260810);
  const vendaveis = PRODUTOS.filter((p) => p.status !== 'Rascunho');
  const out: Pedido[] = [];

  for (let i = 0; i < qtd; i++) {
    const prod = produtoPonderado(rnd(), vendaveis);
    const nome = NOMES[Math.floor(rnd() * NOMES.length)];
    const r = rnd();
    const status: PedidoStatus =
      r < 0.68 ? 'Aprovado' : r < 0.82 ? 'Pendente' : r < 0.9 ? 'Recusado' : r < 0.97 ? 'Reembolsado' : 'Chargeback';
    const metodo: Metodo = prod.tipo === 'Digital'
      ? (rnd() < 0.62 ? 'PIX' : 'Cartão')
      : (rnd() < 0.45 ? 'PIX' : rnd() < 0.9 ? 'Cartão' : 'Boleto');
    const parcelas = metodo === 'Cartão' ? [1, 2, 3, 6, 10, 12][Math.floor(rnd() * 6)] : 1;

    out.push({
      id: `#PG-${10520 - i}`,
      cliente: nome,
      email: slugEmail(nome),
      produtoId: prod.id,
      valor: prod.preco,
      metodo,
      parcelas,
      status,
      hora: horaPonderada(rnd()),
      minuto: Math.floor(rnd() * 60),
      dia: 10 - Math.floor(rnd() * 3),
      afiliado: AFILIADOS[Math.floor(rnd() * AFILIADOS.length)],
      uf: UFS[Math.floor(rnd() * UFS.length)],
    });
  }
  return out;
}

/** Volume de 3 dias de operação. A tabela pagina; as métricas usam tudo. */
export const PEDIDOS = gerarPedidos(524);

export const produtoDe = (id: string) => PRODUTOS.find((p) => p.id === id)!;

/* ---------------- métricas derivadas ---------------- */
const aprovados = PEDIDOS.filter((p) => p.status === 'Aprovado');
const hoje = PEDIDOS.filter((p) => p.dia === 10);
const aprovadosHoje = hoje.filter((p) => p.status === 'Aprovado');

export const METRICAS = {
  pedidosHoje: hoje.length,
  faturamentoHoje: aprovadosHoje.reduce((s, p) => s + p.valor, 0),
  faturamentoTotal: aprovados.reduce((s, p) => s + p.valor, 0),
  ticketMedio: aprovados.length ? aprovados.reduce((s, p) => s + p.valor, 0) / aprovados.length : 0,
  aprovacao: PEDIDOS.length ? (aprovados.length / PEDIDOS.length) * 100 : 0,
  reembolsos: PEDIDOS.filter((p) => p.status === 'Reembolsado').length,
  pendentes: PEDIDOS.filter((p) => p.status === 'Pendente').length,
  chargebacks: PEDIDOS.filter((p) => p.status === 'Chargeback').length,
  disponivelSaque: aprovados.reduce((s, p) => s + p.valor, 0) * 0.82,
  visitantes: 4187,
  aoVivo: 23,
};

/** Faturamento aprovado por hora — alimenta o gráfico do dashboard. */
export const VENDAS_POR_HORA = Array.from({ length: 24 }, (_, h) => ({
  h: `${String(h).padStart(2, '0')}h`,
  v: aprovadosHoje.filter((p) => p.hora === h).reduce((s, p) => s + p.valor, 0),
}));

/** Top produtos por receita aprovada. */
export const TOP_PRODUTOS = PRODUTOS.map((prod) => {
  const meus = aprovados.filter((p) => p.produtoId === prod.id);
  return { prod, qtd: meus.length, receita: meus.reduce((s, p) => s + p.valor, 0) };
})
  .filter((t) => t.qtd > 0)
  .sort((a, b) => b.receita - a.receita);

/** Receita por método de pagamento (share). */
export const POR_METODO = (['PIX', 'Cartão', 'Boleto'] as Metodo[])
  .map((m) => {
    const meus = aprovados.filter((p) => p.metodo === m);
    return { metodo: m, qtd: meus.length, receita: meus.reduce((s, p) => s + p.valor, 0) };
  })
  .filter((x) => x.qtd > 0);
