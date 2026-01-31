<?php

class ChatController
{
    public function index()
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);
        if ($currentUserId <= 0) {
            // fallback simplu
            header("Location: " . BASE_URL . "login");
            exit;
        }

        // mark last seen (badges)
        $stmt = $pdo->query("SELECT MAX(id) AS m FROM messages");
        $_SESSION['chat_last_seen_id'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['m'] ?? 0);

        // Read receipts: when opening chat, mark other users' messages as delivered + read
        if ($this->receiptsEnabled()) {
            $pdo->prepare("
            UPDATE messages
            SET delivered_at = COALESCE(delivered_at, NOW())
            WHERE user_id <> ? AND delivered_at IS NULL
        ")->execute([$currentUserId]);

            $pdo->prepare("
            UPDATE messages
            SET read_at = COALESCE(read_at, NOW())
            WHERE user_id <> ? AND read_at IS NULL
        ")->execute([$currentUserId]);
        }

        // load messages
        if ($this->receiptsEnabled()) {
            $messages = $pdo->query("
            SELECT m.id, m.user_id, m.message, m.created_at, m.delivered_at, m.read_at, u.name
            FROM messages m
            JOIN users u ON u.id = m.user_id
            ORDER BY m.id ASC
            LIMIT 200
        ")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $messages = $pdo->query("
            SELECT m.id, m.user_id, m.message, m.created_at, u.name
            FROM messages m
            JOIN users u ON u.id = m.user_id
            ORDER BY m.id ASC
            LIMIT 200
        ")->fetchAll(PDO::FETCH_ASSOC);
        }

        $messages = $this->attachAttachments($messages);

        foreach ($messages as &$m) {
            $m['is_me'] = ((int)$m['user_id'] === $currentUserId);
        }
        unset($m);

        require __DIR__ . '/../views/chat.php';
    }


    /* =========================
       SEND MESSAGE + FILES
       ========================= */
    public function store()
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $userId = (int)($_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        $msg = trim((string)($_POST['message'] ?? ''));

        $hasFiles = isset($_FILES['files']) && !empty($_FILES['files']['name']);

        if ($msg === '' && !$hasFiles) {
            $this->json(['ok' => true]);
        }

        // insert message
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
        $stmt->execute([$userId, $msg]);
        $messageId = (int)$pdo->lastInsertId();

        if ($hasFiles) {
            $this->handleChatAttachments($messageId, $userId);
        }

        $this->json(['ok' => true, 'id' => $messageId]);
    }

    /* =========================
       POLL
       ========================= */
    public function poll()
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $since = (int)($_GET['since'] ?? 0);

        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);

        if ($this->receiptsEnabled()) {
            // When user is on chat, newly received messages are delivered + read
            $pdo->prepare("UPDATE messages SET delivered_at = COALESCE(delivered_at, NOW())
                   WHERE id > ? AND user_id <> ? AND delivered_at IS NULL")
                ->execute([$since, $currentUserId]);

            // $pdo->prepare("UPDATE messages SET read_at = COALESCE(read_at, NOW())
            //        WHERE id > ? AND user_id <> ? AND read_at IS NULL")
            //     ->execute([$since, $currentUserId]);

            $stmt = $pdo->prepare("
        SELECT m.id, m.user_id, m.message, m.created_at, m.delivered_at, m.read_at, u.name
        FROM messages m
        JOIN users u ON u.id = m.user_id
        WHERE m.id > ?
        ORDER BY m.id ASC
    ");
        } else {
            $stmt = $pdo->prepare("
        SELECT m.id, m.user_id, m.message, m.created_at, u.name
        FROM messages m
        JOIN users u ON u.id = m.user_id
        WHERE m.id > ?
        ORDER BY m.id ASC
    ");
        }
        $stmt->execute([$since]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rows = $this->attachAttachments($rows);
        foreach ($rows as &$m) {
            $m['is_me'] = ((int)$m['user_id'] === $currentUserId);
        }
        unset($m);

        if ($this->receiptsEnabled()) {
            // status updates for my messages (so ✓✓ updates without new messages)
            $from = max(0, $since - 300);
            $st = $pdo->prepare("SELECT id, delivered_at, read_at FROM messages WHERE id > ? AND user_id = ? ORDER BY id ASC");
            $st->execute([$from, $currentUserId]);
            $statuses = $st->fetchAll(PDO::FETCH_ASSOC);

            $this->json(['messages' => $rows, 'statuses' => $statuses]);
        } else {
            $this->json(['messages' => $rows]);
        }
    }



    /* =========================
   READ RECEIPTS SUPPORT
   ========================= */
    private function receiptsEnabled(): bool
    {
        static $enabled = null;
        if ($enabled !== null) return $enabled;

        global $pdo;
        try {
            // Will fail if columns don't exist yet
            $pdo->query("SELECT delivered_at, read_at FROM messages LIMIT 1");
            $enabled = true;
        } catch (\Throwable $e) {
            $enabled = false;
        }
        return $enabled;
    }

    /* =========================
       ATTACHMENTS MAP
       ========================= */
    private function attachAttachments(array $messages): array
    {
        global $pdo;

        if (empty($messages)) return $messages;

        $ids = array_unique(array_map(fn($m) => (int)$m['id'], $messages));
        $ids = array_filter($ids);
        if (!$ids) return $messages;

        $in = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $pdo->prepare("
            SELECT id, message_id, original_name, mime_type, size_bytes
            FROM chat_attachments
            WHERE message_id IN ($in)
            ORDER BY id ASC
        ");
        $stmt->execute($ids);
        $atts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $byMsg = [];
        foreach ($atts as $a) {
            $mid = (int)$a['message_id'];
            $byMsg[$mid][] = [
                'id' => (int)$a['id'],
                'name' => $a['original_name'],
                'mime' => $a['mime_type'],
                'url'  => BASE_URL . "chat-attachment&id={$a['id']}&inline=1",
                'download_url' => BASE_URL . "chat-attachment&id={$a['id']}&download=1",
            ];
        }

        foreach ($messages as &$m) {
            $m['attachments'] = $byMsg[(int)$m['id']] ?? [];
        }
        unset($m);

        return $messages;
    }

    /* =========================
       FILE UPLOAD
       ========================= */
    private function handleChatAttachments(int $messageId, int $userId): void
    {
        global $pdo;

        $uploadDir = dirname(__DIR__, 2) . '/uploads/chat/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $files = $_FILES['files'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        $allowedMime = [
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf'
        ];

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $tmp = $files['tmp_name'][$i];
            if (!is_uploaded_file($tmp)) continue;

            $mime = $finfo->file($tmp);
            if (!in_array($mime, $allowedMime, true)) continue;

            if ($files['size'][$i] > 8 * 1024 * 1024) continue; // 8MB

            $original = $files['name'][$i];
            $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original);
            $stored = bin2hex(random_bytes(16)) . '_' . $safe;

            if (!move_uploaded_file($tmp, $uploadDir . $stored)) continue;

            $stmt = $pdo->prepare("
                INSERT INTO chat_attachments
                  (message_id, uploaded_by, original_name, stored_name, mime_type, size_bytes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $messageId,
                $userId,
                $original,
                $stored,
                $mime,
                (int)$files['size'][$i]
            ]);
        }
    }

    /* =========================
       JSON HELPER
       ========================= */
    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    public function markRead()
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);

        if (!$this->receiptsEnabled()) {
            $this->json(['ok' => true]);
        }

        // marchează drept citite toate mesajele altora (în chat intern)
        $pdo->prepare("
        UPDATE messages
        SET read_at = COALESCE(read_at, NOW())
        WHERE user_id <> ? AND read_at IS NULL
    ")->execute([$currentUserId]);

        $this->json(['ok' => true]);
    }
}
