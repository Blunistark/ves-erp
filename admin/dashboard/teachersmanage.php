<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/teachersmanage.css">
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
            <h1 class="header-title">Manage Teachers</h1>
            <span class="header-path">Dashboard > Teachers > Manage</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="teacherSearch" class="search-input" placeholder="Search by name, ID, department or subject...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="filterToggleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        Filter
                    </button>
                    <button class="btn btn-outline">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Export
                    </button>
                    <a href="teachersadd.php" class="btn btn-primary">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Teacher
                    </a>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel">
                <h3 class="filter-title">Filter Teachers</h3>
                <form class="filter-form">
                    <div class="filter-group">
                        <label class="filter-label">Designation</label>
                        <select class="filter-select">
                            <option value="">All Designations</option>
                            <option value="principal">Principal</option>
                            <option value="vice_principal">Vice Principal</option>
                            <option value="head_teacher">Head Teacher</option>
                            <option value="senior_teacher">Senior Teacher</option>
                            <option value="teacher">Teacher</option>
                            <option value="assistant_teacher">Assistant Teacher</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="filter-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="leave">On Leave</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Qualification</label>
                        <select class="filter-select">
                            <option value="">All Qualifications</option>
                            <option value="phd">Ph.D</option>
                            <option value="masters">Masters</option>
                            <option value="bachelors">Bachelors</option>
                            <option value="diploma">Diploma</option>
                            <option value="certificate">Certificate</option>
                        </select>
                    </div>
                </form>
                <div class="filter-actions">
                    <button class="filter-btn filter-btn-reset">Reset</button>
                    <button class="filter-btn filter-btn-apply">Apply Filters</button>
                </div>
            </div>

            <div class="teachers-table-container">
                <table class="teachers-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Employee ID</th>
                            <th>Qualification</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="teachersTableBody">
                        <!-- Dynamic rows will be inserted here by JS -->
                   </tbody>
               </table>
           </div>

           <div class="pagination">
               <div class="pagination-info">
                   Showing 1-7 of 35 teachers
               </div>
               <div class="pagination-buttons">
                   <button class="page-btn" disabled>
                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                       </svg>
                   </button>
                   <button class="page-btn active">1</button>
                   <button class="page-btn">2</button>
                   <button class="page-btn">3</button>
                   <button class="page-btn">4</button>
                   <button class="page-btn">5</button>
                   <button class="page-btn">
                       <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                       </svg>
                   </button>
               </div>
           </div>
       </main>
   </div>

   <!-- Delete Modal -->
   <div id="deleteTeacherModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999; align-items:center; justify-content:center;">
       <div style="background:#fff; padding:2rem; border-radius:8px; max-width:350px; margin:auto; text-align:center;">
           <h3>Confirm Delete</h3>
           <p>Are you sure you want to delete this teacher?</p>
           <div style="margin-top:1.5rem; display:flex; gap:1rem; justify-content:center;">
               <button id="modalTeacherCancel" class="btn btn-outline">Cancel</button>
               <button id="modalTeacherConfirm" class="btn btn-danger">Delete</button>
           </div>
       </div>
   </div>

   <script>
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

        // AJAX-powered teacher table with performance optimizations
       document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('teacherSearch');
            const filterPanel = document.getElementById('filterPanel');
           const filterToggleBtn = document.getElementById('filterToggleBtn');
            const filterForm = filterPanel.querySelector('.filter-form');
            const resetBtn = filterPanel.querySelector('.filter-btn-reset');
            const applyBtn = filterPanel.querySelector('.filter-btn-apply');
            const tableBody = document.getElementById('teachersTableBody');
            const paginationInfo = document.querySelector('.pagination-info');
            const paginationButtons = document.querySelector('.pagination-buttons');
            
            let currentPage = 1;
            let perPage = 10;
            let lastQuery = {};
            let deleteTeacherId = null;
            let isLoading = false;
            let searchTimeout = null;
            let cache = new Map(); // Simple cache for API responses
            
            const deleteTeacherModal = document.getElementById('deleteTeacherModal');
            const modalTeacherCancel = document.getElementById('modalTeacherCancel');
            const modalTeacherConfirm = document.getElementById('modalTeacherConfirm');

            // Debounce function for search
            function debounce(func, wait) {
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(searchTimeout);
                        func(...args);
                    };
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(later, wait);
                };
            }

            // Show loading state
            function showLoading() {
                if (isLoading) return;
                isLoading = true;
                tableBody.innerHTML = `
                    <tr>
                        <td colspan='6' style='text-align:center; padding: 40px;'>
                            <div style='display: flex; align-items: center; justify-content: center; gap: 10px;'>
                                <div style='width: 20px; height: 20px; border: 2px solid #f3f3f3; border-top: 2px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite;'></div>
                                <span>Loading teachers...</span>
                            </div>
                        </td>
                    </tr>
                `;
            }

            // Hide loading state
            function hideLoading() {
                isLoading = false;
            }

            function getFilters() {
                const filters = {};
                const selects = filterForm.querySelectorAll('select');
                selects.forEach(sel => {
                    if (sel.value) {
                        const label = sel.previousElementSibling.textContent.trim().toLowerCase().replace(/\s+/g, '_');
                        filters[label] = sel.value;
                    }
                });
                return filters;
            }

            function fetchTeachers(page = 1, useCache = true) {
                const search = searchInput.value.trim();
                const filters = getFilters();
                const params = new URLSearchParams({
                    search,
                    page,
                    per_page: perPage,
                    designation: filters['designation'] || '',
                    status: filters['status'] || '',
                    qualification: filters['qualification'] || ''
                });
                
                const queryKey = params.toString();
                
                // Check cache first
                if (useCache && cache.has(queryKey)) {
                    const cachedData = cache.get(queryKey);
                    // Check if cache is still valid (5 minutes)
                    if (Date.now() - cachedData.timestamp < 300000) {
                        renderTable(cachedData.data.teachers);
                        renderPagination(cachedData.data.page, cachedData.data.per_page, cachedData.data.total);
                        return;
                    } else {
                        cache.delete(queryKey); // Remove expired cache
                    }
                }
                
                lastQuery = Object.fromEntries(params.entries());
                showLoading();
                
                fetch('fetch_teachers.php?' + params.toString())
                    .then(res => {
                        if (!res.ok) {
                            throw new Error(`HTTP error! status: ${res.status}`);
                        }
                        return res.json();
                    })
                    .then(data => {
                        hideLoading();
                        if (data.success) {
                            // Cache the response
                            cache.set(queryKey, {
                                data: data,
                                timestamp: Date.now()
                            });
                            
                            // Limit cache size to prevent memory issues
                            if (cache.size > 50) {
                                const firstKey = cache.keys().next().value;
                                cache.delete(firstKey);
                            }
                            
                            renderTable(data.teachers);
                            renderPagination(data.page, data.per_page, data.total);
                        } else {
                            throw new Error(data.error || 'Unknown error occurred');
                        }
                    })
                    .catch(error => {
                        hideLoading();
                        console.error('Error fetching teachers:', error);
                        tableBody.innerHTML = `
                            <tr>
                                <td colspan='6' style='text-align:center; padding: 40px; color: #e74c3c;'>
                                    <div>
                                        <strong>Error loading teachers</strong><br>
                                        <small>${error.message}</small><br>
                                        <button onclick="fetchTeachers(${page}, false)" style="margin-top: 10px; padding: 5px 10px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer;">Retry</button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
            }

            function renderTable(teachers) {
                if (!teachers.length) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan='6' style='text-align:center; padding: 40px;'>
                                <div style='color: #7f8c8d;'>
                                    <svg style='width: 48px; height: 48px; margin-bottom: 16px; opacity: 0.5;' fill='currentColor' viewBox='0 0 20 20'>
                                        <path d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'/>
                                    </svg>
                                    <h3 style='margin: 0 0 8px 0;'>No teachers found</h3>
                                    <p style='margin: 0;'>Try adjusting your search or filters</p>
                                </div>
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                // Use DocumentFragment for better performance
                const fragment = document.createDocumentFragment();
                
                teachers.forEach((row, i) => {
                    // Create avatar from full name
                    const nameParts = row.full_name.split(' ');
                    const avatar = nameParts.length >= 2 ? (nameParts[0][0] + nameParts[1][0]).toUpperCase() : nameParts[0][0].toUpperCase();
                    
                    const teacherId = row.employee_number || '';
                    const contact = row.email || '';
                    const status = row.user_status || '';
                    const statusClass = status === 'active' ? 'status-active' : (status === 'inactive' ? 'status-inactive' : 'status-leave');
                    
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>
                            <div class='teacher-info'>
                                <div class='teacher-avatar'>${avatar}</div>
                                <div class='teacher-details'>
                                    <span class='teacher-name'>${row.full_name}</span>
                                    <span class='teacher-id'>Joined: ${row.joined_date || 'N/A'}</span>
                                </div>
                            </div>
                        </td>
                        <td><strong>${teacherId}</strong></td>
                        <td>${row.qualification || 'Not specified'}</td>
                        <td>${contact}</td>
                        <td><span class='status-badge ${statusClass}'>${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
                        <td class='table-actions'>
                            <button class='action-btn view-btn' data-id='${row.id}' title='View'>View</button>
                            <button class='action-btn edit-btn' data-id='${row.id}' title='Edit'>Edit</button>
                            <button class='action-btn delete-btn' data-id='${row.id}' title='Delete'>Delete</button>
                        </td>
                    `;
                    fragment.appendChild(tr);
                });
                
                // Clear and append all at once for better performance
                tableBody.innerHTML = '';
                tableBody.appendChild(fragment);
                
                // Add event listeners using event delegation for better performance
                attachEventListeners();
            }

            function attachEventListeners() {
                // Remove existing listeners to prevent duplicates
                tableBody.removeEventListener('click', handleTableClick);
                tableBody.addEventListener('click', handleTableClick);
            }

            function handleTableClick(e) {
                const target = e.target;
                const teacherId = target.getAttribute('data-id');
                
                if (!teacherId) return;
                
                if (target.classList.contains('view-btn')) {
                    window.location.href = `teacher_profile.php?id=${encodeURIComponent(teacherId)}`;
                } else if (target.classList.contains('edit-btn')) {
                    window.location.href = `teacher_view_edit.php?id=${encodeURIComponent(teacherId)}&mode=edit`;
                } else if (target.classList.contains('delete-btn')) {
                    deleteTeacherId = teacherId;
                    deleteTeacherModal.style.display = 'flex';
                }
            }

            function renderPagination(page, perPage, total) {
                // Calculate total pages
                const totalPages = Math.ceil(total / perPage);
                let html = '';
                // Previous button
                html += `<button class="page-btn" ${page === 1 ? 'disabled' : ''} data-page="${page - 1}">Prev</button>`;
                // Page numbers (show up to 5 pages, with ellipsis if needed)
                let start = Math.max(1, page - 2);
                let end = Math.min(totalPages, page + 2);
                if (page <= 3) {
                    end = Math.min(5, totalPages);
                } else if (page >= totalPages - 2) {
                    start = Math.max(1, totalPages - 4);
                }
                if (start > 1) {
                    html += `<button class="page-btn" data-page="1">1</button>`;
                    if (start > 2) html += `<span class="page-ellipsis">...</span>`;
                }
                for (let i = start; i <= end; i++) {
                    html += `<button class="page-btn${i === page ? ' active' : ''}" data-page="${i}">${i}</button>`;
                }
                if (end < totalPages) {
                    if (end < totalPages - 1) html += `<span class="page-ellipsis">...</span>`;
                    html += `<button class="page-btn" data-page="${totalPages}">${totalPages}</button>`;
                }
                // Next button
                html += `<button class="page-btn" ${page === totalPages ? 'disabled' : ''} data-page="${page + 1}">Next</button>`;
                paginationButtons.innerHTML = html;
                // Update info
                const from = total === 0 ? 0 : (page - 1) * perPage + 1;
                const to = Math.min(page * perPage, total);
                paginationInfo.textContent = `Showing ${from}-${to} of ${total} teachers`;
                // Add click handlers
                paginationButtons.querySelectorAll('.page-btn').forEach(btn => {
                    if (!btn.disabled && !btn.classList.contains('active')) {
                        btn.addEventListener('click', () => {
                            const newPage = parseInt(btn.getAttribute('data-page'));
                            if (!isNaN(newPage) && newPage !== page) {
                                currentPage = newPage;
                                fetchTeachers(currentPage);
                            }
                        });
                    }
                });
            }

            // Modal event handlers
            modalTeacherCancel.onclick = function() {
                deleteTeacherModal.style.display = 'none';
                deleteTeacherId = null;
            };
            
            modalTeacherConfirm.onclick = function() {
                if (!deleteTeacherId) return;
                
                // Show loading in modal
                modalTeacherConfirm.innerHTML = 'Deleting...';
                modalTeacherConfirm.disabled = true;
                
                fetch('teacher_actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=delete&id=${encodeURIComponent(deleteTeacherId)}`
                })
                .then(res => res.text())
                .then(msg => {
                    alert(msg);
                    // Clear cache to force refresh
                    cache.clear();
                    fetchTeachers(currentPage, false);
                    deleteTeacherModal.style.display = 'none';
                    deleteTeacherId = null;
                })
                .catch(error => {
                    alert('Error deleting teacher: ' + error.message);
                })
                .finally(() => {
                    modalTeacherConfirm.innerHTML = 'Delete';
                    modalTeacherConfirm.disabled = false;
                });
            };

            // Filter panel toggle
           filterToggleBtn.addEventListener('click', () => {
               filterPanel.classList.toggle('show');
           });
           
           // Debounced search functionality
           const debouncedSearch = debounce(() => {
               currentPage = 1; // Reset to first page on search
               fetchTeachers(1, false); // Don't use cache for new searches
           }, 500); // 500ms delay
           
           searchInput.addEventListener('input', debouncedSearch);
           
            // Filter apply/reset
            applyBtn.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = 1;
                cache.clear(); // Clear cache when applying filters
                fetchTeachers(1, false);
            });
            
            resetBtn.addEventListener('click', function(e) {
                e.preventDefault();
                filterForm.reset();
                currentPage = 1;
                cache.clear(); // Clear cache when resetting
                fetchTeachers(1, false);
            });
            
            // Initial fetch
            fetchTeachers(1);
            
            // Add CSS for loading animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
       });
   </script>
</body>
</html>