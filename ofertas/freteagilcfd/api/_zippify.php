<?php
// Zippify helper — not web-accessible (blocked in nginx)
// Env vars needed: ZIPPIFY_API_TOKEN, ZIPPIFY_OFFER_HASH, ZIPPIFY_PRODUCT_HASH_*

function _zip_post(array $payload): ?array {
    $token = getenv('ZIPPIFY_API_TOKEN');
    if (!$token) return null;

    $ch = curl_init('https://api.zippify.com.br/api/public/v1/transactions?api_token=' . urlencode($token));
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 25,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    return $body ? json_decode($body, true) : null;
}

function _zip_get(string $tx_id): ?array {
    $token = getenv('ZIPPIFY_API_TOKEN');
    if (!$token) return null;

    $ch = curl_init('https://api.zippify.com.br/api/public/v1/transactions/' . urlencode($tx_id) . '?api_token=' . urlencode($token));
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    curl_close($ch);
    return $body ? json_decode($body, true) : null;
}

function zip_create_pix(int $amount_cents, string $offer_hash_env_key, string $product_hash_env_key, array $customer, string $title = 'Produto'): ?array {
    $offer_hash   = getenv($offer_hash_env_key);
    $product_hash = getenv($product_hash_env_key);
    if (!$offer_hash || !$product_hash) return null;

    return _zip_post([
        'amount'         => $amount_cents,
        'offer_hash'     => $offer_hash,
        'payment_method' => 'pix',
        'customer'       => [
            'name'         => $customer['name'],
            'document'     => preg_replace('/\D/', '', $customer['document']),
            'email'        => $customer['email'],
            'phone_number' => preg_replace('/\D/', '', $customer['phone']),
        ],
        'cart' => [[
            'product_hash'   => $product_hash,
            'title'          => $title,
            'price'          => $amount_cents,
            'quantity'       => 1,
            'operation_type' => 1,
            'tangible'       => false,
            'cover'          => null,
        ]],
        'installments' => 1,
    ]);
}

function zip_status(string $tx_id): ?array {
    return _zip_get($tx_id);
}

function cfg(): array {
    static $c = null;
    if ($c === null) {
        $path = __DIR__ . '/config.json';
        $c = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
    }
    return $c ?: [];
}

function json_ok(array $data): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => true, 'data' => $data]);
    exit;
}

function json_err(string $msg, int $code = 400): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

function read_body(): array {
    static $b = null;
    if ($b === null) {
        $raw = file_get_contents('php://input');
        $b = $raw ? (json_decode($raw, true) ?: []) : [];
        // fallback to POST
        if (empty($b) && !empty($_POST)) $b = $_POST;
    }
    return $b;
}

function build_pix_response(array $zip_resp, int $amount_cents, string $amount_fmt, array $customer): array {
    // Zippify response: {hash, pix: {pix_qr_code}, payment_status, ...} — sem wrapper data
    return [
        'transaction_hash' => (string)($zip_resp['hash'] ?? ''),
        'pix_code'         => $zip_resp['pix']['pix_qr_code'] ?? '',
        'pix_qrcode'       => $zip_resp['pix']['pix_qr_code'] ?? '',
        'amount'           => $amount_cents,
        'amount_formatted' => $amount_fmt,
        'expires_at'       => time() + 3600,
        'status'           => 'pending',
        'receiver'         => ['name' => '', 'document' => ''],
        'customer'         => $customer,
    ];
}

function is_paid_status(string $status): bool {
    return in_array(strtolower($status), ['paid', 'approved', 'completed', 'confirmed'], true);
}
