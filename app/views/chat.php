<h1>Chat</h1>

<div id="messages">
<?php foreach ($messages as $m): ?>
<p><b><?= $m['name'] ?>:</b> <?= $m['message'] ?></p>
<?php endforeach ?>
</div>

<input id="msg">
<button onclick="send()">Send</button>

<script>
function send() {
  fetch('/chat', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'message='+msg.value
  }).then(()=>location.reload());
}
</script>
