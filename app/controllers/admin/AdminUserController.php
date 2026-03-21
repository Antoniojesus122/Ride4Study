<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../../services/MailService.php';

class AdminUserController {
    private $db;
    private User $user;
    private ?MailService $mailService = null;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
        try { $this->mailService = new MailService(); } catch (Exception $e) { error_log('MailService: ' . $e->getMessage()); }
    }

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
            header('Location: ' . url('/login'));
            exit;
        }
    }

    public function index(): void {
        $this->requireAdmin();
        $tab = $_GET['tab'] ?? 'todos';
        $pendingUsers = $this->user->getPendingVerifications();
        $allUsers = $this->getAllUsers();
        require_once __DIR__ . '/../../../views/admin/users.view.php';
    }

    public function verifications(): void {
        $this->requireAdmin();
        $tab = 'verificaciones';
        $pendingUsers = $this->user->getPendingVerifications();
        $allUsers = $this->getAllUsers();
        require_once __DIR__ . '/../../../views/admin/users.view.php';
    }

    private function getAllUsers(): array {
        $search = trim($_GET['search'] ?? '');
        $roleFilter = $_GET['rol'] ?? '';

        $query = "SELECT u.idUsuario, u.nombre, u.correo, u.telefono, u.ciudad, u.institucion,
                         u.estado_verificacion, u.premium, u.premium_hasta, u.creado_en,
                         u.idRol, r.nombreRol
                  FROM usuarios u
                  LEFT JOIN roles r ON u.idRol = r.idRol
                  WHERE u.idRol != 1";
        $params = [];

        if ($search !== '') {
            $query .= " AND (u.nombre LIKE :s1 OR u.correo LIKE :s2)";
            $params[':s1'] = "%$search%";
            $params[':s2'] = "%$search%";
        }
        if ($roleFilter !== '') {
            $query .= " AND u.idRol = :rol";
            $params[':rol'] = (int)$roleFilter;
        }

        $query .= " ORDER BY u.idUsuario DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateRole(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/users'));
            exit;
        }
        $userId = (int)($_POST['user_id'] ?? 0);
        $newRole = (int)($_POST['new_role'] ?? 0);
        if ($userId > 0 && in_array($newRole, [2, 3, 4])) {
            $stmt = $this->db->prepare("UPDATE usuarios SET idRol = :rol WHERE idUsuario = :id AND idRol != 1");
            $stmt->execute([':rol' => $newRole, ':id' => $userId]);
        }
        header('Location: ' . url('/admin/users') . '?tab=todos&success=role_updated');
        exit;
    }

    public function approveVerification(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/users'));
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            header('Location: ' . url('/admin/users') . '?error=invalid_user');
            exit;
        }

        $userData = $this->user->getUserById($userId);
        if (!$userData) {
            header('Location: ' . url('/admin/users') . '?error=user_not_found');
            exit;
        }

        $this->user->setVerificationStatus($userId, 2);

        // Email de verificación aprobada
        if ($this->mailService && !empty($userData['notificaciones_email'])) {
            $html = $this->mailService->generarPlantilla(
                $userData['nombre'],
                '¡Verificación aprobada!',
                'Hola <strong>' . htmlspecialchars($userData['nombre']) . '</strong>,<br><br>
                Nos complace informarte de que tu verificación de estudiante ha sido <strong style="color:#34d399;">aprobada</strong>.<br><br>
                A partir de ahora aparecerás como estudiante verificado en Ride4Study, lo que generará más confianza en la comunidad.',
                '✓ Estudiante verificado',
                url('/dashboard'),
                'Ir al panel'
            );
            $this->mailService->send($userData['correo'], $userData['nombre'], 'Verificación aprobada · Ride4Study', $html);
        }

        header('Location: ' . url('/admin/users') . '?success=approved');
        exit;
    }

    public function rejectVerification(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/users'));
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($userId <= 0) {
            header('Location: ' . url('/admin/users') . '?error=invalid_user');
            exit;
        }

        $userData = $this->user->getUserById($userId);
        if (!$userData) {
            header('Location: ' . url('/admin/users') . '?error=user_not_found');
            exit;
        }

        $this->user->setVerificationStatus($userId, 0);

        // Email de verificación rechazada
        if ($this->mailService && !empty($userData['notificaciones_email'])) {
            $reasonText = $reason
                ? '<br><br><strong>Motivo:</strong> ' . htmlspecialchars($reason)
                : '';
            $html = $this->mailService->generarPlantilla(
                $userData['nombre'],
                'Verificación no aprobada',
                'Hola <strong>' . htmlspecialchars($userData['nombre']) . '</strong>,<br><br>
                Lamentamos informarte de que tu solicitud de verificación de estudiante no ha podido ser aprobada.' . $reasonText . '<br><br>
                Puedes volver a enviar tu documentación desde tu perfil si lo deseas.',
                null,
                url('/profile') . '?tab=verification',
                'Volver a intentarlo'
            );
            $this->mailService->send($userData['correo'], $userData['nombre'], 'Verificación no aprobada · Ride4Study', $html);
        }

        header('Location: ' . url('/admin/users') . '?success=rejected');
        exit;
    }
}
