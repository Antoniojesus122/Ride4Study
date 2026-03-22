<?php

class User {

    private PDO $conn;
    private string $table = 'usuarios';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function register(string $nombre, string $correo, string $password, int $idRol = 2, int $telefono): bool {

        $sql = "INSERT INTO {$this->table} (nombre, correo, contrasena, idRol, telefono)
                VALUES (:nombre, :correo, :contrasena, :idRol, :telefono)";

        $stmt = $this->conn->prepare($sql);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        return $stmt->execute([
            ':nombre'     => htmlspecialchars(strip_tags($nombre)),
            ':correo'     => htmlspecialchars(strip_tags($correo)),
            ':contrasena' => $hashedPassword,
            ':idRol'      => $idRol,
            ':telefono'   => htmlspecialchars(strip_tags($telefono))
        ]);
    }

    public function login(string $correo, string $contrasena): array|false {

    $sql = "SELECT idUsuario, nombre, contrasena, idRol
            FROM usuarios
            WHERE correo = :correo
            LIMIT 1";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
        return [
            'idUsuario' => $usuario['idUsuario'],
            'nombre'    => $usuario['nombre'],
            'idRol'     => $usuario['idRol']
        ];
    }

    return false;
}


    public function getUserById(int $id): array|false {
        $sql = "SELECT * FROM {$this->table} WHERE idUsuario = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateUser($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nombre'])) { $fields[] = 'nombre = :nombre'; $params[':nombre'] = $data['nombre']; }
        if (isset($data['correo'])) { $fields[] = 'correo = :correo'; $params[':correo'] = $data['correo']; }
        if (isset($data['telefono'])) { $fields[] = 'telefono = :telefono'; $params[':telefono'] = $data['telefono']; }
        if (isset($data['biografia'])) { $fields[] = 'biografia = :biografia'; $params[':biografia'] = $data['biografia']; }
        if (isset($data['vehiculo'])) { $fields[] = 'vehiculo = :vehiculo'; $params[':vehiculo'] = $data['vehiculo']; }
        if (isset($data['institucion'])) { $fields[] = 'institucion = :institucion'; $params[':institucion'] = $data['institucion']; }
        if (isset($data['ciudad'])) { $fields[] = 'ciudad = :ciudad'; $params[':ciudad'] = $data['ciudad']; }
        if (isset($data['foto_perfil'])) { $fields[] = 'foto_perfil = :foto_perfil'; $params[':foto_perfil'] = $data['foto_perfil']; }
        if (isset($data['visibilidad_perfil'])) { $fields[] = 'visibilidad_perfil = :visibilidad_perfil'; $params[':visibilidad_perfil'] = $data['visibilidad_perfil']; }
        if (isset($data['visibilidad_telefono'])) { $fields[] = 'visibilidad_telefono = :visibilidad_telefono'; $params[':visibilidad_telefono'] = $data['visibilidad_telefono']; }
        if (isset($data['notificaciones_email'])) { $fields[] = 'notificaciones_email = :notificaciones_email'; $params[':notificaciones_email'] = $data['notificaciones_email']; }
        if (isset($data['preferencias_viaje'])) { $fields[] = 'preferencias_viaje = :preferencias_viaje'; $params[':preferencias_viaje'] = $data['preferencias_viaje']; }

        if (empty($fields)) return true;

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE idUsuario = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function getPasswordHash(int $userId): string
    {
        $sql = "SELECT contrasena FROM {$this->table} WHERE idUsuario = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['contrasena'] : '';
    }

    public function verifyPassword(int $userId, string $password): bool
    {
        $sql = "SELECT contrasena 
                FROM {$this->table} 
                WHERE idUsuario = :id 
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $userId]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        return password_verify($password, $user['contrasena']);
    }

    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = "UPDATE {$this->table}
                SET contrasena = :contrasena
                WHERE idUsuario = :id";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':contrasena' => $hash,
            ':id' => $userId
        ]);
    }

    public function submitVerification($id, $documentPath) {
        $sql = "UPDATE {$this->table} SET documento_verificacion = :doc, estado_verificacion = 1 WHERE idUsuario = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':doc' => $documentPath, ':id' => $id]);
    }

    public function setVerificationStatus(int $userId, int $status): bool {
        $sql = "UPDATE {$this->table} SET estado_verificacion = :status WHERE idUsuario = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':status' => $status, ':id' => $userId]);
    }

    public function getPendingVerifications(): array {
        $sql = "SELECT idUsuario, nombre, correo, documento_verificacion, creado_en, notificaciones_email
                FROM {$this->table}
                WHERE estado_verificacion = 1
                ORDER BY creado_en ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(): int
    {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM usuarios");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) $row['total'];
    }

     
    public function getUserByEmail(string $email): array|false {
        $sql = "SELECT idUsuario, nombre, correo FROM {$this->table} WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':correo' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Crear código de reseteo
    public function createResetCode(int $userId): string|false {
        try {
            $this->conn->prepare("DELETE FROM password_resets WHERE user_id = ?")
                ->execute([$userId]);

            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $stmt = $this->conn->prepare("
                INSERT INTO password_resets (user_id, code, expires_at)
                VALUES (?, ?, ?)
            ");

            $stmt->execute([$userId, $code, $expiresAt]);

            return $code;

        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }


    // Validar codigo
    public function validateResetCode(string $code): array|false {

        $stmt = $this->conn->prepare("
            SELECT pr.user_id, u.nombre, u.correo
            FROM password_resets pr
            JOIN usuarios u ON u.idUsuario = pr.user_id
            WHERE pr.code = ?
            AND pr.expires_at > NOW()
            LIMIT 1
        ");

        $stmt->execute([$code]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Eliminar cuenta de usuario y todos sus datos asociados
    public function deleteAccount(int $userId): bool {
        try {
            $this->conn->beginTransaction();

            // Eliminar valoraciones donde participa
            $this->conn->prepare("DELETE FROM valoraciones WHERE idValorador = ? OR idValorado = ?")->execute([$userId, $userId]);

            // Eliminar mensajes
            $this->conn->prepare("DELETE FROM mensajes WHERE idEmisor = ? OR idReceptor = ?")->execute([$userId, $userId]);

            // Eliminar conversaciones
            $this->conn->prepare("DELETE FROM conversations WHERE user1_id = ? OR user2_id = ?")->execute([$userId, $userId]);

            // Eliminar viajes/reservas
            $this->conn->prepare("DELETE FROM viajes WHERE idConductor = ? OR idPasajero = ?")->execute([$userId, $userId]);

            // Eliminar anuncios
            $this->conn->prepare("DELETE FROM anuncios WHERE idUsuario = ?")->execute([$userId]);

            // Eliminar reportes donde participa
            $this->conn->prepare("DELETE FROM reportes WHERE idUsuarioReportado = ? OR idUsuarioQueReporta = ?")->execute([$userId, $userId]);

            // Eliminar notificaciones
            $this->conn->prepare("DELETE FROM notificaciones WHERE idUsuario = ?")->execute([$userId]);

            // Eliminar password resets
            $this->conn->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$userId]);

            // Eliminar usuario
            $this->conn->prepare("DELETE FROM {$this->table} WHERE idUsuario = ?")->execute([$userId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error eliminando cuenta $userId: " . $e->getMessage());
            return false;
        }
    }

    // Banear/suspender usuario
    public function banUser(int $userId, string $motivo, ?string $hasta = null): bool {
        $sql = "UPDATE {$this->table} SET baneado = 1, ban_motivo = :motivo, ban_hasta = :hasta WHERE idUsuario = :id AND idRol != 1";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':motivo' => $motivo, ':hasta' => $hasta, ':id' => $userId]);
    }

    // Desbanear usuario
    public function unbanUser(int $userId): bool {
        $sql = "UPDATE {$this->table} SET baneado = 0, ban_motivo = NULL, ban_hasta = NULL WHERE idUsuario = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $userId]);
    }

    // Comprobar si un usuario está baneado (y desbanear si expiró)
    public function isBanned(int $userId): array|false {
        $sql = "SELECT baneado, ban_motivo, ban_hasta FROM {$this->table} WHERE idUsuario = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !$row['baneado']) return false;

        // Si el ban tiene fecha límite y ya expiró, desbanear automáticamente
        if ($row['ban_hasta'] && strtotime($row['ban_hasta']) < time()) {
            $this->unbanUser($userId);
            return false;
        }

        return $row;
    }

    // Obtener usuarios baneados
    public function getBannedUsers(): array {
        $sql = "SELECT idUsuario, nombre, correo, ban_motivo, ban_hasta, creado_en
                FROM {$this->table}
                WHERE baneado = 1 AND idRol != 1
                ORDER BY idUsuario DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Resetear contraseña con código
    public function resetPasswordWithCode(int $userId, string $password): bool {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->conn->prepare("
            UPDATE usuarios SET contrasena = ? WHERE idUsuario = ?
        ")->execute([$hash, $userId]);

        $this->conn->prepare("
            DELETE FROM password_resets WHERE user_id = ?
        ")->execute([$userId]);

        return true;
    }
}


