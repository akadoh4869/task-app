document.addEventListener('DOMContentLoaded', function () {
  // ----------------------------
  // タブ切り替え処理
  // ----------------------------
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

  const startDate = parseYmdToUtc(window.taskCalendar.startDate); // 1/1
  const endDate   = parseYmdToUtc(window.taskCalendar.endDate);   // 12/31
  const oneDayMs  = 24 * 60 * 60 * 1000;
  const totalDays = (endDate - startDate) / oneDayMs + 1;         // 365 or 366

  const now = new Date();
  const todayUtc = Date.UTC(now.getFullYear(), now.getMonth(), now.getDate());

  document.querySelectorAll('.gantt-bar').forEach(bar => {
    const startStr = bar.dataset.start;
    const endStr   = bar.dataset.end;
    if (!startStr || !endStr) return;

    const rawStart = parseYmdToUtc(startStr);
    const rawEnd   = parseYmdToUtc(endStr);

    // 年レンジ外は非表示
    if (rawEnd < startDate || rawStart > endDate) {
      bar.style.display = 'none';
      return;
    }

    let s = rawStart < startDate ? startDate : rawStart;
    let e = rawEnd   > endDate   ? endDate   : rawEnd;

    const offsetDays   = (s - startDate) / oneDayMs;
    const durationDays = (e - s) / oneDayMs + 1;

    const leftPercent  = (offsetDays / totalDays) * 100;
    const widthPercent = (durationDays / totalDays) * 100;

    bar.style.left  = leftPercent + '%';
    bar.style.width = widthPercent + '%';

    // 期限切れ → 赤＋件名も赤
    const isOverdueAttr = bar.dataset.overdue === '1';
    const isOverdueDate = rawEnd.getTime() < todayUtc;
    if (isOverdueAttr || isOverdueDate) {
      bar.classList.add('overdue');
      const row = bar.closest('.gantt-row');
      if (row) row.classList.add('overdue');
    }
  });

  // ----------------------------
// ▼ 今日の列へスクロール（修正版）
// ----------------------------


// ----------------------------
// 🎯 今日の日付列に自動スクロール（全行対応）
// ----------------------------
const wrapper = document.querySelector('.gantt-wrapper');
if (wrapper && window.taskCalendar) {
  const calendarYear = parseInt(window.taskCalendar.startDate.slice(0, 4), 10);
  const today = new Date();
  const todayYear = today.getFullYear();

  if (calendarYear === todayYear) {
    const todayStr = today.toISOString().slice(0, 10);

    // gantt-body 内のすべての .gantt-day を取得して、
    // 最初に見つかった今日の日付セルを使う
    const allDays = wrapper.querySelectorAll(`.gantt-body .gantt-day[data-date="${todayStr}"]`);
    const todayCell = allDays.length > 0 ? allDays[0] : null;

    if (todayCell) {
      todayCell.classList.add('today');

      // レイアウトが確定したあとにスクロール
      requestAnimationFrame(() => {
        const wrapperRect = wrapper.getBoundingClientRect();
        const cellRect = todayCell.getBoundingClientRect();
        const currentScroll = wrapper.scrollLeft;

        // 今日のセルの中央位置
        const cellCenter =
          (cellRect.left - wrapperRect.left) + currentScroll + (todayCell.offsetWidth / 2);

        // 中央付近に来るように調整
        let target = cellCenter - (wrapper.clientWidth / 2);

        // はみ出し防止
        if (target < 0) target = 0;
        const maxScroll = wrapper.scrollWidth - wrapper.clientWidth;
        if (target > maxScroll) target = maxScroll;

        wrapper.scrollLeft = target;

        console.log('✅ 今日の位置へスクロール:', todayStr, target);
      });
    } else {
      console.warn('⚠️ gantt-body 内に今日のセルが見つかりません:', todayStr);
    }
  }
}



}

});
