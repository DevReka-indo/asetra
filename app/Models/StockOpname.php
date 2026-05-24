<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $table = 'stock_opname';

    protected $fillable = [
        'tanggal_mulai',
        'tanggal_berakhir',
        'periode',
        'keterangan',
        'created_by',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date',
        'tanggal_berakhir' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * User yang membuat stock opname ini
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Detail aset yang dicek dalam stock opname ini
     */
    public function detail()
    {
        return $this->hasMany(StockOpnameDetail::class, 'stock_opname_id');
    }
}
