<?php

class AttachmentController
{
    public function download()
    {
        global $pdo;

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            exit('Bad attachment');
        }

        // luăm attachment + ticket_id + owner (client_id)
        $stmt = $pdo->prepare("
            SELECT a.*, t.client_id
            FROM ticket_attachments a
            JOIN tickets t ON t.id = a.ticket_id
            WHERE a.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $att = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$att) {
            http_response_code(404);
            exit('Not found');
        }

        $userId = Auth::id();
        $role = Auth::role();

        // Permisiuni:
        // - admin vede orice
        // - client vede doar dacă e ticketul lui
        if ($role === 'client' && (int)$att['client_id'] !== $userId) {
            http_response_code(403);
            exit('Forbidden');
        }

        $baseDir = __DIR__ . '/../../uploads/tickets/';
        $path = $baseDir . $att['stored_name'];

        if (!is_file($path)) {
            http_response_code(404);
            exit('File missing');
        }

        header('Content-Type: ' . $att['mime_type']);
        header('Content-Length: ' . (string)filesize($path));
        header('Content-Disposition: inline; filename="' . basename($att['original_name']) . '"');

        readfile($path);
        exit;
    }
}
