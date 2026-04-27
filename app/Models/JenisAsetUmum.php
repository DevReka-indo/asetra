<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisAsetUmum extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jenis_aset_umum';

    protected $fillable = [
        'kode_umum',
        'jenis_aset',
    ];

    /**
     * Relasi ke tabel khusus
     */
    public function jenisAsetKhusus()
    {
        return $this->hasMany(JenisAsetKhusus::class, 'jenis_aset_umum_id', 'id');
    }
}