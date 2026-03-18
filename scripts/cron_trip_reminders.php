<?php
// Cron: Recordatorio de viaje (día antes de la salida)
// Envía email y notificación in-app a conductores y pasajeros con viajes programados para el día siguiente
// Se ejecuta cada vez que un usuario inicia sesión.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../app/models/Notification.php';

try {
    $database = new Database();
    $db = $database->connect();
    $mailService = new MailService();
    $notification = new Notification($db);

    $tomorrow = date('Y-m-d', strtotime('+1 day'));

    // Buscar viajes aceptados para el día siguiente que no hayan sido recordados
    // Unimos conductores y pasajeros para notificar a ambos
    $stmt = $db->prepare("
        SELECT
            v.idViaje,
            v.idConductor,
            v.idPasajero,
            a.fechaSalida,
            a.horaSalida,
            a.horaRegreso,
            conductor.nombre as conductorNombre,
            conductor.correo as conductorCorreo,
            conductor.notificaciones_email as conductorNotif,
            pasajero.nombre as pasajeroNombre,
            pasajero.correo as pasajeroCorreo,
            pasajero.notificaciones_email as pasajeroNotif,
            lo.nombreLocalidad as origenNombre,
            ld.nombreLocalidad as destinoNombre
        FROM viajes v
        INNER JOIN anuncios a ON v.idAnuncio = a.idAnuncio
        INNER JOIN usuarios conductor ON v.idConductor = conductor.idUsuario
        INNER JOIN usuarios pasajero ON v.idPasajero = pasajero.idUsuario
        INNER JOIN localidades lo ON a.origen = lo.idLocalidad
        INNER JOIN localidades ld ON a.destino = ld.idLocalidad
        WHERE v.estado = 'aceptado'
          AND a.fechaSalida = :tomorrow
          AND NOT EXISTS (
              SELECT 1 FROM notificaciones n
              WHERE n.idUsuario IN (v.idConductor, v.idPasajero)
              AND n.mensaje LIKE CONCAT('%viaje programado%', lo.nombreLocalidad, '%')
              AND n.fechaEnvio >= DATE_SUB(NOW(), INTERVAL 2 DAY)
          )
    ");
    $stmt->execute([':tomorrow' => $tomorrow]);
    $trips = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Para evitar enviar duplicados al mismo usuario en la misma ejecución
    $notified = [];

    foreach ($trips as $trip) {
        $ruta = $trip['origenNombre'] . ' -> ' . $trip['destinoNombre'];
        $hora = substr($trip['horaSalida'], 0, 5);
        $fechaFormato = date('d/m/Y', strtotime($trip['fechaSalida']));

        // Usuarios a notificar: conductor y pasajero
        $users = [
            [
                'id' => (int)$trip['idConductor'],
                'nombre' => $trip['conductorNombre'],
                'correo' => $trip['conductorCorreo'],
                'notif' => (int)$trip['conductorNotif'],
            ],
            [
                'id' => (int)$trip['idPasajero'],
                'nombre' => $trip['pasajeroNombre'],
                'correo' => $trip['pasajeroCorreo'],
                'notif' => (int)$trip['pasajeroNotif'],
            ],
        ];

        foreach ($users as $user) {
            $key = $user['id'] . '-' . $trip['idViaje'];
            if (isset($notified[$key])) continue;
            $notified[$key] = true;

            // Notificación in-app
            $notification->create(
                $user['id'],
                'Recuerda que manana tienes un viaje programado: ' . $ruta . ' a las ' . $hora . '.',
                'fas fa-bell',
                url('/my-rides')
            );

            // Email
            if ($user['notif'] === 1) {
                $contenido = "
                    <p>Te recordamos que manana tienes un viaje programado.</p>

                    <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Ruta:</strong> {$ruta}</p>
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Fecha:</strong> {$fechaFormato}</p>
                        <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Hora de salida:</strong> {$hora}</p>
                    </div>

                    <p style=\"color:#94a3b8;\">Asegurate de coordinar los detalles con tu companero de viaje. Buen viaje.</p>
                ";

                $html = $mailService->generarPlantilla(
                    $user['nombre'],
                    "Recordatorio de viaje",
                    $contenido,
                    null,
                    'http://localhost/Ride4Study/my-rides',
                    'Ver Mis Viajes'
                );
                $mailService->send($user['correo'], $user['nombre'], 'Recordatorio: viaje manana - Ride4Study', $html);
            }
        }
    }

} catch (Exception $e) {
    error_log("cron_trip_reminders error: " . $e->getMessage());
}
