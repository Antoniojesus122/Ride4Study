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
            'origen'     => $_GET['origen'] ?? '',
            'destino'    => $_GET['destino'] ?? '',
            'fecha'      => $_GET['fecha'] ?? '',
            'tipo'       => $_GET['tipo'] ?? '',
            'precio_max' => $_GET['precio_max'] ?? '',
            'plazas_min' => $_GET['plazas_min'] ?? '',
            'verificado' => $_GET['verificado'] ?? '',
            'orden'      => $_GET['orden'] ?? '',
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

        // CO2 para el widget lateral
        $myCO2 = $this->ride->calculateUserCO2((int)$_SESSION['user_id']);
        $totalCO2Global = $this->ride->getTotalCO2();

        // Variables para la vista
        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        require_once __DIR__ . '/../../views/user/dashboard.view.php';

        // Pseudo-crons: se ejecutan al cargar el dashboard
        require_once __DIR__ . '/../../scripts/cron_send_rating_notifications.php';
        require_once __DIR__ . '/../../scripts/cron_premium_expiration.php';
        require_once __DIR__ . '/../../scripts/cron_trip_reminders.php';
        require_once __DIR__ . '/../../scripts/cron_update_co2.php';
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

        // Validar tipo de anuncio
        if (!in_array($tipo, ['ofrezco', 'busco'], true)) {
            $errors[] = t('publish.error_invalid_type');
        }

        // Validar precio (no negativo)
        if ($data['precio'] !== null && $data['precio'] !== '') {
            $precio = (float)$data['precio'];
            if ($precio < 0 || $precio > 999) {
                $errors[] = t('publish.error_invalid_price');
            }
        }

        // Validar plazas (1-8, solo para ofrezco)
        if ($tipo === 'ofrezco' && !empty($data['plazasDisponibles'])) {
            $plazas = (int)$data['plazasDisponibles'];
            if ($plazas < 1 || $plazas > 8) {
                $errors[] = t('publish.error_invalid_seats');
            }
        }

        // Validaciones lógicas
        if ($origenNombre && $destinoNombre && strtolower($origenNombre) === strtolower($destinoNombre)) {
             $errors[] = 'El origen y el destino no pueden ser el mismo.';
        }

        if ($data['fechaSalida'] < date('Y-m-d')) {
             $errors[] = 'La fecha de salida no puede ser en el pasado.';
        }

        // No permitir fechas con más de 1 año de antelación
        if ($data['fechaSalida'] > date('Y-m-d', strtotime('+1 year'))) {
            $errors[] = t('publish.error_date_too_far');
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

        // Calcular ruta, hora de llegada, distancia y polyline
        $routeInfo = $this->calculateRouteData($origenLat, $origenLng, $destinoLat, $destinoLng, $data['fechaSalida'], $data['horaSalida']);
        $data['horaLlegada']   = $routeInfo['horaLlegada'];
        $data['ruta_polyline'] = $routeInfo['ruta_polyline'];
        $data['distancia_km']  = $routeInfo['distancia_km'];
        $data['duracion_min']  = $routeInfo['duracion_min'];

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
            redirectWithFlash(url('/my-rides'), 'success', 'created');
        } else {
             $errors[] = 'Error al publicar el viaje. Inténtalo de nuevo.';
             $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
             require_once __DIR__ . '/../../views/user/publish.view.php';
        }
    }


    // Función para manejar tanto reservas en anuncios tipo "ofrezco" como ofertas en anuncios tipo "busco"
    public function reserve() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/login'));
            exit;
        }

        $rideId = $_POST['ride_id'] ?? null;
        if (!$rideId) {
            header('Location: ' . url('/dashboard'));
            exit;
        }

        // Recoger detalles del viaje
        $ride = $this->ride->getRideById($rideId);
        
        if (!$ride) {
            redirectWithFlash(url('/dashboard'), 'error', 'not_found');
        }

        $tipoAnuncio = strtolower($ride['tipo']);

        // Evitar reservar/ofrecer en anuncio propio
        if ($ride['idUsuario'] == $_SESSION['user_id']) {
            redirectWithFlash(url('/dashboard'), 'error', 'own_ride');
        }

        // Evitar la doble reserva/oferta
        if ($this->ride->hasBooking($rideId, $_SESSION['user_id'])) {
            redirectWithFlash(url('/dashboard'), 'error', 'already_booked');
        }

        // Verificar si hay plazas disponibles (solo para tipo "ofrezco")
        if ($tipoAnuncio === 'ofrezco' && $ride['plazasDisponibles'] <= 0) {
            redirectWithFlash(url('/dashboard'), 'error', 'no_seats');
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

            redirectWithFlash(url('/my-rides'), 'success', 'reserved');
        } else {
            redirectWithFlash(url('/dashboard'), 'error', 'reservation_failed');
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
             redirectWithFlash(url('/my-rides'), 'error', 'missing_params');
        }

        $ride = $this->ride->getRideById($rideId);
        if (!$ride || $ride['idUsuario'] != $_SESSION['user_id']) {
             redirectWithFlash(url('/my-rides'), 'error', 'unauthorized');
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

             redirectWithFlash(url('/my-rides'), 'success', 'status_updated');
        } else {
             redirectWithFlash(url('/my-rides'), 'error', 'update_failed');
        }
    }

    // Cancelar una reserva
    public function cancelReservation() {
        if (!isset($_SESSION['user_id'])) {
            exit;
        }

        $rideId = $_POST['ride_id'] ?? null;
        
        if (!$rideId) {
            redirectWithFlash(url('/my-rides'), 'error', 'missing_params');
        }

        // Verificar que el usuario tiene una reserva activa
        $booking = $this->ride->hasBooking($rideId, $_SESSION['user_id']);

        if (!$booking) {
            redirectWithFlash(url('/my-rides'), 'error', 'no_booking', 'bookings');
        }

        // No permitir cancelar reservas aceptadas a menos que falten más de 24h
        if ($booking['estado'] === 'aceptado') {
            $ride = $this->ride->getRideById($rideId);
            $rideDateTime = strtotime($ride['fechaSalida'] . ' ' . $ride['horaSalida']);
            $now = time();
            $hoursUntilRide = ($rideDateTime - $now) / 3600;

            if ($hoursUntilRide < 24) {
                redirectWithFlash(url('/my-rides'), 'error', 'too_late_to_cancel', 'bookings');
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

            redirectWithFlash(url('/my-rides'), 'success', 'reservation_cancelled', 'bookings');
        } else {
            redirectWithFlash(url('/my-rides'), 'error', 'cancel_failed', 'bookings');
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
                    fullUrl('/my-rides'),
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
                    fullUrl('/my-rides') . '?tab=bookings',
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
                    fullUrl('/dashboard'),
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
                    fullUrl('/my-rides'),
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
                    fullUrl('/my-rides'),
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
            redirectWithFlash(url('/my-rides'), 'error', 'unauthorized');
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
            redirectWithFlash(url('/my-rides'), 'error', 'unauthorized');
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

        // Calcular ruta, hora de llegada, distancia y polyline
        $routeInfo = $this->calculateRouteData($origenLat, $origenLng, $destinoLat, $destinoLng, $data['fechaSalida'], $data['horaSalida']);
        $data['horaLlegada']   = $routeInfo['horaLlegada'];
        $data['ruta_polyline'] = $routeInfo['ruta_polyline'];
        $data['distancia_km']  = $routeInfo['distancia_km'];
        $data['duracion_min']  = $routeInfo['duracion_min'];

        // Actualizar viaje
        if ($this->ride->updateRide($rideId, $data)) {
            redirectWithFlash(url('/my-rides'), 'success', 'updated');
        } else {
             $errors[] = 'Error al actualizar el viaje. Inténtalo de nuevo.';
             $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';
             $origenLoc  = $this->ride->getLocationById((int)$ride['origen']);
             $destinoLoc = $this->ride->getLocationById((int)$ride['destino']);
             require_once __DIR__ . '/../../views/user/publish.view.php';
        }
    }

    // Calcula hora de llegada, polyline, distancia y duración usando OpenRouteService
    private function calculateRouteData(float $origenLat, float $origenLng, float $destinoLat, float $destinoLng, string $fechaSalida, string $horaSalida): array {
        $result = ['horaLlegada' => null, 'ruta_polyline' => null, 'distancia_km' => null, 'duracion_min' => null];

        if ($origenLat == 0 || $origenLng == 0 || $destinoLat == 0 || $destinoLng == 0) {
            return $result;
        }

        $apiKey = $_ENV['ORS_API_KEY'] ?? '';
        if (!$apiKey) {
            error_log('ORS_API_KEY no configurada en .env');
            return $result;
        }
        $url = "https://api.openrouteservice.org/v2/directions/driving-car?api_key={$apiKey}&start={$origenLng},{$origenLat}&end={$destinoLng},{$destinoLat}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json, application/geo+json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$response || $httpCode !== 200) {
            error_log('OpenRouteService error: HTTP ' . $httpCode . ' - ' . ($response ?: 'sin respuesta'));
            return $result;
        }

        $routeData = json_decode($response, true);
        $feature = $routeData['features'][0] ?? null;

        if (!$feature) {
            error_log('OpenRouteService: respuesta sin features');
            return $result;
        }

        // Hora de llegada
        $duration = $feature['properties']['summary']['duration'] ?? 0;
        $distance = $feature['properties']['summary']['distance'] ?? 0;

        if ($duration > 0) {
            $llegada = new DateTime($fechaSalida . ' ' . $horaSalida);
            $llegada->modify("+" . round($duration) . " seconds");
            $result['horaLlegada'] = $llegada->format('H:i:s');
        }

        // Distancia y duración
        $result['distancia_km'] = round($distance / 1000, 2);
        $result['duracion_min'] = (int)ceil($duration / 60);

        // Polyline (coordenadas GeoJSON => JSON compacto)
        $coords = $feature['geometry']['coordinates'] ?? [];
        if (!empty($coords)) {
            $result['ruta_polyline'] = json_encode($coords);
        }

        return $result;
    }

    // Mantener compatibilidad con la función anterior
    private function calculateArrivalTime(float $origenLat, float $origenLng, float $destinoLat, float $destinoLng, string $fechaSalida, string $horaSalida): ?string {
        $data = $this->calculateRouteData($origenLat, $origenLng, $destinoLat, $destinoLng, $fechaSalida, $horaSalida);
        return $data['horaLlegada'];
    }

    public function delete() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/login'));
            exit;
        }

        $rideId = $_POST['id'] ?? null;
        $userId = $_SESSION['user_id'];

        if (!$rideId) {
            redirectWithFlash(url('/my-rides'), 'error', 'missing_id');
        }

        // Verificar que el usuario sea el dueño del viaje
        $ride = $this->ride->getRideById($rideId);
        if (!$ride || $ride['idUsuario'] != $userId) {
            redirectWithFlash(url('/my-rides'), 'error', 'unauthorized');
        }

        // Notificar a los usuarios conectados (pasajeros/conductores con reserva) antes de eliminar
        $connectedUsers = $this->ride->getConnectedUsers($rideId);
        $ownerName = $_SESSION['user_name'] ?? 'El usuario';
        $origen  = $ride['nombreOrigen']  ?? 'origen';
        $destino = $ride['nombreDestino'] ?? 'destino';

        foreach ($connectedUsers as $connUser) {
            // Notificación in-app
            try {
                $this->notification->create(
                    (int)$connUser['idUsuario'],
                    htmlspecialchars($ownerName) . ' ' . t('notif.ride_cancelled') . ' ' . $origen . ' -> ' . $destino . '.',
                    'fas fa-ban',
                    url('/dashboard')
                );
            } catch (Exception $e) {
                error_log("Error notificación in-app cancelación: " . $e->getMessage());
            }

            // Email de notificación
            if ((int)($connUser['notificaciones_email'] ?? 0) === 1) {
                try {
                    $contenido = "
                        <p><strong>" . htmlspecialchars($ownerName) . "</strong> ha cancelado el siguiente viaje en el que tenias una reserva:</p>

                        <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                            <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Origen:</strong> {$origen}</p>
                            <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Destino:</strong> {$destino}</p>
                            <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Fecha:</strong> " . date('d/m/Y', strtotime($ride['fechaSalida'])) . "</p>
                            <p style=\"margin:0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Hora:</strong> " . substr($ride['horaSalida'], 0, 5) . "</p>
                        </div>

                        <p style=\"color:#94a3b8;\">Te recomendamos buscar otro viaje disponible en la plataforma.</p>
                    ";

                    $html = $this->mailService->generarPlantilla(
                        $connUser['nombre'],
                        "Viaje cancelado",
                        $contenido,
                        null,
                        fullUrl('/dashboard'),
                        'Buscar Otros Viajes'
                    );
                    $this->mailService->send($connUser['correo'], $connUser['nombre'], 'Viaje cancelado - Ride4Study', $html);
                } catch (Exception $e) {
                    error_log("Error email cancelación viaje: " . $e->getMessage());
                }
            }
        }

        // Eliminar viaje (las reservas se eliminan por CASCADE o manualmente)
        if ($this->ride->deleteRide($rideId)) {
            redirectWithFlash(url('/my-rides'), 'success', 'deleted');
        } else {
            redirectWithFlash(url('/my-rides'), 'error', 'delete_failed');
        }
    }

    // Ranking de CO2 ahorrado
    public function ranking() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $ranking = $this->ride->getCO2Ranking(50);
        $totalCO2 = $this->ride->getTotalCO2();
        $userCO2 = $this->ride->calculateUserCO2((int)$_SESSION['user_id']);

        // Encontrar posición del usuario actual
        $userPosition = 0;
        foreach ($ranking as $i => $r) {
            if ((int)$r['idUsuario'] === (int)$_SESSION['user_id']) {
                $userPosition = $i + 1;
                break;
            }
        }

        $userInitial = isset($_SESSION['user_name']) ? strtoupper(substr($_SESSION['user_name'], 0, 1)) : 'U';

        require_once __DIR__ . '/../../views/user/ranking.view.php';
    }
}
