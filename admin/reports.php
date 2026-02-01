<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/ReportController.php';

$database = new Database();
$db = $database->connect();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 0) != 1) {
    header('Location: /login.php');
    exit;
}

$controller = new ReportController($db);
$tab = $_GET['tab'] ?? 'usuario';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['idReporte'])) {
    $reportId = (int)$_POST['idReporte'];
    switch ($_POST['action']) {
        case 'resolve':
            $controller->resolve($tab);
            exit;
        case 'delete':
            $controller->delete($tab);
            exit;
    }
}

switch ($tab) {
    case 'usuario':
        $reportes = $controller->getReportsByType('usuario');
        break;
    case 'anuncio':
        $reportes = $controller->getReportsByType('anuncio');
        break;
    case 'chat':
        $reportes = $controller->getReportsByType('chat');
        break;
    default:
        $reportes = $controller->getReportsByType('usuario');
        $tab = 'usuario';
        break;
}

$successMsg = $_GET['success'] ?? null;
$errorMsg = $_GET['error'] ?? null;

require_once __DIR__ . '/../views/admin/reports.view.php';
