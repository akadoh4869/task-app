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


{{-- ➀プロフィール編集 --}}
<section id="panel-profile-edit" class="setting-panel">
    <!-- メイン -->
    <main class="panel-inner">
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

        {{-- メイン：表示名 & アイコン --}}
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

        {{-- 個別変更（モーダル起動） --}}
        <div class="stack" style="display:grid; gap:12px; max-width:520px;">
            <div>
                <div class="label-sm text-muted">現在のメール</div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <strong>{{ $user->email }}</strong>
                    <button type="button" class="btn btn-light" data-modal-open="modal-email">メールアドレスを変更</button>
                </div>
            </div>

            <div>
                <div class="label-sm text-muted">現在のユーザーネーム</div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <strong>{{ $user->user_name }}</strong>
                    <button type="button" class="btn btn-light" data-modal-open="modal-username">ユーザーネームを変更</button>
                </div>
            </div>

            <div>
                <div class="label-sm text-muted">パスワード</div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <span>********</span>
                    <button type="button" class="btn btn-light" data-modal-open="modal-password">パスワードを変更</button>
                </div>
            </div>
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

{{-- ➁アカウント情報 --}}
<section id="panel-account-info" class="setting-panel">
    <!-- アカウント設定オーバーレイ -->
    <main class="panel-inner">
        <div class="overlay-content">
            <span class="close-btn" onclick="closeOverlay('account-overlay')">&times;</span>
            <h3>アカウント情報</h3>
            <p>ユーザーネーム: <strong>{{ $user->user_name }}</strong></p>
            <p>アカウント名:   <strong>{{ $user->name }}</strong></p>
            <p>メールアドレス: <strong>{{ $user->email }}</strong></p>
            <p>会員ステータス:
            <strong>
                @if($user->subscription_status)
                有料会員（買い切り）
                @else
                無料会員
                @endif
            </strong>
            </p>

        </div>
    </main>

</section>


{{-- ➂招待一覧 --}}
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
<section id="panel-completed" class="setting-panel">
    <main class="panel-inner">
        <!-- 完了タスク一覧オーバーレイ -->
        <div id="completed-tasks-overlay" class="overlay">
            <div class="overlay-content">
                <span class="close-btn" onclick="closeOverlay('completed-tasks-overlay')">&times;</span>
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
        </div>

    </main>
    
</section>