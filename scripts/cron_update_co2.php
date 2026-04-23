<?php
// Cron: Recalcular CO2 ahorrado para todos los usuarios con viajes completados.
// Configurado como cron job diario en IONOS. Solo ejecutable por CLI.

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Forbidden: este script solo puede ejecutarse desde la línea de comandos.');
}

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/models/Ride.php';

try {
    $database = new Database();
    $db = $database->connect();
    $ride = new Ride($db);

    // Obtener usuarios que han participado en viajes completados
    $stmt = $db->query("
        SELECT DISTINCT u.idUsuario
        FROM usuarios u
        JOIN viajes v ON (v.idConductor = u.idUsuario OR v.idPasajero = u.idUsuario)
        JOIN anuncios a ON v.idAnuncio = a.idAnuncio
        WHERE v.estado = 'aceptado'
          AND a.fechaSalida < CURDATE()
          AND u.idRol != 1
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Recalcular CO2 para cada usuario
    $updateStmt = $db->prepare("UPDATE usuarios SET co2_ahorrado = :co2 WHERE idUsuario = :id");
    foreach ($usuarios as $userId) {
        $co2 = $ride->calculateUserCO2((int)$userId);
        $updateStmt->execute([':co2' => $co2, ':id' => $userId]);
    }

} catch (Exception $e) {
    error_log('Cron CO2: ' . $e->getMessage());
}
