<?php

require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
require_once '../includes/mailer.php'; // Para enviar correo de confirmación [Próximamente]

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/register.php');
    exit;
}

$name = sanitize_input($_POST['nombre'] ?? '');
$email = sanitize_input($_POST['correo'] ?? '');
$phone = sanitize_input($_POST['telefono'] ?? '');
$password = $_POST['contrasena'] ?? '';
$confirmPassword = $_POST['confirmar_contrasena'] ?? '';

// Validaciones básicas
if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
    redirect_with_message('../public/register.php', 'Por favor, completa todos los campos.', 'error');
}

if (!is_valid_email($email)) {
    redirect_with_message('../public/register.php', 'Correo electrónico inválido.', 'error');
}

if ($password !== $confirmPassword) {
    redirect_with_message('../public/register.php', 'Las contraseñas no coinciden.', 'error');
}

if (strlen($password) < 6) {
    redirect_with_message('../public/register.php', 'La contraseña debe tener al menos 6 caracteres.', 'error');
}

try {
    // Verificar si el correo ya existe
    $stmt = $pdo->prepare("SELECT idUsuario FROM usuarios WHERE correo = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        redirect_with_message('../public/register.php', 'Este correo ya está registrado.', 'error');
    }

    // Hashear contraseña
    $hashedPassword = hash_password($password);

    // Insertar usuario (por defecto, rol 2 = Usuario normal)
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, correo, telefono, contrasena, idRol) VALUES (?, ?, ?, ?, 2)");
    $stmt->execute([$name, $email, $phone, $hashedPassword]);

    // Obtener ID del nuevo usuario
    $userId = $pdo->lastInsertId();

    // Enviar correo de confirmación (opcional)
    send_registration_confirmation($email, $name);

    // Iniciar sesión automáticamente
    login_user($userId);

    // Redirigir según rol
    redirect_by_role();

} catch (PDOException $e) {
    redirect_with_message('../public/register.php', 'Error al registrar el usuario. Inténtalo más tarde.', 'error');
}
?>