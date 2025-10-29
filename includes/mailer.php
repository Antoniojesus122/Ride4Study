<?php
// includes/mailer.php

/**
 * Enviar correo de confirmación de registro
 * @param string $toEmail
 * @param string $userName
 * @return bool
 */
function send_registration_confirmation($toEmail, $userName) {
    $subject = "¡Bienvenido a Ride4Study, $userName!";
    $message = "
Hola $userName,

Gracias por registrarte en Ride4Study. Tu cuenta ha sido creada con éxito.

¡Empieza a compartir viajes hoy mismo!

Saludos,
El equipo de Ride4Study
";

    $headers = "From: no-reply@ride4study.local\r\n";
    $headers .= "Reply-To: no-reply@ride4study.local\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // En entornos locales, esto puede fallar. Para pruebas, usa un servicio como Mailtrap.
    return mail($toEmail, $subject, $message, $headers);
}

/**
 * Enviar notificación de nuevo anuncio
 * @param string $toEmail
 * @param string $userName
 * @param string $anuncioTitle
 * @return bool
 */
function send_new_post_notification($toEmail, $userName, $anuncioTitle) {
    $subject = "Nuevo anuncio publicado: $anuncioTitle";
    $message = "
Hola $userName,

Se ha publicado un nuevo anuncio en Ride4Study.

Título: $anuncioTitle

¡No te lo pierdas!

Saludos,
El equipo de Ride4Study
";

    $headers = "From: no-reply@ride4study.local\r\n";
    $headers .= "Reply-To: no-reply@ride4study.local\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail($toEmail, $subject, $message, $headers);
}

/**
 * Enviar notificación de contacto entre usuarios
 * @param string $toEmail
 * @param string $fromUser
 * @param string $messageText
 * @return bool
 */
function send_contact_notification($toEmail, $fromUser, $messageText) {
    $subject = "Nuevo mensaje de $fromUser";
    $message = "
Hola,

Has recibido un nuevo mensaje de $fromUser:

$messageText

Puedes responder desde tu panel de usuario.

Saludos,
El equipo de Ride4Study
";

    $headers = "From: no-reply@ride4study.local\r\n";
    $headers .= "Reply-To: no-reply@ride4study.local\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    return mail($toEmail, $subject, $message, $headers);
}
?>