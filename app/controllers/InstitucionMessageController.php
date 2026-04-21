<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/MensajeInstitucion.php';
require_once __DIR__ . '/../models/Institution.php';

// Controlador de mensajeria del lado institucion (espejo de AdminMessageController)
class InstitucionMessageController {
    private PDO $db;
    private MensajeInstitucion $mensajes;
    private Institution $instituciones;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['institution_id'])) {
            header('Location: ' . url('/institution-login'));
            exit;
        }

        $database = new Database();
        $this->db = $database->connect();
        $this->mensajes      = new MensajeInstitucion($this->db);
        $this->instituciones = new Institution($this->db);
    }

    // Bandeja: lista de hilos de esta institucion con el admin
    public function index(): void {
        $idInstitucion = (int)$_SESSION['institution_id'];
        $institucion   = $this->instituciones->getById($idInstitucion);
        $asunto        = trim($_GET['asunto'] ?? '');

        $hilos        = $this->mensajes->listarHilosInstitucionLado($idInstitucion);
        $hiloMensajes = [];

        if ($asunto !== '') {
            $hiloMensajes = $this->mensajes->obtenerHilo($idInstitucion, $asunto);
            // Marcar los mensajes del admin como leidos al abrir el hilo
            $this->mensajes->marcarLeidosAdmin($idInstitucion, $asunto);
        }

        $flashData = getFlash();

        require_once __DIR__ . '/../../views/institucion/messages.view.php';
    }

    // Enviar mensaje al admin (nuevo hilo o respuesta)
    public function send(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/institution/messages'));
            exit;
        }

        $idInstitucion = (int)$_SESSION['institution_id'];
        $asunto        = trim($_POST['asunto']  ?? '');
        $mensaje       = trim($_POST['mensaje'] ?? '');

        if ($asunto === '' || $mensaje === '') {
            redirectWithFlash(url('/institution/messages'), 'error', 'Faltan datos para enviar el mensaje');
        }

        if (mb_strlen($asunto) > 255)   $asunto  = mb_substr($asunto, 0, 255);
        if (mb_strlen($mensaje) > 5000) $mensaje = mb_substr($mensaje, 0, 5000);

        // idAdmin = null cuando el emisor es la institucion (no hay admin asociado)
        $id = $this->mensajes->enviar($idInstitucion, null, $asunto, $mensaje, 'institucion');

        if ($id === false) {
            redirectWithFlash(url('/institution/messages'), 'error', 'No se pudo guardar el mensaje');
        }

        $redirect = url('/institution/messages') . '?asunto=' . urlencode($asunto);
        redirectWithFlash($redirect, 'success', 'Mensaje enviado');
    }
}
