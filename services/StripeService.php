<?php

require_once __DIR__ . '/../config/env.php';

class StripeService {
    private string $secretKey;
    private string $webhookSecret;
    private string $apiBase = 'https://api.stripe.com/v1';

    public function __construct() {
        $this->secretKey     = $_ENV['STRIPE_SECRET_KEY']   ?? '';
        $this->webhookSecret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';

        if (!$this->secretKey) {
            throw new Exception('Clave de Stripe no encontrada en las variables de entorno.');
        }
    }

    // Crear una sesión de Checkout en Stripe
    public function createCheckoutSession(int $userId, string $userEmail, string $successUrl, string $cancelUrl): array {
        $params = http_build_query([
            'payment_method_types[]'       => 'card',
            'mode'                         => 'payment',
            'customer_email'               => $userEmail,
            'metadata[user_id]'            => $userId,
            'line_items[0][price_data][currency]'             => 'eur',
            'line_items[0][price_data][product_data][name]'   => 'Ride4Study Premium — 30 días',
            'line_items[0][price_data][product_data][description]' => 'Anuncios ilimitados + 1 anuncio destacado',
            'line_items[0][price_data][unit_amount]'          => 499, // 4,99 € en céntimos
            'line_items[0][quantity]'                         => 1,
            'success_url'                  => $successUrl . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'                   => $cancelUrl,
        ]);

        return $this->request('POST', '/checkout/sessions', $params);
    }

    // Recuperar una sesión de Checkout por ID para verificar el pago
    public function getCheckoutSession(string $sessionId): array {
        return $this->request('GET', '/checkout/sessions/' . urlencode($sessionId));
    }

    // Verificar la firma del webhook de Stripe
    public function verifyWebhookSignature(string $payload, string $sigHeader): bool {
        if (!$this->webhookSecret) return false;

        $parts = [];
        foreach (explode(',', $sigHeader) as $part) {
            [$k, $v] = explode('=', $part, 2);
            $parts[$k][] = $v;
        }

        $timestamp = $parts['t'][0] ?? null;
        if (!$timestamp || abs(time() - (int)$timestamp) > 300) return false;

        $signedPayload = $timestamp . '.' . $payload;
        $expected      = hash_hmac('sha256', $signedPayload, $this->webhookSecret);

        foreach ($parts['v1'] ?? [] as $sig) {
            if (hash_equals($expected, $sig)) return true;
        }

        return false;
    }

    // Petición cURL genérica a la API de Stripe
    private function request(string $method, string $endpoint, string $body = ''): array {
        $ch = curl_init($this->apiBase . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $this->secretKey . ':',
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
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
