{{-- resources/views/andon/andon-mesin.blade.php --}}
@extends('layouts.app')

@section('title', 'Andon Mesin')

@section('content')

<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Andon Mesin</h1>
            <p class="text-gray-600 mt-2">Data historis produksi yang telah disubmit dari Preview Andon</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <div class="text-right hidden lg:block">
                <div class="text-sm text-gray-500">Data Read Only</div>
                <div class="text-lg font-bold text-purple-600">Tidak bisa diedit</div>
            </div>
            <div class="h-10 w-px bg-gray-300 hidden lg:block"></div>
            <button onclick="exportToExcel()" 
                    class="export-btn px-6 py-3 rounded-xl font-bold flex items-center space-x-3 shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Export Excel</span>
            </button>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="stat-card">
            <div class="stat-icon icon-purple">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-gray-900" id="totalMesin">0</div>
            <div class="text-sm text-gray-600 mt-1">Total Mesin</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon icon-blue">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-gray-900" id="totalSubmission">0</div>
            <div class="text-sm text-gray-600 mt-1">Total Submission</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon icon-green">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-gray-900" id="totalActive">0</div>
            <div class="text-sm text-gray-600 mt-1">Data Aktif</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon icon-orange">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-gray-900" id="totalPlan">0</div>
            <div class="text-sm text-gray-600 mt-1">Total Plan Qty</div>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="bg-black rounded-2xl p-6 shadow-xl">
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div class="flex-1">
                <h2 class="text-xl font-bold text-white mb-6">Filter Data</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Filter Mesin -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                                <span>Mesin</span>
                            </div>
                        </label>
                        <div class="relative">
                            <select id="filterMesin" class="w-full filter-input rounded-xl px-4 py-3.5 text-gray-900 focus:outline-none">
                                <option value="">Semua Mesin</option>
                                @foreach($submittedMesin as $mesin)
                                    <option value="{{ $mesin->mesin_id }}">{{ $mesin->mesin_nama }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filter Tanggal -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Tanggal Produksi</span>
                            </div>
                        </label>
                        <div class="relative">
                            <input type="date" id="filterTanggal" 
                                   class="w-full filter-input rounded-xl px-4 py-3.5 text-gray-900 focus:outline-none"
                                   value="{{ $defaultDate }}">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filter Shift -->
                    <div>
                        <label class="block text-sm font-medium text-white mb-2">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Shift</span>
                            </div>
                        </label>
                        <div class="relative">
                            <select id="filterShift" class="w-full filter-input rounded-xl px-4 py-3.5 text-gray-900 focus:outline-none">
                                <option value="">Semua Shift</option>
                                <option value="1">Shift 1 (07:00)</option>
                                <option value="2">Shift 2 (19:00)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tombol Filter -->
            <div class="lg:w-auto">
                <button onclick="loadAndonMesinData()" 
                        class="bg-white text-dark px-8 py-4 rounded-xl font-bold hover:bg-gray-50 transition-all duration-300 flex items-center space-x-3 shadow-lg hover:shadow-xl w-full lg:w-auto">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <span>TERAPKAN FILTER</span>
                </button>
            </div>
        </div>
        
        <!-- Quick Date Filters -->
        <div class="mt-6 pt-6 border-t border-white border-opacity-20">
            <div class="flex flex-wrap gap-3">
                <span class="text-sm font-medium text-white flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    Filter Cepat:
                </span>
                <button onclick="setDateFilter('today')" 
                        class="quick-filter-btn bg-white bg-opacity-10 hover:bg-opacity-20 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all">
                    Hari Ini
                </button>
                <button onclick="setDateFilter('yesterday')" 
                        class="quick-filter-btn bg-white bg-opacity-10 hover:bg-opacity-20 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all">
                    Kemarin
                </button>
                <button onclick="setDateFilter('this_week')" 
                        class="quick-filter-btn bg-white bg-opacity-10 hover:bg-opacity-20 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all">
                    Minggu Ini
                </button>
                <button onclick="setDateFilter('last_week')" 
                        class="quick-filter-btn bg-white bg-opacity-10 hover:bg-opacity-20 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all">
                    Minggu Lalu
                </button>
                <button onclick="setDateFilter('this_month')" 
                        class="quick-filter-btn bg-white bg-opacity-10 hover:bg-opacity-20 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all">
                    Bulan Ini
                </button>
                <button onclick="setDateFilter('last_month')" 
                        class="quick-filter-btn bg-white bg-opacity-10 hover:bg-opacity-20 text-white px-4 py-2.5 rounded-lg text-sm font-medium transition-all">
                    Bulan Lalu
                </button>
            </div>
        </div>
    </div>
    
    <!-- Loading State -->
    <div id="loadingAndonMesin" class="hidden text-center py-16">
        <div class="inline-flex flex-col items-center space-y-6">
            <div class="relative">
                <svg class="animate-spin h-16 w-16 text-purple-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
            </div>
            <p class="text-gray-600 font-medium text-lg">Memuat data andon mesin...</p>
            <p class="text-gray-500 text-sm">Silakan tunggu sebentar</p>
        </div>
    </div>
    
    <!-- Results Container -->
    <div id="andonMesinResults" class="space-y-8">
        <!-- Data akan diisi via JavaScript -->
    </div>
    
    <!-- Empty State -->
    <div id="emptyState" class="hidden">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="relative w-24 h-24 mx-auto mb-6">
                    <svg class="w-full h-full text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">Data Tidak Ditemukan</h3>
                <p class="text-gray-600 mb-4">Tidak ada data andon mesin untuk filter yang dipilih.</p>
                <p class="text-gray-500 text-sm mb-8">Coba pilih tanggal lain atau mesin yang berbeda</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button onclick="resetFilters()" 
                            class="bg-purple-600 text-white px-6 py-3 rounded-xl font-medium hover:bg-purple-700 transition flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Reset Filter</span>
                    </button>
                    <button onclick="setDateFilter('today')" 
                            class="bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-50 transition flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Lihat Hari Ini</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentData = [];
let currentFilters = {};

// Utility functions
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}

