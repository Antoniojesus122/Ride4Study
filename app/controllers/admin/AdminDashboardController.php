<?php

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Report.php';
require_once __DIR__ . '/../../models/Ride.php';
require_once __DIR__ . '/../../models/AdminLog.php';
require_once __DIR__ . '/../../models/MensajeInstitucion.php';
require_once __DIR__ . '/../../models/Payment.php';

class AdminDashboardController
{
    private PDO $db;
    private User $user;
    private Report $report;
    private Ride $ride;
    private AdminLog $adminLog;
    private MensajeInstitucion $mensajeInst;
    private Payment $payment;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
        $this->report = new Report($this->db);
        $this->ride = new Ride($this->db);
        $this->adminLog = new AdminLog($this->db);
        $this->mensajeInst = new MensajeInstitucion($this->db);
        $this->payment = new Payment($this->db);

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
        // KPIs generales
        $stats = $this->getStats();

        // Datos para tablas y gráficos
        $recentUsers = $this->getRecentUsers();
        $pendingReports = $this->getPendingReports();
        $recentAds = $this->getRecentAds();
        $recentLogs = $this->adminLog->getRecent(6);

        $registrationsByMonth = $this->getRegistrationsByMonth();
        $ridesByMonth = $this->getRidesByMonth();
        $reportsByMonth = $this->getReportsByMonth();
        $reportsByState = $this->getReportsByState();

        require __DIR__ . '/../../../views/admin/dashboard.view.php';
    }

    private function getStats(): array
    {
        $stats = [];

        // Totales base
        $stats['users']       = (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE idRol != 1")->fetchColumn();
        $stats['ads']         = (int)$this->db->query("SELECT COUNT(*) FROM anuncios")->fetchColumn();
        $stats['reports']     = (int)$this->db->query("SELECT COUNT(*) FROM reportes")->fetchColumn();
        $stats['institutions']= (int)$this->db->query("SELECT COUNT(*) FROM instituciones")->fetchColumn();

        // Estados de reportes
        $stats['pending_reports'] = (int)$this->db->query("SELECT COUNT(*) FROM reportes WHERE estado = 'pendiente'")->fetchColumn();

        // Verificaciones
        $stats['verified_users']      = (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE estado_verificacion = 2")->fetchColumn();
        $stats['pending_verification']= (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE estado_verificacion = 1")->fetchColumn();

        // Premium activos y expirando en 7 dias
        $stats['premium_active']   = (int)$this->db->query("SELECT COUNT(*) FROM usuarios WHERE premium = 1")->fetchColumn();
        $stats['premium_expiring'] = (int)$this->db->query(
            "SELECT COUNT(*) FROM usuarios
             WHERE premium = 1 AND premium_hasta IS NOT NULL
               AND premium_hasta <= DATE_ADD(NOW(), INTERVAL 7 DAY)"
        )->fetchColumn();

        // Ingresos del mes actual (pagos completados)
        $stats['revenue_month'] = (float)$this->db->query(
            "SELECT COALESCE(SUM(importe), 0) FROM pagos_premium
             WHERE estado = 'completado'
               AND YEAR(creado_en) = YEAR(NOW())
               AND MONTH(creado_en) = MONTH(NOW())"
        )->fetchColumn();

        // Hilos de mensajes sin leer (institucion -> admin)
        $stats['messages_unread'] = $this->mensajeInst->totalNoLeidos();

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

    private function getRegistrationsByMonth(): array
    {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(creado_en, '%Y-%m') as mes, COUNT(*) as total
            FROM usuarios WHERE idRol != 1
            GROUP BY mes ORDER BY mes DESC LIMIT 12
        ");
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getRidesByMonth(): array
    {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(fechaPublicacion, '%Y-%m') as mes, COUNT(*) as total
            FROM anuncios
            GROUP BY mes ORDER BY mes DESC LIMIT 12
        ");
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function getReportsByMonth(): array
    {
        $stmt = $this->db->query("
            SELECT DATE_FORMAT(creado_en, '%Y-%m') as mes, COUNT(*) as total
            FROM reportes
            GROUP BY mes ORDER BY mes DESC LIMIT 12
        ");
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    // Distribucion de reportes por estado (para donut)
    private function getReportsByState(): array
    {
        $stmt = $this->db->query("
            SELECT estado, COUNT(*) as total
            FROM reportes
            GROUP BY estado
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = ['pendiente' => 0, 'en_revision' => 0, 'resuelto' => 0, 'descartado' => 0];
        foreach ($rows as $r) {
            $key = strtolower($r['estado']);
            if (isset($result[$key])) $result[$key] = (int)$r['total'];
        }
        return $result;
    }
}
