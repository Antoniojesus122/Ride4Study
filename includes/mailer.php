    <?php

    // Todo esto hay que estudiarlo y configurarlo bien, es decir, entender mejor el código y pulirlo como es debido.

    // includes/mailer.php
    // Simple mailer wrapper with optional SMTP support (no external deps).

    // Configuration: adjust to your SMTP provider or leave use_smtp=false to use PHP mail().
    $MAIL_CONFIG = [
        'use_smtp' => false, // set true to enable SMTP
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'smtp_user',
        'password' => 'smtp_pass',
        'encryption' => 'tls', // 'tls' or 'ssl' or ''
        'from_email' => 'no-reply@ride4study.local',
        'from_name' => 'Ride4Study'
    ];

    /**
     * Low-level SMTP sender using sockets. Returns true on success.
     * Note: this is a minimal implementation to support common SMTP servers.
     */
    function smtp_send($to, $subject, $body, $fromEmail, $fromName, $config) {
        $CRLF = "\r\n";
        $timeout = 10;
        $host = $config['host'];
        $port = $config['port'];

        $sock = fsockopen($host, $port, $errno, $errstr, $timeout);
        if (!$sock) {
            error_log("SMTP connect failed: $errno $errstr");
            return false;
        }

        $read = fgets($sock, 512);
        // EHLO
        fwrite($sock, "EHLO localhost$CRLF");
        $ehlo = '';
        while ($line = fgets($sock, 512)) {
            $ehlo .= $line;
            if (substr($line,3,1) != '-') break;
        }

        // Start TLS if needed
        if (!empty($config['encryption']) && strtolower($config['encryption']) === 'tls') {
            fwrite($sock, "STARTTLS$CRLF");
            $res = fgets($sock, 512);
            if (strpos($res, '220') === 0) {
                stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                // EHLO again
                fwrite($sock, "EHLO localhost$CRLF");
                while ($line = fgets($sock, 512)) {
                    if (substr($line,3,1) != '-') break;
                }
            }
        }

        // Auth if username provided
        if (!empty($config['username'])) {
            fwrite($sock, "AUTH LOGIN$CRLF");
            fgets($sock, 512);
            fwrite($sock, base64_encode($config['username']) . $CRLF);
            fgets($sock, 512);
            fwrite($sock, base64_encode($config['password']) . $CRLF);
            fgets($sock, 512);
        }

        // MAIL FROM
        fwrite($sock, "MAIL FROM:<$fromEmail>$CRLF");
        fgets($sock, 512);
        // RCPT TO
        fwrite($sock, "RCPT TO:<$to>$CRLF");
        fgets($sock, 512);
        // DATA
        fwrite($sock, "DATA$CRLF");
        fgets($sock, 512);

        $headers = [];
        $headers[] = "From: $fromName <$fromEmail>";
        $headers[] = "To: $to";
        $headers[] = "Subject: $subject";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/plain; charset=UTF-8";

        $data = implode($CRLF, $headers) . $CRLF . $CRLF . $body . $CRLF . "." . $CRLF;
        fwrite($sock, $data);
        $res = fgets($sock, 512);

        // QUIT
        fwrite($sock, "QUIT$CRLF");
        fclose($sock);

        return (strpos($res, '250') !== false || strpos($res, '354') !== false);
    }

    /**
     * Wrapper to send mail using SMTP (if configured) or PHP mail() as fallback.
     */
    function send_mail($to, $subject, $message, $fromEmail = null, $fromName = null) {
        global $MAIL_CONFIG;

        $fromEmail = $fromEmail ?? $MAIL_CONFIG['from_email'];
        $fromName = $fromName ?? $MAIL_CONFIG['from_name'];

        if (!empty($MAIL_CONFIG['use_smtp'])) {
            $ok = smtp_send($to, $subject, $message, $fromEmail, $fromName, $MAIL_CONFIG);
            if ($ok) return true;
            // log failure and fall back to mail()
            error_log("SMTP send failed for $to, falling back to mail()");
        }

        $headers = "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $fromEmail\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        $result = mail($to, $subject, $message, $headers);
        if (!$result) {
            // Log to file for debugging in local env
            $logLine = date('Y-m-d H:i:s') . " | mail() failed | to:$to | subject:" . str_replace(array("\r", "\n"), ' ', $subject) . "\n";
            @file_put_contents(__DIR__ . '/../logs/email.log', $logLine, FILE_APPEND);
        }
        return $result;
    }


    /**
     * Enviar correo de confirmación de registro
     */
    function send_registration_confirmation($toEmail, $userName) {
        $subject = "¡Bienvenido a Ride4Study, $userName!";
        $message = "Hola $userName,\n\nGracias por registrarte en Ride4Study. Tu cuenta ha sido creada con éxito.\n\n¡Empieza a compartir viajes hoy mismo!\n\nSaludos,\nEl equipo de Ride4Study";

        return send_mail($toEmail, $subject, $message);
    }

    /**
     * Enviar notificación de nuevo anuncio
     */
    function send_new_post_notification($toEmail, $userName, $anuncioTitle) {
        $subject = "Nuevo anuncio publicado: $anuncioTitle";
        $message = "Hola $userName,\n\nSe ha publicado un nuevo anuncio en Ride4Study.\n\nTítulo: $anuncioTitle\n\n¡No te lo pierdas!\n\nSaludos,\nEl equipo de Ride4Study";

        return send_mail($toEmail, $subject, $message);
    }

    /**
     * Enviar notificación de contacto entre usuarios
     */
    function send_contact_notification($toEmail, $fromUser, $messageText) {
        $subject = "Nuevo mensaje de $fromUser";
        $message = "Hola,\n\nHas recibido un nuevo mensaje de $fromUser:\n\n$messageText\n\nPuedes responder desde tu panel de usuario.\n\nSaludos,\nEl equipo de Ride4Study";

        return send_mail($toEmail, $subject, $message);
    }

    ?>