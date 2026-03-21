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
        $adminData = $this->user->getUserById((int)$_SESSION['user_id']);
        $successMsg = $_GET['success'] ?? null;
        $errorMsg = $_GET['error'] ?? null;
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
            header('Location: ' . url('/admin/profile') . '?error=campos_obligatorios');
            exit;
        }

        // Verificar email unico
        $existing = $this->db->prepare("SELECT idUsuario FROM usuarios WHERE correo = :c AND idUsuario != :id");
        $existing->execute([':c' => $correo, ':id' => $userId]);
        if ($existing->fetch()) {
            header('Location: ' . url('/admin/profile') . '?error=correo_en_uso');
            exit;
        }

        $stmt = $this->db->prepare(
            "UPDATE usuarios SET nombre = :nombre, correo = :correo, telefono = :telefono WHERE idUsuario = :id"
        );
        $stmt->execute([':nombre' => $nombre, ':correo' => $correo, ':telefono' => $telefono, ':id' => $userId]);

        $_SESSION['user_name'] = $nombre;

        header('Location: ' . url('/admin/profile') . '?success=info_updated');
        exit;
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
            header('Location: ' . url('/admin/profile') . '?error=campos_obligatorios');
            exit;
        }

        if ($new !== $confirm) {
            header('Location: ' . url('/admin/profile') . '?error=passwords_no_coinciden');
            exit;
        }

        if (strlen($new) < 6) {
            header('Location: ' . url('/admin/profile') . '?error=password_corta');
            exit;
        }

        $hash = $this->user->getPasswordHash($userId);
        if (!password_verify($current, $hash)) {
            header('Location: ' . url('/admin/profile') . '?error=password_incorrecta');
            exit;
        }

        $this->user->updatePassword($userId, $new);

        header('Location: ' . url('/admin/profile') . '?success=password_updated');
        exit;
    }
}
