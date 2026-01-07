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

<div class="tickets-header">
  <h2 class="tickets-title">Tickets Inbox</h2>
<!-- Search -->
  <form method="GET" action="<?= htmlspecialchars(BASE_URL . 'admin/tickets') ?>" class="tickets-search">
    <?php if ($isRouteMode): ?>
      <input type="hidden" name="route" value="admin/tickets">
    <?php endif; ?>
    <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">

    <input
      type="search"
      name="q"
      placeholder="Caută după nume tichet / client..."
      value="<?= htmlspecialchars($q) ?>"
    />
    <button class="btn" type="submit">Caută</button>
  </form>
</div>

<div class="tickets-tabs">
  <a class="btn <?= $tab === 'open' ? 'btn-primary' : '' ?>" href="<?= htmlspecialchars(ticketsTabUrl('open', $q, $paramSep)) ?>">Tichete noi</a>
  <a class="btn <?= $tab === 'resolved' ? 'btn-primary' : '' ?>" href="<?= htmlspecialchars(ticketsTabUrl('resolved', $q, $paramSep)) ?>">Tichete rezolvate</a>
  <a class="btn <?= $tab === 'deleted' ? 'btn-primary' : '' ?>" href="<?= htmlspecialchars(ticketsTabUrl('deleted', $q, $paramSep)) ?>">Șterse</a>
</div>
<!-- Notificare tichete noi -->
<div id="ticketsLiveBar" class="tickets-livebar">
  Au apărut tichete noi. <button class="btn" id="reloadTicketsBtn" type="button">Reîncarcă</button>
</div>

<!-- Toolbar deasupra tabelului (dreapta) -->
<div class="tickets-toolbar">
  <button id="openExportModal" class="btn" type="button">Export</button>
</div>

<!-- Modal export (lightbox) -->
<div id="exportModal" class="modal-overlay" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-label="Export tichete">
    <div class="modal-header">
      <h3>Export tichete (CSV)</h3>
      <button id="closeExportModal" class="btn" type="button">Închide</button>
    </div>

    <p>Exportă tichetele din tabul curent sau aplică filtre suplimentare.</p>

    <form method="GET" action="<?= htmlspecialchars(BASE_URL . 'admin/tickets-export') ?>" class="modal-form">
      <?php if ($isRouteMode): ?>
        <input type="hidden" name="route" value="admin/tickets-export">
      <?php endif; ?>

      <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
      <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

      <input name="id" type="text" placeholder="ID (exact)">
      <input name="subject" type="text" placeholder="Nume tichet">

      <input name="client" type="text" placeholder="Client">
      <select name="status">
        <option value="">Status (toate)</option>
        <option value="open">open</option>
        <option value="resolved">resolved</option>
        <option value="deleted">deleted</option>
      </select>

      <div class="modal-actions">
        <button class="btn" type="button" id="exportModalCancel">Anulează</button>
        <button class="btn btn-primary" type="submit">Descarcă CSV</button>
      </div>
    </form>
  </div>
</div>

<form id="bulkForm" method="POST" action="<?= htmlspecialchars(BASE_URL . 'admin/tickets-bulk-delete') ?>">
  <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
  <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

  <button id="bulkDeleteBtn" class="btn bulk-delete-btn" type="submit"
    onclick="return confirm('Ștergi tichetele selectate?')">
    Șterge tichetele selectate
  </button>

  <table id="ticketsTable" class="tickets-table">
    <thead>
      <tr>
        <th class="col-drag">⇅</th>
        <th class="col-check"><input id="checkAll" type="checkbox" /></th>
        <th class="col-id">ID</th>
        <th>Client</th>
        <th>Subject</th>
        <th class="col-status">Status</th>
        <th>Ultimul mesaj</th>
        <th class="col-updated">Updated</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($tickets as $t): ?>
        <tr data-ticket-id="<?= (int)$t['id'] ?>">
          <td class="drag-handle" draggable="true" title="Trage ca să reordonezi">⋮⋮</td>

          <td>
            <input class="rowCheck" type="checkbox" name="ticket_ids[]" value="<?= (int)$t['id'] ?>" />
          </td>

          <td>
            <a href="<?= htmlspecialchars(BASE_URL . 'admin/ticket' . $idSep . (int)$t['id']) ?>">#<?= (int)$t['id'] ?></a>
          </td>

          <td><?= htmlspecialchars($t['client_name']) ?></td>
          <td class="ticket-subject-cell">
            <?= htmlspecialchars($t['subject']) ?>

            <div class="ticket-actions">
              <a class="btn" href="<?= htmlspecialchars(BASE_URL . 'admin/ticket' . $idSep . (int)$t['id']) ?>">Deschide</a>

              <?php if ($tab !== 'deleted'): ?>
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
            </div>
          </td>
          <td><?= htmlspecialchars($t['status']) ?></td>
          <td><?= htmlspecialchars($t['last_public_message'] ?? '') ?></td>
          <td><?= htmlspecialchars($t['updated_at'] ?? '') ?></td>
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
      const res = await fetch('<?= htmlspecialchars(BASE_URL . "admin/tickets-poll") ?>', { credentials: 'same-origin' });
      if (!res.ok) return;

      const data = await res.json();
      // prima rulare: inițializează baseline și NU afișa notificarea
      if (lastOpenCount === null || lastLatest === null) {
        lastOpenCount = data.open_count;
        lastLatest = data.latest_updated_at;
        liveBar.style.display = 'none';
        return;
      }

      const changed = (data.open_count !== lastOpenCount) || (data.latest_updated_at !== lastLatest);
      if (changed) {
        liveBar.style.display = 'block';
        document.title = `Tickets (${data.open_count} noi)`;
      } else {
        liveBar.style.display = 'none';
      }
    } catch (e) {}
  }

  setInterval(pollTickets, 15000);
  pollTickets();

  // Modal export (lightbox)
  const exportModal = document.getElementById('exportModal');
  const openExportModal = document.getElementById('openExportModal');
  const closeExportModal = document.getElementById('closeExportModal');
  const exportModalCancel = document.getElementById('exportModalCancel');

  function openModal() {
    exportModal.classList.add('is-open');
    exportModal.setAttribute('aria-hidden', 'false');
  }

  function closeModal() {
    exportModal.classList.remove('is-open');
    exportModal.setAttribute('aria-hidden', 'true');
  }

  openExportModal?.addEventListener('click', openModal);
  closeExportModal?.addEventListener('click', closeModal);
  exportModalCancel?.addEventListener('click', closeModal);

  exportModal?.addEventListener('click', (e) => {
    if (e.target === exportModal) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });
</script>

<?php require __DIR__ . '/../_layout_end.php'; ?>
