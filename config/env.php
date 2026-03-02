<?php
return [
    'app' => [
        'url' => 'https://ops-frontier.dev',
        'session_name' => 'staging_servers_manager',
        'allowed_domains' => 'ops-frontier.dev,example.jp',
        'admin_emails' => 'mitsuru@procube.jp',
    ],
    'db' => [
        'dsn' => 'mysql:host=localhost;dbname=staging_servers_manager;charset=utf8mb4',
        'user' => 'db_user',
        'pass' => 'db_password',
    ],
    'google' => [
        'client_id' => 'GOOGLE_CLIENT_ID',
        'client_secret' => 'GOOGLE_CLIENT_SECRET',
        'redirect_uri' => 'https://ops-frontier.dev/oauth2/callback',
    ],
];
