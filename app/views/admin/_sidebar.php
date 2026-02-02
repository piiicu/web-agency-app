<?php Auth::requireRole(['admin','employee','staff']); ?>

<?php
$current = $_GET['route'] ?? '';
function active($needle, $current) {
  return strpos($current, $needle) === 0 ? 'active' : '';
}
?>

<aside class="sidebar">
  <a class="nav-item <?= active('admin/dashboard', $current) ?>" href="<?= BASE_URL ?>admin/dashboard">
    <span>🏠 Dashboard</span>
  </a>

  <a class="nav-item <?= active('admin/tickets', $current) ?>" href="<?= BASE_URL ?>admin/tickets">
    <span>🎫 Tichete</span>
    <span id="badgeTickets" class="badge">0</span>
  </a>

  <a class="nav-item <?= active('chat', $current) ?>" href="<?= BASE_URL ?>chat">
    <span>💬 Chat intern</span>
    <span id="badgeChat" class="badge">0</span>
  </a>

  <a class="nav-item <?= active('tasks', $current) ?>" href="<?= BASE_URL ?>tasks">
    <span>✅ Task-uri interne</span>
    <span id="badgeTasks" class="badge">0</span>
  </a>

  <a class="nav-item <?= active('admin/clients', $current) ?>" href="<?= BASE_URL ?>admin/clients">
    <span>👥 Clienți</span>
  </a>

  <a class="nav-item <?= active('admin/settings', $current) ?>" href="<?= BASE_URL ?>admin/settings">
    <span>⚙️ Setări</span>
  </a>

  <a class="nav-item" href="<?= BASE_URL ?>logout">
    <span>🚪 Deconectare</span>
  </a>
</aside>

<script>

</script>
