<?php

// Entry Point

// Cargar helpers para definir rutas y generar URLs
require_once __DIR__ . '/app/helpers.php';

// Autoload para cargar controladores y modelos automáticamente
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/app/controllers/' . $class . '.php',
        __DIR__ . '/app/controllers/admin/' . $class . '.php',
        __DIR__ . '/app/models/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Router
require_once __DIR__ . '/app/Router.php';
$router = new Router();

// Registrar rutas
require_once __DIR__ . '/routes.php';

// Manejar la solicitud
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);
