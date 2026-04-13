<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsetFoto extends Model
{
    protected $table = 'aset_foto';

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'aset_id',
        'path_foto',
        'keterangan',
        'urutan',
    ];

    /**
     * Aset pemilik foto ini
     */
    public function aset()
    {
        return $this->belongsTo(DataAset::class, 'aset_id');
    }
}
