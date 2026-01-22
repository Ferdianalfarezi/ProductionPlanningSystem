// Utility functions
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function showSuccess(message, callback) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        showConfirmButton: false,
        timer: 1500
    }).then(() => {
        if (callback && typeof callback === 'function') {
            callback();
        }
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: message,
    });
}

function showGlobalLoading(message = 'Memproses data...') {
    const loadingEl = document.getElementById('globalLoading');
    const loadingText = document.getElementById('loadingText');
    if (loadingEl && loadingText) {
        loadingText.textContent = message;
        loadingEl.classList.remove('hidden');
    }
}

function hideGlobalLoading() {
    const loadingEl = document.getElementById('globalLoading');
    if (loadingEl) {
        loadingEl.classList.add('hidden');
    }
}

function showTableLoading(mesinId, show = true) {
    const loadingEl = document.getElementById(`table-loading-${mesinId}`);
    const saveBtn = document.getElementById(`save-btn-${mesinId}`);
    const successEl = document.getElementById(`save-success-${mesinId}`);
    
    if (loadingEl) loadingEl.classList.toggle('hidden', !show);
    if (saveBtn) saveBtn.classList.toggle('hidden', show);
    if (successEl && show) successEl.classList.add('hidden');
}

function showTableSuccess(mesinId) {
    const successEl = document.getElementById(`save-success-${mesinId}`);
    const saveBtn = document.getElementById(`save-btn-${mesinId}`);
    
    if (successEl) {
        successEl.classList.remove('hidden');
        if (saveBtn) saveBtn.classList.add('hidden');
        
        // Hide success message after 2 seconds
        setTimeout(() => {
            successEl.classList.add('hidden');
        }, 2000);
    }
}

// Global object untuk menyimpan perubahan lokal per mesin
let localChanges = {};

// Initialize semua tabel saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    // Reset local changes
    localChanges = {};
    
    // Inisialisasi event listeners
    const updateForm = document.getElementById('updateForm');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleUpdateSubmit.call(this, e);
        });
    }
    
    // Actual input change for efficiency calculation
    const actualInput = document.getElementById('actual_qty');
    if (actualInput) {
        actualInput.addEventListener('input', calculateEstimatedEfficiency);
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeUpdateModal();
        }
        if (e.key === 'r' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            refreshData();
        }
        if (e.key === 's' && (e.ctrlKey || e.metaKey)) {
            e.preventDefault();
            syncData();
        }
    });
    
    // Initialize drag & drop
    initializeDragAndDrop();
});

// ============================================
// LOCAL CHANGES FUNCTIONS
// ============================================

// Function untuk inisialisasi perubahan lokal
function initializeTableForLocalChanges(mesinName, mesinId) {
    if (!localChanges[mesinName]) {
        const container = document.querySelector(`.mesin-container[data-mesin="${mesinName}"]`);
        const shift = container?.querySelector('.sortable-container')?.dataset.shift || '1';
        
        localChanges[mesinName] = {
            updates: [],       // Untuk status aktif/inaktif
            sortOrder: [],     // Untuk urutan
            shiftChanged: false, // Untuk perubahan shift
            originalShift: shift,
            mesinId: mesinId
        };
    }
    return localChanges[mesinName];
}

// Toggle active status dengan perubahan lokal
function toggleActiveLocal(id, checkbox, mesinId, mesinName) {
    const isActive = checkbox.checked;
    const row = checkbox.closest('tr');
    
    // Initialize local changes untuk mesin ini
    const changes = initializeTableForLocalChanges(mesinName, mesinId);
    
    // Update tampilan lokal
    updateRowDisplay(row, isActive);
    
    // Update statistik count
    updateActiveCount(mesinId);
    
    // Simpan perubahan ke localChanges
    const existingIndex = changes.updates.findIndex(u => u.id === id);
    if (existingIndex > -1) {
        changes.updates[existingIndex].is_active = isActive;
    } else {
        changes.updates.push({
            id: id,
            is_active: isActive
        });
    }
    
    // Tampilkan tombol simpan untuk mesin ini
    showSaveButton(mesinName, mesinId);
}

