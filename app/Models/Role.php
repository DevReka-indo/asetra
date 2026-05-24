<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'role';  
    protected $primaryKey = 'id_role';
    protected $fillable = ['nm_role'];
    
    // Relasi One-to-Many ke User
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permission',
            'role_id_role',
            'permission_id',
            'id_role',
            'id'
        );
    }
}
