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
                  <span class="kanban-count" id="count-not_started">{{ $allPersonalTasks->where('status', 'not_started')->count() }}</span>
                </div>

                <div class="kanban-col-body" id="col-not_started">
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
                          data-task-id="{{ $task->id }}"
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

                  {{-- ✅ ここを追加：クイック追加 --}}
                  <div class="kanban-quickadd" data-status="not_started">
                    <button type="button" class="quickadd-btn" aria-label="タスクを追加">＋</button>

                    <div class="quickadd-form" style="display:none;">
                      <input type="text"
                            class="quickadd-input"
                            placeholder="タスク名を入力して Enter"
                            maxlength="100">
                    </div>
                  </div>
                  
                </div>
                
              </div>

              {{-- 進行中 --}}
              <div class="kanban-col">
                <div class="kanban-col-head">
                  <span>進行中</span>
                  <span class="kanban-count" id="count-in_progress">{{ $allPersonalTasks->where('status', 'in_progress')->count() }}</span>
                </div>

                <div class="kanban-col-body" id="col-in_progress">
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
                          data-task-id="{{ $task->id }}"
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

                  {{-- ✅ ここを追加：クイック追加 --}}
                  <div class="kanban-quickadd" data-status="in_progress">
                    <button type="button" class="quickadd-btn" aria-label="タスクを追加">＋</button>

                    <div class="quickadd-form" style="display:none;">
                      <input type="text"
                            class="quickadd-input"
                            placeholder="タスク名を入力して Enter"
                            maxlength="100">
                    </div>
                  </div>
                </div>
              </div>


              {{-- 完了 --}}
              <div class="kanban-col">
                <div class="kanban-col-head head-completed">
                  <span>完了</span>
                  <span class="kanban-count" id="count-completed">{{ $completedTasks->count() }}</span>
                </div>

                <div class="kanban-col-body" id="col-completed">
                  @forelse ($completedTasks as $task)
                    <a href="{{ route('task.detail', $task->id) }}"
                      class="task-card task-row-link is-completed"
                      data-task-id="{{ $task->id }}">

                      @if ($task->start_date || $task->due_date)
                        <div class="task-date">
                          @if ($task->start_date) {{ $task->start_date->format('m/d') }} @endif
                          @if ($task->start_date && $task->due_date) 〜 @endif
                          @if ($task->due_date) {{ $task->due_date->format('m/d') }} @endif
                        </div>
                      @endif

                      <div class="task-main">
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
        if (!checkbox.checked) return;

        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        // ✅ いま押したチェックが入っているカード（aタグ）を取る
        const card = checkbox.closest('a.task-card.task-row-link') || checkbox.closest('.task-row-link');
        if (!card) return;

        // ✅ どの列から来たか判定（col-not_started / col-in_progress）
        const fromColBody = card.closest('.kanban-col-body');
        const fromColId = fromColBody ? fromColBody.id : null;       // col-not_started など
        const fromStatus = fromColId ? fromColId.replace('col-', '') : null; // not_started など

        checkbox.disabled = true;

        fetch(`/task/${taskId}/status`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            _method: 'PATCH',
            status: 'completed'
          })
        })
        .then(async (response) => {
          if (!response.ok) {
            checkbox.checked = false;
            checkbox.disabled = false;
            alert('更新に失敗しました (status not ok)');
            return;
          }

          // ✅ サーバーが task を返してくれるなら使う（無ければ後でフォールバック）
          let json = null;
          try { json = await response.json(); } catch(e) {}

          // ✅ ① まず元の列から消す（removeでもOKだが、完了列へ入れるなら移動がラク）
          // card.remove();

          // ✅ ② 完了列へ「カードをそのまま移動」する（見た目も担当者ラベルもそのまま残る）
          const completedCol = document.getElementById('col-completed');
          if (completedCol) {
            // 完了用の見た目にする（class & checkbox固定）
            card.classList.add('is-completed');

            // チェックボックスを「checked + disabled」にして完了状態っぽく
            checkbox.checked = true;
            checkbox.disabled = true;

            // もし「完了列ではクリックで詳細に飛べる」が維持したいなら a はそのままでOK
            completedCol.prepend(card);
          } else {
            // 完了列が見つからなければ消すだけ
            card.remove();
          }

          // ✅ ③ 件数の更新
          if (fromStatus) bumpCount(fromStatus, -1);
          bumpCount('completed', +1);

        })
        .catch((err) => {
          alert('通信エラー: ' + err.message);
          checkbox.checked = false;
          checkbox.disabled = false;
        });
      }

      function bumpCount(status, delta) {
        const el = document.getElementById(`count-${status}`);
        if (!el) return;
        const n = Number(el.textContent || 0);
        el.textContent = String(Math.max(0, n + delta));
      }

      // =====================================
      // ✅ カンバン各列の「＋」クイック追加（個人ページ）
      // =====================================
      document.addEventListener('DOMContentLoaded', () => {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        document.querySelectorAll('.kanban-quickadd').forEach((wrap) => {
          const status = wrap.dataset.status; // not_started / in_progress / completed
          const btn = wrap.querySelector('.quickadd-btn');
          const form = wrap.querySelector('.quickadd-form');
          const input = wrap.querySelector('.quickadd-input');
          if (!btn || !form || !input) return;

          const colIdMap = {
            not_started: 'col-not_started',
            in_progress: 'col-in_progress',
            completed: 'col-completed',
          };

          btn.addEventListener('click', () => {
            form.style.display = 'block';
            input.value = '';
            input.focus();
          });

          input.addEventListener('keydown', async (e) => {
            if (e.key === 'Escape') {
              form.style.display = 'none';
              return;
            }
            if (e.key !== 'Enter') return;

            e.preventDefault();

            const name = input.value.trim();
            if (!name) {
              form.style.display = 'none';
              return;
            }

            input.disabled = true;

            try {
              // ✅ あなたの既存ルートに合わせて /task にPOST
              const res = await fetch('/task/quick-add', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrf,
                  'Accept': 'application/json',
                },
                body: JSON.stringify({
                  task_name: name,
                  status: status,
                }),
              });

              if (!res.ok) throw new Error('作成に失敗しました');

              const data = await res.json(); // {id, task_name, status}

              const col = document.getElementById(colIdMap[status]);
              if (!col) throw new Error('追加先の列が見つかりません');

              const a = document.createElement('a');
              // ✅ あなたの詳細ルートに合わせる
              a.href = `/task/detail/${data.id}`;
              a.className = 'task-card task-row-link';
              a.dataset.taskId = data.id;

              a.innerHTML = `
                <div class="task-main">
                  <input
                    type="checkbox"
                    onclick="event.stopPropagation(); event.preventDefault();"
                    onchange="completeTask(${data.id}, this)"
                  >
                  <div class="task-text">${escapeHtml(data.task_name)}</div>
                </div>
              `;

              // ✅ 末尾に追加
              // col.appendChild(a);
              // ✅ 「＋」の直前に入れる（＝タスクは＋の上、＋は常に一番下）
              const quickAdd = document.querySelector(`.kanban-quickadd[data-status="${status}"]`);
              if (quickAdd) {
                quickAdd.parentNode.insertBefore(a, quickAdd);
              } else {
                // 予備：見つからなければ末尾
                col.appendChild(a);
              }


              // ✅ 件数 +1
              bumpCount(status, +1);

              form.style.display = 'none';

            } catch (err) {
              alert(err.message);
            } finally {
              input.disabled = false;
            }
          });

          input.addEventListener('blur', () => {
            if (!input.value.trim()) form.style.display = 'none';
          });
        });
      });

      function escapeHtml(str) {
        return String(str)
          .replaceAll('&', '&amp;')
          .replaceAll('<', '&lt;')
          .replaceAll('>', '&gt;')
          .replaceAll('"', '&quot;')
          .replaceAll("'", '&#039;');
      }


     

    </script>


  </body>

 
</html>


