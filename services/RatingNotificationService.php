<?php
require_once __DIR__ . '/MailService.php';
require_once __DIR__ . '/../config/database.php';

// Servicio para gestionar notificaciones de valoración después de viajes completados
class RatingNotificationService {
    private $conn;
    private $mailService;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
        $this->mailService = new MailService();
    }

    // Procesa viajes completados y envía emails de solicitud de valoración
    public function processCompletedTrips() {
        $stats = [
            'trips_processed' => 0,
            'emails_sent' => 0,
            'errors' => []
        ];

        try {
            // Buscar viajes completados en las últimas 24-48 horas que no hayan sido notificados
            $query = "
                SELECT DISTINCT
                    v.idViaje,
                    v.idAnuncio,
                    v.idConductor,
                    v.idPasajero,
                    a.fechaSalida,
                    a.horaSalida,
                    a.horaRegreso,
                    a.tipo,
                    conductor.nombre as conductorNombre,
                    conductor.correo as conductorCorreo,
                    pasajero.nombre as pasajeroNombre,
                    pasajero.correo as pasajeroCorreo,
                    pasajero.notificaciones_email,
                    origen.nombreLocalidad as origenNombre,
                    destino.nombreLocalidad as destinoNombre
                FROM viajes v
                INNER JOIN anuncios a ON v.idAnuncio = a.idAnuncio
                INNER JOIN usuarios conductor ON v.idConductor = conductor.idUsuario
                INNER JOIN usuarios pasajero ON v.idPasajero = pasajero.idUsuario
                INNER JOIN localidades origen ON a.origen = origen.idLocalidad
                INNER JOIN localidades destino ON a.destino = destino.idLocalidad
                WHERE v.estado = 'aceptado'
                  AND CONCAT(a.fechaSalida, ' ', COALESCE(a.horaRegreso, a.horaSalida)) < NOW()
                  AND CONCAT(a.fechaSalida, ' ', COALESCE(a.horaRegreso, a.horaSalida)) >= DATE_SUB(NOW(), INTERVAL 48 HOUR)
                  AND v.notificacion_valoracion_enviada IS NULL
            ";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $completedTrips = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($completedTrips as $trip) {
                $stats['trips_processed']++;

                // Verificar si el pasajero ya valoró al conductor
                $passengerRated = $this->hasUserRatedTrip($trip['idViaje'], $trip['idPasajero']);

                if (!$passengerRated && $trip['notificaciones_email'] == 1) {
                    // Enviar email al pasajero para que valore al conductor
                    $emailSent = $this->sendRatingRequestEmail($trip, 'passenger');

                    if ($emailSent) {
                        $stats['emails_sent']++;
                    } else {
                        $stats['errors'][] = "Error enviando email al pasajero {$trip['idPasajero']} del viaje {$trip['idViaje']}";
                    }
                }

                // Verificar si el conductor ya valoró al pasajero
                $driverRated = $this->hasUserRatedTrip($trip['idViaje'], $trip['idConductor']);

                if (!$driverRated) {
                    // Obtener preferencias de email del conductor
                    $conductorNotif = $this->getUserEmailPreference($trip['idConductor']);
                    if ($conductorNotif) {
                        $emailSent = $this->sendRatingRequestEmail($trip, 'driver');
                        if ($emailSent) {
                            $stats['emails_sent']++;
                        } else {
                            $stats['errors'][] = "Error enviando email al conductor {$trip['idConductor']} del viaje {$trip['idViaje']}";
                        }
                    }
                }

                // Marcar como notificado
                $this->markTripAsNotified($trip['idViaje']);
            }

        } catch (Exception $e) {
            $stats['errors'][] = "Error procesando viajes: " . $e->getMessage();
            error_log("RatingNotificationService Error: " . $e->getMessage());
        }

        return $stats;
    }

    // Verifica si un usuario ya ha valorado un viaje específico
    private function hasUserRatedTrip($idViaje, $idUsuario) {
        $query = "SELECT COUNT(*) as count FROM valoraciones 
                  WHERE idViaje = :idViaje AND idValorador = :idUsuario";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':idViaje' => $idViaje,
            ':idUsuario' => $idUsuario
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    // Marca un viaje como notificado para evitar enviar múltiples emails
    private function markTripAsNotified($idViaje) {
        $query = "UPDATE viajes 
                  SET notificacion_valoracion_enviada = NOW() 
                  WHERE idViaje = :idViaje";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':idViaje' => $idViaje]);
    }

    // Obtiene la preferencia de notificaciones por email de un usuario
    private function getUserEmailPreference($userId) {
        $stmt = $this->conn->prepare("SELECT notificaciones_email FROM usuarios WHERE idUsuario = :id");
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['notificaciones_email'] === 1;
    }

    // Envía un email solicitando valoración después de un viaje completado
    private function sendRatingRequestEmail($trip, $recipientType = 'passenger') {
        try {
            if ($recipientType === 'driver') {
                // Enviar al conductor para que valore al pasajero
                $recipientName = $trip['conductorNombre'];
                $recipientEmail = $trip['conductorCorreo'];
                $ratedPersonName = $trip['pasajeroNombre'];
                $ratedPersonRole = 'pasajero';
            } else {
                // Enviar al pasajero para que valore al conductor
                $recipientName = $trip['pasajeroNombre'];
                $recipientEmail = $trip['pasajeroCorreo'];
                $ratedPersonName = $trip['conductorNombre'];
                $ratedPersonRole = 'conductor';
            }

            $travelDate = date('d/m/Y', strtotime($trip['fechaSalida']));
            $travelTime = date('H:i', strtotime($trip['horaSalida']));

            $contenido = "
                <p>¡Hola <strong>{$recipientName}</strong>!</p>
                
                <p>Esperamos que tu viaje haya sido excelente.</p>
                
                <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                            padding: 20px; 
                            border-radius: 10px; 
                            margin: 20px 0;
                            color: white;'>
                    <p style='margin: 0; font-size: 16px;'><strong>Ruta:</strong> {$trip['origenNombre']} → {$trip['destinoNombre']}</p>
                    <p style='margin: 10px 0 0 0; font-size: 16px;'><strong>Fecha:</strong> {$travelDate} a las {$travelTime}</p>
                    <p style='margin: 10px 0 0 0; font-size: 16px;'><strong>{$ratedPersonRole}:</strong> {$ratedPersonName}</p>
                </div>
                
                <p>Nos encantaría conocer tu experiencia. <strong>¿Podrías valorar a {$ratedPersonName}?</strong></p>
                
                <p style='color: #64748b; font-size: 14px;'>
                    Tu valoración ayuda a construir una comunidad de confianza y permite que otros estudiantes tomen mejores decisiones.
                </p>
                
                <p style='margin-top: 30px; color: #94a3b8; font-size: 13px;'>
                    Tienes <strong>30 días</strong> desde la fecha del viaje para enviar tu valoración.
                </p>
            ";

            $html = $this->mailService->generarPlantilla(
                $recipientName,
                '¡Valora tu experiencia de viaje!',
                $contenido,
                null,
                fullUrl('/rate') . '?viaje=' . $trip['idViaje'],
                'Valorar ahora'
            );

            return $this->mailService->send(
                $recipientEmail,
                $recipientName,
                "Valora tu viaje con {$ratedPersonName} - Ride4Study",
                $html
            );

        } catch (Exception $e) {
            error_log("Error sending rating email: " . $e->getMessage());
            return false;
        }
    }

    // Test para probar los emails
    public function sendTestEmail($idViaje) {
        $query = "
            SELECT 
                v.idViaje, v.idAnuncio, v.idConductor, v.idPasajero,
                a.fechaSalida, a.horaSalida, a.horaRegreso,
                conductor.nombre as conductorNombre,
                pasajero.nombre as pasajeroNombre,
                pasajero.correo as pasajeroCorreo,
                pasajero.notificaciones_email,
                origen.nombreLocalidad as origenNombre,
                destino.nombreLocalidad as destinoNombre
            FROM viajes v
            INNER JOIN anuncios a ON v.idAnuncio = a.idAnuncio
            INNER JOIN usuarios conductor ON v.idConductor = conductor.idUsuario
            INNER JOIN usuarios pasajero ON v.idPasajero = pasajero.idUsuario
            INNER JOIN localidades origen ON a.origen = origen.idLocalidad
            INNER JOIN localidades destino ON a.destino = destino.idLocalidad
            WHERE v.idViaje = :idViaje
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':idViaje' => $idViaje]);
        $trip = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($trip) {
            return $this->sendRatingRequestEmail($trip, 'passenger');
        }

        return false;
    }
}
