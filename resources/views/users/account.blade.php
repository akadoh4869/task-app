<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/modern-normalize@2.0.0/modern-normalize.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/tentative/account.css')}}"/>
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
                <li><a href="/setting"><i class="fa-solid fa-gear"></i><span>設　定</span></a></li>
                <li><img src="{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/images/default.png') }}" alt="アカウント">{{-- <span>プロフィール</span> --}}</li>
            </ul>
      </header>
        
       <main>
            <div class="main">
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

                

                

                <!-- （オプションオーバーレイなど必要に応じて追加） -->

            </div>
            

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
