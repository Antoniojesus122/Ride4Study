<?php
class Message {
    private $conn;
    private $table = 'mensajes';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener lista de conversaciones (último mensaje por usuario)
    public function getConversations($userId) {
        // Esta consulta busca el último mensaje con cada usuario distinto
        // Es una consulta compleja: Union de enviados y recibidos, agrupados.
        
        $query = "
        SELECT 
            u.idUsuario, 
            u.nombre, 
            u.foto_perfil,
            m.mensaje,
            m.fechaCreacion,
            m.leido,
            m.idEmisor
        FROM usuarios u
        JOIN (
            SELECT 
                CASE 
                    WHEN idEmisor = :userId THEN idReceptor 
                    ELSE idEmisor 
                END as other_user_id,
                MAX(idMensaje) as max_id
            FROM mensajes 
            WHERE idEmisor = :userId OR idReceptor = :userId
            GROUP BY other_user_id
        ) latest ON u.idUsuario = latest.other_user_id
        JOIN mensajes m ON m.idMensaje = latest.max_id
        ORDER BY m.fechaCreacion DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener chat completo entre dos usuarios
    public function getMessages($userId, $otherUserId) {
        $query = "SELECT m.*, u.nombre as nombreEmisor 
                  FROM " . $this->table . " m
                  JOIN usuarios u ON m.idEmisor = u.idUsuario
                  WHERE (idEmisor = :userId AND idReceptor = :otherUserId)
                     OR (idEmisor = :otherUserId AND idReceptor = :userId)
                  ORDER BY fechaCreacion ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':otherUserId', $otherUserId);
        $stmt->execute();
        
        // Marcar como leídos si soy el receptor
        $this->markAsRead($userId, $otherUserId);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($userId, $senderId) {
        $query = "UPDATE " . $this->table . " 
                  SET leido = 1 
                  WHERE idReceptor = :userId AND idEmisor = :senderId AND leido = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':senderId', $senderId);
        $stmt->execute();
    }

    public function createMessage($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (idEmisor, idReceptor, mensaje) 
                  VALUES (:idEmisor, :idReceptor, :mensaje)";
        
        $stmt = $this->conn->prepare($query);

        $data['mensaje'] = htmlspecialchars(strip_tags($data['mensaje']));

        $stmt->bindParam(':idEmisor', $data['idEmisor']);
        $stmt->bindParam(':idReceptor', $data['idReceptor']);
        $stmt->bindParam(':mensaje', $data['mensaje']);

        return $stmt->execute();
    }

    // Editar mensaje (Solo si < 1 hora)
    public function updateMessage($idMensaje, $userId, $newText) {
        // Verificar tiempo y propiedad
        $checkQuery = "SELECT fechaCreacion FROM " . $this->table . " 
                       WHERE idMensaje = :idMensaje AND idEmisor = :userId";
        $stmtCheck = $this->conn->prepare($checkQuery);
        $stmtCheck->bindParam(':idMensaje', $idMensaje);
        $stmtCheck->bindParam(':userId', $userId);
        $stmtCheck->execute();
        
        if ($stmtCheck->rowCount() == 0) return false; // No existe o no es tuyo

        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        $msgTime = strtotime($row['fechaCreacion']);
        $now = time();

        if (($now - $msgTime) > 3600) {
            return 'expired'; // Más de 1 hora
        }

        // Actualizar
        $query = "UPDATE " . $this->table . " SET mensaje = :mensaje WHERE idMensaje = :idMensaje";
        $stmt = $this->conn->prepare($query);
        
        $newText = htmlspecialchars(strip_tags($newText));
        
        $stmt->bindParam(':mensaje', $newText);
        $stmt->bindParam(':idMensaje', $idMensaje);

        return $stmt->execute();
    }

    public function deleteMessage($idMensaje, $userId) {
        // Solo permitir borrar si eres el emisor (o receptor, dependiendo de requisitos, asumo emisor para "eliminar sus mensajes")
        // El usuario pidió "eliminar sus mensajes... para todos".
        // Si borro de la BDD, se borra para ambos.
        
        $query = "DELETE FROM " . $this->table . " WHERE idMensaje = :idMensaje AND idEmisor = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idMensaje', $idMensaje);
        $stmt->bindParam(':userId', $userId);
        
        return $stmt->execute();
    }

    public function deleteConversation($userId, $otherUserId) {
        // Eliminar todos los mensajes entre dos usuarios
        $query = "DELETE FROM " . $this->table . " 
                  WHERE (idEmisor = :userId AND idReceptor = :otherUserId)
                     OR (idEmisor = :otherUserId AND idReceptor = :userId)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':otherUserId', $otherUserId);
        
        return $stmt->execute();
    }
}