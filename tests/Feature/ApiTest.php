<?php

namespace Tests\Feature;

use App\Models\DataAset;
use App\Models\JenisKategori;
use App\Models\KategoriAset;
use App\Models\LokasiAset;
use App\Models\StockOpname;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $token;

    protected $headers;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create dependencies
        DB::table('role')->insert([
            'id_role' => 1,
            'nm_role' => 'Superadmin',
        ]);

        DB::table('position')->insert([
            'id_position' => 1,
            'nm_position' => 'Staff IT',
        ]);

        // 2. Create a test user
        $this->user = User::factory()->create([
            'firstname' => 'API',
            'lastname' => 'Test User',
            'email' => 'api_test_user@example.com',
            'nip' => '999999999',
            'password' => Hash::make('password123'),
            'phone_number' => '080000000000',
            'role_id_role' => 1,
            'position_id_position' => 1,
        ]);

        // 3. Generate Bearer Token (Alternative A)
        $payload = [
            'id' => $this->user->id,
            'created_at' => now()->timestamp,
        ];
        $this->token = Crypt::encryptString(json_encode($payload));

        $this->headers = [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/json',
        ];
    }

    /**
     * Test API Login
     */
    public function test_api_login_success()
    {
        $response = $this->postJson('/api/login', [
            'credential' => 'api_test_user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'token',
                    'user',
                ],
            ]);
    }

    public function test_api_login_failed()
    {
        $response = $this->postJson('/api/login', [
            'credential' => 'api_test_user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Password yang Anda masukkan salah.',
            ]);
    }

    /**
     * Test Middleware Authentication
     */
    public function test_api_me_authenticated()
    {
        $response = $this->withHeaders($this->headers)->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'api_test_user@example.com');
    }

    public function test_api_me_unauthenticated()
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Token tidak ditemukan. Silakan gunakan header Authorization Bearer.',
            ]);
    }

    /**
     * Test Lokasi Aset API (CRUD)
     */
    public function test_lokasi_aset_crud()
    {
        // 1. Store
        $responseStore = $this->withHeaders($this->headers)->postJson('/api/lokasi-aset', [
            'kode_lokasi' => 'TEST_LOK_1',
            'nama_lokasi' => 'Test Location 1',
            'detail_lokasi' => 'Detail of test location 1',
        ]);
        $responseStore->assertStatus(210);
        $lokasiId = $responseStore->json('data.lokasi_id');

        // 2. Index
        $responseIndex = $this->withHeaders($this->headers)->getJson('/api/lokasi-aset');
        $responseIndex->assertStatus(200);

        // 3. Show
        $responseShow = $this->withHeaders($this->headers)->getJson('/api/lokasi-aset/'.$lokasiId);
        $responseShow->assertStatus(200)
            ->assertJsonPath('data.nama_lokasi', 'Test Location 1');

        // 4. Update
        $responseUpdate = $this->withHeaders($this->headers)->putJson('/api/lokasi-aset/'.$lokasiId, [
            'kode_lokasi' => 'TEST_LOK_1_EDIT',
            'nama_lokasi' => 'Test Location 1 Edited',
            'detail_lokasi' => 'Updated details',
        ]);
        $responseUpdate->assertStatus(200)
            ->assertJsonPath('data.nama_lokasi', 'Test Location 1 Edited');

        // 5. Destroy
        $responseDestroy = $this->withHeaders($this->headers)->deleteJson('/api/lokasi-aset/'.$lokasiId);
        $responseDestroy->assertStatus(200);
    }

    /**
     * Test Jenis Kategori API (CRUD)
     */
    public function test_jenis_kategori_crud()
    {
        // 1. Store
        $responseStore = $this->withHeaders($this->headers)->postJson('/api/jenis-kategori', [
            'kode_awalan' => '9',
            'nama_jenis' => 'Test Jenis Kategori',
            'warna_label' => '#ea6565',
        ]);
        $responseStore->assertStatus(210);
        $jenisId = $responseStore->json('data.id');

        // 2. Show
        $responseShow = $this->withHeaders($this->headers)->getJson('/api/jenis-kategori/'.$jenisId);
        $responseShow->assertStatus(200);

        // 3. Update
        $responseUpdate = $this->withHeaders($this->headers)->putJson('/api/jenis-kategori/'.$jenisId, [
            'kode_awalan' => '9',
            'nama_jenis' => 'Test Jenis Kategori Updated',
            'warna_label' => '#ea6565',
        ]);
        $responseUpdate->assertStatus(200);

        // 4. Destroy
        $responseDestroy = $this->withHeaders($this->headers)->deleteJson('/api/jenis-kategori/'.$jenisId);
        $responseDestroy->assertStatus(200);
    }

    /**
     * Test Kategori Aset API (CRUD)
     */
    public function test_kategori_aset_crud()
    {
        // Setup JenisKategori dependency
        $jenis = JenisKategori::create([
            'kode_awalan' => '8',
            'nama_jenis' => 'Test Jenis 8',
            'warna_label' => '#333333',
        ]);

        // 1. Store
        $responseStore = $this->withHeaders($this->headers)->postJson('/api/kategori-aset', [
            'kode' => '801',
            'nama' => 'Test Kategori Aset',
            'jenis_kategori_id' => $jenis->id,
        ]);
        $responseStore->assertStatus(210);
        $kategoriId = $responseStore->json('data.id');

        // 2. Index
        $responseIndex = $this->withHeaders($this->headers)->getJson('/api/kategori-aset');
        $responseIndex->assertStatus(200);

        // 3. Show
        $responseShow = $this->withHeaders($this->headers)->getJson('/api/kategori-aset/'.$kategoriId);
        $responseShow->assertStatus(200);

        // 4. Update
        $responseUpdate = $this->withHeaders($this->headers)->putJson('/api/kategori-aset/'.$kategoriId, [
            'kode' => '802',
            'nama' => 'Test Kategori Aset Updated',
            'jenis_kategori_id' => $jenis->id,
        ]);
        $responseUpdate->assertStatus(200);

        // 5. Destroy
        $responseDestroy = $this->withHeaders($this->headers)->deleteJson('/api/kategori-aset/'.$kategoriId);
        $responseDestroy->assertStatus(200);
    }

    /**
     * Test Data Aset API (CRUD)
     */
    public function test_data_aset_crud()
    {
        Storage::fake('public');

        DB::table('director')->insert([
            'id_director' => 1,
            'name_director' => 'Test Directorate',
            'kode_director' => 'TEST',
        ]);

        $lokasi = LokasiAset::create([
            'kode_lokasi' => 'TEST_LOK_ASET',
            'nama_lokasi' => 'Test Location Asset',
            'detail_lokasi' => 'Detail',
        ]);

        $jenis = JenisKategori::create([
            'kode_awalan' => '7',
            'nama_jenis' => 'Test Jenis 7',
            'warna_label' => '#444444',
        ]);

        $kategori = KategoriAset::create([
            'kode' => '701',
            'nama' => 'Test Kategori 7',
            'jenis_kategori_id' => $jenis->id,
        ]);

        // 1. Store Data Aset
        $photo = UploadedFile::fake()->image('asset.jpg');
        $responseStore = $this->withHeaders($this->headers)->postJson('/api/data-aset', [
            'nama_aset' => 'Test Laptop Dev',
            'kategori_id' => $kategori->id,
            'kode_organisasi' => 'director_1',
            'lokasi_id' => $lokasi->lokasi_id,
            'merek' => 'Lenovo Thinkpad',
            'deskripsi' => 'Thinkpad X1 Carbon',
            'tahun_kapitalisasi' => 2026,
            'pic_id' => $this->user->id,
            'penanggung_jawab_id' => $this->user->id,
            'bast' => 'BAST-001',
            'status_kondisi' => 'Baik',
            'status_aset' => 'Aktif',
            'keterangan' => 'Rutin',
            'foto' => [$photo],
        ]);
        $responseStore->assertStatus(210);
        $asetId = $responseStore->json('data.id');

        // 2. Index
        $responseIndex = $this->withHeaders($this->headers)->getJson('/api/data-aset');
        $responseIndex->assertStatus(200);

        // 3. Show
        $responseShow = $this->withHeaders($this->headers)->getJson('/api/data-aset/'.$asetId);
        $responseShow->assertStatus(200);

        // 4. Update
        $photoNew = UploadedFile::fake()->image('asset_new.jpg');
        $responseUpdate = $this->withHeaders($this->headers)->putJson('/api/data-aset/'.$asetId, [
            'nama_aset' => 'Test Laptop Dev Updated',
            'kategori_id' => $kategori->id,
            'kode_organisasi' => 'director_1',
            'lokasi_id' => $lokasi->lokasi_id,
            'merek' => 'Lenovo Thinkpad Updated',
            'deskripsi' => 'Thinkpad X1 Carbon Gen 10',
            'tahun_kapitalisasi' => 2026,
            'pic_id' => $this->user->id,
            'penanggung_jawab_id' => $this->user->id,
            'bast' => 'BAST-001-REV',
            'status_kondisi' => 'Baik',
            'status_aset' => 'Aktif',
            'keterangan' => 'Rutin',
            'foto_baru' => [$photoNew],
        ]);
        $responseUpdate->assertStatus(200);

        // 5. Destroy (Soft Delete) - Requires PDF file
        $pdf = UploadedFile::fake()->create('deletion.pdf', 500, 'application/pdf');
        $responseDestroy = $this->withHeaders($this->headers)->postJson('/api/data-aset/'.$asetId, [
            '_method' => 'DELETE',
            'dokumen_penghapusan' => $pdf,
        ]);
        $responseDestroy->assertStatus(200);
    }

    /**
     * Test Stock Opname API Endpoints
     */
    public function test_stock_opname_endpoints()
    {
        Storage::fake('public');

        // Create a mock active session
        $session = StockOpname::create([
            'periode' => 'Mei 2026',
            'tanggal_mulai' => '2026-05-01',
            'tanggal_berakhir' => '2026-05-31',
            'keterangan' => 'Test',
            'created_by' => $this->user->id,
            'status' => 'aktif',
        ]);

        $lokasi = LokasiAset::create([
            'kode_lokasi' => 'TEST_SO_LOK',
            'nama_lokasi' => 'Test SO Lok',
        ]);

        $jenis = JenisKategori::create([
            'kode_awalan' => '6',
            'nama_jenis' => 'Test Jenis 6',
        ]);

        $kategori = KategoriAset::create([
            'kode' => '601',
            'nama' => 'Test Kat 6',
            'jenis_kategori_id' => $jenis->id,
        ]);

        $aset = DataAset::create([
            'nama_aset' => 'Test Aset SO',
            'kategori_id' => $kategori->id,
            'lokasi_id' => $lokasi->lokasi_id,
            'merek' => 'Test',
            'deskripsi' => 'Test',
            'tahun_kapitalisasi' => 2026,
            'pic_id' => $this->user->id,
            'penanggung_jawab_id' => $this->user->id,
            'status_kondisi' => 'Baik',
            'status_aset' => 'Aktif',
        ]);

        // 1. Scan store (record find)
        $photo = UploadedFile::fake()->image('opname.jpg');
        $responseScan = $this->withHeaders($this->headers)->postJson('/api/stock-opname/scan', [
            'stock_opname_id' => $session->id,
            'aset_id' => (string) $aset->id,
            'kondisi_temuan' => 'Rusak',
            'lokasi_temuan' => (string) $lokasi->lokasi_id,
            'foto_temuan' => $photo,
            'keterangan' => 'Layar retak',
        ]);
        $responseScan->assertStatus(210);

        // 2. Show session
        $responseShow = $this->withHeaders($this->headers)->getJson('/api/stock-opname/'.$session->id);
        $responseShow->assertStatus(200)
            ->assertJsonPath('data.total_checked', 1);

        // 3. Finalize and sync findings to master
        $responseFinalize = $this->withHeaders($this->headers)->putJson("/api/stock-opname/{$session->id}/status", [
            'status' => 'selesai',
        ]);
        $responseFinalize->assertStatus(200);

        $responseSync = $this->withHeaders($this->headers)->postJson("/api/stock-opname/{$session->id}/sync");
        $responseSync->assertStatus(200);
        $this->assertEquals('Rusak', $aset->fresh()->status_kondisi);
    }

    /**
     * Test Pengajuan Perbaikan API Endpoints
     */
    public function test_perbaikan_endpoints()
    {
        Storage::fake('public');

        $lokasi = LokasiAset::create([
            'kode_lokasi' => 'TEST_LOK_PERB',
            'nama_lokasi' => 'Test Lok Perb',
        ]);

        $jenis = JenisKategori::create([
            'kode_awalan' => '5',
            'nama_jenis' => 'Test Jenis 5',
        ]);

        $kategori = KategoriAset::create([
            'kode' => '501',
            'nama' => 'Test Kat 5',
            'jenis_kategori_id' => $jenis->id,
        ]);

        $aset = DataAset::create([
            'nama_aset' => 'Test Aset Perb',
            'kategori_id' => $kategori->id,
            'lokasi_id' => $lokasi->lokasi_id,
            'merek' => 'Test',
            'deskripsi' => 'Test',
            'tahun_kapitalisasi' => 2026,
            'pic_id' => $this->user->id,
            'penanggung_jawab_id' => $this->user->id,
            'status_kondisi' => 'Baik',
            'status_aset' => 'Aktif',
        ]);

        // 1. Store request
        $photo = UploadedFile::fake()->image('broken.jpg');
        $responseStore = $this->withHeaders($this->headers)->postJson('/api/perbaikan', [
            'aset_id' => $aset->id,
            'deskripsi_kerusakan' => 'Power supply meledak',
            'tingkat_urgensi' => 'tinggi',
            'foto_kerusakan' => $photo,
        ]);
        $responseStore->assertStatus(210);
        $perbId = $responseStore->json('data.id');
        $this->assertEquals('Dalam Perbaikan', $aset->fresh()->status_aset);

        // 2. Show request
        $responseShow = $this->withHeaders($this->headers)->getJson('/api/perbaikan/'.$perbId);
        $responseShow->assertStatus(200);

        // 3. Process request (Approve)
        $responseProses = $this->withHeaders($this->headers)->putJson("/api/perbaikan/{$perbId}/proses", [
            'aksi' => 'disetujui',
        ]);
        $responseProses->assertStatus(200);

        // 4. Mark finished (Selesai)
        $responseSelesai = $this->withHeaders($this->headers)->putJson("/api/perbaikan/{$perbId}/selesai", [
            'kondisi_setelah' => 'Baik',
            'catatan' => 'Diganti power supply baru',
        ]);
        $responseSelesai->assertStatus(200);

        $this->assertEquals('Baik', $aset->fresh()->status_kondisi);
        $this->assertEquals('Aktif', $aset->fresh()->status_aset);
    }

    /**
     * Test Monitoring (Log Aset) API Endpoints
     */
    public function test_monitoring_endpoints()
    {
        Storage::fake('public');

        $lokasi = LokasiAset::create([
            'kode_lokasi' => 'TEST_MON_LOK',
            'nama_lokasi' => 'Test Mon Lok',
        ]);

        $jenis = JenisKategori::create([
            'kode_awalan' => '4',
            'nama_jenis' => 'Test Jenis 4',
        ]);

        $kategori = KategoriAset::create([
            'kode' => '401',
            'nama' => 'Test Kat 4',
            'jenis_kategori_id' => $jenis->id,
        ]);

        $aset = DataAset::create([
            'nama_aset' => 'Test Aset Mon',
            'kategori_id' => $kategori->id,
            'lokasi_id' => $lokasi->lokasi_id,
            'merek' => 'Test',
            'deskripsi' => 'Test',
            'tahun_kapitalisasi' => 2026,
            'pic_id' => $this->user->id,
            'penanggung_jawab_id' => $this->user->id,
            'status_kondisi' => 'Baik',
            'status_aset' => 'Aktif',
        ]);

        // 1. Add Log
        $photo = UploadedFile::fake()->image('monitoring.jpg');
        $responseStore = $this->withHeaders($this->headers)->postJson('/api/monitoring', [
            'aset_id' => $aset->id,
            'kondisi' => 'Rusak',
            'status_aset' => 'Dalam Perbaikan',
            'lokasi_id' => $lokasi->lokasi_id,
            'foto_bukti' => $photo,
            'keterangan' => 'Keyboard tidak responsif',
        ]);
        $responseStore->assertStatus(210);

        $this->assertEquals('Rusak', $aset->fresh()->status_kondisi);
        $this->assertEquals('Dalam Perbaikan', $aset->fresh()->status_aset);

        // 2. Index logs
        $responseIndex = $this->withHeaders($this->headers)->getJson('/api/monitoring');
        $responseIndex->assertStatus(200);
    }
}
