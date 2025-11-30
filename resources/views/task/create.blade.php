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
        <li><a href="/setting"><i class="fa-solid fa-gear"></i><span>設　定</span></a></li>
        <li><img src="{{ asset(Auth::user()->avatar ? 'storage/' . Auth::user()->avatar : 'storage/images/default.png') }}" alt="アカウント">{{-- <span>プロフィール</span> --}}</li>
      </ul>
    </header>

    <main>
      <div class="main">
        <section class="content active">

          <form id="uploadForm" action="{{ route('task.store') }}" method="POST" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitCompressedUploads();">
            @csrf

            @php
             $groups = Auth::user()->groups; 
            @endphp

            <div class="task-create">
              
              <input type="text" id="task-name" name="task_name" class="task-name" placeholder="タスク名">
              <br><br>
          

              <div class="task-date">
                <input type="date" name="start_date" class="date" max="9999-12-31"> 〜
                <input type="date" name="due_date" class="date" max="9999-12-31">
              </div>

              <br>

              <div class="task-belong">
                <div class="task-group">
                  <select name="task_type_combined" id="task-type" class="task-type" onchange="toggleAssigneeSection()">
                    <option value="personal">個人タスク</option>
                    @foreach ($user->groups as $group)
                      <option value="group_{{ $group->id }}">{{ $group->group_name }}</option>
                    @endforeach
                  </select>
                </div>

                <div class="task-assignee">
                  <div id="assignee-section" style="display:none; margin-top:15px;">
                    @foreach ($user->groups as $group)
                      <div class="assignee-group" data-group-id="{{ $group->id }}" style="display:none;">
                        @foreach ($group->users as $member)
                          @php
                            $avatarPath = $member->avatar && file_exists(public_path('storage/' . $member->avatar))? asset('storage/' . $member->avatar): asset('storage/images/default.png');
                          @endphp
                          <label class="assignee-item">
                            <input type="checkbox" name="assigned_user_ids[]" value="{{ $member->id }}">
                            <img src="{{ $avatarPath }}" alt="{{ $member->user_name }}" class="assignee-avatar">
                            <span class="assignee-name">{{ $member->user_name }}</span>
                          </label>
                        @endforeach
                      </div>
                    @endforeach
                  </div>
                </div>

              </div>

              <div class="task-content">
                <!-- アップローダ -->
                <div class="create-image" id="uploader">
                  <!-- 画像に限らず選べるように accept を緩める（必要なら特定拡張子に） -->
                  <input type="file" id="file-input" multiple style="display:none" accept="*/*">
                  <div id="image-grid" class="image-grid"></div>
                </div>

                {{-- <!-- 上段メモ -->
                <textarea name="memo" id="content" class="memo" rows="5" style="width:60%" placeholder=" -メモ- "></textarea> --}}
              
                <textarea name="description" class="description" rows="5" style="width:60%;" placeholder=" -内容- "></textarea>


              </div>

              <div class="create">
                <button type="submit" class="create-button">作成</button>

              </div>
              

            </div>

            
           

            
          </form>
        </section>

      </div>
      
    </main>
  </div>
</body>
</html>
