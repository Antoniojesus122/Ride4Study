<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';

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
        
        // Check if viewing own profile or other's
        $viewUserId = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
        $isOwnProfile = ($viewUserId === $_SESSION['user_id']);
        
        $profileUser = $this->user->getUserById($viewUserId);
        
        if (!$profileUser) {
            header('Location: dashboard.php');
            exit;
        }

        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
        
        require_once __DIR__ . '/../../views/user/profile.view.php';
    }

    public function update() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             exit;
        }

        $id = $_SESSION['user_id'];
        $data = [
            'nombre' => $_POST['nombre'],
            'correo' => $_POST['correo'],
            'telefono' => $_POST['telefono'],
            'biografia' => $_POST['biografia'],
            'vehiculo' => $_POST['vehiculo'],
            'institucion' => $_POST['institucion'],
            'ciudad' => $_POST['ciudad']
        ];

        // Handle Photo Upload
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
             // Validar tamaño (2MB) y tipo
             if ($_FILES['foto_perfil']['size'] <= 2097152) {
                 $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
                 if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                 
                 $fileName = uniqid() . '-' . basename($_FILES['foto_perfil']['name']);
                 if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $uploadDir . $fileName)) {
                     $data['foto_perfil'] = $fileName;
                 }
             }
        }

        if ($this->user->updateUser($id, $data)) {
            $_SESSION['user_name'] = $data['nombre']; // Update session name
            header('Location: profile.php?success=updated');
        } else {
            header('Location: profile.php?error=update_failed');
        }
    }

    public function changePassword() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             exit;
        }

        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        if ($new !== $confirm) {
             header('Location: profile.php?error=password_mismatch&tab=security');
             exit;
        }

        if ($this->user->verifyPassword($_SESSION['user_id'], $current)) {
             $this->user->updatePassword($_SESSION['user_id'], $new);
             header('Location: profile.php?success=password_updated&tab=security');
        } else {
             header('Location: profile.php?error=wrong_password&tab=security');
        }
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
        } else {
            header('Location: profile.php?error=update_failed&tab=privacy');
        }
    }
}
