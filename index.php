<?php
session_start();

if (isset($_SESSION['usuario'])) {
    header("Location: public/home.php");
    exit;
} else {
    header("Location: public/login.php");
    exit;
}
?>