<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LokasiAset extends Model
{
    use HasFactory, SoftDeletes;

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
