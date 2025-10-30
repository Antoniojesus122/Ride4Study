<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/helpers.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['idUsuario'])) {
    header('Location: /public/login.php');
    exit;
}

$idUsuario = $_SESSION['idUsuario'];

// Obtener lista de conversaciones
$query = "SELECT DISTINCT 
          u.idUsuario, u.nombre, u.apellidos, u.email, u.foto_perfil,
          (SELECT m.mensaje 
           FROM messages m 
           WHERE (m.idEmisor = u.idUsuario AND m.idReceptor = :idUsuario)
           OR (m.idEmisor = :idUsuario AND m.idReceptor = u.idUsuario)
           ORDER BY m.fechaCreacion DESC 
           LIMIT 1) as last_message,
          (SELECT m.fechaCreacion 
           FROM messages m 
           WHERE (m.idEmisor = u.idUsuario AND m.idReceptor = :idUsuario)
           OR (m.idEmisor = :idUsuario AND m.idReceptor = u.idUsuario)
           ORDER BY m.fechaCreacion DESC 
           LIMIT 1) as last_message_time,
          (SELECT COUNT(*) 
           FROM messages m 
           WHERE m.idEmisor = u.idUsuario 
           AND m.idReceptor = :idUsuario 
           AND m.leido = 0) as unread_count
          FROM usuarios u
          INNER JOIN messages m ON (m.idEmisor = u.idUsuario AND m.idReceptor = :idUsuario)
          OR (m.idEmisor = :idUsuario AND m.idReceptor = u.idUsuario)
          WHERE u.idUsuario != :idUsuario
          GROUP BY u.idUsuario
          ORDER BY last_message_time DESC";

$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$conversations = $stmt->fetchAll();

// Obtener el ID del chat activo (si existe)
$active_chat = isset($_GET['chat']) ? (int)$_GET['chat'] : null;

// Si hay un chat activo, obtener los mensajes
$messages = [];
if ($active_chat) {
    $query = "SELECT m.*, 
              CASE WHEN m.sender_id = :user_id THEN 1 ELSE 0 END as is_sender
              FROM messages m
              WHERE (m.sender_id = :user_id AND m.receiver_id = :active_chat)
              OR (m.sender_id = :active_chat AND m.receiver_id = :user_id)
              ORDER BY m.created_at ASC";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':user_id' => $user_id,
        ':active_chat' => $active_chat
    ]);
    $messages = $stmt->fetchAll();
    
    // Marcar mensajes como leídos
    $updateQuery = "UPDATE messages 
                   SET is_read = 1 
                   WHERE receiver_id = :user_id 
                   AND sender_id = :active_chat 
                   AND is_read = 0";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute([
        ':user_id' => $user_id,
        ':active_chat' => $active_chat
    ]);
}

// Incluir el header
include '../layouts/header.php';
?>

<div class="min-h-screen bg-gray-100">
    <div class="container mx-auto py-8">
        <div class="flex bg-white rounded-lg shadow-lg h-[calc(100vh-12rem)]">
            <!-- Lista de conversaciones -->
            <div class="w-1/3 border-r">
                <div class="p-4 border-b">
                    <h2 class="text-xl font-semibold text-gray-800">Mensajes</h2>
                </div>
                <div class="overflow-y-auto h-[calc(100%-4rem)]">
                    <?php foreach ($conversations as $conv): ?>
                        <a href="?chat=<?php echo $conv['id']; ?>" 
                           class="flex items-center p-4 hover:bg-gray-50 border-b transition-colors <?php echo ($active_chat == $conv['id']) ? 'bg-blue-50' : ''; ?>">
                            <div class="relative">
                                <img src="<?php echo !empty($conv['foto_perfil']) ? $conv['foto_perfil'] : '/assets/img/default-avatar.png'; ?>" 
                                     alt="<?php echo htmlspecialchars($conv['nombre']); ?>" 
                                     class="w-12 h-12 rounded-full object-cover">
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                        <?php echo $conv['unread_count']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="font-semibold text-gray-800">
                                    <?php echo htmlspecialchars($conv['nombre'] . ' ' . $conv['apellidos']); ?>
                                </h3>
                                <p class="text-sm text-gray-600 truncate">
                                    <?php echo htmlspecialchars($conv['last_message']); ?>
                                </p>
                            </div>
                            <span class="text-xs text-gray-500">
                                <?php echo format_time_ago(strtotime($conv['last_message_time'])); ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Área de chat -->
            <div class="flex-1 flex flex-col">
                <?php if ($active_chat): ?>
                    <?php
                    // Obtener información del usuario del chat activo
                    $stmt = $pdo->prepare("SELECT nombre, apellidos, foto_perfil FROM users WHERE id = ?");
                    $stmt->execute([$active_chat]);
                    $chat_user = $stmt->fetch();
                    ?>
                    <div class="p-4 border-b flex items-center">
                        <img src="<?php echo !empty($chat_user['foto_perfil']) ? $chat_user['foto_perfil'] : '/assets/img/default-avatar.png'; ?>" 
                             alt="<?php echo htmlspecialchars($chat_user['nombre']); ?>" 
                             class="w-10 h-10 rounded-full object-cover">
                        <h2 class="ml-4 text-lg font-semibold text-gray-800">
                            <?php echo htmlspecialchars($chat_user['nombre'] . ' ' . $chat_user['apellidos']); ?>
                        </h2>
                    </div>
                    
                    <!-- Mensajes -->
                    <div class="flex-1 overflow-y-auto p-4 message-container" data-conversation-id="<?php echo $active_chat; ?>">
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?php echo $message['is_sender'] ? 'message-sent' : 'message-received'; ?>">
                                <div class="message-content">
                                    <p class="message-text"><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
                                    <span class="message-time">
                                        <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Formulario de mensaje -->
                    <form id="messageForm" class="border-t p-4" data-receiver-id="<?php echo $active_chat; ?>">
                        <div class="flex gap-4">
                            <input type="text" 
                                   id="messageInput"
                                   class="flex-1 rounded-full border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition-colors"
                                   placeholder="Escribe un mensaje...">
                            <button type="submit" 
                                    class="bg-blue-500 text-white px-6 py-2 rounded-full hover:bg-blue-600 transition-colors">
                                Enviar
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="flex-1 flex items-center justify-center text-gray-500">
                        <p>Selecciona una conversación para comenzar</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.message {
    display: flex;
    margin-bottom: 1rem;
}

.message-sent {
    justify-content: flex-end;
}

.message-content {
    max-width: 70%;
    padding: 0.75rem 1rem;
    border-radius: 1rem;
    position: relative;
}

.message-sent .message-content {
    background-color: #3b82f6;
    color: white;
    border-bottom-right-radius: 0.25rem;
}

.message-received .message-content {
    background-color: #f3f4f6;
    color: #1f2937;
    border-bottom-left-radius: 0.25rem;
}

.message-text {
    margin-bottom: 0.25rem;
    line-height: 1.4;
}

.message-time {
    font-size: 0.75rem;
    opacity: 0.8;
    display: block;
    text-align: right;
}

.message-sent .message-time {
    color: rgba(255, 255, 255, 0.9);
}

.message-received .message-time {
    color: #6b7280;
}
</style>

<script src="/assets/js/chat.js"></script>

<?php include '../layouts/footer.php'; ?>
