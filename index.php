<?php
declare(strict_types=1);

require __DIR__ . '/src/bootstrap.php';

$config = load_config();
$pdo = db($config);

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
$path = rtrim($path, '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    if ($path === '/login') {
        start_login($config);
        exit;
    }

    if ($path === '/oauth2/callback') {
        handle_login_callback($config);
        exit;
    }

    if ($path === '/logout') {
        logout_and_redirect($config);
        exit;
    }

    require_login($config);

    if ($path === '/' || $path === '/dashboard') {
        $projects = list_projects($pdo);
        $content = render_view('dashboard', [
            'projects' => $projects,
        ]);
        render_layout('ダッシュボード', $content, $config, current_user());
        exit;
    }

    if ($path === '/admin/projects' && $method === 'GET') {
        require_admin($config);
        $projects = list_projects($pdo);
        $content = render_view('projects', [
            'projects' => $projects,
            'csrf' => csrf_token(),
        ]);
        render_layout('プロジェクト管理', $content, $config, current_user());
        exit;
    }

    if ($path === '/admin/projects' && $method === 'POST') {
        require_admin($config);
        verify_csrf();
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'sakura_project_id' => trim($_POST['sakura_project_id'] ?? ''),
            'sakura_api_token' => trim($_POST['sakura_api_token'] ?? ''),
            'sakura_api_secret' => trim($_POST['sakura_api_secret'] ?? ''),
            'sakura_zone' => trim($_POST['sakura_zone'] ?? ''),
            'coder_fqdn' => trim($_POST['coder_fqdn'] ?? ''),
            'coder_health_url' => trim($_POST['coder_health_url'] ?? ''),
        ];
        create_project($pdo, $data);
        redirect('/admin/projects');
        exit;
    }

    if (preg_match('#^/admin/projects/(\d+)$#', $path, $matches) && $method === 'GET') {
        require_admin($config);
        $project = get_project($pdo, (int) $matches[1]);
        if (!$project) {
            render_not_found($config);
            exit;
        }
        $content = render_view('project_detail', [
            'project' => $project,
            'csrf' => csrf_token(),
        ]);
        render_layout('プロジェクト詳細', $content, $config, current_user());
        exit;
    }

    if (preg_match('#^/admin/projects/(\d+)/(poweron|poweroff|delete)$#', $path, $matches) && $method === 'POST') {
        require_admin($config);
        verify_csrf();
        $project = get_project($pdo, (int) $matches[1]);
        if (!$project) {
            render_not_found($config);
            exit;
        }
        $action = $matches[2];
        if ($action === 'poweron') {
            sakura_power_on_project($project);
        } elseif ($action === 'poweroff') {
            sakura_power_off_project($project);
        } elseif ($action === 'delete') {
            delete_project($pdo, (int) $matches[1]);
        }
        redirect('/admin/projects/' . $matches[1]);
        exit;
    }

    if (preg_match('#^/coder/(\d+)$#', $path, $matches) && $method === 'GET') {
        $project = get_project($pdo, (int) $matches[1]);
        if (!$project) {
            render_not_found($config);
            exit;
        }
        sakura_power_on_project($project);
        $healthUrl = coder_health_url($project);
        $targetUrl = coder_target_url($project);
        $content = render_view('waiting', [
            'project' => $project,
            'health_url' => $healthUrl,
            'target_url' => $targetUrl,
        ]);
        render_layout('サーバ起動中', $content, $config, current_user());
        exit;
    }

    render_not_found($config);
} catch (Throwable $e) {
    $content = render_view('error', [
        'message' => '予期せぬエラーが発生しました。時間をおいて再度お試しください。',
    ]);
    render_layout('エラー', $content, $config, current_user());
}
