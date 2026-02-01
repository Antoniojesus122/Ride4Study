<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 1) {
    header('Location: /login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/controllers/admin/AdminDashboardController.php';

$controller = new AdminDashboardController();
$controller->index();
