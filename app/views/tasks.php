<?php
$activeFilter = $_GET['filter'] ?? 'all';
$q       = $_GET['q'] ?? '';        // search pentru De făcut
$q_done  = $_GET['q_done'] ?? '';   // search pentru Rezolvate
$tab     = $_GET['tab'] ?? 'pending'; // pending | done

// Helper: construiește URL la /tasks cu parametrii doriți (fără erori)
function tasks_url($params = []) {
    $base = BASE_URL . 'tasks';
    if (!$params) return $base;
    return $base . '?' . http_build_query($params);
}
?> 

<h2>Task-uri</h2>

<div style="margin-bottom:15px;">
    <a href="<?= BASE_URL ?>admin/dashboard">← Dashboard</a>
</div>

<!-- Formular adăugare task -->
<form method="POST" action="<?= BASE_URL ?>tasks" style="margin-bottom:15px;">
    <input type="text" name="title" placeholder="Task nou" required>

    <select name="priority">
        <option value="1">1 (Urgent)</option>
        <option value="2">2</option>
        <option value="3" selected>3 (Normal)</option>
        <option value="4">4</option>
        <option value="5">5 (Low)</option>
    </select>

    <button type="submit">➕ Adaugă</button>
</form>

<hr>

<!-- Filtre (doar pentru De făcut) -->
<div style="margin-bottom:15px;">
    <strong>Filtre:</strong>

    <?php
    // Păstrăm q (search De făcut) + tab curent când schimbăm filtrele
    $common = [
        'tab' => $tab,
        'q'   => $q,
        // NU păstrăm q_done în filtre, ca să nu încurcăm done tab
    ];
    ?>

    <a href="<?= tasks_url(array_filter($common)) ?>"
       style="<?= $activeFilter === 'all' ? 'font-weight:bold;' : '' ?>">
        🔁 Toate
    </a> |

    <a href="<?= tasks_url(array_filter($common + ['filter' => 'favorite'])) ?>"
       style="<?= $activeFilter === 'favorite' ? 'font-weight:bold;' : '' ?>">
        ⭐ Favorite
    </a> |

    <a href="<?= tasks_url(array_filter($common + ['filter' => 'urgent'])) ?>"
       style="<?= $activeFilter === 'urgent' ? 'font-weight:bold;' : '' ?>">
        🔥 Urgente
    </a>
</div>

<!-- FORMULAR GENERAL (caută DOAR în De făcut) -->
<!-- Se afișează doar când ești în tabul "De făcut" -->
<?php if ($tab === 'pending'): ?>
<form method="GET" action="<?= BASE_URL ?>tasks" style="margin-bottom:15px;">
    <input type="hidden" name="tab" value="pending">
    <input type="hidden" name="filter" value="<?= htmlspecialchars($activeFilter) ?>">

    <input type="text" name="q" placeholder="Caută în De făcut..."
           value="<?= htmlspecialchars($q) ?>">
    <button type="submit">🔎 Caută</button>

    <?php if (trim($q) !== ''): ?>
        <a href="<?= tasks_url(['tab'=>'pending','filter'=>$activeFilter]) ?>">✖ Reset</a>
    <?php endif; ?>
</form>
<?php endif; ?>

<!-- Tabs -->
<div style="margin: 10px 0;">
    <a href="<?= tasks_url(array_filter(['tab'=>'pending','filter'=>$activeFilter,'q'=>$q])) ?>"
       style="margin-right:10px; <?= $tab==='pending' ? 'font-weight:bold;' : '' ?>">
        📝 De făcut
    </a>

    <a href="<?= tasks_url(array_filter(['tab'=>'done','filter'=>$activeFilter,'q_done'=>$q_done])) ?>"
       style="<?= $tab==='done' ? 'font-weight:bold;' : '' ?>">
        ✅ Rezolvate
    </a>
</div>


<!-- PENDING -->
<div id="tab-pending" style="<?= $tab==='pending' ? '' : 'display:none;' ?>">
    <h3>De făcut</h3>

    <?php if (empty($tasks_pending)): ?>
        <p>Nu există task-uri active.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($tasks_pending as $task): ?>
                <li style="margin-bottom:10px;">
                    <!-- Favorite -->
                    <form method="POST" action="<?= BASE_URL ?>tasks-favorite" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <button type="submit" title="Favorit">
                            <?= $task['is_favorite'] ? '⭐' : '☆' ?>
                        </button>
                    </form>

                    <!-- Done toggle -->
                    <form method="POST" action="<?= BASE_URL ?>tasks-done" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <button type="submit" title="Marchează ca rezolvat">✅</button>
                    </form>

                    <!-- Edit (title + priority) -->
                    <form method="POST" action="<?= BASE_URL ?>tasks-update" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <input type="text" name="title" value="<?= htmlspecialchars($task['title']) ?>" required>

                        <select name="priority">
                            <?php for ($p = 1; $p <= 5; $p++): ?>
                                <option value="<?= $p ?>" <?= ((int)$task['priority'] === $p) ? 'selected' : '' ?>>
                                    <?= $p ?>
                                </option>
                            <?php endfor; ?>
                        </select>

                        <button type="submit">💾</button>
                    </form>

                    <!-- Delete -->
                    <form method="POST" action="<?= BASE_URL ?>tasks-delete"
                          style="display:inline;"
                          onsubmit="return confirm('Ștergi task-ul?');">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <button type="submit">🗑</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- DONE -->
<div id="tab-done" style="<?= $tab==='done' ? '' : 'display:none;' ?>">
    <h3>Rezolvate</h3>

    <!-- Formular căutare DOAR în Rezolvate (doar aici apare) -->
    <form method="GET" action="<?= BASE_URL ?>tasks" style="margin-bottom:10px;">
        <input type="hidden" name="tab" value="done">

        <input type="text" name="q_done" placeholder="Caută în Rezolvate..."
               value="<?= htmlspecialchars($q_done) ?>">
        <button type="submit">🔎</button>

        <?php if (trim($q_done) !== ''): ?>
            <a href="<?= tasks_url(['tab'=>'done']) ?>">✖ Reset</a>
        <?php endif; ?>
    </form>

    <?php if (empty($tasks_done)): ?>
        <p>Nu există task-uri rezolvate.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($tasks_done as $task): ?>
                <li style="margin-bottom:10px;">
                    <!-- Undo done -->
                    <form method="POST" action="<?= BASE_URL ?>tasks-done" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <button type="submit" title="Mută înapoi la De făcut">↩</button>
                    </form>

                    <span style="text-decoration:line-through;">
                        <?= htmlspecialchars($task['title']) ?>
                    </span>

                    <!-- Delete -->
                    <form method="POST" action="<?= BASE_URL ?>tasks-delete"
                          style="display:inline;"
                          onsubmit="return confirm('Ștergi task-ul?');">
                        <input type="hidden" name="id" value="<?= $task['id'] ?>">
                        <button type="submit">🗑</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script>const BASE_URL = "<?= BASE_URL ?>";</script>
<script src="<?= BASE_URL ?>public/assets/js/app.js"></script>


<script>
/*
  Păstrăm funcția pentru compatibilitate, dar acum tab-ul este controlat de URL (?tab=...)
  Dacă vrei încă tab switching fără reload, îți pot adapta și fără parametri.
*/
function showTab(which) {
    // optional: dacă vrei fără reload, putem implementa.
}
</script>
