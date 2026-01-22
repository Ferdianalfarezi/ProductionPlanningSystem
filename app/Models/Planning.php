<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'plannings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'planning_id',
        'judgement',
        'priority',
        'part_no_fg',
        'part_no_parent',
        'part_no_child',
        'store',
        'process',
        'line',
        'rack_no',
        'seq_calc',
        'qty_kbn',
        'qty_consume',
        'qty_lot',
        'stock_min',
        'stock_max',
        'stock_prod',
        'stock_store',
        'stock_realtime',
        'calc_parent',
        'calc_child',
        'calc_lot',
        'calc_prod',
        'add_prod',
        'total_prod',
        'status',
        'qty_print',
        'actual',
        'urutan',
        'pulling_time',
        'lt_pull',
        'lt_prod',
        'max_prod_time',
        'start_time',
        'end_time',
        'lot_no',
        'calc_by',
        'calc_time',
        'reason',
        'update_time_excel',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'planning_id' => 'integer',
            'priority' => 'integer',
            'seq_calc' => 'integer',
            'qty_kbn' => 'integer',
            'qty_consume' => 'integer',
            'qty_lot' => 'integer',
            'stock_min' => 'integer',
            'stock_max' => 'integer',
            'stock_prod' => 'integer',
            'stock_store' => 'integer',
            'stock_realtime' => 'integer',
            'calc_parent' => 'integer',
            'calc_child' => 'integer',
            'calc_lot' => 'integer',
            'calc_prod' => 'integer',
            'add_prod' => 'integer',
            'total_prod' => 'integer',
            'qty_print' => 'integer',
            'actual' => 'integer',
            'urutan' => 'integer',
            'lt_pull' => 'integer',
            'lt_prod' => 'integer',
            'pulling_time' => 'datetime',
            'max_prod_time' => 'datetime',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'calc_time' => 'datetime',
            'update_time_excel' => 'datetime',
        ];
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeAttribute(): array
    {
        return match ($this->status) {
            'Open' => ['class' => 'bg-blue-100 text-blue-800', 'label' => 'Open'],
            'R' => ['class' => 'bg-yellow-100 text-yellow-800', 'label' => 'Running'],
            'J' => ['class' => 'bg-purple-100 text-purple-800', 'label' => 'Job'],
            'C' => ['class' => 'bg-green-100 text-green-800', 'label' => 'Complete'],
            default => ['class' => 'bg-gray-100 text-gray-800', 'label' => $this->status ?? '-'],
        };
    }
}