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

   // 📆 ガントチャート初期表示を「今日」に合わせる
window.addEventListener('load', () => {
  if (!window.taskCalendar) return;

  const wrapper = document.querySelector('.gantt-wrapper');
  if (!wrapper) return;

  const parseYmd = (ymd) => {
    const [y, m, d] = ymd.split('-').map(Number);
    return new Date(y, m - 1, d);
  };

  const startDate = parseYmd(window.taskCalendar.startDate);
  const endDate   = parseYmd(window.taskCalendar.endDate);

  const today = new Date();
  // 今年の範囲外なら何もしない
  if (today < startDate || today > endDate) return;

  const oneDay = 24 * 60 * 60 * 1000;
  const diffDays = Math.floor(
    (Date.UTC(today.getFullYear(), today.getMonth(), today.getDate()) -
     Date.UTC(startDate.getFullYear(), startDate.getMonth(), startDate.getDate())
    ) / oneDay
  );

  // 1日分の幅を .gantt-day-row から取得（grid-auto-columns: 32px 前提）
  let dayWidth = 32;
  const dayRow = wrapper.querySelector('.gantt-day-row');
  if (dayRow) {
    const styles = getComputedStyle(dayRow);
    const v = styles.gridAutoColumns || styles.getPropertyValue('grid-auto-columns');
    const n = parseFloat(v);
    if (!isNaN(n) && n > 0) dayWidth = n;
  }

  // 今日が少し左よりに見えるように2日分引いておく
  const target = Math.max(0, dayWidth * (diffDays - 2));

  // レイアウト確定後に1回だけ適用（アニメーションなし）
  requestAnimationFrame(() => {
    wrapper.scrollLeft = target;
    console.log(`今日(${today.toLocaleDateString()}) の位置まで ${target}px セット`, {
      scrollWidth: wrapper.scrollWidth,
      clientWidth: wrapper.clientWidth,
    });
  });
});


}

});
