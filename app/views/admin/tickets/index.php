<?php require __DIR__ . '/../_layout_start.php'; ?>

<?php
$tab = (string)($_GET['tab'] ?? 'open');
if (!in_array($tab, ['open', 'resolved', 'deleted'], true)) $tab = 'open';
$q = trim((string)($_GET['q'] ?? ''));

// Link helpers: supports both modes
$isRouteMode = strpos((string)BASE_URL, '?route=') !== false;
$paramSep = $isRouteMode ? '&' : '?';
$idSep = $isRouteMode ? '&id=' : '?id=';

function ticketsTabUrl(string $tab, string $q, string $paramSep): string
{
  $url = BASE_URL . 'admin/tickets';
  $params = ['tab' => $tab];
  if ($q !== '') $params['q'] = $q;
  return $url . $paramSep . http_build_query($params);
}
?>

<div class="tickets-header" style="display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
  <h2 style="margin:0;">Tickets Inbox</h2>

  <form method="GET" action="<?= htmlspecialchars(BASE_URL . 'admin/tickets') ?>" style="display:flex; gap:8px; align-items:center;">
    <?php if ($isRouteMode): ?>
      <input type="hidden" name="route" value="admin/tickets">
    <?php endif; ?>
    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
    <input
      type="search"
      name="q"
      placeholder="Caută după nume tichet / client..."
      value="<?= htmlspecialchars($q) ?>"
      style="min-width:260px; padding:10px 12px; border:1px solid #ddd; border-radius:10px;" />
    <button class="btn" type="submit">Caută</button>
  </form>
</div>

<div class="tickets-tabs" style="display:flex; gap:10px; margin:16px 0 14px;">
  <a class="btn <?= $tab === 'open' ? 'btn-primary' : '' ?>" href="<?= htmlspecialchars(ticketsTabUrl('open', $q, $paramSep)) ?>">Tichete noi</a>
  <a class="btn <?= $tab === 'resolved' ? 'btn-primary' : '' ?>" href="<?= htmlspecialchars(ticketsTabUrl('resolved', $q, $paramSep)) ?>">Tichete rezolvate</a>
  <a class="btn <?= $tab === 'deleted' ? 'btn-primary' : '' ?>" href="<?= htmlspecialchars(ticketsTabUrl('deleted', $q, $paramSep)) ?>">Șterse</a>
</div>

<div id="ticketsLiveBar" style="display:none; margin:10px 0; padding:10px 12px; border:1px solid #ffd79a; background:#fff7e8; border-radius:10px;">
  Au apărut tichete noi. <button class="btn" id="reloadTicketsBtn" type="button">Reîncarcă</button>
</div>


<!-- UN SINGUR FORM (fără nested forms) -->
<form id="bulkForm" method="POST" action="<?= htmlspecialchars(BASE_URL . 'admin/tickets-bulk-delete') ?>">
  <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
  <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

  <button id="bulkDeleteBtn" class="btn" type="submit" style="display:none; margin: 6px 0 10px;"
    onclick="return confirm('Ștergi tichetele selectate?')">
    Șterge tichetele selectate
  </button>

  <table id="ticketsTable" border="1" cellpadding="8" cellspacing="0" style="width:100%; border-collapse:collapse;">
    <thead>
      <tr>
        <th style="width:40px;">⇅</th>
        <th style="width:36px;"><input id="checkAll" type="checkbox" /></th>
        <th style="width:70px;">ID</th>
        <th>Client</th>
        <th>Subject</th>
        <th style="width:110px;">Status</th>
        <th>Ultimul mesaj</th>
        <th style="width:170px;">Updated</th>
        <th style="width:220px;">Acțiuni</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tickets as $t): ?>
        <tr data-ticket-id="<?= (int)$t['id'] ?>">
          <!-- Drag handle -->
          <td class="drag-handle" draggable="true" title="Trage ca să reordonezi"
            style="cursor:grab; user-select:none; text-align:center; font-size:18px;">
            ⋮⋮
          </td>

          <td>
            <input class="rowCheck" type="checkbox" name="ticket_ids[]" value="<?= (int)$t['id'] ?>" />
          </td>

          <td>
            <a href="<?= htmlspecialchars(BASE_URL . 'admin/ticket' . $idSep . (int)$t['id']) ?>">#<?= (int)$t['id'] ?></a>
          </td>

          <td><?= htmlspecialchars($t['client_name']) ?></td>
          <td><?= htmlspecialchars($t['subject']) ?></td>
          <td><?= htmlspecialchars($t['status']) ?></td>
          <td><?= htmlspecialchars($t['last_public_message'] ?? '') ?></td>
          <td><?= htmlspecialchars($t['updated_at'] ?? '') ?></td>

          <td style="white-space:nowrap;">
            <a class="btn" href="<?= htmlspecialchars(BASE_URL . 'admin/ticket' . $idSep . (int)$t['id']) ?>">Deschide</a>

            <?php if ($tab !== 'deleted'): ?>
              <!-- DELETE: buton care trimite acest form către alt endpoint -->
              <button
                class="btn"
                type="submit"
                name="ticket_id"
                value="<?= (int)$t['id'] ?>"
                formaction="<?= htmlspecialchars(BASE_URL . 'admin/ticket-delete') ?>"
                formmethod="post"
                onclick="return confirm('Ștergi tichetul #<?= (int)$t['id'] ?>?')">
                Șterge
              </button>
            <?php else: ?>
              <!-- RESTORE -->
              <button
                class="btn"
                type="submit"
                name="ticket_id"
                value="<?= (int)$t['id'] ?>"
                formaction="<?= htmlspecialchars(BASE_URL . 'admin/ticket-restore') ?>"
                formmethod="post">
                Restore
              </button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</form>

