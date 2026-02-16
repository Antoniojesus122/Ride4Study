<?php
require_once __DIR__ . '/services/MailService.php';

// Enviar correo de prueba

$mailService = new MailService();

echo "Antes de enviar<br>";
flush();

$mailService->send(
    "antoniojesusgonzalezdomingo4@gmail.com",
    "Antonio Jesús González Domingo",
    "Correo de prueba desde Ride4Study",
    "<h1>¡Hola Antonio!</h1><p>Tienes que valorar tu viaje.</p>"
);

echo "Despues de enviar";
flush(); 
?>
