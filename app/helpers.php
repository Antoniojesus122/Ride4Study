<?php

// Ruta principal del proyecto
define('BASE_PATH', '/Ride4Study');

// Generar una URL absoluta a partir de una ruta relativa
function url(string $path = ''): string
{
    return BASE_PATH . $path;
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