function formatDateTime(dateTimeString) {
    const date = new Date(dateTimeString);
    return date.toLocaleString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getEfficiencyClass(efficiency) {
    if (!efficiency || efficiency === 0) return 'eff-none';
    if (efficiency >= 90) return 'eff-high';
    if (efficiency >= 70) return 'eff-medium';
    return 'eff-low';
}

// Load data saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    loadAndonMesinData();
    
    // Event listener untuk enter key pada input
    document.getElementById('filterTanggal').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') loadAndonMesinData();
    });
});

async function loadAndonMesinData() {
    const loadingEl = document.getElementById('loadingAndonMesin');
    const resultsEl = document.getElementById('andonMesinResults');
    const emptyEl = document.getElementById('emptyState');
    
    loadingEl.classList.remove('hidden');
    resultsEl.innerHTML = '';
    emptyEl.classList.add('hidden');
    
    const params = new URLSearchParams({
        mesin_id: document.getElementById('filterMesin').value,
        tanggal: document.getElementById('filterTanggal').value,
        shift: document.getElementById('filterShift').value
    });
    
    try {
        const response = await fetch(`/andon/mesin/data?${params}`);
        const result = await response.json();
        
        if (result.success) {
            currentData = result.data;
            currentFilters = result.filters;
            
            // Update statistics
            updateStatistics(result.statistics);
            
            // Render data
            if (currentData.length > 0) {
                renderAndonMesinData(currentData);
                emptyEl.classList.add('hidden');
            } else {
                emptyEl.classList.remove('hidden');
            }
        } else {
            throw new Error(result.message || 'Gagal memuat data');
        }
    } catch (error) {
        console.error('Error loading andon mesin data:', error);
        showError('Gagal memuat data: ' + error.message);
    } finally {
        loadingEl.classList.add('hidden');
    }
}

