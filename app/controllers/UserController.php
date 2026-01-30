<?php

class UserController
{
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

    public function index()
    {
        global $pdo;

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

        require __DIR__ . '/../views/admin/users/index.php';
    }

    public function store()
    {
        global $pdo;

        $redirect = (($_POST['redirect'] ?? '') === 'settings')
            ? (BASE_URL . "admin/settings&tab=users")
            : (BASE_URL . "admin/users");

        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($name === '' || $email === '') {
            $_SESSION['flash_error'] = 'Numele si email-ul sunt obligatorii.';
            header("Location: " . $redirect);
            exit;
        }

        // Email reuse + soft-delete support:
        // - dacă există un user ACTIV cu acest email -> blocăm
        // - dacă există un user INACTIV (is_active=0) -> îl reactivăm (UX corect: poți "reînregistra" emailul)
        // Dacă DB e veche și nu are is_active, o adăugăm automat.
        try {
            $stmt = $pdo->prepare("SELECT id, is_active FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                $pdo->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
                $stmt = $pdo->prepare("SELECT id, is_active FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                throw $e;
            }
        }

        // doar 2 roluri: client/admin
        $role = $_POST['role'] ?? 'client';

        // securitate: permitem doar client / admin
        if (!in_array($role, ['client', 'admin'], true)) {
            $role = 'client';
        }


        // setăm o parolă random (userul își va seta parola prin invite link)
        $randomHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        // Reactivare dacă există un user inactiv cu acest email
        if ($existing && (int)($existing['is_active'] ?? 1) === 0) {
            $userId = (int)$existing['id'];

            // reactivăm contul și actualizăm datele de bază
            $stmt = $pdo->prepare("UPDATE users SET name=?, role=?, password=?, is_active=1 WHERE id=?");
            $stmt->execute([$name, $role, $randomHash, $userId]);

            // curățăm token-urile vechi (opțional, dar sănătos)
            $pdo->prepare("DELETE FROM password_resets WHERE user_id=?")->execute([$userId]);

            $reactivated = true;
        } elseif ($existing) {
            // există și e activ -> blocăm
            $_SESSION['flash_error'] = 'Email deja este înregistrat';
            header("Location: " . $redirect);
            exit;
        } else {
            // cont nou
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([$name, $email, $randomHash, $role]);
            $userId = (int)$pdo->lastInsertId();
            $reactivated = false;
        }

        // generează invite imediat
        try {
            $link = $this->createInviteLink($userId);
            $_SESSION['invite_link'] = $link;
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Nu pot genera invite link (DB). Verifică dacă ai rulat init_db.sql.';
            header("Location: " . $redirect);
            exit;
        }
        $_SESSION['flash_success'] = $reactivated
            ? 'Cont reactivat. Copiază linkul de invitatie si trimite-l userului.'
            : 'Client created. Copy invite link and send it to the client.';
        header("Location: " . $redirect);
        exit;
    }

    public function invite()
    {
        global $pdo;

        $redirect = (($_POST['redirect'] ?? '') === 'settings')
            ? (BASE_URL . "admin/settings&tab=users")
            : (BASE_URL . "admin/users");

        Auth::requireRole(['admin']); // doar admin poate genera invite

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            header("Location: " . $redirect);
            exit;
        }

        // opțional: nu genera invite pentru contul cu care ești logat
        if ($userId === Auth::id()) {
            $_SESSION['flash_error'] = 'Nu poți genera invite pentru contul curent.';
            header("Location: " . $redirect);
            exit;
        }

        // verificăm că userul există
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id=? LIMIT 1");
        $stmt->execute([$userId]);
        if (!$stmt->fetchColumn()) {
            $_SESSION['flash_error'] = 'User not found.';
            header("Location: " . $redirect);
            exit;
        }

        try {
            $link = $this->createInviteLink($userId);
            $_SESSION['invite_link'] = $link;
            $_SESSION['flash_success'] = 'Invite link generated.';
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Nu pot genera invite link (DB). Verifică dacă ai rulat init_db.sql.';
        }
        header("Location: " . $redirect);
        exit;
    }


