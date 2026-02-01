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
    public function createReport(string $tipo, ?int $idUsuarioReportado, ?int $idAnuncio, ?int $idChat, int $idUsuarioQueReporta, string $mensaje): bool
    {
        $sql = "INSERT INTO {$this->table} 
                (tipo, idUsuarioReportado, idAnuncio, idChat, idUsuarioQueReporta, mensaje)
                VALUES
                (:tipo, :idUsuarioReportado, :idAnuncio, :idChat, :idUsuarioQueReporta, :mensaje)";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':tipo' => $tipo,
            ':idUsuarioReportado' => $idUsuarioReportado,
            ':idAnuncio' => $idAnuncio,
            ':idChat' => $idChat,
            ':idUsuarioQueReporta' => $idUsuarioQueReporta,
            ':mensaje' => htmlspecialchars(strip_tags($mensaje))
        ]);
    }

    // Obtener todos los reportes
    public function getAllReports(): array
    {
        $sql = "SELECT r.*, u1.nombre AS reportado_nombre, u2.nombre AS reporta_nombre, a.tipo AS anuncio_tipo
                FROM {$this->table} r
                LEFT JOIN usuarios u1 ON r.idUsuarioReportado = u1.idUsuario
                LEFT JOIN usuarios u2 ON r.idUsuarioQueReporta = u2.idUsuario
                LEFT JOIN anuncios a ON r.idAnuncio = a.idAnuncio
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

    // Obtener reportes por tipo
    public function getReportsByType(string $tipo): array
    {
        $sql = "SELECT r.*, u1.nombre AS reportado_nombre, u2.nombre AS reporta_nombre, a.tipo AS anuncio_tipo
                FROM {$this->table} r
                LEFT JOIN usuarios u1 ON r.idUsuarioReportado = u1.idUsuario
                LEFT JOIN usuarios u2 ON r.idUsuarioQueReporta = u2.idUsuario
                LEFT JOIN anuncios a ON r.idAnuncio = a.idAnuncio
                WHERE r.tipo = :tipo
                ORDER BY r.creado_en DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':tipo' => $tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Marcar un reporte como resuelto
    public function markAsResolved(int $idReporte): bool
    {
        $sql = "UPDATE {$this->table} SET estado = 'resuelto' WHERE idReporte = :id";
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

    // Obtener un reporte por ID
    public function getReportById(int $idReporte)
    {
        $sql = "SELECT r.*, u1.nombre AS reportado_nombre, u2.nombre AS reporta_nombre, a.tipo AS anuncio_tipo
                FROM {$this->table} r
                LEFT JOIN usuarios u1 ON r.idUsuarioReportado = u1.idUsuario
                LEFT JOIN usuarios u2 ON r.idUsuarioQueReporta = u2.idUsuario
                LEFT JOIN anuncios a ON r.idAnuncio = a.idAnuncio
                WHERE r.idReporte = :id
                LIMIT 1";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $idReporte]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
