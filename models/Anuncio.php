<?php
require_once __DIR__ . '/../config/db.php';

class Anuncio {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Crear un nuevo anuncio
    public function crear($tipo, $origen, $destino, $horaSalida, $horaRegreso, $precio, $idUsuario) {
        $sql = "INSERT INTO anuncios (tipo, origen, destino, horaSalida, horaRegreso, precio, idUsuario)
                VALUES (:tipo, :origen, :destino, :horaSalida, :horaRegreso, :precio, :idUsuario)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':tipo' => $tipo,
            ':origen' => $origen,
            ':destino' => $destino,
            ':horaSalida' => $horaSalida,
            ':horaRegreso' => $horaRegreso,
            ':precio' => $precio,
            ':idUsuario' => $idUsuario
        ]);
    }

    // Obtener todos los anuncios
    public function obtenerTodos() {
        $sql = "SELECT a.*, u.nombre AS autor, l1.nombreLocalidad AS origenNombre, l2.nombreLocalidad AS destinoNombre
                FROM anuncios a
                JOIN usuarios u ON a.idUsuario = u.idUsuario
                JOIN localidades l1 ON a.origen = l1.idLocalidad
                JOIN localidades l2 ON a.destino = l2.idLocalidad
                ORDER BY a.fechaPublicacion DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener anuncios por usuario
    public function obtenerPorUsuario($idUsuario) {
        $sql = "SELECT * FROM anuncios WHERE idUsuario = :idUsuario";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idUsuario' => $idUsuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener anuncio por ID
    public function obtenerPorId($idAnuncio) {
        $sql = "SELECT * FROM anuncios WHERE idAnuncio = :idAnuncio";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idAnuncio' => $idAnuncio]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar anuncio
    public function actualizar($idAnuncio, $tipo, $origen, $destino, $horaSalida, $horaRegreso, $precio) {
        $sql = "UPDATE anuncios SET tipo=:tipo, origen=:origen, destino=:destino, 
                horaSalida=:horaSalida, horaRegreso=:horaRegreso, precio=:precio 
                WHERE idAnuncio=:idAnuncio";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':tipo' => $tipo,
            ':origen' => $origen,
            ':destino' => $destino,
            ':horaSalida' => $horaSalida,
            ':horaRegreso' => $horaRegreso,
            ':precio' => $precio,
            ':idAnuncio' => $idAnuncio
        ]);
    }

    // Eliminar anuncio
    public function eliminar($idAnuncio) {
        $sql = "DELETE FROM anuncios WHERE idAnuncio = :idAnuncio";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':idAnuncio' => $idAnuncio]);
    }
}
?>