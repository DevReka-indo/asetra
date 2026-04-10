<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LokasiAset extends Model
{
    protected $table = 'lokasi_aset';
    protected $primaryKey = 'lokasi_id';

    protected $fillable = [
        'kode_lokasi',
        'nama_lokasi',
        'detail_lokasi',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
