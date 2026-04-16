<?php

// Rutas de instituciones (login, verificacion, panel)

$router->any('/institution-login', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../app/controllers/InstitutionAuthController.php';
    $controller = new InstitutionAuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->login();
    } else {
        $controller->showLogin();
    }
});

$router->any('/institution-verify', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../app/controllers/InstitutionAuthController.php';
    $controller = new InstitutionAuthController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->verify();
    } else {
        $controller->showVerify();
    }
});

$router->get('/institution-logout', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../app/controllers/InstitutionAuthController.php';
    $controller = new InstitutionAuthController();
    $controller->logout();
});

$router->get('/institution/dashboard', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['institution_id'])) {
        header('Location: ' . url('/institution-login'));
        exit;
    }
    require_once __DIR__ . '/../app/controllers/InstitucionController.php';
    $controller = new InstitucionController();
    $controller->dashboard();
});

$router->get('/institution/students', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['institution_id'])) {
        header('Location: ' . url('/institution-login'));
        exit;
    }
    require_once __DIR__ . '/../app/controllers/InstitucionController.php';
    $controller = new InstitucionController();
    $controller->students();
});
