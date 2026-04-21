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

        $period = resolvePeriod($_GET);
        $filters = [
            'date_from'    => $period['from'],
            'date_to'      => $period['to'],
            'entidad'      => $_GET['entidad'] ?? '',
            'accion'       => $_GET['accion'] ?? '',
            'admin_id'     => $_GET['admin_id'] ?? '',
            'admin_search' => trim($_GET['admin_search'] ?? ''),
        ];

        $logs = $this->adminLog->getAll($filters, $page, $limit);
        $totalLogs = $this->adminLog->countAll($filters);
        $totalPages = max(1, ceil($totalLogs / $limit));
        $adminList = $this->adminLog->getAdminList();

        require_once __DIR__ . '/../../../views/admin/logs.view.php';
    }

    // Exporta los logs filtrados a CSV. Respeta los mismos filtros de index().
    public function export(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
            header('Location: ' . url('/login'));
            exit;
        }

        $period = resolvePeriod($_GET);
        $filters = [
            'date_from'    => $period['from'],
            'date_to'      => $period['to'],
            'entidad'      => $_GET['entidad'] ?? '',
            'accion'       => $_GET['accion'] ?? '',
            'admin_id'     => $_GET['admin_id'] ?? '',
            'admin_search' => trim($_GET['admin_search'] ?? ''),
        ];

        // Sin paginar: cogemos todo lo que case con los filtros (hasta un tope alto)
        $rows = $this->adminLog->getAll($filters, 1, 100000);

        $filename = 'ride4study-logs-' . date('Ymd-His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // BOM para que Excel detecte UTF-8
        fwrite($out, "\xEF\xBB\xBF");

        // Cabecera
        fputcsv($out, ['Fecha', 'Admin', 'Accion', 'Entidad', 'ID entidad', 'Detalles', 'IP'], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['creado_en'] ?? '',
                $r['admin_nombre'] ?? '',
                $r['accion'] ?? '',
                $r['entidad'] ?? '',
                $r['idEntidad'] ?? '',
                $r['detalles'] ?? '',
                $r['ip'] ?? '',
            ], ';');
        }

        fclose($out);
        exit;
    }
}
