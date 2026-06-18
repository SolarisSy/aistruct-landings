<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$TOKEN        = getenv('ZIPPIFY_API_TOKEN') ?: 'BrUpvxkWA7CvnZj06LMhBaJO0mun6vxrQKIHBCJNz7DmpihNDIc8ddlqmM83';
$OFFER_HASH   = getenv('ZIPPIFY_OFFER_HASH_FRONTEND') ?: '5owcma4wgk';
$PRODUCT_HASH = getenv('ZIPPIFY_PRODUCT_HASH_FRONTEND') ?: 'x3xkdls0vm';

$action = $_GET['action'] ?? 'create';

// ── STATUS POLL ──────────────────────────────────────────────────────
if ($action === 'status') {
    $hash = preg_replace('/[^a-z0-9]/i', '', $_GET['hash'] ?? '');
    if (!$hash) { echo json_encode(['error' => 'hash missing']); exit; }
    $r = file_get_contents("https://api.zippify.com.br/api/public/v1/transactions/{$hash}?api_token={$TOKEN}");
    $d = $r ? json_decode($r, true) : null;
    echo json_encode(['paid' => ($d['payment_status'] ?? '') === 'paid', 'status' => $d['payment_status'] ?? 'unknown']);
    exit;
}

// ── CREATE PIX ───────────────────────────────────────────────────────
$body = json_decode(file_get_contents('php://input'), true) ?: [];

$plan  = $body['plan']  ?? 'pro';
$name  = trim($body['name']  ?? '');
$cpf   = preg_replace('/\D/', '', $body['cpf']   ?? '');
$email = trim($body['email'] ?? '');
$phone = preg_replace('/\D/', '', $body['phone']  ?? '');

if (!$name || !$cpf || !$email || !$phone) {
    http_response_code(422);
    echo json_encode(['error' => 'Preencha todos os campos.']);
    exit;
}
if (strlen($cpf) !== 11) {
    http_response_code(422);
    echo json_encode(['error' => 'CPF inválido.']);
    exit;
}

$prices = ['pro' => 990, 'business' => 2990];
$labels = ['pro' => 'RastreIA Pro — 1 mês', 'business' => 'RastreIA Business — 1 mês'];
$amount = $prices[$plan] ?? 990;
$title  = $labels[$plan] ?? 'RastreIA Pro';

$payload = json_encode([
    'amount'         => $amount,
    'offer_hash'     => $OFFER_HASH,
    'payment_method' => 'pix',
    'customer' => [
        'name'         => $name,
        'document'     => $cpf,
        'email'        => $email,
        'phone_number' => $phone,
    ],
    'cart' => [[
        'product_hash'   => $PRODUCT_HASH,
        'title'          => $title,
        'price'          => $amount,
        'quantity'       => 1,
        'operation_type' => 1,
        'tangible'       => false,
        'cover'          => null,
    ]],
    'installments' => 1,
]);

$ch = curl_init("https://api.zippify.com.br/api/public/v1/transactions?api_token={$TOKEN}");
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 25,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$d = $resp ? json_decode($resp, true) : null;

if ($d && isset($d['hash']) && $d['hash']) {
    echo json_encode([
        'ok'       => true,
        'hash'     => $d['hash'],
        'qr_img'   => $d['pix']['pix_url']      ?? null,
        'qr_code'  => $d['pix']['pix_qr_code']  ?? null,
    ]);
} else {
    http_response_code(502);
    echo json_encode(['error' => $d['message'] ?? 'Erro ao gerar PIX. Tente novamente.']);
}
