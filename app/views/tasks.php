<?php
Auth::requireRole(['admin','employee','staff']);

$activeFilter = $_GET['filter'] ?? 'all';
$tab = $_GET['tab'] ?? 'pending'; // pending | done
$q = trim($_GET['q'] ?? '');
$q_done = trim($_GET['q_done'] ?? '');
if ($tab === 'done' && $q_done === '' && $q !== '') { $q_done = $q; }

// counts provided by controller
$count_pending = $count_pending ?? (is_array($tasks_pending ?? null) ? count($tasks_pending) : 0);
$count_done = $count_done ?? (is_array($tasks_done ?? null) ? count($tasks_done) : 0);

function tasks_url(array $params = []): string {
    $base = BASE_URL . 'tasks';
    if (!$params) return $base;
    $sep = (strpos($base, '?') !== false) ? '&' : '?';
    return $base . $sep . http_build_query($params);
}

function prio_label(int $p): string {
    return match ($p) {
        1 => 'Urgent',
        2 => 'Ridicată',
        3 => 'Normal',
        4 => 'Scăzută',
        5 => 'Foarte scăzută',
        default => 'Normal',
    };
}

function prio_class(int $p): string {
    return match ($p) {
        1 => 'badge--danger',
        2 => 'badge--warn',
        3 => 'badge--info',
        4 => 'badge--muted',
        5 => 'badge--muted',
        default => 'badge--info',
    };
}

function preview_text(string $text, int $max = 160): string {
    $text = trim($text);
    if ($text === '') return '';
    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $max, '…');
    }
    return (strlen($text) > $max) ? substr($text, 0, $max) . '…' : $text;
}
?>

<?php require __DIR__ . '/admin/_layout_start.php'; ?>

<div class="page-header">
  <div class="page-header__left">
    <h2 class="page-header__title">Task-uri interne</h2>
    <p class="page-header__subtitle">Organizează activitățile echipei și urmărește progresul.</p>
  </div>

  <div class="page-header__actions">
    <!-- Search (current tab) -->
    <form method="GET" action="<?= BASE_URL ?>tasks" class="tasks-toolbar" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
      <input type="hidden" name="tab" value="<?= htmlspecialchars($tab) ?>">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($activeFilter) ?>">

      <input class="input" type="text" name="<?= $tab === 'done' ? 'q_done' : 'q' ?>"
             placeholder="Caută task..." value="<?= htmlspecialchars($tab === 'done' ? $q_done : $q) ?>"
             style="min-width:260px;">

      <select class="input" name="filter" style="width:auto;" aria-label="Filtru" onchange="this.form.submit()">
        <option value="all" <?= $activeFilter==='all' ? 'selected' : '' ?>>Toate</option>
        <option value="urgent" <?= $activeFilter==='urgent' ? 'selected' : '' ?>>Urgente (1–2)</option>
        <option value="p1" <?= $activeFilter==='p1' ? 'selected' : '' ?>>Prioritate 1</option>
        <option value="p2" <?= $activeFilter==='p2' ? 'selected' : '' ?>>Prioritate 2</option>
        <option value="p3" <?= $activeFilter==='p3' ? 'selected' : '' ?>>Prioritate 3</option>
        <option value="p4" <?= $activeFilter==='p4' ? 'selected' : '' ?>>Prioritate 4</option>
        <option value="p5" <?= $activeFilter==='p5' ? 'selected' : '' ?>>Prioritate 5</option>
      </select>

      <button class="btn btn--ghost" type="submit">🔎</button>

      <button class="btn" type="button" onclick="TasksUI.openCreateModal()">+ Task nou</button>
    </form>
  </div>
</div>

<!-- Tabs -->
<div class="tabs" role="tablist" aria-label="Task tabs">
  <a class="tab-btn <?= $tab==='pending' ? 'active' : '' ?>" role="tab"
     href="<?= tasks_url(array_filter(['tab'=>'pending','filter'=>$activeFilter,'q'=>$q])) ?>">
    De făcut <span class="tasks-pill"><?= (int)$count_pending ?></span>
  </a>

  <a class="tab-btn <?= $tab==='done' ? 'active' : '' ?>" role="tab"
     href="<?= tasks_url(array_filter(['tab'=>'done','filter'=>$activeFilter,'q_done'=>$q_done])) ?>">
    Rezolvate <span class="tasks-pill"><?= (int)$count_done ?></span>
  </a>
