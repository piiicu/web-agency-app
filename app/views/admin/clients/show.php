<?php require __DIR__ . '/../_layout_start.php'; ?>

<h2 class="page-title">Detalii client</h2>

<?php
$name    = $client['name'] ?? '';
$email   = $client['email'] ?? '';
$phone   = $client['phone'] ?? '';
$company = $client['company'] ?? '';
$initial = strtoupper(substr(trim($name), 0, 1)) ?: '?';
?>

<div class="card card--muted" style="margin-bottom:20px;">
  <div class="profile-row">
    <div>
      <?php if (!empty($client['avatar'])): ?>
        <img class="avatar"
          src="<?= BASE_URL ?>avatar&user_id=<?= (int)$client['id'] ?>"
          alt="avatar">
      <?php else: ?>
        <div class="avatar-fallback"><?= htmlspecialchars($initial) ?></div>
      <?php endif; ?>
    </div>

    <div class="flex-1">
      <div class="title"><?= htmlspecialchars($name) ?></div>
      <div class="muted"><?= htmlspecialchars($email) ?></div>

      <?php if ($company): ?>
        <div class="mt-14"><strong>Companie:</strong> <?= htmlspecialchars($company) ?></div>
      <?php endif; ?>

      <?php if ($phone): ?>
        <div class="mt-14"><strong>Telefon:</strong> <?= htmlspecialchars($phone) ?></div>
      <?php endif; ?>

      <form class="mt-14" method="POST" action="<?= BASE_URL ?>admin/client-disable"
        onsubmit="return confirm('Sigur dezactivezi (ștergi) acest client? Nu va mai putea intra în cont.');">
        <input type="hidden" name="id" value="<?= (int)$client['id'] ?>">
        <button class="btn" type="submit">🗑 Șterge client</button>
      </form>
    </div>
  </div>
</div>




<h3 class="section-title">Tichete clienți</h3>

<?php if (empty($tickets)): ?>
  <p>Acest client nu are tichete.</p>
<?php else: ?>
  <div class="table-wrap rtable">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Subiect</th>
          <th>Status</th>
          <th>Actualizat</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tickets as $t): ?>
          <tr>
            <td>#<?= (int)$t['id'] ?></td>
            <td><?= htmlspecialchars($t['subject']) ?></td>
            <td><?= htmlspecialchars(ticketStatusLabel((string)($t['status'] ?? ''))) ?></td>
            <td><?= htmlspecialchars($t['updated_at']) ?></td>
            <td>
              <a class="btn"
                href="<?= BASE_URL ?>admin/ticket&id=<?= (int)$t['id'] ?>">
                Deschide
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  </div>

  <!-- Mobile cards -->
  <div class="rtable-cards" aria-label="Tichete client">
    <?php foreach ($tickets as $t): ?>
      <?php $st = (string)($t['status'] ?? ''); ?>
      <div class="data-card">
        <div class="data-card__top">
          <div>
            <p class="data-card__title" style="margin:0;">#<?= (int)$t['id'] ?> — <?= htmlspecialchars($t['subject']) ?></p>
            <div class="data-card__meta">
              <div><b>Status:</b> <span class="badge badge--<?= htmlspecialchars($st) ?>"><?= htmlspecialchars(ticketStatusLabel($st)) ?></span></div>
              <div><b>Actualizat:</b> <?= htmlspecialchars($t['updated_at']) ?></div>
            </div>
          </div>
        </div>
        <div class="data-card__actions">
          <a class="btn" href="<?= BASE_URL ?>admin/ticket&id=<?= (int)$t['id'] ?>">Deschide</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../_layout_end.php'; ?>