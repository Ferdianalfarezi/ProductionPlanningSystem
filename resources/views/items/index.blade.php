@extends('layouts.app')

@section('title', 'Master Items')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Master Items</h1>
                <p class="text-gray-600 mt-1">Kelola data items produksi</p>
            </div>
            <div class="flex items-center space-x-3">
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
                    <span>Tambah Item</span>
                </button>
            </div>
        </div>

        <!-- Statistics Badge -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-purple-600 uppercase tracking-wider">Total Items</p>
                        <p class="text-2xl font-bold text-purple-900 mt-1" id="statTotal">{{ $totalItems }}</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
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
                    placeholder="Cari part no, line, process..."
                >
            </div>
            <div class="flex-shrink-0">
                <select id="mesinFilter" class="px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition">
                    <option value="">Semua Mesin</option>
                    @foreach($mesinList as $mesin)
                        <option value="{{ $mesin->id }}">{{ $mesin->nama }} ({{ $mesin->tonase }})</option>
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
                        <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Part No</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Line</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Machine</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tonase</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Process</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Process Name</th>
                        <th class="px-4 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">GSPH</th>
                        <th class="px-4 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="itemsTableBody">
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
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

<!-- Create Modal -->
@include('items.create')

<!-- Edit Modal -->
@include('items.edit')

<!-- Import Modal -->
@include('items.import')

@endsection

@push('scripts')
<script>
    let currentPage = 1;
    let perPage = 20;
    let searchQuery = '';
    let mesinFilter = '';
    let totalPages = 1;
    let isLoading = false;
    let searchTimeout = null;

    // Mesin list for modals
    const mesinList = @json($mesinList);

    document.addEventListener('DOMContentLoaded', function() {
        loadItems();
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
                    loadItems();
                }, 300);
            });
        }

        // Per page selector
        const perPageSelect = document.getElementById('perPageSelect');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                perPage = this.value === 'all' ? 'all' : parseInt(this.value);
                currentPage = 1;
                loadItems();
            });
        }

        // Mesin filter
        const mesinFilterEl = document.getElementById('mesinFilter');
        if (mesinFilterEl) {
            mesinFilterEl.addEventListener('change', function() {
                mesinFilter = this.value;
                currentPage = 1;
                loadItems();
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

    async function loadItems() {
        if (isLoading) return;
        isLoading = true;
        showLoading(true);

        try {
            const params = new URLSearchParams({
                page: currentPage,
                per_page: perPage,
                search: searchQuery,
                mesin_id: mesinFilter,
            });

            const response = await fetch(`/items/data?${params}`, {
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
                const statTotal = document.getElementById('statTotal');
                if (statTotal) statTotal.textContent = result.stats.total_items;
            } else {
                showEmptyState('Failed to load data');
            }
        } catch (error) {
            console.error('Error loading items:', error);
            showEmptyState('Error loading data. Please try again.');
        } finally {
            isLoading = false;
            showLoading(false);
        }
    }

    function renderTable(items) {
        const tbody = document.getElementById('itemsTableBody');
        
        if (!items || items.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-16 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="mt-4 text-gray-600 font-semibold">Data items tidak ditemukan</p>
                        <p class="text-gray-500 text-sm">${searchQuery ? 'Coba kata kunci lain' : 'Klik "Tambah Item" atau "Import Excel" untuk menambahkan data'}</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = items.map(item => `
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-4 text-sm font-medium text-gray-900">${item.row_number}</td>
                <td class="px-4 py-4">
                    <p class="text-sm font-semibold text-gray-900">${escapeHtml(item.part_no)}</p>
                </td>
                <td class="px-4 py-4 text-sm text-gray-600">${escapeHtml(item.line) || '-'}</td>
                <td class="px-4 py-4 text-sm text-gray-600 font-medium">${escapeHtml(item.machine)}</td>
                <td class="px-4 py-4 text-sm text-gray-600">${escapeHtml(item.tonase)}</td>
                <td class="px-4 py-4 text-sm text-gray-600">${escapeHtml(item.process) || '-'}</td>
                <td class="px-4 py-4">
                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                        ${escapeHtml(item.process_name) || '-'}
                    </span>
                </td>
                <td class="px-4 py-4 text-sm text-gray-600 font-mono">${item.gsph}</td>
                <td class="px-4 py-4">
                    <div class="flex items-center justify-center space-x-2">
                        <button onclick="openEditModal(${item.id})"
                                class="bg-orange-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-orange-600 transition">
                            Edit
                        </button>
                        <button onclick="deleteItem(${item.id})"
                                class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-red-600 transition">
                            Delete
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
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
        loadItems();
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
        const tbody = document.getElementById('itemsTableBody');
        if (tbody) {
            tbody.innerHTML = `
                <tr><td colspan="9" class="px-6 py-16 text-center">
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
            const response = await fetch(`/items/${id}`);
            const res = await response.json();
            if (!res.success) throw new Error('API error');

            const item = res.data;
            const editItemId = document.getElementById('editItemId');
            const editPartNo = document.getElementById('editPartNo');
            const editLine = document.getElementById('editLine');
            const editMesinId = document.getElementById('editMesinId');
            const editProcess = document.getElementById('editProcess');
            const editProcessName = document.getElementById('editProcessName');
            const editGsph = document.getElementById('editGsph');

            if (editItemId) editItemId.value = item.id;
            if (editPartNo) editPartNo.value = item.part_no;
            if (editLine) editLine.value = item.line ?? '';
            if (editMesinId) editMesinId.value = item.mesin_id;
            if (editProcess) editProcess.value = item.process ?? '';
            if (editProcessName) editProcessName.value = item.process_name ?? '';
            if (editGsph) editGsph.value = item.gsph ?? '';
            
            clearErrors();

            const modal = document.getElementById('editModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                setTimeout(() => modal.classList.add('modal-fade-in'), 10);
            }
        } catch (error) {
            console.error(error);
            Swal.fire('Error!', 'Gagal memuat data item', 'error');
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
            const response = await fetch('/items', {
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
                    loadItems();
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
        const id = document.getElementById('editItemId').value;
        formData.append('_method', 'PUT');

        try {
            const response = await fetch(`/items/${id}`, {
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
                    loadItems();
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
            console.log('Starting import...');
            
            const response = await fetch('/items/import', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            });

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);

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
                    loadItems();
                });
            } else {
                // Handle validation errors
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

    async function deleteItem(id) {
        const result = await Swal.fire({
            title: 'Hapus Item?',
            text: "Data item akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/items/${id}`, {
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
                    }).then(() => loadItems());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'Gagal menghapus item!', 'error');
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
        }
    });
</script>
@endpush