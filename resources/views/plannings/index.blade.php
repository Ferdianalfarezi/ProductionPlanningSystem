@extends('layouts.app')

@section('title', 'Planning Production')

@section('content')

<style>
/* Modal Animations */
.modal-fade-in {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Planning Production</h1>
                <p class="text-gray-600 mt-1">Kelola data planning produksi per shift</p>
            </div>
            <div class="flex items-center space-x-3">
                <button 
                    onclick="clearAllData()"
                    class="bg-red-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-red-700 transition transform hover:scale-105 flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    <span>Clear All</span>
                </button>
                <button 
                    onclick="openImportModal()"
                    class="bg-green-600 text-white px-5 py-3 rounded-lg font-semibold hover:bg-green-700 transition transform hover:scale-105 flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span>Import Excel</span>
                </button>
                <button 
                    onclick="openCreateModal()"
                    class="bg-black text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition transform hover:scale-105 flex items-center space-x-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span>Tambah Planning</span>
                </button>
            </div>
        </div>

        <!-- Statistics Badges -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Total Planning</p>
                        <p class="text-2xl font-bold text-purple-900 mt-1" id="statTotal">{{ $totalPlannings }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Open</p>
                        <p class="text-2xl font-bold text-blue-900 mt-1" id="statOpen">0</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">Running</p>
                        <p class="text-2xl font-bold text-yellow-900 mt-1" id="statRunning">0</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-wider">Complete</p>
                        <p class="text-2xl font-bold text-green-900 mt-1" id="statComplete">0</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-3 md:space-y-0">
            <div class="w-full md:w-1/3 relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    id="searchInput"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition"
                    placeholder="Cari part no, line, store, lot..."
                >
            </div>
            <div class="flex-shrink-0">
                <select id="statusFilter" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition">
                    <option value="">Semua Status</option>
                    <option value="Open">Open</option>
                    <option value="R">Running (R)</option>
                    <option value="J">Job (J)</option>
                    <option value="C">Complete (C)</option>
                </select>
            </div>
            <div class="flex-shrink-0">
                <select id="lineFilter" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition">
                    <option value="">Semua Line</option>
                    @foreach($lineList as $line)
                        <option value="{{ $line }}">{{ $line }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-shrink-0">
                <select id="perPageSelect" class="px-5 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition">
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="all">All</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden relative">
        <div id="loadingOverlay" class="hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
            <div class="flex items-center space-x-3">
                <svg class="animate-spin h-8 w-8 text-black" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-600 font-medium">Loading...</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
    <tr>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Priority</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Part No FG</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Store</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Line</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Rack</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Qty KBN</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Prod</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Total Produksi</th> <!-- Kolom Baru -->
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actual</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Lot No</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Calc By</th>
        <th class="px-3 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Start Time</th>
        <th class="px-3 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
    </tr>
</thead>
                <tbody class="divide-y divide-gray-200" id="planningsTableBody">
                    <tr>
                        <td colspan="14" class="px-6 py-16 text-center">
                            <svg class="animate-spin h-8 w-8 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="mt-4 text-gray-600">Loading data...</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 border-t border-gray-200 px-6 py-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="text-sm text-gray-600">
                    Showing <span id="showingFrom" class="font-medium">0</span> to 
                    <span id="showingTo" class="font-medium">0</span> of 
                    <span id="totalEntries" class="font-medium">0</span> entries
                </div>
                <div id="paginationContainer" class="flex items-center space-x-2"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modals -->
@include('plannings.create')
@include('plannings.edit')
@include('plannings.import')
@include('plannings.detail')

@endsection

@push('scripts')
<script>
    let currentPage = 1;
    let perPage = 20;
    let searchQuery = '';
    let statusFilter = '';
    let lineFilter = '';
    let totalPages = 1;
    let isLoading = false;
    let searchTimeout = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadPlannings();
        bindEvents();
        initializeImportHandlers();
    });

    function bindEvents() {
        // Search input
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    searchQuery = this.value;
                    currentPage = 1;
                    loadPlannings();
                }, 300);
            });
        }

        // Per page selector
        const perPageSelect = document.getElementById('perPageSelect');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                perPage = this.value === 'all' ? 'all' : parseInt(this.value);
                currentPage = 1;
                loadPlannings();
            });
        }

        // Status filter
        const statusFilterEl = document.getElementById('statusFilter');
        if (statusFilterEl) {
            statusFilterEl.addEventListener('change', function() {
                statusFilter = this.value;
                currentPage = 1;
                loadPlannings();
            });
        }

        // Line filter
        const lineFilterEl = document.getElementById('lineFilter');
        if (lineFilterEl) {
            lineFilterEl.addEventListener('change', function() {
                lineFilter = this.value;
                currentPage = 1;
                loadPlannings();
            });
        }

        // Create form
        const createForm = document.getElementById('createForm');
        if (createForm) {
            createForm.addEventListener('submit', handleCreateSubmit);
        }

        // Edit form
        const editForm = document.getElementById('editForm');
        if (editForm) {
            editForm.addEventListener('submit', handleEditSubmit);
        }
    }

    function initializeImportHandlers() {
        // Import file change handler
        const importFileInput = document.getElementById('importFile');
        if (importFileInput) {
            importFileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const fileInfo = document.getElementById('fileInfo');
                
                if (file) {
                    const fileNameEl = document.getElementById('fileName');
                    const fileSizeEl = document.getElementById('fileSize');
                    
                    if (fileNameEl) fileNameEl.textContent = file.name;
                    if (fileSizeEl) fileSizeEl.textContent = formatFileSize(file.size);
                    if (fileInfo) fileInfo.classList.remove('hidden');
                } else {
                    if (fileInfo) fileInfo.classList.add('hidden');
                }
            });
        }

        // Import form submit handler
        const importForm = document.getElementById('importForm');
        if (importForm) {
            importForm.addEventListener('submit', handleImportSubmit);
        }
    }

    async function loadPlannings() {
        if (isLoading) return;
        isLoading = true;
        showLoading(true);

        try {
            const params = new URLSearchParams({
                page: currentPage,
                per_page: perPage,
                search: searchQuery,
                status: statusFilter,
                line: lineFilter,
            });

            const response = await fetch(`/plannings/data?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                renderTable(result.data);
                renderPagination(result.pagination);
                updateShowingInfo(result.pagination);
                updateStats(result.stats);
                const statTotal = document.getElementById('statTotal');
                if (statTotal) statTotal.textContent = result.stats.total_plannings;
            } else {
                showEmptyState('Failed to load data');
            }
        } catch (error) {
            console.error('Error loading plannings:', error);
            showEmptyState('Error loading data. Please try again.');
        } finally {
            isLoading = false;
            showLoading(false);
        }
    }

    function updateStats(stats) {
        const statOpen = document.getElementById('statOpen');
        const statRunning = document.getElementById('statRunning');
        const statComplete = document.getElementById('statComplete');
        
        if (statOpen) statOpen.textContent = stats.open || 0;
        if (statRunning) statRunning.textContent = stats.running || 0;
        if (statComplete) statComplete.textContent = stats.complete || 0;
    }

    function renderTable(items) {
    const tbody = document.getElementById('planningsTableBody');
    
    if (!items || items.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="15" class="px-6 py-16 text-center"> <!-- Ganti dari 14 ke 15 -->
                    <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="mt-4 text-gray-600 font-semibold">Data planning tidak ditemukan</p>
                    <p class="text-gray-500 text-sm">${searchQuery ? 'Coba kata kunci lain' : 'Klik "Tambah Planning" atau "Import Excel" untuk menambahkan data'}</p>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = items.map(item => `
        <tr class="hover:bg-gray-50 transition">
            <td class="px-3 py-4 text-sm font-medium text-gray-900">${item.row_number}</td>
            <td class="px-3 py-4">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full ${getPriorityClass(item.priority)} font-bold text-sm">
                    ${item.priority ?? '-'}
                </span>
            </td>
            <td class="px-3 py-4">
                <p class="text-sm font-semibold text-gray-900">${escapeHtml(item.part_no_fg) || '-'}</p>
                <p class="text-xs text-gray-500">${escapeHtml(item.part_no_child) || ''}</p>
            </td>
            <td class="px-3 py-4 text-sm text-gray-600">${escapeHtml(item.store) || '-'}</td>
            <td class="px-3 py-4 text-sm text-gray-600 font-medium">${escapeHtml(item.line) || '-'}</td>
            <td class="px-3 py-4 text-sm text-gray-600">${escapeHtml(item.rack_no) || '-'}</td>
            <td class="px-3 py-4 text-sm text-gray-600 font-mono">${item.qty_kbn ?? '-'}</td>
            <td class="px-3 py-4 text-sm text-gray-600 font-mono">${item.total_prod ?? '-'}</td>
            <td class="px-3 py-4 text-sm font-semibold text-gray-900 bg-blue-50">
                ${calculateTotalProduksi(item.qty_kbn, item.total_prod)}
            </td>
            <td class="px-3 py-4">
                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full ${item.status_badge.class}">
                    ${item.status_badge.label}
                </span>
            </td>
            <td class="px-3 py-4 text-sm text-gray-600 font-mono">${item.actual ?? '-'}</td>
            <td class="px-3 py-4 text-sm text-gray-600">${escapeHtml(item.lot_no) || '-'}</td>
            <td class="px-3 py-4">
                <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                    ${escapeHtml(item.calc_by) || '-'}
                </span>
            </td>
            <td class="px-3 py-4 text-xs text-gray-500">${item.start_time}</td>
            <td class="px-3 py-4">
                <div class="flex items-center justify-center space-x-2">
                    <button onclick="openEditModal(${item.id})"
                            class="bg-orange-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-orange-600 transition">
                        Edit
                    </button>
                    <button onclick="deletePlanning(${item.id})"
                            class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-red-600 transition">
                        Delete
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Tambahkan function kalkulasi Total Produksi
function calculateTotalProduksi(qtyKbn, totalProd) {
    // Pastikan kedua nilai adalah number
    const qty = parseFloat(qtyKbn) || 0;
    const prod = parseFloat(totalProd) || 0;
    
    // Hitung hasil perkalian
    const result = qty * prod;
    
    // Format angka (jika perlu)
    return result.toLocaleString('id-ID');
}

    function getPriorityClass(priority) {
        if (priority === 1) return 'bg-red-100 text-red-800';
        if (priority === 2) return 'bg-orange-100 text-orange-800';
        if (priority === 3) return 'bg-yellow-100 text-yellow-800';
        if (priority === 4) return 'bg-blue-100 text-blue-800';
        return 'bg-gray-100 text-gray-800';
    }

    function renderPagination(pagination) {
        const container = document.getElementById('paginationContainer');
        totalPages = pagination.total_pages;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        html += `<button onclick="goToPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''} class="px-2 py-1.5 rounded-lg border text-xs ${currentPage === 1 ? 'border-gray-200 text-gray-400 cursor-not-allowed' : 'border-gray-300 text-gray-700 hover:bg-gray-50'} transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>`;

        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        if (startPage > 1) {
            html += `<button onclick="goToPage(1)" class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-xs">1</button>`;
            if (startPage > 2) html += `<span class="px-1 text-gray-500 text-xs">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<button onclick="goToPage(${i})" class="px-3 py-1.5 rounded-lg border transition text-xs ${i === currentPage ? 'bg-black text-white border-black font-semibold' : 'border-gray-300 text-gray-700 hover:bg-gray-50'}">${i}</button>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<span class="px-1 text-gray-500 text-xs">...</span>`;
            html += `<button onclick="goToPage(${totalPages})" class="px-3 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-xs">${totalPages}</button>`;
        }

        html += `<button onclick="goToPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''} class="px-2 py-1.5 rounded-lg border text-xs ${currentPage === totalPages ? 'border-gray-200 text-gray-400 cursor-not-allowed' : 'border-gray-300 text-gray-700 hover:bg-gray-50'} transition"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>`;

        container.innerHTML = html;
    }

    function goToPage(page) {
        if (page < 1 || page > totalPages || page === currentPage) return;
        currentPage = page;
        loadPlannings();
    }

    function updateShowingInfo(pagination) {
        const showingFrom = document.getElementById('showingFrom');
        const showingTo = document.getElementById('showingTo');
        const totalEntries = document.getElementById('totalEntries');
        
        if (showingFrom) showingFrom.textContent = pagination.from;
        if (showingTo) showingTo.textContent = pagination.to;
        if (totalEntries) totalEntries.textContent = pagination.total;
    }

    function showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        if (overlay) {
            overlay.classList.toggle('hidden', !show);
        }
    }

    function showEmptyState(message) {
    const tbody = document.getElementById('planningsTableBody');
    if (tbody) {
        tbody.innerHTML = `
            <tr><td colspan="15" class="px-6 py-16 text-center"> <!-- Ganti dari 14 ke 15 -->
                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="mt-4 text-gray-600 font-semibold">${message}</p>
            </td></tr>`;
    }
}

    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Modal Functions
    function openCreateModal() {
        const modal = document.getElementById('createModal');
        const form = document.getElementById('createForm');
        if (form) form.reset();
        clearErrors();
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => modal.classList.add('modal-fade-in'), 10);
        }
    }

    async function openEditModal(id) {
        try {
            const response = await fetch(`/plannings/${id}`);
            const res = await response.json();
            if (!res.success) throw new Error('API error');

            const item = res.data;
            const editPlanningId = document.getElementById('editPlanningId');
            const editPartNoFg = document.getElementById('editPartNoFg');
            const editPartNoParent = document.getElementById('editPartNoParent');
            const editPartNoChild = document.getElementById('editPartNoChild');
            const editJudgement = document.getElementById('editJudgement');
            const editPriority = document.getElementById('editPriority');
            const editStore = document.getElementById('editStore');
            const editProcess = document.getElementById('editProcess');
            const editLine = document.getElementById('editLine');
            const editRackNo = document.getElementById('editRackNo');
            const editQtyKbn = document.getElementById('editQtyKbn');
            const editQtyLot = document.getElementById('editQtyLot');
            const editTotalProd = document.getElementById('editTotalProd');
            const editStatus = document.getElementById('editStatus');
            const editLotNo = document.getElementById('editLotNo');

            if (editPlanningId) editPlanningId.value = item.id;
            if (editPartNoFg) editPartNoFg.value = item.part_no_fg ?? '';
            if (editPartNoParent) editPartNoParent.value = item.part_no_parent ?? '';
            if (editPartNoChild) editPartNoChild.value = item.part_no_child ?? '';
            if (editJudgement) editJudgement.value = item.judgement ?? '';
            if (editPriority) editPriority.value = item.priority ?? '';
            if (editStore) editStore.value = item.store ?? '';
            if (editProcess) editProcess.value = item.process ?? '';
            if (editLine) editLine.value = item.line ?? '';
            if (editRackNo) editRackNo.value = item.rack_no ?? '';
            if (editQtyKbn) editQtyKbn.value = item.qty_kbn ?? '';
            if (editQtyLot) editQtyLot.value = item.qty_lot ?? '';
            if (editTotalProd) editTotalProd.value = item.total_prod ?? '';
            if (editStatus) editStatus.value = item.status ?? '';
            if (editLotNo) editLotNo.value = item.lot_no ?? '';
            
            clearErrors();

            const modal = document.getElementById('editModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => modal.classList.add('modal-fade-in'), 10);
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Error!', 'Gagal memuat data planning', 'error');
        }
    }

    async function openDetailModal(id) {
        try {
            const response = await fetch(`/plannings/${id}`);
            const res = await response.json();
            if (!res.success) throw new Error('API error');

            const item = res.data;
            
            // Populate detail modal
            const detailPartNoFg = document.getElementById('detailPartNoFg');
            const detailPartNoParent = document.getElementById('detailPartNoParent');
            const detailPartNoChild = document.getElementById('detailPartNoChild');
            const detailJudgement = document.getElementById('detailJudgement');
            const detailPriority = document.getElementById('detailPriority');
            const detailStore = document.getElementById('detailStore');
            const detailProcess = document.getElementById('detailProcess');
            const detailLine = document.getElementById('detailLine');
            const detailRackNo = document.getElementById('detailRackNo');
            const detailQtyKbn = document.getElementById('detailQtyKbn');
            const detailQtyConsume = document.getElementById('detailQtyConsume');
            const detailQtyLot = document.getElementById('detailQtyLot');
            const detailStockMin = document.getElementById('detailStockMin');
            const detailStockMax = document.getElementById('detailStockMax');
            const detailStockRealtime = document.getElementById('detailStockRealtime');
            const detailTotalProd = document.getElementById('detailTotalProd');
            const detailStatus = document.getElementById('detailStatus');
            const detailActual = document.getElementById('detailActual');
            const detailLotNo = document.getElementById('detailLotNo');
            const detailCalcBy = document.getElementById('detailCalcBy');
            const detailReason = document.getElementById('detailReason');
            const detailPullingTime = document.getElementById('detailPullingTime');
            const detailStartTime = document.getElementById('detailStartTime');
            const detailEndTime = document.getElementById('detailEndTime');

            if (detailPartNoFg) detailPartNoFg.textContent = item.part_no_fg || '-';
            if (detailPartNoParent) detailPartNoParent.textContent = item.part_no_parent || '-';
            if (detailPartNoChild) detailPartNoChild.textContent = item.part_no_child || '-';
            if (detailJudgement) detailJudgement.textContent = item.judgement || '-';
            if (detailPriority) detailPriority.textContent = item.priority || '-';
            if (detailStore) detailStore.textContent = item.store || '-';
            if (detailProcess) detailProcess.textContent = item.process || '-';
            if (detailLine) detailLine.textContent = item.line || '-';
            if (detailRackNo) detailRackNo.textContent = item.rack_no || '-';
            if (detailQtyKbn) detailQtyKbn.textContent = item.qty_kbn || '-';
            if (detailQtyConsume) detailQtyConsume.textContent = item.qty_consume || '-';
            if (detailQtyLot) detailQtyLot.textContent = item.qty_lot || '-';
            if (detailStockMin) detailStockMin.textContent = item.stock_min || '-';
            if (detailStockMax) detailStockMax.textContent = item.stock_max || '-';
            if (detailStockRealtime) detailStockRealtime.textContent = item.stock_realtime || '-';
            if (detailTotalProd) detailTotalProd.textContent = item.total_prod || '-';
            if (detailStatus) detailStatus.textContent = item.status || '-';
            if (detailActual) detailActual.textContent = item.actual || '-';
            if (detailLotNo) detailLotNo.textContent = item.lot_no || '-';
            if (detailCalcBy) detailCalcBy.textContent = item.calc_by || '-';
            if (detailReason) detailReason.textContent = item.reason || '-';
            if (detailPullingTime) detailPullingTime.textContent = item.pulling_time || '-';
            if (detailStartTime) detailStartTime.textContent = item.start_time || '-';
            if (detailEndTime) detailEndTime.textContent = item.end_time || '-';

            const modal = document.getElementById('detailModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => modal.classList.add('modal-fade-in'), 10);
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Error!', 'Gagal memuat detail planning', 'error');
        }
    }

    function openImportModal() {
        const modal = document.getElementById('importModal');
        const form = document.getElementById('importForm');
        const fileInfo = document.getElementById('fileInfo');
        const importErrors = document.getElementById('importErrors');
        
        if (form) form.reset();
        if (fileInfo) fileInfo.classList.add('hidden');
        if (importErrors) importErrors.classList.add('hidden');
        clearErrors();
        
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => modal.classList.add('modal-fade-in'), 10);
        }
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        if (modal) {
            modal.classList.remove('modal-fade-in');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                const form = document.getElementById('createForm');
                if (form) form.reset();
                clearErrors();
            }, 300);
        }
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        if (modal) {
            modal.classList.remove('modal-fade-in');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                const form = document.getElementById('editForm');
                if (form) form.reset();
                clearErrors();
            }, 300);
        }
    }

    function closeDetailModal() {
        const modal = document.getElementById('detailModal');
        if (modal) {
            modal.classList.remove('modal-fade-in');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }
    }

    function closeImportModal() {
        const modal = document.getElementById('importModal');
        if (modal) {
            modal.classList.remove('modal-fade-in');
            setTimeout(() => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                const form = document.getElementById('importForm');
                if (form) form.reset();
                const fileInfo = document.getElementById('fileInfo');
                const importErrors = document.getElementById('importErrors');
                if (fileInfo) fileInfo.classList.add('hidden');
                if (importErrors) importErrors.classList.add('hidden');
                clearErrors();
            }, 300);
        }
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    }

    // Form Submissions
    async function handleCreateSubmit(e) {
        e.preventDefault();
        clearErrors();

        const formData = new FormData(e.target);

        try {
            const response = await fetch('/plannings', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    closeCreateModal();
                    loadPlannings();
                });
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const el = document.getElementById(`error-create-${key}`);
                        if (el) el.textContent = data.errors[key][0];
                    });
                }
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error!', 'Terjadi kesalahan!', 'error');
        }
    }

    async function handleEditSubmit(e) {
        e.preventDefault();
        clearErrors();

        const formData = new FormData(e.target);
        const id = document.getElementById('editPlanningId').value;
        formData.append('_method', 'PUT');

        try {
            const response = await fetch(`/plannings/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    closeEditModal();
                    loadPlannings();
                });
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const el = document.getElementById(`error-edit-${key}`);
                        if (el) el.textContent = data.errors[key][0];
                    });
                }
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error!', 'Terjadi kesalahan!', 'error');
        }
    }

    async function handleImportSubmit(e) {
        e.preventDefault();
        clearErrors();

        const formData = new FormData(e.target);
        const submitBtn = e.target.querySelector('button[type="submit"]');
        
        if (!submitBtn) {
            console.error('Submit button not found');
            return;
        }

        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            Importing...
        `;

        try {
            const response = await fetch('/plannings/import', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                let message = data.message;
                
                // Show errors if any
                if (data.errors && data.errors.length > 0) {
                    const errorList = document.getElementById('importErrorList');
                    if (errorList) {
                        errorList.innerHTML = data.errors.map(err => `<li>${escapeHtml(err)}</li>`).join('');
                        const importErrors = document.getElementById('importErrors');
                        if (importErrors) importErrors.classList.remove('hidden');
                    }
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Import Selesai!',
                    html: `
                        <div class="text-left">
                            <p class="font-semibold mb-2">${message}</p>
                            ${data.imported ? `<p class="text-green-600">✓ ${data.imported} data berhasil diimport</p>` : ''}
                            ${data.skipped ? `<p class="text-orange-600">⚠ ${data.skipped} data dilewati</p>` : ''}
                            ${data.errors && data.errors.length > 0 ? '<p class="text-sm text-gray-500 mt-2">Lihat detail error di modal.</p>' : ''}
                        </div>
                    `,
                    showConfirmButton: true,
                }).then(() => {
                    if (!data.errors || data.errors.length === 0) {
                        closeImportModal();
                    }
                    loadPlannings();
                });
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const el = document.getElementById(`error-import-${key}`);
                        if (el) el.textContent = data.errors[key][0];
                    });
                }
                if (data.message) {
                    Swal.fire('Error!', data.message, 'error');
                }
            }
        } catch (error) {
            console.error('Import error:', error);
            Swal.fire('Error!', 'Terjadi kesalahan saat import! ' + error.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    }

    async function deletePlanning(id) {
        const result = await Swal.fire({
            title: 'Hapus Planning?',
            text: "Data planning akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/plannings/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => loadPlannings());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'Gagal menghapus planning!', 'error');
            }
        }
    }

    async function clearAllData() {
        const result = await Swal.fire({
            title: 'Hapus Semua Data?',
            text: "Semua data planning akan dihapus permanen! Aksi ini tidak dapat dibatalkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch('/plannings/clear', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => loadPlannings());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'Gagal menghapus data!', 'error');
            }
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
            closeImportModal();
            closeDetailModal();
        }
    });

    // Tambahkan function ini sebelum pen

function calculateTotalProduksi(qtyKbn, totalProd) {
    // Jika salah satu kosong, return '-'
    if (qtyKbn === null || qtyKbn === undefined || totalProd === null || totalProd === undefined) {
        return '-';
    }
    
    const qty = parseFloat(qtyKbn);
    const prod = parseFloat(totalProd);
    
    // Jika bukan angka yang valid
    if (isNaN(qty) || isNaN(prod)) {
        return '-';
    }
    
    // Hitung dan format
    const result = qty * prod;
    return result.toLocaleString('id-ID');
}
</script>
@endpush