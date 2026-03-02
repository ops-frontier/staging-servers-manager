<section class="panel">
    <h1>プロジェクト管理</h1>
    <p>さくらのクラウドのプロジェクト情報を登録してください。</p>

    <form method="post" class="form">
        <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
        <div class="form-grid">
            <label>
                プロジェクト名
                <input type="text" name="name" required>
            </label>
            <label>
                さくらのクラウド プロジェクトID
                <input type="text" name="sakura_project_id" required>
            </label>
            <label>
                APIキー (Access Token)
                <input type="text" name="sakura_api_token" required>
            </label>
            <label>
                APIシークレット (Access Token Secret)
                <input type="password" name="sakura_api_secret" required>
            </label>
            <label>
                ゾーン
                <input type="text" name="sakura_zone" placeholder="is1a">
            </label>
            <label>
                Coder サーバ FQDN
                <input type="text" name="coder_fqdn" placeholder="coder.example.com" required>
            </label>
            <label>
                Coder ヘルスチェックURL (任意)
                <input type="text" name="coder_health_url" placeholder="https://coder.example.com/healthz">
            </label>
        </div>
        <button class="button" type="submit">登録</button>
    </form>
</section>

<section class="panel">
    <h2>登録済みプロジェクト</h2>
    <?php if (empty($projects)): ?>
        <p>登録済みプロジェクトはありません。</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>プロジェクト名</th>
                    <th>ゾーン</th>
                    <th>Coder</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= h((string) $project['id']) ?></td>
                        <td><?= h($project['name']) ?></td>
                        <td><?= h($project['sakura_zone']) ?></td>
                        <td><?= h($project['coder_fqdn']) ?></td>
                        <td>
                            <a class="button secondary" href="/admin/projects/<?= h((string) $project['id']) ?>">詳細</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
