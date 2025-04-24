<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>タスク管理</title>
  </head>
  <body>
    <div class="flex">
      

      <header>
        <!--  アプリ名 -->
        <h1 class="appname">タスクアプリ名</h1>

        <!--メニューバー-->
        <ul>
          <li><a href="/task">タスク管理</a></li>
          <li><a href="/create">作成</a></li>
          <li><a href="/share">共有事項</a></li>
          <li><a href="/setting">設定</a></li>
          <li>
            <a href="#"
              ><img src="./img/no_1.jpg" alt="アカウント" class="account"
            /></a>
          </li>
        </ul>
      </header>

      <main>
        <!--コンテンツ-->
        <section class="t-head">
          <div class="year">
            <a href="">
              <img src="./img/no_3.png" alt="pre-year" width="50px" />
              <!--前の年へ-->
            </a>
            <p>2025</p>
            <!--表示する年-->
            <a href="">
              <img src="./img/no_2.png" alt="next-year" width="50px" />
              <!--次の年へ-->
            </a>
          </div>
          <ul id="list">
            <li class="tab1">リスト</li>
            <li class="tab2">カレンダー</li>
          </ul>
        </section>

        <div class="tab-container">
          <section id="content-list" class="content active">
            <table>
                @foreach ($allPersonalTasks as $task)
                    <tr class="flex2">
                        <th>
                            {{ optional($task->start_date)->format('Ymd') ?? '未設定' }}〜{{ optional($task->due_date)->format('Ymd') ?? '未設定' }}
                        </th>
                        <td class="flex2">
                            <input type="checkbox" id="task-{{ $task->id }}" name="todo[]" value="{{ $task->id }}" />
                            <p>{{ $task->getStatusLabel() }}のタスク：{{ $task->task_name }}</p>
                        </td>
                    </tr>
                @endforeach
        
                @if ($allPersonalTasks->isEmpty())
                    <tr>
                        <td colspan="2">現在、個人タスクはありません。</td>
                    </tr>
                @endif
            </table>
        </section>
        

          <section id="content-calendar" class="content">
            <p>カレンダー表示エリア</p>

          </section>
        </div>

      </main>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
  </body>

 
</html>
