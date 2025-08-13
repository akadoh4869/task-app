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

         @php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp

@if ($task->attachments->isNotEmpty())
  <h3>添付ファイル:</h3>

  <ul class="attach-strip">
    @foreach ($task->attachments as $file)
      @php
        $raw  = $file->file_path ?? '';
        $url  = Str::startsWith($raw, ['http://','https://'])
                 ? $raw
                 : (Str::startsWith($raw, ['storage/','/storage/']) ? asset(ltrim($raw,'/')) : Storage::url($raw));

        $name = $file->original_name ?: basename($raw);
        $mime = $file->mime_type ?? '';
        $isImage = Str::startsWith($mime, 'image') || preg_match('/\.(jpe?g|png|gif|webp|bmp|svg)$/i', $raw);
        $isPdf   = Str::startsWith($mime, 'application/pdf') || preg_match('/\.pdf$/i', $raw);

        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $iconMap = [
          'pdf'=>'fa-file-pdf','doc'=>'fa-file-word','docx'=>'fa-file-word',
          'xls'=>'fa-file-excel','xlsx'=>'fa-file-excel','csv'=>'fa-file-excel',
          'ppt'=>'fa-file-powerpoint','pptx'=>'fa-file-powerpoint',
          'zip'=>'fa-file-zipper','rar'=>'fa-file-zipper','7z'=>'fa-file-zipper',
        ];
        $iconClass = $iconMap[$ext] ?? ($isImage ? 'fa-image' : 'fa-file');
      @endphp

      <li>
        <a
          href="{{ $url }}"
          class="attach-tile"
          data-url="{{ $url }}"
          data-kind="{{ $isImage ? 'image' : ($isPdf ? 'pdf' : 'other') }}"
          aria-label="{{ $name }}"
          title="{{ $name }}"
        >
          <div class="tile-box">
            @if ($isImage)
              <img src="{{ $url }}" alt="">
            @else
              <div class="icon-60x75"><i class="fa-solid {{ $iconClass }}" aria-hidden="true"></i></div>
            @endif
          </div>
          <span class="tt">{{ $name }}</span>
        </a>
      </li>
    @endforeach
  </ul>

  {{-- 画像/PDF プレビュー用モーダル --}}
  <div id="viewer-modal" class="viewer hidden" aria-hidden="true">
    <div class="viewer__backdrop" data-close="1"></div>
    <div class="viewer__body">
      <button type="button" class="viewer__close" data-close="1">×</button>
      <div id="viewer-content"></div>
    </div>
  </div>
@endif


      

    </main>

  </div>
    
    
</body>
</html>
<script>
  // 画像とPDFはモーダルで開く。それ以外はデフォルト遷移（新規タブにしたければ target="_blank" を付ける）
  document.addEventListener('click', function(e){
    const a = e.target.closest('.attach-tile');
    if (!a) return;

    const kind = a.getAttribute('data-kind');
    const url  = a.getAttribute('data-url');

    if (kind === 'image' || kind === 'pdf') {
      e.preventDefault();
      openViewer(kind, url);
    }
  });

  const modal = document.getElementById('viewer-modal');
  const content = document.getElementById('viewer-content');

  function openViewer(kind, url){
    // 中身を入れ替え
    content.innerHTML = '';
    if (kind === 'image') {
      const img = new Image();
      img.src = url;
      content.appendChild(img);
    } else if (kind === 'pdf') {
      // ブラウザのPDFビューワで表示
      const iframe = document.createElement('iframe');
      iframe.src = url;
      content.appendChild(iframe);
    }
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
  }

  function closeViewer(){
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    content.innerHTML = '';
  }

  modal?.addEventListener('click', (e) => {
    if (e.target.dataset.close === '1') closeViewer();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeViewer();
  });
</script>
