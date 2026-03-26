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
    private ?MailService $mailService = null;

    public function __construct(PDO $db)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = $db;
        $this->report = new Report($db);
        $this->user = new User($db);
        $this->notification = new Notification($db);
        try { $this->mailService = new MailService(); } catch (Exception $e) { error_log('MailService: ' . $e->getMessage()); }

        if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
            header('Location: ' . url('/login'));
            exit;
        }
    }

    // Obtener reportes por tipo (usado desde la ruta)
    public function getReportsByType(string $tipo): array
    {
        $estado = $_GET['estado'] ?? null;
        if ($estado && !in_array($estado, ['pendiente', 'resuelto'])) {
            $estado = null;
        }
        return $this->report->getReportsByType($tipo, $estado);
    }

    // Resolver reporte con accion y nota del admin
    public function resolve($tab = 'usuario')
    {
        $idReporte = $_POST['idReporte'] ?? null;
        $accion    = $_POST['accion'] ?? 'resolver';
        $notaAdmin = trim($_POST['nota_admin'] ?? '');

        if (!$idReporte) {
            redirectWithFlash(url('/admin/reports'), 'error', 'missing_id', $tab);
        }

        $reporteInfo = $this->report->getReportById((int)$idReporte);
        if (!$reporteInfo) {
            redirectWithFlash(url('/admin/reports'), 'error', 'not_found', $tab);
        }

        // Marcar como resuelto con nota
        $this->report->markAsResolved((int)$idReporte, $notaAdmin);

        // Ejecutar accion sobre el reportado
        if ($accion === 'advertir' && !empty($reporteInfo['idUsuarioReportado'])) {
            $this->advertirUsuario((int)$reporteInfo['idUsuarioReportado'], $reporteInfo);
        } elseif ($accion === 'eliminar_contenido') {
            $this->eliminarContenido($reporteInfo);
        }

        // Notificar al usuario que envio el reporte
        $this->notificarReportante($reporteInfo, $accion, $notaAdmin);

        redirectWithFlash(url('/admin/reports'), 'success', 'resolved', $tab);
    }

    // Eliminar un reporte
    public function delete($tab = 'usuario')
    {
        $idReporte = $_POST['idReporte'] ?? null;
        if ($idReporte) {
            $this->report->deleteReport((int)$idReporte);
            redirectWithFlash(url('/admin/reports'), 'success', 'deleted', $tab);
        }
        redirectWithFlash(url('/admin/reports'), 'error', 'missing_id', $tab);
    }

    // Advertir al usuario reportado (notificacion + email)
    private function advertirUsuario(int $userId, array $reporteInfo): void
    {
        try {
            $this->notification->create(
                $userId,
                'Has recibido una advertencia por parte del equipo de Ride4Study. Por favor, revisa tu comportamiento en la plataforma.',
                'fas fa-exclamation-triangle',
                url('/dashboard')
            );

            $userData = $this->user->getUserById($userId);
            if ($userData && $this->mailService && (int)($userData['notificaciones_email'] ?? 0) === 1) {
                $contenido = "
                    <p>Has recibido una <strong style=\"color:#f59e0b;\">advertencia</strong> por parte del equipo de moderacion de Ride4Study.</p>

                    <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#f59e0b;\">Motivo:</strong> " . htmlspecialchars($reporteInfo['motivo'] ?? $reporteInfo['tipo']) . "</p>
                    </div>

                    <p style=\"color:#94a3b8;\">Te pedimos que revises las normas de la comunidad. En caso de reincidencia, podriamos tomar medidas adicionales.</p>
                ";

                $html = $this->mailService->generarPlantilla(
                    $userData['nombre'],
                    'Advertencia recibida',
                    $contenido,
                    null,
                    fullUrl('/dashboard'),
                    'Ir a Ride4Study'
                );
                $this->mailService->send($userData['correo'], $userData['nombre'], 'Advertencia - Ride4Study', $html);
            }
        } catch (Exception $e) {
            error_log("Error advertencia usuario: " . $e->getMessage());
        }
    }

    // Eliminar el contenido reportado (anuncio o mensaje)
    private function eliminarContenido(array $reporteInfo): void
    {
        try {
            if (!empty($reporteInfo['idAnuncio'])) {
                $stmt = $this->db->prepare("DELETE FROM anuncios WHERE idAnuncio = :id");
                $stmt->execute([':id' => (int)$reporteInfo['idAnuncio']]);
            }

            // Notificar al reportado de que se elimino su contenido
            if (!empty($reporteInfo['idUsuarioReportado'])) {
                $this->notification->create(
                    (int)$reporteInfo['idUsuarioReportado'],
                    'Un contenido tuyo ha sido eliminado por el equipo de moderacion por incumplir las normas de la comunidad.',
                    'fas fa-trash',
                    url('/dashboard')
                );
            }
        } catch (Exception $e) {
            error_log("Error eliminar contenido reportado: " . $e->getMessage());
        }
    }

    // Notificar al usuario que envio el reporte
    private function notificarReportante(array $reporteInfo, string $accion, string $notaAdmin): void
    {
        if (empty($reporteInfo['idUsuarioQueReporta'])) return;
        $reporterId = (int)$reporteInfo['idUsuarioQueReporta'];

        try {
            $this->notification->create(
                $reporterId,
                'Tu reporte ha sido revisado y resuelto por el equipo de Ride4Study.',
                'fas fa-check-circle',
                url('/dashboard')
            );

            $stmtReporter = $this->db->prepare("SELECT nombre, correo, notificaciones_email FROM usuarios WHERE idUsuario = :id");
            $stmtReporter->execute([':id' => $reporterId]);
            $reporter = $stmtReporter->fetch(PDO::FETCH_ASSOC);

            if ($reporter && $this->mailService && (int)($reporter['notificaciones_email'] ?? 0) === 1) {
                $accionTexto = match ($accion) {
                    'advertir'           => 'Se ha enviado una advertencia al usuario reportado.',
                    'eliminar_contenido' => 'El contenido reportado ha sido eliminado.',
                    default              => 'El reporte ha sido revisado.',
                };

                $notaHtml = $notaAdmin
                    ? "<p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#6EE7B7;\">Nota:</strong> " . htmlspecialchars($notaAdmin) . "</p>"
                    : '';

                $contenido = "
                    <p>El reporte que enviaste ha sido revisado y resuelto por nuestro equipo.</p>

                    <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#6EE7B7;\">Tipo:</strong> " . htmlspecialchars($reporteInfo['tipo']) . "</p>
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#6EE7B7;\">Resolucion:</strong> " . htmlspecialchars($accionTexto) . "</p>
                        {$notaHtml}
                    </div>

                    <p style=\"color:#94a3b8;\">Gracias por ayudarnos a mantener la comunidad segura.</p>
                ";

                $html = $this->mailService->generarPlantilla(
                    $reporter['nombre'],
                    'Reporte resuelto',
                    $contenido,
                    null,
                    fullUrl('/dashboard'),
                    'Ir a Ride4Study'
                );
                $this->mailService->send($reporter['correo'], $reporter['nombre'], 'Tu reporte ha sido resuelto - Ride4Study', $html);
            }
        } catch (Exception $e) {
            error_log("Error notificacion reporte resuelto: " . $e->getMessage());
        }
    }
}
