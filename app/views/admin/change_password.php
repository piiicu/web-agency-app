<?php require __DIR__ . '/_layout_start.php'; ?>

<div class="page-header">
  <div class="page-header__left">
    <h2 class="page-header__title">Schimbă parola</h2>
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

<div class="card">
  <form method="POST" action="<?= BASE_URL ?>admin/change-password" class="form-standard">
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

<?php require __DIR__ . '/_layout_end.php'; ?>
