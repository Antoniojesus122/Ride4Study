<?php
class Ride {
    private $conn;
    private $table = 'anuncios';

    public $idAnuncio;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Obtener viajes con paginación y filtros
    public function getPaginatedRides($page = 1, $limit = 9, $filters = [], $excludeUserId = null) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT a.*, u.nombre as nombreUsuario, u.foto_perfil, u.biografia, u.estado_verificacion,
                  lo.nombreLocalidad as nombreOrigen, ld.nombreLocalidad as nombreDestino,
                  COALESCE(AVG(v.puntuacion), 0) as rating, COUNT(v.idValoracion) as ratingCount
                  FROM " . $this->table . " a
                  JOIN usuarios u ON a.idUsuario = u.idUsuario
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  LEFT JOIN valoraciones v ON u.idUsuario = v.idValorado
                  WHERE 1=1";

        $params = [];

        if ($excludeUserId) {
            $query .= " AND a.idUsuario != :excludeUserId";
            $params[':excludeUserId'] = $excludeUserId;
        }

        if (!empty($filters['origen'])) {
            $query .= " AND lo.nombreLocalidad LIKE :origen";
            $params[':origen'] = '%' . $filters['origen'] . '%';
        }

        if (!empty($filters['destino'])) {
            $query .= " AND ld.nombreLocalidad LIKE :destino";
            $params[':destino'] = '%' . $filters['destino'] . '%';
        }

        if (!empty($filters['fecha'])) {
            $query .= " AND a.fechaSalida = :fecha";
            $params[':fecha'] = $filters['fecha'];
        }

        if (!empty($filters['tipo'])) {
            // Pasar los tipos de minúsculas
            $query .= " AND LOWER(a.tipo) = :tipo";
            $params[':tipo'] = strtolower($filters['tipo']);
        }
        
        // Excluir viajes pasados
        $query .= " AND (a.fechaSalida > CURDATE() OR (a.fechaSalida = CURDATE() AND a.horaSalida >= CURTIME()))";

        $query .= " GROUP BY a.idAnuncio";
        // Los anuncios destacados aparecen primero
        $query .= " ORDER BY a.destacado DESC, a.fechaSalida ASC, a.horaSalida ASC, a.fechaPublicacion DESC";
        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        
        // Filtros fijados
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $rides = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener total para paginación
        $countQuery = "SELECT COUNT(*) as total 
                       FROM " . $this->table . " a
                       JOIN localidades lo ON a.origen = lo.idLocalidad
                       JOIN localidades ld ON a.destino = ld.idLocalidad
                       WHERE 1=1";
        
        $countParams = [];
        
        if ($excludeUserId) {
            $countQuery .= " AND a.idUsuario != :excludeUserId";
            $countParams[':excludeUserId'] = $excludeUserId;
        }

        if (!empty($filters['origen'])) {
            $countQuery .= " AND lo.nombreLocalidad LIKE :origen";
            $countParams[':origen'] = '%' . $filters['origen'] . '%';
        }

        if (!empty($filters['destino'])) {
            $countQuery .= " AND ld.nombreLocalidad LIKE :destino";
            $countParams[':destino'] = '%' . $filters['destino'] . '%';
        }

        if (!empty($filters['fecha'])) {
            $countQuery .= " AND a.fechaSalida = :fecha";
            $countParams[':fecha'] = $filters['fecha'];
        }

        if (!empty($filters['tipo'])) {
            $countQuery .= " AND LOWER(a.tipo) = :tipo";
            $countParams[':tipo'] = strtolower($filters['tipo']);
        }

        // Se excluyen los viajes pasados en el recuento de viajes
        $countQuery .= " AND (a.fechaSalida > CURDATE() OR (a.fechaSalida = CURDATE() AND a.horaSalida >= CURTIME()))";

        $countStmt = $this->conn->prepare($countQuery);
        
        // Vincular parámetros
        foreach ($countParams as $key => &$val) {
            $countStmt->bindParam($key, $val);
        }
        
        $countStmt->execute();
        $totalRow = $countStmt->fetch(PDO::FETCH_ASSOC);
        $totalRides = $totalRow['total'];

