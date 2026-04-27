<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisAsetKhusus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jenis_aset_khusus';

    protected $fillable = [
        'jenis_aset_umum_id',
        'kode_khusus',
        'jenis_aset',
    ];

    /**
     * Relasi ke tabel jenis_aset_umum
     */
    public function jenisAsetUmum()
    {
        return $this->belongsTo(JenisAsetUmum::class, 'jenis_aset_umum_id', 'id')->withTrashed();
    }

    
    public function getFullKodeAttribute()
    {
        if ($this->jenisAsetUmum) {
            return $this->jenisAsetUmum->kode_umum . '-' . $this->kode_khusus;
        }
        
        return $this->kode_khusus;
    }
}