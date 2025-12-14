// ===============================
// 🌟 設定画面 共通スクリプト（完全統合版）
// ===============================

document.addEventListener('DOMContentLoaded', () => {

  // -------------------------------
  // ✅ 設定パネル切り替え（インスタ風）
// -------------------------------
  const items  = document.querySelectorAll('.setting-item[data-panel]');
  const panels = document.querySelectorAll('.setting-panel');

  function showPanel(id) {
    if (!panels.length) return;

    panels.forEach(p => p.classList.remove('active'));

    const target = document.getElementById(id);
    if (target) {
      target.classList.add('active');
    }
  }

  if (items.length) {
    items.forEach(item => {
      item.addEventListener('click', () => {
        const panelId = item.dataset.panel;
        if (!panelId) return;

        items.forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        showPanel(panelId);
      });
    });
  }

  // ✅ 初期表示（なければ最初のパネルを表示）
  if (document.getElementById('panel-default')) {
    showPanel('panel-default');
  } else if (panels.length) {
    panels[0].classList.add('active');
  }

});

// -------------------------------
// ✅ PWAキャッシュクリア（非同期 / リロードなし / 再実行OK）
// -------------------------------
function clearAppCache() {
  const clearButton = document.getElementById('clear-cache-btn');
  if (!clearButton) return;

  // 連打防止（実行中だけ）
  if (clearButton.dataset.loading === '1') {
    return;
  }
  clearButton.dataset.loading = '1';

  const originalText = clearButton.textContent;
  clearButton.disabled = true;
  // clearButton.textContent = 'キャッシュクリア中...';

  if (!('caches' in window)) {
    console.error('キャッシュAPIがサポートされていません');
    alert('このブラウザではキャッシュクリア機能が利用できません。');
    clearButton.disabled = false;
    clearButton.textContent = originalText;
    clearButton.dataset.loading = '0';
    return;
  }

  caches.keys()
    .then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => caches.delete(cacheName))
      );
    })
    .then(() => {
      console.log('キャッシュクリア完了');
      // 完了メッセージ（お好みで調整して OK）
      // alert('キャッシュをクリアしました。');

      clearButton.textContent = 'キャッシュクリア完了';

      // 0.3秒くらい表示したあと元の文言に戻す
      setTimeout(() => {
        clearButton.textContent = originalText;
      }, 300);
    })
    .catch(error => {
      console.error('キャッシュクリア失敗:', error);
      alert('キャッシュクリアに失敗しました。');

      // 失敗した場合はテキストを元に戻す
      clearButton.textContent = originalText;
    })
    .finally(() => {
      clearButton.disabled = false;
      clearButton.dataset.loading = '0';
    });
}
