<?php require __DIR__ . '/../_layout_start.php'; ?>

<h2 class="page-title">Clienți</h2>

<?php if (empty($clients)): ?>
  <p>Nu s-au găsit clienți.</p>
<?php else: ?>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Nume</th>
        <th>Email</th>
        <th>Tichete</th>
        <th>Acțiuni</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($clients as $c): ?>
        <tr>
          <td><?= (int)$c['id'] ?></td>
          <td><?= htmlspecialchars($c['name']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= (int)($c['tickets_count'] ?? 0) ?></td>
          <td>
            <a class="btn"
               href="<?= BASE_URL ?>admin/client&id=<?= (int)$c['id'] ?>">
              Vezi
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/../_layout_end.php'; ?>
