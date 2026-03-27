<?php

// Ruta principal del proyecto
define('BASE_PATH', '/Ride4Study');

// Generar una URL relativa a partir de una ruta
function url(string $path = ''): string
{
    return BASE_PATH . $path;
}

// Generar una URL absoluta con dominio (para emails, APIs externas, etc.)
function fullUrl(string $path = ''): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . BASE_PATH . $path;
}

// Verificar si la ruta actual coincide con la ruta dada
function isActive(string $route): bool
{
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $current = rtrim($uri, '/');
    $check   = rtrim(BASE_PATH . $route, '/');
    return $current === $check;
}

// Obtener una traducción por clave. Devuelve la clave si no existe.
function t(string $key): string
{
    return $GLOBALS['translations'][$key] ?? $key;
}

// Obtener el código de idioma actual
function currentLang(): string
{
    return $GLOBALS['lang'] ?? 'es';
}

// Comprobar expiración de sesión por inactividad (45 minutos)
function checkSessionTimeout(): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();

    $timeout = 45 * 60; // 45 minutos

    if (isset($_SESSION['user_id'])) {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
            }
            session_destroy();
            session_start();
            flash('error', t('auth.session_expired'));
            header('Location: ' . url('/login'));
            exit;
        }
        $_SESSION['last_activity'] = time();
    }
}

// Validar archivo subido (foto de perfil, documentos)
function validateUploadedFile(array $file, array $allowedMimes, int $maxSizeMB = 2): array
{
    $result = ['valid' => false, 'error' => '', 'mime' => '', 'ext' => ''];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $result['error'] = 'upload_error';
        return $result;
    }

    // Validar tamaño
    if ($file['size'] > $maxSizeMB * 1024 * 1024) {
        $result['error'] = 'file_too_large';
        return $result;
    }

    // Validar MIME real con finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);

    if (!isset($allowedMimes[$realMime])) {
        $result['error'] = 'invalid_file_type';
        return $result;
    }

    // Validar extensión del nombre original
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $validExts = array_values($allowedMimes);
    // Aceptar 'jpeg' como alias de 'jpg'
    if ($ext === 'jpeg') $ext = 'jpg';
    if (!in_array($ext, $validExts, true)) {
        $result['error'] = 'invalid_file_type';
        return $result;
    }

    // Validar que el archivo es una imagen real (para imágenes)
    if (str_starts_with($realMime, 'image/')) {
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $result['error'] = 'invalid_file_type';
            return $result;
        }
    }

    $result['valid'] = true;
    $result['mime']  = $realMime;
    $result['ext']   = $allowedMimes[$realMime];
    return $result;
}

// Flash messages en sesión (esto para que no puedan manipular los GET params)
function flash(string $type, string $message, ?string $tab = null): void
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message, 'tab' => $tab];
}

function getFlash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) session_start();
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $flash;
}

// Redirigir con flash message
function redirectWithFlash(string $url, string $type, string $message, ?string $tab = null): void
{
    flash($type, $message, $tab);
    $tabParam = $tab ? '?tab=' . $tab : '';
    header('Location: ' . $url . $tabParam);
    exit;
}
