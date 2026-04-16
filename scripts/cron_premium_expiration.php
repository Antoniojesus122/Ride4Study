<?php
// Cron: Aviso de expiración de premium (3 días antes) y desactivación automática de premium expirado.
// Se ejecuta cada vez que un usuario inicia sesión.

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../app/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/MailService.php';
require_once __DIR__ . '/../app/models/Notification.php';

try {
    $database = new Database();
    $db = $database->connect();
    $mailService = new MailService();
    $notification = new Notification($db);

    // Desactivar premium expirado
    $expireStmt = $db->prepare("
        UPDATE usuarios
        SET premium = 0
        WHERE premium = 1 AND premium_hasta IS NOT NULL AND premium_hasta < NOW()
    ");
    $expireStmt->execute();
    $expired = $expireStmt->rowCount();
    if ($expired > 0) {
        error_log("Premium expirado desactivado para {$expired} usuarios.");
    }

    // Avisar a usuarios cuyo premium expira en exactamente 3 días (con margen de 24h para que no haya duplicados)
    // Solo avisar si no se les ha avisado ya 
    $warningStmt = $db->prepare("
        SELECT u.idUsuario, u.nombre, u.correo, u.notificaciones_email, u.premium_hasta
        FROM usuarios u
        WHERE u.premium = 1
          AND u.premium_hasta IS NOT NULL
          AND u.premium_hasta BETWEEN DATE_ADD(NOW(), INTERVAL 2 DAY) AND DATE_ADD(NOW(), INTERVAL 4 DAY)
          AND NOT EXISTS (
              SELECT 1 FROM notificaciones n
              WHERE n.idUsuario = u.idUsuario
              AND n.mensaje LIKE '%Premium expira%'
              AND n.fechaEnvio >= DATE_SUB(NOW(), INTERVAL 5 DAY)
          )
    ");
    $warningStmt->execute();
    $usersToWarn = $warningStmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($usersToWarn as $user) {
        $premiumHasta = date('d/m/Y', strtotime($user['premium_hasta']));

        // Notificación in-app
        $notification->create(
            (int)$user['idUsuario'],
            'Tu suscripcion Premium expira el ' . $premiumHasta . '. Renuevala para seguir disfrutando de las ventajas.',
            'fas fa-exclamation-triangle',
            url('/premium')
        );

        // Email
        if ((int)($user['notificaciones_email'] ?? 0) === 1) {
            $contenido = "
                <p>Tu suscripcion <strong style=\"color:#34d399;\">Premium</strong> esta a punto de expirar.</p>

                <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                    <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Fecha de expiracion:</strong> {$premiumHasta}</p>
                </div>

                <p style=\"color:#94a3b8;\">Renueva tu suscripcion para seguir disfrutando de anuncios ilimitados, destacar tus viajes y la insignia Premium.</p>
            ";

            $html = $mailService->generarPlantilla(
                $user['nombre'],
                "Tu Premium expira pronto",
                $contenido,
                null,
                fullUrl('/premium'),
                'Renovar Premium'
            );
            $mailService->send($user['correo'], $user['nombre'], 'Tu Premium expira pronto - Ride4Study', $html);
        }
    }

} catch (Exception $e) {
    error_log("cron_premium_expiration error: " . $e->getMessage());
}
