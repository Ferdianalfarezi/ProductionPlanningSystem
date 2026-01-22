<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 hidden items-center justify-center z-50 p-4 modal-backdrop">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl transform transition-all max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
            <h2 class="text-xl font-bold text-gray-900">Tambah Planning Baru</h2>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="createForm" class="p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Part No FG -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Part No FG <span class="text-red-500">*</span></label>
                    <input type="text" name="part_no_fg" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: 51321-BZ110">
                    <span class="text-red-500 text-sm error-message" id="error-create-part_no_fg"></span>
                </div>

                <!-- Part No Parent -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Part No Parent</label>
                    <input type="text" name="part_no_parent"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: 51321-BZ110">
                    <span class="text-red-500 text-sm error-message" id="error-create-part_no_parent"></span>
                </div>

                <!-- Part No Child -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Part No Child</label>
                    <input type="text" name="part_no_child"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: 51321-BZ110">
                    <span class="text-red-500 text-sm error-message" id="error-create-part_no_child"></span>
                </div>

                <!-- Judgement -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judgement</label>
                    <input type="text" name="judgement"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: Production">
                    <span class="text-red-500 text-sm error-message" id="error-create-judgement"></span>
                </div>

                <!-- Priority -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Priority</label>
                    <input type="number" name="priority" min="0"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: 1">
                    <span class="text-red-500 text-sm error-message" id="error-create-priority"></span>
                </div>

                <!-- Store -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Store</label>
                    <input type="text" name="store"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: FINISH GOODS">
                    <span class="text-red-500 text-sm error-message" id="error-create-store"></span>
                </div>

                <!-- Process -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Process</label>
                    <input type="text" name="process"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: TD">
                    <span class="text-red-500 text-sm error-message" id="error-create-process"></span>
                </div>

                <!-- Line -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Line</label>
                    <input type="text" name="line"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: LINE 9">
                    <span class="text-red-500 text-sm error-message" id="error-create-line"></span>
                </div>

                <!-- Rack No -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rack No</label>
                    <input type="text" name="rack_no"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: E1-20-A">
                    <span class="text-red-500 text-sm error-message" id="error-create-rack_no"></span>
                </div>

                <!-- Qty KBN -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Qty KBN</label>
                    <input type="number" name="qty_kbn" min="0"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: 10">
                    <span class="text-red-500 text-sm error-message" id="error-create-qty_kbn"></span>
                </div>

                <!-- Qty Lot -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Qty Lot</label>
                    <input type="number" name="qty_lot" min="0"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: 24">
                    <span class="text-red-500 text-sm error-message" id="error-create-qty_lot"></span>
                </div>

                <!-- Total Prod -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Total Prod</label>
                    <input type="number" name="total_prod" min="0"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: 48">
                    <span class="text-red-500 text-sm error-message" id="error-create-total_prod"></span>
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                    <select name="status"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                        <option value="">-- Pilih Status --</option>
                        <option value="Open">Open</option>
                        <option value="R">Running (R)</option>
                        <option value="J">Job (J)</option>
                        <option value="C">Complete (C)</option>
                    </select>
                    <span class="text-red-500 text-sm error-message" id="error-create-status"></span>
                </div>

                <!-- Lot No -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Lot No</label>
                    <input type="text" name="lot_no"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                        placeholder="Contoh: S9 22 A 26 A">
                    <span class="text-red-500 text-sm error-message" id="error-create-lot_no"></span>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeCreateModal()"
                    class="flex-1 bg-gray-200 text-gray-700 px-4 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 bg-black text-white px-4 py-3 rounded-lg font-semibold hover:bg-gray-800 transition">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>