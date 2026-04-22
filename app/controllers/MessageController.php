<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../../services/MailService.php';

class MessageController {
    private $db;
    private $message;
    private $conversation;
    private $user;
    private Notification $notification;
    private ?MailService $mailService = null;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db           = $database->connect();
        $this->message      = new Message($this->db);
        $this->conversation = new Conversation($this->db);
        $this->user         = new User($this->db);
        $this->notification = new Notification($this->db);
        try { $this->mailService = new MailService(); } catch (Exception $e) { error_log('MailService: ' . $e->getMessage()); }
    }

    // Buscar o crear conversación
    private function getOrCreateConversation(int $idAnuncio, int $currentUserId, int $otherUserId): int {
        return $this->conversation->getOrCreate($idAnuncio, $currentUserId, $otherUserId);
    }

    // Listado de conversaciones del usuario
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $chats  = $this->message->getConversations($userId);

        $selectedConversationId = null;
        $messages               = [];
        $otherUser              = null;
        $contextRide            = null;

        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        require_once __DIR__ . '/../../views/user/chat.view.php';
    }

    // Punto de entrada al chat
    public function chat() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $userId = (int) $_SESSION['user_id'];

        // Desde contactar mediante un anuncio
        if (isset($_GET['anuncio_id']) && isset($_GET['other_user_id'])) {
            $idAnuncio   = (int) $_GET['anuncio_id'];
            $otherUserId = (int) $_GET['other_user_id'];

            // Validaciones básicas
            if ($idAnuncio <= 0 || $otherUserId <= 0 || $otherUserId === $userId) {
                redirectWithFlash(url('/messages'), 'error', 'invalid_params');
            }

            // Verificar que el anuncio existe y que other_user_id es su dueño
            $stmt = $this->db->prepare("SELECT idUsuario FROM anuncios WHERE idAnuncio = :id LIMIT 1");
            $stmt->execute([':id' => $idAnuncio]);
            $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$anuncio) {
                redirectWithFlash(url('/messages'), 'error', 'ride_not_found');
            }

            if ((int)$anuncio['idUsuario'] !== $otherUserId) {
                redirectWithFlash(url('/messages'), 'error', 'unauthorized');
            }

            $conversationId = $this->getOrCreateConversation($idAnuncio, $userId, $otherUserId);
            header('Location: ' . url('/chat') . '?conversation_id=' . $conversationId);
            exit;
        }

        // Desde contactar mediante el perfil
        $conversationId = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : null;

        if (!$conversationId) {
            $this->index();
            return;
        }

        // Verificar que el usuario pertenece a esta conversación
        $contextRide = $this->conversation->getByIdForUser($conversationId, $userId);
        if (!$contextRide) {
            header('Location: ' . url('/messages'));
            exit;
        }

        // Determinar el otro usuario
        $otherUserId = ((int) $contextRide['user1_id'] === $userId)
            ? (int) $contextRide['user2_id']
            : (int) $contextRide['user1_id'];

        $otherUser     = $this->user->getUserById($otherUserId);
        $totalMessages = $this->message->countMessages($conversationId);
        $messages      = $this->message->getMessagesPaginated($conversationId, $userId, 30, 0);
        $hasMore       = $totalMessages > 30;
        $chats         = $this->message->getConversations($userId);

        $selectedConversationId = $conversationId;
        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        require_once __DIR__ . '/../../views/user/chat.view.php';
    }

    // Carga de mensajes vía AJAX con paginación
    public function fetchMessages() {
        if (!isset($_SESSION['user_id'])) {
            exit;
        }

        $userId         = (int) $_SESSION['user_id'];
        $conversationId = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : null;
        $offset         = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;
        $afterId        = isset($_GET['after']) ? (int) $_GET['after'] : 0;
        $limit          = 30;

        if (!$conversationId) exit;

        // Verificar pertenencia
        if (!$this->conversation->belongsToUser($conversationId, $userId)) {
            exit;
        }

        // Polling: obtener solo mensajes nuevos (después de un ID)
        if ($afterId > 0) {
            $messages = $this->message->getMessagesAfter($conversationId, $userId, $afterId);
            // Filtrar mensajes del propio usuario (ya los añadimos via optimistic update)
            $messages = array_filter($messages, fn($m) => (int)$m['idEmisor'] !== $userId);
            $messages = array_values($messages);
            if (!empty($messages)) {
                require __DIR__ . '/../../views/user/chat-messages.partial.php';
            }
            exit;
        }

        $totalMessages = $this->message->countMessages($conversationId);
        $messages      = $this->message->getMessagesPaginated($conversationId, $userId, $limit, $offset);
        $hasMore       = ($offset + $limit) < $totalMessages;

        // Si se piden mensajes anteriores, devolver JSON
        if ($offset > 0) {
            header('Content-Type: application/json');
            ob_start();
            require __DIR__ . '/../../views/user/chat-messages.partial.php';
            $html = ob_get_clean();
            echo json_encode([
                'html'    => $html,
                'hasMore' => $hasMore,
                'total'   => $totalMessages
            ]);
            exit;
        }

        require_once __DIR__ . '/../../views/user/chat-messages.partial.php';
    }

    // Enviar mensaje
    public function send() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/login'));
            exit;
        }

        $userId         = (int) $_SESSION['user_id'];
        $conversationId = isset($_POST['conversation_id']) ? (int) $_POST['conversation_id'] : 0;
        $mensaje        = $_POST['message'] ?? '';

        // Validaciones
        if ($conversationId <= 0 || trim($mensaje) === '') {
            header('Location: ' . url('/messages'));
            exit;
        }

        // Cargar la conversación y derivar el receptor server-side
        $conv = $this->conversation->getByIdForUser($conversationId, $userId);
        if (!$conv) {
            header('Location: ' . url('/messages'));
            exit;
        }
        $user1 = (int) $conv['user1_id'];
        $user2 = (int) $conv['user2_id'];
        $receiverId = ($userId === $user1) ? $user2 : $user1;
        if ($receiverId <= 0 || $receiverId === $userId) {
            header('Location: ' . url('/messages'));
            exit;
        }

        $data = [
            'idConversation' => $conversationId,
            'idEmisor'       => $userId,
            'idReceptor'     => $receiverId,
            'mensaje'        => $mensaje,
        ];

        $msgId = $this->message->createMessage($data);

        // Notificación in-app al receptor
        $senderName = $_SESSION['user_name'] ?? 'Un usuario';
        $this->notification->create(
            $receiverId,
            htmlspecialchars($senderName) . ' ' . t('chat.notif_new_message'),
            'fas fa-comment',
            url('/chat') . '?conversation_id=' . $conversationId
        );

        // Notificación instantánea por email al receptor
        if ($this->mailService) {
            $receiver = $this->user->getUserById($receiverId);
            if ($receiver && !empty($receiver['notificaciones_email'])) {
                $senderName = $_SESSION['user_name'] ?? 'Un usuario';
                $html = $this->mailService->generarPlantilla(
                    $receiver['nombre'],
                    'Nuevo mensaje de ' . htmlspecialchars($senderName),
                    'Hola <strong>' . htmlspecialchars($receiver['nombre']) . '</strong>,<br><br>
                    <strong>' . htmlspecialchars($senderName) . '</strong> te ha enviado un nuevo mensaje en Ride4Study.<br><br>
                    Accede a tu bandeja de entrada para leerlo y responder.',
                    null,
                    fullUrl('/chat') . '?conversation_id=' . $conversationId,
                    'Ver mensaje'
                );
                $this->mailService->send($receiver['correo'], $receiver['nombre'], 'Nuevo mensaje · Ride4Study', $html);
            }
        }

        // Respuesta AJAX 
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => [
                    'idMensaje'     => $msgId,
                    'idEmisor'      => $userId,
                    'mensaje'       => htmlspecialchars($mensaje),
                    'fechaCreacion' => date('Y-m-d H:i:s'),
                    'leido'         => 0,
                ]
            ]);
            exit;
        }

        header('Location: ' . url('/chat') . '?conversation_id=' . $conversationId);
        exit;
    }

    // Eliminar mensaje
    public function delete() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $msgId  = isset($_POST['message_id']) ? (int) $_POST['message_id'] : 0;

        if ($msgId > 0) {
            $this->message->deleteMessage($msgId, $userId);
        }

        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            echo json_encode(['success' => true]);
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }

    // Editar mensaje
    public function edit() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }

        $userId  = (int) $_SESSION['user_id'];
        $msgId   = (int) ($_POST['message_id'] ?? 0);
        $newText = $_POST['message'] ?? '';

        $result = $this->message->updateMessage($msgId, $userId, $newText);

        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
        ) {
            if ($result === 'expired') {
                echo json_encode(['success' => false, 'error' => 'Time limit exceeded']);
            } else {
                echo json_encode(['success' => (bool) $result]);
            }
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }

    // Eliminar conversación completa
    public function deleteConversation() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }

        $userId         = (int) $_SESSION['user_id'];
        $conversationId = isset($_POST['conversation_id']) ? (int) $_POST['conversation_id'] : 0;

        if ($conversationId <= 0) {
            header('Location: ' . url('/messages'));
            exit;
        }

        // Verificar pertenencia antes de eliminar
        if (!$this->conversation->belongsToUser($conversationId, $userId)) {
            header('Location: ' . url('/messages'));
            exit;
        }

        $this->message->deleteConversationMessages($conversationId);

        // Eliminar también la conversación vacía
        $query = "DELETE FROM conversations WHERE idConversation = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':id', $conversationId, PDO::PARAM_INT);
        $stmt->execute();

        header('Location: ' . url('/messages'));
        exit;
    }

    // Función para ofrecer llevar a alguien que ha publicado un anuncio "busco"
    public function offerRide() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $userId = (int) $_SESSION['user_id'];
        $anuncioId = isset($_POST['anuncio_id']) ? (int) $_POST['anuncio_id'] : 0;

        if ($anuncioId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de anuncio inválido']);
            exit;
        }

        // Verificar que el anuncio existe y es tipo "busco"
        $checkQuery = "SELECT idUsuario, tipo FROM anuncios WHERE idAnuncio = :anuncioId";
        $stmt = $this->db->prepare($checkQuery);
        $stmt->execute([':anuncioId' => $anuncioId]);
        $anuncio = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$anuncio) {
            echo json_encode(['success' => false, 'message' => 'Anuncio no encontrado']);
            exit;
        }

        if (strtolower($anuncio['tipo']) !== 'busco') {
            echo json_encode(['success' => false, 'message' => 'Este anuncio no es de tipo "busco"']);
            exit;
        }

        // Verificar que no es el propietario del anuncio
        if ($anuncio['idUsuario'] == $userId) {
            echo json_encode(['success' => false, 'message' => 'No puedes ofrecer llevarte a ti mismo']);
            exit;
        }

        // Verificar que no haya ofrecido ya
        $existingQuery = "SELECT idViaje FROM viajes 
                         WHERE idAnuncio = :anuncioId 
                         AND idConductor = :userId";
        $stmt = $this->db->prepare($existingQuery);
        $stmt->execute([
            ':anuncioId' => $anuncioId,
            ':userId' => $userId
        ]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Ya has ofrecido llevarlo en este viaje']);
            exit;
        }

        // Crear la oferta (viaje con estado pendiente)
        // En anuncios "busco": quien ofrece es conductor, quien publicó es pasajero
        $insertQuery = "INSERT INTO viajes (idAnuncio, idConductor, idPasajero, estado)
                       VALUES (:anuncioId, :conductorId, :pasajeroId, 'pendiente')";
        
        $stmt = $this->db->prepare($insertQuery);
        $result = $stmt->execute([
            ':anuncioId' => $anuncioId,
            ':conductorId' => $userId,           // Quien ofrece
            ':pasajeroId' => $anuncio['idUsuario'] // Quien publicó
        ]);

        if ($result) {
            // Notificación dentro del proyecto al publicador del anuncio "busco"
            try {
                $stmt = $this->db->prepare("SELECT nombre FROM usuarios WHERE idUsuario = :id");
                $stmt->execute([':id' => $userId]);
                $driver = $stmt->fetch(PDO::FETCH_ASSOC);
                $driverName = $driver['nombre'] ?? 'Un conductor';

                $rideStmt = $this->db->prepare(
                    "SELECT lo.nombreLocalidad AS nombreOrigen, ld.nombreLocalidad AS nombreDestino
                     FROM anuncios a
                     JOIN localidades lo ON a.origen = lo.idLocalidad
                     JOIN localidades ld ON a.destino = ld.idLocalidad
                     WHERE a.idAnuncio = :id"
                );
                $rideStmt->execute([':id' => $anuncioId]);
                $rideInfo = $rideStmt->fetch(PDO::FETCH_ASSOC);
                $origen  = $rideInfo['nombreOrigen']  ?? 'origen';
                $destino = $rideInfo['nombreDestino'] ?? 'destino';

                $this->notification->create(
                    (int)$anuncio['idUsuario'],
                    htmlspecialchars($driverName) . ' ' . t('notif.ride_offered') . ' ' . htmlspecialchars($origen) . ' → ' . htmlspecialchars($destino) . '.',
                    'fas fa-car',
                    url('/my-rides') . '?tab=requests'
                );
            } catch (Exception $e) {
                error_log("Error enviando notificación in-app: " . $e->getMessage());
            }

            echo json_encode([
                'success' => true,
                'message' => 'Oferta enviada con éxito. El usuario recibirá una notificación.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear la oferta']);
        }
        exit;
    }
}
