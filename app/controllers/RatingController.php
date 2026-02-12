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
        $score = (int)($_POST['puntuacion'] ?? 0);

        if ($to <= 0 || $score < 1 || $score > 5) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        if ($this->rating->hasRated($from, $to)) {
            echo json_encode(['success' => false, 'message' => 'Ya has valorado a este usuario']);
            exit;
        }

        $ok = $this->rating->add($from, $to, $score);
        if ($ok) {
            $avg = $this->rating->getAverage($to);
            echo json_encode(['success' => true, 'avg' => round($avg, 2)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar valoración']);
        }
        exit;
    }
}
