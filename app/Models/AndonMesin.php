<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AndonMesin extends Model
{
    use HasFactory;

    protected $table = 'andon_mesin';
    
    protected $fillable = [
        'mesin_id',
        'mesin_nama',
        'shift',
        'tanggal',
        'data_json',
        'submitted_at'
    ];

    protected $casts = [
        'data_json' => 'array',
        'submitted_at' => 'datetime',
        'tanggal' => 'date'
    ];

    public function mesin()
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }
}