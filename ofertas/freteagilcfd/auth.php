<?php
// ── RastreIA Auth API — session + SQLite ─────────────────────────────
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

ini_set('session.save_path', '/tmp/sessions');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', '604800');
session_start();

define('DB_PATH', '/var/data/rastreia.sqlite');

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
    ");
    return $pdo;
}

function ok(array $data): void { echo json_encode(array_merge(['ok' => true], $data)); exit; }
function fail(string $msg, int $status = 400): void {
    http_response_code($status);
    echo json_encode(['ok' => false, 'error' => $msg]);
    exit;
}
function auth(): array {
    if (!isset($_SESSION['uid'])) fail('Não autenticado.', 401);
    return ['id' => (int)$_SESSION['uid'], 'email' => $_SESSION['email'], 'plan' => $_SESSION['plan']];
}

$action = $_GET['action'] ?? 'check';
$b      = json_decode(file_get_contents('php://input'), true) ?? [];

// ── CHECK ─────────────────────────────────────────────────────────────
if ($action === 'check') {
    if (!isset($_SESSION['uid'])) fail('Não autenticado.', 401);
    $st = db()->prepare("SELECT plan, email_alerts, whatsapp FROM users WHERE id = ?");
    $st->execute([(int)$_SESSION['uid']]);
    $row = $st->fetch() ?: [];
    if ($row) $_SESSION['plan'] = $row['plan'];
    ok(['user' => ['id' => (int)$_SESSION['uid'], 'email' => $_SESSION['email'], 'plan' => $row['plan'] ?? $_SESSION['plan'], 'email_alerts' => $row['email_alerts'] ?? 1, 'whatsapp' => $row['whatsapp'] ?? null]]);
}

// ── REGISTER ─────────────────────────────────────────────────────────
if ($action === 'register') {
    $email = strtolower(trim($b['email'] ?? ''));
    $pass  = $b['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('E-mail inválido.');
    if (strlen($pass) < 6) fail('Senha deve ter pelo menos 6 caracteres.');

    // Check existing account with plan (from checkout)
    $st = db()->prepare("SELECT id, plan FROM users WHERE email = ?");
    $st->execute([$email]);
    $existing = $st->fetch();

    $hash = password_hash($pass, PASSWORD_BCRYPT);

    if ($existing) {
        // Update password for existing (post-checkout) account
        db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$hash, $existing['id']]);
        $id   = (int)$existing['id'];
        $plan = $existing['plan'];
    } else {
        try {
            db()->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)")->execute([$email, $hash]);
            $id   = (int)db()->lastInsertId();
            $plan = 'free';
        } catch (PDOException $e) {
            fail('Este e-mail já está em uso. Tente fazer login.');
        }
    }

    $_SESSION['uid']   = $id;
    $_SESSION['email'] = $email;
    $_SESSION['plan']  = $plan;
    ok(['user' => ['id' => $id, 'email' => $email, 'plan' => $plan]]);
}

// ── LOGIN ─────────────────────────────────────────────────────────────
if ($action === 'login') {
    $email = strtolower(trim($b['email'] ?? ''));
    $pass  = $b['password'] ?? '';
    if (!$email || !$pass) fail('Preencha e-mail e senha.');

    $st = db()->prepare("SELECT id, password_hash, plan FROM users WHERE email = ?");
    $st->execute([$email]);
    $row = $st->fetch();

    if (!$row || !password_verify($pass, $row['password_hash'])) fail('E-mail ou senha incorretos.');

    $_SESSION['uid']   = (int)$row['id'];
    $_SESSION['email'] = $email;
    $_SESSION['plan']  = $row['plan'];
    ok(['user' => ['id' => (int)$row['id'], 'email' => $email, 'plan' => $row['plan']]]);
}

// ── LOGOUT ────────────────────────────────────────────────────────────
if ($action === 'logout') {
    session_destroy();
    ok(['message' => 'Sessão encerrada.']);
}

