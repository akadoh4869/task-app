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
    <script src="{{ asset('js/tentative/common.js') }}"></script>
    <title>共有事項</title>
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
            <form method="GET" action="{{ route('task.share') }}">
                <label for="group_id">グループ選択：</label>
                <select name="group_id" id="group_id" onchange="this.form.submit()">
                    @foreach ($groups as $group)
                        <option value="{{ $group->id }}" {{ $selectedGroupId == $group->id ? 'selected' : '' }}>
                            {{ $group->group_name }}
                        </option>
                    @endforeach

                    {{-- グループ作成オプション --}}
                    <option value="create" {{ $selectedGroupId === 'create' ? 'selected' : '' }}>
                        ＋ グループを作る
                    </option>
                </select>
            </form>

            {{-- 「グループを作る」が選択されているとき or 未所属のとき --}}
            @if ($selectedGroupId === 'create' || $groups->isEmpty())
                <div style="text-align: center; margin-top: 50px;">
                    <p>グループを作成してタスクを共有しましょう。</p>
                    <a href="{{ route('group.create') }}" class="btn" style="padding: 10px 20px; background: #3490dc; color: white; border-radius: 5px; text-decoration: none;">
                        グループを作成する
                    </a>
                </div>
            @elseif ($selectedGroupId)
                {{-- グループタスク表示 --}}
                <section id="content-list" class="content active">
                    <table>
                        @foreach ($groupTasks as $task)
                            <tr class="flex2">
                                <th>
                                    {{ optional($task->start_date)->format('Ymd') ?? '未設定' }}〜
                                    {{ optional($task->due_date)->format('Ymd') ?? '未設定' }}
                                </th>
                                <td class="flex2">
                                    <input type="checkbox" id="task-{{ $task->id }}" name="todo[]" value="{{ $task->id }}" />
                                    <a href="{{ route('task.detail', $task->id) }}">
                                        {{ $task->getStatusLabel() }}のタスク：{{ $task->task_name }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach

                        @if ($groupTasks->isEmpty())
                            <tr>
                                <td colspan="2">現在、グループタスクはありません。</td>
                            </tr>
                        @endif
                    </table>
                </section>

                <section id="content-calendar" class="content">
                    <p>カレンダー表示エリア</p>
                </section>
            @endif
        </div>



      </main>

  </div>
    
</body>
</html>