// resources/js/tentative/create.js

document.addEventListener('DOMContentLoaded', () => {
  // ========= ファイルUI & 送信 =========
  const MAX_FILES = 5;
  let selectedItems = []; // { file: File, isImage: boolean, preview?: string }

  const input = document.getElementById('file-input');
  const grid  = document.getElementById('image-grid');
  const form  = document.getElementById('uploadForm');

  // 画像判定用
  const imageExtRe = /\.(jpe?g|png|gif|webp|bmp|svg)$/i;
  function isImageFile(file) {
    return (file.type && file.type.startsWith('image/')) || imageExtRe.test(file.name);
  }

  // 拡張子に応じて FontAwesome アイコンを決める
  function iconFor(name) {
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

  // サムネイル／ファイル一覧の描画
  function renderGrid() {
    if (!grid) return;

    grid.innerHTML = '';

    selectedItems.forEach((item) => {
      const tile = document.createElement('div');
      tile.className = 'tile' + (item.isImage ? '' : ' file-tile');

      if (item.isImage) {
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

    // 追加タイル
    if (selectedItems.length < MAX_FILES) {
      const add = document.createElement('div');
      add.className = 'tile add-tile';
      add.innerHTML = '<i class="fa-regular fa-file-arrow-up" aria-hidden="true"></i>';
      add.title = 'ファイルを追加';
      add.onclick = () => input && input.click();
      grid.appendChild(add);
    }
  }

  // input change ハンドラ
  if (input && grid) {
    input.addEventListener('change', (e) => {
      const files = Array.from(e.target.files || []);
      const remaining = MAX_FILES - selectedItems.length;
      const toAdd = files.slice(0, Math.max(remaining, 0));

      if (toAdd.length > 0) {
        selectedItems.push(...toAdd.map(f => ({ file: f, isImage: isImageFile(f) })));
        renderGrid();
      }
      if (files.length > toAdd.length) {
        alert(`最大${MAX_FILES}件までです。`);
      }
      input.value = '';
    });

    // 初期描画
    renderGrid();
  }

  // 画像は縮小・圧縮しつつ FormData を組み立てる
  function prepareCompressedUploads(targetForm) {
    const formData = new FormData(targetForm);

    const handleOne = (item) => new Promise((resolve) => {
      if (!item.isImage) {
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

  // Blade 側の onsubmit から呼べるようにグローバル公開
  window.submitCompressedUploads = async function () {
    if (!form) return;

    const formData = await prepareCompressedUploads(form);

    fetch(form.action, { method: 'POST', body: formData })
      .then(res => {
        if (res.ok) {
          window.location.href = '/task';
        } else {
          alert('アップロードに失敗しました');
        }
      })
      .catch(() => alert('通信エラーが発生しました'));
  };

  // ======== 担当UI（グループ選択） ========

  // 使っていないかもしれないが、念のため元の関数も公開しておく
  window.toggleGroupSelect = function () {
    const typeSelect = document.getElementById('task-type');
    const groupSelect = document.getElementById('group-select');
    if (!typeSelect || !groupSelect) return;
    groupSelect.style.display = typeSelect.value === 'group' ? 'inline-block' : 'none';
  };

  window.toggleAssigneeSection = function () {
    const select = document.getElementById('task-type');
    const assigneeSection = document.getElementById('assignee-section');
    const allGroups = document.querySelectorAll('.assignee-group');

    if (!select || !assigneeSection) return;

    const value = select.value;

    allGroups.forEach(g => {
      g.style.display = 'none';
    });

    if (value.startsWith('group_')) {
      const groupId = value.replace('group_', '');
      const groupElement = document.querySelector(`.assignee-group[data-group-id="${groupId}"]`);
      if (groupElement) {
        groupElement.style.display = 'block';
        assigneeSection.style.display = 'block';
      }
    } else {
      assigneeSection.style.display = 'none';
    }
  };
});