// Update tampilan row
function updateRowDisplay(row, isActive) {
    if (isActive) {
        row.classList.remove('inactive-row');
        row.classList.add('hover:bg-gray-50');
        
        // Update data attribute
        row.dataset.isActive = 'true';
        
        // Update kelas untuk styling
        updateRowClasses(row, isActive);
        
        // Enable update button
        const updateBtn = row.querySelector('button[onclick*="openUpdateModal"]');
        if (updateBtn) {
            updateBtn.disabled = false;
            updateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    } else {
        row.classList.add('inactive-row');
        row.classList.remove('hover:bg-gray-50');
        
        // Update data attribute
        row.dataset.isActive = 'false';
        
        // Update kelas untuk styling
        updateRowClasses(row, isActive);
        
        // Disable update button
        const updateBtn = row.querySelector('button[onclick*="openUpdateModal"]');
        if (updateBtn) {
            updateBtn.disabled = true;
            updateBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
}

// Update kelas styling untuk row
function updateRowClasses(row, isActive) {
    const textElements = row.querySelectorAll('.text-sm, .text-lg, .text-xs');
    textElements.forEach(el => {
        // Remove all color classes
        el.classList.remove('text-gray-900', 'text-gray-600', 'text-gray-500', 'text-gray-400', 'text-gray-300', 
                           'text-green-600', 'text-yellow-600', 'text-red-600');
        
        if (isActive) {
            // Restore original colors based on element's role
            if (el.classList.contains('font-semibold') || el.classList.contains('sequence-number')) {
                el.classList.add('text-gray-900');
            } else if (el.classList.contains('font-bold') && el.textContent.match(/\d/)) {
                el.classList.add('text-green-600');
            } else {
                el.classList.add('text-gray-600');
            }
        } else {
            // Gray out for inactive
            if (el.classList.contains('font-semibold') || el.classList.contains('font-bold')) {
                el.classList.add('text-gray-500');
            } else {
                el.classList.add('text-gray-400');
            }
        }
    });
    
    // Update efficiency badge
    const badge = row.querySelector('.efficiency-badge');
    if (badge) {
        badge.classList.remove('bg-green-100', 'text-green-800', 
                              'bg-yellow-100', 'text-yellow-800',
                              'bg-red-100', 'text-red-800',
                              'bg-gray-100', 'text-gray-500');
        
        if (isActive) {
            const efficiency = parseFloat(badge.textContent) || 0;
            if (efficiency >= 90) {
                badge.classList.add('bg-green-100', 'text-green-800');
            } else if (efficiency >= 70) {
                badge.classList.add('bg-yellow-100', 'text-yellow-800');
            } else if (efficiency > 0) {
                badge.classList.add('bg-red-100', 'text-red-800');
            } else {
                badge.classList.add('bg-gray-100', 'text-gray-800');
            }
        } else {
            badge.classList.add('bg-gray-100', 'text-gray-500');
        }
    }
}

// Update statistik count
function updateActiveCount(mesinId) {
    const container = document.querySelector(`.mesin-container[data-mesin-id="${mesinId}"]`);
    if (!container) return;
    
    const rows = container.querySelectorAll('.sortable-row');
    let activeCount = 0;
    let inactiveCount = 0;
    
    rows.forEach(row => {
        const checkbox = row.querySelector('.status-checkbox');
        if (checkbox) {
            if (checkbox.checked) {
                activeCount++;
            } else {
                inactiveCount++;
            }
        }
    });
    
    const activeCountEl = document.getElementById(`active-count-${mesinId}`);
    const inactiveCountEl = document.getElementById(`inactive-count-${mesinId}`);
    
    if (activeCountEl) activeCountEl.textContent = activeCount;
    if (inactiveCountEl) inactiveCountEl.textContent = inactiveCount;
}

// Tampilkan tombol simpan
function showSaveButton(mesinName, mesinId) {
    const saveBtn = document.getElementById(`save-btn-${mesinId}`);
    const discardBtn = document.getElementById(`discard-btn-${mesinId}`);
    
    if (saveBtn) {
        saveBtn.classList.remove('hidden');
        // Remove success message if visible
        const successEl = document.getElementById(`save-success-${mesinId}`);
        if (successEl) successEl.classList.add('hidden');
    }
    
    if (discardBtn) discardBtn.classList.remove('hidden');
}

// Update shift dengan perubahan lokal
function updateShiftLocal(mesinName, checkbox) {
    const newShift = checkbox.checked ? '2' : '1';
    const mesinContainer = checkbox.closest('.mesin-container');
    const mesinId = mesinContainer?.dataset.mesinId;
    
    if (!mesinId) return;
    
    // Initialize local changes
    const changes = initializeTableForLocalChanges(mesinName, mesinId);
    
    // Update badge lokal
    const shiftBadge = document.getElementById(`shift-badge-${mesinId}`);
    if (shiftBadge) {
        shiftBadge.textContent = newShift === '1' ? '07:00' : '19:00';
        shiftBadge.className = `shift-badge shift-${newShift}`;
    }
    
    // Update data-shift di container
    const container = document.querySelector(`.mesin-container[data-mesin="${mesinName}"]`);
    const sortableContainer = container?.querySelector('.sortable-container');
    if (sortableContainer) {
        sortableContainer.dataset.shift = newShift;
    }
    
    // Tandai perubahan shift
    changes.shiftChanged = (newShift !== changes.originalShift);
    
    // Tampilkan tombol simpan
    showSaveButton(mesinName, mesinId);
}

// Mark all active (lokal)
function markAllActiveLocal(mesinName, shift, mesinId) {
    const container = document.querySelector(`.mesin-container[data-mesin="${mesinName}"]`);
    const checkboxes = container.querySelectorAll('.status-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
        toggleActiveLocal(parseInt(checkbox.closest('tr').dataset.id), checkbox, mesinId, mesinName);
    });
}

// Mark all inactive (lokal)
function markAllInactiveLocal(mesinName, shift, mesinId) {
    const container = document.querySelector(`.mesin-container[data-mesin="${mesinName}"]`);
    const checkboxes = container.querySelectorAll('.status-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        toggleActiveLocal(parseInt(checkbox.closest('tr').dataset.id), checkbox, mesinId, mesinName);
    });
}

// Discard changes
function discardChanges(mesinName, mesinId) {
    if (!confirm('Batalkan semua perubahan untuk mesin ini?')) return;
    
    const changes = localChanges[mesinName];
    if (!changes) return;
    
    // Reload hanya tabel ini
    const container = document.querySelector(`.mesin-container[data-mesin="${mesinName}"]`);
    if (container) {
        // Sembunyikan tombol simpan dan discard
        const saveBtn = document.getElementById(`save-btn-${mesinId}`);
        const discardBtn = document.getElementById(`discard-btn-${mesinId}`);
        if (saveBtn) saveBtn.classList.add('hidden');
        if (discardBtn) discardBtn.classList.add('hidden');
        
        // Reset shift jika ada perubahan
        if (changes.shiftChanged) {
            const checkbox = container.querySelector('.toggle-switch input');
            const shiftBadge = document.getElementById(`shift-badge-${mesinId}`);
            if (checkbox && shiftBadge) {
                checkbox.checked = changes.originalShift === '2';
                shiftBadge.textContent = changes.originalShift === '1' ? '07:00' : '19:00';
                shiftBadge.className = `shift-badge shift-${changes.originalShift}`;
                
                const sortableContainer = container.querySelector('.sortable-container');
                if (sortableContainer) {
                    sortableContainer.dataset.shift = changes.originalShift;
                }
            }
        }
        
        // Reload data untuk mesin ini saja
        fetchMesinData(mesinName, mesinId);
    }
    
    // Remove from local changes
    delete localChanges[mesinName];
}

// Fetch data untuk satu mesin
async function fetchMesinData(mesinName, mesinId) {
    showTableLoading(mesinId, true);
    
    try {
        const response = await fetch(`/preview-andon/get-mesin-data?mesin=${encodeURIComponent(mesinName)}`, {
            headers: {
                'Accept': 'application/json',
            }
        });
        
        const responseData = await response.json();
        console.log('Fetch mesin data response:', responseData);
        
        if (response.ok && responseData.success) {
            updateTableWithData(mesinName, mesinId, responseData);
        } else {
            throw new Error(responseData.message || 'Gagal memuat data');
        }
    } catch (error) {
        console.error('Error fetching mesin data:', error);
        showError('Gagal memuat data mesin: ' + error.message);
        
        // Fallback: reload the whole page
        setTimeout(() => {
            if (confirm('Gagal memuat data. Muat ulang halaman?')) {
                location.reload();
            }
        }, 2000);
    } finally {
        showTableLoading(mesinId, false);
    }
}

// Update tabel dengan data baru
function updateTableWithData(mesinName, mesinId, responseData) {
    const container = document.querySelector(`.mesin-container[data-mesin="${mesinName}"]`);
    if (!container || !responseData.success) return;
    
    const tbody = container.querySelector('.sortable-tbody');
    if (!tbody) return;
    
    // Clear existing rows
    tbody.innerHTML = '';
    
    // Get data from response
    const data = responseData.data || [];
    
    // Add new rows
    data.forEach((row, index) => {
        const rowHtml = createTableRow(row, index, mesinName, mesinId, data.length);
        tbody.insertAdjacentHTML('beforeend', rowHtml);
    });
    
    // Update statistics from response
    if (responseData.stats) {
        const activeCountEl = document.getElementById(`active-count-${mesinId}`);
        const inactiveCountEl = document.getElementById(`inactive-count-${mesinId}`);
        
        if (activeCountEl) activeCountEl.textContent = responseData.stats.active || 0;
        if (inactiveCountEl) inactiveCountEl.textContent = responseData.stats.inactive || 0;
    } else {
        // Fallback: recalculate
        updateActiveCount(mesinId);
    }
    
    // Reinitialize drag & drop untuk tabel ini
    initializeDragAndDropForTable(mesinName);
}

// Create table row HTML - update parameter
function createTableRow(row, index, mesinName, mesinId, totalRows) {
    const shift = document.querySelector(`.mesin-container[data-mesin="${mesinName}"] .sortable-container`)?.dataset.shift || '1';
    const isLastRow = index === totalRows - 1;
    
    // Format calculated times
    const calculatedStart = row.calculated_start ? 
        formatDateTime(row.calculated_start) : 
        '<span class="text-gray-400">-</span>';
    
    const calculatedFinish = row.calculated_finish ? 
        formatDateTime(row.calculated_finish) : 
        '<span class="text-gray-400">-</span>';
    
    // Determine efficiency badge classes
    const eff = parseFloat(row.efficiency) || 0;
    let badgeClass = 'efficiency-badge ';
    
    if (!row.is_active) {
        badgeClass += 'bg-gray-100 text-gray-500';
    } else if (eff >= 90) {
        badgeClass += 'bg-green-100 text-green-800';
    } else if (eff >= 70) {
        badgeClass += 'bg-yellow-100 text-yellow-800';
    } else if (eff > 0) {
        badgeClass += 'bg-red-100 text-red-800';
    } else {
        badgeClass += 'bg-gray-100 text-gray-800';
    }
    
    const efficiencyBadge = `<span class="${badgeClass}">${eff.toFixed(1)}%</span>`;
    
    return `
        <tr 
            class="${!row.is_active ? 'inactive-row' : 'hover:bg-gray-50'} transition sortable-row border-b border-gray-200" 
            data-id="${row.id}"
            data-is-active="${row.is_active}"
            draggable="true"
            id="row-${row.id}"
        >
            <td class="px-4 py-3 whitespace-nowrap text-center">
                <input 
                    type="checkbox" 
                    ${row.is_active ? 'checked' : ''}
                    onchange="toggleActiveLocal(${row.id}, this, ${mesinId}, '${mesinName}')"
                    class="status-checkbox"
                    title="${row.is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan'}"
                    data-row-id="${row.id}"
                >
            </td>
            
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="flex items-center space-x-2">
                    <div class="drag-handle cursor-move" title="Drag untuk mengubah urutan" data-mesin="${mesinName}">
                        <svg class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                        </svg>
                    </div>
                    
                    <span class="text-sm font-medium text-gray-900 sequence-number bg-gray-100 px-2 py-1 rounded min-w-[32px] text-center" data-row-id="${row.id}">
                        ${row.sort_order || (index + 1)}
                    </span>
                    
                    <div class="flex flex-col space-y-1">
                        <button 
                            onclick="moveItemUpLocal(${row.id}, '${mesinName}', '${shift}', ${mesinId})"
                            class="sort-btn up-btn"
                            ${index === 0 ? 'disabled' : ''}
                            title="Pindah ke atas"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                        <button 
                            onclick="moveItemDownLocal(${row.id}, '${mesinName}', '${shift}', ${mesinId})"
                            class="sort-btn down-btn"
                            ${isLastRow ? 'disabled' : ''}
                            title="Pindah ke bawah"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </td>
            
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="text-sm font-semibold ${!row.is_active ? 'text-gray-500' : 'text-gray-900'}">
                    ${row.part_no || '-'}
                </div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm ${!row.is_active ? 'text-gray-400' : 'text-gray-600'}">
                ${row.gsph ? parseFloat(row.gsph).toFixed(2) : '0.00'}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm ${!row.is_active ? 'text-gray-400' : 'text-gray-600'}">
                ${calculatedStart}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm ${!row.is_active ? 'text-gray-400' : 'text-gray-600'}">
                ${calculatedFinish}
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                <div class="text-sm font-semibold ${!row.is_active ? 'text-gray-500' : 'text-gray-900'}">
                    ${row.plan_qty ? parseInt(row.plan_qty).toLocaleString() : '0'}
                </div>
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                ${row.actual_qty > 0 ? 
                    `<div class="text-sm font-bold ${!row.is_active ? 'text-gray-400' : 'text-green-600'}">
                        ${parseInt(row.actual_qty).toLocaleString()}
                    </div>` :
                    `<div class="text-sm font-medium ${!row.is_active ? 'text-gray-300' : 'text-gray-400'} italic">
                        Belum diisi
                    </div>`
                }
            </td>
            <td class="px-4 py-3 whitespace-nowrap">
                ${efficiencyBadge}
            </td>
            <td class="px-4 py-3 whitespace-nowrap text-sm">
                <button 
                    onclick="openUpdateModal(${row.id})"
                    class="bg-black text-white px-3 py-1.5 rounded-lg text-xs font-medium hover:bg-gray-800 transition flex items-center space-x-1 ${!row.is_active ? 'opacity-50 cursor-not-allowed' : ''}"
                    ${!row.is_active ? 'disabled' : ''}
                    title="${!row.is_active ? 'Aktifkan data terlebih dahulu' : 'Update actual quantity'}"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    <span>Update</span>
                </button>
            </td>
        </tr>
    `;
}

// Helper functions
function formatDateTime(datetimeString) {
    const date = new Date(datetimeString);
    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getEfficiencyBadge(efficiency, isActive) {
    const eff = parseFloat(efficiency);
    let badgeClass = 'efficiency-badge ';
    
    if (!isActive) {
        badgeClass += 'bg-gray-100 text-gray-500';
    } else if (eff >= 90) {
        badgeClass += 'bg-green-100 text-green-800';
    } else if (eff >= 70) {
        badgeClass += 'bg-yellow-100 text-yellow-800';
    } else if (eff > 0) {
        badgeClass += 'bg-red-100 text-red-800';
    } else {
        badgeClass += 'bg-gray-100 text-gray-800';
    }
    
    return `<span class="${badgeClass}">${eff.toFixed(1)}%</span>`;
}

// Save table changes
async function saveTableChanges(mesinName, shift, mesinId) {
    const changes = localChanges[mesinName];
    if (!changes || (!changes.updates.length && !changes.shiftChanged && !changes.hasOrderChanged)) {
        alert('Tidak ada perubahan yang perlu disimpan');
        return;
    }
    
    showTableLoading(mesinId, true);
    
    try {
        // Prepare request data
        const requestData = {
            mesin_nama: mesinName,
            shift: shift,
            shift_changed: changes.shiftChanged || false
        };
        
        // Add updates if any
        if (changes.updates.length > 0) {
            requestData.updates = changes.updates;
        }
        
        // Add sort order if changed
        if (changes.hasOrderChanged) {
            const container = document.querySelector(`.mesin-container[data-mesin="${mesinName}"]`);
            const rows = container.querySelectorAll('.sortable-row');
            const sortOrder = Array.from(rows).map(row => parseInt(row.dataset.id));
            requestData.sort_order = sortOrder;
        }
        
        console.log('Sending bulk update:', requestData);
        
        const response = await fetch('/preview-andon/bulk-update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        });
        
        const responseData = await response.json();
        console.log('Bulk update response:', responseData);
        
        if (responseData.success) {
            // Update UI dengan data terbaru dari server
            updateTableWithData(mesinName, mesinId, responseData);
            
            // Reset local changes untuk mesin ini
            delete localChanges[mesinName];
            
            // Update original shift
            if (changes.shiftChanged) {
                changes.originalShift = shift;
            }
            
            // Sembunyikan tombol simpan dan discard
            const saveBtn = document.getElementById(`save-btn-${mesinId}`);
            const discardBtn = document.getElementById(`discard-btn-${mesinId}`);
            if (saveBtn) saveBtn.classList.add('hidden');
            if (discardBtn) discardBtn.classList.add('hidden');
            
            // Tampilkan pesan sukses
            showTableSuccess(mesinId);
            showSuccess('Perubahan berhasil disimpan!');
            
        } else {
            throw new Error(responseData.message || 'Gagal menyimpan perubahan');
        }
    } catch (error) {
        console.error('Error saving table changes:', error);
        showError('Gagal menyimpan perubahan: ' + error.message);
    } finally {
        showTableLoading(mesinId, false);
    }
}

// ============================================
// DRAG & DROP FUNCTIONS (Local Changes)
// ============================================

let draggedRow = null;
let dragStartY = 0;
let isDragging = false;

function initializeDragAndDrop() {
    // Event delegation untuk drag handle
    document.addEventListener('mousedown', handleMouseDown);
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
    
    // Initialize existing rows
    document.querySelectorAll('.drag-handle').forEach(handle => {
        handle.addEventListener('mousedown', startDragFromHandle);
    });
}

function initializeDragAndDropForTable(mesinName) {
    const container = document.querySelector(`.mesin-container[data-mesin="${mesinName}"]`);
    if (!container) return;
    
    container.querySelectorAll('.drag-handle').forEach(handle => {
        handle.addEventListener('mousedown', startDragFromHandle);
    });
}

function startDragFromHandle(e) {
    e.preventDefault();
    const handle = e.currentTarget;
    const row = handle.closest('.sortable-row');
    const mesinName = handle.dataset.mesin;
    
    if (!row || !mesinName) return;
    
    startDrag(row, e.clientY, mesinName);
}

function startDrag(row, clientY, mesinName) {
    draggedRow = row;
    dragStartY = clientY;
    isDragging = true;
    
    // Add dragging class
    row.classList.add('dragging');
    row.style.opacity = '0.5';
    
    // Add event listeners for the drag
    document.addEventListener('mousemove', handleMouseMove);
    document.addEventListener('mouseup', handleMouseUp);
}

function handleMouseDown(e) {
    // Only handle if clicking on drag handle
    if (!e.target.closest('.drag-handle')) return;
}

function handleMouseMove(e) {
    if (!isDragging || !draggedRow) return;
    
    e.preventDefault();
    
    // Get all rows in the same table
    const tableBody = draggedRow.closest('tbody');
    const rows = Array.from(tableBody.querySelectorAll('.sortable-row'));
    const draggedIndex = rows.indexOf(draggedRow);
    
    // Calculate position
    const mouseY = e.clientY;
    const tableRect = tableBody.getBoundingClientRect();
    const relativeY = mouseY - tableRect.top;
    
    // Find drop position
    let targetIndex = -1;
    let targetRow = null;
    
    for (let i = 0; i < rows.length; i++) {
        if (i === draggedIndex) continue;
        
        const row = rows[i];
        const rowRect = row.getBoundingClientRect();
        const rowMiddle = rowRect.top + rowRect.height / 2;
        
        if (mouseY < rowMiddle) {
            targetIndex = i;
            targetRow = row;
            break;
        }
    }
    
    // If no target found, place at the end
    if (targetIndex === -1) {
        targetIndex = rows.length;
        targetRow = null;
    }
    
    // Adjust for insertion
    if (targetIndex > draggedIndex) {
        targetIndex--;
    }
    
    // Move the row in the DOM
    if (targetRow && draggedRow !== targetRow) {
        if (targetIndex > draggedIndex) {
            targetRow.parentNode.insertBefore(draggedRow, targetRow.nextSibling);
        } else {
            targetRow.parentNode.insertBefore(draggedRow, targetRow);
        }
        
        // Update sequence numbers
        updateSequenceNumbers(tableBody);
        
        // Show save button
        const mesinContainer = draggedRow.closest('.mesin-container');
        const mesinName = mesinContainer?.dataset.mesin;
        const mesinId = mesinContainer?.dataset.mesinId;
        
        if (mesinName && mesinId) {
            // Initialize local changes for drag & drop
            const changes = initializeTableForLocalChanges(mesinName, mesinId);
            
            // Mark that order has changed
            changes.hasOrderChanged = true;
            
            // Show save button
            showSaveButton(mesinName, mesinId);
        }
    }
}

function handleMouseUp() {
    if (!isDragging || !draggedRow) return;
    
    // Clean up
    draggedRow.classList.remove('dragging');
    draggedRow.style.opacity = '';
    draggedRow = null;
    isDragging = false;
    
    // Remove event listeners
    document.removeEventListener('mousemove', handleMouseMove);
    document.removeEventListener('mouseup', handleMouseUp);
}

function updateSequenceNumbers(tableBody) {
    const rows = tableBody.querySelectorAll('.sortable-row');
    rows.forEach((row, index) => {
        const seqNumber = row.querySelector('.sequence-number');
        if (seqNumber) {
            seqNumber.textContent = index + 1;
        }
        
        // Update up/down button states
        const upBtn = row.querySelector('.up-btn');
        const downBtn = row.querySelector('.down-btn');
        
        if (upBtn) upBtn.disabled = index === 0;
        if (downBtn) downBtn.disabled = index === rows.length - 1;
    });
}

// Move item up (local)
function moveItemUpLocal(id, mesinName, shift, mesinId) {
    const row = document.getElementById(`row-${id}`);
    if (!row) return;
    
    const prevRow = row.previousElementSibling;
    if (!prevRow || !prevRow.classList.contains('sortable-row')) return;
    
    // Swap in UI
    row.parentNode.insertBefore(row, prevRow);
    updateSequenceNumbers(row.closest('tbody'));
    
    // Initialize local changes
    const changes = initializeTableForLocalChanges(mesinName, mesinId);
    changes.hasOrderChanged = true;
    
    // Show save button
    showSaveButton(mesinName, mesinId);
}

// Move item down (local)
function moveItemDownLocal(id, mesinName, shift, mesinId) {
    const row = document.getElementById(`row-${id}`);
    if (!row) return;
    
    const nextRow = row.nextElementSibling;
    if (!nextRow || !nextRow.classList.contains('sortable-row')) return;
    
    // Swap in UI
    nextRow.parentNode.insertBefore(nextRow, row);
    updateSequenceNumbers(row.closest('tbody'));
    
    // Initialize local changes
    const changes = initializeTableForLocalChanges(mesinName, mesinId);
    changes.hasOrderChanged = true;
    
    // Show save button
    showSaveButton(mesinName, mesinId);
}

// ============================================
// ORIGINAL FUNCTIONS (Sync, Update, etc.)
// ============================================

// Sync data
async function syncData() {
    const syncButton = document.getElementById('syncButton');
    const originalText = syncButton.innerHTML;
    
    syncButton.disabled = true;
    syncButton.innerHTML = `
        <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Syncing...
    `;
    
    showGlobalLoading('Synchronizing data from planning...');
    
    try {
        const response = await fetch('/preview-andon/sync', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message, () => location.reload());
        } else {
            throw new Error(data.message || 'Sync failed');
        }
    } catch (error) {
        console.error('Sync error:', error);
        showError(error.message || 'Gagal melakukan sync data');
    } finally {
        syncButton.disabled = false;
        syncButton.innerHTML = originalText;
        hideGlobalLoading();
    }
}

// Update shift (original - untuk backup)
async function updateShift(mesinNama, checkbox) {
    const newShift = checkbox.checked ? '2' : '1';
    const oldShift = checkbox.dataset.shift || '1';
    
    if (newShift === oldShift) return;
    
    // Update checkbox data attribute
    checkbox.dataset.shift = newShift;
    
    showGlobalLoading(`Mengubah shift ke ${newShift}...`);
    
    try {
        const response = await fetch('/preview-andon/update-shift', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                mesin_nama: mesinNama,
                shift: newShift
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message, () => {
                // Update UI
                updateShiftUI(checkbox, newShift, mesinNama);
                // Refresh untuk update schedule
                setTimeout(() => refreshData(), 1500);
            });
        } else {
            // Rollback checkbox
            checkbox.checked = !checkbox.checked;
            checkbox.dataset.shift = oldShift;
            throw new Error(data.message || 'Update shift failed');
        }
    } catch (error) {
        console.error('Update shift error:', error);
        showError(error.message || 'Gagal mengubah shift');
    } finally {
        hideGlobalLoading();
    }
}

// Update UI after shift change
function updateShiftUI(checkbox, newShift, mesinNama) {
    const mesinCard = checkbox.closest('.mesin-container');
    const shiftBadge = mesinCard.querySelector('.shift-badge');
    const sortableContainer = mesinCard.querySelector('.sortable-container');
    
    if (shiftBadge) {
        shiftBadge.textContent = newShift == '1' ? '07:00' : '19:00';
        shiftBadge.className = `shift-badge shift-${newShift}`;
    }
    
    if (sortableContainer) {
        sortableContainer.dataset.shift = newShift;
    }
}

// Open update modal
async function openUpdateModal(id) {
    showGlobalLoading('Loading data...');
    
    try {
        const response = await fetch(`/preview-andon/${id}/detail`);
        const data = await response.json();
        
        if (data.success) {
            // Set modal data
            document.getElementById('updateId').value = id;
            document.getElementById('partNoDisplay').textContent = data.data.part_no;
            document.getElementById('planQtyDisplay').textContent = parseInt(data.data.plan_qty).toLocaleString('id-ID');
            document.getElementById('currentActualDisplay').textContent = parseInt(data.data.actual_qty).toLocaleString('id-ID');
            document.getElementById('actual_qty').value = data.data.actual_qty;
            
            // Calculate initial efficiency
            calculateEstimatedEfficiency();
            
            // Show modal
            const modal = document.getElementById('updateModal');
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => modal.classList.add('modal-fade-in'), 10);
            }
        } else {
            throw new Error('Data tidak ditemukan');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Gagal memuat data untuk update');
    } finally {
        hideGlobalLoading();
    }
}

