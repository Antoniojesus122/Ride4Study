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

        // Añadir estado de reserva a los viajes
        $userBookings = $this->ride->getUserBookings($_SESSION['user_id']);
        foreach ($rides as &$ride) {
            $ride['booking_status'] = $userBookings[$ride['idAnuncio']] ?? null;
        }
        unset($ride);

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
        
        // Recoger viajes publicados por el usuario
        $myRidesData = $this->ride->getRidesByUserId($userId);
        $activeRides = $myRidesData['active'];
        $pastRides = $myRidesData['past'];

        // Obtener reservas donde el usuario es pasajero
        $passengerBookings = $this->ride->getPassengerBookings($userId);
        
        // Separar en activo y pasado los viajes según la fecha
        $currentDate = date('Y-m-d H:i:s');
        $activeBookings = [];
        $pastBookings = [];
        
        foreach ($passengerBookings as $booking) {
            $rideDateTime = $booking['fechaSalida'] . ' ' . $booking['horaSalida'];
            if ($rideDateTime >= $currentDate) {
                $activeBookings[] = $booking;
            } else {
                $pastBookings[] = $booking;
            }
        }

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

        // Validación de inputs
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

        // Validaciones básicas
        if (empty($data['tipo']) || empty($data['origen']) || empty($data['destino']) || 
            empty($data['fechaSalida']) || empty($data['horaSalida']) || empty($data['plazasDisponibles'])) {
            $errors[] = 'Todos los campos obligatorios deben ser completados.';
        }

        // Validaciones lógicas
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

        // Creación de viaje
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
        if (!$rideId) {
            header('Location: dashboard.php');
            exit;
        }

        // Recoger detalles del viaje
        $ride = $this->ride->getRideById($rideId);
        
        if (!$ride) {
            header('Location: dashboard.php?error=not_found');
            exit;
        }

        // Evitar la reserva de viajes de tipo busco
        if (strtolower($ride['tipo']) === 'busco') {
            header('Location: dashboard.php?error=invalid_type');
            exit;
        }

        // Evitar reservar viaje propio
        if ($ride['idUsuario'] == $_SESSION['user_id']) {
            header('Location: dashboard.php?error=own_ride');
            exit;
        }

        // Evitar la doble reserva
        if ($this->ride->hasBooking($rideId, $_SESSION['user_id'])) {
            header('Location: dashboard.php?error=already_booked');
            exit;
        }

        // Verificar disponibilidad de plazas
        if ($ride['plazasDisponibles'] <= 0) {
            header('Location: dashboard.php?error=no_seats');
            exit;
        }

        if ($this->ride->requestReservation($rideId, $_SESSION['user_id'])) {
            /*
            Enviar notificación por email al conductor
            try {
                $this->sendReservationNotification($ride, $_SESSION['user_id'], 'nueva');
            } catch (Exception $e) {
                error_log("Error enviando notificación: " . $e->getMessage());
                // Continuar aunque falle el email
            }*/
            
            header('Location: my-rides.php?success=reserved');
        } else {
            header('Location: dashboard.php?error=reservation_failed');
        }
    }

    public function manageRequest() {
        if (!isset($_SESSION['user_id'])) {
            exit;
        }

        $rideId = $_POST['ride_id'] ?? null;
        $passengerId = $_POST['passenger_id'] ?? null;
        $action = $_POST['action'] ?? null;

        if (!$rideId || !$passengerId || !$action) {
             header('Location: my-rides.php?error=missing_params');
             exit;
        }

        $ride = $this->ride->getRideById($rideId);
        if (!$ride || $ride['idUsuario'] != $_SESSION['user_id']) {
             header('Location: my-rides.php?error=unauthorized');
             exit;
        }

        $status = ($action === 'accept') ? 'aceptado' : 'rechazado';

        if ($this->ride->updateReservationStatus($rideId, $passengerId, $status)) {
             // Enviar notificación al pasajero
             /*$this->sendReservationNotification($ride, $passengerId, $status);*/
             
             header('Location: my-rides.php?success=status_updated&action=' . $action);
        } else {
             header('Location: my-rides.php?error=update_failed');
        }
    }

    // Cancelar una reserva
    public function cancelReservation() {
        if (!isset($_SESSION['user_id'])) {
            exit;
        }

        $rideId = $_POST['ride_id'] ?? null;
        
        if (!$rideId) {
            header('Location: my-rides.php?error=missing_params');
            exit;
        }

        // Verificar que el usuario tiene una reserva activa
        $booking = $this->ride->hasBooking($rideId, $_SESSION['user_id']);
        
        if (!$booking) {
            header('Location: my-rides.php?error=no_booking');
            exit;
        }

        // No permitir cancelar reservas aceptadas a menos que falten más de 24h
        if ($booking['estado'] === 'aceptado') {
            $ride = $this->ride->getRideById($rideId);
            $rideDateTime = strtotime($ride['fechaSalida'] . ' ' . $ride['horaSalida']);
            $now = time();
            $hoursUntilRide = ($rideDateTime - $now) / 3600;
            
            if ($hoursUntilRide < 24) {
                header('Location: my-rides.php?error=too_late_to_cancel');
                exit;
            }
        }

        if ($this->ride->cancelReservation($rideId, $_SESSION['user_id'])) {
            // Notificar al conductor
            $ride = $this->ride->getRideById($rideId);
            /*$this->sendReservationNotification($ride, $_SESSION['user_id'], 'cancelada');*/
            
            header('Location: my-rides.php?success=reservation_cancelled');
        } else {
            header('Location: my-rides.php?error=cancel_failed');
        }
    }

    /* Enviar notificaciones por email
    private function sendReservationNotification($ride, $userId, $type) {
        // Obtener información del usuario
        $stmt = $this->db->prepare("SELECT nombre, correo FROM usuarios WHERE idUsuario = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) return;

        $subject = '';
        $message = '';
        $to = '';

        switch ($type) {
            case 'nueva':
                // Notificar al conductor sobre nueva solicitud
                $stmt = $this->db->prepare("SELECT nombre, correo FROM usuarios WHERE idUsuario = :id");
                $stmt->execute([':id' => $ride['idUsuario']]);
                $conductor = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $to = $conductor['correo'];
                $subject = '🚗 Nueva solicitud de reserva - Ride4Study';
                $message = "Hola {$conductor['nombre']},\n\n";
                $message .= "{$user['nombre']} ha solicitado una plaza en tu viaje:\n";
                $message .= "📍 Desde: {$ride['nombreOrigen']}\n";
                $message .= "📍 Hasta: {$ride['nombreDestino']}\n";
                $message .= "📅 Fecha: " . date('d/m/Y', strtotime($ride['fechaSalida'])) . "\n";
                $message .= "🕐 Hora: " . substr($ride['horaSalida'], 0, 5) . "\n\n";
                $message .= "Entra en tu panel para aceptar o rechazar la solicitud.\n\n";
                $message .= "Saludos,\nEl equipo de Ride4Study";
                break;

            case 'aceptado':
                $to = $user['correo'];
                $subject = '✅ Tu reserva ha sido aceptada - Ride4Study';
                $message = "¡Buenas noticias, {$user['nombre']}!\n\n";
                $message .= "Tu solicitud de reserva ha sido aceptada para el viaje:\n";
                $message .= "📍 Desde: {$ride['nombreOrigen']}\n";
                $message .= "📍 Hasta: {$ride['nombreDestino']}\n";
                $message .= "📅 Fecha: " . date('d/m/Y', strtotime($ride['fechaSalida'])) . "\n";
                $message .= "🕐 Hora: " . substr($ride['horaSalida'], 0, 5) . "\n\n";
                $message .= "Ponte en contacto con el conductor para coordinar los detalles.\n\n";
                $message .= "¡Buen viaje!\nEl equipo de Ride4Study";
                break;

            case 'rechazado':
                $to = $user['correo'];
                $subject = '❌ Actualización de tu reserva - Ride4Study';
                $message = "Hola {$user['nombre']},\n\n";
                $message .= "Lamentamos informarte que tu solicitud de reserva no ha sido aceptada para el viaje:\n";
                $message .= "📍 Desde: {$ride['nombreOrigen']}\n";
                $message .= "📍 Hasta: {$ride['nombreDestino']}\n\n";
                $message .= "No te preocupes, puedes buscar otros viajes disponibles en la plataforma.\n\n";
                $message .= "Saludos,\nEl equipo de Ride4Study";
                break;

            case 'cancelada':
                $stmt = $this->db->prepare("SELECT nombre, correo FROM usuarios WHERE idUsuario = :id");
                $stmt->execute([':id' => $ride['idUsuario']]);
                $conductor = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $to = $conductor['correo'];
                $subject = '🔔 Reserva cancelada - Ride4Study';
                $message = "Hola {$conductor['nombre']},\n\n";
                $message .= "{$user['nombre']} ha cancelado su reserva para tu viaje:\n";
                $message .= "📍 Desde: {$ride['nombreOrigen']}\n";
                $message .= "📍 Hasta: {$ride['nombreDestino']}\n";
                $message .= "📅 Fecha: " . date('d/m/Y', strtotime($ride['fechaSalida'])) . "\n\n";
                $message .= "La plaza vuelve a estar disponible.\n\n";
                $message .= "Saludos,\nEl equipo de Ride4Study";
                break;
        }

        Por hacer todavía
    } */


    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $rideId = $_GET['id'] ?? null;
        if (!$rideId) {
            header('Location: my-rides.php');
            exit;
        }

        $ride = $this->ride->getRideById($rideId);

        if (!$ride || $ride['idUsuario'] != $_SESSION['user_id']) {
            header('Location: my-rides.php?error=unauthorized');
            exit;
        }

        $locations = $this->ride->getAllLocations();
        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
        
        require_once __DIR__ . '/../../views/user/publish.view.php';
    }

    public function update() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $rideId = $_POST['ride_id'] ?? null;
        if (!$rideId) {
            header('Location: my-rides.php');
            exit;
        }

        $ride = $this->ride->getRideById($rideId);
        if (!$ride || $ride['idUsuario'] != $_SESSION['user_id']) {
            header('Location: my-rides.php?error=unauthorized');
            exit;
        }

        $errors = [];
        $data = [
            'origen' => $_POST['origen'] ?? '',
            'destino' => $_POST['destino'] ?? '',
            'fechaSalida' => $_POST['fechaSalida'] ?? '',
            'horaSalida' => $_POST['horaSalida'] ?? '',
            'horaRegreso' => !empty($_POST['horaRegreso']) ? $_POST['horaRegreso'] : null,
            'plazasDisponibles' => $_POST['plazasDisponibles'] ?? '',
            'precio' => !empty($_POST['precio']) ? $_POST['precio'] : null,
            'descripcion' => $_POST['descripcion'] ?? ''
        ];

        if (empty($data['origen']) || empty($data['destino']) || 
            empty($data['fechaSalida']) || empty($data['horaSalida']) || empty($data['plazasDisponibles'])) {
            $errors[] = 'Todos los campos obligatorios deben ser completados.';
        }

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

        // Actualizar viaje
        if ($this->ride->updateRide($rideId, $data)) {
            header('Location: my-rides.php?success=updated');
        } else {
             $errors[] = 'Error al actualizar el viaje. Inténtalo de nuevo.';
             $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
             $locations = $this->ride->getAllLocations();
             require_once __DIR__ . '/../../views/user/publish.view.php';
        }
    }
}
