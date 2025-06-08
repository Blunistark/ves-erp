<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Transfer & Promotion</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/manage_student.css">
    <style>
        .transfer-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #1f2937;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        
        .form-label.required::after {
            content: '*';
            color: #ef4444;
            margin-left: 0.25rem;
        }
        
        .form-select, .form-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: #f9fafb;
            font-size: 0.875rem;
        }
        
        .btn-container {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-primary {
            background-color: #2563eb;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        
        .btn-outline {
            background-color: white;
            color: #4b5563;
            border: 1px solid #d1d5db;
        }
        
        .btn-outline:hover {
            background-color: #f9fafb;
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }
        
        .students-table th, 
        .students-table td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .students-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        .students-table tr:hover {
            background-color: #f3f4f6;
        }
        
        .checkbox-col {
            width: 40px;
        }
        
        .selected-students {
            margin-top: 2rem;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-blue {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .badge-green {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .progress-container {
            margin-top: 2rem;
            display: none;
        }
        
        .progress-bar-container {
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }
        
        .progress-bar {
            height: 100%;
            background-color: #2563eb;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .progress-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.875rem;
            color: #4b5563;
        }
        
        .results-container {
            margin-top: 2rem;
            display: none;
        }
        
        .result-item {
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 0.5rem;
        }
        
        .result-success {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .result-error {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }
        
        .empty-state-icon {
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
            color: #9ca3af;
        }
    </style>
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
            <h1 class="header-title">Student Transfer & Promotion</h1>
            <span class="header-path">Dashboard > Students > Transfer/Promotion</span>
        </header>

        <main class="dashboard-content">
            <div class="transfer-container">
                <!-- Left column - Source selection -->
                <div class="card">
                    <h2 class="card-title">Source Selection</h2>
                    
                    <div class="form-group">
                        <label for="sourceAcademicYear" class="form-label required">Academic Year</label>
                        <select id="sourceAcademicYear" class="form-select">
                            <option value="">Select Academic Year</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sourceClass" class="form-label required">Class</label>
                        <select id="sourceClass" class="form-select">
                            <option value="">Select Class</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sourceSection" class="form-label">Section</label>
                        <select id="sourceSection" class="form-select">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                    
                    <div class="btn-container">
                        <button type="button" class="btn btn-primary" id="loadStudentsBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Load Students
                        </button>
                    </div>
                </div>

                <!-- Right column - Target selection -->
                <div class="card">
                    <h2 class="card-title">Target Selection</h2>
                    
                    <div class="form-group">
                        <label for="transferType" class="form-label required">Transfer Type</label>
                        <select id="transferType" class="form-select">
                            <option value="">Select Transfer Type</option>
                            <option value="promotion">Promotion to Next Class</option>
                            <option value="transfer">Transfer to Different Class/Section</option>
                        </select>
                    </div>
                    
                    <div id="promotionFields" style="display: none;">
                        <div class="form-group">
                            <label for="targetAcademicYear" class="form-label required">Target Academic Year</label>
                            <select id="targetAcademicYear" class="form-select">
                                <option value="">Select Academic Year</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="transferFields" style="display: none;">
                        <div class="form-group">
                            <label for="targetClass" class="form-label required">Target Class</label>
                            <select id="targetClass" class="form-select">
                                <option value="">Select Class</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="targetSection" class="form-label required">Target Section</label>
                            <select id="targetSection" class="form-select">
                                <option value="">Select Section</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="effectiveDate" class="form-label required">Effective Date</label>
                        <input type="date" id="effectiveDate" class="form-input" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="btn-container">
                        <button type="button" class="btn btn-outline" id="cancelBtn">Cancel</button>
                        <button type="button" class="btn btn-primary" id="processBtn" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                            </svg>
                            Process Transfer
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Students table -->
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h2 class="card-title" style="margin-bottom: 0;">Student Selection</h2>
                    <div>
                        <label for="selectAll" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" id="selectAll">
                            <span>Select All</span>
                        </label>
                    </div>
                </div>
                
                <div id="studentsTableContainer">
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <p>No students loaded. Please select a class and click "Load Students".</p>
                    </div>
                </div>
            </div>
            
            <div class="progress-container" id="progressContainer">
                <h3 style="margin-bottom: 1rem;">Transfer Progress</h3>
                <div class="progress-bar-container">
                    <div class="progress-bar" id="progressBar"></div>
                </div>
                <div class="progress-info">
                    <span id="progressText">Processing...</span>
                    <span id="progressPercentage">0%</span>
                </div>
            </div>
            
            <div class="results-container" id="resultsContainer">
                <h3 style="margin-bottom: 1rem;">Transfer Results</h3>
                <div id="resultsContent"></div>
            </div>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Element references
    const sourceAcademicYear = document.getElementById('sourceAcademicYear');
    const sourceClass = document.getElementById('sourceClass');
    const sourceSection = document.getElementById('sourceSection');
    const loadStudentsBtn = document.getElementById('loadStudentsBtn');
    const transferType = document.getElementById('transferType');
    const promotionFields = document.getElementById('promotionFields');
    const transferFields = document.getElementById('transferFields');
    const targetAcademicYear = document.getElementById('targetAcademicYear');
    const targetClass = document.getElementById('targetClass');
    const targetSection = document.getElementById('targetSection');
    const effectiveDate = document.getElementById('effectiveDate');
    const studentsTableContainer = document.getElementById('studentsTableContainer');
    const selectAll = document.getElementById('selectAll');
    const processBtn = document.getElementById('processBtn');
    const cancelBtn = document.getElementById('cancelBtn');
    const progressContainer = document.getElementById('progressContainer');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressPercentage = document.getElementById('progressPercentage');
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsContent = document.getElementById('resultsContent');
    
    // Initial data loading
    loadAcademicYears();
    loadClasses();
    
    // Event listeners
    sourceClass.addEventListener('change', loadSections);
    loadStudentsBtn.addEventListener('click', loadStudents);
    transferType.addEventListener('change', toggleTransferFields);
    targetClass.addEventListener('change', loadTargetSections);
    selectAll.addEventListener('change', toggleSelectAll);
    processBtn.addEventListener('click', processTransfer);
    cancelBtn.addEventListener('click', resetForm);
    
    // Functions
    function loadAcademicYears() {
        fetch('student_actions.php?fetch_academic_years=1')
            .then(response => response.json())
            .then(years => {
                // Populate source academic year dropdown
                sourceAcademicYear.innerHTML = '<option value="">Select Academic Year</option>';
                targetAcademicYear.innerHTML = '<option value="">Select Academic Year</option>';
                
                years.forEach(year => {
                    // Add to source dropdown
                    const sourceOption = document.createElement('option');
                    sourceOption.value = year.id;
                    sourceOption.textContent = year.name + (year.is_current == 1 ? ' (Current)' : '');
                    if (year.is_current == 1) {
                        sourceOption.selected = true;
                    }
                    sourceAcademicYear.appendChild(sourceOption);
                    
                    // Add to target dropdown (for next year, prefer next year if available)
                    const targetOption = document.createElement('option');
                    targetOption.value = year.id;
                    targetOption.textContent = year.name + (year.is_current == 1 ? ' (Current)' : '');
                    
                    // Try to find and select next year
                    if (years.some(y => y.name.includes(parseInt(year.name) + 1))) {
                        if (year.name.includes(parseInt(year.name) + 1)) {
                            targetOption.selected = true;
                        }
                    } else if (year.is_current == 1) {
                        targetOption.selected = true;
                    }
                    
                    targetAcademicYear.appendChild(targetOption);
                });
            })
            .catch(error => console.error('Error loading academic years:', error));
    }
    
    function loadClasses() {
        fetch('student_actions.php?fetch_classes=1')
            .then(response => response.json())
            .then(classes => {
                // Populate source class dropdown
                sourceClass.innerHTML = '<option value="">Select Class</option>';
                targetClass.innerHTML = '<option value="">Select Class</option>';
                
                classes.forEach(cls => {
                    // Add to source dropdown
                    const sourceOption = document.createElement('option');
                    sourceOption.value = cls.id;
                    sourceOption.textContent = cls.name;
                    sourceClass.appendChild(sourceOption);
                    
                    // Add to target dropdown
                    const targetOption = document.createElement('option');
                    targetOption.value = cls.id;
                    targetOption.textContent = cls.name;
                    targetClass.appendChild(targetOption);
                });
            })
            .catch(error => console.error('Error loading classes:', error));
    }
    
    function loadSections() {
        const classId = sourceClass.value;
        if (!classId) {
            sourceSection.innerHTML = '<option value="">All Sections</option>';
            return;
        }
        
        fetch(`student_actions.php?fetch_sections=${classId}`)
            .then(response => response.json())
            .then(sections => {
                sourceSection.innerHTML = '<option value="">All Sections</option>';
                
                sections.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section.id;
                    option.textContent = section.name;
                    sourceSection.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading sections:', error));
    }
    
    function loadTargetSections() {
        const classId = targetClass.value;
        if (!classId) {
            targetSection.innerHTML = '<option value="">Select Section</option>';
            return;
        }
        
        fetch(`student_actions.php?fetch_sections=${classId}`)
            .then(response => response.json())
            .then(sections => {
                targetSection.innerHTML = '<option value="">Select Section</option>';
                
                sections.forEach(section => {
                    const option = document.createElement('option');
                    option.value = section.id;
                    option.textContent = section.name;
                    targetSection.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading target sections:', error));
    }
    
    function loadStudents() {
        const academicYearId = sourceAcademicYear.value;
        const classId = sourceClass.value;
        const sectionId = sourceSection.value;
        
        if (!academicYearId || !classId) {
            alert('Please select academic year and class.');
            return;
        }
        
        // Reset UI
        studentsTableContainer.innerHTML = '<div class="empty-state"><p>Loading students...</p></div>';
        processBtn.disabled = true;
        
        // Build query params
        let queryParams = `academic_year=${academicYearId}&class=${classId}`;
        if (sectionId) {
            queryParams += `&section=${sectionId}`;
        }
        
        fetch(`student_transfer_action.php?action=fetch_students&${queryParams}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.students.length === 0) {
                        studentsTableContainer.innerHTML = `
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p>No students found for the selected criteria.</p>
                            </div>
                        `;
                    } else {
                        renderStudentsTable(data.students);
                        processBtn.disabled = false;
                    }
                } else {
                    studentsTableContainer.innerHTML = `
                        <div class="empty-state">
                            <p>Error: ${data.message || 'Failed to load students'}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                studentsTableContainer.innerHTML = `
                    <div class="empty-state">
                        <p>Error: Failed to load students. Please try again.</p>
                    </div>
                `;
            });
    }
    
    function renderStudentsTable(students) {
        const table = document.createElement('table');
        table.className = 'students-table';
        
        // Create table header
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr>
                <th class="checkbox-col"><input type="checkbox" id="selectAllTable"></th>
                <th>Admission #</th>
                <th>Name</th>
                <th>Class</th>
                <th>Section</th>
                <th>Roll #</th>
                <th>Gender</th>
            </tr>
        `;
        table.appendChild(thead);
        
        // Create table body
        const tbody = document.createElement('tbody');
        students.forEach(student => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="checkbox" class="student-checkbox" value="${student.user_id}"></td>
                <td>${student.admission_number}</td>
                <td>${student.full_name}</td>
                <td>${student.class_name}</td>
                <td>${student.section_name}</td>
                <td>${student.roll_number}</td>
                <td>${student.gender_label || student.gender_code || 'N/A'}</td>
            `;
            tbody.appendChild(tr);
        });
        table.appendChild(tbody);
        
        // Replace the container content
        studentsTableContainer.innerHTML = '';
        studentsTableContainer.appendChild(table);
        
        // Set up the "Select All" checkbox in the table
        document.getElementById('selectAllTable').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            // Update the original select all checkbox to match
            selectAll.checked = this.checked;
            
            // Enable/disable process button
            updateProcessButtonState();
        });
        
        // Add event listeners to individual checkboxes
        document.querySelectorAll('.student-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllState();
                updateProcessButtonState();
            });
        });
    }
    
    function toggleSelectAll() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectAll.checked;
        });
        
        // If there's a select all checkbox in the table, update it too
        const selectAllTable = document.getElementById('selectAllTable');
        if (selectAllTable) {
            selectAllTable.checked = selectAll.checked;
        }
        
        // Enable/disable process button
        updateProcessButtonState();
    }
    
    function updateSelectAllState() {
        const checkboxes = document.querySelectorAll('.student-checkbox');
        const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
        
        selectAll.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
        
        // If there's a select all checkbox in the table, update it too
        const selectAllTable = document.getElementById('selectAllTable');
        if (selectAllTable) {
            selectAllTable.checked = selectAll.checked;
        }
    }
    
    function updateProcessButtonState() {
        const checkedCount = document.querySelectorAll('.student-checkbox:checked').length;
        processBtn.disabled = checkedCount === 0;
    }
    
    function toggleTransferFields() {
        const type = transferType.value;
        
        if (type === 'promotion') {
            promotionFields.style.display = 'block';
            transferFields.style.display = 'none';
        } else if (type === 'transfer') {
            promotionFields.style.display = 'none';
            transferFields.style.display = 'block';
        } else {
            promotionFields.style.display = 'none';
            transferFields.style.display = 'none';
        }
    }
    
    function processTransfer() {
        const type = transferType.value;
        
        if (!type) {
            alert('Please select a transfer type.');
            return;
        }
        
        // Get selected students
        const selectedStudents = Array.from(document.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
        
        if (selectedStudents.length === 0) {
            alert('Please select at least one student.');
            return;
        }
        
        // Validate target selections
        if (type === 'promotion') {
            if (!targetAcademicYear.value) {
                alert('Please select a target academic year.');
                return;
            }
        } else if (type === 'transfer') {
            if (!targetClass.value || !targetSection.value) {
                alert('Please select target class and section.');
                return;
            }
        }
        
        // Validate effective date
        if (!effectiveDate.value) {
            alert('Please select an effective date.');
            return;
        }
        
        // Confirm with user
        if (!confirm(`Are you sure you want to ${type === 'promotion' ? 'promote' : 'transfer'} ${selectedStudents.length} students?`)) {
            return;
        }
        
        // Prepare data for submission
        const formData = new FormData();
        formData.append('action', type === 'promotion' ? 'promote' : 'transfer');
        formData.append('student_ids', JSON.stringify(selectedStudents));
        formData.append('effective_date', effectiveDate.value);
        
        if (type === 'promotion') {
            formData.append('target_academic_year_id', targetAcademicYear.value);
        } else {
            formData.append('target_class_id', targetClass.value);
            formData.append('target_section_id', targetSection.value);
        }
        
        // Show progress
        progressContainer.style.display = 'block';
        progressBar.style.width = '0%';
        progressText.textContent = 'Processing...';
        progressPercentage.textContent = '0%';
        
        // Hide results
        resultsContainer.style.display = 'none';
        resultsContent.innerHTML = '';
        
        // Disable process button
        processBtn.disabled = true;
        
        // Send request
        fetch('student_transfer_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Update progress
            progressBar.style.width = '100%';
            progressText.textContent = 'Complete';
            progressPercentage.textContent = '100%';
            
            // Display results
            resultsContainer.style.display = 'block';
            
            if (data.success) {
                const successCount = data.success_count || 0;
                const failureCount = data.errors ? data.errors.length : 0;
                
                let resultHTML = `
                    <div class="result-item result-success">
                        ${successCount} students successfully ${type === 'promotion' ? 'promoted' : 'transferred'}.
                    </div>
                `;
                
                if (failureCount > 0) {
                    resultHTML += `
                        <div class="result-item result-error">
                            ${failureCount} students could not be ${type === 'promotion' ? 'promoted' : 'transferred'}.
                        </div>
                    `;
                    
                    // Add error details
                    data.errors.forEach(error => {
                        resultHTML += `
                            <div class="result-item result-error">
                                ${error}
                            </div>
                        `;
                    });
                }
                
                resultsContent.innerHTML = resultHTML;
                
                // Reload students table
                loadStudents();
            } else {
                resultsContent.innerHTML = `
                    <div class="result-item result-error">
                        ${data.message || 'An error occurred during the operation.'}
                    </div>
                `;
            }
            
            // Re-enable process button
            processBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Update progress
            progressBar.style.width = '100%';
            progressText.textContent = 'Failed';
            progressPercentage.textContent = '100%';
            
            // Display error
            resultsContainer.style.display = 'block';
            resultsContent.innerHTML = `
                <div class="result-item result-error">
                    An error occurred during the operation. Please try again.
                </div>
            `;
            
            // Re-enable process button
            processBtn.disabled = false;
        });
    }
    
    function resetForm() {
        // Reset dropdowns
        transferType.value = '';
        promotionFields.style.display = 'none';
        transferFields.style.display = 'none';
        
        // Hide progress and results
        progressContainer.style.display = 'none';
        resultsContainer.style.display = 'none';
        
        // Reset student selection
        selectAll.checked = false;
        const selectAllTable = document.getElementById('selectAllTable');
        if (selectAllTable) {
            selectAllTable.checked = false;
        }
        
        document.querySelectorAll('.student-checkbox').forEach(cb => {
            cb.checked = false;
        });
        
        // Disable process button
        processBtn.disabled = true;
    }
});
</script>

</body>
</html> 