// Close update modal
function closeUpdateModal() {
    const modal = document.getElementById('updateModal');
    if (modal) {
        modal.classList.remove('modal-fade-in');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('updateForm').reset();
            document.getElementById('error-actual').textContent = '';
            document.getElementById('estimatedEfficiency').textContent = '0%';
            document.getElementById('estimatedEfficiency').className = 'text-lg font-bold text-gray-900';
        }, 300);
    }
}

// Calculate estimated efficiency in modal
function calculateEstimatedEfficiency() {
    const actualInput = document.getElementById('actual_qty');
    const planQtyDisplay = document.getElementById('planQtyDisplay');
    const estimatedEfficiency = document.getElementById('estimatedEfficiency');
    
    if (!actualInput || !planQtyDisplay || !estimatedEfficiency) return;
    
    const actualQty = parseFloat(actualInput.value) || 0;
    const planQtyText = planQtyDisplay.textContent.replace(/,/g, '');
    const planQty = parseFloat(planQtyText) || 0;
    
    if (planQty > 0) {
        const efficiency = (actualQty / planQty) * 100;
        
        // Set warna berdasarkan efficiency
        let efficiencyClass = 'text-gray-900';
        if (efficiency >= 90) {
            efficiencyClass = 'text-green-600';
        } else if (efficiency >= 70) {
            efficiencyClass = 'text-yellow-600';
        } else if (efficiency > 0) {
            efficiencyClass = 'text-red-600';
        }
        
        estimatedEfficiency.textContent = efficiency.toFixed(1) + '%';
        estimatedEfficiency.className = 'text-lg font-bold ' + efficiencyClass;
    } else {
        estimatedEfficiency.textContent = '0%';
        estimatedEfficiency.className = 'text-lg font-bold text-gray-900';
    }
}

