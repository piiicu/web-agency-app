<?php

$host = '127.0.0.1';     // IMPORTANT: NU localhost
$port = 3306;
$db   = 'web_agency_app'; // SCHIMBĂ dacă DB-ul tău are alt nume
$user = 'root';
$pass = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('DB connection failed: ' . $e->getMessage());
}
