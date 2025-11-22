<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
    <title>アカウント設定ページ</title>
</head>
<body>
    <div class="flex">

        <header class="sidebar">
        <div class="sidebar-hover-zone"></div> <!-- ← 透明エリア追加 -->
        <div class="logo">
          <a href="/task">
            <img src="{{ asset('images/logo/logo2.png') }}" alt="Task Me ロゴ">
          </a>
        </div>

        <ul class="menu">
          <li><a href="/task"><i class="fa-solid fa-list-check"></i><span>タスク一覧</span></a></li>
          <li><a href="/create"><i class="fa-solid fa-plus"></i><span>新規作成</span></a></li>
          <li><a href="/task/share"><i class="fa-solid fa-user-group"></i><span>グループ別</span></a></li>
          <li><a href="/setting"><i class="fa-solid fa-gear"></i><span>設定ああ</span></a></li>
          <li><img src="{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/images/default.png') }}" alt="アカウント">{{-- <span>プロフィール</span> --}}</li>
        </ul>
      </header>
        
       <main>
            <div class="setting-list">
                <!-- アカウント設定 -->
                <div class="setting-item" onclick="openOverlay('account-overlay')">
                    <i class="fa-solid fa-user" style="color:#ff66cc;"></i>
                    <div class="setting-label">アカウント設定</div>
                </div>

                <!-- 完了タスク一覧 -->
                <div class="setting-item" onclick="openOverlay('completed-tasks-overlay')">
                    <i class="fa-solid fa-star" style="color:#5ce0f0;"></i>
                    <div class="setting-label">完了タスク一覧</div>
                </div>

                <!-- 有料オプション -->
                <div class="setting-item" onclick="openOverlay('option-overlay')">
                    <i class="fa-solid fa-star" style="color:#5ce0f0;"></i>
                    <div class="setting-label">有料オプション</div>
                </div>

                <!-- ログアウト -->
                <div class="setting-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fa-solid fa-sign-out-alt" style="color:#ff66cc;"></i>
                    <div class="setting-label">ログアウト</div>
                </div>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>

            <!-- アカウント設定オーバーレイ -->
            <div id="account-overlay" class="overlay">
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

                    <div class="overlay-actions" style="margin-top:16px; display:flex; gap:8px;">
                    <a href="{{ route('users.edit') }}" class="btn edit-btn">プロフィールを編集</a>
                    </div>
                </div>
            </div>

            <!-- 完了タスク一覧オーバーレイ -->
            <div id="completed-tasks-overlay" class="overlay">
                <div class="overlay-content">
                    <span class="close-btn" onclick="closeOverlay('completed-tasks-overlay')">&times;</span>
                    <h3>完了タスク一覧</h3>

                    <!-- プルダウン切り替え -->
                    <form method="GET" action="{{ route('account.index') }}">
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

            <!-- （オプションオーバーレイなど必要に応じて追加） -->

        </main>

    </div>
    @if ($showCompletedOverlay)
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                openOverlay('completed-tasks-overlay');
            });
        </script>
    @endif

    
</body>
</html>
 <script>
    // オーバーレイを開く：他のモーダルを閉じてから指定モーダルを開く
    function openOverlay(id) {
        document.querySelectorAll('.fullscreen-modal, .overlay').forEach(modal => {
            modal.style.display = 'none';
        });
        const target = document.getElementById(id);
        if (target) {
            target.style.display = 'flex';
        }
    }

    // オーバーレイを閉じる
    function closeOverlay(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
        }
    }
</script>
