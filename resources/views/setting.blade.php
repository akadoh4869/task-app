<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  @vite(['resources/sass/app.scss', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
  <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>設定</title>
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
          <li><a href="/share">共有事項</a></li>
          <li><a href="/setting">設定</a></li>
          <li>
            <a href="#">
             <img src="{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/images/default.png') }}" alt="アカウント" class="account">


            </a>
          </li>
        </ul>
      </header>
    <main>
      <!--コンテンツ-->
      <section class="">
        <div>
          <div>
            <h2 class="title">設定</h2>
            <div class="mbox">
              <ul>
                <li>アカウント</li>
                <li>お問い合わせ</li>
                <li>アプリについて<span>利用規約</span></li>
                <li>ログアウト</li>
                <li>退会</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>
  <script src="./JS/app.js"></script>
</body>


</html>