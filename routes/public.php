<?php

// Rutas publicas (no requieren autenticacion)

// Pagina de inicio (landing page)
$router->get('/', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (isset($_SESSION['user_id'])) {
        $role = (int)($_SESSION['user_role'] ?? 0);
        header('Location: ' . url($role === 1 ? '/admin/dashboard' : '/dashboard'));
        exit;
    }
    require_once __DIR__ . '/../views/public/landing.view.php';
});

// Cambio de idioma
$router->get('/set-lang', function () {
    $lang = $_GET['lang'] ?? 'es';
    if (!in_array($lang, ['es', 'en'], true)) {
        $lang = 'es';
    }
    setcookie('lang', $lang, [
        'expires'  => time() + (365 * 24 * 60 * 60),
        'path'     => '/',
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);
    $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
    header('Location: ' . $referer);
    exit;
});

// Pagina publica de instituciones (info + formulario de contacto)
$router->any('/instituciones', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../app/controllers/InstitutionContactController.php';
    $controller = new InstitutionContactController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'contact') {
        $controller->sendContactEmail();
    } else {
        $controller->index();
    }
});

// Soporte
$router->any('/support', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $controller = new SupportController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
        $controller->sendSupportEmail();
    } else {
        $controller->index();
    }
});

// Paginas legales
$router->get('/privacy', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../views/public/privacy.view.php';
});

$router->get('/terms', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../views/public/terms.view.php';
});

$router->get('/cookies', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../views/public/cookies.view.php';
});

$router->get('/safety', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__ . '/../views/public/safety.view.php';
});
