<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/MensajeInstitucion.php';
require_once __DIR__ . '/../../models/Institution.php';
require_once __DIR__ . '/../../models/AdminLog.php';

// Controlador de mensajeria admin <-> institucion 
class AdminMessageController {
    private PDO $db;
    private MensajeInstitucion $mensajes;
    private Institution $instituciones;
    private AdminLog $adminLog;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $database = new Database();
        $this->db = $database->connect();
        $this->mensajes      = new MensajeInstitucion($this->db);
        $this->instituciones = new Institution($this->db);
        $this->adminLog      = new AdminLog($this->db);
    }

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
            header('Location: ' . url('/login'));
            exit;
        }
    }

    // Bandeja: listado de instituciones con resumen.
    public function index(): void {
        $this->requireAdmin();

        $search         = trim($_GET['search'] ?? '');
        $idInstitucion  = (int)($_GET['institucion'] ?? 0);
        $asunto         = trim($_GET['asunto'] ?? '');
        $soloNoLeidos   = !empty($_GET['no_leidos']);

        $instituciones = $this->mensajes->listarBandeja($search, $soloNoLeidos);

        $institucionActiva = null;
        $hilos             = [];
        $hiloMensajes      = [];

        if ($idInstitucion > 0) {
            $institucionActiva = $this->instituciones->getById($idInstitucion);
            if ($institucionActiva) {
                $hilos = $this->mensajes->listarHilosInstitucion($idInstitucion);

                if ($asunto !== '') {
                    $hiloMensajes = $this->mensajes->obtenerHilo($idInstitucion, $asunto);
                    // Marcar los mensajes entrantes del hilo como leidos al abrirlos
                    $this->mensajes->marcarLeidos($idInstitucion, $asunto);
                }
            }
        }

        $flashData = getFlash();

        require_once __DIR__ . '/../../../views/admin/messages.view.php';
    }

    // Enviar un mensaje nuevo (nuevo hilo si no existe ese asunto, respuesta si ya existe)
    public function send(): void {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/messages'));
            exit;
        }

        $idInstitucion = (int)($_POST['idInstitucion'] ?? 0);
        $asunto        = trim($_POST['asunto']  ?? '');
        $mensaje       = trim($_POST['mensaje'] ?? '');

        if ($idInstitucion <= 0 || $asunto === '' || $mensaje === '') {
            redirectWithFlash(url('/admin/messages'), 'error', 'Faltan datos para enviar el mensaje');
        }

        // Validar que la institucion exista
        $inst = $this->instituciones->getById($idInstitucion);
        if (!$inst) {
            redirectWithFlash(url('/admin/messages'), 'error', 'Institucion no encontrada');
        }

        // Limites razonables
        if (mb_strlen($asunto) > 255)   $asunto  = mb_substr($asunto, 0, 255);
        if (mb_strlen($mensaje) > 5000) $mensaje = mb_substr($mensaje, 0, 5000);

        $adminId = (int)$_SESSION['user_id'];
        $id = $this->mensajes->enviar($idInstitucion, $adminId, $asunto, $mensaje, 'admin');

        if ($id === false) {
            redirectWithFlash(url('/admin/messages'), 'error', 'No se pudo guardar el mensaje');
        }

        $this->adminLog->log(
            $adminId,
            'enviar',
            'mensaje_institucion',
            $id,
            "Institucion: {$inst['nombre']} | Asunto: {$asunto}"
        );

        $redirect = url('/admin/messages')
                  . '?institucion=' . $idInstitucion
                  . '&asunto=' . urlencode($asunto);
        redirectWithFlash($redirect, 'success', 'Mensaje enviado');
    }
}
