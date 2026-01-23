@extends('layouts.andon')

@section('title', 'Andon Mesin')
@section('page-title', 'ANDON MESIN LINE 9')
@section('body-class', 'andon-mesin-page')

@section('content')
    <!-- Filter & Summary Bar -->
    <div id="filterBox" class="mt-3 mb-1">

        <div class="card border-0 " style="background-color: #000000; border: 2px solid #ffffff;">
            <div class="card-body py-2 px-4">
                <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                    
                    <!-- Filter Mesin -->
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 0.9rem;">Mesin:</small>
                        <select id="filterMesin" class="form-select form-select-sm bg-black text-white border-white" style="width: 180px;">
                            <option value="">Semua Mesin</option>
                            @foreach($submittedMesin as $mesin)
                                <option value="{{ $mesin->mesin_id }}">{{ $mesin->mesin_nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="vr bg-white" style="height: 30px;"></div>

                    <!-- Filter Tanggal -->
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 0.9rem;">Tanggal:</small>
                        <input type="date" id="filterTanggal" class="form-control form-control-sm bg-black text-white border-white" 
                               value="{{ $defaultDate }}" style="width: 140px;">
                    </div>

                    <div class="vr bg-white" style="height: 30px;"></div>

                    <!-- Filter Shift -->
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 0.9rem;">Shift:</small>
                        <select id="filterShift" class="form-select form-select-sm bg-black text-white border-white" style="width: 120px;">
                            <option value="">Semua Shift</option>
                            <option value="1">Shift 1</option>
                            <option value="2">Shift 2</option>
                        </select>
                    </div>

                    <div class="vr bg-white" style="height: 30px;"></div>

                    <!-- Tombol Filter -->
                    <div class="d-flex align-items-center gap-2">
                        <button onclick="loadAndonMesinData()" class="btn btn-sm btn-light px-3">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <button onclick="resetFilters()" class="btn btn-sm btn-outline-light px-3">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    </div>

                    <div class="vr bg-white" style="height: 30px;"></div>

                    {{-- <!-- Export Button -->
                    <div class="d-flex align-items-center gap-2">
                        <button onclick="exportToExcel()" class="btn btn-sm btn-light px-3">
                            <i class="bi bi-file-earmark-excel me-1"></i> Export
                        </button>
                    </div> --}}

                </div>
            </div>
        </div>
    </div>

    <!-- Loading State -->
    <div id="loadingAndonMesin" class="d-none text-center py-5">
        <div class="d-flex flex-column align-items-center gap-3">
            <div class="spinner-border text-white" style="width: 3rem; height: 3rem;"></div>
            <p class="text-white fs-5">Memuat data andon mesin...</p>
        </div>
    </div>

    <!-- Results Container -->
    <div id="andonMesinResults" class="mt-4">
        <!-- Data akan diisi via JavaScript -->
    </div>

    <!-- Empty State -->
    <div id="emptyState" class="d-none">
        <div class="card border-0 bg-black">
            <div class="card-body text-center py-5" style="border: 2px solid #ffffff;">
                <i class="bi bi-inbox fs-1 text-white mb-3"></i>
                <h5 class="text-white mb-2">Data Tidak Ditemukan</h5>
                <p class="text-white mb-4">Tidak ada data andon mesin untuk filter yang dipilih.</p>
                <button onclick="resetFilters()" class="btn btn-outline-light btn-sm me-2">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </button>
                <button onclick="setDateFilter('today')" class="btn btn-light btn-sm">
                    <i class="bi bi-calendar-day me-1"></i> Lihat Hari Ini
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div id="statisticsCards" class="d-none mt-4">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 bg-black">
                    <div class="card-body" style="border: 2px solid #ffffff;">
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 me-3">
                                <i class="bi bi-cpu text-black fs-4"></i>
                            </div>
                            <div>
                                <small class="text-white d-block">Total Mesin</small>
                                <h4 class="mb-0 fw-bold text-white" id="totalMesin">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-black">
                    <div class="card-body" style="border: 2px solid #ffffff;">
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 me-3">
                                <i class="bi bi-cloud-upload text-black fs-4"></i>
                            </div>
                            <div>
                                <small class="text-white d-block">Total Submission</small>
                                <h4 class="mb-0 fw-bold text-white" id="totalSubmission">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-black">
                    <div class="card-body" style="border: 2px solid #ffffff;">
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 me-3">
                                <i class="bi bi-clipboard-data text-black fs-4"></i>
                            </div>
                            <div>
                                <small class="text-white d-block">Total Plan Qty</small>
                                <h4 class="mb-0 fw-bold text-white" id="totalPlan">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-black">
                    <div class="card-body" style="border: 2px solid #ffffff;">
                        <div class="d-flex align-items-center">
                            <div class="bg-white p-2 me-3">
                                <i class="bi bi-graph-up text-black fs-4"></i>
                            </div>
                            <div>
                                <small class="text-white d-block">Total Actual Qty</small>
                                <h4 class="mb-0 fw-bold text-white" id="totalActual">0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .andon-mesin-page .card {
        border-radius: 0;
    }
    
    .mesin-header {
        background: #000000;
        border-bottom: 2px solid #ffffff;
        padding: 12px 20px;
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
    }
    
    .table-compact th,
    .table-compact td {
        padding: 8px 10px;
        font-size: 0.875rem;
        vertical-align: middle;
        white-space: nowrap;
    }
    
    .table-compact thead th {
        background-color: #000000;
        color: #ffffff;
        font-weight: 600;
        text-align: center;
        border: 1px solid #ffffff;
    }
    
    .efficiency-badge {
        padding: 3px 8px;
        font-size: 0.75rem;
        font-weight: 600;
        min-width: 50px;
        text-align: center;
        display: inline-block;
    }
    
    .eff-high {
        background-color: #32cb0c;
        color: #ffffff;
        border: 1px solid #32cb0c;
    }
    
    .eff-medium {
        background-color: #ffffff;
        color: #000000;
        border: 1px solid #ffffff;
    }
    
    .eff-low {
        background-color: #ff0000;
        color: #ffffff;
        border: 1px solid #fe0202;
    }
    
    .eff-none {
        background-color: #666666;
        color: #ffffff;
        border: 1px solid #666666;
    }
    
    .time-cell {
        font-size: 0.75rem;
        font-weight: 500;
        text-align: center;
    }
    
    .time-actual {
        color: #ffffff;
        font-weight: 600;
    }
    
    .time-plan {
        color: #cccccc;
    }
    
    .quantity-cell {
        text-align: right;
        font-family: 'Courier New', monospace;
        font-weight: 600;
    }
    
    .quantity-actual {
        color: #ffffff;
    }
    
    .quantity-plan {
        color: #cccccc;
    }
    
    .inactive-row {
        background-color: #222222;
    }
    
    .inactive-row td {
        color: #999999 !important;
    }
    
    .scrollable-table {
        overflow-x: auto;
        border: 2px solid #ffffff;
        margin-bottom: 0;
    }
    
    .mesin-card {
        border: 2px solid #ffffff;
        margin-bottom: 1.5rem;
        background: #000000;
    }
    
    .mesin-title {
        background: #000000;
        color: white;
        padding: 12px 20px;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #ffffff;
    }
    
    .mesin-subtitle {
        font-size: 0.8rem;
        color: #cccccc;
        font-weight: normal;
    }
    
    .horizontal-shifts {
        display: flex;
        gap: 20px;
    }
    
    .shift-column {
        flex: 1;
        min-width: 0;
    }
    
    @media (max-width: 1200px) {
        .horizontal-shifts {
            flex-direction: column;
        }
    }
    
    /* Table styling */
    .table-dark {
        --bs-table-bg: #000000;
        --bs-table-striped-bg: #222222;
        --bs-table-striped-color: #ffffff;
        --bs-table-active-bg: #333333;
        --bs-table-active-color: #ffffff;
        --bs-table-hover-bg: #333333;
        --bs-table-hover-color: #ffffff;
        color: #ffffff;
        border-color: #ffffff;
    }
    
    .table-dark tbody, 
    .table-dark td, 
    .table-dark th, 
    .table-dark thead, 
    .table-dark tr {
        border-color: #ffffff;
    }
    
    .table-active {
        --bs-table-bg: #222222;
        --bs-table-color: #ffffff;
    }
    
    /* Button styling */
    .btn-light {
        background-color: #ffffff;
        color: #000000;
        border: 1px solid #ffffff;
    }
    
    .btn-outline-light {
        color: #ffffff;
        border-color: #ffffff;
    }
    
    .btn-outline-light:hover {
        background-color: #ffffff;
        color: #000000;
    }
    
    /* Form control styling */
    .form-control, .form-select {
        border: 1px solid #ffffff;
    }
    
    .form-control:focus, .form-select:focus {
        background-color: #000000;
        color: #ffffff;
        border-color: #ffffff;
        box-shadow: none;
    }
    
    /* Badge styling */
    .badge {
        border-radius: 0;
    }
    
    .badge.bg-primary {
        background-color: #ffffff !important;
        color: #000000 !important;
        border: 1px solid #ffffff;
    }
    
    /* Column widths */
    .col-urutan {
        width: 60px;
        min-width: 60px;
    }
    
    .col-partno {
        width: 120px;
        min-width: 120px;
    }
    
    .col-gsph {
        width: 80px;
        min-width: 80px;
    }
    
    .col-time {
        width: 90px;
        min-width: 90px;
    }
    
    .col-qty {
        width: 100px;
        min-width: 100px;
    }
    
    .col-eff {
        width: 70px;
        min-width: 70px;
    }
    
    /* Alignment */
    .text-start {
        text-align: left !important;
    }
    
    .text-end {
        text-align: right !important;
    }
    
    .text-center {
        text-align: center !important;
    }
</style>
@endpush

@push('scripts')
<script>
let currentData = [];
let currentFilters = {};

// ==================== FUNGSI HELPER ====================
function formatNumber(num) {
    if (!num && num !== 0) return '<span class="text-white-50">-</span>';
    return new Intl.NumberFormat('id-ID').format(num);
}

function formatDate(dateString) {
    if (!dateString) return '<span class="text-white-50">-</span>';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return date.toLocaleDateString('id-ID', { 
        weekday: 'short', 
        day: '2-digit', 
        month: 'short', 
        year: 'numeric' 
    });
}

function formatDateTime(dateTimeString) {
    if (!dateTimeString || dateTimeString === '-') return '<span class="text-white-50">-</span>';
    const date = new Date(dateTimeString);
    if (isNaN(date.getTime())) return dateTimeString;
    return date.toLocaleString('id-ID', { 
        day: '2-digit', 
        month: '2-digit', 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function formatTime(timeString) {
    if (!timeString || timeString === '-') return '<span class="text-white-50">-</span>';
    if (timeString.match(/^\d{2}:\d{2}$/)) return timeString;
    const date = new Date(timeString);
    if (isNaN(date.getTime())) return timeString;
    return date.toLocaleString('id-ID', { 
        hour: '2-digit', 
        minute: '2-digit' 
    });
}

function getEfficiencyClass(efficiency) {
    if (!efficiency && efficiency !== 0) return 'eff-none';
    if (efficiency >= 90) return 'eff-high';
    if (efficiency >= 70) return 'eff-medium';
    if (efficiency > 0) return 'eff-low';
    return 'eff-none';
}

function formatDecimal(value, decimals = 1) {
    if (!value && value !== 0) return '<span class="text-white-50">-</span>';
    return parseFloat(value).toFixed(decimals);
}

// ==================== FUNGSI LOAD DATA ====================
async function loadAndonMesinData() {
    const loadingEl = document.getElementById('loadingAndonMesin');
    const resultsEl = document.getElementById('andonMesinResults');
    const emptyEl = document.getElementById('emptyState');
    const statsEl = document.getElementById('statisticsCards');
    
    loadingEl.classList.remove('d-none');
    resultsEl.innerHTML = '';
    emptyEl.classList.add('d-none');
    statsEl.classList.add('d-none');
    
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
            
            if (currentData.length > 0) {
                updateStatistics(result.statistics);
                statsEl.classList.remove('d-none');
                renderAndonMesinData(currentData);
                emptyEl.classList.add('d-none');
            } else {
                emptyEl.classList.remove('d-none');
                statsEl.classList.add('d-none');
            }
        } else {
            throw new Error(result.message || 'Gagal memuat data');
        }
    } catch (error) {
        console.error('Error loading andon mesin data:', error);
        showError('Gagal memuat data: ' + error.message);
    } finally {
        loadingEl.classList.add('d-none');
    }
}

function updateStatistics(stats) {
    document.getElementById('totalSubmission').textContent = formatNumber(stats.total_submissions || 0);
    document.getElementById('totalPlan').textContent = formatNumber(stats.total_rows || 0);
    
    let totalActual = 0;
    let totalMesin = new Set();
    
    currentData.forEach(item => {
        totalMesin.add(item.mesin_id);
        (item.data_json || []).forEach(row => {
            totalActual += row.actual_qty || 0;
        });
    });
    
    document.getElementById('totalActual').textContent = formatNumber(totalActual);
    document.getElementById('totalMesin').textContent = totalMesin.size;
}

// ==================== FUNGSI RENDER DATA ====================
function renderAndonMesinData(data) {
    const container = document.getElementById('andonMesinResults');
    container.innerHTML = '';
    
    // Group by mesin
    const groupedData = {};
    data.forEach(item => {
        const key = item.mesin_id;
        if (!groupedData[key]) groupedData[key] = [];
        groupedData[key].push(item);
    });
    
    // Render setiap mesin
    Object.entries(groupedData).forEach(([mesinId, items]) => {
        const mesinName = items[0]?.mesin_nama || 'Mesin';
        const card = createMesinCard(mesinName, items);
        container.appendChild(card);
    });
}

function createMesinCard(mesinName, items) {
    const card = document.createElement('div');
    card.className = 'mesin-card';
    
    // Hitung statistik
    let totalRows = 0, activeRows = 0, totalPlan = 0, totalActual = 0, totalEfficiency = 0;
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
    
    // Pisahkan data berdasarkan shift
    const shift1Data = items.filter(item => item.shift === '1');
    const shift2Data = items.filter(item => item.shift === '2');
    
    card.innerHTML = `
        <div class="mesin-title">
            <div>
                <span>${mesinName}</span>
                <span class="mesin-subtitle">
                    ${items.length} submission | 
                    Shift: ${Array.from(hasShifts).sort().join(', ')} | 
                    Rows: ${totalRows} | 
                    <span class="efficiency-badge ${efficiencyClass}">Avg: ${avgEfficiency}%</span>
                </span>
            </div>
            <div class="text-end">
                <small class="d-block">Submitted</small>
                <small>${items[0]?.submitted_date || '-'} ${items[0]?.submitted_time || ''}</small>
            </div>
        </div>
        <div class="p-4">
            <div class="horizontal-shifts">
                ${createShiftColumn('1', shift1Data)}
                ${createShiftColumn('2', shift2Data)}
            </div>
        </div>
    `;
    
    return card;
}

function createShiftColumn(shiftNumber, shiftData) {
    if (shiftData.length === 0) {
        return `
            <div class="shift-column">
                <div class="p-3 mb-3 text-center" style="border: 2px solid #ffffff; background: #000000;">
                    <h5 class="mb-0 text-white">Shift ${shiftNumber}</h5>
                    <small class="text-white">Tidak ada data</small>
                </div>
            </div>
        `;
    }
    
    let columnHTML = `<div class="shift-column">`;
    
    shiftData.forEach((submission, index) => {
        columnHTML += createSubmissionTable(submission, index);
    });
    
    columnHTML += `</div>`;
    return columnHTML;
}

function createSubmissionTable(submission, index) {
    const submittedAt = formatDateTime(submission.submitted_at);
    let totalPlan = 0, totalActual = 0, totalEfficiency = 0, countEfficiency = 0;
    
    (submission.data_json || []).forEach(row => {
        totalPlan += row.plan_qty || 0;
        totalActual += row.actual_qty || 0;
        if (row.efficiency) {
            totalEfficiency += row.efficiency;
            countEfficiency++;
        }
    });
    
    const avgEfficiency = countEfficiency > 0 ? (totalEfficiency / countEfficiency).toFixed(0) : 0;
    const totalGSPH = submission.data_json?.length > 0 ? 
        (submission.data_json.reduce((sum, row) => sum + (parseFloat(row.gsph) || 0), 0)) / submission.data_json.length : 0;
    
    return `
        <div class="mb-4 ${index > 0 ? 'mt-4' : ''}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary">Shift ${submission.shift}</span>
                    <small class="text-white">Tanggal: ${formatDate(submission.tanggal)}</small>
                </div>
                <small class="text-white">${submittedAt}</small>
            </div>
            
            <div class="scrollable-table">
                <table class="table table-dark table-compact mb-0">
                    <thead>
                        <tr>
                            <th class="text-center col-urutan">No</th>
                            <th class="text-start col-partno">Part No</th>
                            <th class="text-end col-gsph">GSPH</th>
                            <th class="text-center col-time">Start Plan</th>
                            <th class="text-center col-time">Start Act</th>
                            <th class="text-center col-time">Finish Plan</th>
                            <th class="text-center col-time">Finish Act</th>
                            <th class="text-end col-qty">Plan</th>
                            <th class="text-end col-qty">Actual</th>
                            <th class="text-center col-eff">Eff</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${(submission.data_json || []).map((row, idx) => `
                            <tr class="${!row.is_active ? 'inactive-row' : ''}">
                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        ${row.sort_order || (idx + 1)}
                                    </span>
                                </td>
                                <td class="text-start fw-medium text-white">
                                    ${row.part_no || '<span class="text-white-50">-</span>'}
                                </td>
                                <td class="quantity-cell text-white">
                                    ${row.gsph ? formatDecimal(row.gsph, 1) : '<span class="text-white-50">-</span>'}
                                </td>
                                <td class="time-cell time-plan">
                                    ${row.start_time || '<span class="text-white-50">-</span>'}
                                </td>
                                <td class="time-cell time-actual text-white">
                                    ${row.start_actual ? formatTime(row.start_actual) : '<span class="text-white-50">-</span>'}
                                </td>
                                <td class="time-cell time-plan">
                                    ${row.finish_time || '<span class="text-white-50">-</span>'}
                                </td>
                                <td class="time-cell time-actual text-white">
                                    ${row.finish_actual ? formatTime(row.finish_actual) : '<span class="text-white-50">-</span>'}
                                </td>
                                <td class="quantity-cell quantity-plan">
                                    ${formatNumber(row.plan_qty)}
                                </td>
                                <td class="quantity-cell quantity-actual text-white">
                                    ${row.actual_qty > 0 ? formatNumber(row.actual_qty) : '<span class="text-white-50">-</span>'}
                                </td>
                                <td class="text-center">
                                    <span class="efficiency-badge ${getEfficiencyClass(row.efficiency)}">
                                        ${row.efficiency ? row.efficiency.toFixed(0) + '%' : '<span class="text-white-50">-</span>'}
                                    </span>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                    <tfoot>
                        <tr class="table-active">
                            <td class="text-start fw-bold text-white" colspan="2">Total</td>
                            <td class="quantity-cell text-white">
                                ${formatDecimal(totalGSPH, 1)}
                            </td>
                            <td colspan="4"></td>
                            <td class="quantity-cell fw-bold text-white">${formatNumber(totalPlan)}</td>
                            <td class="quantity-cell fw-bold text-white">${formatNumber(totalActual)}</td>
                            <td class="text-center">
                                <span class="efficiency-badge ${getEfficiencyClass(avgEfficiency)}">
                                    ${avgEfficiency > 0 ? avgEfficiency + '%' : '<span class="text-white-50">-</span>'}
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="d-flex justify-content-between mt-2 text-white small">
                <div>Total: ${submission.data_json?.length || 0} data</div>
                <div>
                    Struk: ${submission.mesin_info?.struk || '-'} | 
                    Tonase: ${submission.mesin_info?.tonase || '-'}
                </div>
            </div>
        </div>
    `;
}

// ==================== FUNGSI FILTER ====================
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
    }
    loadAndonMesinData();
}

function resetFilters() {
    document.getElementById('filterMesin').value = '';
    document.getElementById('filterTanggal').value = '{{ $defaultDate }}';
    document.getElementById('filterShift').value = '';
    loadAndonMesinData();
}

// ==================== EVENT LISTENERS ====================
document.addEventListener('DOMContentLoaded', function() {
    loadAndonMesinData();
});

// ==================== FUNGSI UTILITY ====================
function exportToExcel() {
    if (currentData.length === 0) { 
        showError('Tidak ada data untuk diexport'); 
        return; 
    }
    showSuccess('Export feature coming soon!');
}

function showError(message) {
    Swal.fire({ 
        icon: 'error', 
        title: 'Error!', 
        text: message, 
        confirmButtonColor: '#000000',
        background: '#000000',
        color: '#ffffff'
    });
}

function showSuccess(message) {
    Swal.fire({ 
        icon: 'success', 
        title: 'Berhasil!', 
        text: message, 
        confirmButtonColor: '#000000',
        background: '#000000',
        color: '#ffffff',
        timer: 1500 
    });
}
</script>
@endpush