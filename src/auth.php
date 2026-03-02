<?php
declare(strict_types=1);

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(array $config): void
{
    if (!current_user()) {
        redirect('/login');
    }
}

function require_admin(array $config): void
{
    $user = current_user();
    if (!$user) {
        redirect('/login');
    }
    $admins = $config['app']['admin_emails'] ?? [];
    if ($admins && !in_array($user['email'] ?? '', $admins, true)) {
        http_response_code(403);
        echo '権限がありません。';
        exit;
    }
}

function logout_and_redirect(array $config): void
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_destroy();
    }
    redirect('/login');
}

function start_login(array $config): void
{
    $clientId = $config['google']['client_id'] ?? '';
    if ($clientId === '') {
        http_response_code(500);
        echo 'Google OAuth の設定が未完了です。';
        exit;
    }

    $state = bin2hex(random_bytes(16));
    $nonce = bin2hex(random_bytes(16));
    $_SESSION['oauth_state'] = $state;
    $_SESSION['oauth_nonce'] = $nonce;

    $params = [
        'client_id' => $clientId,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'redirect_uri' => $config['google']['redirect_uri'],
        'state' => $state,
        'nonce' => $nonce,
        'prompt' => 'select_account',
    ];

    $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    header('Location: ' . $url);
    exit;
}

function handle_login_callback(array $config): void
{
    $state = $_GET['state'] ?? '';
    if (empty($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], (string) $state)) {
        http_response_code(400);
        echo 'Invalid state.';
        exit;
    }

    $code = $_GET['code'] ?? '';
    if ($code === '') {
        http_response_code(400);
        echo 'Authorization code missing.';
        exit;
    }

    $tokenResponse = exchange_code_for_token($config, (string) $code);
    $idToken = $tokenResponse['id_token'] ?? '';
    if ($idToken === '') {
        http_response_code(400);
        echo 'ID token missing.';
        exit;
    }

    $payload = verify_id_token($config, $idToken, $_SESSION['oauth_nonce'] ?? '');
    $email = $payload['email'] ?? '';
    if ($email === '') {
        http_response_code(403);
        echo 'メールアドレスが取得できませんでした。';
        exit;
    }

    $allowedDomains = $config['app']['allowed_domains'] ?? [];
    if ($allowedDomains) {
        $domain = substr(strrchr($email, '@') ?: '', 1);
        if (!in_array($domain, $allowedDomains, true)) {
            http_response_code(403);
            echo '許可されていないドメインです。';
            exit;
        }
    }

    $_SESSION['user'] = [
        'email' => $email,
        'name' => $payload['name'] ?? $email,
        'picture' => $payload['picture'] ?? '',
    ];

    unset($_SESSION['oauth_state'], $_SESSION['oauth_nonce']);
    redirect('/');
}

function exchange_code_for_token(array $config, string $code): array
{
    $postFields = [
        'code' => $code,
        'client_id' => $config['google']['client_id'],
        'client_secret' => $config['google']['client_secret'],
        'redirect_uri' => $config['google']['redirect_uri'],
        'grant_type' => 'authorization_code',
    ];

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postFields),
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $status >= 400) {
        http_response_code(500);
        echo 'Token exchange failed.';
        exit;
    }

    $data = json_decode($response, true);
    return is_array($data) ? $data : [];
}

function verify_id_token(array $config, string $idToken, string $expectedNonce): array
{
    $parts = explode('.', $idToken);
    if (count($parts) !== 3) {
        http_response_code(400);
        echo 'Invalid ID token.';
        exit;
    }

    [$headerB64, $payloadB64, $signatureB64] = $parts;
    $header = json_decode(base64url_decode($headerB64), true);
    $payload = json_decode(base64url_decode($payloadB64), true);
    if (!is_array($header) || !is_array($payload)) {
        http_response_code(400);
        echo 'Invalid ID token.';
        exit;
    }

    $kid = $header['kid'] ?? '';
    $alg = $header['alg'] ?? '';
    if ($alg !== 'RS256') {
        http_response_code(400);
        echo 'Unsupported token algorithm.';
        exit;
    }

    $jwks = fetch_google_jwks();
    $key = null;
    foreach ($jwks as $jwk) {
        if (($jwk['kid'] ?? '') === $kid) {
            $key = $jwk;
            break;
        }
    }
    if (!$key) {
        http_response_code(400);
        echo 'JWK not found.';
        exit;
    }

    $pem = jwk_to_pem($key);
    $data = $headerB64 . '.' . $payloadB64;
    $signature = base64url_decode($signatureB64);

    $verified = openssl_verify($data, $signature, $pem, OPENSSL_ALGO_SHA256);
    if ($verified !== 1) {
        http_response_code(400);
        echo 'Invalid token signature.';
        exit;
    }

    $issuer = $payload['iss'] ?? '';
    if (!in_array($issuer, ['https://accounts.google.com', 'accounts.google.com'], true)) {
        http_response_code(400);
        echo 'Invalid issuer.';
        exit;
    }

    $aud = $payload['aud'] ?? '';
    if ($aud !== ($config['google']['client_id'] ?? '')) {
        http_response_code(400);
        echo 'Invalid audience.';
        exit;
    }

    if (!empty($expectedNonce) && ($payload['nonce'] ?? '') !== $expectedNonce) {
        http_response_code(400);
        echo 'Invalid nonce.';
        exit;
    }

    if (($payload['exp'] ?? 0) < time()) {
        http_response_code(400);
        echo 'Token expired.';
        exit;
    }

    return $payload;
}

function fetch_google_jwks(): array
{
    $ch = curl_init('https://www.googleapis.com/oauth2/v3/certs');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        http_response_code(500);
        echo 'Failed to fetch JWKS.';
        exit;
    }

    $data = json_decode($response, true);
    return $data['keys'] ?? [];
}

function base64url_decode(string $data): string
{
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    $data = strtr($data, '-_', '+/');
    return base64_decode($data) ?: '';
}

function jwk_to_pem(array $jwk): string
{
    $n = base64url_decode($jwk['n']);
    $e = base64url_decode($jwk['e']);

    $modulus = "\x02" . encode_der_length(strlen($n)) . $n;
    $exponent = "\x02" . encode_der_length(strlen($e)) . $e;
    $sequence = "\x30" . encode_der_length(strlen($modulus . $exponent)) . $modulus . $exponent;
    $bitstring = "\x03" . encode_der_length(strlen("\x00" . $sequence)) . "\x00" . $sequence;

    $oid = "\x30\x0D\x06\x09\x2A\x86\x48\x86\xF7\x0D\x01\x01\x01\x05\x00";
    $der = "\x30" . encode_der_length(strlen($oid . $bitstring)) . $oid . $bitstring;

    $pem = "-----BEGIN PUBLIC KEY-----\n";
    $pem .= chunk_split(base64_encode($der), 64, "\n");
    $pem .= "-----END PUBLIC KEY-----\n";

    return $pem;
}

function encode_der_length(int $length): string
{
    if ($length <= 0x7F) {
        return chr($length);
    }
    $temp = ltrim(pack('N', $length), "\x00");
    return chr(0x80 | strlen($temp)) . $temp;
}
