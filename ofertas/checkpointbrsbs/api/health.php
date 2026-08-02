<?php
/**
 * GET /api/health.php — o checkout está de pé e configurado?
 *
 * Só BOOLEANOS: nunca imprime valor de chave, de env nem de pedido. Existe para
 * o gestor confirmar, DEPOIS de salvar as variáveis no painel, que o php-fpm
 * realmente enxerga as credenciais — se elas não chegam ao PHP, o checkout falha
 * em 100% dos pedidos e este é o jeito de ver isso em um request.
 */
require __DIR__ . '/_cfg.php';
require __DIR__ . '/_gate.php';

// Este diagnostico dizia publicamente qual gateway a loja usa e onde fica o
// postback — para qualquer um que pedisse. Agora responde a quem passou pelo
// portao ou a quem traz o token de auditoria (o mesmo do /api/debug.php), que
// e como o gestor confere a configuracao depois de salvar as variaveis.
$cp_diag = cp_env('CHECKOUT_DEBUG_TOKEN');
if (!cp_gate_ok()
    && !($cp_diag !== '' && hash_equals($cp_diag, (string) ($_GET['token'] ?? '')))) {
    http_response_code(404);
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    echo '{"detail":"Not Found"}';
    exit;
}

cp_json_out([
    'ok'  => true,
    'cfg' => [
        'payshark_pk'    => cp_env('PAYSHARK_API_PUBLIC_KEY') !== '',
        'payshark_sk'    => cp_env('PAYSHARK_API_SECRET_KEY') !== '',
        'webhook_token'  => cp_env('CHECKOUT_WEBHOOK_TOKEN') !== '',   // false = fail-open
        'debug_token'    => cp_env('CHECKOUT_DEBUG_TOKEN') !== '',     // false = /debug fechado
        'sink_conversao' => cp_env('GADS_SHEET_WEBHOOK_URL') !== '',   // false = dormente
        'estado_gravavel' => is_writable(cp_dir()),
    ],
    'gateway'  => cp_api_base(),
    'postback' => cp_self_base() . '/api/webhook.php',
]);
