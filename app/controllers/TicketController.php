<?php

class TicketController
{
    // CLIENT: list tickets
    public function clientIndex()
    {
        global $pdo;
        $clientId = Auth::id();

        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE client_id = ? ORDER BY updated_at DESC, id DESC");
        $stmt->execute([$clientId]);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/client/tickets/index.php';
    }

    // CLIENT: create ticket
    public function clientStore()
    {
        global $pdo;
        $clientId = Auth::id();

        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($subject === '' || $message === '') {
            header("Location: " . BASE_URL . "client/tickets");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO tickets (client_id, subject) VALUES (?, ?)");
        $stmt->execute([$clientId, $subject]);
        $ticketId = (int)$pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, body, is_internal) VALUES (?, ?, ?, 0)");
        $stmt->execute([$ticketId, $clientId, $message]);

        // upload attachments (optional)
        if (method_exists($this, 'handleAttachments')) {
            $this->handleAttachments($ticketId);
        }

        header("Location: " . BASE_URL . "client/ticket&id=" . $ticketId);
        exit;
    }

    // CLIENT: show ticket (only own)
    public function clientShow()
    {
        global $pdo;

        $clientId = Auth::id();
        $ticketId = (int)($_GET['id'] ?? 0);

        if ($ticketId <= 0) {
            http_response_code(400);
            exit('Bad ticket id');
        }

        // Ticket (only if belongs to logged client)
        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND client_id = ? LIMIT 1");
        $stmt->execute([$ticketId, $clientId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            http_response_code(404);
            exit('Ticket not found');
        }

        // Client sees only public messages
        $stmt = $pdo->prepare("
            SELECT tm.*, u.name
            FROM ticket_messages tm
            JOIN users u ON u.id = tm.sender_id
            WHERE tm.ticket_id = ? AND tm.is_internal = 0
            ORDER BY tm.id ASC
        ");
        $stmt->execute([$ticketId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attachments (public list; access control is enforced at download route)
        $stmt = $pdo->prepare("
            SELECT id, original_name, created_at
            FROM ticket_attachments
            WHERE ticket_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$ticketId]);
        $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);


        require __DIR__ . '/../views/client/tickets/show.php';
    }

    // CLIENT: add message (public only)
    public function clientAddMessage()
    {
        global $pdo;

        $clientId = Auth::id();
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $body = trim($_POST['message'] ?? '');

        if ($ticketId <= 0) {
            http_response_code(400);
            exit('Bad ticket id');
        }

        if ($body === '') {
            $_SESSION['flash_error'] = 'Mesajul nu poate fi gol.';
            header("Location: " . BASE_URL . "client/ticket&id=" . $ticketId);
            exit;
        }

        // ticket must exist + belong to client + be open
        $stmt = $pdo->prepare("SELECT id, status FROM tickets WHERE id = ? AND client_id = ? LIMIT 1");
        $stmt->execute([$ticketId, $clientId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            http_response_code(404);
            exit('Ticket not found');
        }

        if (($ticket['status'] ?? '') !== 'open') {
            $_SESSION['flash_error'] = 'Ticket închis. Nu poți trimite mesaje.';
            header("Location: " . BASE_URL . "client/ticket&id=" . $ticketId);
            exit;
        }

        // insert public message
        $stmt = $pdo->prepare("
            INSERT INTO ticket_messages (ticket_id, sender_id, body, is_internal)
            VALUES (?, ?, ?, 0)
        ");
        $stmt->execute([$ticketId, $clientId, $body]);

        // upload attachments (if you added handleAttachments() in TicketController)
        if (method_exists($this, 'handleAttachments')) {
            $this->handleAttachments($ticketId);
        }

        // touch ticket updated_at if you have it
        try {
            $pdo->prepare("UPDATE tickets SET updated_at = NOW() WHERE id = ?")->execute([$ticketId]);
        } catch (Throwable $e) {
            // ignore if updated_at column doesn't exist
        }

        header("Location: " . BASE_URL . "client/ticket&id=" . $ticketId);
        exit;
    }

    // ADMIN: list all tickets
    public function adminIndex()
    {
        global $pdo;

        // Tabs: open | resolved | deleted
        $tab = (string)($_GET['tab'] ?? 'open');
        if (!in_array($tab, ['open', 'resolved', 'deleted'], true)) {
            $tab = 'open';
        }

        // Search by subject (and client name)
        $q = trim((string)($_GET['q'] ?? ''));

        $where = [];
        $params = [];

        if ($tab === 'deleted') {
            $where[] = 't.deleted_at IS NOT NULL';
        } else {
            $where[] = 't.deleted_at IS NULL';
            $where[] = 't.status = ?';
            $params[] = $tab; // open / resolved
        }

        if ($q !== '') {
            $where[] = '(t.subject LIKE ? OR u.name LIKE ?)';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "
          SELECT t.*,
                 u.name AS client_name,
                 (SELECT body FROM ticket_messages tm WHERE tm.ticket_id=t.id AND tm.is_internal=0 ORDER BY tm.id DESC LIMIT 1) AS last_public_message
          FROM tickets t
          JOIN users u ON u.id = t.client_id
          $whereSql
          ORDER BY t.sort_order ASC, t.updated_at DESC, t.id DESC
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/tickets/index.php';
    }

    // ADMIN: show ticket + public messages + internal notes
    public function adminShow()
    {
        global $pdo;
        $ticketId = (int)($_GET['id'] ?? 0);

        $stmt = $pdo->prepare("
            SELECT t.*, u.name AS client_name
            FROM tickets t
            JOIN users u ON u.id = t.client_id
            WHERE t.id = ?
            LIMIT 1
        ");
        $stmt->execute([$ticketId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) {
            http_response_code(404);
            exit('Ticket not found');
        }

        $stmt = $pdo->prepare("
            SELECT tm.*, u.name
            FROM ticket_messages tm
            JOIN users u ON u.id = tm.sender_id
            WHERE tm.ticket_id = ?
            ORDER BY tm.id ASC
        ");
        $stmt->execute([$ticketId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Attachments (admin can see all; download route should still enforce access)
        $stmt = $pdo->prepare("
            SELECT id, original_name, created_at
            FROM ticket_attachments
            WHERE ticket_id = ?
            ORDER BY id ASC
        ");
        $stmt->execute([$ticketId]);
        $attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/tickets/show.php';
    }

    // ADMIN: add message (public or internal)
    public function adminAddMessage()
    {
        global $pdo;
        $staffId = Auth::id();
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $body = trim($_POST['body'] ?? '');
        $isInternal = (int)($_POST['is_internal'] ?? 0) === 1 ? 1 : 0;

        if ($ticketId <= 0 || $body === '') {
            header("Location: " . BASE_URL . "admin/tickets");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, body, is_internal) VALUES (?, ?, ?, ?)");
        $stmt->execute([$ticketId, $staffId, $body, $isInternal]);

        $pdo->prepare("UPDATE tickets SET updated_at = NOW() WHERE id=?")->execute([$ticketId]);

        header("Location: " . BASE_URL . "admin/ticket&id=" . $ticketId);
        exit;

        $this->handleAttachments($ticketId);
    }

    // ADMIN: change status
    public function adminUpdateStatus()
    {
        global $pdo;
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $status = (string)($_POST['status'] ?? 'open');

        // Only these 2 states are supported in UI
        $allowed = ['open', 'resolved'];
        if ($ticketId <= 0 || !in_array($status, $allowed, true)) {
            header("Location: " . BASE_URL . "admin/tickets");
            exit;
        }

        $stmt = $pdo->prepare("UPDATE tickets SET status=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$status, $ticketId]);

        header("Location: " . BASE_URL . "admin/ticket&id=" . $ticketId);
        exit;
    }

    // ADMIN: soft delete (moves ticket to Deleted tab)
    public function adminDelete()
    {
        global $pdo;
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        if ($ticketId <= 0) {
            header("Location: " . BASE_URL . "admin/tickets");
            exit;
        }

        $pdo->prepare("UPDATE tickets SET deleted_at = NOW(), updated_at = NOW() WHERE id = ?")
            ->execute([$ticketId]);

        header("Location: " . BASE_URL . "admin/tickets");
        exit;
    }

    // ADMIN: restore from Deleted tab
    public function adminRestore()
    {
        global $pdo;
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        if ($ticketId <= 0) {
            header("Location: " . BASE_URL . "admin/tickets");
            exit;
        }

        $pdo->prepare("UPDATE tickets SET deleted_at = NULL, updated_at = NOW() WHERE id = ?")
            ->execute([$ticketId]);

        header("Location: " . BASE_URL . "admin/tickets&tab=deleted");
        exit;
    }

    // ADMIN: bulk soft delete
    public function adminBulkDelete()
    {
        global $pdo;
        $ids = $_POST['ticket_ids'] ?? [];
        if (!is_array($ids) || count($ids) === 0) {
            header("Location: " . BASE_URL . "admin/tickets");
            exit;
        }

        $clean = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        if (!$clean) {
            header("Location: " . BASE_URL . "admin/tickets");
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($clean), '?'));
        $stmt = $pdo->prepare("UPDATE tickets SET deleted_at = NOW(), updated_at = NOW() WHERE id IN ($placeholders)");
        $stmt->execute($clean);

        $tab = (string)($_POST['tab'] ?? 'open');
        if (!in_array($tab, ['open', 'resolved', 'deleted'], true)) $tab = 'open';
        $q = trim((string)($_POST['q'] ?? ''));
        $redir = BASE_URL . 'admin/tickets&tab=' . urlencode($tab);
        if ($q !== '') $redir .= '&q=' . urlencode($q);

        header("Location: " . $redir);
        exit;
    }

    // ADMIN: reorder tickets within the current tab
    public function adminReorder()
    {
        global $pdo;

        header('Content-Type: application/json');

        $ids = $_POST['order'] ?? [];
        if (!is_array($ids) || count($ids) === 0) {
            echo json_encode(['ok' => false, 'error' => 'Missing order']);
            return;
        }

        $clean = array_values(array_filter(array_map('intval', $ids), fn($v) => $v > 0));
        if (!$clean) {
            echo json_encode(['ok' => false, 'error' => 'Bad ids']);
            return;
        }

        // Use a transaction for consistent ordering
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("UPDATE tickets SET sort_order = ? WHERE id = ?");
            $i = 1;
            foreach ($clean as $id) {
                $stmt->execute([$i, $id]);
                $i++;
            }
            $pdo->commit();
            echo json_encode(['ok' => true]);
        } catch (Throwable $e) {
            $pdo->rollBack();
            echo json_encode(['ok' => false, 'error' => 'DB error']);
        }
    }

    // attachament
    private function handleAttachments(int $ticketId): void
    {
        global $pdo;

        if (empty($_FILES['attachments']) || empty($_FILES['attachments']['name'])) {
            return;
        }

        $uploadDir = __DIR__ . '/../../uploads/tickets/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $userId = Auth::id();

        $names = $_FILES['attachments']['name'];
        $tmp   = $_FILES['attachments']['tmp_name'];
        $types = $_FILES['attachments']['type'];
        $sizes = $_FILES['attachments']['size'];
        $errs  = $_FILES['attachments']['error'];

        for ($i = 0; $i < count($names); $i++) {
            if ($errs[$i] !== UPLOAD_ERR_OK) continue;
            if ($sizes[$i] > 8 * 1024 * 1024) continue; // max 8MB

            // allowlist simplu
            $mime = (string)$types[$i];
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            if (!in_array($mime, $allowed, true)) continue;

            $original = (string)$names[$i];
            $stored = bin2hex(random_bytes(16)) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $original);

            if (!move_uploaded_file($tmp[$i], $uploadDir . $stored)) continue;

            $stmt = $pdo->prepare("
            INSERT INTO ticket_attachments
              (ticket_id, uploaded_by, original_name, stored_name, mime_type, size_bytes)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
            $stmt->execute([$ticketId, $userId, $original, $stored, $mime, (int)$sizes[$i]]);
        }
    }

    // refresh
    public function adminPoll()
{
    header('Content-Type: application/json; charset=utf-8');

    global $pdo;

    // Count open tickets (not deleted)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS c
        FROM tickets
        WHERE status = 'open'
          AND deleted_at IS NULL
    ");
    $stmt->execute();
    $openCount = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

    // Latest update timestamp
    $stmt2 = $pdo->prepare("
        SELECT MAX(updated_at) AS m
        FROM tickets
        WHERE deleted_at IS NULL
    ");
    $stmt2->execute();
    $latest = $stmt2->fetch(PDO::FETCH_ASSOC)['m'] ?? null;

    echo json_encode([
        'open_count' => $openCount,
        'latest_updated_at' => $latest,
    ]);
    exit;
}

}
