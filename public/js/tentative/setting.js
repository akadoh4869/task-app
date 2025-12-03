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


  // -------------------------------
  // ✅ amodal モーダル制御（プロフィール編集・メール・PW等）
  // -------------------------------

  // ▶ 開く
  document.querySelectorAll('[data-modal-open]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const id = btn.getAttribute('data-modal-open');
      const el = document.getElementById(id);
      if (el) el.classList.add('is-open');
    });
  });

  // ▶ 閉じる（×・キャンセル）
  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const id = btn.getAttribute('data-modal-close');
      const el = document.getElementById(id);
      if (el) el.classList.remove('is-open');
    });
  });

  // ▶ 背景クリックで閉じる
  document.querySelectorAll('.amodal-backdrop').forEach(backdrop => {
    backdrop.addEventListener('click', e => {
      if (e.target === backdrop) backdrop.classList.remove('is-open');
    });
  });

  // ▶ ESCキーで全モーダル閉じる
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.amodal-backdrop.is-open')
        .forEach(el => el.classList.remove('is-open'));
    }
  });

});


// -------------------------------
// ✅ 従来型 オーバーレイ（規約・著作権など）
// -------------------------------
function openOverlay(id) {
  document.querySelectorAll('.overlay').forEach(modal => {
    modal.style.display = 'none';
  });

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
// ✅ PWAキャッシュクリア
// -------------------------------
function clearAppCache() {
  const clearButton = document.getElementById('clear-cache-btn');
  if (!clearButton) return;

  clearButton.onclick = null;

  if ('caches' in window) {
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => caches.delete(cacheName))
        );
      })
      .then(() => {
        console.log('キャッシュクリア完了');

        clearButton.textContent = 'キャッシュクリア完了しました';
        clearButton.style.pointerEvents = 'none';
        clearButton.style.color = '#999';
      })
      .catch(error => {
        console.error('キャッシュクリア失敗:', error);
      });
  } else {
    console.error('キャッシュAPIがサポートされていません');
  }
}
