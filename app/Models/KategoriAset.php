<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriAset extends Model
{
    use SoftDeletes;
    
    protected $table = 'kategori_aset';
    protected $primaryKey = 'kategori_id';

    protected $fillable = [
        'kode',
        'nama_kategori',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];
}
