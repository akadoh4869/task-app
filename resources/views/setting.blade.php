<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/sass/app.scss', 'resources/js/app.js'])
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/modern-normalize@2.0.0/modern-normalize.min.css">
  <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
  <link rel="stylesheet" href="{{ asset('css/tentative/setting.css')}}"/>
  <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
  <script src="{{ asset('js/tentative/setting.js') }}"></script>
  <title>設定</title>
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
                {{-- 目次 --}}
                <div class="setting-wrapper">
                    <div class="setting-container">
                        {{-- 大カテゴリ --}}
                        <div class="section-title">アカウント</div>
                        {{-- アカウントメニュー --}}
                        <div class="setting-item" data-panel="panel-profile-edit">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <div class="setting-label">プロフィール編集</div>
                        </div>
                        <div class="setting-item" data-panel="panel-account-info">
                            <i class="fa-solid fa-user" ></i>
                            <div class="setting-label">アカウント情報</div>
                        </div>
                        <div class="setting-item" data-panel="panel-invitations">
                            <i class="fa-solid fa-envelope"></i>
                            <div class="setting-label">招待一覧</div>
                        </div>
                        <div class="setting-item" data-panel="panel-completed">
                            <i class="fa-solid fa-star"></i>
                            <div class="setting-label">完了タスク一覧</div>
                        </div>
                        <div class="setting-item" data-panel="panel-options">
                            <i class="fa-solid fa-gem"></i>
                            <div class="setting-label">有料オプション</div>
                        </div>
                        {{-- TaskMe規約 --}}
                        <div class="section-title">TaskMe規約</div>
                        <div class="setting-item" data-panel="panel-terms">
                            <i class="fa-solid fa-file-lines"></i>
                            <div class="setting-label">利用規約</div>
                        </div>

                        <div class="setting-item" data-panel="panel-privacy">
                            <i class="fa-solid fa-user-lock"></i>
                            <div class="setting-label">プライバシーポリシー</div>
                        </div>

                        <div class="setting-item" data-panel="panel-copyright">
                            <i class="fa-solid fa-copyright"></i>
                            <div class="setting-label">著作権情報</div>
                        </div>
                        {{-- 🔽 ここがバージョン表示（クリックしても何も起きない） --}}
                        <div class="setting-item setting-item-static">
                            <i class="fa-solid fa-code-branch"></i>
                            <div class="setting-label">
                                バージョン
                                <span class="setting-subtext">1.0.0</span>
                            </div>
                        </div>
                        {{-- キャッシュ・ログアウト・退会 --}}
                        <div class="section-title">その他</div>
                        <div class="setting-item" data-panel="panel-cache">
                            <i class="fa-solid fa-broom"></i>
                            <div class="setting-label">キャッシュクリア</div>
                        </div>

                        <div class="setting-item" data-panel="panel-contact">
                            <i class="fa-solid fa-comments"></i>
                            <div class="setting-label">お問い合せ</div>
                        </div>

                        <div class="setting-item"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <div class="setting-label">ログアウト</div>
                        </div>

                        <div class="setting-item" data-panel="panel-withdraw">
                            <i class="fa-solid fa-hand"></i>
                            <div class="setting-label">退会する</div>
                        </div>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                            @csrf
                        </form>

                        @if(Auth::user() && Auth::user()->is_admin)
                            <div class="setting-item" data-panel="panel-admin">
                                <i class="fa-solid fa-user-shield"></i>
                                <div class="setting-label">管理者ページ</div>
                            </div>
                        @endif

                    </div>
                    {{-- ✅ 右側：表示エリア（ここに @include を書く） --}}
                    <div class="setting-detail">

                        {{-- 初期表示 --}}

                        {{-- ✅ アカウント系 --}}
                        @include('partials.account')

                        {{-- ✅ 規約系 --}}
                        @include('partials.policy')

                        {{-- ✅ その他 --}}
                        @include('partials.others')

                    </div>

                </div>
            </div>

        </main>
    </div>
  <script src="./JS/app.js"></script>
</body>
</html>
    