<?php
class Conversation {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Buscar o crear conversación
    public function getOrCreate(int $idAnuncio, int $userA, int $userB): int {
        // Ordenar siempre: user1 = menor id, user2 = mayor id
        $user1 = min($userA, $userB);
        $user2 = max($userA, $userB);

        // Buscar si ya existe
        $query = "SELECT idConversation FROM conversations
                  WHERE idAnuncio = :idAnuncio
                    AND user1_id  = :user1
                    AND user2_id  = :user2
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idAnuncio', $idAnuncio, PDO::PARAM_INT);
        $stmt->bindParam(':user1',     $user1,     PDO::PARAM_INT);
        $stmt->bindParam(':user2',     $user2,     PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return (int) $row['idConversation'];
        }

        // Crear nueva conversación
        $insert = "INSERT INTO conversations (idAnuncio, user1_id, user2_id)
                   VALUES (:idAnuncio, :user1, :user2)";

        $stmtIns = $this->conn->prepare($insert);
        $stmtIns->bindParam(':idAnuncio', $idAnuncio, PDO::PARAM_INT);
        $stmtIns->bindParam(':user1',     $user1,     PDO::PARAM_INT);
        $stmtIns->bindParam(':user2',     $user2,     PDO::PARAM_INT);
        $stmtIns->execute();

        return (int) $this->conn->lastInsertId();
    }

    // Obtiene los datos de una conversación por su id.
    // Verifica que el usuario pertenece a ella.
    public function getByIdForUser(int $idConversation, int $userId) {
        $query = "SELECT c.*,
                         a.tipo        AS anuncioTipo,
                         a.fechaSalida,
                         a.horaSalida,
                         a.precio,
                         lo.nombreLocalidad AS nombreOrigen,
                         ld.nombreLocalidad AS nombreDestino
                  FROM conversations c
                  JOIN anuncios   a  ON a.idAnuncio  = c.idAnuncio
                  JOIN localidades lo ON lo.idLocalidad = a.origen
                  JOIN localidades ld ON ld.idLocalidad = a.destino
                  WHERE c.idConversation = :id
                    AND (c.user1_id = :userId OR c.user2_id = :userId)
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id',     $idConversation, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId,         PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verifica que un usuario pertenece a una conversación.
    public function belongsToUser(int $idConversation, int $userId): bool {
        $query = "SELECT 1 FROM conversations
                  WHERE idConversation = :id
                    AND (user1_id = :userId OR user2_id = :userId)
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id',     $idConversation, PDO::PARAM_INT);
        $stmt->bindParam(':userId', $userId,         PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}
