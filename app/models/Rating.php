<?php

class Rating {

	private PDO $conn;
	private string $table = 'valoraciones';

	public function __construct(PDO $db) {
		$this->conn = $db;
	}

	public function add(int $fromUserId, int $toUserId, int $score): bool {
		if ($fromUserId === $toUserId) return false;
		$score = (int)$score;
		if ($score < 1 || $score > 5) return false;

		$sql = "INSERT INTO {$this->table} (idValorador, idValorado, puntuacion) VALUES (:from, :to, :score)";
		$stmt = $this->conn->prepare($sql);
		return $stmt->execute([':from' => $fromUserId, ':to' => $toUserId, ':score' => $score]);
	}

	public function getAverage(int $userId): float {
		$stmt = $this->conn->prepare("SELECT AVG(puntuacion) as avg_score FROM {$this->table} WHERE idValorado = :id");
		$stmt->execute([':id' => $userId]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		return $row && $row['avg_score'] !== null ? (float)$row['avg_score'] : 0.0;
	}

	public function getByUser(int $userId, int $limit = 10): array {
		$sql = "SELECT v.*, u.nombre as valoradorNombre, u.foto_perfil as valoradorFoto
				FROM {$this->table} v
				JOIN usuarios u ON u.idUsuario = v.idValorador
				WHERE v.idValorado = :id
				ORDER BY v.idValoracion DESC
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
}
