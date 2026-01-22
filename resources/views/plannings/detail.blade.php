<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 hidden items-center justify-center z-50 p-4 modal-backdrop">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl transform transition-all max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
            <h2 class="text-xl font-bold text-gray-900">Detail Planning</h2>
            <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-6">
            <!-- Part Information -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Part Information
                </h3>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Part No FG</p>
                        <p class="font-semibold text-gray-900" id="detailPartNoFg">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Part No Parent</p>
                        <p class="font-semibold text-gray-900" id="detailPartNoParent">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Part No Child</p>
                        <p class="font-semibold text-gray-900" id="detailPartNoChild">-</p>
                    </div>
                </div>
            </div>

            <!-- Production Info -->
            <div class="bg-blue-50 rounded-lg p-4">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Production Info
                </h3>
                <div class="grid grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Judgement</p>
                        <p class="font-semibold text-gray-900" id="detailJudgement">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Priority</p>
                        <p class="font-semibold text-gray-900" id="detailPriority">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Status</p>
                        <p class="font-semibold text-gray-900" id="detailStatus">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Actual</p>
                        <p class="font-semibold text-gray-900" id="detailActual">-</p>
                    </div>
                </div>
            </div>

            <!-- Location Info -->
            <div class="bg-green-50 rounded-lg p-4">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Location Info
                </h3>
                <div class="grid grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Store</p>
                        <p class="font-semibold text-gray-900" id="detailStore">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Process</p>
                        <p class="font-semibold text-gray-900" id="detailProcess">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Line</p>
                        <p class="font-semibold text-gray-900" id="detailLine">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Rack No</p>
                        <p class="font-semibold text-gray-900" id="detailRackNo">-</p>
                    </div>
                </div>
            </div>

            <!-- Quantity Info -->
            <div class="bg-yellow-50 rounded-lg p-4">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Quantity Info
                </h3>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Qty KBN</p>
                        <p class="font-semibold text-gray-900" id="detailQtyKbn">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Qty Consume</p>
                        <p class="font-semibold text-gray-900" id="detailQtyConsume">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Qty Lot</p>
                        <p class="font-semibold text-gray-900" id="detailQtyLot">-</p>
                    </div>
                </div>
            </div>

            <!-- Stock Info -->
            <div class="bg-orange-50 rounded-lg p-4">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    Stock Info
                </h3>
                <div class="grid grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Stock Min</p>
                        <p class="font-semibold text-gray-900" id="detailStockMin">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Stock Max</p>
                        <p class="font-semibold text-gray-900" id="detailStockMax">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Stock Realtime</p>
                        <p class="font-semibold text-gray-900" id="detailStockRealtime">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Total Prod</p>
                        <p class="font-semibold text-gray-900" id="detailTotalProd">-</p>
                    </div>
                </div>
            </div>

            <!-- Lot & Calculation -->
            <div class="bg-indigo-50 rounded-lg p-4">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    Lot & Calculation
                </h3>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Lot No</p>
                        <p class="font-semibold text-gray-900" id="detailLotNo">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Calc By</p>
                        <p class="font-semibold text-gray-900" id="detailCalcBy">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Reason</p>
                        <p class="font-semibold text-gray-900" id="detailReason">-</p>
                    </div>
                </div>
            </div>

            <!-- Time Info -->
            <div class="bg-red-50 rounded-lg p-4">
                <h3 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Time Info
                </h3>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Pulling Time</p>
                        <p class="font-semibold text-gray-900" id="detailPullingTime">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Start Time</p>
                        <p class="font-semibold text-gray-900" id="detailStartTime">-</p>
                    </div>
                    <div>
                        <p class="text-gray-500">End Time</p>
                        <p class="font-semibold text-gray-900" id="detailEndTime">-</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <button type="button" onclick="closeDetailModal()"
                class="w-full bg-black text-white px-4 py-3 rounded-lg font-semibold hover:bg-gray-800 transition">
                Tutup
            </button>
        </div>
    </div>
</div>