document.addEventListener('DOMContentLoaded', function () {

  // ----------------------------
  // タブ切り替え処理
  // ----------------------------
  const tabs = document.querySelectorAll('#list li');
  const contents = document.querySelectorAll('.tab-container .content');

  // 📅 カレンダーを今日の位置にスクロールする関数
  function scrollCalendarToToday(force = false) {
    if (!window.taskCalendar) return;

    const wrapper = document.querySelector('#content-calendar .gantt-wrapper');
    if (!wrapper) return;

    // 非表示中でも「位置だけ」先にセットする
    const calendarYear = parseInt(window.taskCalendar.startDate.slice(0, 4), 10);
    const today = new Date();
    const todayYear = today.getFullYear();
    if (calendarYear !== todayYear) return;

    const todayStr = today.toISOString().slice(0, 10);
    const dayWidth =
      parseFloat(getComputedStyle(wrapper).getPropertyValue('--day-width')) || 32;

    const start = new Date(window.taskCalendar.startDate);
    const todayDate = new Date(todayStr);
    const oneDay = 24 * 60 * 60 * 1000;
    const diffDays = Math.floor((todayDate - start) / oneDay);

    // wrapper の幅が取得できない（display:none）場合 → 仮の幅を設定
    let wrapperWidth = wrapper.clientWidth || 1000; // 非表示でも仮値でOK
    let target = (diffDays - 0) * dayWidth - 180; 
    if (target < 0) target = 0;

    // スクロール可能範囲チェック
    const maxScroll = wrapper.scrollWidth - wrapperWidth;
    if (target > maxScroll) target = maxScroll;

    // 位置をセット（アニメーション不要で即座に）
    wrapper.scrollLeft = target;
    console.log('✅ 今日の位置セット:', todayStr, target, '日数差:', diffDays);

    // 強制再スクロール指定時は、表示後に再度セット
    if (force) {
      setTimeout(() => {
        wrapper.scrollLeft = target;
      }, 300);
    }
  }

  // ----------------------------
  // タブ切り替え
  // ----------------------------
  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');

      const targetId = this.dataset.tab === 'list' ? 'content-list' : 'content-calendar';
      contents.forEach(c => c.classList.remove('active'));
      const targetContent = document.getElementById(targetId);
      targetContent.classList.add('active');

      // カレンダーを選んだときに「再調整」
      if (this.dataset.tab === 'calendar') {
        scrollCalendarToToday(true);
      }
    });
  });

  // ----------------------------
  // 年の制御ロジック
  // ----------------------------
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

  // ----------------------------
  // ▼ ガントチャートのバー表示処理 ▼
  // ----------------------------
  if (window.taskCalendar) {
    function parseYmdToUtc(ymd) {
      const [y, m, d] = ymd.split('-').map(Number);
      return new Date(Date.UTC(y, m - 1, d));
    }

    const startDate = parseYmdToUtc(window.taskCalendar.startDate);
    const endDate = parseYmdToUtc(window.taskCalendar.endDate);
    const oneDayMs = 24 * 60 * 60 * 1000;
    const totalDays = (endDate - startDate) / oneDayMs + 1;

    const now = new Date();
    const todayUtc = Date.UTC(now.getFullYear(), now.getMonth(), now.getDate());

    document.querySelectorAll('.gantt-bar').forEach(bar => {
      const startStr = bar.dataset.start;
      const endStr = bar.dataset.end;
      if (!startStr || !endStr) return;

      const rawStart = parseYmdToUtc(startStr);
      const rawEnd = parseYmdToUtc(endStr);

      if (rawEnd < startDate || rawStart > endDate) {
        bar.style.display = 'none';
        return;
      }

      let s = rawStart < startDate ? startDate : rawStart;
      let e = rawEnd > endDate ? endDate : rawEnd;

      const offsetDays = (s - startDate) / oneDayMs;
      const durationDays = (e - s) / oneDayMs + 1;

      const leftPercent = (offsetDays / totalDays) * 100;
      const widthPercent = (durationDays / totalDays) * 100;

      bar.style.left = leftPercent + '%';
      bar.style.width = widthPercent + '%';

      const isOverdueAttr = bar.dataset.overdue === '1';
      const isOverdueDate = rawEnd.getTime() < todayUtc;
      if (isOverdueAttr || isOverdueDate) {
        bar.classList.add('overdue');
        const row = bar.closest('.gantt-row');
        if (row) row.classList.add('overdue');
      }
    });

    // ✅ ページ読み込み直後にも実行（タブがリストでもOK）
    scrollCalendarToToday();
  }

});
