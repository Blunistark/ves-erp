<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fee Structure Management</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/fee_structure.css">
    <link rel="stylesheet" href="css/fee_structure_builder.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="sidebar-overlay"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Fee Structure Management</h1>
            <span class="header-path">Dashboard > Fee Management > Fee Structure</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div>
                    <h2>Fee Structure Configuration</h2>
                    <p>Manage fee structures for different classes and academic years</p>
                </div>
                <button class="btn btn-primary" id="addFeeStructureBtn">
                    <i class="fas fa-plus-circle"></i>
                    Add Fee Structure
                </button>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Filter Fee Structures</h3>
                    <button type="button" class="btn btn-sm btn-outline" id="resetFilterBtn">
                        <i class="fas fa-redo-alt"></i> Reset
                    </button>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="academicYearFilter">Academic Year</label>
                        <select class="form-select" id="academicYearFilter">
                            <option value="">All Academic Years</option>
                            <!-- Will be populated dynamically -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="classFilter">Class</label>
                        <select class="form-select" id="classFilter">
                            <option value="">All Classes</option>
                            <!-- Will be populated dynamically -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="statusFilter">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions" style="padding: 0; background: transparent; border-top: none;">
                    <button class="btn btn-outline" id="resetFiltersBtn">
                        <i class="fas fa-times"></i> Clear Filters
                    </button>
                    <button class="btn btn-primary" id="applyFilterBtn">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Fee Structures</h3>
                </div>
                <div id="feeStructuresTableContainer">
                    <div class="empty-state">
                        <i class="fas fa-file-invoice-dollar empty-state-icon"></i>
                        <p>Loading fee structures...</p>
                        <div class="loading-indicator">
                            <div class="spinner"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Fee Structure Modal -->
    <div id="feeStructureModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modalTitle">Add Fee Structure</h2>
                <button class="close" aria-label="Close">&times;</button>
            </div>
            
            <div class="fee-builder-container">
                <div class="builder-header">
                    <div class="builder-title">Fee Structure Builder</div>
                    <div class="builder-actions">
                        <div class="template-selector">
                            <select id="templateSelect" class="form-select">
                                <option value="">Select Template</option>
                                <option value="annual">Annual Fee Structure</option>
                                <option value="termwise">Term-wise Fee Structure</option>
                                <option value="monthly">Monthly Fee Structure</option>
                            </select>
                        </div>
                        <button class="btn btn-outline" id="previewToggleBtn">
                            <i class="fas fa-chevron-down"></i> Preview
                        </button>
                    </div>
                </div>
                
                <form id="feeStructureForm">
                    <input type="hidden" id="structureId" name="structureId" value="">
                    
                    <div class="builder-sections">
                        <div class="builder-section">
                            <div class="section-header">
                                <div class="section-title">Basic Details</div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="academicYearSelect">Academic Year</label>
                                    <select class="form-select" id="academicYearSelect" name="academicYearSelect" required>
                                        <option value="">Select Academic Year</option>
                                        <!-- Will be populated dynamically -->
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="classSelect">Class</label>
                                    <select class="form-select" id="classSelect" name="classSelect" required>
                                        <option value="">Select Class</option>
                                        <!-- Will be populated dynamically -->
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="feeTitle">Fee Structure Title</label>
                                <input type="text" class="form-input" id="feeTitle" name="feeTitle" required placeholder="E.g., Annual Fees 2023-24">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label" for="totalAmount">Total Amount</label>
                                    <input type="number" class="form-input" id="totalAmount" name="totalAmount" required min="0" step="0.01" placeholder="Total fee amount" readonly>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="dueDate">Due Date</label>
                                    <input type="date" class="form-input" id="dueDate" name="dueDate" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-textarea" id="description" name="description" placeholder="Add details about this fee structure"></textarea>
                            </div>
                        </div>
                        
                        <div class="builder-section">
                            <div class="section-header">
                                <div class="section-title">Fee Components</div>
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline" id="componentLibraryBtn">
                                        <i class="fas fa-list"></i> Component Library
                                    </button>
                                </div>
                            </div>
                            
                            <div class="component-library" id="componentLibrary" style="display: none;">
                                <!-- Will be populated dynamically -->
                            </div>
                            
                            <div id="feeComponentsContainer" class="fee-components-container">
                                <!-- Fee components will be added here -->
                            </div>
                            
                            <div class="add-component-btn" id="addComponentBtn">
                                <i class="fas fa-plus"></i> Add Fee Component
                            </div>
                            
                            <div class="total-calculation">
                                <div class="total-label">Total Amount:</div>
                                <div class="total-value">₹<span id="totalValue">0.00</span></div>
                            </div>
                        </div>
                        
                        <div class="builder-section">
                            <div class="section-header">
                                <div class="section-title">Payment Schedule</div>
                            </div>
                            
                            <div class="schedule-options">
                                <div class="schedule-option">
                                    <input type="radio" id="fullPayment" name="paymentSchedule" value="full" checked>
                                    <label for="fullPayment">Full Payment (One-time)</label>
                                </div>
                                <div class="schedule-option">
                                    <input type="radio" id="termWise" name="paymentSchedule" value="term">
                                    <label for="termWise">Term-wise (3 installments)</label>
                                </div>
                                <div class="schedule-option">
                                    <input type="radio" id="customSchedule" name="paymentSchedule" value="custom">
                                    <label for="customSchedule">Custom Schedule</label>
                                </div>
                            </div>
                            
                            <div id="installmentContainer" class="installment-container">
                                <table id="installmentTable" class="installment-table">
                                    <thead>
                                        <tr>
                                            <th>Installment Name</th>
                                            <th>Amount</th>
                                            <th>Due Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Installments will be added here -->
                                    </tbody>
                                </table>
                                
                                <button type="button" class="add-installment-btn" id="addInstallmentBtn">
                                    <i class="fas fa-plus"></i> Add Installment
                                </button>
                            </div>
                        </div>
                        
                        <div class="builder-section">
                            <div class="section-header">
                                <div class="section-title">Apply to Classes</div>
                            </div>
                            
                            <div class="class-grid" id="classGrid">
                                <!-- Will be populated dynamically -->
                            </div>
                        </div>
                        
                        <div class="preview-panel">
                            <div class="preview-header">
                                <span>Student/Parent Preview</span>
                                <button type="button" class="preview-toggle" id="previewPanelToggle">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="preview-content" id="previewContent">
                                <!-- Preview content will be populated here -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline close-modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Fee Structure
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h2 class="modal-title">Confirm Delete</h2>
                <button class="close" aria-label="Close">&times;</button>
            </div>
            <div style="padding: 1.5rem;">
                <p style="margin-bottom: 1.5rem; color: #4b5563;">Are you sure you want to delete this fee structure? This action cannot be undone.</p>
                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <button type="button" class="btn btn-outline close-modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

