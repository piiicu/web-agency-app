<h2>Set your password</h2>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <p style="color:red;"><?= htmlspecialchars($_SESSION['flash_error']) ?></p>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <p style="color:green;"><?= htmlspecialchars($_SESSION['flash_success']) ?></p>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<form method="POST" action="<?= BASE_URL ?>set-password">
  <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

  <div>
    <label>New password</label><br>
    <input type="password" name="password" required>
  </div>
  <br>

  <div>
    <label>Confirm password</label><br>
    <input type="password" name="password_confirm" required>
  </div>
  <br>

  <button type="submit">Set password</button>
</form>

<p style="margin-top: 15px;">
  <a href="<?= BASE_URL ?>login">Back to login</a>
</p>
