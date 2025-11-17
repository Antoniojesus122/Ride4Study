    <?php

    // Utilizarlo más adelante

    /**
     * Sanitizar entrada de texto
     * @param string $input
     * @return string
     */

    function sanitize_input($input) {
        return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Validar correo electrónico
     * @param string $email
     * @return bool
     */

    function is_valid_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Hash de contraseña
     * @param string $password
     * @return string
     */
    function hash_password($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verificar contraseña
     * @param string $password
     * @param string $hash
     * @return bool
     */

    function verify_password($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Redirigir con mensaje
     * @param string $url
     * @param string $message
     * @param string $type 'success' | 'error'
     */

    function redirect_with_message($url, $message, $type = 'success') {
        $_SESSION['flash_message'] = [
            'text' => $message,
            'type' => $type
        ];
        header("Location: $url");
        exit;
    }

    /**
     * Mostrar mensaje flash
     */

    function show_flash_message() {
        if (isset($_SESSION['flash_message'])) {
            $msg = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            
            $class = $msg['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
            echo "<div class='p-4 mb-4 rounded-lg $class'>{$msg['text']}</div>";
        }
    }

    /**
     * Formatear tiempo transcurrido
     * @param int $timestamp
     * @return string
     */

    function format_time_ago($timestamp) {
        $diff = time() - $timestamp;
        
        if ($diff < 60) {
            return "Ahora mismo";
        } elseif ($diff < 3600) {
            $mins = round($diff / 60);
            return $mins . " min" . ($mins != 1 ? "s" : "");
        } elseif ($diff < 86400) {
            $hours = round($diff / 3600);
            return $hours . " h" . ($hours != 1 ? "s" : "");
        } elseif ($diff < 604800) {
            $days = round($diff / 86400);
            return $days . " día" . ($days != 1 ? "s" : "");
        } else {
            return date("d/m/Y", $timestamp);
        }
    }
    ?>