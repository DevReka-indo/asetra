<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriAset extends Model
{
    use SoftDeletes;

    protected $table = 'kategori_aset';

    protected $fillable = [
        'kode',
        'nama',
        'jenis_kategori_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke JenisKategori
     */
    public function jenisKategori()
    {
        return $this->belongsTo(JenisKategori::class, 'jenis_kategori_id');
    }

    /**
     * Relasi ke data aset
     */
    public function dataAset()
    {
        return $this->hasMany(DataAset::class, 'kategori_id', 'id');
    }

    /**
     * Scope: filter berdasarkan jenis kategori tertentu
     */
    public function scopeOfJenis($query, $jenisKategoriId)
    {
        return $query->where('jenis_kategori_id', $jenisKategoriId);
    }

    /**
     * Label jenis kategori untuk tampilan UI (via relasi)
     */
    public function getTipeLabelAttribute(): string
    {
        return $this->jenisKategori ? $this->jenisKategori->nama_jenis : '-';
    }

    /**
     * Warna badge berdasarkan urutan kode_awalan (1=danger, 2=primary, lainnya=secondary)
     */
    public function getTipeBadgeColorAttribute(): string
    {
        if (!$this->jenisKategori) return 'secondary';
        return match($this->jenisKategori->kode_awalan) {
            '1'     => 'danger',
            '2'     => 'primary',
            '3'     => 'success',
            '4'     => 'warning',
            '5'     => 'info',
            default => 'secondary',
        };
    }

}
