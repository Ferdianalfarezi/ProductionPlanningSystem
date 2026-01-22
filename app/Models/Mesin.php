<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesin extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'mesin';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama',
        'struk',
        'tonase',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'struk' => 'integer',
        ];
    }
}