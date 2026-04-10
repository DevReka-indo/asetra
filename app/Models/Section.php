<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    protected $table = 'section';
    protected $primaryKey = 'id_section';
    protected $fillable = ['name_section', 'department_id_department'];

    public $timestamps = false;

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id_department', 'id_department');
    }

    public function unit()
    {
        return $this->hasMany(Unit::class, 'section_id_section', 'id_section');
    }
    
    public function users()
    {
        return $this->hasMany(User::class, 'section_id_section', 'id_section');
    }
}

?>