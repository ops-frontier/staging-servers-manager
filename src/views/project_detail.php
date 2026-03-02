<section class="panel">
    <h1>プロジェクト詳細</h1>
    <div class="detail-grid">
        <div><span>プロジェクト名</span><?= h($project['name']) ?></div>
        <div><span>プロジェクトID</span><?= h($project['sakura_project_id']) ?></div>
        <div><span>APIキー</span><?= h(mask_secret($project['sakura_api_token'])) ?></div>
        <div><span>APIシークレット</span><?= h(mask_secret($project['sakura_api_secret'])) ?></div>
        <div><span>ゾーン</span><?= h($project['sakura_zone']) ?></div>
        <div><span>Coder FQDN</span><?= h($project['coder_fqdn']) ?></div>
        <div><span>Coder Health URL</span><?= h($project['coder_health_url']) ?></div>
    </div>

    <div class="actions">
        <a class="button" href="/coder/<?= h((string) $project['id']) ?>">Coderへ</a>
        <form method="post" action="/admin/projects/<?= h((string) $project['id']) ?>/poweron" class="inline">
            <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
            <button class="button" type="submit">サーバ起動</button>
        </form>
        <form method="post" action="/admin/projects/<?= h((string) $project['id']) ?>/poweroff" class="inline">
            <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
            <button class="button secondary" type="submit">サーバ停止</button>
        </form>
        <form method="post" action="/admin/projects/<?= h((string) $project['id']) ?>/delete" class="inline" onsubmit="return confirm('削除してもよろしいですか？');">
            <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
            <button class="button danger" type="submit">削除</button>
        </form>
    </div>
</section>
