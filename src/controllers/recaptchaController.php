<?php
require 'recaptchaKeyPrivate.php';

class RecaptchaController {
    private static $logFile = __DIR__ . '/recaptcha_debug.log';

    private static function logMessage($message, $data = null) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[{$timestamp}] {$message}";
        if ($data !== null) {
            $logEntry .= "\nData: " . print_r($data, true);
        }
        $logEntry .= "\n" . str_repeat('-', 80) . "\n";
        
        // Write to custom log file
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }

    public static function verifyHuman() {
        try {
            // Enable error display and logging
            ini_set('display_errors', 1);
            ini_set('log_errors', 1);
            ini_set('error_log', self::$logFile);
            error_reporting(E_ALL);

            // Set headers
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: http://127.0.0.1:5501');
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Accept');

            self::logMessage("Request started", $_SERVER);

            // Handle CORS preflight
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            // Get and validate input
            $input = file_get_contents('php://input');
            self::logMessage("Received input", $input);

            $data = json_decode($input, true);
            if (!$data || !isset($data['token'])) {
                throw new Exception('Invalid or missing token in request');
            }

            $token = $data['token'];
            self::logMessage("Processing token", substr($token, 0, 20) . '...');

            // Get reCAPTCHA secret
            $secret = recaptchaKeyPrivate::getRecaptchaKeyV3();
            self::logMessage("Using secret key", substr($secret, 0, 6) . '...');

            // Prepare verification request
            $verifyData = http_build_query([
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            // Initialize cURL
            $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
            if (!$ch) {
                throw new Exception('Failed to initialize cURL');
            }

            // Set cURL options
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $verifyData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_VERBOSE => true
            ]);

            // Execute request
            self::logMessage("Sending verification request to Google");
            $response = curl_exec($ch);

            // Check for cURL errors
            if ($response === false) {
                $error = curl_error($ch);
                $errno = curl_errno($ch);
                curl_close($ch);
                throw new Exception("cURL error ($errno): $error");
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            self::logMessage("Google API response", [
                'httpCode' => $httpCode,
                'response' => $response
            ]);

            // Parse response
            $result = json_decode($response, true);
            if (!$result) {
                throw new Exception('Failed to parse Google response: ' . json_last_error_msg());
            }

            // Send response
            if (isset($result['success']) && $result['success']) {
                $score = $result['score'] ?? 0;
                self::logMessage("Verification successful", ['score' => $score]);
                
                echo json_encode([
                    'success' => true,
                    'score' => $score,
                    'needsCaptchaV2' => ($score < 0.5)
                ]);
            } else {
                $errors = isset($result['error-codes']) ? implode(', ', $result['error-codes']) : 'unknown';
                throw new Exception("Verification failed: $errors");
            }

        } catch (Exception $e) {
            self::logMessage("Error occurred", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public static function simulateBot() {
        try {
            // Enable error display and logging
            ini_set('display_errors', 1);
            ini_set('log_errors', 1);
            ini_set('error_log', self::$logFile);
            error_reporting(E_ALL);

            // Set headers
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: http://127.0.0.1:5501');
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Accept');

            self::logMessage("Bot simulation request received");

            // Handle CORS preflight
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                http_response_code(200);
                exit();
            }

            // Log the request
            self::logMessage("Simulating bot detection", [
                'timestamp' => date('Y-m-d H:i:s'),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);

            // Send response
            http_response_code(200);
            echo json_encode([
                'success' => false,
                'message' => 'Bot behavior detected! Additional verification required.',
                'needsCaptchaV2' => true,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

        } catch (Exception $e) {
            self::logMessage("Error in bot simulation", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Internal server error during bot simulation',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        }
    }
}