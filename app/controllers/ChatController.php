<?php

class ChatController
{
    /* =========================
       CHAT HOME (LIST + ACTIVE)
       ========================= */
    public function index(): void
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $meId = (int)($_SESSION['user']['id'] ?? 0);
        if ($meId <= 0) {
            header("Location: " . BASE_URL . "login");
            exit;
        }

        // Ensure schema exists (tables created via SQL). If not, fallback to old global chat.
        if (!$this->chatV2Enabled()) {
            $this->legacyIndex();
            return;
        }

        // Ensure a "General" conversation exists and I'm a participant.
        $generalId = $this->ensureGeneralConversation($meId);

        $cid = (int)($_GET['cid'] ?? 0);
        if ($cid <= 0) $cid = $generalId;

        // Ensure I can access that conversation
        if (!$this->isParticipant($cid, $meId)) {
            $cid = $generalId;
        } else {
            // If I previously hid this chat, opening it should unhide it (WhatsApp-like)
            $this->unhideIfNeeded($cid, $meId);
        }

        // Conversations sidebar (exclude hidden / left / deleted)
        $conversations = $this->getMyConversations($meId);
        $activeConversation = $this->getConversation($cid);

        // Users list for creating DM / group (internal users only)
        $users = $pdo->query("SELECT id, name, role FROM users WHERE role IN ('admin','employee','staff') AND is_active = 1 ORDER BY role DESC, name ASC")
            ->fetchAll(PDO::FETCH_ASSOC);

        // Mark delivered when I load/poll this conversation (like WhatsApp: delivered when received)
        $this->touchDelivered($cid, $meId);

        // Messages
        $messages = $this->getMessages($cid, 0);
        $messages = $this->attachAttachments($messages);

        foreach ($messages as &$m) {
            $m['is_me'] = ((int)$m['user_id'] === $meId);
        }
        unset($m);

        // For top header title
        $pageTitle = 'Chat intern';

