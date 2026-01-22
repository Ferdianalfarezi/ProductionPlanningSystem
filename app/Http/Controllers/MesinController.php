<?php

namespace App\Http\Controllers;

use App\Models\Mesin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MesinController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $totalMesin = Mesin::count();
        
        return view('mesin.index', compact('totalMesin'));
    }

    /**
     * Get mesin data for AJAX with server-side pagination.
     */
    public function data(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $search = $request->input('search', '');
        $page = $request->input('page', 1);

        $query = Mesin::query();

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('tonase', 'like', "%{$search}%");
            });
        }

        // Get total for stats
        $totalMesin = Mesin::count();

        // Handle pagination
        if ($perPage === 'all') {
            $mesin = $query->orderBy('id', 'asc')->get();
            $total = $mesin->count();
            $from = $total > 0 ? 1 : 0;
            $to = $total;
            $totalPages = 1;
        } else {
            $perPage = (int) $perPage;
            $paginated = $query->orderBy('id', 'asc')->paginate($perPage, ['*'], 'page', $page);
            $mesin = $paginated->items();
            $total = $paginated->total();
            $from = $paginated->firstItem() ?? 0;
            $to = $paginated->lastItem() ?? 0;
            $totalPages = $paginated->lastPage();
        }

        // Format data with row numbers
        $startNumber = $perPage === 'all' ? 1 : (($page - 1) * $perPage) + 1;
        $formattedData = collect($mesin)->map(function ($item, $index) use ($startNumber) {
            return [
                'id' => $item->id,
                'row_number' => $startNumber + $index,
                'nama' => $item->nama,
                'struk' => $item->struk,
                'tonase' => $item->tonase ?? '-',
                'created_at' => $item->created_at?->format('d/m/Y H:i'),
                'updated_at' => $item->updated_at?->format('d/m/Y H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $totalPages,
                'from' => $from,
                'to' => $to,
            ],
            'stats' => [
                'total_mesin' => $totalMesin,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'struk' => 'required|integer|min:0',
            'tonase' => 'nullable|string|max:100',
        ], [
            'nama.required' => 'Nama mesin wajib diisi.',
            'struk.required' => 'Struk wajib diisi.',
            'struk.integer' => 'Struk harus berupa angka.',
        ]);

        try {
            $mesin = Mesin::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil ditambahkan!',
                'data' => $mesin,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan mesin: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Mesin $mesin): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $mesin->id,
                'nama' => $mesin->nama,
                'struk' => $mesin->struk,
                'tonase' => $mesin->tonase,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mesin $mesin): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'struk' => 'required|integer|min:0',
            'tonase' => 'nullable|string|max:100',
        ], [
            'nama.required' => 'Nama mesin wajib diisi.',
            'struk.required' => 'Struk wajib diisi.',
            'struk.integer' => 'Struk harus berupa angka.',
        ]);

        try {
            $mesin->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil diupdate!',
                'data' => $mesin,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate mesin: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mesin $mesin): JsonResponse
    {
        try {
            $mesin->delete();

            return response()->json([
                'success' => true,
                'message' => 'Mesin berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus mesin: ' . $e->getMessage(),
            ], 500);
        }
    }
}