<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <title>新規作成ページ</title>
</head>
<body>
    <div class="flex">
       
        <header>
            <!--  アプリ名 -->
            <h1 class="appname">タスクアプリ名</h1>

            <!--メニューバー-->
            <ul>
            <li><a href="/task">タスク管理</a></li>
            <li><a href="/create">作成</a></li>
            <li><a href="/share">共有事項</a></li>
            <li><a href="/setting">設定</a></li>
            <li>
                <a href="#"
                ><img src="./img/no_1.jpg" alt="アカウント" class="account"
                /></a>
            </li>
            </ul>
        </header>
        <main>
            <main>
                <h2 class="title">新規タスク</h2>
            
                <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="flex2">
                        {{-- <label for="task-type">タスクタイプ</label> --}}
                        <select name="task_type_combined" id="task-type">
                            <option value="solo">個人タスク</option>
                            @foreach ($groups as $group)
                                <option value="group_{{ $group->id }}">{{ $group->group_name }}タスク</option>
                            @endforeach
                        </select>
                    </div>
                    
            
                    <br>
            
                    <input type="date" name="start_date" max="9999-12-31">&nbsp;〜&nbsp;
                    <input type="date" name="due_date" max="9999-12-31">
            
                    <br><br>
            
                    <input type="text" name="task_name" id="task-name" placeholder="タスク名">
            
                    <br><br>
            
                    <div class="flex4">
                        <div class="create-image">
                            <input type="file" name="image" id="image" accept="image/*" />
                        </div>
                        <textarea name="description" id="content" rows="5" cols="33" placeholder="タスク内容"></textarea>
                    </div>
            
                    <br>
                    <button type="submit">登録</button>
                </form>
            </main>
            
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        function displayImage(){
            var input = document.getElementById('image');
            var image = document.getElementById('selectedImage');
            var hiddenBlock = document.getElementById('hiddenBlock');

            // ファイルが選択されたか確認
            if(input.files && input.files[0]){
                var reader = new FileReader();
            

            // 画像が読み込まれた時の処理
            reader.onload = function(e){
                image.src = e.target.result;
                image.style.display = 'block';
            };

            // 画像を読み込む
            reader.readAsDataURL(input.files[0]);
            } 

            hiddenBlock.style.display = "none";
        }

        document.addEventListener('DOMContentLoaded', function () {
            const typeSelect = document.getElementById('task-type');
            const groupSelect = document.getElementById('ifgroup');

            typeSelect.addEventListener('change', function () {
                if (this.value === 'group') {
                    groupSelect.style.display = 'inline-block';
                } else {
                    groupSelect.style.display = 'none';
                    groupSelect.value = ''; // 非表示時は値をリセット
                }
            });
        });
    </script>

     <!-- {{-- 投稿画像の表示 --}}
                    <div>
                        <input type="file" id="image" name="image" style="display: none;" onchange="displayImage()">
                    </div> -->

</body>
</html>