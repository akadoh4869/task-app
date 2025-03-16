<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>テストページ</title>
    <style>
        .group-details, .personal-tasks {
            display: none; /* 初期状態は非表示 */
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <h1>ユーザー情報</h1>
    <p>ユーザー名: {{ $user->user_name }}</p>

    <h2>所属グループ</h2>
    @if($groups->isNotEmpty())
        <ul>
            @foreach ($groups as $group)
                <li>
                    <a href="javascript:void(0);" class="group-toggle" data-group-id="{{ $group->id }}">
                        {{ $group->group_name }}
                    </a>
                    <div class="group-details" id="details-{{ $group->id }}">
                        <h3>メンバー一覧</h3>
                        <ul>
                            @foreach ($group->users as $member)
                                <li>{{ $member->user_name }}</li>
                            @endforeach
                        </ul>

                        <h3>タスク一覧</h3>
                        @if($group->tasks->isNotEmpty())
                            <ul>
                                @foreach ($group->tasks as $task)
                                    <li>
                                        <strong>{{ $task->task_name }}</strong> - {{ $task->getStatusLabel() }}<br>
                                        作成者: {{ $task->creator->user_name ?? '不明' }} <br>
                                        担当者: 
                                        @if($task->assignedUsers->isNotEmpty())
                                            {{ $task->assignedUsers->pluck('user_name')->join(', ') }}
                                        @else
                                            担当なし
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p>このグループにはタスクがありません。</p>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @else
        <p>未所属</p>
    @endif

    <h2>
        <a href="javascript:void(0);" class="personal-task-toggle">
            個人タスク
        </a>
    </h2>
    <div class="personal-tasks" id="personal-tasks">
        @if($allPersonalTasks->isNotEmpty())
            <ul>
                @foreach ($allPersonalTasks as $task)
                    <li>
                        <strong>{{ $task->task_name }}</strong> - {{ $task->getStatusLabel() }}<br>
                       
                    </li>
                @endforeach
            </ul>
        @else
            <p>あなたのタスクはありません。</p>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 所属グループのタスク表示トグル
            const groupToggles = document.querySelectorAll('.group-toggle');
            groupToggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const groupId = this.dataset.groupId;
                    const details = document.getElementById(`details-${groupId}`);
                    details.style.display = (details.style.display === 'none' || details.style.display === '') ? 'block' : 'none';
                });
            });

            // 個人タスクのトグル
            const personalTaskToggle = document.querySelector('.personal-task-toggle');
            const personalTasks = document.getElementById('personal-tasks');
            personalTaskToggle.addEventListener('click', function() {
                personalTasks.style.display = (personalTasks.style.display === 'none' || personalTasks.style.display === '') ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
