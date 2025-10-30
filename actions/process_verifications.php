<?php
// Script para procesar notificaciones y estados de verificación de viajes.
// Ejecutar desde cron (cada 15-60 minutos) o manualmente.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

try {
    // 1) Enviar notificación a conductor y pasajero para viajes cuya fechaRegreso ya pasó y aún no se ha enviado notificación
    $now = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("SELECT v.idViaje, v.idConductor, v.idPasajero, v.idAnuncio, v.fechaRegreso, u1.correo AS correoConductor, u2.correo AS correoPasajero
        FROM viajes v
        JOIN usuarios u1 ON v.idConductor = u1.idUsuario
        JOIN usuarios u2 ON v.idPasajero = u2.idUsuario
        WHERE v.notification_sent = 0 AND v.fechaRegreso IS NOT NULL AND v.fechaRegreso <= ?");
    $stmt->execute([$now]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $r) {
        $idViaje = $r['idViaje'];
        $link = "http://" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['PHP_SELF']) . "/confirm_viaje.php?idViaje=$idViaje";
        // Enviar email simple a conductor y pasajero
        $subject = "Confirmación de viaje - Ride4Study";
        $body = "Por favor confirma si el viaje (anuncio #{$r['idAnuncio']}) se realizó correctamente. \n\n";
        $body .= "Conductor: confirmar aquí: " . str_replace('confirm_viaje.php', 'confirm_viaje_form.php', $link) . "\n";
        $body .= "Pasajero: confirmar aquí: " . str_replace('confirm_viaje.php', 'confirm_viaje_form.php', $link) . "\n\n";
        $body .= "Si ambos confirman, el viaje se marcará como verificado. Si solo uno confirma será parcial. Si nadie confirma en 48h quedará no verificado.";

        // Enviar notificaciones usando funciones existentes en includes/mailer.php.
        $msg = "Por favor confirma si el viaje (anuncio #{$r['idAnuncio']}) se realizó correctamente.\n\nVisita tu panel en Ride4Study para confirmar.";
        if (function_exists('send_contact_notification')) {
            // enviar a conductor y pasajero
            send_contact_notification($r['correoConductor'], 'Ride4Study', $msg);
            send_contact_notification($r['correoPasajero'], 'Ride4Study', $msg);
        }

        $upd = $pdo->prepare("UPDATE viajes SET notification_sent = 1, notification_sent_at = ? WHERE idViaje = ?");
        $upd->execute([date('Y-m-d H:i:s'), $idViaje]);
    }

    // 2) Marcar no_verificado si han pasado 48h desde fechaRegreso sin confirmaciones
    $boundary = date('Y-m-d H:i:s', strtotime('-48 hours'));
    $stmt2 = $pdo->prepare("SELECT idViaje FROM viajes WHERE estado = 'pendiente' AND fechaRegreso IS NOT NULL AND fechaRegreso <= ?");
    $stmt2->execute([$boundary]);
    $toMark = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($toMark as $m) {
        $upd2 = $pdo->prepare("UPDATE viajes SET estado = 'no_verificado' WHERE idViaje = ?");
        $upd2->execute([$m['idViaje']]);
    }

    echo "Procesado correctamente. Notificaciones enviadas: " . count($rows) . ", viajes marcados no_verificado: " . count($toMark) . "\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
