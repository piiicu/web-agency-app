<?php

class AttachmentController
{
    public function download(): void
    {
        global $pdo;

        Auth::check();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo "Bad request";
            return;
        }

        // ia atașamentul + ticket_id + client_id (pentru permisiuni)
        $stmt = $pdo->prepare("
            SELECT a.id, a.ticket_id, a.original_name, a.stored_name, a.mime_type, a.size_bytes,
                   t.client_id
            FROM ticket_attachments a
            JOIN tickets t ON t.id = a.ticket_id
            WHERE a.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $a = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$a) {
            http_response_code(404);
            echo "Attachment not found";
            return;
        }

        // permisiuni: admin/staff/employee vede orice; client doar propriile tichete
        $role = Auth::role();
        $uid  = Auth::id();
        $isStaff = in_array($role, ['admin','employee','staff'], true);

        if (!$isStaff && (int)$a['client_id'] !== (int)$uid) {
            http_response_code(403);
            echo "Forbidden";
            return;
        }

        $baseDir = dirname(__DIR__, 2) . '/uploads/tickets/';
        $path = $baseDir . $a['stored_name'];

        if (!is_file($path)) {
            http_response_code(404);
            echo "File missing";
            return;
        }

        $mime = $a['mime_type'] ?: 'application/octet-stream';
        $filename = $a['original_name'] ?: 'download';

        $inline = isset($_GET['inline']) && $_GET['inline'] === '1';
        $forceDownload = isset($_GET['download']) && $_GET['download'] === '1';

        // pdf + imagini -> inline (dacă nu cere download)
        $isInlineType = (
            str_starts_with($mime, 'image/') ||
            $mime === 'application/pdf'
        );

        $disposition = 'attachment';
        if (!$forceDownload && ($inline && $isInlineType)) {
            $disposition = 'inline';
        }

        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: ' . $disposition . '; filename="' . $this->safeFilename($filename) . '"');

        // curățare output buffer (evită coruperi)
        while (ob_get_level()) { ob_end_clean(); }

        readfile($path);
        exit;
    }

    private function safeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        return $name ?: 'file';
    }
}

