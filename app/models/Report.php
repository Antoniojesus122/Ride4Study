<?php

class Report
{
    private PDO $conn;
    private string $table = 'reportes';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    // Crear un nuevo reporte con calculo de prioridad automatico
    public function createReport(string $tipo, ?int $idUsuarioReportado, ?int $idAnuncio, ?int $idChat, int $idUsuarioQueReporta, string $mensaje, string $motivo = '', ?string $evidenciaImg = null): bool
    {
        $prioridad = $this->calcularPrioridad($tipo, $motivo, $idUsuarioReportado);

        $sql = "INSERT INTO {$this->table}
                (tipo, idUsuarioReportado, idAnuncio, idChat, idUsuarioQueReporta, mensaje, evidencia_img, motivo, prioridad)
                VALUES
                (:tipo, :idUsuarioReportado, :idAnuncio, :idChat, :idUsuarioQueReporta, :mensaje, :evidencia, :motivo, :prioridad)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':tipo' => $tipo,
            ':idUsuarioReportado' => $idUsuarioReportado,
            ':idAnuncio' => $idAnuncio,
            ':idChat' => $idChat,
            ':idUsuarioQueReporta' => $idUsuarioQueReporta,
            ':mensaje' => htmlspecialchars(strip_tags($mensaje)),
            ':evidencia' => $evidenciaImg,
            ':motivo' => $motivo ?: null,
            ':prioridad' => $prioridad,
        ]);
    }

    // Calcular prioridad automatica basada en motivo y historial
    private function calcularPrioridad(string $tipo, string $motivo, ?int $idUsuarioReportado): string
    {
        // Fraude y suplantacion siempre urgente
        if (in_array($motivo, ['fraude', 'suplantacion'])) {
            return 'urgente';
        }

        // Si el usuario tiene 3+ reportes pendientes, prioridad alta
        if ($idUsuarioReportado) {
            $count = $this->countPendingByUser($idUsuarioReportado);
            if ($count >= 3) return 'urgente';
            if ($count >= 2) return 'alta';
        }

        // Ofensivo = alta
        if ($motivo === 'ofensivo') return 'alta';

        // Inapropiado = media
        if ($motivo === 'inapropiado') return 'media';

        // Spam y otro = baja
        return 'baja';
    }

    // Contar reportes pendientes contra un usuario
    public function countPendingByUser(int $userId): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE idUsuarioReportado = :uid AND estado IN ('pendiente', 'en_revision')";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    // Comprobar si ya existe un reporte pendiente del mismo usuario al mismo objetivo
    public function existsPending(string $tipo, ?int $idUsuarioReportado, ?int $idAnuncio, ?int $idChat, int $idUsuarioQueReporta): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE tipo = :tipo AND idUsuarioQueReporta = :reporter AND estado IN ('pendiente', 'en_revision')";
        $params = [':tipo' => $tipo, ':reporter' => $idUsuarioQueReporta];

        if ($idUsuarioReportado) {
            $sql .= " AND idUsuarioReportado = :target";
            $params[':target'] = $idUsuarioReportado;
        } elseif ($idAnuncio) {
            $sql .= " AND idAnuncio = :target";
            $params[':target'] = $idAnuncio;
        } elseif ($idChat) {
            $sql .= " AND idChat = :target";
            $params[':target'] = $idChat;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Obtener todos los reportes con datos de usuario y anuncio
    public function getAllReports(): array
    {
        $sql = "SELECT r.*, u1.nombre AS reportado_nombre, u1.correo AS reportado_correo,
                       u2.nombre AS reporta_nombre, a.tipo AS anuncio_tipo,
                       lo.nombreLocalidad AS anuncio_origen, ld.nombreLocalidad AS anuncio_destino,
                       adm.nombre AS admin_nombre
                FROM {$this->table} r
                LEFT JOIN usuarios u1 ON r.idUsuarioReportado = u1.idUsuario
                LEFT JOIN usuarios u2 ON r.idUsuarioQueReporta = u2.idUsuario
                LEFT JOIN anuncios a ON r.idAnuncio = a.idAnuncio
                LEFT JOIN localidades lo ON a.origen = lo.idLocalidad
                LEFT JOIN localidades ld ON a.destino = ld.idLocalidad
                LEFT JOIN usuarios adm ON r.admin_asignado = adm.idUsuario
                ORDER BY FIELD(r.prioridad, 'urgente', 'alta', 'media', 'baja'), r.creado_en DESC";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(): int
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM {$this->table}");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    // Obtener reportes filtrados por tipo y opcionalmente por estado
    public function getReportsByType(string $tipo, ?string $estado = null): array
    {
        $sql = "SELECT r.*, u1.nombre AS reportado_nombre, u1.correo AS reportado_correo,
                       u2.nombre AS reporta_nombre, a.tipo AS anuncio_tipo,
                       lo.nombreLocalidad AS anuncio_origen, ld.nombreLocalidad AS anuncio_destino,
                       adm.nombre AS admin_nombre
                FROM {$this->table} r
                LEFT JOIN usuarios u1 ON r.idUsuarioReportado = u1.idUsuario
                LEFT JOIN usuarios u2 ON r.idUsuarioQueReporta = u2.idUsuario
                LEFT JOIN anuncios a ON r.idAnuncio = a.idAnuncio
                LEFT JOIN localidades lo ON a.origen = lo.idLocalidad
                LEFT JOIN localidades ld ON a.destino = ld.idLocalidad
                LEFT JOIN usuarios adm ON r.admin_asignado = adm.idUsuario
                WHERE r.tipo = :tipo";
        $params = [':tipo' => $tipo];

        if ($estado) {
            $sql .= " AND r.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY FIELD(r.estado, 'pendiente', 'en_revision', 'resuelto'), FIELD(r.prioridad, 'urgente', 'alta', 'media', 'baja'), r.creado_en DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Marcar como resuelto con nota del admin
    public function markAsResolved(int $idReporte, string $notaAdmin = '', string $accion = 'resolver'): bool
    {
        $sql = "UPDATE {$this->table} SET estado = 'resuelto', nota_admin = :nota, accion_tomada = :accion, resuelto_en = NOW() WHERE idReporte = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $idReporte, ':nota' => $notaAdmin ?: null, ':accion' => $accion]);
    }

    // Asignar admin (estado "en revision")
    public function assignAdmin(int $idReporte, int $adminId): bool
    {
        $sql = "UPDATE {$this->table} SET estado = 'en_revision', admin_asignado = :admin WHERE idReporte = :id AND estado = 'pendiente'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $idReporte, ':admin' => $adminId]);
    }

    // Liberar reporte (volver a pendiente)
    public function unassignAdmin(int $idReporte): bool
    {
        $sql = "UPDATE {$this->table} SET estado = 'pendiente', admin_asignado = NULL WHERE idReporte = :id AND estado = 'en_revision'";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $idReporte]);
    }

    // Eliminar un reporte
    public function deleteReport(int $idReporte): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE idReporte = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $idReporte]);
    }

    // Obtener un reporte por ID con toda la info
    public function getReportById(int $idReporte)
    {
        $sql = "SELECT r.*, u1.nombre AS reportado_nombre, u1.correo AS reportado_correo,
                       u2.nombre AS reporta_nombre, u2.correo AS reporta_correo,
                       a.tipo AS anuncio_tipo,
                       lo.nombreLocalidad AS anuncio_origen, ld.nombreLocalidad AS anuncio_destino,
                       adm.nombre AS admin_nombre
                FROM {$this->table} r
                LEFT JOIN usuarios u1 ON r.idUsuarioReportado = u1.idUsuario
                LEFT JOIN usuarios u2 ON r.idUsuarioQueReporta = u2.idUsuario
                LEFT JOIN anuncios a ON r.idAnuncio = a.idAnuncio
                LEFT JOIN localidades lo ON a.origen = lo.idLocalidad
                LEFT JOIN localidades ld ON a.destino = ld.idLocalidad
                LEFT JOIN usuarios adm ON r.admin_asignado = adm.idUsuario
                WHERE r.idReporte = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $idReporte]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Historial de reportes contra un usuario (para panel admin)
    public function getHistoryByUser(int $userId): array
    {
        $sql = "SELECT r.idReporte, r.tipo, r.motivo, r.estado, r.prioridad, r.accion_tomada, r.creado_en, r.resuelto_en,
                       u.nombre AS reporta_nombre
                FROM {$this->table} r
                LEFT JOIN usuarios u ON r.idUsuarioQueReporta = u.idUsuario
                WHERE r.idUsuarioReportado = :uid
                ORDER BY r.creado_en DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contar sanciones previas (advertencias, eliminaciones, suspensiones, bans)
    public function countSanctionsByUser(int $userId): array
    {
        $sql = "SELECT accion_tomada, COUNT(*) as total FROM {$this->table}
                WHERE idUsuarioReportado = :uid AND estado = 'resuelto' AND accion_tomada IS NOT NULL
                GROUP BY accion_tomada";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':uid' => $userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sanctions = ['advertir' => 0, 'eliminar_contenido' => 0, 'suspender' => 0, 'banear' => 0, 'resolver' => 0];
        foreach ($rows as $row) {
            $sanctions[$row['accion_tomada']] = (int)$row['total'];
        }
        return $sanctions;
    }

    // Estadisticas generales para panel admin
    public function getStats(): array
    {
        $stats = [];

        // Totales por estado
        $stmt = $this->conn->query("SELECT estado, COUNT(*) as total FROM {$this->table} GROUP BY estado");
        $byEstado = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $byEstado[$row['estado']] = (int)$row['total'];
        }
        $stats['pendientes'] = $byEstado['pendiente'] ?? 0;
        $stats['en_revision'] = $byEstado['en_revision'] ?? 0;
        $stats['resueltos'] = $byEstado['resuelto'] ?? 0;
        $stats['total'] = array_sum($byEstado);

        // Por motivo
        $stmt = $this->conn->query("SELECT motivo, COUNT(*) as total FROM {$this->table} WHERE motivo IS NOT NULL GROUP BY motivo ORDER BY total DESC");
        $stats['por_motivo'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Tiempo medio de resolucion (en horas)
        $stmt = $this->conn->query("SELECT AVG(TIMESTAMPDIFF(HOUR, creado_en, resuelto_en)) as avg_hours FROM {$this->table} WHERE estado = 'resuelto' AND resuelto_en IS NOT NULL");
        $stats['tiempo_medio_horas'] = round((float)($stmt->fetchColumn() ?: 0), 1);

        // Usuarios mas reportados (top 5)
        $stmt = $this->conn->query("SELECT r.idUsuarioReportado, u.nombre, COUNT(*) as total_reportes,
                                           SUM(CASE WHEN r.estado IN ('pendiente','en_revision') THEN 1 ELSE 0 END) as pendientes
                                    FROM {$this->table} r
                                    LEFT JOIN usuarios u ON r.idUsuarioReportado = u.idUsuario
                                    WHERE r.idUsuarioReportado IS NOT NULL
                                    GROUP BY r.idUsuarioReportado
                                    ORDER BY total_reportes DESC LIMIT 5");
        $stats['usuarios_mas_reportados'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Reportes por semana (ultimas 8 semanas)
        $stmt = $this->conn->query("SELECT YEARWEEK(creado_en, 1) as semana,
                                           MIN(DATE(creado_en)) as fecha_inicio,
                                           COUNT(*) as total
                                    FROM {$this->table}
                                    WHERE creado_en >= DATE_SUB(NOW(), INTERVAL 8 WEEK)
                                    GROUP BY YEARWEEK(creado_en, 1)
                                    ORDER BY semana ASC");
        $stats['por_semana'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Por prioridad
        $stmt = $this->conn->query("SELECT prioridad, COUNT(*) as total FROM {$this->table} WHERE estado IN ('pendiente','en_revision') GROUP BY prioridad");
        $stats['por_prioridad'] = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $stats['por_prioridad'][$row['prioridad']] = (int)$row['total'];
        }

        return $stats;
    }
}
