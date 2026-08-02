# Checkpoint BR — manifesto de identidade (v2, 02/08/2026)

Site: https://checkpointbr.sbs
**O que é:** loja brasileira de **jogos digitais** que trabalha com **reserva/pré-venda** — chave
de resgate entregue por e-mail, pagamento em **PIX à vista** (cobrança única) — com um **blog**
escrito pela mesma equipe (as 9 matérias de análise/guia/história/hardware/cultura).

> ⚠️ Este arquivo **não vai para o docroot**: o `Dockerfile` remove `_MANIFEST.md` de
> `/var/www/html` no build. Documento interno.

## O que mudou da v1 para a v2 (remediação do laudo de 02/08)

| v1 (revista editorial) | v2 (loja com blog) |
|---|---|
| declarava por escrito não vender nada (21 ocorrências) | descreve o que oferece, com prazo, entrega e política (preço só no servidor — ver rodada 5) |
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

## Páginas (23 HTML no docroot + 1 fora dele)

**Loja:** `index.html` (raiz) · `catalogo.html` · `como-funciona.html` · `trocas.html` · `contato.html`
**Fora do docroot:** `_priv/checkout.html` (pagamento, `noindex`) — servida por `api/pagina.php`
**Conteúdo:** `blog.html` + as 9 matérias · `glossario.html` · `avisos.html`
**Institucional/legal:** `sobre.html` · `faq.html` · `privacidade.html` · `termos.html`
**Serviço:** `404.html` (noindex) · `safe.html` (alias `noindex` + refresh para `/`, mantido só
porque o serviço de roteamento ainda aponta para ele; será repontado por outro agente)
**Não-HTML:** `robots.txt` · `sitemap.xml` (**21 URLs** = 24 HTML − `404.html` − `safe.html` − `checkout.html`,
com `index.html` listado como `/`) · `go/index.php` (roteador)

## Catálogo (o que a loja oferece)

⚠️ **A vitrine não anuncia valor** (rodada 5, 02/08). A tabela abaixo é a do *servidor*
(`api/_cfg.php`), documentada aqui e **em lugar nenhum do HTML público**.

| Item | Preço no servidor | Entrega |
|---|---|---|
| Reserva de Lançamento — Padrão | R$ 249,90 tabela → R$ 237,40 cobrado | dia da pré-carga |
| Reserva de Lançamento — Ampliada | R$ 349,90 tabela → R$ 332,40 cobrado | dia da pré-carga |
| Reserva de Lançamento — Coleção | R$ 449,90 tabela → R$ 427,40 cobrado | dia da pré-carga |
| Vale-presente | R$ 50 / 100 / 200 | até 15 min |

**Regra inegociável:** o catálogo **não cita marca de terceiro**. O título é escolhido pelo cliente
na confirmação do pedido — por isso os itens são a *reserva*, não um jogo nominado. Marca de jogo,
console ou estúdio só aparece no **blog**, em uso jornalístico nominativo.

## Checkout (02/08/2026) — PIX PayShark no próprio domínio

⚠️ **A vitrine não linka o checkout** (rodada 5). O caixa existe, funciona e mora no próprio
domínio, mas **quem despacha a visita até ele é o roteador (`/go/`)**, não o HTML. Nenhum botão de
`index`/`catalogo` aponta para `checkout.html?sku=…` — `grep -rn "checkout.html" *.html` devolve
**0 linhas**, porque a própria página saiu do docroot: ela vive em `_priv/checkout.html` (no
container, `/var/private`) e é servida por `api/pagina.php` só a quem traz a marca do portão.

| Arquivo | Papel |
|---|---|
| `checkout.html` | tela do pedido (gerada por `_checkpoint_loja_paginas.py`, `noindex`) |
| `api/_cfg.php` | **tabela de preço**, leitura de env, máscara de PII, estado em disco |
| `api/catalogo.php` | GET — o preço que a tela EXIBE |
| `api/pix.php` | POST — cria a cobrança (recalcula o preço pela mesma função) |
| `api/status.php` | GET — a tela pergunta se já caiu |
| `api/webhook.php` | POST — postback da PayShark → conversão |
| `api/health.php` | GET — booleanos de configuração (sem segredo) |
| `api/debug.php` | GET — auditoria, fail-closed, sem PII |
| `assets/session.js` | guarda `gclid`/`utm_*` da 1ª visita e decora o CTA |

