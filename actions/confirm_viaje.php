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
$idViaje = isset($_POST['idViaje']) ? (int)$_POST['idViaje'] : 0;

if ($idViaje <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'idViaje inválido']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM viajes WHERE idViaje = ? LIMIT 1");
    $stmt->execute([$idViaje]);
    $viaje = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$viaje) {
        http_response_code(404);
        echo json_encode(['error' => 'Viaje no encontrado']);
        exit;
    }

    $isConductor = $viaje['idConductor'] == $userId;
    $isPasajero = $viaje['idPasajero'] == $userId;

    if (!$isConductor && !$isPasajero) {
        http_response_code(403);
        echo json_encode(['error' => 'No puedes confirmar este viaje']);
        exit;
    }

    // Actualizar confirmación según rol
    if ($isConductor) {
        $update = $pdo->prepare("UPDATE viajes SET conductor_confirmo = 1 WHERE idViaje = ?");
        $update->execute([$idViaje]);
    }
    if ($isPasajero) {
        $update = $pdo->prepare("UPDATE viajes SET pasajero_confirmo = 1 WHERE idViaje = ?");
        $update->execute([$idViaje]);
    }

    // Releer estado de confirmaciones
    $stmt = $pdo->prepare("SELECT conductor_confirmo, pasajero_confirmo FROM viajes WHERE idViaje = ?");
    $stmt->execute([$idViaje]);
    $conf = $stmt->fetch(PDO::FETCH_ASSOC);

    $newEstado = 'pendiente';
    if ($conf['conductor_confirmo'] && $conf['pasajero_confirmo']) {
        $newEstado = 'verificado';
    } elseif ($conf['conductor_confirmo'] || $conf['pasajero_confirmo']) {
        $newEstado = 'parcial';
    }

    $upd = $pdo->prepare("UPDATE viajes SET estado = ? WHERE idViaje = ?");
    $upd->execute([$newEstado, $idViaje]);

    echo json_encode(['ok' => true, 'estado' => $newEstado]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error interno']);
}

?>
