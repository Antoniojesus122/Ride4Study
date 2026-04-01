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

        $flash = getFlash();
        if ($flash) {
            if ($flash['type'] === 'success') {
                $success = $flash['message'];
            } else {
                $error = $flash['message'];
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
                    // Rate limiting: máximo 5 intentos en 15 minutos por IP
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    $lockKey = 'login_attempts_' . md5($ip);
                    if (!isset($_SESSION[$lockKey])) {
                        $_SESSION[$lockKey] = ['count' => 0, 'first_attempt' => time()];
                    }
                    $attempts = &$_SESSION[$lockKey];

                    // Resetear si han pasado 15 minutos desde el primer intento
                    if (time() - $attempts['first_attempt'] > 900) {
                        $attempts = ['count' => 0, 'first_attempt' => time()];
                    }

                    if ($attempts['count'] >= 5) {
                        $remaining = 900 - (time() - $attempts['first_attempt']);
                        $mins = ceil($remaining / 60);
                        $error = t('auth.too_many_attempts') . ' ' . $mins . ' min.';
                        require __DIR__ . '/../../views/auth/login.view.php';
                        return;
                    }

                    $attempts['count']++;

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
                                unset($_SESSION[$lockKey]);
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

                            // Login exitoso: resetear rate limiting
                            unset($_SESSION[$lockKey]);

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

            $nombre      = trim($_POST['nombre'] ?? '');
            $correo      = trim($_POST['correo'] ?? '');
            $telefono    = (int)($_POST['telefono'] ?? 0);
            $institucion = trim($_POST['institucion'] ?? '');
            $password    = $_POST['contrasena'] ?? '';
            $confirm     = $_POST['confirmar_contrasena'] ?? '';
            $poliza      = isset($_POST['acepta_politicas']) ? 1 : 0;

            // Rate limiting: máximo 5 registros en 15 minutos por IP
            $rateCheck = checkRateLimit('register', 5, 900);
            if ($rateCheck['limited']) {
                $mins = ceil($rateCheck['remaining_seconds'] / 60);
                $error = t('auth.too_many_attempts') . ' ' . $mins . ' min.';
            } elseif (empty($nombre) || empty($correo) || empty($password) || empty($confirm) || empty($institucion)) {
                $error = 'Todos los campos son obligatorios.';
            } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $error = 'El correo no tiene un formato válido.';
            } elseif ($password !== $confirm) {
                $error = 'Las contraseñas no coinciden.';
            } elseif (strlen($password) < 8) {
                $error = t('auth.password_min_8');
            } elseif ($telefono !== 0 && (!is_numeric($telefono) || strlen((string)$telefono) !== 9)) {
                $error = 'El teléfono debe ser numérico y tener 9 dígitos.';
            } elseif ($poliza !== 1) {
                $error = 'Debes aceptar la política de privacidad.';
            } else {

                try {
                    // Comprobar si el correo ya existe
                    $existing = $this->user->getUserByEmail($correo);
                    if ($existing) {
                        $error = t('auth.email_already_exists');
                    } else {
                        // Generar hash de contraseña y código de verificación
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $code = $this->user->createEmailVerification($correo, $nombre, $hashedPassword, $telefono, $institucion);

                        if ($code) {
                            // Enviar email con código de verificación
                            try {
                                $mail = new MailService();
                                $contenido = "
                                    <p>" . t('auth.verify_email_body') . "</p>
                                    <p style=\"font-size:14px; color:#94a3b8; margin-top:10px;\">
                                        " . t('auth.verify_email_expires') . "
                                    </p>
                                ";
                                $html = $mail->generarPlantilla(
                                    $nombre,
                                    t('auth.verify_email_heading'),
                                    $contenido,
                                    $code,
                                    null,
                                    null
                                );
                                $mail->send($correo, $nombre, t('auth.verify_email_subject') . ' - Ride4Study', $html);
                            } catch (Exception $e) {
                                error_log('Verification email error: ' . $e->getMessage());
                            }

                            // Guardar correo en sesión para la página de verificación
                            $_SESSION['verify_email'] = $correo;
                            $_SESSION['verify_name']  = $nombre;
                            header('Location: ' . url('/verify-email'));
                            exit;
                        } else {
                            $error = t('auth.register_error');
                        }
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
            redirectWithFlash(url('/login'), 'error', t('auth.2fa_expired'));
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
                    redirectWithFlash(url('/login'), 'error', t('auth.2fa_blocked'));
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

    // Verificación de email durante el registro
    public function verifyEmail(): void
    {
        $error = '';

        // Si no hay email pendiente de verificación, redirigir al registro
        if (empty($_SESSION['verify_email'])) {
            header('Location: ' . url('/register'));
            exit;
        }

        $correo = $_SESSION['verify_email'];
        $nombre = $_SESSION['verify_name'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Acción de reenvío
            if (isset($_POST['resend'])) {
                // Rate limiting para reenvío
                $resendRate = checkRateLimit('verify_resend', 3, 300);
                if ($resendRate['limited']) {
                    $error = t('auth.verify_wait');
                } else {
                    $code = $this->user->createEmailVerification(
                        $correo,
                        $nombre,
                        '', // No necesitamos el hash de nuevo, ya está guardado
                        0
                    );

                    // Recargar los datos reales de la verificación pendiente
                    $stmt = $this->db->prepare("SELECT * FROM email_verifications WHERE correo = ? ORDER BY created_at DESC LIMIT 1");
                    $stmt->execute([$correo]);
                    $pendingData = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($pendingData) {
                        $newCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                        $newExpires = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                        $this->db->prepare("UPDATE email_verifications SET code = ?, expires_at = ? WHERE correo = ?")
                            ->execute([$newCode, $newExpires, $correo]);

                        try {
                            $mail = new MailService();
                            $contenido = "<p>" . t('auth.verify_email_body') . "</p>
                                <p style=\"font-size:14px; color:#94a3b8; margin-top:10px;\">" . t('auth.verify_email_expires') . "</p>";
                            $html = $mail->generarPlantilla($nombre, t('auth.verify_email_heading'), $contenido, $newCode, null, null);
                            $mail->send($correo, $nombre, t('auth.verify_email_subject') . ' - Ride4Study', $html);
                        } catch (Exception $e) {
                            error_log('Resend verification email error: ' . $e->getMessage());
                        }
                    }

                    $_SESSION['last_verify_resend'] = time();
                }
                require __DIR__ . '/../../views/auth/verify-email.view.php';
                return;
            }

            $code = trim($_POST['code'] ?? '');

            if (empty($code)) {
                $error = t('auth.2fa_empty');
            } elseif (!preg_match('/^\d{6}$/', $code)) {
                $error = t('auth.2fa_invalid');
            } else {
                $_SESSION['verify_attempts'] = ($_SESSION['verify_attempts'] ?? 0) + 1;

                if ($_SESSION['verify_attempts'] > 5) {
                    unset($_SESSION['verify_email'], $_SESSION['verify_name'], $_SESSION['verify_attempts']);
                    redirectWithFlash(url('/register'), 'error', t('auth.2fa_blocked'));
                }

                $result = $this->user->verifyEmailCode($correo, $code);

                if ($result === true) {
                    // Registro completado: limpiar sesión y enviar bienvenida
                    unset($_SESSION['verify_email'], $_SESSION['verify_name'], $_SESSION['verify_attempts'], $_SESSION['last_verify_resend']);

                    try {
                        $mail = new MailService();
                        $contenido = "
                            <p>Tu cuenta ha sido verificada y creada correctamente. Ya puedes iniciar sesion y empezar a compartir trayectos con otros estudiantes.</p>
                            <p style=\"font-size:14px; color:#94a3b8; margin-top:15px;\">
                                Si tienes alguna duda, no dudes en contactarnos.
                            </p>
                        ";
                        $html = $mail->generarPlantilla(
                            $nombre,
                            "Bienvenido a Ride4Study!",
                            $contenido,
                            null,
                            fullUrl('/login'),
                            'Iniciar sesion'
                        );
                        $mail->send($correo, $nombre, 'Bienvenido a Ride4Study!', $html);
                    } catch (Exception $e) {
                        error_log('Welcome email error: ' . $e->getMessage());
                    }

                    redirectWithFlash(url('/login'), 'success', t('auth.register_success'));
                } elseif ($result === 'email_exists') {
                    $error = t('auth.email_already_exists');
                } else {
                    $error = t('auth.verify_invalid_code');
                }
            }
        }

        $attemptsLeft = 5 - ($_SESSION['verify_attempts'] ?? 0);
        require __DIR__ . '/../../views/auth/verify-email.view.php';
    }

    // Solicitar código de reseteo
    public function forgotPassword(): void
    {

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $correo = trim($_POST['correo'] ?? '');

            // Rate limiting: máximo 5 solicitudes en 15 minutos por IP
            $rateCheck = checkRateLimit('forgot_password', 5, 900);

            if ($rateCheck['limited']) {
                $mins = ceil($rateCheck['remaining_seconds'] / 60);
                $error = t('auth.too_many_attempts') . ' ' . $mins . ' min.';
            } elseif (!$correo) {
                $error = 'Introduce tu correo.';
            }
            elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $error = 'Correo inválido.';
            }
            else {
                {
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
            } elseif (strlen($pass) < 8) {
                $error = t('auth.password_min_8');
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
