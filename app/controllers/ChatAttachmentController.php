<?php

class ChatAttachmentController
{
    public function download(): void
    {
        global $pdo;

        Auth::requireRole(['admin','employee','staff']);

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo "Bad request";
            return;
        }

        // Prefer chat v2 attachments if table exists
        $table = 'chat_attachments';
        try {
            $pdo->query("SELECT 1 FROM conversation_attachments LIMIT 1");
            $table = 'conversation_attachments';
        } catch (\Throwable $e) {
            // ignore
        }

        $stmt = $pdo->prepare("SELECT id, original_name, stored_name, mime_type FROM {$table} WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $a = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$a) {
            http_response_code(404);
            echo "Not found";
            return;
        }

        $path = dirname(__DIR__, 2) . '/uploads/chat/' . $a['stored_name'];
        if (!is_file($path)) {
            http_response_code(404);
            echo "File missing";
            return;
        }

        $mime = $a['mime_type'] ?: 'application/octet-stream';
        $name = $a['original_name'] ?: 'file';

        $inline = isset($_GET['inline']) && $_GET['inline'] === '1';
        $download = isset($_GET['download']) && $_GET['download'] === '1';

        $isInlineType = (str_starts_with($mime, 'image/') || $mime === 'application/pdf');
        $disposition = 'attachment';
        if (!$download && $inline && $isInlineType) $disposition = 'inline';

        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: ' . $disposition . '; filename="' . $this->safeFilename($name) . '"');

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
