<?php

class MailService
{
    private $apiKey;

    public function __construct()
    {
        $this->apiKey = "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";
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

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}