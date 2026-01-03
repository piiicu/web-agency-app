<?php

class AuthController
{
    public function login()
    {
        global $pdo;

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id'   => $user['id'],
                'name' => $user['name'],
                'role' => $user['role']
            ];

            header("Location: " . BASE_URL . "dashboard");
            exit;
        }

        header("Location: " . BASE_URL . "login");
        exit;
    }
}