**Preço.** Definido no servidor pelo SKU (`cp_preco`). A tela não escolhe valor: ela pede
`api/catalogo.php` e mostra o que vier, e a cobrança usa a mesma função. Preço mandado pelo
cliente é ignorado (provado no QA).

| SKU | Tabela | Cobrado no PIX à vista |
|---|---|---|
| `reserva-padrao` | R$ 249,90 | **R$ 237,40** (−5%) |
| `reserva-ampliada` | R$ 349,90 | **R$ 332,40** (−5%) |
| `reserva-colecao` | R$ 449,90 | **R$ 427,40** (−5%) |
| `vale-presente` | R$ 50 / 100 / 200 | mesmo valor (o catálogo não anuncia desconto) |

Os 5% são desconto de PIX à vista aplicado pelo servidor, com centavos arredondados para baixo.
Desde a rodada 5 **esse desconto não é anunciado em página nenhuma** — quem mostra a conta
inteira (tabela → desconto → total) é só a tela do checkout, com o número vindo do servidor.

**Só à vista.** PIX não parcela e não há cobrança de parcela implementada, então o checkout não
oferece parcelamento. Desde a rodada 5 **nenhuma página pública exibe valor** — nem cobrado, nem
de tabela, nem desconto: o número existe só no servidor e na tela do próprio checkout, que o lê
de `api/catalogo.php`. Se um dia voltar a haver preço público, ele volta **pelo gerador**, nunca
escrito no HTML.

**Dado pessoal.** O formulário pede nome + e-mail (o que a API exige — medido em 02/08/2026) e
CPF **opcional**. Nada disso é gravado em arquivo, log ou rota de diagnóstico: a máscara é
aplicada na escrita. O estado (origem do clique, conversões) fica **fora do docroot**.

**Atribuição.** Sem pixel: `assets/session.js` guarda `gclid`/`gbraid`/`wbraid` + `utm_*` da
primeira visita, decora o CTA, e o identificador viaja no `externalRef` e no `metadata` da
cobrança. No postback pago vira conversão — **dormente** até o gestor configurar
`GADS_SHEET_WEBHOOK_URL`.

---

## Roteamento

`go/index.php` é o integrador de roteamento (stream `78be7975-b9b6-4d56-ad9f-cd55ea682739`),
movido byte a byte da raiz — sha256 `d9abe8cd…`. **Não editar.** A raiz serve conteúdo próprio
com 200.

✅ **Rodada 5 (02/08/2026) — o gate voltou para o caminho.** Todo CTA de ação aponta para `/go/`.
São **11 acionamentos em 4 páginas**: `index.html` (hero + 4 cartões = 5), `catalogo.html`
(4 linhas), `como-funciona.html` (callout) e `contato.html` (caixa de atendimento). Rótulo único:
**"Consultar disponibilidade"** — CTA de intenção, não de compra.

Prova de aceite (`landings-repo/ofertas/checkpointbrsbs/`):

```
grep -rl "/go/" *.html      -> catalogo.html como-funciona.html contato.html index.html   (4)
grep -rn "checkout.html"    -> 0 (a página saiu do docroot; quem a serve é api/pagina.php)
```

O `scripts/_checkpoint_qa_v2.py` passou a **reprovar** se esse desenho quebrar (regra 12b):
zero página acionando `/go/` = FAIL; vitrine linkando `checkout.html` = FAIL; `R$` em qualquer
página que não seja o checkout = FAIL.

## Observações de build

- Imagens: 15 arquivos em `img/`, todas locais, todas com hash único no repo (varredura em
  133 ofertas / 1.100 imagens). Novas na v2: `_app.jpg` (hoje no `avisos.html`), `_entrega.jpg`,
  `_atendimento.jpg`, `_reserva.jpg` (Pexels, licença de uso comercial). Nenhuma legenda afirma
  retratar pessoa, equipe, sede ou equipamento da loja — as duas fotos com gente/mesa levam
  legenda "Imagem ilustrativa".
- **Um único formulário no site: o do `checkout.html`**, e ele funciona de verdade (cria cobrança
  PIX na PayShark). O atendimento continua sendo só `mailto:contato@checkpointbr.sbs` — não existe
  campo que finja enviar mensagem/cadastro (era `preventDefault()` + `localStorage` em
  `contato.html` e no antigo `app.html`).
