# Hubla — pesquisa consolidada (para espelhar no Paggins)

> Doc de trabalho do `paggins-black`. O que absorvemos do Hubla em 10/08/2026 e como isso
> vira feature aqui. Referência viva da skill: `.claude/skills/hubla/SKILL.md` · dump bruto:
> `reports/hubla/` (gitignored). **A skill manda no "como operar o Hubla"; este doc manda no
> "o que construir no Paggins".**

## Origem
- Acesso cedido pelo **Vitor Chioqueti** (WhatsApp) — conta `vsetecompany@gmail.com`.
- Task dele (verbatim): agente de IA no checkout · agente de IA na área de membros ·
  copiar a aba de assinatura · área de produtos com co-produção e afiliados.
- Entrega = neste projeto (`saas/paggins-black`, no ar em `paggins-black.tiectu.easypanel.host`).

## Como o Hubla é por dentro (o que importa pro clone)
- **SPA sem REST público** — só webhook de saída. Operar = API nativa (microserviços
  `backend-bff-*.platform.hub.la`). Auth Firebase + `x-device-data` (Castle). Detalhe na skill.
- **Design:** tema claro, verde-limão (`#c9f24d`-ish) como cor de marca/CTA, tipografia sem-serifa,
  cantos arredondados, muito respiro. O Paggins-black é o **oposto proposital** (dark) — mantemos
  a ESTRUTURA/fluxo deles, não a paleta.

## As 4 features → plano de construção no Paggins

### #3 Assinaturas  (`/assinaturas`) — [tela, mais simples]
Hubla: abas **Visão geral** (KPIs: ativas, novos assinantes, cancelados, inativados + gráfico) e
**Assinaturas** (lista: assinante, produto, plano, status, próxima cobrança, MRR).
→ Paggins: replicar as 2 abas com dataset de exemplo derivado. **Status: em construção.**

### #4 Produtos  (`/produtos`) — [tela, refatorar a existente]
Hubla: 3 abas — **Meus produtos · Minhas co-produções · Minhas afiliações**. API `products/offers`
devolve exatamente `{owner, affiliates, partners}`.
→ Paggins: adicionar as 3 abas à tela de produtos que já existe. **Status: em construção.**

### #1 Agente IA no checkout — [motor, o pesado]
Hubla "Assistente de checkout": chat que aborda objeção e conduz o lead até fechar; mede receita
atribuída ao agente. Modelo mental = Persona + Base de conhecimento + Gatilho (checkout aberto /
carrinho abandonado) + Canal + Objetivo (fechar venda).
→ Paggins: decidir motor (LLM Claude + base de conhecimento do produto + widget no checkout).
**Status: a definir motor antes de construir.**

### #2 Agente IA na área de membros — [motor]
Hubla "Tutor": assistente dentro da área do aluno, responde dúvida sobre o conteúdo.
→ mesmo motor da #1, gatilho diferente (dentro da área de membros). **Status: depende de #1.**

## Hubla Agents — estrutura a copiar (features #1/#2)
Menu deles: OPERAÇÃO (Métricas · Meus agentes · Conversas) + INTELIGÊNCIA (Personas · Bases de
conhecimento). Métricas: receita gerada, disparos, conversas, conversão, horas trabalhadas,
respostas fora do horário. É o blueprint da nossa seção de agentes.

## Diário
- **10/08/2026** — plataforma absorvida (14 áreas, 7 microserviços, client validado). Dashboard
  base do Paggins-black no ar (5 telas). Início da construção das features #3 e #4.