<script src="js/fee_structure_builder.js"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        sidebar.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');

        if (!overlay.hasEventListener) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
            overlay.hasEventListener = true;
        }
    }
    
    // Set initial state on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Check if sidebar is already visible (from sidebar.php)
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('show')) {
            document.body.classList.add('sidebar-open');
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const feeStructureModal = document.getElementById('feeStructureModal');
    const deleteConfirmModal = document.getElementById('deleteConfirmModal');
    const feeStructureForm = document.getElementById('feeStructureForm');
    const feeComponentsContainer = document.getElementById('feeComponentsContainer');
    const addComponentBtn = document.getElementById('addComponentBtn');
    const addFeeStructureBtn = document.getElementById('addFeeStructureBtn');
    const applyFilterBtn = document.getElementById('applyFilterBtn');
    const academicYearFilter = document.getElementById('academicYearFilter');
    const classFilter = document.getElementById('classFilter');
    const feeStructuresTableContainer = document.getElementById('feeStructuresTableContainer');
    const previewPanelToggle = document.getElementById('previewPanelToggle');
    const previewContent = document.getElementById('previewContent');
    
    let currentStructureId = null;
    
    // Event Listeners
    addFeeStructureBtn.addEventListener('click', showAddFeeStructureModal);
    applyFilterBtn.addEventListener('click', loadFeeStructures);
    
    if (previewPanelToggle && previewContent) {
        previewPanelToggle.addEventListener('click', function() {
            previewContent.classList.toggle('open');
            
            // Update icon
            if (previewContent.classList.contains('open')) {
                this.innerHTML = '<i class="fas fa-chevron-up"></i>';
            } else {
                this.innerHTML = '<i class="fas fa-chevron-down"></i>';
            }
        });
    }
    
    // Close modal buttons
    document.querySelectorAll('.close, .close-modal').forEach(button => {
        button.addEventListener('click', function() {
            feeStructureModal.style.display = 'none';
            deleteConfirmModal.style.display = 'none';
        });
    });
    
    // When clicked outside of modal, close it
    window.addEventListener('click', function(event) {
        if (event.target === feeStructureModal) {
            feeStructureModal.style.display = 'none';
        }
        if (event.target === deleteConfirmModal) {
            deleteConfirmModal.style.display = 'none';
        }
    });
    
    // Form submission
    feeStructureForm.addEventListener('submit', saveFeeStructure);
    
    // Initial data loading
    loadAcademicYears();
    loadClasses();
    loadFeeStructures();
    
    // Functions
    function showAddFeeStructureModal() {
        document.getElementById('modalTitle').textContent = 'Add Fee Structure';
        document.getElementById('structureId').value = '';
        feeStructureForm.reset();
        feeComponentsContainer.innerHTML = '';
        feeStructureModal.style.display = 'block';
    }
    
    function showEditFeeStructureModal(structureId) {
        document.getElementById('modalTitle').textContent = 'Edit Fee Structure';
        document.getElementById('structureId').value = structureId;
        currentStructureId = structureId;
        
        // Fetch fee structure details
        fetch(`fee_structure_action.php?action=get_structure&id=${structureId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const structure = data.structure;
                    
                    // Fill form fields
                    document.getElementById('academicYearSelect').value = structure.academic_year_id;
                    document.getElementById('classSelect').value = structure.class_id;
                    document.getElementById('feeTitle').value = structure.title;
                    document.getElementById('totalAmount').value = structure.amount;
                    document.getElementById('dueDate').value = structure.due_date;
                    document.getElementById('description').value = structure.description || '';
                    
                    // Fill fee components
                    feeComponentsContainer.innerHTML = '';
                    
                    if (structure.components && structure.components.length > 0) {
                        structure.components.forEach(component => {
                            window.FeeStructure.addFeeComponent(component.name, component.amount);
                        });
                    } else {
                        window.FeeStructure.addFeeComponent(); // Add empty component if none exists
                    }
                    
                    // Update total calculation
                    window.FeeStructure.calculateTotal();
                    
                    feeStructureModal.style.display = 'block';
                } else {
                    alert('Failed to load fee structure details: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching fee structure details.');
            });
    }
    
    function showDeleteConfirmModal(structureId) {
        currentStructureId = structureId;
        deleteConfirmModal.style.display = 'block';
        
        // Setup confirm button
        document.getElementById('confirmDeleteBtn').onclick = function() {
            deleteFeeStructure(structureId);
        };
    }
    
    function saveFeeStructure(event) {
        event.preventDefault();
        
        // Validate form
        if (!feeStructureForm.checkValidity()) {
            return;
        }
        
        // Collect form data
        const formData = new FormData(feeStructureForm);
        formData.append('action', document.getElementById('structureId').value ? 'update_structure' : 'add_structure');
        
        // Send request
        fetch('fee_structure_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                feeStructureModal.style.display = 'none';
                loadFeeStructures(); // Reload fee structures
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving the fee structure.');
        });
    }
    
    function deleteFeeStructure(structureId) {
        fetch('fee_structure_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `action=delete_structure&id=${structureId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                deleteConfirmModal.style.display = 'none';
                loadFeeStructures(); // Reload fee structures
                alert(data.message);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the fee structure.');
        });
    }
    
    function loadAcademicYears() {
        fetch('fee_structure_action.php?action=get_academic_years')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate academic year filter
                    const filterSelect = document.getElementById('academicYearFilter');
                    filterSelect.innerHTML = '<option value="">All Academic Years</option>';
                    
                    // Populate academic year select in the form
                    const formSelect = document.getElementById('academicYearSelect');
                    formSelect.innerHTML = '<option value="">Select Academic Year</option>';
                    
                    data.years.forEach(year => {
                        const filterOption = document.createElement('option');
                        filterOption.value = year.id;
                        filterOption.textContent = year.name;
                        filterSelect.appendChild(filterOption);
                        
                        const formOption = document.createElement('option');
                        formOption.value = year.id;
                        formOption.textContent = year.name;
                        formSelect.appendChild(formOption);
                    });
                } else {
                    console.error('Failed to load academic years:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    function loadClasses() {
        fetch('fee_structure_action.php?action=get_classes')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate class filter
                    const filterSelect = document.getElementById('classFilter');
                    filterSelect.innerHTML = '<option value="">All Classes</option>';
                    
                    // Populate class select in the form
                    const formSelect = document.getElementById('classSelect');
                    formSelect.innerHTML = '<option value="">Select Class</option>';
                    
                    // Populate class grid for multi-select
                    const classGrid = document.getElementById('classGrid');
                    if (classGrid) {
                        classGrid.innerHTML = '';
                    }
                    
                    data.classes.forEach(cls => {
                        const filterOption = document.createElement('option');
                        filterOption.value = cls.id;
                        filterOption.textContent = cls.name;
                        filterSelect.appendChild(filterOption);
                        
                        const formOption = document.createElement('option');
                        formOption.value = cls.id;
                        formOption.textContent = cls.name;
                        formSelect.appendChild(formOption);
                        
                        // Add to class grid
                        if (classGrid) {
                            const classCheckbox = document.createElement('div');
                            classCheckbox.className = 'class-checkbox';
                            classCheckbox.innerHTML = `
                                <input type="checkbox" id="class-${cls.id}" name="classes[]" value="${cls.id}">
                                <label for="class-${cls.id}">${cls.name}</label>
                            `;
                            classGrid.appendChild(classCheckbox);
                        }
                    });
                } else {
                    console.error('Failed to load classes:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    function loadFeeStructures() {
        const academicYearId = academicYearFilter.value;
        const classId = classFilter.value;
        const statusId = statusFilter ? statusFilter.value : '';
        
        // Show loading state
        feeStructuresTableContainer.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-file-invoice-dollar empty-state-icon"></i>
                <p>Loading fee structures...</p>
                <div class="loading-indicator">
                    <div class="spinner"></div>
                </div>
            </div>
        `;
        
        // Build query params
        let url = 'fee_structure_action.php?action=get_structures';
        if (academicYearId) {
            url += `&academic_year_id=${academicYearId}`;
        }
        if (classId) {
            url += `&class_id=${classId}`;
        }
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.structures.length === 0) {
                        feeStructuresTableContainer.innerHTML = `
                            <div class="empty-state">
                                <i class="fas fa-file-invoice-dollar empty-state-icon"></i>
                                <p>No fee structures found.</p>
                                <button class="btn btn-primary" onclick="document.getElementById('addFeeStructureBtn').click()">
                                    <i class="fas fa-plus-circle"></i> Add Fee Structure
                                </button>
                            </div>
                        `;
                    } else {
                        // Create table
                        const table = document.createElement('table');
                        table.innerHTML = `
                            <thead>
                                <tr>
                                    <th>Academic Year</th>
                                    <th>Class</th>
                                    <th>Title</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Components</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        `;
                        
                        const tbody = table.querySelector('tbody');
                        
                        data.structures.forEach(structure => {
                            const row = document.createElement('tr');
                            
                            // Format date
                            const dueDate = new Date(structure.due_date);
                            const formattedDate = dueDate.toLocaleDateString();
                            
                            // Count components
                            const componentsCount = structure.components ? structure.components.length : 0;
                            
                            row.innerHTML = `
                                <td>${structure.academic_year_name}</td>
                                <td>${structure.class_name}</td>
                                <td>${structure.title}</td>
                                <td>₹${parseFloat(structure.amount).toFixed(2)}</td>
                                <td>${formattedDate}</td>
                                <td>${componentsCount}</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn btn-sm btn-outline edit-btn" data-id="${structure.id}">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-btn" data-id="${structure.id}">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            `;
                            
                            tbody.appendChild(row);
                        });
                        
                        feeStructuresTableContainer.innerHTML = '';
                        feeStructuresTableContainer.appendChild(table);
                        
                        // Add event listeners to edit and delete buttons
                        document.querySelectorAll('.edit-btn').forEach(button => {
                            button.addEventListener('click', function() {
                                const structureId = this.getAttribute('data-id');
                                showEditFeeStructureModal(structureId);
                            });
                        });
                        
                        document.querySelectorAll('.delete-btn').forEach(button => {
                            button.addEventListener('click', function() {
                                const structureId = this.getAttribute('data-id');
                                showDeleteConfirmModal(structureId);
                            });
                        });
                    }
                } else {
                    feeStructuresTableContainer.innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-circle empty-state-icon" style="color: #ef4444;"></i>
                            <p>Error: ${data.message}</p>
                            <button class="btn btn-outline" onclick="loadFeeStructures()">
                                <i class="fas fa-redo-alt"></i> Try Again
                            </button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                feeStructuresTableContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-exclamation-triangle empty-state-icon" style="color: #f59e0b;"></i>
                        <p>Error: Failed to load fee structures.</p>
                        <button class="btn btn-outline" onclick="loadFeeStructures()">
                            <i class="fas fa-redo-alt"></i> Try Again
                        </button>
                    </div>
                `;
            });
    }
    
    // Note: The functions addFeeComponent, removeFeeComponent, and calculateTotal are now 
    // defined in the FeeStructure namespace in fee_structure_builder.js file
});
</script>

</body>
</html> 