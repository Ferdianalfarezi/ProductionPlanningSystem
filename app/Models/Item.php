<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'items';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'part_no',
        'line',
        'mesin_id',
        'process',
        'process_name',
        'gsph',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'gsph' => 'decimal:6',
        ];
    }

    /**
     * Get the mesin that owns the item.
     */
    public function mesin(): BelongsTo
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }
}