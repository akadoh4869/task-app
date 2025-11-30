<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
    <title>プロフィール編集ページ</title>

    <style>
        /* ===== 自前モーダル（Bootstrap衝突回避のため固有名） ===== */
        .amodal-backdrop{
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            display:none;
            align-items:center;
            justify-content:center;
            z-index:3000;
        }
        .amodal-backdrop.is-open{ display:flex !important; }

        .amodal{
            background:#fff;
            border-radius:12px;
            width:min(560px,92vw);
            padding:20px;
            box-shadow:0 10px 30px rgba(0,0,0,.25);
        }
        .amodal header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:12px;
        }
        .amodal .close{
            background:none;
            border:none;
            font-size:24px;
            line-height:1;
            cursor:pointer;
        }
        .amodal .actions{
            display:flex;
            gap:8px;
            justify-content:flex-end;
            margin-top:16px;
        }
    </style>
</head>
<body>
<div class="flex">

    <!-- ヘッダー -->
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

    <!-- メイン -->
    <main style="flex:1; padding:20px; max-width:720px; margin:auto;">
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

</div>

{{-- ===== モーダル制御スクリプト（amodal版） ===== --}}
<script>
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
</script>

</body>
</html>
