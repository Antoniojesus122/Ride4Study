<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/app/controllers/AuthController.php';

$controller = new AuthController();
$controller->resetPassword();
