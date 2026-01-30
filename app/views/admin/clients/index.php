<?php require __DIR__ . '/../_layout_start.php'; ?>

<div class="page-header">
  <div class="page-header__left">
    <h2 class="page-header__title">Clienți</h2>
  </div>
</div>

<?php if (empty($clients)): ?>
  <p>Nu s-au găsit clienți.</p>
<?php else: ?>
  <div class="table-wrap rtable">
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

  </div>

  <!-- Mobile cards -->
  <div class="rtable-cards" aria-label="Lista clienți">
    <?php foreach ($clients as $c): ?>
      <div class="data-card">
        <div class="data-card__top">
          <div>
            <p class="data-card__title" style="margin:0;">#<?= (int)$c['id'] ?> — <?= htmlspecialchars($c['name']) ?></p>
            <div class="data-card__meta">
              <div><b>Email:</b> <?= htmlspecialchars($c['email']) ?></div>
              <div><b>Tichete:</b> <?= (int)($c['tickets_count'] ?? 0) ?></div>
            </div>
          </div>
        </div>
        <div class="data-card__actions">
          <a class="btn" href="<?= BASE_URL ?>admin/client&id=<?= (int)$c['id'] ?>">Vezi</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../_layout_end.php'; ?>
