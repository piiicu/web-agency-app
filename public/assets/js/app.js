// CHAT
function send(){
  let msg=document.getElementById('msg');
  fetch('/chat',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'message='+encodeURIComponent(msg.value)
  }).then(()=>location.reload());
}

// TASKS
function add(){
  let task=document.getElementById('task');
  fetch('/tasks',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'title='+encodeURIComponent(task.value)
  }).then(()=>location.reload());
}

// let dragged = null;

// document.querySelectorAll('.card[draggable="true"]').forEach(card => {
//   card.addEventListener('dragstart', e => {
//     dragged = card;
//     card.classList.add('dragging');
//     e.dataTransfer.effectAllowed = 'move';
//   });
//   card.addEventListener('dragend', () => {
//     if (dragged) dragged.classList.remove('dragging');
//     dragged = null;
//   });
// });

// document.querySelectorAll('.dropzone').forEach(zone => {
//   zone.addEventListener('dragover', e => {
//     e.preventDefault();
//     const after = getDragAfterElement(zone, e.clientY);
//     if (!dragged) return;
//     if (after == null) zone.appendChild(dragged);
//     else zone.insertBefore(dragged, after);
//   });

//   zone.addEventListener('drop', async e => {
//     e.preventDefault();
//     if (!dragged) return;

//     const status = zone.dataset.status; // pending/done
//     const id = dragged.dataset.id;

//     // 1) update status if moved between columns
//     await fetch(`${BASE_URL}tasks-move`, {
//       method: 'POST',
//       headers: {'Content-Type': 'application/json'},
//       body: JSON.stringify({id, status})
//     });

//     // 2) save ordering for this column
//     const orderedIds = [...zone.querySelectorAll('.card')].map(c => c.dataset.id);

//     await fetch(`${BASE_URL}tasks-reorder`, {
//       method: 'POST',
//       headers: {'Content-Type': 'application/json'},
//       body: JSON.stringify({status, orderedIds})
//     });
//   });
// });

// function getDragAfterElement(container, y) {
//   const els = [...container.querySelectorAll('.card:not(.dragging)')];
//   return els.reduce((closest, child) => {
//     const box = child.getBoundingClientRect();
//     const offset = y - box.top - box.height / 2;
//     if (offset < 0 && offset > closest.offset) {
//       return { offset, element: child };
//     }
//     return closest;
//   }, { offset: Number.NEGATIVE_INFINITY }).element;
// }

// // Quick actions
// document.addEventListener('click', async (e) => {
//   const btn = e.target.closest('button[data-action]');
//   if (!btn) return;

//   const card = btn.closest('.card');
//   const id = card.dataset.id;
//   const action = btn.dataset.action;

//   if (action === 'favorite') {
//     await postForm(`${BASE_URL}tasks-favorite`, {id});
//     location.reload();
//   }

//   if (action === 'done') {
//     await postForm(`${BASE_URL}tasks-done`, {id});
//     location.reload();
//   }

//   if (action === 'undo') {
//     await postForm(`${BASE_URL}tasks-done`, {id});
//     location.reload();
//   }

//   if (action === 'delete') {
//     if (!confirm('Ștergi task-ul?')) return;
//     await postForm(`${BASE_URL}tasks-delete`, {id});
//     location.reload();
//   }
// });

// async function postForm(url, data) {
//   const body = new URLSearchParams(data).toString();
//   return fetch(url, {
//     method:'POST',
//     headers:{'Content-Type':'application/x-www-form-urlencoded'},
//     body
//   });
// }
