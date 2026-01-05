<?php

class ChatController
{
    public function index()
    {
        global $pdo;

        $messages = $pdo->query("
            SELECT m.id, m.message, m.created_at, u.name
            FROM messages m
            JOIN users u ON u.id = m.user_id
            ORDER BY m.id ASC
            LIMIT 200
        ")->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/chat.php';
    }

    public function store()
    {
        global $pdo;

        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(401);
            exit('Unauthorized');
        }

        $msg = trim($_POST['message'] ?? '');
        if ($msg !== '') {
            $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
            $stmt->execute([$userId, $msg]);
        }

        // răspuns JSON (ca să nu dai reload)
        header('Content-Type: application/json');
        echo json_encode(['ok' => true]);
    }

    // GET: chat-poll&since=ID
    public function poll()
    {
        global $pdo;

        $since = (int)($_GET['since'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT m.id, m.message, m.created_at, u.name
            FROM messages m
            JOIN users u ON u.id = m.user_id
            WHERE m.id > ?
            ORDER BY m.id ASC
        ");
        $stmt->execute([$since]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode(['messages' => $rows]);
    }
}
