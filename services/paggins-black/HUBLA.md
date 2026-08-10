> ⚠️ **ESCOPO REVISADO (10/08/2026):** a Paggins 2.0 é a **dashboard NOVA da Paggins 1.0 real**
> (não o clone do Hubla). O alvo virou espelhar TODAS as funções do painel real
> (`www.paggins.com`, acesso sip) — mapa em `reports/paggins-v1/` via `scripts/_paggins_absorve.py`.
> As features do Hubla (agentes IA) entraram como DIFERENCIAL por cima. Este doc segue válido para
> a parte dos agentes; o mapa completo da 1.0 está no README e no Jira PG2.

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

### #3 Assinaturas  (`/assinaturas`) — ✅ FEITO
Hubla: abas **Visão geral** (KPIs: ativas, novos assinantes, cancelados, inativados + gráfico) e
**Assinaturas** (lista: assinante, produto, plano, status, próxima cobrança, MRR).
→ Paggins: 2 abas, KPIs, gráfico MRR (6 meses), assinantes por plano, lista de 96 assinaturas.

### #4 Produtos  (`/produtos`) — ✅ FEITO
Hubla: 3 abas — **Meus produtos · Minhas co-produções · Minhas afiliações**. API `products/offers`
devolve exatamente `{owner, affiliates, partners}`.
→ Paggins: 3 abas; co-produção/afiliação mostram autor + comissão + sua receita. KPIs de catálogo.

### #1 Agente IA no checkout — [motor, o pesado] — 🟡 UI FEITA, motor pendente
Hubla "Assistente de checkout": chat que aborda objeção e conduz o lead até fechar; mede receita
atribuída ao agente. Modelo mental = Persona + Base de conhecimento + Gatilho (checkout aberto /
carrinho abandonado) + Canal + Objetivo (fechar venda).
→ Paggins: `/agentes` já tem a ESTRUTURA (Métricas · Meus agentes · Conversas · Personas · Bases),
com os 3 agentes (checkout/recuperação/tutor) e métricas. **Falta o MOTOR:** LLM Claude +
base de conhecimento do produto + widget de chat no checkout + atribuição de receita.
**Status: decidir motor (Claude API já é padrão da casa) antes de plugar.**

### #2 Agente IA na área de membros — [motor] — 🟡 UI no card "Tutor", motor pendente
Hubla "Tutor": assistente dentro da área do aluno, responde dúvida sobre o conteúdo.
→ mesmo motor da #1, gatilho diferente (dentro da área de membros). **Status: depende de #1.**

## Hubla Agents — estrutura a copiar (features #1/#2)
Menu deles: OPERAÇÃO (Métricas · Meus agentes · Conversas) + INTELIGÊNCIA (Personas · Bases de
conhecimento). Métricas: receita gerada, disparos, conversas, conversão, horas trabalhadas,
respostas fora do horário. É o blueprint da nossa seção de agentes.

## Diário
- **10/08/2026** — plataforma absorvida (14 áreas, 7 microserviços, client validado). Dashboard
  base do Paggins-black no ar (5 telas). Início da construção das features #3 e #4.
