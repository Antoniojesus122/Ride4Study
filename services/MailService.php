<?php

require_once __DIR__ . '/../config/env.php';

class MailService
{
    private $apiKey;

    public function __construct()
    {

    $this->apiKey = $_ENV['BREVO_API_KEY'] ?? null;

    if (!$this->apiKey) {
            throw new Exception("API Key no encontrada en variables de entorno.");
    }

    }
    
    public function send($toEmail, $toName, $subject, $html)
    {
        $data = [
            "sender" => [
                "name" => "Ride4Study",
                "email" => "ride4study@outlook.es"
            ],
            "to" => [[
                "email" => $toEmail,
                "name" => $toName
            ]],
            "subject" => $subject,
            "htmlContent" => $html
        ];

        $ch = curl_init("https://api.brevo.com/v3/smtp/email");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "api-key: {$this->apiKey}",
            "content-type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($ch);
        $curlErrNo = curl_errno($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        $success = ($curlErrNo === 0 && $httpCode >= 200 && $httpCode < 300);

        $result = [
            'success' => $success,
            'http_code' => $httpCode,
            'response' => $decoded !== null ? $decoded : $response,
            'curl_errno' => $curlErrNo,
            'curl_error' => $curlError
        ];

        if (!$success) {
            $errMsg = 'MailService error: ' . json_encode($result);
            error_log($errMsg);
        }

        return $result;
    }
}