</div>

<!-- Panels -->
<div class="tab-panel active" style="padding:0;">

  <?php if ($tab === 'pending'): ?>
    <?php if (empty($tasks_pending)): ?>
      <div style="padding:16px;">Nu există task-uri active.</div>
    <?php else: ?>
      <div class="table-wrap rtable">
        <table class="table tasks-table" style="min-width:760px;">
          <thead>
          <tr>
            <th style="width:54px;">Status</th>
            <th>Task</th>
            <th style="width:180px;">Prioritate</th>
            <th style="width:120px; text-align:center;">Acțiuni</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($tasks_pending as $t): ?>
            <?php $tid = (int)$t['id']; ?>
            <tr class="task-row" data-task-id="<?= $tid ?>">
              <td>
                <form method="POST" action="<?= BASE_URL ?>tasks-done" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $tid ?>">
                  <button class="btn btn--ghost" type="submit" title="Marchează ca rezolvat" aria-label="Rezolvat">✅</button>
                </form>
              </td>
              <td>
                <button type="button" class="task-title" onclick="TasksUI.openDetails(<?= $tid ?>)">
                  <?= htmlspecialchars($t['title'] ?? '') ?>
                </button>
                <?php if (!empty($t['description'])): ?>
                  <div class="task-subtext"><?= htmlspecialchars(preview_text((string)$t['description'], 160)) ?></div>
                <?php else: ?>
                  <div class="task-subtext">—</div>
                <?php endif; ?>
              </td>
              <td>
                <?php $p = (int)($t['priority'] ?? 3); ?>
                <span class="badge <?= prio_class($p) ?>"><?= prio_label($p) ?></span>
              </td>
              <td style="text-align:center;">
                <div class="kebab" data-kebab>
                  <button class="btn btn--ghost" type="button" aria-haspopup="menu" aria-label="Acțiuni" onclick="TasksUI.toggleMenu(event, <?= $tid ?>)">⋯</button>
                  <div class="kebab-menu" id="kebab-<?= $tid ?>" role="menu" aria-hidden="true">
                    <button type="button" class="kebab-item" role="menuitem" onclick="TasksUI.openDetails(<?= $tid ?>)">Deschide</button>
                    <form method="POST" action="<?= BASE_URL ?>tasks-done" style="margin:0;">
                      <input type="hidden" name="id" value="<?= $tid ?>">
                      <button class="kebab-item" type="submit" role="menuitem">Marchează ca rezolvat</button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>tasks-delete" style="margin:0;" onsubmit="return confirm('Ștergi task-ul?');">
                      <input type="hidden" name="id" value="<?= $tid ?>">
                      <button class="kebab-item kebab-item--danger" type="submit" role="menuitem">Șterge</button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Mobile cards -->
      <div class="rtable-cards" aria-label="Lista task-uri">
        <?php foreach ($tasks_pending as $t): ?>
          <?php $tid = (int)$t['id']; $p = (int)($t['priority'] ?? 3); ?>
          <div class="data-card">
            <div class="data-card__top">
              <div>
                <p class="data-card__title" style="margin:0;">
                  <button type="button" class="task-title" onclick="TasksUI.openDetails(<?= $tid ?>)">
                    <?= htmlspecialchars($t['title'] ?? '') ?>
                  </button>
                </p>
                <div class="data-card__meta">
                  <div><span class="badge <?= prio_class($p) ?>"><?= prio_label($p) ?></span></div>
                  <?php if (!empty($t['description'])): ?>
                    <div><?= htmlspecialchars(preview_text((string)$t['description'], 120)) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="data-card__actions" style="justify-content:space-between;">
              <form method="POST" action="<?= BASE_URL ?>tasks-done" style="margin:0;">
                <input type="hidden" name="id" value="<?= $tid ?>">
                <button class="btn" type="submit">✅ Rezolvat</button>
              </form>
              <button class="btn btn--ghost" type="button" onclick="TasksUI.toggleMenu(event, <?= $tid ?>)">⋯</button>
              <div class="kebab-menu" id="kebab-<?= $tid ?>" role="menu" aria-hidden="true">
                <button type="button" class="kebab-item" role="menuitem" onclick="TasksUI.openDetails(<?= $tid ?>)">Deschide</button>
                <form method="POST" action="<?= BASE_URL ?>tasks-delete" style="margin:0;" onsubmit="return confirm('Ștergi task-ul?');">
                  <input type="hidden" name="id" value="<?= $tid ?>">
                  <button class="kebab-item kebab-item--danger" type="submit" role="menuitem">Șterge</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  <?php else: ?>

    <?php if (empty($tasks_done)): ?>
      <div style="padding:16px;">Nu există task-uri rezolvate.</div>
    <?php else: ?>
      <div class="table-wrap rtable">
        <table class="table tasks-table" style="min-width:760px;">
          <thead>
          <tr>
            <th style="width:54px;">Undo</th>
            <th>Task</th>
            <th style="width:180px;">Prioritate</th>
            <th style="width:120px; text-align:center;">Acțiuni</th>
          </tr>
          </thead>
          <tbody>
          <?php foreach ($tasks_done as $t): ?>
            <?php $tid = (int)$t['id']; $p = (int)($t['priority'] ?? 3); ?>
            <tr class="task-row task-row--done" data-task-id="<?= $tid ?>">
              <td>
                <form method="POST" action="<?= BASE_URL ?>tasks-done" style="display:inline;">
                  <input type="hidden" name="id" value="<?= $tid ?>">
                  <button class="btn btn--ghost" type="submit" title="Mută înapoi" aria-label="Mută înapoi">↩</button>
                </form>
              </td>
              <td>
                <button type="button" class="task-title" onclick="TasksUI.openDetails(<?= $tid ?>)">
                  <?= htmlspecialchars($t['title'] ?? '') ?>
                </button>
                <?php if (!empty($t['description'])): ?>
                  <div class="task-subtext"><?= htmlspecialchars(preview_text((string)$t['description'], 160)) ?></div>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= prio_class($p) ?>"><?= prio_label($p) ?></span>
              </td>
              <td style="text-align:center;">
                <div class="kebab" data-kebab>
                  <button class="btn btn--ghost" type="button" aria-haspopup="menu" aria-label="Acțiuni" onclick="TasksUI.toggleMenu(event, <?= $tid ?>)">⋯</button>
                  <div class="kebab-menu" id="kebab-<?= $tid ?>" role="menu" aria-hidden="true">
                    <button type="button" class="kebab-item" role="menuitem" onclick="TasksUI.openDetails(<?= $tid ?>)">Deschide</button>
                    <form method="POST" action="<?= BASE_URL ?>tasks-done" style="margin:0;">
                      <input type="hidden" name="id" value="<?= $tid ?>">
                      <button class="kebab-item" type="submit" role="menuitem">Mută înapoi la De făcut</button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>tasks-delete" style="margin:0;" onsubmit="return confirm('Ștergi task-ul?');">
                      <input type="hidden" name="id" value="<?= $tid ?>">
                      <button class="kebab-item kebab-item--danger" type="submit" role="menuitem">Șterge</button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Mobile cards -->
      <div class="rtable-cards" aria-label="Task-uri rezolvate">
        <?php foreach ($tasks_done as $t): ?>
          <?php $tid = (int)$t['id']; $p = (int)($t['priority'] ?? 3); ?>
          <div class="data-card">
            <div class="data-card__top">
              <div>
                <p class="data-card__title" style="margin:0;">
                  <button type="button" class="task-title" onclick="TasksUI.openDetails(<?= $tid ?>)">
                    <?= htmlspecialchars($t['title'] ?? '') ?>
                  </button>
                </p>
                <div class="data-card__meta">
                  <div><span class="badge <?= prio_class($p) ?>"><?= prio_label($p) ?></span></div>
                  <?php if (!empty($t['description'])): ?>
                    <div><?= htmlspecialchars(preview_text((string)$t['description'], 120)) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="data-card__actions" style="justify-content:space-between;">
              <form method="POST" action="<?= BASE_URL ?>tasks-done" style="margin:0;">
                <input type="hidden" name="id" value="<?= $tid ?>">
                <button class="btn" type="submit">↩ Înapoi</button>
              </form>
              <button class="btn btn--ghost" type="button" onclick="TasksUI.toggleMenu(event, <?= $tid ?>)">⋯</button>
              <div class="kebab-menu" id="kebab-<?= $tid ?>" role="menu" aria-hidden="true">
                <button type="button" class="kebab-item" role="menuitem" onclick="TasksUI.openDetails(<?= $tid ?>)">Deschide</button>
                <form method="POST" action="<?= BASE_URL ?>tasks-delete" style="margin:0;" onsubmit="return confirm('Ștergi task-ul?');">
                  <input type="hidden" name="id" value="<?= $tid ?>">
                  <button class="kebab-item kebab-item--danger" type="submit" role="menuitem">Șterge</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- Create Task Modal -->
