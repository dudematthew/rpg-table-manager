<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiceTable extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'dice_expression'
    ];

    /**
     * Get the project that owns this table
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the entries for this table
     */
    public function entries(): HasMany
    {
        return $this->hasMany(TableEntry::class, 'table_id');
    }

    /**
     * Export the table to markdown format
     */
    public function toMarkdown(): string
    {
        $markdown = "# {$this->name}\n\n";
        $markdown .= "Dice: {$this->dice_expression}\n\n";
        
        if ($this->description) {
            $markdown .= "{$this->description}\n\n";
        }

        $markdown .= "| {$this->dice_expression} | Result |\n";
        $markdown .= "|" . str_repeat("-", strlen($this->dice_expression) + 2) . "|--------|\n";

        foreach ($this->entries()->orderBy('min_value')->get() as $entry) {
            $range = $entry->min_value === $entry->max_value 
                ? $entry->min_value 
                : "{$entry->min_value}-{$entry->max_value}";
            $markdown .= "| {$range} | {$entry->result} |\n";
        }

        return $markdown;
    }

    /**
     * Export the table to CSV format
     */
    public function toCsv(): string
    {
        $csv = "\"{$this->dice_expression}\",Result\n";

        foreach ($this->entries()->orderBy('min_value')->get() as $entry) {
            $range = $entry->min_value === $entry->max_value 
                ? $entry->min_value 
                : "{$entry->min_value}-{$entry->max_value}";
            $csv .= "\"{$range}\",\"{$entry->result}\"\n";
        }

        return $csv;
    }
} 