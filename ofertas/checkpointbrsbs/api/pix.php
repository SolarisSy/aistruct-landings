<?php
/**
 * POST /api/pix.php — cria a cobrança PIX do pedido na PayShark.
 *
 * Recebe do checkout: {sku, variante?, customer:{nome,email,cpf?}, attr, clickId, clickType}
 * NÃO recebe preço. O valor sai de cp_preco($sku, $variante), a MESMA função que
 * alimenta a tela — é isso que garante que o cobrado é o exibido.
 *
 * Campos pedidos ao comprador = o mínimo que o gateway exige. Medido contra a API
 * em 02/08/2026 (POST /v1/transactions sem `amount`, que não cria cobrança):
 *   customer.name  → "O nome do cliente é obrigatório."
 *   customer.email → "O e-mail do cliente é obrigatório."
 *   document/phone/birthdate/address → NÃO reclamados.
 * Por isso o formulário pede nome e e-mail; o CPF é opcional e só é enviado quando
 * o comprador preenche.
 *
 * ATRIBUIÇÃO: o identificador do clique vai em `externalRef` E em `metadata` —
 * a PayShark devolve os dois no postback, então a venda volta ao clique mesmo
 * depois de um redeploy (o mapa em disco é só a rede de segurança).
 * A loja NÃO tem pixel nem tag do Google: a conversão é enviada pelo servidor.
 */
require __DIR__ . '/_cfg.php';

const CP_ERRO_GATEWAY = 'Não foi possível gerar o PIX agora e nenhum valor foi cobrado. '
    . 'Tente de novo em alguns instantes ou escreva para contato@checkpointbr.sbs.';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    cp_json_out(['ok' => false, 'erro' => 'Método não permitido.'], 405);
}

$body = cp_body_json();
cp_stat('pedido_recebido');

// ── 1. Item e preço: decididos no servidor ───────────────────────────────────
$sku      = preg_replace('/[^a-z\-]/', '', (string) ($body['sku'] ?? ''));
$variante = isset($body['variante']) ? (int) $body['variante'] : null;
$preco    = cp_preco($sku, $variante);
if (!$preco) {
    cp_stat('pedido_sku_invalido');
    cp_json_out(['ok' => false, 'erro' => 'Escolha um item do catálogo para continuar.'], 400);
}

// ── 2. Comprador: só o que o gateway exige ───────────────────────────────────
$c     = is_array($body['customer'] ?? null) ? $body['customer'] : [];
$nome  = cp_corta(trim((string) ($c['nome'] ?? $c['name'] ?? '')), 120);
$email = cp_corta(trim((string) ($c['email'] ?? '')), 160);
$cpf   = preg_replace('/\D/', '', (string) ($c['cpf'] ?? ''));

$faltando = [];
if (cp_tamanho($nome) < 3) {
    $faltando['nome'] = 'Escreva seu nome completo.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $faltando['email'] = 'Escreva um e-mail válido — é nele que a chave chega.';
}
if ($cpf !== '' && strlen($cpf) !== 11) {
    $faltando['cpf'] = 'O CPF precisa ter 11 dígitos (ou deixe em branco).';
}
if ($faltando) {
    cp_stat('pedido_invalido');
    cp_json_out(['ok' => false, 'erro' => 'Confira os campos destacados.', 'campos' => $faltando], 422);
}

// ── 3. De onde veio o comprador ──────────────────────────────────────────────
$attr = cp_limpa_attr($body['attr'] ?? []);
list($click_id, $click_type) = cp_click($attr, (string) ($body['clickId'] ?? ''),
                                        (string) ($body['clickType'] ?? ''));

$pedido_ref = 'cb-' . $preco['sku'] . '-' . bin2hex(random_bytes(6));
$external   = ($click_id !== '' && strlen($click_id) <= 190) ? $click_id : $pedido_ref;

$metadata = [
    'loja'     => 'checkpointbr.sbs',
    'sku'      => $preco['sku'],
    'variante' => $preco['variante'],
    'pedido'   => $pedido_ref,
    'click'    => $click_id,
    'click_t'  => $click_type,
    'utm'      => array_intersect_key($attr, array_flip(CP_UTM_KEYS)),
];

