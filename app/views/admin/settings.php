<?php require __DIR__ . '/_layout_start.php'; ?>
<?php $isAdmin = (Auth::role() === 'admin'); ?>

<h2 class="page-title">Settings</h2>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="flash flash--error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="flash flash--success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<div class="tabs">
  <?php if ($isAdmin): ?>
    <button class="tab-btn" data-tab="users">Users</button>
  <?php endif; ?>
  <button class="tab-btn" data-tab="password">Change password</button>
  <button class="tab-btn" data-tab="profile">Editează profilul</button>
</div>

<?php if ($isAdmin): ?>
  <div id="users" class="tab-panel">
    <h3 class="section-title">Users</h3>

    <?php
      // Reuse the same panel used on the dedicated Admin -> Users page.
      $redirectTarget = 'settings';
      require __DIR__ . '/users/panel.php';
    ?>
  </div>
<?php endif; ?>

<div id="password" class="tab-panel">
  <h3 class="section-title">Change my password</h3>

  <div class="card">
    <form method="POST" action="<?= BASE_URL ?>admin/change-password">
      <div class="form-row">
        <label class="label">Old password</label><br>
        <input class="input" type="password" name="current_password" required>
      </div>

      <div class="form-row">
        <label class="label">New password</label><br>
        <input class="input" type="password" name="new_password" required>
      </div>

      <div class="form-row">
        <label class="label">Confirm new password</label><br>
        <input class="input" type="password" name="new_password_confirm" required>
      </div>

      <button class="btn" type="submit">Update password</button>
    </form>
  </div>
</div>

<div id="profile" class="tab-panel">
  <h3 class="section-title">Editează profilul</h3>

  <div class="card">
    <form method="POST" action="<?= BASE_URL ?>admin/profile" enctype="multipart/form-data">

      <div class="form-row">
        <label class="label">Name</label><br>
        <input class="input" name="name" value="<?= htmlspecialchars($me['name'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <label class="label">Email</label><br>
        <input class="input" type="email" name="email" value="<?= htmlspecialchars($me['email'] ?? '') ?>" required>
      </div>

      <div class="form-row">
        <label class="label">Phone</label><br>
        <input class="input" name="phone" value="<?= htmlspecialchars($me['phone'] ?? '') ?>">
      </div>

      <div class="form-row">
        <label class="label">Avatar (jpg/png/webp, max 3MB)</label><br>
        <input class="input" style="padding:8px 12px;" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp">
      </div>

      <button class="btn" type="submit">Save changes</button>
    </form>
  </div>
</div>

<script>
  const buttons = document.querySelectorAll('.tab-btn');
  const panels = document.querySelectorAll('.tab-panel');

  function activate(tab) {
    buttons.forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    panels.forEach(p => p.classList.toggle('active', p.id === tab));
    if (location.hash !== '#' + tab) history.replaceState(null, '', '#'+tab);
  }

  const defaultTab = <?= $isAdmin ? "'users'" : "'password'" ?>;
  const hash = (location.hash || ('#' + defaultTab)).replace('#', '');
  const allowed = <?= $isAdmin ? "['users','password','profile']" : "['password','profile']" ?>;
  activate(allowed.includes(hash) ? hash : defaultTab);

  buttons.forEach(btn => btn.addEventListener('click', () => activate(btn.dataset.tab)));
</script>

<?php require __DIR__ . '/_layout_end.php'; ?>
