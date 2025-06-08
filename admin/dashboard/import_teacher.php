<?php include 'sidebar.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Teachers</title>
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
            <h1 class="header-title">Import Teachers</h1>
            <span class="header-path">Dashboard > Teachers > Import</span>
        </header>
        <main class="dashboard-content">
            <div class="import-container">
                <div class="card">
                    <h2 class="card-title">Import Teacher Data</h2>
                    <div class="template-download">
                        <svg xmlns="http://www.w3.org/2000/svg" class="template-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="template-text">Download the template file to ensure proper data formatting for import.</span>
                        <a href="teacher_import_template.csv" class="download-btn" download>Download</a>
                    </div>
                    <label for="csvFile" class="file-upload-container" id="dropZone">
                        <svg xmlns="http://www.w3.org/2000/svg" class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <h3 class="upload-title">Upload CSV File</h3>
                        <p class="upload-subtitle">Drag and drop or click to browse</p>
                        <span class="upload-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            Choose file
                        </span>
                        <input type="file" id="csvFile" class="file-input" accept=".csv" style="display: block !important;">
                    </label>
                    <div id="selectedFileContainer" style="display: none;" class="selected-file">
                        <svg xmlns="http://www.w3.org/2000/svg" class="file-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <div class="file-details">
                            <div class="file-name" id="fileName">teachers.csv</div>
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
                    <div id="importProgress" class="import-progress" style="display: none;">
                        <h3 class="progress-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Import Progress
                        </h3>
                        <div class="progress-bar-container">
                            <div class="progress-bar" id="progressBar" style="width: 70%;"></div>
                        </div>
                        <div class="progress-info">
                            <span>Processing records...</span>
                            <span>70%</span>
                        </div>
                        <div class="progress-stats">
                            <div class="stat-item">
                                <div class="stat-value success-value">0</div>
                                <div class="stat-label">Successful</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value error-value">0</div>
                                <div class="stat-label">Failed</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value warning-value">0</div>
                                <div class="stat-label">Warnings</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">0</div>
                                <div class="stat-label">Total Records</div>
                            </div>
                        </div>
                    </div>
                    <div class="btn-container">
                        <button type="button" class="btn btn-outline">Back</button>
                        <button type="button" class="btn btn-primary" id="importBtn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12" />
                            </svg>
                            Import Teachers
                        </button>
                    </div>
                </div>
            </div>
            <div id="importMessage" class="import-message" style="display:none;"></div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/papaparse@5.4.1/papaparse.min.js"></script>
    <script>
        const REQUIRED_FIELDS = [
            'employee_id','first_name','last_name','dob','gender','email','alt_email','phone','emergency_phone','joined_date','profile_photo','address','city','qualification','experience','username','designation','status'
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
            const fileInput = document.getElementById('csvFile');
            const dropZone = document.getElementById('dropZone');
            const selectedFileContainer = document.getElementById('selectedFileContainer');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const removeFileBtn = document.getElementById('removeFile');
            const validateBtn = document.getElementById('validateBtn');
            const importBtn = document.getElementById('importBtn');
            const importProgress = document.getElementById('importProgress');
            let previewData = [];
            let csvHeader = [];
            fileInput.addEventListener('change', handleFileSelect);
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
                dropZone.style.borderColor = '#667eea';
                dropZone.style.backgroundColor = '#f9fafb';
            }
            function unhighlight() {
                dropZone.style.borderColor = '#e5e7eb';
                dropZone.style.backgroundColor = '';
            }
            dropZone.addEventListener('drop', function(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                fileInput.files = files;
                handleFileSelect();
            });
            function handleFileSelect() {
                if (fileInput.files && fileInput.files[0]) {
                    const file = fileInput.files[0];
                    fileName.textContent = file.name;
                    fileSize.textContent = formatFileSize(file.size);
                    selectedFileContainer.style.display = 'flex';
                }
            }
            function formatFileSize(bytes) {
                if (bytes < 1024) return bytes + ' bytes';
                else if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                else return (bytes / 1048576).toFixed(1) + ' MB';
            }
            removeFileBtn.addEventListener('click', function() {
                fileInput.value = '';
                selectedFileContainer.style.display = 'none';
            });
            const downloadBtn = document.querySelector('.download-btn');
            downloadBtn.addEventListener('click', function() {
                window.location.href = 'teacher_import_template.csv';
            });
            function renderPreviewTableHeader() {
                const thead = document.querySelector('.preview-table thead tr');
                thead.innerHTML = '<th><input type="checkbox" id="selectAllRows"></th>' +
                    '<th>Row</th>' +
                    REQUIRED_FIELDS.map(col => `<th>${col}</th>`).join('');
            }
            renderPreviewTableHeader();
            validateBtn.addEventListener('click', function() {
                if (!fileInput.files[0]) {
                    alert('Please select a CSV file.');
                    return;
                }
                validateBtn.disabled = true;
                validateBtn.innerHTML = 'Loading...';
                Papa.parse(fileInput.files[0], {
                    header: true,
                    skipEmptyLines: true,
                    complete: function(results) {
                        csvHeader = results.meta.fields;
                        previewData = results.data.map((row, idx) => ({ rowNum: idx+1, data: row }));
                        renderPreviewTable(previewData);
                        renderMappingTable(csvHeader);
                        validateBtn.disabled = false;
                        validateBtn.innerHTML = 'Validate Data';
                    },
                    error: function() {
                        validateBtn.disabled = false;
                        validateBtn.innerHTML = 'Validate Data';
                        alert('Failed to parse CSV.');
                    }
                });
            });
            function renderPreviewTable(preview) {
                renderPreviewTableHeader();
                const tbody = document.querySelector('.preview-table tbody');
                tbody.innerHTML = '';
                if (!preview.length) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td colspan="${REQUIRED_FIELDS.length+2}" style="text-align:center;">No data. Please validate a file.</td>`;
                    tbody.appendChild(tr);
                    return;
                }
                preview.forEach((row, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td><input type="checkbox" class="row-select" data-row="${idx+1}" checked></td>` +
                        `<td>${row.rowNum}</td>` +
                        REQUIRED_FIELDS.map(col => `<td>${row.data[col]||''}</td>`).join('');
                    tbody.appendChild(tr);
                });
                const selectAll = document.getElementById('selectAllRows');
                selectAll.addEventListener('change', function() {
                    document.querySelectorAll('.row-select').forEach(cb => {
                        cb.checked = selectAll.checked;
                    });
                });
            }
            function renderMappingTable(header) {
                const mappingTable = document.querySelector('.mapping-table tbody');
                mappingTable.innerHTML = '';
                REQUIRED_FIELDS.forEach(field => {
                    const normalizedField = field.replace(/['\s]/g, '').toLowerCase();
                    let matchIdx = header.findIndex(h => h.replace(/['\s]/g, '').toLowerCase() === normalizedField);
                    const options = header.map((h, i) => `<option value="${h}"${i===matchIdx?' selected':''}>${h}</option>`).join('');
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td><select class="form-select">${options}</select></td><td>${field}${['employee_id','first_name','last_name','dob','gender','email'].includes(field) ? ' <span class="field-required">*</span>' : ''}</td>`;
                    mappingTable.appendChild(tr);
                });
            }
            importBtn.addEventListener('click', function() {
                const selectedRows = Array.from(document.querySelectorAll('.row-select:checked')).map(cb => parseInt(cb.getAttribute('data-row')));
                if (!selectedRows.length) {
                    alert('Please select at least one row to import.');
                    return;
                }
                const rowsToImport = selectedRows.map(idx => previewData[idx]);
                importBtn.disabled = true;
                importBtn.innerHTML = 'Importing...';
                importProgress.style.display = 'block';
                const formData = new FormData();
                formData.append('action', 'import');
                formData.append('teachers', JSON.stringify(rowsToImport));
                fetch('import_teacher_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    importBtn.disabled = false;
                    importBtn.innerHTML = 'Import Teachers';
                    document.getElementById('progressBar').style.width = '100%';
                    document.querySelector('.progress-info span:last-child').textContent = '100%';
                    document.querySelector('.success-value').textContent = data.imported;
                    document.querySelector('.error-value').textContent = data.errors ? data.errors.length : 0;
                    document.querySelector('.warning-value').textContent = data.duplicates ? data.duplicates.length : 0;
                    document.querySelector('.stat-value:not(.success-value):not(.error-value):not(.warning-value)').textContent = previewData.length;
                    let msg = `Import completed.\nImported: ${data.imported}`;
                    if (data.duplicates && data.duplicates.length) {
                        msg += `\nDuplicates (not imported):\n` + data.duplicates.map(d => `Row ${d.rowNum} (Employee ID: ${d.employee_id}, Email: ${d.email})`).join('\n');
                    }
                    if (data.errors && data.errors.length) {
                        msg += `\nErrors:\n` + data.errors.join('\n');
                    }
                    const importMessage = document.getElementById('importMessage');
                    importMessage.style.display = 'block';
                    importMessage.innerHTML = msg.replace(/\n/g, '<br>');
                    importMessage.className = 'import-message ' + (data.errors && data.errors.length ? 'error' : 'success');
                    importMessage.scrollIntoView({ behavior: 'smooth' });
                })
                .catch(() => {
                    importBtn.disabled = false;
                    importBtn.innerHTML = 'Import Teachers';
                    document.getElementById('progressBar').style.width = '100%';
                    document.querySelector('.progress-info span:last-child').textContent = '100%';
                    alert('Import failed due to a server or network error.');
                });
            });
        });
    </script>
</body>
</html> 