function updateStatistics(stats) {
    document.getElementById('totalSubmission').textContent = formatNumber(stats.total_submissions || 0);
    document.getElementById('totalActive').textContent = formatNumber(stats.active_rows || 0);
    document.getElementById('totalPlan').textContent = formatNumber(stats.total_rows || 0);
    
    // Hitung total mesin unik
    const uniqueMesins = new Set(currentData.map(item => item.mesin_id));
    document.getElementById('totalMesin').textContent = uniqueMesins.size;
}

function renderAndonMesinData(data) {
    const container = document.getElementById('andonMesinResults');
    container.innerHTML = '';
    
    // Group by mesin_nama jika menampilkan semua mesin
    if (!currentFilters.selected_mesin) {
        const groupedData = {};
        
        data.forEach(item => {
            const key = item.mesin_nama;
            if (!groupedData[key]) {
                groupedData[key] = [];
            }
            groupedData[key].push(item);
        });
        
        Object.entries(groupedData).forEach(([mesinName, items]) => {
            const card = createMesinCard(mesinName, items);
            container.appendChild(card);
        });
    } else {
        const mesinName = data[0]?.mesin_nama || 'Mesin';
        const card = createMesinCard(mesinName, data);
        container.appendChild(card);
    }
}

function createMesinCard(mesinName, items) {
    const card = document.createElement('div');
    card.className = 'data-card bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden';
    
    // Hitung statistik untuk mesin ini
    let totalRows = 0;
    let activeRows = 0;
    let totalPlan = 0;
    let totalActual = 0;
    let totalEfficiency = 0;
    let hasShifts = new Set();
    
    items.forEach(item => {
        const dataRows = item.data_json || [];
        totalRows += dataRows.length;
        hasShifts.add(item.shift);
        
        dataRows.forEach(row => {
            if (row.is_active) activeRows++;
            totalPlan += row.plan_qty || 0;
            totalActual += row.actual_qty || 0;
            totalEfficiency += row.efficiency || 0;
        });
    });
    
    const avgEfficiency = totalRows > 0 ? (totalEfficiency / totalRows).toFixed(1) : 0;
    const efficiencyClass = getEfficiencyClass(avgEfficiency);
    
    card.innerHTML = `
        <div class="mesin-header p-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <div class="bg-purple-100 p-3 rounded-xl">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">${mesinName}</h2>
                        <div class="flex flex-wrap items-center gap-3 mt-2">
                            <span class="text-sm text-gray-600">
                                <span class="font-semibold">${items.length}</span> submission
                            </span>
                            <span class="h-3 w-px bg-gray-300"></span>
                            <span class="text-sm text-gray-600">
                                Shift: <span class="font-semibold">${Array.from(hasShifts).sort().join(', ')}</span>
                            </span>
                            <span class="h-3 w-px bg-gray-300"></span>
                            <span class="text-sm text-green-600">
                                Aktif: <span class="font-semibold">${activeRows}</span>/${totalRows}
                            </span>
                            <span class="h-3 w-px bg-gray-300"></span>
                            <span class="efficiency-badge ${efficiencyClass}">
                                Avg: ${avgEfficiency}%
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="text-sm text-gray-500">Submitted</div>
                    <div class="text-lg font-bold text-gray-900">
                        ${items[0]?.submitted_date || ''}
                    </div>
                    <div class="text-sm text-gray-600">
                        ${items[0]?.submitted_time || ''}
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            ${items.map(item => createSubmissionTable(item)).join('')}
        </div>
    `;
    
    return card;
}

