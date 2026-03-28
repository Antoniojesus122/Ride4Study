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
        if ($estado && !in_array($estado, ['pendiente', 'en_revision', 'resuelto'])) {
            $estado = null;
        }
        return $this->report->getReportsByType($tipo, $estado);
    }

    // Obtener estadisticas
    public function getStats(): array
    {
        return $this->report->getStats();
    }

    // Obtener historial de un usuario reportado
    public function getUserHistory(int $userId): array
    {
        return [
            'historial' => $this->report->getHistoryByUser($userId),
            'sanciones' => $this->report->countSanctionsByUser($userId),
        ];
    }

    // Tomar un reporte (asignar admin "en revision")
    public function takeReport($tab = 'usuario')
    {
        $idReporte = $_POST['idReporte'] ?? null;
        if ($idReporte) {
            $this->report->assignAdmin((int)$idReporte, (int)$_SESSION['user_id']);
            redirectWithFlash(url('/admin/reports'), 'success', 'assigned', $tab);
        }
        redirectWithFlash(url('/admin/reports'), 'error', 'missing_id', $tab);
    }

    // Liberar un reporte (volver a pendiente)
    public function releaseReport($tab = 'usuario')
    {
        $idReporte = $_POST['idReporte'] ?? null;
        if ($idReporte) {
            $this->report->unassignAdmin((int)$idReporte);
            redirectWithFlash(url('/admin/reports'), 'success', 'released', $tab);
        }
        redirectWithFlash(url('/admin/reports'), 'error', 'missing_id', $tab);
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

        // Marcar como resuelto con nota y accion
        $this->report->markAsResolved((int)$idReporte, $notaAdmin, $accion);

        // Ejecutar accion sobre el reportado
        switch ($accion) {
            case 'advertir':
                if (!empty($reporteInfo['idUsuarioReportado'])) {
                    $this->advertirUsuario((int)$reporteInfo['idUsuarioReportado'], $reporteInfo);
                }
                break;
            case 'eliminar_contenido':
                $this->eliminarContenido($reporteInfo);
                break;
            case 'suspender':
                if (!empty($reporteInfo['idUsuarioReportado'])) {
                    $dias = (int)($_POST['dias_suspension'] ?? 7);
                    $this->suspenderUsuario((int)$reporteInfo['idUsuarioReportado'], $dias, $reporteInfo);
                }
                break;
            case 'banear':
                if (!empty($reporteInfo['idUsuarioReportado'])) {
                    $this->banearUsuario((int)$reporteInfo['idUsuarioReportado'], $reporteInfo);
                }
                break;
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

    // Suspender usuario temporalmente
    private function suspenderUsuario(int $userId, int $dias, array $reporteInfo): void
    {
        try {
            $hasta = date('Y-m-d H:i:s', strtotime("+{$dias} days"));
            $motivo = 'Suspension por reporte #' . $reporteInfo['idReporte'] . ': ' . ($reporteInfo['motivo'] ?? $reporteInfo['tipo']);

            $stmt = $this->db->prepare("UPDATE usuarios SET baneado = 1, ban_motivo = :motivo, ban_hasta = :hasta WHERE idUsuario = :id");
            $stmt->execute([':id' => $userId, ':motivo' => $motivo, ':hasta' => $hasta]);

            $this->notification->create(
                $userId,
                "Tu cuenta ha sido suspendida temporalmente ({$dias} dias) por incumplir las normas de la comunidad.",
                'fas fa-ban',
                url('/dashboard')
            );

            $userData = $this->user->getUserById($userId);
            if ($userData && $this->mailService && (int)($userData['notificaciones_email'] ?? 0) === 1) {
                $contenido = "
                    <p>Tu cuenta ha sido <strong style=\"color:#ef4444;\">suspendida temporalmente</strong> por {$dias} dias.</p>

                    <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#ef4444;\">Motivo:</strong> " . htmlspecialchars($reporteInfo['motivo'] ?? $reporteInfo['tipo']) . "</p>
                        <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#ef4444;\">Hasta:</strong> " . date('d/m/Y H:i', strtotime($hasta)) . "</p>
                    </div>

                    <p style=\"color:#94a3b8;\">Podras volver a usar la plataforma tras la fecha indicada.</p>
                ";

                $html = $this->mailService->generarPlantilla(
                    $userData['nombre'],
                    'Cuenta suspendida',
                    $contenido,
                    null,
                    fullUrl('/dashboard'),
                    'Ir a Ride4Study'
                );
                $this->mailService->send($userData['correo'], $userData['nombre'], 'Cuenta suspendida - Ride4Study', $html);
            }
        } catch (Exception $e) {
            error_log("Error suspension usuario: " . $e->getMessage());
        }
    }

    // Banear usuario permanentemente
    private function banearUsuario(int $userId, array $reporteInfo): void
    {
        try {
            $motivo = 'Ban permanente por reporte #' . $reporteInfo['idReporte'] . ': ' . ($reporteInfo['motivo'] ?? $reporteInfo['tipo']);

            $stmt = $this->db->prepare("UPDATE usuarios SET baneado = 1, ban_motivo = :motivo, ban_hasta = NULL WHERE idUsuario = :id");
            $stmt->execute([':id' => $userId, ':motivo' => $motivo]);

            $this->notification->create(
                $userId,
                'Tu cuenta ha sido suspendida permanentemente por incumplir gravemente las normas de la comunidad.',
                'fas fa-ban',
                url('/dashboard')
            );

            $userData = $this->user->getUserById($userId);
            if ($userData && $this->mailService && (int)($userData['notificaciones_email'] ?? 0) === 1) {
                $contenido = "
                    <p>Tu cuenta ha sido <strong style=\"color:#ef4444;\">suspendida permanentemente</strong> por incumplir gravemente las normas de la comunidad.</p>

                    <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                        <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#ef4444;\">Motivo:</strong> " . htmlspecialchars($reporteInfo['motivo'] ?? $reporteInfo['tipo']) . "</p>
                    </div>

                    <p style=\"color:#94a3b8;\">Si crees que se trata de un error, puedes contactarnos respondiendo a este email.</p>
                ";

                $html = $this->mailService->generarPlantilla(
                    $userData['nombre'],
                    'Cuenta suspendida permanentemente',
                    $contenido,
                    null,
                    fullUrl('/dashboard'),
                    'Ir a Ride4Study'
                );
                $this->mailService->send($userData['correo'], $userData['nombre'], 'Cuenta suspendida - Ride4Study', $html);
            }
        } catch (Exception $e) {
            error_log("Error ban usuario: " . $e->getMessage());
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
                    'suspender'          => 'El usuario reportado ha sido suspendido temporalmente.',
                    'banear'             => 'El usuario reportado ha sido suspendido de la plataforma.',
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
