<?php

require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

if (!is_logged_in()) {
    redirect_with_message('../public/login.php', 'Debes iniciar sesión para publicar un anuncio.', 'error');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../user/post/create.php');
    exit;
}

$action = $_POST['action'] ?? '';
$idAnuncio = (int)($_POST['idAnuncio'] ?? 0);
$tipo = $_POST['tipo'] ?? '';
$origen = (int)($_POST['origen'] ?? 0);
$destino = (int)($_POST['destino'] ?? 0);
$horaSalida = $_POST['horaSalida'] ?? '';
$horaRegreso = $_POST['horaRegreso'] ?? null;
$precio = $_POST['precio'] ?? null;

// Convertir precio a decimal
if ($precio !== null) {
    $precio = floatval(str_replace(',', '.', $precio));
    if ($precio < 0) $precio = 0.00;
}

// Validar campos obligatorios
if (empty($tipo) || !in_array($tipo, ['ofrezco', 'busco']) || $origen <= 0 || $destino <= 0 || empty($horaSalida)) {
    redirect_with_message('../user/post/create.php', 'Por favor, completa todos los campos obligatorios.', 'error');
}

try {
    switch ($action) {
        case 'create':
            $stmt = $pdo->prepare("
                INSERT INTO anuncios (tipo, origen, destino, horaSalida, horaRegreso, precio, idUsuario)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$tipo, $origen, $destino, $horaSalida, $horaRegreso, $precio, $_SESSION['user_id']]);
            redirect_with_message('../user/dashboard.php', 'Anuncio publicado correctamente.', 'success');
            break;

        case 'edit':
            if ($idAnuncio <= 0) {
                throw new Exception('ID de anuncio inválido.');
            }

            // Verificar que el anuncio pertenece al usuario
            $stmt = $pdo->prepare("SELECT idUsuario FROM anuncios WHERE idAnuncio = ?");
            $stmt->execute([$idAnuncio]);
            $anuncio = $stmt->fetch();

            if (!$anuncio || $anuncio['idUsuario'] != $_SESSION['user_id']) {
                throw new Exception('No tienes permiso para editar este anuncio.');
            }

            $stmt = $pdo->prepare("
                UPDATE anuncios 
                SET tipo = ?, origen = ?, destino = ?, horaSalida = ?, horaRegreso = ?, precio = ?
                WHERE idAnuncio = ?
            ");
            $stmt->execute([$tipo, $origen, $destino, $horaSalida, $horaRegreso, $precio, $idAnuncio]);
            redirect_with_message('../user/dashboard.php', 'Anuncio actualizado correctamente.', 'success');
            break;

        case 'delete':
            if ($idAnuncio <= 0) {
                throw new Exception('ID de anuncio inválido.');
            }

            // Verificar propiedad
            $stmt = $pdo->prepare("SELECT idUsuario FROM anuncios WHERE idAnuncio = ?");
            $stmt->execute([$idAnuncio]);
            $anuncio = $stmt->fetch();

            if (!$anuncio || $anuncio['idUsuario'] != $_SESSION['user_id']) {
                throw new Exception('No tienes permiso para eliminar este anuncio.');
            }

            $stmt = $pdo->prepare("DELETE FROM anuncios WHERE idAnuncio = ?");
            $stmt->execute([$idAnuncio]);
            redirect_with_message('../user/dashboard.php', 'Anuncio eliminado correctamente.', 'success');
            break;

        default:
            throw new Exception('Acción no válida.');
    }

} catch (Exception $e) {
    redirect_with_message('../user/post/create.php', 'Error: ' . $e->getMessage(), 'error');
}
?>