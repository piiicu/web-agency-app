<?php

class TaskAttachmentController
{
    private function ensureSchema(): void
    {
        global $pdo;

        // Ensure table exists
        try {
            $pdo->query("SELECT id FROM task_attachments LIMIT 1");
        } catch (Throwable $e) {
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS task_attachments (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    task_id INT(11) NOT NULL,
                    original_name VARCHAR(255) NOT NULL,
                    stored_name VARCHAR(255) NOT NULL,
                    mime_type VARCHAR(100) DEFAULT NULL,
                    size_bytes INT(11) NOT NULL DEFAULT 0,
                    uploaded_by INT(11) DEFAULT NULL,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY task_id (task_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            } catch (Throwable $e2) {
                // ignore
            }
        }
    }

    public function upload(): void
    {
        global $pdo;
        Auth::requireRole(['admin','employee','staff']);
        $this->ensureSchema();

        $taskId = (int)($_POST['task_id'] ?? 0);
        if ($taskId <= 0) {
            http_response_code(400);
            $this->json(['ok' => false, 'error' => 'bad_request']);
            return;
        }

        // Ensure task exists
        $stmtT = $pdo->prepare("SELECT id FROM tasks WHERE id=? LIMIT 1");
        $stmtT->execute([$taskId]);
        if (!$stmtT->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(404);
            $this->json(['ok' => false, 'error' => 'task_not_found']);
            return;
        }

        if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
            http_response_code(400);
            $this->json(['ok' => false, 'error' => 'no_file']);
            return;
        }

        $f = $_FILES['file'];
        if (!empty($f['error'])) {
            http_response_code(400);
            $this->json(['ok' => false, 'error' => 'upload_error']);
            return;
        }

        $maxBytes = 20 * 1024 * 1024; // 20MB
        $size = (int)($f['size'] ?? 0);
        if ($size <= 0 || $size > $maxBytes) {
            http_response_code(400);
            $this->json(['ok' => false, 'error' => 'file_too_large']);
            return;
        }

        $original = (string)($f['name'] ?? 'file');
        $tmp = (string)($f['tmp_name'] ?? '');

        $mime = null;
        try {
            if (is_file($tmp)) {
                $fi = new finfo(FILEINFO_MIME_TYPE);
                $mime = $fi->file($tmp) ?: null;
            }
        } catch (Throwable $e) {
            $mime = null;
        }

        // Basic allowlist (images + common docs)
        $allowed = [
            'image/jpeg','image/png','image/webp','image/gif',
            'application/pdf',
            'application/zip',
            'text/plain',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
        ];
        if ($mime !== null && !in_array($mime, $allowed, true)) {
            http_response_code(400);
            $this->json(['ok' => false, 'error' => 'file_type_not_allowed']);
            return;
        }

        $ext = pathinfo($original, PATHINFO_EXTENSION);
        $ext = preg_replace('/[^a-zA-Z0-9]/', '', (string)$ext);
        $stored = bin2hex(random_bytes(16)) . ($ext ? ('.' . strtolower($ext)) : '');

        $baseDir = dirname(__DIR__, 2) . '/uploads/tasks/';
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0775, true);
        }

        $dest = $baseDir . $stored;
        if (!@move_uploaded_file($tmp, $dest)) {
            http_response_code(500);
            $this->json(['ok' => false, 'error' => 'move_failed']);
            return;
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO task_attachments (task_id, original_name, stored_name, mime_type, size_bytes, uploaded_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $taskId,
                $original,
                $stored,
                $mime,
                $size,
                (int)Auth::id(),
            ]);
            $id = (int)$pdo->lastInsertId();
        } catch (Throwable $e) {
            @unlink($dest);
            http_response_code(500);
            $this->json(['ok' => false, 'error' => 'db_failed']);
            return;
        }

        $this->json([
            'ok' => true,
            'attachment' => [
                'id' => $id,
                'task_id' => $taskId,
                'original_name' => $original,
                'stored_name' => $stored,
                'mime_type' => $mime,
                'size_bytes' => $size,
            ],
        ]);
    }

    public function download(): void
    {
        global $pdo;
        Auth::requireRole(['admin','employee','staff']);
        $this->ensureSchema();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo "Bad request";
            return;
        }

        $stmt = $pdo->prepare("SELECT id, original_name, stored_name, mime_type, size_bytes FROM task_attachments WHERE id=? LIMIT 1");
        $stmt->execute([$id]);
        $a = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$a) {
            http_response_code(404);
            echo "Attachment not found";
            return;
        }

        $baseDir = dirname(__DIR__, 2) . '/uploads/tasks/';
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

        $isInlineType = (str_starts_with($mime, 'image/') || $mime === 'application/pdf');
        $disposition = 'attachment';
        if (!$forceDownload && ($inline && $isInlineType)) {
            $disposition = 'inline';
        }

        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: ' . $disposition . '; filename="' . $this->safeFilename($filename) . '"');

        while (ob_get_level()) { ob_end_clean(); }
        readfile($path);
        exit;
    }

    public function delete(): void
    {
        global $pdo;
        Auth::requireRole(['admin','employee','staff']);
        $this->ensureSchema();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            $this->json(['ok' => false, 'error' => 'bad_request']);
            return;
        }

        $stmt = $pdo->prepare("SELECT id, stored_name FROM task_attachments WHERE id=? LIMIT 1");
        $stmt->execute([$id]);
        $a = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$a) {
            http_response_code(404);
            $this->json(['ok' => false, 'error' => 'not_found']);
            return;
        }

        try {
            $pdo->prepare("DELETE FROM task_attachments WHERE id=?")->execute([$id]);
        } catch (Throwable $e) {
            http_response_code(500);
            $this->json(['ok' => false, 'error' => 'db_failed']);
            return;
        }

        $baseDir = dirname(__DIR__, 2) . '/uploads/tasks/';
        $path = $baseDir . ($a['stored_name'] ?? '');
        if (is_string($a['stored_name']) && $a['stored_name'] !== '' && is_file($path)) {
            @unlink($path);
        }

        $this->json(['ok' => true]);
    }

    private function safeFilename(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
        return $name ?: 'file';
    }

    private function json(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }
}
