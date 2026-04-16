<?php

// Rutas de usuario autenticado (dashboard, viajes, mensajes, perfil, etc.)

// Dashboard
$router->any('/dashboard', [RideController::class, 'index']);

// Gestion de viajes
$router->any('/publish', function () {
    $controller = new RideController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->store();
    } else {
        $controller->create();
    }
});

$router->any('/edit-ride', function () {
    $controller = new RideController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->update();
    } else {
        $controller->edit();
    }
});

$router->post('/delete-ride', [RideController::class, 'delete']);
$router->get('/my-rides', [RideController::class, 'myRides']);
$router->post('/reserve', [RideController::class, 'reserve']);
$router->post('/manage-reservation', [RideController::class, 'manageRequest']);
$router->post('/complete-trip', [RideController::class, 'completeTrip']);
$router->any('/cancel-reservation', [RideController::class, 'cancelReservation']);
$router->post('/toggle-featured', [RideController::class, 'toggleFeatured']);
$router->get('/ranking', [RideController::class, 'ranking']);

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
        'send' => $controller->send(),
        'edit' => $controller->edit(),
        'delete' => $controller->delete(),
        'load' => $controller->fetchMessages(),
        'offer_ride' => $controller->offerRide(),
        default => $controller->chat(),
    };
});

// Valoraciones
$router->any('/rate', function () {
    header('Location: ' . url('/rating') . '?' . http_build_query($_GET));
    exit;
});

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
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $db = $database->connect();
    $report = new Report($db);
    $tipo    = $_POST['tipo']    ?? '';
    $motivo  = trim($_POST['motivo'] ?? '');
    $mensaje = trim($_POST['mensaje'] ?? '');
    $idUsuarioReportado = !empty($_POST['idUsuarioReportado']) ? (int)$_POST['idUsuarioReportado'] : null;
    $idAnuncio          = !empty($_POST['idAnuncio'])          ? (int)$_POST['idAnuncio']          : null;
    $idChat             = !empty($_POST['idChat'])             ? (int)$_POST['idChat']             : null;

    if (!in_array($tipo, Report::TIPOS_VALIDOS) || !in_array($motivo, Report::MOTIVOS_VALIDOS)) {
        echo json_encode(['success' => false, 'message' => t('nav.report_error')]);
        exit;
    }

    // Prevenir duplicados
    if ($report->existsPending($tipo, $idUsuarioReportado, $idAnuncio, $idChat, (int)$_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => t('nav.report_duplicate')]);
        exit;
    }

    // Procesar evidencia (imagen)
    $evidenciaImg = null;
    if (!empty($_FILES['evidencia']) && $_FILES['evidencia']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['evidencia']['tmp_name']);
        finfo_close($finfo);
        if (in_array($mimeType, $allowed) && $_FILES['evidencia']['size'] <= 5 * 1024 * 1024) {
            $ext = match($mimeType) { 'image/jpeg' => '.jpg', 'image/png' => '.png', 'image/webp' => '.webp' };
            $filename = uniqid('report_') . $ext;
            $destPath = __DIR__ . '/../public/uploads/reports/' . $filename;
            if (move_uploaded_file($_FILES['evidencia']['tmp_name'], $destPath)) {
                $evidenciaImg = $filename;
            }
        }
    }

    $ok = $report->createReport($tipo, $idUsuarioReportado, $idAnuncio, $idChat, (int)$_SESSION['user_id'], $mensaje ?: $motivo, $motivo, $evidenciaImg);

    // Notificar a admins por email
    if ($ok) {
        try {
            require_once __DIR__ . '/../services/MailService.php';
            $mail = new MailService();
            $tipoLabel = match($tipo) { Report::TIPO_USUARIO => 'usuario', Report::TIPO_ANUNCIO => 'anuncio', Report::TIPO_CHAT => 'chat', default => $tipo };
            $motivoLabel = match($motivo) { Report::MOTIVO_SPAM => 'Spam', Report::MOTIVO_OFENSIVO => 'Contenido ofensivo', Report::MOTIVO_SUPLANTACION => 'Suplantacion', Report::MOTIVO_INAPROPIADO => 'Comportamiento inapropiado', Report::MOTIVO_FRAUDE => 'Fraude', default => 'Otro' };
            $reporterName = $_SESSION['user_name'] ?? 'Usuario';
            $admins = $db->query("SELECT nombre, correo FROM usuarios WHERE idRol = 1")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($admins as $admin) {
                $html = $mail->generarPlantilla(
                    $admin['nombre'],
                    'Nuevo reporte recibido',
                    'Se ha recibido un nuevo reporte en Ride4Study.<br><br>
                    <strong>Tipo:</strong> ' . htmlspecialchars(ucfirst($tipoLabel)) . '<br>
                    <strong>Motivo:</strong> ' . htmlspecialchars($motivoLabel) . '<br>
                    <strong>Reportado por:</strong> ' . htmlspecialchars($reporterName) . '<br>
                    <strong>Mensaje:</strong> ' . htmlspecialchars($mensaje ?: $motivo),
                    null,
                    fullUrl('/admin/reports') . '?tab=' . urlencode($tipo),
                    'Ver reportes'
                );
                $mail->send($admin['correo'], $admin['nombre'], 'Nuevo reporte · Ride4Study', $html);
            }
        } catch (Exception $e) { error_log('Admin email notify: ' . $e->getMessage()); }

        // Notificacion in-app para admins
        try {
            require_once __DIR__ . '/../app/models/Notification.php';
            $notif = new Notification($db);
            $adminIds = $db->query("SELECT idUsuario FROM usuarios WHERE idRol = 1")->fetchAll(PDO::FETCH_COLUMN);
            $reporterName = $_SESSION['user_name'] ?? 'Usuario';
            $tipoLabel = match($tipo) { Report::TIPO_USUARIO => 'usuario', Report::TIPO_ANUNCIO => 'anuncio', Report::TIPO_CHAT => 'chat', default => $tipo };
            foreach ($adminIds as $adminId) {
                $notif->create(
                    (int)$adminId,
                    'Nuevo reporte de ' . htmlspecialchars($tipoLabel) . ' por ' . htmlspecialchars($reporterName),
                    'fas fa-flag',
                    url('/admin/reports') . '?tab=' . urlencode($tipo)
                );
            }
        } catch (Exception $e) { error_log('Admin notif: ' . $e->getMessage()); }
    }

    echo json_encode(['success' => $ok, 'message' => $ok ? t('nav.report_sent') : t('nav.report_error')]);
    exit;
});

// Notificaciones dentro de la aplicacion web
$router->any('/notifications', function () {
    if (session_status() === PHP_SESSION_NONE) session_start();
    header('Content-Type: application/json');
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false]);
        exit;
    }
    require_once __DIR__ . '/../config/database.php';
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
    require_once __DIR__ . '/../services/StripeService.php';
    $controller = new PremiumController();
    $action = $_GET['action'] ?? null;
    match ($action) {
        'checkout' => $controller->checkout(),
        'success' => $controller->success(),
        'cancel' => $controller->cancel(),
        default => $controller->index(),
    };
});
