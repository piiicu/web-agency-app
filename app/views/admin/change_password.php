<?php require __DIR__ . '/_layout_start.php'; ?>

<h2 class="page-title">Change my password</h2>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="flash flash--error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="flash flash--success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

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

<?php require __DIR__ . '/_layout_end.php'; ?>