<div class="attachments-modal is-hidden" id="taskCreateModal" aria-hidden="true">
  <div class="attachments-modal__overlay" onclick="TasksUI.closeCreateModal()"></div>
  <div class="attachments-modal__panel" style="max-width:760px;">
    <div class="attachments-modal__head">
      <div class="attachments-modal__title">➕ Task nou</div>
      <button class="attachments-modal__close" type="button" onclick="TasksUI.closeCreateModal()">Închide</button>
    </div>
    <div class="attachments-modal__body">
      <form method="POST" action="<?= BASE_URL ?>tasks" class="form-standard">
        <div class="form-row">
          <label class="label">Titlu scurt</label>
          <input class="input" name="title" placeholder="Ex: Verifică notificările pe mobil" required>
        </div>

        <div class="form-row">
          <label class="label">Descriere</label>
          <textarea class="input" name="description" rows="6" placeholder="Detalii task (pași, linkuri, cerințe)…"></textarea>
          <div class="helptext">Poți adăuga fișiere după ce creezi task-ul (în Detalii).</div>
        </div>

        <div class="form-row">
          <label class="label">Prioritate</label>
          <select class="input" name="priority" data-cselect>
            <option value="1">1 (Urgent)</option>
            <option value="2">2 (Ridicată)</option>
            <option value="3" selected>3 (Normal)</option>
            <option value="4">4 (Scăzută)</option>
            <option value="5">5 (Foarte scăzută)</option>
          </select>
        </div>

        <div class="form-actions">
          <button class="btn" type="submit">Creează</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Task Details Modal (autosave + attachments) -->
