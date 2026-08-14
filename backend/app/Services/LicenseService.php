<?php
// License verification and online activation service
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/SequenceService.php';

class LicenseService {
    // Configurable licensing server URL
    private static $keyServerUrl = 'https://key.sandslab.com';

    /**
     * Verify license locally using public key signature checks
     */
    public static function checkLicense() {
        try {
            $settings = SequenceService::getSettings();
            $key = $settings['license_key'] ?? '';
            $token = $settings['license_token'] ?? '';
            $publicKey = $settings['license_public_key'] ?? '';

            if (empty($key) || empty($token) || empty($publicKey)) {
                return [
                    'valid' => false,
                    'message' => 'Software is not activated. Please activate your license.'
                ];
            }

            // Split token into payload and signature
            $parts = explode('.', $token);
            if (count($parts) !== 2) {
                return [
                    'valid' => false,
                    'message' => 'Invalid license token format.'
                ];
            }

            list($payloadBase64, $signatureBase64) = $parts;
            $payloadJson = base64_decode($payloadBase64);
            $signature = base64_decode($signatureBase64);
            $payload = json_decode($payloadJson, true);

            if (!$payload) {
                return [
                    'valid' => false,
                    'message' => 'Unable to decode license payload.'
                ];
            }

            // 1. Verify RSA Signature using the public key stored in system settings
            $ok = openssl_verify($payloadJson, $signature, $publicKey, OPENSSL_ALGO_SHA256);
            if ($ok !== 1) {
                return [
                    'valid' => false,
                    'message' => 'License verification signature mismatch.'
                ];
            }

            // 2. Verify Domain Match
            $currentDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $currentDomainClean = preg_replace('#^https?://#', '', strtolower($currentDomain));
            $currentDomainClean = explode(':', explode('/', $currentDomainClean)[0])[0];

            $licenseDomainClean = preg_replace('#^https?://#', '', strtolower($payload['domain_name']));
            $licenseDomainClean = explode(':', explode('/', $licenseDomainClean)[0])[0];

            // Allow localhost for local development checks, or require exact matches
            if ($currentDomainClean !== $licenseDomainClean && $currentDomainClean !== 'localhost' && $currentDomainClean !== '127.0.0.1') {
                return [
                    'valid' => false,
                    'message' => "License domain mismatch. This key is registered for '{$payload['domain_name']}', but runs on '{$currentDomain}'."
                ];
            }

            // 3. Verify Expiry Date
            if (!empty($payload['expiry_date'])) {
                $today = date('Y-m-d');
                if ($today > $payload['expiry_date']) {
                    return [
                        'valid' => false,
                        'message' => "License expired on {$payload['expiry_date']}."
                    ];
                }
            }

            return [
                'valid' => true,
                'message' => 'License verified successfully.',
                'payload' => $payload
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'License verification error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Activate the license online against key.sandslab.co
     */
    public static function activate($licenseKey) {
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $postData = [
            'license_key' => $licenseKey,
            'domain_name' => $domain
        ];

        // Make HTTP Call to licensing server
        $url = self::$keyServerUrl . '/api/activate';
        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/json\r\nConnection: close\r\n",
                'content' => json_encode($postData),
                'timeout' => 8
            ]
        ]);

        $responseJson = @file_get_contents($url, false, $ctx);
        if ($responseJson === false) {
            throw new Exception("Unable to connect to key server at " . self::$keyServerUrl);
        }

        $res = json_decode($responseJson, true);
        if (empty($res) || !$res['success']) {
            throw new Exception($res['message'] ?? 'Activation server returned an error.');
        }

        // Save settings to database
        $pdo = Model::getDB();
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        $stmt->execute(['license_key', $licenseKey]);
        $stmt->execute(['license_token', $res['token']]);
        $stmt->execute(['license_public_key', $res['public_key']]);

        return true;
    }
}
