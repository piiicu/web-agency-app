<h2>Ticket #<?= (int)$ticket['id'] ?> — <?= htmlspecialchars($ticket['subject']) ?></h2>
<p><a href="<?= BASE_URL ?>client/tickets">⬅ Back to tickets</a></p>

<p>Status: <b><?= htmlspecialchars($ticket['status']) ?></b></p>

<hr>

<div style="border:1px solid #ddd; padding:10px;">
  <?php foreach ($messages as $m): ?>
    <p>
      <b><?= htmlspecialchars($m['name']) ?>:</b>
      <?= nl2br(htmlspecialchars($m['body'])) ?>
      <span style="color:#999; font-size:12px;">(<?= htmlspecialchars($m['created_at']) ?>)</span>
    </p>
  <?php endforeach; ?>
</div>

<hr>

<form method="POST" action="<?= BASE_URL ?>client/ticket-message">
  <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
  <textarea name="body" placeholder="Write a reply..." style="width: 60%; height: 120px;" required></textarea>
  <br><br>
  <button type="submit">Reply</button>
</form>
