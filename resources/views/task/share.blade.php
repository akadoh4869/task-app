<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/tentative/task.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/tentative/share.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
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
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>共有事項</title>
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
          <li><a href="/setting"><i class="fa-solid fa-gear"></i><span>設定ああ</span></a></li>
          <li><img src="{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/images/default.png') }}" alt="アカウント">{{-- <span>プロフィール</span> --}}</li>
        </ul>
      </header>
      
      <main>
        <div class="task-page">
          
        </div>
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
          <div class="main-content">
            <div class="tab-container">
              <div class="select-group">
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

              </div>

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
                  {{-- ▼ グループタスク：リスト表示 --}}
                  <section id="content-list" class="content active">
                    <div class="task-list">

                      @forelse ($groupTasks as $task)

                        <a href="{{ route('task.detail', $task->id) }}" class="task-row-link">

                          {{-- 左：期間 --}}
                          <div class="task-date">
                            {{ optional($task->start_date)->format('md') ?? '未設定' }}〜
                            {{ optional($task->due_date)->format('md') ?? '未設定' }}
                          </div>

                          {{-- 右：チェック＋本文 --}}
                          <div class="task-main">
                            <input
                              type="checkbox"
                              onclick="event.stopPropagation(); event.preventDefault();"
                              onchange="completeTask({{ $task->id }}, this)"
                            >

                            <div class="task-text">
                              {{ $task->getStatusLabel() }}のタスク：{{ $task->task_name }}

                              {{-- ▼ 担当メンバーラベル --}}
                              @if ($task->group)
                                @php
                                  // 多対多: task_user 経由のリレーション
                                  $assignees = $task->assignedUsers ?? collect();
                                @endphp

                                <span class="task-group-label-wrap">
                                  @if ($assignees->isNotEmpty())
                                    @foreach ($assignees as $user)
                                      <span class="task-assignee-label">
                                        {{ $user->user_name }}
                                      </span>
                                    @endforeach
                                  @else
                                    <span class="task-assignee-label is-shared">
                                      共有
                                    </span>
                                  @endif
                                </span> 
                              @endif
                            </div>
                          </div>

                        </a>

                      @empty
                        {{-- ★ タスクがないときも、同じ .task-row-link の中で表示する --}}
                        <div class="task-row-link task-row-empty">
                          <div class="task-main">
                            <div class="task-text">
                              現在、グループタスクはありません。
                            </div>
                          </div>
                        </div>
                      @endforelse

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
                      <div class="gantt-body">
                        {{-- ▼ 実タスク分の行 --}}
                        @foreach($groupTasks as $task)
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
                          $minRows   = 12;                       // 最低表示したい行数
                          $taskCount = $groupTasks->count();     // 実際のタスク数
                          $emptyRows = max($minRows - $taskCount, 0); // 空行の数
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

                        
                    </section>
              @endif
            </div>
            <section class="group">
              
                {{-- グループメンバー一覧 --}}
                @if ($selectedGroup)
                  <h6>グループメンバー</h6>
                  <ul>
                    @forelse ($groupMembers as $member)
                      <li class="group-member-item">
                        <img src="{{ asset($member->avatar ? 'storage/' . $member->avatar : 'storage/images/default.png') }}"
                            alt="avatar" class="group-avatar">

                        <div class="group-member-info">
                          <span class="group-member-name">
                            {{ $member->user_name ?? $member->name }}
                          </span>

                          {{-- グループ離脱ボタン（ログインユーザーのみ） --}}
                          @if (auth()->id() === $member->id)
                            <form method="POST" action="{{ route('group.leave', $selectedGroupId) }}">
                              @csrf
                              @method('DELETE')
                              <button type="submit"
                                      onclick="return confirm('本当にこのグループを離脱しますか？')">
                                グループを離脱
                              </button>
                            </form>
                          @endif
                        </div>
                      </li>
                    @empty
                      <li>メンバーがいません</li>
                    @endforelse
                  </ul>

                  {{-- ユーザー検索・招待フォーム --}}
                  <form method="GET" action="{{ route('task.share') }}">
                    <input type="hidden" name="group_id" value="{{ $selectedGroupId }}">
                    <input type="text" name="search_user" placeholder="ユーザー名で検索" value="{{ request('search_user') }}">
                    <button type="submit">検索</button>
                  </form>

                  {{-- 招待候補の表示 --}}
                  @if ($inviteCandidates->isNotEmpty())
                    <p>以下のユーザーを招待できます：</p>
                    <ul>
                      @foreach ($inviteCandidates as $candidate)
                        <li>
                          <img src="{{ asset($candidate->avatar ? 'storage/' . $candidate->avatar : 'storage/images/default.png') }}" alt="avatar" width="30" height="30" style="border-radius: 50%; vertical-align: middle; margin-right: 8px;">
                          {{ $candidate->user_name }}

                          @if ($pendingInvitedUserIds->contains($candidate->id))
                            <span style="color: gray;">（招待中）</span>
                          @else
                            <form method="POST" action="{{ route('group.invite', $selectedGroupId) }}" style="display:inline;">
                              @csrf
                              <input type="hidden" name="user_id" value="{{ $candidate->id }}">
                              <button type="submit">招待</button>
                            </form>
                          @endif
                        </li>
                      @endforeach
                    </ul>
                  @endif

                  {{-- 招待中のユーザー --}}
                  @if ($pendingInvitedUsers->isNotEmpty())
                    <h4>招待中のユーザー：</h4>
                    <ul>
                      @foreach ($pendingInvitedUsers as $invited)
                        <li>
                          <img src="{{ asset($invited->avatar ? 'storage/' . $invited->avatar : 'storage/images/default.png') }}" alt="avatar" width="30" height="30" style="border-radius: 50%; vertical-align: middle; margin-right: 8px;">
                          {{ $invited->user_name }}
                        </li>
                      @endforeach
                    </ul>
                  @endif
                @endif

              
              
            </section>

          </div>


          

            
        

        

      </main>

  </div>
  <script>
    function completeTask(taskId, checkbox) {
        setTimeout(() => {
            fetch(`/task/${taskId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    _method: 'PATCH',
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