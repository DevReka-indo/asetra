<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisAset extends Model
{
    protected $table = 'master_jenis_aset';
    protected $primaryKey = 'jenis_id';

    protected $fillable = [
        'kode_jenis',
        'nama_jenis',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
