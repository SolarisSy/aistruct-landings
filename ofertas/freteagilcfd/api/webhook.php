<?php
// Zippify webhook receiver → RedTrack S2S postback
// Payload: {"hash":"v6zky9amve","payment_status":"paid","amount":6798,...}
// Looks up rtkcid in SQLite tracking table, fires GET postback to RedTrack
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, private');

$TRACKING_DOMAIN = getenv('REDTRACK_TRACKING_DOMAIN') ?: 'ohjzb.ttrk.io';
$DB_PATH         = '/var/data/rastreia.sqlite';

$raw     = file_get_contents('php://input');
$payload = $raw ? (json_decode($raw, true) ?: []) : [];
if (empty($payload) && !empty($_POST)) $payload = $_POST;

error_log('[webhook.php] raw: ' . substr($raw, 0, 400));

$hash   = (string)($payload['hash'] ?? $payload['transaction_hash'] ?? $payload['id'] ?? '');
$status = strtolower((string)($payload['payment_status'] ?? $payload['status'] ?? ''));
$amount = (int)($payload['amount'] ?? 0);

if (!$hash) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'missing_hash']);
    exit;
}

// Only process paid events (idempotent on retries)
if (!in_array($status, ['paid', 'approved', 'completed', 'confirmed'], true)) {
    echo json_encode(['ok' => true, 'skipped' => 'status:' . $status]);
    exit;
}

try {
    $db = new PDO('sqlite:' . $DB_PATH, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $st = $db->prepare("SELECT rtkcid, amount, step, postback_sent FROM tracking WHERE zip_hash = ?");
    $st->execute([$hash]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
} catch (\Throwable $e) {
    error_log('[webhook.php] db: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'db_error']);
    exit;
}

if (!$row) {
    // Organic purchase (no tracking), ignore silently
    echo json_encode(['ok' => true, 'skipped' => 'hash_not_found']);
    exit;
}

if ((int)$row['postback_sent'] > 0) {
    echo json_encode(['ok' => true, 'skipped' => 'already_sent']);
    exit;
}

// Upsells share the same clickid — re-posting would overwrite the main conversion value
// (postback_mode=update). Only attribute the main (frontend) conversion to RedTrack.
$step = (string)($row['step'] ?? 'frontend');
if ($step !== 'frontend') {
    // Mark as sent so retries don't re-evaluate
    $db->prepare("UPDATE tracking SET postback_sent = 1 WHERE zip_hash = ?")->execute([$hash]);
    echo json_encode(['ok' => true, 'skipped' => 'upsell_no_reattribute:' . $step]);
    exit;
}

$rtkcid = (string)$row['rtkcid'];

// Validate 24-hex rtkcid (RedTrack format)
if (!preg_match('/^[0-9a-fA-F]{24}$/', $rtkcid)) {
    echo json_encode(['ok' => true, 'skipped' => 'invalid_rtkcid:' . substr($rtkcid, 0, 8)]);
    exit;
}

$sum = (($amount > 0 ? $amount : (int)$row['amount']) / 100);
$postback_url = "https://{$TRACKING_DOMAIN}/postback"
    . "?clickid=" . urlencode($rtkcid)
    . "&sum=" . number_format($sum, 2, '.', '')
    . "&status=approved";

$ch = curl_init($postback_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_SSL_VERIFYPEER => true,
]);
$pbody     = curl_exec($ch);
$http_code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$ok = ($http_code >= 200 && $http_code < 300);
if ($ok) {
    $db->prepare("UPDATE tracking SET postback_sent = 1 WHERE zip_hash = ?")->execute([$hash]);
}

error_log("[webhook.php] postback rtkcid={$rtkcid} sum={$sum} http={$http_code}");

echo json_encode([
    'ok'        => $ok,
    'rtkcid'    => $rtkcid,
    'sum'       => $sum,
    'http_code' => $http_code,
]);
