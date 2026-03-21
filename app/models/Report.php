<?php

class Report
{
    private PDO $conn;
    private string $table = 'reportes';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    // Crear un nuevo reporte
    public function createReport(string $tipo, ?int $idUsuarioReportado, ?int $idAnuncio, ?int $idChat, int $idUsuarioQueReporta, string $mensaje, string $motivo = ''): bool
    {
        $sql = "INSERT INTO {$this->table}
                (tipo, idUsuarioReportado, idAnuncio, idChat, idUsuarioQueReporta, mensaje, motivo)
                VALUES
                (:tipo, :idUsuarioReportado, :idAnuncio, :idChat, :idUsuarioQueReporta, :mensaje, :motivo)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':tipo' => $tipo,
            ':idUsuarioReportado' => $idUsuarioReportado,
            ':idAnuncio' => $idAnuncio,
            ':idChat' => $idChat,
            ':idUsuarioQueReporta' => $idUsuarioQueReporta,
            ':mensaje' => htmlspecialchars(strip_tags($mensaje)),
            ':motivo' => $motivo ?: null,
        ]);
    }

    // Comprobar si ya existe un reporte pendiente del mismo usuario al mismo objetivo
    public function existsPending(string $tipo, ?int $idUsuarioReportado, ?int $idAnuncio, ?int $idChat, int $idUsuarioQueReporta): bool
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE tipo = :tipo AND idUsuarioQueReporta = :reporter AND estado = 'pendiente'";
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
                       lo.nombreLocalidad AS anuncio_origen, ld.nombreLocalidad AS anuncio_destino
                FROM {$this->table} r
                LEFT JOIN usuarios u1 ON r.idUsuarioReportado = u1.idUsuario
                LEFT JOIN usuarios u2 ON r.idUsuarioQueReporta = u2.idUsuario
                LEFT JOIN anuncios a ON r.idAnuncio = a.idAnuncio
                LEFT JOIN localidades lo ON a.origen = lo.idLocalidad
                LEFT JOIN localidades ld ON a.destino = ld.idLocalidad
                ORDER BY r.creado_en DESC";

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
                       lo.nombreLocalidad AS anuncio_origen, ld.nombreLocalidad AS anuncio_destino
                FROM {$this->table} r
                LEFT JOIN usuarios u1 ON r.idUsuarioReportado = u1.idUsuario
                LEFT JOIN usuarios u2 ON r.idUsuarioQueReporta = u2.idUsuario
                LEFT JOIN anuncios a ON r.idAnuncio = a.idAnuncio
                LEFT JOIN localidades lo ON a.origen = lo.idLocalidad
                LEFT JOIN localidades ld ON a.destino = ld.idLocalidad
                WHERE r.tipo = :tipo";
        $params = [':tipo' => $tipo];

        if ($estado) {
            $sql .= " AND r.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY FIELD(r.estado, 'pendiente', 'resuelto'), r.creado_en DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Marcar como resuelto con nota del admin
    public function markAsResolved(int $idReporte, string $notaAdmin = ''): bool
    {
        $sql = "UPDATE {$this->table} SET estado = 'resuelto', nota_admin = :nota WHERE idReporte = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $idReporte, ':nota' => $notaAdmin ?: null]);
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
                       lo.nombreLocalidad AS anuncio_origen, ld.nombreLocalidad AS anuncio_destino
                FROM {$this->table} r
                LEFT JOIN usuarios u1 ON r.idUsuarioReportado = u1.idUsuario
                LEFT JOIN usuarios u2 ON r.idUsuarioQueReporta = u2.idUsuario
                LEFT JOIN anuncios a ON r.idAnuncio = a.idAnuncio
                LEFT JOIN localidades lo ON a.origen = lo.idLocalidad
                LEFT JOIN localidades ld ON a.destino = ld.idLocalidad
                WHERE r.idReporte = :id
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $idReporte]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