<script>
  // Bulk delete button show/hide
  const bulkBtn = document.getElementById('bulkDeleteBtn');
  const checkAll = document.getElementById('checkAll');
  const rowChecks = () => Array.from(document.querySelectorAll('.rowCheck'));

  function updateBulkBtn() {
    const any = rowChecks().some(c => c.checked);
    bulkBtn.style.display = any ? 'inline-block' : 'none';
  }

  checkAll?.addEventListener('change', (e) => {
    rowChecks().forEach(c => c.checked = e.target.checked);
    updateBulkBtn();
  });

  document.addEventListener('change', (e) => {
    if (e.target && e.target.classList.contains('rowCheck')) updateBulkBtn();
  });

  // Drag & drop reorder (handle only)
  const tbody = document.querySelector('#ticketsTable tbody');
  let dragged = null;

  function postReorder() {
    if (!tbody) return;
    const ids = Array.from(tbody.querySelectorAll('tr')).map(tr => tr.getAttribute('data-ticket-id'));

    const formData = new FormData();
    ids.forEach(id => formData.append('order[]', id));

    fetch('<?= htmlspecialchars(BASE_URL . 'admin/tickets-reorder') ?>', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    }).catch(() => {});
  }

  tbody?.addEventListener('dragstart', (e) => {
    if (!e.target.classList.contains('drag-handle')) return;

    const tr = e.target.closest('tr');
    if (!tr) return;

    dragged = tr;
    e.dataTransfer.effectAllowed = 'move';
  });

  tbody?.addEventListener('dragover', (e) => {
    if (!dragged) return;
    e.preventDefault();

    const tr = e.target.closest('tr');
    if (!tr || tr === dragged) return;

    const rect = tr.getBoundingClientRect();
    const next = (e.clientY - rect.top) > (rect.height / 2);
    tbody.insertBefore(dragged, next ? tr.nextSibling : tr);
  });

  tbody?.addEventListener('drop', (e) => {
    if (!dragged) return;
    e.preventDefault();

    dragged = null;
    postReorder();
  });

  tbody?.addEventListener('dragend', () => {
    dragged = null;
  });

// Notificare tichete noi
  let lastOpenCount = null;
  let lastLatest = null;

  const liveBar = document.getElementById('ticketsLiveBar');
  const reloadBtn = document.getElementById('reloadTicketsBtn');

  reloadBtn?.addEventListener('click', () => location.reload());

  async function pollTickets() {
    try {
      const res = await fetch('<?= htmlspecialchars(BASE_URL . "admin/tickets-poll") ?>', {
        credentials: 'same-origin'
      });
      if (!res.ok) return;

      const data = await res.json();

      if (lastOpenCount === null) lastOpenCount = data.open_count;
      if (lastLatest === null) lastLatest = data.latest_updated_at;

      const changed = (data.open_count !== lastOpenCount) || (data.latest_updated_at !== lastLatest);

      if (changed) {
        liveBar.style.display = 'block';
        document.title = `Tickets (${data.open_count} noi)`;
      }
    } catch (e) {}
  }

  setInterval(pollTickets, 15000); // la 15 sec
  pollTickets();
</script>

<?php require __DIR__ . '/../_layout_end.php'; ?>