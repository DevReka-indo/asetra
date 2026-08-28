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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RepairAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    private int $nextRoleId = 10;

    #[DataProvider('surfaces')]
    public function test_superadmin_can_approve_a_pending_repair(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $repair = $this->createRepairRequest('menunggu');

        $response = $this->processRepair($surface, $manager, $repair, 'disetujui');

        $this->assertTransitionSucceeded($response, $surface, $repair, 'disetujui');
    }

    #[DataProvider('surfaces')]
    public function test_superadmin_can_complete_an_approved_repair(string $surface): void
    {
        $manager = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $repair = $this->createRepairRequest('disetujui');

        $response = $this->completeRepair($surface, $manager, $repair);

        $this->assertCompletionSucceeded($response, $surface, $repair);
    }

    #[DataProvider('surfaces')]
    public function test_user_with_permission_can_approve_a_pending_repair(string $surface): void
    {
        $manager = $this->createUser();
        $this->grantRepairPermission($manager);
        $repair = $this->createRepairRequest('menunggu');

        $response = $this->processRepair($surface, $manager, $repair, 'disetujui');

        $this->assertTransitionSucceeded($response, $surface, $repair, 'disetujui');
    }

    #[DataProvider('surfaces')]
    public function test_user_with_permission_can_reject_a_pending_repair(string $surface): void
    {
        $manager = $this->createUser();
        $this->grantRepairPermission($manager);
        $repair = $this->createRepairRequest('menunggu');

        $response = $this->processRepair($surface, $manager, $repair, 'ditolak');

        $this->assertTransitionSucceeded($response, $surface, $repair, 'ditolak');
        $this->assertSame('Aktif', $repair->aset->fresh()->status_aset);
    }

    #[DataProvider('surfaces')]
    public function test_user_with_permission_can_complete_an_approved_repair(string $surface): void
    {
        $manager = $this->createUser();
        $this->grantRepairPermission($manager);
        $repair = $this->createRepairRequest('disetujui');

        $response = $this->completeRepair($surface, $manager, $repair);

        $this->assertCompletionSucceeded($response, $surface, $repair);
    }

    #[DataProvider('surfaces')]
    public function test_user_without_permission_cannot_approve_directly(string $surface): void
    {
        $manager = $this->createUser();
        $repair = $this->createRepairRequest('menunggu');

        $response = $this->processRepair($surface, $manager, $repair, 'disetujui');

        $response->assertForbidden();
        $this->assertSame('menunggu', $repair->fresh()->status);
    }

    #[DataProvider('surfaces')]
    public function test_user_without_permission_cannot_complete_directly(string $surface): void
    {
        $manager = $this->createUser();
        $repair = $this->createRepairRequest('disetujui');

        $response = $this->completeRepair($surface, $manager, $repair);

        $response->assertForbidden();
        $this->assertSame('disetujui', $repair->fresh()->status);
    }

    #[DataProvider('surfaces')]
    public function test_organization_name_ga_false_positive_does_not_grant_repair_management(string $surface): void
    {
        $departmentId = DB::table('department')->insertGetId([
            'name_department' => 'Legal Services',
        ], 'id_department');
        $manager = $this->createUser(departmentId: $departmentId);
        $repair = $this->createRepairRequest('menunggu');
        $this->assertTrue($manager->isBagianUmum());

        $response = $this->processRepair($surface, $manager, $repair, 'disetujui');

        $response->assertForbidden();
        $this->assertSame('menunggu', $repair->fresh()->status);
    }

    #[DataProvider('surfaces')]
    public function test_admin_role_name_alone_does_not_grant_repair_management(string $surface): void
    {
        $manager = $this->createUser(roleName: 'admin');
        $repair = $this->createRepairRequest('menunggu');

        $response = $this->processRepair($surface, $manager, $repair, 'disetujui');

        $response->assertForbidden();
        $this->assertSame('menunggu', $repair->fresh()->status);
    }

    #[DataProvider('surfaces')]
    public function test_already_processed_repair_cannot_be_processed_again(string $surface): void
    {
        $manager = $this->createUser();
        $this->grantRepairPermission($manager);
        $repair = $this->createRepairRequest('ditolak');

        $response = $this->processRepair($surface, $manager, $repair, 'disetujui');

        $this->assertInvalidTransitionResponse($response, $surface);
        $this->assertSame('ditolak', $repair->fresh()->status);
    }

    #[DataProvider('surfaces')]
    public function test_unapproved_repair_cannot_be_completed(string $surface): void
    {
        $manager = $this->createUser();
        $this->grantRepairPermission($manager);
        $repair = $this->createRepairRequest('menunggu');

        $response = $this->completeRepair($surface, $manager, $repair);

        $this->assertInvalidTransitionResponse($response, $surface);
        $this->assertSame('menunggu', $repair->fresh()->status);
    }

    public function test_authorized_manager_sees_pending_repair_controls(): void
    {
        $manager = $this->createUser();
        $this->grantRepairPermission($manager);
        $repair = $this->createRepairRequest('menunggu');

        $response = $this->actingAs($manager)->get(route('perbaikan.show', $repair));

        $response->assertOk()
            ->assertSee('Proses Pengajuan')
            ->assertSee('Tolak')
            ->assertSee('Setujui');
    }

    public function test_unauthorized_user_does_not_see_pending_repair_controls(): void
    {
        $repair = $this->createRepairRequest('menunggu');

        $response = $this->actingAs($repair->pengaju)->get(route('perbaikan.show', $repair));

        $response->assertOk()
            ->assertDontSee('Proses Pengajuan');
    }

    public function test_authorized_manager_sees_completion_control_for_approved_repair(): void
    {
        $manager = $this->createUser();
        $this->grantRepairPermission($manager);
        $repair = $this->createRepairRequest('disetujui');

        $response = $this->actingAs($manager)->get(route('perbaikan.show', $repair));

        $response->assertOk()
            ->assertSee('Tandai Perbaikan Selesai');
    }

    public function test_unauthorized_user_does_not_see_completion_control(): void
    {
        $repair = $this->createRepairRequest('disetujui');

        $response = $this->actingAs($repair->pengaju)->get(route('perbaikan.show', $repair));

        $response->assertOk()
            ->assertDontSee('Tandai Perbaikan Selesai');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function surfaces(): array
    {
        return [
            'web' => ['web'],
            'api' => ['api'],
        ];
    }

    private function createUser(
        ?int $roleId = null,
        string $roleName = 'Staff',
        ?int $departmentId = null,
    ): User {
        $roleId ??= $this->nextRoleId++;

        DB::table('role')->insert([
            'id_role' => $roleId,
            'nm_role' => $roleName,
        ]);
        DB::table('position')->insertOrIgnore([
            'id_position' => 1,
            'nm_position' => 'Test Position',
        ]);

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

    private function createRepairRequest(string $status): PengajuanPerbaikan
    {
        $submitter = $this->createUser();
        $location = LokasiAset::query()->create([
            'kode_lokasi' => 'REPAIR-TEST',
            'nama_lokasi' => 'Repair Test Location',
        ]);
        $categoryType = JenisKategori::query()->create([
            'kode_awalan' => '9',
            'nama_jenis' => 'Repair Test Type',
        ]);
        $category = KategoriAset::query()->create([
            'kode' => '901',
            'nama' => 'Repair Test Category',
            'jenis_kategori_id' => $categoryType->id,
        ]);
        $asset = DataAset::query()->create([
            'nama_aset' => 'Repair Test Asset',
            'kategori_id' => $category->id,
            'lokasi_id' => $location->lokasi_id,
            'merek' => 'Test',
            'deskripsi' => 'Asset used for repair authorization tests',
            'tahun_kapitalisasi' => 2026,
            'pic_id' => $submitter->id,
            'penanggung_jawab_id' => $submitter->id,
            'status_kondisi' => 'Rusak',
            'status_aset' => $status === 'ditolak' ? 'Aktif' : 'Dalam Perbaikan',
        ]);

        return PengajuanPerbaikan::query()->create([
            'aset_id' => $asset->id,
            'diajukan_oleh' => $submitter->id,
            'tanggal_pengajuan' => '2026-08-27',
            'deskripsi_kerusakan' => 'Test damage',
            'tingkat_urgensi' => 'sedang',
            'status' => $status,
        ]);
    }

    private function processRepair(
        string $surface,
        User $user,
        PengajuanPerbaikan $repair,
        string $action,
    ): TestResponse {
        $payload = [
            'aksi' => $action,
            'catatan' => $action === 'ditolak' ? 'Repair rejected in test' : null,
        ];

        if ($surface === 'api') {
            return $this->withHeaders($this->apiHeaders($user))
                ->putJson("/api/perbaikan/{$repair->id}/proses", $payload);
        }

        return $this->actingAs($user)
            ->put(route('perbaikan.proses', $repair), $payload);
    }

    private function completeRepair(
        string $surface,
        User $user,
        PengajuanPerbaikan $repair,
    ): TestResponse {
        $payload = [
            'kondisi_setelah' => 'Baik',
            'catatan' => 'Repair completed in test',
        ];

        if ($surface === 'api') {
            return $this->withHeaders($this->apiHeaders($user))
                ->putJson("/api/perbaikan/{$repair->id}/selesai", $payload);
        }

        return $this->actingAs($user)
            ->put(route('perbaikan.selesai', $repair), $payload);
    }

    /**
     * @return array<string, string>
     */
    private function apiHeaders(User $user): array
    {
        $token = Crypt::encryptString(json_encode([
            'id' => $user->id,
            'created_at' => now()->timestamp,
        ], JSON_THROW_ON_ERROR));

        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];
    }

    private function assertTransitionSucceeded(
        TestResponse $response,
        string $surface,
        PengajuanPerbaikan $repair,
        string $status,
    ): void {
        if ($surface === 'api') {
            $response->assertOk();
        } else {
            $response->assertRedirect(route('perbaikan.show', $repair));
        }

        $this->assertSame($status, $repair->fresh()->status);
    }

    private function assertCompletionSucceeded(
        TestResponse $response,
        string $surface,
        PengajuanPerbaikan $repair,
    ): void {
        $this->assertTransitionSucceeded($response, $surface, $repair, 'selesai');
        $this->assertSame('Baik', $repair->aset->fresh()->status_kondisi);
        $this->assertSame('Aktif', $repair->aset->fresh()->status_aset);
        $this->assertDatabaseHas('log_aset', [
            'aset_id' => $repair->aset_id,
            'kondisi' => 'Baik',
            'status_aset' => 'Aktif',
        ]);
    }

    private function assertInvalidTransitionResponse(TestResponse $response, string $surface): void
    {
        if ($surface === 'api') {
            $response->assertUnprocessable();

            return;
        }

        $response->assertRedirect()
            ->assertSessionHas('error');
    }
}
