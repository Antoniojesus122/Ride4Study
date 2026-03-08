<?php

// Definición de rutas para la aplicación web 
// Cada ruta se asocia a un controlador específico que maneja la lógica de esa sección

// Página de inicio (landing page)
$router->get('/', function () {
    session_start();
    if (isset($_SESSION['user_id'])) {
        $role = $_SESSION['user_role'] ?? null;
        if (in_array((int)$role, [1, 3], true)) {
            header('Location: ' . url('/admin'));
        } else {
            header('Location: ' . url('/dashboard'));
        }
        exit;
    }
    require_once __DIR__ . '/views/public/landing.view.php';
});

// Páginas de autenticación
$router->any('/login', [AuthController::class, 'login']);
$router->any('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->any('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->any('/reset-password', [AuthController::class, 'resetPassword']);

// Dashboard, para usuarios logeados
$router->any('/dashboard', [RideController::class, 'index']);

// Gestión de viajes
$router->any('/publish', function () { // Publicar nuevo viaje
    $controller = new RideController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->store();
    } else {
        $controller->create();
    }
});

$router->any('/edit-ride', function () { // Editar viaje
    $controller = new RideController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->update();
    } else {
        $controller->edit();
    }
});

$router->get('/delete-ride', [RideController::class, 'delete']); // Eliminar viaje
$router->get('/my-rides', [RideController::class, 'myRides']); // Ver viajes propios
$router->any('/reserve', [RideController::class, 'reserve']); // Reservar
$router->post('/manage-reservation', [RideController::class, 'manageRequest']); // Aceptar/rechazar reservas
$router->any('/cancel-reservation', [RideController::class, 'cancelReservation']); // Cancelar reserva
$router->post('/toggle-featured', [RideController::class, 'toggleFeatured']); // Destacar anuncio (premium)

// Perfil de usuario
$router->any('/profile', function () {
    $controller = new UserController();
    $action = $_GET['action'] ?? null;
    match ($action) {
        'update'         => $controller->update(),
        'change_password' => $controller->changePassword(),
        'verify'         => $controller->verify(),
        'update_privacy' => $controller->updatePrivacy(),
        default          => $controller->index(),
    };
});

// Mensajes y chat
$router->any('/messages', function () {
    $controller = new MessageController();
    if (isset($_GET['action']) && $_GET['action'] === 'delete_conversation') {
        $controller->deleteConversation();
    } else {
        $controller->index();
    }
});

$router->any('/chat', function () {
    $controller = new MessageController();
    $action = $_GET['action'] ?? null;
    match ($action) {
        'send'           => $controller->send(),
        'edit'           => $controller->edit(),
        'delete'         => $controller->delete(),
        'fetch_messages' => $controller->fetchMessages(),
        'offer_ride'     => $controller->offerRide(),
        default          => $controller->chat(),
    };
});

// Valoraciones
$router->any('/rating', function () {
    $controller = new RatingController();
    $action = $_GET['action'] ?? null;
    if ($action === 'reply' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->submitReply();
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->submit();
    } elseif (isset($_GET['viaje'])) {
        $controller->showRatingForm();
    } else {
        header('Location: ' . url('/dashboard'));
        exit;
    }
});

// Reportes (usuario, anuncio, chat)
$router->post('/report', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'No autenticado.']);
        exit;
    }
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $db = $database->connect();
    $report = new Report($db);
    $tipo    = $_POST['tipo']    ?? '';
    $mensaje = trim($_POST['mensaje'] ?? '');
    $idUsuarioReportado = !empty($_POST['idUsuarioReportado']) ? (int)$_POST['idUsuarioReportado'] : null;
    $idAnuncio          = !empty($_POST['idAnuncio'])          ? (int)$_POST['idAnuncio']          : null;
    $idChat             = !empty($_POST['idChat'])             ? (int)$_POST['idChat']             : null;
    if (!in_array($tipo, ['usuario', 'anuncio', 'chat']) || empty($mensaje)) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
        exit;
    }
    $ok = $report->createReport($tipo, $idUsuarioReportado, $idAnuncio, $idChat, (int)$_SESSION['user_id'], $mensaje);
    echo json_encode(['success' => $ok, 'message' => $ok ? 'Reporte enviado. Gracias.' : 'No se pudo enviar el reporte.']);
    exit;
});

