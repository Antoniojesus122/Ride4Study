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

    public function create(array $data): bool {
        $sql = "INSERT INTO {$this->table} (nombre, correo, telefono, direccion, logo, descripcion)
                VALUES (:nombre, :correo, :telefono, :direccion, :logo, :descripcion)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':correo' => $data['correo'],
            ':telefono' => $data['telefono'] ?? null,
            ':direccion' => $data['direccion'] ?? null,
            ':logo' => $data['logo'] ?? null,
            ':descripcion' => $data['descripcion'] ?? null,
        ]);
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            $fields[] = "$key = :$key";
            $params[":$key"] = $value;
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
