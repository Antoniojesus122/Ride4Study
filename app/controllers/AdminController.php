<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../models/Institution.php';
require_once __DIR__ . '/../models/Report.php';

class AdminController
{
    private PDO $db;
    private User $userModel;
    private Ride $rideModel;
    private Institution $institutionModel;
    private Report $reportModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db = $database->connect();

        $this->userModel        = new User($this->db);
        $this->rideModel        = new Ride($this->db);
        $this->institutionModel = new Institution($this->db);
        $this->reportModel      = new Report($this->db);
    }

    public function index()
    {
        // Datos reales
        $stats = [
            'users'        => $this->userModel->countAll(),
            'ads'          => $this->rideModel->countAll(),
            'reports'      => $this->reportModel->countAll(),
            'institutions' => $this->institutionModel->countAll()
        ];

        // Cargar layout y dashboard
        require_once __DIR__ . '/../../views/admin/layout/header.view.php';
        require_once __DIR__ . '/../../views/admin/layout/sidebar.view.php';
        require_once __DIR__ . '/../../views/admin/dashboard.view.php';
        require_once __DIR__ . '/../../views/admin/layout/footer.view.php';
    }
}
