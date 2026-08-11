/** Lojas do produtor — multi-loja (o seletor no topo da sidebar). */
export type Loja = {
  id: string;
  nome: string;
  inicial: string;
};

export const LOJAS: Loja[] = [
  { id: 'l1', nome: 'Minha loja 1', inicial: 'M' },
  { id: 'l2', nome: 'Paggins SIP', inicial: 'P' },
  { id: 'l3', nome: 'Bernardo Store', inicial: 'B' },
  { id: 'l4', nome: 'SIP - Alemão', inicial: 'S' },
  { id: 'l5', nome: 'Lucas Suporte', inicial: 'L' },
];
