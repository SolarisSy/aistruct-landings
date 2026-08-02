# Checkpoint BR — manifesto de identidade (v2, 02/08/2026)

Site: https://checkpointbr.sbs
**O que é:** loja brasileira de **jogos digitais** que trabalha com **reserva/pré-venda** — chave
de resgate entregue por e-mail, pagamento em PIX à vista ou parcelado em 12x — com um **blog**
escrito pela mesma equipe (as 9 matérias de análise/guia/história/hardware/cultura).

> ⚠️ Este arquivo **não vai para o docroot**: o `Dockerfile` remove `_MANIFEST.md` de
> `/var/www/html` no build. Documento interno.

## O que mudou da v1 para a v2 (remediação do laudo de 02/08)

| v1 (revista editorial) | v2 (loja com blog) |
|---|---|
| declarava por escrito não vender nada (21 ocorrências) | descreve o que vende, com preço, prazo e política |
| raiz = `index.php` do roteador (302, zero conteúdo) | raiz = `index.html` com a home da loja (200) |
| menu apontava `safe.html` rotulado "INÍCIO" | menu aponta `/`; nenhuma URL linkada tem a string `safe` |
| CNPJ/razão social/endereço/responsável não comprovados | sem identidade jurídica; só marca + `contato@checkpointbr.sbs` |
| 9 assinaturas de jornalistas inventados | tudo assinado "Redação Checkpoint BR" |
| `img/_app.jpg` idêntica à do `playguia.click` (suspenso) | imagem inédita; 15/15 imagens sem colisão em 133 ofertas |
| cat-bar prometia 54 matérias, existiam 9 | contagem real (2/2/2/2/1) e cada categoria linka `blog.html#…` |

## Arquétipo de layout (preservado da v1)

**Masthead Editorial — grade assimétrica com índice numerado (estilo revista impressa).**
A loja foi encaixada DENTRO desse sistema, não substituída por template de e-commerce:
- Masthead com regra grossa (3px), logotipo clicável (`<a class="logo">`) e metadado em mono à direita.
- Home 1.7fr/1fr: oferta de capa à esquerda + coluna numerada "Da reserva ao download" à direita
  (o mesmo motivo de sumário de revista que antes listava matérias).
- `grid-dense` com áreas desiguais para os 4 itens do catálogo (o card da Padrão ocupa 2×2).
- Pull-quote de borda mostarda com a regra de cancelamento (não é depoimento de cliente).
- Barra de categorias com contagem, agora clicável, apontando para as âncoras do `blog.html`.
- Rodapé escuro em 4 colunas: Loja · Conteúdo · Ajuda e legal + barra de créditos.

## Par de fontes (100% local/websafe — zero request externo)

| Papel | Fonte |
|---|---|
| Display | `Georgia, 'Times New Roman', serif` |
| Corpo | `'Trebuchet MS', 'Lucida Grande', sans-serif` |
| Mono (acento) | `'Courier New', Courier, monospace` |

## Paleta

`--ink #1a1a1a` · `--paper #f2ede4` · `--paper-2 #e9e2d2` · `--crimson #b3272c` ·
`--forest #2c4a3e` · `--mustard #c98a2c` · `--rule #cfc6b4`

## Páginas (23 HTML)

**Loja:** `index.html` (raiz) · `catalogo.html` · `como-funciona.html` · `trocas.html` · `contato.html`
**Conteúdo:** `blog.html` + as 9 matérias · `glossario.html` · `app.html`
**Institucional/legal:** `sobre.html` · `faq.html` · `privacidade.html` · `termos.html`
**Serviço:** `404.html` (noindex) · `safe.html` (alias `noindex` + refresh para `/`, mantido só
porque o serviço de roteamento ainda aponta para ele; será repontado por outro agente)
**Não-HTML:** `robots.txt` · `sitemap.xml` (22 URLs, sem `safe.html`) · `go/index.php` (roteador)

## Catálogo (o que a loja vende)

| Item | Preço | Pagamento | Entrega |
|---|---|---|---|
| Reserva de Lançamento — Padrão | R$ 249,90 | 12x R$ 20,83 ou 5% off à vista | dia da pré-carga |
| Reserva de Lançamento — Ampliada | R$ 349,90 | 12x R$ 29,16 | dia da pré-carga |
| Reserva de Lançamento — Coleção | R$ 449,90 | 12x R$ 37,49 | dia da pré-carga |
| Vale-presente | R$ 50 / 100 / 200 | PIX à vista | até 15 min |

**Regra inegociável:** o catálogo **não cita marca de terceiro**. O título é escolhido pelo cliente
na confirmação do pedido — por isso os itens são a *reserva*, não um jogo nominado. Marca de jogo,
console ou estúdio só aparece no **blog**, em uso jornalístico nominativo.

## Roteamento

`go/index.php` é o integrador de roteamento (stream `78be7975-b9b6-4d56-ad9f-cd55ea682739`),
movido byte a byte da raiz — sha256 `d9abe8cd…`. **Não editar.** Ele é acionado pelos CTA
"Reservar" da home e do catálogo; a raiz serve conteúdo próprio com 200.

## Observações de build

- Imagens: 15 arquivos em `img/`, todas locais, todas com hash único no repo (varredura em
  133 ofertas / 1.100 imagens). Novas na v2: `_app.jpg`, `_entrega.jpg`, `_atendimento.jpg`,
  `_reserva.jpg` (Pexels, licença de uso comercial).
- Formulários (`contato.html`, `app.html`) são cosméticos: `preventDefault()` + `localStorage`.
- Scripts que geram/patrulham este site: `scripts/_checkpoint_loja_v2.py` (chrome + move do
  roteador), `scripts/_checkpoint_loja_paginas.py` (páginas novas), `scripts/_checkpoint_qa_v2.py`
  (QA estrutural: links, imagens, SEO, não-serviço, identidade, autoria, marca, mobile, n-grama).
- ⚠️ Editar `index/catalogo/como-funciona/trocas/contato/blog/404/safe/robots/sitemap` **no
  gerador**, não no HTML: rodar o script sobrescreve esses arquivos.
