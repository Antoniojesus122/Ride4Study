<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../../services/StripeService.php';

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
            header('Location: ' . url('/premium') . '?error=stripe_unavailable');
            exit;
        }

        $userData = $this->user->getUserById($_SESSION['user_id']);
        if (!$userData) {
            header('Location: ' . url('/premium'));
            exit;
        }

        $successUrl = url('/premium') . '?action=success';
        $cancelUrl  = url('/premium') . '?action=cancel';

        $session = $this->stripe->createCheckoutSession(
            (int)$_SESSION['user_id'],
            $userData['correo'],
            $successUrl,
            $cancelUrl
        );

        if (isset($session['url'])) {
            header('Location: ' . $session['url']);
            exit;
        }

        error_log('Stripe checkout error: ' . json_encode($session));
        header('Location: ' . url('/premium') . '?error=checkout_failed');
        exit;
    }

    // Página de éxito tras el pago — verificar con Stripe y activar premium
    public function success() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('/login'));
            exit;
        }

        $sessionId = $_GET['session_id'] ?? '';
        if (!$sessionId || !$this->stripe) {
            header('Location: ' . url('/premium') . '?error=invalid_session');
            exit;
        }

        // Verificar el estado del pago directamente en Stripe
        $session = $this->stripe->getCheckoutSession($sessionId);

        if (($session['payment_status'] ?? '') !== 'paid') {
            header('Location: ' . url('/premium') . '?error=payment_not_confirmed');
            exit;
        }

        // Activar premium durante 30 días
        $this->activatePremium((int)$_SESSION['user_id']);
        header('Location: ' . url('/premium') . '?activated=1');
        exit;
    }

    // Cancelación del proceso de pago
    public function cancel() {
        header('Location: ' . url('/premium') . '?cancelled=1');
        exit;
    }

    // Webhook de Stripe para confirmar pagos de forma segura
    public function webhook() {
        if (!$this->stripe) {
            http_response_code(503);
            exit;
        }

        $payload   = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        if (!$this->stripe->verifyWebhookSignature($payload, $sigHeader)) {
            http_response_code(400);
            exit('Firma inválida');
        }

        $event = json_decode($payload, true);

        if (($event['type'] ?? '') === 'checkout.session.completed') {
            $userId = (int)($event['data']['object']['metadata']['user_id'] ?? 0);
            if ($userId > 0) {
                $this->activatePremium($userId);
            }
        }

        http_response_code(200);
        echo 'OK';
        exit;
    }

    // Activar premium para el usuario (30 días)
    private function activatePremium(int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE usuarios
             SET premium = 1, premium_hasta = DATE_ADD(NOW(), INTERVAL 30 DAY)
             WHERE idUsuario = :id"
        );
        $stmt->execute([':id' => $userId]);
    }
}
