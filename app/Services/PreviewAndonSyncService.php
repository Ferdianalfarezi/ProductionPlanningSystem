<?php

namespace App\Services;

use App\Models\PreviewAndon;
use App\Models\Planning;
use App\Models\Mesin;
use App\Models\Item;
use Carbon\Carbon;

class PreviewAndonSyncService
{
    private $shiftStartTimes = [
        '1' => '07:00:00',
        '2' => '19:00:00'
    ];

    public function syncFromPlanning()
    {
        PreviewAndon::truncate();
        
        $mesins = Mesin::all();
        
        foreach ($mesins as $mesin) {
            $items = Item::where('mesin_id', $mesin->id)->get();
            
            if ($items->isEmpty()) {
                continue;
            }
            
            $partNos = $items->pluck('part_no')->toArray();
            
            $plannings = Planning::where(function($query) use ($partNos) {
                $query->whereIn('part_no_fg', $partNos)
                    ->orWhereIn('part_no_child', $partNos)
                    ->orWhereIn('part_no_parent', $partNos);
            })->orderBy('priority', 'asc')
              ->orderBy('start_time', 'asc')
              ->get();
            
            if ($plannings->isEmpty()) {
                continue;
            }
            
            $batchData = [];
            
            foreach ($plannings as $planning) {
                $item = $this->findItemByPlanning($planning, $partNos);
                
                if (!$item) {
                    continue;
                }
                
                // Hitung plan quantity
                $planQty = 0;
                if ($planning->qty_kbn && $planning->total_prod) {
                    $planQty = $planning->qty_kbn * $planning->total_prod;
                }
                
                // Default shift 1
                $shift = '1';
                
                // Simpan batch data
                $batchData[] = [
                    'mesin_nama' => $mesin->nama,
                    'part_no' => $item->part_no,
                    'gsph' => $item->gsph,
                    'start_time' => $planning->start_time,
                    'end_time' => $planning->end_time,
                    'plan_qty' => $planQty,
                    'actual_qty' => 0,
                    'efficiency' => 0,
                    'shift' => $shift,
                    'calculated_start' => null,
                    'calculated_finish' => null,
                    'production_duration' => 0,
                    'shift_sequence' => count($batchData) + 1,
                    'mesin_id' => $mesin->id,
                    'planning_id' => $planning->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Insert batch
            if (!empty($batchData)) {
                PreviewAndon::insert($batchData);
                
                // Recalculate schedule untuk mesin ini
                $this->recalculateScheduleForMesin($mesin->nama, $shift);
            }
        }
        
        return PreviewAndon::count();
    }
    
    private function findItemByPlanning($planning, $partNos)
    {
        $item = null;
        
        if ($planning->part_no_fg && in_array($planning->part_no_fg, $partNos)) {
            $item = Item::where('part_no', $planning->part_no_fg)->first();
        }
        
        if (!$item && $planning->part_no_child && in_array($planning->part_no_child, $partNos)) {
            $item = Item::where('part_no', $planning->part_no_child)->first();
        }
        
        if (!$item && $planning->part_no_parent && in_array($planning->part_no_parent, $partNos)) {
            $item = Item::where('part_no', $planning->part_no_parent)->first();
        }
        
        return $item;
    }
    
    /**
     * Recalculate schedule untuk mesin tertentu
     */
    public function recalculateScheduleForMesin($mesinNama, $shift)
    {
        $dataForRecalculation = PreviewAndon::where('mesin_nama', $mesinNama)
            ->where('shift', $shift)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
        
        if ($dataForRecalculation->isEmpty()) {
            return;
        }
        
        // Gunakan tanggal dari data pertama
        $firstItem = $dataForRecalculation->first();
        $baseDate = $firstItem->start_time ? Carbon::parse($firstItem->start_time) : Carbon::today();
        
        // Tentukan start time berdasarkan shift
        $startTime = Carbon::parse($baseDate->format('Y-m-d') . ' ' . $this->shiftStartTimes[$shift]);
        $currentDateTime = $startTime->copy();
        
        foreach ($dataForRecalculation as $index => $item) {
            // Update urutan
            $item->sort_order = $index + 1;
            
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
     * Update shift untuk mesin
     */
    public function updateShift($mesinNama, $shift)
    {
        // Update semua data untuk mesin ini ke shift baru
        PreviewAndon::where('mesin_nama', $mesinNama)
            ->update(['shift' => $shift]);
        
        // Recalculate schedule
        $this->recalculateScheduleForMesin($mesinNama, $shift);
        
        return PreviewAndon::where('mesin_nama', $mesinNama)->count();
    }
    
    public function updateActual($previewAndonId, $actualQty)
    {
        $previewAndon = PreviewAndon::find($previewAndonId);
        
        if (!$previewAndon) {
            return false;
        }
        
        $previewAndon->actual_qty = $actualQty;
        
        // Update efficiency
        if ($previewAndon->plan_qty > 0) {
            $previewAndon->efficiency = ($actualQty / $previewAndon->plan_qty) * 100;
        }
        
        $previewAndon->save();
        
        // Update juga di tabel planning
        if ($previewAndon->planning_id) {
            $planning = Planning::find($previewAndon->planning_id);
            if ($planning) {
                $planning->actual = $actualQty;
                $planning->save();
            }
        }
        
        return $previewAndon;
    }
}