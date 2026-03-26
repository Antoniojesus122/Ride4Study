<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../services/StripeService.php';
require_once __DIR__ . '/../../services/MailService.php';
require_once __DIR__ . '/../models/Notification.php';

class PremiumController {
    private PDO $db;
    private User $user;
    private ?StripeService $stripe = null;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $database   = new Database();
        $this->db   = $database->connect();
        $this->user = new User($this->db);
        try { $this->stripe = new StripeService(); } catch (Exception $e) { error_log('StripeService: ' . $e->getMessage()); }
    }

    // Página de presentación del plan premium
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        // Comprobar si ya es premium
        $stmt = $this->db->prepare("SELECT premium, premium_hasta FROM usuarios WHERE idUsuario = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
        $isPremium = $userData && $userData['premium'] && (!$userData['premium_hasta'] || $userData['premium_hasta'] > date('Y-m-d H:i:s'));
        $premiumHasta = $userData['premium_hasta'] ?? null;

        require_once __DIR__ . '/../../views/user/premium.view.php';
    }

    // Iniciar sesión de pago con Stripe Checkout
    public function checkout() {
        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/premium'));
            exit;
        }

        if (!$this->stripe) {
            error_log('Premium checkout: StripeService no disponible');
            redirectWithFlash(url('/premium'), 'error', 'stripe_unavailable');
        }

        $userData = $this->user->getUserById($_SESSION['user_id']);
        if (!$userData) {
            header('Location: ' . url('/premium'));
            exit;
        }

        // Stripe requiere URLs absolutas
        $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                 . '://' . $_SERVER['HTTP_HOST'];
        $successUrl = $baseUrl . url('/premium') . '?action=success';
        $cancelUrl  = $baseUrl . url('/premium') . '?action=cancel';

        $session = $this->stripe->createCheckoutSession(
            (int)$_SESSION['user_id'],
            $userData['correo'],
            $successUrl,
            $cancelUrl
        );

        if (isset($session['error'])) {
            error_log('Stripe checkout error: ' . json_encode($session['error']));
            redirectWithFlash(url('/premium'), 'error', 'checkout_failed');
        }

        if (isset($session['url'])) {
            header('Location: ' . $session['url']);
            exit;
        }

        error_log('Stripe checkout: respuesta inesperada: ' . json_encode($session));
        redirectWithFlash(url('/premium'), 'error', 'checkout_failed');
    }

    // Página de éxito tras el pago — verificar con Stripe y activar premium
    public function success() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $sessionId = $_GET['session_id'] ?? '';
        if (!$sessionId || !$this->stripe) {
            redirectWithFlash(url('/premium'), 'error', 'invalid_session');
        }

        // Verificar el estado del pago directamente en Stripe
        $session = $this->stripe->getCheckoutSession($sessionId);

        if (($session['payment_status'] ?? '') !== 'paid') {
            error_log('Premium success: payment_status=' . ($session['payment_status'] ?? 'null') . ' session=' . json_encode($session));
            redirectWithFlash(url('/premium'), 'error', 'payment_not_confirmed');
        }

        // Evitar doble activación si el usuario recarga la página
        $stmt = $this->db->prepare("SELECT premium, premium_hasta FROM usuarios WHERE idUsuario = :id");
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($current && $current['premium'] && $current['premium_hasta'] && $current['premium_hasta'] > date('Y-m-d H:i:s')) {
            redirectWithFlash(url('/premium'), 'success', 'activated');
        }

        // Activar premium durante 30 días
        $this->activatePremium((int)$_SESSION['user_id']);
        redirectWithFlash(url('/premium'), 'success', 'activated');
    }

    // Cancelación del proceso de pago
    public function cancel() {
        redirectWithFlash(url('/premium'), 'error', 'cancelled');
    }

    // Activar premium para el usuario (30 días)
    private function activatePremium(int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET premium = 1, premium_hasta = DATE_ADD(NOW(), INTERVAL 30 DAY)
             WHERE idUsuario = :id"
        );
        $stmt->execute([':id' => $userId]);

        // Enviar email de confirmación de activación premium
        try {
            $userData = $this->user->getUserById($userId);
            if ($userData) {
                $premiumHasta = date('d/m/Y', strtotime('+30 days'));

                // Notificación in-app
                $notification = new Notification($this->db);
                $notification->create(
                    $userId,
                    'Tu suscripcion Premium ha sido activada. Disfruta de todas las ventajas hasta el ' . $premiumHasta . '.',
                    'fas fa-crown',
                    url('/premium')
                );

                // Email de confirmación
                if ((int)($userData['notificaciones_email'] ?? 1) === 1) {
                    $mail = new MailService();
                    $contenido = "
                        <p>Tu suscripcion <strong style=\"color:#34d399;\">Premium</strong> ha sido activada correctamente.</p>

                        <div style=\"background-color:#0f172a; padding:20px; border-radius:12px; margin:20px 0;\">
                            <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#34d399;\">Plan:</strong> Premium 30 dias</p>
                            <p style=\"margin:0 0 10px 0; color:#cbd5e1;\"><strong style=\"color:#22d3ee;\">Valido hasta:</strong> {$premiumHasta}</p>
                        </div>

                        <p style=\"color:#94a3b8;\">Ahora puedes disfrutar de:</p>
                        <ul style=\"color:#94a3b8; line-height:2;\">
                            <li>Anuncios ilimitados (sin limite de 4)</li>
                            <li>Destacar un anuncio para que aparezca primero</li>
                            <li>Insignia Premium en tu perfil</li>
                        </ul>
                    ";

                    $html = $mail->generarPlantilla(
                        $userData['nombre'],
                        "Premium activado",
                        $contenido,
                        null,
                        fullUrl('/premium'),
                        'Ver Mi Premium'
                    );
                    $mail->send($userData['correo'], $userData['nombre'], 'Premium activado - Ride4Study', $html);
                }
            }
        } catch (Exception $e) {
            error_log("Error email confirmación premium: " . $e->getMessage());
        }
    }
}
