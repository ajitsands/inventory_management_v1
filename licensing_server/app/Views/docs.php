<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaNDS Lab - API Documentation</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1C8DCD;
            --bg-color: #F8FAFC;
            --bg-gradient: radial-gradient(circle at 10% 20%, #F1F5F9 0%, #E2E8F0 90%);
            --card-bg: #FFFFFF;
            --border-color: #E2E8F0;
            --text-light: #1E293B;
            --text-muted: #64748B;
            --code-bg: #1E293B;
            --success: #10B981;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image: var(--bg-gradient);
            color: var(--text-light);
            min-height: 100vh;
            margin: 0;
            padding: 40px 20px;
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            backdrop-filter: blur(10px);
        }

        h1, h2, h3 {
            color: var(--text-light);
            margin-top: 2em;
            margin-bottom: 0.5em;

        }
        h1 { margin-top: 0; font-size: 2.5em; color: var(--primary); }
        h2 { border-bottom: 1px solid var(--border-color); padding-bottom: 10px; }

        p { color: var(--text-muted); font-size: 16px; margin-bottom: 1.5em; }
        
        pre {
            background: var(--code-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 20px;
            overflow-x: auto;
            font-family: 'Fira Code', monospace;
            font-size: 14px;
            color: #E5E7EB;
        }

        code {
            font-family: 'Fira Code', monospace;
            background: rgba(255,255,255,0.1);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
            color: var(--primary);
        }

        pre code {
            background: transparent;
            padding: 0;
            color: inherit;
        }

        .note {
            background: rgba(28, 141, 205, 0.1);
            border-left: 4px solid var(--primary);
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .note strong { color: var(--primary); }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 30px;
            transition: color 0.2s;
        }

        .btn-back:hover {
            color: var(--text-light);
        }
    </style>
</head>
<body>

<div class="container">
    <a href="/public/" class="btn-back">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        Back to Dashboard
    </a>

    <div class="card">
        <h1>SaNDS Lab API Integration Guide</h1>
        <p>This document outlines how to integrate the SaNDS Lab Licensing System (<code>key.sandslab.com</code>) into any PHP application using an asymmetric offline verification model.</p>

        <h2>1. Activation Endpoint</h2>
        <p>The activation API is used to exchange a License Key and Domain Name for a cryptographically signed token.</p>
        
        <ul>
            <li><strong>Endpoint:</strong> <code>POST https://key.sandslab.com/public/api/activate</code></li>
            <li><strong>Headers:</strong> <code>Content-Type: application/json</code></li>
        </ul>

        <h3>Request Payload (JSON)</h3>
<pre><code>{
    "license_key": "INV-XXXXXX-XXXXXX-XXXXXX-XXXXXX",
    "domain_name": "client-app.com",
    "ip_address": "123.45.67.89" 
}</code></pre>
        
        <div class="note">
            <strong>NOTE:</strong> <code>ip_address</code> is optional but highly recommended. If provided, the key server will strictly enforce it against the IP lock.
        </div>

        <h3>Success Response (200 OK)</h3>
<pre><code>{
    "success": true,
    "message": "Activation successful.",
    "token": "eyJ... (Base64 Payload) ... . ... (Base64 Signature) ...",
    "public_key": "-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"
}</code></pre>

        <h2>2. Implementing the Client (Activation)</h2>
        <p>To activate the license from your client PHP app, make a <code>cURL</code> POST request:</p>

<pre><code>function activateLicense($licenseKey) {
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
        // SUCCESS: Save data to your application!
        saveLicenseData($licenseKey, $data['token'], $data['public_key']);
        return true;
    }

    throw new Exception($data['message'] ?? 'Unknown activation error.');
}</code></pre>

        <div class="note">
            <strong>DATA STORAGE:</strong> You DO NOT need a dedicated database table for this. You simply need to save 3 strings: the License Key, Token, and Public Key. You can save these in an existing `settings` key-value table, a local `.env` file, or a secure JSON file.
        </div>

        <h2>3. Local Verification (Page Load)</h2>
        <p>On every page load, your client app should read the saved Token and Public Key from its database and verify it locally. Do not make network requests to the key server on every page load.</p>

<pre><code>function checkLicenseLocally($token, $publicKey) {
    if (empty($token) || empty($publicKey)) {
        return ['valid' => false, 'message' => 'No license installed.'];
    }

    $parts = explode('.', $token);
    if (count($parts) !== 2) return ['valid' => false, 'message' => 'Invalid token format.'];
    
    list($payloadBase64, $signatureBase64) = $parts;
    $payloadJson = base64_decode($payloadBase64);
    $signature = base64_decode($signatureBase64);
    $payload = json_decode($payloadJson, true);

    // 1. Verify RSA Signature
    $ok = openssl_verify($payloadJson, $signature, $publicKey, OPENSSL_ALGO_SHA256);
    if ($ok !== 1) {
        return ['valid' => false, 'message' => 'Signature mismatch. Token is corrupted.'];
    }

    // 2. Verify Domain Match
    $currentDomain = preg_replace('#^https?://#', '', strtolower($_SERVER['HTTP_HOST'] ?? ''));
    $licenseDomain = preg_replace('#^https?://#', '', strtolower($payload['domain_name']));
    
    if (explode(':', $currentDomain)[0] !== explode(':', $licenseDomain)[0]) {
        return ['valid' => false, 'message' => 'Domain mismatch.'];
    }

    return ['valid' => true, 'payload' => $payload];
}</code></pre>
    </div>
</div>

</body>
</html>
