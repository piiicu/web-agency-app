<?php require __DIR__ . '/../_layout_start.php'; ?>

<div class="page-header">
  <div class="page-header__left">
    <h2 class="page-header__title">Utilizatori</h2>
  </div>
</div>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <div class="flash flash--error"><?php echo htmlspecialchars($_SESSION['flash_error']); ?></div>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <div class="flash flash--success"><?php echo htmlspecialchars($_SESSION['flash_success']); ?></div>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php
  // On this dedicated page, keep the original redirect behaviour (back to admin/users).
  $redirectTarget = '';
  require __DIR__ . '/panel.php';
?>

<?php require __DIR__ . '/../_layout_end.php'; ?>
