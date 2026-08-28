<?php

namespace Tests\Feature\Authorization;

use App\Models\DataAset;
use App\Models\JenisKategori;
use App\Models\KategoriAset;
use App\Models\LokasiAset;
use App\Models\PengajuanPerbaikan;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RepairSubmissionAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    private int $nextFixtureId = 10;

    private int $nextRoleId = 100;

    #[DataProvider('surfaces')]
    public function test_ordinary_user_can_submit_for_asset_in_organizational_scope(string $surface): void
    {
        Storage::fake('public');
        $departmentId = $this->createDepartment('Permitted Department');
        $user = $this->createUser(departmentId: $departmentId);
        $asset = $this->createAsset(departmentId: $departmentId);

        $response = $this->submitRepair($surface, $user, $asset);

        $this->assertSubmissionSucceeded($response, $surface, $user, $asset);
    }

    #[DataProvider('surfaces')]
    public function test_pic_can_submit_for_assigned_asset_outside_organizational_scope(string $surface): void
    {
        Storage::fake('public');
        $userDepartmentId = $this->createDepartment('User Department');
        $assetDepartmentId = $this->createDepartment('Asset Department');
        $user = $this->createUser(departmentId: $userDepartmentId);
        $asset = $this->createAsset(departmentId: $assetDepartmentId, pic: $user);

        $response = $this->submitRepair($surface, $user, $asset);

        $this->assertSubmissionSucceeded($response, $surface, $user, $asset);
    }

    #[DataProvider('surfaces')]
    public function test_manually_submitting_asset_id_from_another_department_is_forbidden_without_mutation(string $surface): void
    {
        Storage::fake('public');
        $userDepartmentId = $this->createDepartment('User Department');
        $otherDepartmentId = $this->createDepartment('Other Department');
        $user = $this->createUser(departmentId: $userDepartmentId);
        $this->createAsset(departmentId: $userDepartmentId);
        $inaccessibleAsset = $this->createAsset(departmentId: $otherDepartmentId);

        $response = $this->submitRepair($surface, $user, $inaccessibleAsset);

        $this->assertSubmissionForbiddenWithoutMutation($response, $user, $inaccessibleAsset);
    }

    #[DataProvider('surfaces')]
    public function test_superadmin_can_submit_for_asset_outside_organizational_scope(string $surface): void
    {
        Storage::fake('public');
        $assetDepartmentId = $this->createDepartment('Asset Department');
        $superadmin = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $asset = $this->createAsset(departmentId: $assetDepartmentId);

        $response = $this->submitRepair($surface, $superadmin, $asset);

        $this->assertSubmissionSucceeded($response, $surface, $superadmin, $asset);
    }

    #[DataProvider('surfaces')]
    public function test_repair_manager_permission_does_not_expand_submission_object_scope(string $surface): void
    {
        Storage::fake('public');
        $managerDepartmentId = $this->createDepartment('Manager Department');
        $assetDepartmentId = $this->createDepartment('Asset Department');
        $manager = $this->createUser(departmentId: $managerDepartmentId);
        $this->grantRepairPermission($manager);
        $asset = $this->createAsset(departmentId: $assetDepartmentId);

        $response = $this->submitRepair($surface, $manager, $asset);

        $this->assertSubmissionForbiddenWithoutMutation($response, $manager, $asset);
    }

    #[DataProvider('activeRepairCases')]
    public function test_duplicate_active_repair_is_still_rejected(string $surface, string $activeStatus): void
    {
        Storage::fake('public');
        $departmentId = $this->createDepartment('Permitted Department');
        $user = $this->createUser(departmentId: $departmentId);
        $asset = $this->createAsset(departmentId: $departmentId, status: 'Dalam Perbaikan');
        PengajuanPerbaikan::query()->create([
            'aset_id' => $asset->id,
            'diajukan_oleh' => $user->id,
            'tanggal_pengajuan' => now()->toDateString(),
            'deskripsi_kerusakan' => 'Existing active repair',
            'tingkat_urgensi' => 'sedang',
            'status' => $activeStatus,
        ]);

        $response = $this->submitRepair($surface, $user, $asset);

        if ($surface === 'api') {
            $response->assertUnprocessable();
        } else {
            $response->assertRedirect()->assertSessionHas('error');
        }

        $this->assertDatabaseCount('pengajuan_perbaikan', 1);
        $this->assertSame('Dalam Perbaikan', $asset->fresh()->status_aset);
        $this->assertSame([], Storage::disk('public')->allFiles('perbaikan'));
    }

    public function test_database_failure_rolls_back_repair_and_removes_stored_photo(): void
    {
        Storage::fake('public');
        $departmentId = $this->createDepartment('Permitted Department');
        $user = $this->createUser(departmentId: $departmentId);
        $asset = $this->createAsset(departmentId: $departmentId);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER fail_repair_asset_status_update
            BEFORE UPDATE OF status_aset ON data_aset
            BEGIN
                SELECT RAISE(ABORT, 'forced asset update failure');
            END
            SQL);

        $response = $this->withExceptionHandling()->actingAs($user)->post(
            route('perbaikan.store'),
            $this->repairPayload($asset),
        );

        $response->assertServerError();
        $this->assertDatabaseMissing('pengajuan_perbaikan', [
            'aset_id' => $asset->id,
            'diajukan_oleh' => $user->id,
        ]);
        $this->assertSame('Aktif', $asset->fresh()->status_aset);
        $this->assertSame([], Storage::disk('public')->allFiles('perbaikan'));
    }

    /** @return array<string, array{string}> */
    public static function surfaces(): array
    {
        return ['web' => ['web'], 'api' => ['api']];
    }

    /** @return array<string, array{string, string}> */
    public static function activeRepairCases(): array
    {
        return [
            'web pending' => ['web', 'menunggu'],
            'web approved' => ['web', 'disetujui'],
            'api pending' => ['api', 'menunggu'],
            'api approved' => ['api', 'disetujui'],
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

    private function grantRepairPermission(User $user): void
    {
        $permission = Permission::query()->create([
            'name' => 'manage_perbaikan_aset',
            'description' => 'Manage asset repair requests',
        ]);
        DB::table('role_permission')->insert([
            'role_id_role' => $user->role_id_role,
            'permission_id' => $permission->id,
        ]);
    }

    private function createAsset(int $departmentId, ?User $pic = null, string $status = 'Aktif'): DataAset
    {
        $fixtureId = $this->nextFixtureId++;
        $location = LokasiAset::query()->create([
            'kode_lokasi' => "REPAIR-{$fixtureId}",
            'nama_lokasi' => "Repair Location {$fixtureId}",
        ]);
        $categoryType = JenisKategori::query()->create([
            'kode_awalan' => (string) $fixtureId,
            'nama_jenis' => "Repair Type {$fixtureId}",
        ]);
        $category = KategoriAset::query()->create([
            'kode' => (string) (900 + $fixtureId),
            'nama' => "Repair Category {$fixtureId}",
            'jenis_kategori_id' => $categoryType->id,
        ]);

        return DataAset::query()->create([
            'nama_aset' => "Repair Asset {$fixtureId}",
            'kategori_id' => $category->id,
            'lokasi_id' => $location->lokasi_id,
            'merek' => 'Test',
            'deskripsi' => 'Asset used for repair submission authorization tests',
            'tahun_kapitalisasi' => 2026,
            'id_department' => $departmentId,
            'pic_id' => $pic?->id,
            'penanggung_jawab_id' => $pic?->id,
            'status_kondisi' => 'Rusak',
            'status_aset' => $status,
        ]);
    }

    private function submitRepair(string $surface, User $user, DataAset $asset): TestResponse
    {
        $payload = $this->repairPayload($asset);

        if ($surface === 'api') {
            return $this->withHeaders($this->apiHeaders($user))->post('/api/perbaikan', $payload);
        }

        return $this->actingAs($user)->post(route('perbaikan.store'), $payload);
    }

    /** @return array<string, mixed> */
    private function repairPayload(DataAset $asset): array
    {
        return [
            'aset_id' => $asset->id,
            'deskripsi_kerusakan' => 'Damage submitted through authorization test',
            'tingkat_urgensi' => 'sedang',
            'foto_kerusakan' => UploadedFile::fake()->image('damage.jpg'),
        ];
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

    private function assertSubmissionSucceeded(TestResponse $response, string $surface, User $user, DataAset $asset): void
    {
        if ($surface === 'api') {
            $response->assertStatus(210);
        } else {
            $response->assertRedirect()->assertSessionHas('success');
        }

        $this->assertDatabaseHas('pengajuan_perbaikan', [
            'aset_id' => $asset->id,
            'diajukan_oleh' => $user->id,
            'status' => 'menunggu',
        ]);
        $this->assertSame('Dalam Perbaikan', $asset->fresh()->status_aset);
    }

    private function assertSubmissionForbiddenWithoutMutation(TestResponse $response, User $user, DataAset $asset): void
    {
        $response->assertForbidden();
        $this->assertDatabaseMissing('pengajuan_perbaikan', [
            'aset_id' => $asset->id,
            'diajukan_oleh' => $user->id,
        ]);
        $this->assertSame('Aktif', $asset->fresh()->status_aset);
        $this->assertSame([], Storage::disk('public')->allFiles('perbaikan'));

    }
}
