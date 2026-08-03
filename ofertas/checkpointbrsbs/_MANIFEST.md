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
| raiz = `index.php` do roteador (302, zero conteúdo) | raiz = `index.html` com a home da loja (200) — **revertido na rodada 6: a raiz voltou a ser o roteador, por decisão do gestor (método direct_url); a home continua servida em `/index.html`** |
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

## Páginas (22 HTML no docroot + 1 fora dele)

**Loja:** `index.html` (raiz) · `catalogo.html` · `como-funciona.html` · `trocas.html` · `contato.html`
**Fora do docroot:** `_priv/checkout.html` (pagamento, `noindex`) — servida por `api/pagina.php`
**Conteúdo:** `blog.html` + as 9 matérias · `glossario.html` · `avisos.html`
**Institucional/legal:** `sobre.html` · `faq.html` · `privacidade.html` · `termos.html`
**Serviço:** `404.html` (noindex). **Não existe mais `safe.html`**: era um stub que redirecionava
para `/` e, com a raiz virando roteador (rodada 6), viraria volta infinita. A página que o
roteador entrega a quem não qualifica é a própria home, por caminho direto — `/index.html`.
O nginx ainda atende `/safe.html` servindo o conteúdo de `index.html` (200), só como rede de
segurança caso o stream seja repontado para lá; não há arquivo com esse nome.
**Não-HTML:** `robots.txt` · `sitemap.xml` (**21 URLs** = 22 HTML do docroot − `404.html`,
com `index.html` listado como `/`) · `index.php` e `go/index.php` (roteador, o mesmo arquivo)

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

O integrador de roteamento (stream `78be7975-b9b6-4d56-ad9f-cd55ea682739`) — sha256
`d9abe8cd…`, **não editar** — vive em DOIS caminhos, byte a byte idênticos:
`index.php` (raiz) e `go/index.php`.

✅ **Rodada 6 (02/08/2026) — método direct_url: a URL anunciada É o roteador.**
Decisão do gestor. A raiz deixou de servir HTML: `location = /` executa `index.php`, que filtra
na ENTRADA e devolve 302 — quem qualifica vai direto para `/ir/` (marca do portão → checkout),
quem não qualifica vai para `/index.html` (a mesma vitrine, 200, por caminho direto).
Consequências:
- **não existe mais CTA que "aciona" o filtro** — quem qualifica nunca passa pela vitrine;
- os 11 CTA para `/go/` continuam no ar como **caminho alternativo** (mesmo stream, mesmo
  comportamento). Antes da aprovação (`mode=Review`) eles devolvem 302 para `/index.html`;
- a página alternativa do stream (`safe_pages`) foi repontada de `https://checkpointbr.sbs/`
  para `https://checkpointbr.sbs/index.html` — apontar para a raiz seria volta infinita;
- o `Dockerfile` **parou de apagar** `/var/www/html/index.php` (apagava o phpinfo da imagem base;
  agora esse caminho é o roteador — apagar deixaria a URL anunciada em 404);
- `absolute_redirect off; port_in_redirect off;` entraram no vhost (redirect do nginx sai
  relativo, sem host/porta da origem);
- o fix `HTTP_CF_CONNECTING_IP` vale para a raiz também: o `rewrite ^ /index.php last` reentra no
  roteamento e cai no bloco `\.php$` — medido, `CF_CONNECTING_IP` chega em GET e em POST
  (o POST importa: com `enable_fp=1` o navegador devolve a impressão digital na MESMA URL).

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

## Rodada 7 (02/08) — loja de PRODUTO FÍSICO, aberta na vitrine

O pedido do gestor era um caixa **visível**: catálogo com preço, botão de compra e PIX de verdade
para **qualquer** visitante. As rodadas 5 e 6 tinham tirado todo valor e todo botão de compra da
vitrine — o que resolvia a reprovação, mas deixou o site sem loja aparente.

Esta rodada **acrescenta** uma segunda linha de venda, aberta, sem tocar na primeira.

### O que é a linha nova

