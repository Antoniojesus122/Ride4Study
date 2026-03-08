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

    public function verifications(): void {
        $this->requireAdmin();
        $pendingUsers = $this->user->getPendingVerifications();
        require_once __DIR__ . '/../../../views/admin/users.view.php';
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
