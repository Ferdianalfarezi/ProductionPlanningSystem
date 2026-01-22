<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 hidden items-center justify-center z-50 p-4 modal-backdrop">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg transform transition-all max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white">
            <h2 class="text-xl font-bold text-gray-900">Import Data Items</h2>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Modal Body -->
        <form id="importForm" class="p-6 space-y-4" enctype="multipart/form-data">
            @csrf

            <!-- Download Template -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-blue-800">Petunjuk Import</h4>
                        <ul class="mt-2 text-sm text-blue-700 space-y-1">
                            <li>• Kolom <strong>Part_No</strong> dan <strong>Machine</strong> wajib diisi</li>
                            <li>• Kolom <strong>Machine</strong> harus sesuai dengan nama mesin di Master Mesin</li>
                            <li>• Kolom <strong>Tonage</strong> akan diambil otomatis dari Master Mesin</li>
                        </ul>
                        <a href="{{ route('items.template') }}" 
                           class="inline-flex items-center mt-3 text-sm font-medium text-blue-600 hover:text-blue-800 transition">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download Template Excel
                        </a>
                    </div>
                </div>
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">File Excel <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="file" name="file" id="importFile" required accept=".xlsx,.xls"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-black focus:ring-2 focus:ring-black transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800 file:cursor-pointer">
                </div>
                <p class="text-xs text-gray-500 mt-1">Format: .xlsx atau .xls (Max: 10MB)</p>
                <span class="text-red-500 text-sm error-message" id="error-import-file"></span>
            </div>

            <!-- File Info -->
            <div id="fileInfo" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" id="fileName">-</p>
                        <p class="text-xs text-gray-500" id="fileSize">-</p>
                    </div>
                </div>
            </div>

            <!-- Import Errors -->
            <div id="importErrors" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-red-800">Beberapa data tidak dapat diimport:</h4>
                        <ul class="mt-2 text-sm text-red-700 space-y-1 list-disc list-inside" id="importErrorList">
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div class="flex space-x-3 pt-4">
                <button type="button" onclick="closeImportModal()"
                    class="flex-1 bg-gray-200 text-gray-700 px-4 py-3 rounded-lg font-semibold hover:bg-gray-300 transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 bg-green-600 text-white px-4 py-3 rounded-lg font-semibold hover:bg-green-700 transition flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import
                </button>
            </div>
        </form>
    </div>
</div>