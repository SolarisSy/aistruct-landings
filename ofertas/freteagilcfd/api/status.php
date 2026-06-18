<?php
require_once __DIR__ . '/_zippify.php';

header('Content-Type: application/json; charset=utf-8');

$hash = trim($_GET['hash'] ?? '');
if (!$hash) {
    json_err('hash obrigatório');
}

$resp = zip_status($hash);
if (!$resp) {
    json_err('Erro ao consultar status', 500);
}

$d      = $resp['data'] ?? [];
$status = (string)($d['status'] ?? 'pending');
$paid   = is_paid_status($status);

json_ok([
    'is_paid'          => $paid,
    'status'           => $paid ? 'paid' : $status,
    'transaction_hash' => $hash,
    'hash'             => $hash,
    'pix_code'         => $d['pix']['pix_qr_code'] ?? '',
    'amount_formatted' => '',
]);
