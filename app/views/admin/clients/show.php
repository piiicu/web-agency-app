<?php require __DIR__ . '/../_nav.php'; ?>

<h2>Client: <?= htmlspecialchars($client['name']) ?></h2>
<p><a href="<?= BASE_URL ?>admin/clients">⬅ Back to clients</a></p>

<h3>Profile</h3>
<ul>
  <li><b>Email:</b> <?= htmlspecialchars($client['email']) ?></li>
  <li><b>Company:</b> <?= htmlspecialchars($client['company'] ?? '') ?></li>
  <li><b>Phone:</b> <?= htmlspecialchars($client['phone'] ?? '') ?></li>
  <li><b>Address:</b> <?= htmlspecialchars($client['address'] ?? '') ?></li>
</ul>

<hr>

<h3>Tickets</h3>
<?php if (empty($tickets)): ?>
  <p>No tickets yet.</p>
<?php else: ?>
  <table border="1" cellpadding="8" cellspacing="0">
    <tr>
      <th>ID</th>
      <th>Subject</th>
      <th>Status</th>
      <th>Priority</th>
      <th>Updated</th>
    </tr>
    <?php foreach ($tickets as $t): ?>
      <tr>
        <td><a href="<?= BASE_URL ?>admin/ticket&id=<?= (int)$t['id'] ?>">#<?= (int)$t['id'] ?></a></td>
        <td><?= htmlspecialchars($t['subject']) ?></td>
        <td><?= htmlspecialchars($t['status']) ?></td>
        <td><?= htmlspecialchars($t['priority']) ?></td>
        <td><?= htmlspecialchars($t['updated_at']) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>
