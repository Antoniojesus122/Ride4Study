<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/User.php';

class AdminProfileController {
    private PDO $db;
    private User $user;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
    }

    public function index(): void {
        $userId = (int)$_SESSION['user_id'];
        $adminData = $this->user->getUserById($userId);

        // Estadisticas propias del admin (para el hero del perfil)
        $totalAcciones = (int)$this->db->query(
            "SELECT COUNT(*) FROM admin_logs WHERE idAdmin = " . $userId
        )->fetchColumn();

        $stmt = $this->db->prepare("SELECT creado_en FROM admin_logs WHERE idAdmin = :id ORDER BY creado_en DESC LIMIT 1");
        $stmt->execute([':id' => $userId]);
        $ultimaAccion = $stmt->fetchColumn() ?: null;

        $stmt = $this->db->prepare("SELECT accion, entidad, creado_en FROM admin_logs WHERE idAdmin = :id ORDER BY creado_en DESC LIMIT 5");
        $stmt->execute([':id' => $userId]);
        $ultimasAcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $flash = getFlash();
        $successMsg = ($flash && $flash['type'] === 'success') ? $flash['message'] : null;
        $errorMsg = ($flash && $flash['type'] === 'error') ? $flash['message'] : null;
        require_once __DIR__ . '/../../../views/admin/profile.view.php';
    }

    public function updateInfo(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/profile'));
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if (!$nombre || !$correo) {
            redirectWithFlash(url('/admin/profile'), 'error', 'campos_obligatorios');
        }

        // Verificar email unico
        $existing = $this->db->prepare("SELECT idUsuario FROM usuarios WHERE correo = :c AND idUsuario != :id");
        $existing->execute([':c' => $correo, ':id' => $userId]);
        if ($existing->fetch()) {
            redirectWithFlash(url('/admin/profile'), 'error', 'correo_en_uso');
        }

        $stmt = $this->db->prepare(
            "UPDATE usuarios SET nombre = :nombre, correo = :correo, telefono = :telefono WHERE idUsuario = :id"
        );
        $stmt->execute([':nombre' => $nombre, ':correo' => $correo, ':telefono' => $telefono, ':id' => $userId]);

        $_SESSION['user_name'] = $nombre;

        redirectWithFlash(url('/admin/profile'), 'success', 'info_updated');
    }

    public function changePassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/profile'));
            exit;
        }

        $userId = (int)$_SESSION['user_id'];
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$current || !$new || !$confirm) {
            redirectWithFlash(url('/admin/profile'), 'error', 'campos_obligatorios');
        }

        if ($new !== $confirm) {
            redirectWithFlash(url('/admin/profile'), 'error', 'passwords_no_coinciden');
        }

        if (strlen($new) < 12) {
            redirectWithFlash(url('/admin/profile'), 'error', 'password_corta');
        }

        $hash = $this->user->getPasswordHash($userId);
        if (!password_verify($current, $hash)) {
            redirectWithFlash(url('/admin/profile'), 'error', 'password_incorrecta');
        }

        $this->user->updatePassword($userId, $new);

        redirectWithFlash(url('/admin/profile'), 'success', 'password_updated');
    }
}
