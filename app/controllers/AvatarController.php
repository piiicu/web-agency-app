<?php

class AvatarController
{
    public function show()
    {
        global $pdo;

        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(400);
            exit('Bad request');
        }

        // doar user logat
        Auth::check();

        $role = Auth::role();
        $me   = Auth::id();

        // client poate vedea doar avatarul lui; admin poate vedea orice
        if ($role === 'client' && $me !== $userId) {
            http_response_code(403);
            exit('Forbidden');
        }

        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $avatar = $stmt->fetchColumn();

        if (!$avatar) {
            http_response_code(404);
            exit('No avatar');
        }

        $path = __DIR__ . '/../../uploads/avatars/' . $avatar;
        if (!is_file($path)) {
            http_response_code(404);
            exit('File missing');
        }

        // detect mime (simplu)
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream'
        };

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string)filesize($path));
        header('Cache-Control: private, max-age=86400');

        readfile($path);
        exit;
    }
}
