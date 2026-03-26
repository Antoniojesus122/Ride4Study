<?php

class SupportController {
    
    public function index() {
        require_once __DIR__ . '/../../views/public/support.view.php';
    }

    public function sendSupportEmail() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = htmlspecialchars($_POST['name'] ?? '');
            $email = htmlspecialchars($_POST['email'] ?? '');
            $message = htmlspecialchars($_POST['message'] ?? '');
            $subject = htmlspecialchars($_POST['subject'] ?? 'Solicitud de Soporte');

            require_once __DIR__ . '/../../services/MailService.php';
            $mailService = new MailService();

            $to = 'ride4study@outlook.es';

            $contenido = "
                <p>Has recibido un nuevo mensaje de soporte desde la plataforma Ride4Study.</p>
                
                <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                    <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Nombre:</strong> {$name}</p>
                    <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Email:</strong> {$email}</p>
                    <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Asunto:</strong> {$subject}</p>
                </div>
                
                <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                    <p style=\"margin:0 0 10px 0; color:#34d399; font-weight:bold;\">Mensaje:</p>
                    <p style=\"margin:0; color:#cbd5e1; white-space:pre-wrap;\">" . nl2br($message) . "</p>
                </div>
                
                <p style=\"color:#94a3b8; font-size:13px;\">Responde directamente a <strong>{$email}</strong> para contactar con el usuario.</p>
            ";
            
            $html = $mailService->generarPlantilla(
                'Equipo de Ride4Study',
                'Nuevo mensaje de soporte',
                $contenido,
                null,
                null,
                null
            );

            $response = $mailService->send($to, 'Soporte Ride4Study', $subject, $html);

            if (is_array($response) && !empty($response['success'])) {
                redirectWithFlash(url('/support'), 'success', 'Tu mensaje ha sido enviado correctamente.');
            } else {
                redirectWithFlash(url('/support'), 'error', 'No se pudo enviar el mensaje. Comprueba la configuración de correo.');
            }
        }
    }
}
