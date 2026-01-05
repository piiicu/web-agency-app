<?php require __DIR__ . '/../partials/head.php'; ?>
<div class="container">

<h2 class="h2">Admin Dashboard</h2>

<div class="layout">
  <?php require __DIR__ . '/_sidebar.php'; ?>

  <main class="content">
    <h3 class="h3">Informații personale</h3>

    <?php
      $name = $me['name'] ?? '';
      $email = $me['email'] ?? '';
      $phone = $me['phone'] ?? '';
      $initial = strtoupper(substr(trim($name), 0, 1)) ?: '?';
    ?>

    <div class="card card--muted">
      <div class="profile-row">
        <div>
          <?php if (!empty($me['avatar'])): ?>
            <img class="avatar" src="<?= BASE_URL ?>avatar&user_id=<?= (int)$me['id'] ?>" alt="avatar">
          <?php else: ?>
            <div class="avatar-fallback"><?= htmlspecialchars($initial) ?></div>
          <?php endif; ?>
        </div>

        <div style="flex:1;">
          <div class="title"><?= htmlspecialchars($name) ?></div>
          <div class="muted"><?= htmlspecialchars($email) ?></div>

          <?php if (!empty($phone)): ?>
            <div>
              <span class="pill">📞 <?= htmlspecialchars($phone) ?></span>
            </div>
          <?php endif; ?>

          <div style="margin-top:14px;">
            <a href="<?= BASE_URL ?>admin/settings">⚙️ Mergi la Settings</a>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
