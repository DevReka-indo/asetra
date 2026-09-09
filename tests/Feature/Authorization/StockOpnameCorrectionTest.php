<?php

namespace Tests\Feature\Authorization;

use App\Models\DataAset;
use App\Models\JenisKategori;
use App\Models\KategoriAset;
use App\Models\LokasiAset;
use App\Models\Permission;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockOpnameDetailRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StockOpnameCorrectionTest extends TestCase
{
    use LazilyRefreshDatabase;

    private int $nextFixtureId = 900;

    private int $nextRoleId = 900;

    public function test_explicitly_permitted_non_ga_user_can_correct_active_finding_with_audit(): void
    {
        $departmentId = $this->createDepartment('Engineering');
        $manager = $this->createUser(departmentId: $departmentId);
        $this->grantManagementPermission($manager);
        $checker = $this->createUser();
        $session = $this->createSession($manager);
        $finding = $this->createFinding($session, $this->createAsset(), $checker);
        $newLocation = $this->createLocation();

        $this->assertFalse($manager->isBagianUmum());

        $response = $this->correct($manager, $session, $finding, [
            'kondisi_temuan' => 'Rusak',
            'lokasi_temuan' => (string) $newLocation->lokasi_id,
            'deskripsi_temuan' => 'Deskripsi dikoreksi setelah pemeriksaan ulang.',
        ]);

        $response->assertRedirect(route('stock-opname.show', $session))
            ->assertSessionHas('success', 'Hasil pemeriksaan berhasil dikoreksi.');
        $finding->refresh();
        $this->assertSame('Rusak', $finding->kondisi_temuan);
        $this->assertSame((string) $newLocation->lokasi_id, (string) $finding->lokasi_temuan);
        $this->assertSame('Deskripsi dikoreksi setelah pemeriksaan ulang.', $finding->deskripsi_temuan);
        $this->assertSame('Hasil pemeriksaan awal', $finding->keterangan);

        $revision = StockOpnameDetailRevision::query()->sole();
        $this->assertSame($manager->id, $revision->changed_by);
        $this->assertSame([
            'kondisi_temuan' => 'Baik',
            'lokasi_temuan' => (string) $finding->aset->lokasi_id,
            'deskripsi_temuan' => 'Stock opname correction test asset',
        ], $revision->before_values);
        $this->assertSame([
            'kondisi_temuan' => 'Rusak',
            'lokasi_temuan' => (string) $newLocation->lokasi_id,
            'deskripsi_temuan' => 'Deskripsi dikoreksi setelah pemeriksaan ulang.',
        ], $revision->after_values);
    }

    public function test_superadmin_can_correct_active_finding(): void
    {
        $superadmin = $this->createUser(roleId: 1, roleName: 'Root Operator');
        $checker = $this->createUser();
        $session = $this->createSession($superadmin);
        $finding = $this->createFinding($session, $this->createAsset(), $checker);

        $this->correct($superadmin, $session, $finding, ['deskripsi_temuan' => 'Koreksi superadmin'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('Koreksi superadmin', $finding->fresh()->deskripsi_temuan);
        $this->assertDatabaseHas('stock_opname_detail_revisions', ['changed_by' => $superadmin->id]);
    }

    public function test_original_checker_can_correct_own_finding_with_accurate_audit(): void
    {
        $departmentId = $this->createDepartment('Checker Department');
        $checker = $this->createUser(departmentId: $departmentId);
        $session = $this->createSession($checker);
        $finding = $this->createFinding(
            $session,
            $this->createAsset(departmentId: $departmentId),
            $checker,
        );

        $response = $this->correct($checker, $session, $finding, [
            'deskripsi_temuan' => 'Koreksi oleh pemeriksa',
            'correction_context' => 'execution',
            'correction_detail_id' => $finding->id,
        ]);

        $response->assertRedirect(route('stock-opname.user-show', [
            'id' => $session->id,
            'tab' => 'checked',
        ]))->assertSessionHas('success', 'Hasil pemeriksaan berhasil dikoreksi.');

        $this->assertSame('Koreksi oleh pemeriksa', $finding->fresh()->deskripsi_temuan);
        $revision = StockOpnameDetailRevision::query()->sole();
        $this->assertSame($checker->id, $revision->changed_by);
        $this->assertSame(['deskripsi_temuan' => 'Stock opname correction test asset'], $revision->before_values);
        $this->assertSame(['deskripsi_temuan' => 'Koreksi oleh pemeriksa'], $revision->after_values);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Hasil pemeriksaan berhasil dikoreksi.')
            ->assertSee('Koreksi oleh pemeriksa');
    }

    public function test_ordinary_user_cannot_correct_another_users_finding(): void
    {
        $checker = $this->createUser();
        $otherUser = $this->createUser();
        $session = $this->createSession($checker);
        $finding = $this->createFinding($session, $this->createAsset(), $checker);

        $this->correct($otherUser, $session, $finding, ['deskripsi_temuan' => 'Forged correction'])
            ->assertForbidden();

        $this->assertSame('Stock opname correction test asset', $finding->fresh()->deskripsi_temuan);
        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);
    }

    public function test_ga_name_false_positive_does_not_grant_correction_access(): void
    {
        $departmentId = $this->createDepartment('Legal Services');
        $user = $this->createUser(departmentId: $departmentId);
        $checker = $this->createUser();
        $session = $this->createSession($user);
        $finding = $this->createFinding($session, $this->createAsset(), $checker);

        $this->assertTrue($user->isBagianUmum());
        $this->correct($user, $session, $finding, ['deskripsi_temuan' => 'Tidak sah'])->assertForbidden();
        $this->assertSame('Stock opname correction test asset', $finding->fresh()->deskripsi_temuan);
        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);
    }

    public function test_admin_role_name_alone_does_not_grant_correction_access(): void
    {
        $user = $this->createUser(roleName: 'admin');
        $checker = $this->createUser();
        $session = $this->createSession($user);
        $finding = $this->createFinding($session, $this->createAsset(), $checker);

        $this->correct($user, $session, $finding, ['deskripsi_temuan' => 'Tidak sah'])->assertForbidden();
        $this->assertSame('Stock opname correction test asset', $finding->fresh()->deskripsi_temuan);
        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);
    }

    public function test_manager_and_superadmin_cannot_correct_completed_findings(): void
    {
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $superadmin = $this->createUser(roleId: 1, roleName: 'Root Operator');

        foreach ([$manager, $superadmin] as $user) {
            $session = $this->createSession($user, 'selesai');
            $finding = $this->createFinding($session, $this->createAsset(), $user);

            $this->correct($user, $session, $finding, ['deskripsi_temuan' => 'Tidak boleh berubah'])
                ->assertRedirect()
                ->assertSessionHas('error', 'Temuan pada sesi Stock Opname yang sudah selesai tidak dapat dikoreksi.');

            $this->assertSame('Stock opname correction test asset', $finding->fresh()->deskripsi_temuan);
        }

        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);
    }

    public function test_original_checker_cannot_correct_own_completed_finding(): void
    {
        $checker = $this->createUser();
        $session = $this->createSession($checker, 'selesai');
        $finding = $this->createFinding($session, $this->createAsset(), $checker);

        $this->correct($checker, $session, $finding, ['deskripsi_temuan' => 'Tidak boleh berubah'])
            ->assertRedirect()
            ->assertSessionHas('error', 'Temuan pada sesi Stock Opname yang sudah selesai tidak dapat dikoreksi.');

        $this->assertSame('Stock opname correction test asset', $finding->fresh()->deskripsi_temuan);
        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);
    }

    public function test_correction_updates_only_allowed_fields(): void
    {
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $session = $this->createSession($manager);
        $otherSession = $this->createSession($manager);
        $asset = $this->createAsset();
        $otherAsset = $this->createAsset();
        $otherUser = $this->createUser();
        $finding = $this->createFinding($session, $asset, $manager);

        $this->correct($manager, $session, $finding, [
            'deskripsi_temuan' => 'Hanya hasil yang berubah',
            'keterangan' => 'Catatan lama tidak boleh berubah',
            'aset_id' => $otherAsset->id,
            'stock_opname_id' => $otherSession->id,
            'dicek_oleh' => $otherUser->id,
            'tanggal_cek' => '2026-09-09',
        ])->assertRedirect()->assertSessionHas('success');

        $finding->refresh();
        $this->assertSame($asset->id, $finding->aset_id);
        $this->assertSame($session->id, $finding->stock_opname_id);
        $this->assertSame($manager->id, $finding->dicek_oleh);
        $this->assertSame('2026-08-28', $finding->tanggal_cek->format('Y-m-d'));
        $this->assertSame('Hanya hasil yang berubah', $finding->deskripsi_temuan);
        $this->assertSame('Hasil pemeriksaan awal', $finding->keterangan);
        $this->assertSame(['deskripsi_temuan' => 'Hanya hasil yang berubah'], $finding->revisions()->sole()->after_values);
    }

    public function test_detail_from_another_session_cannot_be_corrected_through_current_session_url(): void
    {
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $firstSession = $this->createSession($manager);
        $secondSession = $this->createSession($manager);
        $finding = $this->createFinding($secondSession, $this->createAsset(), $manager);

        $this->correct($manager, $firstSession, $finding, ['deskripsi_temuan' => 'IDOR attempt'])
            ->assertNotFound();

        $this->assertSame('Stock opname correction test asset', $finding->fresh()->deskripsi_temuan);
        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);
    }

    public function test_no_op_correction_does_not_create_a_revision(): void
    {
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $session = $this->createSession($manager);
        $finding = $this->createFinding($session, $this->createAsset(), $manager);

        $this->correct($manager, $session, $finding)
            ->assertRedirect()
            ->assertSessionHas('success', 'Tidak ada perubahan pada temuan Stock Opname.');

        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);
    }

    public function test_validation_failure_does_not_change_finding_or_create_revision(): void
    {
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $session = $this->createSession($manager);
        $finding = $this->createFinding($session, $this->createAsset(), $manager);

        $this->actingAs($manager)->patch(route('stock-opname.detail.update', [$session, $finding]), [
            'kondisi_temuan' => 'INVALID',
            'lokasi_temuan' => $finding->lokasi_temuan,
            'deskripsi_temuan' => 'Tidak boleh tersimpan',
        ])->assertSessionHasErrors('kondisi_temuan');

        $this->assertSame('Hasil pemeriksaan awal', $finding->fresh()->keterangan);
        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);
    }

    public function test_invalid_location_from_execution_page_returns_to_checked_tab_with_visible_error(): void
    {
        $departmentId = $this->createDepartment('Validation Department');
        $checker = $this->createUser(departmentId: $departmentId);
        $session = $this->createSession($checker);
        $finding = $this->createFinding(
            $session,
            $this->createAsset(departmentId: $departmentId),
            $checker,
        );

        $response = $this->correct($checker, $session, $finding, [
            'lokasi_temuan' => '999999',
            'deskripsi_temuan' => 'Tidak boleh tersimpan',
            'correction_context' => 'execution',
            'correction_detail_id' => $finding->id,
        ]);

        $response->assertRedirect(route('stock-opname.user-show', [
            'id' => $session->id,
            'tab' => 'checked',
        ]))->assertSessionHasErrors('lokasi_temuan');
        $this->assertSame((string) $finding->aset->lokasi_id, (string) $finding->fresh()->lokasi_temuan);
        $this->assertSame('Hasil pemeriksaan awal', $finding->fresh()->keterangan);
        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Koreksi Gagal')
            ->assertSee('correctionModal'.$finding->id)
            ->assertSee('Tidak boleh tersimpan');
    }

    public function test_audit_insert_failure_rolls_back_finding_correction(): void
    {
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $session = $this->createSession($manager);
        $finding = $this->createFinding($session, $this->createAsset(), $manager);
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER fail_stock_opname_revision
            BEFORE INSERT ON stock_opname_detail_revisions
            BEGIN
                SELECT RAISE(ABORT, 'forced revision failure');
            END
            SQL);

        $this->correct($manager, $session, $finding, ['deskripsi_temuan' => 'Harus rollback'])
            ->assertRedirect()
            ->assertSessionHas('error', 'Gagal mengoreksi temuan Stock Opname.');

        $this->assertSame('Stock opname correction test asset', $finding->fresh()->deskripsi_temuan);
        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);
    }

    public function test_photo_replacement_is_audited_and_removes_old_file_after_commit(): void
    {
        Storage::fake('public');
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $session = $this->createSession($manager);
        $finding = $this->createFinding($session, $this->createAsset(), $manager, 'stock_opname_foto/original.jpg');
        Storage::disk('public')->put($finding->foto_temuan, 'original');

        $this->correct($manager, $session, $finding, [
            'foto_temuan' => UploadedFile::fake()->image('replacement.jpg'),
        ])->assertRedirect()->assertSessionHas('success');

        $finding->refresh();
        $this->assertNotSame('stock_opname_foto/original.jpg', $finding->foto_temuan);
        Storage::disk('public')->assertExists($finding->foto_temuan);
        Storage::disk('public')->assertMissing('stock_opname_foto/original.jpg');
        $revision = $finding->revisions()->sole();
        $this->assertSame('stock_opname_foto/original.jpg', $revision->before_values['foto_temuan']);
        $this->assertSame($finding->foto_temuan, $revision->after_values['foto_temuan']);
    }

    public function test_rejected_completed_photo_correction_cleans_new_file_and_preserves_old_file(): void
    {
        Storage::fake('public');
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $session = $this->createSession($manager, 'selesai');
        $finding = $this->createFinding($session, $this->createAsset(), $manager, 'stock_opname_foto/original.jpg');
        Storage::disk('public')->put($finding->foto_temuan, 'original');

        $this->correct($manager, $session, $finding, [
            'foto_temuan' => UploadedFile::fake()->image('replacement.jpg'),
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame('stock_opname_foto/original.jpg', $finding->fresh()->foto_temuan);
        $this->assertSame(['stock_opname_foto/original.jpg'], Storage::disk('public')->allFiles('stock_opname_foto'));
        $this->assertDatabaseCount('stock_opname_detail_revisions', 0);
    }

    public function test_correction_control_is_only_rendered_for_authorized_manager_on_active_session(): void
    {
        $manager = $this->createUser();
        $this->grantManagementPermission($manager);
        $activeSession = $this->createSession($manager);
        $activeFinding = $this->createFinding($activeSession, $this->createAsset(), $manager);
        $completedSession = $this->createSession($manager, 'selesai');
        $completedFinding = $this->createFinding($completedSession, $this->createAsset(), $manager);

        $this->actingAs($manager)->get(route('stock-opname.show', $activeSession))
            ->assertOk()
            ->assertSee('correctionModal'.$activeFinding->id);
        $this->actingAs($manager)->get(route('stock-opname.show', $completedSession))
            ->assertOk()
            ->assertDontSee('correctionModal'.$completedFinding->id);
    }

    public function test_original_checker_sees_correction_action_for_own_result_on_execution_page(): void
    {
        $departmentId = $this->createDepartment('Execution Department');
        $checker = $this->createUser(departmentId: $departmentId);
        $session = $this->createSession($checker);
        $finding = $this->createFinding(
            $session,
            $this->createAsset(departmentId: $departmentId),
            $checker,
        );

        $this->actingAs($checker)
            ->get(route('stock-opname.user-show', $session))
            ->assertOk()
            ->assertSee('Aksi')
            ->assertSee('correctionModal'.$finding->id)
            ->assertSee('Koreksi');
    }

    public function test_other_participant_does_not_see_correction_action_for_another_users_result(): void
    {
        $departmentId = $this->createDepartment('Shared Department');
        $checker = $this->createUser(departmentId: $departmentId);
        $otherParticipant = $this->createUser(departmentId: $departmentId);
        $session = $this->createSession($checker);
        $finding = $this->createFinding(
            $session,
            $this->createAsset(departmentId: $departmentId),
            $checker,
        );

        $this->actingAs($otherParticipant)
            ->get(route('stock-opname.user-show', $session))
            ->assertOk()
            ->assertSee($finding->aset->nomor_aset)
            ->assertDontSee('correctionModal'.$finding->id);
    }

    public function test_completed_execution_session_has_no_correction_controls(): void
    {
        $checker = $this->createUser();
        $session = $this->createSession($checker, 'selesai');
        $finding = $this->createFinding($session, $this->createAsset(), $checker);

        $this->actingAs($checker)
            ->get(route('stock-opname.user-show', $session))
            ->assertRedirect(route('stock-opname.user-index'))
            ->assertDontSee('correctionModal'.$finding->id);
    }

    private function createDepartment(string $name): int
    {
        return DB::table('department')->insertGetId(['name_department' => $name], 'id_department');
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
            'periode' => "Correction Period {$fixtureId}",
            'tanggal_mulai' => '2026-08-01',
            'tanggal_berakhir' => '2026-08-31',
            'keterangan' => 'Correction test fixture',
            'created_by' => $creator->id,
            'status' => $status,
        ]);
    }

    private function createLocation(): LokasiAset
    {
        $fixtureId = $this->nextFixtureId++;

        return LokasiAset::query()->create([
            'kode_lokasi' => "CORR-{$fixtureId}",
            'nama_lokasi' => "Correction Location {$fixtureId}",
        ]);
    }

    private function createAsset(?int $departmentId = null, ?User $pic = null): DataAset
    {
        $fixtureId = $this->nextFixtureId++;
        $location = $this->createLocation();
        $categoryType = JenisKategori::query()->create([
            'kode_awalan' => (string) $fixtureId,
            'nama_jenis' => "Correction Type {$fixtureId}",
        ]);
        $category = KategoriAset::query()->create([
            'kode' => (string) (1000 + $fixtureId),
            'nama' => "Correction Category {$fixtureId}",
            'jenis_kategori_id' => $categoryType->id,
        ]);

        return DataAset::query()->create([
            'nama_aset' => "Correction Asset {$fixtureId}",
            'kategori_id' => $category->id,
            'lokasi_id' => $location->lokasi_id,
            'merek' => 'Test',
            'deskripsi' => 'Stock opname correction test asset',
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
            'deskripsi_temuan' => $asset->deskripsi,
            'keterangan' => 'Hasil pemeriksaan awal',
            'foto_temuan' => $photoPath,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function correct(
        User $user,
        StockOpname $session,
        StockOpnameDetail $finding,
        array $overrides = [],
    ): TestResponse {
        $payload = array_merge([
            'kondisi_temuan' => $finding->kondisi_temuan,
            'lokasi_temuan' => $finding->lokasi_temuan,
            'deskripsi_temuan' => $finding->deskripsi_temuan,
        ], $overrides);

        return $this->actingAs($user)
            ->patch(route('stock-opname.detail.update', [$session, $finding]), $payload);
    }
}
