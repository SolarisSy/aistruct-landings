<?php
require_once __DIR__ . '/_zippify.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_err('Method not allowed', 405);
}

$body = read_body();
if (!empty($body['website'])) json_ok([]);

$cpf   = trim($body['cpf'] ?? '');
$nome  = trim($body['nome'] ?? '');
$email = trim($body['email'] ?? '');
$fone  = preg_replace('/\D/', '', $body['telefone'] ?? '');

if (!$cpf || !$email) {
    json_err('Dados obrigatórios ausentes');
}

$cfg = cfg();
$amount = (int)($cfg['up1_cents'] ?? 2795);
$fmt    = $cfg['up1_fmt']    ?? 'R$ 27,95';

$customer = ['name' => $nome ?: 'Prezado', 'document' => $cpf, 'email' => $email, 'phone' => $fone];
$resp = zip_create_pix($amount, 'ZIPPIFY_OFFER_HASH_UP1', 'ZIPPIFY_PRODUCT_HASH_UP1', $customer, $cfg['up1_nome'] ?? 'Proteção de Rastreio');

if (!$resp || empty($resp['hash'])) {
    error_log('[up1-pix.php] Zippify error: ' . json_encode($resp));
    json_err('Erro ao gerar PIX. Tente novamente.', 500);
}

json_ok(build_pix_response($resp, $amount, $fmt, $customer));
