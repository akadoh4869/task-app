// =====================================
// ✅ Account（amodal + 自動更新）専用スクリプト
// =====================================

document.addEventListener('DOMContentLoaded', () => {

  // ===========================
  // ✅ amodal モーダル制御
  // ===========================

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


  // ===========================
  // ✅ プロフィール自動更新（名前・画像）
  // ===========================
  const form = document.getElementById('profileForm');
  if (!form) return; // アカウントパネルが無いページでは何もしない

  const nameInput = document.getElementById('nameInput');
  const avatarInput = document.getElementById('avatarInput');
  const avatarPreview = document.getElementById('avatarPreview');
  const status = document.getElementById('autosaveStatus');

  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  let timer = null;
  let isSaving = false;
  let pending = false;

  // 初期値（同じ値なら送らない）
  let lastSentName = nameInput ? nameInput.value : '';

  function setStatus(msg, type = 'info') {
    if (!status) return;
    status.textContent = msg || '';
    status.style.color =
      type === 'ok' ? '#16a34a' :
      type === 'err' ? '#dc2626' :
      '#666';
  }

  async function saveProfile({ withAvatar = false } = {}) {
    if (!csrf) {
      console.error('CSRF token が見つかりません（meta[name="csrf-token"]）');
      setStatus('保存に失敗しました（CSRF）', 'err');
      return;
    }

    if (isSaving) { pending = true; return; }
    isSaving = true;
    pending = false;

    setStatus('保存中...', 'info');

    try {
      const fd = new FormData(form);

      // Laravel PATCH：POST + _method が安定
      fd.set('_method', 'PATCH');

      // 画像なし保存の時はファイルを外す（無駄に送らない）
      if (!withAvatar) {
        fd.delete('avatar');
      }

      const res = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf,
          'Accept': 'application/json',
        },
        body: fd
      });

      if (!res.ok) {
        // バリデーション等（JSONが返る想定）
        let data = null;
        try { data = await res.json(); } catch (e) {}

        const msg =
          data?.message ||
          (data?.errors ? Object.values(data.errors).flat()[0] : null) ||
          `保存に失敗しました（${res.status}）`;

        throw new Error(msg);
      }

      setStatus('保存しました', 'ok');
      if (nameInput) lastSentName = nameInput.value;

    } catch (err) {
      console.error(err);
      setStatus(err.message || '保存に失敗しました', 'err');

    } finally {
      isSaving = false;
      // 保存中に更に変更されたら、もう一回保存（名前のみ）
      if (pending) {
        saveProfile({ withAvatar: false });
      }
    }
  }

  // ✅ 名前：入力停止（700ms）で自動保存
  if (nameInput) {
    nameInput.addEventListener('input', () => {
      if (nameInput.value === lastSentName) return;

      clearTimeout(timer);
      timer = setTimeout(() => {
        saveProfile({ withAvatar: false });
      }, 700);
    });

    // フォーカス外れでも保存
    nameInput.addEventListener('change', () => {
      if (nameInput.value === lastSentName) return;
      clearTimeout(timer);
      saveProfile({ withAvatar: false });
    });
  }

  // ✅ 画像：選択→即プレビュー→自動保存
  if (avatarInput) {
    avatarInput.addEventListener('change', () => {
      const file = avatarInput.files?.[0];
      if (!file) return;

      // 先にプレビュー
      if (avatarPreview) {
        avatarPreview.src = URL.createObjectURL(file);
      }

      clearTimeout(timer);
      saveProfile({ withAvatar: true });
    });
  }

});

