<?php
declare(strict_types=1);

function load_config(): array
{
    $config = [
        'app' => [
            'url' => getenv('APP_URL') ?: '',
            'session_name' => getenv('SESSION_NAME') ?: 'staging_servers_manager',
            'allowed_domains' => getenv('ALLOWED_DOMAINS') ?: '',
            'admin_emails' => getenv('ADMIN_EMAILS') ?: '',
        ],
        'db' => [
            'dsn' => getenv('DB_DSN') ?: '',
            'user' => getenv('DB_USER') ?: '',
            'pass' => getenv('DB_PASS') ?: '',
        ],
        'google' => [
            'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
            'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
            'redirect_uri' => getenv('GOOGLE_REDIRECT_URI') ?: '',
        ],
    ];

    $envPath = __DIR__ . '/../config/env.php';
    if (is_file($envPath)) {
        $fileConfig = require $envPath;
        if (is_array($fileConfig)) {
            $config = array_replace_recursive($config, $fileConfig);
        }
    }

    $config['app']['allowed_domains'] = normalize_csv($config['app']['allowed_domains']);
    $config['app']['admin_emails'] = normalize_csv($config['app']['admin_emails']);

    if (empty($config['google']['redirect_uri']) && !empty($config['app']['url'])) {
        $config['google']['redirect_uri'] = rtrim($config['app']['url'], '/') . '/oauth2/callback';
    }

    return $config;
}
