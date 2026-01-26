<?php require __DIR__ . '/_layout_start.php'; ?>

<div class="client-dashboard">
  <h2 style="margin-top:0;">Panou control client</h2>

  <?php
    $avatar = $client['avatar'] ?? '';
    $name = $client['name'] ?? '';
    $email = $client['email'] ?? '';
    $initial = strtoupper(substr(trim($name), 0, 1)) ?: '?';
  ?>

  <div class="client-card" style="display:flex; gap:18px; align-items:center; padding:16px; border:1px solid #e5e7eb; border-radius:16px; background:#fafafa;">
    <div>
      <?php if (!empty($client['avatar'])): ?>
        <img
          style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:1px solid #d1d5db; background:#fff;"
          src="<?= BASE_URL ?>avatar&user_id=<?= (int)$client['id'] ?>"
          alt="avatar">
      <?php else: ?>
        <div style="width:120px; height:120px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#e5e7eb; border:1px solid #d1d5db; font-size:44px; font-weight:800; color:#374151;">
          <?= htmlspecialchars($initial) ?>
        </div>
      <?php endif; ?>
    </div>

    <div>
      <div style="font-size:18px; font-weight:700;"><?= htmlspecialchars($name) ?></div>
      <div style="color:#6b7280; margin-top:4px;"><?= htmlspecialchars($email) ?></div>
      <div style="margin-top:10px;">
        <span style="display:inline-block; padding:6px 10px; border-radius:999px; border:1px solid #e5e7eb; background:#fff; margin-right:8px; margin-top:8px;">Client</span>
      </div>
    </div>
  </div>

</div>

<?php require __DIR__ . '/_layout_end.php'; ?>
