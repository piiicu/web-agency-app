<?php

class DashboardController
{
    public function adminDashboard()
    {
        global $pdo;

        Auth::requireRole(['admin','employee','staff']);

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

        Auth::requireRole(['admin','employee','staff']);

        $userId = Auth::id();
        $stmt = $pdo->prepare("SELECT id, name, email, phone, avatar FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$userId]);
        $me = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$me) {
            http_response_code(404);
            exit('User not found');
        }

        require __DIR__ . '/../views/admin/settings.php';
    }

    public function updateProfile()
    {
        global $pdo;

        Auth::requireRole(['admin','employee','staff']);

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
}
