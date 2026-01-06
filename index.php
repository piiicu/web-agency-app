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
require_once __DIR__ . '/app/controllers/AttachmentController.php';
require_once __DIR__ . '/app/controllers/UserController.php';
require_once __DIR__ . '/app/controllers/AvatarController.php';
require_once __DIR__ . '/app/controllers/DashboardController.php';


/* 2️⃣ ROUTE */
$route = $_GET['route'] ?? 'login';

/* 3️⃣ PROTECȚIE (login required) */
$protected = [
    'dashboard',
    'tasks', 'tasks-update', 'tasks-delete', 'tasks-done', 'tasks-favorite',
    'chat', 'chat-poll',
    'admin/dashboard',
    'client/dashboard',
    'ticket-attachment',

    // tickets protected (cleaner)
    'client/tickets', 'client/ticket',
    'admin/tickets', 'admin/ticket',
    'admin/tickets-poll',
    'admin/badges-poll',
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

/* =========================
   DASHBOARDS (role-based)
   ========================= */

// ADMIN dashboard (via controller -> provides $me)
if ($route === 'admin/dashboard' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin','employee','staff']);
    require_once __DIR__ . '/app/controllers/DashboardController.php';
    (new DashboardController())->adminDashboard();
    exit;
}

// CLIENT dashboard
if ($route === 'client/dashboard' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['client']);
    require_once __DIR__ . '/app/controllers/ClientController.php';
    (new ClientController())->dashboard();
    exit;
}

/* =========================
   TASKS (internal)
   ========================= */

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

/* =========================
   CHAT (temporar)
   ========================= */

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

/* =========================
   TICKETS - CLIENT
   ========================= */

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

/* =========================
   TICKETS - ADMIN/STAFF
   ========================= */

if ($route === 'admin/tickets' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new TicketController())->adminIndex();
    exit;
}

/**
 * ✅ NEW: lightweight polling endpoint (auto-refresh counter + latest update)
 */
if ($route === 'admin/tickets-poll' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new TicketController())->adminPoll();
    exit;
}

if ($route === 'admin/badges-poll' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new DashboardController())->badgesPoll();
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

if ($route === 'admin/ticket-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new TicketController())->adminDelete();
    exit;
}

if ($route === 'admin/ticket-restore' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new TicketController())->adminRestore();
    exit;
}

if ($route === 'admin/tickets-bulk-delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new TicketController())->adminBulkDelete();
    exit;
}

if ($route === 'admin/tickets-reorder' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin', 'employee', 'staff']);
    (new TicketController())->adminReorder();
    exit;
}

/* =========================
   ADMIN TABS / PAGES
   ========================= */

if ($route === 'admin/internal-tasks' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin','employee','staff']);
    header("Location: " . BASE_URL . "tasks");
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
    Auth::requireRole(['admin','employee','staff']);
    require_once __DIR__ . '/app/controllers/DashboardController.php';
    (new DashboardController())->settings();
    exit;
}


/* =========================
   ADMIN USERS
   ========================= */

if ($route === 'admin/users' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin']);
    (new UserController())->index();
    exit;
}

if ($route === 'admin/users-create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin']);
    (new UserController())->store();
    exit;
}

if ($route === 'admin/users-invite' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin']);
    (new UserController())->invite();
    exit;
}

/* =========================
   INVITE: SET PASSWORD
   ========================= */

if ($route === 'set-password' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    (new UserController())->setPasswordForm();
    exit;
}

if ($route === 'set-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new UserController())->setPasswordSubmit();
    exit;
}

/* =========================
   PASSWORD CHANGE
   ========================= */

// ADMIN: change own password
if ($route === 'admin/change-password' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['admin']);
    require __DIR__ . '/app/views/admin/change_password.php';
    exit;
}

if ($route === 'admin/change-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin']);
    (new AuthController())->changePassword();
    exit;
}

// CLIENT: account (tabs page)
if ($route === 'client/account' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::requireRole(['client']);
    require_once __DIR__ . '/app/controllers/ClientController.php';
    (new ClientController())->account();
    exit;
}

// CLIENT: update profile
if ($route === 'client/profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['client']);
    require_once __DIR__ . '/app/controllers/ClientController.php';
    (new ClientController())->updateProfile();
    exit;
}

// CLIENT: change password
if ($route === 'client/change-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['client']);
    (new AuthController())->changePassword();
    exit;
}

/* =========================
   DOWNLOADS + AVATAR
   ========================= */

// ATTACHMENT DOWNLOAD (client + admin)
if ($route === 'ticket-attachment' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::check();
    (new AttachmentController())->download();
    exit;
}

// AVATAR (served via PHP)
if ($route === 'avatar' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    Auth::check();
    (new AvatarController())->show();
    exit;
}

/* =========================
   LOGOUT
   ========================= */

if ($route === 'logout' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    (new AuthController())->logout();
    exit;
}


// salvarea profilului admin
if ($route === 'admin/profile' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin','employee','staff']);
    require_once __DIR__ . '/app/controllers/DashboardController.php';
    (new DashboardController())->updateProfile();
    exit;
}

// admin/users-disable
if ($route === 'admin/users-disable' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin']);
    (new UserController())->disable();
    exit;
}

// ADMIN: disable client (soft delete)
if ($route === 'admin/client-disable' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['admin']);
    require_once __DIR__ . '/app/controllers/ClientController.php';
    (new ClientController())->disable();
    exit;
}

// CLIENT: self delete
if ($route === 'client/delete-account' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::requireRole(['client']);
    require_once __DIR__ . '/app/controllers/ClientController.php';
    (new ClientController())->deleteOwnAccount();
    exit;
}


/* 5️⃣ ROUTE SIMPLE → VIEWS */

$routes = [
    'login'     => 'login.php',
];

if (!isset($routes[$route])) {
    http_response_code(404);
    echo '404 - Page not found';
    exit;
}

require __DIR__ . '/app/views/' . $routes[$route];
