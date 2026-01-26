<?php require __DIR__ . '/partials/head.php'; ?>

<div class="auth-page">
  <div class="auth-card card">
    <div class="auth-brand">
      <img class="auth-logo" src="<?= ASSET_URL ?>assets/img/app-logo.svg" alt="App logo" onerror="this.style.display='none'">
      <div class="auth-title">Web Agency App</div>
    </div>

    <?php if (!empty($_SESSION['flash_error'])): ?>
      <div class="flash flash--error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
      <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_success'])): ?>
      <div class="flash flash--success"><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
      <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>login" class="auth-form">
      <div class="form-row">
        <label class="label">Email</label><br>
        <input class="input" type="email" name="email" placeholder="email@example.com" required>
      </div>

      <div class="form-row">
        <label class="label">Parolă</label><br>
        <input class="input" type="password" name="password" placeholder="••••••••" required>
      </div>

      <label class="auth-remember">
        <input type="checkbox" name="remember" value="1">
        Ține-mă minte
      </label>

      <button class="btn auth-btn" type="submit">Autentificare</button>

      <div class="auth-links">
        <a href="<?= BASE_URL ?>forgot-password">Ai uitat parola?</a>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
