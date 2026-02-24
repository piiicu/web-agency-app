<?php

class TaskController
{
    private function ensureSchema(): void
    {
        global $pdo;

        // Add tasks.description (long text) if missing
        try {
            $pdo->query("SELECT description FROM tasks LIMIT 1");
        } catch (Throwable $e) {
            try {
                $pdo->exec("ALTER TABLE tasks ADD COLUMN description TEXT NULL AFTER title");
            } catch (Throwable $e2) {
                // ignore
            }
        }

        // Create task_attachments table if missing
        try {
            $pdo->query("SELECT id FROM task_attachments LIMIT 1");
        } catch (Throwable $e) {
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS task_attachments (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    task_id INT(11) NOT NULL,
                    original_name VARCHAR(255) NOT NULL,
                    stored_name VARCHAR(255) NOT NULL,
                    mime_type VARCHAR(100) DEFAULT NULL,
                    size_bytes INT(11) NOT NULL DEFAULT 0,
                    uploaded_by INT(11) DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY task_id (task_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            } catch (Throwable $e2) {
                // ignore
            }
        }
    }

    public function index()
    {
        global $pdo;

        // Badge baseline: when I open Tasks, mark them as seen (per-user)
        Auth::requireRole(['admin', 'employee', 'staff']);
        $me = (int)Auth::id();
        try {
            $pdo->query("SELECT last_seen_tasks_at FROM users LIMIT 1");
        } catch (Throwable $e) {
            try { $pdo->exec("ALTER TABLE users ADD COLUMN last_seen_tasks_at DATETIME NULL"); } catch (Throwable $e2) {}
        }
        try {
            $pdo->prepare("UPDATE users SET last_seen_tasks_at = NOW() WHERE id=? LIMIT 1")->execute([$me]);
        } catch (Throwable $e) {}

        $this->ensureSchema();

        $filter = $_GET['filter'] ?? 'all';
        $tab = $_GET['tab'] ?? 'pending';
        $q = trim($_GET['q'] ?? '');
        $q_done = trim($_GET['q_done'] ?? '');
        // Allow a single q param for both tabs (keep backwards compatible)
        if ($tab === 'done' && $q_done === '' && $q !== '') {
            $q_done = $q;
        }

        /*
    |--------------------------------------------------------------------------
    | PENDING (De făcut)
    |--------------------------------------------------------------------------
    */
        $wherePending = ["status = 'pending'"];
        $paramsPending = [];

        // "favorite" is deprecated (UI removed), but keep URL compatibility.
        if ($filter === 'favorite') {
            $wherePending[] = "is_favorite = 1";
        }

        if ($filter === 'urgent') {
            $wherePending[] = "priority IN (1,2)";
        }

        // Priority-specific filters: p1..p5
        if (preg_match('/^p([1-5])$/', (string)$filter, $m)) {
            $wherePending[] = "priority = " . (int)$m[1];
        }

        if ($q !== '') {
            $wherePending[] = "(title LIKE ? OR description LIKE ?)";
            $paramsPending[] = "%{$q}%";
            $paramsPending[] = "%{$q}%";
        }

        $sqlPending = "
        SELECT * FROM tasks
        WHERE " . implode(" AND ", $wherePending) . "
        ORDER BY priority ASC, created_at DESC
    ";

        $stmt = $pdo->prepare($sqlPending);
        $stmt->execute($paramsPending);
        $tasks_pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

        /*
    |--------------------------------------------------------------------------
    | DONE (Rezolvate)
    |--------------------------------------------------------------------------
    */
        $whereDone = ["status = 'done'"];
        $paramsDone = [];

        if ($q_done !== '') {
            $whereDone[] = "(title LIKE ? OR description LIKE ?)";
            $paramsDone[] = "%{$q_done}%";
            $paramsDone[] = "%{$q_done}%";
        }

        $sqlDone = "
        SELECT * FROM tasks
        WHERE " . implode(" AND ", $whereDone) . "
        ORDER BY created_at DESC
    ";

        $stmt = $pdo->prepare($sqlDone);
        $stmt->execute($paramsDone);
        $tasks_done = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count_pending = count($tasks_pending);
        $count_done = count($tasks_done);

        require __DIR__ . '/../views/tasks.php';
    }

    public function store()
    {
        global $pdo;

        $this->ensureSchema();

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = (int)($_POST['priority'] ?? 3);
        if ($priority < 1 || $priority > 5) $priority = 3;

        if ($title !== '') {
            $stmt = $pdo->prepare("INSERT INTO tasks (title, description, priority) VALUES (?, ?, ?)");
            $stmt->execute([$title, $description !== '' ? $description : null, $priority]);
        }

        header("Location: " . BASE_URL . "tasks");
        exit;
    }

    public function update()
    {
        global $pdo;

        $this->ensureSchema();

        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $priority = (int)($_POST['priority'] ?? 3);
        if ($priority < 1 || $priority > 5) $priority = 3;

        if ($id && $title !== '') {
            $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, priority = ? WHERE id = ?");
            $stmt->execute([$title, $description !== '' ? $description : null, $priority, $id]);
        }

        header("Location: " . BASE_URL . "tasks");
        exit;
    }

    public function delete()
    {
        global $pdo;

        $this->ensureSchema();

        $id = $_POST['id'] ?? null;
        if ($id) {
            // delete attachments (db + files)
            try {
                $stmtA = $pdo->prepare("SELECT id, stored_name FROM task_attachments WHERE task_id = ?");
                $stmtA->execute([$id]);
                $atts = $stmtA->fetchAll(PDO::FETCH_ASSOC);
                $baseDir = dirname(__DIR__, 2) . '/uploads/tasks/';
                foreach ($atts as $a) {
                    $p = $baseDir . ($a['stored_name'] ?? '');
                    if (is_string($a['stored_name']) && $a['stored_name'] !== '' && is_file($p)) {
                        @unlink($p);
                    }
                }
                $pdo->prepare("DELETE FROM task_attachments WHERE task_id = ?")->execute([$id]);
            } catch (Throwable $e) {}

            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$id]);
        }