| Produto | Preço | Opção | Frete | Prazo |
|---|---|---|---|---|
| Camiseta Save Point | R$ 89,90 | Tamanho P/M/G/GG | R$ 19,90 | até 20 dias úteis |
| Caneca Continue? | R$ 59,90 | — | R$ 19,90 | até 20 dias úteis |
| Pôster A3 Fase 01 | R$ 49,90 | — | R$ 19,90 | até 20 dias úteis |

Arte **100% da própria loja** (monograma CB + tipografia e paleta do site, palavras genéricas de
videogame). Nenhuma marca, personagem ou arte de terceiro — em produto físico isso seria
falsificação, a falta mais grave possível.

**Foto de produto (03/08/2026):** cada peça tem foto de verdade em `img/produto-<sku>.jpg`
(840×840, JPEG q82) — camiseta preta lisa, caneca branca lisa e folha em branco na parede,
geradas na RTX local (ComfyUI + FLUX), com a arte da loja composta por cima (estampa achatada na
camiseta, envolvida no cilindro da caneca, em perspectiva na folha). Quem regenera é
`scripts/_cpbr_prod_arte.py`, a partir das bases em `reports/_cpbr_bases/`. O bloco `.pf-arte`
(retângulo em CSS com o monograma) **saiu**: cartão sem foto foi lido como imagem quebrada.

### Onde mora

- **Vitrine:** seção "Loja Checkpoint" em `index.html` (3 cartões com preço e botão) + link no
  rodapé de **todas** as 24 páginas.
- **Páginas:** `loja/index.html` (catálogo + ficha de cada peça) e `loja/pedido.html` (resumo do
  item + endereço + PIX). `pedido.html` é `noindex`; `/loja/` entra no sitemap.
- **Servidor:** `api/_loja.php` (preço, validação de entrega, registro mascarado, teto por origem),
  `api/_loja_tabela.php` (**gerado**), `api/loja_catalogo.php`, `api/loja_pix.php`,
  `api/loja_status.php`. Reusam `api/_cfg.php` (ambiente, máscara, mapa de origem, contadores) e o
  mesmo `api/webhook.php` — a venda vira conversão pelo mesmo caminho de sempre.
- **Vhost:** um bloco novo, `location /loja/`, no formato já usado nas outras pastas servidas
  desta operação (não depende do módulo de índice). Nenhuma regra antiga foi alterada.

### O que NÃO foi tocado

`index.php` e `go/index.php` (sha256 `d9abe8cd…`), `ir/index.php`, `_priv/checkout.html`,
`api/_gate.php`, `api/pix.php`, `api/status.php`, `api/catalogo.php`, `api/webhook.php` e o
`Dockerfile` — todos conferidos por sha256 antes e depois. A linha de venda com marca continua
exatamente como estava: `/checkout.html` sem marca → 302, `/api/catalogo.php` sem marca → 404.

### Regras que valem para a linha nova

- **Preço decidido no servidor, por SKU.** A tabela do gerador escreve, ao mesmo tempo, o HTML da
  vitrine e `api/_loja_tabela.php`. Preço enviado pelo cliente é ignorado (provado: cliente mandou
  1 centavo, servidor cobrou R$ 109,80).
- **Total = produto + frete**, os dois como linhas separadas na cobrança.
- **Dado do comprador:** o endereço completo vai no corpo da cobrança (é de lá que sai o despacho).
  O que fica gravado aqui é resumo **mascarado na escrita** — nome e rua viram iniciais, e-mail,
  CPF, telefone e CEP passam pela mesma máscara do webhook. Nenhuma rota devolve dado de comprador.
- **`/api/loja_status.php` só responde sobre cobrança desta linha** (id de fora → 404), para não
  virar consulta de status de qualquer cobrança da conta.
- **Teto de 20 pedidos por origem/hora** — a rota é aberta na internet.

### Pendente (não inventado)

O parceiro de produção/envio (impressão sob demanda) **ainda não tem credencial provisionada** e o
código de cada peça no catálogo dele não existe. Por isso `cp_loja_producao()` está **dormente**,
sem chamar endereço nenhum e sem `sku_id` inventado. Enquanto isso o despacho sai do painel do
processador de pagamento, que recebe o endereço completo. Quando a credencial existir, será preciso
decidir de onde essa função lê o endereço — o registro local é mascarado de propósito.

