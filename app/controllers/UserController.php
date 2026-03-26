<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Rating.php';
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../../services/MailService.php';

class UserController {
    private $db;
    private $user;
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

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $viewUserId = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
        $isOwnProfile = ($viewUserId === $_SESSION['user_id']);
        
        $profileUser = $this->user->getUserById($viewUserId);
        
        if (!$profileUser) {
            header('Location: ' . url('/dashboard'));
            exit;
        }

        // Estadísticas: valoracion promedio y últimas valoraciones
        $ratingModel = new Rating($this->db);
        $userStats['valoracion_promedio'] = round($ratingModel->getAverage($viewUserId), 1);
        $ratings = $ratingModel->getByUser($viewUserId, 10);

        // CO2 ahorrado
        $rideModel = new Ride($this->db);
        $userStats['co2_ahorrado'] = $rideModel->calculateUserCO2($viewUserId);

        // Anuncios activos del usuario visitado (para mostrar en su perfil)
        $stmt = $this->db->prepare("
            SELECT a.idAnuncio, a.tipo, a.fechaSalida, a.horaSalida, a.horaLlegada, a.horaRegreso,
                   a.precio, a.plazasDisponibles, a.descripcion,
                   a.ruta_polyline, a.distancia_km, a.duracion_min,
                   lo.nombreLocalidad AS nombreOrigen, ld.nombreLocalidad AS nombreDestino
            FROM anuncios a
            JOIN localidades lo ON a.origen = lo.idLocalidad
            JOIN localidades ld ON a.destino = ld.idLocalidad
            WHERE a.idUsuario = :id
              AND CONCAT(a.fechaSalida, ' ', a.horaSalida) >= NOW()
            ORDER BY a.fechaSalida ASC, a.horaSalida ASC
            LIMIT 6
        ");
        $stmt->execute([':id' => $viewUserId]);
        $activeRides = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        require_once __DIR__ . '/../../views/user/profile.view.php';
    }


    public function update()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/login'));
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

        // Preferencias de viaje
        $validPrefs = ['silencio','charla','mascotas','no_fumar','equipaje','musica'];
        $prefs = array_intersect($_POST['preferencias_viaje'] ?? [], $validPrefs);
        $data['preferencias_viaje'] = json_encode(array_values($prefs));

        if (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            redirectWithFlash(url('/profile'), 'error', 'invalid_email');
        }

