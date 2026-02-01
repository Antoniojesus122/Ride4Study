<?php

class AdminController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        // Aquí luego cargaremos estadísticas reales
        $stats = [
            'users'        => 1245,
            'ads'          => 342,
            'reports'      => 18,
            'institutions' => 27
        ];

        // Layout
        require_once __DIR__ . '/../../views/admin/layout/header.view.php';
        require_once __DIR__ . '/../../views/admin/layout/sidebar.view.php';
        require_once __DIR__ . '/../../views/admin/dashboard.view.php';
        require_once __DIR__ . '/../../views/admin/layout/footer.view.php';
    }
}