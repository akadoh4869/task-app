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

                    <div class="flex2">
                        <select name="task_type_combined" id="task-type">
                            <option value="solo">個人タスク</option>
                            {{-- グループが複数ある場合は以下のようにループでも可 --}}
                            <option value="group_1">グループ1</option>
                            <option value="group_2">グループ2</option>
                        </select>
                        <input type="text" id="ifgroup" style="display: none;">
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
    </script>

    <!-- {{-- 投稿画像の表示 --}}
                    <div>
                        <input type="file" id="image" name="image" style="display: none;" onchange="displayImage()">
                    </div> -->

</body>

</html>