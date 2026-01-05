<?php
Auth::requireRole(['admin']);
require __DIR__ . '/_nav.php';
?>

<h2>Change my password (Admin)</h2>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <p style="color:red;"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <p style="color:green;"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>admin/change-password">
  <div>
    <label>Current password</label><br>
    <input type="password" name="current_password" required>
  </div>
  <br>

  <div>
    <label>New password</label><br>
    <input type="password" name="new_password" required>
  </div>
  <br>

  <div>
    <label>Confirm new password</label><br>
    <input type="password" name="new_password_confirm" required>
  </div>
  <br>

  <button type="submit">Update</button>
</form>