**Validação da rodada** (`scripts/_checkpoint_loja_fisica_qa.py`, `scripts/_checkpoint_overflow_qa.py`):

- **62/62 checks**, jornada em browser real com gateway FALSO — nenhum POST ao processador de verdade
- payload interceptado: `amount=10980` = tela; endereço completo; `gclid` em `externalRef` e em
  `metadata`; item da camiseta `tangible: true`; frete como linha própria
- registro em disco varrido: **0** ocorrência de nome, e-mail, CPF, telefone, CEP ou rua
- gerador rodado **2×** → sha256 idêntico nos 6 arquivos gerados
- **0 overflow** em 24 páginas × 360/412 px

## Rodada 8 (03/08) — os 3 bloqueadores do QA de compra real

QA com compra real (3 PIX gerados, nenhum pago) apontou três defeitos que impediam a loja física
de operar. Corrigidos **no gerador**, não no HTML.

### 1. Quem pagasse não receberia nada — promessa removida, não implementada

A tela prometia acompanhamento por e-mail. **Não existe caminho de envio de e-mail nesta oferta** —
verificado ANTES de escrever qualquer linha:

- `grep -l SMTP profiles/*.env` → **vazio**. Nenhum perfil tem segredo de SMTP (`.env.example` tem
  os campos `TYPEBOT_SMTP_*`, nenhum perfil os preenche).
- Serviço de e-mail da conta de nuvem: **`ProductionAccessEnabled=false`** (modo restrito) e
  **nenhum remetente verificado**, em `us-east-1` e `sa-east-1`. Só escreveria para endereço
  previamente cadastrado — nunca para o e-mail de um comprador qualquer.

**Decisão que o teste determinou:** não implementar e não pedir credencial. A tela passou a dizer o
que o sistema faz — guardar o número do pedido e falar com o atendimento. O ponto de envio fica
isolado e desligado em `cp_loja_aviso_comprador()` (`api/_loja.php`); ligá-lo obriga a rever, na
mesma mudança, o texto em `scripts/_checkpoint_loja_fisica.py`.

### 2. O endereço agora chega a quem despacha — sem PII em repouso

O processador de pagamento devolve `address`, `shipping` e `delivery` **vazios**: o endereço só
sobrevive dentro do `metadata` do pedido.

**Onde o operador olha:** `GET /api/loja_pedidos.php?g=<marca>` **+ cabeçalho** `X-Token: <segredo>`

Duas trancas **em série**, nesta ordem:

1. **a marca de passagem** (`cp_gate_exige()`), a mesma que `catalogo.php`, `pix.php` e
   `status.php` já exigem. Sem ela a rota não existe — venha qual segredo vier.
2. **`LOJA_PEDIDOS_TOKEN`**, segredo **próprio**, **só por cabeçalho `X-Token`**.

| chamada | devolve |
|---|---|
| sem marca de passagem | **404 seco**, mesmo com `X-Token` correto |
| com marca, sem `X-Token` / errado | **404 seco** |
| **`?token=<segredo>` na URL** | **404 seco** — esse caminho não existe mais |
| `?g=<marca>` + `X-Token` | índice **mascarado**: número, produto, valor, cidade/UF, iniciais |
| `…&completo=1` | + endereço de entrega em claro (teto de 12 leituras) |
| `…&completo=1&id=<cobrança>` | um pedido só |

**O segredo não tem substituto.** `CHECKOUT_DEBUG_TOKEN` **não abre mais esta rota** (03/08/2026):
ele foi criado para contadores de conversão **sem PII** e, como fallback, transformava um escopo de
auditoria em escopo de endereço de comprador — e como `LOJA_PEDIDOS_TOKEN` nunca foi provisionado,
era exatamente esse o estado em produção. Segredo de diagnóstico e segredo de PII não podem ser o
mesmo.

**O segredo nunca vai na URL.** `nginx-site.conf` tem `access_log /dev/stdout`: segredo em query
string vira segredo no log do container, no histórico do navegador e em qualquer intermediário.

