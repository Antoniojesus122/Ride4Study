<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/Ride.php';
require_once __DIR__ . '/../../models/AdminLog.php';

class AdminAdController {
    private PDO $db;
    private Ride $ride;
    private AdminLog $adminLog;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new Database();
        $this->db = $database->connect();
        $this->ride = new Ride($this->db);
        $this->adminLog = new AdminLog($this->db);
    }

    public function listAll(): void {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;

        $period = resolvePeriod($_GET);
        $filters = [
            'tipo'       => $_GET['tipo'] ?? '',
            'search'     => trim($_GET['search'] ?? ''),
            'date_from'  => $period['from'],
            'date_to'    => $period['to'],
            'estado'     => $_GET['estado'] ?? '',
            'precio'     => $_GET['precio'] ?? '',
            'institucion'=> $_GET['institucion'] ?? '',
        ];

        $ads = $this->getAllAds($page, $limit, $filters);
        $totalAds = $this->countAllAds($filters);
        $totalPages = max(1, ceil($totalAds / $limit));

        // Listado de instituciones para el dropdown del filtro
        $instituciones = $this->db->query(
            "SELECT DISTINCT u.institucion AS nombre
             FROM anuncios a JOIN usuarios u ON a.idUsuario = u.idUsuario
             WHERE u.institucion IS NOT NULL AND u.institucion <> ''
             ORDER BY u.institucion ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

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
            $this->adminLog->log((int)$_SESSION['user_id'], 'eliminar', 'anuncio', $id, 'Anuncio eliminado por admin');
        }

        redirectWithFlash(url('/admin/ads'), 'success', 'deleted');
    }

    // Construye las clausulas WHERE comunes para listar, contar y exportar
    private function applyAdFilters(string &$query, array &$params, array $filters): void {
        if (!empty($filters['tipo'])) {
            $query .= " AND LOWER(a.tipo) = :tipo";
            $params[':tipo'] = strtolower($filters['tipo']);
        }
        if (!empty($filters['search'])) {
            $query .= " AND (u.nombre LIKE :search OR lo.nombreLocalidad LIKE :search2 OR ld.nombreLocalidad LIKE :search3)";
            $params[':search']  = '%' . $filters['search'] . '%';
            $params[':search2'] = '%' . $filters['search'] . '%';
            $params[':search3'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $query .= " AND a.fechaSalida >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $query .= " AND a.fechaSalida <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        // Estado del viaje segun fechaSalida respecto a hoy
        if (!empty($filters['estado'])) {
            switch ($filters['estado']) {
                case 'futuros':
                    $query .= " AND a.fechaSalida > CURDATE()";
                    break;
                case 'activos':
                    $query .= " AND a.fechaSalida = CURDATE()";
                    break;
                case 'pasados':
                    $query .= " AND a.fechaSalida < CURDATE()";
                    break;
            }
        }
        // Con precio / gratis
        if (!empty($filters['precio'])) {
            if ($filters['precio'] === 'con_precio') {
                $query .= " AND a.precio IS NOT NULL AND a.precio > 0";
            } elseif ($filters['precio'] === 'gratis') {
                $query .= " AND (a.precio IS NULL OR a.precio = 0)";
            }
        }
        // Institucion del autor
        if (!empty($filters['institucion'])) {
            $query .= " AND u.institucion = :institucion";
            $params[':institucion'] = $filters['institucion'];
        }
    }

    private function getAllAds(int $page, int $limit, array $filters): array {
        $offset = ($page - 1) * $limit;
        $query = "SELECT a.*, u.nombre as usuario_nombre, u.correo as usuario_correo, u.institucion AS usuario_institucion,
                  lo.nombreLocalidad as nombreOrigen, ld.nombreLocalidad as nombreDestino
                  FROM anuncios a
                  JOIN usuarios u ON a.idUsuario = u.idUsuario
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  WHERE 1=1";
        $params = [];
        $this->applyAdFilters($query, $params, $filters);

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
        $this->applyAdFilters($query, $params, $filters);

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function exportCsv(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $period = resolvePeriod($_GET);
        $filters = [
            'tipo'       => $_GET['tipo'] ?? '',
            'search'     => trim($_GET['search'] ?? ''),
            'date_from'  => $period['from'],
            'date_to'    => $period['to'],
            'estado'     => $_GET['estado'] ?? '',
            'precio'     => $_GET['precio'] ?? '',
            'institucion'=> $_GET['institucion'] ?? '',
        ];
        $ads = $this->getAllAdsNoPagination($filters);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="anuncios_ride4study_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, ['ID', 'Tipo', 'Origen', 'Destino', 'Fecha Salida', 'Usuario', 'Correo', 'Institucion', 'Precio', 'Plazas', 'Publicado'], ';');

        foreach ($ads as $ad) {
            fputcsv($output, [
                $ad['idAnuncio'],
                $ad['tipo'],
                $ad['nombreOrigen'],
                $ad['nombreDestino'],
                $ad['fechaSalida'],
                $ad['usuario_nombre'],
                $ad['usuario_correo'],
                $ad['usuario_institucion'] ?? '',
                $ad['precio'] ?? '',
                $ad['plazasDisponibles'] ?? '',
                $ad['fechaPublicacion'],
            ], ';');
        }
        fclose($output);
        exit;
    }

    private function getAllAdsNoPagination(array $filters): array {
        $query = "SELECT a.*, u.nombre as usuario_nombre, u.correo as usuario_correo, u.institucion AS usuario_institucion,
                  lo.nombreLocalidad as nombreOrigen, ld.nombreLocalidad as nombreDestino
                  FROM anuncios a
                  JOIN usuarios u ON a.idUsuario = u.idUsuario
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  WHERE 1=1";
        $params = [];
        $this->applyAdFilters($query, $params, $filters);

        $query .= " ORDER BY a.fechaPublicacion DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
