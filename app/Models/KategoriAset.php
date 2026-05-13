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
        'tipe',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Otomatis tentukan tipe berdasarkan digit pertama kode.
     * 1xx = aset_tetap, 2xx = inventaris
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->tipe) && !empty($model->kode)) {
                $model->tipe = str_starts_with((string) $model->kode, '1')
                    ? 'aset_tetap'
                    : 'inventaris';
            }
        });

        static::updating(function ($model) {
            if (!empty($model->kode)) {
                $model->tipe = str_starts_with((string) $model->kode, '1')
                    ? 'aset_tetap'
                    : 'inventaris';
            }
        });
    }

    /**
     * Relasi ke data aset
     */
    public function dataAset()
    {
        return $this->hasMany(DataAset::class, 'kategori_id', 'id');
    }

    /**
     * Scope: hanya aset tetap (kode 1xx)
     */
    public function scopeAsetTetap($query)
    {
        return $query->where('tipe', 'aset_tetap');
    }

    /**
     * Scope: hanya inventaris/EC (kode 2xx)
     */
    public function scopeInventaris($query)
    {
        return $query->where('tipe', 'inventaris');
    }

    /**
     * Label badge untuk tampilan UI
     */
    public function getTipeLabelAttribute(): string
    {
        return $this->tipe === 'aset_tetap' ? 'Aset Tetap' : 'Inventaris/EC';
    }

    /**
     * Warna badge berdasarkan tipe
     */
    public function getTipeBadgeColorAttribute(): string
    {
        return $this->tipe === 'aset_tetap' ? 'danger' : 'primary';
    }
}
