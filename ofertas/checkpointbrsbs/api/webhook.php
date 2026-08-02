<?php
/**
 * POST /api/webhook.php — postback da PayShark (mudança de status da cobrança).
 *
 * PORTÃO FAIL-OPEN, de propósito: sem CHECKOUT_WEBHOOK_TOKEN no ambiente aceita
 * qualquer POST. Ligar o segredo antes de o gestor configurá-lo faria a loja
 * RECUSAR confirmação de venda real — custo maior que o abuso evitado. Com o
 * segredo setado, só passa quem o traz (?token= ou X-Token), e o postbackUrl
 * enviado na criação da cobrança já vai com ele.
 *
 * Aqui é onde a venda vira conversão. NÃO existe pixel na loja: o identificador
 * do clique volta pelo externalRef/metadata da própria transação e a conversão
 * sai daqui, do servidor, para o destino de importação offline (dormente até o
 * gestor configurar GADS_SHEET_WEBHOOK_URL).
 */
require __DIR__ . '/_cfg.php';

cp_stat('webhook_recebido');

function cp_nao_existe(): void
{
    // 404 e não 403: não confirma para quem sonda que existe validação aqui.
    http_response_code(404);
    header('Content-Type: application/json');
    echo '{"detail":"Not Found"}';
    exit;
}

$segredo = cp_env('CHECKOUT_WEBHOOK_TOKEN');
if ($segredo !== '') {
    $veio = (string) ($_GET['token'] ?? ($_SERVER['HTTP_X_TOKEN'] ?? ''));
    if (!hash_equals($segredo, $veio)) {
        cp_stat('webhook_recusado');
        cp_log('webhook recusado (segredo ausente ou errado)');
        cp_nao_existe();
    }
}

$payload = cp_body_json();
$obj = (isset($payload['data']) && is_array($payload['data'])) ? $payload['data'] : $payload;

$status   = strtolower((string) ($obj['status'] ?? ''));
$tx_id    = (string) ($obj['id'] ?? '');
$external = (string) ($obj['externalRef'] ?? '');
$centavos = (int) ($obj['amount'] ?? 0);

if (!in_array($status, ['paid', 'approved'], true)) {
    cp_stat('webhook_ignorado');
    cp_json_out(['ok' => true, 'ignorado' => $status]);
}

// Dedup: a PayShark reenvia o postback; a mesma venda não pode virar duas conversões.
$marca = cp_dir('pagos') . '/' . cp_attr_chave($tx_id !== '' ? $tx_id : $external);
if (is_file($marca)) {
    cp_stat('webhook_duplicado');
    cp_json_out(['ok' => true, 'duplicado' => true]);
}
@file_put_contents($marca, '1');
cp_stat('webhook_pago');

// ── De volta ao clique que gerou a venda ─────────────────────────────────────
// 1º o mapa em disco; 2º o metadata ecoado; 3º o próprio externalRef.
$rec        = cp_attr_le([$tx_id, $external]) ?? [];
$click_id   = (string) ($rec['click_id'] ?? '');
$click_type = (string) ($rec['click_type'] ?? '');
$utm        = is_array($rec['utm'] ?? null) ? $rec['utm'] : [];

if ($click_id === '' && isset($obj['metadata'])) {
    $meta = is_array($obj['metadata']) ? $obj['metadata'] : json_decode((string) $obj['metadata'], true);
    if (is_array($meta)) {
        $c = (string) ($meta['click'] ?? '');
        if ($c !== '' && preg_match(CP_CLICK_RE, $c)) {
            $click_id   = $c;
            $click_type = (string) ($meta['click_t'] ?? 'metadata');
            if (is_array($meta['utm'] ?? null)) {
                $utm = $meta['utm'];
            }
        }
    }
}
if ($click_id === '' && $external !== '' && strpos($external, 'cb-') !== 0
    && preg_match(CP_CLICK_RE, $external)) {
    $click_id   = $external;
    $click_type = 'externalRef';
}

$conv = [
    'quando'     => gmdate('c'),
    'hora_conv'  => cp_hora_conversao((string) ($obj['paidAt'] ?? '')),
    'click_id'   => $click_id,
    'click_type' => $click_type,
    'valor'      => round($centavos / 100, 2),
    'moeda'      => cp_env('GADS_CURRENCY', 'BRL'),
    'acao'       => cp_env('GADS_CONVERSION_NAME', 'Compra'),
    'pedido'     => $tx_id !== '' ? $tx_id : $external,
    'sku'        => (string) ($rec['sku'] ?? ''),
    'utm'        => $utm,
    'enviado'    => false,
];

if ($click_id === '') {
    cp_stat('pago_sem_clique');
    cp_log('PAGO sem identificador de clique tx=' . $tx_id . ' valor=' . $conv['valor']);
    cp_conv_grava($conv);
    cp_json_out(['ok' => true, 'atribuido' => false]);
}

cp_stat('pago_com_clique');
$conv['enviado'] = cp_envia_conversao($conv);
cp_conv_grava($conv);
cp_log(sprintf('PAGO tx=%s valor=%s %s=…%s enviado=%s', $tx_id, $conv['valor'],
               $click_type, substr($click_id, -6), $conv['enviado'] ? 'sim' : 'nao'));

cp_json_out(['ok' => true, 'atribuido' => true, 'enviado_ao_google' => $conv['enviado']]);