⚠️ **A rota nasce FECHADA.** Enquanto `LOJA_PEDIDOS_TOKEN` não for configurado no serviço, ela
responde 404 para todo mundo — inclusive para o operador. Ver "Env que o gestor precisa configurar".

**Como o operador legítimo entra:** pedir `/ir/?k=<segredo do portão>&t=<unixtime>` devolve
`302 → /checkout.html?g=<marca>`; copiar o `g=` e chamar esta rota com ele **mais** o cabeçalho
`X-Token`, **do mesmo aparelho** (a marca é presa ao User-Agent) e dentro da janela de 2h. Vencida
a janela, repetir o passo emite outra marca.

**A tensão "registro nasce mascarado × operador precisa do endereço real" foi resolvida assim:** o
endereço **nunca é guardado deste lado**. O registro local continua sendo só índice mascarado; o
endereço é **lido ao vivo** do pedido no processador (`cp_loja_entrega_remota()`) no momento do
despacho e devolvido sem gravar. PII em trânsito para o operador autenticado, **zero PII em
repouso** — vazamento do disco do servidor continua não entregando comprador.

### 3. CPF inválido não passa mais

`api/_loja.php` validava só `strlen() !== 11`. CPF errado passava, a cobrança nascia e o
processador **trocava o documento em silêncio** pelo do cadastro que já tinha para aquele e-mail —
cobrança gravada no documento de outra pessoa. Agora `cp_loja_cpf_ok()` confere **os dois dígitos
verificadores** e recusa as sequências repetidas (`000…`, `111…`), que fecham a conta por
coincidência aritmética. Mesma verificação no browser, por comodidade; quem decide é o servidor.

### Menores da mesma rodada

- Cabeçalho não diz mais "chave enviada por e-mail" — passou a cobrir as duas linhas.
- Recarregar a página do PIX **não perde mais o código**: ele fica guardado no aparelho e volta
  (sem abrir cobrança nova). A validade agora é exibida a partir do `pix.expirationDate` que o
  próprio processador devolve, em vez de prazo escrito à mão.
- **Busca de CEP** preenche rua/bairro/cidade/UF. Falha em silêncio, campos seguem editáveis.

### O que NÃO foi tocado

`index.php`, `go/index.php`, `ir/index.php`, `_priv/checkout.html`, `api/_gate.php`, `api/_cfg.php`,
`api/pix.php`, `api/catalogo.php`, `api/status.php`, `api/pagina.php`, `api/webhook.php`, o
`Dockerfile`, preço, SKU e `cp_loja_item()` — todos limpos no `git status`.

### Validação da rodada

`scripts/_checkpoint_loja_correcoes_qa.py` (**12/12**) + `scripts/_checkpoint_loja_pix_ui_qa.py`
(**14/14**) + `scripts/_checkpoint_overflow_qa.py` — tudo contra gateway **FALSO**, nenhuma cobrança
real criada.

- 5 CPFs inválidos (inclusive `111.111.111-11`, o do QA) → **422, e o gateway nunca foi chamado**
  (provado pelo log do mock não registrar nada); 3 CPFs válidos → cobrança nasce.
- endereço: sem token **404** · com token **mascarado** · `completo=1` devolve o endereço inteiro ·
  registro em disco varrido: **0** ocorrência de rua ou CPF.
- reload da tela do PIX mantém código e número do pedido, com **1** cobrança só.
- gerador rodado **3×** → sha256 idêntico nos 45 arquivos.
- **0 overflow** em 24 páginas × 360/412 px, e também com a seção do PIX **aberta**.

### Escalado (fora desta correção)

A linha antiga (jogos digitais) promete **"chave/código de resgate entregue por e-mail"** em **42
ocorrências, 25 páginas** — rodapé, meta description, páginas legais e a própria página de compra.
Como não há caminho de envio de e-mail nenhum, essa promessa **também não tem lastro**. Trocar isso
é redefinir o que a linha antiga vende e como entrega: **medido e escalado, não corrigido por conta
própria.**

---

## Rodada 9 (03/08) — fechar exposição de dado de comprador em `api/loja_pedidos.php`

