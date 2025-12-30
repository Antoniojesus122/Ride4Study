<?php
require_once __DIR__ . '/app/controllers/MessageController.php';

$controller = new MessageController();

if (isset($_GET['action']) && $_GET['action'] === 'delete_conversation') {
    $controller->deleteConversation();
} else {
    $controller->index();
}