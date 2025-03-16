<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TableEntry extends Model
{
    protected $fillable = [
        'table_id',
        'min_value',
        'max_value',
        'result'
    ];

    /**
     * Get the table that owns this entry
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(DiceTable::class, 'table_id');
    }
} 