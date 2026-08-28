<?php

namespace Tests\Feature;

use App\Models\AsetFoto;
use App\Models\DataAset;
use App\Models\JenisKategori;
use App\Models\KategoriAset;
use App\Models\LokasiAset;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StockOpnameLifecycleTest extends TestCase
{
    use LazilyRefreshDatabase;

    private int $nextFixtureId = 20;

    private int $nextRoleId = 200;

    #[DataProvider('surfaces')]
    public function test_active_session_accepts_a_valid_finding(string $surface): void
    {
        $departmentId = $this->createDepartment('Participating Department');
        $user = $this->createUser(departmentId: $departmentId);
        $session = $this->createSession($user, 'aktif');
        $asset = $this->createAsset(departmentId: $departmentId);

        $response = $this->scan($surface, $user, $session, (string) $asset->id);

        $this->assertScanSucceeded($response, $surface);
        $this->assertDatabaseHas('stock_opname_detail', [
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
            'dicek_oleh' => $user->id,
        ]);
    }

    #[DataProvider('completedFindingCases')]
    public function test_completed_session_rejects_qr_and_manual_findings(string $surface, string $referenceType): void
    {
        $departmentId = $this->createDepartment('Participating Department');
        $user = $this->createUser(departmentId: $departmentId);
        $session = $this->createSession($user, 'selesai');
        $asset = $this->createAsset(departmentId: $departmentId);
        $assetReference = $referenceType === 'qr' ? $asset->nomor_aset : (string) $asset->id;

        $response = $this->scan($surface, $user, $session, $assetReference);

        $response->assertStatus(409);
        $this->assertDatabaseMissing('stock_opname_detail', [
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
        ]);
    }

    #[DataProvider('surfaces')]
    public function test_ordinary_user_asset_scope_remains_enforced(string $surface): void
    {
        $userDepartmentId = $this->createDepartment('User Department');
        $otherDepartmentId = $this->createDepartment('Other Department');
        $user = $this->createUser(departmentId: $userDepartmentId);
        $otherUser = $this->createUser(departmentId: $otherDepartmentId);
        $session = $this->createSession($user, 'aktif');
        $asset = $this->createAsset(departmentId: $otherDepartmentId, pic: $otherUser);

        $response = $this->scan($surface, $user, $session, (string) $asset->id);

        $response->assertForbidden();
        $this->assertDatabaseMissing('stock_opname_detail', [
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
        ]);
    }

    #[DataProvider('surfaces')]
    public function test_active_session_can_transition_to_completed(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'aktif');

        $response = $this->updateStatus($surface, $manager, $session, 'selesai');

        $this->assertManagementMutationSucceeded($response, $surface);
        $this->assertSame('selesai', $session->fresh()->status);
    }

    #[DataProvider('surfaces')]
    public function test_completed_session_cannot_be_reopened_through_status_endpoint(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'selesai');

        $response = $this->updateStatus($surface, $manager, $session, 'aktif');

        $this->assertStateConflict($response, $surface);
        $this->assertSame('selesai', $session->fresh()->status);
    }

    public function test_completed_session_cannot_be_reopened_through_general_update(): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'selesai');

        $response = $this->actingAs($manager)->put(route('stock-opname.update', $session), [
            'periode' => 'Changed Period',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2026-09-30',
            'keterangan' => 'Attempted reopen',
            'status' => 'aktif',
        ]);

        $response->assertRedirect()->assertSessionHas('error');
        $session->refresh();
        $this->assertSame('selesai', $session->status);
        $this->assertSame('Lifecycle Test Period', $session->periode);
    }

    public function test_completed_session_metadata_can_be_updated_without_reopening(): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'selesai');

        $response = $this->actingAs($manager)->put(route('stock-opname.update', $session), [
            'periode' => 'Corrected Label',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2026-09-30',
            'keterangan' => 'Metadata only',
            'status' => 'selesai',
        ]);

        $response->assertRedirect(route('stock-opname.index'))->assertSessionHas('success');
        $session->refresh();
        $this->assertSame('selesai', $session->status);
        $this->assertSame('Corrected Label', $session->periode);
    }

    #[DataProvider('surfaces')]
    public function test_active_session_cannot_be_synchronized(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'aktif');
        $asset = $this->createAsset();
        $this->createFinding($session, $asset, $manager, condition: 'Rusak');

        $response = $this->synchronize($surface, $manager, $session);

        $this->assertStateConflict($response, $surface);
        $this->assertSame('Baik', $asset->fresh()->status_kondisi);
        $this->assertNull($session->fresh()->synced_at);
    }

    #[DataProvider('surfaces')]
    public function test_completed_session_synchronizes_master_data_and_photo_once(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'selesai');
        $targetLocation = $this->createLocation();
        $asset = $this->createAsset();
        $finding = $this->createFinding(
            $session,
            $asset,
            $manager,
            condition: 'Rusak',
            location: (string) $targetLocation->lokasi_id,
            photoPath: 'stock_opname_foto/finding.jpg',
        );

        $response = $this->synchronize($surface, $manager, $session);

        $this->assertManagementMutationSucceeded($response, $surface);
        $asset->refresh();
        $this->assertSame('Rusak', $asset->status_kondisi);
        $this->assertSame($targetLocation->lokasi_id, $asset->lokasi_id);
        $this->assertNotNull($session->fresh()->synced_at);
        $this->assertDatabaseHas('aset_foto', [
            'aset_id' => $asset->id,
            'path_foto' => $finding->foto_temuan,
        ]);
        $this->assertSame(1, AsetFoto::query()->where('aset_id', $asset->id)->count());
    }

    #[DataProvider('surfaces')]
    public function test_second_sync_does_not_reapply_stale_findings_or_duplicate_photos(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'selesai');
        $asset = $this->createAsset();
        $finding = $this->createFinding(
            $session,
            $asset,
            $manager,
            condition: 'Rusak',
            photoPath: 'stock_opname_foto/stale-finding.jpg',
        );
        $this->synchronize($surface, $manager, $session);
        $asset->refresh()->update(['status_kondisi' => 'Baik']);
        $finding->update(['kondisi_temuan' => 'Bongkar']);

        $response = $this->synchronize($surface, $manager, $session);

        $this->assertStateConflict($response, $surface);
        $this->assertSame('Baik', $asset->fresh()->status_kondisi);
        $this->assertSame(1, AsetFoto::query()->where('aset_id', $asset->id)->count());
    }

    #[DataProvider('surfaces')]
    public function test_failed_sync_rolls_back_master_changes_and_does_not_mark_session_synchronized(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'selesai');
        $asset = $this->createAsset();
        $this->createFinding($session, $asset, $manager, condition: 'Rusak');
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER fail_stock_opname_sync
            BEFORE UPDATE OF status_kondisi ON data_aset
            BEGIN
                SELECT RAISE(ABORT, 'forced stock opname sync failure');
            END
            SQL);

        $response = $this->synchronize($surface, $manager, $session);

        if ($surface === 'api') {
            $response->assertServerError();
        } else {
            $response->assertRedirect()->assertSessionHas('error');
        }

        $this->assertSame('Baik', $asset->fresh()->status_kondisi);
        $this->assertNull($session->fresh()->synced_at);
    }

    /** @return array<string, array{string}> */
    public static function surfaces(): array
    {
        return ['web' => ['web'], 'api' => ['api']];
    }

    /** @return array<string, array{string, string}> */
    public static function completedFindingCases(): array
    {
        return [
            'web QR scan' => ['web', 'qr'],
            'web manual finding' => ['web', 'manual'],
            'API QR scan' => ['api', 'qr'],
            'API manual finding' => ['api', 'manual'],
        ];
    }

    private function createDepartment(string $name): int
    {
        return DB::table('department')->insertGetId(['name_department' => $name], 'id_department');
    }

    private function createUser(?int $roleId = null, string $roleName = 'Staff', ?int $departmentId = null): User
    {
        $roleId ??= $this->nextRoleId++;
        DB::table('role')->insert(['id_role' => $roleId, 'nm_role' => $roleName]);
        DB::table('position')->insertOrIgnore(['id_position' => 1, 'nm_position' => 'Test Position']);

        return User::factory()->create([
            'role_id_role' => $roleId,
            'position_id_position' => 1,
            'department_id_department' => $departmentId,
        ]);
    }

    private function createSession(User $creator, string $status): StockOpname
    {
        return StockOpname::query()->create([
            'periode' => 'Lifecycle Test Period',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_berakhir' => '2026-08-31',
            'keterangan' => 'Lifecycle test fixture',
            'created_by' => $creator->id,
            'status' => $status,
        ]);
    }

    private function createLocation(): LokasiAset
    {
        $fixtureId = $this->nextFixtureId++;

        return LokasiAset::query()->create([
            'kode_lokasi' => "SO-{$fixtureId}",
            'nama_lokasi' => "Stock Opname Location {$fixtureId}",
        ]);
    }

    private function createAsset(?int $departmentId = null, ?User $pic = null): DataAset
    {
        $fixtureId = $this->nextFixtureId++;
        $location = $this->createLocation();
        $categoryType = JenisKategori::query()->create([
            'kode_awalan' => (string) $fixtureId,
            'nama_jenis' => "Stock Opname Type {$fixtureId}",
        ]);
        $category = KategoriAset::query()->create([
            'kode' => (string) (700 + $fixtureId),
            'nama' => "Stock Opname Category {$fixtureId}",
            'jenis_kategori_id' => $categoryType->id,
        ]);

        return DataAset::query()->create([
            'nama_aset' => "Stock Opname Asset {$fixtureId}",
            'kategori_id' => $category->id,
            'lokasi_id' => $location->lokasi_id,
            'merek' => 'Test',
            'deskripsi' => 'Stock opname lifecycle test asset',
            'tahun_kapitalisasi' => 2026,
            'id_department' => $departmentId,
            'pic_id' => $pic?->id,
            'penanggung_jawab_id' => $pic?->id,
            'status_kondisi' => 'Baik',
            'status_aset' => 'Aktif',
        ]);
    }

    private function createFinding(
        StockOpname $session,
        DataAset $asset,
        User $checker,
        string $condition,
        ?string $location = null,
        ?string $photoPath = null,
    ): StockOpnameDetail {
        return StockOpnameDetail::query()->create([
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
            'dicek_oleh' => $checker->id,
            'tanggal_cek' => '2026-08-20',
            'kondisi_temuan' => $condition,
            'lokasi_temuan' => $location,
            'foto_temuan' => $photoPath,
            'keterangan' => 'Lifecycle finding',
        ]);
    }

    private function scan(
        string $surface,
        User $user,
        StockOpname $session,
        string $assetReference,
    ): TestResponse {
        $payload = [
            'stock_opname_id' => $session->id,
            'aset_id' => $assetReference,
            'kondisi_temuan' => 'Baik',
            'lokasi_temuan' => '1',
            'keterangan' => 'Lifecycle scan',
        ];

        if ($surface === 'api') {
            return $this->withHeaders($this->apiHeaders($user))->postJson('/api/stock-opname/scan', $payload);
        }

        return $this->actingAs($user)->post(route('stock-opname.scanStore'), $payload, [
            'Accept' => 'application/json',
        ]);
    }

    private function updateStatus(
        string $surface,
        User $user,
        StockOpname $session,
        string $status,
    ): TestResponse {
        if ($surface === 'api') {
            return $this->withHeaders($this->apiHeaders($user))
                ->putJson("/api/stock-opname/{$session->id}/status", ['status' => $status]);
        }

        return $this->actingAs($user)
            ->put(route('stock-opname.update-status', $session), ['status' => $status]);
    }

    private function synchronize(string $surface, User $user, StockOpname $session): TestResponse
    {
        if ($surface === 'api') {
            return $this->withHeaders($this->apiHeaders($user))
                ->postJson("/api/stock-opname/{$session->id}/sync");
        }

        return $this->actingAs($user)->post(route('stock-opname.sync', $session));
    }

    /** @return array<string, string> */
    private function apiHeaders(User $user): array
    {
        $token = Crypt::encryptString(json_encode([
            'id' => $user->id,
            'created_at' => now()->timestamp,
        ], JSON_THROW_ON_ERROR));

        return ['Authorization' => "Bearer {$token}", 'Accept' => 'application/json'];
    }

    private function assertScanSucceeded(TestResponse $response, string $surface): void
    {
        $response->assertStatus($surface === 'api' ? 210 : 200);
    }

    private function assertManagementMutationSucceeded(TestResponse $response, string $surface): void
    {
        if ($surface === 'api') {
            $response->assertOk();

            return;
        }

        $response->assertRedirect()->assertSessionHas('success');
    }

    private function assertStateConflict(TestResponse $response, string $surface): void
    {
        if ($surface === 'api') {
            $response->assertStatus(409);

            return;
        }

        $response->assertRedirect()->assertSessionHas('error');
    }
}
