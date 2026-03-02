<?php
return [
    'app' => [
        'url' => 'https://example.com',
        'session_name' => 'staging_servers_manager',
        'allowed_domains' => 'example.com,example.jp',
        'admin_emails' => 'admin@example.com,ops@example.com',
    ],
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=staging_servers_manager;charset=utf8mb4',
        'user' => 'db_user',
        'pass' => 'db_password',
    ],
    'google' => [
        'client_id' => 'GOOGLE_CLIENT_ID',
        'client_secret' => 'GOOGLE_CLIENT_SECRET',
        'redirect_uri' => 'https://example.com/oauth2/callback',
    ],
];
