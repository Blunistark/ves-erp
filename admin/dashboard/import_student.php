<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Students</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/import_student.css">
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
            <h1 class="header-title">Import Students</h1>
            <span class="header-path">Dashboard > Students > Import</span>
        </header>

        <!-- Add success notification banner (hidden by default) -->
        <div id="successBanner" class="success-banner" style="display: none; background-color: #d1fae5; border-left: 4px solid #047857; padding: 16px; margin-bottom: 20px; border-radius: 4px;">
            <div style="display: flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="#047857" style="min-width: 24px; margin-right: 12px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <div>
                    <h3 style="margin: 0 0 4px 0; color: #047857; font-weight: 600;">Import Successful</h3>
                    <p id="successMessage" style="margin: 0; color: #065f46;">Students have been successfully imported into the system.</p>
                </div>
            </div>
            <div style="margin-top: 12px; text-align: right;">
                <a href="manage_student.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Manage Students
                </a>
                <button id="closeBanner" class="btn btn-outline" style="margin-left: 8px;">Close</button>
            </div>
        </div>

        <main class="dashboard-content">
            <div class="import-container">
                <!-- Left column - Import form -->
                <div class="card">
                    <h2 class="card-title">Import Student Data</h2>
                    
                    <!-- Diagnostic tools for admins -->
                    <div style="margin-bottom: 15px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; font-size: 0.9em;">
                        <strong>Admin Tools:</strong> 
                        <a href="check_db.php" target="_blank" style="margin-right: 10px;">Database Check</a> | 
                        <a href="log_viewer.php" target="_blank">View Logs</a>
                    </div>
                    
                    <div class="form-group">
                        <label for="academicYear" class="form-label required">Academic Year</label>
                        <div style="display: flex; gap: 10px;">
                        <select id="academicYear" class="form-select">
                                <!-- Will be populated from database -->
                        </select>
                            <button type="button" class="btn btn-outline" id="addAcademicYearBtn" style="white-space: nowrap;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Year
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="semester" class="form-label">Semester (Optional)</label>
                        <select id="semester" class="form-select">
                            <option value="">Select Semester</option>
                            <option value="Spring">Spring Semester</option>
                            <option value="Fall">Fall Semester</option>
                            <option value="Summer">Summer Semester</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="class" class="form-label">Class (Optional)</label>
                        <select id="class" class="form-select">
                            <option value="">Select Class</option>
                            <option value="1">Class 1</option>
                            <option value="2">Class 2</option>
                            <option value="3">Class 3</option>
                            <option value="4">Class 4</option>
                            <option value="5">Class 5</option>
                            <option value="6">Class 6</option>
                            <option value="7">Class 7</option>
                            <option value="8">Class 8</option>
                            <option value="9">Class 9</option>
                            <option value="10">Class 10</option>
                            <option value="11">Class 11</option>
                            <option value="12">Class 12</option>
                        </select>
                        <small class="form-hint">Will use class info from CSV if not selected here</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="section" class="form-label">Section (Optional)</label>
                        <select id="section" class="form-select">
                            <option value="">Select Section</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                        <small class="form-hint">Will use section info from CSV if not selected here</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Parent Information</label>
                        <div class="checkbox-wrapper">
                            <input type="checkbox" id="createParentAccounts" class="form-checkbox" checked>
                            <label for="createParentAccounts" class="checkbox-label">Import parent information with student data</label>
                        </div>
                        <small class="form-hint">Parents will use the same account as their child to access the portal</small>
                    </div>

                    <!-- Template download -->
                    <div class="template-download">
                        <svg xmlns="http://www.w3.org/2000/svg" class="template-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="template-text">Download the template file to ensure proper data formatting for import.</span>
                        <a href="student_template_download.php" class="download-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="download-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download
                        </a>
                    </div>

                    <!-- File upload area -->
                    <label for="csvFile" class="file-upload-container" id="dropZone">
                        <svg xmlns="http://www.w3.org/2000/svg" class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <h3 class="upload-title">Upload CSV or Excel File</h3>
                        <p class="upload-subtitle">Drag and drop or click to browse</p>
                        <span class="upload-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            Choose file
                        </span>
                        <input type="file" id="csvFile" class="file-input" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" style="display: block !important;">
                    </label>

                    <div id="selectedFileContainer" style="display: none;" class="selected-file">
                        <svg xmlns="http://www.w3.org/2000/svg" class="file-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <div class="file-details">
                            <div class="file-name" id="fileName">students.csv</div>
                            <div class="file-size" id="fileSize">256 KB</div>
                        </div>
                        <button type="button" class="remove-file" id="removeFile">
                            <svg xmlns="http://www.w3.org/2000/svg" class="remove-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>

                    <div class="btn-container">
                        <button type="button" class="btn btn-outline">Cancel</button>
                        <button type="button" class="btn btn-primary" id="validateBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            Validate Data
                        </button>
                    </div>
                </div>

                <!-- Right column - Field mapping and preview -->
                <div class="card">
                    <h2 class="card-title">Field Mapping</h2>
                    <p>Match the columns in your file with the required fields in the system.</p>
                    
                    <table class="mapping-table">
                        <thead>
                            <tr>
                                <th>CSV Column</th>
                                <th>System Field</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Mapping table rows will be dynamically rendered here by JS after validation -->
                        </tbody>
                    </table>

                    <h3 class="card-title" style="margin-top: 2rem;">Data Preview</h3>
                    <p>Review the data before importing. Rows with errors are highlighted in red.</p>

                    <div class="preview-container">
                        <table class="preview-table">
                            <thead>
                                <tr></tr>
                            </thead>
                            <tbody>
                                <!-- Preview rows will be dynamically rendered here by JS after validation -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Import progress (hidden initially) -->
                    <div id="importProgress" class="import-progress" style="display: none;">
                        <h3 class="progress-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Import Progress
                        </h3>
                        <div class="progress-bar-container">
                            <div class="progress-bar" id="progressBar" style="width: 0%;"></div>
                        </div>
                        <div class="progress-info">
                            <span id="progressText">Processing records...</span>
                            <span id="progressPercent">0%</span>
                        </div>
                        
                        <div class="progress-stats">
                            <div class="stat-item">
                                <div class="stat-value success-value" id="successCount">0</div>
                                <div class="stat-label">Successful</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value error-value" id="errorCount">0</div>
                                <div class="stat-label">Failed</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value warning-value" id="warningCount">0</div>
                                <div class="stat-label">Warnings</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value" id="totalCount">0</div>
                                <div class="stat-label">Total</div>
                            </div>
                        </div>
                    </div>

                    <!-- Credentials Display (hidden initially) -->
                    <div id="credentialsDisplay" class="credentials-display" style="display: none;">
                        <h3 class="credentials-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                            </svg>
                            Login Credentials
                        </h3>
                        
                        <div class="tabs">
                            <button class="tab-btn active" data-tab="student-tab">Student/Family Accounts</button>
                            <button class="tab-btn" data-tab="parent-tab">Parent Information</button>
                        </div>
                        
                        <div class="tab-content">
                            <div id="student-tab" class="tab-pane active">
                                <div class="table-container">
                                    <table class="credentials-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Admission Number</th>
                                                <th>Username</th>
                                                <th>Password</th>
                                                <th>Email</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentCredentialsBody">
                                            <!-- Student credentials will be inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div id="parent-tab" class="tab-pane">
                                <div class="table-container">
                                    <table class="credentials-table">
                                        <thead>
                                            <tr>
                                                <th>Student Name</th>
                                                <th>Admission Number</th>
                                                <th>Parent Name</th>
                                                <th>Relationship</th>
                                                <th>Note</th>
                                            </tr>
                                        </thead>
                                        <tbody id="parentCredentialsBody">
                                            <!-- Parent credentials will be inserted here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="btn-container">
                            <button type="button" class="btn btn-primary" id="downloadCredentials">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download Credentials
                            </button>
                        </div>
                    </div>

                    <div class="btn-container">
                        <button type="button" class="btn btn-outline">Back</button>
                        <button type="button" class="btn btn-primary" id="importBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import Students
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add PapaParse CDN for robust CSV parsing -->
    <script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>

    <script>
        const REQUIRED_FIELDS = [
            'Admission Number', 'First Name', 'Last Name', 'Date of Birth', 'Gender', 'Admission Date', 'Class', 'Section', 'Academic Year', 'Address', 'City'
        ];

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Load academic years
            fetchAcademicYears();
            
            const dropZone = document.getElementById('dropZone');
            const csvFile = document.getElementById('csvFile');
            const selectedFileContainer = document.getElementById('selectedFileContainer');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const removeFile = document.getElementById('removeFile');
            const validateBtn = document.getElementById('validateBtn');
            const mappingTable = document.querySelector('.mapping-table tbody');
            const previewTable = document.querySelector('.preview-table');
            const importProgress = document.getElementById('importProgress');
            const credentialsDisplay = document.getElementById('credentialsDisplay');
            
            // Tab functionality for credentials
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tabId = this.dataset.tab;
                    
                    // Remove active class from all buttons and panes
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));
            
                    // Add active class to current button and pane
                    this.classList.add('active');
                    document.getElementById(tabId).classList.add('active');
                });
            });
            
            // Drag and drop functionality
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                dropZone.classList.add('highlight');
            }
            
            function unhighlight() {
                dropZone.classList.remove('highlight');
            }
            
            dropZone.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                csvFile.files = files;
                handleFiles(files);
            }
            
            csvFile.addEventListener('change', function() {
                handleFiles(this.files);
            });
            
            function handleFiles(files) {
                if (files.length) {
                    updateFileInfo(files[0]);
                    selectedFileContainer.style.display = 'flex';
                }
            }
            
            function updateFileInfo(file) {
                fileName.textContent = file.name;
                
                // Format file size
                let size = file.size;
                const units = ['B', 'KB', 'MB', 'GB'];
                let unitIndex = 0;
                
                while (size > 1024 && unitIndex < units.length - 1) {
                    size /= 1024;
                    unitIndex++;
                }
                
                fileSize.textContent = Math.round(size * 100) / 100 + ' ' + units[unitIndex];
            }
            
            removeFile.addEventListener('click', function() {
                csvFile.value = '';
                selectedFileContainer.style.display = 'none';
                clearTables();
            });
            
            function clearTables() {
                mappingTable.innerHTML = '';
                previewTable.querySelector('thead tr').innerHTML = '';
                previewTable.querySelector('tbody').innerHTML = '';
                }
            
            // Validate button click
            validateBtn.addEventListener('click', function() {
                if (!csvFile.files.length) {
                    alert('Please select a file first.');
                    return;
                }
                
                const academicYear = document.getElementById('academicYear').value;
                
                if (!academicYear) {
                    alert('Please select an Academic Year.');
                    return;
                }
                
                // Other fields are now optional for validation
                validateFile();
            });
            
            function validateFile() {
                const file = csvFile.files[0];
                const formData = new FormData();
                formData.append('action', 'validate');
                formData.append('csvFile', file);
                
                fetch('import_student_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayPreview(data.preview);
                        // Update the validate button to show import button
                        validateBtn.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            Import Data
                        `;
                        validateBtn.removeEventListener('click', validateFile);
                        validateBtn.addEventListener('click', importData);
                    } else {
                        alert(data.message || 'Validation failed. Please check the file format.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during validation.');
                });
            }
            
            function displayPreview(preview) {
                if (!preview || !preview.length) return;
                
                // Get headers from the first row
                const headers = Object.keys(preview[0].data);
                
                // Clear existing preview
                clearTables();
                
                // Populate mapping table
                headers.forEach(header => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${header}</td>
                        <td>${mapColumnToSystemField(header)}</td>
                    `;
                    mappingTable.appendChild(row);
                });
                
                // Create table headers
                const headerRow = previewTable.querySelector('thead tr');
                headers.forEach(header => {
                    const th = document.createElement('th');
                    th.textContent = header;
                    headerRow.appendChild(th);
                });
                
                // Add status column
                const statusTh = document.createElement('th');
                statusTh.textContent = 'Status';
                headerRow.appendChild(statusTh);
                
                // Populate preview table with data
                const tbody = previewTable.querySelector('tbody');
                preview.forEach(item => {
                    const row = document.createElement('tr');
                    row.dataset.rowNum = item.rowNum;
                    
                    if (item.status === 'error') {
                        row.classList.add('row-error');
                    } else if (item.status === 'warning') {
                        row.classList.add('row-warning');
                    }
                    
                    headers.forEach(header => {
                        const td = document.createElement('td');
                        td.textContent = item.data[header] || '';
                        row.appendChild(td);
                    });
                    
                    // Add status cell
                    const statusTd = document.createElement('td');
                    if (item.status === 'error') {
                        statusTd.innerHTML = `<span class="status-badge error">${item.errors.join(', ')}</span>`;
                    } else if (item.status === 'warning') {
                        statusTd.innerHTML = `<span class="status-badge warning">${item.warnings.join(', ')}</span>`;
                    } else {
                        statusTd.innerHTML = '<span class="status-badge valid">Valid</span>';
                    }
                    row.appendChild(statusTd);
                    
                    tbody.appendChild(row);
                });
            }
            
            function mapColumnToSystemField(column) {
                // Map CSV column names to system field names
                const mapping = {
                    'Sl.No': 'Serial Number',
                    'Class': 'Class',
                    'Section': 'Section',
                    'Name': 'Full Name',
                    'Gender': 'Gender',
                    'DOB': 'Date of Birth',
                    'Mother Name': 'Mother\'s Name',
                    'Father Name': 'Father\'s Name',
                    'Address': 'Address',
                    'Pincode': 'Postal Code',
                    'Mobile No.': 'Phone',
                    'Alternate Mobile No.': 'Alternative Phone',
                    'Contact Email Id': 'Email',
                    'Mother Tongue': 'Mother Tongue',
                    'Blood Group': 'Blood Group',
                    'Admission No.': 'Admission Number',
                    'Admission Date': 'Admission Date',
                    'Student State Code': 'Student State Code'
                };
                
                return mapping[column] || 'Not Mapped';
            }
            
            function importData() {
                const academicYear = document.getElementById('academicYear').value;
                const createParentAccounts = document.getElementById('createParentAccounts').checked;
                
                // Verify academic year is selected
                if (!academicYear) {
                    alert('Please select an Academic Year.');
                    return;
                }
                
                // Get all valid rows from the preview table
                const validRows = Array.from(previewTable.querySelectorAll('tbody tr:not(.row-error)'));
                const totalValidRows = validRows.length;
                
                if (totalValidRows === 0) {
                    alert('No valid data to import.');
                    return;
                }
                
                if (!confirm(`Are you sure you want to import ${totalValidRows} students?`)) {
                    return;
                }
                
                // Extract data from valid rows
                const students = validRows.map(row => {
                    const rowNum = parseInt(row.dataset.rowNum);
                    const cells = row.querySelectorAll('td');
                    const data = {};
                    
                    // Get all headers except the last one (status)
                    const headers = Array.from(previewTable.querySelectorAll('thead th'));
                    headers.pop(); // Remove status column
                    
                    headers.forEach((header, index) => {
                        data[header.textContent] = cells[index].textContent;
                    });
                    
                    return { rowNum, data };
                });
                
                // Show progress
                importProgress.style.display = 'block';
                document.getElementById('totalCount').textContent = totalValidRows;
                
                // Prepare form data
                const formData = new FormData();
                formData.append('action', 'import');
                formData.append('students', JSON.stringify(students));
                formData.append('createParentAccounts', createParentAccounts);
                formData.append('academicYear', academicYear);
                
                // Send import request
                fetch('import_student_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update progress
                        const successCount = data.imported || 0;
                        const errorCount = (data.errors || []).length;
                        const duplicateCount = (data.duplicates || []).length;
                        
                        document.getElementById('successCount').textContent = successCount;
                        document.getElementById('errorCount').textContent = errorCount + duplicateCount;
                    document.getElementById('progressBar').style.width = '100%';
                        document.getElementById('progressText').textContent = 'Import completed';
                        document.getElementById('progressPercent').textContent = '100%';
                        
                        // Display credentials
                        if (data.student_credentials && data.student_credentials.length > 0) {
                            displayCredentials(data.student_credentials, data.parent_credentials || []);
                            credentialsDisplay.style.display = 'block';
                        }
                        
                        // Disable import button
                        importBtn.disabled = true;
                        importBtn.textContent = 'Import Completed';
                        
                        // Create success message
                        let successMessageText = `Successfully imported ${successCount} students.`;
                        if (errorCount > 0 || duplicateCount > 0) {
                            successMessageText += ` ${errorCount + duplicateCount} records were skipped due to errors or duplicates.`;
                        }
                        
                        // Show the success banner and update message
                        document.getElementById('successMessage').textContent = successMessageText;
                        document.getElementById('successBanner').style.display = 'block';
                        
                        // Scroll to top to make the success banner visible
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        alert(data.message || 'Import failed. Please try again.');
                        importProgress.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred during import.');
                    importProgress.style.display = 'none';
                });
            }
            
            function displayCredentials(studentCredentials, parentCredentials) {
                const studentBody = document.getElementById('studentCredentialsBody');
                const parentBody = document.getElementById('parentCredentialsBody');
                
                // Clear existing content
                studentBody.innerHTML = '';
                parentBody.innerHTML = '';
                
                // Display student/family credentials
                studentCredentials.forEach(student => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${student.name}</td>
                        <td>${student.admission_no}</td>
                        <td>${student.username}</td>
                        <td>${student.password}</td>
                        <td>${student.email}</td>
                    `;
                    studentBody.appendChild(row);
                });
                
                // Display parent information
                parentCredentials.forEach(parent => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${parent.student_name}</td>
                        <td>${parent.admission_no}</td>
                        <td>${parent.parent_name || 'N/A'}</td>
                        <td>${parent.relationship || 'N/A'}</td>
                        <td>Parents use the same login as the student</td>
                    `;
                    parentBody.appendChild(row);
                });
            }
            
            // Download credentials as CSV
            document.getElementById('downloadCredentials').addEventListener('click', function() {
                const activeTab = document.querySelector('.tab-pane.active').id;
                
                if (activeTab === 'student-tab') {
                    downloadCSV('student-credentials.csv', document.querySelector('#student-tab table'));
                } else {
                    downloadCSV('parent-credentials.csv', document.querySelector('#parent-tab table'));
                }
            });
            
            function downloadCSV(filename, table) {
                const rows = table.querySelectorAll('tr');
                const csv = [];
                
                // Get headers
                const headers = Array.from(rows[0].querySelectorAll('th')).map(th => th.textContent);
                csv.push(headers.join(','));
                
                // Get data rows
                for (let i = 1; i < rows.length; i++) {
                    const cells = rows[i].querySelectorAll('td');
                    const row = Array.from(cells).map(cell => {
                        // Escape commas and quotes
                        let content = cell.textContent.replace(/"/g, '""');
                        if (content.includes(',')) {
                            content = `"${content}"`;
                        }
                        return content;
                    });
                    csv.push(row.join(','));
                }
                
                // Create and download the file
                const csvContent = csv.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                
                link.href = URL.createObjectURL(blob);
                link.setAttribute('download', filename);
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
            
            // Function to fetch academic years from the server
            function fetchAcademicYears() {
                fetch('student_actions.php?fetch_academic_years=1')
                    .then(response => response.json())
                    .then(years => {
                        const select = document.getElementById('academicYear');
                        select.innerHTML = '<option value="">Select Academic Year</option>';
                        
                        if (years && years.length > 0) {
                            // Sort years by name descending (newest first)
                            years.sort((a, b) => {
                                // Extract the first year from the name (e.g., "2023" from "2023-2024")
                                const yearA = parseInt(a.name.split('-')[0]);
                                const yearB = parseInt(b.name.split('-')[0]);
                                return yearB - yearA; // Descending order
                            });
                            
                            years.forEach(year => {
                                const option = document.createElement('option');
                                option.value = year.id;
                                option.textContent = year.name + (year.is_current == 1 ? ' (Current)' : '');
                                if (year.is_current == 1) {
                                    option.selected = true;
                                }
                                select.appendChild(option);
                            });
                            
                            // If there's no current year selected, select the first one
                            if (!select.value && years.length > 0) {
                                select.value = years[0].id;
                            }
                        } else {
                            // If no academic years found, show a message and prompt to add one
                            const option = document.createElement('option');
                            option.value = "";
                            option.textContent = "No academic years found";
                            option.disabled = true;
                            option.selected = true;
                            select.appendChild(option);
                            
                            // Show an alert after a short delay
                            setTimeout(() => {
                                alert("No academic years found. Please add an academic year first.");
                                document.getElementById('addAcademicYearBtn').click();
                            }, 500);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching academic years:', error);
                        const select = document.getElementById('academicYear');
                        select.innerHTML = '<option value="">Error loading academic years</option>';
                    });
            }

            // Add academic year popup functionality
            document.getElementById('addAcademicYearBtn').addEventListener('click', function() {
                document.getElementById('academicYearModal').style.display = 'block';
            });
            
            // Close modal handlers
            document.querySelector('.modal-close').addEventListener('click', closeModal);
            document.querySelector('.modal-cancel').addEventListener('click', closeModal);
            
            function closeModal() {
                document.getElementById('academicYearModal').style.display = 'none';
            }
            
            // Add event listener for closing the success banner
            document.getElementById('closeBanner').addEventListener('click', function() {
                document.getElementById('successBanner').style.display = 'none';
            });
            
            function showMessage(message, isError = false) {
                const messageEl = document.getElementById('addYearMessage');
                messageEl.textContent = message;
                messageEl.className = isError ? 'error-message' : 'success-message';
                messageEl.style.backgroundColor = isError ? '#fee2e2' : '#ecfdf5';
                messageEl.style.color = isError ? '#b91c1c' : '#047857';
                messageEl.style.display = 'block';
                
                // Scroll to the message
                messageEl.scrollIntoView({ behavior: 'smooth' });
            }
            
            // Form submission for adding academic year
            document.getElementById('addAcademicYearForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const yearName = document.getElementById('yearName').value;
                const isCurrent = document.getElementById('isCurrentYear').checked ? 1 : 0;
                
                // Basic validation (in addition to HTML5 validation)
                if (!yearName.match(/^\d{4}[^\d]*\d{2,4}$/)) {
                    showMessage('Please enter a valid academic year format (e.g., 2023-2024 or 2023-24)', true);
                    return;
                }
                
                // Show processing state
                const submitBtn = document.getElementById('addYearBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Adding...';
                showMessage('Adding academic year...', false);
                
                const formData = new FormData();
                formData.append('action', 'add_academic_year');
                formData.append('name', yearName);
                formData.append('is_current', isCurrent);
                
                fetch('student_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Check if the response is JSON
                    const contentType = response.headers.get('content-type');
                    if (contentType && contentType.indexOf('application/json') !== -1) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            throw new Error('Received non-JSON response: ' + text);
                        });
                    }
                })
                .then(data => {
                    if (data.success) {
                        showMessage('Academic year added successfully!');
                        
                        // Wait a moment before closing the modal and refreshing
                        setTimeout(() => {
                            closeModal();
                            fetchAcademicYears(); // Refresh the dropdown
                        }, 1500);
                    } else {
                        console.error('Server error:', data);
                        showMessage(data.message || 'Failed to add academic year. Please check server logs.', true);
                    }
                })
                .catch(error => {
                    console.error('Request error:', error);
                    showMessage('An error occurred: ' + error.message, true);
                })
                .finally(() => {
                    // Reset button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Add Year';
                });
            });
        });
    </script>

    <!-- Add CSS for credentials display -->
    <style>
    .credentials-display {
        margin-top: 2rem;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
    }

    .credentials-title {
        display: flex;
        align-items: center;
        font-size: 1.25rem;
        margin-bottom: 1.5rem;
        color: #2563eb;
    }

    .credentials-title svg {
        margin-right: 0.5rem;
        stroke: #2563eb;
    }

    .tabs {
        display: flex;
        border-bottom: 1px solid #e5e7eb;
        margin-bottom: 1rem;
    }

    .tab-btn {
        padding: 0.75rem 1rem;
        background: none;
        border: none;
        cursor: pointer;
        font-weight: 500;
        color: #6b7280;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
    }

    .tab-btn:hover {
        color: #2563eb;
    }

    .tab-btn.active {
        color: #2563eb;
        border-bottom-color: #2563eb;
    }

    .tab-content {
        margin-top: 1rem;
    }

    .tab-pane {
        display: none;
    }

    .tab-pane.active {
        display: block;
    }

    .table-container {
        max-height: 400px;
        overflow-y: auto;
        margin-bottom: 1rem;
    }

    .credentials-table {
        width: 100%;
        border-collapse: collapse;
    }

    .credentials-table th,
    .credentials-table td {
        padding: 0.75rem 1rem;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .credentials-table th {
        background-color: #f9fafb;
        font-weight: 600;
        color: #374151;
    }

    .credentials-table tr:last-child td {
        border-bottom: none;
    }

    .checkbox-wrapper {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .form-checkbox {
        margin-right: 0.5rem;
    }

    .form-hint {
        color: #6b7280;
        font-size: 0.875rem;
    }
    </style>

    <!-- Academic Year Modal -->
    <div id="academicYearModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
        <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; border-radius: 8px; width: 400px; max-width: 80%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0;">Add Academic Year</h3>
                <span class="modal-close" style="cursor: pointer; font-size: 24px; font-weight: bold;">&times;</span>
            </div>
            
            <div id="addYearMessage" style="margin-bottom: 15px; padding: 10px; border-radius: 4px; display: none;"></div>
            
            <form id="addAcademicYearForm">
                <div class="form-group">
                    <label for="yearName" class="form-label required">Academic Year Name</label>
                    <input type="text" id="yearName" class="form-input" placeholder="e.g., 2023-2024" required 
                           pattern="\d{4}[^\d]*\d{2,4}" title="Format: YYYY-YYYY or YYYY-YY">
                    <small class="form-hint">Format: YYYY-YYYY or YYYY-YY</small>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="isCurrentYear" class="form-checkbox" checked>
                        <label for="isCurrentYear" class="checkbox-label">Set as current academic year</label>
                    </div>
                </div>
                
                <div class="btn-container" style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" class="btn btn-outline modal-cancel">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addYearBtn">Add Year</button>
                </div>
            </form>
            
            <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                <p style="margin-top: 0; font-size: 0.9em;">Having trouble? <a href="check_db.php" target="_blank">Run database diagnostic</a></p>
            </div>
        </div>
    </div>
</body>
</html>