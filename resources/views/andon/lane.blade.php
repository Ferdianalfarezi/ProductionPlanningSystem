@extends('layouts.andon')

@section('title', 'Andon - Lane 9 Monitoring')
@section('page-title', 'LANE 9 MONITORING')
@section('body-class', 'andon-page')

@section('content')
    <!-- Stats Badges -->
    <div class="d-flex justify-content-end align-items-center gap-2 mb-3 mt-3">
        
        {{-- <!-- On Target Badge -->
        <div class="bg-success card border-0 shadow-sm" id="badgeOnTargetBox">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-check-circle text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">On Target</small>
                        <h5 class="mb-0 fw-bold text-white" id="statOnTarget">{{ $summary['machines_on_target'] ?? 0 }}</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Warning Badge -->
        <div class="bg-warning card border-0 shadow-sm" id="badgeWarningBox">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-exclamation-triangle text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Warning</small>
                        <h5 class="mb-0 fw-bold text-white" id="statWarning">{{ $summary['machines_warning'] ?? 0 }}</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Critical Badge -->
        <div class="bg-danger card border-0 shadow-sm" id="badgeCriticalBox">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-x-circle text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">Critical</small>
                        <h5 class="mb-0 fw-bold text-white" id="statCritical">{{ $summary['machines_critical'] ?? 0 }}</h5>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- No Data Badge -->
        <div class="bg-secondary card border-0 shadow-sm me-3" id="badgeNoDataBox">
            <div class="card-body p-1">
                <div class="d-flex align-items-center">
                    <div class="bg-white bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-slash-circle text-white fs-5"></i>
                    </div>
                    <div>
                        <small class="text-white d-block fw-bold me-3" style="font-size: 0.7rem;">No Data</small>
                        <h5 class="mb-0 fw-bold text-white" id="statNoData">{{ $summary['machines_no_data'] ?? 0 }}</h5>
                    </div>
                </div>
            </div>
        </div> --}}
        
    </div>

    <!-- Filter & Summary Bar -->
    <div id="filterSummaryBox">
        <div class="card border-0 shadow-sm mb-3 ms-3 me-3" style="border-radius:0; background-color:#000000; outline:2px solid #ffffff;">
            <div class="card-body py-2 px-4">
                <div class="d-flex align-items-center justify-content-center gap-3 flex-wrap">
                    
                    <!-- Filter Tanggal -->
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 0.9rem;">Tanggal:</small>
                        <input type="date" id="filterTanggal" class="form-control form-control-sm bg-dark text-white border-secondary" 
                               value="{{ $tanggal }}" style="width: 140px;">
                    </div>

                    <div class="vr bg-secondary" style="height: 30px; opacity: 0.5;"></div>

                    <!-- Filter Shift -->
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 0.9rem;">Shift:</small>
                        <select id="filterShift" class="form-select form-select-sm bg-dark text-white border-secondary" style="width: 120px;">
                            <option value="1" {{ $shift == '1' ? 'selected' : '' }}>Shift 1</option>
                            <option value="2" {{ $shift == '2' ? 'selected' : '' }}>Shift 2</option>
                        </select>
                    </div>

                    <div class="vr bg-secondary" style="height: 30px; opacity: 0.5;"></div>

                    <!-- Jam Berjalan -->
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 0.9rem;">Jam Berjalan:</small>
                        <span class="fw-bold text-info" id="elapsedHours">{{ number_format($elapsedHours ?? 0, 1) }}</span>
                        <small class="text-white">jam</small>
                    </div>

                    <div class="vr bg-secondary" style="height: 30px; opacity: 0.5;"></div>

                    <!-- Summary -->
                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 0.9rem;">Total Mesin:</small>
                        <span class="fw-bold text-white" id="sumMachines">{{ $summary['total_machines'] ?? 0 }}</span>
                    </div>

                    <div class="vr bg-secondary" style="height: 30px; opacity: 0.5;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 0.9rem;">Planning:</small>
                        <span class="fw-bold text-primary" id="sumPlanning">{{ number_format($summary['total_planning'] ?? 0) }}</span>
                    </div>

                    <div class="vr bg-secondary" style="height: 30px; opacity: 0.5;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 0.9rem;">Actual:</small>
                        <span class="fw-bold text-success" id="sumActual">{{ number_format($summary['total_actual'] ?? 0) }}</span>
                    </div>

                    <div class="vr bg-secondary" style="height: 30px; opacity: 0.5;"></div>

                    <div class="d-flex align-items-center gap-2">
                        <small class="text-white" style="font-size: 0.9rem;">Avg Eff:</small>
                        <span class="fw-bold" id="sumEff" style="color: {{ ($summary['avg_eff'] ?? 0) >= 90 ? '#10b981' : (($summary['avg_eff'] ?? 0) >= 70 ? '#f59e0b' : '#ef4444') }}">
                            {{ $summary['avg_eff'] ?? 0 }}%
                        </span>
                    </div>

                    <div class="vr bg-secondary" style="height: 30px; opacity: 0.5;"></div>

                    <!-- Server Time -->
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-clock-fill text-white"></i>
                        <span class="text-white fw-bold" id="serverTime">{{ now()->format('H:i:s') }}</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive p-0 mt-0" style="max-height: 70vh; overflow-y: auto;">
        <table class="table table-compact w-100 mt-1" id="laneTable">
            <thead class="sticky-top">
                <tr class="fs-5">
                    <th>Machine</th>
                    <th>Shift</th>
                    <th>GSPH Avg</th>
                    <th>Planning All</th>
                    <th>Plan/Hours</th>
                    <th>Actual</th>
                    
                    <th>Eff %</th>
                    <th>Eff All %</th>
                    
                </tr>
            </thead>
            <tbody id="laneTableBody">
                @forelse($data as $lane)
                    @php
                        $rowClass = match($lane['status']) {
                            'critical' => 'table-danger-subtle',
                            'warning' => 'table-warning-subtle',
                            'on-target' => '',
                            default => 'table-secondary-subtle'
                        };
                        $effColor = $lane['eff'] >= 90 ? 'success' : ($lane['eff'] >= 70 ? 'warning' : 'danger');
                        $effAllColor = $lane['eff_all'] >= 90 ? 'success' : ($lane['eff_all'] >= 70 ? 'warning' : 'danger');
                        $varianceColor = $lane['variance'] >= 0 ? 'success' : 'danger';
                        $varianceIcon = $lane['variance'] >= 0 ? '↑' : '↓';
                    @endphp
                    <tr class="fs-4 {{ $rowClass }} {{ $lane['status'] === 'critical' ? 'row-critical' : '' }}">
                        <td><strong>{{ $lane['machine'] }}</strong></td>
                        <td>{{ $lane['shift'] }}</td>
                        <td>{{ number_format($lane['gsph_avg'], 1) }}</td>
                        <td>{{ number_format($lane['planning_all']) }}</td>
                        <td>{{ number_format($lane['plan_hours']) }}</td>
                        <td><strong>{{ number_format($lane['actual']) }}</strong></td>
                        
                        <td>
                            <span class="badge bg-{{ $effColor }} fw-bold px-3 py-2">
                                {{ number_format($lane['eff'], 1) }}%
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $effAllColor }} fw-bold px-3 py-2">
                                {{ number_format($lane['eff_all'], 1) }}%
                            </span>
                        </td>
                        
                    </tr>
                @empty
                    <tr class="mt-3">
                        <td colspan="11" class="text-center py-4">
                            <div class="text-muted">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">Belum ada data lane monitoring</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection

