<?php Auth::requireRole(['client']); ?>
<?php require __DIR__ . '/../partials/head.php'; ?>

<div class="container client-account">
  <h2>My Account</h2>
  <p><a href="<?= BASE_URL ?>client/dashboard">⬅ Back</a></p>

  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="flash flash--error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="flash flash--success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>

  <div class="tabs">
    <button class="tab-btn" data-tab="profile">Profile</button>
    <button class="tab-btn" data-tab="password">Change password</button>
    <button class="tab-btn" data-tab="security">Șterge contul</button>
  </div>

  <!-- PROFILE -->
  <div id="profile" class="tab-panel">
    <h3 style="margin-top:0;">Edit profile</h3>

    <form method="POST" action="<?= BASE_URL ?>client/profile" enctype="multipart/form-data">
      <div class="grid">
        <div class="row">
          <div class="label">Company</div>
          <input class="input" name="company" value="<?= htmlspecialchars($client['company'] ?? '') ?>">
        </div>

        <div class="row">
          <div class="label">Phone</div>
          <input class="input" name="phone" value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
        </div>

        <div class="row" style="grid-column: 1 / -1;">
          <div class="label">Address</div>
          <input class="input" name="address" value="<?= htmlspecialchars($client['address'] ?? '') ?>">
        </div>

        <div class="row" style="grid-column: 1 / -1;">
          <div class="label">Avatar (jpg/png/webp, max 3MB)</div>
          <input class="input" style="padding:8px 12px;" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp">
        </div>
      </div>

      <div style="margin-top:14px;">
        <button class="btn" type="submit">Save changes</button>
      </div>
    </form>
  </div>

  <!-- CHANGE PASSWORD -->
  <div id="password" class="tab-panel">
    <h3 style="margin-top:0;">Change password</h3>

    <form method="POST" action="<?= BASE_URL ?>client/change-password">
      <div class="row">
        <div class="label">Old password</div>
        <input class="input" type="password" name="current_password" required>
      </div>

      <div class="row">
        <div class="label">New password</div>
        <input class="input" type="password" name="new_password" required>
      </div>

      <div class="row">
        <div class="label">Confirm new password</div>
        <input class="input" type="password" name="new_password_confirm" required>
      </div>

      <div style="margin-top:14px;">
        <button class="btn" type="submit">Update password</button>
      </div>
    </form>
  </div>

  <!-- SECURITY: DELETE ACCOUNT -->
  <div id="security" class="tab-panel">
    <h3 style="margin-top:0;">Șterge contul</h3>

    <div class="card">
      <p class="muted">
        Ștergerea contului îți va dezactiva accesul. Ticketele rămân în sistem pentru istoric.
      </p>

      <form method="POST" action="<?= BASE_URL ?>client/delete-account"
            onsubmit="return confirm('Sigur vrei să îți ștergi contul? Acțiune ireversibilă.');">
        <div class="row">
          <div class="label">Confirmă parola</div>
          <input class="input" type="password" name="current_password" required>
        </div>

        <button class="btn" type="submit">🗑 Șterge contul meu</button>
      </form>
    </div>
  </div>

  <script>
    const buttons = document.querySelectorAll('.tab-btn');
    const panels = document.querySelectorAll('.tab-panel');

    function activate(tab) {
      buttons.forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
      panels.forEach(p => p.classList.toggle('active', p.id === tab));
      if (location.hash !== '#' + tab) history.replaceState(null, '', '#' + tab);
    }

    buttons.forEach(btn => {
      btn.addEventListener('click', () => activate(btn.dataset.tab));
    });

    // default: profile, dar acceptăm și #password / #security
    const hash = (location.hash || '#profile').replace('#', '');
    const allowed = ['profile', 'password', 'security'];
    activate(allowed.includes(hash) ? hash : 'profile');
  </script>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
