{{-- ➀ キャッシュクリア --}}
<section id="panel-cache" class="setting-panel">
    <main class="panel-inner">
        <h2 class="others">キャッシュクリア</h2>
        <p>
            Task Me 内のキャッシュを削除します。<br>
            表示が崩れる・最新情報が反映されない場合などにお試しください。
        </p>

        <div class="confirm-buttons">
            <button type="button"
                    class="confirm"
                    id="clear-cache-btn"
                    onclick="clearAppCache()">
                キャッシュをクリアする
            </button>
        </div>
    </main>
</section>

{{-- ➁退会パネル --}}
<section id="panel-withdraw" class="setting-panel">
    <main class="panel-inner">
        <h2 class="others">退会確認</h2>

        <p>
            本当に退会しますか？<br>
            退会すると、アカウントおよび関連データは削除対象となります。<br>
            ※復元はできません。
        </p>

        <div class="confirm-buttons" style="margin-top:20px; display:flex; gap:12px;">
            {{-- ✅ 退会実行 --}}
            <form method="POST" action="{{ route('withdraw') }}"
                  onsubmit="return confirm('本当に退会しますか？この操作は取り消せません。');">
                @csrf
                <button type="submit" class="confirm">
                    退会する
                </button>
            </form>

            {{-- ✅ キャンセル（例：プロフィール編集パネルに戻す） --}}
            <button type="button"
                    class="cancel"
                    onclick="
                        document.querySelectorAll('.setting-panel').forEach(p => p.classList.remove('active'));
                        const target = document.getElementById('panel-profile-edit') || document.getElementById('panel-account-info');
                        if (target) target.classList.add('active');
                    ">
                キャンセル
            </button>
        </div>
    </main>
</section>
