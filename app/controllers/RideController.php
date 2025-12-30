<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Ride.php';

class RideController {
    private $db;
    private $ride;

    public function __construct() {
        // Asegurar que la sesión esté iniciada para manejar autenticación
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db = $database->connect();
        $this->ride = new Ride($this->db);
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $filters = [
            'origen' => $_GET['origen'] ?? '',
            'destino' => $_GET['destino'] ?? '',
            'fecha' => $_GET['fecha'] ?? '',
            'tipo' => $_GET['tipo'] ?? ''
        ];

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 6; // 6 items por página

        $data = $this->ride->getPaginatedRides($page, $limit, $filters, $_SESSION['user_id']);
        
        $rides = $data['rides'];
        $totalPages = $data['pages'];
        $currentPage = $page;
        $totalItems = $data['total'];

        // Variables para la vista
        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
        
        require_once __DIR__ . '/../../views/user/dashboard.view.php';
    }

    public function myRides() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
        
        $myRidesData = $this->ride->getRidesByUserId($userId);
        
        $activeRides = $myRidesData['active'];
        $pastRides = $myRidesData['past'];

        require_once __DIR__ . '/../../views/user/my-rides.view.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }
        
        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
        $locations = $this->ride->getAllLocations();
        
        require_once __DIR__ . '/../../views/user/publish.view.php';
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        // Validate Inputs
        $errors = [];
        $data = [
            'idUsuario' => $_SESSION['user_id'],
            'tipo' => $_POST['tipo'] ?? '',
            'origen' => $_POST['origen'] ?? '',
            'destino' => $_POST['destino'] ?? '',
            'fechaSalida' => $_POST['fechaSalida'] ?? '',
            'horaSalida' => $_POST['horaSalida'] ?? '',
            'horaRegreso' => !empty($_POST['horaRegreso']) ? $_POST['horaRegreso'] : null,
            'plazasDisponibles' => $_POST['plazasDisponibles'] ?? '',
            'precio' => !empty($_POST['precio']) ? $_POST['precio'] : null,
            'descripcion' => $_POST['descripcion'] ?? ''
        ];

        // Basic Validation
        if (empty($data['tipo']) || empty($data['origen']) || empty($data['destino']) || 
            empty($data['fechaSalida']) || empty($data['horaSalida']) || empty($data['plazasDisponibles'])) {
            $errors[] = 'Todos los campos obligatorios deben ser completados.';
        }

        // Logic Validation
        if ($data['origen'] == $data['destino']) {
             $errors[] = 'El origen y el destino no pueden ser el mismo.';
        }

        if ($data['fechaSalida'] < date('Y-m-d')) {
             $errors[] = 'La fecha de salida no puede ser en el pasado.';
        }

        if ($data['horaRegreso']) {
             if ($data['horaRegreso'] <= $data['horaSalida']) {
                  $errors[] = 'La hora de regreso debe ser posterior a la hora de salida.';
             }
        }

        if (!empty($errors)) {
            $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
            $locations = $this->ride->getAllLocations();
            require_once __DIR__ . '/../../views/user/publish.view.php';
            return;
        }

        // Create
        if ($this->ride->createRide($data)) {
            header('Location: my-rides.php?success=created');
        } else {
             $errors[] = 'Error al publicar el viaje. Inténtalo de nuevo.';
             $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
             $locations = $this->ride->getAllLocations();
             require_once __DIR__ . '/../../views/user/publish.view.php';
        }
    }


    public function reserve() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $rideId = $_GET['ride_id'] ?? null;
        if ($rideId) {
             if ($this->ride->requestReservation($rideId, $_SESSION['user_id'])) {
                 header('Location: my-rides.php?success=reserved');
             } else {
                 header('Location: dashboard.php?error=reservation_failed');
             }
        }
    }

    public function manageRequest() {
        if (!isset($_SESSION['user_id'])) {
            exit;
        }

        $rideId = $_POST['ride_id'];
        $passengerId = $_POST['passenger_id'];
        $action = $_POST['action']; // accept, reject

        $status = ($action === 'accept') ? 'aceptado' : 'rechazado';

        if ($this->ride->updateReservationStatus($rideId, $passengerId, $status)) {
             header('Location: my-rides.php');
        } else {
             header('Location: my-rides.php?error=update_failed');
        }
    }
}