    private function createInviteLink(int $userId): string
    {
        global $pdo;

        $this->ensurePasswordResetsTable();

        // token în clar (îl dăm clientului)
        $token = bin2hex(random_bytes(32));
        // în DB salvăm doar hash
        $tokenHash = hash('sha256', $token);

        // valabil 24h
        $expiresAt = (new DateTime('+24 hours'))->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
        $ok = $stmt->execute([$userId, $tokenHash, $expiresAt]);
        if (!$ok) {
            // If PDO runs in silent mode, don't return a broken link.
            throw new RuntimeException('Cannot generate invite link (DB)');
        }

        // BASE_URL la tine include deja ?route=
        return BASE_URL . "set-password&token=" . $token;
    }

    public function setPasswordForm()
    {
        global $pdo;

        $this->ensurePasswordResetsTable();

        $token = (string)($_GET['token'] ?? '');
        if ($token === '') {
            $_SESSION['flash_error'] = 'Link invalid sau expirat. Cere unul nou.';
            header('Location: ' . BASE_URL . 'forgot-password');
            exit;
        }

        // Validate token early (better UX than showing a form that will fail on submit)
        $tokenHash = hash('sha256', $token);
        // Use PHP time for expiry comparison to avoid MySQL/PHP timezone mismatches on local setups.
        $now = (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("
            SELECT pr.id
            FROM password_resets pr
            WHERE (pr.token_hash = ? OR pr.token_hash = ?)
              AND pr.used_at IS NULL
              AND pr.expires_at > ?
            LIMIT 1
        ");
        $stmt->execute([$tokenHash, $token, $now]);
        $ok = (bool)$stmt->fetchColumn();

        if (!$ok) {
            $_SESSION['flash_error'] = 'Link invalid sau expirat. Cere unul nou.';
            header('Location: ' . BASE_URL . 'forgot-password');
            exit;
        }

        require __DIR__ . '/../views/account/set_password.php';
    }

    public function setPasswordSubmit()
    {
        global $pdo;

        $this->ensurePasswordResetsTable();

        // Token can come either from POST (hidden field) or from the query string.
        // Keeping both makes the flow more robust.
        $token = (string)($_POST['token'] ?? ($_GET['token'] ?? ''));
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

        // Backward/edge compatibility:
        // - token in the URL is hex (64 chars), and older rows might have stored the raw token in token_hash.
        // - newer rows store sha256(token) in token_hash.
        $tokenHash = hash('sha256', $token);

        // Use PHP time for expiry comparison to avoid MySQL/PHP timezone mismatches on local setups.
        $now = (new DateTime())->format('Y-m-d H:i:s');

        $stmt = $pdo->prepare("
            SELECT pr.id, pr.user_id
            FROM password_resets pr
            WHERE (pr.token_hash = ? OR pr.token_hash = ?)
              AND pr.used_at IS NULL
              AND pr.expires_at > ?
            ORDER BY pr.id DESC
            LIMIT 1
        ");
        $stmt->execute([$tokenHash, $token, $now]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Don't show a blank technical page. Send user back to the reset request.
            $_SESSION['flash_error'] = 'Link invalid sau expirat. Cere unul nou.';
            header('Location: ' . BASE_URL . 'forgot-password');
            exit;
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

        $redirect = (($_POST['redirect'] ?? '') === 'settings')
            ? (BASE_URL . "admin/settings&tab=users")
            : (BASE_URL . "admin/users");

        Auth::requireRole(['admin']);

        $targetId = (int)($_POST['id'] ?? 0);
        if ($targetId <= 0) {
            http_response_code(400);
            exit('Bad request');
        }

        // nu te poți dezactiva pe tine
        if ($targetId === Auth::id()) {
            $_SESSION['flash_error'] = 'Nu poți dezactiva contul cu care ești logat.';
            header("Location: " . $redirect);
            exit;
        }

        // Soft delete (preferat): is_active = 0.
        // Dacă baza de date e veche și nu are coloana is_active, o adăugăm automat.
        try {
            $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ? LIMIT 1");
            $stmt->execute([$targetId]);
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                $pdo->exec("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1");
                $stmt = $pdo->prepare("UPDATE users SET is_active = 0 WHERE id = ? LIMIT 1");
                $stmt->execute([$targetId]);
            } else {
                throw $e;
            }
        }

        $_SESSION['flash_success'] = 'Utilizator dezactivat.';
        header("Location: " . $redirect);
        exit;
    }
}
