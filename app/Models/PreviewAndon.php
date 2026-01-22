<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PreviewAndon extends Model
{
    use HasFactory;

    protected $table = 'preview_andon';

    protected $fillable = [
        'mesin_nama',
        'part_no',
        'gsph',
        'start_time',
        'end_time',
        'plan_qty',
        'actual_qty',
        'efficiency',
        'shift',
        'calculated_start',
        'calculated_finish',
        'production_duration',
        'shift_sequence',
        'mesin_id',
        'planning_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gsph' => 'decimal:2',
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'plan_qty' => 'integer',
            'actual_qty' => 'integer',
            'efficiency' => 'decimal:2',
            'shift' => 'string',
            'calculated_start' => 'datetime',
            'calculated_finish' => 'datetime',
            'production_duration' => 'decimal:2',
            'shift_sequence' => 'integer',
        ];
    }

    public function mesin()
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }

    public function planning()
    {
        return $this->belongsTo(Planning::class, 'planning_id');
    }

    /**
     * Hitung ulang schedule berdasarkan shift
     */
    public function recalculateSchedule()
    {
        // Ambil semua data untuk mesin ini dengan shift yang sama
        $dataForRecalculation = PreviewAndon::where('mesin_nama', $this->mesin_nama)
            ->where('shift', $this->shift)
            ->orderBy('sort_order', 'asc')
            ->get();

        $startTime = $this->getShiftStartTime($this->shift);
        $currentDateTime = $startTime->copy();

        foreach ($dataForRecalculation as $index => $item) {
            // Update urutan
            $item->shift_sequence = $index + 1;
            
            // Hitung durasi
            $durationHours = 0;
            if ($item->gsph > 0) {
                $durationHours = $item->plan_qty / $item->gsph;
            }
            
            // Set waktu yang dihitung
            $item->calculated_start = $currentDateTime->copy();
            $item->production_duration = $durationHours;
            
            // Hitung finish time
            $finishTime = $currentDateTime->copy()->addHours($durationHours);
            $item->calculated_finish = $finishTime;
            
            // Update current time untuk item berikutnya
            $currentDateTime = $finishTime;
            
            $item->save();
        }
    }

    /**
     * Dapatkan waktu mulai shift
     */
    private function getShiftStartTime($shift)
    {
        $shiftStartTimes = [
            '1' => '07:00:00',
            '2' => '19:00:00'
        ];

        // Gunakan tanggal dari original start_time atau hari ini
        $baseDate = $this->start_time ? Carbon::parse($this->start_time) : Carbon::today();
        
        return Carbon::parse($baseDate->format('Y-m-d') . ' ' . $shiftStartTimes[$shift]);
    }

    /**
     * Format waktu untuk display
     */
    public function getFormattedStartAttribute()
    {
        if ($this->calculated_start) {
            return Carbon::parse($this->calculated_start)->format('d/m/Y H:i');
        }
        return '-';
    }

    public function getFormattedFinishAttribute()
    {
        if ($this->calculated_finish) {
            return Carbon::parse($this->calculated_finish)->format('d/m/Y H:i');
        }
        return '-';
    }

    // Tambah method untuk update order
    public function updateOrder($newOrder)
    {
        $this->sort_order = $newOrder;
        $this->save();
        
        // Recalculate schedule setelah order berubah
        $this->recalculateSchedule();
    }

    // Update scope untuk sorting
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    // Scope untuk data aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', 0);
    }
}