<?php

// Rutas de autenticacion (login, registro, recuperacion de contrasena, verificacion)

$router->any('/login', [AuthController::class, 'login']);
$router->any('/register', [AuthController::class, 'register']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->any('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->any('/reset-password', [AuthController::class, 'resetPassword']);
$router->any('/verify-email', [AuthController::class, 'verifyEmail']);
$router->any('/admin-verify', [AuthController::class, 'adminVerify']);
