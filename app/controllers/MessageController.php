<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Message.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ride.php';

class MessageController {
    private $db;
    private $message;
    private $user;
    private $ride;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db = $database->connect();
        $this->message = new Message($this->db);
        $this->user = new User($this->db);
        $this->ride = new Ride($this->db);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $chats = $this->message->getConversations($userId);
        
        $selectedUserId = null;
        $messages = [];
        $otherUser = null;
        $contextRide = null;

        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        // Reuse the same view for both list and specific chat
        require_once __DIR__ . '/../../views/user/chat.view.php';
    }

    public function fetchMessages() {
        if (!isset($_SESSION['user_id'])) {
            exit;
        }

        $userId = $_SESSION['user_id'];
        $otherUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

        if (!$otherUserId) exit;

        $messages = $this->message->getMessages($userId, $otherUserId);
        
        // Render only the partial
        require_once __DIR__ . '/../../views/user/chat-messages.partial.php';
    }

    public function chat() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $chats = $this->message->getConversations($userId);

        $otherUserId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

        if (!$otherUserId) {
             $this->index(); // Fallback to index if no user selected
             return;
        }

        $selectedUserId = $otherUserId;
        $messages = $this->message->getMessages($userId, $otherUserId);
        $otherUser = $this->user->getUserById($otherUserId); 

        // Context (Ride)
        $contextRide = null;
        if (isset($_GET['ride_id'])) {
            $rideId = (int)$_GET['ride_id'];
            $contextRide = $this->ride->getRideById($rideId);
        }

        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        require_once __DIR__ . '/../../views/user/chat.view.php';
    }

    public function send() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             header('Location: login.php');
             exit;
        }

        $data = [
            'idEmisor' => $_SESSION['user_id'],
            'idReceptor' => $_POST['receiver_id'],
            'mensaje' => $_POST['message']
        ];

        if (!empty($data['mensaje'])) {
            $this->message->createMessage($data);
        }

        $redirect = 'chat.php?user_id=' . $data['idReceptor'];
        if(isset($_POST['ride_id'])) {
            $redirect .= '&ride_id=' . $_POST['ride_id'];
        }
        
        header('Location: ' . $redirect);
    }

    public function delete() {
         if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             exit;
        }
        
        $msgId = $_POST['message_id'] ?? null;
        if ($msgId) {
            $this->message->deleteMessage($msgId, $_SESSION['user_id']);
        }
        
        // Return JSON for AJAX or redirect
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
             echo json_encode(['success' => true]);
        } else {
             header('Location: ' . $_SERVER['HTTP_REFERER']);
        }
    }

    public function edit() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
       }

       $msgId = $_POST['message_id'];
       $newText = $_POST['message'];
       
       $result = $this->message->updateMessage($msgId, $_SESSION['user_id'], $newText);
       
       if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            if ($result === 'expired') {
                 echo json_encode(['success' => false, 'error' => 'Time limit exceeded']);
            } else {
                 echo json_encode(['success' => $result]);
            }
       } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
       }
    }
    
    public function deleteConversation() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
             exit;
        }

        $otherUserId = $_POST['user_id'];
        $this->message->deleteConversation($_SESSION['user_id'], $otherUserId);
        
        header('Location: messages.php');
    }
}
