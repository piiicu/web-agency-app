<?php
session_start();

/* 1️⃣ CONFIG & CONTROLLERS */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/app/core/Auth.php';

require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/TaskController.php';
require_once __DIR__ . '/app/controllers/ChatController.php';
require_once __DIR__ . '/app/controllers/TicketController.php';

/* 2️⃣ ROUTE */
$route = $_GET['route'] ?? 'login';

/* 3️⃣ PROTECȚIE (login required) */
$protected = [
    'dashboard',
    'tasks', 'tasks-update', 'tasks-delete', 'tasks-done', 'tasks-favorite',
    'chat', 'chat-poll',
    'admin/dashboard',
    'client/dashboard',
];

if (in_array($route, $protected, true) && !isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "login");
    exit;
}

/* ✅ PASUL 2D — PROTECȚIE ROLE-BASED PENTRU TASKS (doar staff/admin) */
$adminOnlyRoutes = [
    'tasks', 'tasks-update', 'tasks-delete', 'tasks-done', 'tasks-favorite'
];

if (in_array($route, $adminOnlyRoutes, true)) {
    Auth::requireRole(['admin', 'employee', 'staff']);
}

/* 4️⃣ ROUTE CU LOGICĂ (CONTROLLERE / VIEWS) */

// LOGIN - AUTH
if ($route === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AuthController())->login();
    exit;
}

// DASHBOARDS (role-based)
if ($route === 'admin/dashboard') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    require __DIR__ . '/app/views/admin/dashboard.php';
    exit;
}

if ($route === 'client/dashboard') {
    Auth::requireRole(['client']);
    require __DIR__ . '/app/views/client/dashboard.php';
    exit;
}

// TASKS
if ($route === 'tasks' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    (new TaskController())->index();
    exit;
}

if ($route === 'tasks' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new TaskController())->store();
    exit;
}

if ($route === 'tasks-update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new TaskController())->update();
    exit;
}

if ($route === 'tasks-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new TaskController())->delete();
    exit;
}

if ($route === 'tasks-done' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new TaskController())->toggleDone();
    exit;
}

if ($route === 'tasks-favorite' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new TaskController())->toggleFavorite();
    exit;
}

// CHAT (momentan accesibil atât client cât și staff/admin)
if ($route === 'chat' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    (new ChatController())->index();
    exit;
}

if ($route === 'chat' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new ChatController())->store();
    exit;
}

if ($route === 'chat-poll' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    (new ChatController())->poll();
    exit;
}

// TICKETS - CLIENT
if ($route === 'client/tickets' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['client']);
    (new TicketController())->clientIndex();
    exit;
}

if ($route === 'client/tickets-create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['client']);
    (new TicketController())->clientStore();
    exit;
}

if ($route === 'client/ticket' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['client']);
    (new TicketController())->clientShow();
    exit;
}

if ($route === 'client/ticket-message' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['client']);
    (new TicketController())->clientAddMessage();
    exit;
}

// TICKETS - ADMIN/STAFF
if ($route === 'admin/tickets' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new TicketController())->adminIndex();
    exit;
}

if ($route === 'admin/ticket' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new TicketController())->adminShow();
    exit;
}

if ($route === 'admin/ticket-message' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new TicketController())->adminAddMessage();
    exit;
}

if ($route === 'admin/ticket-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new TicketController())->adminUpdateStatus();
    exit;
}

// ADMIN TABS
if ($route === 'admin/internal-tasks') {
    Auth::requireRole(['admin']);
    header("Location: " . BASE_URL . "tasks"); // reuse tasks ca internal tasks
    exit;
}

if ($route === 'admin/clients' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin']);
    require_once __DIR__ . '/app/controllers/ClientController.php';
    (new ClientController())->index();
    exit;
}

if ($route === 'admin/client' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin']);
    require_once __DIR__ . '/app/controllers/ClientController.php';
    (new ClientController())->show();
    exit;
}

if ($route === 'admin/settings' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin']);
    require __DIR__ . '/app/views/admin/settings.php';
    exit;
}


/* 5️⃣ ROUTE SIMPLE → VIEWS */

$routes = [
    'login'     => 'login.php',
    // 'dashboard' => 'dashboard.php', // recomand să nu-l mai folosești (folosim admin/client dashboards)
    'logout'    => 'logout.php'
];

if (!isset($routes[$route])) {
    http_response_code(404);
    echo '404 - Page not found';
    exit;
}

require __DIR__ . '/app/views/' . $routes[$route];
