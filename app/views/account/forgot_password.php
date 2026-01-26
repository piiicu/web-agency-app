<?php require __DIR__ . '/../partials/head.php'; ?>

<div class="auth-page">
  <div class="auth-card card">
    <div class="auth-brand">
      <img class="auth-logo" src="<?= ASSET_URL ?>assets/img/app-logo.svg" alt="App logo" onerror="this.style.display='none'">
      <div class="auth-title">Resetare parolă</div>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="flash flash--error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
      <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_success'])): ?>
      <div class="flash flash--success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>forgot-password" class="auth-form">
      <div class="form-row">
        <label class="label">Email</label><br>
        <input class="input" type="email" name="email" placeholder="email@example.com" required>
      </div>

      <button class="btn auth-btn" type="submit">Generează link</button>
    </form>

    <?php if (!empty($_SESSION['reset_link'])): ?>
      <div class="card card--muted" style="margin-top:12px;">
        <div style="font-weight:700; margin-bottom:6px;">Link resetare (local)</div>
        <div style="word-break:break-all; font-size:13px;">
          <a href="<?= htmlspecialchars($_SESSION['reset_link']) ?>"><?= htmlspecialchars($_SESSION['reset_link']) ?></a>
        </div>
        <?php unset($_SESSION['reset_link']); ?>
      </div>
    <?php endif; ?>

    <div class="auth-links">
      <a href="<?= BASE_URL ?>login">Înapoi la login</a>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
