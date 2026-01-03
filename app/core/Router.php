<?php

class Router {
    public static function dispatch($url) {
        switch ($url) {

            case '':
            case 'login':
                require '../app/controllers/AuthController.php';
                break;

            case 'dashboard':
                Auth::check();
                require '../app/controllers/DashboardController.php';
                break;

            case 'chat':
                Auth::check();
                require '../app/controllers/ChatController.php';
                break;

            case 'tasks':
                Auth::check();
                require '../app/controllers/TaskController.php';
                break;

            case 'logout':
                session_destroy();
                header('Location: /login');
                break;

            default:
                http_response_code(404);
                echo '404 – Page not found';
        }
    }
}
