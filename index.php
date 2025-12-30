<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? null;
    if (in_array((int)$role, [1, 3], true)) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}
require_once __DIR__ . '/views/public/landing.view.php';