<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 hidden items-center justify-center z-50 p-4 modal-backdrop">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
            <h2 class="text-xl font-bold text-gray-900">Edit Planning</h2>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="editForm" class="p-6 space-y-4">
            @csrf
            <input type="hidden" id="editPlanningId" name="id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Part No FG -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Part No FG <span class="text-red-500">*</span></label>
                    <input type="text" name="part_no_fg" id="editPartNoFg" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-part_no_fg"></span>
                </div>

                <!-- Part No Parent -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Part No Parent</label>
                    <input type="text" name="part_no_parent" id="editPartNoParent"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-part_no_parent"></span>
                </div>

                <!-- Part No Child -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Part No Child</label>
                    <input type="text" name="part_no_child" id="editPartNoChild"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-part_no_child"></span>
                </div>

                <!-- Judgement -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judgement</label>
                    <input type="text" name="judgement" id="editJudgement"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-judgement"></span>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                    <input type="number" name="priority" id="editPriority" min="0"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-priority"></span>
                </div>

                <!-- Store -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Store</label>
                    <input type="text" name="store" id="editStore"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-store"></span>
                </div>

                <!-- Process -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Process</label>
                    <input type="text" name="process" id="editProcess"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-process"></span>
                </div>

                <!-- Line -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Line</label>
                    <input type="text" name="line" id="editLine"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-line"></span>
                </div>

                <!-- Rack No -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rack No</label>
                    <input type="text" name="rack_no" id="editRackNo"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-rack_no"></span>
                </div>

                <!-- Qty KBN -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Qty KBN</label>
                    <input type="number" name="qty_kbn" id="editQtyKbn" min="0"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-qty_kbn"></span>
                </div>

                <!-- Qty Lot -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Qty Lot</label>
                    <input type="number" name="qty_lot" id="editQtyLot" min="0"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-qty_lot"></span>
                </div>

                <!-- Total Prod -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Total Prod</label>
                    <input type="number" name="total_prod" id="editTotalProd" min="0"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-total_prod"></span>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status" id="editStatus"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                        <option value="">-- Pilih Status --</option>
                        <option value="Open">Open</option>
                        <option value="R">Running (R)</option>
                        <option value="J">Job (J)</option>
                        <option value="C">Complete (C)</option>
                    </select>
                    <span class="text-red-500 text-sm error-message" id="error-edit-status"></span>
                </div>

                <!-- Lot No -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Lot No</label>
                    <input type="text" name="lot_no" id="editLotNo"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <span class="text-red-500 text-sm error-message" id="error-edit-lot_no"></span>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeEditModal()"
                    class="flex-1 bg-gray-200 text-gray-700 px-4 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 bg-black text-white px-4 py-3 rounded-lg font-semibold hover:bg-gray-800 transition">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>