        return [
            'rides' => $rides,
            'total' => $totalRides,
            'pages' => ceil($totalRides / $limit)
        ];
    }

    // Comprobar si el usuario tiene una reserva para un viaje específico
    // Busca tanto como pasajero (ofrezco) como conductor (busco)
    public function hasBooking($rideId, $userId) {
        $query = "SELECT v.estado FROM viajes v
                  WHERE v.idAnuncio = :rideId
                  AND (v.idPasajero = :userId1 OR v.idConductor = :userId2)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rideId', $rideId);
        $stmt->bindParam(':userId1', $userId);
        $stmt->bindParam(':userId2', $userId);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener todas las reservas de un usuario (para el dashboard)
    // Incluye reservas como pasajero en "ofrezco" y ofertas como conductor en "busco"
    public function getUserBookings($userId) {
        $query = "SELECT v.idAnuncio, v.estado FROM viajes v
                  JOIN " . $this->table . " a ON v.idAnuncio = a.idAnuncio
                  WHERE (v.idPasajero = :userId1 AND LOWER(a.tipo) = 'ofrezco')
                     OR (v.idConductor = :userId2 AND LOWER(a.tipo) = 'busco')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId1', $userId);
        $stmt->bindParam(':userId2', $userId);
        $stmt->execute();

        $bookings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bookings[$row['idAnuncio']] = $row['estado'];
        }
        return $bookings;
    }

    public function getRidesByUserId($userId) {
        $currentDate = date('Y-m-d H:i:s');
        
        $query = "SELECT a.*, lo.nombreLocalidad as nombreOrigen, ld.nombreLocalidad as nombreDestino,
                  (SELECT COUNT(*) FROM viajes v WHERE v.idAnuncio = a.idAnuncio) as pasajerosCount
                  FROM " . $this->table . " a
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  WHERE a.idUsuario = :idUsuario
                  ORDER BY a.fechaSalida DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':idUsuario', $userId);
        $stmt->execute();
        
        $allRides = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $activeRides = [];
        $pastRides = [];
        
        foreach ($allRides as &$ride) {
            // Obtener los usuarios asociados al viaje según el tipo de anuncio
            // Para "busco": mostrar conductores que ofrecieron llevar
            // Para "ofrezco": mostrar pasajeros que reservaron plaza
            if (strtolower($ride['tipo']) === 'busco') {
                $passengerQuery = "SELECT u.idUsuario, u.nombre, u.foto_perfil, v.fechaSalida as fechaUnido, v.estado
                                   FROM viajes v
                                   JOIN usuarios u ON v.idConductor = u.idUsuario
                                   WHERE v.idAnuncio = :idAnuncio";
            } else {
                $passengerQuery = "SELECT u.idUsuario, u.nombre, u.foto_perfil, v.fechaSalida as fechaUnido, v.estado
                                   FROM viajes v
                                   JOIN usuarios u ON v.idPasajero = u.idUsuario
                                   WHERE v.idAnuncio = :idAnuncio";
            }
            $pStmt = $this->conn->prepare($passengerQuery);
            $pStmt->bindParam(':idAnuncio', $ride['idAnuncio']);
            $pStmt->execute();
            $ride['passengers'] = $pStmt->fetchAll(PDO::FETCH_ASSOC);

            // Separar viajes activos de pasados
            $rideDateTime = $ride['fechaSalida'] . ' ' . $ride['horaSalida'];
            if ($rideDateTime >= $currentDate) {
                $activeRides[] = $ride;
            } else {
                $pastRides[] = $ride;
            }
        }

        return ['active' => $activeRides, 'past' => $pastRides];
    }

    public function getPassengerBookings($userId) {
        // Para "ofrezco": el usuario reservó plaza como pasajero (v.idPasajero = userId)
        // Para "busco": el usuario ofreció llevar como conductor (v.idConductor = userId)
        // En ambos casos mostramos la info del publicador del anuncio (a.idUsuario)
        $query = "SELECT a.*, u.nombre as nombreUsuario, u.foto_perfil, v.estado as estadoReserva,
                  lo.nombreLocalidad as nombreOrigen, ld.nombreLocalidad as nombreDestino
                  FROM viajes v
                  JOIN " . $this->table . " a ON v.idAnuncio = a.idAnuncio
                  JOIN usuarios u ON a.idUsuario = u.idUsuario
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  WHERE (v.idPasajero = :userId1 AND LOWER(a.tipo) = 'ofrezco')
                     OR (v.idConductor = :userId2 AND LOWER(a.tipo) = 'busco')
                  ORDER BY a.fechaSalida DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId1', $userId);
        $stmt->bindParam(':userId2', $userId);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllLocations() {
        $query = "SELECT * FROM localidades ORDER BY nombreLocalidad ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createRide($data) {
        $query = "INSERT INTO " . $this->table . " 
                  (idUsuario, tipo, origen, destino, fechaSalida, horaSalida, horaRegreso, plazasDisponibles, precio, descripcion)
                  VALUES
                  (:idUsuario, :tipo, :origen, :destino, :fechaSalida, :horaSalida, :horaRegreso, :plazasDisponibles, :precio, :descripcion)";

        $stmt = $this->conn->prepare($query);

        // Sanitización de datos
        $data['descripcion'] = htmlspecialchars(strip_tags($data['descripcion']));

        $stmt->bindParam(':idUsuario', $data['idUsuario']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':origen', $data['origen']);
        $stmt->bindParam(':destino', $data['destino']);
        $stmt->bindParam(':fechaSalida', $data['fechaSalida']);
        $stmt->bindParam(':horaSalida', $data['horaSalida']);
        $stmt->bindParam(':horaRegreso', $data['horaRegreso']);
        $stmt->bindParam(':plazasDisponibles', $data['plazasDisponibles']);
        $stmt->bindParam(':precio', $data['precio']);
        $stmt->bindParam(':descripcion', $data['descripcion']);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getRideById($id) {
        $query = "SELECT a.*, u.nombre as nombreUsuario, u.foto_perfil, 
                  lo.nombreLocalidad as nombreOrigen, ld.nombreLocalidad as nombreDestino,
                  COALESCE(AVG(v.puntuacion), 0) as rating,
                  a.tipo, a.idUsuario, a.fechaSalida, a.horaSalida
                  FROM " . $this->table . " a
                  JOIN usuarios u ON a.idUsuario = u.idUsuario
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  LEFT JOIN valoraciones v ON u.idUsuario = v.idValorado
                  WHERE a.idAnuncio = :id
                  GROUP BY a.idAnuncio";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Función para solicitar una reserva u oferta en un viaje, manejando ambos casos (ofrezco y busco)
    public function requestReservation($rideId, $userId) {
        try {
            // Obtener información completa del anuncio
            $getRideQuery = "SELECT idUsuario, tipo FROM " . $this->table . " WHERE idAnuncio = :rideId";
            $stmt = $this->conn->prepare($getRideQuery);
            $stmt->execute([':rideId' => $rideId]);
            $ride = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$ride) {
                error_log("Viaje no encontrado: " . $rideId);
                return false;
            }
            
            // Determinar roles según tipo de anuncio
            $tipo = strtolower($ride['tipo']);
            
            if ($tipo === 'ofrezco') {
                // TIPO OFREZCO: Usuario reserva plaza en viaje ofrecido
                $conductorId = $ride['idUsuario'];  // Quien publicó es conductor
                $pasajeroId = $userId;              // Quien reserva es pasajero
                error_log("Reserva tipo 'ofrezco' - Conductor: $conductorId, Pasajero: $pasajeroId");
                
            } else if ($tipo === 'busco') {
                // TIPO  BUSCO: Usuario ofrece llevar a quien busca viaje
                $conductorId = $userId;             // Quien responde es conductor
                $pasajeroId = $ride['idUsuario'];   // Quien publicó es pasajero
                error_log("Oferta tipo 'busco' - Conductor: $conductorId, Pasajero: $pasajeroId");
                
            } else {
                error_log("Tipo de anuncio inválido: " . $tipo);
                return false;
            }
            
            // Crear la reserva/oferta con roles correctos
            $query = "INSERT INTO viajes (idAnuncio, idConductor, idPasajero, estado)
                    VALUES (:rideId, :conductorId, :pasajeroId, 'pendiente')";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':rideId' => $rideId,
                ':conductorId' => $conductorId,
                ':pasajeroId' => $pasajeroId
            ]);
            
            if ($result) {
                error_log("Reserva/Oferta creada correctamente - Anuncio: $rideId, Tipo: $tipo");
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("✗ Error en requestReservation: " . $e->getMessage());
            error_log("Código error: " . $e->getCode());
            return false;
        }
    }

    // Actualizar el estado de una reserva/oferta
    // $userId puede ser idPasajero (ofrezco) o idConductor (busco)
    public function updateReservationStatus($rideId, $userId, $status) {

        if ($status === 'aceptado') {
            $updateSeats = "UPDATE " . $this->table . " SET plazasDisponibles = plazasDisponibles - 1
                            WHERE idAnuncio = :rideId AND plazasDisponibles > 0";
            $stmtSeats = $this->conn->prepare($updateSeats);
            $stmtSeats->bindParam(':rideId', $rideId);
            $stmtSeats->execute();

            if ($stmtSeats->rowCount() === 0) {
                return false;
            }
        }

        $query = "UPDATE viajes SET estado = :status
                  WHERE idAnuncio = :rideId
                  AND (idPasajero = :userId1 OR idConductor = :userId2)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':rideId', $rideId);
        $stmt->bindParam(':userId1', $userId);
        $stmt->bindParam(':userId2', $userId);

        return $stmt->execute();
    }

    public function updateRide($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET tipo = :tipo,
                      origen = :origen, 
                      destino = :destino, 
                      fechaSalida = :fechaSalida, 
                      horaSalida = :horaSalida, 
                      horaRegreso = :horaRegreso, 
                      plazasDisponibles = :plazasDisponibles, 
                      precio = :precio, 
                      descripcion = :descripcion
                  WHERE idAnuncio = :id";

        $stmt = $this->conn->prepare($query);

        $data['descripcion'] = htmlspecialchars(strip_tags($data['descripcion']));

        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':origen', $data['origen']);
        $stmt->bindParam(':destino', $data['destino']);
        $stmt->bindParam(':fechaSalida', $data['fechaSalida']);
        $stmt->bindParam(':horaSalida', $data['horaSalida']);
        $stmt->bindParam(':horaRegreso', $data['horaRegreso']);
        $stmt->bindParam(':plazasDisponibles', $data['plazasDisponibles']);
        $stmt->bindParam(':precio', $data['precio']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function countAll(): int {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM {$this->table}");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    // Cancelar una reserva (funciona tanto para pasajero en "ofrezco" como conductor en "busco")
    public function cancelReservation($rideId, $userId) {
        try {
            $this->conn->beginTransaction();

            // Obtener el estado actual de la reserva
            $checkQuery = "SELECT estado FROM viajes
                          WHERE idAnuncio = :rideId
                          AND (idPasajero = :userId1 OR idConductor = :userId2)";
            $stmt = $this->conn->prepare($checkQuery);
            $stmt->execute([
                ':rideId' => $rideId,
                ':userId1' => $userId,
                ':userId2' => $userId
            ]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking) {
                $this->conn->rollBack();
                return false;
            }

            // Si la reserva estaba aceptada, devolver la plaza
            if ($booking['estado'] === 'aceptado') {
                $updateSeats = "UPDATE " . $this->table . "
                               SET plazasDisponibles = plazasDisponibles + 1
                               WHERE idAnuncio = :rideId";
                $stmtSeats = $this->conn->prepare($updateSeats);
                $stmtSeats->execute([':rideId' => $rideId]);
            }

            // Eliminar la reserva
            $deleteQuery = "DELETE FROM viajes
                           WHERE idAnuncio = :rideId
                           AND (idPasajero = :userId1 OR idConductor = :userId2)";
            $stmt = $this->conn->prepare($deleteQuery);
            $result = $stmt->execute([
                ':rideId' => $rideId,
                ':userId1' => $userId,
                ':userId2' => $userId
            ]);

            $this->conn->commit();
            return $result;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Cancel Reservation Error: " . $e->getMessage());
            return false;
        }
    }

    // Obtener detalles de una reserva específica para un usuario
    public function getBookingDetails($rideId, $userId) {
        $query = "SELECT v.*, a.fechaSalida, a.horaSalida,
                  u.nombre as conductorNombre, u.telefono as conductorTelefono,
                  u.correo as conductorCorreo
                  FROM viajes v
                  JOIN " . $this->table . " a ON v.idAnuncio = a.idAnuncio
                  JOIN usuarios u ON a.idUsuario = u.idUsuario
                  WHERE v.idAnuncio = :rideId AND v.idPasajero = :userId";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':rideId' => $rideId,
            ':userId' => $userId
        ]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener todas las reservas pendientes para un conductor
    public function getPendingReservations($conductorId) {
        $query = "SELECT v.*, u.nombre as pasajeroNombre, u.foto_perfil,
                  a.origen, a.destino, a.fechaSalida, a.horaSalida,
                  lo.nombreLocalidad as nombreOrigen, 
                  ld.nombreLocalidad as nombreDestino
                  FROM viajes v
                  JOIN usuarios u ON v.idPasajero = u.idUsuario
                  JOIN " . $this->table . " a ON v.idAnuncio = a.idAnuncio
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  WHERE v.idConductor = :conductorId 
                  AND v.estado = 'pendiente'
                  ORDER BY v.fechaSalida ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':conductorId' => $conductorId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los usuarios conectados a un anuncio (para notificar al eliminar)
    // Para "ofrezco": pasajeros que reservaron. Para "busco": conductores que ofrecieron.
    public function getConnectedUsers($rideId) {
        $typeQuery = "SELECT tipo FROM " . $this->table . " WHERE idAnuncio = :rideId";
        $stmt = $this->conn->prepare($typeQuery);
        $stmt->execute([':rideId' => $rideId]);
        $ride = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$ride) return [];

        if (strtolower($ride['tipo']) === 'ofrezco') {
            $query = "SELECT u.idUsuario, u.nombre, u.correo, u.notificaciones_email
                      FROM viajes v
                      JOIN usuarios u ON v.idPasajero = u.idUsuario
                      WHERE v.idAnuncio = :rideId";
        } else {
            $query = "SELECT u.idUsuario, u.nombre, u.correo, u.notificaciones_email
                      FROM viajes v
                      JOIN usuarios u ON v.idConductor = u.idUsuario
                      WHERE v.idAnuncio = :rideId";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':rideId' => $rideId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contar anuncios futuros activos del usuario (para el límite de plan gratuito)
    public function getActiveCount(int $userId): int {
        $sql = "SELECT COUNT(*) as c FROM {$this->table}
                WHERE idUsuario = :id
                  AND (fechaSalida > CURDATE() OR (fechaSalida = CURDATE() AND horaSalida >= CURTIME()))";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['c'] ?? 0);
    }

    // Destacar un anuncio (desactiva el destacado de los demás del mismo usuario primero)
    public function toggleFeatured(int $rideId, int $userId): bool {
        // Comprobar si ya está destacado
        $check = $this->conn->prepare("SELECT destacado FROM {$this->table} WHERE idAnuncio = :id AND idUsuario = :uid");
        $check->execute([':id' => $rideId, ':uid' => $userId]);
        $current = $check->fetch(PDO::FETCH_ASSOC);

        if (!$current) return false;

        if ($current['destacado']) {
            // Si ya está destacado, quitarlo
            $stmt = $this->conn->prepare("UPDATE {$this->table} SET destacado = 0 WHERE idAnuncio = :id AND idUsuario = :uid");
            return $stmt->execute([':id' => $rideId, ':uid' => $userId]);
        }

        // Si no está destacado, quitar el destacado de todos y activar este
        $this->conn->prepare("UPDATE {$this->table} SET destacado = 0 WHERE idUsuario = :uid")->execute([':uid' => $userId]);
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET destacado = 1 WHERE idAnuncio = :id AND idUsuario = :uid");
        return $stmt->execute([':id' => $rideId, ':uid' => $userId]);
    }

    // Eliminar viaje
    public function deleteRide($rideId) {
        $query = "DELETE FROM " . $this->table . " WHERE idAnuncio = :rideId";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':rideId' => $rideId]);
    }
}