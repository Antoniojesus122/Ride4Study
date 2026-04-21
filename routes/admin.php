<?php

// Rutas de administracion

// Helper: verificar que el usuario es admin
$requireAdmin = function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
        header('Location: ' . url('/login'));
        exit;
    }
};

// /admin redirige al dashboard
$router->get('/admin', function () use ($requireAdmin) {
    $requireAdmin();
    header('Location: ' . url('/admin/dashboard'));
    exit;
});

$router->any('/admin/dashboard', function () use ($requireAdmin) {
    $requireAdmin();
    $controller = new AdminDashboardController();
    $controller->index();
});

$router->any('/admin/users', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminUserController.php';
    $controller = new AdminUserController();
    $action = $_POST['action'] ?? $_GET['action'] ?? null;
    match ($action) {
        'approve' => $controller->approveVerification(),
        'reject' => $controller->rejectVerification(),
        'update_role' => $controller->updateRole(),
        'delete' => $controller->deleteUser(),
        'ban' => $controller->banUser(),
        'unban' => $controller->unbanUser(),
        'export_csv' => $controller->exportCsv(),
        default => $controller->index(),
    };
});

$router->any('/admin/reports', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $db = $database->connect();
    $controller = new ReportController($db);
    $tab = $_GET['tab'] ?? Report::TIPO_USUARIO;

    // Historial de usuarios
    if (isset($_GET['ajax']) && $_GET['ajax'] === 'history' && isset($_GET['userId'])) {
        header('Content-Type: application/json');
        echo json_encode($controller->getUserHistory((int)$_GET['userId']));
        exit;
    }

    // Vista previa del contenido reportado
    if (isset($_GET['ajax']) && $_GET['ajax'] === 'preview' && isset($_GET['tipo'], $_GET['id'])) {
        $controller->previewContent();
        exit;
    }

    // Exportar CSV
    if (($_GET['action'] ?? '') === 'export_csv' && $tab !== 'stats') {
        $controller->exportCsv($tab);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['idReporte'])) {
        switch ($_POST['action']) {
            case 'resolve': $controller->resolve($tab); exit;
            case 'delete': $controller->delete($tab);  exit;
            case 'take': $controller->takeReport($tab); exit;
            case 'release': $controller->releaseReport($tab); exit;
        }
    }

    // Estadisticas
    $stats = $controller->getStats();

    switch ($tab) {
        case Report::TIPO_ANUNCIO: $reportes = $controller->getReportsByType(Report::TIPO_ANUNCIO); break;
        case Report::TIPO_CHAT:    $reportes = $controller->getReportsByType(Report::TIPO_CHAT);    break;
        case 'stats': $reportes = []; break;
        default: $reportes = $controller->getReportsByType(Report::TIPO_USUARIO); $tab = Report::TIPO_USUARIO; break;
    }
    $flashData = getFlash();
    $successMsg = ($flashData && $flashData['type'] === 'success') ? $flashData['message'] : null;
    $errorMsg = ($flashData && $flashData['type'] === 'error') ? $flashData['message'] : null;
    require_once __DIR__ . '/../views/admin/reports.view.php';
});

$router->any('/admin/instituciones', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminInstitucionController.php';
    $controller = new AdminInstitucionController();
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
    match ($action) {
        'create' => $controller->create(),
        'edit' => $controller->edit(),
        'delete' => $controller->delete(),
        'reset_password' => $controller->resetPassword(),
        'toggle_active' => $controller->toggleActive(),
        'export_csv' => $controller->exportCsv(),
        default => $controller->listAll(),
    };
});

$router->any('/admin/ads', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminAdController.php';
    $controller = new AdminAdController();
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
    match ($action) {
        'delete' => $controller->deleteAd(),
        'export_csv' => $controller->exportCsv(),
        default => $controller->listAll(),
    };
});

$router->any('/admin/profile', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminProfileController.php';
    $controller = new AdminProfileController();
    $action = $_POST['action'] ?? $_GET['action'] ?? null;
    match ($action) {
        'update_info' => $controller->updateInfo(),
        'change_password' => $controller->changePassword(),
        default => $controller->index(),
    };
});

// Bandeja de mensajes admin <-> institucion
$router->any('/admin/messages', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminMessageController.php';
    (new AdminMessageController())->index();
});

// Enviar mensaje a una institucion
$router->any('/admin/messages/send', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminMessageController.php';
    (new AdminMessageController())->send();
});

$router->any('/admin/notifications', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminNotificationController.php';
    (new AdminNotificationController())->index();
});

// Envio de notificacion masiva
$router->any('/admin/notifications/send', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminNotificationController.php';
    (new AdminNotificationController())->send();
});

// Vista previa del numero de destinatarios
$router->any('/admin/notifications/preview', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminNotificationController.php';
    (new AdminNotificationController())->preview();
});

$router->any('/admin/logs', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminLogController.php';
    $controller = new AdminLogController();
    $controller->index();
});

$router->any('/admin/config', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminConfigController.php';
    $controller = new AdminConfigController();
    $action = $_POST['action'] ?? $_GET['action'] ?? null;
    match ($action) {
        'update' => $controller->update(),
        default => $controller->index(),
    };
});

$router->any('/admin/premium', function () use ($requireAdmin) {
    $requireAdmin();
    require_once __DIR__ . '/../app/controllers/admin/AdminPremiumController.php';
    $controller = new AdminPremiumController();
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
    match ($action) {
        'grant' => $controller->grant(),
        'revoke' => $controller->revoke(),
        'search' => $controller->searchUsers(),
        default => $controller->listAll(),
    };
});
