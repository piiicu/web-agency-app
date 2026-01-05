<?php require __DIR__ . '/_nav.php'; ?>

<h2>Ticket #<?= (int)$ticket['id'] ?> — <?= htmlspecialchars($ticket['subject']) ?></h2>
<p><a href="<?= BASE_URL ?>admin/tickets">⬅ Back to inbox</a></p>

<p>Client: <b><?= htmlspecialchars($ticket['client_name']) ?></b></p>

<form method="POST" action="<?= BASE_URL ?>admin/ticket-status" style="margin: 10px 0;">
  <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
  <label>Status:</label>
  <select name="status">
    <?php foreach (['open','in_progress','resolved','closed'] as $s): ?>
      <option value="<?= $s ?>" <?= $ticket['status'] === $s ? 'selected' : '' ?>>
        <?= $s ?>
      </option>
    <?php endforeach; ?>
  </select>
  <button type="submit">Update</button>
</form>

<hr>

<div style="border:1px solid #ddd; padding:10px;">
  <?php foreach ($messages as $m): ?>
    <p>
      <b><?= htmlspecialchars($m['name']) ?>:</b>
      <?= nl2br(htmlspecialchars($m['body'])) ?>
      <?php if ((int)$m['is_internal'] === 1): ?>
        <span style="background:#ffe7a8; padding:2px 6px; border-radius:6px; font-size:12px;">INTERNAL</span>
      <?php endif; ?>
      <span style="color:#999; font-size:12px;">(<?= htmlspecialchars($m['created_at']) ?>)</span>
    </p>
  <?php endforeach; ?>
</div>

<hr>

<h3>Add message</h3>
<form method="POST" action="<?= BASE_URL ?>admin/ticket-message">
  <input type="hidden" name="ticket_id" value="<?= (int)$ticket['id'] ?>">
  <textarea name="body" placeholder="Write message / internal note..." style="width: 70%; height: 120px;" required></textarea>
  <br><br>
  <label>
    <input type="checkbox" name="is_internal" value="1">
    Internal note (client can't see)
  </label>
  <br><br>
  <button type="submit">Send</button>
</form>
