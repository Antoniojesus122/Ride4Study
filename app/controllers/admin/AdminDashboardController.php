<?php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Report.php';
require_once __DIR__ . '/../../models/Ride.php';

class AdminDashboardController
{
    private PDO $db;
    private User $user;
    private Report $report;
    private Ride $ride;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
        $this->report = new Report($this->db);
        $this->ride = new Ride($this->db);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
            header('Location: ' . url('/login'));
            exit;
        }
    }

    public function index()
    {
        // Obtener estadísticas
        $stats = $this->getStats();

        // Obtener datos para gráficos y tablas
        $recentUsers = $this->getRecentUsers();
        $pendingReports = $this->getPendingReports();
        $recentAds = $this->getRecentAds();
        $usersByRole = $this->getUsersByRole();

        $registrationsByMonth = $this->getRegistrationsByMonth();
        $ridesByMonth = $this->getRidesByMonth();
        $reportsByMonth = $this->getReportsByMonth();

        require_once __DIR__ . '/../../../views/admin/dashboard.view.php';
    }

    private function getStats(): array
    {
        $stats = [];

        // Total de usuarios
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE idRol != 1");
        $stats['users'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de anuncios
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM anuncios");
        $stats['ads'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de reportes
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM reportes");
        $stats['reports'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Reportes pendientes
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM reportes WHERE estado = 'pendiente'");
        $stats['pending_reports'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total de instituciones
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM instituciones");
        $stats['institutions'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Usuarios verificados
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE estado_verificacion = 2");
        $stats['verified_users'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Usuarios pendientes de verificación
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE estado_verificacion = 1");
        $stats['pending_verification'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return $stats;
    }

    private function getRecentUsers(): array
    {
        $stmt = $this->db->query("
            SELECT idUsuario, nombre, correo, estado_verificacion, creado_en 
            FROM usuarios 
            WHERE idRol != 1 
            ORDER BY idUsuario DESC 
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

    private function getPendingReports(): array
    {
        $stmt = $this->db->query("
            SELECT r.*, u.nombre as reportado_nombre, u2.nombre as reporta_nombre
            FROM reportes r
            LEFT JOIN usuarios u ON r.idUsuarioReportado = u.idUsuario
            LEFT JOIN usuarios u2 ON r.idUsuarioQueReporta = u2.idUsuario
            WHERE r.estado = 'pendiente'
            ORDER BY r.creado_en DESC
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

    private function getRecentAds(): array
    {
        $stmt = $this->db->query("
            SELECT a.idAnuncio, a.tipo, a.descripcion, a.precio, a.fechaPublicacion, u.nombre as usuario_nombre
            FROM anuncios a
            LEFT JOIN usuarios u ON a.idUsuario = u.idUsuario
            ORDER BY a.fechaPublicacion DESC
            LIMIT 5
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

    private function getUsersByRole(): array
    {
        $stmt = $this->db->query("
            SELECT rol.nombreRol, COUNT(u.idUsuario) as total
            FROM usuarios u
            LEFT JOIN roles rol ON u.idRol = rol.idRol
            WHERE u.idRol != 1
            GROUP BY u.idRol, rol.nombreRol
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?? [];
    }

    private function getRegistrationsByMonth(): array
    {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(creado_en, '%Y-%m') as mes, COUNT(*) as total
            FROM usuarios WHERE idRol != 1
            GROUP BY mes ORDER BY mes DESC LIMIT 12
        ");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_reverse($data);
    }

    private function getRidesByMonth(): array
    {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(fechaPublicacion, '%Y-%m') as mes, COUNT(*) as total
            FROM anuncios
            GROUP BY mes ORDER BY mes DESC LIMIT 12
        ");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_reverse($data);
    }

    private function getReportsByMonth(): array
    {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(creado_en, '%Y-%m') as mes, COUNT(*) as total
            FROM reportes
            GROUP BY mes ORDER BY mes DESC LIMIT 12
        ");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_reverse($data);
    }
}
