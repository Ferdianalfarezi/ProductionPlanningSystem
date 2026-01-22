@extends('layouts.app')

@section('title', 'Preview Andon')

@section('content')
<style>
    /* Toggle Switch */
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 70px;
        height: 32px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #3b82f6;
        transition: .4s;
        border-radius: 34px;
    }
    
    .toggle-slider:before {
        position: absolute;
        content: "1";
        height: 24px;
        width: 24px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: bold;
        color: #3b82f6;
    }
    
    input:checked + .toggle-slider {
        background-color: #8b5cf6;
    }
    
    input:checked + .toggle-slider:before {
        content: "2";
        transform: translateX(38px);
        color: #8b5cf6;
    }
    
    .shift-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        min-width: 60px;
    }
    
    .shift-1 {
        background-color: #dbeafe;
        color: #1e40af;
    }
    
    .shift-2 {
        background-color: #f3e8ff;
        color: #6d28d9;
    }
    
    .efficiency-badge {
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-align: center;
        min-width: 60px;
    }
    
    /* Custom scrollbar */
    .scroll-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .scroll-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    
    .scroll-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Drag & Drop Styles */
    .drag-handle {
        cursor: move;
        cursor: grab;
        user-select: none;
        color: #6b7280;
        transition: color 0.2s;
        padding: 4px;
        border-radius: 4px;
    }
    
    .drag-handle:hover {
        background-color: #f3f4f6;
        color: #374151;
    }
    
    .drag-handle:active {
        cursor: grabbing;
    }
    
    .dragging {
        opacity: 0.5;
        background-color: #f3f4f6 !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    .drag-over {
        border-top: 2px solid #3b82f6 !important;
    }
    
    .sortable-ghost {
        opacity: 0.4;
        background-color: #dbeafe;
    }
    
    /* Up/Down buttons */
    .sort-btn {
        padding: 2px 6px;
        border-radius: 4px;
        background-color: #f3f4f6;
        color: #4b5563;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
    }
    
    .sort-btn:hover:not(:disabled) {
        background-color: #e5e7eb;
    }
    
    .sort-btn:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    
    .sort-btn svg {
        width: 12px;
        height: 12px;
    }
    
    /* Global loading */
    .global-loading {
        animation: pulse 1.5s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>

<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Preview Andon</h1>
            <p class="text-gray-600 mt-1">Monitoring produksi dengan shift calculation</p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
            <button 
                onclick="refreshData()"
                class="bg-blue-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-blue-700 transition flex items-center space-x-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span>Refresh</span>
            </button>
            
            <button 
                onclick="syncData()"
                id="syncButton"
                class="bg-green-600 text-white px-4 py-2.5 rounded-lg font-medium hover:bg-green-700 transition flex items-center space-x-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                </svg>
                <span>Sync Data</span>
            </button>
            
            <div class="bg-gray-100 border border-gray-200 rounded-lg px-4 py-2.5">
                <div class="flex items-center space-x-3">
                    <div class="text-center">
                        <div class="text-xs text-gray-500">Total Mesin</div>
                        <div class="text-lg font-bold text-gray-900">{{ $mesins->count() }}</div>
                    </div>
                    <div class="h-8 w-px bg-gray-300"></div>
                    <div class="text-center">
                        <div class="text-xs text-gray-500">Total Data</div>
                        <div class="text-lg font-bold text-gray-900">
                            @php
                                $totalData = 0;
                                foreach($dataByMesin as $data) {
                                    $totalData += count($data);
                                }
                                echo $totalData;
                            @endphp
                        </div>
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
        <div id="andonContainer" class="scroll-container p-6 max-h-[calc(100vh-250px)] overflow-y-auto">
            @if($mesins->count() > 0)
                @foreach($mesins as $index => $mesin)
                    @php
                        $mesinData = $dataByMesin[$mesin->nama] ?? [];
                        $hasData = count($mesinData) > 0;
                        $currentShift = $hasData ? $mesinData->first()->shift : '1';
                    @endphp
                    
                    <div class="mb-8 last:mb-0 bg-gray-50 rounded-lg border border-gray-200 overflow-hidden mesin-container" data-mesin="{{ $mesin->nama }}">
                        <!-- Mesin Header with Toggle -->
                        <div class="bg-white border-b border-gray-200 p-4">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                <div class="flex items-center space-x-3">
                                    <div class="bg-blue-100 p-2 rounded-lg">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    
                                    <div>
                                        <h2 class="text-xl font-bold text-gray-800">{{ $mesin->nama }}</h2>
                                        <div class="flex items-center space-x-4 mt-1">
                                            @if($mesin->struk)
                                                <span class="text-sm text-gray-600">
                                                    Struk: {{ $mesin->struk }}
                                                </span>
                                            @endif
                                            @if($mesin->tonase)
                                                <span class="text-sm text-gray-600">
                                                    Tonase: {{ $mesin->tonase }}
                                                </span>
                                            @endif
                                            <span class="text-sm text-gray-600">
                                                Total Data: <span class="font-bold">{{ count($mesinData) }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Shift Toggle -->
                                <div class="flex items-center space-x-4">
                                    @if($hasData)
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-700">Shift:</span>
                                        <label class="toggle-switch">
                                            <input type="checkbox" {{ $currentShift == '2' ? 'checked' : '' }} 
                                                   onchange="updateShift('{{ $mesin->nama }}', this)"
                                                   data-shift="{{ $currentShift }}">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="shift-badge shift-{{ $currentShift }}">
                                            {{ $currentShift == '1' ? '07:00' : '19:00' }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Data -->
                        <div class="p-4">
                            @if(!$hasData)
                                <div class="text-center py-8">
                                    <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="mt-4 text-gray-600 font-medium">Tidak ada data untuk mesin ini</p>
                                    <p class="text-gray-500 text-sm">Data akan muncul setelah sync dari planning</p>
                                </div>
                            @else
                                <div class="overflow-x-auto rounded-lg border border-gray-200 sortable-container" 
                                     data-mesin="{{ $mesin->nama }}" 
                                     data-shift="{{ $currentShift }}">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 120px;">
                                                    <div class="flex items-center space-x-2">
                                                        <span class="drag-icon">Urutan</span>
                                                    </div>
                                                </th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part No</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GSPH</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Finish</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Efficiency</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="sortable-{{ $mesin->id }}" class="sortable-tbody">
                                            @foreach($mesinData as $key => $row)
                                                <tr 
                                                    class="hover:bg-gray-50 transition sortable-row border-b border-gray-200" 
                                                    data-id="{{ $row->id }}"
                                                    draggable="true"
                                                    id="row-{{ $row->id }}"
                                                >
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <div class="flex items-center space-x-2">
                                                            <!-- Drag Handle -->
                                                            <div class="drag-handle cursor-move" title="Drag untuk mengubah urutan">
                                                                <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                                                </svg>
                                                            </div>
                                                            
                                                            <!-- Sequence Number -->
                                                            <span class="text-sm font-medium text-gray-900 sequence-number bg-gray-100 px-2 py-1 rounded">
                                                                {{ $row->sort_order ?: ($key + 1) }}
                                                            </span>
                                                            
                                                            <!-- Up/Down Buttons (Fallback) -->
                                                            <div class="flex flex-col space-y-1">
                                                                <button 
                                                                    onclick="moveItemUp({{ $row->id }}, '{{ $mesin->nama }}', '{{ $currentShift }}')"
                                                                    class="sort-btn up-btn"
                                                                    {{ $loop->first ? 'disabled' : '' }}
                                                                    title="Pindah ke atas"
                                                                >
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                                                    </svg>
                                                                </button>
                                                                <button 
                                                                    onclick="moveItemDown({{ $row->id }}, '{{ $mesin->nama }}', '{{ $currentShift }}')"
                                                                    class="sort-btn down-btn"
                                                                    {{ $loop->last ? 'disabled' : '' }}
                                                                    title="Pindah ke bawah"
                                                                >
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <div class="text-sm font-semibold text-gray-900">{{ $row->part_no }}</div>
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                                        {{ number_format($row->gsph, 2) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                                        @if($row->calculated_start)
                                                            {{ \Carbon\Carbon::parse($row->calculated_start)->format('d/m/Y H:i') }}
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                                        @if($row->calculated_finish)
                                                            {{ \Carbon\Carbon::parse($row->calculated_finish)->format('d/m/Y H:i') }}
                                                        @else
                                                            <span class="text-gray-400">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">
                                                        {{ number_format($row->plan_qty) }}
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        @if($row->actual_qty > 0)
                                                            <div class="text-sm font-bold text-green-600">
                                                                {{ number_format($row->actual_qty) }}
                                                            </div>
                                                        @else
                                                            <div class="text-sm font-medium text-gray-400 italic">
                                                                Belum diisi
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        @if($row->efficiency > 0)
                                                            @php
                                                                $efficiencyClass = '';
                                                                if ($row->efficiency >= 90) {
                                                                    $efficiencyClass = 'bg-green-100 text-green-800';
                                                                } elseif ($row->efficiency >= 70) {
                                                                    $efficiencyClass = 'bg-yellow-100 text-yellow-800';
                                                                } else {
                                                                    $efficiencyClass = 'bg-red-100 text-red-800';
                                                                }
                                                            @endphp
                                                            <span class="efficiency-badge {{ $efficiencyClass }}">
                                                                {{ number_format($row->efficiency, 1) }}%
                                                            </span>
                                                        @else
                                                            <span class="efficiency-badge bg-gray-100 text-gray-800">
                                                                0%
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                        <button 
                                                            onclick="openUpdateModal({{ $row->id }})"
                                                            class="bg-black text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-800 transition flex items-center space-x-1"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                            <span>Update</span>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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

<!-- Include Modal Terpisah -->
@include('andon.modal-update')
@endsection

@push('scripts')
<script>
    // Utility functions
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }
    
    function showSuccess(message, callback) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: message,
            showConfirmButton: false,
            timer: 1500
        }).then(() => {
            if (callback && typeof callback === 'function') {
                callback();
            }
        });
    }
    
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
        });
    }
    
    function showGlobalLoading(message = 'Memproses data...') {
        const loadingEl = document.getElementById('globalLoading');
        const loadingText = document.getElementById('loadingText');
        if (loadingEl && loadingText) {
            loadingText.textContent = message;
            loadingEl.classList.remove('hidden');
        }
    }
    
    function hideGlobalLoading() {
        const loadingEl = document.getElementById('globalLoading');
        if (loadingEl) {
            loadingEl.classList.add('hidden');
        }
    }
</script>
<script src="{{ asset('js/andon-functions.js') }}"></script>
<script src="{{ asset('js/andon-drag-drop.js') }}"></script>
@endpush