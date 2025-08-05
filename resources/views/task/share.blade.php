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
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
        <div class="main">
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
          </section>
          <section>
            {{-- グループメンバー一覧 --}}
            @if ($selectedGroup)
              <h3>グループメンバー一覧</h3>
              <ul>
                @forelse ($groupMembers as $member)
                  <li>{{ $member->user_name ?? $member->name }}</li>
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

              @if ($inviteCandidates->isNotEmpty())
                  <p>以下のユーザーを招待できます：</p>
                  <ul>
                      @foreach ($inviteCandidates as $candidate)
                          <li>
                              {{ $candidate->user_name }}
                              <form method="POST" action="{{ route('group.invite', $selectedGroupId) }}" style="display:inline;">
                                  @csrf
                                  <input type="hidden" name="user_id" value="{{ $candidate->id }}">
                                  <button type="submit">招待</button>
                              </form>
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