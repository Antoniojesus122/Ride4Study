<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

$userId = $_SESSION['user_id'];
$idAnuncio = isset($_POST['idAnuncio']) ? (int)$_POST['idAnuncio'] : 0;

if ($idAnuncio <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'idAnuncio inválido']);
    exit;
}

try {
    // Obtener anuncio para datos del conductor y horas
    $stmt = $pdo->prepare("SELECT idAnuncio, idUsuario AS idConductor, fechaPublicacion, horaSalida, horaRegreso FROM anuncios WHERE idAnuncio = ?");
    $stmt->execute([$idAnuncio]);
    $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anuncio) {
        http_response_code(404);
        echo json_encode(['error' => 'Anuncio no encontrado']);
        exit;
    }

    if ($anuncio['idConductor'] == $userId) {
        http_response_code(400);
        echo json_encode(['error' => 'No puedes unirte a tu propio anuncio']);
        exit;
    }

    // Evitar duplicados: comprobar si ya existe un viaje para este usuario y anuncio
    $check = $pdo->prepare("SELECT idViaje, estado FROM viajes WHERE idAnuncio = ? AND idPasajero = ? LIMIT 1");
    $check->execute([$idAnuncio, $userId]);
    $existing = $check->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo json_encode(['ok' => true, 'idViaje' => $existing['idViaje'], 'message' => 'Ya estás inscrito en este viaje', 'estado' => $existing['estado']]);
        exit;
    }

    // Insertar nuevo viaje en estado pendiente
    $fechaSalida = null; $fechaRegreso = null;
    // Si el anuncio guarda fecha/hora completos en DB, intentar componer DATETIME
    // Aquí asumimos fechaPublicacion y horaSalida/horaRegreso contienen datos relevantes; mejor: si el anuncio tiene campos específicos de fechaSalida.
    // Usamos fechaPublicacion como referencia si no hay otra fecha específica
    if (!empty($anuncio['fechaPublicacion'])) {
        $fechaSalida = $anuncio['fechaPublicacion'] . ' ' . ($anuncio['horaSalida'] ?? '00:00:00');
    }
    if (!empty($anuncio['horaRegreso'])) {
        $fechaRegreso = $anuncio['fechaPublicacion'] . ' ' . $anuncio['horaRegreso'];
    }

    $insert = $pdo->prepare("INSERT INTO viajes (idAnuncio, idConductor, idPasajero, estado, fechaSalida, fechaRegreso) VALUES (?, ?, ?, 'pendiente', ?, ?)");
    $insert->execute([$idAnuncio, $anuncio['idConductor'], $userId, $fechaSalida, $fechaRegreso]);
    $idViaje = (int)$pdo->lastInsertId();

    echo json_encode(['ok' => true, 'idViaje' => $idViaje, 'message' => 'Te has unido al viaje (pendiente de verificación)']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno']);
}

?>
