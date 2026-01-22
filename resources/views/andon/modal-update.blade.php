<!-- Modal Update Actual -->
<div id="updateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-xl w-full max-w-md modal-fade-in">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Update Actual Quantity</h3>
                <button onclick="closeUpdateModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="updateForm">
                @csrf
                <input type="hidden" id="updateId" name="id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Part No</label>
                        <div id="partNoDisplay" class="text-lg font-semibold text-gray-900"></div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Plan Quantity</label>
                            <div id="planQtyDisplay" class="text-lg font-bold text-blue-600"></div>
                            <div class="text-xs text-gray-500" id="planDetailDisplay"></div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Current Actual</label>
                            <div id="currentActualDisplay" class="text-lg font-bold text-green-600">0</div>
                        </div>
                    </div>
                    
                    <div>
                        <label for="actual_qty" class="block text-sm font-medium text-gray-700 mb-2">
                            New Actual Quantity <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            id="actual_qty" 
                            name="actual_qty"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-black focus:border-black transition"
                            placeholder="Masukkan actual quantity"
                            min="0"
                            required
                        >
                        <div id="error-actual" class="error-message text-red-500 text-sm mt-1"></div>
                    </div>
                    
                    <!-- Preview Efficiency -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Efficiency</label>
                                <p class="text-xs text-gray-500">Berdasarkan input actual</p>
                            </div>
                            <div id="estimatedEfficiency" class="text-lg font-bold text-gray-900">
                                0%
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button 
                        type="button"
                        onclick="closeUpdateModal()"
                        class="px-4 py-2.5 text-gray-700 font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit"
                        class="bg-black text-white px-6 py-2.5 rounded-lg font-medium hover:bg-gray-800 transition"
                    >
                        Simpan Actual
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
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