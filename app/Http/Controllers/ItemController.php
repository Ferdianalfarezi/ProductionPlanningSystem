<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Mesin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $totalItems = Item::count();
        $mesinList = Mesin::orderBy('nama')->get();
        
        
        return view('items.index', compact('totalItems', 'mesinList'));
    }

    /**
     * Get paginated data for AJAX.
     */
    public function data(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $search = $request->input('search', '');
        $mesinFilter = $request->input('mesin_id', '');

        $query = Item::with('mesin');

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no', 'like', "%{$search}%")
                  ->orWhere('line', 'like', "%{$search}%")
                  ->orWhere('process', 'like', "%{$search}%")
                  ->orWhere('process_name', 'like', "%{$search}%")
                  ->orWhereHas('mesin', function ($mq) use ($search) {
                      $mq->where('nama', 'like', "%{$search}%")
                        ->orWhere('tonase', 'like', "%{$search}%");
                  });
            });
        }

        // Mesin filter
        if (!empty($mesinFilter)) {
            $query->where('mesin_id', $mesinFilter);
        }

        $query->orderBy('id', 'desc');

        // Pagination
        if ($perPage === 'all') {
            $items = $query->get();
            $total = $items->count();
            $from = $total > 0 ? 1 : 0;
            $to = $total;
            $totalPages = 1;
            $currentPage = 1;
        } else {
            $perPage = (int) $perPage;
            $paginated = $query->paginate($perPage);
            $items = $paginated->items();
            $total = $paginated->total();
            $from = $paginated->firstItem() ?? 0;
            $to = $paginated->lastItem() ?? 0;
            $totalPages = $paginated->lastPage();
            $currentPage = $paginated->currentPage();
        }

        // Transform data
        $data = collect($items)->map(function ($item, $index) use ($from) {
            return [
                'id' => $item->id,
                'row_number' => $from + $index,
                'part_no' => $item->part_no,
                'line' => $item->line,
                'mesin_id' => $item->mesin_id,
                'machine' => $item->mesin?->nama ?? '-',
                'tonase' => $item->mesin?->tonase ?? '-',
                'process' => $item->process,
                'process_name' => $item->process_name,
                'gsph' => $item->gsph ? number_format($item->gsph, 2) : '-',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'from' => $from,
                'to' => $to,
                'current_page' => $currentPage,
                'total_pages' => $totalPages,
            ],
            'stats' => [
                'total_items' => Item::count(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'part_no' => 'required|string|max:50',
            'line' => 'nullable|string|max:50',
            'mesin_id' => 'required|exists:mesin,id',
            'process' => 'nullable|string|max:50',
            'process_name' => 'nullable|string|max:100',
            'gsph' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $item = Item::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil ditambahkan!',
            'data' => $item,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $item->load('mesin');
        
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $item->id,
                'part_no' => $item->part_no,
                'line' => $item->line,
                'mesin_id' => $item->mesin_id,
                'machine' => $item->mesin?->nama,
                'tonase' => $item->mesin?->tonase,
                'process' => $item->process,
                'process_name' => $item->process_name,
                'gsph' => $item->gsph,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validator = Validator::make($request->all(), [
            'part_no' => 'required|string|max:50',
            'line' => 'nullable|string|max:50',
            'mesin_id' => 'required|exists:mesin,id',
            'process' => 'nullable|string|max:50',
            'process_name' => 'nullable|string|max:100',
            'gsph' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $item->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil diupdate!',
            'data' => $item,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        try {
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus item!',
            ], 500);
        }
    }

    public function import(Request $request)
{
    \Log::info('=== IMPORT STARTED ===');
    
    $validator = Validator::make($request->all(), [
        'file' => 'required|file|mimes:xlsx,xls|max:10240',
    ]);

    if ($validator->fails()) {
        \Log::error('Validation failed:', $validator->errors()->toArray());
        return response()->json([
            'success' => false,
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        $file = $request->file('file');
        \Log::info('File uploaded:', ['name' => $file->getClientOriginalName()]);

        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        \Log::info('Total rows (with header):', [count($rows)]);

        if (empty($rows) || count($rows) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'File Excel kosong atau hanya berisi header!',
            ], 422);
        }

        // Get and process header
        $header = array_shift($rows);
        \Log::info('Raw header:', $header);
        
        // Normalize header - remove underscore, space, and lowercase
        $headerMap = [];
        foreach ($header as $index => $col) {
            if ($col !== null && $col !== '') {
                $normalized = strtolower(str_replace(['_', ' ', '-'], '', trim($col)));
                $headerMap[$normalized] = $index;
            }
        }

        \Log::info('Header map:', $headerMap);

        // Check required columns
        $requiredColumns = ['partno', 'machine'];
        foreach ($requiredColumns as $col) {
            if (!isset($headerMap[$col])) {
                \Log::error("Missing column: {$col}");
                return response()->json([
                    'success' => false,
                    'message' => "Kolom '{$col}' tidak ditemukan! Kolom yang ada: " . implode(', ', array_keys($headerMap)),
                ], 422);
            }
        }

        // Get all mesin and create cache
        $mesinList = Mesin::all();
        
        if ($mesinList->isEmpty()) {
            \Log::error('No mesin data found in database');
            return response()->json([
                'success' => false,
                'message' => 'Master Mesin kosong! Silakan tambahkan data mesin terlebih dahulu.',
            ], 422);
        }

        \Log::info('Total mesin:', [$mesinList->count()]);

        // Build mesin cache (case insensitive)
        $mesinCache = [];
        foreach ($mesinList as $mesin) {
            $key = strtolower(trim($mesin->nama));
            $mesinCache[$key] = $mesin->id;
            \Log::info("Cached mesin: '{$mesin->nama}' -> ID {$mesin->id}");
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // Excel row (1-based + header)
            
            // Extract data
            $partNo = isset($headerMap['partno']) && isset($row[$headerMap['partno']]) 
                ? trim($row[$headerMap['partno']]) 
                : '';
            
            $machineName = isset($headerMap['machine']) && isset($row[$headerMap['machine']]) 
                ? trim($row[$headerMap['machine']]) 
                : '';
            
            $line = isset($headerMap['line']) && isset($row[$headerMap['line']]) 
                ? trim($row[$headerMap['line']]) 
                : null;
            
            $process = isset($headerMap['process']) && isset($row[$headerMap['process']]) 
                ? trim($row[$headerMap['process']]) 
                : null;
            
            // Handle both "processname" and "process_name"
            $processName = null;
            if (isset($headerMap['processname']) && isset($row[$headerMap['processname']])) {
                $processName = trim($row[$headerMap['processname']]);
            }
            
            $gsph = isset($headerMap['gsph']) && isset($row[$headerMap['gsph']]) 
                ? $row[$headerMap['gsph']] 
                : null;

            // Log first 3 rows for debugging
            if ($rowIndex < 3) {
                \Log::info("Row {$rowNumber}:", [
                    'part_no' => $partNo,
                    'machine' => $machineName,
                    'line' => $line,
                    'process' => $process,
                    'process_name' => $processName,
                    'gsph' => $gsph,
                ]);
            }

            // Skip completely empty rows
            if (empty($partNo) && empty($machineName)) {
                \Log::info("Row {$rowNumber}: Empty, skipped");
                continue;
            }

            // Validate required fields
            if (empty($partNo)) {
                $errors[] = "Baris {$rowNumber}: Part No kosong";
                $skipped++;
                continue;
            }

            if (empty($machineName)) {
                $errors[] = "Baris {$rowNumber}: Machine kosong";
                $skipped++;
                continue;
            }

            // Find mesin (case insensitive)
            $machineKey = strtolower(trim($machineName));
            $mesinId = $mesinCache[$machineKey] ?? null;
            
            if (!$mesinId) {
                $errors[] = "Baris {$rowNumber}: Mesin '{$machineName}' tidak ditemukan";
                $skipped++;
                \Log::warning("Row {$rowNumber}: Machine '{$machineName}' not found");
                continue;
            }

            // Process GSPH - ambil 3 digit pertama
            $gsphValue = null;
            if ($gsph !== null && $gsph !== '') {
                // Ambil hanya angka
                $numericGsph = preg_replace('/\D/', '', (string) $gsph);

                if ($numericGsph !== '') {
                    // Ambil 3 digit pertama
                    $gsphValue = (int) substr($numericGsph, 0, 3);
                } else {
                    \Log::warning("Row {$rowNumber}: Invalid GSPH value: {$gsph}");
                }
            }


            // Prepare data
            $itemData = [
                'part_no' => $partNo,
                'line' => $line ?: null,
                'mesin_id' => $mesinId,
                'process' => $process ?: null,
                'process_name' => $processName ?: null,
                'gsph' => $gsphValue,
            ];

            try {
                $item = Item::create($itemData);
                $imported++;
                
                if ($rowIndex < 3) {
                    \Log::info("Row {$rowNumber}: Created item ID {$item->id}");
                }
            } catch (\Exception $e) {
                $errors[] = "Baris {$rowNumber}: {$e->getMessage()}";
                $skipped++;
                \Log::error("Row {$rowNumber} failed:", [
                    'error' => $e->getMessage(),
                    'data' => $itemData
                ]);
            }
        }

        $message = "Import selesai! {$imported} data berhasil diimport.";
        if ($skipped > 0) {
            $message .= " {$skipped} data dilewati.";
        }

        \Log::info("=== IMPORT COMPLETED ===");
        \Log::info("Result: {$imported} imported, {$skipped} skipped");

        return response()->json([
            'success' => true,
            'message' => $message,
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 10),
        ]);

    } catch (\Exception $e) {
        \Log::error('=== IMPORT FAILED ===');
        \Log::error('Error: ' . $e->getMessage());
        \Log::error('Trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal mengimport file: ' . $e->getMessage(),
        ], 500);
    }
}

    /**
     * Download import template.
     */
    public function template()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Headers
        $headers = ['No', 'Part_No', 'Line', 'Machine', 'Tonage', 'Process', 'Process_name', 'GSPH'];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

        // Sample data
        $sheet->fromArray([1, '52566-BZ010', 'Line 9', 'PT95', '110 T', '2/9', 'BE', 352.67], null, 'A2');
        $sheet->fromArray([2, '52566-BZ010', 'Line 9', 'PT96', '110 T', '3/9', 'BE', 203.67], null, 'A3');

        // Auto width
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Note sheet
        $noteSheet = $spreadsheet->createSheet();
        $noteSheet->setTitle('Notes');
        $noteSheet->setCellValue('A1', 'CATATAN IMPORT:');
        $noteSheet->setCellValue('A2', '1. Kolom "Machine" HARUS sesuai dengan nama mesin di Master Mesin');
        $noteSheet->setCellValue('A3', '2. Kolom "Tonage" akan diambil otomatis dari Master Mesin berdasarkan Machine');
        $noteSheet->setCellValue('A4', '3. Kolom "Part_No" dan "Machine" wajib diisi');
        $noteSheet->setCellValue('A5', '4. Kolom "No" bisa diabaikan (hanya untuk referensi)');
        $noteSheet->getStyle('A1')->getFont()->setBold(true);

        $spreadsheet->setActiveSheetIndex(0);

        // Response
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        $filename = 'template_import_items.xlsx';
        $tempFile = storage_path('app/temp/' . $filename);
        
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
}