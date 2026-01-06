<?php

class ChatController
{
    public function index()
    {
        global $pdo;

        // 1) Get last message id and mark as seen (for badge)
        $stmt = $pdo->query("SELECT MAX(id) AS m FROM messages");
        $maxId = (int)($stmt->fetch(PDO::FETCH_ASSOC)['m'] ?? 0);
        $_SESSION['chat_last_seen_id'] = $maxId;

        // 2) Load latest messages
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
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
            exit;
        }

        $msg = trim((string)($_POST['message'] ?? ''));
        if ($msg !== '') {
            $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
            $stmt->execute([$userId, $msg]);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => true]);
        exit;
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

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['messages' => $rows]);
        exit;
    }
}
