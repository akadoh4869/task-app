<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
     @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <title>グループ作成</title>
</head>
<body>
    <div class="flex">

        <header>
            <!--  アプリ名 -->
            <h1 class="appname">Task Me</h1>

            <!--メニューバー-->
            <ul>
            <li><a href="/task">タスク管理</a></li>
            <li><a href="/create">作成</a></li>
            <li><a href="/task/share">共有事項</a></li>
            <li><a href="/setting">設定</a></li>
            <li>
                <a href="#">
                <img src="{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/images/default.png') }}" alt="アカウント" class="account">
                </a>
            </li>
            </ul>
        </header>

        <main>
            <div class="space-title">グループ作成</div>

            <form action="{{ route('groups.store') }}" method="POST" class="group-create-form">
                @csrf

                <div class="form-group">
                    <label for="group_name">グループ名</label>
                    <input type="text" id="group_name" name="group_name" required placeholder="グループ名を入力してください">
                    @error('name')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group center">
                    <button type="submit" class="submit-button">作成する</button>
                </div>
            </form>
        </main>


  </div>
</body>
</html>