<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./task.css" />
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v6.7.2/css/all.css">
    <title>Document</title>
</head>
<body>
    <div class="flex">
        <header>
            <!--  アプリ名 -->
            <h1 class="appname">タスクアプリ名</h1>

            <!--メニューバー-->
            <ul>
            <li><a href="task.blade.html">タスク管理</a></li>
            <li><a href="#">作成</a></li>
            <li><a href="#">共有事項</a></li>
            <li><a href="#">設定</a></li>
            <li>
                <a href="#"
                ><img src="./img/no_1.jpg" alt="アカウント" class="account"
                /></a>
            </li>
            </ul>
        </header>
        <main>
            <h2 class="title">新規タスク</h2>
            <form action="">
                <!-- <label for="pet-select">タスク:</label> -->
                <!-- <br> -->
                 <div class="flex2">
                    <select name="task" id="task-type">
                        <option value="solo">個人タスク</option>
                        <option value="group">グループ</option>
                    </select>
                    <input type="text" id="ifgroup" style="display: none;">
                </div>
                <br>
                <input type="date" name="calendar" max="9999-12-31">&nbsp;
                〜
                &nbsp;<input type="date" name="calendar" max="9999-12-31">
                <br>
                <br>
                <input type="text" id="task-name" placeholder="タスク名">
                <br>
                <br>
                <div class="flex4">
                    <!-- {{-- ここに反映する画像挿入 --}} -->
                    <div class="create-image">
                        <!-- <img src="#" alt="" id="selectedImage"> -->

                        <div id="hiddenBlock" class="hidden">
                            <!-- ここに非表示にしたいコンテンツを追加 -->
                            <!-- {{-- 画像が選択されたとき非表示 --}} -->
                            <i class="fa-regular fa-images"></i>
                            <label for="image">
                                <!-- <div class="image">写真を選択</div> -->
                            </label>
                            <!-- {{-- エラー文表示 --}}
                            @error('image')
                                <p class="text-red-500" style="color: red;">{{ $message }}</p>
                            @enderror -->
                        </div>
                        
                        <!-- <label for="image">
                            <div class="image">写真を選択</div>
                        </label> -->
                    </div>
                    <textarea name="task-content" id="content" rows="5" cols="33"></textarea>
                </div>
            </form>
        </main>
    </div>

    <script src="./JS/app.js"></script>
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
    </script>

     <!-- {{-- 投稿画像の表示 --}}
                    <div>
                        <input type="file" id="image" name="image" style="display: none;" onchange="displayImage()">
                    </div> -->

</body>
</html>