<?php

namespace App\Http\Controllers;

use App\Models\AndonMesin;
use App\Models\Mesin;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AndonLaneController extends Controller
{
    /**
     * Menampilkan halaman Andon Lane
     */
    public function index(Request $request)
{
    $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
    $shift = $request->get('shift', $this->getCurrentShift());
    
    $data = $this->calculateLaneData($tanggal, $shift);
    $summary = $this->calculateSummary($data);
    $elapsedHours = $this->getElapsedHours($shift);
    
    return view('andon.lane', compact('data', 'summary', 'tanggal', 'shift', 'elapsedHours'));
}

    /**
     * API endpoint untuk refresh data via AJAX
     */
    public function getData(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $shift = $request->get('shift', $this->getCurrentShift());
        
        $laneData = $this->calculateLaneData($tanggal, $shift);
        $summary = $this->calculateSummary($laneData);
        
        return response()->json([
            'success' => true,
            'data' => $laneData,
            'summary' => $summary,
            'server_time' => now()->format('H:i:s'),
            'elapsed_hours' => $this->getElapsedHours($shift)
        ]);
    }

    /**
     * Hitung data lane per mesin
     */
    private function calculateLaneData($tanggal, $shift)
    {
        $andonData = AndonMesin::where('tanggal', $tanggal)
            ->when($shift, function($query) use ($shift) {
                $query->where('shift', $shift);
            })
            ->get();

        $elapsedHours = $this->getElapsedHours($shift);
        $laneData = [];

        foreach ($andonData as $andon) {
            $dataRows = $andon->data_json ?? [];
            
            // Filter hanya baris aktif untuk kalkulasi GSPH
            $activeRows = collect($dataRows)->where('is_active', true);
            $allRows = collect($dataRows);
            
            // Kalkulasi
            $gsphTotal = $activeRows->sum('gsph');
            $gsphCount = $activeRows->count();
            $gsphAvg = $gsphCount > 0 ? round($gsphTotal / $gsphCount, 2) : 0;
            
            $planningAll = $allRows->sum('plan_qty');
            $actual = $allRows->sum('actual_qty');
            
            // Plan/Hours = elapsed hours × GSPH Avg
            $planHours = round($elapsedHours * $gsphAvg, 0);
            
            // Efficiency calculations
            $eff = $planHours > 0 ? round(($actual / $planHours) * 100, 1) : 0;
            $effAll = $planningAll > 0 ? round(($actual / $planningAll) * 100, 1) : 0;
            
            // Variance (selisih actual vs plan hours)
            $variance = $actual - $planHours;
            
            $laneData[] = [
                'id' => $andon->id,
                'mesin_id' => $andon->mesin_id,
                'machine' => $andon->mesin_nama,
                'shift' => $andon->shift,
                'gsph_avg' => $gsphAvg,
                'planning_all' => $planningAll,
                'plan_hours' => $planHours,
                'actual' => $actual,
                'eff' => $eff,
                'eff_all' => $effAll,
                'variance' => $variance,
                'status' => $this->getStatus($eff),
                'active_rows' => $activeRows->count(),
                'total_rows' => $allRows->count(),
                'submitted_at' => $andon->submitted_at?->format('H:i:s')
            ];
        }

        // Sort by machine name
        usort($laneData, function($a, $b) {
            return strcmp($a['machine'], $b['machine']);
        });

        return $laneData;
    }

    /**
     * Hitung summary keseluruhan
     */
    private function calculateSummary($laneData)
    {
        $collection = collect($laneData);
        
        return [
            'total_machines' => $collection->count(),
            'total_planning' => $collection->sum('planning_all'),
            'total_plan_hours' => $collection->sum('plan_hours'),
            'total_actual' => $collection->sum('actual'),
            'avg_eff' => $collection->count() > 0 ? round($collection->avg('eff'), 1) : 0,
            'avg_eff_all' => $collection->count() > 0 ? round($collection->avg('eff_all'), 1) : 0,
            'machines_on_target' => $collection->where('eff', '>=', 90)->count(),
            'machines_warning' => $collection->whereBetween('eff', [70, 89.9])->count(),
            'machines_critical' => $collection->where('eff', '<', 70)->where('eff', '>', 0)->count(),
            'machines_no_data' => $collection->where('eff', '=', 0)->count(),
        ];
    }

    /**
     * Tentukan shift saat ini berdasarkan jam
     */
    private function getCurrentShift()
    {
        $hour = now()->hour;
        // Shift 1: 07:00 - 18:59
        // Shift 2: 19:00 - 06:59
        return ($hour >= 7 && $hour < 19) ? '1' : '2';
    }

    /**
     * Hitung jam yang sudah berlalu dari awal shift
     */
    private function getElapsedHours($shift)
    {
        $now = now();
        
        if ($shift == '1') {
            // Shift 1 mulai jam 07:00
            $shiftStart = $now->copy()->setTime(7, 0, 0);
            
            // Jika sebelum jam 7, belum mulai
            if ($now->hour < 7) {
                return 0;
            }
            
            // Jika sudah lewat shift 1 (>= 19:00), maksimal 12 jam
            if ($now->hour >= 19) {
                return 12;
            }
        } else {
            // Shift 2 mulai jam 19:00
            if ($now->hour >= 19) {
                $shiftStart = $now->copy()->setTime(19, 0, 0);
            } else {
                // Jika sekarang antara 00:00 - 06:59, shift start kemarin jam 19:00
                $shiftStart = $now->copy()->subDay()->setTime(19, 0, 0);
            }
            
            // Maksimal 12 jam untuk shift 2
            if ($now->hour >= 7 && $now->hour < 19) {
                return 12;
            }
        }
        
        $elapsed = $now->diffInMinutes($shiftStart) / 60;
        return round(min($elapsed, 12), 2); // Max 12 jam per shift
    }

    /**
     * Tentukan status berdasarkan efficiency
     */
    private function getStatus($eff)
    {
        if ($eff == 0) return 'no-data';
        if ($eff >= 90) return 'on-target';
        if ($eff >= 70) return 'warning';
        return 'critical';
    }
}