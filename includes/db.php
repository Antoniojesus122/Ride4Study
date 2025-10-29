<?php
$host = 'localhost';
$dbname = 'ride4study';
$user = 'root';
$password = '';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, $options);
} catch (PDOException $e) {
    die("Error de conexión: " . htmlspecialchars($e->getMessage())); // Error temporal para producción
    // header('Location: /error.php');
    // exit;
}
?>