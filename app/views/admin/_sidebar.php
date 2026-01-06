<?php Auth::requireRole(['admin','employee','staff']); ?>

<?php
$current = $_GET['route'] ?? '';
function active($needle, $current) {
  return strpos($current, $needle) === 0 ? 'active' : '';
}
?>

<style>
  .nav-item {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
  }
  .badge {
    display:none;
    min-width: 22px;
    height: 22px;
    padding: 0 7px;
    border-radius: 999px;
    font-size: 12px;
    line-height: 22px;
    text-align:center;
    font-weight: 700;
    background: #111;
    color: #fff;
  }
</style>

<aside class="sidebar">
  <a class="nav-item <?= active('admin/dashboard', $current) ?>" href="<?= BASE_URL ?>admin/dashboard">
    <span>🏠 Dashboard</span>
  </a>

  <a class="nav-item <?= active('admin/tickets', $current) ?>" href="<?= BASE_URL ?>admin/tickets">
    <span>🎫 Tickets</span>
    <span id="badgeTickets" class="badge">0</span>
  </a>

  <a class="nav-item <?= active('chat', $current) ?>" href="<?= BASE_URL ?>chat">
    <span>💬 Mesaje</span>
    <span id="badgeChat" class="badge">0</span>
  </a>

  <a class="nav-item <?= active('tasks', $current) ?>" href="<?= BASE_URL ?>tasks">
    <span>✅ Internal Tasks</span>
    <span id="badgeTasks" class="badge">0</span>
  </a>

  <a class="nav-item <?= active('admin/clients', $current) ?>" href="<?= BASE_URL ?>admin/clients">
    <span>👥 Clients</span>
  </a>

  <a class="nav-item <?= active('admin/settings', $current) ?>" href="<?= BASE_URL ?>admin/settings">
    <span>⚙️ Settings</span>
  </a>

  <a class="nav-item" href="<?= BASE_URL ?>logout">
    <span>🚪 Logout</span>
  </a>
</aside>

<script>
  function setBadge(el, value) {
    if (!el) return;
    const n = parseInt(value || 0, 10);
    if (n > 0) {
      el.textContent = n;
      el.style.display = 'inline-block';
    } else {
      el.style.display = 'none';
    }
  }

  async function pollBadges() {
    try {
      const res = await fetch('<?= htmlspecialchars(BASE_URL . "admin/badges-poll") ?>', {
        credentials: 'same-origin'
      });
      if (!res.ok) return;

      const data = await res.json();

      setBadge(document.getElementById('badgeTickets'), data.tickets_open);
      setBadge(document.getElementById('badgeTasks'), data.tasks_pending);
      setBadge(document.getElementById('badgeChat'), data.chat_new);
    } catch (e) {}
  }

  pollBadges();
  setInterval(pollBadges, 15000);
</script>
