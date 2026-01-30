<?php require __DIR__ . '/_layout_start.php'; ?>

<div class="client-account">
  <div class="page-header">
    <div class="page-header__left">
      <h2 class="page-header__title">Contul meu</h2>
      <p class="page-header__subtitle">Actualizează datele de profil și parola.</p>
    </div>
    <div class="page-header__actions">
      <a class="btn btn-ghost" href="<?= BASE_URL ?>client/dashboard">⬅ Înapoi</a>
    </div>
  </div>

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
    <h3 style="margin-top:0;">Profil</h3>

    <div class="card">
      <form method="POST" action="<?= BASE_URL ?>client/profile" enctype="multipart/form-data" class="form-grid">
        <div class="form-row">
          <label class="label">Companie</label>
          <input class="input" name="company" value="<?= htmlspecialchars($client['company'] ?? '') ?>">
        </div>

        <div class="form-row">
          <label class="label">Telefon</label>
          <input class="input" name="phone" value="<?= htmlspecialchars($client['phone'] ?? '') ?>">
        </div>

        <div class="form-row" style="grid-column: 1 / -1;">
          <label class="label">Adresă</label>
          <input class="input" name="address" value="<?= htmlspecialchars($client['address'] ?? '') ?>">
        </div>

        <div class="form-row" style="grid-column: 1 / -1;">
          <label class="label">Poză profil <span class="help">(jpg/png/webp, max 3MB)</span></label>
          <input class="input" style="padding:8px 12px;" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp">
        </div>

        <div class="form-actions" style="grid-column: 1 / -1;">
          <button class="btn" type="submit">Salvează</button>
        </div>
      </form>
    </div>
  </div>

  <!-- CHANGE PASSWORD -->
  <div id="password" class="tab-panel">
    <h3 style="margin-top:0;">Schimbă parola</h3>

    <div class="card">
      <form method="POST" action="<?= BASE_URL ?>client/change-password" class="form-standard">
        <div class="form-row">
          <label class="label">Parola veche</label>
          <input class="input" type="password" name="current_password" required>
        </div>

        <div class="form-row">
          <label class="label">Parola nouă</label>
          <input class="input" type="password" name="new_password" required>
        </div>

        <div class="form-row">
          <label class="label">Confirmă parola nouă</label>
          <input class="input" type="password" name="new_password_confirm" required>
        </div>

        <div class="form-actions">
          <button class="btn" type="submit">Actualizează parola</button>
        </div>
      </form>
    </div>
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
        <div class="form-row">
          <label class="label">Confirmă parola</label>
          <input class="input" type="password" name="current_password" required>
        </div>

        <div class="form-actions">
          <button class="btn" type="submit">🗑 Șterge contul meu</button>
        </div>
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

<?php require __DIR__ . '/_layout_end.php'; ?>
