<?php
require_once __DIR__ . '/_zippify.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_err('Method not allowed', 405);
}

$body = read_body();
if (!empty($body['website'])) json_ok([]);

$cpf    = trim($body['cpf'] ?? '');
$nome   = trim($body['nome'] ?? '');
$email  = trim($body['email'] ?? '');
$fone   = preg_replace('/\D/', '', $body['telefone'] ?? '');
$lote   = (int)($body['lote_id'] ?? 1);

if (!$cpf || !$email) {
    json_err('Dados obrigatórios ausentes');
}
if (!in_array($lote, [1, 2, 3], true)) {
    json_err('Lote inválido');
}

$cfg = cfg();
$amount_map = [
    1 => [(int)($cfg['lote1_cents'] ?? 7834), $cfg['lote1_fmt'] ?? 'R$ 78,34', 'ZIPPIFY_PRODUCT_HASH_LOTE1'],
    2 => [(int)($cfg['lote2_cents'] ?? 12834), $cfg['lote2_fmt'] ?? 'R$ 128,34', 'ZIPPIFY_PRODUCT_HASH_LOTE2'],
    3 => [(int)($cfg['lote3_cents'] ?? 4519), $cfg['lote3_fmt'] ?? 'R$ 45,19', 'ZIPPIFY_PRODUCT_HASH_LOTE3'],
];
[$amount, $fmt, $hash_env] = $amount_map[$lote];

$customer = ['name' => $nome ?: 'Prezado', 'document' => $cpf, 'email' => $email, 'phone' => $fone];
$resp = zip_create_pix($amount, $hash_env, $customer);

if (!$resp || empty($resp['data']['id'])) {
    error_log('[lote-pix.php] Zippify error lote=' . $lote . ': ' . json_encode($resp));
    json_err('Erro ao gerar PIX. Tente novamente.', 500);
}

json_ok(build_pix_response($resp, $amount, $fmt, $customer));
