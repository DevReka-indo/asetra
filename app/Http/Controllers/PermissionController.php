<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Tampilkan halaman manajemen hak akses.
     */
    public function index()
    {
        $departments = \App\Models\Department::with(['section.permissions', 'permissions'])
            ->orderBy('name_department')
            ->get();
        $permissions = Permission::all();

        return view('superadmin.permission_manage', compact('departments', 'permissions'));
    }

    /**
     * Perbarui hak akses struktur organisasi (Department & Section).
     */
    public function update(Request $request)
    {
        $request->validate([
            'department_permissions' => 'array',
            'section_permissions' => 'array',
        ]);

        $departmentPermissions = $request->input('department_permissions', []);
        $sectionPermissions = $request->input('section_permissions', []);

        // 1. Sinkronkan Izin level Department
        $departments = \App\Models\Department::all();
        foreach ($departments as $dept) {
            $deptPermIds = $departmentPermissions[$dept->id_department] ?? [];
            $dept->permissions()->sync($deptPermIds);
        }

        // 2. Sinkronkan Izin level Section
        $sections = \App\Models\Section::all();
        foreach ($sections as $sec) {
            $secPermIds = $sectionPermissions[$sec->id_section] ?? [];
            $sec->permissions()->sync($secPermIds);
        }

        return redirect()->route('permissions.manage')
            ->with('success', 'Hak akses struktur organisasi berhasil diperbarui!');
    }
}
