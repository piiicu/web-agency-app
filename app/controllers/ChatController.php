<?php
require '../app/core/DB.php';

$db = DB::connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $db->prepare("INSERT INTO messages (user_id, message) VALUES (?,?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['message']]);
    exit;
}

$messages = $db->query("
    SELECT m.message, u.name 
    FROM messages m 
    JOIN users u ON u.id = m.user_id
    ORDER BY m.id DESC
")->fetchAll();

require '../app/views/chat.php';
