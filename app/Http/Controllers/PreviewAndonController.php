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
        // Ambil semua mesin
        $mesins = Mesin::orderBy('nama')->get();
        
        $dataByMesin = [];
        
        foreach ($mesins as $mesin) {
            // Ambil data untuk mesin ini, urutkan berdasarkan shift dan sort_order
            $dataByMesin[$mesin->nama] = PreviewAndon::where('mesin_nama', $mesin->nama)
                ->orderBy('shift')
                ->orderBy('sort_order')
                ->get();
        }
        
        return view('andon.preview-andon', compact('mesins', 'dataByMesin'));
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
            'actual_qty' => 'required|integer|min:0'
        ]);
        
        $syncService = new PreviewAndonSyncService();
        $result = $syncService->updateActual($id, $request->actual_qty);
        
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Actual quantity berhasil diupdate!',
                'data' => $result
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal update actual quantity.'
        ], 404);
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
            // Update semua sort_order
            foreach ($request->order as $index => $id) {
                PreviewAndon::where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
            
            // Recalculate schedule
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
}