{{-- resources/views/partials/account.blade.php --}}
@php
    // コントローラから $user が来ていなければ、ログインユーザーを使う
    $user = $user ?? Auth::user();
@endphp

@php
    $selectedScope = $selectedScope ?? 'all';
    $groups = $groups ?? collect();
    $completedTasks = $completedTasks ?? collect();
@endphp

{{-- 利用状況（簡易統計） --}}
@php
// リレーションがある前提。無い場合は 0 になるようにガード
$groupCount   = method_exists($user, 'groups') ? $user->groups()->count() : 0;
$taskBuilder  = method_exists($user, 'tasks')  ? $user->tasks() : null;
$taskTotal    = $taskBuilder ? $taskBuilder->count() : 0;
$taskDone     = $taskBuilder ? (clone $taskBuilder)->where('status', 'completed')->count() : 0;
@endphp


{{-- ➀ アカウント設定（プロフィール編集＋アカウント情報 統合） --}}
<section id="panel-profile-edit" class="setting-panel">
    <main class="panel-inner">

        {{-- ✅ 成功・エラー --}}
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <h2 class="mb-3">プロフィール編集</h2>

        {{-- =========================
            🔹 プロフィール編集ブロック
        ========================= --}}
        <form method="POST" action="{{ route('users.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="mb-3">
                <label class="form-label">アカウント名（表示名）</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label">アイコン画像（任意）</label>
                <div style="display:flex; gap:16px; align-items:center;">
                    <img src="{{ asset($user->avatar ? 'storage/'.$user->avatar : 'storage/images/default.png') }}"
                         alt="avatar" width="64" height="64" style="border-radius:50%; object-fit:cover;">
                    <input type="file" name="avatar" accept="image/*">
                </div>
                <small class="text-muted">jpg / png / webp、5MBまで</small>
            </div>

            <button class="btn btn-primary">更新する</button>
        </form>

        <hr class="my-4">

        {{-- =========================
            🔹 個別変更（モーダル起動）
        ========================= --}}
        <div class="stack" style="display:grid; gap:12px; max-width:520px;">
            <div>
                <div class="label-sm text-muted">現在のメール</div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <strong>{{ $user->email }}</strong>
                    <button type="button" class="btn btn-light" data-modal-open="modal-email">メール変更</button>
                </div>
            </div>

            <div>
                <div class="label-sm text-muted">登録ユーザーネーム</div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <strong>{{ $user->user_name }}</strong>
                    <button type="button" class="btn btn-light" data-modal-open="modal-username">ユーザーネーム変更</button>
                </div>
            </div>

            <div>
                <div class="label-sm text-muted">パスワード</div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <span>********</span>
                    <button type="button" class="btn btn-light" data-modal-open="modal-password">パスワード変更</button>
                </div>
            </div>
        </div>

        {{-- =========================
            🔹 アカウント情報（表示専用）
        ========================= --}}
        <div class="account-block">

            <p>会員ステータス:
                <strong>無料会員</strong>
            </p>
        </div>

        {{-- =========================
            🔹 利用状況（統計）
        ========================= --}}
        <div class="account-block">
            <h4>利用状況</h4>

            <p>所属グループ数:
                <strong>{{ $groupCount }}</strong>
            </p>

            <ul class="group-name-list">
                @foreach($groups as $group)
                    <li class="group-name-item">
                        ・{{ $group->group_name }}
                    </li>
                @endforeach
            </ul>

            <p>自分にアサインされたタスク数:
                <strong>{{ $assignedTotal }}</strong>
            </p>

            <p>そのうち完了タスク数:
                <strong>{{ $assignedDone }}</strong>
            </p>

            <p>自分がすべきタスクの達成率:
                <strong>{{ $assignedRate }}%</strong>
            </p>
        </div>

    </main>

    {{-- ===== モーダル群（amodal系に置換） ===== --}}
    {{-- メール変更 --}}
    <div id="modal-email" class="amodal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-email-title">
        <div class="amodal">
            <header>
                <h3 id="modal-email-title">メールアドレスを変更</h3>
                <button type="button" class="close" data-modal-close="modal-email" aria-label="閉じる">&times;</button>
            </header>
            <form method="POST" action="{{ route('users.updateEmail') }}">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label">新しいメールアドレス</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">現在のパスワード（確認）</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="actions">
                    <button type="button" class="btn btn-light" data-modal-close="modal-email">キャンセル</button>
                    <button class="btn btn-primary">変更する</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ユーザーネーム変更 --}}
    <div id="modal-username" class="amodal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-username-title">
        <div class="amodal">
            <header>
                <h3 id="modal-username-title">ユーザーネームを変更</h3>
                <button type="button" class="close" data-modal-close="modal-username" aria-label="閉じる">&times;</button>
            </header>
            <form method="POST" action="{{ route('users.updateUserName') }}">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label">新しいユーザーネーム</label>
                    <input type="text" name="user_name" class="form-control" value="{{ old('user_name', $user->user_name) }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">現在のパスワード（確認）</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="actions">
                    <button type="button" class="btn btn-light" data-modal-close="modal-username">キャンセル</button>
                    <button class="btn btn-primary">変更する</button>
                </div>
            </form>
        </div>
    </div>

    {{-- パスワード変更 --}}
    <div id="modal-password" class="amodal-backdrop" role="dialog" aria-modal="true" aria-labelledby="modal-password-title">
        <div class="amodal">
            <header>
                <h3 id="modal-password-title">パスワードを変更</h3>
                <button type="button" class="close" data-modal-close="modal-password" aria-label="閉じる">&times;</button>
            </header>
            <form method="POST" action="{{ route('users.updatePassword') }}">
                @csrf
                @method('PATCH')
                <div class="mb-3">
                    <label class="form-label">現在のパスワード</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">新しいパスワード</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">新しいパスワード（確認）</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div class="actions">
                    <button type="button" class="btn btn-light" data-modal-close="modal-password">キャンセル</button>
                    <button class="btn btn-primary">変更する</button>
                </div>
            </form>
        </div>
    </div>
