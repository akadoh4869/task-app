// ===============================
// 🌟 設定画面 共通スクリプト
// ===============================

// -------------------------------
// 設定パネル切り替え（インスタ風）
// -------------------------------
document.addEventListener('DOMContentLoaded', () => {
  // data-panel を持っているメニュー（左側）
  const items  = document.querySelectorAll('.setting-item[data-panel]');
  // 右側のパネル群
  const panels = document.querySelectorAll('.setting-panel');

  // 指定IDのパネルだけを表示
  function showPanel(id) {
    if (!panels.length) return;

    panels.forEach(p => p.classList.remove('active'));

    const target = document.getElementById(id);
    if (target) {
      target.classList.add('active');
    }
  }

  // メニュークリックでパネル切り替え
  if (items.length) {
    items.forEach(item => {
      item.addEventListener('click', () => {
        const panelId = item.dataset.panel;
        if (!panelId) return;

        // メニューの見た目を active に
        items.forEach(i => i.classList.remove('active'));
        item.classList.add('active');

        // 対応するパネルを表示
        showPanel(panelId);
      });
    });
  }

  // 初期表示（必要に応じてIDを変更）
  // Blade側で panel-default がある前提
  showPanel('panel-default');
});


// -------------------------------
// オーバーレイ（モーダル）操作
//   利用規約 / プライバシー / 著作権 など
// -------------------------------

function openOverlay(id) {
  // まず全部のオーバーレイを閉じる
  // （class="overlay" が付いている要素を想定）
  document.querySelectorAll('.overlay').forEach(modal => {
    modal.style.display = 'none';
  });

  // 指定されたオーバーレイだけ開く
  const target = document.getElementById(id);
  if (target) {
    target.style.display = 'flex';
  }
}

function closeOverlay(id) {
  const el = document.getElementById(id);
  if (el) {
    el.style.display = 'none';
  }
}


// -------------------------------
// PWAキャッシュクリア
// -------------------------------
function clearAppCache() {
  const clearButton = document.getElementById('clear-cache-btn');
  if (!clearButton) return; // ボタンなかったら何もしない

  // 押したらすぐに無効化（連打防止）
  clearButton.onclick = null;

  if ('caches' in window) {
    caches.keys()
      .then(function (cacheNames) {
        return Promise.all(
          cacheNames.map(function (cacheName) {
            return caches.delete(cacheName);
          })
        );
      })
      .then(function () {
        console.log('キャッシュクリア完了');

        // ボタンの表示を変更
        clearButton.textContent = 'キャッシュクリア完了しました';
        clearButton.style.pointerEvents = 'none';
        clearButton.style.color = '#999';
      })
      .catch(function (error) {
        console.error('キャッシュクリア失敗:', error);
      });
  } else {
    console.error('キャッシュAPIがサポートされていません');
  }
}
