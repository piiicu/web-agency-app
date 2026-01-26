      </div>
    </div>
  </main>

  <aside class="app-sidebar" data-sidebar>
    <div class="app-sidebar__mobile-header">
      <div class="app-sidebar__mobile-title">Menu</div>
      <button class="app-sidebar__close" type="button" data-sidebar-close aria-label="Close menu">✕</button>
    </div>
    <?php $ticketsActive = (strpos($currentRoute, 'client/tickets') === 0 || strpos($currentRoute, 'client/ticket') === 0) ? 'is-active' : ''; ?>
    <nav class="client-sidebar">
      <a class="nav-item <?= $active('client/dashboard') ?>" href="<?= BASE_URL ?>client/dashboard"><span>🏠 Dashboard</span></a>
      <a class="nav-item <?= $ticketsActive ?>" href="<?= BASE_URL ?>client/tickets"><span>🎫 Tickets</span></a>
      <a class="nav-item <?= $active('client/account') ?>" href="<?= BASE_URL ?>client/account"><span>👤 My Account</span></a>
      <a class="nav-item" href="<?= BASE_URL ?>logout"><span>🚪 Logout</span></a>
    </nav>
  </aside>

  <div class="app-overlay" data-sidebar-overlay></div>

  <nav class="app-bottomnav" aria-label="Bottom navigation">
    <a class="app-bottomnav__item <?= $active('client/dashboard') ?>" href="<?= BASE_URL ?>client/dashboard" aria-label="Dashboard">
      <span class="app-bottomnav__icon">🏠</span>
      <span class="app-bottomnav__label">Home</span>
    </a>
    <a class="app-bottomnav__item <?= $ticketsActive ?>" href="<?= BASE_URL ?>client/tickets" aria-label="Tickets">
      <span class="app-bottomnav__icon">🎫</span>
      <span class="app-bottomnav__label">Tickets</span>
    </a>
    <a class="app-bottomnav__item <?= $active('client/account') ?>" href="<?= BASE_URL ?>client/account" aria-label="Account">
      <span class="app-bottomnav__icon">👤</span>
      <span class="app-bottomnav__label">Account</span>
    </a>
    <button class="app-bottomnav__item" type="button" data-sidebar-open aria-label="Menu">
      <span class="app-bottomnav__icon">☰</span>
      <span class="app-bottomnav__label">Menu</span>
    </button>
  </nav>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