<div class="attachments-modal is-hidden" id="taskDetailsModal" aria-hidden="true">
  <div class="attachments-modal__overlay" onclick="TasksUI.closeDetails()"></div>
  <div class="attachments-modal__panel" style="max-width:980px;">
    <div class="attachments-modal__head">
      <div class="attachments-modal__title">
        🧩 Detalii task
        <span id="taskSaveState" class="tasks-save">&nbsp;</span>
      </div>
      <button class="attachments-modal__close" type="button" onclick="TasksUI.closeDetails()">Închide</button>
    </div>
    <div class="attachments-modal__body">
      <input type="hidden" id="taskId" value="0">

      <div class="tasks-details-grid">
        <div>
          <div class="form-row">
            <label class="label">Titlu</label>
            <input class="input" id="taskTitle" placeholder="Titlu" autocomplete="off">
          </div>

          <div class="form-row">
            <label class="label">Descriere</label>
            <textarea class="input" id="taskDescription" rows="10" placeholder="Descriere / pași / linkuri…"></textarea>
          </div>
        </div>

        <div>
          <div class="card" style="padding:14px; border-radius:16px; border:1px solid rgba(17,24,39,0.1);">
            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; justify-content:space-between;">
              <div>
                <div style="font-weight:800; margin-bottom:8px;">Setări</div>
                <div class="helptext">Modificările se salvează automat.</div>
              </div>
              <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <form method="POST" action="<?= BASE_URL ?>tasks-done" id="taskDoneForm" style="margin:0;">
                  <input type="hidden" name="id" id="taskDoneId" value="0">
                  <button class="btn" type="submit" id="taskDoneBtn">✅ Marchează ca rezolvat</button>
                </form>

                <form method="POST" action="<?= BASE_URL ?>tasks-delete" id="taskDeleteForm" style="margin:0;" onsubmit="return confirm('Ștergi task-ul?');">
                  <input type="hidden" name="id" id="taskDeleteId" value="0">
                  <button class="btn btn-danger" type="submit">🗑 Șterge</button>
                </form>
              </div>
            </div>

            <div class="form-row" style="margin-top:12px;">
              <label class="label">Prioritate</label>
              <select class="input" id="taskPriority" data-cselect>
                <option value="1">1 (Urgent)</option>
                <option value="2">2 (Ridicată)</option>
                <option value="3">3 (Normal)</option>
                <option value="4">4 (Scăzută)</option>
                <option value="5">5 (Foarte scăzută)</option>
              </select>
            </div>
          </div>

          <div class="card" style="margin-top:12px; padding:14px; border-radius:16px; border:1px solid rgba(17,24,39,0.1);">
            <div style="font-weight:800; margin-bottom:10px;">Atașamente</div>

            <div class="tasks-attach-bar">
              <input type="file" id="taskFile" class="input" style="padding:10px;" />
              <button type="button" class="btn" onclick="TasksUI.uploadAttachment()">📎 Încarcă</button>
            </div>
            <div class="helptext">Imagini (jpg/png/webp), PDF, Word, Excel, ZIP (max 20MB).</div>

            <div id="taskAttachments" style="margin-top:12px;"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  const BASE_URL = "<?= BASE_URL ?>";
