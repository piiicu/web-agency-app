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
  // Badge behavior (clasic): badge-ul apare doar când valoarea este > 0
  function setBadge(el, value) {
    if (!el) return;
    const n = Number.isFinite(Number(value)) ? parseInt(value, 10) : 0;
    el.textContent = String(n);

    // show only when > 0
    el.style.display = (n > 0) ? '' : 'none';
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

      // Mobile bottom nav (if present)
      setBadge(document.getElementById('badgeTicketsMobile'), data.tickets_open);
      setBadge(document.getElementById('badgeTasksMobile'), data.tasks_pending);
      setBadge(document.getElementById('badgeChatMobile'), data.chat_new);
    } catch (e) {
      // ignore polling errors (e.g. non-JSON redirect) without breaking UI
    }
  }

  pollBadges();
  setInterval(pollBadges, 15000);
</script>