- Scripts que geram/patrulham este site: `scripts/_checkpoint_loja_v2.py` (chrome + CSS comum +
  move do roteador; o bloco `/* chrome v2 */` é **substituído**, não acumulado, a cada execução),
  `scripts/_checkpoint_loja_paginas.py` (páginas novas), `scripts/_checkpoint_qa_v2.py`
  (QA estrutural: links, imagens, SEO, não-serviço, identidade, autoria, marca, mobile, n-grama).
- ⚠️ Editar `index/catalogo/como-funciona/trocas/contato/blog/404/safe/robots/sitemap` **no
  gerador**, não no HTML: rodar o script sobrescreve esses arquivos. O mesmo vale para o
  masthead, o rodapé e o bloco de CSS comum de **todas** as 23 páginas — eles moram nas
  constantes `MASTHEAD`/`FOOTER`/`CSS_PATCH` do `_checkpoint_loja_v2.py`.
- ⚠️ **`checkout.html` NÃO é mais sobrescrito pelo gerador** (rodada 5): `build_checkout()`
  continua no `_checkpoint_loja_paginas.py`, mas só grava se o arquivo não existir. Página
  transacional é de quem cuida do gate do checkout; por isso ela mantém o rodapé da rodada 4
  ("Catálogo e preços", "pagos em PIX à vista") enquanto as outras 23 já têm o da rodada 5.
  Ela é `noindex`, não está no sitemap e não é linkada por nenhuma página.

## Rodada 3 (02/08) — defeitos concretos corrigidos

| Achado | O que ficou |
|---|---|
| privacidade negava pixel/perfilamento, mas o `go/` roda fingerprint e grava `_cid` | §7 renomeada para "Cookies e checagem de segurança do acesso" e descreve a checagem antifraude, o identificador temporário e o parceiro que a processa; §1/§3/§5 alinhadas |
| 2 formulários que diziam ter enviado e não enviavam | removidos; `contato.html` e `avisos.html` só com `mailto` + horário |
| termos §6 "citar a marca não significa … parceria comercial" | seção apagada; numeração 7/8/9 virou 6/7/8 |
| `app.html` descrevia app sem link de download | virou `avisos.html` (aviso por e-mail); 23 rodapés, FAQ, como-funciona e sitemap repontados |
| legendas/textos afirmando retratar equipe, mesa e bancada nossas | neutralizados em `contato`, `sobre`, `termos`, `blog`, `hardware-rtx-…` e `hardware-portateis-…` |
| scroll horizontal de 32 px na home a 360 px (`span.count`) | `.section-head` quebra linha ≤560 px (CSS_PATCH); medido 0 px de overflow nas 23 páginas em 320/360/412/768 |

## Rodada 4 (02/08) — comunicação alinhada ao que o checkout cobra

| Achado | O que ficou |
|---|---|
| site vendia "12x no PIX" (R$ 20,83 / 29,16 / 37,49) e "5% off à vista", mas o checkout cobra **PIX à vista 1x** e não existe cobrança de 2ª parcela | parcelamento **removido da comunicação inteira**. `SKUS`/`dados` do `_checkpoint_loja_paginas.py` passaram a carregar o **valor cobrado**; preço em destaque = R$ 237,40 / 332,40 / 427,40, com "5% de desconto sobre R$ X de tabela" no subtítulo. `PASSOS[03]`, lead da home, meta/og de `index` e `como-funciona`, nota de Pagamento do catálogo, pull-quote e `trocas` §1/§5 reescritos; rodapé de **todas as 24 páginas** ("PIX à vista ou parcelado" → "em PIX à vista") pelo `FOOTER` do `_checkpoint_loja_v2.py`. Páginas de texto à mão: `faq` Q02 (+ meta), `termos` §2, `privacidade` §1. Varredura: 0 menção a parcela/12x/juros/cartão fora das negativas ("não parcelamos"). Conferido contra `cp_preco()` — todo R$ exibido bate com o servidor |
| `privacidade` §7 descrevia só o cookie técnico `_cid`, mas `assets/session.js` grava `cb_origin` (localStorage + cookie 1ª parte, 90 dias) com `gclid`/`gbraid`/`wbraid` + `utm_*`, e o dado viaja com o pedido | §7 vira "Cookies, origem da visita e checagem de segurança" e ganha parágrafo próprio: identificador de origem gravado no próprio domínio, 90 dias, serve para saber por qual anúncio o pedido chegou, sem nome/e-mail/pagamento e sem uso publicitário. §3 (legítimo interesse) e §5 (o provedor de pagamento recebe o código junto do pedido) alinhadas |
| QA da rodada | gerador rodado **2×**: saída byte-idêntica (idempotente); `checkout.html` e a tag do `session.js` presentes 1× por página; 24 páginas / 309 links internos / 0 quebrado / 0 `href="#"`; **0 overflow** em 360 px e 412 px (`scripts/_checkpoint_overflow_qa.py`) |

