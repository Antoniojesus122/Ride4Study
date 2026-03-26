<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Notification.php';

class AdminPremiumController {
    private PDO $db;
    private User $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
    }

    public function listAll(): void {
        $premiumUsers = $this->getPremiumUsers();
        $totalPremium = count($premiumUsers);
        $expiringSoon = array_filter($premiumUsers, function($u) {
            return $u['premium_hasta'] && strtotime($u['premium_hasta']) <= strtotime('+7 days');
        });
        $expiringCount = count($expiringSoon);

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

        // Notificacion in-app
        $notification = new Notification($this->db);
        $notification->create(
            $userId,
            'Se te ha concedido Premium por ' . $days . ' dias desde administracion.',
            'fas fa-crown',
            url('/premium')
        );

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

        redirectWithFlash(url('/admin/premium'), 'success', 'revoked');
    }

    private function getPremiumUsers(): array {
        $stmt = $this->db->query(
            "SELECT idUsuario, nombre, correo, premium, premium_hasta, creado_en
             FROM usuarios WHERE premium = 1 ORDER BY premium_hasta ASC"
        );
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
