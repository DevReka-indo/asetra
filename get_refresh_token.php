<?php

require_once 'vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

$clientId = null;
$clientSecret = null;

if (file_exists('.env')) {
    $lines = file('.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1], " \t\n\r\0\x0B\"'");
            if ($key === 'GOOGLE_DRIVE_CLIENT_ID') $clientId = $val;
            if ($key === 'GOOGLE_DRIVE_CLIENT_SECRET') $clientSecret = $val;
        }
    }
}

if (!$clientId || !$clientSecret) {
    echo "=================================================================\n";
    echo "ERROR: GOOGLE_DRIVE_CLIENT_ID atau GOOGLE_DRIVE_CLIENT_SECRET belum diisi di .env\n";
    echo "=================================================================\n";
    echo "Silakan tambahkan kedua variabel tersebut di file .env Anda terlebih dahulu:\n";
    echo "GOOGLE_DRIVE_CLIENT_ID=xxx\n";
    echo "GOOGLE_DRIVE_CLIENT_SECRET=xxx\n\n";
    echo "Petunjuk Singkat:\n";
    echo "1. Buka Google Cloud Console (https://console.cloud.google.com/)\n";
    echo "2. Masuk ke 'APIs & Services' > 'Credentials'\n";
    echo "3. Klik '+ Create Credentials' > 'OAuth client ID'\n";
    echo "4. Pilih Application Type: 'Web application' atau 'Desktop app'\n";
    echo "5. Jika 'Web application', tambahkan Redirect URI: 'https://developers.google.com/oauthplayground'\n";
    echo "6. Salin Client ID dan Client Secret ke file .env Anda, lalu jalankan script ini lagi.\n";
    echo "=================================================================\n";
    exit(1);
}

$client = new Client();
$client->setClientId($clientId);
$client->setClientSecret($clientSecret);
$client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob'); // For Out-Of-Band or Desktop CLI copy-paste
$client->setAccessType('offline');
$client->setApprovalPrompt('force');
$client->addScope(Drive::DRIVE);

// Also try playground redirect URI if they chose Web App
// If using Desktop app, 'urn:ietf:wg:oauth:2.0:oob' is standard.

$authUrl = $client->createAuthUrl();

echo "=================================================================\n";
echo "PANDUAN MENDAPATKAN REFRESH TOKEN GOOGLE DRIVE (AKUN PERSONAL)\n";
echo "=================================================================\n\n";
echo "Langkah 1: Silakan buka URL berikut di browser Anda:\n\n";
echo $authUrl . "\n\n";
echo "Langkah 2: Login dengan akun Google/Gmail Anda, izinkan aplikasi.\n";
echo "Langkah 3: Google akan memberikan sebuah 'Authorization Code'.\n";
echo "           (Catatan: Jika Anda membuat tipe 'Web Application' dengan redirect URI ke oauthplayground,\n";
echo "           Anda bisa menggunakan https://developers.google.com/oauthplayground untuk mempermudah).\n\n";

echo "Masukkan 'Authorization Code' / Kode Otorisasi yang Anda dapatkan di sini: ";
$handle = fopen("php://stdin", "r");
$authCode = trim(fgets($handle));

if (empty($authCode)) {
    echo "Error: Kode otorisasi tidak boleh kosong.\n";
    exit(1);
}

try {
    echo "\nMenukar Kode Otorisasi dengan Access & Refresh Token...\n";
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
    
    if (isset($accessToken['error'])) {
        throw new \Exception($accessToken['error_description'] ?? $accessToken['error']);
    }

    $refreshToken = $accessToken['refresh_token'] ?? null;

    if (!$refreshToken) {
        echo "=================================================================\n";
        echo "PERINGATAN: Refresh Token tidak ditemukan!\n";
        echo "=================================================================\n";
        echo "Hal ini biasanya terjadi jika Anda tidak memaksa persetujuan ('prompt=consent') atau akun sudah terhubung.\n";
        echo "Silakan coba hapus akses aplikasi ini dari akun Google Anda (Security > Third-party apps with account access)\n";
        echo "lalu jalankan ulang script ini.\n";
        echo "=================================================================\n";
    } else {
        echo "\n=================================================================\n";
        echo "BERHASIL! BERIKUT ADALAH REFRESH TOKEN ANDA:\n";
        echo "=================================================================\n\n";
        echo "GOOGLE_DRIVE_REFRESH_TOKEN=" . $refreshToken . "\n\n";
        echo "Silakan salin baris di atas dan tempelkan ke file .env Anda!\n";
        echo "=================================================================\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
