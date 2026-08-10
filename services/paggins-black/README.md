# Paggins — Black

Versão **black** da dashboard Paggins, reconstruída em código a partir do arquivo Figma
(cópia `Paggins (cópia)`, página `Web 2.0` — 52 telas em 9 seções).

Segue o **ADR #12** do `PLANO.md`: o design system vive em CSS (`app/globals.css`), não no Figma.

```bash
npm install
npm run dev     # http://localhost:3210
npm run build   # validação de tipos + compilação
```

## Tema

Tokens em `app/globals.css` (`@theme inline` do Tailwind v4). A conversão foi medida no arquivo
original, não chutada:

| Papel | Original (Figma) | Black |
|---|---|---|
| Fundo | `#0f1535` | `#000000` |
| Card | `#1a1f37` | `#0e0e11` |
| Sidebar | `#060b26` | `#060607` |
| Texto secundário | `#a0aec0` | `#8b8b96` |
| Primária | `#0b6eae` | `#1a8fd8` ← elevada p/ contraste sobre preto |
| Sucesso | `#0cc036` | `#22c55e` |
| Teal | `#4fd1c5` | mantida |

Tipografia **Montserrat** e raios **8/12/100px** são fiéis ao original.
O gradiente azul de fundo virou a classe `.aurora` — um halo frio quase imperceptível no topo,
que dá profundidade sem sujar o preto.

## Escopo (10/08/2026)

A **Paggins 2.0** é a **dashboard nova da Paggins 1.0 real** (`www.paggins.com`, acesso no perfil
sip) — todas as funções do painel no tema black. Mapa do painel real capturado por
`scripts/_paggins_absorve.py` → `reports/paggins-v1/`. Diferencial por cima: **Agentes IA**
(inspirados no Hubla — ver `HUBLA.md`).

## Telas (23 rotas — espelham o menu da Paggins 1.0)

| Grupo | Rotas |
|---|---|
| Dashboard | `/` |
| Produtos | `/produtos` (abas Meus/Co-produções/Afiliações) · `/produtos/novo` · `/produtos/order-bump` · `/funil` · `/descontos` |
| Vendas | `/pedidos` · `/assinaturas` · `/clientes` · `/recuperacao` |
| Agentes IA | `/agentes` · `/checkout` (agente checkout) · `/membros` (tutor) · `POST /api/agent` (motor Claude) |
| Afiliados | `/afiliados` |
| Financeiro | `/financeiro` · `/financeiro/extrato` · `/financeiro/saque` |
| Relatórios | `/metricas` |
| Extensões | `/extensoes` · `/extensoes/webhooks` · `/extensoes/api-keys` |
| Config | `/configuracoes` |

O motor dos agentes (`/api/agent`) chama a Claude API (`claude-haiku-4-5`) com base de
conhecimento por produto (`lib/agent-kb.ts`). Precisa de `ANTHROPIC_API_KEY` no ambiente
(local: `.env.local`; produção: env do serviço Easypanel). Sem a chave, responde em modo
degradado sem quebrar a UI.

## QA visual

```bash
npm start                                      # sobe em :3210
.\pyrun.ps1 scripts/_paggins_black_shots.py    # → reports/paggins-black/*.png
```
O script falha (exit 1) se alguma rota sair de 200 ou se houver erro de runtime na página.

## Dados de exemplo

`lib/data.ts` — 16 produtos e **524 pedidos** de 3 dias, gerados por um LCG de seed fixa
(determinístico: server e client rendem o mesmo HTML, sem hydration mismatch).

Duas escolhas que fazem a demo parecer real:
- **hora ponderada por curva de tráfego** (madrugada morta, picos às 11h e 20–21h) em vez de
  distribuição uniforme — sem isso o gráfico fica achatado com um pico solitário;
- **produto sorteado por popularidade**, não uniforme — os campeões de venda dominam o mix.

**Nenhum KPI é hardcoded.** Faturamento, ticket médio, taxa de aprovação, top produtos, receita
por método e as pendências são todos derivados de `PEDIDOS`. Mudar o dataset move a dashboard
inteira junto.

## Pendente

- 47 das 52 telas do Figma ainda não portadas (Detalhes do produto, Criar produto digital,
  demais passos do wizard, consultar pedido, Solicitar reembolso, landing page).
- **Sem backend**: dados vêm do módulo, não de API/banco. Filtros, busca, paginação e botões
  são estáticos — a UI existe, a ação não.
- Fonte via Google Fonts CDN; para produção, hospedar local (footprint + offline).