@push('styles')
<style>
    /* Critical row animation */
    @keyframes pulse-critical {
        0%, 100% { background-color: rgba(239, 68, 68, 0.1); }
        50% { background-color: rgba(239, 68, 68, 0.3); }
    }
    
    .row-critical {
        animation: pulse-critical 2s infinite;
    }
    
    /* Sticky header */
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: var(--bs-table-bg, #fff);
    }
    
    /* Table subtle colors */
    .table-danger-subtle {
        background-color: rgba(239, 68, 68, 0.1) !important;
    }
    
    .table-warning-subtle {
        background-color: rgba(245, 158, 11, 0.1) !important;
    }
    
    .table-secondary-subtle {
        background-color: rgba(107, 114, 128, 0.1) !important;
    }
    
    /* Badge delay animation (same as preparations) */
    .badge-delay {
        animation: blink 1s infinite;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {

    const REFRESH_INTERVAL = 3000; // 3 detik
    let countdownSeconds = REFRESH_INTERVAL / 1000;

    function startCountdown() {
        countdownSeconds = REFRESH_INTERVAL / 1000;

        const timer = setInterval(() => {
            countdownSeconds--;

            if (countdownSeconds <= 0 || document.hidden) {
                clearInterval(timer);
            }
        }, 1000);
    }

    startCountdown();

    // Build URL with current filters
    function getFilteredUrl() {
        const tanggal = $('#filterTanggal').val();
        const shift = $('#filterShift').val();
        return location.pathname + `?tanggal=${tanggal}&shift=${shift}`;
    }

    // Ajax refresh table only
    setInterval(() => {

        if (!document.hidden) {
            const url = getFilteredUrl();

            // Refresh TABLE
            $("#laneTable").load(url + " #laneTable>*");
            
            // Refresh Badges
            $("#badgeOnTargetBox").load(url + " #badgeOnTargetBox>*");
            $("#badgeWarningBox").load(url + " #badgeWarningBox>*");
            $("#badgeCriticalBox").load(url + " #badgeCriticalBox>*");
            $("#badgeNoDataBox").load(url + " #badgeNoDataBox>*");
            
            // Refresh Filter Summary (untuk update server time, elapsed hours, summary)
            $("#filterSummaryBox").load(url + " #filterSummaryBox>*");
        }

        startCountdown();

    }, REFRESH_INTERVAL);

    // Filter change - reload page with new params
    $('#filterTanggal, #filterShift').on('change', function() {
        const url = getFilteredUrl();
        window.location.href = url;
    });

});
</script>
@endpush