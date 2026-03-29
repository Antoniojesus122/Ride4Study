<?php

class Payment
{
    private PDO $conn;
    private string $table = 'pagos_premium';

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    // Crear un nuevo registro de pago
    public function create(int $idUsuario, ?string $sessionId, ?string $paymentIntent, float $importe = 4.99, string $moneda = 'eur', string $estado = 'completado', string $origen = 'stripe'): bool
    {
        $sql = "INSERT INTO {$this->table}
                (idUsuario, stripe_session_id, stripe_payment_intent, importe, moneda, estado, origen)
                VALUES
                (:idUsuario, :sessionId, :paymentIntent, :importe, :moneda, :estado, :origen)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':idUsuario' => $idUsuario,
            ':sessionId' => $sessionId,
            ':paymentIntent' => $paymentIntent,
            ':importe' => $importe,
            ':moneda' => $moneda,
            ':estado' => $estado,
            ':origen' => $origen,
        ]);
    }

    // Obtener todos los pagos con paginacion y filtros, JOIN con usuarios
    public function getAll(array $filters = [], int $page = 1, int $limit = 20): array
    {
        $sql = "SELECT p.*, u.nombre AS usuario_nombre, u.correo AS usuario_correo
                FROM {$this->table} p
                LEFT JOIN usuarios u ON p.idUsuario = u.idUsuario
                WHERE 1=1";
        $params = [];

        $this->applyFilters($sql, $params, $filters);

        $sql .= " ORDER BY p.creado_en DESC";

        $offset = ($page - 1) * $limit;
        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contar total de pagos con filtros
    public function countAll(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} p
                LEFT JOIN usuarios u ON p.idUsuario = u.idUsuario
                WHERE 1=1";
        $params = [];

        $this->applyFilters($sql, $params, $filters);

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // Aplicar filtros comunes a las consultas
    private function applyFilters(string &$sql, array &$params, array $filters): void
    {
        if (!empty($filters['date_from'])) {
            $sql .= " AND p.creado_en >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND p.creado_en <= :date_to";
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['estado'])) {
            $sql .= " AND p.estado = :estado";
            $params[':estado'] = $filters['estado'];
        }

        if (!empty($filters['origen'])) {
            $sql .= " AND p.origen = :origen";
            $params[':origen'] = $filters['origen'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (u.nombre LIKE :search OR u.correo LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
    }

    // Obtener todos los pagos de un usuario
    public function getByUser(int $idUsuario): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE idUsuario = :idUsuario
                ORDER BY creado_en DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':idUsuario' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ingresos totales de pagos completados
    public function getTotalRevenue(): float
    {
        $sql = "SELECT COALESCE(SUM(importe), 0) FROM {$this->table} WHERE estado = 'completado'";
        $stmt = $this->conn->query($sql);
        return (float) $stmt->fetchColumn();
    }

    // Ingresos agrupados por mes (ultimos 6 meses)
    public function getRevenueByMonth(): array
    {
        $sql = "SELECT DATE_FORMAT(creado_en, '%Y-%m') AS mes,
                       SUM(importe) AS total,
                       COUNT(*) AS num_pagos
                FROM {$this->table}
                WHERE estado = 'completado'
                  AND creado_en >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY DATE_FORMAT(creado_en, '%Y-%m')
                ORDER BY mes ASC";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Estadisticas generales de pagos
    public function getStats(): array
    {
        $stats = [];

        // Total de pagos
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table}");
        $stats['total_payments'] = (int) $stmt->fetchColumn();

        // Ingresos totales (completados)
        $stats['total_revenue'] = $this->getTotalRevenue();

        // Pagos de este mes
        $stmt = $this->conn->query("SELECT COUNT(*) FROM {$this->table}
                                    WHERE YEAR(creado_en) = YEAR(NOW()) AND MONTH(creado_en) = MONTH(NOW())");
        $stats['payments_this_month'] = (int) $stmt->fetchColumn();

        // Importe medio por pago (completados)
        $stmt = $this->conn->query("SELECT COALESCE(AVG(importe), 0) FROM {$this->table} WHERE estado = 'completado'");
        $stats['avg_payment_amount'] = round((float) $stmt->fetchColumn(), 2);

        return $stats;
    }
}
