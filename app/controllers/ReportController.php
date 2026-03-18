<?php
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../../services/MailService.php';

class ReportController
{
    private PDO $db;
    private Report $report;
    private User $user;
    private Notification $notification;
    private MailService $mailService;

    public function __construct(PDO $db)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = $db;
        $this->report = new Report($db);
        $this->user = new User($db);
        $this->notification = new Notification($db);
        $this->mailService = new MailService();

        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
            header('Location: ' . url('/login'));
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
            // Obtener info del reporte antes de resolverlo
            $reporteInfo = $this->report->getReportById((int)$idReporte);

            $this->report->markAsResolved((int)$idReporte);

            // Notificar al usuario que envió el reporte
            if ($reporteInfo && !empty($reporteInfo['idUsuarioQueReporta'])) {
                $reporterId = (int)$reporteInfo['idUsuarioQueReporta'];

                try {
                    // Notificación in-app
                    $this->notification->create(
                        $reporterId,
                        t('notif.report_resolved'),
                        'fas fa-check-circle',
                        url('/dashboard')
                    );

                    // Email
                    $stmtReporter = $this->db->prepare("SELECT nombre, correo, notificaciones_email FROM usuarios WHERE idUsuario = :id");
                    $stmtReporter->execute([':id' => $reporterId]);
                    $reporter = $stmtReporter->fetch(PDO::FETCH_ASSOC);

                    if ($reporter && (int)($reporter['notificaciones_email'] ?? 0) === 1) {
                        $contenido = "
                            <p>El reporte que enviaste ha sido revisado y resuelto por nuestro equipo.</p>

                            <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                                <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Tipo:</strong> " . htmlspecialchars($reporteInfo['tipo']) . "</p>
                                <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Estado:</strong> Resuelto</p>
                            </div>

                            <p style=\"color:#94a3b8;\">Gracias por ayudarnos a mantener la comunidad segura.</p>
                        ";

                        $html = $this->mailService->generarPlantilla(
                            $reporter['nombre'],
                            "Reporte resuelto",
                            $contenido,
                            null,
                            'http://localhost/Ride4Study/dashboard',
                            'Ir a Ride4Study'
                        );
                        $this->mailService->send($reporter['correo'], $reporter['nombre'], 'Tu reporte ha sido resuelto - Ride4Study', $html);
                    }
                } catch (Exception $e) {
                    error_log("Error notificación reporte resuelto: " . $e->getMessage());
                }
            }

            header("Location: " . url('/admin/reports') . "?tab=$tab&success=resolved");
            exit;
        }
        header("Location: " . url('/admin/reports') . "?tab=$tab&error=missing_id");
        exit;
    }

    // Eliminar un reporte
    public function delete($tab = 'usuario')
    {
        $idReporte = $_POST['idReporte'] ?? null;
        if ($idReporte) {
            $this->report->deleteReport((int)$idReporte);
            header("Location: " . url('/admin/reports') . "?tab=$tab&success=deleted");
            exit;
        }
        header("Location: " . url('/admin/reports') . "?tab=$tab&error=missing_id");
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
        header('Location: ' . url('/admin/reports') . '?error=not_found');
        exit;
    }

    // Obtener reportes por tipo
    public function getReportsByType(string $tipo): array
    {
        return $this->report->getReportsByType($tipo);
    }
}