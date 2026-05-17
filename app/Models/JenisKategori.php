<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisKategori extends Model
{
    use SoftDeletes;

    protected $table = 'jenis_kategori';

    protected $fillable = [
        'kode_awalan',
        'nama_jenis',
        'warna_label',
    ];

    /**
     * Relasi ke tabel kategori_aset
     */
    public function kategoriAset()
    {
        return $this->hasMany(KategoriAset::class, 'jenis_kategori_id', 'id');
    }
}