</script>

<script>
  // Minimal, dependency-free UI logic
  const TasksUI = (() => {
    let saveTimer = null;
    let lastPayload = { title: null, description: null, priority: null };
    let menuOpenId = null;

    const el = (id) => document.getElementById(id);
    const saveState = () => el('taskSaveState');

    function setSave(text) {
      const s = saveState();
      if (!s) return;
      s.textContent = text || '';
    }

    function openCreateModal() {
      const m = el('taskCreateModal');
      if (!m) return;
      m.classList.remove('is-hidden');
      document.body.classList.add('has-modal');
    }
    function closeCreateModal() {
      const m = el('taskCreateModal');
      if (!m) return;
      m.classList.add('is-hidden');
      document.body.classList.remove('has-modal');
    }

    async function openDetails(id) {
      closeAnyMenu();
      const m = el('taskDetailsModal');
      if (!m) return;

      m.classList.remove('is-hidden');
      document.body.classList.add('has-modal');
      setSave('');

      el('taskId').value = String(id);
      el('taskDoneId').value = String(id);
      el('taskDeleteId').value = String(id);

      // Load
      const url = BASE_URL + 'tasks-view' + (BASE_URL.includes('?') ? '&' : '?') + 'id=' + encodeURIComponent(id);
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) {
        setSave('Eroare la încărcare');
        return;
      }
      const data = await res.json();
      if (!data || !data.ok) {
        setSave('Eroare la încărcare');
        return;
      }

      const task = data.task || {};
      el('taskTitle').value = task.title || '';
      el('taskDescription').value = task.description || '';
      el('taskPriority').value = String(task.priority || 3);

      // Done button label
      const doneBtn = el('taskDoneBtn');
      if (doneBtn) {
        doneBtn.textContent = (task.status === 'done') ? '↩ Mută la De făcut' : '✅ Marchează ca rezolvat';
      }

      lastPayload = {
        title: el('taskTitle').value,
        description: el('taskDescription').value,
        priority: el('taskPriority').value,
      };

      renderAttachments(data.attachments || []);
      wireAutosave();
    }

    function closeDetails() {
      const m = el('taskDetailsModal');
      if (!m) return;
      m.classList.add('is-hidden');
      document.body.classList.remove('has-modal');
      setSave('');
    }

    function wireAutosave() {
      const title = el('taskTitle');
      const desc = el('taskDescription');
      const prio = el('taskPriority');

      if (!title || !desc || !prio) return;

      title.oninput = () => scheduleSave('title', title.value);
      desc.oninput = () => scheduleSave('description', desc.value);
      prio.onchange = () => scheduleSave('priority', prio.value);

      // Save on blur too
      title.onblur = () => flushSave('title', title.value);
      desc.onblur = () => flushSave('description', desc.value);
    }

    function scheduleSave(field, value) {
      // Avoid spamming server with identical values
      if (lastPayload[field] === value) return;
      setSave('Se salvează…');
      if (saveTimer) clearTimeout(saveTimer);
      saveTimer = setTimeout(() => flushSave(field, value), 750);
    }

    async function flushSave(field, value) {
      if (lastPayload[field] === value) return;
      if (saveTimer) { clearTimeout(saveTimer); saveTimer = null; }

      const id = parseInt(el('taskId').value || '0', 10);
      if (!id) return;

      const fd = new FormData();
      fd.append('id', String(id));
      fd.append('field', field);
      fd.append('value', value);

      try {
        const res = await fetch(BASE_URL + 'tasks-autosave', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin'
        });
        const data = await res.json().catch(() => null);
        if (!res.ok || !data || !data.ok) {
          setSave('Eroare la salvare');
          return;
        }
        lastPayload[field] = value;
        setSave('Salvat ✓');
        // Also update list row title/subtext live
        updateRowPreview(id);
      } catch (e) {
        setSave('Eroare la salvare');
      }
    }

    function updateRowPreview(id) {
      const row = document.querySelector('[data-task-id="' + id + '"]');
      if (!row) return;
      const titleBtn = row.querySelector('.task-title');
      if (titleBtn) titleBtn.textContent = el('taskTitle').value;
      const sub = row.querySelector('.task-subtext');
      if (sub) {
        const text = (el('taskDescription').value || '').trim();
        sub.textContent = text ? (text.length > 160 ? text.slice(0, 160) + '…' : text) : '—';
      }
    }

    function formatBytes(bytes) {
      const b = Number(bytes || 0);
      if (!b) return '0 B';
      const units = ['B','KB','MB','GB'];
      let i = 0;
      let v = b;
      while (v >= 1024 && i < units.length - 1) { v /= 1024; i++; }
      return (Math.round(v * 10) / 10) + ' ' + units[i];
    }

    function renderAttachments(list) {
      const wrap = el('taskAttachments');
      if (!wrap) return;
      if (!list || !list.length) {
        wrap.innerHTML = '<div class="attachments-empty">Nu există atașamente.</div>';
        return;
      }

      const items = list.map(a => {
        const id = a.id;
        const name = a.original_name || 'fișier';
        const mime = a.mime_type || '';
        const isImg = mime.startsWith('image/');
        const href = BASE_URL + 'task-attachment' + (BASE_URL.includes('?') ? '&' : '?') + 'id=' + encodeURIComponent(id) + '&inline=1';
        const thumb = isImg
          ? '<a class="attachment-thumb__preview" href="' + href + '" target="_blank" rel="noopener"><img src="' + href + '" alt=""></a>'
          : '<a class="attachment-thumb__preview" href="' + href + '" target="_blank" rel="noopener"><div class="attachment-thumb__file">📄<div style="font-size:12px; text-align:center;">' + escapeHtml(name) + '</div></div></a>';

        return (
          '<div class="attachment-thumb">'
            + thumb +
            '<div class="attachment-thumb__meta">'
              + '<div class="attachment-thumb__name">' + escapeHtml(name) + '</div>'
              + '<div class="attachment-thumb__size">' + formatBytes(a.size_bytes) + '</div>'
              + '<div class="attachment-thumb__actions">'
                + '<a class="btn btn--ghost" style="padding:6px 10px;" href="' + (BASE_URL + 'task-attachment' + (BASE_URL.includes('?') ? '&' : '?') + 'id=' + encodeURIComponent(id) + '&download=1') + '">Download</a>'
                + '<button class="btn btn-danger" style="padding:6px 10px;" type="button" onclick="TasksUI.deleteAttachment(' + id + ')">Șterge</button>'
              + '</div>'
            + '</div>'
          + '</div>'
        );
      }).join('');

      wrap.innerHTML = '<div class="tasks-attachments-grid">' + items + '</div>';
    }

    function escapeHtml(s) {
      return String(s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    async function uploadAttachment() {
      const input = el('taskFile');
      const id = parseInt(el('taskId').value || '0', 10);
      if (!id) return;
      if (!input || !input.files || !input.files[0]) {
        setSave('Alege un fișier');
        return;
      }

      setSave('Se încarcă…');
      const fd = new FormData();
      fd.append('task_id', String(id));
      fd.append('file', input.files[0]);

      try {
        const res = await fetch(BASE_URL + 'task-attachment-upload', {
          method: 'POST',
          body: fd,
          credentials: 'same-origin'
        });
        const data = await res.json().catch(() => null);
        if (!res.ok || !data || !data.ok) {
          setSave('Eroare upload');
          return;
        }
        input.value = '';
        // Reload attachments via view
        await refreshAttachments(id);
        setSave('Salvat ✓');
      } catch (e) {
        setSave('Eroare upload');
      }
    }

    async function refreshAttachments(taskId) {
      const url = BASE_URL + 'tasks-view' + (BASE_URL.includes('?') ? '&' : '?') + 'id=' + encodeURIComponent(taskId);
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) return;
      const data = await res.json().catch(() => null);
      if (!data || !data.ok) return;
      renderAttachments(data.attachments || []);
    }

    async function deleteAttachment(attId) {
      if (!confirm('Ștergi atașamentul?')) return;
      const fd = new FormData();
      fd.append('id', String(attId));
      setSave('Se șterge…');
      const res = await fetch(BASE_URL + 'task-attachment-delete', {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });
      const data = await res.json().catch(() => null);
      if (!res.ok || !data || !data.ok) {
        setSave('Eroare la ștergere');
        return;
      }
      const taskId = parseInt(el('taskId').value || '0', 10);
      if (taskId) await refreshAttachments(taskId);
      setSave('Salvat ✓');
    }

    function toggleMenu(e, id) {
      e.preventDefault();
      e.stopPropagation();

      if (menuOpenId && menuOpenId !== id) closeAnyMenu();
      const menu = document.getElementById('kebab-' + id);
      if (!menu) return;

      const isOpen = menu.classList.contains('is-open');
      closeAnyMenu();
      if (!isOpen) {
        menu.classList.add('is-open');
        menu.setAttribute('aria-hidden', 'false');
        menuOpenId = id;
        // Position for mobile cards (inline)
        const rect = (e.currentTarget && e.currentTarget.getBoundingClientRect) ? e.currentTarget.getBoundingClientRect() : null;
        if (rect) {
          menu.style.minWidth = '200px';
        }
      }
    }

    function closeAnyMenu() {
      if (!menuOpenId) return;
      const menu = document.getElementById('kebab-' + menuOpenId);
      if (menu) {
        menu.classList.remove('is-open');
        menu.setAttribute('aria-hidden', 'true');
      }
      menuOpenId = null;
    }

    // Close menus on outside click / escape
    document.addEventListener('click', () => closeAnyMenu());
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { closeAnyMenu(); closeDetails(); closeCreateModal(); } });

    return {
      openCreateModal,
      closeCreateModal,
      openDetails,
      closeDetails,
      uploadAttachment,
      deleteAttachment,
      toggleMenu,
    };
  })();
</script>

<?php require __DIR__ . '/admin/_layout_end.php'; ?>
