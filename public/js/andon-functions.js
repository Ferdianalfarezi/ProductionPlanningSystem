// Main functions for Preview Andon

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

// Update shift
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

// Initialize event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Update form submit
    const updateForm = document.getElementById('updateForm');
    if (updateForm) {
        updateForm.addEventListener('submit', handleUpdateSubmit);
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
});

// Export functions for global use
window.refreshData = refreshData;
window.syncData = syncData;
window.updateShift = updateShift;
window.openUpdateModal = openUpdateModal;
window.closeUpdateModal = closeUpdateModal;
window.calculateEstimatedEfficiency = calculateEstimatedEfficiency;
window.showGlobalLoading = showGlobalLoading;
window.hideGlobalLoading = hideGlobalLoading;
window.showSuccess = showSuccess;
window.showError = showError;