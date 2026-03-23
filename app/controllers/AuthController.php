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
                header('Location: ' . url($idRol === 1 ? '/admin/dashboard' : '/dashboard'));
                exit;
            }
        }

        if (isset($_GET['msg'])) {
            switch ($_GET['msg']) {
                case 'registrado':
                    $success = 'Registro completado. Inicia sesion para continuar.';
                    break;
                case '2fa_expired':
                    $error = t('auth.2fa_expired');
                    break;
                case '2fa_blocked':
                    $error = t('auth.2fa_blocked');
                    break;
            }
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

                            // Comprobar si el usuario está baneado
                            $banInfo = $this->user->isBanned((int)$userData['idUsuario']);
                            if ($banInfo) {
                                $banMsg = t('auth.account_banned');
                                if (!empty($banInfo['ban_motivo'])) {
                                    $banMsg .= ' ' . t('auth.ban_reason') . ': ' . htmlspecialchars($banInfo['ban_motivo']) . '.';
                                }
                                if (!empty($banInfo['ban_hasta'])) {
                                    $banMsg .= ' ' . t('auth.ban_until') . ': ' . date('d/m/Y H:i', strtotime($banInfo['ban_hasta'])) . '.';
                                }
                                $error = $banMsg;
                                require __DIR__ . '/../../views/auth/login.view.php';
                                return;
                            }

                            $idRol = (int)$userData['idRol'];

                            // 2FA para administradores: enviar código por email antes de completar el login
                            if ($idRol === 1) {
                                session_regenerate_id(true);

                                // Generar código de 6 dígitos
                                $code2fa = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                                // Guardar datos temporales en sesión (NO se loguea aún)
                                $_SESSION['2fa_pending']  = true;
                                $_SESSION['2fa_user_id']  = $userData['idUsuario'];
                                $_SESSION['2fa_code']     = password_hash($code2fa, PASSWORD_DEFAULT);
                                $_SESSION['2fa_expires']  = time() + 600; // 10 minutos
                                $_SESSION['2fa_attempts'] = 0;

                                // Enviar código por email
                                try {
                                    $mail = new MailService();
                                    $contenido = "
                                        <p>Se ha detectado un inicio de sesion en el panel de administracion.</p>
                                        <p>Introduce el siguiente codigo para verificar tu identidad:</p>
                                        <p style=\"font-size:14px; color:#94a3b8; margin-top:10px;\">
                                            Este codigo es valido por <strong>10 minutos</strong>.
                                        </p>
                                        <p style=\"font-size:14px; color:#94a3b8;\">
                                            Si no has sido tu, cambia tu contrasena inmediatamente.
                                        </p>
                                    ";
                                    $html = $mail->generarPlantilla(
                                        $userData['nombre'],
                                        "Verificacion de acceso",
                                        $contenido,
                                        $code2fa,
                                        null,
                                        null
                                    );
                                    $mail->send($userData['correo'], $userData['nombre'], 'Codigo de verificacion - Ride4Study Admin', $html);
                                } catch (Exception $e) {
                                    error_log('2FA email error: ' . $e->getMessage());
                                }

                                header('Location: ' . url('/admin-verify'));
                                exit;
                            }

                            // Login normal para usuarios no-admin
                            session_regenerate_id(true);

                            $_SESSION['user_id']   = $userData['idUsuario'];
                            $_SESSION['user_role'] = $userData['idRol'];
                            $_SESSION['user_name'] = $userData['nombre'] ?? '';
                            $full = $this->user->getUserById((int)$userData['idUsuario']);
                            if ($full && !empty($full['foto_perfil'])) {
                                $_SESSION['user_photo'] = $full['foto_perfil'];
                            }

                            header('Location: ' . url('/dashboard'));
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

        // Crons: se ejecutan en cada carga de login
        require_once __DIR__ . '/../../scripts/cron_send_rating_notifications.php';
        require_once __DIR__ . '/../../scripts/cron_premium_expiration.php';
        require_once __DIR__ . '/../../scripts/cron_trip_reminders.php';
    }



    public function register() {

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre   = trim($_POST['nombre'] ?? '');
            $correo   = trim($_POST['correo'] ?? '');
            $telefono = (int)($_POST['telefono'] ?? 0);
            $password = $_POST['contrasena'] ?? '';
            $confirm  = $_POST['confirmar_contrasena'] ?? '';
            $poliza   = isset($_POST['acepta_politicas']) ? 1 : 0;

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
            } elseif ($poliza !== 1) {
                $error = 'Debes aceptar la política de privacidad.';
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
                        try {
                            $mail = new MailService();
                            $contenido = "
                                <p>Tu cuenta ha sido creada correctamente. Ya puedes iniciar sesión y empezar a compartir trayectos con otros estudiantes.</p>
                                <p style=\"font-size:14px; color:#94a3b8; margin-top:15px;\">
                                    Si tienes alguna duda, no dudes en contactarnos.
                                </p>
                            ";
                            $html = $mail->generarPlantilla(
                                $nombre,
                                "¡Bienvenido a Ride4Study!",
                                $contenido,
                                null,
                                fullUrl('/login'),
                                'Iniciar sesión'
                            );
                            $mail->send($correo, $nombre, '¡Bienvenido a Ride4Study!', $html);
                        } catch (Exception $e) {
                            error_log('Welcome email error: ' . $e->getMessage());
                        }

                        header('Location: ' . url('/login') . '?msg=registrado');
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

        header('Location: ' . url('/login'));
        exit;
    }

    // Verificación 2FA para administradores
    public function adminVerify(): void
    {
        $error = '';

        // Si no hay 2FA pendiente, redirigir al login
        if (empty($_SESSION['2fa_pending'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        // Comprobar si el código ha expirado
        if (time() > ($_SESSION['2fa_expires'] ?? 0)) {
            unset($_SESSION['2fa_pending'], $_SESSION['2fa_user_id'], $_SESSION['2fa_code'], $_SESSION['2fa_expires'], $_SESSION['2fa_attempts']);
            header('Location: ' . url('/login') . '?msg=2fa_expired');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $code = trim($_POST['code'] ?? '');

            if (empty($code)) {
                $error = t('auth.2fa_empty');
            } elseif (!preg_match('/^\d{6}$/', $code)) {
                $error = t('auth.2fa_invalid');
            } else {
                $_SESSION['2fa_attempts'] = ($_SESSION['2fa_attempts'] ?? 0) + 1;

                // Máximo 5 intentos
                if ($_SESSION['2fa_attempts'] > 5) {
                    unset($_SESSION['2fa_pending'], $_SESSION['2fa_user_id'], $_SESSION['2fa_code'], $_SESSION['2fa_expires'], $_SESSION['2fa_attempts']);
                    header('Location: ' . url('/login') . '?msg=2fa_blocked');
                    exit;
                }

                // Verificar código
                if (password_verify($code, $_SESSION['2fa_code'])) {
                    $userId = (int)$_SESSION['2fa_user_id'];

                    // Limpiar datos 2FA
                    unset($_SESSION['2fa_pending'], $_SESSION['2fa_user_id'], $_SESSION['2fa_code'], $_SESSION['2fa_expires'], $_SESSION['2fa_attempts']);

                    // Completar login
                    session_regenerate_id(true);
                    $userData = $this->user->getUserById($userId);

                    $_SESSION['user_id']   = $userData['idUsuario'];
                    $_SESSION['user_role'] = $userData['idRol'];
                    $_SESSION['user_name'] = $userData['nombre'] ?? '';
                    if (!empty($userData['foto_perfil'])) {
                        $_SESSION['user_photo'] = $userData['foto_perfil'];
                    }

                    header('Location: ' . url('/admin/dashboard'));
                    exit;
                } else {
                    $error = t('auth.2fa_wrong');
                }
            }
        }

        $attemptsLeft = 5 - ($_SESSION['2fa_attempts'] ?? 0);
        require __DIR__ . '/../../views/auth/admin-verify.view.php';
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
                            
                            $mail = new MailService();
                            $contenido = "
                                <p>Has solicitado recuperar tu contraseña. Utiliza el siguiente código de verificación para continuar con el proceso:</p>
                                <p style=\"font-size:14px; color:#94a3b8; margin-top:15px;\">
                                    Este código es válido por <strong>15 minutos</strong>.
                                </p>
                                <p style=\"font-size:14px; color:#94a3b8;\">
                                    Si no solicitaste este cambio, puedes ignorar este mensaje de forma segura.
                                </p>
                            ";
                            
                            $html = $mail->generarPlantilla(
                                $userData['nombre'],
                                "Hola {$userData['nombre']},",
                                $contenido,
                                $code,
                                null,
                                null
                            );
                            
                            $mail->send($correo, $userData['nombre'], $subject, $html);
                        }
                    }

                    $_SESSION['last_reset'] = time();
                    header("Location: " . url('/reset-password') . "?sent=1");
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
                        header("Refresh:3; url=" . url('/login'));
                    }
                }
            }
        }

        require __DIR__ . '/../../views/auth/reset-password.view.php';
    }
}
