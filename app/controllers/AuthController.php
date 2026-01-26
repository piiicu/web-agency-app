<?php

class AuthController
{
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

        // redirect smart
        if ($user['role'] === 'client') {
            header("Location: " . BASE_URL . "client/dashboard");
        } else {
            header("Location: " . BASE_URL . "admin/dashboard");
        }
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
