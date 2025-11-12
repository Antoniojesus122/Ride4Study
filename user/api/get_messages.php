<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

require_once __DIR__ . '/../../includes/db.php';
$userId = $_SESSION['user_id'];
$chatWithId = $_GET['chat_with'] ?? null;

if (!$chatWithId) {
    echo json_encode([]);
    exit;
}

try {
    // Obtener los mensajes entre los dos usuarios
    $stmt = $pdo->prepare("
        SELECT * FROM mensajes
        WHERE (idEmisor = ? AND idReceptor = ?)
           OR (idEmisor = ? AND idReceptor = ?)
        ORDER BY fechaCreacion ASC
    ");
    $stmt->execute([$userId, $chatWithId, $chatWithId, $userId]);
    $messages = $stmt->fetchAll();

    // Marcar mensajes como leídos
    $updateStmt = $pdo->prepare("
        UPDATE mensajes SET leido = 1 
        WHERE idEmisor = ? AND idReceptor = ? AND leido = 0
    ");
    $updateStmt->execute([$chatWithId, $userId]);

    echo json_encode($messages);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error de base de datos']);
}