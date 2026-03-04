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


