<?php
/**
 * /ir/ — o passo entre o roteador e o checkout. NAO e o roteador.
 *
 * go/index.php e o integrador do servico e nao pode ser tocado. Entao a marca de
 * "este visitante passou pelo filtro" e emitida AQUI: a pagina principal do stream
 * aponta para este endereco, e so o stream conhece o segredo que abre a porta.
 *
 * Quem chega sem o segredo (ou com ele velho) e mandado para a home, sem mensagem
 * e sem pista de que exista um caminho de compra. Nao ha link para /ir/ em lugar
 * nenhum do site: nenhuma navegacao chega aqui por acidente.
 *
 * Entra:  /ir/?k=<segredo>&t=<unixtime do roteador>&gclid=...&sku=...
 * Sai:    302 -> /checkout.html?g=<token assinado>&sku=...&gclid=...
 */
require __DIR__ . '/../api/_cfg.php';
require __DIR__ . '/../api/_gate.php';

// Nunca guardar esta resposta: ela carrega um token de uso individual e o CDN
// deste dominio ignora query string ao formar a chave de cache.
header('Cache-Control: no-store');

/** Sai de cena sem revelar nada: a home, como qualquer endereco desconhecido. */
function cp_ir_home(): void
{
    header('Location: /', true, 302);
    exit;
}

// ── 1. O segredo do portao ───────────────────────────────────────────────────
$k = (string) ($_GET['k'] ?? '');
if (!cp_gate_k_ok($k)) {
    cp_ir_home();
}

// ── 2. Frescor: o roteador carimba a hora na propria URL da pagina principal.
// Endereco copiado e colado depois morre sozinho, mesmo com o segredo certo.
$t = (string) ($_GET['t'] ?? '');
if (!ctype_digit($t) || abs(time() - (int) $t) > 600) {
    cp_ir_home();
}

// ── 3. Chave de assinatura + token desta visita ──────────────────────────────
$chave = cp_gate_guarda($k);
$token = cp_gate_emite($chave);

// Se um dia o CDN parar de remover cookie, ele passa a valer tambem — sem
// depender disso hoje (medido em 02/08/2026: o CDN retira o Set-Cookie).
@setcookie('cp_g', $token, [
    'expires'  => time() + CP_GATE_TTL,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

// ── 4. O item escolhido na vitrine sobrevive ao salto ────────────────────────
// O CTA leva ?sku=<slug> para /go/, o roteador repassa (arg_passthru) e ele chega
// aqui. Conferido contra o catalogo do servidor: nada que nao esteja a venda passa.
$saida = ['g' => $token];
$sku = preg_replace('/[^a-z\-]/', '', (string) ($_GET['sku'] ?? ''));
if ($sku !== '' && isset(CP_SKUS[$sku])) {
    $saida['sku'] = $sku;
}
// Sem sku valido nao inventamos um: o checkout abre com o catalogo inteiro e o
// visitante escolhe la dentro.

// ── 5. O identificador do clique segue adiante ───────────────────────────────
// E o que liga a venda ao anuncio. Sem isto a conversao nao volta para o Google.
foreach (array_merge(CP_CLICK_KEYS, CP_UTM_KEYS) as $campo) {
    $v = (string) ($_GET[$campo] ?? '');
    if ($v !== '') {
        $saida[$campo] = cp_corta($v, 400);
    }
}

header('Location: /checkout.html?' . http_build_query($saida), true, 302);
exit;
