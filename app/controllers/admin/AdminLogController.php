<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/AdminLog.php';

class AdminLogController {
    private PDO $db;
    private AdminLog $adminLog;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->adminLog = new AdminLog($this->db);
    }

    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
            header('Location: ' . url('/login'));
            exit;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 30;

        $filters = [
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to'] ?? '',
            'entidad'   => $_GET['entidad'] ?? '',
            'accion'    => $_GET['accion'] ?? '',
            'admin_id'  => $_GET['admin_id'] ?? '',
        ];

        $logs = $this->adminLog->getAll($filters, $page, $limit);
        $totalLogs = $this->adminLog->countAll($filters);
        $totalPages = max(1, ceil($totalLogs / $limit));

        require_once __DIR__ . '/../../../views/admin/logs.view.php';
    }
}
