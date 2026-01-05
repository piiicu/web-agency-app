<h2>Chat</h2>

<div style="margin-bottom:15px;">
  <a href="<?= BASE_URL ?>dashboard">⬅ Dashboard</a>
</div>

<div id="messages" style="border:1px solid #ddd; padding:10px; height:320px; overflow:auto;">
  <?php $lastId = 0; ?>
  <?php foreach ($messages as $m): ?>
    <?php $lastId = max($lastId, (int)$m['id']); ?>
    <p><b><?= htmlspecialchars($m['name']) ?>:</b> <?= htmlspecialchars($m['message']) ?></p>
  <?php endforeach ?>
</div>

<div style="margin-top:10px;">
  <input id="msg" style="width:75%;" placeholder="Scrie un mesaj...">
  <button id="sendBtn">Send</button>
</div>

<script>
const BASE_URL = "<?= BASE_URL ?>";
let sinceId = <?= (int)($lastId ?? 0) ?>;

function escapeHtml(s){
  return s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;')
          .replaceAll('"','&quot;').replaceAll("'","&#039;");
}

function appendMessage(m){
  const box = document.getElementById('messages');
  const p = document.createElement('p');
  p.innerHTML = `<b>${escapeHtml(m.name)}:</b> ${escapeHtml(m.message)}`;
  box.appendChild(p);
  box.scrollTop = box.scrollHeight;
}

async function poll(){
  const res = await fetch(`${BASE_URL}chat-poll&since=${sinceId}`, {credentials:'same-origin'});
  if(!res.ok) return;
  const data = await res.json();
  for(const m of data.messages){
    appendMessage(m);
    sinceId = Math.max(sinceId, parseInt(m.id,10));
  }
}

async function send(){
  const input = document.getElementById('msg');
  const text = input.value.trim();
  if(!text) return;

  const fd = new FormData();
  fd.append('message', text);

  const res = await fetch(`${BASE_URL}chat`, {method:'POST', body: fd, credentials:'same-origin'});
  if(res.ok){
    input.value = '';
    await poll();
  }
}

document.getElementById('sendBtn').addEventListener('click', send);
document.getElementById('msg').addEventListener('keydown', (e)=>{ if(e.key==='Enter') send(); });

poll();
setInterval(poll, 1500);
</script>

