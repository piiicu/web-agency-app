<?php require __DIR__ . '/_layout_start.php'; ?>

<?php
  $name  = $me['name'] ?? '';
  $email = $me['email'] ?? '';
  $phone = $me['phone'] ?? '';
  $role  = $me['role'] ?? '';
  $initial = strtoupper(substr(trim($name), 0, 1)) ?: '?';
?>

<h2 class="page-title">Bine ai venit <?= htmlspecialchars($name) ?>!</h2>

<h3 class="section-title">Informații personale</h3>

<div class="card card--muted">
  <div class="profile-row">
    <div>
      <?php if (!empty($me['avatar'])): ?>
        <img class="avatar" src="<?= BASE_URL ?>avatar&user_id=<?= (int)$me['id'] ?>" alt="avatar">
      <?php else: ?>
        <div class="avatar-fallback"><?= htmlspecialchars($initial) ?></div>
      <?php endif; ?>
    </div>

    <div class="flex-1">
      <div class="profile-name">
        <?= htmlspecialchars($name) ?>
      </div>

      <div class="muted profile-email">
        <?= htmlspecialchars($email) ?>
      </div>

      <div class="mt-14">
        <?php if (!empty($phone)): ?>
          <span class="pill">📞 <?= htmlspecialchars($phone) ?></span>
        <?php endif; ?>

        <?php if (!empty($role)): ?>
          <span class="pill">🛡 <?= htmlspecialchars($role) ?></span>
        <?php endif; ?>
      </div>

      <div class="mt-14 mt-32">
        <a href="<?= BASE_URL ?>admin/settings#profile" class="btn">✏️ Editează profilul</a>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
