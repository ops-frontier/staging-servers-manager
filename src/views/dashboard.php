<section class="panel">
    <h1>ダッシュボード</h1>
    <p>登録済みのプロジェクト一覧です。</p>

    <?php if (empty($projects)): ?>
        <p>プロジェクトが登録されていません。管理コンソールから追加してください。</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>プロジェクト名</th>
                    <th>Coder</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><?= h((string) $project['id']) ?></td>
                        <td><?= h($project['name']) ?></td>
                        <td><?= h($project['coder_fqdn']) ?></td>
                        <td>
                            <a class="button" href="/coder/<?= h((string) $project['id']) ?>">Coderへ</a>
                            <a class="button secondary" href="/admin/projects/<?= h((string) $project['id']) ?>">詳細</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
