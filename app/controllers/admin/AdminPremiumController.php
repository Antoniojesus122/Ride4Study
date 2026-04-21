<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Notification.php';
require_once __DIR__ . '/../../models/AdminLog.php';
require_once __DIR__ . '/../../models/Payment.php';

class AdminPremiumController {
    private PDO $db;
    private User $user;
    private AdminLog $adminLog;
    private Payment $payment;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
        $this->adminLog = new AdminLog($this->db);
        $this->payment = new Payment($this->db);
    }

    public function listAll(): void {
        $tab = $_GET['tab'] ?? 'usuarios';

        // Filtro "Vence pronto" para la pestania de usuarios Premium
        $vencePronto = $_GET['vence'] ?? '';
        $premiumUsers = $this->getPremiumUsers($vencePronto);

        // Contador global (sin filtrar) para la tarjeta superior
        $allPremium = $this->getPremiumUsers('');
        $totalPremium = count($allPremium);
        $expiringSoon = array_filter($allPremium, function($u) {
            return $u['premium_hasta'] && strtotime($u['premium_hasta']) <= strtotime('+7 days');
        });
        $expiringCount = count($expiringSoon);

        // Historial de pagos con filtros (resolvemos el periodo con prefijo 'p')
        $paymentPage = max(1, (int)($_GET['ppage'] ?? 1));
        $paymentPeriod = resolvePeriod($_GET, 'p');
        $paymentFilters = [
            'search'    => trim($_GET['psearch'] ?? ''),
            'date_from' => $paymentPeriod['from'],
            'date_to'   => $paymentPeriod['to'],
            'origen'    => $_GET['porigen'] ?? '',
            'estado'    => $_GET['pestado'] ?? '',
        ];
        $payments = $this->payment->getAll($paymentFilters, $paymentPage, 20);
        $totalPayments = $this->payment->countAll($paymentFilters);
        $totalPaymentPages = max(1, ceil($totalPayments / 20));
        $paymentStats = $this->payment->getStats();

        require_once __DIR__ . '/../../../views/admin/premium.view.php';
    }

    public function grant(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/premium'));
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $days = max(1, (int)($_POST['days'] ?? 30));

        if (!$userId) {
            redirectWithFlash(url('/admin/premium'), 'error', 'user_not_found');
        }

        $stmt = $this->db->prepare(
            "UPDATE usuarios SET premium = 1, premium_hasta = DATE_ADD(NOW(), INTERVAL :days DAY) WHERE idUsuario = :id"
        );
        $stmt->execute([':days' => $days, ':id' => $userId]);

        // Registrar en historial de pagos como concesion manual
        $this->payment->create($userId, null, null, 0.00, 'eur', 'completado', 'admin');

        // Notificacion in-app
        $notification = new Notification($this->db);
        $notification->create(
            $userId,
            'Se te ha concedido Premium por ' . $days . ' dias desde administracion.',
            'fas fa-crown',
            url('/premium')
        );

        $this->adminLog->log((int)$_SESSION['user_id'], 'conceder_premium', 'usuario', $userId, "$days dias");
        redirectWithFlash(url('/admin/premium'), 'success', 'granted');
    }

    public function revoke(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/premium'));
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if (!$userId) {
            header('Location: ' . url('/admin/premium'));
            exit;
        }

        $stmt = $this->db->prepare(
            "UPDATE usuarios SET premium = 0, premium_hasta = NULL WHERE idUsuario = :id"
        );
        $stmt->execute([':id' => $userId]);

        $notification = new Notification($this->db);
        $notification->create(
            $userId,
            'Tu suscripcion Premium ha sido revocada por un administrador.',
            'fas fa-crown',
            url('/premium')
        );

        $this->adminLog->log((int)$_SESSION['user_id'], 'revocar_premium', 'usuario', $userId, '');
        redirectWithFlash(url('/admin/premium'), 'success', 'revoked');
    }

    private function getPremiumUsers(string $vence = ''): array {
        $sql = "SELECT idUsuario, nombre, correo, premium, premium_hasta, creado_en
                FROM usuarios WHERE premium = 1";
        if ($vence === '7') {
            $sql .= " AND premium_hasta IS NOT NULL AND premium_hasta <= DATE_ADD(NOW(), INTERVAL 7 DAY)";
        } elseif ($vence === '30') {
            $sql .= " AND premium_hasta IS NOT NULL AND premium_hasta <= DATE_ADD(NOW(), INTERVAL 30 DAY)";
        }
        $sql .= " ORDER BY premium_hasta ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchUsers(): void {
        $q = trim($_GET['q'] ?? '');
        $results = [];
        if (strlen($q) >= 2) {
            $stmt = $this->db->prepare(
                "SELECT idUsuario, nombre, correo FROM usuarios
                 WHERE idRol != 1 AND premium = 0 AND (nombre LIKE :q OR correo LIKE :q2)
                 LIMIT 10"
            );
            $stmt->execute([':q' => "%$q%", ':q2' => "%$q%"]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        header('Content-Type: application/json');
        echo json_encode($results);
        exit;
    }
}