// ── 4. Cobrança ──────────────────────────────────────────────────────────────
$pk = cp_env('PAYSHARK_API_PUBLIC_KEY');
$sk = cp_env('PAYSHARK_API_SECRET_KEY');
if ($pk === '' || $sk === '') {
    cp_stat('erro_sem_credencial');
    cp_log('PAYSHARK_API_PUBLIC_KEY/SECRET ausentes no ambiente do php-fpm');
    cp_json_out(['ok' => false, 'erro' => CP_ERRO_GATEWAY], 503);
}

$customer = ['name' => $nome, 'email' => $email];
if ($cpf !== '') {
    $customer['document'] = ['type' => 'cpf', 'number' => $cpf];
}

$payload = [
    'amount'        => $preco['cobrado'],          // ← centavos, iguais aos exibidos
    'paymentMethod' => 'pix',
    'customer'      => $customer,
    'items'         => [[
        'title'     => $preco['titulo_pedido'],
        'quantity'  => 1,
        'tangible'  => false,
        'unitPrice' => $preco['cobrado'],
    ]],
    'externalRef'   => $external,
    'metadata'      => json_encode($metadata, JSON_UNESCAPED_UNICODE),
];

$token = cp_env('CHECKOUT_WEBHOOK_TOKEN');
$postback = cp_self_base() . '/api/webhook.php';
if ($token !== '') {
    $postback .= '?token=' . rawurlencode($token);
}
$payload['postbackUrl'] = $postback;

$ch = curl_init(cp_api_base() . '/v1/transactions');
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Basic ' . base64_encode($pk . ':' . $sk),
        'Content-Type: application/json',
        'Accept: application/json',
    ],
]);
$resp   = curl_exec($ch);
$http   = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$cerr   = curl_error($ch);
curl_close($ch);

if ($resp === false || $http === 0) {
    cp_stat('erro_rede_gateway');
    cp_log('gateway inalcancavel: ' . substr((string) $cerr, 0, 200));
    cp_json_out(['ok' => false, 'erro' => CP_ERRO_GATEWAY], 502);
}
$tx = json_decode((string) $resp, true);
if ($http >= 400 || !is_array($tx)) {
    cp_stat('erro_gateway_' . $http);
    cp_log('gateway ' . $http . ': ' . substr((string) $resp, 0, 300));
    cp_json_out(['ok' => false, 'erro' => CP_ERRO_GATEWAY], 502);
}

$tx_id  = (string) ($tx['id'] ?? '');
$qrcode = (string) (($tx['pix'] ?? [])['qrcode'] ?? '');
if ($tx_id === '' || $qrcode === '') {
    cp_stat('erro_gateway_sem_pix');
    cp_log('gateway 2xx sem id/qrcode: ' . substr((string) $resp, 0, 200));
    cp_json_out(['ok' => false, 'erro' => CP_ERRO_GATEWAY], 502);
}

// ── 5. Guardar a origem (sem dado pessoal) ───────────────────────────────────
cp_attr_grava([$tx_id, $external, $pedido_ref], [
    'tx_id'      => $tx_id,
    'external'   => $external,
    'pedido'     => $pedido_ref,
    'click_id'   => $click_id,
    'click_type' => $click_type,
    'utm'        => $metadata['utm'],
    'sku'        => $preco['sku'],
    'cobrado'    => $preco['cobrado'],
    'criado_em'  => gmdate('c'),
]);
cp_stat($click_id !== '' ? 'pix_com_clique' : 'pix_sem_clique');
cp_stat('pix_criado');
cp_log(sprintf('pix criado sku=%s valor=%d tx=%s clique=%s(%s)', $preco['sku'],
               $preco['cobrado'], $tx_id,
               $click_id !== '' ? substr($click_id, -6) : '-', $click_type ?: '-'));

// A resposta devolve o valor cobrado para a tela do PIX repetir o MESMO número —
// o comprador confere na mesma página que gerou a cobrança.
cp_json_out([
    'ok'         => true,
    'id'         => $tx_id,
    'qrcode'     => $qrcode,
    'item'       => $preco['titulo_pedido'],
    'entrega'    => $preco['entrega'],
    'cobrado'    => $preco['cobrado'],
    'cobrado_br' => $preco['cobrado_br'],
    'tabela_br'  => $preco['tabela_br'],
    'desconto_pct' => $preco['desconto_pct'],
    'pedido'     => $pedido_ref,
]);