        if (!empty($data['biografia']) && mb_strlen($data['biografia']) > 300) {
            redirectWithFlash(url('/profile'), 'error', 'biografia_too_long');
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
                if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $uploadDir . $fileName)) {
                    $data['foto_perfil'] = $fileName;
                }
            }
        }

        if ($this->user->updateUser($id, $data)) {
            $_SESSION['user_name'] = $data['nombre'];
            if (isset($data['foto_perfil'])) {
                $_SESSION['user_photo'] = $data['foto_perfil'];
            }
            redirectWithFlash(url('/profile'), 'success', 'updated');
        }

        redirectWithFlash(url('/profile'), 'error', 'update_failed');
    }


    public function changePassword()
    {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/profile'));
            exit;
        }

        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Campos vacíos
        if (empty($current) || empty($new) || empty($confirm)) {
            redirectWithFlash(url('/profile'), 'error', 'empty_fields', 'security');
        }

        // Las nuevas contraseñas no coinciden
        if ($new !== $confirm) {
            redirectWithFlash(url('/profile'), 'error', 'password_mismatch', 'security');
        }

        // Longitud mínima
        if (strlen($new) < 8) {
            redirectWithFlash(url('/profile'), 'error', 'password_too_short', 'security');
        }

        // La nueva contraseña debe contener al menos una letra mayúscula y un número
        if (!preg_match('/[A-Z]/', $new) || !preg_match('/[0-9]/', $new)) {
            redirectWithFlash(url('/profile'), 'error', 'password_weak', 'security');
        }

        // Verificar que la contraseña actual es correcta
        if (!$this->user->verifyPassword($_SESSION['user_id'], $current)) {
            redirectWithFlash(url('/profile'), 'error', 'wrong_password', 'security');
        }

        // La nueva contraseña no puede ser igual a la actual
        if (password_verify($new, $this->user->getPasswordHash($_SESSION['user_id']))) {
            redirectWithFlash(url('/profile'), 'error', 'same_password', 'security');
        }

        // Actualizar contraseña
        if (!$this->user->updatePassword($_SESSION['user_id'], $new)) {
            redirectWithFlash(url('/profile'), 'error', 'update_failed', 'security');
        }

        // Email de confirmación de cambio de contraseña
        $userData = $this->user->getUserById($_SESSION['user_id']);
        if ($this->mailService && $userData && !empty($userData['notificaciones_email'])) {
            $html = $this->mailService->generarPlantilla(
                $userData['nombre'],
                'Contraseña actualizada',
                'Hola <strong>' . htmlspecialchars($userData['nombre']) . '</strong>,<br><br>
                Tu contraseña ha sido cambiada correctamente.<br><br>
                Si no fuiste tú quien realizó este cambio, restablece tu contraseña de inmediato y contáctanos.',
                null,
                null
            );
            $this->mailService->send($userData['correo'], $userData['nombre'], 'Contraseña actualizada · Ride4Study', $html);
        }

        redirectWithFlash(url('/profile'), 'success', 'password_updated', 'security');
    }

    public function verify() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             exit;
        }

        if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
              // Validar tipo de archivo: solo PDF e imágenes
              $allowedMimes = [
                  'application/pdf' => 'pdf',
                  'image/jpeg'      => 'jpg',
                  'image/png'       => 'png',
                  'image/webp'      => 'webp',
              ];
              $mime = mime_content_type($_FILES['document']['tmp_name']);
              if (!isset($allowedMimes[$mime])) {
                  redirectWithFlash(url('/profile'), 'error', 'invalid_file_type', 'verification');
              }

              // Validar extensión del archivo original
              $ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));
              if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'webp'], true)) {
                  redirectWithFlash(url('/profile'), 'error', 'invalid_file_type', 'verification');
              }

              // Límite de 5MB
              if ($_FILES['document']['size'] > 5 * 1024 * 1024) {
                  redirectWithFlash(url('/profile'), 'error', 'file_too_large', 'verification');
              }

              $uploadDir = __DIR__ . '/../../public/uploads/verification/';
              if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

              // Extensión segura basada en MIME type
              $safeExt = $allowedMimes[$mime];
              $fileName = uniqid() . '-verification.' . $safeExt;
              if (move_uploaded_file($_FILES['document']['tmp_name'], $uploadDir . $fileName)) {
                   $this->user->submitVerification($_SESSION['user_id'], $fileName);

                   // Email de confirmación de recepción de documentación
                   $userData = $this->user->getUserById($_SESSION['user_id']);
                   if ($this->mailService && $userData && !empty($userData['notificaciones_email'])) {
                       $html = $this->mailService->generarPlantilla(
                           $userData['nombre'],
                           'Documentación recibida',
                           'Hola <strong>' . htmlspecialchars($userData['nombre']) . '</strong>,<br><br>
                           Hemos recibido tu solicitud de verificación de estudiante y la documentación adjunta.<br><br>
                           Nuestro equipo revisará la información en breve y te notificaremos por correo cuando hayamos tomado una decisión.',
                           'En revisión',
                           null
                       );
                       $this->mailService->send($userData['correo'], $userData['nombre'], 'Verificación recibida · Ride4Study', $html);
                   }

                   redirectWithFlash(url('/profile'), 'success', 'verification_sent', 'verification');
              } else {
                   redirectWithFlash(url('/profile'), 'error', 'upload_failed', 'verification');
              }
        } else {
             redirectWithFlash(url('/profile'), 'error', 'no_file', 'verification');
        }
    }

    public function deleteAccount() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/profile'));
            exit;
        }

        $password = $_POST['password'] ?? '';
        if (empty($password)) {
            redirectWithFlash(url('/profile'), 'error', 'empty_fields', 'privacy');
        }

        // Verificar contraseña antes de eliminar
        if (!$this->user->verifyPassword($_SESSION['user_id'], $password)) {
            redirectWithFlash(url('/profile'), 'error', 'wrong_password', 'privacy');
        }

        $userData = $this->user->getUserById($_SESSION['user_id']);

        if ($this->user->deleteAccount($_SESSION['user_id'])) {
            // Enviar email de confirmación de eliminación
            if ($this->mailService && $userData && !empty($userData['notificaciones_email'])) {
                $html = $this->mailService->generarPlantilla(
                    $userData['nombre'],
                    t('profile.delete_email_title'),
                    '<p>' . t('profile.delete_email_body') . '</p>',
                    null, null
                );
                $this->mailService->send($userData['correo'], $userData['nombre'], t('profile.delete_email_title') . ' · Ride4Study', $html);
            }

            // Destruir sesión
            $_SESSION = [];
            session_destroy();
            redirectWithFlash(url('/login'), 'success', 'account_deleted');
        }

        redirectWithFlash(url('/profile'), 'error', 'delete_failed', 'privacy');
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
            redirectWithFlash(url('/profile'), 'success', 'privacy_updated', 'privacy');
        } else {
            redirectWithFlash(url('/profile'), 'error', 'update_failed', 'privacy');
        }
    }
}
