<?php

class AuthController
{
    /**
     * Ensure the password_resets table exists (older DB installs may miss it).
     * Keeps the app working on local without forcing a full re-import.
     */
    private function ensurePasswordResetsTable(): void
    {
        global $pdo;

        $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
          id INT AUTO_INCREMENT PRIMARY KEY,
          user_id INT NOT NULL,
          token_hash CHAR(64) NOT NULL,
          expires_at DATETIME NOT NULL,
          used_at DATETIME NULL,
          created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          UNIQUE KEY uniq_token_hash (token_hash),
          KEY idx_user_id (user_id),
          KEY idx_expires_used (expires_at, used_at),
          CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
    public function login()
    {
        global $pdo;

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['flash_error'] = 'Email sau parolă greșită';
            header("Location: " . BASE_URL . "login");
            exit;
        }


        // 🔒 User dezactivat
        if (isset($user['is_active']) && (int)$user['is_active'] === 0) {
            $_SESSION['flash_error'] = 'Cont dezactivat. Contactează administratorul.';
            header("Location: " . BASE_URL . "login");
            exit;
        }

        if (!password_verify($password, $user['password'])) {
            $_SESSION['flash_error'] = 'Email sau parolă greșită';
            header("Location: " . BASE_URL . "login");
            exit;
        }

        $_SESSION['user'] = [
            'id'    => $user['id'],
            'role'  => $user['role'],
            'name'  => $user['name'],
            'email' => $user['email'],
        ];

        // Remember me (simple): persist the PHP session cookie for 30 days.
        // This keeps the user logged in on the same device/browser.
        if (!empty($_POST['remember'])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                time() + (60 * 60 * 24 * 30),
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // redirect smart
        if ($user['role'] === 'client') {
            header("Location: " . BASE_URL . "client/dashboard");
        } else {
            header("Location: " . BASE_URL . "admin/dashboard");
        }
        exit;
    }


    /**
     * Forgot password (local-friendly): generate a password reset link.
     * On production you can email this link; on local we display it.
     */
    public function forgotPasswordSubmit()
    {
        global $pdo;

        $email = trim($_POST['email'] ?? '');
        if ($email === '') {
            $_SESSION['flash_error'] = 'Introdu emailul.';
            header('Location: ' . BASE_URL . 'forgot-password');
            exit;
        }

        // Find user (do not disclose whether an email exists too loudly)
        $stmt = $pdo->prepare('SELECT id, is_active FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && isset($user['is_active']) && (int)$user['is_active'] === 0) {
            $_SESSION['flash_error'] = 'Cont dezactivat. Contactează administratorul.';
            header('Location: ' . BASE_URL . 'forgot-password');
            exit;
        }

        if ($user) {
            $userId = (int)$user['id'];

            // Make sure the table exists even on older installs
            $this->ensurePasswordResetsTable();

            // create reset token (same table as invites)
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

            $stmtIns = $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
            $ok = $stmtIns->execute([$userId, $tokenHash, $expiresAt]);
            if (!$ok) {
                // If PDO is in silent mode, avoid showing a link that won't work.
                $_SESSION['flash_error'] = 'Nu pot genera link-ul acum (DB). Încearcă din nou.';
                header('Location: ' . BASE_URL . 'forgot-password');
                exit;
            }

            $_SESSION['reset_link'] = BASE_URL . 'set-password&token=' . $token;
        }

        // Same success message whether user exists or not (basic privacy)
        $_SESSION['flash_success'] = 'Dacă există un cont cu acest email, am generat un link de resetare.';
        header('Location: ' . BASE_URL . 'forgot-password');
        exit;
    }


    public function changePassword()
    {
        global $pdo;

        $userId = Auth::id();
        $current = (string)($_POST['current_password'] ?? '');
        $new1 = (string)($_POST['new_password'] ?? '');
        $new2 = (string)($_POST['new_password_confirm'] ?? '');

        // unde redirectăm după eroare/succes (în funcție de cine a trimis formularul)
        // Adminul schimbă parola direct din Settings (tab-ul #password)
        $fallback = (Auth::role() === 'admin')
            ? (BASE_URL . 'admin/settings#password')
            : (BASE_URL . 'client/account#password');


        if ($new1 === '' || $new1 !== $new2) {
            $_SESSION['flash_error'] = 'Parolele noi nu coincid.';
            header("Location: " . $fallback);
            exit;
        }

        if (strlen($new1) < 6) {
            $_SESSION['flash_error'] = 'Parola trebuie să aibă minim 6 caractere.';
            header("Location: " . $fallback);
            exit;
        }

        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($current, $row['password'])) {
            $_SESSION['flash_error'] = 'Parola curentă este greșită.';
            header("Location: " . $fallback);
            exit;
        }

        $hash = password_hash($new1, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);

        $_SESSION['flash_success'] = 'Parola a fost schimbată.';
        header("Location: " . $fallback);
        exit;
    }

    public function logout()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        header("Location: " . BASE_URL . "login");
        exit;
    }
}
