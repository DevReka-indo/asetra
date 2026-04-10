<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisAsetUmum extends Model
{
    use HasFactory;

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