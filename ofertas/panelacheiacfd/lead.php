<?php
// Recebe o cadastro da newsletter do Panela Cheia e encaminha server-side pro coletor.
// Fica no proprio dominio de proposito: o browser nunca fala com host de terceiro.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['ok' => false, 'erro' => 'metodo']));
}

$raw = file_get_contents('php://input');
$in = json_decode($raw, true);
if (!is_array($in)) { $in = $_POST; }

$email = trim((string)($in['email'] ?? ''));
$origem = substr((string)($in['origem'] ?? ''), 0, 80);

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    http_response_code(422);
    exit(json_encode(['ok' => false, 'erro' => 'email']));
}

$ip = '';
foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $k) {
    if (!empty($_SERVER[$k])) { $ip = explode(',', $_SERVER[$k])[0]; break; }
}

$payload = json_encode([
    'site'   => 'panelacheia.cfd',
    'email'  => strtolower($email),
    'origem' => $origem,
    'ip'     => $ip,
]);

$endpoint = getenv('LEADBOX_URL') ?: 'https://leadbox.tiectu.easypanel.host/lead';
$token    = getenv('LEADBOX_TOKEN') ?: 'ei2A4LMbss-HcTePUxR1PnFa38KS_2-q';

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'X-Leadbox-Token: ' . $token],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 8,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// backup local (o container e efemero, mas evita perder lead se o coletor cair)
@file_put_contents(__DIR__ . '/leads.log',
    date('c') . "\t" . strtolower($email) . "\t" . $origem . "\t" . $code . "\n",
    FILE_APPEND | LOCK_EX);

if ($code >= 200 && $code < 300) {
    exit(json_encode(['ok' => true]));
}
http_response_code(502);
echo json_encode(['ok' => false, 'erro' => 'coletor']);
