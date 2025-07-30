<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <title>詳細ページ</title>
</head>
<body>
    <header>
        <!--  アプリ名 -->
        <h1 class="appname">Task Me</h1>

        <!--メニューバー-->
        <ul>
          <li><a href="/task">タスク管理</a></li>
          <li><a href="/create">作成</a></li>
          <li><a href="/share">共有事項</a></li>
          <li><a href="/setting">設定</a></li>
          <li>
            <a href="#">
             <img src="{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/images/default.png') }}" alt="アカウント" class="account">


            </a>
          </li>
        </ul>
      </header>
    詳細
    
</body>
</html>