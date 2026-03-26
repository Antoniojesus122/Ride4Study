<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Ride.php';

class AdminAdController {
    private PDO $db;
    private Ride $ride;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->ride = new Ride($this->db);
    }

    public function listAll(): void {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $filters = [
            'tipo'    => $_GET['tipo'] ?? '',
            'search'  => trim($_GET['search'] ?? ''),
        ];

        $ads = $this->getAllAds($page, $limit, $filters);
        $totalAds = $this->countAllAds($filters);
        $totalPages = max(1, ceil($totalAds / $limit));

        require_once __DIR__ . '/../../../views/admin/ads.view.php';
    }

    public function deleteAd(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/ads'));
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id) {
            $this->ride->deleteRide($id);
        }

        redirectWithFlash(url('/admin/ads'), 'success', 'deleted');
    }

    private function getAllAds(int $page, int $limit, array $filters): array {
        $offset = ($page - 1) * $limit;
        $query = "SELECT a.*, u.nombre as usuario_nombre, u.correo as usuario_correo,
                  lo.nombreLocalidad as nombreOrigen, ld.nombreLocalidad as nombreDestino
                  FROM anuncios a
                  JOIN usuarios u ON a.idUsuario = u.idUsuario
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  WHERE 1=1";
        $params = [];

        if (!empty($filters['tipo'])) {
            $query .= " AND LOWER(a.tipo) = :tipo";
            $params[':tipo'] = strtolower($filters['tipo']);
        }
        if (!empty($filters['search'])) {
            $query .= " AND (u.nombre LIKE :search OR lo.nombreLocalidad LIKE :search2 OR ld.nombreLocalidad LIKE :search3)";
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
            $params[':search3'] = '%' . $filters['search'] . '%';
        }

        $query .= " ORDER BY a.fechaPublicacion DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function countAllAds(array $filters): int {
        $query = "SELECT COUNT(*) FROM anuncios a
                  JOIN usuarios u ON a.idUsuario = u.idUsuario
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  WHERE 1=1";
        $params = [];

        if (!empty($filters['tipo'])) {
            $query .= " AND LOWER(a.tipo) = :tipo";
            $params[':tipo'] = strtolower($filters['tipo']);
        }
        if (!empty($filters['search'])) {
            $query .= " AND (u.nombre LIKE :search OR lo.nombreLocalidad LIKE :search2 OR ld.nombreLocalidad LIKE :search3)";
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
            $params[':search3'] = '%' . $filters['search'] . '%';
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
