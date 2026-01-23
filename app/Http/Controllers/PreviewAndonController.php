<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use App\Models\PreviewAndon;
use App\Services\PreviewAndonSyncService;
use Illuminate\Http\Request;

class PreviewAndonController extends Controller
{
    public function index(PreviewAndonSyncService $syncService)
    {
        $mesins = Mesin::orderBy('nama')->get();
        
        $dataByMesin = [];
        
        foreach ($mesins as $mesin) {
            $dataByMesin[$mesin->nama] = PreviewAndon::where('mesin_nama', $mesin->nama)
                ->orderBy('shift')
                ->orderBy('sort_order')
                ->get();
        }
        
        return view('andon.preview-andon', compact('mesins', 'dataByMesin'));
    }

    public function toggleActive(Request $request, $id)
    {
        try {
            $previewAndon = PreviewAndon::findOrFail($id);
            
            if ($request->has('force_active') && $request->force_active) {
                $previewAndon->is_active = true;
            } 
            else if ($request->has('force_inactive') && $request->force_inactive) {
                $previewAndon->is_active = false;
            }
            else if ($request->has('is_active')) {
                $previewAndon->is_active = $request->is_active;
            }
            else {
                $previewAndon->is_active = !$previewAndon->is_active;
            }
            
            $previewAndon->save();
            
            if ($previewAndon->is_active) {
                $syncService = new PreviewAndonSyncService();
                $syncService->recalculateScheduleForMesin($previewAndon->mesin_nama, $previewAndon->shift);
            }
            
            return response()->json([
                'success' => true,
                'message' => $previewAndon->is_active ? 'Data diaktifkan' : 'Data dinonaktifkan',
                'is_active' => $previewAndon->is_active
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status aktif: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function sync(PreviewAndonSyncService $syncService)
    {
        $count = $syncService->syncFromPlanning();
        
        return response()->json([
            'success' => true,
            'message' => "Data berhasil di-sync! {$count} records diupdate.",
            'count' => $count
        ]);
    }
    
    public function updateShift(Request $request, PreviewAndonSyncService $syncService)
    {
        $request->validate([
            'mesin_nama' => 'required|string',
            'shift' => 'required|in:1,2'
        ]);
        
        $count = $syncService->updateShift($request->mesin_nama, $request->shift);
        
        return response()->json([
            'success' => true,
            'message' => "Shift berhasil diubah ke Shift {$request->shift}!",
            'count' => $count
        ]);
    }
    
    public function updateActual(Request $request, $id)
    {
        $request->validate([
            'actual_qty' => 'required|integer|min:0',
            'start_actual' => 'nullable|date',
            'finish_actual' => 'nullable|date',
        ]);
        
        try {
            $previewAndon = PreviewAndon::findOrFail($id);
            
            // Update actual_qty
            $previewAndon->actual_qty = $request->actual_qty;
            
            // Update start_actual jika ada
            if ($request->has('start_actual') && $request->start_actual) {
                $previewAndon->start_actual = $request->start_actual;
            }
            
            // Update finish_actual jika ada
            if ($request->has('finish_actual') && $request->finish_actual) {
                $previewAndon->finish_actual = $request->finish_actual;
            }
            
            // Hitung efficiency
            if ($previewAndon->plan_qty > 0) {
                $previewAndon->efficiency = ($previewAndon->actual_qty / $previewAndon->plan_qty) * 100;
            }
            
            $previewAndon->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Data actual berhasil diupdate!',
                'data' => [
                    'id' => $previewAndon->id,
                    'actual_qty' => $previewAndon->actual_qty,
                    'start_actual' => $previewAndon->start_actual,
                    'finish_actual' => $previewAndon->finish_actual,
                    'efficiency' => $previewAndon->efficiency
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function detail($id)
    {
        $previewAndon = PreviewAndon::find($id);
        
        if (!$previewAndon) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $previewAndon->id,
                'part_no' => $previewAndon->part_no,
                'plan_qty' => $previewAndon->plan_qty,
                'actual_qty' => $previewAndon->actual_qty,
                'gsph' => $previewAndon->gsph,
                'shift' => $previewAndon->shift,
                'calculated_start' => $previewAndon->calculated_start,
                'calculated_finish' => $previewAndon->calculated_finish,
                'start_actual' => $previewAndon->start_actual,
                'finish_actual' => $previewAndon->finish_actual,
                'production_duration' => $previewAndon->production_duration,
            ]
        ]);
    }
    
    public function reorder(Request $request)
    {
        $request->validate([
            'mesin_nama' => 'required|string',
            'shift' => 'required|in:1,2',
            'order' => 'required|array',
            'order.*' => 'integer|exists:preview_andon,id'
        ]);
        
        try {
            foreach ($request->order as $index => $id) {
                PreviewAndon::where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
            
            $syncService = new PreviewAndonSyncService();
            $syncService->recalculateScheduleForMesin($request->mesin_nama, $request->shift);
            
            return response()->json([
                'success' => true,
                'message' => 'Urutan berhasil diupdate!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal update urutan: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function bulkUpdate(Request $request, PreviewAndonSyncService $syncService)
    {
        $request->validate([
            'mesin_nama' => 'required|string',
            'shift' => 'required|in:1,2',
            'updates' => 'nullable|array',
            'updates.*.id' => 'required|integer|exists:preview_andon,id',
            'updates.*.is_active' => 'required|boolean',
            'sort_order' => 'nullable|array',
            'sort_order.*' => 'integer|exists:preview_andon,id',
            'shift_changed' => 'nullable|boolean'
        ]);

        try {
            $mesinNama = $request->mesin_nama;
            $shift = $request->shift;
            
            if ($request->shift_changed) {
                $count = $syncService->updateShift($mesinNama, $shift);
            }
            
            if ($request->has('updates')) {
                foreach ($request->updates as $update) {
                    $previewAndon = PreviewAndon::where('id', $update['id'])
                        ->where('mesin_nama', $mesinNama)
                        ->first();
                    
                    if ($previewAndon) {
                        $previewAndon->is_active = $update['is_active'];
                        $previewAndon->save();
                    }
                }
            }
            
            if ($request->has('sort_order')) {
                foreach ($request->sort_order as $index => $id) {
                    PreviewAndon::where('id', $id)
                        ->where('mesin_nama', $mesinNama)
                        ->update(['sort_order' => $index + 1]);
                }
            }
            
            $syncService->recalculateScheduleForMesin($mesinNama, $shift);
            
            $updatedData = PreviewAndon::where('mesin_nama', $mesinNama)
                ->where('shift', $shift)
                ->orderBy('sort_order')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'part_no' => $item->part_no,
                        'gsph' => $item->gsph,
                        'calculated_start' => $item->calculated_start,
                        'calculated_finish' => $item->calculated_finish,
                        'start_actual' => $item->start_actual,
                        'finish_actual' => $item->finish_actual,
                        'plan_qty' => $item->plan_qty,
                        'actual_qty' => $item->actual_qty,
                        'efficiency' => $item->efficiency,
                        'is_active' => $item->is_active,
                        'sort_order' => $item->sort_order,
                        'shift' => $item->shift
                    ];
                });
            
            $activeCount = $updatedData->where('is_active', true)->count();
            $inactiveCount = $updatedData->where('is_active', false)->count();
            
            return response()->json([
                'success' => true,
                'message' => 'Perubahan berhasil disimpan!',
                'data' => $updatedData,
                'stats' => [
                    'active' => $activeCount,
                    'inactive' => $inactiveCount
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan perubahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getMesinData(Request $request)
    {
        $request->validate([
            'mesin' => 'required|string'
        ]);
        
        try {
            $mesinName = $request->mesin;
            
            $data = PreviewAndon::where('mesin_nama', $mesinName)
                ->orderBy('shift')
                ->orderBy('sort_order')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'part_no' => $item->part_no,
                        'gsph' => $item->gsph,
                        'calculated_start' => $item->calculated_start,
                        'calculated_finish' => $item->calculated_finish,
                        'start_actual' => $item->start_actual,
                        'finish_actual' => $item->finish_actual,
                        'plan_qty' => $item->plan_qty,
                        'actual_qty' => $item->actual_qty,
                        'efficiency' => $item->efficiency,
                        'is_active' => $item->is_active,
                        'sort_order' => $item->sort_order,
                        'shift' => $item->shift
                    ];
                });
            
            $activeCount = $data->where('is_active', true)->count();
            $inactiveCount = $data->where('is_active', false)->count();
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'stats' => [
                    'active' => $activeCount,
                    'inactive' => $inactiveCount
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    // METHOD SUBMIT KE ANDON MESIN (masih di controller ini)
    public function submitToAndonMesin(Request $request)
    {
        $request->validate([
            'mesin_id' => 'required|integer|exists:mesin,id',
            'mesin_nama' => 'required|string',
            'shift' => 'required|in:1,2',
            'tanggal' => 'required|date',
            'data' => 'required|array'
        ]);

        try {
            // Panggil method dari AndonMesinController
            $andonMesinController = new AndonMesinController();
            return $andonMesinController->store($request);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit: ' . $e->getMessage()
            ], 500);
        }
    }
}