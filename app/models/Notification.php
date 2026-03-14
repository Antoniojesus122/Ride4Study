<?php

class Notification {
    private PDO $conn;
    private string $table = 'notificaciones';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // Crear una notificación dentro de la aplicación web para un usuario
    public function create(int $idUsuario, string $mensaje, string $icono = 'fas fa-bell', string $url = ''): bool {
        $sql = "INSERT INTO {$this->table} (idUsuario, tipoNotificacion, mensaje, icono, url)
                VALUES (:idUsuario, 'sistema', :mensaje, :icono, :url)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':idUsuario' => $idUsuario,
            ':mensaje'   => $mensaje,
            ':icono'     => $icono,
            ':url'       => $url,
        ]);
    }

    // Obtener notificaciones no leídas de un usuario
    public function getUnread(int $idUsuario, int $limit = 20): array {
        $sql = "SELECT idNotificacion, idUsuario, tipoNotificacion, mensaje, leida, icono, url,
                       fechaEnvio,
                       fechaEnvio AS fecha_creacion
                FROM {$this->table}
                WHERE idUsuario = :id AND tipoNotificacion = 'sistema' AND leida = 0
                ORDER BY fechaEnvio DESC
                LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contar notificaciones no leídas de un usuario
    public function countUnread(int $idUsuario): int {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as c FROM {$this->table}
             WHERE idUsuario = :id AND tipoNotificacion = 'sistema' AND leida = 0"
        );
        $stmt->execute([':id' => $idUsuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['c'] ?? 0);
    }

    // Marcar todas las notificaciones de un usuario como leídas
    public function markAllRead(int $idUsuario): bool {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET leida = 1
             WHERE idUsuario = :id AND tipoNotificacion = 'sistema'"
        );
        return $stmt->execute([':id' => $idUsuario]);
    }

    // Marcar una notificación concreta como leída
    public function markRead(int $idNotificacion, int $idUsuario): bool {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET leida = 1
             WHERE idNotificacion = :id AND idUsuario = :userId"
        );
        return $stmt->execute([':id' => $idNotificacion, ':userId' => $idUsuario]);
    }
}
