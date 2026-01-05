<?php

class AuthController
{
    public function login()
    {
        global $pdo;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {

                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ];

                // 🔽 AICI este redirect-ul smart
                $role = Auth::role();

                if (in_array($role, ['admin', 'employee', 'staff'], true)) {
                    header("Location: " . BASE_URL . "admin/dashboard");
                } else {
                    header("Location: " . BASE_URL . "client/dashboard");
                }
                exit;
            }
        }

        require __DIR__ . '/../views/login.php';
    }
}
