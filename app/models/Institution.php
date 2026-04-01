<?php
class Institution {
    private PDO $conn;
    private string $table = 'instituciones';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function countAll(): int {
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM {$this->table}");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$row['total'];
    }

    public function getAll(): array {
        $stmt = $this->conn->query("SELECT * FROM {$this->table} ORDER BY creado_en DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE idInstitucion = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int|false {
        $sql = "INSERT INTO {$this->table} (nombre, correo, telefono, direccion, logo, descripcion, contrasena, activo)
                VALUES (:nombre, :correo, :telefono, :direccion, :logo, :descripcion, :contrasena, :activo)";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            ':nombre' => $data['nombre'],
            ':correo' => $data['correo'],
            ':telefono' => $data['telefono'] ?? null,
            ':direccion' => $data['direccion'] ?? null,
            ':logo' => $data['logo'] ?? null,
            ':descripcion' => $data['descripcion'] ?? null,
            ':contrasena' => $data['contrasena'] ?? null,
            ':activo' => $data['activo'] ?? 1,
        ]);
        return $result ? (int)$this->conn->lastInsertId() : false;
    }

    // Buscar institucion por correo (para login)
    public function getByEmail(string $email): array|false {
        $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE correo = :correo LIMIT 1");
        $stmt->execute([':correo' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar ultimo acceso
    public function updateLastAccess(int $id): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET ultimo_acceso = NOW() WHERE idInstitucion = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Guardar codigo 2FA
    public function save2FACode(int $id, string $hashedCode): bool {
        $stmt = $this->conn->prepare(
            "UPDATE {$this->table} SET codigo_2fa = :code, expiracion_2fa = DATE_ADD(NOW(), INTERVAL 10 MINUTE), intentos_2fa = 0 WHERE idInstitucion = :id"
        );
        return $stmt->execute([':code' => $hashedCode, ':id' => $id]);
    }

    // Incrementar intentos 2FA
    public function increment2FAAttempts(int $id): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET intentos_2fa = intentos_2fa + 1 WHERE idInstitucion = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Limpiar 2FA
    public function clear2FA(int $id): bool {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET codigo_2fa = NULL, expiracion_2fa = NULL, intentos_2fa = 0 WHERE idInstitucion = :id");
        return $stmt->execute([':id' => $id]);
    }

    // Contar estudiantes vinculados a una institucion
    public function countStudents(string $nombre): int {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM usuarios WHERE institucion = :nombre");
        $stmt->execute([':nombre' => $nombre]);
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function update(int $id, array $data): bool {
        $allowedFields = ['nombre', 'correo', 'telefono', 'direccion', 'logo', 'descripcion', 'contrasena', 'activo', 'ultimo_acceso', 'codigo_2fa', 'expiracion_2fa', 'intentos_2fa'];
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            if (!in_array($key, $allowedFields, true)) {
                continue;
            }
            $fields[] = "`$key` = :$key";
            $params[":$key"] = $value;
        }

        if (empty($fields)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE idInstitucion = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE idInstitucion = :id");
        return $stmt->execute([':id' => $id]);
    }
}
