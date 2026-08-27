<?php

namespace Tests\Feature\Authorization;

use App\Models\Permission;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class GateAuthorizationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_superadmin_role_id_is_allowed_for_every_registered_ability_without_permissions(): void
    {
        $user = $this->createUser(roleId: 1, roleName: 'Root Operator');

        foreach (AppServiceProvider::PERMISSION_ABILITIES as $ability) {
            $this->assertTrue(Gate::has($ability), "{$ability} is not registered");
            $this->assertTrue(Gate::forUser($user)->allows($ability), $ability);
        }

        $this->assertTrue(Gate::forUser($user)->allows('unregistered_test_ability'));
    }

    public function test_normal_user_is_allowed_by_role_permission(): void
    {
        $user = $this->createUser();
        $permission = $this->createPermission('manage_assets');
        DB::table('role_permission')->insert([
            'role_id_role' => $user->role_id_role,
            'permission_id' => $permission->id,
        ]);

        $this->assertTrue(Gate::forUser($user)->allows('manage_assets'));
    }

    public function test_normal_user_is_allowed_by_department_permission(): void
    {
        $departmentId = $this->createDepartment('Operations');
        $user = $this->createUser(departmentId: $departmentId);
        $permission = $this->createPermission('manage_stock_opname');
        DB::table('department_permission')->insert([
            'department_id_department' => $departmentId,
            'permission_id' => $permission->id,
        ]);

        $this->assertTrue(Gate::forUser($user)->allows('manage_stock_opname'));
    }

    public function test_normal_user_is_allowed_by_section_permission(): void
    {
        $sectionId = $this->createSection('Asset Operations');
        $user = $this->createUser(sectionId: $sectionId);
        $permission = $this->createPermission('manage_log_aset');
        DB::table('section_permission')->insert([
            'section_id_section' => $sectionId,
            'permission_id' => $permission->id,
        ]);

        $this->assertTrue(Gate::forUser($user)->allows('manage_log_aset'));
    }

    public function test_normal_user_without_an_applicable_permission_is_denied(): void
    {
        $user = $this->createUser(roleName: 'superadmin');

        $this->assertFalse(Gate::forUser($user)->allows('manage_users'));
    }

    public function test_organization_name_containing_ga_does_not_grant_an_ability(): void
    {
        $departmentId = $this->createDepartment('Legal Services');
        $user = $this->createUser(departmentId: $departmentId);

        $this->assertFalse(Gate::forUser($user)->allows('manage_assets'));
    }

    private function createUser(
        int $roleId = 2,
        string $roleName = 'Staff',
        ?int $departmentId = null,
        ?int $sectionId = null,
    ): User {
        DB::table('role')->insert([
            'id_role' => $roleId,
            'nm_role' => $roleName,
        ]);

        DB::table('position')->insert([
            'id_position' => 1,
            'nm_position' => 'Test Position',
        ]);

        return User::factory()->create([
            'role_id_role' => $roleId,
            'position_id_position' => 1,
            'department_id_department' => $departmentId,
            'section_id_section' => $sectionId,
        ]);
    }

    private function createPermission(string $name): Permission
    {
        return Permission::query()->create([
            'name' => $name,
            'description' => "Test permission for {$name}",
        ]);
    }

    private function createDepartment(string $name): int
    {
        return DB::table('department')->insertGetId([
            'name_department' => $name,
        ], 'id_department');
    }

    private function createSection(string $name): int
    {
        return DB::table('section')->insertGetId([
            'name_section' => $name,
        ], 'id_section');
    }
}
