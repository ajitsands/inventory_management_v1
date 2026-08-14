# SaNDS Lab License Server Integration Guide

This document outlines how to integrate the SaNDS Lab Licensing System (`key.sandslab.com`) into any PHP application.

## Overview

The licensing system operates using an **asymmetric offline verification model**. 
When a client activates a license, the key server generates a secure JWT-like token mathematically signed with a private key. The client application stores this token along with a public key and verifies it locally on every page load. This guarantees maximum performance (0 network latency) and zero downtime if the key server goes offline.

---

## 1. Activation Endpoint

The activation API is used to exchange a License Key and Domain Name for a cryptographically signed token.

- **Endpoint:** `POST https://key.sandslab.com/public/api/activate`
- **Headers:** `Content-Type: application/json`

### Request Payload (JSON)

```json
{
    "license_key": "INV-XXXXXX-XXXXXX-XXXXXX-XXXXXX",
    "domain_name": "client-app.com",
    "ip_address": "123.45.67.89" 
}
```
> [!NOTE]
> `ip_address` is optional but highly recommended. If provided, the key server will strictly enforce it against the IP lock.

### Success Response (200 OK)

```json
{
    "success": true,
    "message": "Activation successful.",
    "token": "eyJ... (Base64 Payload) ... . ... (Base64 Signature) ...",
    "public_key": "-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"
}
```

### Error Response (400, 403, 404)

```json
{
    "success": false,
    "message": "License is currently suspended. Please contact support."
}
```

---

## 2. Implementing the Client (Activation)

To activate the license from your client PHP app, make a `cURL` POST request:

```php
function activateLicense($licenseKey) {
    $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Optional: Resolve true public IP if running on shared localhost
    $ip = gethostbyname($domain);
    if ($ip === $domain || $ip === '127.0.0.1') {
        $ip = @file_get_contents('https://api.ipify.org') ?: $_SERVER['SERVER_ADDR'];
    }

    $ch = curl_init('https://key.sandslab.com/public/api/activate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'license_key' => $licenseKey,
            'domain_name' => $domain,
            'ip_address'  => trim($ip)
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 15
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode === 200 && !empty($data['token']) && !empty($data['public_key'])) {
        // SUCCESS: Save the license data to your application!
        saveLicenseData($licenseKey, $data['token'], $data['public_key']);
        return true;
    }

    throw new Exception($data['message'] ?? 'Unknown activation error.');
}

// ---------------------------------------------------------
// NOTE ON DATA STORAGE:
// You DO NOT need a dedicated database table for this. 
// You simply need to save 3 strings:
// 1. $licenseKey 
// 2. $data['token'] 
// 3. $data['public_key']
//
// You can save these in an existing `settings` key-value table, 
// a local `.env` file, or a secure JSON file.
// ---------------------------------------------------------
```

---

## 3. Implementing the Client (Local Verification)

On every page load, your client app should read the saved Token and Public Key from its database and verify it locally.

> [!IMPORTANT]
> Do **not** make network requests to the key server on every page load. Verify the signature locally for maximum performance.

```php
function checkLicenseLocally($token, $publicKey) {
    if (empty($token) || empty($publicKey)) {
        return ['valid' => false, 'message' => 'No license installed.'];
    }

    // Split Token
    $parts = explode('.', $token);
    if (count($parts) !== 2) return ['valid' => false, 'message' => 'Invalid token format.'];
    
    list($payloadBase64, $signatureBase64) = $parts;
    $payloadJson = base64_decode($payloadBase64);
    $signature = base64_decode($signatureBase64);
    $payload = json_decode($payloadJson, true);

    // 1. Verify RSA Signature using OpenSSL
    $ok = openssl_verify($payloadJson, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    if ($ok !== 1) {
        return ['valid' => false, 'message' => 'Signature mismatch. Token is corrupted or forged.'];
    }

    // 2. Verify Domain Matches
    $currentDomain = preg_replace('#^https?://#', '', strtolower($_SERVER['HTTP_HOST'] ?? ''));
    $licenseDomain = preg_replace('#^https?://#', '', strtolower($payload['domain_name']));
    
    if (explode(':', $currentDomain)[0] !== explode(':', $licenseDomain)[0]) {
        return ['valid' => false, 'message' => 'Domain mismatch.'];
    }

    // 3. Verify Expiry Date (if applicable)
    if (!empty($payload['expiry_date']) && date('Y-m-d') > $payload['expiry_date']) {
        return ['valid' => false, 'message' => 'License expired.'];
    }

    return ['valid' => true, 'payload' => $payload];
}
```

---

## 4. Live Deactivation Checks (Optional Graceful Fallback)

If you want to instantly block deactivated clients when they log in, you can ping the activation API silently with a short timeout. 

```php
// Call this during User Login
function checkLiveStatus($licenseKey, $domain) {
    $ch = curl_init('https://key.sandslab.com/public/api/activate');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'license_key' => $licenseKey,
            'domain_name' => $domain
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 4, // Fast timeout! Don't hang the login if offline.
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Explicitly reject only if the server responds with 403 (Suspended)
    if ($httpCode === 403) {
        $data = json_decode($response, true);
        return ['valid' => false, 'message' => $data['message'] ?? 'Deactivated'];
    }

    // If server is offline (timeout) or returns 200, allow login based on local token
    return ['valid' => true];
}
```
