<?php

    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../models/Institution.php';
    require_once __DIR__ . '/../../services/MailService.php';

    // Controlador de autenticacion para instituciones (login + 2FA)
    class InstitutionAuthController {
        private PDO $db;
        private Institution $institution;

        public function __construct() {
            if (session_status() === PHP_SESSION_NONE) session_start();
            $database = new Database();
            $this->db = $database->connect();
            $this->institution = new Institution($this->db);
        }

        // Mostrar formulario de login
        public function showLogin(): void {
            // Si ya esta logueado como institucion, redirigir al dashboard
            if (isset($_SESSION['institution_id'])) {
                header('Location: ' . url('/institution/dashboard'));
                exit;
            }

            $error = '';
            require_once __DIR__ . '/../../views/auth/institution-login.view.php';
        }

        // Procesar login
        public function login(): void {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . url('/institution-login'));
                exit;
            }

            $correo = trim($_POST['correo'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';
            $error = '';

            if (!$correo || !$contrasena) {
                $error = t('inst_auth.err_empty');
                require_once __DIR__ . '/../../views/auth/institution-login.view.php';
                return;
            }

            // Rate limiting por IP
            $lockKey = 'inst_login_lock_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
            if (isset($_SESSION[$lockKey]) && $_SESSION[$lockKey] > time()) {
                $error = t('inst_auth.err_too_many');
                require_once __DIR__ . '/../../views/auth/institution-login.view.php';
                return;
            }

            // Buscar institucion por correo
            $inst = $this->institution->getByEmail($correo);

            if (!$inst || !password_verify($contrasena, $inst['contrasena'])) {
                // Incrementar intentos
                $attemptsKey = 'inst_login_attempts_' . md5($_SERVER['REMOTE_ADDR'] ?? '');
                $_SESSION[$attemptsKey] = ($_SESSION[$attemptsKey] ?? 0) + 1;
                if ($_SESSION[$attemptsKey] >= 5) {
                    $_SESSION[$lockKey] = time() + 300; // Bloqueo 5 minutos
                    $_SESSION[$attemptsKey] = 0;
                }
                $error = t('inst_auth.err_invalid');
                require_once __DIR__ . '/../../views/auth/institution-login.view.php';
                return;
            }

            // Verificar que la cuenta este activa
            if (!(int)$inst['activo']) {
                $error = t('inst_auth.err_inactive');
                require_once __DIR__ . '/../../views/auth/institution-login.view.php';
                return;
            }

            // Generar codigo 2FA
            $code2fa = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Guardar en sesion
            session_regenerate_id(true);
            $_SESSION['inst_2fa_pending'] = true;
            $_SESSION['inst_2fa_id'] = $inst['idInstitucion'];
            $_SESSION['inst_2fa_code'] = password_hash($code2fa, PASSWORD_DEFAULT);
            $_SESSION['inst_2fa_expires'] = time() + 600; // 10 minutos
            $_SESSION['inst_2fa_attempts'] = 0;

            // Enviar codigo por email
            try {
                $mail = new MailService();
                $contenido = "
                    <p>Se ha detectado un inicio de sesion en el panel institucional.</p>
                    <p>Introduce el siguiente codigo para verificar tu identidad:</p>
                    <p style=\"font-size:14px; color:#94a3b8; margin-top:10px;\">
                        Este codigo es valido por <strong>10 minutos</strong>.
                    </p>
                    <p style=\"font-size:14px; color:#94a3b8;\">
                        Si no has sido tu, contacta con el administrador de Ride4Study.
                    </p>
                ";
                $html = $mail->generarPlantilla(
                    htmlspecialchars($inst['nombre']),
                    'Verificacion de acceso institucional',
                    $contenido,
                    $code2fa,
                    null,
                    null
                );
                $mail->send($inst['correo'], $inst['nombre'], 'Codigo de verificacion - Ride4Study Instituciones', $html);
            } catch (\Exception $e) {
                error_log('Institution 2FA email error: ' . $e->getMessage());
            }

            header('Location: ' . url('/institution-verify'));
            exit;
        }

        // Mostrar formulario de verificacion 2FA
        public function showVerify(): void {
            if (empty($_SESSION['inst_2fa_pending'])) {
                header('Location: ' . url('/institution-login'));
                exit;
            }

            // Verificar expiracion
            if (time() > ($_SESSION['inst_2fa_expires'] ?? 0)) {
                unset($_SESSION['inst_2fa_pending'], $_SESSION['inst_2fa_id'], $_SESSION['inst_2fa_code'], $_SESSION['inst_2fa_expires'], $_SESSION['inst_2fa_attempts']);
                $error = t('inst_auth.err_expired');
                require_once __DIR__ . '/../../views/auth/institution-login.view.php';
                return;
            }

            $error = '';
            $attemptsLeft = 5 - ($_SESSION['inst_2fa_attempts'] ?? 0);
            require_once __DIR__ . '/../../views/auth/institution-verify.view.php';
        }

        // Procesar verificacion 2FA
        public function verify(): void {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['inst_2fa_pending'])) {
                header('Location: ' . url('/institution-login'));
                exit;
            }

            // Verificar expiracion
            if (time() > ($_SESSION['inst_2fa_expires'] ?? 0)) {
                unset($_SESSION['inst_2fa_pending'], $_SESSION['inst_2fa_id'], $_SESSION['inst_2fa_code'], $_SESSION['inst_2fa_expires'], $_SESSION['inst_2fa_attempts']);
                header('Location: ' . url('/institution-login'));
                exit;
            }

            $code = trim($_POST['code'] ?? '');
            $_SESSION['inst_2fa_attempts'] = ($_SESSION['inst_2fa_attempts'] ?? 0) + 1;

            // Maximo 5 intentos
            if ($_SESSION['inst_2fa_attempts'] >= 5) {
                unset($_SESSION['inst_2fa_pending'], $_SESSION['inst_2fa_id'], $_SESSION['inst_2fa_code'], $_SESSION['inst_2fa_expires'], $_SESSION['inst_2fa_attempts']);
                $error = t('inst_auth.err_max_attempts');
                require_once __DIR__ . '/../../views/auth/institution-login.view.php';
                return;
            }

            // Verificar codigo
            if (password_verify($code, $_SESSION['inst_2fa_code'])) {
                $instId = $_SESSION['inst_2fa_id'];

                // Limpiar datos 2FA
                unset($_SESSION['inst_2fa_pending'], $_SESSION['inst_2fa_id'], $_SESSION['inst_2fa_code'], $_SESSION['inst_2fa_expires'], $_SESSION['inst_2fa_attempts']);

                // Obtener datos de la institucion
                $inst = $this->institution->getById($instId);
                if (!$inst) {
                    header('Location: ' . url('/institution-login'));
                    exit;
                }

                // Crear sesion de institucion
                session_regenerate_id(true);
                $_SESSION['institution_id'] = $inst['idInstitucion'];
                $_SESSION['institution_name'] = $inst['nombre'];
                $_SESSION['institution_email'] = $inst['correo'];
                $_SESSION['user_role'] = 4; // Rol institucion
                $_SESSION['last_activity'] = time();

                // Actualizar ultimo acceso
                $this->institution->updateLastAccess($instId);

                header('Location: ' . url('/institution/dashboard'));
                exit;
            }

            // Codigo incorrecto
            $error = t('inst_auth.err_wrong_code');
            $attemptsLeft = 5 - $_SESSION['inst_2fa_attempts'];
            require_once __DIR__ . '/../../views/auth/institution-verify.view.php';
        }

        // Cerrar sesion
        public function logout(): void {
            session_destroy();
            header('Location: ' . url('/institution-login'));
            exit;
        }
    }
