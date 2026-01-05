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

        header("Location: " . BASE_URL . "client/ticket&id=" . $ticketId);
        exit;
    }

    // CLIENT: show ticket (only own)
    public function clientShow()
    {
        global $pdo;
        $clientId = Auth::id();
        $ticketId = (int)($_GET['id'] ?? 0);

        $stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND client_id = ? LIMIT 1");
        $stmt->execute([$ticketId, $clientId]);
        $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ticket) { http_response_code(404); exit('Ticket not found'); }

        // client sees only public messages
        $stmt = $pdo->prepare("
            SELECT tm.*, u.name
            FROM ticket_messages tm
            JOIN users u ON u.id = tm.sender_id
            WHERE tm.ticket_id = ? AND tm.is_internal = 0
            ORDER BY tm.id ASC
        ");
        $stmt->execute([$ticketId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/client/tickets/show.php';
    }

    // CLIENT: add message (public only)
    public function clientAddMessage()
    {
        global $pdo;
        $clientId = Auth::id();
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $body = trim($_POST['body'] ?? '');

        if ($ticketId <= 0 || $body === '') {
            header("Location: " . BASE_URL . "client/tickets");
            exit;
        }

        // ensure ownership
        $stmt = $pdo->prepare("SELECT 1 FROM tickets WHERE id=? AND client_id=?");
        $stmt->execute([$ticketId, $clientId]);
        if (!$stmt->fetchColumn()) { http_response_code(403); exit('Forbidden'); }

        $stmt = $pdo->prepare("INSERT INTO ticket_messages (ticket_id, sender_id, body, is_internal) VALUES (?, ?, ?, 0)");
        $stmt->execute([$ticketId, $clientId, $body]);

        // bump updated_at
        $pdo->prepare("UPDATE tickets SET updated_at = NOW() WHERE id=?")->execute([$ticketId]);

        header("Location: " . BASE_URL . "client/ticket&id=" . $ticketId);
        exit;
    }

    // ADMIN: list all tickets
    public function adminIndex()
    {
        global $pdo;

        $sql = "
          SELECT t.*,
                 u.name AS client_name,
                 (SELECT body FROM ticket_messages tm WHERE tm.ticket_id=t.id AND tm.is_internal=0 ORDER BY tm.id DESC LIMIT 1) AS last_public_message
          FROM tickets t
          JOIN users u ON u.id = t.client_id
          ORDER BY t.updated_at DESC, t.id DESC
        ";
        $tickets = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

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

        if (!$ticket) { http_response_code(404); exit('Ticket not found'); }

        $stmt = $pdo->prepare("
            SELECT tm.*, u.name
            FROM ticket_messages tm
            JOIN users u ON u.id = tm.sender_id
            WHERE tm.ticket_id = ?
            ORDER BY tm.id ASC
        ");
        $stmt->execute([$ticketId]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    }

    // ADMIN: change status
    public function adminUpdateStatus()
    {
        global $pdo;
        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $status = (string)($_POST['status'] ?? 'open');

        $allowed = ['open','in_progress','resolved','closed'];
        if ($ticketId <= 0 || !in_array($status, $allowed, true)) {
            header("Location: " . BASE_URL . "admin/tickets");
            exit;
        }

        $stmt = $pdo->prepare("UPDATE tickets SET status=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$status, $ticketId]);

        header("Location: " . BASE_URL . "admin/ticket&id=" . $ticketId);
        exit;
    }
}
