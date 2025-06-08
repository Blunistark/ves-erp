<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/manage_student.css">
    <style>
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background-color: #f3f4f6;
            border: none;
            border-radius: 5px;
            padding: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .action-btn:hover {
            background-color: #e5e7eb;
        }
        .action-btn svg {
            width: 18px;
            height: 18px;
        }
        .edit-btn {
            color: #3b82f6;
        }
        .delete-btn {
            color: #ef4444;
        }
        .edit-btn:hover {
            background-color: #dbeafe;
        }
        .delete-btn:hover {
            background-color: #fee2e2;
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
            <h1 class="header-title">Manage Students</h1>
            <span class="header-path">Dashboard > Students > Manage</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="studentSearch" class="search-input" placeholder="Search students...">
                </div>
                <div class="filter-group">
                    <select id="classFilter" class="filter-select">
                        <option value="">All Classes</option>
                    </select>
                    <select id="statusFilter" class="filter-select">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="exportBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Export
                    </button>
                    <a href="add_student.php" class="btn btn-primary">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Student
                    </a>
                </div>
            </div>

            <div class="students-table-container">
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Gender</th>
                            <th>Parent</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-tbody">
                        <!-- Student data will be dynamically populated here -->
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <div class="pagination-info">
                    Showing 1-5 of 24 students
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

    <div id="deleteModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; padding:2rem; border-radius:8px; max-width:350px; margin:auto; text-align:center;">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this student?</p>
            <div style="margin-top:1.5rem; display:flex; gap:1rem; justify-content:center;">
                <button id="modalCancel" class="btn btn-outline">Cancel</button>
                <button id="modalConfirm" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');

            // Click overlay to close sidebar
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }

        // Student search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('studentSearch');
            const classFilter = document.getElementById('classFilter');
            const statusFilter = document.getElementById('statusFilter');
            const exportBtn = document.getElementById('exportBtn');
            const paginationInfo = document.querySelector('.pagination-info');
            const paginationButtons = document.querySelector('.pagination-buttons');
            let currentPage = 1;
            let perPage = 10;
            let totalStudents = 0;
            let lastQuery = {};
            let lastFetchedStudents = [];
            let debounceTimeout = null;

            // Populate class filter (fetch classes from backend)
            fetch('student_actions.php?fetch_classes=1')
                .then(res => res.json())
                .then(classes => {
                    classes.forEach(cls => {
                        const opt = document.createElement('option');
                        opt.value = cls.id;
                        opt.textContent = cls.name;
                        classFilter.appendChild(opt);
                    });
                });

            function fetchStudents(page = 1, search = null) {
                const classId = classFilter.value;
                const status = statusFilter.value;
                const searchTerm = search !== null ? search : searchInput.value.trim();
                const params = new URLSearchParams({
                    fetch: 1,
                    page,
                    per_page: perPage,
                    search: searchTerm,
                    class: classId,
                    status
                });
                lastQuery = Object.fromEntries(params.entries());
                fetch('student_actions.php?' + params.toString())
                    .then(res => res.json())
                    .then(data => {
                        lastFetchedStudents = data.students;
                        renderStudents(data.students, searchTerm);
                        renderPagination(data.page, data.per_page, data.total);
                    })
                    .catch(error => {
                        console.error('Error fetching students:', error);
                        alert('Failed to load students. Please try refreshing the page.');
                    });
            }
            
            // Add event listeners for filters
            searchInput.addEventListener('input', function() {
                if (debounceTimeout) clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => fetchStudents(1), 500);
            });
            
            classFilter.addEventListener('change', function() {
                fetchStudents(1);
            });
            
            statusFilter.addEventListener('change', function() {
                fetchStudents(1);
            });

            // Load students when the page loads
            fetchStudents();

            function renderPagination(page, perPage, total) {
                // Add pagination rendering code here if not already implemented
                const pageCount = Math.ceil(total / perPage);
                const info = document.querySelector('.pagination-info');
                const buttons = document.querySelector('.pagination-buttons');
                
                if (info) {
                    const start = (page - 1) * perPage + 1;
                    const end = Math.min(page * perPage, total);
                    info.textContent = `Showing ${start}-${end} of ${total} students`;
                }
                
                if (buttons) {
                    buttons.innerHTML = '';
                    
                    // Previous button
                    const prevBtn = document.createElement('button');
                    prevBtn.className = 'page-btn';
                    prevBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>';
                    prevBtn.disabled = page <= 1;
                    prevBtn.addEventListener('click', () => fetchStudents(page - 1));
                    buttons.appendChild(prevBtn);
                    
                    // Page buttons
                    const maxButtons = 5;
                    const startPage = Math.max(1, page - Math.floor(maxButtons / 2));
                    const endPage = Math.min(pageCount, startPage + maxButtons - 1);
                    
                    for (let i = startPage; i <= endPage; i++) {
                        const pageBtn = document.createElement('button');
                        pageBtn.className = 'page-btn' + (i === page ? ' active' : '');
                        pageBtn.textContent = i;
                        pageBtn.addEventListener('click', () => fetchStudents(i));
                        buttons.appendChild(pageBtn);
                    }
                    
                    // Next button
                    const nextBtn = document.createElement('button');
                    nextBtn.className = 'page-btn';
                    nextBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>';
                    nextBtn.disabled = page >= pageCount;
                    nextBtn.addEventListener('click', () => fetchStudents(page + 1));
                    buttons.appendChild(nextBtn);
                }
            }

            function renderStudents(students, filterTerm = '') {
                        const tbody = document.getElementById('students-tbody');
                        tbody.innerHTML = '';
                let filtered = students;
                if (filterTerm) {
                    const term = filterTerm.toLowerCase();
                    filtered = students.filter(row => {
                        const studentName = `${row.first_name} ${row.last_name}`.toLowerCase();
                        const studentId = String(row.id).toLowerCase();
                        const studentClass = `${row.class_name || row.class_id || ''}`.toLowerCase();
                        const studentSection = `${row.section_name || ''}`.toLowerCase();
                        const studentParent = (row.father_full_name || '').toLowerCase();
                        return (
                            studentName.includes(term) ||
                            studentId.includes(term) ||
                            studentClass.includes(term) ||
                            studentSection.includes(term) ||
                            studentParent.includes(term)
                        );
                    });
                }

                if (filtered.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="8" class="text-center">No students found</td></tr>`;
                    return;
                }

                filtered.forEach(student => {
                            const tr = document.createElement('tr');
                    
                    // Student Cell
                    const nameCell = document.createElement('td');
                    nameCell.className = 'student-cell';
                    nameCell.innerHTML = `
                        <div class="student-info">
                            <div class="student-photo">
                                <img src="${student.photo || 'assets/default-avatar.png'}" alt="Student Photo">
                            </div>
                                        <div class="student-details">
                                <div class="student-name">${student.full_name || `${student.first_name || ''} ${student.last_name || ''}`}</div>
                                <div class="student-id">#${student.admission_number}</div>
                                        </div>
                                    </div>
                    `;
                    tr.appendChild(nameCell);
                    
                    // Class Cell
                    const classCell = document.createElement('td');
                    classCell.textContent = student.class_name || student.class_id;
                    tr.appendChild(classCell);
                    
                    // Section Cell
                    const sectionCell = document.createElement('td');
                    sectionCell.textContent = student.section_name || student.section_id;
                    tr.appendChild(sectionCell);
                    
                    // Gender Cell
                    const genderCell = document.createElement('td');
                    genderCell.textContent = student.gender_label || student.gender || student.gender_code || 'Not specified';
                    tr.appendChild(genderCell);
                    
                    // Parent Cell
                    const parentCell = document.createElement('td');
                    // Display father's name if available, otherwise mother's name
                    parentCell.textContent = student.father_name || student.mother_name || 'Not specified';
                    tr.appendChild(parentCell);
                    
                    // Contact Cell
                    const contactCell = document.createElement('td');
                    contactCell.textContent = student.mobile || student.phone || 'Not provided';
                    tr.appendChild(contactCell);
                    
                    // Status Cell
                    const statusCell = document.createElement('td');
                    const statusClass = student.status === 'active' ? 'status-active' : 'status-inactive';
                    statusCell.innerHTML = `<span class="status-badge ${statusClass}">${student.status || 'active'}</span>`;
                    tr.appendChild(statusCell);
                    
                    // Actions Cell
                    const actionsCell = document.createElement('td');
                    actionsCell.className = 'actions-cell';
                    actionsCell.innerHTML = `
                        <div class="action-buttons">
                            <a href="student_view_edit.php?id=${student.user_id}" class="action-btn edit-btn" title="Edit Student">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                            </a>
                            <button class="action-btn delete-btn" data-id="${student.user_id}" title="Delete Student">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                        </div>
                            `;
                    tr.appendChild(actionsCell);
                    
                            tbody.appendChild(tr);
                        });

                // Attach event listeners to actions
                document.querySelectorAll('.delete-btn').forEach(btn => {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                                const studentId = this.getAttribute('data-id');
                        document.getElementById('modalConfirm').setAttribute('data-id', studentId);
                        document.getElementById('deleteModal').style.display = 'flex';
                    });
                });
            }

            // Close modals
            document.getElementById('modalCancel').addEventListener('click', function() {
                document.getElementById('deleteModal').style.display = 'none';
            });
            
            // Confirm student deletion
            document.getElementById('modalConfirm').addEventListener('click', function() {
                const studentId = this.getAttribute('data-id');
                
                if (!studentId) {
                    alert('Student ID is missing.');
                    return;
                    }
                
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', studentId);
                
                fetch('student_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide the modal
                        document.getElementById('deleteModal').style.display = 'none';
                        
                        // Refresh the student list
                        fetchStudents(currentPage);
                        
                        // Show success message
                        alert('Student deleted successfully.');
                    } else {
                        alert(data.message || 'Failed to delete student. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the student.');
            });
            });
        });
    </script>
</body>
</html>