<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Transfer Records</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/manage_student.css">
    <style>
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
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
        
        .form-select, .form-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            background-color: #f9fafb;
            font-size: 0.875rem;
        }
        
        .search-container {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
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
            height: 38px;
        }
        
        .btn-primary {
            background-color: #2563eb;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #374151;
        }
        
        tr:hover {
            background-color: #f3f4f6;
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
        
        .badge-amber {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6b7280;
        }
        
        .empty-state-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 1rem;
            color: #9ca3af;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.5rem;
        }
        
        .pagination-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border-radius: 0.375rem;
            color: #4b5563;
            background-color: #fff;
            border: 1px solid #d1d5db;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .pagination-item.active {
            background-color: #2563eb;
            color: white;
            border-color: #2563eb;
        }
        
        .pagination-item:hover:not(.active) {
            background-color: #f3f4f6;
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
            <h1 class="header-title">Student Transfer Records</h1>
            <span class="header-path">Dashboard > Students > Transfer Records</span>
        </header>

        <main class="dashboard-content">
            <div class="card">
                <div class="search-container">
                    <div class="form-group">
                        <label for="filterStudent" class="form-label">Student</label>
                        <input type="text" id="filterStudent" class="form-input" placeholder="Search by name or ID...">
                    </div>
                    
                    <div class="form-group">
                        <label for="filterClass" class="form-label">Class</label>
                        <select id="filterClass" class="form-select">
                            <option value="">All Classes</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="filterType" class="form-label">Transfer Type</label>
                        <select id="filterType" class="form-select">
                            <option value="">All Types</option>
                            <option value="Annual Promotion">Promotion</option>
                            <option value="Transfer to different class/section">Transfer</option>
                        </select>
                    </div>
                    
                    <button type="button" class="btn btn-primary" id="searchBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        Search
                    </button>
                </div>
            </div>
            
            <div class="card" id="recordsCard">
                <h2 class="card-title">Transfer History</h2>
                
                <div id="loading" style="display: none;">
                    <p class="text-center">Loading records...</p>
                </div>
                
                <div id="recordsContainer">
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p>Use the search filters above to find transfer records.</p>
                    </div>
                </div>
                
                <div id="pagination" class="pagination" style="display: none;"></div>
            </div>
        </main>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Element references
    const filterStudent = document.getElementById('filterStudent');
    const filterClass = document.getElementById('filterClass');
    const filterType = document.getElementById('filterType');
    const searchBtn = document.getElementById('searchBtn');
    const recordsContainer = document.getElementById('recordsContainer');
    const loading = document.getElementById('loading');
    const pagination = document.getElementById('pagination');
    
    // Load initial data
    loadClasses();
    
    // Event listeners
    searchBtn.addEventListener('click', searchRecords);
    
    // Functions
    function loadClasses() {
        fetch('student_actions.php?fetch_classes=1')
            .then(response => response.json())
            .then(classes => {
                filterClass.innerHTML = '<option value="">All Classes</option>';
                
                classes.forEach(cls => {
                    const option = document.createElement('option');
                    option.value = cls.id;
                    option.textContent = cls.name;
                    filterClass.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading classes:', error));
    }
    
    function searchRecords(page = 1) {
        // Show loading indicator
        recordsContainer.style.display = 'none';
        pagination.style.display = 'none';
        loading.style.display = 'block';
        
        // Build query params
        const params = new URLSearchParams();
        params.append('action', 'fetch_transfers');
        params.append('page', page);
        
        if (filterStudent.value) {
            params.append('student', filterStudent.value);
        }
        
        if (filterClass.value) {
            params.append('class', filterClass.value);
        }
        
        if (filterType.value) {
            params.append('reason', filterType.value);
        }
        
        // Fetch data
        fetch(`student_transfer_action.php?${params.toString()}`)
            .then(response => response.json())
            .then(data => {
                // Hide loading indicator
                loading.style.display = 'none';
                recordsContainer.style.display = 'block';
                
                if (data.success) {
                    if (data.records.length === 0) {
                        recordsContainer.innerHTML = `
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p>No transfer records found matching your criteria.</p>
                            </div>
                        `;
                        pagination.style.display = 'none';
                    } else {
                        // Render records table
                        renderRecordsTable(data.records);
                        
                        // Generate pagination if needed
                        if (data.total_pages > 1) {
                            renderPagination(data.current_page, data.total_pages);
                            pagination.style.display = 'flex';
                        } else {
                            pagination.style.display = 'none';
                        }
                    }
                } else {
                    recordsContainer.innerHTML = `
                        <div class="empty-state">
                            <p>Error: ${data.message || 'Failed to load transfer records'}</p>
                        </div>
                    `;
                    pagination.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                loading.style.display = 'none';
                recordsContainer.style.display = 'block';
                recordsContainer.innerHTML = `
                    <div class="empty-state">
                        <p>Error: Failed to load transfer records. Please try again.</p>
                    </div>
                `;
                pagination.style.display = 'none';
            });
    }
    
    function renderRecordsTable(records) {
        const table = document.createElement('table');
        
        // Create table header
        const thead = document.createElement('thead');
        thead.innerHTML = `
            <tr>
                <th>Student</th>
                <th>From Class</th>
                <th>To Class</th>
                <th>Date</th>
                <th>Type</th>
            </tr>
        `;
        table.appendChild(thead);
        
        // Create table body
        const tbody = document.createElement('tbody');
        records.forEach(record => {
            const tr = document.createElement('tr');
            
            // Determine badge class based on reason
            let badgeClass = 'badge-blue';
            if (record.reason.includes('Promotion')) {
                badgeClass = 'badge-green';
            } else if (record.reason.includes('Transfer')) {
                badgeClass = 'badge-amber';
            }
            
            tr.innerHTML = `
                <td>${record.student_name} <br><small>${record.admission_number || ''}</small></td>
                <td>${record.from_class_name} ${record.from_section_name ? '- ' + record.from_section_name : ''}</td>
                <td>${record.to_class_name} ${record.to_section_name ? '- ' + record.to_section_name : ''}</td>
                <td>${new Date(record.transfer_date).toLocaleDateString()}</td>
                <td><span class="badge ${badgeClass}">${record.reason}</span></td>
            `;
            tbody.appendChild(tr);
        });
        table.appendChild(tbody);
        
        // Replace the container content
        recordsContainer.innerHTML = '';
        recordsContainer.appendChild(table);
    }
    
    function renderPagination(currentPage, totalPages) {
        currentPage = parseInt(currentPage);
        totalPages = parseInt(totalPages);
        
        pagination.innerHTML = '';
        
        // Previous button
        if (currentPage > 1) {
            const prev = document.createElement('div');
            prev.className = 'pagination-item';
            prev.innerHTML = '&laquo;';
            prev.addEventListener('click', () => searchRecords(currentPage - 1));
            pagination.appendChild(prev);
        }
        
        // Page numbers
        const maxButtons = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxButtons - 1);
        
        if (endPage - startPage + 1 < maxButtons) {
            startPage = Math.max(1, endPage - maxButtons + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const pageButton = document.createElement('div');
            pageButton.className = `pagination-item ${i === currentPage ? 'active' : ''}`;
            pageButton.textContent = i;
            
            if (i !== currentPage) {
                pageButton.addEventListener('click', () => searchRecords(i));
            }
            
            pagination.appendChild(pageButton);
        }
        
        // Next button
        if (currentPage < totalPages) {
            const next = document.createElement('div');
            next.className = 'pagination-item';
            next.innerHTML = '&raquo;';
            next.addEventListener('click', () => searchRecords(currentPage + 1));
            pagination.appendChild(next);
        }
    }
});
</script>

</body>
</html> 