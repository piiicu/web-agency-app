<?php Auth::requireRole(['admin','employee','staff']); ?>

<?php
$current = $_GET['route'] ?? '';
function active($needle, $current) {
  return strpos($current, $needle) === 0 ? 'active' : '';
}
?>

<aside class="sidebar">
  <a class="<?= active('admin/dashboard', $current) ?>" href="<?= BASE_URL ?>admin/dashboard">🏠 Dashboard</a>
  <a class="<?= active('admin/tickets', $current) ?>" href="<?= BASE_URL ?>admin/tickets">🎫 Tickets</a>
  <a class="<?= active('tasks', $current) ?>" href="<?= BASE_URL ?>tasks">✅ Internal Tasks</a>
  <a class="<?= active('admin/clients', $current) ?>" href="<?= BASE_URL ?>admin/clients">👥 Clients</a>
  <a class="<?= active('admin/settings', $current) ?>" href="<?= BASE_URL ?>admin/settings">⚙️ Settings</a>
  <a href="<?= BASE_URL ?>logout">🚪 Logout</a>
</aside>
