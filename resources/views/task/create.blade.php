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
                <form action="{{ route('task.store') }}" method="POST" enctype="multipart/form-data">
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
                                <input type="file" id="image" name="image" style="display: none;">
                                <div id="hiddenBlock">
                                    <div class="icon-wrapper">
                                        <label for="image" class="icon-label">
                                            <i class="fa-regular fa-images"></i>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <textarea name="description" id="content" class="content" rows="5" cols="33"></textarea>
                        </div>
                    </div>

                    <br>

                    <button type="submit" class="create-button">作成</button>
                </form>

            </section>
        </main>

            
            
    </div>

    <script src="./JS/app.js"></script>
    <script>
        function displayImage() {
            var input = document.getElementById('image');
            var image = document.getElementById('selectedImage');
            var hiddenBlock = document.getElementById('hiddenBlock');

            // ファイルが選択されたか確認
            if (input.files && input.files[0]) {
                var reader = new FileReader();


                // 画像が読み込まれた時の処理
                reader.onload = function(e) {
                    image.src = e.target.result;
                    image.style.display = 'block';
                };

                // 画像を読み込む
                reader.readAsDataURL(input.files[0]);
            }

            hiddenBlock.style.display = "none";
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

    <!-- {{-- 投稿画像の表示 --}}
                    <div>
                        <input type="file" id="image" name="image" style="display: none;" onchange="displayImage()">
                    </div> -->

</body>

</html>