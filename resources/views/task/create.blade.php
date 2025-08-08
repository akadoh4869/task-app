<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/tentative/common.css')}}"/>
    <link rel="stylesheet" href="{{ asset('css/common.css')}}"/>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <title>新規作成</title>
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
            <section class="content active">
                <h2 class="title">新規タスク</h2>
                <form id="uploadForm" action="{{ route('task.store') }}" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitCompressedImages();">
                    @csrf

                    @php
                        $groups = Auth::user()->groups;
                    @endphp

                    <div class="flex2">
                        <label>タスクの種類:</label>
                        <!-- タスクの種別選択 -->
                        <select name="task_type_combined" id="task-type" onchange="toggleAssigneeSection()">
                            <option value="personal">個人タスク</option>
                            @foreach ($user->groups as $group)
                                <option value="group_{{ $group->id }}">{{ $group->group_name }}</option>
                            @endforeach
                        </select>

                        <!-- 担当者選択（グループタスク用） -->
                        <div id="assignee-section" style="display: none; margin-top: 15px;">
                            <p>担当者を選択:</p>
                            @foreach ($user->groups as $group)
                                <div class="assignee-group" data-group-id="{{ $group->id }}" style="display: none;">
                                    @foreach ($group->users as $member)
                                        <label style="margin-right: 10px;">
                                            <input type="checkbox" name="assigned_user_ids[]" value="{{ $member->id }}">
                                            {{ $member->user_name }}
                                        </label>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <br>

                    <input type="date" name="start_date" max="9999-12-31">
                    〜
                    <input type="date" name="due_date" max="9999-12-31">

                    <br><br>

                    <input type="text" id="task-name" name="task_name" placeholder="タスク名">

                    <br><br>

                    <div class="flex4">
                        <div class="textarea-container">
                            <div class="create-image">
                                <input type="file" id="image" name="images[]" accept="image/*" multiple onchange="displayImages()" style="display: none;">

                                <div id="hiddenBlock">
                                    <div class="icon-wrapper">
                                        <label for="image" class="icon-label">
                                            <i class="fa-regular fa-images"></i>
                                        </label>
                                    </div>
                                </div>

                                <!-- プレビュー表示枠 -->
                                <div id="image-preview" style="margin-top: 10px; display: flex; flex-wrap: wrap; gap: 10px;"></div>
                            </div>

                            <textarea name="description" id="content" class="content" rows="5" cols="33"></textarea>
                        </div>
                    </div>


                    <br>

                    {{-- 詳細編集 --}}
                    <p>詳細:</p>
                    <textarea name="description" rows="5" style="width: 100%;"></textarea>

                    <br><br>

                    <button type="submit" class="create-button">作成</button>
                </form>

            </section>
        </main>

            
            
    </div>

    <script src="./JS/app.js"></script>
    <script>
        let selectedImages = [];

        function displayImages() {
            const input = document.getElementById('image');
            const preview = document.getElementById('image-preview');
            const hiddenBlock = document.getElementById('hiddenBlock');

            // ファイル選択
            const files = Array.from(input.files);

            if (selectedImages.length + files.length > 5) {
                alert('最大5枚まで選択できます');
                input.value = ''; // クリア
                return;
            }

            hiddenBlock.style.display = 'none';

            files.forEach((file, index) => {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const wrapper = document.createElement('div');
                    wrapper.style.position = 'relative';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '60px';
                    img.style.height = '75px';
                    img.style.objectFit = 'cover';
                    img.style.border = '1px solid #ccc';

                    const closeBtn = document.createElement('span');
                    closeBtn.textContent = '×';
                    closeBtn.style.position = 'absolute';
                    closeBtn.style.top = '0';
                    closeBtn.style.right = '0';
                    closeBtn.style.cursor = 'pointer';
                    closeBtn.style.background = '#fff';
                    closeBtn.style.border = '1px solid #ccc';
                    closeBtn.style.borderRadius = '50%';
                    closeBtn.style.padding = '2px 6px';
                    closeBtn.style.fontSize = '14px';
                    closeBtn.style.lineHeight = '1';

                    closeBtn.onclick = function() {
                        preview.removeChild(wrapper);
                        selectedImages = selectedImages.filter(obj => obj.name !== file.name);
                        if (selectedImages.length === 0) {
                            hiddenBlock.style.display = 'block';
                        }
                    };

                    wrapper.appendChild(img);
                    wrapper.appendChild(closeBtn);
                    preview.appendChild(wrapper);
                };

                reader.readAsDataURL(file);
                selectedImages.push(file);
            });

            // 元のinputをクリア（次回のchangeイベントのため）
            input.value = '';
        }

        // フォーム送信時に画像を圧縮して FormData に追加する例
        function prepareCompressedImages(form) {
            const formData = new FormData(form);

            const compressAndAppend = (file, index) => {
                return new Promise((resolve) => {
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
                                formData.append('images[]', blob, file.name);
                                resolve();
                            }, 'image/jpeg', 0.7); // 画質70%
                        };
                    };
                    reader.readAsDataURL(file);
                });
            };

            return Promise.all(selectedImages.map(compressAndAppend)).then(() => formData);
        }

        function submitCompressedImages() {
            const form = document.getElementById('uploadForm');
            prepareCompressedImages(form).then((formData) => {
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (response.ok) {
                        window.location.href = "/task"; // 成功後にリダイレクト
                    } else {
                        alert('アップロードに失敗しました');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('通信エラーが発生しました');
                });
            });
        }

        function toggleGroupSelect() {
            const typeSelect = document.getElementById('task-type');
            const groupSelect = document.getElementById('group-select');
            groupSelect.style.display = typeSelect.value === 'group' ? 'inline-block' : 'none';
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
                if (groupElement) {
                    groupElement.style.display = 'block';
                    assigneeSection.style.display = 'block';
                }
            } else {
                assigneeSection.style.display = 'none';
            }
        }
    </script>

</body>

</html>