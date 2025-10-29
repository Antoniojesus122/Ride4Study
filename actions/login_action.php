<?php

require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/login.php');
    exit;
}

$email = sanitize_input($_POST['correo'] ?? '');
$password = $_POST['contrasena'] ?? '';

if (empty($email) || empty($password)) {
    redirect_with_message('../public/login.php', 'Por favor, completa todos los campos.', 'error');
}

try {
    $stmt = $pdo->prepare("SELECT idUsuario, correo, contrasena, idRol FROM usuarios WHERE correo = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && verify_password($password, $user['contrasena'])) {
        login_user($user['idUsuario']);
        redirect_by_role(); // Redirige según rol
    } else {
        redirect_with_message('../public/login.php', 'Correo o contraseña incorrectos.', 'error');
    }
} catch (PDOException $e) {
    redirect_with_message('../public/login.php', 'Error al procesar el inicio de sesión.', 'error');
}
?>