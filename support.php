<?php
session_start();
require_once __DIR__ . '/app/controllers/SupportController.php';

$controller = new SupportController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'contact') {
    $controller->sendSupportEmail();
} else {
    $controller->index();
}