<?php

class Router
{
    private array $routes = [];

    public function get(string $path, $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post(string $path, $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function any(string $path, $handler): void
    {
        $this->routes['GET'][$path]  = $handler;
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch(string $uri, string $method): void
    {
        // Quitar base path
        $base = defined('BASE_PATH') ? BASE_PATH : '';
        $path = '/' . trim(substr($uri, strlen($base)), '/');
        if ($path === '') {
            $path = '/';
        }

        // Buscar ruta exacta
        if (isset($this->routes[$method][$path])) {
            $this->call($this->routes[$method][$path]);
            return;
        }

        // 404
        http_response_code(404);
        require_once __DIR__ . '/../views/errors/404.view.php';
    }

    private function call($handler): void
    {
        if (is_callable($handler)) {
            $handler();
        } elseif (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = new $class();
            $controller->$method();
        }
    }
}
