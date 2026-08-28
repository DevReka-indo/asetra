<?php

namespace Tests\Feature\Authorization;

use App\Models\DataAset;
use App\Models\JenisKategori;
use App\Models\KategoriAset;
use App\Models\LokasiAset;
use App\Models\Permission;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StockOpnameAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    private int $nextFixtureId = 600;

    private int $nextRoleId = 600;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_superadmin_can_perform_every_stock_opname_management_action(): void
    {
        $superadmin = $this->createUser(roleId: 1, roleName: 'Root Operator');

        $this->assertEveryManagementActionAllowed($superadmin);
    }

    public function test_explicitly_permitted_non_ga_user_can_perform_every_management_action(): void
    {
        $departmentId = $this->createDepartment('Engineering');
        $manager = $this->createUser(departmentId: $departmentId);
        $this->grantManagementPermission($manager);

        $this->assertFalse($manager->isBagianUmum());
        $this->assertEveryManagementActionAllowed($manager);
    }

    public function test_ordinary_user_is_forbidden_from_every_stock_opname_management_action(): void
    {
        $user = $this->createUser();

        $this->assertEveryManagementActionForbidden($user);
    }

    #[DataProvider('surfaces')]
    public function test_ga_name_false_positive_does_not_grant_management_access(string $surface): void
    {
        $departmentId = $this->createDepartment('Legal Services');
        $user = $this->createUser(departmentId: $departmentId);

        $this->assertTrue($user->isBagianUmum());

        $this->managementRequest($surface, 'index', $user)->assertForbidden();
    }

    #[DataProvider('surfaces')]
    public function test_admin_role_name_alone_does_not_grant_management_access(string $surface): void
    {
        $user = $this->createUser(roleName: 'admin');

        $this->managementRequest($surface, 'index', $user)->assertForbidden();
    }

    #[DataProvider('surfaces')]
    public function test_ordinary_user_can_scan_asset_in_organizational_scope(string $surface): void
    {
        $departmentId = $this->createDepartment('Participating Department');
        $user = $this->createUser(departmentId: $departmentId);
        $session = $this->createSession($user);
        $asset = $this->createAsset(departmentId: $departmentId);

        $response = $this->scan($surface, $user, $session, $asset);

        $this->assertScanSucceeded($response, $surface);
        $this->assertDatabaseHas('stock_opname_detail', [
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
            'dicek_oleh' => $user->id,
        ]);
    }

    #[DataProvider('surfaces')]
    public function test_ordinary_user_cannot_scan_asset_outside_object_scope(string $surface): void
    {
        $userDepartmentId = $this->createDepartment('User Department');
        $otherDepartmentId = $this->createDepartment('Other Department');
        $user = $this->createUser(departmentId: $userDepartmentId);
        $session = $this->createSession($user);
        $asset = $this->createAsset(departmentId: $otherDepartmentId);

        $response = $this->scan($surface, $user, $session, $asset);

        $response->assertForbidden();
        $this->assertDatabaseMissing('stock_opname_detail', [
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
        ]);
    }

    #[DataProvider('surfaces')]
    public function test_pic_can_enter_and_scan_assigned_asset(string $surface): void
    {
        $assetDepartmentId = $this->createDepartment('Asset Department');
        $pic = $this->createUser();
        $session = $this->createSession($pic);
        $asset = $this->createAsset(departmentId: $assetDepartmentId, pic: $pic);

        $showResponse = $this->showParticipationSession($surface, $pic, $session);
        $showResponse->assertOk();

        if ($surface === 'api') {
            $showResponse->assertJsonPath('data.total_assets', 1);
        } else {
            $showResponse->assertSee($asset->nama_aset);
        }

        $this->assertScanSucceeded($this->scan($surface, $pic, $session, $asset), $surface);
    }

    #[DataProvider('surfaces')]
    public function test_manager_permission_grants_unrestricted_execution_scope(string $surface): void
    {
        $managerDepartmentId = $this->createDepartment('Manager Department');
        $otherDepartmentId = $this->createDepartment('Other Department');
        $manager = $this->createUser(departmentId: $managerDepartmentId);
        $this->grantManagementPermission($manager);
        $session = $this->createSession($manager);
        $asset = $this->createAsset(departmentId: $otherDepartmentId);

        $response = $this->scan($surface, $manager, $session, $asset);

        $this->assertScanSucceeded($response, $surface);
        $this->assertDatabaseHas('stock_opname_detail', [
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
            'dicek_oleh' => $manager->id,
        ]);
    }

    public function test_ordinary_user_can_list_active_web_participation_sessions(): void
    {
        $user = $this->createUser();
        $activeSession = $this->createSession($user, 'aktif');
        $this->createSession($user, 'selesai');

        $this->actingAs($user)
            ->get(route('stock-opname.user-index'))
            ->assertOk()
            ->assertSee($activeSession->periode);
    }

    public function test_sidebar_management_link_uses_canonical_ability(): void
    {
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $this->actingAs($manager);

        $managerSidebar = view('partials.sidebar')->render();

        $falsePositiveDepartmentId = $this->createDepartment('Legal Services');
        $falsePositive = $this->createUser(departmentId: $falsePositiveDepartmentId);
        $this->actingAs($falsePositive);

        $falsePositiveSidebar = view('partials.sidebar')->render();
        $managementHref = 'href="'.route('stock-opname.index').'"';

        $this->assertStringContainsString($managementHref, $managerSidebar);
        $this->assertStringNotContainsString($managementHref, $falsePositiveSidebar);
    }

    public function test_authorized_manager_can_delete_active_session_with_existing_findings(): void
    {
        Storage::fake('public');
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $session = $this->createSession($manager);
        $asset = $this->createAsset();
        $finding = $this->createFinding($session, $asset, $manager, 'stock_opname_foto/active-finding.jpg');
        Storage::disk('public')->put($finding->foto_temuan, 'test image');

        $response = $this->actingAs($manager)
            ->delete(route('stock-opname.destroy', $session));

        $response->assertRedirect(route('stock-opname.index'))
            ->assertSessionHas('success');
        $this->assertModelMissing($session);
        $this->assertModelMissing($finding);
        Storage::disk('public')->assertMissing('stock_opname_foto/active-finding.jpg');
    }

    public function test_manager_cannot_delete_completed_session_and_history_remains_reportable(): void
    {
        Storage::fake('public');
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $session = $this->createSession($manager, 'selesai');
        $asset = $this->createAsset();
        $finding = $this->createFinding($session, $asset, $manager, 'stock_opname_foto/completed-finding.jpg');
        Storage::disk('public')->put($finding->foto_temuan, 'historical image');

        $response = $this->actingAs($manager)
            ->delete(route('stock-opname.destroy', $session));

        $response->assertRedirect()
            ->assertSessionHas('error', 'Sesi Stock Opname yang sudah selesai tidak dapat dihapus.');
        $this->assertModelExists($session);
        $this->assertModelExists($finding);
        Storage::disk('public')->assertExists('stock_opname_foto/completed-finding.jpg');
        $this->actingAs($manager)
            ->get(route('stock-opname.show', $session))
            ->assertOk();
        $this->actingAs($manager)
            ->get(route('stock-opname.export', $session))
            ->assertOk();
    }

    public function test_superadmin_cannot_bypass_completed_session_delete_rule(): void
    {
        $superadmin = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $session = $this->createSession($superadmin, 'selesai');

        $response = $this->actingAs($superadmin)
            ->delete(route('stock-opname.destroy', $session));

        $response->assertRedirect()
            ->assertSessionHas('error', 'Sesi Stock Opname yang sudah selesai tidak dapat dihapus.');
        $this->assertModelExists($session);
    }

    public function test_ordinary_user_still_receives_forbidden_when_deleting_completed_session(): void
    {
        $ordinaryUser = $this->createUser();
        $session = $this->createSession($ordinaryUser, 'selesai');

        $response = $this->actingAs($ordinaryUser)
            ->delete(route('stock-opname.destroy', $session));

        $response->assertForbidden();
        $this->assertModelExists($session);
    }

    public function test_delete_control_is_only_rendered_for_active_sessions(): void
    {
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $activeSession = $this->createSession($manager);
        $completedSession = $this->createSession($manager, 'selesai');

        $response = $this->actingAs($manager)
            ->get(route('stock-opname.index'));

        $response->assertOk()
            ->assertSee('deleteModal'.$activeSession->id)
            ->assertDontSee('deleteModal'.$completedSession->id);
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

    private function assertEveryManagementActionAllowed(User $user): void
    {
        foreach ($this->managementActions() as [$surface, $action]) {
            $response = $this->managementRequest($surface, $action, $user);

            if ($surface === 'api' && $action === 'store') {
                $response->assertStatus(210);
            } elseif (in_array($action, ['index', 'show', 'export'], true) || $surface === 'api') {
                $response->assertOk();
            } else {
                $response->assertRedirect();
            }
        }
    }

    private function assertEveryManagementActionForbidden(User $user): void
    {
        foreach ($this->managementActions() as [$surface, $action]) {
            $this->managementRequest($surface, $action, $user)->assertForbidden();
        }
    }

    /**
     * @return list<array{string, string}>
     */
    private function managementActions(): array
    {
        return [
            ['web', 'index'],
            ['web', 'store'],
            ['web', 'show'],
            ['web', 'update'],
            ['web', 'status'],
            ['web', 'destroy'],
            ['web', 'sync'],
            ['web', 'export'],
            ['api', 'index'],
            ['api', 'store'],
            ['api', 'status'],
            ['api', 'sync'],
        ];
    }

    private function managementRequest(string $surface, string $action, User $user): TestResponse
    {
        StockOpnameDetail::query()->delete();
        StockOpname::query()->delete();

        $sessionStatus = in_array($action, ['store', 'sync'], true) ? 'selesai' : 'aktif';
        $session = $this->createSession($user, $sessionStatus);
        $headers = $surface === 'api' ? $this->apiHeaders($user) : [];

        return match ([$surface, $action]) {
            ['web', 'index'] => $this->actingAs($user)->get(route('stock-opname.index')),
            ['web', 'store'] => $this->actingAs($user)->post(route('stock-opname.store'), $this->sessionPayload()),
            ['web', 'show'] => $this->actingAs($user)->get(route('stock-opname.show', $session)),
            ['web', 'update'] => $this->actingAs($user)->put(route('stock-opname.update', $session), [
                ...$this->sessionPayload(),
                'status' => 'aktif',
            ]),
            ['web', 'status'] => $this->actingAs($user)->put(
                route('stock-opname.update-status', $session),
                ['status' => 'selesai'],
            ),
            ['web', 'destroy'] => $this->actingAs($user)->delete(route('stock-opname.destroy', $session)),
            ['web', 'sync'] => $this->actingAs($user)->post(route('stock-opname.sync', $session)),
            ['web', 'export'] => $this->actingAs($user)->get(route('stock-opname.export', $session)),
            ['api', 'index'] => $this->withHeaders($headers)->getJson('/api/stock-opname'),
            ['api', 'store'] => $this->withHeaders($headers)->postJson('/api/stock-opname', $this->sessionPayload()),
            ['api', 'status'] => $this->withHeaders($headers)
                ->putJson("/api/stock-opname/{$session->id}/status", ['status' => 'selesai']),
            ['api', 'sync'] => $this->withHeaders($headers)->postJson("/api/stock-opname/{$session->id}/sync"),
        };
    }

    /**
     * @return array{periode: string, tanggal_mulai: string, tanggal_berakhir: string, keterangan: string}
     */
    private function sessionPayload(): array
    {
        return [
            'periode' => 'Auth Test Period',
            'tanggal_mulai' => '2026-09-01',
            'tanggal_berakhir' => '2026-09-30',
            'keterangan' => 'Authorization test fixture',
        ];
    }

    private function createDepartment(string $name): int
    {
        return DB::table('department')->insertGetId([
            'name_department' => $name,
        ], 'id_department');
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

    private function grantManagementPermission(User $user): void
    {
        $permission = Permission::query()->firstOrCreate(
            ['name' => 'manage_stock_opname'],
            ['description' => 'Manage stock opname'],
        );

        DB::table('role_permission')->insertOrIgnore([
            'role_id_role' => $user->role_id_role,
            'permission_id' => $permission->id,
        ]);
    }

    private function createSession(User $creator, string $status = 'aktif'): StockOpname
    {
        $fixtureId = $this->nextFixtureId++;

        return StockOpname::query()->create([
            'periode' => "Authorization Period {$fixtureId}",
            'tanggal_mulai' => '2026-08-01',
            'tanggal_berakhir' => '2026-08-31',
            'keterangan' => 'Authorization test fixture',
            'created_by' => $creator->id,
            'status' => $status,
        ]);
    }

    private function createAsset(?int $departmentId = null, ?User $pic = null): DataAset
    {
        $fixtureId = $this->nextFixtureId++;
        $location = LokasiAset::query()->create([
            'kode_lokasi' => "AUTH-{$fixtureId}",
            'nama_lokasi' => "Authorization Location {$fixtureId}",
        ]);
        $categoryType = JenisKategori::query()->create([
            'kode_awalan' => (string) $fixtureId,
            'nama_jenis' => "Authorization Type {$fixtureId}",
        ]);
        $category = KategoriAset::query()->create([
            'kode' => (string) (1000 + $fixtureId),
            'nama' => "Authorization Category {$fixtureId}",
            'jenis_kategori_id' => $categoryType->id,
        ]);

        return DataAset::query()->create([
            'nama_aset' => "Authorization Asset {$fixtureId}",
            'kategori_id' => $category->id,
            'lokasi_id' => $location->lokasi_id,
            'merek' => 'Test',
            'deskripsi' => 'Stock opname authorization test asset',
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
        ?string $photoPath = null,
    ): StockOpnameDetail {
        return StockOpnameDetail::query()->create([
            'stock_opname_id' => $session->id,
            'aset_id' => $asset->id,
            'dicek_oleh' => $checker->id,
            'tanggal_cek' => '2026-08-28',
            'kondisi_temuan' => 'Baik',
            'lokasi_temuan' => (string) $asset->lokasi_id,
            'foto_temuan' => $photoPath,
            'keterangan' => 'Finalized record protection test',
        ]);
    }

    private function showParticipationSession(
        string $surface,
        User $user,
        StockOpname $session,
    ): TestResponse {
        if ($surface === 'api') {
            return $this->withHeaders($this->apiHeaders($user))
                ->getJson("/api/stock-opname/{$session->id}");
        }

        return $this->actingAs($user)->get(route('stock-opname.user-show', $session));
    }

    private function scan(
        string $surface,
        User $user,
        StockOpname $session,
        DataAset $asset,
    ): TestResponse {
        $payload = [
            'stock_opname_id' => $session->id,
            'aset_id' => (string) $asset->id,
            'kondisi_temuan' => 'Baik',
            'lokasi_temuan' => (string) $asset->lokasi_id,
            'keterangan' => 'Authorization scan',
        ];

        if ($surface === 'api') {
            return $this->withHeaders($this->apiHeaders($user))
                ->postJson('/api/stock-opname/scan', $payload);
        }

        return $this->actingAs($user)->post(route('stock-opname.scanStore'), $payload, [
            'Accept' => 'application/json',
        ]);
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

    private function assertScanSucceeded(TestResponse $response, string $surface): void
    {
        $response->assertStatus($surface === 'api' ? 210 : 200);
    }
}
