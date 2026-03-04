<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../../services/MailService.php';

class RideController {
    private $db;
    private $ride;
    private $mailService;

    public function __construct() {
        // Asegurar que la sesión esté iniciada para manejar autenticación
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db = $database->connect();
        $this->ride = new Ride($this->db);
        $this->mailService = new MailService();
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
        $tipo = $_POST['tipo'] ?? '';
        
        $data = [
            'idUsuario' => $_SESSION['user_id'],
            'tipo' => $tipo,
            'origen' => $_POST['origen'] ?? '',
            'destino' => $_POST['destino'] ?? '',
            'fechaSalida' => $_POST['fechaSalida'] ?? '',
            'horaSalida' => $_POST['horaSalida'] ?? '',
            'horaRegreso' => !empty($_POST['horaRegreso']) ? $_POST['horaRegreso'] : null,
            'plazasDisponibles' => ($tipo === 'busco') ? 1 : ($_POST['plazasDisponibles'] ?? ''),
            'precio' => !empty($_POST['precio']) ? $_POST['precio'] : null,
            'descripcion' => $_POST['descripcion'] ?? ''
        ];

        // Validaciones básicas
        if (empty($data['tipo']) || empty($data['origen']) || empty($data['destino']) || 
            empty($data['fechaSalida']) || empty($data['horaSalida'])) {
            $errors[] = 'Todos los campos obligatorios deben ser completados.';
        }
        
        // Validar plazas solo si NO es tipo "busco"
        if ($data['tipo'] !== 'busco' && empty($data['plazasDisponibles'])) {
            $errors[] = 'Debes especificar las plazas disponibles.';
        }

        // Validaciones lógicas
        if ($data['origen'] == $data['destino']) {
             $errors[] = 'El origen y el destino no pueden ser el mismo.';
        }

        if ($data['fechaSalida'] < date('Y-m-d')) {
             $errors[] = 'La fecha de salida no puede ser en el pasado.';
        }
        
        // Validar hora de salida si el viaje es para el mismo día
        if ($data['fechaSalida'] === date('Y-m-d')) {
            $horaActual = date('H:i');
            if ($data['horaSalida'] <= $horaActual) {
                $errors[] = 'Para viajes del mismo día, la hora de salida debe ser posterior a la hora actual.';
            }
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


    // Función para manejar tanto reservas en anuncios tipo "ofrezco" como ofertas en anuncios tipo "busco"
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

        $tipoAnuncio = strtolower($ride['tipo']);

        // Evitar reservar/ofrecer en anuncio propio
        if ($ride['idUsuario'] == $_SESSION['user_id']) {
            header('Location: dashboard.php?error=own_ride');
            exit;
        }

        // Evitar la doble reserva/oferta
        if ($this->ride->hasBooking($rideId, $_SESSION['user_id'])) {
            header('Location: dashboard.php?error=already_booked');
            exit;
        }

        // Verificar si hay plazas disponibles (solo para tipo "ofrezco")
        if ($tipoAnuncio === 'ofrezco' && $ride['plazasDisponibles'] <= 0) {
            header('Location: dashboard.php?error=no_seats');
            exit;
        }

        // Crear reserva/oferta
        if ($this->ride->requestReservation($rideId, $_SESSION['user_id'])) {
            // Enviar notificación por email según el tipo
            try {
                if ($tipoAnuncio === 'ofrezco') {
                    // Notificar al conductor que alguien reservó
                    $this->sendReservationNotification($ride, $_SESSION['user_id'], 'nueva');
                } else {
                    // Notificar al pasajero que alguien ofrece llevarlo
                    $this->sendReservationNotification($ride, $_SESSION['user_id'], 'oferta_nueva');
                }
            } catch (Exception $e) {
                error_log("Error enviando notificación: " . $e->getMessage());
                // Continuar aunque falle el email
            }
            
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
             $this->sendReservationNotification($ride, $passengerId, $status);
             
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
            $this->sendReservationNotification($ride, $_SESSION['user_id'], 'cancelada');
            
            header('Location: my-rides.php?success=reservation_cancelled');
        } else {
            header('Location: my-rides.php?error=cancel_failed');
        }
    }

    // Enviar notificaciones por email
    private function sendReservationNotification($ride, $userId, $type) {
        // Obtener información del usuario que hizo la reserva/oferta
        $stmt = $this->db->prepare("SELECT nombre, correo FROM usuarios WHERE idUsuario = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) return;

        $subject = '';
        $message = '';
        $to = '';
        $toName = '';

        switch ($type) {
            case 'nueva':
                // Alguien reserva en anuncio tipo "ofrezco"
                // Notificar al conductor sobre nueva solicitud
                $stmt = $this->db->prepare("SELECT nombre, correo FROM usuarios WHERE idUsuario = :id");
                $stmt->execute([':id' => $ride['idUsuario']]);
                $conductor = $stmt->fetch(PDO::FETCH_ASSOC);

                $to = $conductor['correo'];
                $toName = $conductor['nombre'];
                $subject = 'Nueva solicitud de reserva - Ride4Study';
                
                $contenido = "
                    <p><strong>{$user['nombre']}</strong> ha solicitado una plaza en tu viaje.</p>
                    
                    <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Origen:</strong> {$ride['nombreOrigen']}</p>
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Destino:</strong> {$ride['nombreDestino']}</p>
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Fecha:</strong> " . date('d/m/Y', strtotime($ride['fechaSalida'])) . "</p>
                        <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Hora:</strong> " . substr($ride['horaSalida'], 0, 5) . "</p>
                    </div>
                    
                    <p style=\"color:#94a3b8;\">Entra en tu panel de <strong>Mis Viajes</strong> para aceptar o rechazar la solicitud.</p>
                ";
                
                $message = $this->mailService->generarPlantilla(
                    $conductor['nombre'],
                    "Hola {$conductor['nombre']},",
                    $contenido,
                    null,
                    'http://localhost/Ride4Study/my-rides.php',
                    'Ver Mis Viajes'
                );
                break;

            case 'aceptado':
                $to = $user['correo'];
                $toName = $user['nombre'];
                $subject = 'Tu reserva ha sido aceptada - Ride4Study';
                
                $contenido = "
                    <p>¡Buenas noticias! Tu solicitud de reserva ha sido <strong style=\"color:#34d399;\">aceptada</strong>.</p>
                    
                    <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Origen:</strong> {$ride['nombreOrigen']}</p>
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Destino:</strong> {$ride['nombreDestino']}</p>
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Fecha:</strong> " . date('d/m/Y', strtotime($ride['fechaSalida'])) . "</p>
                        <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Hora:</strong> " . substr($ride['horaSalida'], 0, 5) . "</p>
                    </div>
                    
                    <p style=\"color:#94a3b8;\">Ponte en contacto con el conductor para coordinar los detalles del viaje.</p>
                    <p style=\"color:#34d399; font-weight:bold;\">¡Buen viaje!</p>
                ";
                
                $message = $this->mailService->generarPlantilla(
                    $user['nombre'],
                    "¡Reserva confirmada!",
                    $contenido,
                    null,
                    'http://localhost/Ride4Study/my-rides.php?tab=bookings',
                    'Ver Mis Reservas'
                );
                break;

            case 'rechazado':
                $to = $user['correo'];
                $toName = $user['nombre'];
                $subject = 'Actualización de tu reserva - Ride4Study';
                
                $contenido = "
                    <p>Lamentamos informarte que tu solicitud de reserva no ha sido aceptada para el siguiente viaje:</p>
                    
                    <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">📍 Origen:</strong> {$ride['nombreOrigen']}</p>
                        <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">📍 Destino:</strong> {$ride['nombreDestino']}</p>
                    </div>
                    
                    <p style=\"color:#94a3b8;\">No te preocupes, hay muchos otros viajes disponibles. Sigue buscando en la plataforma para encontrar el viaje perfecto para ti.</p>
                ";
                
                $message = $this->mailService->generarPlantilla(
                    $user['nombre'],
                    "Hola {$user['nombre']},",
                    $contenido,
                    null,
                    'http://localhost/Ride4Study/dashboard.php',
                    'Buscar Otros Viajes'
                );
                break;

            case 'oferta_nueva':
                // Alguien ofrece llevar en anuncio tipo "busco"
                // Notificar al pasajero (publicador) sobre nueva oferta
                $stmt = $this->db->prepare("SELECT nombre, correo FROM usuarios WHERE idUsuario = :id");
                $stmt->execute([':id' => $ride['idUsuario']]);
                $pasajero = $stmt->fetch(PDO::FETCH_ASSOC);

                $to = $pasajero['correo'];
                $toName = $pasajero['nombre'];
                $subject = '¡Alguien puede llevarte! - Ride4Study';
                
                $contenido = "
                    <p>¡Buenas noticias! <strong>{$user['nombre']}</strong> ha ofrecido llevarte en tu viaje.</p>
                    
                    <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Origen:</strong> {$ride['nombreOrigen']}</p>
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Destino:</strong> {$ride['nombreDestino']}</p>
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Fecha:</strong> " . date('d/m/Y', strtotime($ride['fechaSalida'])) . "</p>
                        <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Hora:</strong> " . substr($ride['horaSalida'], 0, 5) . "</p>
                    </div>
                    
                    <p style=\"color:#94a3b8;\">Entra en tu panel de <strong>Mis Viajes</strong> para aceptar o rechazar la oferta.</p>
                ";
                
                $message = $this->mailService->generarPlantilla(
                    $pasajero['nombre'],
                    "Hola {$pasajero['nombre']},",
                    $contenido,
                    null,
                    'http://localhost/Ride4Study/my-rides.php',
                    'Ver Mis Viajes'
                );
                break;

            case 'cancelada':
                $stmt = $this->db->prepare("SELECT nombre, correo FROM usuarios WHERE idUsuario = :id");
                $stmt->execute([':id' => $ride['idUsuario']]);
                $conductor = $stmt->fetch(PDO::FETCH_ASSOC);

                $to = $conductor['correo'];
                $toName = $conductor['nombre'];
                $subject = 'Reserva cancelada - Ride4Study';
                
                $contenido = "
                    <p><strong>{$user['nombre']}</strong> ha cancelado su reserva para tu viaje.</p>
                    
                    <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Origen:</strong> {$ride['nombreOrigen']}</p>
                        <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Destino:</strong> {$ride['nombreDestino']}</p>
                        <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Fecha:</strong> " . date('d/m/Y', strtotime($ride['fechaSalida'])) . "</p>
                    </div>
                    
                    <p style=\"color:#94a3b8;\">La plaza vuelve a estar disponible para otros pasajeros.</p>
                ";
                
                $message = $this->mailService->generarPlantilla(
                    $conductor['nombre'],
                    "Hola {$conductor['nombre']},",
                    $contenido,
                    null,
                    'http://localhost/Ride4Study/my-rides.php',
                    'Ver Mis Viajes'
                );
                break;
        }

        // Enviar correo si hay destinatario definido y tiene las notificaciones activadas
        if ($to && $toName && $subject && $message) {
            $stmt = $this->db->prepare("SELECT notificaciones_email FROM usuarios WHERE correo = :correo LIMIT 1");
            $stmt->execute([':correo' => $to]);
            $pref = $stmt->fetchColumn();
            if ($pref !== false && (int)$pref === 1) {
                $this->mailService->send($to, $toName, $subject, $message);
            }
        }

    }



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

        // Verificar si hay pasajeros con reserva aceptada
        $hasAcceptedPassengers = false;
        if ($ride['tipo'] === 'ofrezco') {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM viajes WHERE idAnuncio = :rideId AND estado = 'aceptado'");
            $stmt->execute([':rideId' => $rideId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasAcceptedPassengers = $result['count'] > 0;
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
        
        // Verificar si se puede cambiar el tipo
        $hasAcceptedPassengers = false;
        if ($ride['tipo'] === 'ofrezco') {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM viajes WHERE idAnuncio = :rideId AND estado = 'aceptado'");
            $stmt->execute([':rideId' => $rideId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $hasAcceptedPassengers = $result['count'] > 0;
        }
        
        // Si hay pasajeros aceptados, mantener el tipo actual; si no, permitir cambio
        $tipo = ($hasAcceptedPassengers) ? $ride['tipo'] : ($_POST['tipo'] ?? $ride['tipo']);
        
        $data = [
            'tipo' => $tipo,
            'origen' => $_POST['origen'] ?? '',
            'destino' => $_POST['destino'] ?? '',
            'fechaSalida' => $_POST['fechaSalida'] ?? '',
            'horaSalida' => $_POST['horaSalida'] ?? '',
            'horaRegreso' => !empty($_POST['horaRegreso']) ? $_POST['horaRegreso'] : null,
            'plazasDisponibles' => ($tipo === 'busco') ? 1 : ($_POST['plazasDisponibles'] ?? ''),
            'precio' => !empty($_POST['precio']) ? $_POST['precio'] : null,
            'descripcion' => $_POST['descripcion'] ?? ''
        ];

        if (empty($data['origen']) || empty($data['destino']) || 
            empty($data['fechaSalida']) || empty($data['horaSalida'])) {
            $errors[] = 'Todos los campos obligatorios deben ser completados.';
        }
        
        // Validar plazas solo si NO es tipo "busco"
        if ($tipo !== 'busco' && empty($data['plazasDisponibles'])) {
            $errors[] = 'Debes especificar las plazas disponibles.';
        }

        if ($data['origen'] == $data['destino']) {
             $errors[] = 'El origen y el destino no pueden ser el mismo.';
        }

        if ($data['fechaSalida'] < date('Y-m-d')) {
             $errors[] = 'La fecha de salida no puede ser en el pasado.';
        }
        
        // Validar hora de salida si el viaje es para el mismo día
        if ($data['fechaSalida'] === date('Y-m-d')) {
            $horaActual = date('H:i');
            if ($data['horaSalida'] <= $horaActual) {
                $errors[] = 'Para viajes del mismo día, la hora de salida debe ser posterior a la hora actual.';
            }
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

    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $rideId = $_GET['id'] ?? null;
        $userId = $_SESSION['user_id'];

        if (!$rideId) {
            header('Location: my-rides.php?error=missing_id');
            exit;
        }

        // Verificar que el usuario sea el dueño del viaje
        $ride = $this->ride->getRideById($rideId);
        if (!$ride || $ride['idUsuario'] != $userId) {
            header('Location: my-rides.php?error=unauthorized');
            exit;
        }

        // Eliminar viaje
        if ($this->ride->deleteRide($rideId)) {
            header('Location: my-rides.php?success=deleted');
        } else {
            header('Location: my-rides.php?error=delete_failed');
        }
    }
}
