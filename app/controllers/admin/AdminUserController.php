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

    // Lista de instituciones para poblar el dropdown del filtro de usuarios
    private function getInstitutionList(): array {
        $stmt = $this->db->query(
            "SELECT DISTINCT institucion FROM usuarios
             WHERE institucion IS NOT NULL AND institucion <> ''
             ORDER BY institucion ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function index(): void {
        $this->requireAdmin();
        $tab = $_GET['tab'] ?? 'todos';
        $pendingUsers = $this->user->getPendingVerifications();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $totalUsers = $this->countAllUsers();
        $totalPages = max(1, (int)ceil($totalUsers / $perPage));
        if ($page > $totalPages) $page = $totalPages;
        $allUsers = $this->getAllUsers($perPage, ($page - 1) * $perPage);
        $bannedUsers = $this->user->getBannedUsers();
        $instituciones = $this->getInstitutionList();
        require_once __DIR__ . '/../../../views/admin/users.view.php';
    }

    public function verifications(): void {
        $this->requireAdmin();
        $tab = 'verificaciones';
        $pendingUsers = $this->user->getPendingVerifications();
        $page = 1;
        $perPage = 20;
        $totalUsers = $this->countAllUsers();
        $totalPages = max(1, (int)ceil($totalUsers / $perPage));
        $allUsers = $this->getAllUsers($perPage, 0);
        $instituciones = $this->getInstitutionList();
        require_once __DIR__ . '/../../../views/admin/users.view.php';
    }

    private function countAllUsers(): int {
        [$where, $params] = $this->buildUserFiltersWhere();
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios u WHERE u.idRol != 1" . $where);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    private function buildUserFiltersWhere(): array {
        $search = trim($_GET['search'] ?? '');
        $roleFilter = $_GET['rol'] ?? '';
        $verificacionFilter = $_GET['verificacion'] ?? '';
        $premiumFilter = $_GET['premium_filter'] ?? '';
        $institucionFilter = trim($_GET['institucion'] ?? '');
        $anunciosFilter    = $_GET['anuncios'] ?? '';

        $where = '';
        $params = [];
        if ($search !== '') {
            $where .= " AND (u.nombre LIKE :s1 OR u.correo LIKE :s2)";
            $params[':s1'] = "%$search%";
            $params[':s2'] = "%$search%";
        }
        if ($roleFilter !== '') {
            $where .= " AND u.idRol = :rol";
            $params[':rol'] = (int)$roleFilter;
        }
        if ($verificacionFilter !== '') {
            $where .= " AND u.estado_verificacion = :verif";
            $params[':verif'] = (int)$verificacionFilter;
        }
        if ($premiumFilter !== '') {
            $where .= " AND u.premium = :prem";
            $params[':prem'] = (int)$premiumFilter;
        }
        if ($institucionFilter !== '') {
            $where .= " AND u.institucion = :inst";
            $params[':inst'] = $institucionFilter;
        }
        if ($anunciosFilter === 'con') {
            $where .= " AND EXISTS (SELECT 1 FROM anuncios a WHERE a.idUsuario = u.idUsuario)";
        } elseif ($anunciosFilter === 'sin') {
            $where .= " AND NOT EXISTS (SELECT 1 FROM anuncios a WHERE a.idUsuario = u.idUsuario)";
        }
        return [$where, $params];
    }

    private function getAllUsers(int $limit = 0, int $offset = 0): array {
        $search = trim($_GET['search'] ?? '');
        $roleFilter = $_GET['rol'] ?? '';
        $verificacionFilter = $_GET['verificacion'] ?? '';
        $premiumFilter = $_GET['premium_filter'] ?? '';
        $institucionFilter = trim($_GET['institucion'] ?? '');
        $anunciosFilter    = $_GET['anuncios'] ?? ''; // con | sin

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
        if ($institucionFilter !== '') {
            $query .= " AND u.institucion = :inst";
            $params[':inst'] = $institucionFilter;
        }
        // Con / sin anuncios publicados
        if ($anunciosFilter === 'con') {
            $query .= " AND EXISTS (SELECT 1 FROM anuncios a WHERE a.idUsuario = u.idUsuario)";
        } elseif ($anunciosFilter === 'sin') {
            $query .= " AND NOT EXISTS (SELECT 1 FROM anuncios a WHERE a.idUsuario = u.idUsuario)";
        }

        $query .= " ORDER BY u.idUsuario DESC";
        if ($limit > 0) {
            $query .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
        }

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
        if ($userId > 0 && in_array($newRole, [2, 4])) {
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
                fullUrl('/dashboard'),
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
                fullUrl('/profile') . '?tab=verification',
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

    // Eliminar usuario
    public function deleteUser(): void {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/users'));
            exit;
        }

        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId <= 0 || $userId === (int)$_SESSION['user_id']) {
            redirectWithFlash(url('/admin/users'), 'error', 'No puedes eliminarte a ti mismo');
        }

        $userData = $this->user->getUserById($userId);
        if (!$userData || (int)($userData['idRol'] ?? 0) === 1) {
            redirectWithFlash(url('/admin/users'), 'error', 'Usuario no encontrado o es administrador');
        }

        $stmt = $this->db->prepare("DELETE FROM usuarios WHERE idUsuario = :id AND idRol != 1");
        $stmt->execute([':id' => $userId]);

        $this->adminLog->log((int)$_SESSION['user_id'], 'eliminar_usuario', 'usuario', $userId, $userData['nombre'] . ' (' . $userData['correo'] . ')');

        redirectWithFlash(url('/admin/users'), 'success', 'deleted');
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
