<?php

class ClientController
{
    public function index()
    {
        global $pdo;

        // doar clientii
        $stmt = $pdo->query("
            SELECT id, name, email, company, phone
            FROM users
            WHERE role = 'client'
            ORDER BY id DESC
        ");
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/clients/index.php';
    }

    public function show()
    {
        global $pdo;

        $clientId = (int)($_GET['id'] ?? 0);
        if ($clientId <= 0) {
            http_response_code(400);
            exit('Bad client');
        }

        $stmt = $pdo->prepare("
            SELECT id, name, email, role, company, phone, address
            FROM users
            WHERE id = ? AND role = 'client'
            LIMIT 1
        ");
        $stmt->execute([$clientId]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            http_response_code(404);
            exit('Client not found');
        }

        // tichetele clientului
        $stmt = $pdo->prepare("
            SELECT id, subject, status, priority, updated_at, created_at
            FROM tickets
            WHERE client_id = ?
            ORDER BY updated_at DESC, id DESC
        ");
        $stmt->execute([$clientId]);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/clients/show.php';
    }

    public function profile()
    {
        global $pdo;
        $userId = Auth::id();

        $stmt = $pdo->prepare("SELECT id, name, email, company, phone, address, avatar FROM users WHERE id=? AND role='client' LIMIT 1");
        $stmt->execute([$userId]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            http_response_code(404);
            exit('Client not found');
        }

        require __DIR__ . '/../views/client/profile.php';
    }

    public function updateProfile()
    {
        global $pdo;
        $userId = Auth::id();

        $company = trim($_POST['company'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');

        // avatar upload (optional)
        $avatarFile = null;
        if (!empty($_FILES['avatar']) && ($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $tmp  = $_FILES['avatar']['tmp_name'];
            $name = $_FILES['avatar']['name'];
            $type = $_FILES['avatar']['type'];
            $size = (int)$_FILES['avatar']['size'];

            // max 3MB
            if ($size <= 3 * 1024 * 1024) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                if (in_array($type, $allowed, true)) {
                    $dir = __DIR__ . '/../../uploads/avatars/';
                    if (!is_dir($dir)) mkdir($dir, 0777, true);

                    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
                    $stored = bin2hex(random_bytes(12)) . '_' . $safeName;

                    if (move_uploaded_file($tmp, $dir . $stored)) {
                        $avatarFile = $stored;
                    }
                }
            }
        }

        if ($avatarFile !== null) {
            $stmt = $pdo->prepare("UPDATE users SET company=?, phone=?, address=?, avatar=? WHERE id=? AND role='client'");
            $stmt->execute([$company, $phone, $address, $avatarFile, $userId]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET company=?, phone=?, address=? WHERE id=? AND role='client'");
            $stmt->execute([$company, $phone, $address, $userId]);
        }

        $_SESSION['flash_success'] = 'Profile updated.';
        header("Location: " . BASE_URL . "client/dashboard");
        exit;
    }

    public function account()
    {
        global $pdo;
        $userId = Auth::id();

        $stmt = $pdo->prepare("SELECT id, name, email, company, phone, address, avatar FROM users WHERE id=? AND role='client' LIMIT 1");
        $stmt->execute([$userId]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            http_response_code(404);
            exit('Client not found');
        }

        require __DIR__ . '/../views/client/account.php';
    }

    public function dashboard()
    {
        global $pdo;

        $userId = Auth::id();

        $stmt = $pdo->prepare("
        SELECT id, name, email, company, phone, address, avatar
        FROM users
        WHERE id = ? AND role='client'
        LIMIT 1
        ");

        $stmt->execute([$userId]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$client) {
            http_response_code(404);
            exit('Client not found');
        }

        require __DIR__ . '/../views/client/dashboard.php';
    }
}
