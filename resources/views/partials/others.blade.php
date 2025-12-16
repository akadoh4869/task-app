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

{{-- ➂管理者ダッシュボード --}}
<section class="setting-panel" id="panel-admin">
    @if(isset($adminStats))
        <main class="panel-inner">
            <h3 class="mb-3">管理者ダッシュボード</h3>

            {{-- サマリーカード --}}
            <div class="admin-summary-cards">
                <div class="admin-card">
                    <div class="admin-card-label">登録ユーザー数</div>
                    <div class="admin-card-value">{{ $adminStats['total_users'] }}</div>
                </div>

                {{-- <div class="admin-card">
                    <div class="admin-card-label">有料会員数</div>
                    <div class="admin-card-value">{{ $adminStats['paid_users'] }}</div>
                </div> --}}

                <div class="admin-card">
                    <div class="admin-card-label">グループ数</div>
                    <div class="admin-card-value">{{ $adminStats['total_groups'] }}</div>
                </div>

                <div class="admin-card">
                    <div class="admin-card-label">タスク総数</div>
                    <div class="admin-card-value">{{ $adminStats['total_tasks'] }}</div>
                </div>
            </div>

            {{-- 利用状況ミニ統計 --}}
            <div class="admin-section">
                <h4 class="admin-section-title">利用状況</h4>
                <ul class="admin-stats-list">
                    <li>本日作成されたタスク：<strong>{{ $adminStats['today_tasks'] }}</strong> 件</li>
                    {{-- <li>今週完了したタスク：<strong>{{ $adminStats['this_week_done'] }}</strong> 件</li> --}}
                    <li>保留中のグループ招待：<strong>{{ $adminStats['pending_invites'] }}</strong> 件</li>
                </ul>
            </div>

            {{-- 最近登録したユーザー --}}
            <div class="admin-section">
                <h4 class="admin-section-title">最近登録したユーザー</h4>

                @if($adminStats['recent_users']->isEmpty())
                    <p>最近の新規登録ユーザーはまだいません。</p>
                @else
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ユーザーネーム</th>
                                <th>アカウント名</th>
                                <th>メールアドレス</th>
                                <th>登録日</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adminStats['recent_users'] as $u)
                                <tr>
                                    <td>{{ $u->user_name }}</td>
                                    <td>{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td>{{ $u->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </main>
    @else
        {{-- 念のための保険（非管理者が panel-admin に来た場合） --}}
        <main class="panel-inner">
            <p>管理者のみがアクセスできるページです。</p>
        </main>
    @endif
</section>


