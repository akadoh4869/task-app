document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('#list li');
    const contents = document.querySelectorAll('.tab-container .content');

    tabs.forEach(tab => {
      tab.addEventListener('click', function () {
        // タブの切り替え
        tabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');

        // 対応するセクションを表示
        const targetId = this.dataset.tab === 'list' ? 'content-list' : 'content-calendar';
        contents.forEach(c => c.classList.remove('active'));
        document.getElementById(targetId).classList.add('active');
      });
    });
});

let currentYear = 2025; // 初期値。Laravelの変数で渡してもOK
const yearDisplay = document.getElementById('yearDisplay');

document.getElementById('prevYear').addEventListener('click', function(e) {
e.preventDefault();
currentYear--;
yearDisplay.textContent = currentYear + '年';
});

document.getElementById('nextYear').addEventListener('click', function(e) {
e.preventDefault();
currentYear++;
yearDisplay.textContent = currentYear + '年';
});
