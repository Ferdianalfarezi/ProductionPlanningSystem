// Drag & Drop functionality for reordering - Compatible with Local Changes System

class DragDropManager {
    constructor() {
        this.draggedItem = null;
        this.dragOverItem = null;
        this.mesinNama = null;
        this.mesinId = null;
        this.shift = null;
        this.dragStartY = 0;
        this.isDragging = false;
        this.ghostElement = null;
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.setupSortableRows();
    }
    
    setupEventListeners() {
        // Event delegation untuk drag handle
        document.addEventListener('mousedown', this.handleMouseDown.bind(this));
        document.addEventListener('mousemove', this.handleMouseMove.bind(this));
        document.addEventListener('mouseup', this.handleMouseUp.bind(this));
        
        // Touch events untuk mobile
        document.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: false });
        document.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
        document.addEventListener('touchend', this.handleTouchEnd.bind(this));
        
        // Prevent default drag behavior
        document.addEventListener('dragstart', (e) => {
            if (e.target.closest('.drag-handle')) {
                e.preventDefault();
            }
        });
    }
    
    setupSortableRows() {
        const rows = document.querySelectorAll('.sortable-row');
        rows.forEach(row => {
            row.setAttribute('draggable', 'false'); // Disable native drag
        });
    }
    
    // Mouse events
    handleMouseDown(e) {
        const dragHandle = e.target.closest('.drag-handle');
        if (!dragHandle) return;
        
        e.preventDefault();
        
        const row = dragHandle.closest('.sortable-row');
        if (!row) return;
        
        this.startDrag(row, e.clientY);
        this.isDragging = true;
    }
    
    handleMouseMove(e) {
        if (!this.isDragging || !this.draggedItem) return;
        
        e.preventDefault();
        this.updateDragPosition(e.clientY);
    }
    
    handleMouseUp() {
        if (!this.isDragging) return;
        
        this.completeDrag();
        this.isDragging = false;
    }
    
    // Touch events
    handleTouchStart(e) {
        const touch = e.touches[0];
        const dragHandle = document.elementFromPoint(touch.clientX, touch.clientY)?.closest('.drag-handle');
        if (!dragHandle) return;
        
        e.preventDefault();
        
        const row = dragHandle.closest('.sortable-row');
        if (!row) return;
        
        this.startDrag(row, touch.clientY);
        this.isDragging = true;
    }
    
    handleTouchMove(e) {
        if (!this.isDragging || !this.draggedItem) return;
        
        e.preventDefault();
        const touch = e.touches[0];
        this.updateDragPosition(touch.clientY);
    }
    
    handleTouchEnd() {
        if (!this.isDragging) return;
        
        this.completeDrag();
        this.isDragging = false;
    }
    
    // Core drag methods
    startDrag(row, clientY) {
        this.draggedItem = row;
        this.dragStartY = clientY;
        this.initialTop = row.getBoundingClientRect().top;
        
        // Get mesin and shift info
        const container = row.closest('.sortable-container');
        const mesinContainer = row.closest('.mesin-container');
        this.mesinNama = container?.dataset.mesin;
        this.shift = container?.dataset.shift;
        this.mesinId = mesinContainer?.dataset.mesinId;
        
        // Add dragging class
        row.classList.add('dragging');
        
        // Create ghost element
        this.createGhostElement(row, clientY);
    }
    
    createGhostElement(row, clientY) {
        this.ghostElement = row.cloneNode(true);
        this.ghostElement.classList.add('sortable-ghost');
        this.ghostElement.classList.remove('dragging');
        this.ghostElement.style.cssText = `
            position: fixed;
            z-index: 1000;
            opacity: 0.8;
            pointer-events: none;
            width: ${row.offsetWidth}px;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-radius: 4px;
        `;
        
        // Position at row location
        const rect = row.getBoundingClientRect();
        this.ghostElement.style.left = `${rect.left}px`;
        this.ghostElement.style.top = `${rect.top}px`;
        
        document.body.appendChild(this.ghostElement);
    }
    
    updateDragPosition(clientY) {
        if (!this.ghostElement || !this.draggedItem) return;
        
        // Update ghost position
        const deltaY = clientY - this.dragStartY;
        this.ghostElement.style.top = `${this.initialTop + deltaY}px`;
        
        // Find drop target
        const tbody = this.draggedItem.closest('tbody');
        const rows = Array.from(tbody.querySelectorAll('.sortable-row:not(.dragging)'));
        
        // Remove all drag-over classes
        rows.forEach(r => r.classList.remove('drag-over'));
        
        // Find the row we're hovering over
        for (const row of rows) {
            const rect = row.getBoundingClientRect();
            if (clientY >= rect.top && clientY <= rect.bottom) {
                this.dragOverItem = row;
                row.classList.add('drag-over');
                break;
            }
        }
    }
    
    swapRows(dragged, target) {
        if (!dragged || !target || dragged === target) return;
        
        const parent = dragged.parentNode;
        const draggedIndex = Array.from(parent.children).indexOf(dragged);
        const targetIndex = Array.from(parent.children).indexOf(target);
        
        if (draggedIndex < targetIndex) {
            parent.insertBefore(dragged, target.nextSibling);
        } else {
            parent.insertBefore(dragged, target);
        }
        
        // Update sequence numbers locally
        this.updateSequenceNumbers(parent);
        
        // Show save button (local changes approach)
        if (this.mesinNama && typeof showSaveButton === 'function') {
            showSaveButton(this.mesinNama);
        }
    }
    
    completeDrag() {
        if (this.draggedItem && this.dragOverItem && this.draggedItem !== this.dragOverItem) {
            this.swapRows(this.draggedItem, this.dragOverItem);
        }
        
        this.cleanupDragUI();
    }
    
    cleanupDragUI() {
        // Remove ghost element
        if (this.ghostElement) {
            this.ghostElement.remove();
            this.ghostElement = null;
        }
        
        // Remove dragging classes
        if (this.draggedItem) {
            this.draggedItem.classList.remove('dragging');
        }
        
        document.querySelectorAll('.drag-over').forEach(el => {
            el.classList.remove('drag-over');
        });
        
        this.draggedItem = null;
        this.dragOverItem = null;
        this.isDragging = false;
    }
    
    // Helper methods
    updateSequenceNumbers(parent) {
        const rows = parent.querySelectorAll('.sortable-row:not(.sortable-ghost)');
        rows.forEach((row, index) => {
            const seqNumber = row.querySelector('.sequence-number');
            if (seqNumber) {
                seqNumber.textContent = index + 1;
            }
            
            // Update button states
            const upBtn = row.querySelector('.up-btn');
            const downBtn = row.querySelector('.down-btn');
            
            if (upBtn) upBtn.disabled = index === 0;
            if (downBtn) downBtn.disabled = index === rows.length - 1;
        });
    }
}

