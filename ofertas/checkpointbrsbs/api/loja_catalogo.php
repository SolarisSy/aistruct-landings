<?php
/**
 * GET /api/loja_catalogo.php[?sku=...] — o que a linha de produtos fisicos vende.
 *
 * Existe para que o valor MOSTRADO ao visitante saia da mesma funcao que a
 * cobranca usa, e nao de um numero escrito no HTML. Mudou aqui, mudou na
 * vitrine e na cobranca no mesmo instante.
 *
 * Aberta de proposito: e a vitrine. Nao recebe nem devolve nada do comprador.
 */
require __DIR__ . '/_loja.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    cp_json_out(['ok' => false, 'erro' => 'Método não permitido.'], 405);
}

$sku   = preg_replace('/[^a-z0-9\-]/', '', (string) ($_GET['sku'] ?? ''));
$itens = cp_loja_catalogo();

if ($sku !== '') {
    $um = cp_loja_item($sku);
    if (!$um) {
        cp_json_out(['ok' => false, 'erro' => 'Produto não encontrado no catálogo.'], 404);
    }
    $itens = [$um];
}

cp_json_out([
    'ok'        => true,
    'pagamento' => 'PIX à vista',
    'frete'     => CP_LOJA_FRETE,
    'frete_br'  => cp_centavos_br(CP_LOJA_FRETE),
    'prazo'     => CP_LOJA_PRAZO,
    'itens'     => $itens,
]);
