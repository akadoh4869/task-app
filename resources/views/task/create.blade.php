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
  <script src="{{ asset('js/tentative/create.js') }}"></script>
  <title>新規作成</title>
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
        <li><a href="/setting"><i class="fa-solid fa-gear"></i><span>設定ああ</span></a></li>
        <li><img src="{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/images/default.png') }}" alt="アカウント">{{-- <span>プロフィール</span> --}}</li>
      </ul>
    </header>

    <main>
      <div class="main">
        <section class="content active">
          <h2 class="title">新規タスク</h2>

          <form id="uploadForm" action="{{ route('task.store') }}" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitCompressedUploads();">
            @csrf

            @php $groups = Auth::user()->groups; @endphp

            <div class="flex2">
              <label>タスクグループ:</label>
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

      </div>
      
    </main>
  </div>
</body>
</html>
