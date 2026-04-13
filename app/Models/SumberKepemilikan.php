<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SumberKepemilikan extends Model
{
    protected $table = 'sumber_kepemilikan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'kode',
        'nama',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Aset dari sumber kepemilikan 
     */
    public function aset()
    {
        return $this->hasMany(DataAset::class, 'sumber_kepemilikan_id', 'id');
    }
}
