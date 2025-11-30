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
        <li><a href="/setting"><i class="fa-solid fa-gear"></i><span>　設定　</span></a></li>
        <li><img src="{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/images/default.png') }}" alt="アカウント">{{-- <span>プロフィール</span> --}}</li>
      </ul>
    </header>
    
    <main>
      <div class="main">

        <form action="{{ route('task.updateDetail', $task->id) }}" method="POST">
          @csrf
          @method('PATCH')

          <div class="task-container">
            <div class="task-info">
              <div class="task-name">
                <h2>{{ $task->task_name }}</h2>
              </div>
              <div class="task-status">
                <span id="status-dot" class="status-dot"></span>
                <select name="status" class="status" id="status-select">
                  <option value="not_started" {{ $task->status === 'not_started' ? 'selected' : '' }}>未着手</option>
                  <option value="in_progress" {{ $task->status === 'in_progress' ? 'selected' : '' }}>進行中</option>
                  <option value="completed" {{ $task->status === 'completed' ? 'selected' : '' }}>完了</option>
                </select>
              </div>

            </div>
           
            <div class="create-date">
              <p>作成日: {{ $task->created_at->format('Y年m月d日') }}</p>

            </div>
            <div class="task-date">
              <input type="date" class="date" name="start_date" value="{{ optional($task->start_date)->format('Y-m-d') }}" max="9999-12-31"> ～
              <input type="date" class="date" name="due_date" value="{{ optional($task->due_date)->format('Y-m-d') }}" max="9999-12-31">
            </div>
            <div class="task-belong">
              <div class="task-group">
                @if ($task->group)
                  <p>グループ: <span>{{ $task->group->group_name }}</span></p>
                @else
                    <p>個人タスク</p>
                @endif

              </div>
              <div class="task-assignee">
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

                      <div class="assignee-item">
                          <img src="{{ $avatarPath }}" alt="{{ $user->user_name }}"  class="assignee-avatar" width="30" height="30" style="border-radius:50%;">
                          <span class="assignee-name">{{ $user->user_name }}</span>
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

                  @php
                    // まだアサインされていないメンバーだけ抽出
                    $addableUsers = $task->group->users->filter(function ($user) use ($assignees) {
                      return !$assignees->contains('id', $user->id);
                    });
                  @endphp

                  @if ($addableUsers->isNotEmpty())
                    <p>メンバーを追加:</p>

                    <div id="assignable-members" class="assignable-members">
                      @foreach ($addableUsers as $user)
                        @php
                          $avatarPath = $user->avatar && file_exists(public_path('storage/' . $user->avatar))
                              ? asset('storage/' . $user->avatar)
                              : asset('storage/images/default.png');
                        @endphp

                        <button type="button"
                                class="assignee-pill assignee-add"
                                data-user-id="{{ $user->id }}"
                                data-name="{{ $user->user_name }}"
                                data-avatar="{{ $avatarPath }}">
                          <img src="{{ $avatarPath }}" alt="{{ $user->user_name }}" class="assignee-avatar">
                          <span class="assignee-name">{{ $user->user_name }}</span>
                          <span class="assignee-plus">＋</span>
                        </button>
                      @endforeach
                    </div>
                  @endif




                @endif
              </div>
     
            </div>
            

            <div class="task-file">
               @php
                  use Illuminate\Support\Facades\Storage;
                  use Illuminate\Support\Str;
              @endphp

              @if ($task->attachments->isNotEmpty())
                  <p>添付ファイル:</p>

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

            </div>
            <div class="task-content">
              <textarea name="description" class="description" rows="5" style="width: 60%;" placeholder="　-内容-　">{{ $task->description }}</textarea>
              <br><br>

            </div>
            <div class="renew">
              <button type="submit" class="renew-button">更新</button>
            </div>
            
            

          </div>
            

            
            
        </form>

      </div>

    </main>

  </div>
      
</body>
</html>
<script>
  // ▼ × を押したときに担当メンバーから外す
  function removeUser(button) {
    // 自分が入っている assignee の箱を探す
    const chip = button.closest('.assignee-item, .assigned-user');
    if (!chip) return;

    chip.remove();

    // もし誰もいなくなったら「担当者なし」を表示したい場合
    const assignedList = document.getElementById('assigned-user-list');
    const stillExists  = assignedList?.querySelector('.assignee-item, .assigned-user');

    if (!stillExists) {
      let emptyLabel = document.getElementById('assigned-empty');
      if (!emptyLabel) {
        emptyLabel = document.createElement('div');
        emptyLabel.id = 'assigned-empty';
        emptyLabel.className = 'muted';
        emptyLabel.textContent = '（担当者なし：共有）';
        assignedList.appendChild(emptyLabel);
      } else {
        emptyLabel.style.display = 'block';
      }
    }
  }

  // ここから下に、元のモーダル・ステータスなどの処理を続ける
  // 画像とPDFはモーダルで開く。それ以外はデフォルト遷移
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
    content.innerHTML = '';
    if (kind === 'image') {
      const img = new Image();
      img.src = url;
      content.appendChild(img);
    } else if (kind === 'pdf') {
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

  document.addEventListener('DOMContentLoaded', () => {
    const select = document.getElementById('status-select');
    const dot    = document.getElementById('status-dot');

    if (select && dot) {
      function updateStatusColor() {
        const status = select.value;

        select.className = 'status';
        dot.className    = 'status-dot';

        select.classList.add(status);
        dot.classList.add(status);
      }

      select.addEventListener('change', updateStatusColor);
      updateStatusColor();
    }

    // ＋でアサイン追加する処理もここに続けてOK（前回渡したやつ）
    const assignedList       = document.getElementById('assigned-user-list');
    const candidateContainer = document.getElementById('assignable-members');

    function addAssignedChip(userId, name, avatar) {
      if (!assignedList) return;

      // 「担当者なし」のラベルがあれば隠す
      const emptyLabel = document.getElementById('assigned-empty');
      if (emptyLabel) emptyLabel.style.display = 'none';

      const wrapper = document.createElement('div');
      wrapper.className = 'assignee-item';
      wrapper.innerHTML = `
        <img src="${avatar}" alt="${name}" class="assignee-avatar" width="30" height="30" style="border-radius:50%;">
        <span class="assignee-name">${name}</span>
        <input type="hidden" name="assigned_user_ids[]" value="${userId}">
        <button type="button" class="remove-user" onclick="removeUser(this)">×</button>
      `;
      assignedList.appendChild(wrapper);
    }

    if (candidateContainer) {
      candidateContainer.addEventListener('click', (e) => {
        const btn = e.target.closest('.assignee-add');
        if (!btn) return;

        const userId = btn.dataset.userId;
        const name   = btn.dataset.name;
        const avatar = btn.dataset.avatar;

        addAssignedChip(userId, name, avatar);
        btn.remove();
      });
    }
  });
</script>
