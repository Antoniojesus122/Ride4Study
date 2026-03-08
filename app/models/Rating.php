<?php

class Rating {

	private PDO $conn;
	private string $table = 'valoraciones';

	public function __construct(PDO $db) {
		$this->conn = $db;
	}

	// Agrega una nueva valoración con validaciones
	public function add(int $fromUserId, int $toUserId, int $idViaje, int $score, array $categories = [], ?string $comentario = null) {
		// Validaciones básicas
		if ($fromUserId === $toUserId) {
			return 'No puedes valorarte a ti mismo';
		}
		
		if ($score < 1 || $score > 5) {
			return 'La puntuación debe estar entre 1 y 5';
		}

		// Verificar que el viaje existe y los usuarios están relacionados
		$tripValidation = $this->validateTripRelationship($idViaje, $fromUserId, $toUserId);
		if ($tripValidation !== true) {
			return $tripValidation; // Retorna el mensaje de error
		}

		// Verificar que ya no haya valorado este viaje
		if ($this->hasRatedTrip($fromUserId, $idViaje)) {
			return 'Ya has valorado este viaje';
		}

		// Verificar que el viaje haya finalizado hace al menos 24 horas
		$tripDate = $this->getTripEndDate($idViaje);
		if ($tripDate === false) {
			return 'No se pudo obtener la fecha del viaje';
		}

		$now = new DateTime();
		$tripEnd = new DateTime($tripDate);
		$diff = $now->diff($tripEnd);
		
		// Debe haber pasado al menos 24 horas desde el fin del viaje
		if ($diff->invert === 0) {
			return 'El viaje aún no ha finalizado';
		}
		
		$hoursPassed = ($diff->days * 24) + $diff->h;
		if ($hoursPassed < 24) {
			return 'Debes esperar al menos 24 horas después del viaje para valorar';
		}

		// Verificar que no hayan pasado más de 30 días
		if ($diff->days > 30) {
			return 'El plazo para valorar este viaje ha expirado (máximo 30 días)';
		}

		// Validar categorías
		$puntualidad = isset($categories['puntualidad']) ? (int)$categories['puntualidad'] : null;
		$comunicacion = isset($categories['comunicacion']) ? (int)$categories['comunicacion'] : null;
		$vehiculo = isset($categories['vehiculo']) ? (int)$categories['vehiculo'] : null;
		$conduccion = isset($categories['conduccion']) ? (int)$categories['conduccion'] : null;
		$comportamiento = isset($categories['comportamiento']) ? (int)$categories['comportamiento'] : null;

		// Validar rangos de categorías
		foreach ([$puntualidad, $comunicacion, $vehiculo, $conduccion, $comportamiento] as $cat) {
			if ($cat !== null && ($cat < 1 || $cat > 5)) {
				return 'Todas las categorías deben estar entre 1 y 5';
			}
		}

		// Insertar valoración
		$sql = "INSERT INTO {$this->table} 
				(idViaje, idValorador, idValorado, puntuacion, puntualidad, comunicacion, vehiculo, conduccion, comportamiento, comentario) 
				VALUES (:idViaje, :from, :to, :score, :puntualidad, :comunicacion, :vehiculo, :conduccion, :comportamiento, :comentario)";
		
		$stmt = $this->conn->prepare($sql);
		
		try {
			return $stmt->execute([
				':idViaje' => $idViaje,
				':from' => $fromUserId,
				':to' => $toUserId,
				':score' => $score,
				':puntualidad' => $puntualidad,
				':comunicacion' => $comunicacion,
				':vehiculo' => $vehiculo,
				':conduccion' => $conduccion,
				':comportamiento' => $comportamiento,
				':comentario' => $comentario
			]);
		} catch (PDOException $e) {
			error_log("Error al insertar valoración: " . $e->getMessage());
			return 'Error al guardar la valoración';
		}
	}

	// Valida que el viaje existe, está aceptado y que los usuarios están relacionados
	private function validateTripRelationship(int $idViaje, int $fromUserId, int $toUserId): bool|string {
		$sql = "SELECT idConductor, idPasajero, estado FROM viajes WHERE idViaje = :idViaje";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute([':idViaje' => $idViaje]);
		$trip = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$trip) {
			return 'El viaje no existe';
		}

