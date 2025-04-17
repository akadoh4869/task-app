<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="./task.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Document</title>
  </head>
  <body>
    <div class="flex">

      <header>
        <!--  アプリ名 -->
        <h1 class="appname">タスクアプリ名</h1>

        <!--メニューバー-->
        <ul>
          <li><a href="#">タスク管理</a></li>
          <li><a href="create.blade.html">作成</a></li>
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
        <!--コンテンツ-->
        <section class="t-head">
          <div class="year">
            <a href="">
              <img src="./img/no_3.png" alt="pre-year" width="50px" />
              <!--前の年へ-->
            </a>
            <p>2025</p>
            <!--表示する年-->
            <a href="">
              <img src="./img/no_2.png" alt="next-year" width="50px" />
              <!--次の年へ-->
            </a>
          </div>
          <ul id="list">
            <li class="tab1">リスト</li>
            <li class="tab2">カレンダー</li>
          </ul>
        </section>

        <div class="tab-container">
          <section id="content-list" class="content active">
            <table>
              <tr class="flex2">
                <th>
                  期間〜期間
                </th>
                <td class="flex2">
                  <input type="checkbox" id="task" name="todo" value="task"/>
                  <p>期限切れのタスク</p>
                </td>
              </tr>
              <tr class="flex2">
                <th>
                  期間〜期間
                </th>
                <td class="flex2">
                  <input type="checkbox" id="task" name="todo" value="task"/>
                  <p>期限切れのタスク</p>
                </td>
              </tr>
              <tr class="flex2">
                <th>
                  期間〜期間
                </th>
                <td class="flex2">
                  <input type="checkbox" id="task" name="todo" value="task"/>
                  <p>作業進行中のタスク</p>
                </td>
              </tr>
              <tr class="flex2">
                <th>
                  期間〜期間
                </th>
                <td class="flex2">
                  <input type="checkbox" id="task" name="todo" value="task"/>
                  <p>作業進行中のタスク</p>
                </td>
              </tr>
              <tr class="flex2">
                <th>
                  期間〜期間
                </th>
                <td class="flex2">
                  <input type="checkbox" id="task" name="todo" value="task"/>
                  <p>作業進行中のタスク</p>
                </td>
              </tr>
              <tr class="flex2">
                <th>
                  期間〜期間
                </th>
                <td class="flex2">
                  <input type="checkbox" id="task" name="todo" value="task"/>
                  <p>作業進行中のタスク</p>
                </td>
              </tr>
            </table>
          </section>

          <section id="content-calendar" class="content">
            <p>カレンダー表示エリア</p>

          </section>
        </div>

      </main>
    </div>
     <script src="./JS/app.js"></script>
  </body>

 
</html>