// Notificaciones dentro de la aplicación web
$router->any('/notifications', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false]);
        exit;
    }
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $db = $database->connect();
    $notif = new Notification($db);
    $userId = (int)$_SESSION['user_id'];
    $action = $_GET['action'] ?? $_POST['action'] ?? 'list';
    if ($action === 'mark_read' && isset($_POST['id'])) {
        $ok = $notif->markRead((int)$_POST['id'], $userId);
        echo json_encode(['success' => $ok]);
    } elseif ($action === 'mark_all_read') {
        $ok = $notif->markAllRead($userId);
        echo json_encode(['success' => $ok]);
    } else {
        $items = $notif->getUnread($userId, 15);
        echo json_encode(['success' => true, 'notifications' => $items]);
    }
    exit;
});

// Premium y pagos con Stripe
$router->any('/premium', function () {
    require_once __DIR__ . '/services/StripeService.php';
    $controller = new PremiumController();
    $action = $_GET['action'] ?? null;
    match ($action) {
        'checkout' => $controller->checkout(),
        'success'  => $controller->success(),
        'cancel'   => $controller->cancel(),
        default    => $controller->index(),
    };
});

$router->post('/webhook/stripe', function () {
    require_once __DIR__ . '/services/StripeService.php';
    $controller = new PremiumController();
    $controller->webhook();
});

// Administración
$router->get('/admin', function () {
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . url('/login'));
        exit;
    }
    if ((int)($_SESSION['user_role'] ?? 0) !== 1) {
        header('Location: ' . url('/dashboard'));
        exit;
    }
    $controller = new AdminController();
    $controller->index();
});

$router->any('/admin/dashboard', function () { // Panel de administración
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
        header('Location: ' . url('/login'));
        exit;
    }
    $controller = new AdminDashboardController();
    $controller->index();
});

$router->any('/admin/users', function () { // Gestión de verificaciones de estudiantes
    session_start();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
        header('Location: ' . url('/login'));
        exit;
    }
    require_once __DIR__ . '/app/controllers/admin/AdminUserController.php';
    $controller = new AdminUserController();
    $action = $_POST['action'] ?? $_GET['action'] ?? null;
    match ($action) {
        'approve' => $controller->approveVerification(),
        'reject'  => $controller->rejectVerification(),
        default   => $controller->verifications(),
    };
});

$router->any('/admin/reports', function () { // Gestión de reportes
    session_start();
    if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 0) != 1) {
        header('Location: ' . url('/login'));
        exit;
    }
    require_once __DIR__ . '/config/database.php';
    $database = new Database();
    $db = $database->connect();
    $controller = new ReportController($db);
    $tab = $_GET['tab'] ?? 'usuario';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['idReporte'])) {
        switch ($_POST['action']) {
            case 'resolve': $controller->resolve($tab); exit;
            case 'delete':  $controller->delete($tab);  exit;
        }
    }
    switch ($tab) {
        case 'anuncio': $reportes = $controller->getReportsByType('anuncio'); break;
        case 'chat':    $reportes = $controller->getReportsByType('chat');    break;
        default:        $reportes = $controller->getReportsByType('usuario'); $tab = 'usuario'; break;
    }
    $successMsg = $_GET['success'] ?? null;
    $errorMsg   = $_GET['error'] ?? null;
    require_once __DIR__ . '/views/admin/reports.view.php';
});

// Soporte
$router->any('/support', function () {
    session_start();
    $controller = new SupportController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
        $controller->sendSupportEmail();
    } else {
        $controller->index();
    }
});

// Otras páginas públicas
$router->get('/privacy', function () { // Política de privacidad
    session_start();
    require_once __DIR__ . '/views/public/privacy.view.php';
});

$router->get('/terms', function () { // Términos y condiciones
    session_start();
    require_once __DIR__ . '/views/public/terms.view.php';
});

$router->get('/safety', function () { // Consejos de seguridad
    session_start();
    require_once __DIR__ . '/views/public/safety.view.php';
});
