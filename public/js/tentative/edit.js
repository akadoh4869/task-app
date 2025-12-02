// {{-- ===== モーダル制御スクリプト（amodal版） ===== --}}
document.addEventListener('DOMContentLoaded', () => {
    // 開く
    document.querySelectorAll('[data-modal-open]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const id = btn.getAttribute('data-modal-open');
            const el = document.getElementById(id);
            if (el) el.classList.add('is-open');
        });
    });

    // 閉じる（× / キャンセル）
    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            const id = btn.getAttribute('data-modal-close');
            const el = document.getElementById(id);
            if (el) el.classList.remove('is-open');
        });
    });

    // 背景クリックで閉じる
    document.querySelectorAll('.amodal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', e => {
            if (e.target === backdrop) backdrop.classList.remove('is-open');
        });
    });

    // ESCキーで全部閉じる
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.amodal-backdrop.is-open')
                .forEach(el => el.classList.remove('is-open'));
        }
    });
});