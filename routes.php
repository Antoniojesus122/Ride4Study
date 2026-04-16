<?php

// Definicion de rutas para la aplicacion web
// Las rutas estan organizadas en archivos separados por contexto

require_once __DIR__ . '/routes/public.php';       // Rutas publicas (landing, legal, soporte)
require_once __DIR__ . '/routes/auth.php';          // Autenticacion (login, registro, reset, 2FA)
require_once __DIR__ . '/routes/institution.php';   // Instituciones (login, panel, estudiantes)
require_once __DIR__ . '/routes/user.php';          // Usuario autenticado (dashboard, viajes, mensajes, perfil, premium)
require_once __DIR__ . '/routes/admin.php';         // Administracion (dashboard, usuarios, reportes, config)
require_once __DIR__ . '/routes/api.php';           // API (autocompletado instituciones)
