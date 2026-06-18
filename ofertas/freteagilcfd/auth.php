<?php
// ── RastreIA Auth API — token-based (CDN-safe, sem cookies) ──────────
// Bunny CDN na frente strip Set-Cookie; usamos token no body + localStorage
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, private');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

define('DB_PATH', '/var/data/rastreia.sqlite');
define('TOKEN_TTL', 7 * 24 * 3600); // 7 days

function db(): PDO {
    static $pdo;
    if ($pdo) return $pdo;
    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("PRAGMA journal_mode=WAL; PRAGMA foreign_keys=ON;");
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT UNIQUE NOT NULL COLLATE NOCASE,
            password_hash TEXT NOT NULL,
            plan TEXT NOT NULL DEFAULT 'free',
            plan_activated_at TEXT,
            created_at TEXT NOT NULL DEFAULT (datetime('now')),
            email_alerts INTEGER NOT NULL DEFAULT 1,
            whatsapp TEXT
        );
        CREATE TABLE IF NOT EXISTS sessions (
            token TEXT PRIMARY KEY,
            user_id INTEGER NOT NULL,
            expires TEXT NOT NULL,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        CREATE TABLE IF NOT EXISTS codes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            code TEXT NOT NULL,
            label TEXT,
            last_status TEXT,
            last_updated TEXT,
            added_at TEXT NOT NULL DEFAULT (datetime('now')),
            UNIQUE(user_id, code),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        CREATE TABLE IF NOT EXISTS tracking (
            zip_hash TEXT PRIMARY KEY,
            rtkcid TEXT NOT NULL,
            amount INTEGER NOT NULL DEFAULT 0,
            step TEXT NOT NULL DEFAULT 'frontend',
            postback_sent INTEGER NOT NULL DEFAULT 0,
            created_at TEXT NOT NULL DEFAULT (datetime('now'))
        );
    ");
    return $pdo;
}

function ok(array $data): void { echo json_encode(array_merge(['ok' => true], $data)); exit; }
function fail(string $msg, int $status = 400): void {
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}

function make_token(): string { return bin2hex(random_bytes(32)); }

function get_token(): ?string {
    $h = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? '';
    if (!$h) {
        // Also accept Authorization: Bearer <token>
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($auth, 'Bearer ')) $h = substr($auth, 7);
    }
    return $h ?: null;
}

function auth(): array {
    $token = get_token();
    if (!$token) fail('Token ausente.', 401);

    $db = db();
    // Clean expired sessions
    $db->exec("DELETE FROM sessions WHERE expires < datetime('now')");

    $st = $db->prepare("SELECT s.user_id, s.expires, u.email, u.plan, u.email_alerts, u.whatsapp FROM sessions s JOIN users u ON u.id = s.user_id WHERE s.token = ?");
    $st->execute([$token]);
    $row = $st->fetch();
    if (!$row) fail('Sessão inválida ou expirada. Faça login novamente.', 401);

    return $row;
}

$action = $_GET['action'] ?? 'check';
$b      = json_decode(file_get_contents('php://input'), true) ?? [];

// ── CHECK ─────────────────────────────────────────────────────────────
if ($action === 'check') {
    $u = auth();
    ok(['user' => ['id' => (int)$u['user_id'], 'email' => $u['email'], 'plan' => $u['plan'], 'email_alerts' => (int)$u['email_alerts'], 'whatsapp' => $u['whatsapp']]]);
}

// ── REGISTER ─────────────────────────────────────────────────────────
if ($action === 'register') {
    $email = strtolower(trim($b['email'] ?? ''));
    $pass  = $b['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('E-mail inválido.');
    if (strlen($pass) < 6) fail('Senha deve ter pelo menos 6 caracteres.');

    $db = db();
    $st = $db->prepare("SELECT id, plan FROM users WHERE email = ?");
    $st->execute([$email]);
    $existing = $st->fetch();

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    if ($existing) {
        $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $existing['id']]);
        $id   = (int)$existing['id'];
        $plan = $existing['plan'];
    } else {
        try {
            $db->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)")->execute([$email, $hash]);
            $id   = (int)$db->lastInsertId();
            $plan = 'free';
        } catch (PDOException $e) {
            fail('Este e-mail já está em uso. Tente fazer login.');
        }
    }

    $token   = make_token();
    $expires = date('Y-m-d H:i:s', time() + TOKEN_TTL);
    $db->prepare("INSERT INTO sessions (token, user_id, expires) VALUES (?, ?, ?)")->execute([$token, $id, $expires]);

    ok(['token' => $token, 'user' => ['id' => $id, 'email' => $email, 'plan' => $plan]]);
}

