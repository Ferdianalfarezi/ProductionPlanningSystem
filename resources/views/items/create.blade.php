<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 hidden items-center justify-center z-50 p-4 modal-backdrop">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
            <h2 class="text-xl font-bold text-gray-900">Tambah Item Baru</h2>
            <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="createForm" class="p-6 space-y-4">
            @csrf

            <!-- Part No -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Part No <span class="text-red-500">*</span></label>
                <input type="text" name="part_no" required
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                    placeholder="Contoh: 52566-BZ010">
                <span class="text-red-500 text-sm error-message" id="error-create-part_no"></span>
            </div>

            <!-- Line -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Line</label>
                <input type="text" name="line"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                    placeholder="Contoh: Line 9">
                <span class="text-red-500 text-sm error-message" id="error-create-line"></span>
            </div>

            <!-- Machine (Mesin) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Machine <span class="text-red-500">*</span></label>
                <select name="mesin_id" required
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition">
                    <option value="">-- Pilih Mesin --</option>
                    @foreach($mesinList as $mesin)
                        <option value="{{ $mesin->id }}">{{ $mesin->nama }} ({{ $mesin->tonase }})</option>
                    @endforeach
                </select>
                <span class="text-red-500 text-sm error-message" id="error-create-mesin_id"></span>
            </div>

            <!-- Process -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Process</label>
                <input type="text" name="process"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                    placeholder="Contoh: 2/9">
                <span class="text-red-500 text-sm error-message" id="error-create-process"></span>
            </div>

            <!-- Process Name -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Process Name</label>
                <input type="text" name="process_name"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                    placeholder="Contoh: BE, PI">
                <span class="text-red-500 text-sm error-message" id="error-create-process_name"></span>
            </div>

            <!-- GSPH -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">GSPH</label>
                <input type="number" name="gsph" step="0.000001" min="0"
                    class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition"
                    placeholder="Contoh: 352.666667">
                <span class="text-red-500 text-sm error-message" id="error-create-gsph"></span>
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