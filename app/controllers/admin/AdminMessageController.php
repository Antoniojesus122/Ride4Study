<?php
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../models/AdminLog.php';

class AdminMessageController {
    private PDO $db;
    private AdminLog $adminLog;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $database = new Database();
        $this->db = $database->connect();
        $this->adminLog = new AdminLog($this->db);
    }

    private function requireAdmin(): void {
        if (!isset($_SESSION['user_id']) || (int)($_SESSION['user_role'] ?? 0) !== 1) {
            header('Location: ' . url('/login'));
            exit;
        }
    }

    public function index(): void {
        $this->requireAdmin();

        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Obtener conversaciones con info de usuarios, total mensajes, último mensaje y fecha del último mensaje
        $query = "SELECT c.*,
                    u1.nombre as user1_nombre, u1.correo as user1_correo,
                    u2.nombre as user2_nombre, u2.correo as user2_correo,
                    (SELECT COUNT(*) FROM mensajes WHERE idConversation = c.idConversation) as total_mensajes,
                    (SELECT mensaje FROM mensajes WHERE idConversation = c.idConversation ORDER BY fechaCreacion DESC LIMIT 1) as ultimo_mensaje,
                    (SELECT fechaCreacion FROM mensajes WHERE idConversation = c.idConversation ORDER BY fechaCreacion DESC LIMIT 1) as ultima_fecha
                  FROM conversations c
                  JOIN usuarios u1 ON c.user1_id = u1.idUsuario
                  JOIN usuarios u2 ON c.user2_id = u2.idUsuario
                  WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $query .= " AND (u1.nombre LIKE :s1 OR u2.nombre LIKE :s2)";
            $params[':s1'] = '%' . $search . '%';
            $params[':s2'] = '%' . $search . '%';
        }

        $query .= " ORDER BY ultima_fecha DESC";

        // Contar total de conversaciones para paginación
        $countQuery = "SELECT COUNT(*) FROM conversations c
                       JOIN usuarios u1 ON c.user1_id = u1.idUsuario
                       JOIN usuarios u2 ON c.user2_id = u2.idUsuario
                       WHERE 1=1";
        $countParams = [];

        if ($search !== '') {
            $countQuery .= " AND (u1.nombre LIKE :s1 OR u2.nombre LIKE :s2)";
            $countParams[':s1'] = '%' . $search . '%';
            $countParams[':s2'] = '%' . $search . '%';
        }

        $countStmt = $this->db->prepare($countQuery);
        $countStmt->execute($countParams);
        $totalConversations = (int)$countStmt->fetchColumn();
        $totalPages = max(1, ceil($totalConversations / $limit));

        // Paginación
        $query .= " LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener IDs de conversaciones reportadas para marcar en la vista
        $reportedIds = [];
        $repStmt = $this->db->query("SELECT DISTINCT idEntidad FROM reportes WHERE tipo = 'chat' AND estado != 'resuelto'");
        if ($repStmt) {
            $reportedIds = $repStmt->fetchAll(PDO::FETCH_COLUMN);
        }

        $flashData = getFlash();

        require_once __DIR__ . '/../../../views/admin/messages.view.php';
    }

    public function viewConversation(): void {
        $this->requireAdmin();

        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de conversacion invalido']);
            return;
        }

        // Recoger todos los mensajes
        $msgStmt = $this->db->prepare(
            "SELECT m.*, u.nombre as emisor_nombre
             FROM mensajes m
             JOIN usuarios u ON m.idEmisor = u.idUsuario
             WHERE m.idConversation = :id
             ORDER BY m.fechaCreacion ASC"
        );
        $msgStmt->bindValue(':id', $id, PDO::PARAM_INT);
        $msgStmt->execute();
        $messages = $msgStmt->fetchAll(PDO::FETCH_ASSOC);

        // Recoger info de la conversación y usuarios involucrados
        $convStmt = $this->db->prepare(
            "SELECT c.*, u1.nombre as user1_nombre, u2.nombre as user2_nombre
             FROM conversations c
             JOIN usuarios u1 ON c.user1_id = u1.idUsuario
             JOIN usuarios u2 ON c.user2_id = u2.idUsuario
             WHERE c.idConversation = :id"
        );
        $convStmt->bindValue(':id', $id, PDO::PARAM_INT);
        $convStmt->execute();
        $conversation = $convStmt->fetch(PDO::FETCH_ASSOC);

        header('Content-Type: application/json');
        echo json_encode([
            'conversation' => $conversation,
            'messages' => $messages
        ]);
    }

    public function deleteMessage(): void {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Metodo no permitido']);
            return;
        }

        $messageId = (int)($_POST['message_id'] ?? 0);
        if ($messageId <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de mensaje invalido']);
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM mensajes WHERE idMensaje = :id");
        $stmt->bindValue(':id', $messageId, PDO::PARAM_INT);
        $success = $stmt->execute();

        if ($success) {
            $this->adminLog->log(
                (int)$_SESSION['user_id'],
                'eliminar',
                'mensaje',
                $messageId,
                'Mensaje eliminado por admin'
            );
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }

    public function deleteConversation(): void {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/admin/messages'));
            exit;
        }

        $conversationId = (int)($_POST['conversation_id'] ?? 0);
        if ($conversationId <= 0) {
            redirectWithFlash(url('/admin/messages'), 'error', 'invalid_id');
            return;
        }

        // Eliminar mensajes de la conversación
        $msgStmt = $this->db->prepare("DELETE FROM mensajes WHERE idConversation = :id");
        $msgStmt->bindValue(':id', $conversationId, PDO::PARAM_INT);
        $msgStmt->execute();

        $convStmt = $this->db->prepare("DELETE FROM conversations WHERE idConversation = :id");
        $convStmt->bindValue(':id', $conversationId, PDO::PARAM_INT);
        $convStmt->execute();

        $this->adminLog->log(
            (int)$_SESSION['user_id'],
            'eliminar',
            'conversacion',
            $conversationId,
            'Conversacion y sus mensajes eliminados por admin'
        );

        redirectWithFlash(url('/admin/messages'), 'success', 'conversation_deleted');
    }
}
