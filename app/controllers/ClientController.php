<?php

class ClientController
{
    public function index()
    {
        global $pdo;

        // doar clientii
        $stmt = $pdo->query("
            SELECT id, name, email, company, phone
            FROM users
            WHERE role = 'client'
            ORDER BY id DESC
        ");
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/clients/index.php';
    }

    public function show()
    {
        global $pdo;

        $clientId = (int)($_GET['id'] ?? 0);
        if ($clientId <= 0) { http_response_code(400); exit('Bad client'); }

        $stmt = $pdo->prepare("
            SELECT id, name, email, role, company, phone, address
            FROM users
            WHERE id = ? AND role = 'client'
            LIMIT 1
        ");
        $stmt->execute([$clientId]);
        $client = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$client) { http_response_code(404); exit('Client not found'); }

        // tichetele clientului
        $stmt = $pdo->prepare("
            SELECT id, subject, status, priority, updated_at, created_at
            FROM tickets
            WHERE client_id = ?
            ORDER BY updated_at DESC, id DESC
        ");
        $stmt->execute([$clientId]);
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/admin/clients/show.php';
    }
}
