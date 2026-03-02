<section class="panel waiting" data-health-url="<?= h($health_url) ?>" data-target-url="<?= h($target_url) ?>">
    <h1>サーバを起動しております。しばらくお待ちください</h1>
    <p>Coder サーバが起動するまで自動で待機します。</p>
    <div id="status" class="status">起動状況を確認中...</div>
    <div class="spinner"></div>
</section>
<script src="/assets/js/waiting.js"></script>
