<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['idUsuario'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$idUsuario = $_SESSION['idUsuario'];
$conversation_id = isset($_GET['conversation_id']) ? (int)$_GET['conversation_id'] : 0;
$last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

try {
    // Obtener los mensajes más recientes
    $query = "SELECT m.*, 
              CASE WHEN m.idEmisor = :idUsuario THEN 1 ELSE 0 END as is_sender
              FROM messages m
              WHERE (m.idEmisor = :idUsuario AND m.idReceptor = :conversation_id)
              OR (m.idEmisor = :conversation_id AND m.idReceptor = :idUsuario)
              AND m.idMensaje > :last_id
              ORDER BY m.fechaCreacion ASC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':idUsuario' => $idUsuario,
        ':conversation_id' => $conversation_id,
        ':last_id' => $last_id
    ]);
    
    // Marcar mensajes como leídos
    $updateQuery = "UPDATE messages 
                   SET leido = 1 
                   WHERE idReceptor = :idUsuario 
                   AND idEmisor = :conversation_id 
                   AND leido = 0";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute([
        ':idUsuario' => $idUsuario,
        ':conversation_id' => $conversation_id
    ]);
    
    echo json_encode([
        'success' => true,
        'messages' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Error al cargar los mensajes',
        'debug' => $e->getMessage()
    ]);
}
?>