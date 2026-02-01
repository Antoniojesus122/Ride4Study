<?php
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/User.php';

class ReportController
{
    private PDO $db;
    private Report $report;
    private User $user;

    public function __construct(PDO $db)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = $db;
        $this->report = new Report($db);
        $this->user = new User($db);

        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
            header('Location: ../../login.php');
            exit;
        }
    }

    // Mostrar lista de reportes
    public function index()
    {
        $tipo = $_GET['tipo'] ?? null;

        if ($tipo) {
            $reportes = $this->report->getReportsByType($tipo);
        } else {
            $reportes = $this->report->getAllReports();
        }

        require_once __DIR__ . '/../../views/admin/reports.view.php';
    }

    // Marcar un reporte como resuelto
    public function resolve($tab = 'usuario')
    {
        $idReporte = $_POST['idReporte'] ?? null;
        if ($idReporte) {
            $this->report->markAsResolved((int)$idReporte);
            header("Location: reports.php?tab=$tab&success=resolved");
            exit;
        }
        header("Location: reports.php?tab=$tab&error=missing_id");
        exit;
    }

    // Eliminar un reporte
    public function delete($tab = 'usuario')
    {
        $idReporte = $_POST['idReporte'] ?? null;
        if ($idReporte) {
            $this->report->deleteReport((int)$idReporte);
            header("Location: reports.php?tab=$tab&success=deleted");
            exit;
        }
        header("Location: reports.php?tab=$tab&error=missing_id");
        exit;
    }

    // Ver detalle de un reporte
    public function view()
    {
        $idReporte = $_GET['id'] ?? null;
        if ($idReporte) {
            $reporte = $this->report->getReportById((int)$idReporte);
            if ($reporte) {
                require_once __DIR__ . '/../../views/admin/report_detail.view.php';
                exit;
            }
        }
        header('Location: reports.php?error=not_found');
        exit;
    }

    // Obtener reportes por tipo
    public function getReportsByType(string $tipo): array
    {
        return $this->report->getReportsByType($tipo);
    }
}
