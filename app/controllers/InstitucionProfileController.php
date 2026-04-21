<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Institution.php';

// Perfil de la institucion: ver/editar datos + cambio de contraseña + logo
class InstitucionProfileController {
    private PDO $db;
    private Institution $institution;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['institution_id'])) {
            header('Location: ' . url('/institution-login'));
            exit;
        }

        $database = new Database();
        $this->db = $database->connect();
        $this->institution = new Institution($this->db);
    }

    public function index(): void {
        $inst = $this->institution->getById((int)$_SESSION['institution_id']);
        if (!$inst) {
            header('Location: ' . url('/institution-logout'));
            exit;
        }
        $flashData = getFlash();
        require_once __DIR__ . '/../../views/institucion/profile.view.php';
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/institution/profile'));
            exit;
        }

        $id = (int)$_SESSION['institution_id'];

        $data = [
            'nombre'      => trim($_POST['nombre']      ?? ''),
            'correo'      => trim($_POST['correo']      ?? ''),
            'telefono'    => trim($_POST['telefono']    ?? ''),
            'direccion'   => trim($_POST['direccion']   ?? ''),
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ];

        if ($data['nombre'] === '' || $data['correo'] === '') {
            redirectWithFlash(url('/institution/profile'), 'error', 'Nombre y correo son obligatorios.');
        }

        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            redirectWithFlash(url('/institution/profile'), 'error', 'El correo no es valido.');
        }

        // Verificar que el correo no este en uso por otra institucion
        $existing = $this->institution->getByEmail($data['correo']);
        if ($existing && (int)$existing['idInstitucion'] !== $id) {
            redirectWithFlash(url('/institution/profile'), 'error', 'Ese correo ya esta en uso.');
        }

        // Subida de logo (opcional)
        if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            ];
            $validation = validateUploadedFile($_FILES['logo'], $allowed, 2);
            if (!$validation['valid']) {
                redirectWithFlash(url('/institution/profile'), 'error', 'Logo no valido (solo JPG/PNG/WEBP, max 2MB).');
            }

            $uploadDir = __DIR__ . '/../../public/uploads/institutions/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }

            $newName = 'inst_' . $id . '_' . time() . '.' . $validation['ext'];
            $dest = $uploadDir . $newName;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $dest)) {
                // Borrar logo anterior si existe
                $prev = $this->institution->getById($id);
                if ($prev && !empty($prev['logo']) && is_file($uploadDir . $prev['logo'])) {
                    @unlink($uploadDir . $prev['logo']);
                }
                $data['logo'] = $newName;
            }
        }

        $this->institution->update($id, $data);

        // Refrescar sesion
        $_SESSION['institution_name']  = $data['nombre'];
        $_SESSION['institution_email'] = $data['correo'];

        redirectWithFlash(url('/institution/profile'), 'success', 'Perfil actualizado correctamente.');
    }

    public function changePassword(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/institution/profile'));
            exit;
        }

        $id          = (int)$_SESSION['institution_id'];
        $actual      = $_POST['actual']      ?? '';
        $nueva       = $_POST['nueva']       ?? '';
        $confirmar   = $_POST['confirmar']   ?? '';

        if ($actual === '' || $nueva === '' || $confirmar === '') {
            redirectWithFlash(url('/institution/profile'), 'error', 'Completa todos los campos de contraseña.');
        }

        $inst = $this->institution->getById($id);
        if (!$inst || !password_verify($actual, $inst['contrasena'])) {
            redirectWithFlash(url('/institution/profile'), 'error', 'La contraseña actual no es correcta.');
        }

        if (strlen($nueva) < 8) {
            redirectWithFlash(url('/institution/profile'), 'error', 'La nueva contraseña debe tener al menos 8 caracteres.');
        }

        if ($nueva !== $confirmar) {
            redirectWithFlash(url('/institution/profile'), 'error', 'La confirmacion no coincide.');
        }

        $this->institution->update($id, [
            'contrasena' => password_hash($nueva, PASSWORD_DEFAULT),
        ]);

        redirectWithFlash(url('/institution/profile'), 'success', 'Contraseña actualizada.');
    }
}
