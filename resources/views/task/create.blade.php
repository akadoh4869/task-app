<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite(['resources/sass/app.scss', 'resources/js/app.js'])
  <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
  <link rel="stylesheet" href="{{ asset('css/tentative/create.css')}}"/>
  <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>新規作成</title>

  <!-- タイル表示の最小CSS（create.cssに移動可） -->
  <style>
    .image-grid{ display:flex; flex-wrap:wrap; gap:10px; margin-top:10px; }
    .tile{ width:60px; height:75px; position:relative; display:inline-flex; align-items:center; justify-content:center; background:#fff; border:1px dashed #bbb; overflow:hidden; }
    .thumb{ width:100%; height:100%; object-fit:cover; border:1px solid #ccc; }
    .remove{ position:absolute; top:0; right:0; border:1px solid #ccc; background:#fff; border-radius:50%; padding:2px 6px; font-size:14px; line-height:1; cursor:pointer; }
    .add-tile{ cursor:pointer; }
    .add-tile i{ font-size:20px; opacity:.75; }
    /* 非画像の見た目 */
    .file-tile{ flex-direction:column; border-style:solid; }
    .file-tile i{ font-size:26px; opacity:.8; }
    .filemeta{ position:absolute; left:4px; right:4px; bottom:4px; font-size:10px; line-height:1.1; text-align:center; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  </style>
</head>

<body>
  <div class="flex">
    <header>
      <h1 class="appname">Task Me</h1>
      <ul>
        <li><a href="/task">タスク一覧</a></li>
        <li><a href="/create">新規作成</a></li>
        <li><a href="/task/share">グループ別</a></li>
        <li><a href="/setting">設定</a></li>
        <li>
          <a href="#">
            <img src="{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/images/default.png') }}" alt="アカウント" class="account">
          </a>
        </li>
      </ul>
    </header>

    <main>
      <section class="content active">
        <h2 class="title">新規タスク</h2>

        <form id="uploadForm" action="{{ route('task.store') }}" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitCompressedUploads();">
          @csrf

          @php $groups = Auth::user()->groups; @endphp

          <div class="flex2">
            <label>タスクの種類:</label>
            <select name="task_type_combined" id="task-type" onchange="toggleAssigneeSection()">
              <option value="personal">個人タスク</option>
              @foreach ($user->groups as $group)
                <option value="group_{{ $group->id }}">{{ $group->group_name }}</option>
              @endforeach
            </select>

            <div id="assignee-section" style="display:none; margin-top:15px;">
              <p>担当者を選択:</p>
              @foreach ($user->groups as $group)
                <div class="assignee-group" data-group-id="{{ $group->id }}" style="display:none;">
                  @foreach ($group->users as $member)
                    <label style="margin-right:10px;">
                      <input type="checkbox" name="assigned_user_ids[]" value="{{ $member->id }}">
                      {{ $member->user_name }}
                    </label>
                  @endforeach
                </div>
              @endforeach
            </div>
          </div>

          <br>

          <input type="date" name="start_date" max="9999-12-31"> 〜
          <input type="date" name="due_date" max="9999-12-31">

          <br><br>

          <input type="text" id="task-name" name="task_name" placeholder="タスク名">

          <br><br>

          <div class="flex4">
            <div class="textarea-container">

              <!-- アップローダ -->
              <div class="create-image" id="uploader">
                <!-- 画像に限らず選べるように accept を緩める（必要なら特定拡張子に） -->
                <input type="file" id="file-input" multiple style="display:none" accept="*/*">
                <div id="image-grid" class="image-grid"></div>
              </div>

              <!-- 上段メモ -->
              <textarea name="memo" id="content" class="content" rows="5" cols="33" placeholder="メモ"></textarea>
            </div>
          </div>

          <br>

          <p>詳細:</p>
          <textarea name="description" rows="5" style="width:100%;"></textarea>

          <br><br>

          <button type="submit" class="create-button">作成</button>
        </form>
      </section>
    </main>
  </div>

  <script>
    // ========= ファイルUI & 送信 =========
    const MAX_FILES = 5;
    let selectedItems = []; // { file: File, isImage: boolean, preview?: string }

    const input = document.getElementById('file-input');
    const grid  = document.getElementById('image-grid');

    const imageExtRe = /\.(jpe?g|png|gif|webp|bmp|svg)$/i;
    function isImageFile(file){
      return (file.type && file.type.startsWith('image/')) || imageExtRe.test(file.name);
    }
    function iconFor(name){
      const ext = (name.split('.').pop() || '').toLowerCase();
      const map = {
        pdf: 'fa-file-pdf',
        xls: 'fa-file-excel', xlsx: 'fa-file-excel', csv: 'fa-file-excel',
        doc: 'fa-file-word',  docx: 'fa-file-word',
        ppt: 'fa-file-powerpoint', pptx: 'fa-file-powerpoint',
        zip: 'fa-file-zipper', rar: 'fa-file-zipper', '7z': 'fa-file-zipper',
        txt: 'fa-file-lines',
      };
      return map[ext] || 'fa-file';
    }

    function renderGrid(){
      grid.innerHTML = '';

      selectedItems.forEach((item) => {
        const tile = document.createElement('div');
        tile.className = 'tile' + (item.isImage ? '' : ' file-tile');

        if (item.isImage){
          if (!item.preview) item.preview = URL.createObjectURL(item.file);
          const img = document.createElement('img');
          img.className = 'thumb';
          img.src = item.preview;
          img.alt = item.file.name;
          tile.appendChild(img);
        } else {
          const ic = document.createElement('i');
          ic.className = `fa-regular ${iconFor(item.file.name)}`;
          tile.appendChild(ic);

          const meta = document.createElement('div');
          meta.className = 'filemeta';
          meta.textContent = item.file.name;
          tile.appendChild(meta);
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = '×';
        btn.className = 'remove';
        btn.onclick = () => {
          selectedItems = selectedItems.filter(x => x !== item);
          renderGrid();
        };
        tile.appendChild(btn);

        grid.appendChild(tile);
      });

      if (selectedItems.length < MAX_FILES){
        const add = document.createElement('div');
        add.className = 'tile add-tile';
        add.innerHTML = '<i class="fa-regular fa-file-arrow-up" aria-hidden="true"></i>';
        add.title = 'ファイルを追加';
        add.onclick = () => input.click();
        grid.appendChild(add);
      }
    }

    input.addEventListener('change', (e) => {
      const files = Array.from(e.target.files);
      const remaining = MAX_FILES - selectedItems.length;
      const toAdd = files.slice(0, Math.max(remaining, 0));

      if (toAdd.length > 0){
        selectedItems.push(...toAdd.map(f => ({ file:f, isImage:isImageFile(f) })));
        renderGrid();
      }
      if (files.length > toAdd.length){
        alert(`最大${MAX_FILES}件までです。`);
      }
      input.value = '';
    });

    // 初期描画
    renderGrid();

    // 圧縮してFormDataへ（画像は圧縮、非画像はそのまま）
    function prepareCompressedUploads(form) {
      const formData = new FormData(form);

      const handleOne = (item) => new Promise((resolve) => {
        if (!item.isImage){
          // 非画像はそのまま 'files[]'
          formData.append('files[]', item.file, item.file.name);
          return resolve();
        }

        // 画像は縮小圧縮して 'images[]'
        const reader = new FileReader();
        reader.onload = (e) => {
          const img = new Image();
          img.src = e.target.result;
          img.onload = () => {
            const canvas = document.createElement('canvas');
            const maxWidth = 800;
            const scale = Math.min(maxWidth / img.width, 1);
            canvas.width = img.width * scale;
            canvas.height = img.height * scale;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            canvas.toBlob((blob) => {
              formData.append('images[]', blob, item.file.name);
              resolve();
            }, 'image/jpeg', 0.7);
          };
        };
        reader.readAsDataURL(item.file);
      });

      return Promise.all(selectedItems.map(handleOne)).then(() => formData);
    }

    async function submitCompressedUploads() {
      const form = document.getElementById('uploadForm');
      const formData = await prepareCompressedUploads(form);

      fetch(form.action, { method:'POST', body: formData })
        .then(res => {
          if (res.ok) {
            window.location.href = '/task';
          } else {
            alert('アップロードに失敗しました');
          }
        })
        .catch(() => alert('通信エラーが発生しました'));
    }

    // 既存：担当UI
    function toggleGroupSelect() {
      const typeSelect = document.getElementById('task-type');
      const groupSelect = document.getElementById('group-select');
      if (groupSelect) groupSelect.style.display = typeSelect.value === 'group' ? 'inline-block' : 'none';
    }
    function toggleAssigneeSection() {
      const select = document.getElementById('task-type');
      const value = select.value;
      const assigneeSection = document.getElementById('assignee-section');
      const allGroups = document.querySelectorAll('.assignee-group');
      allGroups.forEach(g => g.style.display = 'none');
      if (value.startsWith('group_')) {
        const groupId = value.replace('group_', '');
        const groupElement = document.querySelector(`.assignee-group[data-group-id="${groupId}"]`);
        if (groupElement) { groupElement.style.display = 'block'; assigneeSection.style.display = 'block'; }
      } else {
        assigneeSection.style.display = 'none';
      }
    }
  </script>
</body>
</html>
