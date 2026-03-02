<?php
declare(strict_types=1);

function render_view(string $name, array $data = []): string
{
    $path = __DIR__ . '/views/' . $name . '.php';
    if (!is_file($path)) {
        return '';
    }
    extract($data);
    ob_start();
    require $path;
    return ob_get_clean();
}

function render_layout(string $title, string $content, array $config, ?array $user): void
{
    require __DIR__ . '/views/layout.php';
}

function render_not_found(array $config): void
{
    http_response_code(404);
    $content = render_view('error', [
        'message' => 'ページが見つかりませんでした。',
    ]);
    render_layout('Not Found', $content, $config, current_user());
}
