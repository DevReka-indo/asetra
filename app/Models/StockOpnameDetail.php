<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    protected $table = 'stock_opname_detail';

    protected $fillable = [
        'stock_opname_id',
        'aset_id',
        'dicek_oleh',       // pengganti pic_monitoring dari data_aset
        'tanggal_cek',
        'kondisi_temuan',
        'lokasi_temuan',
        'keterangan',
        'foto_temuan',
    ];

    protected $casts = [
        'tanggal_cek' => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Header stock opname induk
     */
    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    /**
     * Aset yang dicek
     */
    public function aset()
    {
        return $this->belongsTo(DataAset::class, 'aset_id');
    }

    /**
     * PIC yang melakukan pengecekan (sebelumnya pic_monitoring di data_aset)
     */
    public function dicekOleh()
    {
        return $this->belongsTo(User::class, 'dicek_oleh');
    }
}
