<?php
// app/core/Router.php
class Router {
    protected $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function dispatch() {
        $url = $_GET['url'] ?? 'auth/login';
        $url = trim($url, '/');
        $parts = explode('/', $url);

        $controllerName = ucfirst($parts[0]) . 'Controller';
        $method = $parts[1] ?? 'index';
        $params = array_slice($parts, 2);

        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            header("HTTP/1.0 404 Not Found");
            echo "Controlador no encontrado: $controllerName";
            exit;
        }

        require_once $controllerFile;
        $controller = new $controllerName($this->pdo);

        if (!method_exists($controller, $method)) {
            header("HTTP/1.0 404 Not Found");
            echo "Método no encontrado: $method";
            exit;
        }

        call_user_func_array([$controller, $method], $params);
    }
}

if (login_user($email, $pass)) {
    redirectTo('dashboard'); // o header('Location: ' . BASE_URL . '/index.php?page=dashboard');
}
