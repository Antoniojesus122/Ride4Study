<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->connect();

try {
    $sql = "ALTER TABLE usuarios
            ADD COLUMN visibilidad_perfil ENUM('public', 'registered', 'private') DEFAULT 'public',
            ADD COLUMN visibilidad_telefono ENUM('public', 'rides_only', 'private') DEFAULT 'rides_only',
            ADD COLUMN notificaciones_email TINYINT(1) DEFAULT 1";
    
    $db->exec($sql);
    echo "Columns added successfully.";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
