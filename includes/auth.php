<?php
/* Shared authentication + helper functions.
   Include this FIRST on every page, before any output is sent. */

if (!defined('BASE_URL')) {
    // Auto-detects the web path of the site folder, e.g. "/mendnest" (or "" at the domain root).
    // If redirects land on the wrong URL, replace this block with:  define('BASE_URL', '/your-folder');
    $docRoot  = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    $siteRoot = realpath(dirname(__DIR__));
    $base = '';
    if ($docRoot && $siteRoot && strpos($siteRoot, $docRoot) === 0) {
        $base = str_replace('\\', '/', substr($siteRoot, strlen($docRoot)));
    }
    define('BASE_URL', rtrim($base, '/'));
    unset($docRoot, $siteRoot, $base);
}

const IDLE_TIMEOUT = 1800;   // sign out after 30 minutes of inactivity
const ROLE_HOME = [
    'admin'   => BASE_URL . '/admin/dashboard.php',
    'patient' => BASE_URL . '/patient/dashboard.php',
];
const LOGIN_URL = BASE_URL . '/login.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ---------- small helpers ---------- */
function h($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(): bool {
    return hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''));
}

function flash(string $type, string $msg): void {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flash_render(): string {
    $out = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $out .= '<div class="alert alert-' . h($f['type']) . '">' . h($f['msg']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $out;
}

/* ---------- authentication ---------- */
function current_user(): ?array {
    return $_SESSION['auth'] ?? null;
}

function redirect_for_role(string $role): void {
    redirect(ROLE_HOME[$role] ?? LOGIN_URL);
}

function login_user(array $row): void {
    session_regenerate_id(true);                       // prevents session fixation
    unset($_SESSION['admin'], $_SESSION['patient'], $_SESSION['user'], $_SESSION['role']); // old formats
    $_SESSION['auth'] = [
        'id'    => (int)$row['id'],
        'name'  => (string)$row['name'],
        'email' => (string)$row['email'],
        'role'  => (string)$row['role'],
    ];
    $_SESSION['last_active'] = time();
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* Top of any page that needs a signed-in user. */
function require_login(): array {
    header('Cache-Control: no-store, no-cache, must-revalidate');   // no back-button access after logout
    header('Pragma: no-cache');

    $user = current_user();
    if (!$user) {
        redirect(LOGIN_URL);
    }
    if (time() - ($_SESSION['last_active'] ?? 0) > IDLE_TIMEOUT) {
        logout_user();
        redirect(LOGIN_URL . '?timeout=1');
    }
    $_SESSION['last_active'] = time();
    return $user;
}

/* Top of role-restricted pages:  $admin = require_role('admin');
   Signed-in users with the wrong role are sent to their own area. */
function require_role(string ...$roles): array {
    $user = require_login();
    if (!in_array($user['role'], $roles, true)) {
        redirect_for_role($user['role']);
    }
    return $user;
}