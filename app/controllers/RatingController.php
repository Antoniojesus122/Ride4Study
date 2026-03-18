<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Rating.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../../services/MailService.php';

class RatingController {
    private $db;
    private $rating;
    private Notification $notification;
    private MailService $mailService;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $database = new Database();
        $this->db = $database->connect();
        $this->rating = new Rating($this->db);
        $this->notification = new Notification($this->db);
        $this->mailService = new MailService();
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

            // Notificar al usuario valorado
            try {
                $fromName = $_SESSION['user_name'] ?? 'Un usuario';
                // Obtener info del usuario valorado
                $stmtTo = $this->db->prepare("SELECT nombre, correo, notificaciones_email FROM usuarios WHERE idUsuario = :id");
                $stmtTo->execute([':id' => $to]);
                $ratedUser = $stmtTo->fetch(PDO::FETCH_ASSOC);

                // Obtener info del viaje para la notificación
                $stmtTrip = $this->db->prepare("
                    SELECT lo.nombreLocalidad as origenNombre, ld.nombreLocalidad as destinoNombre
                    FROM viajes v
                    JOIN anuncios a ON v.idAnuncio = a.idAnuncio
                    JOIN localidades lo ON a.origen = lo.idLocalidad
                    JOIN localidades ld ON a.destino = ld.idLocalidad
                    WHERE v.idViaje = :idViaje
                ");
                $stmtTrip->execute([':idViaje' => $idViaje]);
                $tripInfo = $stmtTrip->fetch(PDO::FETCH_ASSOC);
                $ruta = $tripInfo ? $tripInfo['origenNombre'] . ' -> ' . $tripInfo['destinoNombre'] : '';

                // Notificación in-app
                $this->notification->create(
                    $to,
                    htmlspecialchars($fromName) . ' ' . t('notif.new_rating') . ' ' . $ruta . '. (' . $score . '/5)',
                    'fas fa-star',
                    url('/profile')
                );

                // Email
                if ($ratedUser && (int)($ratedUser['notificaciones_email'] ?? 0) === 1) {
                    $starsHtml = str_repeat('*', $score) . str_repeat('-', 5 - $score);
                    $contenido = "
                        <p><strong>" . htmlspecialchars($fromName) . "</strong> te ha dejado una valoracion tras vuestro viaje.</p>

                        <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                            <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Ruta:</strong> {$ruta}</p>
                            <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Puntuacion:</strong> {$score}/5</p>"
                            . ($comentario ? "<p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Comentario:</strong> " . htmlspecialchars($comentario) . "</p>" : "") . "
                        </div>

                        <p style=\"color:#94a3b8;\">Puedes ver todas tus valoraciones en tu perfil.</p>
                    ";

                    $html = $this->mailService->generarPlantilla(
                        $ratedUser['nombre'],
                        "Nueva valoracion recibida",
                        $contenido,
                        null,
                        'http://localhost/Ride4Study/profile',
                        'Ver Mi Perfil'
                    );
                    $this->mailService->send($ratedUser['correo'], $ratedUser['nombre'], 'Nueva valoracion recibida - Ride4Study', $html);
                }
            } catch (Exception $e) {
                error_log("Error notificación valoración: " . $e->getMessage());
            }

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

    // Responder a una valoración recibida (solo el usuario valorado)
    public function submitReply() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $idValoracion = (int)($_POST['idValoracion'] ?? 0);
        $respuesta    = trim($_POST['respuesta'] ?? '');

        if ($idValoracion <= 0 || empty($respuesta)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        if (strlen($respuesta) > 300) {
            echo json_encode(['success' => false, 'message' => 'La respuesta no puede superar los 300 caracteres']);
            exit;
        }

        if ($this->rating->addReply($idValoracion, (int)$_SESSION['user_id'], $respuesta)) {
            // Notificar al valorador original
            try {
                $stmtRating = $this->db->prepare("
                    SELECT v.idValorador, v.idViaje, u.nombre as valoradorNombre, u.correo, u.notificaciones_email,
                           lo.nombreLocalidad as origenNombre, ld.nombreLocalidad as destinoNombre
                    FROM valoraciones v
                    JOIN usuarios u ON v.idValorador = u.idUsuario
                    LEFT JOIN viajes vj ON v.idViaje = vj.idViaje
                    LEFT JOIN anuncios a ON vj.idAnuncio = a.idAnuncio
                    LEFT JOIN localidades lo ON a.origen = lo.idLocalidad
                    LEFT JOIN localidades ld ON a.destino = ld.idLocalidad
                    WHERE v.idValoracion = :id
                ");
                $stmtRating->execute([':id' => $idValoracion]);
                $ratingInfo = $stmtRating->fetch(PDO::FETCH_ASSOC);

                if ($ratingInfo) {
                    $replierName = $_SESSION['user_name'] ?? 'Un usuario';
                    $ruta = ($ratingInfo['origenNombre'] && $ratingInfo['destinoNombre'])
                        ? $ratingInfo['origenNombre'] . ' -> ' . $ratingInfo['destinoNombre'] : '';

                    // Notificación in-app
                    $this->notification->create(
                        (int)$ratingInfo['idValorador'],
                        htmlspecialchars($replierName) . ' ' . t('notif.rating_reply') . ' ' . $ruta . '.',
                        'fas fa-reply',
                        url('/profile')
                    );

                    // Email
                    if ((int)($ratingInfo['notificaciones_email'] ?? 0) === 1) {
                        $contenido = "
                            <p><strong>" . htmlspecialchars($replierName) . "</strong> ha respondido a la valoracion que dejaste:</p>

                            <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                                " . ($ruta ? "<p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Ruta:</strong> {$ruta}</p>" : "") . "
                                <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Respuesta:</strong> " . htmlspecialchars($respuesta) . "</p>
                            </div>

                            <p style=\"color:#94a3b8;\">Puedes ver la respuesta completa en tu perfil.</p>
                        ";

                        $html = $this->mailService->generarPlantilla(
                            $ratingInfo['valoradorNombre'],
                            "Respuesta a tu valoracion",
                            $contenido,
                            null,
                            'http://localhost/Ride4Study/profile',
                            'Ver Mi Perfil'
                        );
                        $this->mailService->send($ratingInfo['correo'], $ratingInfo['valoradorNombre'], 'Respuesta a tu valoracion - Ride4Study', $html);
                    }
                }
            } catch (Exception $e) {
                error_log("Error notificación respuesta valoración: " . $e->getMessage());
            }

            echo json_encode(['success' => true, 'message' => '¡Respuesta publicada!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo publicar. Puede que ya hayas respondido.']);
        }
        exit;
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