// Handle update form submission
async function handleUpdateSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = formData.get('id');
    
    // Clear error
    const errorElement = document.getElementById('error-actual');
    if (errorElement) errorElement.textContent = '';
    
    showGlobalLoading('Updating data...');
    
    try {
        const response = await fetch(`/preview-andon/${id}/update-actual`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message, () => {
                closeUpdateModal();
                refreshData();
            });
        } else {
            if (data.errors?.actual_qty) {
                document.getElementById('error-actual').textContent = data.errors.actual_qty[0];
            } else {
                throw new Error(data.message || 'Update failed');
            }
        }
    } catch (error) {
        console.error('Update error:', error);
        showError(error.message || 'Terjadi kesalahan saat update');
    } finally {
        hideGlobalLoading();
    }
}

// Refresh data
function refreshData() {
    showGlobalLoading('Refreshing data...');
    location.reload();
}

// Toggle active status untuk satu row (original - untuk backup)
async function toggleActive(id, checkbox) {
    const isActive = checkbox.checked;
    
    showGlobalLoading(isActive ? 'Mengaktifkan data...' : 'Menonaktifkan data...');
    
    try {
        const response = await fetch(`/preview-andon/${id}/toggle-active`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                is_active: isActive
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showSuccess(data.message, () => refreshData());
        } else {
            // Rollback checkbox
            checkbox.checked = !checkbox.checked;
            throw new Error(data.message || 'Toggle failed');
        }
    } catch (error) {
        console.error('Toggle active error:', error);
        checkbox.checked = !checkbox.checked;
        showError(error.message || 'Gagal mengubah status aktif');
    } finally {
        hideGlobalLoading();
    }
}

