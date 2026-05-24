<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $table = 'permissions';
    protected $fillable = ['name', 'description'];

    /**
     * Relasi Many-to-Many ke Section.
     */
    public function sections()
    {
        return $this->belongsToMany(
            Section::class,
            'section_permission',
            'permission_id',
            'section_id_section',
            'id',
            'id_section'
        );
    }

    /**
     * Relasi Many-to-Many ke Department.
     */
    public function departments()
    {
        return $this->belongsToMany(
            Department::class,
            'department_permission',
            'permission_id',
            'department_id_department',
            'id',
            'id_department'
        );
    }

    /**
     * Relasi Many-to-Many ke Role.
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_permission',
            'permission_id',
            'role_id_role',
            'id',
            'id_role'
        );
    }
}
