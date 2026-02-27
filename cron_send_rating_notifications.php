<?php
// Este script se ejecuta periódicamente para enviar notificaciones de valoración a los usuarios que han completado un viaje recientemente.

require_once __DIR__ . '/services/RatingNotificationService.php';

// Iniciar output buffering para logging
ob_start();

echo "===========================================\n";
echo "RIDE4STUDY - Rating Notifications Cron Job\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "===========================================\n\n";

try {
    $service = new RatingNotificationService();
    
    echo "Procesando viajes completados...\n\n";
    
    $stats = $service->processCompletedTrips();
    
    echo "RESULTADOS:\n";
    echo "- Viajes procesados: {$stats['trips_processed']}\n";
    echo "- Emails enviados: {$stats['emails_sent']}\n";
    
    if (!empty($stats['errors'])) {
        echo "- Errores encontrados: " . count($stats['errors']) . "\n\n";
        echo "DETALLE DE ERRORES:\n";
        foreach ($stats['errors'] as $error) {
            echo "  ✗ {$error}\n";
        }
    } else {
        echo "- Sin errores ✓\n";
    }
    
    echo "\n===========================================\n";
    echo "Proceso completado exitosamente\n";
    echo "===========================================\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR CRÍTICO:\n";
    echo $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}