// ── LOGIN ─────────────────────────────────────────────────────────────
if ($action === 'login') {
    $email = strtolower(trim($b['email'] ?? ''));
    $pass  = $b['password'] ?? '';
    if (!$email || !$pass) fail('Preencha e-mail e senha.');

    $db = db();
    $st = $db->prepare("SELECT id, password_hash, plan FROM users WHERE email = ?");
    $st->execute([$email]);
    $row = $st->fetch();
    if (!$row || !password_verify($pass, $row['password_hash'])) fail('E-mail ou senha incorretos.');

    $token   = make_token();
    $expires = date('Y-m-d H:i:s', time() + TOKEN_TTL);
    $db->prepare("INSERT INTO sessions (token, user_id, expires) VALUES (?, ?, ?)")->execute([$token, (int)$row['id'], $expires]);

    ok(['token' => $token, 'user' => ['id' => (int)$row['id'], 'email' => $email, 'plan' => $row['plan']]]);
}

// ── LOGOUT ────────────────────────────────────────────────────────────
if ($action === 'logout') {
    $token = get_token();
    if ($token) db()->prepare("DELETE FROM sessions WHERE token = ?")->execute([$token]);
    ok(['message' => 'Sessão encerrada.']);
}

// ── UPGRADE ──────────────────────────────────────────────────────────
if ($action === 'upgrade') {
    $email = strtolower(trim($b['email'] ?? ''));
    $plan  = in_array($b['plan'] ?? '', ['pro', 'business']) ? $b['plan'] : 'pro';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('E-mail inválido.');

    $db = db();
    $st = $db->prepare("SELECT id FROM users WHERE email = ?");
    $st->execute([$email]);
    $row = $st->fetch();

    if ($row) {
        $db->prepare("UPDATE users SET plan = ?, plan_activated_at = datetime('now') WHERE id = ?")->execute([$plan, (int)$row['id']]);
    } else {
        $tmp = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        $db->prepare("INSERT OR IGNORE INTO users (email, password_hash, plan, plan_activated_at) VALUES (?, ?, ?, datetime('now'))")->execute([$email, $tmp, $plan]);
    }
    ok(['message' => "Plano {$plan} ativado.", 'plan' => $plan]);
}

// ── SAVE CODE ─────────────────────────────────────────────────────────
if ($action === 'save_code') {
    $u    = auth();
    $code = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $b['code'] ?? ''));
    $lbl  = substr(trim($b['label'] ?? ''), 0, 80);
    if (!preg_match('/^[A-Z]{2}\d{9}[A-Z]{2}$/', $code)) fail('Código inválido.');

    try {
        db()->prepare("INSERT OR IGNORE INTO codes (user_id, code, label) VALUES (?, ?, ?)")->execute([(int)$u['user_id'], $code, $lbl ?: null]);
    } catch (PDOException $e) { fail('Erro ao salvar.'); }
    ok(['message' => 'Código salvo.', 'code' => $code]);
}

// ── DELETE CODE ───────────────────────────────────────────────────────
if ($action === 'delete_code') {
    $u  = auth();
    $id = (int)($b['id'] ?? 0);
    if (!$id) fail('ID inválido.');
    db()->prepare("DELETE FROM codes WHERE id = ? AND user_id = ?")->execute([$id, (int)$u['user_id']]);
    ok(['message' => 'Removido.']);
}

// ── GET CODES ─────────────────────────────────────────────────────────
if ($action === 'get_codes') {
    $u  = auth();
    $st = db()->prepare("SELECT id, code, label, last_status, last_updated, added_at FROM codes WHERE user_id = ? ORDER BY added_at DESC");
    $st->execute([(int)$u['user_id']]);
    ok(['codes' => $st->fetchAll()]);
}

// ── UPDATE PREFS ───────────────────────────────────────────────────────
if ($action === 'update_prefs') {
    $u  = auth();
    $ea = isset($b['email_alerts']) ? (int)(bool)$b['email_alerts'] : null;
    $wa = isset($b['whatsapp']) ? preg_replace('/\D/', '', $b['whatsapp']) : null;
    if ($ea !== null) db()->prepare("UPDATE users SET email_alerts = ? WHERE id = ?")->execute([$ea, (int)$u['user_id']]);
    if ($wa !== null) db()->prepare("UPDATE users SET whatsapp = ? WHERE id = ?")->execute([$wa ?: null, (int)$u['user_id']]);
    ok(['message' => 'Preferências salvas.']);
}

// ── CHANGE PASSWORD ───────────────────────────────────────────────────
if ($action === 'change_password') {
    $u   = auth();
    $cur = $b['current'] ?? '';
    $new = $b['new']     ?? '';
    if (strlen($new) < 6) fail('Nova senha deve ter pelo menos 6 caracteres.');

    $st = db()->prepare("SELECT password_hash FROM users WHERE id = ?");
    $st->execute([(int)$u['user_id']]);
    $row = $st->fetch();
    if (!$row || !password_verify($cur, $row['password_hash'])) fail('Senha atual incorreta.');

    db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_BCRYPT), (int)$u['user_id']]);
    ok(['message' => 'Senha alterada.']);
}

fail('Ação inválida.', 404);
