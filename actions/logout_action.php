<?php
session_start();
require_once __DIR__ . '/../includes/auth.php';
logout_user();
header('Location: ../public/login.php');
exit;
?>