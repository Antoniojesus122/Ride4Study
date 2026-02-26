<?php

require_once __DIR__ . '/../config/env.php';

class MailService
{
    private $apiKey;

    public function __construct()
    {

    $this->apiKey = $_ENV['BREVO_API_KEY'] ?? null;

    if (!$this->apiKey) {
        throw new Exception("API Key no encontrada en variables de entorno.");
    }

    }
    /**
     * Genera una plantilla HTML moderna con el diseño de Ride4Study
     * 
     * @param string $nombre Nombre del destinatario
     * @param string $titulo Título principal del correo
     * @param string $contenido Contenido HTML del mensaje
     * @param string|null $destacado Texto destacado (código, token, etc.)
     * @param string|null $urlBoton URL del botón de acción
     * @param string|null $textoBoton Texto del botón
     * @return string HTML completo del correo
     */
    public function generarPlantilla($nombre, $titulo, $contenido, $destacado = null, $urlBoton = null, $textoBoton = 'Ir a Ride4Study')
    {
        $anio = date('Y');
        
        $bloqueDestacado = $destacado ? "
            <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:25px 0; text-align:center;\">
                <p style=\"margin:0; font-size:22px; font-weight:bold; letter-spacing:2px; color:#34d399;\">
                    {$destacado}
                </p>
            </div>
        " : '';
        
        $boton = $urlBoton ? "
            <div style=\"text-align:center; margin-top:30px;\">
                <a href=\"{$urlBoton}\" 
                   style=\"background:linear-gradient(90deg,#34d399,#22d3ee);
                          color:#0f172a;
                          text-decoration:none;
                          padding:14px 28px;
                          border-radius:999px;
                          font-weight:bold;
                          display:inline-block;
                          font-size:14px;\">
                    {$textoBoton}
                </a>
            </div>
        " : '';
        
        return "
<!DOCTYPE html>
<html lang=\"es\">
<head>
<meta charset=\"UTF-8\">
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
<title>Ride4Study</title>
</head>

<body style=\"margin:0; padding:0; background-color:#0f172a; font-family:Arial, Helvetica, sans-serif;\">

<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:#0f172a; padding:40px 0;\">
<tr>
<td align=\"center\">

<!-- CONTENEDOR PRINCIPAL -->
<table width=\"600\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\" style=\"background-color:#1e293b; border-radius:16px; overflow:hidden; max-width:90%;\">

<!-- HEADER -->
<tr>
<td align=\"center\" style=\"padding:30px; background:linear-gradient(90deg,#34d399,#22d3ee);\">
    <h1 style=\"margin:0; color:#0f172a; font-size:24px; font-weight:bold;\">
        🚗 Ride4Study
    </h1>
</td>
</tr>

<!-- CONTENIDO -->
<tr>
<td style=\"padding:40px 30px; color:#e2e8f0;\">

    <h2 style=\"margin-top:0; font-size:20px; color:#ffffff;\">
        {$titulo}
    </h2>

    <div style=\"font-size:15px; line-height:1.6; color:#cbd5e1;\">
        {$contenido}
    </div>

    {$bloqueDestacado}

    {$boton}

</td>
</tr>

<!-- FOOTER -->
<tr>
<td style=\"padding:25px; text-align:center; background-color:#0f172a; color:#64748b; font-size:12px;\">
    © {$anio} Ride4Study<br>
    Conectando estudiantes, compartiendo trayectos.
</td>
</tr>

</table>

</td>
</tr>
</table>

</body>
</html>
        ";
    }

    public function send($toEmail, $toName, $subject, $html)
    {
        $data = [
            "sender" => [
                "name" => "Ride4Study",
                "email" => "ride4study@outlook.es"
            ],
            "to" => [[
                "email" => $toEmail,
                "name" => $toName
            ]],
            "subject" => $subject,
            "htmlContent" => $html
        ];

        $ch = curl_init("https://api.brevo.com/v3/smtp/email");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "api-key: {$this->apiKey}",
            "content-type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        $success = ($curlErrNo === 0 && $httpCode >= 200 && $httpCode < 300);

        $result = [
            'success' => $success,
            'http_code' => $httpCode,
            'response' => $decoded !== null ? $decoded : $response,
            'curl_errno' => $curlErrNo,
            'curl_error' => $curlError
        ];

        if (!$success) {
            $errMsg = 'MailService error: ' . json_encode($result);
            error_log($errMsg);
        }

        return $result;
    }
}