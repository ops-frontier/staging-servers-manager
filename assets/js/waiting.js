(() => {
  const panel = document.querySelector('.waiting');
  if (!panel) return;

  const healthUrl = panel.dataset.healthUrl;
  const targetUrl = panel.dataset.targetUrl;
  const statusEl = document.getElementById('status');
  const intervalMs = 5000;

  if (!healthUrl || !targetUrl) {
    statusEl.textContent = 'Coder サーバのURLが設定されていません。';
    return;
  }

  const checkHealth = async () => {
    try {
      statusEl.textContent = 'Coder サーバの起動を確認しています...';
      const response = await fetch(healthUrl, {
        method: 'GET',
        mode: 'cors',
        cache: 'no-store',
      });
      if (response.ok) {
        statusEl.textContent = 'Coder サーバが起動しました。遷移します。';
        window.location.href = targetUrl;
        return;
      }
      statusEl.textContent = '起動中です。しばらくお待ちください。';
    } catch (error) {
      statusEl.textContent = '起動中です。しばらくお待ちください。';
    }
    setTimeout(checkHealth, intervalMs);
  };

  checkHealth();
})();
