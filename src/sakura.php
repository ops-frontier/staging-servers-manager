<?php
declare(strict_types=1);

function sakura_power_on_project(array $project): void
{
    $servers = sakura_list_servers($project);
    foreach ($servers as $server) {
        $status = $server['Instance']['Status'] ?? '';
        if ($status !== 'up') {
            sakura_api_request($project, 'POST', '/server/' . $server['ID'] . '/power');
        }
    }
}

function sakura_power_off_project(array $project): void
{
    $servers = sakura_list_servers($project);
    foreach ($servers as $server) {
        $status = $server['Instance']['Status'] ?? '';
        if ($status === 'up') {
            sakura_api_request($project, 'DELETE', '/server/' . $server['ID'] . '/power');
        }
    }
}

function sakura_list_servers(array $project): array
{
    $response = sakura_api_request($project, 'GET', '/server');
    return $response['Servers'] ?? [];
}

function sakura_api_request(array $project, string $method, string $path, ?array $body = null): array
{
    $zone = $project['sakura_zone'] ?: 'is1a';
    $baseUrl = 'https://secure.sakura.ad.jp/cloud/zone/' . $zone . '/api/cloud/1.1';
    $url = $baseUrl . $path;

    $ch = curl_init($url);
    $headers = ['Content-Type: application/json'];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_USERPWD, $project['sakura_api_token'] . ':' . $project['sakura_api_secret']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $status >= 400) {
        return [];
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : [];
}

function coder_health_url(array $project): string
{
    if (!empty($project['coder_health_url'])) {
        return $project['coder_health_url'];
    }
    $fqdn = rtrim($project['coder_fqdn'] ?? '', '/');
    if ($fqdn === '') {
        return '';
    }
    return 'https://' . $fqdn . '/healthz';
}

function coder_target_url(array $project): string
{
    $fqdn = rtrim($project['coder_fqdn'] ?? '', '/');
    if ($fqdn === '') {
        return '';
    }
    return 'https://' . $fqdn . '/';
}