// Helper function to get mesin ID from container
function getMesinIdFromContainer(mesinNama) {
    const container = document.querySelector(`.mesin-container[data-mesin="${mesinNama}"]`);
    return container?.dataset.mesinId;
}

// Up/Down button functions - LOCAL version (compatible with local changes)
function moveItemUpLocal(id, mesinNama, shift, mesinId) {
    const row = document.getElementById(`row-${id}`);
    if (!row) return;
    
    const prevRow = row.previousElementSibling;
    if (!prevRow || !prevRow.classList.contains('sortable-row')) return;
    
    // Swap in UI
    row.parentNode.insertBefore(row, prevRow);
    
    // Update sequence numbers
    updateSequenceNumbersLocal(mesinId);
    
    // Show save button
    if (typeof showSaveButton === 'function') {
        showSaveButton(mesinNama);
    }
}

function moveItemDownLocal(id, mesinNama, shift, mesinId) {
    const row = document.getElementById(`row-${id}`);
    if (!row) return;
    
    const nextRow = row.nextElementSibling;
    if (!nextRow || !nextRow.classList.contains('sortable-row')) return;
    
    // Swap in UI
    row.parentNode.insertBefore(nextRow, row);
    
    // Update sequence numbers
    updateSequenceNumbersLocal(mesinId);
    
    // Show save button
    if (typeof showSaveButton === 'function') {
        showSaveButton(mesinNama);
    }
}

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

// Legacy functions for backwards compatibility (direct server update)
async function moveItemUp(id, mesinNama, shift) {
    const row = document.querySelector(`.sortable-row[data-id="${id}"]`);
    if (!row) return;
    
    const prevRow = row.previousElementSibling;
    if (!prevRow || !prevRow.classList.contains('sortable-row')) return;
    
    // Swap in UI
    row.parentNode.insertBefore(row, prevRow);
    updateSequenceNumbersInContainer(row.closest('.sortable-container'));
    
    // Update server
    await updateOrderOnServer(mesinNama, shift);
}

async function moveItemDown(id, mesinNama, shift) {
    const row = document.querySelector(`.sortable-row[data-id="${id}"]`);
    if (!row) return;
    
    const nextRow = row.nextElementSibling;
    if (!nextRow || !nextRow.classList.contains('sortable-row')) return;
    
    // Swap in UI
    row.parentNode.insertBefore(nextRow, row);
    updateSequenceNumbersInContainer(row.closest('.sortable-container'));
    
    // Update server
    await updateOrderOnServer(mesinNama, shift);
}

function updateSequenceNumbersInContainer(container) {
    if (!container) return;
    
    const rows = container.querySelectorAll('.sortable-row');
    rows.forEach((row, index) => {
        const seqNumber = row.querySelector('.sequence-number');
        if (seqNumber) {
            seqNumber.textContent = index + 1;
        }
        
        // Update button states
        const upBtn = row.querySelector('.up-btn');
        const downBtn = row.querySelector('.down-btn');
        
        if (upBtn) upBtn.disabled = index === 0;
        if (downBtn) downBtn.disabled = index === rows.length - 1;
    });
}

async function updateOrderOnServer(mesinNama, shift) {
    const container = document.querySelector(`.sortable-container[data-mesin="${mesinNama}"]`);
    if (!container) return;
    
    const rows = container.querySelectorAll('.sortable-row[data-id]');
    const order = Array.from(rows).map(row => parseInt(row.dataset.id));
    
    try {
        const response = await fetch('/andon/reorder', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                mesin_nama: mesinNama,
                shift: shift,
                order: order
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (typeof showSuccess === 'function') {
                showSuccess('Urutan berhasil disimpan!');
            }
        } else {
            throw new Error(data.message || 'Update failed');
        }
    } catch (error) {
        console.error('Update order error:', error);
        if (typeof showError === 'function') {
            showError('Gagal menyimpan urutan');
        }
        location.reload();
    }
}

// Initialize when DOM is loaded
let dragDropManager;

document.addEventListener('DOMContentLoaded', () => {
    dragDropManager = new DragDropManager();
    
    // Export functions for global use
    window.moveItemUp = moveItemUp;
    window.moveItemDown = moveItemDown;
    window.moveItemUpLocal = moveItemUpLocal;
    window.moveItemDownLocal = moveItemDownLocal;
    window.updateSequenceNumbersInContainer = updateSequenceNumbersInContainer;
    window.updateSequenceNumbersLocal = updateSequenceNumbersLocal;
});

// Make manager available globally
window.DragDropManager = DragDropManager;