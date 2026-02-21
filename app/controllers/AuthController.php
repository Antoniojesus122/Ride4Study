<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../services/MailService.php';

class AuthController {

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

    public function login(): void {

        $error = '';
        $success = '';

        // Redirigir si ya hay sesión iniciada
        if (isset($_SESSION['user_id'])) {
            $user = $this->user->getUserById((int)$_SESSION['user_id']);
            if ($user) {
                $idRol = (int)$user['idRol'];
                if (in_array($idRol, [1, 3], true)) {
                    header('Location: admin.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            }
        }

        if (isset($_GET['msg']) && $_GET['msg'] === 'registrado') {
            $success = 'Registro completado. Inicia sesión para continuar.';
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $correo = trim($_POST['correo'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';

            if ($correo === '' || $contrasena === '') {
                $error = 'Por favor, completa todos los campos.';
            } else {
                if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $error = 'El correo no tiene un formato válido.';
                } else {
                    try {
                        $userData = $this->user->login($correo, $contrasena);


                        if ($userData !== false) {

                            session_regenerate_id(true);

                            $_SESSION['user_id']   = $userData['idUsuario'];
                            $_SESSION['user_role'] = $userData['idRol'];
                            $_SESSION['user_name'] = $userData['nombre'] ?? '';
                            // Cargar foto de perfil en sesión para mostrarla globalmente
                            $full = $this->user->getUserById((int)$userData['idUsuario']);
                            if ($full && !empty($full['foto_perfil'])) {
                                $_SESSION['user_photo'] = $full['foto_perfil'];
                            }

                            $idRol = (int)$userData['idRol'];
                            if (in_array($idRol, [1, 3], true)) {
                                header('Location: admin/dashboard.php');
                            } else {
                                header('Location: dashboard.php');
                            }
                            exit;

                        } else {
                            $error = 'Correo o contraseña incorrectos.';
                        }
                    } catch (PDOException $e) {
                        error_log('Login error: ' . $e->getMessage());
                        $error = 'Error al conectar con la base de datos.';
                    }
                }
            }
        }

        require __DIR__ . '/../../views/auth/login.view.php';
    }



    public function register() {

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre   = trim($_POST['nombre'] ?? '');
            $correo   = trim($_POST['correo'] ?? '');
            $telefono = (int)($_POST['telefono'] ?? 0);
            $password = $_POST['contrasena'] ?? '';
            $confirm  = $_POST['confirmar_contrasena'] ?? '';

            if (empty($nombre) || empty($correo) || empty($password) || empty($confirm)) {
                $error = 'Todos los campos son obligatorios.';
            } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $error = 'El correo no tiene un formato válido.';
            } elseif ($password !== $confirm) {
                $error = 'Las contraseñas no coinciden.';
            } elseif (strlen($password) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } elseif ($telefono !== 0 && (!is_numeric($telefono) || strlen((string)$telefono) !== 9)) {
                $error = 'El teléfono debe ser numérico y tener 9 dígitos.';
            } else {

                try {
                    $registered = $this->user->register(
                        $nombre,
                        $correo,
                        $password,
                        2,
                        $telefono
                    );

                    if ($registered) {
                        header('Location: login.php?msg=registrado');
                        exit;
                    } else {
                        $error = 'El correo ya está registrado o ha ocurrido un error.';
                    }
                } catch (PDOException $e) {
                    error_log('Register error: ' . $e->getMessage());
                    $error = 'Error al conectar con la base de datos.';
                }
            }
        }

        require_once __DIR__ . '/../../views/auth/register.view.php';
    }

    public function logout() {

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        header('Location: login.php');
        exit;
    }

    // Solicitar código de reseteo
    public function forgotPassword(): void
    {

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $correo = trim($_POST['correo'] ?? '');

            // Validaciones básicas
            if (!$correo) {
                $error = 'Introduce tu correo.';
            }
            elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $error = 'Correo inválido.';
            }
            else {
                // Evitar spam de solicitudes de codigos
                if (isset($_SESSION['last_reset']) && time() - $_SESSION['last_reset'] < 30) {
                    $error = 'Espera unos segundos antes de volver a solicitarlo.';
                }
                else {
                    $userData = $this->user->getUserByEmail($correo);

                    //Importante, no revelar el correo evidentemente pero si existe el usuario, generar código y enviarlo
                    if ($userData) {
                        $code = $this->user->createResetCode((int)$userData['idUsuario']);

                        if ($code) {
                            $subject = "Código de recuperación - Ride4Study";
                            $html = "
                                <h2>Hola {$userData['nombre']}</h2>
                                <p>Tu código de recuperación es:</p>
                                <h1 style='font-size:32px;letter-spacing:6px'>{$code}</h1>
                                <p>Válido por 15 minutos.</p>
                            ";
                            $mail = new MailService();
                            $mail->send($correo, $userData['nombre'], $subject, $html);
                        }
                    }

                    $_SESSION['last_reset'] = time();
                    header("Location: reset-password.php?sent=1");
                    exit;
                }
            }
        }

        require __DIR__ . '/../../views/auth/forgot-password.view.php';
    }

    // Validar código y resetear contraseña
    public function resetPassword(): void
    {
        $error = '';
        $success = '';

        // Obtener código desde GET si existe
        $resetData = null;
        if (isset($_GET['code'])) {
            $resetData = $this->user->validateResetCode($_GET['code']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim($_POST['code'] ?? '');
            $pass = $_POST['contrasena'] ?? '';
            $confirm = $_POST['confirmar_contrasena'] ?? '';

            // Validaciones básicas
            if (!$code || !$pass || !$confirm) {
                $error = 'Completa todos los campos.';
            } elseif (!preg_match('/^\d{6}$/', $code)) {
                $error = 'Código inválido.';
            } elseif (strlen($pass) < 6) {
                $error = 'La contraseña debe tener al menos 6 caracteres.';
            } elseif ($pass !== $confirm) {
                $error = 'Las contraseñas no coinciden.';
            } else {
                $_SESSION['reset_attempts'] = ($_SESSION['reset_attempts'] ?? 0) + 1;

                if ($_SESSION['reset_attempts'] > 5) {
                    $error = 'Demasiados intentos. Solicita un nuevo código.';
                } else {
                    $data = $this->user->validateResetCode($code);
                    if (!$data) {
                        $error = 'Código inválido o expirado.';
                    } else {
                        $this->user->resetPasswordWithCode((int)$data['user_id'], $pass);
                        unset($_SESSION['reset_attempts']);
                        $success = 'Contraseña cambiada correctamente. Redirigiendo...';
                        header("Refresh:3; url=login.php");
                    }
                }
            }
        }

        require __DIR__ . '/../../views/auth/reset-password.view.php';
    }
}
