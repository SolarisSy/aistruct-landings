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
