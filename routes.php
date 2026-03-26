<?php

// Definición de rutas para la aplicación web 
// Cada ruta se asocia a un controlador específico que maneja la lógica de esa sección

// Página de inicio (landing page)
$router->get('/', function () {
    session_start();
    if (isset($_SESSION['user_id'])) {
        $role = (int)($_SESSION['user_role'] ?? 0);
        header('Location: ' . url($role === 1 ? '/admin/dashboard' : '/dashboard'));
        exit;
    }
    require_once __DIR__ . '/views/public/landing.view.php';
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

// Páginas de autenticación
$router->any('/login', [AuthController::class, 'login']);
$router->any('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);
$router->any('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->any('/reset-password', [AuthController::class, 'resetPassword']);
$router->any('/admin-verify', [AuthController::class, 'adminVerify']); // 2FA admin

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

$router->post('/delete-ride', [RideController::class, 'delete']); // Eliminar viaje
$router->get('/my-rides', [RideController::class, 'myRides']); // Ver viajes propios
$router->post('/reserve', [RideController::class, 'reserve']); // Reservar
$router->post('/manage-reservation', [RideController::class, 'manageRequest']); // Aceptar/rechazar reservas
$router->any('/cancel-reservation', [RideController::class, 'cancelReservation']); // Cancelar reserva
$router->post('/toggle-featured', [RideController::class, 'toggleFeatured']); // Destacar anuncio (premium)
$router->get('/ranking', [RideController::class, 'ranking']); // Ranking CO2

// Perfil de usuario
$router->any('/profile', function () {
    $controller = new UserController();
    $action = $_GET['action'] ?? null;
    match ($action) {
        'update'          => $controller->update(),
        'change_password' => $controller->changePassword(),
        'verify'          => $controller->verify(),
        'update_privacy'  => $controller->updatePrivacy(),
        'delete_account'  => $controller->deleteAccount(),
        default           => $controller->index(),
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
        'load'           => $controller->fetchMessages(),
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
    $motivo  = trim($_POST['motivo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $idUsuarioReportado = !empty($_POST['idUsuarioReportado']) ? (int)$_POST['idUsuarioReportado'] : null;
    $idAnuncio          = !empty($_POST['idAnuncio'])          ? (int)$_POST['idAnuncio']          : null;
    $idChat             = !empty($_POST['idChat'])             ? (int)$_POST['idChat']             : null;

    $motivosValidos = ['spam', 'ofensivo', 'suplantacion', 'inapropiado', 'fraude', 'otro'];
    if (!in_array($tipo, ['usuario', 'anuncio', 'chat']) || !in_array($motivo, $motivosValidos)) {
        echo json_encode(['success' => false, 'message' => t('nav.report_error')]);
        exit;
    }

    // Prevenir duplicados
    if ($report->existsPending($tipo, $idUsuarioReportado, $idAnuncio, $idChat, (int)$_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => t('nav.report_duplicate')]);
        exit;
    }

    $ok = $report->createReport($tipo, $idUsuarioReportado, $idAnuncio, $idChat, (int)$_SESSION['user_id'], $mensaje ?: $motivo, $motivo);
    echo json_encode(['success' => $ok, 'message' => $ok ? t('nav.report_sent') : t('nav.report_error')]);
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
    } elseif ($action === 'count') {
        echo json_encode(['success' => true, 'count' => $notif->countUnread($userId)]);
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

// Administración — /admin redirige al dashboard
$router->get('/admin', function () {
    session_start();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
        header('Location: ' . url('/login'));
        exit;
    }
    header('Location: ' . url('/admin/dashboard'));
    exit;
});

$router->any('/admin/dashboard', function () {
    session_start();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
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
        'approve'     => $controller->approveVerification(),
        'reject'      => $controller->rejectVerification(),
        'update_role' => $controller->updateRole(),
        'ban'         => $controller->banUser(),
        'unban'       => $controller->unbanUser(),
        'export_csv'  => $controller->exportCsv(),
        default       => $controller->index(),
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
    $flashData = getFlash();
    $successMsg = ($flashData && $flashData['type'] === 'success') ? $flashData['message'] : null;
    $errorMsg   = ($flashData && $flashData['type'] === 'error') ? $flashData['message'] : null;
    require_once __DIR__ . '/views/admin/reports.view.php';
});

$router->any('/admin/instituciones', function () {
    session_start();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
        header('Location: ' . url('/login'));
        exit;
    }
    require_once __DIR__ . '/app/controllers/admin/AdminInstitucionController.php';
    $controller = new AdminInstitucionController();
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
    match ($action) {
        'create' => $controller->create(),
        'edit'   => $controller->edit(),
        'delete' => $controller->delete(),
        default  => $controller->listAll(),
    };
});

$router->any('/admin/ads', function () {
    session_start();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
        header('Location: ' . url('/login'));
        exit;
    }
    require_once __DIR__ . '/app/controllers/admin/AdminAdController.php';
    $controller = new AdminAdController();
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
    match ($action) {
        'delete' => $controller->deleteAd(),
        default  => $controller->listAll(),
    };
});

$router->any('/admin/profile', function () {
    session_start();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
        header('Location: ' . url('/login'));
        exit;
    }
    require_once __DIR__ . '/app/controllers/admin/AdminProfileController.php';
    $controller = new AdminProfileController();
    $action = $_POST['action'] ?? $_GET['action'] ?? null;
    match ($action) {
        'update_info'     => $controller->updateInfo(),
        'change_password' => $controller->changePassword(),
        default           => $controller->index(),
    };
});

$router->any('/admin/premium', function () {
    session_start();
    if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
        header('Location: ' . url('/login'));
        exit;
    }
    require_once __DIR__ . '/app/controllers/admin/AdminPremiumController.php';
    $controller = new AdminPremiumController();
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';
    match ($action) {
        'grant'  => $controller->grant(),
        'revoke' => $controller->revoke(),
        'search' => $controller->searchUsers(),
        default  => $controller->listAll(),
    };
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

$router->get('/cookies', function () { // Politica de cookies
    session_start();
    require_once __DIR__ . '/views/public/cookies.view.php';
});

$router->get('/safety', function () { // Consejos de seguridad
    session_start();
    require_once __DIR__ . '/views/public/safety.view.php';
});
