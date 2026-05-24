<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Section;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Hak Akses GA (Semua menu kecuali Pengaturan & User Management)
            [
                'name' => 'view_dashboard_ga',
                'description' => 'Akses ke Dashboard General Affairs'
            ],
            [
                'name' => 'manage_lokasi_aset',
                'description' => 'Akses menu Lokasi Aset'
            ],
            [
                'name' => 'manage_jenis_kategori',
                'description' => 'Akses menu Jenis Kategori'
            ],
            [
                'name' => 'manage_kategori_aset',
                'description' => 'Akses menu Kategori Aset'
            ],
            [
                'name' => 'manage_assets',
                'description' => 'Akses menu Data Aset Perusahaan & Aset PIC'
            ],
            [
                'name' => 'manage_log_aset',
                'description' => 'Akses menu Riwayat Monitoring'
            ],
            [
                'name' => 'manage_stock_opname',
                'description' => 'Akses menu Stock Opname'
            ],
            [
                'name' => 'manage_perbaikan_aset',
                'description' => 'Akses menu Pengajuan Perbaikan'
            ],
            [
                'name' => 'manage_pemulihan',
                'description' => 'Akses menu Pemulihan'
            ],

            // Hak Akses Eksklusif Superadmin (Pengaturan & Manajemen Pengguna)
            [
                'name' => 'manage_organization',
                'description' => 'Akses Menu Struktur Organisasi & Manajemen Kode Bagian Kerja'
            ],
            [
                'name' => 'manage_users',
                'description' => 'Akses Menu Manajemen Pengguna'
            ],
        ];

        $permissionIds = [];
        $gaPermissionIds = [];

        foreach ($permissions as $perm) {
            $inserted = Permission::updateOrCreate(
                ['name' => $perm['name']],
                ['description' => $perm['description']]
            );
            $permissionIds[] = $inserted->id;
            
            // Hak akses GA (kecuali pengaturan & user management)
            if (!in_array($perm['name'], ['manage_organization', 'manage_users'])) {
                $gaPermissionIds[] = $inserted->id;
            }
        }

        // 1. Petakan seluruh hak akses GA ke Department GA/Umum (ID 13)
        $gaDept = \App\Models\Department::find(13);
        if ($gaDept) {
            $gaDept->permissions()->sync($gaPermissionIds);
        } else {
            // Sebagai cadangan, cari department yang namanya mengandung 'Umum', 'GA', atau 'General'
            $fallbackDept = \App\Models\Department::where('name_department', 'like', '%Umum%')
                ->orWhere('name_department', 'like', '%GA%')
                ->orWhere('name_department', 'like', '%General%')
                ->first();
            if ($fallbackDept) {
                $fallbackDept->permissions()->sync($gaPermissionIds);
            }
        }

        // 2. Petakan seluruh hak akses ke Role Superadmin (ID 1)
        $superadminRole = Role::find(1);
        if ($superadminRole) {
            $superadminRole->permissions()->sync($permissionIds);
        }
    }
}