		if ($trip['estado'] !== 'aceptado') {
			return 'Solo puedes valorar viajes aceptados';
		}

		// Verificar que fromUserId sea conductor o pasajero, y toUserId sea el otro
		$validRelationship = 
			($fromUserId == $trip['idPasajero'] && $toUserId == $trip['idConductor']) ||
			($fromUserId == $trip['idConductor'] && $toUserId == $trip['idPasajero']);

		if (!$validRelationship) {
			return 'No tienes relación con este viaje';
		}

		return true;
	}

	// Obtiene la fecha de finalización del viaje (fechaSalida + horaRegreso o horaSalida)
	private function getTripEndDate(int $idViaje): string|false {
		$sql = "SELECT CONCAT(a.fechaSalida, ' ', COALESCE(a.horaRegreso, a.horaSalida)) as fecha_fin
				FROM viajes v
				INNER JOIN anuncios a ON v.idAnuncio = a.idAnuncio
				WHERE v.idViaje = :idViaje";
		$stmt = $this->conn->prepare($sql);
		$stmt->execute([':idViaje' => $idViaje]);
		$result = $stmt->fetch(PDO::FETCH_ASSOC);
		
		return $result ? $result['fecha_fin'] : false;
	}

	// Verifica si el usuario ya ha valorado este viaje
	private function hasRatedTrip(int $userId, int $idViaje): bool {
		$stmt = $this->conn->prepare("SELECT COUNT(*) as c FROM {$this->table} WHERE idValorador = :userId AND idViaje = :idViaje");
		$stmt->execute([':userId' => $userId, ':idViaje' => $idViaje]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row && (int)$row['c'] > 0;
	}

	public function getAverage(int $userId): float {
		$stmt = $this->conn->prepare("SELECT AVG(puntuacion) as avg_score FROM {$this->table} WHERE idValorado = :id");
		$stmt->execute([':id' => $userId]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row && $row['avg_score'] !== null ? (float)$row['avg_score'] : 0.0;
	}

	public function getByUser(int $userId, int $limit = 10): array {
		$sql = "SELECT v.*, u.nombre as valoradorNombre, u.foto_perfil as valoradorFoto,
				a.fechaSalida, a.horaSalida,
				origen.nombreLocalidad as origenNombre,
				destino.nombreLocalidad as destinoNombre
				FROM {$this->table} v
				JOIN usuarios u ON u.idUsuario = v.idValorador
				LEFT JOIN viajes vj ON v.idViaje = vj.idViaje
				LEFT JOIN anuncios a ON vj.idAnuncio = a.idAnuncio
				LEFT JOIN localidades origen ON a.origen = origen.idLocalidad
				LEFT JOIN localidades destino ON a.destino = destino.idLocalidad
				WHERE v.idValorado = :id
				ORDER BY v.fecha_valoracion DESC
				LIMIT :lim";

		$stmt = $this->conn->prepare($sql);
		$stmt->bindValue(':id', $userId, PDO::PARAM_INT);
		$stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function hasRated(int $fromUserId, int $toUserId): bool {
		$stmt = $this->conn->prepare("SELECT COUNT(*) as c FROM {$this->table} WHERE idValorador = :from AND idValorado = :to");
		$stmt->execute([':from' => $fromUserId, ':to' => $toUserId]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row && (int)$row['c'] > 0;
	}

	// Obtener viajes pendientes de valoración para un usuario
	public function getPendingRatingsForUser(int $userId): array {
		$sql = "SELECT DISTINCT
				v.idViaje,
				v.idAnuncio,
				v.idConductor,
				v.idPasajero,
				a.fechaSalida,
				a.horaSalida,
				a.horaRegreso,
				a.tipo,
				CASE 
					WHEN v.idPasajero = :userId THEN conductor.idUsuario
					ELSE pasajero.idUsuario
				END as idUsuarioAValorar,
				CASE 
					WHEN v.idPasajero = :userId THEN conductor.nombre
					ELSE pasajero.nombre
				END as nombreUsuarioAValorar,
				CASE 
					WHEN v.idPasajero = :userId THEN conductor.foto_perfil
					ELSE pasajero.foto_perfil
				END as fotoUsuarioAValorar,
				CASE 
					WHEN v.idPasajero = :userId THEN 'conductor'
					ELSE 'pasajero'
				END as rolAValorar,
				origen.nombreLocalidad as origenNombre,
				destino.nombreLocalidad as destinoNombre,
				CONCAT(a.fechaSalida, ' ', COALESCE(a.horaRegreso, a.horaSalida)) as fechaFinViaje
			FROM viajes v
			INNER JOIN anuncios a ON v.idAnuncio = a.idAnuncio
			INNER JOIN usuarios conductor ON v.idConductor = conductor.idUsuario
			INNER JOIN usuarios pasajero ON v.idPasajero = pasajero.idUsuario
			INNER JOIN localidades origen ON a.origen = origen.idLocalidad
			INNER JOIN localidades destino ON a.destino = destino.idLocalidad
			WHERE v.estado = 'aceptado'
			  AND (v.idConductor = :userId OR v.idPasajero = :userId)
			  AND CONCAT(a.fechaSalida, ' ', COALESCE(a.horaRegreso, a.horaSalida)) < NOW()
			  AND CONCAT(a.fechaSalida, ' ', COALESCE(a.horaRegreso, a.horaSalida)) >= DATE_SUB(NOW(), INTERVAL 30 DAY)
			  AND NOT EXISTS (
				  SELECT 1 FROM valoraciones vr 
				  WHERE vr.idViaje = v.idViaje AND vr.idValorador = :userId
			  )
			ORDER BY a.fechaSalida DESC";

		$stmt = $this->conn->prepare($sql);
		$stmt->execute([':userId' => $userId]);
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	// Responder a una valoración recibida (solo el usuario valorado puede responder una vez)
	public function addReply(int $idValoracion, int $idValorado, string $respuesta): bool {
		$sql = "UPDATE {$this->table}
				SET respuesta = :respuesta, fecha_respuesta = NOW()
				WHERE idValoracion = :id AND idValorado = :idValorado AND respuesta IS NULL";
		$stmt = $this->conn->prepare($sql);
		return $stmt->execute([
			':respuesta'  => htmlspecialchars(strip_tags(trim($respuesta))),
			':id'         => $idValoracion,
			':idValorado' => $idValorado,
		]);
	}

	// Obtener detalles de un viaje específico para mostrar en la valoración
	public function getTripDetailsForRating(int $idViaje, int $userId): array|false {
		$sql = "SELECT 
				v.idViaje,
				v.idAnuncio,
				v.idConductor,
				v.idPasajero,
				a.fechaSalida,
				a.horaSalida,
				a.horaRegreso,
				a.tipo,
				conductor.idUsuario as conductorId,
				conductor.nombre as conductorNombre,
				conductor.foto_perfil as conductorFoto,
				pasajero.idUsuario as pasajeroId,
				pasajero.nombre as pasajeroNombre,
				pasajero.foto_perfil as pasajeroFoto,
				origen.nombreLocalidad as origenNombre,
				destino.nombreLocalidad as destinoNombre
			FROM viajes v
			INNER JOIN anuncios a ON v.idAnuncio = a.idAnuncio
			INNER JOIN usuarios conductor ON v.idConductor = conductor.idUsuario
			INNER JOIN usuarios pasajero ON v.idPasajero = pasajero.idUsuario
			INNER JOIN localidades origen ON a.origen = origen.idLocalidad
			INNER JOIN localidades destino ON a.destino = destino.idLocalidad
			WHERE v.idViaje = :idViaje
			  AND (v.idConductor = :userId OR v.idPasajero = :userId)";

		$stmt = $this->conn->prepare($sql);
		$stmt->execute([':idViaje' => $idViaje, ':userId' => $userId]);
		return $stmt->fetch(PDO::FETCH_ASSOC);
	}
}
