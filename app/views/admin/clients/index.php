<?php require __DIR__ . '/../_nav.php'; ?>

<h2>Clients</h2>

<table border="1" cellpadding="8" cellspacing="0">
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>Company</th>
    <th>Phone</th>
  </tr>

  <?php foreach ($clients as $c): ?>
    <tr>
      <td><?= (int)$c['id'] ?></td>
      <td>
        <a href="<?= BASE_URL ?>admin/client&id=<?= (int)$c['id'] ?>">
          <?= htmlspecialchars($c['name']) ?>
        </a>
      </td>
      <td><?= htmlspecialchars($c['email']) ?></td>
      <td><?= htmlspecialchars($c['company'] ?? '') ?></td>
      <td><?= htmlspecialchars($c['phone'] ?? '') ?></td>
    </tr>
  <?php endforeach; ?>
</table>
