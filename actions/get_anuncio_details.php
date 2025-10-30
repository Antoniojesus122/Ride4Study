<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    http_response_code(403);
    exit('Acceso denegado');
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            lo.nombreLocalidad AS origen,
            ld.nombreLocalidad AS destino,
            u.idUsuario AS propietarioId,
            u.nombre AS nombreUsuario,
            u.correo AS correoUsuario
        FROM anuncios a
        JOIN localidades lo ON a.origen = lo.idLocalidad
        JOIN localidades ld ON a.destino = ld.idLocalidad
        JOIN usuarios u ON a.idUsuario = u.idUsuario
        WHERE a.idAnuncio = ?
    ");
    
    $stmt->execute([$_GET['id']]);
    $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$anuncio) {
        http_response_code(404);
        exit('Anuncio no encontrado');
    }
    // Formatear fechas
    $anuncio['fechaPublicacion'] = date('d/m/Y', strtotime($anuncio['fechaPublicacion']));

    // ¿Propietario con viajes verificados?
    $stmtV = $pdo->prepare("SELECT COUNT(*) FROM viajes WHERE (idConductor = ? OR idPasajero = ?) AND estado = 'verificado'");
    $stmtV->execute([$anuncio['propietarioId'], $anuncio['propietarioId']]);
    $anuncio['propietarioVerificado'] = (int)$stmtV->fetchColumn() > 0 ? 1 : 0;

    // ¿El usuario actual ya se ha unido a este anuncio como pasajero?
    $anuncio['usuarioYaUnido'] = 0;
    $anuncio['usuarioViajeId'] = null;
    if (isset($_SESSION['user_id'])) {
        $stmtU = $pdo->prepare("SELECT idViaje, estado FROM viajes WHERE idAnuncio = ? AND idPasajero = ? LIMIT 1");
        $stmtU->execute([$anuncio['idAnuncio'], $_SESSION['user_id']]);
        $u = $stmtU->fetch(PDO::FETCH_ASSOC);
        if ($u) {
            $anuncio['usuarioYaUnido'] = 1;
            $anuncio['usuarioViajeId'] = (int)$u['idViaje'];
            $anuncio['usuarioViajeEstado'] = $u['estado'];
        }
        // ¿es el usuario actual el propietario del anuncio?
        $anuncio['esPropietario'] = $_SESSION['user_id'] == $anuncio['propietarioId'] ? 1 : 0;
    }

    header('Content-Type: application/json');
    echo json_encode($anuncio);

} catch (PDOException $e) {
    http_response_code(500);
    exit('Error al obtener los detalles del anuncio');
}
?>