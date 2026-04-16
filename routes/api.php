<?php

// Rutas de API (respuestas JSON, autocompletado, etc.)

// API de busqueda de instituciones educativas (autocompletado)
$router->get('/api/instituciones-search', function () {
    require_once __DIR__ . '/../app/controllers/ApiInstitucionesController.php';
    $controller = new ApiInstitucionesController();
    $controller->search();
});
