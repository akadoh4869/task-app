document.addEventListener('DOMContentLoaded', function () {
  // タブ切り替え処理（必要に応じて）
  const tabs = document.querySelectorAll('#list li');
  const contents = document.querySelectorAll('.tab-container .content');

  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      const targetId = this.dataset.tab === 'list' ? 'content-list' : 'content-calendar';
      contents.forEach(c => c.classList.remove('active'));
      document.getElementById(targetId).classList.add('active');
    });
  });

  // 年の制御ロジック
  const yearDisplay = document.getElementById('yearDisplay');
  let currentYear = parseInt(yearDisplay.dataset.year);

  const prevBtn = document.getElementById('prevYear');
  if (prevBtn) {
    prevBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const prevYear = currentYear - 1;
      if (prevYear >= 2025) {
        window.location.href = '?year=' + prevYear;
      }
    });
  }

  const nextBtn = document.getElementById('nextYear');
  if (nextBtn) {
    nextBtn.addEventListener('click', function (e) {
      e.preventDefault();
      const nextYear = currentYear + 1;
      window.location.href = '?year=' + nextYear;
    });
  }
});
