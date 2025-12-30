<?php
require_once __DIR__ . '/app/controllers/UserController.php';

$controller = new UserController();
$action = $_GET['action'] ?? null;

if ($action === 'update') {
    $controller->update();
} elseif ($action === 'change_password') {
    $controller->changePassword();
} elseif ($action === 'verify') {
    $controller->verify();
} elseif ($action === 'update_privacy') {
    $controller->updatePrivacy();
} else {
    $controller->index();
}