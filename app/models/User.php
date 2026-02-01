<?php

class User {

    private PDO $conn;
    private string $table = 'usuarios';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function register(string $nombre, string $correo, string $password, int $idRol = 2): bool {

        $sql = "INSERT INTO {$this->table} (nombre, correo, contrasena, idRol)
                VALUES (:nombre, :correo, :contrasena, :idRol)";

        $stmt = $this->conn->prepare($sql);

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        return $stmt->execute([
            ':nombre'     => htmlspecialchars(strip_tags($nombre)),
            ':correo'     => htmlspecialchars(strip_tags($correo)),
            ':contrasena' => $hashedPassword,
            ':idRol'      => $idRol
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
}
