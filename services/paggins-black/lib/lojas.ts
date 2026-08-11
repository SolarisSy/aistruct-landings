/** Lojas do produtor — multi-loja (o seletor no topo da sidebar). */
export type Loja = {
  id: string;
  nome: string;
  grad: string;       // gradiente do avatar (cor própria por loja)
  inicial: string;
};

export const LOJAS: Loja[] = [
  { id: 'l1', nome: 'Minha loja 1', grad: 'from-sky-400 to-teal-400', inicial: 'M' },
  { id: 'l2', nome: 'Paggins SIP', grad: 'from-blue-500 to-indigo-600', inicial: 'P' },
  { id: 'l3', nome: 'Bernardo Store', grad: 'from-amber-400 to-orange-600', inicial: 'B' },
  { id: 'l4', nome: 'SIP - Alemão', grad: 'from-rose-500 to-red-600', inicial: 'S' },
  { id: 'l5', nome: 'Lucas Suporte', grad: 'from-violet-500 to-fuchsia-600', inicial: 'L' },
];
