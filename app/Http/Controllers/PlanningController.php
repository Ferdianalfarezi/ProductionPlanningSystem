<?php

namespace App\Http\Controllers;

use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PlanningController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $totalPlannings = Planning::count();
        $statusList = Planning::distinct()->pluck('status')->filter()->values();
        $lineList = Planning::distinct()->pluck('line')->filter()->values();
        
        return view('plannings.index', compact('totalPlannings', 'statusList', 'lineList'));
    }

    /**
     * Get paginated data for AJAX.
     */
    public function data(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $search = $request->input('search', '');
        $statusFilter = $request->input('status', '');
        $lineFilter = $request->input('line', '');

        $query = Planning::query();

        // Search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('part_no_fg', 'like', "%{$search}%")
                  ->orWhere('part_no_parent', 'like', "%{$search}%")
                  ->orWhere('part_no_child', 'like', "%{$search}%")
                  ->orWhere('line', 'like', "%{$search}%")
                  ->orWhere('store', 'like', "%{$search}%")
                  ->orWhere('lot_no', 'like', "%{$search}%")
                  ->orWhere('rack_no', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (!empty($statusFilter)) {
            $query->where('status', $statusFilter);
        }

        // Line filter
        if (!empty($lineFilter)) {
            $query->where('line', $lineFilter);
        }

        $query->orderBy('priority', 'asc')->orderBy('id', 'desc');

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
            $statusBadge = $item->status_badge;
            return [
                'id' => $item->id,
                'row_number' => $from + $index,
                'planning_id' => $item->planning_id,
                'judgement' => $item->judgement,
                'priority' => $item->priority,
                'part_no_fg' => $item->part_no_fg,
                'part_no_parent' => $item->part_no_parent,
                'part_no_child' => $item->part_no_child,
                'store' => $item->store,
                'process' => $item->process,
                'line' => $item->line,
                'rack_no' => $item->rack_no,
                'qty_kbn' => $item->qty_kbn,
                'qty_lot' => $item->qty_lot,
                'total_prod' => $item->total_prod,
                'status' => $item->status,
                'status_badge' => $statusBadge,
                'actual' => $item->actual,
                'lot_no' => $item->lot_no,
                'calc_by' => $item->calc_by,
                'pulling_time' => $item->pulling_time ? $item->pulling_time->format('Y-m-d H:i') : '-',
                'start_time' => $item->start_time ? $item->start_time->format('Y-m-d H:i') : '-',
                'end_time' => $item->end_time ? $item->end_time->format('Y-m-d H:i') : '-',
                'reason' => $item->reason,
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
                'total_plannings' => Planning::count(),
                'open' => Planning::where('status', 'Open')->count(),
                'running' => Planning::where('status', 'R')->count(),
                'complete' => Planning::where('status', 'C')->count(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'part_no_fg' => 'required|string|max:50',
            'part_no_parent' => 'nullable|string|max:50',
            'part_no_child' => 'nullable|string|max:50',
            'judgement' => 'nullable|string|max:50',
            'priority' => 'nullable|integer|min:0',
            'store' => 'nullable|string|max:50',
            'process' => 'nullable|string|max:20',
            'line' => 'nullable|string|max:50',
            'rack_no' => 'nullable|string|max:50',
            'qty_kbn' => 'nullable|integer|min:0',
            'qty_lot' => 'nullable|integer|min:0',
            'total_prod' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:20',
            'lot_no' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $planning = Planning::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Planning berhasil ditambahkan!',
            'data' => $planning,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Planning $planning)
    {
        return response()->json([
            'success' => true,
            'data' => $planning,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Planning $planning)
    {
        $validator = Validator::make($request->all(), [
            'part_no_fg' => 'required|string|max:50',
            'part_no_parent' => 'nullable|string|max:50',
            'part_no_child' => 'nullable|string|max:50',
            'judgement' => 'nullable|string|max:50',
            'priority' => 'nullable|integer|min:0',
            'store' => 'nullable|string|max:50',
            'process' => 'nullable|string|max:20',
            'line' => 'nullable|string|max:50',
            'rack_no' => 'nullable|string|max:50',
            'qty_kbn' => 'nullable|integer|min:0',
            'qty_lot' => 'nullable|integer|min:0',
            'total_prod' => 'nullable|integer|min:0',
            'status' => 'nullable|string|max:20',
            'lot_no' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $planning->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Planning berhasil diupdate!',
            'data' => $planning,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Planning $planning)
    {
        try {
            $planning->delete();

            return response()->json([
                'success' => true,
                'message' => 'Planning berhasil dihapus!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus planning!',
            ], 500);
        }
    }

    /**
     * Import from Excel.
     */
    public function import(Request $request)
    {
        \Log::info('=== PLANNING IMPORT STARTED ===');
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
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

            if (empty($rows) || count($rows) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'File Excel kosong atau hanya berisi header!',
                ], 422);
            }

            // Get header row - check first row
            $header = $rows[0];
            
            // Check if first row is title (E-Planning Production)
            $firstCell = trim($header[0] ?? '');
            if (stripos($firstCell, 'E-Planning') !== false || empty($header[1])) {
                // Use second row as header
                $header = $rows[1] ?? [];
                $dataStartRow = 2;
            } else {
                $dataStartRow = 1;
            }

            \Log::info('Header:', $header);

            // Build header map (normalize column names)
            $headerMap = [];
            foreach ($header as $index => $col) {
                if ($col !== null && $col !== '') {
                    $normalized = strtolower(str_replace(['_', ' ', '-'], '', trim($col)));
                    $headerMap[$normalized] = $index;
                }
            }

            \Log::info('Header map:', $headerMap);

            // Column mapping
            $columnMapping = [
                'id' => ['id', 'planningid'],
                'judgement' => ['judgement', 'judge'],
                'priority' => ['priority', 'prio'],
                'partnofg' => ['partnofg', 'partno'],
                'partnoparent' => ['partnoparent'],
                'partnochild' => ['partnochild'],
                'store' => ['store'],
                'process' => ['process', 'proc'],
                'line' => ['line'],
                'rackno' => ['rackno', 'rack'],
                'seqcalc' => ['seqcalc', 'seq'],
                'qtykbn' => ['qtykbn', 'kbn'],
                'qtyconsume' => ['qtyconsume', 'consume'],
                'qtylot' => ['qtylot', 'lot'],
                'stockmin' => ['stockmin', 'min'],
                'stockmax' => ['stockmax', 'max'],
                'stockprod' => ['stockprod'],
                'stockstore' => ['stockstore'],
                'stockrealtime' => ['stockrealtime', 'realtime'],
                'calcparent' => ['calcparent'],
                'calcchild' => ['calcchild'],
                'calclot' => ['calclot'],
                'calcprod' => ['calcprod'],
                'addprod' => ['addprod'],
                'totalprod' => ['totalprod', 'total'],
                'status' => ['status', 'stat'],
                'qtyprint' => ['qtyprint', 'print'],
                'actual' => ['actual', 'act'],
                'urutan' => ['urutan', 'order'],
                'pullingtime' => ['pullingtime', 'pulling'],
                'ltpull' => ['ltpull'],
                'ltprod' => ['ltprod'],
                'maxprodtime' => ['maxprodtime'],
                'starttime' => ['starttime', 'start'],
                'endtime' => ['endtime', 'end'],
                'lotno' => ['lotno'],
                'calcby' => ['calcby'],
                'calctime' => ['calctime'],
                'reason' => ['reason'],
                'updatetime' => ['updatetime', 'update'],
            ];

            // Find column indices
            $colIndices = [];
            foreach ($columnMapping as $field => $aliases) {
                foreach ($aliases as $alias) {
                    if (isset($headerMap[$alias])) {
                        $colIndices[$field] = $headerMap[$alias];
                        break;
                    }
                }
            }

            \Log::info('Column indices:', $colIndices);

            $imported = 0;
            $skipped = 0;
            $errors = [];

            // Process data rows
            for ($i = $dataStartRow; $i < count($rows); $i++) {
                $row = $rows[$i];
                $rowNumber = $i + 1;

                // Get values
                $planningId = $this->getValue($row, $colIndices, 'id');
                $partNoFg = $this->getValue($row, $colIndices, 'partnofg');

                // Skip empty rows
                if (empty($planningId) && empty($partNoFg)) {
                    continue;
                }

                // Prepare data
                $data = [
                    'planning_id' => $this->parseInteger($planningId),
                    'judgement' => $this->getValue($row, $colIndices, 'judgement'),
                    'priority' => $this->parseInteger($this->getValue($row, $colIndices, 'priority')),
                    'part_no_fg' => $partNoFg,
                    'part_no_parent' => $this->getValue($row, $colIndices, 'partnoparent'),
                    'part_no_child' => $this->getValue($row, $colIndices, 'partnochild'),
                    'store' => $this->getValue($row, $colIndices, 'store'),
                    'process' => $this->getValue($row, $colIndices, 'process'),
                    'line' => $this->getValue($row, $colIndices, 'line'),
                    'rack_no' => $this->getValue($row, $colIndices, 'rackno'),
                    'seq_calc' => $this->parseInteger($this->getValue($row, $colIndices, 'seqcalc')),
                    'qty_kbn' => $this->parseInteger($this->getValue($row, $colIndices, 'qtykbn')),
                    'qty_consume' => $this->parseInteger($this->getValue($row, $colIndices, 'qtyconsume')),
                    'qty_lot' => $this->parseInteger($this->getValue($row, $colIndices, 'qtylot')),
                    'stock_min' => $this->parseInteger($this->getValue($row, $colIndices, 'stockmin')),
                    'stock_max' => $this->parseInteger($this->getValue($row, $colIndices, 'stockmax')),
                    'stock_prod' => $this->parseInteger($this->getValue($row, $colIndices, 'stockprod')),
                    'stock_store' => $this->parseInteger($this->getValue($row, $colIndices, 'stockstore')),
                    'stock_realtime' => $this->parseInteger($this->getValue($row, $colIndices, 'stockrealtime')),
                    'calc_parent' => $this->parseInteger($this->getValue($row, $colIndices, 'calcparent')),
                    'calc_child' => $this->parseInteger($this->getValue($row, $colIndices, 'calcchild')),
                    'calc_lot' => $this->parseInteger($this->getValue($row, $colIndices, 'calclot')),
                    'calc_prod' => $this->parseInteger($this->getValue($row, $colIndices, 'calcprod')),
                    'add_prod' => $this->parseInteger($this->getValue($row, $colIndices, 'addprod')),
                    'total_prod' => $this->parseInteger($this->getValue($row, $colIndices, 'totalprod')),
                    'status' => $this->getValue($row, $colIndices, 'status'),
                    'qty_print' => $this->parseInteger($this->getValue($row, $colIndices, 'qtyprint')),
                    'actual' => $this->parseInteger($this->getValue($row, $colIndices, 'actual')),
                    'urutan' => $this->parseInteger($this->getValue($row, $colIndices, 'urutan')),
                    'pulling_time' => $this->parseDateTime($this->getValue($row, $colIndices, 'pullingtime')),
                    'lt_pull' => $this->parseInteger($this->getValue($row, $colIndices, 'ltpull')),
                    'lt_prod' => $this->parseInteger($this->getValue($row, $colIndices, 'ltprod')),
                    'max_prod_time' => $this->parseDateTime($this->getValue($row, $colIndices, 'maxprodtime')),
                    'start_time' => $this->parseDateTime($this->getValue($row, $colIndices, 'starttime')),
                    'end_time' => $this->parseDateTime($this->getValue($row, $colIndices, 'endtime')),
                    'lot_no' => $this->getValue($row, $colIndices, 'lotno'),
                    'calc_by' => $this->getValue($row, $colIndices, 'calcby'),
                    'calc_time' => $this->parseDateTime($this->getValue($row, $colIndices, 'calctime')),
                    'reason' => $this->getValue($row, $colIndices, 'reason'),
                    'update_time_excel' => $this->parseDateTime($this->getValue($row, $colIndices, 'updatetime')),
                ];

                try {
                    Planning::create($data);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNumber}: {$e->getMessage()}";
                    $skipped++;
                    \Log::error("Row {$rowNumber} failed:", ['error' => $e->getMessage()]);
                }
            }

            $message = "Import selesai! {$imported} data berhasil diimport.";
            if ($skipped > 0) {
                $message .= " {$skipped} data dilewati.";
            }

            \Log::info("=== IMPORT COMPLETED: {$imported} imported, {$skipped} skipped ===");

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => array_slice($errors, 0, 10),
            ]);

        } catch (\Exception $e) {
            \Log::error('Import failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimport file: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get value from row by column index.
     */
    private function getValue(array $row, array $colIndices, string $field)
    {
        if (!isset($colIndices[$field])) {
            return null;
        }
        
        $value = $row[$colIndices[$field]] ?? null;
        
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }
        
        return is_string($value) ? trim($value) : $value;
    }

    /**
     * Parse integer value.
     */
    private function parseInteger($value): ?int
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }
        
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Parse datetime value.
     */
    private function parseDateTime($value): ?string
    {
        if ($value === null || $value === '' || $value === '-' || $value === 'NaT') {
            return null;
        }
        
        try {
            // If it's already a datetime string
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                return $value;
            }
            
            // If it's an Excel date serial
            if (is_numeric($value)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d H:i:s');
            }
            
            // Try to parse as date
            $date = new \DateTime($value);
            return $date->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Download import template.
     */
    public function template()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Planning Template');

        // Headers
        $headers = [
            'ID', 'JUDGEMENT', 'PRIORITY', 'PART_NO_FG', 'PART_NO_PARENT', 'PART_NO_CHILD',
            'STORE', 'PROCESS', 'LINE', 'RACK_NO', 'SEQ_CALC', 'QTY_KBN', 'QTY_CONSUME',
            'QTY_LOT', 'STOCK_MIN', 'STOCK_MAX', 'STOCK_PROD', 'STOCK_STORE', 'STOCK_REALTIME',
            'CALC_PARENT', 'CALC_CHILD', 'CALC_LOT', 'CALC_PROD', 'ADD_PROD', 'TOTAL_PROD',
            'STATUS', 'QTY_PRINT', 'ACTUAL', 'URUTAN', 'PULLING_TIME', 'LT_PULL', 'LT_PROD',
            'MAX_PROD_TIME', 'START_TIME', 'END_TIME', 'LOT_NO', 'CALC_BY', 'CALC_TIME',
            'REASON', 'UPDATE_TIME'
        ];
        $sheet->fromArray($headers, null, 'A1');

        // Style header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F2937']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $lastCol = chr(64 + count($headers)); // Get last column letter
        if (count($headers) > 26) {
            $lastCol = 'A' . chr(64 + count($headers) - 26);
        }
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);

        // Sample data
        $sampleData = [
            1001, 'Production', 1, '51321-BZ110', '51321-BZ110', '51321-BZ110',
            'FINISH GOODS', 'TD', 'LINE 9', 'E1-20-A', 1, 10, 0,
            24, 18, 42, 0, 0, 0,
            10, 52, 2, 48, 0, 48,
            'R', 40, 30, 1, '2026-01-22 08:00:00', 60, 3,
            '2026-01-22 07:00:00', '2026-01-24 02:40:00', '2026-01-24 05:04:00', 'S9 22 A 26 A', 'MINOR', '2026-01-21 22:51:18',
            'Partial', '2026-01-22 07:15:24'
        ];
        $sheet->fromArray($sampleData, null, 'A2');

        // Auto width for all columns
        foreach (range('A', 'Z') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension('A' . $col)->setAutoSize(true);
        }

        // Note sheet
        $noteSheet = $spreadsheet->createSheet();
        $noteSheet->setTitle('Notes');
        $noteSheet->setCellValue('A1', 'CATATAN IMPORT PLANNING:');
        $noteSheet->setCellValue('A2', '1. Kolom ID dan PART_NO_FG minimal salah satu harus diisi');
        $noteSheet->setCellValue('A3', '2. Format datetime: YYYY-MM-DD HH:MM:SS');
        $noteSheet->setCellValue('A4', '3. Status yang valid: Open, R (Running), J (Job), C (Complete)');
        $noteSheet->setCellValue('A5', '4. Kolom numerik akan dikonversi otomatis');
        $noteSheet->getStyle('A1')->getFont()->setBold(true);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'template_import_planning.xlsx';
        $tempFile = storage_path('app/temp/' . $filename);
        
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }
        
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Clear all plannings.
     */
    public function clear()
    {
        try {
            $count = Planning::count();
            Planning::truncate();

            return response()->json([
                'success' => true,
                'message' => "{$count} data planning berhasil dihapus!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage(),
            ], 500);
        }
    }
}