        require __DIR__ . '/../views/chat.php';
    }

    /* =========================
       WHATSAPP-LIKE ACTIONS
       - hide: only for me (remove from list)
       - leave: only for me (exit group/dm)
       - delete: for everyone (creator only; group/dm), general cannot be deleted
       ========================= */
    public function hide(): void
    {
        global $pdo;
        Auth::requireRole(['admin', 'employee', 'staff']);

        $meId = (int)($_SESSION['user']['id'] ?? 0);
        $cid = (int)($_POST['cid'] ?? 0);
        if (!$this->chatV2Enabled()) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }
        if ($cid <= 0 || !$this->isParticipant($cid, $meId)) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        // Can't hide General (keep always visible)
        $type = (string)$pdo->prepare("SELECT type FROM conversations WHERE id = ? LIMIT 1")->execute([$cid]);
        // (avoid extra query errors - use safe select)
        $st = $pdo->prepare("SELECT type FROM conversations WHERE id = ? LIMIT 1");
        $st->execute([$cid]);
        $t = (string)($st->fetchColumn() ?: '');
        if ($t === 'general') {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        $pdo->prepare("UPDATE conversation_participants SET hidden_at = NOW() WHERE conversation_id = ? AND user_id = ?")
            ->execute([$cid, $meId]);

        header('Location: ' . BASE_URL . 'chat');
        exit;
    }

    public function leave(): void
    {
        global $pdo;
        Auth::requireRole(['admin', 'employee', 'staff']);

        $meId = (int)($_SESSION['user']['id'] ?? 0);
        $cid = (int)($_POST['cid'] ?? 0);
        if (!$this->chatV2Enabled()) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }
        if ($cid <= 0 || !$this->isParticipant($cid, $meId)) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        $st = $pdo->prepare("SELECT type FROM conversations WHERE id = ? LIMIT 1");
        $st->execute([$cid]);
        $t = (string)($st->fetchColumn() ?: '');
        if ($t === 'general') {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        $pdo->prepare("UPDATE conversation_participants SET left_at = NOW(), hidden_at = NOW() WHERE conversation_id = ? AND user_id = ?")
            ->execute([$cid, $meId]);

        header('Location: ' . BASE_URL . 'chat');
        exit;
    }

    public function delete(): void
    {
        global $pdo;
        Auth::requireRole(['admin', 'employee', 'staff']);

        $meId = (int)($_SESSION['user']['id'] ?? 0);
        $cid = (int)($_POST['cid'] ?? 0);
        if (!$this->chatV2Enabled()) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }
        if ($cid <= 0 || !$this->isParticipant($cid, $meId)) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        $st = $pdo->prepare("SELECT type, created_by, deleted_at FROM conversations WHERE id = ? LIMIT 1");
        $st->execute([$cid]);
        $c = $st->fetch(PDO::FETCH_ASSOC) ?: [];

        $type = (string)($c['type'] ?? '');
        $createdBy = (int)($c['created_by'] ?? 0);
        $deletedAt = $c['deleted_at'] ?? null;

        if ($type === 'general' || $deletedAt) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        // Only the creator can delete for everyone.
        if ($createdBy !== $meId) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        $pdo->prepare("UPDATE conversations SET deleted_at = NOW(), deleted_by = ? WHERE id = ?")
            ->execute([$meId, $cid]);

        // Hide for all participants (keeps rows for auditing)
        $pdo->prepare("UPDATE conversation_participants SET hidden_at = COALESCE(hidden_at, NOW()), left_at = COALESCE(left_at, NOW()) WHERE conversation_id = ?")
            ->execute([$cid]);

        header('Location: ' . BASE_URL . 'chat');
        exit;
    }

    /* =========================
       CREATE / OPEN DM
       ========================= */
    public function dm(): void
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);
        $meId = (int)($_SESSION['user']['id'] ?? 0);
        $otherId = (int)($_POST['user_id'] ?? 0);

        if (!$this->chatV2Enabled()) {
            header("Location: " . BASE_URL . "chat");
            exit;
        }

        if ($meId <= 0 || $otherId <= 0 || $otherId === $meId) {
            header("Location: " . BASE_URL . "chat");
            exit;
        }

        // Only internal users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role IN ('admin','employee','staff') AND is_active = 1");
        $stmt->execute([$otherId]);
        if (!$stmt->fetchColumn()) {
            header("Location: " . BASE_URL . "chat");
            exit;
        }

        $cid = $this->findDmConversation($meId, $otherId);
        if ($cid <= 0) {
            $cid = $this->createConversation('dm', null, $meId);
            $this->addParticipant($cid, $meId);
            $this->addParticipant($cid, $otherId);
        }

        header("Location: " . BASE_URL . "chat&cid=" . (int)$cid);
        exit;
    }

    /* =========================
       CREATE GROUP
       ========================= */
    public function group(): void
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);
        $meId = (int)($_SESSION['user']['id'] ?? 0);

        if (!$this->chatV2Enabled()) {
            header("Location: " . BASE_URL . "chat");
            exit;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $ids = $_POST['user_ids'] ?? [];
        if (!is_array($ids)) $ids = [];

        $participantIds = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!in_array($meId, $participantIds, true)) {
            $participantIds[] = $meId;
        }

        // Minimum 2 participants total (me + at least one other)
        $participantIds = array_values(array_unique($participantIds));
        if (count($participantIds) < 2) {
            header("Location: " . BASE_URL . "chat");
            exit;
        }

        if ($title === '') {
            $title = 'Grup nou';
        }

        // Validate all are internal active users
        $in = implode(',', array_fill(0, count($participantIds), '?'));
        $st = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id IN ($in) AND role IN ('admin','employee','staff') AND is_active = 1");
        $st->execute($participantIds);
        $okCount = (int)$st->fetchColumn();
        if ($okCount !== count($participantIds)) {
            header("Location: " . BASE_URL . "chat");
            exit;
        }

        $cid = $this->createConversation('group', $title, $meId);
        foreach ($participantIds as $uid) {
            $this->addParticipant($cid, $uid);
        }

        header("Location: " . BASE_URL . "chat&cid=" . (int)$cid);
        exit;
    }

    /* =========================
       SEND MESSAGE + FILES
       ========================= */
    public function store(): void
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);
        $meId = (int)($_SESSION['user']['id'] ?? 0);
        if ($meId <= 0) {
            $this->json(['ok' => false, 'error' => 'Unauthorized'], 401);
        }

        // v2
        if ($this->chatV2Enabled()) {
            $cid = (int)($_GET['cid'] ?? ($_POST['cid'] ?? 0));
            if ($cid <= 0 || !$this->isParticipant($cid, $meId)) {
                $this->json(['ok' => false, 'error' => 'Forbidden'], 403);
            }

            $msg = trim((string)($_POST['message'] ?? ''));
            $hasFiles = isset($_FILES['files']) && !empty($_FILES['files']['name']);
            if ($msg === '' && !$hasFiles) {
                $this->json(['ok' => true]);
            }

            $stmt = $pdo->prepare("INSERT INTO conversation_messages (conversation_id, sender_id, body) VALUES (?, ?, ?)");
            $stmt->execute([$cid, $meId, $msg]);
            $messageId = (int)$pdo->lastInsertId();

            if ($hasFiles) {
                $this->handleChatAttachments($messageId, $meId);
            }

            $this->json(['ok' => true, 'id' => $messageId]);
        }

        // legacy
        $this->legacyStore();
    }

    /* =========================
       POLL
       ========================= */
    public function poll(): void
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $since = (int)($_GET['since'] ?? 0);
        $meId = (int)($_SESSION['user']['id'] ?? 0);

        if ($this->chatV2Enabled()) {
            $cid = (int)($_GET['cid'] ?? 0);
            if ($cid <= 0 || !$this->isParticipant($cid, $meId)) {
                $this->json(['messages' => []]);
            }

            // Delivered when user is connected to this conversation (polling)
            $this->touchDelivered($cid, $meId);

            $rows = $this->getMessages($cid, $since);
            $rows = $this->attachAttachments($rows);
            foreach ($rows as &$m) {
                $m['is_me'] = ((int)$m['user_id'] === $meId);
            }
            unset($m);

            // Status updates for my messages (so ✓✓ updates without new messages)
            $from = max(0, $since - 300);
            $st = $pdo->prepare("SELECT id FROM conversation_messages WHERE conversation_id = ? AND id > ? AND sender_id = ? ORDER BY id ASC");
            $st->execute([$cid, $from, $meId]);
            $ids = $st->fetchAll(PDO::FETCH_COLUMN);
            $statuses = [];
            foreach ($ids as $mid) {
                $statuses[] = $this->messageStatus((int)$mid, $cid, $meId);
            }

            $this->json(['messages' => $rows, 'statuses' => $statuses]);
        }

        $this->legacyPoll();
    }

    /* =========================
       MARK READ (VISIBLE)
       ========================= */
    public function markRead(): void
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $meId = (int)($_SESSION['user']['id'] ?? 0);
        $cid = (int)($_GET['cid'] ?? ($_POST['cid'] ?? 0));

        if (!$this->chatV2Enabled()) {
            // legacy: keep the old behavior if needed
            $this->legacyMarkRead();
            return;
        }

        if ($cid <= 0 || !$this->isParticipant($cid, $meId)) {
            $this->json(['ok' => true]);
        }

        $pdo->prepare("UPDATE conversation_participants SET last_read_at = NOW() WHERE conversation_id = ? AND user_id = ? AND left_at IS NULL")
            ->execute([$cid, $meId]);

        $this->json(['ok' => true]);
    }

    /* =========================
       CONVERSATION ACTIONS (WhatsApp-like)
       ========================= */
    public function hideConversation(): void
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $meId = (int)($_SESSION['user']['id'] ?? 0);
        $cid = (int)($_POST['cid'] ?? 0);

        if (!$this->chatV2Enabled() || $cid <= 0) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        // Don't allow hiding the General chat (keeps a safe default)
        $st = $pdo->prepare("SELECT type FROM conversations WHERE id = ? LIMIT 1");
        $st->execute([$cid]);
        $type = (string)($st->fetchColumn() ?: '');
        if ($type === 'general') {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        // Only hide if I'm still a participant
        if ($this->isParticipant($cid, $meId)) {
            $pdo->prepare("UPDATE conversation_participants SET hidden_at = NOW() WHERE conversation_id = ? AND user_id = ?")
                ->execute([$cid, $meId]);
        }

        header('Location: ' . BASE_URL . 'chat');
        exit;
    }

    public function leaveConversation(): void
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $meId = (int)($_SESSION['user']['id'] ?? 0);
        $cid = (int)($_POST['cid'] ?? 0);

        if (!$this->chatV2Enabled() || $cid <= 0) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        $st = $pdo->prepare("SELECT type FROM conversations WHERE id = ? LIMIT 1");
        $st->execute([$cid]);
        $type = (string)($st->fetchColumn() ?: '');
        if ($type === 'general') {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        // Leave: lose access (but chat history stays for others)
        $pdo->prepare("UPDATE conversation_participants SET left_at = NOW(), hidden_at = NOW() WHERE conversation_id = ? AND user_id = ? AND left_at IS NULL")
            ->execute([$cid, $meId]);

        header('Location: ' . BASE_URL . 'chat');
        exit;
    }

    public function deleteConversation(): void
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $meId = (int)($_SESSION['user']['id'] ?? 0);
        $cid = (int)($_POST['cid'] ?? 0);

        if (!$this->chatV2Enabled() || $cid <= 0) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        $st = $pdo->prepare("SELECT type, created_by FROM conversations WHERE id = ? LIMIT 1");
        $st->execute([$cid]);
        $row = $st->fetch(PDO::FETCH_ASSOC) ?: [];
        $type = (string)($row['type'] ?? '');
        $createdBy = (int)($row['created_by'] ?? 0);

        if ($type === 'general' || $createdBy !== $meId) {
            header('Location: ' . BASE_URL . 'chat');
            exit;
        }

        // Delete for everyone: mark conversation deleted
        $pdo->prepare("UPDATE conversations SET deleted_at = NOW(), deleted_by = ? WHERE id = ? AND deleted_at IS NULL")
            ->execute([$meId, $cid]);

        header('Location: ' . BASE_URL . 'chat');
        exit;
    }

    /* =========================
       DATA HELPERS (V2)
       ========================= */

    private function chatV2Enabled(): bool
    {
        static $enabled = null;
        if ($enabled !== null) return $enabled;
        global $pdo;
        try {
            $pdo->query("SELECT 1 FROM conversations LIMIT 1");
            $pdo->query("SELECT 1 FROM conversation_participants LIMIT 1");
            $pdo->query("SELECT 1 FROM conversation_messages LIMIT 1");
            $enabled = true;
        } catch (\Throwable $e) {
            $enabled = false;
        }
        return $enabled;
    }

    private function ensureGeneralConversation(int $meId): int
    {
        global $pdo;

        $cid = (int)$pdo->query("SELECT id FROM conversations WHERE type = 'general' LIMIT 1")->fetchColumn();
        if ($cid <= 0) {
            $cid = $this->createConversation('general', 'General', $meId);
        }

        // Ensure all internal users are members (safe idempotent)
        $internal = $pdo->query("SELECT id FROM users WHERE role IN ('admin','employee','staff') AND is_active = 1")
            ->fetchAll(PDO::FETCH_COLUMN);
        foreach ($internal as $uid) {
            $this->addParticipant($cid, (int)$uid);
        }

        return $cid;
    }

    private function createConversation(string $type, ?string $title, int $createdBy): int
    {
        global $pdo;
        $stmt = $pdo->prepare("INSERT INTO conversations (type, title, created_by) VALUES (?, ?, ?)");
        $stmt->execute([$type, $title, $createdBy]);
        return (int)$pdo->lastInsertId();
    }

    private function addParticipant(int $cid, int $uid): void
    {
        global $pdo;
        // Idempotent insert
        $pdo->prepare("INSERT IGNORE INTO conversation_participants (conversation_id, user_id) VALUES (?, ?)")
            ->execute([$cid, $uid]);
    }

    private function isParticipant(int $cid, int $uid): bool
    {
        global $pdo;
        // Must be a participant that didn't leave, and conversation not deleted
        $st = $pdo->prepare(
            "SELECT 1\n"
            . "FROM conversation_participants p\n"
            . "JOIN conversations c ON c.id = p.conversation_id\n"
            . "WHERE p.conversation_id = ? AND p.user_id = ?\n"
            . "  AND (p.left_at IS NULL)\n"
            . "  AND (c.deleted_at IS NULL)\n"
            . "LIMIT 1"
        );
        $st->execute([$cid, $uid]);
        return (bool)$st->fetchColumn();
    }

    private function unhideIfNeeded(int $cid, int $uid): void
    {
        global $pdo;
        // If user hid the chat before, unhide when opening it.
        $pdo->prepare("UPDATE conversation_participants SET hidden_at = NULL WHERE conversation_id = ? AND user_id = ? AND hidden_at IS NOT NULL")
            ->execute([$cid, $uid]);
    }

    private function getConversation(int $cid): array
    {
        global $pdo;

        $st = $pdo->prepare("SELECT id, type, title, created_by, created_at FROM conversations WHERE id = ? LIMIT 1");
        $st->execute([$cid]);
        $c = $st->fetch(PDO::FETCH_ASSOC);
        if (!$c) return ['id' => $cid, 'type' => 'unknown', 'title' => ''];

        // DM title fallback: other participant name
        if (($c['type'] ?? '') === 'dm') {
            $st2 = $pdo->prepare("SELECT u.name
                FROM conversation_participants p
                JOIN users u ON u.id = p.user_id
                WHERE p.conversation_id = ? AND p.user_id <> ?
                LIMIT 1");
            $st2->execute([$cid, (int)($_SESSION['user']['id'] ?? 0)]);
            $other = $st2->fetchColumn();
            if ($other) $c['title'] = (string)$other;
        }

        return $c;
    }

    private function getMyConversations(int $meId): array
    {
        global $pdo;

        // list with last message time
        $st = $pdo->prepare(
            "SELECT c.id, c.type, c.title, "
            . "(SELECT cm.created_at FROM conversation_messages cm WHERE cm.conversation_id = c.id ORDER BY cm.id DESC LIMIT 1) AS last_at "
            . "FROM conversations c "
            . "JOIN conversation_participants p ON p.conversation_id = c.id "
            . "WHERE p.user_id = ? "
            . "  AND p.left_at IS NULL "
            . "  AND p.hidden_at IS NULL "
            . "  AND c.deleted_at IS NULL "
            . "ORDER BY (last_at IS NULL) ASC, last_at DESC, c.id DESC"
        );
        $st->execute([$meId]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);

        // fill DM titles
        foreach ($rows as &$c) {
            if (($c['type'] ?? '') === 'dm') {
                $st2 = $pdo->prepare("SELECT u.name
                    FROM conversation_participants p
                    JOIN users u ON u.id = p.user_id
                    WHERE p.conversation_id = ? AND p.user_id <> ?
                    LIMIT 1");
                $st2->execute([(int)$c['id'], $meId]);
                $other = $st2->fetchColumn();
                $c['title'] = $other ? (string)$other : 'DM';
            } elseif (($c['type'] ?? '') === 'general' && trim((string)($c['title'] ?? '')) === '') {
                $c['title'] = 'General';
            }
        }
        unset($c);

        return $rows;
    }

    private function findDmConversation(int $a, int $b): int
    {
        global $pdo;

        $st = $pdo->prepare(
            "SELECT c.id "
            . "FROM conversations c "
            . "JOIN conversation_participants p1 ON p1.conversation_id = c.id AND p1.user_id = ? "
            . "JOIN conversation_participants p2 ON p2.conversation_id = c.id AND p2.user_id = ? "
            . "WHERE c.type = 'dm' "
            . "LIMIT 1"
        );
        $st->execute([$a, $b]);
        return (int)($st->fetchColumn() ?: 0);
    }

    private function touchDelivered(int $cid, int $meId): void
    {
        global $pdo;
        $pdo->prepare("UPDATE conversation_participants SET last_delivered_at = NOW() WHERE conversation_id = ? AND user_id = ? AND left_at IS NULL")
            ->execute([$cid, $meId]);
    }

    private function getMessages(int $cid, int $sinceId): array
    {
        global $pdo;
        $st = $pdo->prepare(
            "SELECT cm.id, cm.sender_id AS user_id, cm.body AS message, cm.created_at, u.name "
            . "FROM conversation_messages cm "
            . "JOIN users u ON u.id = cm.sender_id "
            . "WHERE cm.conversation_id = ? AND cm.id > ? "
            . "ORDER BY cm.id ASC"
        );
        $st->execute([$cid, $sinceId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    private function messageStatus(int $messageId, int $cid, int $senderId): array
    {
        global $pdo;

        $st = $pdo->prepare("SELECT created_at FROM conversation_messages WHERE id = ? AND conversation_id = ? AND sender_id = ? LIMIT 1");
        $st->execute([$messageId, $cid, $senderId]);
        $createdAt = $st->fetchColumn();
        if (!$createdAt) {
            return ['id' => $messageId, 'delivered' => false, 'read' => false];
        }

        // Delivered: at least one other participant has last_delivered_at >= created_at
        $st2 = $pdo->prepare(
            "SELECT "
            . "MAX(CASE WHEN user_id <> ? AND last_delivered_at IS NOT NULL AND last_delivered_at >= ? THEN 1 ELSE 0 END) AS any_delivered, "
            . "MAX(CASE WHEN user_id <> ? AND last_read_at IS NOT NULL AND last_read_at >= ? THEN 1 ELSE 0 END) AS any_read "
            . "FROM conversation_participants "
            . "WHERE conversation_id = ? AND left_at IS NULL"
        );
        $st2->execute([$senderId, $createdAt, $senderId, $createdAt, $cid]);
        $r = $st2->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'id' => $messageId,
            'delivered' => !empty($r['any_delivered']),
            'read' => !empty($r['any_read']),
        ];
    }

    /* =========================
       ATTACHMENTS (V2)
       ========================= */
    private function attachAttachments(array $messages): array
    {
        global $pdo;
        if (empty($messages)) return $messages;

        $ids = array_unique(array_map(fn($m) => (int)($m['id'] ?? 0), $messages));
        $ids = array_filter($ids);
        if (!$ids) return $messages;

        $in = implode(',', array_fill(0, count($ids), '?'));

        if ($this->chatV2Enabled()) {
            $stmt = $pdo->prepare(
                "SELECT id, message_id, original_name, mime_type, size_bytes "
                . "FROM conversation_attachments "
                . "WHERE message_id IN ($in) "
                . "ORDER BY id ASC"
            );
        } else {
            $stmt = $pdo->prepare(
                "SELECT id, message_id, original_name, mime_type, size_bytes "
                . "FROM chat_attachments "
                . "WHERE message_id IN ($in) "
                . "ORDER BY id ASC"
            );
        }

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

            if ($this->chatV2Enabled()) {
                $stmt = $pdo->prepare(
                    "INSERT INTO conversation_attachments (message_id, uploaded_by, original_name, stored_name, mime_type, size_bytes) "
                    . "VALUES (?, ?, ?, ?, ?, ?)"
                );
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO chat_attachments (message_id, uploaded_by, original_name, stored_name, mime_type, size_bytes) "
                    . "VALUES (?, ?, ?, ?, ?, ?)"
                );
            }

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
       LEGACY CHAT (fallback)
       ========================= */
    private function legacyIndex(): void
    {
        global $pdo;
        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);

        $stmt = $pdo->query("SELECT MAX(id) AS m FROM messages");
        $_SESSION['chat_last_seen_id'] = (int)($stmt->fetch(PDO::FETCH_ASSOC)['m'] ?? 0);

        $messages = $pdo->query(
            "SELECT m.id, m.user_id, m.message, m.created_at, m.delivered_at, m.read_at, u.name "
            . "FROM messages m "
            . "JOIN users u ON u.id = m.user_id "
            . "ORDER BY m.id ASC "
            . "LIMIT 200"
        )->fetchAll(PDO::FETCH_ASSOC);

        $messages = $this->attachAttachments($messages);
        foreach ($messages as &$m) {
            $m['is_me'] = ((int)$m['user_id'] === $currentUserId);
        }
        unset($m);

        $conversations = [];
        $activeConversation = ['id' => 0, 'type' => 'legacy', 'title' => 'General'];
        $users = $pdo->query("SELECT id, name, role FROM users WHERE role IN ('admin','employee','staff') AND is_active = 1 ORDER BY role DESC, name ASC")
            ->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/chat.php';
    }

    private function legacyStore(): void
    {
        global $pdo;
        $userId = (int)($_SESSION['user']['id'] ?? 0);
        $msg = trim((string)($_POST['message'] ?? ''));
        $hasFiles = isset($_FILES['files']) && !empty($_FILES['files']['name']);
        if ($msg === '' && !$hasFiles) {
            $this->json(['ok' => true]);
        }
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
        $stmt->execute([$userId, $msg]);
        $messageId = (int)$pdo->lastInsertId();
        if ($hasFiles) $this->handleChatAttachments($messageId, $userId);
        $this->json(['ok' => true, 'id' => $messageId]);
    }

    private function legacyPoll(): void
    {
        global $pdo;
        $since = (int)($_GET['since'] ?? 0);
        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);

        $pdo->prepare("UPDATE messages SET delivered_at = COALESCE(delivered_at, NOW())
                   WHERE id > ? AND user_id <> ? AND delivered_at IS NULL")
            ->execute([$since, $currentUserId]);

        $stmt = $pdo->prepare(
            "SELECT m.id, m.user_id, m.message, m.created_at, m.delivered_at, m.read_at, u.name "
            . "FROM messages m "
            . "JOIN users u ON u.id = m.user_id "
            . "WHERE m.id > ? "
            . "ORDER BY m.id ASC"
        );
        $stmt->execute([$since]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rows = $this->attachAttachments($rows);
        foreach ($rows as &$m) {
            $m['is_me'] = ((int)$m['user_id'] === $currentUserId);
        }
        unset($m);

        $this->json(['messages' => $rows]);
    }

    private function legacyMarkRead(): void
    {
        global $pdo;
        $currentUserId = (int)($_SESSION['user']['id'] ?? 0);
        try {
            $pdo->query("SELECT read_at FROM messages LIMIT 1");
        } catch (\Throwable $e) {
            $this->json(['ok' => true]);
        }
        $pdo->prepare("UPDATE messages SET read_at = COALESCE(read_at, NOW()) WHERE user_id <> ? AND read_at IS NULL")
            ->execute([$currentUserId]);
        $this->json(['ok' => true]);
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
}
