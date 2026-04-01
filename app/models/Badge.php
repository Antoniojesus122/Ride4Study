<?php

class Badge
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Obtener los badges obtenidos por un usuario
    public function getUserBadges(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, ub.obtenido_en
            FROM usuario_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.idUsuario = :uid
            ORDER BY b.nivel DESC, ub.obtenido_en ASC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener todos los badges disponibles
    public function getAllBadges(): array
    {
        $stmt = $this->db->query("SELECT * FROM badges ORDER BY categoria, nivel ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Verificar si un usuario ya tiene un badge específico
    public function hasBadge(int $userId, int $badgeId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM usuario_badges WHERE idUsuario = :uid AND badge_id = :bid
        ");
        $stmt->execute([':uid' => $userId, ':bid' => $badgeId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // Otorgar un badge a un usuario (si no lo tiene ya)
    public function award(int $userId, int $badgeId): bool
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO usuario_badges (idUsuario, badge_id) VALUES (:uid, :bid)
        ");
        return $stmt->execute([':uid' => $userId, ':bid' => $badgeId]);
    }

    // Verificar condiciones y otorgar nuevos badges a un usuario
    public function checkAndAward(int $userId): array
    {
        $stats = $this->getUserStats($userId);
        $allBadges = $this->getAllBadges();
        $newBadges = [];

        foreach ($allBadges as $badge) {
            if ($this->hasBadge($userId, (int)$badge['id'])) {
                continue;
            }

            $earned = false;

            switch ($badge['condicion_tipo']) {
                case 'total_viajes':
                    $earned = $stats['total_viajes'] >= $badge['condicion_valor'];
                    break;
                case 'viajes_conductor':
                    $earned = $stats['viajes_conductor'] >= $badge['condicion_valor'];
                    break;
                case 'viajes_pasajero':
                    $earned = $stats['viajes_pasajero'] >= $badge['condicion_valor'];
                    break;
                case 'co2_ahorrado':
                    $earned = $stats['co2_ahorrado'] >= $badge['condicion_valor'];
                    break;
                case 'valoraciones_recibidas':
                    $earned = $stats['valoraciones_recibidas'] >= $badge['condicion_valor'];
                    break;
                case 'media_valoracion':
                    // condicion_valor 45 = media >= 4.5, 50 = media = 5.0
                    $minRating = $badge['condicion_valor'] / 10;
                    $earned = $stats['valoraciones_recibidas'] >= 3 && $stats['media_valoracion'] >= $minRating;
                    break;
                case 'verificado':
                    $earned = $stats['verificado'] === true;
                    break;
                case 'premium':
                    $earned = $stats['premium'] === true;
                    break;
                case 'early_adopter':
                    $earned = $stats['early_adopter'] === true;
                    break;
                case 'viajes_completados':
                    $earned = $stats['viajes_completados'] >= $badge['condicion_valor'];
                    break;
            }

            if ($earned) {
                $this->award($userId, (int)$badge['id']);
                $newBadges[] = $badge;
            }
        }

        return $newBadges;
    }

    // Obtener estadísticas relevantes de un usuario para evaluar badges
    private function getUserStats(int $userId): array
    {
        // Datos del usuario
        $stmt = $this->db->prepare("
            SELECT co2_ahorrado, estado_verificacion, premium, creado_en
            FROM usuarios WHERE idUsuario = :uid
        ");
        $stmt->execute([':uid' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return [];
        }

        // Viajes como conductor (aceptados o completados)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM viajes
            WHERE idConductor = :uid AND estado IN ('aceptado', 'completado')
        ");
        $stmt->execute([':uid' => $userId]);
        $conductor = (int)$stmt->fetchColumn();

        // Viajes como pasajero (aceptados o completados)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM viajes
            WHERE idPasajero = :uid AND estado IN ('aceptado', 'completado')
        ");
        $stmt->execute([':uid' => $userId]);
        $pasajero = (int)$stmt->fetchColumn();

        // Viajes completados (estado = 'completado')
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM viajes
            WHERE (idConductor = :uid1 OR idPasajero = :uid2) AND estado = 'completado'
        ");
        $stmt->execute([':uid1' => $userId, ':uid2' => $userId]);
        $completados = (int)$stmt->fetchColumn();

        // Valoraciones recibidas
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM valoraciones WHERE idValorado = :uid
        ");
        $stmt->execute([':uid' => $userId]);
        $totalRatings = (int)$stmt->fetchColumn();

        // Media de valoraciones
        $stmt = $this->db->prepare("
            SELECT COALESCE(AVG(puntuacion), 0) FROM valoraciones WHERE idValorado = :uid
        ");
        $stmt->execute([':uid' => $userId]);
        $avgRating = (float)$stmt->fetchColumn();

        // Early adopter: registrado en los primeros 30 días del proyecto
        // (consideramos usuarios entre los primeros 50)
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM usuarios WHERE idUsuario <= :uid
        ");
        $stmt->execute([':uid' => $userId]);
        $position = (int)$stmt->fetchColumn();

        return [
            'total_viajes' => $conductor + $pasajero,
            'viajes_conductor' => $conductor,
            'viajes_pasajero' => $pasajero,
            'viajes_completados' => $completados,
            'co2_ahorrado' => (float)($user['co2_ahorrado'] ?? 0),
            'valoraciones_recibidas' => $totalRatings,
            'media_valoracion' => round($avgRating, 1),
            'verificado' => (int)($user['estado_verificacion'] ?? 0) === 2,
            'premium' => (int)($user['premium'] ?? 0) === 1,
            'early_adopter' => $position <= 50,
        ];
    }

    // Contar cuántos badges tiene un usuario (para mostrar progreso)
    public function countUserBadges(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM usuario_badges WHERE idUsuario = :uid
        ");
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    // Obtener los últimos badges obtenidos por un usuario (para notificaciones)
    public function getRecentBadges(int $userId, int $limit = 3): array
    {
        $stmt = $this->db->prepare("
            SELECT b.*, ub.obtenido_en
            FROM usuario_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.idUsuario = :uid
            ORDER BY ub.obtenido_en DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
