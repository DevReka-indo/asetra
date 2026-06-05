<?php

namespace Tests;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Role;
use App\Models\Position;
use App\Models\LokasiAset;
use App\Models\JenisKategori;
use App\Models\KategoriAset;
use App\Models\DataAset;

$kernel = $app->make(Kernel::class);
// Bootstrap Laravel by handling a dummy request
$kernel->handle(Request::create('/up'));

DB::beginTransaction();

try {
    // 1. Setup mock user and token
    $lokasiId = null;
    $jenisId = null;
    $kategoriId = null;
    $asetId = null;

    $role = Role::firstOrCreate(['id_role' => 1], ['nm_role' => 'Superadmin', 'desc_role' => 'Superadmin']);
    $position = Position::firstOrCreate(['id_position' => 1], ['nm_position' => 'Staff IT', 'desc_position' => 'Staff IT']);
    
    $user = User::create([
        'firstname' => 'API',
        'lastname' => 'Test',
        'email' => 'api_test_verify@example.com',
        'nip' => '888888888',
        'password' => Hash::make('password123'),
        'phone_number' => '08123456789',
        'role_id_role' => $role->id_role,
        'position_id_position' => $position->id_position,
    ]);

    $payload = [
        'id' => $user->id,
        'created_at' => now()->timestamp,
    ];
    $token = Crypt::encryptString(json_encode($payload));
    $headers = [
        'HTTP_Authorization' => 'Bearer ' . $token,
        'HTTP_Accept' => 'application/json',
    ];

    echo "--- STARTING API VERIFICATION RUNNER ---\n\n";

    // Test 1: Public Login
    echo "1. Testing POST /api/login ... ";
    $req = Request::create('/api/login', 'POST', [
        'credential' => 'api_test_verify@example.com',
        'password' => 'password123'
    ]);
    $resp = $kernel->handle($req);
    $content = json_decode($resp->getContent(), true);
    if ($resp->getStatusCode() === 200 && isset($content['status']) && $content['status'] === 'success') {
        echo "SUCCESS\n";
    } else {
        echo "FAILED: Status Code: " . $resp->getStatusCode() . " Content: " . $resp->getContent() . "\n";
    }

    // Test 2: Me endpoint
    echo "2. Testing GET /api/me ... ";
    $req = Request::create('/api/me', 'GET', [], [], [], $headers);
    $resp = $kernel->handle($req);
    $content = json_decode($resp->getContent(), true);
    if ($resp->getStatusCode() === 200 && isset($content['data']['email']) && $content['data']['email'] === 'api_test_verify@example.com') {
        echo "SUCCESS\n";
    } else {
        echo "FAILED: " . $resp->getContent() . "\n";
    }

    // Test 3: Create Location
    echo "3. Testing POST /api/lokasi-aset ... ";
    $req = Request::create('/api/lokasi-aset', 'POST', [
        'kode_lokasi' => 'VERIFY_LOK_1',
        'nama_lokasi' => 'Verify Location 1',
        'detail_lokasi' => 'Test detail'
    ], [], [], $headers);
    $resp = $kernel->handle($req);
    $content = json_decode($resp->getContent(), true);
    if ($resp->getStatusCode() === 210) {
        echo "SUCCESS\n";
        $lokasiId = $content['data']['lokasi_id'];
    } else {
        echo "FAILED: " . $resp->getContent() . "\n";
    }

    // Test 4: Create Jenis Kategori
    echo "4. Testing POST /api/jenis-kategori ... ";
    $verifyPrefix = (string) rand(100, 999);
    $req = Request::create('/api/jenis-kategori', 'POST', [
        'kode_awalan' => $verifyPrefix,
        'nama_jenis' => 'Verify Jenis ' . $verifyPrefix,
    ], [], [], $headers);
    $resp = $kernel->handle($req);
    $content = json_decode($resp->getContent(), true);
    if ($resp->getStatusCode() === 210) {
        echo "SUCCESS\n";
        $jenisId = $content['data']['id'];
    } else {
        echo "FAILED: " . $resp->getContent() . "\n";
        $jenisId = null;
    }

    // Test 5: Create Kategori Aset
    echo "5. Testing POST /api/kategori-aset ... ";
    $req = Request::create('/api/kategori-aset', 'POST', [
        'kode' => $verifyPrefix . '01',
        'nama' => 'Verify Kategori ' . $verifyPrefix . '01',
        'jenis_kategori_id' => $jenisId
    ], [], [], $headers);
    $resp = $kernel->handle($req);
    $content = json_decode($resp->getContent(), true);
    if ($resp->getStatusCode() === 210) {
        echo "SUCCESS\n";
        $kategoriId = $content['data']['id'];
    } else {
        echo "FAILED: " . $resp->getContent() . "\n";
        $kategoriId = null;
    }

    // Test 6: Create Data Aset
    echo "6. Testing POST /api/data-aset ... ";
    $photo = UploadedFile::fake()->image('verify_asset.jpg');
    $files = ['foto' => [$photo]];
    $req = Request::create('/api/data-aset', 'POST', [
        'nama_aset' => 'Verify Asset Laptop',
        'kategori_id' => $kategoriId,
        'kode_organisasi' => 'director_1',
        'lokasi_id' => $lokasiId,
        'merek' => 'Verify Merek',
        'deskripsi' => 'Verify Deskripsi',
        'tanggal_kapitalisasi' => '2026-05-01',
        'pic_id' => $user->id,
        'penanggung_jawab_id' => $user->id,
        'bast' => 'BAST-VERIFY',
        'status_kondisi' => 'Baik',
        'status_aset' => 'Aktif',
        'keterangan' => 'Verify Keterangan',
    ], [], $files, $headers);
    $resp = $kernel->handle($req);
    $content = json_decode($resp->getContent(), true);
    if ($resp->getStatusCode() === 210) {
        echo "SUCCESS\n";
        $asetId = $content['data']['id'];
    } else {
        echo "FAILED: " . $resp->getContent() . "\n";
    }

    // Test 7: Show Data Aset
    echo "7. Testing GET /api/data-aset/{id} ... ";
    $req = Request::create('/api/data-aset/' . $asetId, 'GET', [], [], [], $headers);
    $resp = $kernel->handle($req);
    $content = json_decode($resp->getContent(), true);
    if ($resp->getStatusCode() === 200) {
        echo "SUCCESS\n";
    } else {
        echo "FAILED: " . $resp->getContent() . "\n";
    }

    // Test 8: Submit Repair request
    echo "8. Testing POST /api/perbaikan ... ";
    $photoPerbaikan = UploadedFile::fake()->image('broken_verify.jpg');
    $req = Request::create('/api/perbaikan', 'POST', [
        'aset_id' => $asetId,
        'deskripsi_kerusakan' => 'Power supply verify',
        'tingkat_urgensi' => 'sedang'
    ], [], ['foto_kerusakan' => $photoPerbaikan], $headers);
    $resp = $kernel->handle($req);
    $content = json_decode($resp->getContent(), true);
    if ($resp->getStatusCode() === 210) {
        echo "SUCCESS\n";
        $perbId = $content['data']['id'];
    } else {
        echo "FAILED: " . $resp->getContent() . "\n";
    }

    // Test 9: Process Repair request
    echo "9. Testing PUT /api/perbaikan/{id}/proses ... ";
    $req = Request::create("/api/perbaikan/{$perbId}/proses", 'PUT', [
        'aksi' => 'disetujui'
    ], [], [], $headers);
    $resp = $kernel->handle($req);
    if ($resp->getStatusCode() === 200) {
        echo "SUCCESS\n";
    } else {
        echo "FAILED: " . $resp->getContent() . "\n";
    }

    // Test 10: Complete Repair request
    echo "10. Testing PUT /api/perbaikan/{id}/selesai ... ";
    $req = Request::create("/api/perbaikan/{$perbId}/selesai", 'PUT', [
        'kondisi_setelah' => 'Baik',
        'catatan' => 'Verify fixed'
    ], [], [], $headers);
    $resp = $kernel->handle($req);
    if ($resp->getStatusCode() === 200) {
        echo "SUCCESS\n";
    } else {
        echo "FAILED: " . $resp->getContent() . "\n";
    }

    // Test 11: Create Monitoring Log
    echo "11. Testing POST /api/monitoring ... ";
    $req = Request::create('/api/monitoring', 'POST', [
        'aset_id' => $asetId,
        'kondisi' => 'Baik',
        'status_aset' => 'Aktif',
        'lokasi_id' => $lokasiId,
        'keterangan' => 'Verify monitoring check'
    ], [], [], $headers);
    $resp = $kernel->handle($req);
    if ($resp->getStatusCode() === 210) {
        echo "SUCCESS\n";
    } else {
        echo "FAILED: " . $resp->getContent() . "\n";
    }

    // Test 12: Print label for selected assets
    echo "12. Testing POST /api/data-aset/cetak-label ... ";
    $req = Request::create('/api/data-aset/cetak-label', 'POST', [
        'ids' => [$asetId]
    ], [], [], $headers);
    $resp = $kernel->handle($req);
    if ($resp->getStatusCode() === 200 && str_contains($resp->headers->get('Content-Type'), 'application/pdf')) {
        echo "SUCCESS (PDF Streamed)\n";
    } else {
        echo "FAILED: " . $resp->getStatusCode() . " Content-Type: " . $resp->headers->get('Content-Type') . "\n";
    }

    // Test 13: Print label for location
    echo "13. Testing GET /api/data-aset/cetak-label-lokasi/{id} ... ";
    $req = Request::create('/api/data-aset/cetak-label-lokasi/' . $lokasiId, 'GET', [], [], [], $headers);
    $resp = $kernel->handle($req);
    if ($resp->getStatusCode() === 200 && str_contains($resp->headers->get('Content-Type'), 'application/pdf')) {
        echo "SUCCESS (PDF Streamed)\n";
    } else {
        echo "FAILED: " . $resp->getStatusCode() . " Content-Type: " . $resp->headers->get('Content-Type') . "\n";
    }

    echo "\n--- ALL TESTS COMPLETED ---\n";
} catch (\Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
} finally {
    DB::rollBack();
    echo "Database changes rolled back.\n";
}
