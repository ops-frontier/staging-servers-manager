<?php
declare(strict_types=1);

date_default_timezone_set('Asia/Tokyo');

$sessionName = getenv('SESSION_NAME') ?: 'staging_servers_manager';
if (is_file(__DIR__ . '/../config/env.php')) {
    $env = require __DIR__ . '/../config/env.php';
    if (is_array($env) && isset($env['app']['session_name'])) {
        $sessionName = (string) $env['app']['session_name'];
    }
}

session_name($sessionName);
$secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/sakura.php';
require_once __DIR__ . '/views.php';
