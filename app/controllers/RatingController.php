<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Rating.php';

class RatingController {
    private $db;
    private $rating;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new Database();
        $this->db = $database->connect();
        $this->rating = new Rating($this->db);
    }

    public function submit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $from = (int)$_SESSION['user_id'];
        $to = (int)($_POST['idValorado'] ?? 0);
        $idViaje = (int)($_POST['idViaje'] ?? 0);
        $score = (int)($_POST['puntuacion'] ?? 0);

        // Validaciones básicas
        if ($to <= 0 || $idViaje <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        if ($score < 1 || $score > 5) {
            echo json_encode(['success' => false, 'message' => 'La puntuación debe estar entre 1 y 5']);
            exit;
        }

        // Recoger categorías opcionales
        $categories = [];
        if (isset($_POST['puntualidad'])) {
            $categories['puntualidad'] = (int)$_POST['puntualidad'];
        }
        if (isset($_POST['comunicacion'])) {
            $categories['comunicacion'] = (int)$_POST['comunicacion'];
        }
        if (isset($_POST['vehiculo'])) {
            $categories['vehiculo'] = (int)$_POST['vehiculo'];
        }
        if (isset($_POST['conduccion'])) {
            $categories['conduccion'] = (int)$_POST['conduccion'];
        }
        if (isset($_POST['comportamiento'])) {
            $categories['comportamiento'] = (int)$_POST['comportamiento'];
        }

        // Comentario opcional
        $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : null;
        if ($comentario !== null && strlen($comentario) > 500) {
            echo json_encode(['success' => false, 'message' => 'El comentario no puede exceder 500 caracteres']);
            exit;
        }

        // Intentar agregar valoración
        $result = $this->rating->add($from, $to, $idViaje, $score, $categories, $comentario);
        
        if ($result === true) {
            $avg = $this->rating->getAverage($to);
            echo json_encode([
                'success' => true, 
                'avg' => round($avg, 2),
                'message' => '¡Gracias por tu valoración!'
            ]);
        } else {
            // $result contiene el mensaje de error
            echo json_encode([
                'success' => false, 
                'message' => is_string($result) ? $result : 'Error al guardar valoración'
            ]);
        }
        exit;
    }

    // Mostrar formulario de valoración
    public function showRatingForm() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $idViaje = isset($_GET['viaje']) ? (int)$_GET['viaje'] : 0;

        if ($idViaje <= 0) {
            header('Location: ' . url('/dashboard'));
            exit;
        }

        // Obtener detalles del viaje
        $tripDetails = $this->rating->getTripDetailsForRating($idViaje, $_SESSION['user_id']);

        if (!$tripDetails) {
            $_SESSION['error'] = 'No se encontró el viaje o no tienes permiso para valorarlo';
            header('Location: ' . url('/dashboard'));
            exit;
        }

        // Determinar quién es el usuario a valorar
        $userToRate = null;
        $userRole = '';
        
        if ($tripDetails['idPasajero'] == $_SESSION['user_id']) {
            // El usuario actual es pasajero, valora al conductor
            $userToRate = [
                'id' => $tripDetails['conductorId'],
                'nombre' => $tripDetails['conductorNombre'],
                'foto' => $tripDetails['conductorFoto']
            ];
            $userRole = 'conductor';
        } else {
            // El usuario actual es conductor, valora al pasajero
            $userToRate = [
                'id' => $tripDetails['pasajeroId'],
                'nombre' => $tripDetails['pasajeroNombre'],
                'foto' => $tripDetails['pasajeroFoto']
            ];
            $userRole = 'pasajero';
        }

        // Cargar vista
        require_once __DIR__ . '/../../views/user/rating-form.view.php';
    }

    // Obtener valoraciones pendientes para el usuario actual
    public function getPendingRatings() {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $pending = $this->rating->getPendingRatingsForUser($_SESSION['user_id']);
        echo json_encode(['success' => true, 'ratings' => $pending]);
        exit;
    }
}
