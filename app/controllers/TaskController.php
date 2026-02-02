<?php

class TaskController
{
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

        $filter = $_GET['filter'] ?? 'all';
        $q = trim($_GET['q'] ?? '');
        $q_done = trim($_GET['q_done'] ?? '');

        /*
    |--------------------------------------------------------------------------
    | PENDING (De făcut)
    |--------------------------------------------------------------------------
    */
        $wherePending = ["status = 'pending'"];
        $paramsPending = [];

        if ($filter === 'favorite') {
            $wherePending[] = "is_favorite = 1";
        }

        if ($filter === 'urgent') {
            $wherePending[] = "priority IN (1,2)";
        }

        if ($q !== '') {
            $wherePending[] = "title LIKE ?";
            $paramsPending[] = "%{$q}%";
        }

        $sqlPending = "
        SELECT * FROM tasks
        WHERE " . implode(" AND ", $wherePending) . "
        ORDER BY is_favorite DESC, priority ASC, created_at DESC
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
            $whereDone[] = "title LIKE ?";
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

        require __DIR__ . '/../views/tasks.php';
    }

    public function store()
    {
        global $pdo;

        $title = trim($_POST['title'] ?? '');
        $priority = (int)($_POST['priority'] ?? 3);
        if ($priority < 1 || $priority > 5) $priority = 3;

        if ($title !== '') {
            $stmt = $pdo->prepare("INSERT INTO tasks (title, priority) VALUES (?, ?)");
            $stmt->execute([$title, $priority]);
        }

        header("Location: " . BASE_URL . "tasks");
        exit;
    }

    public function update()
    {
        global $pdo;

        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $priority = (int)($_POST['priority'] ?? 3);
        if ($priority < 1 || $priority > 5) $priority = 3;

        if ($id && $title !== '') {
            $stmt = $pdo->prepare("UPDATE tasks SET title = ?, priority = ? WHERE id = ?");
            $stmt->execute([$title, $priority, $id]);
        }

        header("Location: " . BASE_URL . "tasks");
        exit;
    }

    public function delete()
    {
        global $pdo;

        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$id]);
        }

        header("Location: " . BASE_URL . "tasks");
        exit;
    }

    public function toggleDone()
    {
        global $pdo;

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

    
}
