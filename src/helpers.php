<?php
declare(strict_types=1);

function normalize_csv($value): array
{
    if (is_array($value)) {
        return array_values(array_filter(array_map('trim', $value)));
    }
    $string = trim((string) $value);
    if ($string === '') {
        return [];
    }
    $parts = array_map('trim', explode(',', $string));
    return array_values(array_filter($parts));
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
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
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $token)) {
        http_response_code(403);
        echo 'CSRF token mismatch.';
        exit;
    }
}

function mask_secret(string $value): string
{
    if ($value === '') {
        return '';
    }
    $length = mb_strlen($value);
    if ($length <= 4) {
        return str_repeat('*', $length);
    }
    return mb_substr($value, 0, 2) . str_repeat('*', $length - 4) . mb_substr($value, -2);
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
