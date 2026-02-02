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

        Auth::requireRole(['admin', 'employee', 'staff']);

        $me = (int)Auth::id();

        // ------------------------------------------------------------------
        // Backwards compatible schema: add per-user "last seen" columns if missing
        // ------------------------------------------------------------------
        $neededCols = [
            'last_seen_tickets_at'    => "ALTER TABLE users ADD COLUMN last_seen_tickets_at DATETIME NULL",
            'last_seen_tasks_at'      => "ALTER TABLE users ADD COLUMN last_seen_tasks_at DATETIME NULL",
            'last_seen_chat_v2_msg_id' => "ALTER TABLE users ADD COLUMN last_seen_chat_v2_msg_id INT NULL",
            'last_seen_chat_msg_id'   => "ALTER TABLE users ADD COLUMN last_seen_chat_msg_id INT NULL",
        ];

        foreach ($neededCols as $col => $ddl) {
            try {
                $pdo->query("SELECT {$col} FROM users LIMIT 1");
            } catch (Throwable $e) {
                try {
                    $pdo->exec($ddl);
                } catch (Throwable $e2) { /* ignore */
                }
            }
        }

        // Read my last seen state
        $stMe = $pdo->prepare("SELECT last_seen_tickets_at, last_seen_tasks_at, last_seen_chat_v2_msg_id, last_seen_chat_msg_id FROM users WHERE id=? LIMIT 1");
        $stMe->execute([$me]);
        $meRow = $stMe->fetch(PDO::FETCH_ASSOC) ?: [];

        $lastTicketsAt = $meRow['last_seen_tickets_at'] ?? null;
        $lastTasksAt   = $meRow['last_seen_tasks_at'] ?? null;
        $lastChatV2Id  = isset($meRow['last_seen_chat_v2_msg_id']) ? (int)$meRow['last_seen_chat_v2_msg_id'] : null;
        $lastChatId    = isset($meRow['last_seen_chat_msg_id']) ? (int)$meRow['last_seen_chat_msg_id'] : null;

        // ------------------------------------------------------------------
        // 1) Tickets badge: how many tickets changed since I last visited tickets
        // ------------------------------------------------------------------
        if ($lastTicketsAt === null) {
            // First time: set baseline (don't show historical as "new")
            $pdo->prepare("UPDATE users SET last_seen_tickets_at = NOW() WHERE id=? AND last_seen_tickets_at IS NULL")->execute([$me]);
            $ticketsNew = 0;
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS c
                FROM tickets
                WHERE deleted_at IS NULL
                  AND updated_at > ?
            ");
            $stmt->execute([$lastTicketsAt]);
            $ticketsNew = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
        }

        // ------------------------------------------------------------------
        // 2) Tasks badge: how many new pending tasks since I last visited tasks
        // ------------------------------------------------------------------
        if ($lastTasksAt === null) {
            $pdo->prepare("UPDATE users SET last_seen_tasks_at = NOW() WHERE id=? AND last_seen_tasks_at IS NULL")->execute([$me]);
            $tasksNew = 0;
        } else {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS c
                FROM tasks
                WHERE status = 'pending'
                  AND created_at > ?
            ");
            $stmt->execute([$lastTasksAt]);
            $tasksNew = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
        }


        // 3) Chat: mesaje noi (V2 conversații) sau fallback legacy
        $chatNew = 0;

        $chatV2 = false;
        try {
            $pdo->query("SELECT 1 FROM conversations LIMIT 1");
            $pdo->query("SELECT 1 FROM conversation_messages LIMIT 1");
            $pdo->query("SELECT 1 FROM conversation_participants LIMIT 1");
            $chatV2 = true;
        } catch (Throwable $e) {
            $chatV2 = false;
        }

        if ($chatV2) {
            // Variante "clasică" și sigură: numărăm mesaje noi după un baseline în sesiune.
            // Baseline-ul se setează când userul intră în Chat (vezi ChatController) și se actualizează pe poll în chat.
            if ($lastChatV2Id === null || $lastChatV2Id <= 0) {
                // first time: set baseline to current max message id in my conversations
                $st = $pdo->prepare("
						SELECT COALESCE(MAX(cm.id), 0) AS m
						FROM conversation_messages cm
						JOIN conversation_participants p
						  ON p.conversation_id = cm.conversation_id
						 AND p.user_id = ?
						 AND p.left_at IS NULL
						JOIN conversations c
						  ON c.id = cm.conversation_id
						 AND c.deleted_at IS NULL
					");
                $st->execute([$me]);
                $max = (int)($st->fetch(PDO::FETCH_ASSOC)['m'] ?? 0);
                $pdo->prepare("UPDATE users SET last_seen_chat_v2_msg_id=? WHERE id=?")->execute([$max, $me]);
                $chatNew = 0;
            } else {
                $lastSeen = (int)$lastChatV2Id;
                $st = $pdo->prepare("
                        SELECT COUNT(*) AS c
                        FROM conversation_messages cm
                        JOIN conversation_participants p
                        ON p.conversation_id = cm.conversation_id
                        AND p.user_id = :me1
                        AND p.left_at IS NULL
                        JOIN conversations c
                        ON c.id = cm.conversation_id
                        AND c.deleted_at IS NULL
                        WHERE cm.id > :lastSeen
                        AND cm.sender_id <> :me2
                ");

                $st->execute([
                    'me1' => $me,
                    'me2' => $me,
                    'lastSeen' => $lastSeen
                ]);
                $chatNew = (int)($st->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
            }
        } else {
            // Legacy: "new messages" since last seen (session-based)
            $stmt = $pdo->prepare("SELECT MAX(id) AS m FROM messages");
            $stmt->execute();
            $maxMsgId = (int)($stmt->fetch(PDO::FETCH_ASSOC)['m'] ?? 0);

            if ($lastChatId === null || $lastChatId <= 0) {
                $pdo->prepare("UPDATE users SET last_seen_chat_msg_id=? WHERE id=?")->execute([$maxMsgId, $me]);
                $chatNew = 0;
            } else {
                $lastSeen = (int)$lastChatId;

                $stmt = $pdo->prepare("
						SELECT COUNT(*) AS c
						FROM messages
						WHERE id > :lastSeen
						  AND user_id <> :me
					");
                $stmt->execute(['lastSeen' => $lastSeen, 'me' => $me]);
                $chatNew = (int)($stmt->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);
            }
        }

        echo json_encode([
            'tickets_new'   => $ticketsNew,
            'tasks_new'     => $tasksNew,
            'chat_new'      => $chatNew,
        ]);
        exit;
    }
}
