// Main functions for Preview Andon

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
        const response = await fetch('/andon/sync', {
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

// Update shift - VERSI LOCAL untuk perubahan belum disimpan
function updateShiftLocal(mesinNama, checkbox) {
    const newShift = checkbox.checked ? '2' : '1';
    const oldShift = checkbox.dataset.shift || '1';
    
    if (newShift === oldShift) return;
    
    // Update checkbox data attribute
    checkbox.dataset.shift = newShift;
    
    // Update UI
    updateShiftUILocal(checkbox, newShift, mesinNama);
    
    // Tampilkan save button
    showSaveButton(mesinNama);
}

// Update UI after shift change - LOCAL version
function updateShiftUILocal(checkbox, newShift, mesinNama) {
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

// Open update modal - PERBAIKAN: tambah slash di URL
async function openUpdateModal(id) {
    showGlobalLoading('Loading data...');
    
    try {
        console.log('Fetching detail for ID:', id);
        
        // PERBAIKAN: TAMBAHKAN SLASH DI DEPAN
        const response = await fetch(`/andon/${id}/detail`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Detail response:', data);
        
        if (data.success && data.data) {
            // Set modal data
            document.getElementById('updateId').value = id;
            document.getElementById('partNoDisplay').textContent = data.data.part_no || '-';
            document.getElementById('planQtyDisplay').textContent = parseInt(data.data.plan_qty || 0).toLocaleString('id-ID');
            document.getElementById('currentActualDisplay').textContent = parseInt(data.data.actual_qty || 0).toLocaleString('id-ID');
            document.getElementById('actual_qty').value = data.data.actual_qty || '';
            
            // Calculate initial efficiency
            calculateEstimatedEfficiency();
            
            // Show modal
            const modal = document.getElementById('updateModal');
            if (modal) {
                modal.classList.remove('hidden');
                setTimeout(() => modal.classList.add('modal-fade-in'), 10);
                
                // Focus ke input setelah modal terbuka
                setTimeout(() => {
                    const actualInput = document.getElementById('actual_qty');
                    if (actualInput) {
                        actualInput.focus();
                        actualInput.select();
                    }
                }, 350);
            }
        } else {
            throw new Error(data.message || 'Data tidak ditemukan');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Gagal memuat data untuk update: ' + error.message);
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
        const response = await fetch(`/andon/${id}/update-actual`, {
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

// Toggle active status - VERSI LOCAL untuk perubahan belum disimpan
function toggleActiveLocal(id, checkbox, mesinId, mesinNama) {
    const isActive = checkbox.checked;
    const row = checkbox.closest('tr');
    
    // Update UI lokal terlebih dahulu
    if (isActive) {
        row.classList.remove('inactive-row');
        // Enable update button
        const updateBtn = row.querySelector('button[onclick*="openUpdateModal"]');
        if (updateBtn) {
            updateBtn.disabled = false;
            updateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    } else {
        row.classList.add('inactive-row');
        // Disable update button
        const updateBtn = row.querySelector('button[onclick*="openUpdateModal"]');
        if (updateBtn) {
            updateBtn.disabled = true;
            updateBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
    
    // Update counter
    updateActiveCountLocal(mesinId, mesinNama);
    
    // Tampilkan save button
    showSaveButton(mesinNama);
}

// Update active count local
function updateActiveCountLocal(mesinId, mesinNama) {
    const mesinContainer = document.querySelector(`#mesin-container-${mesinId}`);
    if (!mesinContainer) return;
    
    const activeRows = mesinContainer.querySelectorAll('tr.sortable-row:not(.inactive-row)');
    const inactiveRows = mesinContainer.querySelectorAll('tr.inactive-row');
    
    const activeCount = activeRows.length;
    const inactiveCount = inactiveRows.length;
    
    document.getElementById(`active-count-${mesinId}`).textContent = activeCount;
    document.getElementById(`inactive-count-${mesinId}`).textContent = inactiveCount;
}

// Toggle active status untuk satu row - VERSI SERVER (untuk langsung save)
async function toggleActive(id, checkbox) {
    const isActive = checkbox.checked;
    const row = checkbox.closest('tr');
    
    showGlobalLoading(isActive ? 'Mengaktifkan data...' : 'Menonaktifkan data...');
    
    try {
        const response = await fetch(`/andon/${id}/toggle-active`, {
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
            // Update row styling
            if (data.is_active) {
                row.classList.remove('inactive-row');
                // Enable update button
                const updateBtn = row.querySelector('button[onclick*="openUpdateModal"]');
                if (updateBtn) {
                    updateBtn.disabled = false;
                    updateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            } else {
                row.classList.add('inactive-row');
                // Disable update button
                const updateBtn = row.querySelector('button[onclick*="openUpdateModal"]');
                if (updateBtn) {
                    updateBtn.disabled = true;
                    updateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            }
            
            showSuccess(data.message);
            
            // Refresh setelah beberapa saat untuk update schedule
            setTimeout(() => refreshData(), 1500);
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

// Mark all active - VERSI LOCAL
function markAllActiveLocal(mesinNama, shift, mesinId) {
    const mesinContainer = document.querySelector(`#mesin-container-${mesinId}`);
    if (!mesinContainer) return;
    
    const checkboxes = mesinContainer.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        if (!checkbox.checked) {
            checkbox.checked = true;
            toggleActiveLocal(checkbox.closest('tr').dataset.id, checkbox, mesinId, mesinNama);
        }
    });
    
    showSaveButton(mesinNama);
    showSuccess('Semua data ditandai aktif (lokal). Klik "Simpan Perubahan" untuk menyimpan.');
}

// Mark all inactive - VERSI LOCAL
function markAllInactiveLocal(mesinNama, shift, mesinId) {
    if (!confirm('Apakah Anda yakin ingin menonaktifkan semua data? Data nonaktif tidak akan dihitung dalam schedule.')) {
        return;
    }
    
    const mesinContainer = document.querySelector(`#mesin-container-${mesinId}`);
    if (!mesinContainer) return;
    
    const checkboxes = mesinContainer.querySelectorAll('input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            checkbox.checked = false;
            toggleActiveLocal(checkbox.closest('tr').dataset.id, checkbox, mesinId, mesinNama);
        }
    });
    
    showSaveButton(mesinNama);
    showSuccess('Semua data ditandai nonaktif (lokal). Klik "Simpan Perubahan" untuk menyimpan.');
}

// Move item up - VERSI LOCAL
function moveItemUpLocal(id, mesinNama, shift, mesinId) {
    const row = document.getElementById(`row-${id}`);
    const tableBody = row.closest('tbody');
    const rows = Array.from(tableBody.querySelectorAll('tr.sortable-row'));
    const currentIndex = rows.indexOf(row);
    
    if (currentIndex > 0) {
        // Swap positions
        const prevRow = rows[currentIndex - 1];
        tableBody.insertBefore(row, prevRow);
        
        // Update sequence numbers
        updateSequenceNumbersLocal(mesinId);
        
        // Tampilkan save button
        showSaveButton(mesinNama);
    }
}

// Move item down - VERSI LOCAL
function moveItemDownLocal(id, mesinNama, shift, mesinId) {
    const row = document.getElementById(`row-${id}`);
    const tableBody = row.closest('tbody');
    const rows = Array.from(tableBody.querySelectorAll('tr.sortable-row'));
    const currentIndex = rows.indexOf(row);
    
    if (currentIndex < rows.length - 1) {
        // Swap positions
        const nextRow = rows[currentIndex + 1];
        if (nextRow.nextSibling) {
            tableBody.insertBefore(row, nextRow.nextSibling);
        } else {
            tableBody.appendChild(row);
        }
        
        // Update sequence numbers
        updateSequenceNumbersLocal(mesinId);
        
        // Tampilkan save button
        showSaveButton(mesinNama);
    }
}

// Update sequence numbers local
function updateSequenceNumbersLocal(mesinId) {
    const tableBody = document.querySelector(`#mesin-container-${mesinId} tbody`);
    if (!tableBody) return;
    
    const rows = tableBody.querySelectorAll('tr.sortable-row');
    rows.forEach((row, index) => {
        const seqElement = row.querySelector('.sequence-number');
        if (seqElement) {
            seqElement.textContent = index + 1;
        }
        
        // Update up/down button states
        const upBtn = row.querySelector('.up-btn');
        const downBtn = row.querySelector('.down-btn');
        
        if (upBtn) upBtn.disabled = index === 0;
        if (downBtn) downBtn.disabled = index === rows.length - 1;
    });
}

// Show save button
function showSaveButton(mesinNama) {
    const mesinContainer = document.querySelector(`.mesin-container[data-mesin="${mesinNama}"]`);
    if (!mesinContainer) return;
    
    const mesinId = mesinContainer.dataset.mesinId;
    const saveBtn = document.getElementById(`save-btn-${mesinId}`);
    const discardBtn = document.getElementById(`discard-btn-${mesinId}`);
    
    if (saveBtn) saveBtn.classList.remove('hidden');
    if (discardBtn) discardBtn.classList.remove('hidden');
}

// Save table changes to server
async function saveTableChanges(mesinNama, shift, mesinId) {
    showGlobalLoading('Menyimpan perubahan...');
    
    try {
        // Collect all changes
        const mesinContainer = document.querySelector(`#mesin-container-${mesinId}`);
        if (!mesinContainer) return;
        
        const rows = mesinContainer.querySelectorAll('tr.sortable-row');
        const updates = [];
        
        rows.forEach((row, index) => {
            const id = row.dataset.id;
            const checkbox = row.querySelector('input[type="checkbox"]');
            const isActive = checkbox ? checkbox.checked : true;
            
            updates.push({
                id: id,
                is_active: isActive,
                sort_order: index + 1
            });
        });
        
        // Get current shift
        const shiftToggle = mesinContainer.querySelector('input[type="checkbox"][data-mesin]');
        const currentShift = shiftToggle ? (shiftToggle.checked ? '2' : '1') : shift;
        
        // Send bulk update
        const response = await fetch('/andon/bulk-update', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                mesin_nama: mesinNama,
                shift: currentShift,
                updates: updates
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Hide save button and show success
            const saveBtn = document.getElementById(`save-btn-${mesinId}`);
            const discardBtn = document.getElementById(`discard-btn-${mesinId}`);
            const successMsg = document.getElementById(`save-success-${mesinId}`);
            
            if (saveBtn) saveBtn.classList.add('hidden');
            if (discardBtn) discardBtn.classList.add('hidden');
            if (successMsg) {
                successMsg.classList.remove('hidden');
                setTimeout(() => successMsg.classList.add('hidden'), 3000);
            }
            
            // Refresh untuk update schedule
            setTimeout(() => refreshData(), 2000);
            
            showSuccess(data.message);
        } else {
            throw new Error(data.message || 'Gagal menyimpan perubahan');
        }
    } catch (error) {
        console.error('Save changes error:', error);
        showError(error.message || 'Terjadi kesalahan saat menyimpan');
    } finally {
        hideGlobalLoading();
    }
}

// Discard changes
function discardChanges(mesinNama, mesinId) {
    if (!confirm('Batalkan semua perubahan? Perubahan lokal akan hilang.')) {
        return;
    }
    
    // Reload halaman untuk reset ke state awal
    showGlobalLoading('Membatalkan perubahan...');
    setTimeout(() => {
        location.reload();
    }, 500);
}

/// Submit to Andon Mesin - FIXED dengan start/finish time
async function submitToAndonMesin(mesinNama, mesinId) {
    showGlobalLoading('Submitting to Andon Mesin...');
    
    try {
        const submitBtn = document.getElementById(`submit-btn-${mesinId}`);
        const submitLoading = document.getElementById(`submit-loading-${mesinId}`);
        
        if (submitBtn) submitBtn.classList.add('hidden');
        if (submitLoading) submitLoading.classList.remove('hidden');
        
        // Ambil data dari container
        const mesinContainer = document.querySelector(`#mesin-container-${mesinId}`);
        
        // Ambil shift saat ini
        const shiftToggle = mesinContainer?.querySelector('input[type="checkbox"][data-mesin]');
        const currentShift = shiftToggle ? (shiftToggle.checked ? '2' : '1') : '1';
        
        // Kumpulkan data dari tabel untuk dikirim
        const rows = mesinContainer?.querySelectorAll('tr.sortable-row') || [];
        const dataItems = [];
        
        rows.forEach((row, index) => {
            const checkbox = row.querySelector('input.status-checkbox');
            
            // Ambil data dari kolom tabel
            const cells = row.querySelectorAll('td');
            
            // Kolom: 0=Status, 1=Urutan, 2=PartNo, 3=GSPH, 4=Start, 5=Finish, 6=Plan, 7=Actual, 8=Efficiency, 9=Aksi
            const partNo = cells[2]?.textContent?.trim() || '';
            const gsph = cells[3]?.textContent?.trim() || '0';
            const startTime = cells[4]?.textContent?.trim() || '-';
            const finishTime = cells[5]?.textContent?.trim() || '-';
            const planQty = cells[6]?.textContent?.trim()?.replace(/\./g, '')?.replace(/,/g, '') || '0';
            
            // Actual qty bisa "Belum diisi" atau angka
            let actualQtyText = cells[7]?.textContent?.trim() || '0';
            if (actualQtyText.toLowerCase().includes('belum')) {
                actualQtyText = '0';
            }
            const actualQty = actualQtyText.replace(/\./g, '')?.replace(/,/g, '') || '0';
            
            // Efficiency dari badge
            const efficiencyEl = cells[8]?.querySelector('.efficiency-badge');
            const efficiency = efficiencyEl?.textContent?.trim()?.replace('%', '')?.replace(',', '.') || '0';
            
            dataItems.push({
                id: parseInt(row.dataset.id),
                part_no: partNo,
                gsph: parseFloat(gsph.replace(',', '.')) || 0,
                start_time: startTime !== '-' ? startTime : null,
                finish_time: finishTime !== '-' ? finishTime : null,
                plan_qty: parseInt(planQty) || 0,
                actual_qty: parseInt(actualQty) || 0,
                efficiency: parseFloat(efficiency) || 0,
                is_active: checkbox ? checkbox.checked : true,
                sort_order: index + 1
            });
        });
        
        // Tanggal hari ini format Y-m-d
        const today = new Date();
        const tanggal = today.getFullYear() + '-' + 
                        String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                        String(today.getDate()).padStart(2, '0');
        
        console.log('Submitting data:', {
            mesin_id: mesinId,
            mesin_nama: mesinNama,
            shift: currentShift,
            tanggal: tanggal,
            data: dataItems
        });
        
        const response = await fetch('/andon/submit-to-mesin', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                mesin_id: mesinId,
                mesin_nama: mesinNama,
                shift: currentShift,
                tanggal: tanggal,
                data: dataItems
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const submitSuccess = document.getElementById(`submit-success-${mesinId}`);
            if (submitSuccess) submitSuccess.classList.remove('hidden');
            
            // Update badge dengan tanggal submit
            updateSubmitBadge(mesinId, new Date().toISOString());
            
            showSuccess(data.message);
            
            setTimeout(() => {
                if (submitLoading) submitLoading.classList.add('hidden');
                if (submitSuccess) submitSuccess.classList.add('hidden');
                if (submitBtn) submitBtn.classList.remove('hidden');
            }, 3000);
        } else {
            // Tampilkan error detail dari validasi
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                throw new Error(errorMessages);
            }
            throw new Error(data.message || 'Gagal submit ke Andon Mesin');
        }
    } catch (error) {
        console.error('Submit error:', error);
        showError(error.message || 'Terjadi kesalahan saat submit');
        
        const submitBtn = document.getElementById(`submit-btn-${mesinId}`);
        const submitLoading = document.getElementById(`submit-loading-${mesinId}`);
        
        if (submitBtn) submitBtn.classList.remove('hidden');
        if (submitLoading) submitLoading.classList.add('hidden');
    } finally {
        hideGlobalLoading();
    }
}

// Update submit badge
function updateSubmitBadge(mesinId, lastDate) {
    const badgeContainer = document.getElementById(`submit-badge-${mesinId}`);
    const infoContainer = document.getElementById(`last-submission-info-${mesinId}`);
    
    if (badgeContainer) {
        badgeContainer.innerHTML = `
            <span class="submitted-badge px-2 py-1 rounded-full text-xs font-bold">
                ✓ Submitted
            </span>
        `;
    }
    
    if (infoContainer && lastDate) {
        const date = new Date(lastDate);
        infoContainer.innerHTML = `
            Terakhir submit: ${date.toLocaleDateString('id-ID')} ${date.toLocaleTimeString('id-ID')}
        `;
    }
}

// Check last submission status for each machine
async function checkLastSubmissionStatus() {
    try {
        const mesinContainers = document.querySelectorAll('.mesin-container');
        
        for (const container of mesinContainers) {
            const mesinId = container.dataset.mesinId;
            if (!mesinId) continue;
            
            const response = await fetch(`/andon/mesin/last-submission/${mesinId}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                if (data.last_submission_date) {
                    updateSubmitBadge(mesinId, data.last_submission_date);
                }
            }
        }
    } catch (error) {
        console.error('Error checking submission status:', error);
    }
}

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Update form submit
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
    
    // Check last submission status on page load
    setTimeout(() => {
        checkLastSubmissionStatus();
    }, 1000);
});

// Export functions for global use
window.refreshData = refreshData;
window.syncData = syncData;
window.updateShift = updateShift;
window.openUpdateModal = openUpdateModal;
window.closeUpdateModal = closeUpdateModal;
window.calculateEstimatedEfficiency = calculateEstimatedEfficiency;
window.toggleActive = toggleActive;
window.activateAll = activateAll;
window.deactivateAll = deactivateAll;
window.moveItemUp = moveItemUp;
window.moveItemDown = moveItemDown;
window.showGlobalLoading = showGlobalLoading;
window.hideGlobalLoading = hideGlobalLoading;
window.showSuccess = showSuccess;
window.showError = showError;

// Export LOCAL functions
window.updateShiftLocal = updateShiftLocal;
window.toggleActiveLocal = toggleActiveLocal;
window.markAllActiveLocal = markAllActiveLocal;
window.markAllInactiveLocal = markAllInactiveLocal;
window.moveItemUpLocal = moveItemUpLocal;
window.moveItemDownLocal = moveItemDownLocal;
window.saveTableChanges = saveTableChanges;
window.discardChanges = discardChanges;
window.submitToAndonMesin = submitToAndonMesin;