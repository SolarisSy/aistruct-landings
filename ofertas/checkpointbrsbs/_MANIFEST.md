# Checkpoint BR — Manifesto de identidade

Site: https://checkpointbr.sbs
Tema: jornalismo/editorial de games em PT-BR (análises, guias, história de franquias, cultura gamer, hardware).

## Arquétipo de layout

**Masthead Editorial — grade assimétrica com índice numerado (estilo revista impressa).**

Não é "hero gigante + 3 cards + seções empilhadas". A home segue a lógica de uma capa de revista:
- Masthead com regra grossa (3px) separando o logotipo do corpo, metadado de edição em mono à direita.
- Hero assimétrico 1.7fr / 1fr: matéria de capa grande à esquerda + coluna "Nesta edição" à direita,
  com índice numerado (02, 03, 04, 05) em mono, separado por hairlines — motivo visual recorrente
  do sumário de revista impressa.
- Grade densa e desigual (`grid-template-areas`) para as últimas matérias: um card ocupa 2×2, os
  outros três dividem o restante — não é grid uniforme de cards iguais.
- Pull-quote com borda grossa lateral (mostarda) entre seções, quebrando o ritmo.
- Barra de categorias com contagem (estilo rodapé de índice de revista).
- Rodapé escuro (tinta) em 4 colunas com regra de créditos abaixo.

Páginas internas (Sobre/FAQ/Glossário/App/Privacidade/Termos) usam variação de coluna única do
mesmo sistema: capitular (drop cap) em Sobre, colunas jornalísticas (`columns:2`) com divisor de
letra em Glossário, lista numerada em mono no FAQ, cartão de recursos em grade 2×2 no App.

## Par de fontes (100% local/websafe — zero request externo)

| Papel | Fonte | Uso |
|---|---|---|
| Display | `Georgia, 'Times New Roman', serif` | H1/H2, capitulares, títulos de card |
| Corpo | `'Trebuchet MS', 'Lucida Grande', sans-serif` | Parágrafo, dek, navegação |
| Mono (acento editorial) | `'Courier New', Courier, monospace` | Kickers/eyebrows, bylines, números do índice, meta de edição |

Nenhum `@font-face`/CDN — só fontes de sistema referenciadas por `font-family`.

## Paleta (6 cores com função definida)

| Cor | Hex | Função |
|---|---|---|
| Tinta (ink) | `#1a1a1a` | Texto principal, títulos, rodapé (fundo) |
| Papel (paper) | `#f2ede4` | Fundo base (off-white quente, não branco puro) |
| Carmim (crimson) | `#b3272c` | Categoria "Análises", destaque de link ativo, capitular, hover de botão |
| Verde-mata (forest) | `#2c4a3e` | Categoria "Guias", links padrão |
| Mostarda (mustard) | `#c98a2c` | Categoria "Hardware", borda do pull-quote, destaque de rodapé |
| Linha (rule) | `#cfc6b4` | Hairlines, divisores, numerais de índice apagados |

Categorias História e Cultura reaproveitam a tinta (`--ink`) com sublinhado grosso — evita introduzir
uma 7ª cor no sistema.

## Os 9 artigos (slug + título + resumo + foto ideal)

1. **`elden-ring-nightreign-analise`** — *Elden Ring Nightreign: quando o roguelike engole a alma de FromSoftware*
   Resumo: análise do spin-off cooperativo com rodadas de 40 minutos, testando se a estrutura roguelike combina com o ritmo contemplativo da série.
   Foto ideal: personagem em armadura escura enfrentando criatura colossal em campo de batalha ao entardecer, tons terrosos.

2. **`guia-builds-baldurs-gate-3-2026`** — *Sete builds de Baldur's Gate 3 que ainda quebram o jogo em 2026*
   Resumo: guia técnico com combinações de classe/multiclasse que continuam eficazes mesmo após os patches de balanceamento.
   Foto ideal: mesa de RPG de mesa com dados poliédricos e miniaturas, iluminada por vela.

3. **`historia-secreta-chrono-trigger`** — *A sala dos fundos da Square: como quatro gênios criaram Chrono Trigger em 14 meses*
   Resumo: reconstituição do "dream team" (Horii, Toriyama, Sakaguchi, Uematsu) e do cronograma apertado que gerou um clássico do SNES.
   Foto ideal: cartucho de Super Nintendo antigo sobre mesa de madeira, luz nostálgica lateral.

4. **`hardware-rtx-5070-custo-beneficio`** — *RTX 5070 um ano depois: o preço caiu, a fila de espera não*
   Resumo: revisão de custo-benefício da placa um ano após o lançamento, com benchmark próprio e comparação de disponibilidade no varejo brasileiro.
   Foto ideal: placa de vídeo iluminada por RGB em bancada escura, close nos coolers.

5. **`cultura-esports-cs2-brasil`** — *Do fliperama ao Major: a geração que profissionalizou o CS no Brasil*
   Resumo: perfil da cena competitiva brasileira de Counter-Strike, da lan house paga por hora às arenas patrocinadas de hoje.
   Foto ideal: arena de esports lotada, telão exibindo placar de partida.

6. **`analise-hollow-knight-silksong`** — *Silksong não perdoa: por que a sequência é mais cruel que o original*
   Resumo: análise da curva de dificuldade de Silksong frente a Hollow Knight, com pontos onde o desafio funciona e onde desanda.
   Foto ideal: personagem inseto samurai com espada em caverna bioluminescente.

7. **`guia-emuladores-legais-2026`** — *O que é (e o que não é) legal emular no Brasil em 2026*
   Resumo: guia jurídico-prático sobre emulação de consoles à luz da Lei de Direitos Autorais e da jurisprudência recente.
   Foto ideal: console retro conectado a TV de tubo em sala de estar doméstica brasileira.

8. **`franquia-resident-evil-25-anos`** — *25 anos de Resident Evil: como a série morreu duas vezes e ressuscitou três*
   Resumo: linha do tempo da franquia com dados de venda por era, do tropeço comercial de 2005 ao relançamento de 2017.
   Foto ideal: corredor escuro de mansão em jogo de terror, iluminado por lanterna.

9. **`hardware-portateis-2026-steam-deck`** — *Guerra dos portáteis: Steam Deck, ROG Ally e a fatia que a Nintendo não quer perder*
   Resumo: comparativo de autonomia, ruído e desempenho entre os três portáteis mais vendidos do primeiro semestre de 2026.
   Foto ideal: três consoles portáteis lado a lado sobre mesa de madeira, ângulo de comparação.

## Observações de build

- `safe.html`, `sobre.html`, `faq.html`, `glossario.html`, `app.html`, `privacidade.html`, `termos.html`
  criados nesta pasta. `index.php`, `Dockerfile` e `nginx-site.conf` não foram tocados.
- Referências de imagem usadas (a baixar depois): `img/_hero.jpg`, `img/_sobre.jpg`, `img/_app.jpg`
  e `img/<slug>.jpg` para cada um dos 9 artigos.
- As 9 páginas de artigo (`<slug>.html`) ainda não existem — ficam para os redatores; os links do
  índice/grade na home e no rodapé já apontam para esses arquivos.
- Newsletter em `app.html` é cosmética (`preventDefault()`, sem coleta real).
- Nenhuma página tem preço, urgência, link externo ou formulário que colete dado real.