// Aktifkan semua data pada mesin tertentu (original)
async function activateAll(mesinNama, shift) {
    showGlobalLoading('Mengaktifkan semua data...');
    
    try {
        // Ambil semua ID untuk mesin dan shift ini
        const rows = document.querySelectorAll(`.mesin-container[data-mesin="${mesinNama}"] tr.sortable-row`);
        const ids = [];
        
        rows.forEach(row => {
            const id = row.getAttribute('data-id');
            if (id) ids.push(id);
        });
        
        // Kirim request untuk setiap ID
        for (const id of ids) {
            await fetch(`/preview-andon/${id}/toggle-active`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    force_active: true 
                })
            });
        }
        
        showSuccess('Semua data berhasil diaktifkan', () => refreshData());
        
    } catch (error) {
        console.error('Activate all error:', error);
        showError('Gagal mengaktifkan semua data');
    } finally {
        hideGlobalLoading();
    }
}

// Nonaktifkan semua data pada mesin tertentu (original)
async function deactivateAll(mesinNama, shift) {
    if (!confirm('Apakah Anda yakin ingin menonaktifkan semua data? Data nonaktif tidak akan dihitung dalam schedule.')) {
        return;
    }
    
    showGlobalLoading('Menonaktifkan semua data...');
    
    try {
        // Ambil semua ID untuk mesin dan shift ini
        const rows = document.querySelectorAll(`.mesin-container[data-mesin="${mesinNama}"] tr.sortable-row`);
        const ids = [];
        
        rows.forEach(row => {
            const id = row.getAttribute('data-id');
            if (id) ids.push(id);
        });
        
        // Kirim request untuk setiap ID
        for (const id of ids) {
            await fetch(`/preview-andon/${id}/toggle-active`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    force_inactive: true 
                })
            });
        }
        
        showSuccess('Semua data berhasil dinonaktifkan', () => refreshData());
        
    } catch (error) {
        console.error('Deactivate all error:', error);
        showError('Gagal menonaktifkan semua data');
    } finally {
        hideGlobalLoading();
    }
}

// Export functions for global use
window.refreshData = refreshData;
window.syncData = syncData;
window.updateShift = updateShift;
window.updateShiftLocal = updateShiftLocal;
window.openUpdateModal = openUpdateModal;
window.closeUpdateModal = closeUpdateModal;
window.calculateEstimatedEfficiency = calculateEstimatedEfficiency;
window.toggleActive = toggleActive;
window.toggleActiveLocal = toggleActiveLocal;
window.activateAll = activateAll;
window.deactivateAll = deactivateAll;
window.markAllActiveLocal = markAllActiveLocal;
window.markAllInactiveLocal = markAllInactiveLocal;
window.moveItemUp = moveItemUpLocal;
window.moveItemDown = moveItemDownLocal;
window.saveTableChanges = saveTableChanges;
window.discardChanges = discardChanges;
window.showGlobalLoading = showGlobalLoading;
window.hideGlobalLoading = hideGlobalLoading;
window.showSuccess = showSuccess;
window.showError = showError;