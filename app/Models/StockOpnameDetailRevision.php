<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpnameDetailRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'changed_by',
        'before_values',
        'after_values',
    ];

    protected function casts(): array
    {
        return [
            'before_values' => 'array',
            'after_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function detail(): BelongsTo
    {
        return $this->belongsTo(StockOpnameDetail::class, 'stock_opname_detail_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
