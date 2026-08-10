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

## Telas

| Rota | Origem no Figma |
|---|---|
| `/` | `Dashboard Screen` (`4482:21755`) |
| `/produtos` | `Todos os produtos` (`4482:25571`) |
| `/produtos/novo` | `Produto fisico` passo 2/8 (`4488:17512`) |
| `/funil` | `UpSell` (`4678:13514`) |
| `/pedidos` | `Todos os pedidos` (`4924:16605`) — dados de exemplo |

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
