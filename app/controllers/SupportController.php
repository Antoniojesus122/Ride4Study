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

            $to = 'antoniojesusgonzalezdomingo4@gmail.com';
            $headers = "From: $email\r\n";
            $headers .= "Reply-To: $email\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            $fullMessage = "Nombre: $name\n";
            $fullMessage .= "Email: $email\n\n";
            $fullMessage .= "Mensaje:\n$message";

            if (mail($to, $subject, $fullMessage, $headers)) {
                $status = 'success';
                $msg = 'Tu mensaje ha sido enviado correctamente.';
            } else {
                $status = 'success';
                $msg = 'Tu mensaje ha sido enviado correctamente (Simulación Local).';
            }

            header("Location: support.php?status=$status&msg=" . urlencode($msg));
            exit;
        }
    }
}
