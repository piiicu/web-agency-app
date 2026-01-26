<?php

class DashboardController
{
    public function adminDashboard()
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $userId = Auth::id();
        $stmt = $pdo->prepare("SELECT id, name, email, phone, avatar, role FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$userId]);
        $me = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$me) {
            http_response_code(404);
            exit('User not found');
        }

        require __DIR__ . '/../views/admin/dashboard.php';
    }

    public function settings()
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $userId = Auth::id();
        $stmt = $pdo->prepare("SELECT id, name, email, phone, avatar FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$userId]);
        $me = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$me) {
            http_response_code(404);
            exit('User not found');
        }

        // Admin-only: preload users list for Settings -> Users tab
        $users = [];
        $inviteLink = null;
        if (Auth::role() === 'admin') {
            // Soft delete support: afișăm doar userii activi (is_active = 1).
            // Dacă DB e veche și nu are coloana is_active, o adăugăm automat.
            try {
                $users = $pdo->query("SELECT id, name, email, role, created_at FROM users WHERE is_active = 1 ORDER BY id DESC")
                    ->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Unknown column') !== false) {
                    $pdo->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
                    $users = $pdo->query("SELECT id, name, email, role, created_at FROM users WHERE is_active = 1 ORDER BY id DESC")
                        ->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    throw $e;
                }
            }

            $inviteLink = $_SESSION['invite_link'] ?? null;
            unset($_SESSION['invite_link']);
        }

        require __DIR__ . '/../views/admin/settings.php';
    }

    public function updateProfile()
    {
        global $pdo;

        Auth::requireRole(['admin', 'employee', 'staff']);

        $userId = Auth::id();

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if ($name === '' || $email === '') {
            $_SESSION['flash_error'] = 'Name și Email sunt obligatorii.';
            header("Location: " . BASE_URL . "admin/settings#profile");
            exit;
        }

        // Upload avatar (optional)
        $avatarFilename = null;
        if (!empty($_FILES['avatar']['name']) && is_uploaded_file($_FILES['avatar']['tmp_name'])) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mime = mime_content_type($_FILES['avatar']['tmp_name']);

            if (!isset($allowed[$mime])) {
                $_SESSION['flash_error'] = 'Avatar invalid. Acceptăm jpg/png/webp.';
                header("Location: " . BASE_URL . "admin/settings#profile");
                exit;
            }

            if (($_FILES['avatar']['size'] ?? 0) > 3 * 1024 * 1024) {
                $_SESSION['flash_error'] = 'Avatar prea mare (max 3MB).';
                header("Location: " . BASE_URL . "admin/settings#profile");
                exit;
            }

            $ext = $allowed[$mime];
            $avatarFilename = $userId . '_' . time() . '.' . $ext;

            $dir = __DIR__ . '/../../uploads/avatars';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }

            $dest = $dir . '/' . $avatarFilename;

            if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $dest)) {
                $_SESSION['flash_error'] = 'Nu am putut salva avatarul.';
                header("Location: " . BASE_URL . "admin/settings#profile");
                exit;
            }
        }

        if ($avatarFilename) {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, avatar=? WHERE id=? LIMIT 1");
            $stmt->execute([$name, $email, $phone, $avatarFilename, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone=? WHERE id=? LIMIT 1");
            $stmt->execute([$name, $email, $phone, $userId]);
        }

        $_SESSION['flash_success'] = 'Profil actualizat.';
        header("Location: " . BASE_URL . "admin/settings#profile");
        exit;
    }

    public function badgesPoll()
    {
        header('Content-Type: application/json; charset=utf-8');

        global $pdo;

        // 1) Tickets: open + not deleted
        $stmt = $pdo->prepare("
        SELECT COUNT(*) AS c
        FROM tickets
        WHERE status = 'open'
          AND deleted_at IS NULL
        ");
        $stmt->execute();
        $ticketsOpen = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

        // 2) Tasks: pending
        $stmt = $pdo->prepare("
        SELECT COUNT(*) AS c
        FROM tasks
        WHERE status = 'pending'
        ");
        $stmt->execute();
        $tasksPending = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

        // 3) Chat: "new messages" since last seen (session-based)
        $stmt = $pdo->prepare("SELECT MAX(id) AS m FROM messages");
        $stmt->execute();
        $maxMsgId = (int)($stmt->fetch(PDO::FETCH_ASSOC)['m'] ?? 0);

        if (!isset($_SESSION['chat_last_seen_id'])) {
            // prima dată când userul intră în admin: nu-l spamăm cu "mesaje noi" vechi
            $_SESSION['chat_last_seen_id'] = $maxMsgId;
            $chatNew = 0;
        } else {
            $lastSeen = (int)$_SESSION['chat_last_seen_id'];
            $me = (int)Auth::id();

            $stmt = $pdo->prepare("
            SELECT COUNT(*) AS c
            FROM messages
            WHERE id > :lastSeen
              AND user_id <> :me
        ");
            $stmt->execute(['lastSeen' => $lastSeen, 'me' => $me]);
            $chatNew = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
        }

        echo json_encode([
            'tickets_open'  => $ticketsOpen,
            'tasks_pending' => $tasksPending,
            'chat_new'      => $chatNew,
        ]);
        exit;
    }
}
