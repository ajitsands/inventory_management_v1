<?php
// License verification and online activation service
require_once __DIR__ . '/../../core/Model.php';
require_once __DIR__ . '/SequenceService.php';

class LicenseService {
    // Configurable licensing server URL
    private static $keyServerUrl = 'https://key.sandslab.com/public';

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
        $resolvedIp = gethostbyname($domain);
        
        // If the server resolves itself to localhost, fetch the true public IP
        if ($resolvedIp === $domain || $resolvedIp === '127.0.0.1' || $resolvedIp === '::1' || strpos($resolvedIp, '192.168.') === 0 || strpos($resolvedIp, '10.') === 0) {
            $publicIp = @file_get_contents('https://api.ipify.org');
            if ($publicIp) {
                $resolvedIp = trim($publicIp);
            } else {
                $resolvedIp = $_SERVER['SERVER_ADDR'] ?? '';
            }
        }
        
        $postData = [
            'license_key' => $licenseKey,
            'domain_name' => $domain,
            'ip_address' => $resolvedIp
        ];

        // Make HTTP Call to licensing server using cURL
        $url = self::$keyServerUrl . '/api/activate';
        $ch = curl_init($url);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($postData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Connection: close'
            ],
            CURLOPT_TIMEOUT => 12,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $responseJson = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($responseJson === false) {
            throw new Exception("Connection failed: " . $curlError);
        }

        $res = json_decode($responseJson, true);
        if (empty($res) || !$res['success'] || $httpCode !== 200) {
            throw new Exception($res['message'] ?? "Activation server returned error code " . $httpCode);
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