</section>

{{-- ➁お知らせ --}}
<section id="panel-notice" class="setting-panel">
    <main class="panel-inner">
        <h2 class="mb-3">お知らせ</h2>

        <div class="notice-list">

            {{-- 例：未読 --}}
            <div class="notice-item is-unread">
                <div class="notice-icon">
                    <i class="fa-solid fa-user-plus"></i>
                </div>
                <div class="notice-body">
                    <div class="notice-title">グループに招待されました</div>
                    <div class="notice-text">「○○プロジェクト」への参加招待が届いています。</div>
                    <div class="notice-time">2025/04/16 20:31</div>
                </div>
            </div>

            {{-- 例：既読 --}}
            <div class="notice-item">
                <div class="notice-icon">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="notice-body">
                    <div class="notice-title">タスクの期限が近づいています</div>
                    <div class="notice-text">「資料作成」の期限は明日です。</div>
                    <div class="notice-time">2025/04/15 09:10</div>
                </div>
            </div>

            {{-- お知らせなし --}}
            {{--
            <p class="text-muted">現在お知らせはありません。</p>
            --}}

        </div>
    </main>
</section>

{{-- ➂通知設定 --}}
<section id="panel-notification-setting" class="setting-panel">
    <main class="panel-inner">
        <h2 class="mb-3">通知設定</h2>

        <form method="POST" action="#">
            @csrf

            <div class="notify-item">
                <span>タスク期限の通知</span>
                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="notify-item">
                <span>グループ招待の通知</span>
                <label class="switch">
                    <input type="checkbox" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="notify-item">
                <span>タスク完了時の通知</span>
                <label class="switch">
                    <input type="checkbox">
                    <span class="slider"></span>
                </label>
            </div>

            <div class="notify-item">
                <span>プッシュ通知（PWA）</span>
                <label class="switch">
                    <input type="checkbox">
                    <span class="slider"></span>
                </label>
            </div>

            <button class="btn btn-primary mt-3">保存する</button>
        </form>
    </main>
</section>




{{-- ➃招待一覧 --}}
<section id="panel-invitations" class="setting-panel">
    <main class="panel-inner">

        <div class="history-title">保留中招待一覧</div>

        <div class="invitation-icon">
            <i class="fa-solid fa-envelope"></i>
        </div>

        @if($pendingInvitations->isEmpty())
            <p style="text-align: center; margin-top: 50px;">
                保留中のグループ招待はありません。
            </p>
        @else
            <div class="invitation-list">
                @foreach($pendingInvitations as $invitation)
                    <div class="invitation-card">

                        <div class="invitation-text">
                            {{ $invitation->group->group_name }}
                        </div>

                        <div class="invitation-buttons">
                            <form action="{{ route('invitation.respond') }}" method="POST"
                                style="display: flex; gap: 10px;">
                                @csrf

                                <input type="hidden" name="invitation_id"
                                    value="{{ $invitation->id }}">

                                @if($totalSpaceCount < 3)
                                    <button class="accept-btn"
                                            name="response"
                                            value="accept">
                                        参加
                                    </button>
                                @endif

                                <button class="decline-btn"
                                        name="response"
                                        value="decline">
                                    辞退
                                </button>
                            </form>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </main>

    

</section>

{{-- ➃完了タスク一覧 --}}
{{-- <section id="panel-completed" class="setting-panel">
    <main class="panel-inner">
        <!-- 完了タスク一覧オーバーレイ -->
            <div class="completed-content">
                <h3>完了タスク一覧</h3>
                <!-- プルダウン切り替え -->
                <form method="GET" action="{{ route('setting.index') }}">
                    <label for="task_scope">表示対象：</label>
                    <select name="task_scope" id="task_scope" onchange="this.form.submit()">
                        <option value="all" {{ $selectedScope === 'all' ? 'selected' : '' }}>すべて</option>
                        <option value="personal" {{ $selectedScope === 'personal' ? 'selected' : '' }}>個人タスク</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group->id }}" {{ $selectedScope == $group->id ? 'selected' : '' }}>
                                {{ $group->group_name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <div class="modal-scroll-content">
                    @if($completedTasks->isEmpty())
                        <p>完了タスクはありません。</p>
                    @else
                        <ul style="list-style: none; padding: 0;">
                            @foreach($completedTasks as $task)
                                <li style="margin-bottom: 15px; border-bottom: 1px dashed #ccc; padding-bottom: 10px;">
                                    <strong>{{ $task->task_name }}</strong><br>
                                    期限：{{ optional($task->due_date)->format('Y-m-d') ?? '未設定' }}<br>                      
                                    @if($task->group_id !== null)
                                        グループ：{{ optional($task->group)->group_name ?? '不明' }}<br>
                                        担当者：
                                        @if($task->assignedUsers->isNotEmpty())
                                            {{ $task->assignedUsers->pluck('name')->join('、') }}
                                        @else
                                            なし
                                        @endif
                                        <br>
                                    @endif
                                    <!-- ✅ 復元ボタン（フォーム） -->
                                    <form method="POST" action="{{ route('tasks.restore', $task->id) }}" style="margin-top: 8px;">
                                        @csrf
                                        <button type="submit" class="restore-btn" style="padding: 4px 8px; font-size: 0.9em;">復元する</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

    </main>
    
</section>



 --}}
