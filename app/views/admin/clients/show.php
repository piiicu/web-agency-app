<?php require __DIR__ . '/../_layout_start.php'; ?>

<h2 class="page-title">Client details</h2>

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
        <div class="mt-14"><strong>Company:</strong> <?= htmlspecialchars($company) ?></div>
      <?php endif; ?>

      <?php if ($phone): ?>
        <div class="mt-14"><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<form method="POST" action="<?= BASE_URL ?>admin/client-disable"
      onsubmit="return confirm('Sigur dezactivezi (ștergi) acest client? Nu va mai putea intra în cont.');">
  <input type="hidden" name="id" value="<?= (int)$client['id'] ?>">
  <button class="btn" type="submit">🗑 Șterge client</button>
</form>


<h3 class="section-title">Client tickets</h3>

<?php if (empty($tickets)): ?>
  <p>This client has no tickets.</p>
<?php else: ?>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Subject</th>
        <th>Status</th>
        <th>Updated</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tickets as $t): ?>
        <tr>
          <td>#<?= (int)$t['id'] ?></td>
          <td><?= htmlspecialchars($t['subject']) ?></td>
          <td><?= htmlspecialchars($t['status']) ?></td>
          <td><?= htmlspecialchars($t['updated_at']) ?></td>
          <td>
            <a class="btn btn--secondary"
               href="<?= BASE_URL ?>admin/ticket&id=<?= (int)$t['id'] ?>">
              Open
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require __DIR__ . '/../_layout_end.php'; ?>
