<?php

// Controlador para la pagina publica de instituciones y el formulario de contacto
class InstitutionContactController {

    public function index() {
        require_once __DIR__ . '/../../views/public/instituciones.view.php';
    }

    // Enviar email de solicitud de acceso para instituciones
    public function sendContactEmail() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/instituciones'));
            exit;
        }

        // Datos de la institucion
        $instNombre    = htmlspecialchars(trim($_POST['inst_nombre'] ?? ''));
        $instTipo      = htmlspecialchars(trim($_POST['inst_tipo'] ?? ''));
        $instCorreo    = htmlspecialchars(trim($_POST['inst_correo'] ?? ''));
        $instTelefono  = htmlspecialchars(trim($_POST['inst_telefono'] ?? ''));
        $instDireccion = htmlspecialchars(trim($_POST['inst_direccion'] ?? ''));
        $instEstudiantes = htmlspecialchars(trim($_POST['inst_estudiantes'] ?? ''));
        $instWeb       = htmlspecialchars(trim($_POST['inst_web'] ?? ''));

        // Datos del contacto
        $contactoNombre   = htmlspecialchars(trim($_POST['contacto_nombre'] ?? ''));
        $contactoCargo    = htmlspecialchars(trim($_POST['contacto_cargo'] ?? ''));
        $contactoEmail    = htmlspecialchars(trim($_POST['contacto_email'] ?? ''));
        $contactoTelefono = htmlspecialchars(trim($_POST['contacto_telefono'] ?? ''));

        // Mensaje adicional
        $mensaje = htmlspecialchars(trim($_POST['mensaje'] ?? ''));

        // Validar campos obligatorios
        if (!$instNombre || !$instTipo || !$instCorreo || !$instTelefono || !$instDireccion || !$instEstudiantes || !$contactoNombre || !$contactoCargo || !$contactoEmail) {
            redirectWithFlash(url('/instituciones') . '#formulario', 'error', t('inst_public.err_required'));
        }

        if (!filter_var($instCorreo, FILTER_VALIDATE_EMAIL) || !filter_var($contactoEmail, FILTER_VALIDATE_EMAIL)) {
            redirectWithFlash(url('/instituciones') . '#formulario', 'error', t('inst_public.err_email'));
        }

        // Mapear tipo de institucion
        $tiposMap = [
            'universidad' => 'Universidad',
            'instituto'   => 'Instituto / IES',
            'fp'          => 'Centro de FP',
            'otro'        => 'Otro',
        ];
        $tipoTexto = $tiposMap[$instTipo] ?? $instTipo;

        // Mapear rango de estudiantes
        $estudiantesMap = [
            'menos_100'  => 'Menos de 100',
            '100_500'    => '100 - 500',
            '500_1000'   => '500 - 1.000',
            '1000_5000'  => '1.000 - 5.000',
            'mas_5000'   => 'Mas de 5.000',
        ];
        $estudiantesTexto = $estudiantesMap[$instEstudiantes] ?? $instEstudiantes;

        require_once __DIR__ . '/../../services/MailService.php';
        $mailService = new MailService();

        $contenido = "
            <p>Has recibido una <strong>nueva solicitud de acceso institucional</strong> desde la plataforma Ride4Study.</p>

            <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                <p style=\"margin:0 0 5px 0; color:#60a5fa; font-weight:bold; font-size:14px;\">DATOS DE LA INSTITUCION</p>
                <p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Nombre:</strong> {$instNombre}</p>
                <p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Tipo:</strong> {$tipoTexto}</p>
                <p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Email:</strong> {$instCorreo}</p>
                <p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Telefono:</strong> {$instTelefono}</p>
                <p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Direccion:</strong> {$instDireccion}</p>
                <p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Estudiantes:</strong> {$estudiantesTexto}</p>
                " . ($instWeb ? "<p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Web:</strong> {$instWeb}</p>" : "") . "
            </div>

            <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                <p style=\"margin:0 0 5px 0; color:#60a5fa; font-weight:bold; font-size:14px;\">PERSONA DE CONTACTO</p>
                <p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Nombre:</strong> {$contactoNombre}</p>
                <p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Cargo:</strong> {$contactoCargo}</p>
                <p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Email:</strong> {$contactoEmail}</p>
                " . ($contactoTelefono ? "<p style=\"margin:8px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Telefono:</strong> {$contactoTelefono}</p>" : "") . "
            </div>

            " . ($mensaje ? "
            <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                <p style=\"margin:0 0 5px 0; color:#60a5fa; font-weight:bold; font-size:14px;\">MENSAJE ADICIONAL</p>
                <p style=\"margin:8px 0; color:#cbd5e1; white-space:pre-wrap;\">" . nl2br($mensaje) . "</p>
            </div>
            " : "") . "

            <p style=\"color:#94a3b8; font-size:13px;\">Responde a <strong>{$contactoEmail}</strong> para contactar con el responsable de la institucion.</p>
        ";

        $html = $mailService->generarPlantilla(
            'Equipo de Ride4Study',
            'Nueva solicitud de acceso institucional',
            $contenido,
            null,
            null,
            null
        );

        $response = $mailService->send(
            'ride4study@outlook.es',
            'Ride4Study Instituciones',
            "Solicitud de acceso: {$instNombre}",
            $html
        );

        if (is_array($response) && !empty($response['success'])) {
            redirectWithFlash(url('/instituciones') . '#formulario', 'success', 'sent');
        } else {
            redirectWithFlash(url('/instituciones') . '#formulario', 'error', t('inst_public.err_send'));
        }
    }
}
