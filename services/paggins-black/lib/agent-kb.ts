/**
 * Motor dos Agentes IA — configuração + base de conhecimento.
 * Espelha o modelo do Hubla: cada agente = Persona + Base de conhecimento + Gatilho + Objetivo.
 * Consumido pela API route /api/agent (server) e pelo widget de chat (client).
 */

export type AgenteTipo = 'checkout' | 'tutor';

type AgenteConfig = {
  nome: string;
  saudacao: string;
  /** system prompt = persona + objetivo + regras */
  system: (ctx: KbContext) => string;
};

export type KbContext = {
  produto: string;
  preco?: string;
  descricao?: string;
  faq?: { q: string; a: string }[];
  aula?: string; // p/ tutor: assunto atual da área de membros
};

const REGRAS_COMUNS = `
Regras invioláveis:
- Responda SEMPRE em português do Brasil, curto (2-4 frases), tom humano e direto.
- Use SÓ as informações da base abaixo. Se não souber, diga que vai verificar — nunca invente preço, prazo ou promessa.
- Nunca peça dados sensíveis (cartão, CPF, senha) no chat; o pagamento acontece no próprio checkout.
- Uma pergunta por vez. Conduza, não empurre.`;

export const AGENTES_CFG: Record<AgenteTipo, AgenteConfig> = {
  checkout: {
    nome: 'Assistente de checkout',
    saudacao: 'Oi! 👋 Posso te ajudar a finalizar sua compra ou tirar qualquer dúvida sobre o produto. O que você precisa?',
    system: (ctx) => `Você é um assistente de vendas consultivo no CHECKOUT da loja, ajudando o cliente a concluir a compra de "${ctx.produto}".
Seu objetivo: remover objeções e conduzir o lead até finalizar o pagamento — sem pressão, com empatia.
${REGRAS_COMUNS}

Base de conhecimento:
- Produto: ${ctx.produto}${ctx.preco ? ` (${ctx.preco})` : ''}
${ctx.descricao ? `- Descrição: ${ctx.descricao}` : ''}
${(ctx.faq ?? []).map((f) => `- P: ${f.q}\n  R: ${f.a}`).join('\n')}

Quando o cliente demonstrar intenção de comprar, incentive a concluir no botão de pagamento da página.`,
  },
  tutor: {
    nome: 'Tutor',
    saudacao: 'Olá! Sou seu tutor aqui na área de membros. Pode me perguntar qualquer coisa sobre o conteúdo. 📚',
    system: (ctx) => `Você é um TUTOR dentro da área de membros, ajudando o aluno a entender o conteúdo do curso "${ctx.produto}".
Seu objetivo: tirar dúvidas sobre o material, explicar de forma didática e manter o aluno engajado.
${REGRAS_COMUNS}

Base de conhecimento:
- Curso: ${ctx.produto}
${ctx.aula ? `- Aula/módulo atual do aluno: ${ctx.aula}` : ''}
${(ctx.faq ?? []).map((f) => `- P: ${f.q}\n  R: ${f.a}`).join('\n')}

Explique passo a passo, com exemplos simples. Se a dúvida for fora do curso, redirecione com gentileza.`,
  },
};

/** KB de exemplo por contexto (na vida real viria do produto/base cadastrada). */
export const KB_DEMO: Record<AgenteTipo, KbContext> = {
  checkout: {
    produto: 'Método Renda Extra 6.0',
    preco: 'R$ 497,00 (ou 12x de R$ 49,70)',
    descricao: 'Curso digital de renda extra com acesso vitalício, 8 módulos e comunidade.',
    faq: [
      { q: 'Tem garantia?', a: 'Sim, 7 dias de garantia incondicional — se não gostar, devolvemos 100%.' },
      { q: 'Como recebo o acesso?', a: 'Na hora: assim que o pagamento é aprovado, o acesso chega no seu e-mail.' },
      { q: 'Aceita PIX?', a: 'Sim — PIX (aprovação na hora), cartão em até 12x e boleto.' },
      { q: 'Por quanto tempo tenho acesso?', a: 'Acesso vitalício, incluindo todas as atualizações futuras.' },
    ],
  },
  tutor: {
    produto: 'Método Renda Extra 6.0',
    aula: 'Módulo 2 — Escolhendo seu primeiro produto',
    faq: [
      { q: 'Onde vejo minhas aulas?', a: 'No menu Conteúdo, os módulos aparecem em ordem; clique para assistir.' },
      { q: 'Posso baixar os materiais?', a: 'Sim, cada aula tem os anexos em PDF na aba Materiais.' },
      { q: 'Tenho certificado?', a: 'Sim, ao concluir 100% dos módulos o certificado é liberado automaticamente.' },
    ],
  },
};
