<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/Ride.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../../services/MailService.php';

class RideController {
    private $db;
    private $ride;
    private $mailService;
    private Notification $notification;

    public function __construct() {
        // Asegurar que la sesión esté iniciada para manejar autenticación
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $database = new Database();
        $this->db           = $database->connect();
        $this->ride         = new Ride($this->db);
        $this->notification = new Notification($this->db);
        $this->mailService  = new MailService();
    }

    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
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
            header('Location: ' . url('/login'));
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

        // Comprobar si el usuario es premium (para mostrar botón de destacar)
        $premiumStmt = $this->db->prepare("SELECT premium, premium_hasta FROM usuarios WHERE idUsuario = :id");
        $premiumStmt->execute([':id' => $userId]);
        $premiumRow = $premiumStmt->fetch(PDO::FETCH_ASSOC);
        $isPremium = $premiumRow && $premiumRow['premium'] && (!$premiumRow['premium_hasta'] || $premiumRow['premium_hasta'] > date('Y-m-d H:i:s'));

        require_once __DIR__ . '/../../views/user/my-rides.view.php';
    }

    public function create() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        require_once __DIR__ . '/../../views/user/publish.view.php';
    }

    public function store() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        // Validación de inputs
        $errors = [];
        $tipo = $_POST['tipo'] ?? '';

        // Recoger datos de localización del autocompletado (nombre + coordenadas)
        $origenNombre = trim($_POST['origen_nombre'] ?? '');
        $origenLat    = (float)($_POST['origen_lat'] ?? 0);
        $origenLng    = (float)($_POST['origen_lng'] ?? 0);
        $destinoNombre = trim($_POST['destino_nombre'] ?? '');
        $destinoLat    = (float)($_POST['destino_lat'] ?? 0);
        $destinoLng    = (float)($_POST['destino_lng'] ?? 0);

        $data = [
            'idUsuario' => $_SESSION['user_id'],
            'tipo' => $tipo,
            'origen' => '',
            'destino' => '',
            'fechaSalida' => $_POST['fechaSalida'] ?? '',
            'horaSalida' => $_POST['horaSalida'] ?? '',
            'horaLlegada' => null,
            'horaRegreso' => !empty($_POST['horaRegreso']) ? $_POST['horaRegreso'] : null,
            'plazasDisponibles' => ($tipo === 'busco') ? 1 : ($_POST['plazasDisponibles'] ?? ''),
            'precio' => !empty($_POST['precio']) ? $_POST['precio'] : null,
            'descripcion' => $_POST['descripcion'] ?? ''
        ];

        // Validaciones básicas
        if (empty($tipo) || empty($origenNombre) || empty($destinoNombre) ||
            empty($data['fechaSalida']) || empty($data['horaSalida'])) {
            $errors[] = 'Todos los campos obligatorios deben ser completados.';
        }

        // Validar que se hayan seleccionado ciudades del autocompletado (con coordenadas)
        if (!empty($origenNombre) && ($origenLat == 0 && $origenLng == 0)) {
            $errors[] = 'Selecciona una ciudad de origen de la lista de sugerencias.';
        }
        if (!empty($destinoNombre) && ($destinoLat == 0 && $destinoLng == 0)) {
            $errors[] = 'Selecciona una ciudad de destino de la lista de sugerencias.';
        }

        // Validar plazas solo si NO es tipo "busco"
        if ($data['tipo'] !== 'busco' && empty($data['plazasDisponibles'])) {
            $errors[] = 'Debes especificar las plazas disponibles.';
        }

        // Validar longitud de descripción
        if (!empty($data['descripcion']) && mb_strlen($data['descripcion']) > 500) {
            $errors[] = 'La descripción no puede superar los 500 caracteres.';
        }

        // Validaciones lógicas
        if ($origenNombre && $destinoNombre && strtolower($origenNombre) === strtolower($destinoNombre)) {
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
            require_once __DIR__ . '/../../views/user/publish.view.php';
            return;
        }

        // Resolver nombres de ciudad a idLocalidad (buscar o crear en la tabla localidades)
        $data['origen']  = $this->ride->findOrCreateLocation($origenNombre, $origenLat, $origenLng);
        $data['destino'] = $this->ride->findOrCreateLocation($destinoNombre, $destinoLat, $destinoLng);

        $data['horaLlegada'] = $this->calculateArrivalTime($origenLat, $origenLng, $destinoLat, $destinoLng, $data['fechaSalida'], $data['horaSalida']);

        // Comprobar límite de anuncios para usuarios gratuitos (máximo 4 activos)
        $userData = $this->db->prepare("SELECT premium, premium_hasta FROM usuarios WHERE idUsuario = :id");
        $userData->execute([':id' => $_SESSION['user_id']]);
        $user = $userData->fetch(PDO::FETCH_ASSOC);
        $isPremium = $user && $user['premium'] && (!$user['premium_hasta'] || $user['premium_hasta'] > date('Y-m-d H:i:s'));

        if (!$isPremium && $this->ride->getActiveCount((int)$_SESSION['user_id']) >= 4) {
            $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
            $errors[]    = 'Has alcanzado el límite de 4 anuncios activos del plan gratuito. ¡Hazte Premium para publicar ilimitados!';
            require_once __DIR__ . '/../../views/user/publish.view.php';
            return;
        }

        // Creación de viaje
        if ($this->ride->createRide($data)) {
            header('Location: ' . url('/my-rides') . '?success=created');
        } else {
             $errors[] = 'Error al publicar el viaje. Inténtalo de nuevo.';
             $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
             require_once __DIR__ . '/../../views/user/publish.view.php';
        }
    }


    // Función para manejar tanto reservas en anuncios tipo "ofrezco" como ofertas en anuncios tipo "busco"
    public function reserve() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $rideId = $_GET['ride_id'] ?? null;
        if (!$rideId) {
            header('Location: ' . url('/dashboard'));
            exit;
        }

        // Recoger detalles del viaje
        $ride = $this->ride->getRideById($rideId);
        
        if (!$ride) {
            header('Location: ' . url('/dashboard') . '?error=not_found');
            exit;
        }

        $tipoAnuncio = strtolower($ride['tipo']);

        // Evitar reservar/ofrecer en anuncio propio
        if ($ride['idUsuario'] == $_SESSION['user_id']) {
            header('Location: ' . url('/dashboard') . '?error=own_ride');
            exit;
        }

        // Evitar la doble reserva/oferta
        if ($this->ride->hasBooking($rideId, $_SESSION['user_id'])) {
            header('Location: ' . url('/dashboard') . '?error=already_booked');
            exit;
        }

        // Verificar si hay plazas disponibles (solo para tipo "ofrezco")
        if ($tipoAnuncio === 'ofrezco' && $ride['plazasDisponibles'] <= 0) {
            header('Location: ' . url('/dashboard') . '?error=no_seats');
            exit;
        }

        // Crear reserva/oferta
        if ($this->ride->requestReservation($rideId, $_SESSION['user_id'])) {
            $origen  = $ride['nombreOrigen']  ?? 'origen';
            $destino = $ride['nombreDestino'] ?? 'destino';
            $requesterName = $_SESSION['user_name'] ?? 'Un usuario';

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
            }

            // Notificación in-app al dueño del anuncio
            try {
                if ($tipoAnuncio === 'ofrezco') {
                    $this->notification->create(
                        (int)$ride['idUsuario'],
                        htmlspecialchars($requesterName) . ' ' . t('notif.seat_requested') . ' ' . $origen . ' → ' . $destino . '.',
                        'fas fa-user-plus',
                        url('/my-rides') . '?tab=requests'
                    );
                } else {
                    $this->notification->create(
                        (int)$ride['idUsuario'],
                        htmlspecialchars($requesterName) . ' ' . t('notif.ride_offered') . ' ' . $origen . ' → ' . $destino . '.',
                        'fas fa-car',
                        url('/my-rides') . '?tab=requests'
                    );
                }
            } catch (Exception $e) {
                error_log("Error enviando notificación in-app: " . $e->getMessage());
            }

            header('Location: ' . url('/my-rides') . '?success=reserved');
        } else {
            header('Location: ' . url('/dashboard') . '?error=reservation_failed');
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
             header('Location: ' . url('/my-rides') . '?error=missing_params');
             exit;
        }

        $ride = $this->ride->getRideById($rideId);
        if (!$ride || $ride['idUsuario'] != $_SESSION['user_id']) {
             header('Location: ' . url('/my-rides') . '?error=unauthorized');
             exit;
        }

        $status = ($action === 'accept') ? 'aceptado' : 'rechazado';

        if ($this->ride->updateReservationStatus($rideId, $passengerId, $status)) {
             // Enviar email de notificación al pasajero
             $this->sendReservationNotification($ride, $passengerId, $status);

             // Notificación dentro de la aplicación web al pasajero/conductor implicado
             $origen  = $ride['nombreOrigen']  ?? 'origen';
             $destino = $ride['nombreDestino'] ?? 'destino';
             if ($status === 'aceptado') {
                 $this->notification->create(
                     (int)$passengerId,
                     t('notif.request_accepted') . ' ' . $origen . ' → ' . $destino . ' ' . t('notif.accepted_suffix'),
                     'fas fa-check-circle',
                     url('/my-rides') . '?tab=bookings'
                 );
             } else {
                 $this->notification->create(
                     (int)$passengerId,
                     t('notif.request_rejected') . ' ' . $origen . ' → ' . $destino . ' ' . t('notif.rejected_suffix'),
                     'fas fa-times-circle',
                     url('/my-rides') . '?tab=bookings'
                 );
             }

             header('Location: ' . url('/my-rides') . '?success=status_updated&action=' . $action);
        } else {
             header('Location: ' . url('/my-rides') . '?error=update_failed');
        }
    }

    // Cancelar una reserva
    public function cancelReservation() {
        if (!isset($_SESSION['user_id'])) {
            exit;
        }

        $rideId = $_POST['ride_id'] ?? null;
        
        if (!$rideId) {
            header('Location: ' . url('/my-rides') . '?error=missing_params');
            exit;
        }

        // Verificar que el usuario tiene una reserva activa
        $booking = $this->ride->hasBooking($rideId, $_SESSION['user_id']);
        
        if (!$booking) {
            header('Location: ' . url('/my-rides') . '?error=no_booking&tab=bookings');
            exit;
        }

        // No permitir cancelar reservas aceptadas a menos que falten más de 24h
        if ($booking['estado'] === 'aceptado') {
            $ride = $this->ride->getRideById($rideId);
            $rideDateTime = strtotime($ride['fechaSalida'] . ' ' . $ride['horaSalida']);
            $now = time();
            $hoursUntilRide = ($rideDateTime - $now) / 3600;

            if ($hoursUntilRide < 24) {
                header('Location: ' . url('/my-rides') . '?error=too_late_to_cancel&tab=bookings');
                exit;
            }
        }

        if ($this->ride->cancelReservation($rideId, $_SESSION['user_id'])) {
            // Notificar al dueño del anuncio por email
            $ride = $this->ride->getRideById($rideId);
            $this->sendReservationNotification($ride, $_SESSION['user_id'], 'cancelada');

            // Notificación in-app al dueño del anuncio
            try {
                $stmt = $this->db->prepare("SELECT nombre FROM usuarios WHERE idUsuario = :id");
                $stmt->execute([':id' => $_SESSION['user_id']]);
                $canceller = $stmt->fetch(PDO::FETCH_ASSOC);
                $cancellerName = $canceller['nombre'] ?? 'Un usuario';
                $origen  = $ride['nombreOrigen']  ?? 'origen';
                $destino = $ride['nombreDestino'] ?? 'destino';
                $this->notification->create(
                    (int)$ride['idUsuario'],
                    htmlspecialchars($cancellerName) . ' ' . t('notif.reservation_cancelled') . ' ' . $origen . ' → ' . $destino . '.',
                    'fas fa-times-circle',
                    url('/my-rides') . '?tab=requests'
                );
            } catch (Exception $e) {
                error_log("Error enviando notificación in-app: " . $e->getMessage());
            }

            header('Location: ' . url('/my-rides') . '?success=reservation_cancelled&tab=bookings');
        } else {
            header('Location: ' . url('/my-rides') . '?error=cancel_failed&tab=bookings');
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
                    'http://localhost/Ride4Study/my-rides',
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
                    'http://localhost/Ride4Study/dashboard',
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
                    'http://localhost/Ride4Study/my-rides',
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
                    'http://localhost/Ride4Study/my-rides',
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

    // Destacar / quitar destacado de un anuncio (solo usuarios premium)
    public function toggleFeatured() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $rideId = (int)($_POST['ride_id'] ?? 0);
        if ($rideId <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de anuncio inválido']);
            exit;
        }

        // Verificar que el usuario es premium
        $stmt = $this->db->prepare("SELECT premium, premium_hasta FROM usuarios WHERE idUsuario = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $isPremium = $user && $user['premium'] && (!$user['premium_hasta'] || $user['premium_hasta'] > date('Y-m-d H:i:s'));

        if (!$isPremium) {
            echo json_encode(['success' => false, 'message' => 'Esta función es exclusiva de usuarios Premium']);
            exit;
        }

        $result = $this->ride->toggleFeatured($rideId, (int)$_SESSION['user_id']);
        echo json_encode(['success' => $result]);
        exit;
    }

    public function edit() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $rideId = $_GET['id'] ?? null;
        if (!$rideId) {
            header('Location: ' . url('/my-rides'));
            exit;
        }

        $ride = $this->ride->getRideById($rideId);

        if (!$ride || $ride['idUsuario'] != $_SESSION['user_id']) {
            header('Location: ' . url('/my-rides') . '?error=unauthorized');
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

        // Cargar datos de las localidades (origen/destino) para el autocompletado
        $origenLoc  = $this->ride->getLocationById((int)$ride['origen']);
        $destinoLoc = $this->ride->getLocationById((int)$ride['destino']);

        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        require_once __DIR__ . '/../../views/user/publish.view.php';
    }

    public function update() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $rideId = $_POST['ride_id'] ?? null;
        if (!$rideId) {
            header('Location: ' . url('/my-rides'));
            exit;
        }

        $ride = $this->ride->getRideById($rideId);
        if (!$ride || $ride['idUsuario'] != $_SESSION['user_id']) {
            header('Location: ' . url('/my-rides') . '?error=unauthorized');
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

        // Recoger datos de localización del autocompletado
        $origenNombre  = trim($_POST['origen_nombre'] ?? '');
        $origenLat     = (float)($_POST['origen_lat'] ?? 0);
        $origenLng     = (float)($_POST['origen_lng'] ?? 0);
        $destinoNombre = trim($_POST['destino_nombre'] ?? '');
        $destinoLat    = (float)($_POST['destino_lat'] ?? 0);
        $destinoLng    = (float)($_POST['destino_lng'] ?? 0);

        $data = [
            'tipo' => $tipo,
            'origen' => '',
            'destino' => '',
            'fechaSalida' => $_POST['fechaSalida'] ?? '',
            'horaSalida' => $_POST['horaSalida'] ?? '',
            'horaRegreso' => !empty($_POST['horaRegreso']) ? $_POST['horaRegreso'] : null,
            'plazasDisponibles' => ($tipo === 'busco') ? 1 : ($_POST['plazasDisponibles'] ?? ''),
            'precio' => !empty($_POST['precio']) ? $_POST['precio'] : null,
            'descripcion' => $_POST['descripcion'] ?? ''
        ];

        // Validaciones básicas
        if (empty($origenNombre) || empty($destinoNombre) ||
            empty($data['fechaSalida']) || empty($data['horaSalida'])) {
            $errors[] = 'Todos los campos obligatorios deben ser completados.';
        }

        // Validar que se hayan seleccionado ciudades del autocompletado
        if (!empty($origenNombre) && ($origenLat == 0 && $origenLng == 0)) {
            $errors[] = 'Selecciona una ciudad de origen de la lista de sugerencias.';
        }
        if (!empty($destinoNombre) && ($destinoLat == 0 && $destinoLng == 0)) {
            $errors[] = 'Selecciona una ciudad de destino de la lista de sugerencias.';
        }

        // Validar plazas solo si NO es tipo "busco"
        if ($tipo !== 'busco' && empty($data['plazasDisponibles'])) {
            $errors[] = 'Debes especificar las plazas disponibles.';
        }

        // Validar longitud de descripción
        if (!empty($data['descripcion']) && mb_strlen($data['descripcion']) > 500) {
            $errors[] = 'La descripción no puede superar los 500 caracteres.';
        }

        if ($origenNombre && $destinoNombre && strtolower($origenNombre) === strtolower($destinoNombre)) {
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
            // Recargar datos de localidades para el formulario de edición
            $origenLoc  = $this->ride->getLocationById((int)$ride['origen']);
            $destinoLoc = $this->ride->getLocationById((int)$ride['destino']);
            require_once __DIR__ . '/../../views/user/publish.view.php';
            return;
        }

        // Resolver nombres de ciudad a idLocalidad
        $data['origen']  = $this->ride->findOrCreateLocation($origenNombre, $origenLat, $origenLng);
        $data['destino'] = $this->ride->findOrCreateLocation($destinoNombre, $destinoLat, $destinoLng);

        // Calcular hora de llegada con OpenRouteService
        $data['horaLlegada'] = $this->calculateArrivalTime($origenLat, $origenLng, $destinoLat, $destinoLng, $data['fechaSalida'], $data['horaSalida']);

        // Actualizar viaje
        if ($this->ride->updateRide($rideId, $data)) {
            header('Location: ' . url('/my-rides') . '?success=updated');
        } else {
             $errors[] = 'Error al actualizar el viaje. Inténtalo de nuevo.';
             $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
             $origenLoc  = $this->ride->getLocationById((int)$ride['origen']);
             $destinoLoc = $this->ride->getLocationById((int)$ride['destino']);
             require_once __DIR__ . '/../../views/user/publish.view.php';
        }
    }

    /* Calcula la hora de llegada estimada usando OpenRouteService */
    private function calculateArrivalTime(float $origenLat, float $origenLng, float $destinoLat, float $destinoLng, string $fechaSalida, string $horaSalida): ?string {
        if ($origenLat == 0 || $origenLng == 0 || $destinoLat == 0 || $destinoLng == 0) {
            return null;
        }

        $apiKey = 'eyJvcmciOiI1YjNjZTM1OTc4NTExMTAwMDFjZjYyNDgiLCJpZCI6IjkyMTRlZDJiZjMxYTQ4Nzc4NGVkYmVkNGMxNGY4YTdiIiwiaCI6Im11cm11cjY0In0=';
        $url = "https://api.openrouteservice.org/v2/directions/driving-car?api_key={$apiKey}&start={$origenLng},{$origenLat}&end={$destinoLng},{$destinoLat}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json, application/geo+json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode === 200) {
            $routeData = json_decode($response, true);
            if (isset($routeData['features'][0]['properties']['summary']['duration'])) {
                $segundos = $routeData['features'][0]['properties']['summary']['duration'];
                $llegada = new DateTime($fechaSalida . ' ' . $horaSalida);
                $llegada->modify("+" . round($segundos) . " seconds");
                return $llegada->format('H:i:s');
            }
        }

        return null;
    }

    public function delete() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $rideId = $_GET['id'] ?? null;
        $userId = $_SESSION['user_id'];

        if (!$rideId) {
            header('Location: ' . url('/my-rides') . '?error=missing_id');
            exit;
        }

        // Verificar que el usuario sea el dueño del viaje
        $ride = $this->ride->getRideById($rideId);
        if (!$ride || $ride['idUsuario'] != $userId) {
            header('Location: ' . url('/my-rides') . '?error=unauthorized');
            exit;
        }

        // Eliminar viaje
        if ($this->ride->deleteRide($rideId)) {
            header('Location: ' . url('/my-rides') . '?success=deleted');
        } else {
            header('Location: ' . url('/my-rides') . '?error=delete_failed');
        }
    }
}
