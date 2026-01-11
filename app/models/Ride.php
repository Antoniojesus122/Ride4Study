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
        $query .= " ORDER BY a.fechaSalida ASC, a.horaSalida ASC, a.fechaPublicacion DESC";
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
    public function hasBooking($rideId, $userId) {
        $query = "SELECT estado FROM viajes WHERE idAnuncio = :rideId AND idPasajero = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':rideId', $rideId);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // Obtener todas las reservar de un usuario
    public function getUserBookings($userId) {
        $query = "SELECT idAnuncio, estado FROM viajes WHERE idPasajero = :userId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
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
            // Obtener los pasajeros de un viaje
            $passengerQuery = "SELECT u.idUsuario, u.nombre, u.foto_perfil, v.fechaSalida as fechaUnido, v.estado
                               FROM viajes v
                               JOIN usuarios u ON v.idPasajero = u.idUsuario
                               WHERE v.idAnuncio = :idAnuncio";
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
        $query = "SELECT a.*, u.nombre as nombreUsuario, u.foto_perfil, v.estado as estadoReserva, 
                  lo.nombreLocalidad as nombreOrigen, ld.nombreLocalidad as nombreDestino
                  FROM viajes v
                  JOIN " . $this->table . " a ON v.idAnuncio = a.idAnuncio
                  JOIN usuarios u ON a.idUsuario = u.idUsuario
                  JOIN localidades lo ON a.origen = lo.idLocalidad
                  JOIN localidades ld ON a.destino = ld.idLocalidad
                  WHERE v.idPasajero = :userId
                  ORDER BY a.fechaSalida DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
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
    public function requestReservation($rideId, $userId) {
        $query = "INSERT INTO viajes (idAnuncio, idConductor, idPasajero, estado, fechaSalida)
                  SELECT :rideId, idUsuario, :userId, 'pendiente', ADDTIME(fechaSalida, horaSalida)
                  FROM " . $this->table . " WHERE idAnuncio = :rideId";
        
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':rideId', $rideId);
            $stmt->bindParam(':userId', $userId);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Reservation Error: " . $e->getMessage());
            return false;
        }
    }


    // Función para obtener si se ha actualizado el estatus de la reserva de una plaza en un viaje
    public function updateReservationStatus($rideId, $passengerId, $status) {
        
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
                  WHERE idAnuncio = :rideId AND idPasajero = :passengerId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':rideId', $rideId);
        $stmt->bindParam(':passengerId', $passengerId);
        
        return $stmt->execute();
    }

    public function updateRide($id, $data) {
        $query = "UPDATE " . $this->table . " 
                  SET origen = :origen, 
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
}
