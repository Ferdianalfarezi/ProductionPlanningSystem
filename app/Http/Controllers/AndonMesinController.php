<?php

namespace App\Http\Controllers;

use App\Models\AndonMesin;
use App\Models\Mesin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AndonMesinController extends Controller
{
    /**
     * Menampilkan halaman Andon Mesin
     */
    public function index()
    {
        // Ambil list mesin yang pernah di-submit untuk dropdown
        $submittedMesin = AndonMesin::select('mesin_id', 'mesin_nama')
            ->distinct()
            ->orderBy('mesin_nama')
            ->get();

        // Default tanggal hari ini
        $defaultDate = now()->format('Y-m-d');
        
        return view('andon.mesin', compact('submittedMesin', 'defaultDate'));
    }

    /**
     * Menyimpan data submit dari Preview Andon
     */
    public function store(Request $request)
    {
        $request->validate([
            'mesin_id' => 'required|integer|exists:mesin,id',
            'mesin_nama' => 'required|string',
            'shift' => 'required|in:1,2',
            'tanggal' => 'required|date',
            'data' => 'required|array'
        ]);

        DB::beginTransaction();
        
        try {
            // Cek apakah sudah ada data untuk mesin+shift+tanggal ini
            $existing = AndonMesin::where('mesin_id', $request->mesin_id)
                ->where('shift', $request->shift)
                ->where('tanggal', $request->tanggal)
                ->first();

            if ($existing) {
                // Update existing data
                $existing->update([
                    'data_json' => $request->data,
                    'submitted_at' => now(),
                    'updated_at' => now()
                ]);
                
                $message = 'Data andon mesin berhasil diupdate!';
            } else {
                // Create new submission
                AndonMesin::create([
                    'mesin_id' => $request->mesin_id,
                    'mesin_nama' => $request->mesin_nama,
                    'shift' => $request->shift,
                    'tanggal' => $request->tanggal,
                    'data_json' => $request->data,
                    'submitted_at' => now()
                ]);
                
                $message = 'Data berhasil disubmit ke andon mesin!';
            }

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal submit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil data Andon Mesin dengan filter
     */
    public function getData(Request $request)
    {
        $request->validate([
            'mesin_id' => 'nullable|integer',
            'tanggal' => 'nullable|date',
            'shift' => 'nullable|in:1,2'
        ]);

        $query = AndonMesin::with('mesin');
        
        if ($request->mesin_id) {
            $query->where('mesin_id', $request->mesin_id);
        }
        
        if ($request->tanggal) {
            $query->where('tanggal', $request->tanggal);
        } else {
            $query->where('tanggal', now()->format('Y-m-d'));
        }
        
        if ($request->shift) {
            $query->where('shift', $request->shift);
        }
        
        $data = $query->orderBy('tanggal', 'desc')
                      ->orderBy('shift', 'desc')
                      ->get()
                      ->map(function ($item) {
                          return [
                              'id' => $item->id,
                              'mesin_id' => $item->mesin_id,
                              'mesin_nama' => $item->mesin_nama,
                              'shift' => $item->shift,
                              'tanggal' => $item->tanggal,
                              'data_json' => $item->data_json,
                              'submitted_at' => $item->submitted_at,
                              'submitted_time' => $item->submitted_at ? $item->submitted_at->format('H:i') : null,
                              'submitted_date' => $item->submitted_at ? $item->submitted_at->format('d/m/Y') : null,
                              'mesin_info' => $item->mesin ? [
                                  'struk' => $item->mesin->struk,
                                  'tonase' => $item->mesin->tonase
                              ] : null
                          ];
                      });

        // Get list mesin yang pernah di-submit untuk dropdown
        $mesin = AndonMesin::select('mesin_id', 'mesin_nama')
            ->distinct()
            ->orderBy('mesin_nama')
            ->get();

        // Get unique dates untuk filter tanggal
        $dates = AndonMesin::select('tanggal')
            ->distinct()
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->tanggal,
                    'label' => Carbon::parse($item->tanggal)->format('d/m/Y'),
                    'count' => AndonMesin::where('tanggal', $item->tanggal)->count()
                ];
            });

        // Get statistics
        $totalSubmissions = $data->count();
        $totalRows = $data->sum(function($item) {
            return count($item['data_json'] ?? []);
        });
        $activeRows = $data->sum(function($item) {
            return collect($item['data_json'] ?? [])->where('is_active', true)->count();
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'mesin' => $mesin,
            'dates' => $dates,
            'statistics' => [
                'total_submissions' => $totalSubmissions,
                'total_rows' => $totalRows,
                'active_rows' => $activeRows
            ],
            'filters' => [
                'selected_mesin' => $request->mesin_id,
                'selected_tanggal' => $request->tanggal ?: now()->format('Y-m-d'),
                'selected_shift' => $request->shift
            ]
        ]);
    }

    /**
     * Get last submission status untuk tombol submit di Preview Andon
     */
    public function getLastSubmissionStatus($mesinId)
    {
        $lastSubmission = AndonMesin::where('mesin_id', $mesinId)
            ->orderBy('submitted_at', 'desc')
            ->first();

        if (!$lastSubmission) {
            return response()->json([
                'success' => true,
                'has_submission' => false
            ]);
        }

        return response()->json([
            'success' => true,
            'has_submission' => true,
            'data' => [
                'id' => $lastSubmission->id,
                'mesin_id' => $lastSubmission->mesin_id,
                'mesin_nama' => $lastSubmission->mesin_nama,
                'shift' => $lastSubmission->shift,
                'tanggal' => $lastSubmission->tanggal,
                'submitted_at' => $lastSubmission->submitted_at->format('Y-m-d H:i:s'),
                'submitted_time' => $lastSubmission->submitted_at->format('H:i'),
                'submitted_date' => $lastSubmission->submitted_at->format('d/m/Y')
            ]
        ]);
    }

    /**
     * Export data Andon Mesin ke Excel
     */
    public function export(Request $request)
    {
        $request->validate([
            'mesin_id' => 'nullable|integer',
            'tanggal' => 'nullable|date',
            'shift' => 'nullable|in:1,2'
        ]);

        $query = AndonMesin::query();
        
        if ($request->mesin_id) {
            $query->where('mesin_id', $request->mesin_id);
        }
        
        if ($request->tanggal) {
            $query->where('tanggal', $request->tanggal);
        }
        
        if ($request->shift) {
            $query->where('shift', $request->shift);
        }
        
        $data = $query->orderBy('tanggal', 'desc')
                      ->orderBy('shift', 'desc')
                      ->get();

        // TODO: Implement Excel export
        // Menggunakan Laravel Excel atau manual

        return response()->json([
            'success' => true,
            'message' => 'Export feature coming soon',
            'data_count' => $data->count()
        ]);
    }

    /**
     * Get tanggal-tanggal yang tersedia untuk filter
     */
    public function getAvailableDates()
    {
        $dates = AndonMesin::select('tanggal')
            ->distinct()
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'value' => $item->tanggal,
                    'label' => Carbon::parse($item->tanggal)->format('d/m/Y'),
                    'count' => AndonMesin::where('tanggal', $item->tanggal)->count()
                ];
            });

        return response()->json([
            'success' => true,
            'dates' => $dates
        ]);
    }

    /**
     * Delete submission (optional, untuk admin)
     */
    public function destroy($id)
    {
        try {
            $submission = AndonMesin::findOrFail($id);
            $submission->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Submission berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage()
            ], 500);
        }
    }
}