A rota criada na rodada 8 devolve **endereço de entrega em claro**. Revisão de código achou 3
falhas na forma como ela se protegia. Só `api/loja_pedidos.php` foi tocado.

| # | Falha | Correção | Linha |
|---|---|---|---|
| 1 | `CHECKOUT_DEBUG_TOKEN` servia de fallback — um segredo de **diagnóstico sem PII** abria endereço de comprador. E como `LOJA_PEDIDOS_TOKEN` **nunca foi provisionado**, era exatamente esse o estado em produção. | Fallback **removido**. Segredo próprio, sem substituto. Sem ele a rota fica **fechada** (404), nunca aberta por segredo de outro escopo. | 91-100 |
| 2 | Aceitava `?token=<segredo>`, e `nginx-site.conf` tem `access_log /dev/stdout` → segredo no log do container e de qualquer intermediário. | **Só `X-Token`**. O caminho por query string deixou de existir: o valor certo na URL responde 404. | 96 |
| 3 | Não chamava `cp_gate_exige()`, ao contrário das outras rotas de dados — alcançável de qualquer lugar, protegida só por uma string. | Marca de passagem **somada** ao segredo próprio, as duas em série, marca primeiro. | 83-89 |

### ⚠️ Env que o gestor precisa configurar

**A rota nasce FECHADA e continua 404 para todo mundo até isto ser feito** (inclusive para o
operador — é o preço de tirar o fallback, e é o comportamento correto).

| env | onde | para quê |
|---|---|---|
| **`LOJA_PEDIDOS_TOKEN`** | serviço `checkpointbrsbs` no Easypanel | **única** chave que abre a lista de pedidos com endereço de entrega. Valor novo e aleatório (ex.: `openssl rand -base64 24`), **diferente** de `CHECKOUT_DEBUG_TOKEN` — o motivo da mudança é justamente que os dois escopos não podem coincidir. |

Nenhuma outra env muda. `CHECKOUT_DEBUG_TOKEN` continua com o escopo dele (`/api/debug.php`,
`/api/health.php`) e **não** abre mais esta rota.

### Validação da rodada

`scripts/_checkpoint_pedidos_pii_qa.php` — **28/28**, gateway **falso** em `php -S`, dado fictício,
nenhuma cobrança criada, nenhum acesso a site ao vivo.

- **sem a env**: marca válida + token de diagnóstico válido → **404**; na URL → **404**;
  `completo=1` → **404 sem endereço no corpo**. O mesmo token **ainda abre** `/api/debug.php` (200).
- **com a env**: `X-Token` correto → **200 mascarado**; errado → 404; **segredo certo na query
  string → 404**; token de diagnóstico no cabeçalho → 404.
- **marca obrigatória**: sem marca / marca forjada / marca de **outro aparelho** → 404, mesmo com
  `X-Token` correto. `POST` → 404.
- **máscara por padrão**: resposta sem `completo=1` não traz rua nem CPF; `completo=1` com
  credencial válida devolve o endereço, e **nada foi gravado em disco** (0 ocorrência).
- **sem regressão**: vitrine aberta 200 · `loja_status.php` 200 / desconhecido 404 · linha antiga
  `catalogo.php` 404 sem marca e 200 com · `status.php` 200 · `health.php` 200/404 · `php -l` limpo
  em todos os `.php` da oferta.

### O que NÃO foi tocado

Preço, SKU, `cp_loja_item()`, `api/_gate.php` (o HMAC em si), `api/_cfg.php`, `api/_loja.php`,
`api/pix.php`, `api/catalogo.php`, `api/status.php`, `api/webhook.php`, `api/debug.php`,
`api/health.php`, `index.php` da raiz, `go/index.php`, `ir/index.php`, `_priv/`, `nginx-site.conf`,
o `Dockerfile` e o stream. `git status` da rodada: **2 arquivos** — `api/loja_pedidos.php` e este
manifesto.

O gate do método `direct_url` está intacto: `index.php` na raiz + `location = / { rewrite ^
/index.php last; }` — nenhuma linha desta rodada toca o caminho anúncio → decisão → money.
