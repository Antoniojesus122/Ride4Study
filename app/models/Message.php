<?php
class Message {
    private $conn;
    private $table = 'mensajes';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener lista de conversaciones (último mensaje por usuario)
    public function getConversations($userId) {
        $query = "
        SELECT 
            u.idUsuario, 
            u.nombre, 
            u.foto_perfil,
            m.mensaje,
            m.fechaCreacion,
            m.leido,
            m.idEmisor,
            m.tipo
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


    public function createContextMessage($data) {
        // Primero verificar que no exista ya un mensaje de contexto
        $checkQuery = "SELECT COUNT(*) as count FROM " . $this->table . " 
                       WHERE ((idEmisor = :userId AND idReceptor = :otherUserId) 
                          OR (idEmisor = :otherUserId AND idReceptor = :userId))
                       AND tipo = 'sistema'
                       AND ride_id = :rideId";
        
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bindParam(':userId', $data['idEmisor']);
        $stmt->bindParam(':otherUserId', $data['idReceptor']);
        $stmt->bindParam(':rideId', $data['ride_id']);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si ya existe, no crear otro
        if ($result['count'] > 0) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " 
                  (idEmisor, idReceptor, mensaje, tipo, ride_id, leido) 
                  VALUES (:idEmisor, :idReceptor, :mensaje, :tipo, :rideId, 1)";
        
        $stmt = $this->conn->prepare($query);

        $data['mensaje'] = htmlspecialchars(strip_tags($data['mensaje']));

        $stmt->bindParam(':idEmisor', $data['idEmisor']);
        $stmt->bindParam(':idReceptor', $data['idReceptor']);
        $stmt->bindParam(':mensaje', $data['mensaje']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':rideId', $data['ride_id']);

        return $stmt->execute();
    }

    // Obtener contexto de una conversación
    public function getConversationContext($userId, $otherUserId) {
        $query = "SELECT m.ride_id, a.*, 
                  lo.nombreLocalidad as nombreOrigen,
                  ld.nombreLocalidad as nombreDestino
                  FROM " . $this->table . " m
                  JOIN anuncios a ON m.ride_id = a.idAnuncio
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  WHERE ((m.idEmisor = :userId AND m.idReceptor = :otherUserId)
                      OR (m.idEmisor = :otherUserId AND m.idReceptor = :userId))
                  AND m.tipo = 'sistema'
                  AND m.ride_id IS NOT NULL
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':otherUserId', $otherUserId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    // Editar mensaje (Solo se puede editar si no ha pasado 1 hora)
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


    // Eliminar un mensaje
    public function deleteMessage($idMensaje, $userId) {        
        $query = "DELETE FROM " . $this->table . " WHERE idMensaje = :idMensaje AND idEmisor = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idMensaje', $idMensaje);
        $stmt->bindParam(':userId', $userId);
        
        return $stmt->execute();
    }

    // Eliminar toda la conversación
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