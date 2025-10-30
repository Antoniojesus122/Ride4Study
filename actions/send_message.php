<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if (!isset($_POST['message']) || !isset($_POST['receiver_id'])) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

$idEmisor = $_SESSION['idUsuario'];
$idReceptor = (int)$_POST['receiver_id'];
$mensaje = trim($_POST['message']);

// Validación básica
if (empty($mensaje)) {
    echo json_encode(['error' => 'El mensaje no puede estar vacío']);
    exit;
}

try {
    // Insertar el mensaje
    $query = "INSERT INTO messages (idEmisor, idReceptor, mensaje) 
              VALUES (:idEmisor, :idReceptor, :mensaje)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':idEmisor' => $idEmisor,
        ':idReceptor' => $idReceptor,
        ':mensaje' => $mensaje
    ]);
    
    echo json_encode([
        'success' => true,
        'message_id' => $pdo->lastInsertId()
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error al enviar el mensaje',
        'debug' => $e->getMessage()
    ]);
}
?>