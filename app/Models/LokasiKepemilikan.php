<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiKepemilikan extends Model
{
    protected $table = 'lokasi_kepemilikan';
    protected $primaryKey = 'lokasi_kepemilikan_id';

    protected $fillable = [
        'kode_lokasi_kepemilikan',
        'nama_lokasi_kepemilikan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}