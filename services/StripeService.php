<?php

require_once __DIR__ . '/../config/env.php';

class StripeService {
    private string $secretKey;
    private string $apiBase = 'https://api.stripe.com/v1';

    public function __construct() {
        $this->secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? '';

        if (!$this->secretKey) {
            throw new Exception('Clave de Stripe no encontrada en las variables de entorno.');
        }
    }

    // Crear una sesión de Checkout en Stripe
    public function createCheckoutSession(int $userId, string $userEmail, string $successUrl, string $cancelUrl): array {
        // Construir los params manualmente para que Stripe interprete correctamente los arrays
        $params = implode('&', [
            'payment_method_types[0]=card',
            'mode=payment',
            'customer_email=' . urlencode($userEmail),
            'metadata[user_id]=' . $userId,
            'line_items[0][price_data][currency]=eur',
            'line_items[0][price_data][product_data][name]=' . urlencode('Ride4Study Premium - 30 dias'),
            'line_items[0][price_data][unit_amount]=499',
            'line_items[0][quantity]=1',
            'success_url=' . urlencode($successUrl . '&session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url=' . urlencode($cancelUrl),
        ]);

        return $this->request('POST', '/checkout/sessions', $params);
    }

    // Recuperar una sesión de Checkout por ID para verificar el pago
    public function getCheckoutSession(string $sessionId): array {
        return $this->request('GET', '/checkout/sessions/' . urlencode($sessionId));
    }

    // Petición CURL genérica a la API de Stripe
    private function request(string $method, string $endpoint, string $body = ''): array {
        $url = $this->apiBase . $endpoint;

        if ($method === 'GET' && $body) {
            $url .= '?' . $body;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $this->secretKey . ':',
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            error_log('StripeService cURL error: ' . $curlErr);
            return ['error' => ['message' => $curlErr]];
        }

        $decoded = json_decode($response, true) ?? [];
        if ($httpCode >= 400) {
            error_log('StripeService HTTP ' . $httpCode . ': ' . $response);
        }

        return $decoded;
    }
}
