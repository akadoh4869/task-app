document.addEventListener('DOMContentLoaded', function () {

  // ----------------------------
  // タブ切り替え処理
  // ----------------------------
  const tabs = document.querySelectorAll('#list li');
  const contents = document.querySelectorAll('.tab-container .content');

  // 📅 カレンダーを今日の位置にスクロールする関数
  // 📅 カレンダーを今日の位置にスクロールする関数（位置を直接拾う版）
  function scrollCalendarToToday(force = false) {
    if (!window.taskCalendar) return;

    const wrapper = document.querySelector('.gantt-wrapper'); // 必要ならセレクタ調整
    if (!wrapper) return;

    const yearDisplay = document.getElementById('yearDisplay');
    const calendarYear = parseInt(yearDisplay.dataset.year, 10);

    const today = new Date();
    const todayYear = today.getFullYear();
    if (calendarYear !== todayYear) {
      // 表示している年と今年が違うときはスクロールしない
      return;
    }

    const todayStr = today.toISOString().slice(0, 10);

    // ヘッダー側の日付セル（.gantt-day-row）の中から今日のセルを探す
    const todayCell = wrapper.querySelector(
      '.gantt-day-row .gantt-day[data-date="' + todayStr + '"]'
    );
    if (!todayCell) {
      console.log('今日の日付セルが見つかりません:', todayStr);
      return;
    }

    // wrapper から見た todayCell の相対位置を計算
    const wrapperRect = wrapper.getBoundingClientRect();
    const cellRect = todayCell.getBoundingClientRect();

    let target = wrapper.scrollLeft + (cellRect.left - wrapperRect.left) - 180; // 180px だけ左に余白
    if (target < 0) target = 0;

    const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
    if (target > maxScroll) target = maxScroll;

    wrapper.scrollLeft = target;
    console.log('✅ 今日の位置にスクロール:', todayStr, '→', target);

    // タブ切替直後などで再調整したい場合
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
