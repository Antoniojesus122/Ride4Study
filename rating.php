<?php
require_once __DIR__ . '/app/controllers/RatingController.php';

$controller = new RatingController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->submit();
} else {
    // Mostrar formulario de valoración si se pasa parámetro 'viaje'
    if (isset($_GET['viaje'])) {
        $controller->showRatingForm();
    } else {
        header('Location: dashboard.php');
        exit;
    }
}
