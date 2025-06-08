/**
 * Fee Structure Builder JavaScript
 * Enhances the fee structure management with advanced features
 */

// Create a global namespace for Fee Structure functions
window.FeeStructure = {
    componentCounter: 0,
    
    // Add a new fee component
    addFeeComponent: function(name = '', amount = '') {
        const componentId = this.componentCounter++;
        
        const componentDiv = document.createElement('div');
        componentDiv.className = 'fee-component component-enter-active';
        componentDiv.dataset.id = componentId;
        
        componentDiv.innerHTML = `
            <div class="fee-component-drag">
                <i class="fas fa-grip-vertical"></i>
            </div>
            <div class="fee-component-name">
                <input type="text" class="form-input" name="componentName[]" value="${name}" placeholder="Component name (e.g., Tuition Fee)" required>
            </div>
            <div class="fee-component-amount">
                <input type="number" class="form-input" name="componentAmount[]" value="${amount}" placeholder="Amount" min="0" step="0.01" required>
            </div>
            <div class="fee-component-remove" data-id="${componentId}">
                <i class="fas fa-trash"></i>
            </div>
        `;
        
        const feeComponentsContainer = document.getElementById('feeComponentsContainer');
        if (feeComponentsContainer) {
            feeComponentsContainer.appendChild(componentDiv);
            
            // Add event listener to remove button
            componentDiv.querySelector('.fee-component-remove').addEventListener('click', function() {
                window.FeeStructure.removeFeeComponent(this.getAttribute('data-id'));
            });
        }
        
        // Animation effect
        setTimeout(() => {
            componentDiv.classList.remove('component-enter-active');
        }, 100);
        
        return componentDiv;
    },
    
    // Remove a fee component
    removeFeeComponent: function(componentId) {
        const component = document.querySelector(`.fee-component[data-id="${componentId}"]`);
        if (component) {
            // Add exit animation
            component.classList.add('component-exit-active');
            
            // Remove after animation completes
            setTimeout(() => {
                component.remove();
                this.calculateTotal();
            }, 300);
        }
    },
    
    // Calculate the total fee amount
    calculateTotal: function() {
        let total = 0;
        const componentAmounts = document.querySelectorAll('input[name="componentAmount[]"]');
        
        componentAmounts.forEach(input => {
            const amount = parseFloat(input.value) || 0;
            total += amount;
        });
        
        const totalAmountInput = document.getElementById('totalAmount');
        if (totalAmountInput) {
            totalAmountInput.value = total.toFixed(2);
        }
        
        const totalValueDisplay = document.getElementById('totalValue');
        if (totalValueDisplay) {
            totalValueDisplay.textContent = total.toFixed(2);
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    // Variables for internal use
    let isDragging = false;
    let draggedElement = null;

    // Constants and Elements
    const feeStructureForm = document.getElementById('feeStructureForm');
    const feeComponentsContainer = document.getElementById('feeComponentsContainer');
    const addComponentBtn = document.getElementById('addComponentBtn');
    const totalAmountInput = document.getElementById('totalAmount');
    const componentLibraryBtn = document.getElementById('componentLibraryBtn');
    const componentLibrary = document.getElementById('componentLibrary');
    const previewToggleBtn = document.getElementById('previewToggleBtn');
    const previewContent = document.getElementById('previewContent');
    const templateSelect = document.getElementById('templateSelect');
    
    const commonComponents = [
        { name: 'Tuition Fee', amount: 10000 },
        { name: 'Development Fee', amount: 5000 },
        { name: 'Library Fee', amount: 2000 },
        { name: 'Technology Fee', amount: 3000 },
        { name: 'Laboratory Fee', amount: 2500 },
        { name: 'Sports Fee', amount: 1500 },
        { name: 'Transport Fee', amount: 8000 },
        { name: 'Examination Fee', amount: 1200 },
        { name: 'Uniform Fee', amount: 3500 },
        { name: 'Books & Stationery', amount: 4000 }
    ];
    
    // Templates for quick starts
    const feeTemplates = {
        annual: {
            title: 'Annual Fee Structure',
            components: [
                { name: 'Tuition Fee', amount: 20000 },
                { name: 'Development Fee', amount: 5000 },
                { name: 'Library Fee', amount: 2000 },
                { name: 'Technology Fee', amount: 3000 }
            ],
            schedule: 'full'
        },
        termwise: {
            title: 'Term-wise Fee Structure',
            components: [
                { name: 'Tuition Fee', amount: 7500 },
                { name: 'Development Fee', amount: 2000 },
                { name: 'Laboratory Fee', amount: 1500 }
            ],
            schedule: 'term'
        },
        monthly: {
            title: 'Monthly Fee Structure',
            components: [
                { name: 'Tuition Fee', amount: 2000 },
                { name: 'Transport Fee', amount: 800 }
            ],
            schedule: 'monthly'
        }
    };
    
    // Initialize the form
    function initializeFeeBuilder() {
        // Show at least one component
        if (feeComponentsContainer && feeComponentsContainer.children.length === 0) {
            window.FeeStructure.addFeeComponent();
        }
        
        // Update total on any change
        window.FeeStructure.calculateTotal();
        
        // Setup event listeners
        setupEventListeners();
        
        // Initialize drag and drop
        setupDragAndDrop();
        
        // Populate component library
        populateComponentLibrary();
    }
    
    // Set up all event listeners
    function setupEventListeners() {
        // Add component button
        if (addComponentBtn) {
            addComponentBtn.addEventListener('click', function() {
                window.FeeStructure.addFeeComponent();
                window.FeeStructure.calculateTotal();
            });
        }
        
        // Form validation
        if (feeStructureForm) {
            feeStructureForm.addEventListener('submit', function(e) {
                // Additional validation (beyond HTML5 validation)
                const components = document.querySelectorAll('.fee-component');
                if (components.length === 0) {
                    e.preventDefault();
                    alert('Please add at least one fee component');
                    return false;
                }
                
                // Make sure all fee components have valid data
                let valid = true;
                components.forEach(component => {
                    const nameInput = component.querySelector('input[name="componentName[]"]');
                    const amountInput = component.querySelector('input[name="componentAmount[]"]');
                    
                    if (!nameInput.value || !amountInput.value || parseFloat(amountInput.value) <= 0) {
                        valid = false;
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please ensure all fee components have a name and valid amount');
                    return false;
                }
            });
        }
        
        // Component Library
        if (componentLibraryBtn && componentLibrary) {
            componentLibraryBtn.addEventListener('click', function(e) {
                e.preventDefault();
                componentLibrary.style.display = componentLibrary.style.display === 'none' ? 'block' : 'none';
            });
            
            // Close library when clicking outside
            document.addEventListener('click', function(e) {
                if (!componentLibraryBtn.contains(e.target) && !componentLibrary.contains(e.target)) {
                    componentLibrary.style.display = 'none';
                }
            });
        }
        
        // Preview panel toggle
        if (previewToggleBtn && previewContent) {
            previewToggleBtn.addEventListener('click', function() {
                previewContent.classList.toggle('open');
                
                // Update the preview content
                if (previewContent.classList.contains('open')) {
                    updatePreview();
                }
                
                // Change the toggle icon/text
                if (previewContent.classList.contains('open')) {
                    previewToggleBtn.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Preview';
                } else {
                    previewToggleBtn.innerHTML = '<i class="fas fa-chevron-down"></i> Show Preview';
                }
            });
        }
        
        // Template selection
        if (templateSelect) {
            templateSelect.addEventListener('change', function() {
                const templateKey = this.value;
                if (templateKey && feeTemplates[templateKey]) {
                    applyTemplate(templateKey);
                }
            });
        }
        
        // Payment schedule options
        const scheduleOptions = document.querySelectorAll('input[name="paymentSchedule"]');
        const installmentContainer = document.getElementById('installmentContainer');
        
        if (scheduleOptions.length && installmentContainer) {
            scheduleOptions.forEach(option => {
                option.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        installmentContainer.style.display = 'block';
                    } else {
                        installmentContainer.style.display = 'none';
                    }
                });
            });
        }
        
        // Add installment button
        const addInstallmentBtn = document.getElementById('addInstallmentBtn');
        const installmentTableBody = document.querySelector('#installmentTable tbody');
        
        if (addInstallmentBtn && installmentTableBody) {
            addInstallmentBtn.addEventListener('click', function() {
                const rowCount = installmentTableBody.querySelectorAll('tr').length;
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <input type="text" class="form-input" name="installmentName[]" value="Installment ${rowCount + 1}" required>
                    </td>
                    <td>
                        <input type="number" class="form-input" name="installmentAmount[]" value="0" min="0" step="0.01" required>
                    </td>
                    <td>
                        <input type="date" class="form-input" name="installmentDueDate[]" required>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-installment">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                
                installmentTableBody.appendChild(row);
                
                // Add event listener to remove button
                row.querySelector('.remove-installment').addEventListener('click', function() {
                    row.remove();
                });
            });
        }
        
        // Listen for changes to recalculate total
        if (feeComponentsContainer) {
            feeComponentsContainer.addEventListener('input', function(e) {
                if (e.target.name === 'componentAmount[]') {
                    window.FeeStructure.calculateTotal();
                }
            });
        }
    }
    
    // Set up drag and drop functionality
    function setupDragAndDrop() {
        if (feeComponentsContainer) {
            feeComponentsContainer.addEventListener('dragover', function(e) {
                e.preventDefault();
                const afterElement = getDragAfterElement(feeComponentsContainer, e.clientY);
                const draggable = document.querySelector('.dragging');
                if (draggable) {
                    if (afterElement == null) {
                        feeComponentsContainer.appendChild(draggable);
                    } else {
                        feeComponentsContainer.insertBefore(draggable, afterElement);
                    }
                }
            });
        }
    }
    
    // Helper function for drag and drop
    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.fee-component:not(.dragging)')];
        
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }
    
    // Populate the component library with common components
    function populateComponentLibrary() {
        if (componentLibrary) {
            componentLibrary.innerHTML = '';
            
            // Add a header
            const header = document.createElement('div');
            header.className = 'library-header';
            header.textContent = 'Common Fee Components';
            componentLibrary.appendChild(header);
            
            // Add components
            commonComponents.forEach(component => {
                const componentItem = document.createElement('div');
                componentItem.className = 'library-item';
                componentItem.innerHTML = `
                    <div class="library-item-name">${component.name}</div>
                    <div class="library-item-amount">₹${component.amount.toFixed(2)}</div>
                    <button class="btn btn-sm btn-outline add-from-library" data-name="${component.name}" data-amount="${component.amount}">
                        <i class="fas fa-plus"></i>
                    </button>
                `;
                
                componentLibrary.appendChild(componentItem);
                
                // Add click event to add this component
                componentItem.querySelector('.add-from-library').addEventListener('click', function() {
                    const name = this.getAttribute('data-name');
                    const amount = this.getAttribute('data-amount');
                    window.FeeStructure.addFeeComponent(name, amount);
                    window.FeeStructure.calculateTotal();
                    componentLibrary.style.display = 'none';
                });
            });
        }
    }
    
    // Apply a template to the fee structure
    function applyTemplate(templateKey) {
        const template = feeTemplates[templateKey];
        if (!template) return;
        
        // Set the title
        const titleInput = document.getElementById('feeTitle');
        if (titleInput) {
            titleInput.value = template.title;
        }
        
        // Clear existing components
        if (feeComponentsContainer) {
            feeComponentsContainer.innerHTML = '';
        }
        
        // Add template components
        template.components.forEach(component => {
            window.FeeStructure.addFeeComponent(component.name, component.amount);
        });
        
        // Set payment schedule
        const scheduleInput = document.querySelector(`input[name="paymentSchedule"][value="${template.schedule}"]`);
        if (scheduleInput) {
            scheduleInput.checked = true;
            
            // Trigger change event
            const event = new Event('change');
            scheduleInput.dispatchEvent(event);
        }
        
        // Update total
        window.FeeStructure.calculateTotal();
    }
    
    // Update the preview panel
    function updatePreview() {
        if (!previewContent) return;
        
        const title = document.getElementById('feeTitle').value || 'Fee Structure';
        const totalAmount = document.getElementById('totalAmount').value || '0.00';
        const components = [];
        
        document.querySelectorAll('.fee-component').forEach(component => {
            const nameInput = component.querySelector('input[name="componentName[]"]');
            const amountInput = component.querySelector('input[name="componentAmount[]"]');
            
            if (nameInput && nameInput.value && amountInput && amountInput.value) {
                components.push({
                    name: nameInput.value,
                    amount: parseFloat(amountInput.value) || 0
                });
            }
        });
        
        // Generate preview HTML
        let previewHTML = `
            <div class="preview-fee-structure">
                <div class="preview-header">
                    <h3>${title}</h3>
                    <div class="preview-total">Total: ₹${parseFloat(totalAmount).toFixed(2)}</div>
                </div>
                <div class="preview-components">
        `;
        
        if (components.length === 0) {
            previewHTML += `
                <div class="preview-empty">
                    <i class="fas fa-info-circle"></i>
                    <p>No fee components added yet.</p>
                </div>
            `;
        } else {
            previewHTML += `<table class="preview-table">
                <thead>
                    <tr>
                        <th>Fee Component</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
            `;
            
            components.forEach(component => {
                previewHTML += `
                    <tr>
                        <td>${component.name}</td>
                        <td>₹${component.amount.toFixed(2)}</td>
                    </tr>
                `;
            });
            
            previewHTML += `
                </tbody>
                <tfoot>
                    <tr>
                        <th>Total</th>
                        <th>₹${parseFloat(totalAmount).toFixed(2)}</th>
                    </tr>
                </tfoot>
            </table>`;
        }
        
        previewHTML += `
                </div>
            </div>
        `;
        
        previewContent.innerHTML = previewHTML;
    }
    
    // Initialize the fee builder
    initializeFeeBuilder();
}); 