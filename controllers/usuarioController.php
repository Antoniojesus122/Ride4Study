<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../includes/funciones.php';
session_start();

// Instancia PDO
require_once __DIR__ . '/../config/db.php';
$usuarioModel = new Usuario($pdo);

// Registro
if (isset($_POST['registro'])) {
    $nombre = limpiarEntrada($_POST['nombre']);
    $correo = limpiarEntrada($_POST['correo']);
    $telefono = limpiarEntrada($_POST['telefono']);
    $contrasena = limpiarEntrada($_POST['contrasena']);

    if ($usuarioModel->obtenerPorCorreo($correo)) {
        mostrarError("This email is already registered.");
    } else {
        $usuarioModel->registrar($nombre, $correo, $telefono, $contrasena);
        mostrarExito("Registration successful! You can now log in.");
        redirigir("../public/login.php");
    }
}

// Login
if (isset($_POST['login'])) {
    $correo = limpiarEntrada($_POST['correo']);
    $contrasena = limpiarEntrada($_POST['contrasena']);

    $usuario = $usuarioModel->verificarLogin($correo, $contrasena);

    if ($usuario) {
        $_SESSION['usuario'] = [
            'id' => $usuario['idUsuario'],
            'nombre' => $usuario['nombre'],
            'rol' => $usuario['idRol']
        ];
        redirigir("../public/home.php");
    } else {
        mostrarError("Incorrect email or password.");
    }
}

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    redirigir("../public/login.php");
}
?>