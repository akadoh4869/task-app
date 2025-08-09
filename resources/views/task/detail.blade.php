<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/tentative/detail.css')}}"/>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <script src="{{ asset('js/tentative/detail.js') }}"></script>
    <title>詳細ページ</title>
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

      <form action="{{ route('task.updateDetail', $task->id) }}" method="POST">
          @csrf
          @method('PATCH')

          <h2>{{ $task->task_name }}</h2>

          <p>作成日: {{ $task->created_at->format('Y年m月d日') }}</p>
          {{-- <p>期限: {{ optional($task->due_date)->format('Y年m月d日') ?? '未設定' }}</p> --}}
          {{-- ✅ ここを修正済み --}}
          <p>開始日:</p>
          <input type="date" name="start_date" value="{{ optional($task->start_date)->format('Y-m-d') }}" max="9999-12-31">

          <p>期限:</p>
          <input type="date" name="due_date" value="{{ optional($task->due_date)->format('Y-m-d') }}" max="9999-12-31">


          @if ($task->group)
              <p>グループ: {{ $task->group->group_name }}</p>
          @else
              <p>個人タスク</p>
          @endif

          @if ($task->group && $task->group->users->isNotEmpty())
            <p>担当メンバー:</p>

            @php
                $assignees = $task->assignedUsers; // 多対多: task_user
            @endphp

            <div id="assigned-user-list" style="display:flex; flex-wrap:wrap; gap:15px;">
                @forelse ($assignees as $user)
                @php
                    $avatarPath = $user->avatar && file_exists(public_path('storage/' . $user->avatar))
                        ? asset('storage/' . $user->avatar)
                        : asset('storage/images/default.png');
                @endphp

                <div class="assigned-user" style="position:relative; display:flex; align-items:center; gap:5px; background:#f0f0f0; padding:5px 10px; border-radius:8px;">
                    <img src="{{ $avatarPath }}" alt="{{ $user->user_name }}" width="30" height="30" style="border-radius:50%;">
                    <span class="assigned-name">{{ $user->user_name }}</span>
                    <input type="hidden" name="assigned_user_ids[]" value="{{ $user->id }}">
                    <button type="button" class="remove-user" onclick="removeUser(this)">×</button>
                </div>
                @empty
                {{-- 誰もアサインされていない場合の表示 --}}
                <div id="assigned-empty" class="muted">
                    {{ $task->group_id ? '（担当者なし：共有）' : '（担当者なし）' }}
                </div>
                @endforelse
            </div>

            <br>

            <p>メンバーを追加:</p>
            <select id="add-user-select" onchange="addSelectedUser()">
                <option value="">-- メンバーを選択 --</option>
                @foreach ($task->group->users as $user)
                    @if (!$assignees->contains('id', $user->id))
                        @php
                            $avatarPath = $user->avatar && file_exists(public_path('storage/' . $user->avatar))
                                ? asset('storage/' . $user->avatar)
                                : asset('storage/images/default.png');
                        @endphp
                        <option value="{{ $user->id }}"
                                data-avatar="{{ $avatarPath }}"
                                data-name="{{ $user->user_name }}">
                            {{ $user->user_name }}
                        </option>
                    @endif
                @endforeach
            </select>
            @endif



          {{-- ステータス変更 --}}
          <p>ステータス:</p>
          <select name="status">
              <option value="not_started" {{ $task->status === 'not_started' ? 'selected' : '' }}>未着手</option>
              <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>進行中</option>
              <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>完了</option>
          </select>

          {{-- 詳細編集 --}}
          <p>詳細:</p>
          <textarea name="description" rows="5" style="width: 100%;">{{ $task->description }}</textarea>

          <br><br>
          <button type="submit">更新</button>
      </form>

        @if ($task->attachments->isNotEmpty())
            <h3>添付ファイル:</h3>
            <ul style="display: flex; gap: 20px; flex-wrap: wrap;">
                @foreach ($task->attachments as $file)
                    <li style="list-style: none; text-align: center;">
                        <a href="{{ asset('storage/' . $file->file_path) }}" target="_blank" class="tooltip-wrapper">
                            @php
                                $isImage = Str::startsWith($file->mime_type, 'image');
                                $ext = pathinfo($file->original_name, PATHINFO_EXTENSION);
                                $icon = match(strtolower($ext)) {
                                    'pdf' => 'pdf-icon.png',
                                    'xls', 'xlsx' => 'excel-icon.png',
                                    'ppt', 'pptx' => 'ppt-icon.png',
                                    default => 'file-icon.png',
                                };
                            @endphp

                            @if ($isImage)
                                <img src="{{ asset('storage/' . $file->file_path) }}" alt="画像" width="60" height="75"
                                    style="object-fit: cover; border: 1px solid #ccc;">
                            @else
                                <img src="{{ asset('icons/' . $icon) }}" alt="ファイル" width="64" height="64"><br>

                                <span class="tooltip-text">{{ $file->original_name }}</span>

                                <span style="display: block;
                                    max-width: 100px;
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                    white-space: nowrap;">
                                    {{ $file->original_name }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

      

    </main>

  </div>
    
    
</body>
</html>