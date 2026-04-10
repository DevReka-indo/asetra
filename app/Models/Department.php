<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'department';
    protected $primaryKey = 'id_department';
    protected $fillable = ['name_department', 'divisi_id_divisi'];

    public $timestamps = false;

    public function divisi()
    {
        return $this->belongsTo(Divisi::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'department_id_department', 'id_department');
    }

    public function unit()
    {
        return $this->hasMany(Unit::class, 'department_id_department');
    }

    public function section()
    {
        return $this->hasMany(Section::class, 'department_id_department', 'id_department');
    }

    public function director()
    {
        return $this->belongsTo(Director::class, 'director_id_director');
    }

}
