<?php
/**
 * POST /api/loja_pix.php — abre a cobranca PIX de um produto fisico.
 *
 * Recebe: {sku, opcao?, cliente:{nome,email,cpf,telefone},
 *          entrega:{cep,rua,numero,complemento,bairro,cidade,uf},
 *          attr, clickId, clickType}
 *
 * NAO recebe preco. Se vier `amount`, `preco`, `total` ou qualquer outro numero
 * de dinheiro no corpo, ele e simplesmente ignorado: o valor sai de
 * cp_loja_item($sku), a MESMA funcao que alimenta a vitrine.
 *
 * O endereco completo vai no corpo enviado ao processador de pagamento — e de
 * la que sai o despacho. O que fica gravado deste lado e um resumo mascarado,
 * sem nome, sem documento e sem endereco identificavel.
 *
 * A origem da visita (identificador do clique + utm) viaja em externalRef e em
 * metadata, para que a venda volte ao anuncio que a gerou.
 */
require __DIR__ . '/_loja.php';

const CP_LOJA_ERRO_GATEWAY = 'Não foi possível gerar o PIX agora e nenhum valor foi cobrado. '
    . 'Tente de novo em alguns instantes ou escreva para contato@checkpointbr.sbs.';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    cp_json_out(['ok' => false, 'erro' => 'Método não permitido.'], 405);
}

$body = cp_body_json();
cp_stat('loja_pedido_recebido');

if (!cp_loja_teto_ok()) {
    cp_stat('loja_teto_atingido');
    cp_json_out(['ok' => false, 'erro' => 'Muitos pedidos abertos deste aparelho na última hora. '
        . 'Tente mais tarde ou escreva para contato@checkpointbr.sbs.'], 429);
}

// ── 1. Produto e preco: decididos no servidor ────────────────────────────────
$sku  = preg_replace('/[^a-z0-9\-]/', '', (string) ($body['sku'] ?? ''));
$item = cp_loja_item($sku);
if (!$item) {
    cp_stat('loja_sku_invalido');
    cp_json_out(['ok' => false, 'erro' => 'Escolha um produto do catálogo para continuar.'], 400);
}

$opcao = cp_loja_opcao($item, (string) ($body['opcao'] ?? ''));
if ($opcao === null) {
    cp_stat('loja_opcao_invalida');
    cp_json_out(['ok' => false, 'erro' => 'Escolha uma opção disponível para este produto.',
                 'campos' => ['opcao' => 'Escolha ' . strtolower($item['opcao_label']) . '.']], 422);
}

// ── 2. Comprador e entrega ───────────────────────────────────────────────────
list($d, $faltando) = cp_loja_valida(
    is_array($body['cliente'] ?? null) ? $body['cliente'] : [],
    is_array($body['entrega'] ?? null) ? $body['entrega'] : []
);
if ($faltando) {
    cp_stat('loja_pedido_invalido');
    cp_json_out(['ok' => false, 'erro' => 'Confira os campos destacados.',
                 'campos' => $faltando], 422);
}

// ── 3. De onde veio o comprador ──────────────────────────────────────────────
$attr = cp_limpa_attr($body['attr'] ?? []);
list($click_id, $click_type) = cp_click($attr, (string) ($body['clickId'] ?? ''),
                                        (string) ($body['clickType'] ?? ''));

$titulo     = $item['nome'] . ($opcao !== '' ? ' — ' . $item['opcao_label'] . ' ' . $opcao : '');
$pedido_ref = 'cl-' . $item['sku'] . '-' . bin2hex(random_bytes(6));
$external   = ($click_id !== '' && strlen($click_id) <= 190) ? $click_id : $pedido_ref;

// O endereco completo (inclusive bairro e complemento, que nao cabem no formato
// de endereco do gateway) segue junto do pedido para quem separa e despacha.
$metadata = [
    'loja'    => 'checkpointbr.sbs',
    'linha'   => 'produto-fisico',
    'sku'     => $item['sku'],
    'opcao'   => $opcao,
    'pedido'  => $pedido_ref,
    'click'   => $click_id,
    'click_t' => $click_type,
    'utm'     => array_intersect_key($attr, array_flip(CP_UTM_KEYS)),
    'entrega' => [
        'cep'         => $d['cep'],
        'rua'         => $d['rua'],
        'numero'      => $d['numero'],
        'complemento' => $d['complemento'],
        'bairro'      => $d['bairro'],
        'cidade'      => $d['cidade'],
        'uf'          => $d['uf'],
        'telefone'    => $d['telefone'],
        'prazo'       => CP_LOJA_PRAZO,
    ],
];

