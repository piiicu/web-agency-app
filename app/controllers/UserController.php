<?php

class UserController
{
    public function index()
    {
        global $pdo;

        $users = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC")
            ->fetchAll(PDO::FETCH_ASSOC);

        $inviteLink = $_SESSION['invite_link'] ?? null;
        unset($_SESSION['invite_link']);

        require __DIR__ . '/../views/admin/users/index.php';
    }

    public function store()
    {
        global $pdo;

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '' || $email === '') {
            $_SESSION['flash_error'] = 'Numele si email-ul sunt obligatorii.';
            header("Location: " . BASE_URL . "admin/users");
            exit;
        }

        // email unic
        $stmt = $pdo->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) {
            $_SESSION['flash_error'] = 'Email deja este înregistrat';
            header("Location: " . BASE_URL . "admin/users");
            exit;
        }

        // doar 2 roluri: client/admin
        $role = $_POST['role'] ?? 'client';

        // securitate: permitem doar client / admin
        if (!in_array($role, ['client', 'admin'], true)) {
            $role = 'client';
        }


        // setăm o parolă random (userul își va seta parola prin invite link)
        $randomHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $randomHash, $role]);
        $userId = (int)$pdo->lastInsertId();

        // generează invite imediat
        $link = $this->createInviteLink($userId);

        $_SESSION['invite_link'] = $link;
        $_SESSION['flash_success'] = 'Client created. Copy invite link and send it to the client.';
        header("Location: " . BASE_URL . "admin/users");
        exit;
    }

    public function invite()
    {
        global $pdo;

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            header("Location: " . BASE_URL . "admin/users");
            exit;
        }

        $stmt = $pdo->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$userId]);
        $role = $stmt->fetchColumn();

        if ($role !== 'client') {
            $_SESSION['flash_error'] = 'Invite links are only for clients.';
            header("Location: " . BASE_URL . "admin/users");
            exit;
        }

        $link = $this->createInviteLink($userId);

        $_SESSION['invite_link'] = $link;
        $_SESSION['flash_success'] = 'Invite link generated.';
        header("Location: " . BASE_URL . "admin/users");
        exit;
    }

    private function createInviteLink(int $userId): string
    {
        global $pdo;

        // token în clar (îl dăm clientului)
        $token = bin2hex(random_bytes(32));
        // în DB salvăm doar hash
        $tokenHash = hash('sha256', $token);

        // valabil 24h
        $expiresAt = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $tokenHash, $expiresAt]);

        // BASE_URL la tine include deja ?route=
        return BASE_URL . "set-password&token=" . $token;
    }

    public function setPasswordForm()
    {
        $token = (string)($_GET['token'] ?? '');
        if ($token === '') {
            http_response_code(400);
            exit('Missing token');
        }

        require __DIR__ . '/../views/account/set_password.php';
    }

    public function setPasswordSubmit()
    {
        global $pdo;

        $token = (string)($_POST['token'] ?? '');
        $pass1 = (string)($_POST['password'] ?? '');
        $pass2 = (string)($_POST['password_confirm'] ?? '');

        if ($token === '' || $pass1 === '' || $pass1 !== $pass2) {
            $_SESSION['flash_error'] = 'Passwords do not match.';
            header("Location: " . BASE_URL . "set-password&token=" . urlencode($token));
            exit;
        }

        if (strlen($pass1) < 6) {
            $_SESSION['flash_error'] = 'Password must be at least 6 characters.';
            header("Location: " . BASE_URL . "set-password&token=" . urlencode($token));
            exit;
        }

        $tokenHash = hash('sha256', $token);

        $stmt = $pdo->prepare("
            SELECT pr.id, pr.user_id
            FROM password_resets pr
            WHERE pr.token_hash = ?
              AND pr.used_at IS NULL
              AND pr.expires_at > NOW()
            ORDER BY pr.id DESC
            LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            http_response_code(400);
            exit('Invalid or expired token.');
        }

        $hash = password_hash($pass1, PASSWORD_DEFAULT);

        $pdo->prepare("UPDATE users SET password=? WHERE id=?")
            ->execute([$hash, (int)$row['user_id']]);

        $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE id=?")
            ->execute([(int)$row['id']]);

        $_SESSION['flash_success'] = 'Password set. You can login now.';
        header("Location: " . BASE_URL . "login");
        exit;
    }

    public function disable()
    {
        global $pdo;

        Auth::requireRole(['admin']);

        $targetId = (int)($_POST['id'] ?? 0);
        if ($targetId <= 0) {
            http_response_code(400);
            exit('Bad request');
        }

        // nu te poți dezactiva pe tine
        if ($targetId === Auth::id()) {
            $_SESSION['flash_error'] = 'Nu poți dezactiva contul cu care ești logat.';
            header("Location: " . BASE_URL . "admin/users");
            exit;
        }

        $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ? LIMIT 1");
        $stmt->execute([$targetId]);

        $_SESSION['flash_success'] = 'Utilizator dezactivat.';
        header("Location: " . BASE_URL . "admin/users");
        exit;
    }
}
