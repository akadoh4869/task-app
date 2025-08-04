<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
    <script src="{{ asset('js/tentative/common.js') }}"></script>
    <title>タスク管理ページ</title>
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
        <!--コンテンツ-->
        <section class="t-head">
          <div class="year">
            @if ($year > 2025)
              <a href="#" id="prevYear">
                <img src="{{ asset('images/left.png') }}" alt="前の年" width="50px" />
              </a>
            @else
              <span style="width: 50px; display: inline-block;"></span>
            @endif

            <p id="yearDisplay" data-year="{{ $year }}">{{ $year }}年</p>

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
                    {{ optional($task->start_date)->format('md') ?? '未設定' }}〜
                    {{ optional($task->due_date)->format('md') ?? '未設定' }}
                  </th>
                  <td class="flex2">
                    <input type="checkbox" onchange="completeTask({{ $task->id }}, this)">
                    <a href="{{ route('task.detail', $task->id) }}">
                      {{ $task->getStatusLabel() }}のタスク：{{ $task->task_name }}
                    </a>
                  </td>
                </tr>
              @endforeach

              @if ($allPersonalTasks->isEmpty())
                <tr>
                  <td colspan="2">現在、{{ $year }}年のタスクはありません。</td>
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
    <script>
      function completeTask(taskId, checkbox) {
          setTimeout(() => {
              fetch(`/task/${taskId}/status`, {
                  method: 'POST', // ← PATCHではなくPOSTで送る
                  headers: {
                      'Content-Type': 'application/json',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                  },
                  body: JSON.stringify({ 
                      _method: 'PATCH', // ← LaravelがこれでPATCHとして処理
                      status: 'completed' 
                  })
              })
              .then(response => {
                  if (response.ok) {
                      const row = checkbox.closest('tr');
                      if (row) row.remove();
                  } else {
                      alert('更新に失敗しました (status not ok)');
                      checkbox.checked = false;
                  }
              })
              .catch((err) => {
                  alert('通信エラー: ' + err.message);
                  checkbox.checked = false;
              });
          }, 1000);
      }
    </script>


  </body>

 
</html>


