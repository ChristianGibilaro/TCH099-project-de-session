<?php
require 'recaptchaKeyPrivate.php';

class RecaptchaController {
    public static function verify() {
        $secretKey = recaptchaKeyPrivate::getRecaptcahKey(); // Replace with your real secret key
        $token = $_POST['token'] ?? '';
        $remoteIp = $_SERVER['REMOTE_ADDR'];

        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$token&remoteip=$remoteIp");
        $result = json_decode($response, true);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result['success'] ?? false,
            'score' => $result['score'] ?? 0,
            'action' => $result['action'] ?? '',
        ]);
    }
}