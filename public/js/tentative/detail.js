
// function removeUser(button) {
//     const userDiv = button.closest('div');
//     userDiv.remove();
// }

// function addSelectedUser() {
//     const select = document.getElementById('add-user-select');
//     const selectedOption = select.options[select.selectedIndex];

//     if (!selectedOption.value) return;

//     const userId = selectedOption.value;
//     const userName = selectedOption.dataset.name;
//     const userAvatar = selectedOption.dataset.avatar;

//     // 担当メンバーエリアに追加
//     const container = select.previousElementSibling;
//     const wrapper = document.createElement('div');
//     wrapper.style.cssText = "position:relative; display:flex; align-items:center; gap:5px; background:#f0f0f0; padding:5px 10px; border-radius:8px; margin-top:5px;";
//     wrapper.innerHTML = `
//         <img src="${userAvatar}" alt="${userName}" width="30" height="30" style="border-radius: 50%;">
//         <span>${userName}</span>
//         <input type="hidden" name="assigned_user_ids[]" value="${userId}">
//         <button type="button" class="remove-user" onclick="removeUser(this)">×</button>
//     `;
//     container.appendChild(wrapper);

//     // 選択済みの option を削除
//     select.remove(select.selectedIndex);
//     select.selectedIndex = 0;
// }


function removeUser(button) {
    button.closest('.assigned-user').remove();
}

function addSelectedUser() {
    const select = document.getElementById('add-user-select');
    const userId = select.value;
    if (!userId) return;

    const name = select.options[select.selectedIndex].dataset.name;
    const avatar = select.options[select.selectedIndex].dataset.avatar;

    const container = document.getElementById('assigned-user-list');
    const newDiv = document.createElement('div');
    newDiv.className = 'assigned-user';
    newDiv.style = 'position: relative; display: flex; align-items: center; gap: 5px; background: #f0f0f0; padding: 5px 10px; border-radius: 8px;';
    newDiv.innerHTML = `
        <img src="${avatar}" alt="${name}" width="30" height="30" style="border-radius: 50%;">
        <span>${name}</span>
        <input type="hidden" name="assigned_user_ids[]" value="${userId}">
        <button type="button" class="remove-user" onclick="removeUser(this)">×</button>
    `;
    container.appendChild(newDiv);
    select.selectedIndex = 0;
    select.querySelector(`option[value="${userId}"]`).remove();
}

