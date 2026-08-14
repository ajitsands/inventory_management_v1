<?php
$domain = 'inventory.sandslab.com';
$resolvedIp = gethostbyname($domain);
echo "Resolved IP for $domain is: $resolvedIp\n";

if ($resolvedIp === $domain) {
    $resolvedIp = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
}

$postData = [
    'license_key' => 'INV-FC0B22-92DC01-2B1D24-B7F6F6',
    'domain_name' => $domain,
    'ip_address' => $resolvedIp
];

$url = 'https://key.sandslab.com/public/api/activate';
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
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "cURL Error: $curlError\n";
echo "Response JSON: \n";
print_r(json_decode($responseJson, true));
