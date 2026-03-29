<?php

class Config {

    private PDO $conn;
    private string $table = 'configuracion';
    private static array $cache = [];

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    public function get(string $clave): mixed {
        if (array_key_exists($clave, self::$cache)) {
            return self::$cache[$clave];
        }

        $sql = "SELECT valor, tipo FROM {$this->table} WHERE clave = :clave LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':clave', $clave);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $value = $this->castValue($row['valor'], $row['tipo']);
        self::$cache[$clave] = $value;

        return $value;
    }

    public function getAll(): array {
        $sql = "SELECT clave, valor, tipo, descripcion, actualizado_en FROM {$this->table}";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function set(string $clave, string $valor): bool {
        $sql = "UPDATE {$this->table} SET valor = :valor WHERE clave = :clave";
        $stmt = $this->conn->prepare($sql);
        $result = $stmt->execute([
            ':valor' => $valor,
            ':clave' => $clave
        ]);

        if ($result) {
            unset(self::$cache[$clave]);
        }

        return $result;
    }

    public function setMultiple(array $values): bool {
        foreach ($values as $clave => $valor) {
            if (!$this->set($clave, $valor)) {
                return false;
            }
        }

        return true;
    }

    public function getGrouped(): array {
        $rows = $this->getAll();
        $grouped = [];

        $prefixMap = [
            'premium' => 'Premium',
            'max'     => 'Limites',
        ];

        foreach ($rows as $row) {
            $parts = explode('_', $row['clave'], 2);
            $prefix = $parts[0];

            $group = $prefixMap[$prefix] ?? ucfirst($prefix);
            $grouped[$group][] = $row;
        }

        return $grouped;
    }

    private function castValue(string $valor, ?string $tipo): mixed {
        return match ($tipo) {
            'int'    => intval($valor),
            'bool'   => (bool) $valor,
            'json'   => json_decode($valor, true),
            default  => $valor,
        };
    }
}
