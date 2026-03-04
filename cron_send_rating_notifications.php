<?php
// Este script se ejecuta periódicamente para enviar notificaciones de valoración a los usuarios que han completado un viaje recientemente.
// Por ahora lo voy a poner para que se ejecute cada vez que se inice sesión

require_once __DIR__ . '/services/RatingNotificationService.php';

// Iniciar output buffering para logging
ob_start();

try {
    $service = new RatingNotificationService();    
    $stats = $service->processCompletedTrips();
    
    if (!empty($stats['errors'])) {
        error_log("- Errores encontrados: " . count($stats['errors']) . "\n\n");
        error_log("DETALLE DE ERRORES:\n");
        foreach ($stats['errors'] as $error) {
            error_log("  ✗ {$error}\n");
        }
    } else {
        error_log("- Sin errores ✓\n");
    }
    
    error_log("Proceso completado exitosamente\n");
    
} catch (Exception $e) {
    error_log("\n❌ ERROR CRÍTICO:\n");
    error_log($e->getMessage() . "\n");
    error_log("\nStack trace:\n");
    error_log($e->getTraceAsString() . "\n");
}