<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TOPページ</title>
    <style>
        .group-details, .personal-tasks {
            display: none; /* 初期状態は非表示 */
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <p>ユーザー名: {{ $user->user_name }}</p>

    <h2>所属グループ</h2>
    <ul>
        @foreach ($groups as $group)
            <li>
                <a href="javascript:void(0);" class="group-toggle" data-group-id="{{ $group->id }}">
                    {{ $group->group_name }}
                </a>
                <div class="group-details" id="details-{{ $group->id }}">
                    <h3>タスク一覧</h3>
                    @if($group->tasks->isNotEmpty())
                        <ul>
                            @foreach ($group->tasks as $task)
                                <li><strong>{{ $task->task_name }}</strong></li>
                            @endforeach
                        </ul>
                    @else
                        <p>このグループにはタスクがありません。</p>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
    
    <h2>
        <a href="javascript:void(0);" class="personal-task-toggle">個人タスク</a>
    </h2>
    <div class="personal-tasks" id="personal-tasks">
        @if($allPersonalTasks->isNotEmpty())
            <ul>
                @foreach ($allPersonalTasks as $task)
                    <li><strong>{{ $task->task_name }}</strong></li>
                @endforeach
            </ul>
        @else
            <p>あなたのタスクはありません。</p>
        @endif
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const personalTasks = document.getElementById('personal-tasks');
            const groupDetails = document.querySelectorAll('.group-details');
    
            // 初期状態：個人タスク表示
            personalTasks.style.display = 'block';
            groupDetails.forEach(d => d.style.display = 'none');
    
            // グループクリック時：他を非表示にし、該当グループ表示
            document.querySelectorAll('.group-toggle').forEach(toggle => {
                toggle.addEventListener('click', function() {
                    personalTasks.style.display = 'none';
                    groupDetails.forEach(d => d.style.display = 'none');
    
                    const groupId = this.dataset.groupId;
                    const details = document.getElementById(`details-${groupId}`);
                    details.style.display = 'block';
                });
            });
    
            // 個人タスクを表示する
            document.querySelector('.personal-task-toggle').addEventListener('click', () => {
                groupDetails.forEach(d => d.style.display = 'none');
                personalTasks.style.display = 'block';
            });
        });
    </script>

</body>
</html>
