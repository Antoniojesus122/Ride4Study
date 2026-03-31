<?php
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/AdminLog.php';
require_once __DIR__ . '/../../services/MailService.php';

class ReportController
{
    private PDO $db;
    private Report $report;
    private User $user;
    private Notification $notification;
    private AdminLog $adminLog;
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
        $this->adminLog = new AdminLog($db);
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
        $motivo = $_GET['motivo'] ?? null;
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;
        return $this->report->getReportsByType($tipo, $estado, $motivo ?: null, $dateFrom ?: null, $dateTo ?: null);
    }

    // Exportar reportes a CSV
    public function exportCsv(string $tipo): void
    {
        $reportes = $this->getReportsByType($tipo);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="reportes_' . $tipo . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['ID', 'Tipo', 'Estado', 'Prioridad', 'Motivo', 'Mensaje', 'Reportado', 'Reporta', 'Admin', 'Accion', 'Nota Admin', 'Fecha'], ';');

        foreach ($reportes as $r) {
            fputcsv($output, [
                $r['idReporte'],
                $r['tipo'],
                $r['estado'],
                $r['prioridad'] ?? '',
                $r['motivo'] ?? '',
                $r['mensaje'] ?? '',
                $r['reportado_nombre'] ?? '',
                $r['reporta_nombre'] ?? '',
                $r['admin_nombre'] ?? '',
                $r['accion_tomada'] ?? '',
                $r['nota_admin'] ?? '',
                $r['creado_en'],
            ], ';');
        }
        fclose($output);
        exit;
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
            $this->adminLog->log((int)$_SESSION['user_id'], 'tomar_reporte', 'reporte', (int)$idReporte, '');
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

        $this->adminLog->log((int)$_SESSION['user_id'], 'resolver_reporte', 'reporte', (int)$idReporte, "Accion: $accion" . ($notaAdmin ? " - $notaAdmin" : ''));
        redirectWithFlash(url('/admin/reports'), 'success', 'resolved', $tab);
    }

    // Eliminar un reporte
    public function delete($tab = 'usuario')
    {
        $idReporte = $_POST['idReporte'] ?? null;
        if ($idReporte) {
            $this->report->deleteReport((int)$idReporte);
            $this->adminLog->log((int)$_SESSION['user_id'], 'eliminar', 'reporte', (int)$idReporte, '');
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

    // Vista previa AJAX del contenido reportado
    public function previewContent(): void
    {
        header('Content-Type: application/json');
        $tipo = $_GET['tipo'] ?? '';
        $id = (int)($_GET['id'] ?? 0);

        if (!$id) {
            echo json_encode(['error' => 'ID invalido']);
            exit;
        }

        $data = [];

        if ($tipo === 'usuario' && $id) {
            $stmt = $this->db->prepare(
                "SELECT u.idUsuario, u.nombre, u.correo, u.telefono, u.ciudad, u.institucion,
                        u.estado_verificacion, u.premium, u.baneado, u.creado_en,
                        u.foto_perfil AS fotoPerfil, r.nombreRol
                 FROM usuarios u
                 LEFT JOIN roles r ON u.idRol = r.idRol
                 WHERE u.idUsuario = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $data['_tipo'] = 'usuario';
        } elseif ($tipo === 'anuncio' && $id) {
            $stmt = $this->db->prepare(
                "SELECT a.*, u.nombre as usuario_nombre, u.correo as usuario_correo,
                        lo.nombreLocalidad as nombreOrigen, ld.nombreLocalidad as nombreDestino
                 FROM anuncios a
                 JOIN usuarios u ON a.idUsuario = u.idUsuario
                 JOIN localidades lo ON a.origen = lo.idLocalidad
                 JOIN localidades ld ON a.destino = ld.idLocalidad
                 WHERE a.idAnuncio = :id"
            );
            $stmt->execute([':id' => $id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
            $data['_tipo'] = 'anuncio';
        } elseif ($tipo === 'chat' && $id) {
            // $id is a conversation ID
            $stmt = $this->db->prepare(
                "SELECT c.*, u1.nombre as user1_nombre, u2.nombre as user2_nombre
                 FROM conversations c
                 JOIN usuarios u1 ON c.user1_id = u1.idUsuario
                 JOIN usuarios u2 ON c.user2_id = u2.idUsuario
                 WHERE c.idConversation = :id"
            );
            $stmt->execute([':id' => $id]);
            $conv = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $msgStmt = $this->db->prepare(
                "SELECT m.idMensaje, m.mensaje, m.fechaCreacion, u.nombre as emisor_nombre
                 FROM mensajes m
                 JOIN usuarios u ON m.idEmisor = u.idUsuario
                 WHERE m.idConversation = :id
                 ORDER BY m.fechaCreacion DESC LIMIT 10"
            );
            $msgStmt->execute([':id' => $id]);
            $conv['mensajes'] = array_reverse($msgStmt->fetchAll(PDO::FETCH_ASSOC));
            $conv['_tipo'] = 'chat';
            $data = $conv;
        } elseif ($tipo === 'chat_msg' && $id) {
            // $id es el idMensaje reportado, mostrar contexto de la conversacion
            $msgStmt = $this->db->prepare(
                "SELECT m.idConversation FROM mensajes m WHERE m.idMensaje = :id"
            );
            $msgStmt->execute([':id' => $id]);
            $msgRow = $msgStmt->fetch(PDO::FETCH_ASSOC);

            if ($msgRow) {
                $convId = (int)$msgRow['idConversation'];
                $stmt = $this->db->prepare(
                    "SELECT c.*, u1.nombre as user1_nombre, u2.nombre as user2_nombre
                     FROM conversations c
                     JOIN usuarios u1 ON c.user1_id = u1.idUsuario
                     JOIN usuarios u2 ON c.user2_id = u2.idUsuario
                     WHERE c.idConversation = :id"
                );
                $stmt->execute([':id' => $convId]);
                $conv = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

                // Obtener mensajes anteriores y posteriores al mensaje reportado para contexto
                $contextStmt = $this->db->prepare(
                    "(SELECT m.idMensaje, m.mensaje, m.fechaCreacion, u.nombre as emisor_nombre
                      FROM mensajes m JOIN usuarios u ON m.idEmisor = u.idUsuario
                      WHERE m.idConversation = :cid AND m.idMensaje <= :mid
                      ORDER BY m.idMensaje DESC LIMIT 6)
                     UNION
                     (SELECT m.idMensaje, m.mensaje, m.fechaCreacion, u.nombre as emisor_nombre
                      FROM mensajes m JOIN usuarios u ON m.idEmisor = u.idUsuario
                      WHERE m.idConversation = :cid2 AND m.idMensaje > :mid2
                      ORDER BY m.idMensaje ASC LIMIT 5)
                     ORDER BY idMensaje ASC"
                );
                $contextStmt->execute([':cid' => $convId, ':mid' => $id, ':cid2' => $convId, ':mid2' => $id]);
                $conv['mensajes'] = $contextStmt->fetchAll(PDO::FETCH_ASSOC);
                $conv['reported_message_id'] = $id;
                $conv['_tipo'] = 'chat';
                $data = $conv;
            }
        } elseif ($tipo === 'chat_conv' && $id) {
            // $id es el idAnuncio reportado, mostrar la conversacion relacionada (si existe) para contexto
            $extraId = (int)($_GET['extraId'] ?? 0);
            $convStmt = $this->db->prepare(
                "SELECT c.*, u1.nombre as user1_nombre, u2.nombre as user2_nombre
                 FROM conversations c
                 JOIN usuarios u1 ON c.user1_id = u1.idUsuario
                 JOIN usuarios u2 ON c.user2_id = u2.idUsuario
                 WHERE c.idAnuncio = :idAnuncio AND (c.user1_id = :uid OR c.user2_id = :uid2)
                 LIMIT 1"
            );
            $convStmt->execute([':idAnuncio' => $id, ':uid' => $extraId, ':uid2' => $extraId]);
            $conv = $convStmt->fetch(PDO::FETCH_ASSOC);

            if ($conv) {
                $msgStmt = $this->db->prepare(
                    "SELECT m.idMensaje, m.mensaje, m.fechaCreacion, u.nombre as emisor_nombre
                     FROM mensajes m
                     JOIN usuarios u ON m.idEmisor = u.idUsuario
                     WHERE m.idConversation = :cid
                     ORDER BY m.fechaCreacion DESC LIMIT 15"
                );
                $msgStmt->execute([':cid' => $conv['idConversation']]);
                $conv['mensajes'] = array_reverse($msgStmt->fetchAll(PDO::FETCH_ASSOC));
                $conv['_tipo'] = 'chat';
                $data = $conv;
            }
        }

        echo json_encode($data);
        exit;
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
