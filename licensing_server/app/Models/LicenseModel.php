<?php
// License Model for Licensing Server
require_once __DIR__ . '/../../core/Model.php';

class LicenseModel extends Model {
    protected static $table = 'licenses';

    /**
     * Generates a secure, readable License Key
     */
    public static function generateLicenseKey($prefix = 'INV') {
        $parts = [];
        for ($i = 0; $i < 4; $i++) {
            $parts[] = strtoupper(bin2hex(random_bytes(3))); // 6 char blocks
        }
        return $prefix . '-' . implode('-', $parts); // e.g. INV-A1B2C3-D4E5F6-G7H8I9-J0K1L2
    }

    /**
     * Generates an RSA 2048 keypair using PHP OpenSSL
     */
    public static function generateKeyPair() {
        $config = [
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        
        // On Windows environments, we often need to specify the openssl.cnf path explicitly
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $winPaths = [
                'C:/AppServ/php8/extras/ssl/openssl.cnf',
                'C:/AppServ/php8.2/extras/ssl/openssl.cnf',
                'C:/AppServ/php8.1/extras/ssl/openssl.cnf',
                'C:/AppServ/php7/extras/ssl/openssl.cnf',
                'C:/xampp/php/extras/ssl/openssl.cnf',
                'C:/Program Files/Common Files/SSL/openssl.cnf',
            ];
            foreach ($winPaths as $path) {
                if (file_exists($path)) {
                    $config['config'] = $path;
                    break;
                }
            }
        }
        
        $res = openssl_pkey_new($config);
        if ($res === false) {
            // Check openssl errors
            $err = '';
            while ($msg = openssl_error_string()) {
                $err .= $msg . '; ';
            }
            throw new Exception("OpenSSL key generation failed: " . ($err ?: "Unknown error. Verify php_openssl is enabled."));
        }
        
        // Export Private Key
        $privateKey = '';
        if (!openssl_pkey_export($res, $privateKey, null, $config)) {
            throw new Exception("Failed to export Private Key");
        }
        
        // Extract Public Key
        $pubKeyDetails = openssl_pkey_get_details($res);
        if ($pubKeyDetails === false || !isset($pubKeyDetails["key"])) {
            throw new Exception("Failed to get Public Key details");
        }
        $publicKey = $pubKeyDetails["key"];
        
        return [
            'private' => $privateKey,
            'public'  => $publicKey
        ];
    }
}
