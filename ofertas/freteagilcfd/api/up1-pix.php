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
$rtkcid = preg_replace('/[^0-9a-fA-F]/', '', $body['rtkcid'] ?? '');

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

if ($rtkcid && strlen($rtkcid) === 24) {
    try {
        $db = new PDO('sqlite:/var/data/rastreia.sqlite', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $db->exec("CREATE TABLE IF NOT EXISTS tracking (zip_hash TEXT PRIMARY KEY, rtkcid TEXT NOT NULL, amount INTEGER NOT NULL DEFAULT 0, step TEXT NOT NULL DEFAULT 'frontend', postback_sent INTEGER NOT NULL DEFAULT 0, created_at TEXT NOT NULL DEFAULT (datetime('now')));");
        $db->prepare("INSERT OR IGNORE INTO tracking (zip_hash, rtkcid, amount, step) VALUES (?, ?, ?, 'up1')")
            ->execute([$resp['hash'], $rtkcid, $amount]);
    } catch (\Throwable $e) {
        error_log('[up1-pix.php] tracking: ' . $e->getMessage());
    }
}

json_ok(build_pix_response($resp, $amount, $fmt, $customer));
