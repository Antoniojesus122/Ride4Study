<?php
require_once __DIR__ . '/app/controllers/MessageController.php';

$controller = new MessageController();
$action     = $_GET['action'] ?? null;

if ($action === 'send') {
    $controller->send();
} elseif ($action === 'edit') {
    $controller->edit();
} elseif ($action === 'delete') {
    $controller->delete();
} elseif ($action === 'fetch_messages') {
    $controller->fetchMessages();
} elseif ($action === 'offer_ride') {
    $controller->offerRide();
} else {
    $controller->chat();
}