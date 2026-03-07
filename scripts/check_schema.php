<?php
require_once __DIR__ . '/../config/database.php';

$database = new Database();
$db = $database->connect();

$stmt = $db->query("SELECT * FROM usuarios LIMIT 1");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

print_r(array_keys($row));
