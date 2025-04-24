document.addEventListener('DOMContentLoaded', function () {
 // task.blade>コンテンツ切り替え
  const tabList = document.querySelector(".tab1");
  const tabCalendar = document.querySelector(".tab2");
  const contentList = document.getElementById("content-list");
  const contentCalendar = document.getElementById("content-calendar");

  if (tabList && tabCalendar && contentList && contentCalendar) {
    const switchTab = (show, hide) => {
      hide.classList.remove("active");
      setTimeout(() => {
        show.classList.add("active");
      }, 50);
    };

    tabList.addEventListener("click", () => {
      switchTab(contentList, contentCalendar);
      tabList.classList.add("active");
      tabCalendar.classList.remove("active");
    });

    tabCalendar.addEventListener("click", () => {
      switchTab(contentCalendar, contentList);
      tabList.classList.remove("active");
      tabCalendar.classList.add("active");
    });
  }

  // group選択でgroup名前入力表示
  const taskType = document.getElementById('task-type');
  const ifGroupInput = document.getElementById('ifgroup');

  if (taskType && ifGroupInput) {
    taskType.addEventListener('change', function () {
      ifGroupInput.style.display = this.value === 'group' ? 'block' : 'none';
    });
  }
});