// ── UPGRADE ──────────────────────────────────────────────────────────
// Activates plan for an email — called from obrigado.html after payment
if ($action === 'upgrade') {
    $email = strtolower(trim($b['email'] ?? $_SESSION['email'] ?? ''));
    $plan  = in_array($b['plan'] ?? '', ['pro', 'business']) ? $b['plan'] : 'pro';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('E-mail inválido.');

    $st = db()->prepare("SELECT id FROM users WHERE email = ?");
    $st->execute([$email]);
    $row = $st->fetch();

    if ($row) {
        db()->prepare("UPDATE users SET plan = ?, plan_activated_at = datetime('now') WHERE id = ?")->execute([$plan, $row['id']]);
        $id = (int)$row['id'];
    } else {
        // Pre-create account — user will set password when they register
        $tmp = password_hash(bin2hex(random_bytes(16)), PASSWORD_BCRYPT);
        db()->prepare("INSERT OR IGNORE INTO users (email, password_hash, plan, plan_activated_at) VALUES (?, ?, ?, datetime('now'))")->execute([$email, $tmp, $plan]);
        $id = (int)db()->lastInsertId();
    }

    if (isset($_SESSION['email']) && $_SESSION['email'] === $email) {
        $_SESSION['plan'] = $plan;
    }
    ok(['message' => "Plano {$plan} ativado para {$email}.", 'plan' => $plan, 'user_id' => $id]);
}

// ── SAVE CODE ─────────────────────────────────────────────────────────
if ($action === 'save_code') {
    $u     = auth();
    $code  = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $b['code'] ?? ''));
    $label = substr(trim($b['label'] ?? ''), 0, 80);
    if (strlen($code) !== 13 || !preg_match('/^[A-Z]{2}\d{9}[A-Z]{2}$/', $code)) fail('Código inválido.');

    try {
        db()->prepare("INSERT OR IGNORE INTO codes (user_id, code, label) VALUES (?, ?, ?)")->execute([$u['id'], $code, $label ?: null]);
    } catch (PDOException $e) { fail('Erro ao salvar.'); }
    ok(['message' => 'Código salvo.', 'code' => $code]);
}

// ── DELETE CODE ───────────────────────────────────────────────────────
if ($action === 'delete_code') {
    $u  = auth();
    $id = (int)($b['id'] ?? 0);
    if (!$id) fail('ID inválido.');
    db()->prepare("DELETE FROM codes WHERE id = ? AND user_id = ?")->execute([$id, $u['id']]);
    ok(['message' => 'Removido.']);
}

// ── GET CODES ─────────────────────────────────────────────────────────
if ($action === 'get_codes') {
    $u  = auth();
    $st = db()->prepare("SELECT id, code, label, last_status, last_updated, added_at FROM codes WHERE user_id = ? ORDER BY added_at DESC");
    $st->execute([$u['id']]);
    ok(['codes' => $st->fetchAll()]);
}

// ── UPDATE PREFS ───────────────────────────────────────────────────────
if ($action === 'update_prefs') {
    $u  = auth();
    $ea = isset($b['email_alerts']) ? (int)(bool)$b['email_alerts'] : null;
    $wa = isset($b['whatsapp']) ? preg_replace('/\D/', '', $b['whatsapp']) : null;
    if ($ea !== null) db()->prepare("UPDATE users SET email_alerts = ? WHERE id = ?")->execute([$ea, $u['id']]);
    if ($wa !== null) db()->prepare("UPDATE users SET whatsapp = ? WHERE id = ?")->execute([$wa ?: null, $u['id']]);
    ok(['message' => 'Preferências salvas.']);
}

// ── CHANGE PASSWORD ───────────────────────────────────────────────────
if ($action === 'change_password') {
    $u   = auth();
    $cur = $b['current'] ?? '';
    $new = $b['new']     ?? '';
    if (strlen($new) < 6) fail('Nova senha deve ter pelo menos 6 caracteres.');

    $st = db()->prepare("SELECT password_hash FROM users WHERE id = ?");
    $st->execute([$u['id']]);
    $row = $st->fetch();
    if (!$row || !password_verify($cur, $row['password_hash'])) fail('Senha atual incorreta.');

    db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_BCRYPT), $u['id']]);
    ok(['message' => 'Senha alterada.']);
}

fail('Ação inválida.', 404);