// ── 4. Cobranca ──────────────────────────────────────────────────────────────
$pk = cp_env('PAYSHARK_API_PUBLIC_KEY');
$sk = cp_env('PAYSHARK_API_SECRET_KEY');
if ($pk === '' || $sk === '') {
    cp_stat('loja_erro_sem_credencial');
    cp_log('credencial de cobranca ausente no ambiente do php-fpm (linha fisica)');
    cp_json_out(['ok' => false, 'erro' => CP_LOJA_ERRO_GATEWAY], 503);
}

$payload = [
    'amount'        => $item['total'],            // ← centavos, iguais aos exibidos
    'paymentMethod' => 'pix',
    'customer'      => [
        'name'     => $d['nome'],
        'email'    => $d['email'],
        'phone'    => $d['telefone'],
        'document' => ['type' => 'cpf', 'number' => $d['cpf']],
        // Formato de endereco aceito pelo gateway. Bairro e complemento nao tem
        // campo proprio la — por isso o endereco completo vai tambem no metadata.
        'address'  => [
            'street'  => $d['rua'],
            'number'  => $d['numero'],
            'city'    => $d['cidade'],
            'state'   => $d['uf'],
            'zipCode' => $d['cep'],
            'country' => 'BR',
        ],
    ],
    'items'         => [
        ['title' => $titulo, 'quantity' => 1, 'tangible' => true,
         'unitPrice' => $item['preco']],
        ['title' => 'Frete e manuseio', 'quantity' => 1, 'tangible' => false,
         'unitPrice' => $item['frete']],
    ],
    'externalRef'   => $external,
    'metadata'      => json_encode($metadata, JSON_UNESCAPED_UNICODE),
];

$token    = cp_env('CHECKOUT_WEBHOOK_TOKEN');
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
$resp = curl_exec($ch);
$http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$cerr = curl_error($ch);
curl_close($ch);

if ($resp === false || $http === 0) {
    cp_stat('loja_erro_rede_gateway');
    cp_log('cobranca inalcancavel: ' . substr((string) $cerr, 0, 200));
    cp_json_out(['ok' => false, 'erro' => CP_LOJA_ERRO_GATEWAY], 502);
}
$tx = json_decode((string) $resp, true);
if ($http >= 400 || !is_array($tx)) {
    cp_stat('loja_erro_gateway_' . $http);
    cp_log('cobranca ' . $http . ': ' . substr((string) $resp, 0, 300));
    cp_json_out(['ok' => false, 'erro' => CP_LOJA_ERRO_GATEWAY], 502);
}

$tx_id  = (string) ($tx['id'] ?? '');
$qrcode = (string) (($tx['pix'] ?? [])['qrcode'] ?? '');
if ($tx_id === '' || $qrcode === '') {
    cp_stat('loja_erro_gateway_sem_pix');
    cp_log('cobranca 2xx sem id/qrcode: ' . substr((string) $resp, 0, 200));
    cp_json_out(['ok' => false, 'erro' => CP_LOJA_ERRO_GATEWAY], 502);
}

// ── 5. Guardar a origem (sem dado pessoal) e o resumo do pedido (mascarado) ──
cp_attr_grava([$tx_id, $external, $pedido_ref], [
    'tx_id'      => $tx_id,
    'external'   => $external,
    'pedido'     => $pedido_ref,
    'click_id'   => $click_id,
    'click_type' => $click_type,
    'utm'        => $metadata['utm'],
    'sku'        => $item['sku'],
    'cobrado'    => $item['total'],
    'criado_em'  => gmdate('c'),
]);
cp_loja_registra($tx_id, [
    'tx_id'   => $tx_id,
    'pedido'  => $pedido_ref,
    'sku'     => $item['sku'],
    'opcao'   => $opcao,
    'produto' => $item['preco'],
    'frete'   => $item['frete'],
    'total'   => $item['total'],
    'prazo'   => CP_LOJA_PRAZO,
    'click_t' => $click_type,
], $d);

cp_stat($click_id !== '' ? 'loja_pix_com_clique' : 'loja_pix_sem_clique');
cp_stat('loja_pix_criado');
cp_log(sprintf('pedido fisico sku=%s valor=%d tx=%s clique=%s(%s)', $item['sku'],
               $item['total'], $tx_id,
               $click_id !== '' ? substr($click_id, -6) : '-', $click_type ?: '-'));

// A resposta devolve o valor cobrado para a tela do PIX repetir o MESMO numero.
cp_json_out([
    'ok'       => true,
    'id'       => $tx_id,
    'qrcode'   => $qrcode,
    'item'     => $titulo,
    'preco_br' => $item['preco_br'],
    'frete_br' => $item['frete_br'],
    'total'    => $item['total'],
    'total_br' => $item['total_br'],
    'prazo'    => CP_LOJA_PRAZO,
    'pedido'   => $pedido_ref,
]);
