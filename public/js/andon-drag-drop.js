// Drag & Drop functionality for reordering

class DragDropManager {
    constructor() {
        this.draggedItem = null;
        this.dragOverItem = null;
        this.mesinNama = null;
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
        document.addEventListener('dragstart', (e) => e.preventDefault());
    }
    
    setupSortableRows() {
        const rows = document.querySelectorAll('.sortable-row');
        rows.forEach(row => {
            row.addEventListener('dragstart', this.handleDragStart.bind(this));
            row.addEventListener('dragover', this.handleDragOver.bind(this));
            row.addEventListener('dragenter', this.handleDragEnter.bind(this));
            row.addEventListener('dragleave', this.handleDragLeave.bind(this));
            row.addEventListener('drop', this.handleDrop.bind(this));
            row.addEventListener('dragend', this.handleDragEnd.bind(this));
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
    
    // HTML5 Drag & Drop events
    handleDragStart(e) {
        const row = e.target.closest('.sortable-row');
        if (!row) return;
        
        this.startDrag(row, e.clientY);
        e.dataTransfer.setData('text/plain', row.dataset.id);
        e.dataTransfer.effectAllowed = 'move';
        
        // Create ghost image
        setTimeout(() => {
            row.classList.add('dragging');
        }, 0);
    }
    
    handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        
        const row = this.getRowFromPoint(e.clientX, e.clientY);
        if (row && row !== this.draggedItem) {
            this.dragOverItem = row;
            this.updateDragOverUI(row, e.clientY);
        }
    }
    
    handleDragEnter(e) {
        e.preventDefault();
        const row = this.getRowFromPoint(e.clientX, e.clientY);
        if (row && row !== this.draggedItem) {
            row.classList.add('drag-over');
        }
    }
    
    handleDragLeave(e) {
        const row = this.getRowFromPoint(e.clientX, e.clientY);
        if (row) {
            row.classList.remove('drag-over');
        }
    }
    
    async handleDrop(e) {
        e.preventDefault();
        
        if (!this.draggedItem || !this.dragOverItem) return;
        
        const draggedId = this.draggedItem.dataset.id;
        const targetId = this.dragOverItem.dataset.id;
        
        // Swap positions in UI
        await this.swapRows(this.draggedItem, this.dragOverItem);
        
        // Clean up
        this.cleanupDragUI();
    }
    
    handleDragEnd() {
        this.cleanupDragUI();
    }
    
    // Core drag methods
    startDrag(row, clientY) {
        this.draggedItem = row;
        this.dragStartY = clientY;
        
        // Get mesin and shift info
        const container = row.closest('.sortable-container');
        this.mesinNama = container?.dataset.mesin;
        this.shift = container?.dataset.shift;
        
        // Create ghost element
        this.createGhostElement(row);
    }
    
    createGhostElement(row) {
        this.ghostElement = row.cloneNode(true);
        this.ghostElement.classList.add('sortable-ghost');
        this.ghostElement.style.position = 'absolute';
        this.ghostElement.style.zIndex = '1000';
        this.ghostElement.style.opacity = '0.7';
        this.ghostElement.style.pointerEvents = 'none';
        this.ghostElement.style.width = `${row.offsetWidth}px`;
        this.ghostElement.style.height = `${row.offsetHeight}px`;
        
        // Position initially at row location
        const rect = row.getBoundingClientRect();
        this.ghostElement.style.left = `${rect.left}px`;
        this.ghostElement.style.top = `${rect.top}px`;
        
        document.body.appendChild(this.ghostElement);
        row.classList.add('dragging');
    }
    
    updateDragPosition(clientY) {
        if (!this.ghostElement) return;
        
        // Update ghost position
        const deltaY = clientY - this.dragStartY;
        const rect = this.draggedItem.getBoundingClientRect();
        this.ghostElement.style.top = `${rect.top + deltaY}px`;
        
        // Find drop target
        const row = this.getRowFromPoint(rect.left + 50, clientY);
        if (row && row !== this.draggedItem && !row.classList.contains('dragging')) {
            this.dragOverItem = row;
            this.updateDragOverUI(row, clientY);
        }
    }
    
    updateDragOverUI(row, clientY) {
        // Remove all drag-over classes
        document.querySelectorAll('.drag-over').forEach(el => {
            el.classList.remove('drag-over');
        });
        
        // Add to current target
        row.classList.add('drag-over');
        
        // Visual indicator for position
        const rect = row.getBoundingClientRect();
        const middle = rect.top + rect.height / 2;
        
        if (clientY > middle) {
            row.classList.add('drag-over-bottom');
        } else {
            row.classList.add('drag-over-top');
        }
    }
    
    async swapRows(dragged, target) {
        const parent = dragged.parentNode;
        const draggedIndex = Array.from(parent.children).indexOf(dragged);
        const targetIndex = Array.from(parent.children).indexOf(target);
        
        if (draggedIndex < targetIndex) {
            parent.insertBefore(dragged, target.nextSibling);
        } else {
            parent.insertBefore(dragged, target);
        }
        
        // Update sequence numbers
        this.updateSequenceNumbers(parent);
        
        // Send update to server
        await this.updateOrderOnServer();
    }
    
    async completeDrag() {
        if (this.draggedItem && this.dragOverItem && this.draggedItem !== this.dragOverItem) {
            await this.swapRows(this.draggedItem, this.dragOverItem);
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
        
        document.querySelectorAll('.drag-over, .drag-over-top, .drag-over-bottom').forEach(el => {
            el.classList.remove('drag-over', 'drag-over-top', 'drag-over-bottom');
        });
        
        this.draggedItem = null;
        this.dragOverItem = null;
        this.isDragging = false;
    }
    
    // Helper methods
    getRowFromPoint(x, y) {
        const element = document.elementFromPoint(x, y);
        return element?.closest('.sortable-row');
    }
    
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
    
    async updateOrderOnServer() {
        if (!this.mesinNama || !this.shift) return;
        
        const container = document.querySelector(`.sortable-container[data-mesin="${this.mesinNama}"]`);
        if (!container) return;
        
        const rows = container.querySelectorAll('.sortable-row[data-id]');
        const order = Array.from(rows).map(row => parseInt(row.dataset.id));
        
        try {
            const response = await fetch('/preview-andon/reorder', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    mesin_nama: this.mesinNama,
                    shift: this.shift,
                    order: order
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Urutan berhasil disimpan!');
                
                // Schedule will be recalculated automatically
                // We can optionally refresh just this mesin's data
                setTimeout(() => {
                    // Update start/finish times if needed
                    this.updateScheduleTimes(container);
                }, 500);
            } else {
                throw new Error(data.message || 'Update failed');
            }
        } catch (error) {
            console.error('Update order error:', error);
            showError('Gagal menyimpan urutan');
            // Reload to revert changes
            setTimeout(() => location.reload(), 1000);
        }
    }
    
    updateScheduleTimes(container) {
        // This is a placeholder - in real implementation, you might want to
        // fetch updated schedule data from server or recalculate locally
        const rows = container.querySelectorAll('.sortable-row');
        // Could update start/finish times here if needed
    }
}

// Up/Down button functions (fallback)
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
        const response = await fetch('/preview-andon/reorder', {
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
            showSuccess('Urutan berhasil disimpan!');
        } else {
            throw new Error(data.message || 'Update failed');
        }
    } catch (error) {
        console.error('Update order error:', error);
        showError('Gagal menyimpan urutan');
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
    window.updateSequenceNumbersInContainer = updateSequenceNumbersInContainer;
});

// Make manager available globally
window.dragDropManager = dragDropManager;