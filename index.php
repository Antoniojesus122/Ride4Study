<?php

// Entry Point

// Cargar variables de entorno
require_once __DIR__ . '/config/env.php';

// Configuración de errores: NO mostrar errores al usuario en producción
$isProd = in_array($_SERVER['HTTP_HOST'] ?? '', ['ride4study.es', 'www.ride4study.es'], true);
ini_set('display_errors', $isProd ? '0' : '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// Config cookie de sesión (HttpOnly / Secure / SameSite) — endurece cookies antes de session_start
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isProd, // Secure solo en prod (HTTPS)
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// Headers de seguridad a nivel PHP
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Cargar helpers para definir rutas y generar URLs
require_once __DIR__ . '/app/helpers.php';

// Inicializar idioma desde cookie
$lang = $_COOKIE['lang'] ?? 'es';
if (!in_array($lang, ['es', 'en'], true)) {
    $lang = 'es';
}
$GLOBALS['translations'] = require __DIR__ . '/config/lang/' . $lang . '.php';
$GLOBALS['lang'] = $lang;

// Comprobar expiración de sesión por inactividad
checkSessionTimeout();

// Autoload para cargar controladores y modelos automáticamente
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/app/controllers/' . $class . '.php',
        __DIR__ . '/app/controllers/admin/' . $class . '.php',
        __DIR__ . '/app/models/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Router
require_once __DIR__ . '/app/Router.php';
$router = new Router();

// Registrar rutas
require_once __DIR__ . '/routes.php';

// --- CSRF ---
// Rutas que legítimamente reciben POST cross-origin (webhooks externos, etc.)
$csrfExempt = [
];

// Verificar token CSRF en TODAS las peticiones POST antes de dispatch
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
    // Normalizar quitando BASE_PATH si está presente
    if (BASE_PATH !== '' && str_starts_with($path, BASE_PATH)) {
        $path = substr($path, strlen(BASE_PATH));
    }
    $isExempt = in_array($path, $csrfExempt, true);
    csrfVerify($isExempt);
}

// Inyectar <meta name="csrf-token"> y JS que añade el token a todos los formularios automáticamente
ob_start(function ($buffer) {
    if ($buffer === false || $buffer === '') return $buffer;
    // Solo inyectar en respuestas HTML con <head>
    if (stripos($buffer, '</head>') === false) return $buffer;

    $token = htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8');
    $inject = '<meta name="csrf-token" content="' . $token . '">'
            . '<script>(function(){'
            . 'var m=document.querySelector(\'meta[name=csrf-token]\');if(!m)return;'
            . 'var t=m.getAttribute(\'content\');'
            . 'function addToForms(){document.querySelectorAll(\'form\').forEach(function(f){'
            . 'var mth=(f.getAttribute(\'method\')||\'\').toUpperCase();if(mth!==\'POST\')return;'
            . 'if(f.querySelector(\'input[name=_csrf]\'))return;'
            . 'var i=document.createElement(\'input\');i.type=\'hidden\';i.name=\'_csrf\';i.value=t;f.appendChild(i);});}'
            . 'if(document.readyState===\'loading\'){document.addEventListener(\'DOMContentLoaded\',addToForms);}else{addToForms();}'
            . 'var of=window.fetch;window.fetch=function(u,o){o=o||{};var mth=(o.method||\'GET\').toUpperCase();'
            . 'if([\'POST\',\'PUT\',\'PATCH\',\'DELETE\'].indexOf(mth)>=0){'
            . 'var h=o.headers||{};if(h instanceof Headers){h.set(\'X-CSRF-Token\',t);}else{h[\'X-CSRF-Token\']=t;}o.headers=h;}'
            . 'return of.call(this,u,o);};'
            . 'var _xo=XMLHttpRequest.prototype.open;XMLHttpRequest.prototype.open=function(m){this._m=m;return _xo.apply(this,arguments);};'
            . 'var _xs=XMLHttpRequest.prototype.send;XMLHttpRequest.prototype.send=function(){try{if([\'POST\',\'PUT\',\'PATCH\',\'DELETE\'].indexOf(String(this._m||\'\').toUpperCase())>=0){this.setRequestHeader(\'X-CSRF-Token\',t);}}catch(e){}return _xs.apply(this,arguments);};'
            . '})();</script>';

    return preg_replace('/<\/head>/i', $inject . '</head>', $buffer, 1);
});

// Manejar la solicitud
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$router->dispatch($uri, $method);
