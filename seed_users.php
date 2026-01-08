<?php
require __DIR__ . '/config/database.php';

function addUser(PDO $pdo, string $name, string $email, string $pass, string $role): void {
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // Ajustează dacă la tine coloanele se numesc altfel (ex: username în loc de name)
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $hash, $role]);
}

try {
    addUser($pdo, 'Admin', 'admin@local.test', 'admin1234', 'admin');
    addUser($pdo, 'Client', 'client@local.test', 'client1234', 'client');

    echo "OK\n\nAdmin: admin@local.test / admin1234\nClient: client@local.test / client1234\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage();
}
