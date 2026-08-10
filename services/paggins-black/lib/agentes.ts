/** Dados de exemplo dos Agentes IA — espelho do "Hubla Agents". */

export type Agente = {
  id: string;
  nome: string;
  tipo: 'Checkout' | 'Tutor' | 'Recuperação';
  gatilho: string;
  canal: string;
  ativo: boolean;
  persona: string;
  disparos: number;
  conversas: number;
  vendas: number;
  receita: number;
};

export const AGENTES: Agente[] = [
  {
    id: 'ag01', nome: 'Assistente de checkout', tipo: 'Checkout',
    gatilho: 'Checkout aberto', canal: 'Chat no checkout', ativo: true,
    persona: 'Vendedor consultivo', disparos: 1284, conversas: 892, vendas: 214, receita: 63658,
  },
  {
    id: 'ag02', nome: 'Recuperação de carrinho', tipo: 'Recuperação',
    gatilho: 'Carrinho abandonado (15 min)', canal: 'WhatsApp', ativo: true,
    persona: 'Vendedor consultivo', disparos: 742, conversas: 388, vendas: 97, receita: 28812,
  },
  {
    id: 'ag03', nome: 'Tutor da área de membros', tipo: 'Tutor',
    gatilho: 'Dúvida dentro do curso', canal: 'Área de membros', ativo: false,
    persona: 'Mentor didático', disparos: 0, conversas: 0, vendas: 0, receita: 0,
  },
];

export const AGENTE_METRICAS = {
  receita: AGENTES.reduce((s, a) => s + a.receita, 0),
  disparos: AGENTES.reduce((s, a) => s + a.disparos, 0),
  conversas: AGENTES.reduce((s, a) => s + a.conversas, 0),
  vendas: AGENTES.reduce((s, a) => s + a.vendas, 0),
  get conversao() {
    return this.conversas ? (this.vendas / this.conversas) * 100 : 0;
  },
};

export type Persona = { id: string; nome: string; tom: string; usadaPor: number };
export const PERSONAS: Persona[] = [
  { id: 'p1', nome: 'Vendedor consultivo', tom: 'Empático, foca em resolver objeção e criar urgência sem pressão', usadaPor: 2 },
  { id: 'p2', nome: 'Mentor didático', tom: 'Paciente, explica passo a passo, linguagem simples', usadaPor: 1 },
];

export type Base = { id: string; nome: string; itens: number; tipo: string };
export const BASES: Base[] = [
  { id: 'b1', nome: 'FAQ dos produtos', itens: 42, tipo: 'Perguntas e respostas' },
  { id: 'b2', nome: 'Política de reembolso', itens: 8, tipo: 'Documento' },
  { id: 'b3', nome: 'Conteúdo do curso Método VSL', itens: 120, tipo: 'Transcrições' },
];
