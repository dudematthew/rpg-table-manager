<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'user_id'
    ];

    /**
     * Get the tables for this project
     */
    public function tables(): HasMany
    {
        return $this->hasMany(DiceTable::class);
    }
} 