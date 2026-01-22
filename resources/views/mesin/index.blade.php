@extends('layouts.app')

@section('title', 'Master Mesin')

@section('content')
<div class="space-y-6">
    
    <!-- Page Header -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Master Mesin</h1>
                <p class="text-gray-600 mt-1">Kelola data mesin produksi</p>
            </div>
            <button 
                onclick="openCreateModal()"
                class="bg-black text-white px-6 py-3 rounded-lg font-semibold hover:bg-gray-800 transition transform hover:scale-105 flex items-center space-x-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span>Tambah Mesin</span>
            </button>
        </div>

        <!-- Statistics Badge -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider">Total Mesin</p>
                        <p class="text-2xl font-bold text-blue-900 mt-1" id="statTotal">{{ $totalMesin }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col md:flex-row md:items-center md:space-x-4 space-y-3 md:space-y-0">
            <div class="w-full md:w-1/2 lg:w-1/3 relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input 
                    type="text" 
                    id="searchInput"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition"
                    placeholder="Cari nama, tonase..."
                >
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
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama Mesin</th>
                       
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Struk</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tonase</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="mesinTableBody">
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
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
@include('mesin.create')

<!-- Edit Modal -->
@include('mesin.edit')

@endsection

@push('scripts')
<script>
    let currentPage = 1;
    let perPage = 20;
    let searchQuery = '';
    let totalPages = 1;
    let isLoading = false;
    let searchTimeout = null;

    document.addEventListener('DOMContentLoaded', function() {
        loadMesin();
        bindEvents();
    });

    function bindEvents() {
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                searchQuery = this.value;
                currentPage = 1;
                loadMesin();
            }, 300);
        });

        document.getElementById('perPageSelect').addEventListener('change', function() {
            perPage = this.value === 'all' ? 'all' : parseInt(this.value);
            currentPage = 1;
            loadMesin();
        });
    }

    async function loadMesin() {
        if (isLoading) return;
        isLoading = true;
        showLoading(true);

        try {
            const params = new URLSearchParams({
                page: currentPage,
                per_page: perPage,
                search: searchQuery,
            });

            const response = await fetch(`/mesin/data?${params}`, {
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
                document.getElementById('statTotal').textContent = result.stats.total_mesin;
            } else {
                showEmptyState('Failed to load data');
            }
        } catch (error) {
            console.error('Error loading mesin:', error);
            showEmptyState('Error loading data. Please try again.');
        } finally {
            isLoading = false;
            showLoading(false);
        }
    }

    function renderTable(mesin) {
        const tbody = document.getElementById('mesinTableBody');
        
        if (!mesin || mesin.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                        <p class="mt-4 text-gray-600 font-semibold">Data mesin tidak ditemukan</p>
                        <p class="text-gray-500 text-sm">${searchQuery ? 'Coba kata kunci lain' : 'Klik "Tambah Mesin" untuk menambahkan data'}</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = mesin.map(item => `
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${item.row_number}</td>
                <td class="px-6 py-4">
                    <p class="text-sm font-semibold text-gray-900">${escapeHtml(item.nama)}</p>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">${item.struk}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${escapeHtml(item.tonase)}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-center space-x-2">
                        <button onclick="openEditModal(${item.id})"
                                class="bg-orange-500 text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-orange-600 transition">
                            Edit
                        </button>
                        <button onclick="deleteMesin(${item.id})"
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
        loadMesin();
    }

    function updateShowingInfo(pagination) {
        document.getElementById('showingFrom').textContent = pagination.from;
        document.getElementById('showingTo').textContent = pagination.to;
        document.getElementById('totalEntries').textContent = pagination.total;
    }

    function showLoading(show) {
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.toggle('hidden', !show);
    }

    function showEmptyState(message) {
        document.getElementById('mesinTableBody').innerHTML = `
            <tr><td colspan="5" class="px-6 py-16 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="mt-4 text-gray-600 font-semibold">${message}</p>
            </td></tr>`;
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
        document.getElementById('createForm').reset();
        clearErrors();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => modal.classList.add('modal-fade-in'), 10);
    }

    async function openEditModal(id) {
        try {
            const response = await fetch(`/mesin/${id}`);
            const res = await response.json();
            if (!res.success) throw new Error('API error');

            const mesin = res.data;
            document.getElementById('editMesinId').value = mesin.id;
            document.getElementById('editNama').value = mesin.nama;
            document.getElementById('editStruk').value = mesin.struk;
            document.getElementById('editTonase').value = mesin.tonase ?? '';
            clearErrors();

            const modal = document.getElementById('editModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => modal.classList.add('modal-fade-in'), 10);
        } catch (error) {
            console.error(error);
            Swal.fire('Error!', 'Gagal memuat data mesin', 'error');
        }
    }

    function closeCreateModal() {
        const modal = document.getElementById('createModal');
        modal.classList.remove('modal-fade-in');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('createForm').reset();
            clearErrors();
        }, 300);
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        modal.classList.remove('modal-fade-in');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.getElementById('editForm').reset();
            clearErrors();
        }, 300);
    }

    function clearErrors() {
        document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
    }

    // Form Submissions
    document.getElementById('createForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();

        const formData = new FormData(this);

        try {
            const response = await fetch('/mesin', {
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
                    loadMesin();
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
    });

    document.getElementById('editForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();

        const formData = new FormData(this);
        const id = document.getElementById('editMesinId').value;
        formData.append('_method', 'PUT');

        try {
            const response = await fetch(`/mesin/${id}`, {
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
                    loadMesin();
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
    });

    async function deleteMesin(id) {
        const result = await Swal.fire({
            title: 'Hapus Mesin?',
            text: "Data mesin akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#000',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch(`/mesin/${id}`, {
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
                    }).then(() => loadMesin());
                } else {
                    Swal.fire('Error!', data.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'Gagal menghapus mesin!', 'error');
            }
        }
    }

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeEditModal();
        }
    });
</script>
@endpush