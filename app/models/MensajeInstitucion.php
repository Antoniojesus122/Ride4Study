<?php
// Modelo para la mensajeria admin <-> institucion
// Tabla: mensajes_instituciones (idMensaje, idInstitucion, idAdmin, asunto, mensaje, emisor, leido, creado_en)

class MensajeInstitucion {
    private PDO $db;
    private string $table = 'mensajes_instituciones';

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    // Listado de instituciones con resumen de conversacion (ultimo mensaje + no leidos)
    public function listarBandeja(string $search = ''): array {
        $sql = "SELECT i.idInstitucion, i.nombre, i.correo, i.logo, i.activo,
                    (SELECT COUNT(*) FROM {$this->table} m
                        WHERE m.idInstitucion = i.idInstitucion
                          AND m.emisor = 'institucion'
                          AND m.leido = 0) AS no_leidos,
                    (SELECT COUNT(*) FROM {$this->table} m
                        WHERE m.idInstitucion = i.idInstitucion) AS total_mensajes,
                    (SELECT creado_en FROM {$this->table} m
                        WHERE m.idInstitucion = i.idInstitucion
                        ORDER BY creado_en DESC LIMIT 1) AS ultima_fecha,
                    (SELECT asunto FROM {$this->table} m
                        WHERE m.idInstitucion = i.idInstitucion
                        ORDER BY creado_en DESC LIMIT 1) AS ultimo_asunto,
                    (SELECT emisor FROM {$this->table} m
                        WHERE m.idInstitucion = i.idInstitucion
                        ORDER BY creado_en DESC LIMIT 1) AS ultimo_emisor
                FROM instituciones i
                WHERE 1=1 ";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (i.nombre LIKE :s OR i.correo LIKE :s) ";
            $params[':s'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY (ultima_fecha IS NULL), ultima_fecha DESC, i.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Hilos de una institucion agrupados por asunto
    public function listarHilosInstitucion(int $idInstitucion): array {
        $sql = "SELECT asunto,
                    COUNT(*) AS total,
                    SUM(CASE WHEN emisor = 'institucion' AND leido = 0 THEN 1 ELSE 0 END) AS no_leidos,
                    MAX(creado_en) AS ultima_fecha,
                    SUBSTRING_INDEX(GROUP_CONCAT(emisor ORDER BY creado_en DESC SEPARATOR '|'), '|', 1) AS ultimo_emisor
                FROM {$this->table}
                WHERE idInstitucion = :id
                GROUP BY asunto
                ORDER BY ultima_fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idInstitucion]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mensajes de un hilo concreto (institucion + asunto)
    public function obtenerHilo(int $idInstitucion, string $asunto): array {
        $sql = "SELECT m.*, u.nombre AS admin_nombre
                FROM {$this->table} m
                LEFT JOIN usuarios u ON m.idAdmin = u.idUsuario
                WHERE m.idInstitucion = :id AND m.asunto = :asunto
                ORDER BY m.creado_en ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idInstitucion, ':asunto' => $asunto]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear mensaje (emisor admin siempre desde el panel)
    public function enviar(int $idInstitucion, int $idAdmin, string $asunto, string $mensaje, string $emisor = 'admin'): int|false {
        $sql = "INSERT INTO {$this->table} (idInstitucion, idAdmin, asunto, mensaje, emisor)
                VALUES (:id, :admin, :asunto, :mensaje, :emisor)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([
            ':id'      => $idInstitucion,
            ':admin'   => $idAdmin,
            ':asunto'  => $asunto,
            ':mensaje' => $mensaje,
            ':emisor'  => $emisor,
        ]);
        return $ok ? (int)$this->db->lastInsertId() : false;
    }

    // Marcar como leidos los mensajes de la institucion dentro de un asunto
    public function marcarLeidos(int $idInstitucion, string $asunto): void {
        $sql = "UPDATE {$this->table}
                SET leido = 1
                WHERE idInstitucion = :id AND asunto = :asunto AND emisor = 'institucion' AND leido = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idInstitucion, ':asunto' => $asunto]);
    }

    // Contador global de no leidos (para badge en el sidebar/topbar)
    public function totalNoLeidos(): int {
        $stmt = $this->db->query(
            "SELECT COUNT(*) FROM {$this->table} WHERE emisor = 'institucion' AND leido = 0"
        );
        return (int)$stmt->fetchColumn();
    }
}