function createSubmissionTable(submission) {
    const shiftText = submission.shift === '1' ? 'Shift 1 (07:00)' : 'Shift 2 (19:00)';
    const submittedAt = formatDateTime(submission.submitted_at);
    
    return `
        <div class="mb-8 last:mb-0">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <span class="shift-badge shift-${submission.shift}">
                        ${shiftText}
                    </span>
                    <span class="text-sm text-gray-600">
                        Tanggal: <span class="font-semibold">${formatDate(submission.tanggal)}</span>
                    </span>
                </div>
                <div class="text-sm text-gray-500">
                    Submitted: ${submittedAt}
                </div>
            </div>
            
            <div class="scrollable-table border border-gray-200 rounded-xl overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GSPH</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actual Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Efficiency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Finish</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        ${(submission.data_json || []).map((row, index) => `
                            <tr class="data-row hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    ${row.sort_order || (index + 1)}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge ${row.is_active ? 'status-active' : 'status-inactive'}">
                                        ${row.is_active ? 'Aktif' : 'Nonaktif'}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold ${row.is_active ? 'text-gray-900' : 'text-gray-500'}">
                                    ${row.part_no || '-'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm ${row.is_active ? 'text-gray-600' : 'text-gray-400'}">
                                    ${row.gsph ? parseFloat(row.gsph).toFixed(2) : '0.00'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold ${row.is_active ? 'text-gray-900' : 'text-gray-500'}">
                                    ${formatNumber(row.plan_qty || 0)}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    ${row.actual_qty > 0 ? 
                                        `<div class="text-sm font-bold ${row.is_active ? 'text-green-600' : 'text-gray-400'}">
                                            ${formatNumber(row.actual_qty)}
                                        </div>` :
                                        `<div class="text-sm font-medium text-gray-400 italic">
                                            Belum diisi
                                        </div>`
                                    }
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="efficiency-badge ${getEfficiencyClass(row.efficiency)}">
                                        ${(row.efficiency || 0).toFixed(1)}%
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm ${row.is_active ? 'text-gray-600' : 'text-gray-400'}">
                                    ${row.start_time || '-'}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm ${row.is_active ? 'text-gray-600' : 'text-gray-400'}">
                                    ${row.finish_time || '-'}
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 flex justify-between items-center text-sm text-gray-500">
                <div>
                    Total: <span class="font-semibold">${submission.data_json?.length || 0}</span> data
                </div>
                <div>
                    Mesin: <span class="font-semibold">${submission.mesin_info?.struk || '-'}</span> | 
                    Tonase: <span class="font-semibold">${submission.mesin_info?.tonase || '-'}</span>
                </div>
            </div>
        </div>
    `;
}

// Quick filter functions
function setDateFilter(type) {
    const dateInput = document.getElementById('filterTanggal');
    const today = new Date();
    
    switch(type) {
        case 'today':
            dateInput.value = today.toISOString().split('T')[0];
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            dateInput.value = yesterday.toISOString().split('T')[0];
            break;
        case 'this_week':
            const startOfWeek = new Date(today);
            startOfWeek.setDate(today.getDate() - today.getDay());
            dateInput.value = startOfWeek.toISOString().split('T')[0];
            break;
        case 'last_week':
            const lastWeek = new Date(today);
            lastWeek.setDate(today.getDate() - 7 - today.getDay());
            dateInput.value = lastWeek.toISOString().split('T')[0];
            break;
        case 'this_month':
            dateInput.value = today.getFullYear() + '-' + 
                            String(today.getMonth() + 1).padStart(2, '0') + '-01';
            break;
        case 'last_month':
            const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            dateInput.value = lastMonth.getFullYear() + '-' + 
                            String(lastMonth.getMonth() + 1).padStart(2, '0') + '-01';
            break;
    }
    
    loadAndonMesinData();
}

function resetFilters() {
    document.getElementById('filterMesin').value = '';
    document.getElementById('filterTanggal').value = '{{ $defaultDate }}';
    document.getElementById('filterShift').value = '';
    loadAndonMesinData();
}

function exportToExcel() {
    if (currentData.length === 0) {
        showError('Tidak ada data untuk diexport');
        return;
    }
    
    // TODO: Implement Excel export
    showSuccess('Export feature coming soon!');
}

// Global error/success functions
function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message,
        confirmButtonColor: '#667eea'
    });
}

function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        confirmButtonColor: '#667eea',
        timer: 1500
    });
}
</script>
@endsection