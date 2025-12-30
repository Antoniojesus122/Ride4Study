<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';

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
                    header('Location: admin/dashboard.php');
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
            } else {

                try {
                    $registered = $this->user->register(
                        $nombre,
                        $correo,
                        $password,
                        2
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
}
