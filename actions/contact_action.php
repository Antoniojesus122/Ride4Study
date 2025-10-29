<?php

require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
require_once '../includes/mailer.php';

if (!is_logged_in()) {
    redirect_with_message('../public/login.php', 'Debes iniciar sesión para contactar.', 'error');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../public/search.php');
    exit;
}

$idAnuncio = (int)($_POST['idAnuncio'] ?? 0);
$mensaje = sanitize_input($_POST['mensaje'] ?? '');

if ($idAnuncio <= 0 || empty($mensaje)) {
    redirect_with_message('../public/search.php', 'Mensaje inválido o anuncio no especificado.', 'error');
}

try {
    // Obtener datos del anuncio y del usuario receptor
    $stmt = $pdo->prepare("
        SELECT a.idUsuario AS receptor_id, u.correo AS receptor_email, u.nombre AS receptor_nombre
        FROM anuncios a
        JOIN usuarios u ON a.idUsuario = u.idUsuario
        WHERE a.idAnuncio = ?
    ");
    $stmt->execute([$idAnuncio]);
    $receptor = $stmt->fetch();

    if (!$receptor) {
        throw new Exception('Anuncio no encontrado.');
    }

    // Obtener nombre del emisor
    $stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE idUsuario = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $emisor = $stmt->fetch();

    if (!$emisor) {
        throw new Exception('Usuario emisor no encontrado.');
    }

    // Enviar notificación por correo
    send_contact_notification(
        $receptor['receptor_email'],
        $emisor['nombre'],
        $mensaje
    );

    $stmt = $pdo->prepare("INSERT INTO mensajes (idEmisor, idReceptor, contenido) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $receptor['receptor_id'], $mensaje]);

    redirect_with_message('../public/search.php', 'Tu mensaje ha sido enviado.', 'success');

} catch (Exception $e) {
    redirect_with_message('../public/search.php', 'Error al enviar el mensaje: ' . $e->getMessage(), 'error');
}
?>