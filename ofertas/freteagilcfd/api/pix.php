<?php
require_once __DIR__ . '/_zippify.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_err('Method not allowed', 405);
}

$body = read_body();

// Honeypot
if (!empty($body['website'])) {
    json_ok(build_pix_response([], 0, '', [])); // silently discard
}

$cpf   = trim($body['cpf'] ?? '');
$nome  = trim($body['nome'] ?? '');
$email = trim($body['email'] ?? '');
$fone  = preg_replace('/\D/', '', $body['telefone'] ?? '');

if (!$cpf || !$email) {
    json_err('Dados obrigatórios ausentes');
}

$cfg = cfg();
$amount = (int)($cfg['taxa_cents'] ?? 6798);
$fmt    = $cfg['taxa_fmt']   ?? 'R$ 67,98';

$customer = ['name' => $nome ?: 'Prezado', 'document' => $cpf, 'email' => $email, 'phone' => $fone];
$resp = zip_create_pix($amount, 'ZIPPIFY_OFFER_HASH_FRONTEND', 'ZIPPIFY_PRODUCT_HASH_FRONTEND', $customer, $cfg['taxa_nome'] ?? 'Taxa de Liberação');

if (!$resp || empty($resp['hash'])) {
    error_log('[pix.php] Zippify error: ' . json_encode($resp));
    json_err('Erro ao gerar PIX. Tente novamente.', 500);
}

json_ok(build_pix_response($resp, $amount, $fmt, $customer));
