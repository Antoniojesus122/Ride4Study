<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/Conversation.php';
require_once __DIR__ . '/../models/User.php';

class MessageController {
    private $db;
    private $message;
    private $conversation;
    private $user;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db           = $database->connect();
        $this->message      = new Message($this->db);
        $this->conversation = new Conversation($this->db);
        $this->user         = new User($this->db);
    }

    // Buscar o crear conversación
    private function getOrCreateConversation(int $idAnuncio, int $currentUserId, int $otherUserId): int {
        return $this->conversation->getOrCreate($idAnuncio, $currentUserId, $otherUserId);
    }

    // Listado de conversaciones del usuario
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
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
            header('Location: login.php');
            exit;
        }

        $userId = (int) $_SESSION['user_id'];

        // // Desde contactar mediante un anuncio
        if (isset($_GET['anuncio_id']) && isset($_GET['other_user_id'])) {
            $idAnuncio   = (int) $_GET['anuncio_id'];
            $otherUserId = (int) $_GET['other_user_id'];

            // Validaciones básicas
            if ($idAnuncio <= 0 || $otherUserId <= 0 || $otherUserId === $userId) {
                header('Location: messages.php');
                exit;
            }

            $conversationId = $this->getOrCreateConversation($idAnuncio, $userId, $otherUserId);
            header('Location: chat.php?conversation_id=' . $conversationId);
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
            header('Location: messages.php');
            exit;
        }

        // Determinar el otro usuario
        $otherUserId = ((int) $contextRide['user1_id'] === $userId)
            ? (int) $contextRide['user2_id']
            : (int) $contextRide['user1_id'];

        $otherUser = $this->user->getUserById($otherUserId);
        $messages  = $this->message->getMessages($conversationId, $userId);
        $chats     = $this->message->getConversations($userId);

        $selectedConversationId = $conversationId;
        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        require_once __DIR__ . '/../../views/user/chat.view.php';
    }

    // Carga de mensajes vía AJAX
    public function fetchMessages() {
        if (!isset($_SESSION['user_id'])) {
            exit;
        }

        $userId         = (int) $_SESSION['user_id'];
        $conversationId = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : null;

        if (!$conversationId) exit;

        // Verificar pertenencia
        if (!$this->conversation->belongsToUser($conversationId, $userId)) {
            exit;
        }

        $messages = $this->message->getMessages($conversationId, $userId);

        require_once __DIR__ . '/../../views/user/chat-messages.partial.php';
    }

    // Enviar mensaje
    public function send() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: login.php');
            exit;
        }

        $userId         = (int) $_SESSION['user_id'];
        $conversationId = isset($_POST['conversation_id']) ? (int) $_POST['conversation_id'] : 0;
        $receiverId     = isset($_POST['receiver_id'])     ? (int) $_POST['receiver_id']     : 0;
        $mensaje        = $_POST['message'] ?? '';

        // Validaciones
        if ($conversationId <= 0 || $receiverId <= 0 || trim($mensaje) === '') {
            header('Location: messages.php');
            exit;
        }

        // Verificar que el usuario pertenece a la conversación
        if (!$this->conversation->belongsToUser($conversationId, $userId)) {
            header('Location: messages.php');
            exit;
        }

        $data = [
            'idConversation' => $conversationId,
            'idEmisor'       => $userId,
            'idReceptor'     => $receiverId,
            'mensaje'        => $mensaje,
        ];

        $this->message->createMessage($data);

        header('Location: chat.php?conversation_id=' . $conversationId);
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
            header('Location: messages.php');
            exit;
        }

        // Verificar pertenencia antes de eliminar
        if (!$this->conversation->belongsToUser($conversationId, $userId)) {
            header('Location: messages.php');
            exit;
        }

        $this->message->deleteConversationMessages($conversationId);

        // Eliminar también la conversación vacía
        $query = "DELETE FROM conversations WHERE idConversation = :id";
        $stmt  = $this->db->prepare($query);
        $stmt->bindParam(':id', $conversationId, PDO::PARAM_INT);
        $stmt->execute();

        header('Location: messages.php');
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
            //Enviar notificación por email al publicador
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
