<?php

class AdminLog
{
    private PDO $conn;
    private string $table = 'admin_logs';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    // Registrar una accion administrativa
    public function log(int $adminId, string $accion, string $entidad, ?int $idEntidad = null, ?string $detalles = null): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        $sql = "INSERT INTO {$this->table} (idAdmin, accion, entidad, idEntidad, detalles, ip)
                VALUES (:idAdmin, :accion, :entidad, :idEntidad, :detalles, :ip)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':idAdmin'    => $adminId,
            ':accion'     => $accion,
            ':entidad'    => $entidad,
            ':idEntidad'  => $idEntidad,
            ':detalles'   => $detalles,
            ':ip'         => $ip,
        ]);
    }

    // Obtener logs paginados con filtros y nombre del admin
    public function getAll(array $filters = [], int $page = 1, int $limit = 30): array
    {
        $offset = ($page - 1) * $limit;
        $params = [];

        $sql = "SELECT l.*, u.nombre AS admin_nombre
                FROM {$this->table} l
                LEFT JOIN usuarios u ON l.idAdmin = u.idUsuario
                WHERE 1=1";

        $sql .= $this->buildWhereClause($filters, $params);

        $sql .= " ORDER BY l.creado_en DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contar logs segun filtros para paginacion
    public function countAll(array $filters = []): int
    {
        $params = [];

        $sql = "SELECT COUNT(*) FROM {$this->table} l WHERE 1=1";

        $sql .= $this->buildWhereClause($filters, $params);

        $stmt = $this->conn->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // Obtener acciones distintas para dropdown de filtros
    public function getActions(): array
    {
        $sql = "SELECT DISTINCT accion FROM {$this->table} ORDER BY accion ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Obtener logs recientes para widget del dashboard
    public function getRecent(int $limit = 10): array
    {
        $sql = "SELECT l.*, u.nombre AS admin_nombre
                FROM {$this->table} l
                LEFT JOIN usuarios u ON l.idAdmin = u.idUsuario
                ORDER BY l.creado_en DESC
                LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Construir clausulas WHERE a partir de filtros
    private function buildWhereClause(array $filters, array &$params): string
    {
        $sql = '';

        if (!empty($filters['date_from'])) {
            $sql .= " AND l.creado_en >= :date_from";
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND l.creado_en <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['entidad'])) {
            $sql .= " AND l.entidad = :entidad";
            $params[':entidad'] = $filters['entidad'];
        }

        if (!empty($filters['accion'])) {
            $sql .= " AND l.accion = :accion";
            $params[':accion'] = $filters['accion'];
        }

        if (!empty($filters['admin_id'])) {
            $sql .= " AND l.idAdmin = :admin_id";
            $params[':admin_id'] = (int) $filters['admin_id'];
        }

        return $sql;
    }
}
