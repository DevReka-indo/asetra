<?php

namespace Tests\Feature;

use App\Exports\StockOpnameExport;
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
            'deskripsi_temuan' => $asset->deskripsi,
        ]);
    }

    public function test_manual_check_page_preloads_asset_master_description(): void
    {
        $departmentId = $this->createDepartment('Manual Check Department');
        $user = $this->createUser(departmentId: $departmentId);
        $session = $this->createSession($user, 'aktif');
        $asset = $this->createAsset(departmentId: $departmentId);
        $asset->update(['deskripsi' => 'Deskripsi dari master aset']);

        $this->actingAs($user)
            ->get(route('stock-opname.user-show', $session))
            ->assertOk()
            ->assertSee('Deskripsi Aset')
            ->assertSee('data-aset-deskripsi="Deskripsi dari master aset"', false)
            ->assertSee('name="deskripsi_temuan"', false)
            ->assertDontSee('name="keterangan"', false);
    }

    #[DataProvider('surfaces')]
    public function test_submitted_description_is_stored_without_immediately_changing_master(string $surface): void
    {
        $departmentId = $this->createDepartment('Description Department');
        $user = $this->createUser(departmentId: $departmentId);
        $session = $this->createSession($user, 'aktif');
        $asset = $this->createAsset(departmentId: $departmentId);

        $response = $this->scan($surface, $user, $session, (string) $asset->id, [
            'deskripsi_temuan' => 'Deskripsi hasil koreksi lapangan',
        ]);

        $this->assertScanSucceeded($response, $surface);
        $this->assertDatabaseHas('stock_opname_detail', [
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
            'deskripsi_temuan' => 'Deskripsi hasil koreksi lapangan',
            'keterangan' => null,
        ]);
        $this->assertSame('Stock opname lifecycle test asset', $asset->fresh()->deskripsi);
    }

    #[DataProvider('surfaces')]
    public function test_qr_finding_snapshots_description_server_side(string $surface): void
    {
        $departmentId = $this->createDepartment('QR Department');
        $user = $this->createUser(departmentId: $departmentId);
        $session = $this->createSession($user, 'aktif');
        $asset = $this->createAsset(departmentId: $departmentId);
        $asset->update(['deskripsi' => 'Snapshot QR dari master']);

        $response = $this->scan($surface, $user, $session, $asset->nomor_aset);

        $this->assertScanSucceeded($response, $surface);
        $this->assertDatabaseHas('stock_opname_detail', [
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
            'deskripsi_temuan' => 'Snapshot QR dari master',
            'keterangan' => null,
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
            description: 'Deskripsi final hasil opname',
        );

        $response = $this->synchronize($surface, $manager, $session);

        $this->assertManagementMutationSucceeded($response, $surface);
        $asset->refresh();
        $this->assertSame('Rusak', $asset->status_kondisi);
        $this->assertSame($targetLocation->lokasi_id, $asset->lokasi_id);
        $this->assertSame('Deskripsi final hasil opname', $asset->deskripsi);
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
            description: 'Deskripsi sinkron pertama',
        );
        $this->synchronize($surface, $manager, $session);
        $asset->refresh()->update([
            'status_kondisi' => 'Baik',
            'deskripsi' => 'Deskripsi master sesudah sinkron',
        ]);
        $finding->update([
            'kondisi_temuan' => 'Bongkar',
            'deskripsi_temuan' => 'Deskripsi stale yang tidak boleh diterapkan',
        ]);

        $response = $this->synchronize($surface, $manager, $session);

        $this->assertStateConflict($response, $surface);
        $this->assertSame('Baik', $asset->fresh()->status_kondisi);
        $this->assertSame('Deskripsi master sesudah sinkron', $asset->fresh()->deskripsi);
        $this->assertSame(1, AsetFoto::query()->where('aset_id', $asset->id)->count());
    }

    #[DataProvider('surfaces')]
    public function test_failed_sync_rolls_back_master_changes_and_does_not_mark_session_synchronized(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'selesai');
        $asset = $this->createAsset();
        $this->createFinding(
            $session,
            $asset,
            $manager,
            condition: 'Rusak',
            description: 'Deskripsi yang harus di-rollback',
        );
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
        $this->assertSame('Stock opname lifecycle test asset', $asset->fresh()->deskripsi);
        $this->assertNull($session->fresh()->synced_at);
    }

    #[DataProvider('surfaces')]
    public function test_legacy_note_and_null_description_never_replace_master_description(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'selesai');
        $asset = $this->createAsset();
        $finding = $this->createFinding($session, $asset, $manager, condition: 'Baik');

        $this->assertNull($finding->deskripsi_temuan);
        $this->assertSame('Lifecycle finding', $finding->keterangan);

        $response = $this->synchronize($surface, $manager, $session);

        $this->assertManagementMutationSucceeded($response, $surface);
        $this->assertSame('Stock opname lifecycle test asset', $asset->fresh()->deskripsi);
    }

    #[DataProvider('surfaces')]
    public function test_unchanged_finding_description_remains_stable_during_sync(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'selesai');
        $asset = $this->createAsset();
        $this->createFinding(
            $session,
            $asset,
            $manager,
            condition: 'Baik',
            description: $asset->deskripsi,
        );

        $response = $this->synchronize($surface, $manager, $session);

        $this->assertManagementMutationSucceeded($response, $surface);
        $this->assertSame('Stock opname lifecycle test asset', $asset->fresh()->deskripsi);
    }

    public function test_export_separates_finding_description_from_legacy_note(): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($manager, 'selesai');
        $asset = $this->createAsset();
        $this->createFinding(
            $session,
            $asset,
            $manager,
            condition: 'Baik',
            description: 'Deskripsi hasil pemeriksaan',
        );
        $export = new StockOpnameExport($session->id);

        $headings = $export->headings()[0];
        $mappedRow = $export->map($export->collection()->sole());

        $this->assertSame('Deskripsi Aset Master', $headings[4]);
        $this->assertSame('Deskripsi Aset', $headings[25]);
        $this->assertSame('Catatan Temuan Historis', $headings[26]);
        $this->assertSame('Deskripsi hasil pemeriksaan', $mappedRow[25]);
        $this->assertSame('Lifecycle finding', $mappedRow[26]);
        $this->assertNotEmpty(\Maatwebsite\Excel\Facades\Excel::raw(
            new StockOpnameExport($session->id),
            \Maatwebsite\Excel\Excel::XLSX,
        ));
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
        ?string $description = null,
    ): StockOpnameDetail {
        return StockOpnameDetail::query()->create([
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
            'dicek_oleh' => $checker->id,
            'tanggal_cek' => '2026-08-20',
            'kondisi_temuan' => $condition,
            'lokasi_temuan' => $location,
            'foto_temuan' => $photoPath,
            'deskripsi_temuan' => $description,
            'keterangan' => 'Lifecycle finding',
        ]);
    }

    private function scan(
        string $surface,
        User $user,
        StockOpname $session,
        string $assetReference,
        array $overrides = [],
    ): TestResponse {
        $payload = array_merge([
            'stock_opname_id' => $session->id,
            'aset_id' => $assetReference,
            'kondisi_temuan' => 'Baik',
            'lokasi_temuan' => '1',
        ], $overrides);

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
