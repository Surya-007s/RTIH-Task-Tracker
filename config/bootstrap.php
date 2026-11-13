<?php
declare(strict_types=1);

// Keep runtime errors out of the page; use the PHP/web-server error log for diagnosis.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$settingsFile = __DIR__ . '/database.php';
if (!is_file($settingsFile)) {
    http_response_code(500);
    exit('Database configuration is missing. Copy config/database.example.php to config/database.php and update it.');
}
$settings = require $settingsFile;
$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $settings['host'], $settings['port'], $settings['database'], $settings['charset']);
try {
    $pdo = new PDO($dsn, $settings['username'], $settings['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $error) {
    error_log('Database connection failed: ' . $error->getMessage());
    http_response_code(500);
    exit('The application could not connect to its database. Check the local setup instructions and server log.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('This form expired or could not be verified. Go back, reload the page, and try again.');
    }
}

function require_login(): int
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
    return (int) $_SESSION['user_id'];
}

function flash(string $kind, string $message): void
{
    $_SESSION['flash'] = ['kind' => $kind, 'message' => $message];
}

function take_flash(): ?array
{
    $message = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $message;
}

function valid_date(string $date): bool
{
    $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}
