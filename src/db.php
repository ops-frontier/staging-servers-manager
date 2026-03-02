<?php
declare(strict_types=1);

function db(array $config): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = $config['db']['dsn'] ?? '';
    $user = $config['db']['user'] ?? '';
    $pass = $config['db']['pass'] ?? '';

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}

function list_projects(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM projects ORDER BY id DESC');
    return $stmt->fetchAll();
}

function get_project(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $project = $stmt->fetch();
    return $project ?: null;
}

function create_project(PDO $pdo, array $data): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO projects (name, sakura_project_id, sakura_api_token, sakura_api_secret, sakura_zone, coder_fqdn, coder_health_url, created_at)\n         VALUES (:name, :sakura_project_id, :sakura_api_token, :sakura_api_secret, :sakura_zone, :coder_fqdn, :coder_health_url, NOW())'
    );
    $stmt->execute([
        ':name' => $data['name'],
        ':sakura_project_id' => $data['sakura_project_id'],
        ':sakura_api_token' => $data['sakura_api_token'],
        ':sakura_api_secret' => $data['sakura_api_secret'],
        ':sakura_zone' => $data['sakura_zone'] ?: 'is1a',
        ':coder_fqdn' => $data['coder_fqdn'],
        ':coder_health_url' => $data['coder_health_url'],
    ]);
}

function delete_project(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare('DELETE FROM projects WHERE id = :id');
    $stmt->execute([':id' => $id]);
}
