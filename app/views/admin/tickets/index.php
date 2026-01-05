<?php
// app/views/admin/tickets/index.php
require __DIR__ . '/../_nav.php';
?>

<h2>Tickets Inbox</h2>

<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>ID</th>
    <th>Client</th>
    <th>Subject</th>
    <th>Status</th>
    <th>Last public message</th>
    <th>Updated</th>
  </tr>

  <?php foreach ($tickets as $t): ?>
    <tr>
      <td>
        <a href="<?= BASE_URL ?>admin/ticket&id=<?= (int)$t['id'] ?>">
          #<?= (int)$t['id'] ?>
        </a>
      </td>
      <td><?= htmlspecialchars($t['client_name']) ?></td>
      <td><?= htmlspecialchars($t['subject']) ?></td>
      <td><?= htmlspecialchars($t['status']) ?></td>
      <td><?= htmlspecialchars($t['last_public_message'] ?? '') ?></td>
      <td><?= htmlspecialchars($t['updated_at']) ?></td>
    </tr>
  <?php endforeach; ?>
</table>
