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

            $html = "<p><strong>Nombre:</strong> {$name}</p>";
            $html .= "<p><strong>Email:</strong> {$email}</p>";
            $html .= "<p><strong>Asunto:</strong> {$subject}</p>";
            $html .= "<hr><p><strong>Mensaje:</strong></p><p>" . nl2br($message) . "</p>";

            $response = $mailService->send($to, 'Soporte Ride4Study', $subject, $html);

            if (is_array($response) && !empty($response['success'])) {
                $status = 'success';
                $msg = 'Tu mensaje ha sido enviado correctamente.';
            } else {
                $status = 'error';
                $msg = 'No se pudo enviar el mensaje. Comprueba la configuración de correo.';
            }

            header("Location: support.php?status=$status&msg=" . urlencode($msg));
            exit;
        }
    }
}