        header("Location: " . BASE_URL . "tasks");
        exit;
    }

    public function toggleDone()
    {
        global $pdo;

        $this->ensureSchema();

        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE tasks
                SET status = IF(status='pending','done','pending')
                WHERE id = ?
            ");
            $stmt->execute([$id]);
        }

        header("Location: " . BASE_URL . "tasks");
        exit;
    }

    public function toggleFavorite()
    {
        global $pdo;

        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("
                UPDATE tasks
                SET is_favorite = IF(is_favorite=1,0,1)
                WHERE id = ?
            ");
            $stmt->execute([$id]);
        }

        header("Location: " . BASE_URL . "tasks");
        exit;
    }

    public function view(): void
    {
        global $pdo;
        Auth::requireRole(['admin', 'employee', 'staff']);
        $this->ensureSchema();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'bad_request']);
            return;
        }

        $stmt = $pdo->prepare("SELECT id, title, description, status, priority, created_at FROM tasks WHERE id=? LIMIT 1");
        $stmt->execute([$id]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$task) {
            http_response_code(404);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            return;
        }

        $stmtA = $pdo->prepare("SELECT id, original_name, stored_name, mime_type, size_bytes, created_at FROM task_attachments WHERE task_id=? ORDER BY created_at DESC");
        $stmtA->execute([$id]);
        $attachments = $stmtA->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => true,
            'task' => $task,
            'attachments' => $attachments,
        ]);
    }

    public function autosave(): void
    {
        global $pdo;
        Auth::requireRole(['admin', 'employee', 'staff']);
        $this->ensureSchema();

        $id = (int)($_POST['id'] ?? 0);
        $field = (string)($_POST['field'] ?? '');
        $value = (string)($_POST['value'] ?? '');

        if ($id <= 0 || $field === '') {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'bad_request']);
            return;
        }

        $allowed = ['title', 'description', 'priority'];
        if (!in_array($field, $allowed, true)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'bad_field']);
            return;
        }

        try {
            if ($field === 'priority') {
                $p = (int)$value;
                if ($p < 1 || $p > 5) $p = 3;
                $pdo->prepare("UPDATE tasks SET priority=? WHERE id=?")->execute([$p, $id]);
            } elseif ($field === 'title') {
                $t = trim($value);
                if ($t === '') {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['ok' => false, 'error' => 'empty_title']);
                    return;
                }
                $pdo->prepare("UPDATE tasks SET title=? WHERE id=?")->execute([$t, $id]);
            } else {
                // description
                $d = trim($value);
                $pdo->prepare("UPDATE tasks SET description=? WHERE id=?")->execute([$d !== '' ? $d : null, $id]);
            }
        } catch (Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'server_error']);
            return;
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true]);
    }

    
}
