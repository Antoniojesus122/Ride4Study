<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Notification.php';
require_once __DIR__ . '/../../models/AdminLog.php';
require_once __DIR__ . '/../../../services/MailService.php';

class AdminUserController {
    private $db;
    private User $user;
    private AdminLog $adminLog;
    private ?MailService $mailService = null;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
        $this->adminLog = new AdminLog($this->db);
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
        $bannedUsers = $this->user->getBannedUsers();
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
        $verificacionFilter = $_GET['verificacion'] ?? '';
        $premiumFilter = $_GET['premium_filter'] ?? '';

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
        if ($verificacionFilter !== '') {
            $query .= " AND u.estado_verificacion = :verif";
            $params[':verif'] = (int)$verificacionFilter;
        }
        if ($premiumFilter !== '') {
            $query .= " AND u.premium = :prem";
            $params[':prem'] = (int)$premiumFilter;
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
            $this->adminLog->log((int)$_SESSION['user_id'], 'cambiar_rol', 'usuario', $userId, "Nuevo rol: $newRole");
        }
        redirectWithFlash(url('/admin/users'), 'success', 'role_updated', 'todos');
    }

    public function approveVerification(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/users'));
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            redirectWithFlash(url('/admin/users'), 'error', 'invalid_user');
        }

        $userData = $this->user->getUserById($userId);
        if (!$userData) {
            redirectWithFlash(url('/admin/users'), 'error', 'user_not_found');
        }

        $this->user->setVerificationStatus($userId, 2);
        $this->adminLog->log((int)$_SESSION['user_id'], 'aprobar_verificacion', 'usuario', $userId, $userData['nombre']);

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

        redirectWithFlash(url('/admin/users'), 'success', 'approved');
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
            redirectWithFlash(url('/admin/users'), 'error', 'invalid_user');
        }

        $userData = $this->user->getUserById($userId);
        if (!$userData) {
            redirectWithFlash(url('/admin/users'), 'error', 'user_not_found');
        }

        $this->user->setVerificationStatus($userId, 0);
        $this->adminLog->log((int)$_SESSION['user_id'], 'rechazar_verificacion', 'usuario', $userId, $userData['nombre'] . ($reason ? ": $reason" : ''));

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

        redirectWithFlash(url('/admin/users'), 'success', 'rejected');
    }

    // Banear/suspender usuario
    public function banUser(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/users'));
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $duracion = $_POST['duracion'] ?? 'permanente';

        if ($userId <= 0 || empty($motivo)) {
            redirectWithFlash(url('/admin/users'), 'error', 'invalid_data', 'baneados');
        }

        $userData = $this->user->getUserById($userId);
        if (!$userData) {
            redirectWithFlash(url('/admin/users'), 'error', 'user_not_found', 'baneados');
        }

        // Calcular fecha límite del ban
        $hasta = null;
        if ($duracion !== 'permanente') {
            $dias = (int)$duracion;
            if ($dias > 0) {
                $hasta = date('Y-m-d H:i:s', strtotime("+{$dias} days"));
            }
        }

        $this->user->banUser($userId, $motivo, $hasta);
        $this->adminLog->log((int)$_SESSION['user_id'], 'banear', 'usuario', $userId, $userData['nombre'] . " - $motivo ($duracion)");

        // Notificación in-app
        $notification = new Notification($this->db);
        $notification->create(
            $userId,
            'Tu cuenta ha sido suspendida. Motivo: ' . htmlspecialchars($motivo),
            'fas fa-ban',
            url('/support')
        );

        // Email al usuario baneado
        if ($this->mailService && !empty($userData['notificaciones_email'])) {
            $hastaText = $hasta ? 'hasta el ' . date('d/m/Y H:i', strtotime($hasta)) : 'de forma indefinida';
            $html = $this->mailService->generarPlantilla(
                $userData['nombre'],
                'Cuenta suspendida',
                '<p>Tu cuenta en Ride4Study ha sido suspendida ' . $hastaText . '.</p>
                <p><strong>Motivo:</strong> ' . htmlspecialchars($motivo) . '</p>
                <p style="color:#94a3b8;">Si consideras que es un error, contacta con soporte.</p>',
                null,
                fullUrl('/support'),
                'Contactar soporte'
            );
            $this->mailService->send($userData['correo'], $userData['nombre'], 'Cuenta suspendida · Ride4Study', $html);
        }

        redirectWithFlash(url('/admin/users'), 'success', 'banned', 'baneados');
    }

    // Desbanear usuario
    public function unbanUser(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/users'));
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0) {
            redirectWithFlash(url('/admin/users'), 'error', 'invalid_data', 'baneados');
        }

        $userData = $this->user->getUserById($userId);
        $this->user->unbanUser($userId);
        $this->adminLog->log((int)$_SESSION['user_id'], 'desbanear', 'usuario', $userId, $userData['nombre'] ?? '');

        // Notificación in-app
        $notification = new Notification($this->db);
        $notification->create($userId, 'Tu cuenta ha sido reactivada. Ya puedes usar Ride4Study con normalidad.', 'fas fa-check-circle', url('/dashboard'));

        // Email
        if ($this->mailService && $userData && !empty($userData['notificaciones_email'])) {
            $html = $this->mailService->generarPlantilla(
                $userData['nombre'],
                'Cuenta reactivada',
                '<p>Tu cuenta en Ride4Study ha sido reactivada. Ya puedes volver a iniciar sesion y usar la plataforma con normalidad.</p>',
                null,
                fullUrl('/login'),
                'Iniciar sesion'
            );
            $this->mailService->send($userData['correo'], $userData['nombre'], 'Cuenta reactivada · Ride4Study', $html);
        }

        redirectWithFlash(url('/admin/users'), 'success', 'unbanned', 'baneados');
    }

    // Exportar usuarios en CSV
    public function exportCsv(): void {
        $this->requireAdmin();

        $users = $this->getAllUsers();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="usuarios_ride4study_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        // BOM UTF-8 para Excel
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeceras
        fputcsv($output, ['ID', 'Nombre', 'Correo', 'Telefono', 'Ciudad', 'Rol', 'Verificacion', 'Premium', 'Premium hasta', 'Registro'], ';');

        foreach ($users as $u) {
            $verificacion = match((int)($u['estado_verificacion'] ?? 0)) {
                2 => 'Verificado',
                1 => 'Pendiente',
                default => 'No verificado'
            };
            fputcsv($output, [
                $u['idUsuario'],
                $u['nombre'],
                $u['correo'],
                $u['telefono'] ?? '',
                $u['ciudad'] ?? '',
                $u['nombreRol'] ?? 'Usuario',
                $verificacion,
                $u['premium'] ? 'Si' : 'No',
                $u['premium_hasta'] ?? '',
                $u['creado_en']
            ], ';');
        }

        fclose($output);
        exit;
    }
}
