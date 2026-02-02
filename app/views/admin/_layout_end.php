      </div>
    </div>
  </main>

  <!-- Right sidebar (desktop). On mobile this becomes a drawer. -->
  <aside class="app-sidebar" data-sidebar>
    <div class="app-sidebar__mobile-header">
      <div class="app-sidebar__mobile-title">Meniu</div>
      <button class="app-sidebar__close" type="button" data-sidebar-close aria-label="Close menu">✕</button>
    </div>
    <?php require __DIR__ . '/_sidebar.php'; ?>
  </aside>

  <div class="app-overlay" data-sidebar-overlay></div>

  <!-- Mobile bottom navigation (app-like) -->
  <nav class="app-bottomnav" aria-label="Bottom navigation">
    <?php
      $currentRoute = $_GET['route'] ?? '';
      $active = function($needle) use ($currentRoute) {
        return strpos($currentRoute, $needle) === 0 ? 'is-active' : '';
      };
    ?>
    <a class="app-bottomnav__item <?= $active('admin/dashboard') ?>" href="<?= BASE_URL ?>admin/dashboard" aria-label="Dashboard">
      <span class="app-bottomnav__icon">🏠</span>
      <span class="app-bottomnav__label">Acasă</span>
    </a>
    <a class="app-bottomnav__item <?= $active('admin/tickets') ?>" href="<?= BASE_URL ?>admin/tickets" aria-label="Tickets">
      <span class="app-bottomnav__icon">🎫</span>
      <span class="app-bottomnav__label">Tichete</span>
      <span id="badgeTicketsMobile" class="app-bottomnav__badge" aria-hidden="true">0</span>
    </a>

    <a class="app-bottomnav__item <?= $active('chat') ?>" href="<?= BASE_URL ?>chat" aria-label="Chat intern">
      <span class="app-bottomnav__icon">💬</span>
      <span class="app-bottomnav__label">Chat</span>
      <span id="badgeChatMobile" class="app-bottomnav__badge" aria-hidden="true">0</span>
    </a>

    <a class="app-bottomnav__item <?= $active('tasks') ?>" href="<?= BASE_URL ?>tasks" aria-label="Task-uri interne">
      <span class="app-bottomnav__icon">✅</span>
      <span class="app-bottomnav__label">Task-uri</span>
      <span id="badgeTasksMobile" class="app-bottomnav__badge" aria-hidden="true">0</span>
    </a>
    <button class="app-bottomnav__item" type="button" data-sidebar-open aria-label="Menu">
      <span class="app-bottomnav__icon">☰</span>
      <span class="app-bottomnav__label">Meniu</span>
    </button>
  </nav>
</div>

<?php require __DIR__ . '/../partials/footer.php'; ?>
