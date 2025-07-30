<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('css/tentative/task.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
    <title>タスク管理</title>
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
        <section class="t-head">
          <div class="year">
            <a href="#" id="prevYear">
              <img src="{{ asset('images/left.png') }}" alt="前の年" width="50px" />
            </a>
            <p id="yearDisplay">2025年</p>
            <a href="#" id="nextYear">
              <img src="{{ asset('images/right.png') }}" alt="次の年" width="50px" />
            </a>
            
          </div>
          <ul id="list">
            <li class="tab1 active" data-tab="list">リスト</li>
            <li class="tab2" data-tab="calendar">カレンダー</li>
          </ul>

          <div class="tab-content">
            <div id="listContent" class="tab-pane">リストの内容</div>
            <div id="calendarContent" class="tab-pane hidden">カレンダーの内容</div>
          </div>
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
<script> 
  document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('#list li');
    const contents = document.querySelectorAll('.tab-container .content');

    tabs.forEach(tab => {
      tab.addEventListener('click', function () {
        // タブの切り替え
        tabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        // 対応するセクションを表示
        const targetId = this.dataset.tab === 'list' ? 'content-list' : 'content-calendar';
        contents.forEach(c => c.classList.remove('active'));
        document.getElementById(targetId).classList.add('active');
      });
    });
  });

  let currentYear = 2025; // 初期値。Laravelの変数で渡してもOK
  const yearDisplay = document.getElementById('yearDisplay');

  document.getElementById('prevYear').addEventListener('click', function(e) {
    e.preventDefault();
    currentYear--;
    yearDisplay.textContent = currentYear + '年';
  });

  document.getElementById('nextYear').addEventListener('click', function(e) {
    e.preventDefault();
    currentYear++;
    yearDisplay.textContent = currentYear + '年';
  });

</script>

