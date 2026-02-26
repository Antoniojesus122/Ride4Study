<?php
require_once __DIR__ . '/services/MailService.php';

// Enviar correo de prueba con la nueva plantilla

$mailService = new MailService();

echo "Antes de enviar<br>";
flush();

$contenido = "
    <p>Este es un correo de prueba desde Ride4Study.</p>
    <p style=\"color:#94a3b8;\">Estamos probando el nuevo diseño de correos electrónicos de la plataforma.</p>
    <p style=\"color:#34d399; font-weight:bold;\">¡Todo funciona correctamente! ✅</p>
";

$html = $mailService->generarPlantilla(
    'Antonio Jesús González Domingo',
    'Hola Antonio,',
    $contenido,
    null,
    'http://localhost/Ride4Study/dashboard.php',
    'Ir al Dashboard'
);

$mailService->send(
    "antoniojesusgonzalezdomingo4@gmail.com",
    "Antonio Jesús González Domingo",
    "Correo de prueba desde Ride4Study",
    $html
);

echo "Después de enviar";
flush(); 
?>
