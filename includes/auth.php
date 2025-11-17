    <?php
    session_start();

    /**
     * Iniciar sesión para un usuario
     * @param int $userId ID del usuario
     */
    
    function login_user($userId) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['logged_in'] = true;
    }

    /*Cerrar sesión*/
    function logout_user() {
        session_destroy();
    }

    /**
     * Verificar si el usuario está logueado
     * @return bool
     */

    function is_logged_in() {
        return isset($_SESSION['user_id']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Obtener el rol del usuario actual
     * @return int|null
     */

    function get_user_role() {
        if (!is_logged_in()) return null;

        require_once __DIR__ . '/db.php';

        try {
            $stmt = $pdo->prepare("SELECT idRol FROM usuarios WHERE idUsuario = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            return $user ? (int)$user['idRol'] : null;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Redirigir según el rol del usuario
     */
    function redirect_by_role() {
        if (!is_logged_in()) {
            header('Location: ../public/login.php');
            exit;
        }

        $role = get_user_role();

        if ($role === 1 || $role === 3) { // Administrador
            header('Location: ../admin/dashboard.php');
        } elseif ($role === 2 || $role === 4) { // Usuario normal
            header('Location: ../user/dashboard.php');
        } else {
            // Rol no válido o desconocido
            logout_user();
            header('Location: ../public/login.php?error=rol_invalido');
            exit;
        }
    }
    ?>