<?php Auth::requireRole(['client']); ?>
<!-- <?php require __DIR__ . '/_nav.php'; ?> -->
<?php require __DIR__ . '/../partials/head.php'; ?>
<div class="container client-dashboard">
  <style>
    .layout {
      display: flex;
      gap: 18px;
      align-items: flex-start;
    }

    .sidebar {
      width: 220px;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      padding: 12px;
      background: #fff;
    }

    .sidebar a {
      display: block;
      padding: 10px 10px;
      border-radius: 10px;
      text-decoration: none;
    }

    .sidebar a:hover {
      background: #f3f4f6;
    }

    .content {
      flex: 1;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      padding: 18px;
      background: #fff;
      min-height: 420px;
    }

    .card {
      display: flex;
      gap: 18px;
      align-items: center;
      padding: 16px;
      border: 1px solid #e5e7eb;
      border-radius: 16px;
      background: #fafafa;
    }

    .avatar {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 1px solid #d1d5db;
      background: #fff;
    }

    .avatar-fallback {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #e5e7eb;
      border: 1px solid #d1d5db;
      font-size: 44px;
      font-weight: 800;
      color: #374151;
    }

    .muted {
      color: #6b7280;
    }

    .pill {
      display: inline-block;
      padding: 6px 10px;
      border-radius: 999px;
      border: 1px solid #e5e7eb;
      background: #fff;
      margin-right: 8px;
      margin-top: 8px;
    }
  </style>

  <h2>Panou control client</h2>

  <div class="layout">
    <aside class="sidebar">
      <a href="<?= BASE_URL ?>client/dashboard">🏠 Dashboard</a>
      <a href="<?= BASE_URL ?>client/tickets">🎫 My Tickets</a>
      <a href="<?= BASE_URL ?>client/account">👤 My Account</a>
      <a href="<?= BASE_URL ?>logout">🚪 Logout</a>
    </aside>

    <main class="content">
      <h3 style="margin-top:0;">Informații personale</h3>

      <?php
      $avatar = $client['avatar'] ?? '';
      $name = $client['name'] ?? '';
      $email = $client['email'] ?? '';
      $initial = strtoupper(substr(trim($name), 0, 1)) ?: '?';
      ?>

      <div class="card">
        <div>
          <?php if (!empty($client['avatar'])): ?>
            <img
              class="avatar"
              src="<?= BASE_URL ?>avatar&user_id=<?= (int)$client['id'] ?>"
              alt="avatar">
          <?php else: ?>
            <div class="avatar-fallback"><?= htmlspecialchars($initial) ?></div>
          <?php endif; ?>

        </div>

        <div style="flex:1;">
          <div style="font-size:22px; font-weight:800;"><?= htmlspecialchars($name) ?></div>
          <div class="muted" style="margin-top:4px;"><?= htmlspecialchars($email) ?></div>

          <div style="margin-top:10px;">
            <?php if (!empty($client['company'])): ?>
              <span class="pill">🏢 <?= htmlspecialchars($client['company']) ?></span>
            <?php endif; ?>
            <?php if (!empty($client['phone'])): ?>
              <span class="pill">📞 <?= htmlspecialchars($client['phone']) ?></span>
            <?php endif; ?>
            <?php if (!empty($client['address'])): ?>
              <span class="pill">📍 <?= htmlspecialchars($client['address']) ?></span>
            <?php endif; ?>

            <?php if (empty($client['company']) && empty($client['phone']) && empty($client['address'])): ?>
              <div class="muted">Completează datele din My Account ca să apară aici.</div>
            <?php endif; ?>
          </div>

          <div style="margin-top:14px;">
            <a href="<?= BASE_URL ?>client/account">✏️ Editează profilul în My Account</a>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>