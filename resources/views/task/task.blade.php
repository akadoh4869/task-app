<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/modern-normalize@2.0.0/modern-normalize.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/tentative/task.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
    @php
    use Carbon\Carbon;

    // コントローラから $year を受け取っている前提
    $startDate = Carbon::create($year, 1, 1);
    $endDate   = $startDate->copy()->endOfYear();
    $days      = $startDate->diffInDays($endDate) + 1; // 365 or 366
    @endphp

    <script>
      window.taskCalendar = {
        startDate: "{{ $startDate->format('Y-m-d') }}",
        endDate: "{{ $endDate->format('Y-m-d') }}",
        days: {{ $days }}
      };
    </script>
    <script src="{{ asset('js/tentative/common.js') }}"></script>
    <title>タスク管理ページ</title>
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
        <!--コンテンツ-->
        <section class="t-head">
          <div class="year">
            @if ($year > 2025)
              <a href="#" id="prevYear"><</a>
            @else
              <span style="width: 50px; display: inline-block;"></span>
            @endif

            <p id="yearDisplay" data-year="{{ $year }}">{{ $year }}年</p>

            <a href="#" id="nextYear">></a>
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
            <div class="kanban">

              {{-- 未着手 --}}
              <div class="kanban-col">
                <div class="kanban-col-head">
                  <span>未着手</span>
                  <span class="kanban-count">{{ $allPersonalTasks->where('status', 'not_started')->count() }}</span>
                </div>

                <div class="kanban-col-body">
                  @foreach ($allPersonalTasks->where('status', 'not_started') as $task)
                    <a href="{{ route('task.detail', $task->id) }}" class="task-card task-row-link" data-task-id="{{ $task->id }}">
                      @if ($task->start_date || $task->due_date)
                        <div class="task-date">
                          @if ($task->start_date)
                            {{ $task->start_date->format('m/d') }}
                          @endif

                          @if ($task->start_date && $task->due_date)
                            〜
                          @endif

                          @if ($task->due_date)
                            {{ $task->due_date->format('m/d') }}
                          @endif
                        </div>
                      @endif

                      <div class="task-main">
                        <input
                          type="checkbox"
                          onclick="event.stopPropagation();"
                          onchange="completeTask({{ $task->id }}, this)"
                        >

                        <div class="task-text">
                          {{ $task->task_name }}
                          @if ($task->group)
                            <span class="task-group-label">{{ $task->group->group_name }}</span>
                          @endif
                        </div>
                      </div>
                    </a>
                  @endforeach

                  @if ($allPersonalTasks->where('status','not_started')->isEmpty())
                    <p class="empty-text">未着手のタスクはありません</p>
                  @endif
                </div>
              </div>

              {{-- 進行中 --}}
              <div class="kanban-col">
                <div class="kanban-col-head">
                  <span>進行中</span>
                  <span class="kanban-count">{{ $allPersonalTasks->where('status', 'in_progress')->count() }}</span>
                </div>

                <div class="kanban-col-body">
                  @foreach ($allPersonalTasks->where('status', 'in_progress') as $task)
                    <a href="{{ route('task.detail', $task->id) }}" class="task-card task-row-link" data-task-id="{{ $task->id }}">
                      @if ($task->start_date || $task->due_date)
                        <div class="task-date">
                          @if ($task->start_date)
                            {{ $task->start_date->format('m/d') }}
                          @endif

                          @if ($task->start_date && $task->due_date)
                            〜
                          @endif

                          @if ($task->due_date)
                            {{ $task->due_date->format('m/d') }}
                          @endif
                        </div>
                      @endif

                      <div class="task-main">
                        <input
                          type="checkbox"
                          onclick="event.stopPropagation();"
                          onchange="completeTask({{ $task->id }}, this)"
                        >

                        <div class="task-text">
                          {{ $task->task_name }}
                          @if ($task->group)
                            <span class="task-group-label">{{ $task->group->group_name }}</span>
                          @endif
                        </div>
                      </div>
                    </a>
                  @endforeach

                  @if ($allPersonalTasks->where('status','in_progress')->isEmpty())
                    <p class="empty-text">進行中のタスクはありません</p>
                  @endif
                </div>
              </div>


              {{-- 完了 --}}
            <div class="kanban-col">
              <div class="kanban-col-head head-completed">
                <span>完了</span>
                <span class="kanban-count">{{ $completedTasks->count() }}</span>
              </div>

              <div class="kanban-col-body" id="kanban-completed-body">
                @forelse ($completedTasks as $task)
                  <a href="{{ route('task.detail', $task->id) }}"
                    class="task-card task-row-link is-completed"
                    data-task-id="{{ $task->id }}">

                    @if ($task->start_date || $task->due_date)
                      <div class="task-date">
                        @if ($task->start_date)
                          {{ $task->start_date->format('m/d') }}
                        @endif

                        @if ($task->start_date && $task->due_date)
                          〜
                        @endif

                        @if ($task->due_date)
                          {{ $task->due_date->format('m/d') }}
                        @endif
                      </div>
                    @endif

                    <div class="task-main">
                      {{-- 完了列は固定チェック --}}
                      <input type="checkbox" checked disabled onclick="event.stopPropagation();">

                      <div class="task-text">
                        {{ $task->task_name }}
                        @if ($task->group)
                          <span class="task-group-label">{{ $task->group->group_name }}</span>
                        @endif
                      </div>
                    </div>
                  </a>

                @empty
                  <p class="empty-text">完了タスクはありません</p>
                @endforelse
              </div>
            </div>

            </div>
          </section>


          {{-- カレンダー --}}
          <section id="content-calendar" class="content">
            <div class="gantt-wrapper">

              {{-- =========================
                  ヘッダー部
              ========================== --}}
              <div class="gantt-header">
                <div class="gantt-task-col">タスク名</div>
                <div class="gantt-timeline">

                  {{-- 月ラベル行 --}}
                  <div class="gantt-month-row">
                    @php
                      $prevMonth = null;
                      $start = $startDate->copy();
                      $end = $endDate->copy();
                    @endphp

                    @while ($start->lte($end))
                      @php
                        $monthStart = $start->copy()->startOfMonth();
                        $monthEnd = $start->copy()->endOfMonth();
                        $daysInMonth = $monthEnd->diffInDays($monthStart) + 1;
                      @endphp
                      <div class="gantt-month" style="width: calc(var(--day-width) * {{ $daysInMonth }})">
                        {{ $start->format('n月') }}
                      </div>
                      @php $start->addMonth(); @endphp
                    @endwhile
                  </div>

                  {{-- 日付ラベル行 --}}
                  <div class="gantt-day-row">
                    @php $d = $startDate->copy(); @endphp
                    @while ($d->lte($endDate))
                      <div class="gantt-day gantt-number_day" data-date="{{ $d->format('Y-m-d') }}">
                        <span class="day-label">{{ $d->format('j') }}</span>
                      </div>
                      @php $d->addDay(); @endphp
                    @endwhile
                  </div>

                </div>
              </div>

              {{-- =========================
                  ボディ部
              ========================== --}}

              @php
                $minRows = 12;
                $tasksForCalendar = $allPersonalTasks->take(10); // ← 最大10件だけ表示（不要ならtake(10)消す）
              @endphp

              <div class="gantt-body">
                @foreach($allPersonalTasks as $task)
                  <div class="gantt-row">
                    <div class="gantt-task-col">{{ $task->task_name }}</div>
                    <div class="gantt-timeline">

                      {{-- 📅 各タスク行にも日付セルを生成（透明背景） --}}
                      @php $d = $startDate->copy(); @endphp
                      @while ($d->lte($endDate))
                        <div class="gantt-day" data-date="{{ $d->format('Y-m-d') }}"></div>
                        @php $d->addDay(); @endphp
                      @endwhile

                      {{-- 📊 タスクバー --}}
                      @if ($task->start_date && $task->due_date)
                        @php $isOverdue = $task->due_date->isPast(); @endphp
                        <div class="gantt-bar"
                            data-start="{{ $task->start_date->format('Y-m-d') }}"
                            data-end="{{ $task->due_date->format('Y-m-d') }}"
                            data-overdue="{{ $isOverdue ? '1' : '0' }}">
                          <span class="gantt-label">{{ $task->task_name }}</span>
                        </div>
                      @endif
                      
                    </div>
                  </div>
                @endforeach

                {{-- ▼ 足りない分を「空行」で埋める（最低10行にする） --}}
                @php
                  $taskCount = $tasksForCalendar->count();
                  $emptyRows = max($minRows - $taskCount, 0);
                @endphp

                @for ($i = 0; $i < $emptyRows; $i++)
                  <div class="gantt-row gantt-row-empty">
                    <div class="gantt-task-col">&nbsp;</div>
                    <div class="gantt-timeline">
                      @php $d = $startDate->copy(); @endphp
                      @while ($d->lte($endDate))
                        <div class="gantt-day" data-date="{{ $d->format('Y-m-d') }}"></div>
                        @php $d->addDay(); @endphp
                      @endwhile
                    </div>
                  </div>
                @endfor

              </div>
            </div>
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
                      // const row = checkbox.closest('tr');
                      const row = checkbox.closest('.task-row-link');
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


