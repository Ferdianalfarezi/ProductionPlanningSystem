@extends('layouts.app')

@section('title', 'Preview Andon')

@section('content')
<style>
    .toggle-switch { position: relative; display: inline-block; width: 70px; height: 32px; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #3b82f6; transition: .4s; border-radius: 34px; }
    .toggle-slider:before { position: absolute; content: "1"; height: 24px; width: 24px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; color: #3b82f6; }
    input:checked + .toggle-slider { background-color: #1f2937; }
    input:checked + .toggle-slider:before { content: "2"; transform: translateX(38px); color: #1f2937; }
    .shift-badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-align: center; min-width: 60px; }
    .shift-1 { background-color: #dbeafe; color: #1e40af; }
    .shift-2 { background-color: #e5e7eb; color: #1f2937; }
    .efficiency-badge { padding: 2px 6px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; text-align: center; min-width: 45px; }
    .submit-btn { background: linear-gradient(135deg, #09af00 0%, #27af02 100%); color: white; transition: all 0.3s; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); background: linear-gradient(135deg, #078a00 0%, #1e8a02 100%); }
    .submit-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; box-shadow: none; }
    .submitted-badge { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; animation: pulse 2s infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.8; } }
    .last-submission-info { font-size: 0.75rem; color: #6b7280; }
    /* Scrollbar vertikal */
    .scroll-container::-webkit-scrollbar { width: 8px; }
    .scroll-container::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .scroll-container::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
    .scroll-container::-webkit-scrollbar-thumb:hover { background: #555; }
    /* Scrollbar horizontal untuk tabel */
    .table-scroll { overflow-x: auto; }
    .table-scroll::-webkit-scrollbar { height: 8px; }
    .table-scroll::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
    .table-scroll::-webkit-scrollbar-thumb:hover { background: #555; }
    .drag-handle { cursor: move; cursor: grab; user-select: none; color: #6b7280; transition: color 0.2s; padding: 2px; border-radius: 4px; }
    .drag-handle:hover { background-color: #f3f4f6; color: #374151; }
    .drag-handle:active { cursor: grabbing; }
    .dragging { opacity: 0.5; background-color: #f3f4f6 !important; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); }
    .drag-over { border-top: 2px solid #3b82f6 !important; }
    .sortable-ghost { opacity: 0.4; background-color: #dbeafe; }
    .sort-btn { padding: 1px 3px; border-radius: 3px; background-color: #f3f4f6; color: #4b5563; transition: all 0.2s; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; width: 18px; height: 18px; }
    .sort-btn:hover:not(:disabled) { background-color: #e5e7eb; }
    .sort-btn:disabled { opacity: 0.4; cursor: not-allowed; }
    .sort-btn svg { width: 10px; height: 10px; }
    tr.inactive-row { background-color: #f9fafb !important; }
    tr.inactive-row td { color: #6b7280 !important; }
    tr.inactive-row .efficiency-badge { background-color: #e5e7eb !important; color: #6b7280 !important; }
    .status-checkbox { width: 14px; height: 14px; border-radius: 3px; border: 2px solid #d1d5db; cursor: pointer; transition: all 0.2s; }
    .status-checkbox:checked { background-color: #10b981; border-color: #10b981; }
    .status-checkbox:disabled { opacity: 0.5; cursor: not-allowed; }
    .save-table-btn { transition: all 0.3s; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); }
    .save-table-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); }
    .actual-time { font-size: 0.7rem; color: #059669; font-weight: 500; }
    /* Compact table styles */
    .compact-table { font-size: 0.75rem; }
    .compact-table th { padding: 6px 4px !important; font-size: 0.65rem !important; white-space: nowrap; }
    .compact-table td { padding: 4px !important; }
    .compact-table .text-sm { font-size: 0.7rem !important; }
    .compact-table .sequence-number { font-size: 0.7rem; padding: 2px 6px; min-width: 24px; }
</style>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Preview Andon</h1>
            <p class="text-gray-600 mt-1">Monitoring produksi dengan shift calculation</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button onclick="refreshData()" class="bg-blue-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Refresh</span>
            </button>
            <button onclick="syncData()" id="syncButton" class="bg-green-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-green-700 transition flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Sync Data</span>
            </button>
            <a href="{{ route('andon.mesin') }}" class="bg-purple-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-purple-700 transition flex items-center space-x-2" target="_blank">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                <span>Lihat Andon Mesin</span>
            </a>
            <div class="bg-gray-100 border border-gray-200 rounded-lg px-4 py-2.5">
                <div class="flex items-center space-x-3">
                    <div class="text-center">
                        <div class="text-xs text-gray-500">Total Mesin</div>
                        <div class="text-lg font-bold text-gray-900">{{ $mesins->count() }}</div>
                    </div>
                    <div class="h-8 w-px bg-gray-300"></div>
                    <div class="text-center">
                        <div class="text-xs text-gray-500">Total Data</div>
                        <div class="text-lg font-bold text-gray-900">@php $totalData = 0; foreach($dataByMesin as $data) { $totalData += count($data); } echo $totalData; @endphp</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Loading Overlay -->
    <div id="globalLoading" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 flex flex-col items-center space-y-4">
            <svg class="animate-spin h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-700 font-medium" id="loadingText">Memproses data...</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div id="andonContainer" class="scroll-container p-6 max-h-[calc(100vh-300px)] overflow-y-auto">
            @if($mesins->count() > 0)
                @foreach($mesins as $index => $mesin)
                    @php
                        $mesinData = $dataByMesin[$mesin->nama] ?? [];
                        $hasData = count($mesinData) > 0;
                        $currentShift = $hasData ? $mesinData->first()->shift : '1';
                        $activeCount = $hasData ? $mesinData->where('is_active', true)->count() : 0;
                        $inactiveCount = $hasData ? $mesinData->where('is_active', false)->count() : 0;
                    @endphp
                    
                    <div class="mb-8 last:mb-0 bg-gray-50 rounded-lg border border-gray-200 overflow-hidden mesin-container" 
                         data-mesin="{{ $mesin->nama }}" 
                         data-mesin-id="{{ $mesin->id }}" 
                         data-mesin-nama="{{ $mesin->nama }}"
                         data-original-shift="{{ $currentShift }}"
                         id="mesin-container-{{ $mesin->id }}">
                        <!-- Mesin Header -->
                        <div class="bg-white border-b border-gray-200 p-4">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-blue-100 p-2 rounded-lg">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-3">
                                            <h2 class="text-xl font-bold text-gray-800">{{ $mesin->nama }}</h2>
                                            <div id="submit-badge-{{ $mesin->id }}"></div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-4 mt-1">
                                            @if($mesin->struk)<span class="text-sm text-gray-600">Struk: {{ $mesin->struk }}</span>@endif
                                            @if($mesin->tonase)<span class="text-sm text-gray-600">Tonase: {{ $mesin->tonase }}</span>@endif
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm text-gray-600">Total: <span class="font-bold">{{ count($mesinData) }}</span></span>
                                                <div class="h-3 w-px bg-gray-300"></div>
                                                <span class="text-sm text-green-600">Aktif: <span class="font-bold" id="active-count-{{ $mesin->id }}">{{ $activeCount }}</span></span>
                                                <div class="h-3 w-px bg-gray-300"></div>
                                                <span class="text-sm text-gray-500">Nonaktif: <span class="font-bold" id="inactive-count-{{ $mesin->id }}">{{ $inactiveCount }}</span></span>
                                            </div>
                                        </div>
                                        <div id="last-submission-info-{{ $mesin->id }}" class="last-submission-info mt-2"></div>
                                    </div>
                                </div>
                                <div class="flex flex-col md:flex-row items-start md:items-center gap-3">
                                    @if($hasData)
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-700">Shift:</span>
                                        <label class="toggle-switch">
                                            <input type="checkbox" 
                                                   id="shift-toggle-{{ $mesin->id }}"
                                                   {{ $currentShift == '2' ? 'checked' : '' }} 
                                                   onchange="updateShiftLocal('{{ $mesin->nama }}', this)" 
                                                   data-shift="{{ $currentShift }}" 
                                                   data-mesin="{{ $mesin->nama }}"
                                                   data-mesin-id="{{ $mesin->id }}">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span id="shift-badge-{{ $mesin->id }}" class="shift-badge shift-{{ $currentShift }}">{{ $currentShift == '1' ? '07:00' : '19:00' }}</span>
                                    </div>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-2">
                                        <div id="table-loading-{{ $mesin->id }}" class="hidden"><svg class="animate-spin h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
                                        <!-- PERBAIKAN: Parameter hanya mesin.id -->
                                        <button onclick="saveTableChanges({{ $mesin->id }})" 
                                                id="save-btn-{{ $mesin->id }}" 
                                                class="hidden bg-green-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-700 transition flex items-center space-x-2 save-table-btn">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Simpan Perubahan</span>
                                        </button>
                                        <div id="save-success-{{ $mesin->id }}" class="hidden text-green-600 text-sm font-medium">✓ Disimpan</div>
                                        <button onclick="submitToAndonMesin('{{ $mesin->nama }}', {{ $mesin->id }})" 
                                                id="submit-btn-{{ $mesin->id }}" 
                                                class="submit-btn px-5 py-2.5 rounded-lg font-medium transition flex items-center space-x-2">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Submit ke Andon</span>
                                        </button>
                                        <div id="submit-loading-{{ $mesin->id }}" class="hidden">
                                            <svg class="animate-spin h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                        <div id="submit-success-{{ $mesin->id }}" class="hidden text-green-600 text-sm font-medium">✓ Submitted</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Tabel Data -->
                        <div class="p-3">
                            @if(!$hasData)
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="mt-4 text-gray-600 font-medium">Tidak ada data untuk mesin ini</p>
                                    <p class="text-gray-500 text-sm">Data akan muncul setelah sync dari planning</p>
                                </div>
                            @else
                                <div class="table-scroll rounded-lg border border-gray-200 sortable-container" 
                                     id="table-{{ $mesin->id }}" 
                                     data-mesin="{{ $mesin->nama }}" 
                                     data-shift="{{ $currentShift }}"
                                     data-mesin-id="{{ $mesin->id }}">
                                    <table class="min-w-full divide-y divide-gray-200 compact-table">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 uppercase" style="width: 40px;">St</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase" style="width: 90px;">Urutan</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Part No</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">GSPH</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Start Plan</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-green-600 uppercase">Start Act</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Finish Plan</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-green-600 uppercase">Finish Act</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actual</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Eff</th>
                                                <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sortable-{{ $mesin->id }}" class="sortable-tbody" data-mesin="{{ $mesin->nama }}">
                                            @foreach($mesinData as $key => $row)
                                                <tr class="{{ !$row->is_active ? 'inactive-row' : 'hover:bg-gray-50' }} transition sortable-row border-b border-gray-200" 
                                                    data-id="{{ $row->id }}" 
                                                    data-is-active="{{ $row->is_active }}" 
                                                    draggable="true" 
                                                    id="row-{{ $row->id }}">
                                                    <td class="px-2 py-2 whitespace-nowrap text-center">
                                                        <input type="checkbox" 
                                                               {{ $row->is_active ? 'checked' : '' }} 
                                                               onchange="toggleActiveLocal({{ $row->id }}, this, {{ $mesin->id }}, '{{ $mesin->nama }}')" 
                                                               class="status-checkbox" 
                                                               data-row-id="{{ $row->id }}">
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap">
                                                        <div class="flex items-center space-x-1">
                                                            <div class="drag-handle cursor-move" data-mesin="{{ $mesin->nama }}">
                                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                                </svg>
                                                            </div>
                                                            <span class="text-xs font-medium text-gray-900 sequence-number bg-gray-100 px-1.5 py-0.5 rounded min-w-[24px] text-center">
                                                                {{ $row->sort_order ?: ($key + 1) }}
                                                            </span>
                                                            <div class="flex flex-col">
                                                                <button onclick="moveItemUpLocal({{ $row->id }}, '{{ $mesin->nama }}', '{{ $currentShift }}', {{ $mesin->id }})" 
                                                                        class="sort-btn up-btn" 
                                                                        {{ $loop->first ? 'disabled' : '' }}>
                                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                                    </svg>
                                                                </button>
                                                                <button onclick="moveItemDownLocal({{ $row->id }}, '{{ $mesin->nama }}', '{{ $currentShift }}', {{ $mesin->id }})" 
                                                                        class="sort-btn down-btn" 
                                                                        {{ $loop->last ? 'disabled' : '' }}>
                                                                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap">
                                                        <div class="text-xs font-semibold {{ !$row->is_active ? 'text-gray-500' : 'text-gray-900' }}">{{ $row->part_no }}</div>
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap text-xs {{ !$row->is_active ? 'text-gray-400' : 'text-gray-600' }}">
                                                        {{ number_format($row->gsph, 1) }}
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap text-xs {{ !$row->is_active ? 'text-gray-400' : 'text-gray-600' }}">
                                                        @if($row->calculated_start)
                                                            {{ \Carbon\Carbon::parse($row->calculated_start)->format('d/m H:i') }}
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap">
                                                        @if($row->start_actual)
                                                            <span class="actual-time">{{ \Carbon\Carbon::parse($row->start_actual)->format('d/m H:i') }}</span>
                                                        @else
                                                            <span class="text-xs text-gray-400 italic">Belum diisi</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap text-xs {{ !$row->is_active ? 'text-gray-400' : 'text-gray-600' }}">
                                                        @if($row->calculated_finish)
                                                            {{ \Carbon\Carbon::parse($row->calculated_finish)->format('d/m H:i') }}
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap">
                                                        @if($row->finish_actual)
                                                            <span class="actual-time">{{ \Carbon\Carbon::parse($row->finish_actual)->format('d/m H:i') }}</span>
                                                        @else
                                                            <span class="text-xs text-gray-400 italic">Belum diisi</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap">
                                                        <div class="text-xs font-semibold {{ !$row->is_active ? 'text-gray-500' : 'text-gray-900' }}">
                                                            {{ number_format($row->plan_qty) }}
                                                        </div>
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap">
                                                        @if($row->actual_qty > 0)
                                                            <div class="text-xs font-bold {{ !$row->is_active ? 'text-gray-400' : 'text-green-600' }}">
                                                                {{ number_format($row->actual_qty) }}
                                                            </div>
                                                        @else
                                                            <div class="text-xs text-gray-400 italic">Belum diisi</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap">
                                                        @php
                                                            $effClass = 'bg-gray-100 text-gray-800';
                                                            if ($row->efficiency > 0) {
                                                                if ($row->efficiency >= 90) $effClass = !$row->is_active ? 'bg-gray-100 text-gray-500' : 'bg-green-100 text-green-800';
                                                                elseif ($row->efficiency >= 70) $effClass = !$row->is_active ? 'bg-gray-100 text-gray-500' : 'bg-yellow-100 text-yellow-800';
                                                                else $effClass = !$row->is_active ? 'bg-gray-100 text-gray-500' : 'bg-red-100 text-red-800';
                                                            }
                                                        @endphp
                                                        <span class="efficiency-badge {{ $effClass }}">
                                                            {{ $row->efficiency > 0 ? number_format($row->efficiency, 0).'%' : '0%' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-2 py-2 whitespace-nowrap">
                                                        <button onclick="openUpdateModal({{ $row->id }})" 
                                                                class="bg-black text-white px-2 py-1 rounded text-xs font-medium hover:bg-gray-800 transition {{ !$row->is_active ? 'opacity-50 cursor-not-allowed' : '' }}" 
                                                                {{ !$row->is_active ? 'disabled' : '' }}>
                                                            Edit
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2 flex flex-col md:flex-row justify-between items-start md:items-center text-xs text-gray-600 gap-2">
                                    <div>
                                        <span class="font-medium">Catatan:</span> 
                                        <span class="text-gray-500">Klik "Simpan" lalu "Submit" untuk simpan ke Andon Mesin</span>
                                    </div>
                                    <div class="flex items-center space-x-3">
                                        <!-- PERBAIKAN: Tambahkan fungsi restoreOriginalShift -->
                                        <button onclick="restoreOriginalShift({{ $mesin->id }})" 
                                                class="text-blue-600 hover:text-blue-800 font-medium flex items-center space-x-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                            <span>Reset Shift</span>
                                        </button>
                                        <button onclick="markAllActiveLocal('{{ $mesin->nama }}', '{{ $currentShift }}', {{ $mesin->id }})" 
                                                class="text-green-600 hover:text-green-800 font-medium flex items-center space-x-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Aktifkan Semua</span>
                                        </button>
                                        <button onclick="markAllInactiveLocal('{{ $mesin->nama }}', '{{ $currentShift }}', {{ $mesin->id }})" 
                                                class="text-gray-600 hover:text-gray-800 font-medium flex items-center space-x-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span>Nonaktifkan Semua</span>
                                        </button>
                                        <!-- PERBAIKAN: Parameter hanya mesin.id -->
                                        <button onclick="discardChanges({{ $mesin->id }})" 
                                                id="discard-btn-{{ $mesin->id }}" 
                                                class="hidden text-red-600 hover:text-red-800 font-medium flex items-center space-x-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <span>Batal</span>
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-16">
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="mt-4 text-gray-600 font-semibold">Tidak ada mesin yang terdaftar</p>
                    <p class="text-gray-500 text-sm">Tambahkan mesin terlebih dahulu di halaman master data</p>
                    <a href="{{ route('mesin.index') }}" class="mt-4 inline-block bg-black text-white px-6 py-2.5 rounded-lg font-medium hover:bg-gray-800 transition">
                        Ke Halaman Mesin
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@include('andon.modal-update')
@endsection

@push('scripts')
<script src="{{ asset('js/andon-functions.js') }}"></script>
<script src="{{ asset('js/andon-drag-drop.js') }}"></script>
<script>
    // JavaScript untuk handle perubahan shift lokal
    document.addEventListener('DOMContentLoaded', function() {
        // Restore shift dari localStorage saat page load
        restoreShiftFromStorage();
        
        // Tambahkan event listener untuk semua shift toggles
        document.querySelectorAll('input[type="checkbox"][onchange*="updateShiftLocal"]').forEach(toggle => {
            const mesinContainer = toggle.closest('.mesin-container');
            const mesinId = mesinContainer.dataset.mesinId;
            const savedShift = localStorage.getItem(`shift-${mesinId}`);
            
            if (savedShift) {
                const shouldBeChecked = savedShift === '2';
                if (toggle.checked !== shouldBeChecked) {
                    toggle.checked = shouldBeChecked;
                    // Trigger UI update
                    const mesinNama = toggle.dataset.mesin;
                    updateShiftUILocal(toggle, savedShift, mesinNama);
                }
            }
        });
    });
    
    // Fungsi untuk restore original shift
    function restoreOriginalShift(mesinId) {
        const mesinContainer = document.querySelector(`#mesin-container-${mesinId}`);
        if (!mesinContainer) return;
        
        const originalShift = mesinContainer.dataset.originalShift || '1';
        const toggle = mesinContainer.querySelector(`#shift-toggle-${mesinId}`);
        const shouldBeChecked = originalShift === '2';
        
        if (toggle) {
            toggle.checked = shouldBeChecked;
            toggle.dataset.shift = originalShift;
            
            // Update UI
            const mesinNama = mesinContainer.dataset.mesinNama;
            updateShiftUILocal(toggle, originalShift, mesinNama);
            
            // Tampilkan save button
            showSaveButtonById(mesinId);
            
            // Hapus dari localStorage
            localStorage.removeItem(`shift-${mesinId}`);
            
            Swal.fire({
                icon: 'info',
                title: 'Shift Direset',
                text: `Shift kembali ke semula: ${originalShift == '1' ? '07:00' : '19:00'}`,
                timer: 1500,
                showConfirmButton: false
            });
        }
    }
    
    // Export fungsi ke global scope
    window.restoreOriginalShift = restoreOriginalShift;
</script>
@endpush