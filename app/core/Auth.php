<?php

class Auth {
    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): int {
        return (int)($_SESSION['user']['id'] ?? 0);
    }

    public static function role(): string {
        return (string)($_SESSION['user']['role'] ?? '');
    }

    public static function check(): void {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "login");
            exit;
        }
    }

    public static function requireRole(array $roles): void {
        self::check();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}
