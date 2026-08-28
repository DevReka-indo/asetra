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
        'synced_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_berakhir' => 'date',
        'synced_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'selesai';
    }

    public function canAcceptFindings(): bool
    {
        return $this->isActive();
    }

    public function isSynchronized(): bool
    {
        return $this->synced_at !== null;
    }

    public function canSynchronize(): bool
    {
        return $this->isCompleted() && ! $this->isSynchronized();
    }

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
