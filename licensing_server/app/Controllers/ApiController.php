<?php
// API Controller to handle remote client activation requests
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../Models/LicenseModel.php';

class ApiController extends Controller {

    /**
     * POST /api/activate
     * Public API Endpoint called by client inventory systems
     */
    public function activate() {
        // Allow CORS requests for the activation API
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type');
        header('Access-Control-Allow-Methods: POST, OPTIONS');

        // Check if request is JSON or POST
        $json = file_get_contents('php_input');
        if (!$json) {
            $json = file_get_contents('php://input');
        }
        $data = json_decode($json, true);

        if (!$data) {
            $data = $_POST;
        }

        $licenseKey = trim($data['license_key'] ?? '');
        $clientDomain = trim($data['domain_name'] ?? '');
        $clientIp = trim($data['ip_address'] ?? $_SERVER['REMOTE_ADDR'] ?? '');

        if (empty($licenseKey) || empty($clientDomain)) {
            return $this->json([
                'success' => false,
                'message' => 'Missing parameters. license_key and domain_name are required.'
            ], 400);
        }

        // 1. Fetch license details from database
        $license = LicenseModel::findWhere(['license_key' => $licenseKey]);

        if (!$license) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid License Key.'
            ], 404);
        }

        // 2. Check License Status
        if ($license['status'] !== 'active') {
            return $this->json([
                'success' => false,
                'message' => "License is currently {$license['status']}. Please contact support."
            ], 403);
        }

        // 3. Verify Domain Match (ignore http/https protocol prefixes)
        $cleanClientDomain = preg_replace('#^https?://#', '', strtolower($clientDomain));
        $cleanLicenseDomain = preg_replace('#^https?://#', '', strtolower($license['domain_name']));
        
        // Strip trailing slash or port
        $cleanClientDomain = explode(':', explode('/', $cleanClientDomain)[0])[0];
        $cleanLicenseDomain = explode(':', explode('/', $cleanLicenseDomain)[0])[0];

        if ($cleanClientDomain !== $cleanLicenseDomain) {
            return $this->json([
                'success' => false,
                'message' => "Domain mismatch. This license key is registered to '{$license['domain_name']}', but request came from '{$clientDomain}'."
            ], 403);
        }

        // 4. Verify IP Address if bound to one
        if (!empty($license['ip_address'])) {
            if ($clientIp !== $license['ip_address']) {
                return $this->json([
                    'success' => false,
                    'message' => "IP Address mismatch. This license is bound to IP '{$license['ip_address']}'."
                ], 403);
            }
        }

        // 5. Verify Expiration
        if (!empty($license['expiry_date'])) {
            $today = date('Y-m-d');
            if ($today > $license['expiry_date']) {
                // Auto-update status to suspended/expired optionally, or just deny
                LicenseModel::update($license['id'], ['status' => 'suspended']);
                return $this->json([
                    'success' => false,
                    'message' => "License expired on {$license['expiry_date']}."
                ], 403);
            }
        }

        // 6. Generate Cryptographic Signature (RSA-SHA256)
        try {
            $payload = [
                'license_key'      => $license['license_key'],
                'customer_name'    => $license['customer_name'],
                'application_name' => $license['application_name'],
                'domain_name'      => $license['domain_name'],
                'ip_address'       => $license['ip_address'],
                'expiry_date'      => $license['expiry_date'],
                'activated_at'     => date('Y-m-d H:i:s')
            ];

            $payloadJson = json_encode($payload);
            $signature = '';

            // Sign the JSON using the Private Key
            $privateKey = $license['private_key'];
            $success = openssl_sign($payloadJson, $signature, $privateKey, OPENSSL_ALGO_SHA256);

            if (!$success) {
                throw new Exception("Signature signing failed.");
            }

            // Create token: base64(payload) . "." . base64(signature)
            $token = base64_encode($payloadJson) . '.' . base64_encode($signature);

            return $this->json([
                'success'    => true,
                'message'    => 'Activation successful.',
                'token'      => $token,
                'public_key' => $license['public_key'] // The client saves this to verify local decryptions offline
            ]);

        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Cryptographic signing failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
