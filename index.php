<?php
session_start();

/* 1️⃣ CONFIG & CONTROLLERS */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/TaskController.php';

/* 2️⃣ ROUTE */
$route = $_GET['route'] ?? 'login';

/* 3️⃣ PROTECȚIE */
$protected = ['dashboard', 'tasks', 'chat'];

if (in_array($route, $protected) && !isset($_SESSION['user'])) {
    header("Location: " . BASE_URL . "login");
    exit;
}

/* 4️⃣ ROUTE CU LOGICĂ (CONTROLLERE) */

// LOGIN - AUTH
if ($route === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    (new AuthController())->login();
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

// if ($route === 'tasks-move' && $_SERVER['REQUEST_METHOD'] === 'POST') {
//     (new TaskController())->move();
//     exit;
// }

// if ($route === 'tasks-reorder' && $_SERVER['REQUEST_METHOD'] === 'POST') {
//     (new TaskController())->reorder();
//     exit;
// }


/* 5️⃣ ROUTE SIMPLE → VIEWS */

$routes = [
    'login'     => 'login.php',
    'dashboard' => 'dashboard.php',
    'chat'      => 'chat.php',
    'logout'    => 'logout.php'
];

if (!isset($routes[$route])) {
    http_response_code(404);
    echo '404 - Page not found';
    exit;
}

require __DIR__ . '/app/views/' . $routes[$route];
