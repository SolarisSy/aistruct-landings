<?php
/**
 * GET /api/debug.php?token=... — auditoria da marcação.
 *
 * FAIL-CLOSED: sem CHECKOUT_DEBUG_TOKEN no ambiente ninguém entra, nem com token
 * vazio. Esta rota fica exposta na internet junto com a loja, então "só quem sabe
 * a URL" não é proteção.
 *
 * NÃO devolve dado de comprador — nome, e-mail e CPF nunca são gravados em disco
 * (ver _cfg.php). Do identificador do clique sai só a cauda: dá para conferir que
 * a ponta fechou sem expor o identificador inteiro.
 */
require __DIR__ . '/_cfg.php';

$segredo = cp_env('CHECKOUT_DEBUG_TOKEN');
$veio    = (string) ($_GET['token'] ?? '');
if ($segredo === '' || !hash_equals($segredo, $veio)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo '{"detail":"Not Found"}';
    exit;
}

$itens = [];
foreach (cp_conversoes(50) as $c) {
    $itens[] = [
        'quando'     => $c['quando'] ?? '',
        'hora_conv'  => $c['hora_conv'] ?? '',
        'click_type' => $c['click_type'] ?? '',
        'click_tail' => substr((string) ($c['click_id'] ?? ''), -8),
        'valor'      => $c['valor'] ?? 0,
        'sku'        => $c['sku'] ?? '',
        'pedido'     => $c['pedido'] ?? '',
        'enviado'    => (bool) ($c['enviado'] ?? false),
        'utm_source' => ($c['utm'] ?? [])['utm_source'] ?? null,
        'utm_campaign' => ($c['utm'] ?? [])['utm_campaign'] ?? null,
    ];
}

cp_json_out([
    'sink_ligado' => cp_env('GADS_SHEET_WEBHOOK_URL') !== '',
    'stats'       => cp_stats(),
    'conversoes'  => $itens,
]);
