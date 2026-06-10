<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';

function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) { return; }
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function current_admin(): ?array
{
    secure_session_start();
    return $_SESSION['admin'] ?? null;
}

function require_admin(): void
{
    secure_session_start();
    if (empty($_SESSION['admin'])) {
        redirect('/admin/login.php');
    }
}

function login_admin(string $email, string $password): bool
{
    secure_session_start();
    $admins = read_json('admins.json', []);
    foreach ($admins as $admin) {
        if (strcasecmp((string)($admin['email'] ?? ''), $email) === 0 && password_verify($password, (string)($admin['password'] ?? ''))) {
            session_regenerate_id(true);
            $_SESSION['admin'] = ['email' => $admin['email'], 'name' => $admin['name'] ?? 'مدیر سایت'];
            return true;
        }
    }
    return false;
}

function logout_admin(): void
{
    secure_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
