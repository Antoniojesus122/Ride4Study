<?php
require_once __DIR__ . '/app/controllers/RideController.php';

$controller = new RideController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->store();
} else {
    $controller->create();
}