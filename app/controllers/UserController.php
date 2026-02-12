<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Rating.php';

class UserController {
    private $db;
    private $user;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $database = new Database();
        $this->db = $database->connect();
        $this->user = new User($this->db);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
        
        $viewUserId = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
        $isOwnProfile = ($viewUserId === $_SESSION['user_id']);
        
        $profileUser = $this->user->getUserById($viewUserId);
        
        if (!$profileUser) {
            header('Location: dashboard.php');
            exit;
        }

        // Estadísticas: valoracion promedio y últimas valoraciones
        $ratingModel = new Rating($this->db);
        $userStats['valoracion_promedio'] = round($ratingModel->getAverage($viewUserId), 1);
        $ratings = $ratingModel->getByUser($viewUserId, 10);

        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        require_once __DIR__ . '/../../views/user/profile.view.php';
    }

    public function update()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: login.php');
            exit;
        }

        $id = $_SESSION['user_id'];

        $data = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
            'biografia' => trim($_POST['biografia'] ?? ''),
            'vehiculo' => trim($_POST['vehiculo'] ?? ''),
            'institucion' => trim($_POST['institucion'] ?? ''),
            'ciudad' => trim($_POST['ciudad'] ?? '')
        ];

        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            header('Location: profile.php?error=invalid_email');
            exit;
        }

        // Foto de perfil
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

            if (
                $_FILES['foto_perfil']['size'] <= 2097152 &&
                in_array(mime_content_type($_FILES['foto_perfil']['tmp_name']), $allowedTypes, true)
            ) {
                $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

                $fileName = uniqid('profile_') . '.jpg';
                move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $uploadDir . $fileName);

                $data['foto_perfil'] = $fileName;
            }
        }

        if ($this->user->updateUser($id, $data)) {
            $_SESSION['user_name'] = $data['nombre'];
            if (isset($data['foto_perfil'])) {
                $_SESSION['user_photo'] = $data['foto_perfil'];
            }
            header('Location: profile.php?success=updated');
            exit;
        }

        header('Location: profile.php?error=update_failed');
        exit;
    }


    public function changePassword()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: profile.php');
            exit;
        }

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($current) || empty($new) || empty($confirm)) {
            header('Location: profile.php?error=empty_fields&tab=security');
            exit;
        }

        if ($new !== $confirm) {
            header('Location: profile.php?error=password_mismatch&tab=security');
            exit;
        }

        if (strlen($new) < 8) {
            header('Location: profile.php?error=password_too_short&tab=security');
            exit;
        }

        // Verificar contraseña actual
        if (!$this->user->verifyPassword($_SESSION['user_id'], $current)) {
            header('Location: profile.php?error=wrong_password&tab=security');
            exit;
        }

        // Actualizar contraseña
        if (!$this->user->updatePassword($_SESSION['user_id'], $new)) {
            header('Location: profile.php?error=update_failed&tab=security');
            exit;
        }

        header('Location: profile.php?success=password_updated&tab=security');
        exit;
    }

    public function verify() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             exit;
        }

        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
              $uploadDir = __DIR__ . '/../../public/uploads/verification/';
              if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
              
              $fileName = uniqid() . '-' . basename($_FILES['document']['name']);
              if (move_uploaded_file($_FILES['document']['tmp_name'], $uploadDir . $fileName)) {
                   $this->user->submitVerification($_SESSION['user_id'], $fileName);
                   header('Location: profile.php?success=verification_sent&tab=verification');
              } else {
                   header('Location: profile.php?error=upload_failed&tab=verification');
              }
        } else {
             header('Location: profile.php?error=no_file&tab=verification');
        }
    }

    public function updatePrivacy() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             exit;
        }

        $id = $_SESSION['user_id'];
        $data = [
            'visibilidad_perfil' => $_POST['visibilidad_perfil'] ?? 'public',
            'visibilidad_telefono' => $_POST['visibilidad_telefono'] ?? 'rides_only',
            'notificaciones_email' => isset($_POST['notificaciones_email']) ? 1 : 0
        ];

        if ($this->user->updateUser($id, $data)) {
            header('Location: profile.php?success=privacy_updated&tab=privacy');
            exit;
        } else {
            header('Location: profile.php?error=update_failed&tab=privacy');
            exit;
        }
    }
}