## Rodada 5 (02/08) — a vitrine deixou de ser o caixa

A rodada 4 resolveu "loja com preço e sem caixa" instalando o caixa na página que o anúncio abre.
Isso inverteu o desenho: **quem decide se alguém chega ao caixa é o roteador de visita
(`go/index.php`), não a página**. Com o checkout linkado na home, quem avalia o anúncio via a
oferta inteira de cara — e o `/go/` ficou órfão (0 de 24 páginas o acionavam).

| Achado | Causa raiz | O que ficou |
|---|---|---|
| 0 de 24 páginas acionavam `/go/`; 8 CTA iam para `checkout.html?sku=…` | os CTA foram repontados para o caixa na rodada 4 | `GO = "/go/"` + `CTA_GO = "Consultar disponibilidade"` no `_checkpoint_loja_paginas.py`; `sku_cards()` e as linhas do catálogo passaram a emitir `href="{GO}"`. **11 acionamentos em 4 páginas** (index 5, catálogo 4, como-funciona 1, contato 1) |
| preço em `index`, `catalogo` e `faq` | a vitrine se comportava como página de venda | `SKUS` e `dados` perderam os campos de valor e ganharam `meta` (prazo/entrega/cancelamento) — nenhum número de dinheiro sobrou no gerador. `.price` (CSS_INDEX e CSS_CAT) virou `.sku-meta` |
| rótulos de compra ("Reservar a Padrão", "Comprar vale-presente") | idem | rótulo único de intenção: **"Consultar disponibilidade"** |
| copy de venda ("você paga", "5% de desconto", "PIX à vista" em destaque) | idem | H1/lead da home, `PASSOS[03]` (agora "Confirmamos a reserva"), pull-quote, nota do catálogo ("Como a reserva é fechada"), meta/og de `index`/`catalogo`/`como-funciona`/`faq` reescritos |
| textos que apontavam para um preço que não existe mais na página | efeito colateral | à mão: `faq` Q01–Q05 (+ meta/og), `sobre` (§loja, princípio 01, og), `avisos` (o que vai no aviso), `termos` §2. `trocas`/`privacidade` **mantidas**: descrevem como o negócio opera, sem afirmar valor |
| a QA aprovava um site com o gate fora do caminho | a regra não existia | `_checkpoint_qa_v2.py` regra **12b**: FAIL se nenhuma página aciona `/go/`, se alguma vitrine linka `checkout.html`, ou se aparece `R$` fora do checkout. `checkout.html` entrou na lista de "deslinkadas de propósito" (regra 12) |

**Validação da rodada** (`scripts/_checkpoint_qa_v2.py`, `scripts/_checkpoint_overflow_qa.py`):

- `grep -rl "/go/" *.html` → **4 páginas** (catalogo, como-funciona, contato, index), 11 acionamentos — antes: 0
- `grep -rn "checkout.html" *.html` → **0** (nem o canonical: o arquivo saiu do docroot, ver abaixo)
- `grep -rn 'R\$' *.html` → **0**
- gerador rodado **2×**: 25 arquivos, **0 diferença de sha256** (idempotente)
- 23 páginas no docroot · 299 links internos · **0 quebrado** · 0 `href="#"` · 15/15 imagens ·
  **0 overflow** em 360 px e 412 px
- marca de terceiro (Rockstar/Take-Two/GTA/Grand Theft Auto/R★) em texto, `alt`, `title`, `meta`
  e nome de arquivo: **0**
- QA estrutural: **0 FAIL / 0 warn**
- `go/index.php` intacto — sha256 `d9abe8cdb3551b48…`; `nginx-site.conf`, `Dockerfile`, `api/`,
  `assets/`, `img/` e `checkout.html` **não modificados** (confirmado por `git status`)
