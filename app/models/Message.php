<?php
class Message {
    private $conn;
    private $table = 'mensajes';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene la lista de conversaciones del usuario con el último mensaje,
     * nombre del otro usuario e información del anuncio.
     *
     * @param int $userId
     * @return array
     */
    public function getConversations(int $userId): array {
        $query = "
            SELECT
                c.idConversation,
                c.idAnuncio,
                -- Determinar quién es el otro usuario
                CASE WHEN c.user1_id = :userId THEN c.user2_id ELSE c.user1_id END AS otherUserId,
                u.nombre      AS otherUserName,
                u.foto_perfil AS otherUserPhoto,
                -- Info del anuncio
                lo.nombreLocalidad AS nombreOrigen,
                ld.nombreLocalidad AS nombreDestino,
                a.fechaSalida,
                -- Último mensaje
                m.mensaje,
                m.fechaCreacion,
                m.leido,
                m.idEmisor
            FROM conversations c
            JOIN usuarios  u  ON u.idUsuario  = CASE WHEN c.user1_id = :userId THEN c.user2_id ELSE c.user1_id END
            JOIN anuncios  a  ON a.idAnuncio  = c.idAnuncio
            JOIN localidades lo ON lo.idLocalidad = a.origen
            JOIN localidades ld ON ld.idLocalidad = a.destino
            -- Subconsulta para obtener el último mensaje de cada conversación
            JOIN mensajes m ON m.idMensaje = (
                SELECT idMensaje FROM mensajes
                WHERE idConversation = c.idConversation
                ORDER BY fechaCreacion DESC
                LIMIT 1
            )
            WHERE c.user1_id = :userId OR c.user2_id = :userId
            ORDER BY m.fechaCreacion DESC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los mensajes de una conversación, ordenados por fecha ASC.
     * Marca como leídos los mensajes recibidos por el usuario actual.
     *
     * @param int $conversationId
     * @param int $userId  Usuario actual (para marcar como leídos)
     * @return array
     */
    public function getMessages(int $conversationId, int $userId): array {
        $query = "SELECT m.*, u.nombre AS nombreEmisor
                  FROM {$this->table} m
                  JOIN usuarios u ON u.idUsuario = m.idEmisor
                  WHERE m.idConversation = :conversationId
                  ORDER BY m.fechaCreacion ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
        $stmt->execute();

        // Marcar como leídos los mensajes que el usuario actual recibe
        $this->markAsRead($conversationId, $userId);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Inserta un nuevo mensaje en una conversación.
     *
     * @param array $data  Debe contener: idConversation, idEmisor, idReceptor, mensaje
     * @return bool
     */
    public function createMessage(array $data): bool {
        $query = "INSERT INTO {$this->table}
                    (idConversation, idEmisor, idReceptor, mensaje)
                  VALUES
                    (:idConversation, :idEmisor, :idReceptor, :mensaje)";

        $stmt = $this->conn->prepare($query);

        $mensaje = htmlspecialchars(strip_tags($data['mensaje']));

        $stmt->bindParam(':idConversation', $data['idConversation'], PDO::PARAM_INT);
        $stmt->bindParam(':idEmisor',       $data['idEmisor'],       PDO::PARAM_INT);
        $stmt->bindParam(':idReceptor',     $data['idReceptor'],     PDO::PARAM_INT);
        $stmt->bindParam(':mensaje',        $mensaje);

        return $stmt->execute();
    }

    /**
     * Marca como leídos los mensajes de una conversación dirigidos al usuario actual.
     *
     * @param int $conversationId
     * @param int $userId
     */
    public function markAsRead(int $conversationId, int $userId): void {
        $query = "UPDATE {$this->table}
                  SET leido = 1
                  WHERE idConversation = :conversationId
                    AND idReceptor = :userId
                    AND leido = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);
        $stmt->bindParam(':userId',         $userId,         PDO::PARAM_INT);
        $stmt->execute();
    }

    /**
     * Edita un mensaje (solo el emisor, dentro de la primera hora).
     *
     * @param int    $idMensaje
     * @param int    $userId
     * @param string $newText
     * @return bool|string  true si ok, 'expired' si pasó 1 hora, false si no es tuyo
     */
    public function updateMessage(int $idMensaje, int $userId, string $newText) {
        $checkQuery = "SELECT fechaCreacion FROM {$this->table}
                       WHERE idMensaje = :idMensaje AND idEmisor = :userId";
        $stmtCheck = $this->conn->prepare($checkQuery);
        $stmtCheck->bindParam(':idMensaje', $idMensaje, PDO::PARAM_INT);
        $stmtCheck->bindParam(':userId',    $userId,    PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() === 0) {
            return false; // No existe o no es tuyo
        }

        $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        if ((time() - strtotime($row['fechaCreacion'])) > 3600) {
            return 'expired';
        }

        $query = "UPDATE {$this->table} SET mensaje = :mensaje WHERE idMensaje = :idMensaje";
        $stmt  = $this->conn->prepare($query);

        $newText = htmlspecialchars(strip_tags($newText));
        $stmt->bindParam(':mensaje',   $newText);
        $stmt->bindParam(':idMensaje', $idMensaje, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Elimina un mensaje (solo el emisor puede eliminarlo).
     *
     * @param int $idMensaje
     * @param int $userId
     * @return bool
     */
    public function deleteMessage(int $idMensaje, int $userId): bool {
        $query = "DELETE FROM {$this->table}
                  WHERE idMensaje = :idMensaje AND idEmisor = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idMensaje', $idMensaje, PDO::PARAM_INT);
        $stmt->bindParam(':userId',    $userId,    PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Elimina todos los mensajes de una conversación.
     * Solo lo puede hacer un participante de la conversación (validado en el controlador).
     *
     * @param int $conversationId
     * @return bool
     */
    public function deleteConversationMessages(int $conversationId): bool {
        $query = "DELETE FROM {$this->table} WHERE idConversation = :conversationId";
        $stmt  = $this->conn->prepare($query);
        $stmt->bindParam(':conversationId', $conversationId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Cuenta los mensajes no leídos del usuario en todas sus conversaciones.
     *
     * @param int $userId
     * @return int
     */
    public function countUnread(int $userId): int {
        $query = "SELECT COUNT(*) FROM {$this->table}
                  WHERE idReceptor = :userId AND leido = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }
}