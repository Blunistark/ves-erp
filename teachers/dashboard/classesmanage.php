<?php include"sidebar.php"?>
<?php
require_once 'con.php';
// Fetch all classes
$classes = [];
$res = $conn->query('SELECT * FROM classes');
while ($row = $res->fetch_assoc()) {
    $classId = $row['id'];
    // Get sections count
    $secRes = $conn->query("SELECT COUNT(*) as cnt FROM sections WHERE class_id = $classId");
    $secCount = $secRes ? $secRes->fetch_assoc()['cnt'] : 0;
    // Get department, academic year, etc. if available
    $classes[] = [
        'id' => $classId,
        'name' => $row['name'],
        'academic_year' => $row['academic_year'] ?? '',
        'department' => $row['department'] ?? '',
        'sections' => $secCount
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Classes</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/classesmanage.css">
    <!-- Add SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    /* SweetAlert2 Custom Styles */
    .swal2-popup {
        border-radius: 12px;
    }
    
    .swal2-title {
        font-size: 1.25rem !important;
        font-weight: 600 !important;
    }
    
    .swal2-html-container {
        font-size: 0.875rem !important;
    }
    
    .swal2-confirm {
        background-color: #4299e1 !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
    }
    
    .swal2-confirm.swal2-styled.danger {
        background-color: #ef4444 !important;
    }
    
    .swal2-cancel {
        background-color: #e2e8f0 !important;
        color: #4a5568 !important;
        border-radius: 6px !important;
        font-weight: 500 !important;
    }
    </style>
</head>
<body>
    <div class="sidebar-overlay"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Manage Classes</h1>
            <span class="header-path">Dashboard > Classes > Manage</span>
        </header>

        <main class="dashboard-content">
            <!-- Filters and Actions Section -->
            <section class="filter-section">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="academicYearFilter" class="filter-label">Academic Year</label>
                        <select id="academicYearFilter" class="filter-select">
                            <option value="">All Academic Years</option>
                            <option value="2024-2025">2024-2025</option>
                            <option value="2023-2024">2023-2024</option>
                            <option value="2022-2023">2022-2023</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="departmentFilter" class="filter-label">Department</label>
                        <select id="departmentFilter" class="filter-select">
                            <option value="">All Departments</option>
                            <option value="primary">Primary</option>
                            <option value="secondary">Secondary</option>
                            <option value="higher_secondary">Higher Secondary</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="statusFilter" class="filter-label">Status</label>
                        <select id="statusFilter" class="filter-select">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="searchClasses" class="filter-label">Search</label>
                        <input type="text" id="searchClasses" class="filter-input" placeholder="Search classes...">
                    </div>
                </div>
                <div class="action-buttons">
                    <button type="button" class="btn btn-outline" id="resetFilters">
                        <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filters
                    </button>
                    <button type="button" class="btn btn-outline" id="exportData">
                        <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Data
                    </button>
                    <button type="button" class="btn btn-primary" id="createClass" onclick="location.href='create.php'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create New Class
                    </button>
                </div>
            </section>

            <!-- Classes Grid -->
            <div class="classes-grid" id="classesGrid">
            <?php foreach ($classes as $class): ?>
                <div class="class-card" data-class-id="<?= $class['id'] ?>">
                    <div class="class-header">
                        <div class="class-info">
                            <h3 class="class-name"><?= htmlspecialchars($class['name']) ?></h3>
                            <p class="class-numeric">Academic Year: <?= htmlspecialchars($class['academic_year']) ?></p>
                        </div>
                        <div class="class-badge badge-active">Active</div>
                    </div>
                    <div class="class-content">
                        <div class="class-detail">
                            <div class="detail-info">Department: <span class="detail-value"><?= htmlspecialchars($class['department']) ?></span></div>
                        </div>
                        <div class="sections-list">
                            <p class="sections-title">Sections <span class="section-badge"><?= $class['sections'] ?></span></p>
                        </div>
                    </div>
                    <div class="class-footer">
                        <span class="update-time">Updated: just now</span>
                        <div class="action-menu">
                            <button type="button" class="action-trigger" onclick="event.stopPropagation(); toggleActionMenu(<?= $class['id'] ?>)">
                                <svg xmlns="http://www.w3.org/2000/svg" class="dropdown-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>
                            <div class="action-dropdown" id="actionMenu<?= $class['id'] ?>">
                                <a href="editclass.php?id=<?= $class['id'] ?>" class="dropdown-item">
                                    <span>Edit Class</span>
                                </a>
                                <a href="#" class="dropdown-item">
                                    <span>View Details</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item">
                                    <span>Duplicate Class</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item warning delete-class-link" 
                                   data-class-id="<?= $class['id'] ?>" 
                                   data-class-name="<?= htmlspecialchars($class['name']) ?>">
                                    <span>Delete Class</span>
                                </a>
                            </div>
                        </div>
                        <a href="classessections.php?class_id=<?= $class['id'] ?>" class="btn btn-outline" style="margin-left: 1rem;">
                            Manage Sections
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>

            <!-- Pagination and Summary -->
            <div class="list-summary">
                <span class="summary-text">Showing 1-10 of 24 classes</span>
                <div class="pagination">
                    <button class="page-nav disabled" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" class="page-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-nav">
                        <svg xmlns="http://www.w3.org/2000/svg" class="page-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteClassModal" style="display:none;">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="modal-title">Delete Class</h3>
          <button class="close-modal" id="closeDeleteModal">
            <svg xmlns="http://www.w3.org/2000/svg" class="close-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <p>Are you sure you want to delete <strong id="deleteClassName">this class</strong>?</p>
          <p>This action cannot be undone. All sections and subjects for this class will also be deleted.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" id="cancelDeleteClassBtn">Cancel</button>
          <button type="button" class="btn btn-primary" style="background-color: #ef4444;" id="confirmDeleteClassBtn">Delete</button>
        </div>
      </div>
    </div>

    <!-- Edit Class Modal -->
    <div class="modal-overlay" id="editClassModal" style="display:none;">
      <div class="modal-container">
        <div class="modal-header">
          <h3 class="modal-title">Edit Class</h3>
          <button class="close-modal" id="closeEditModal">
            <svg xmlns="http://www.w3.org/2000/svg" class="close-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form id="editClassForm">
          <div class="modal-body">
            <input type="hidden" id="editClassId" name="class_id">
            <div class="form-group">
              <label for="editClassName" class="form-label required">Class Name</label>
              <input type="text" id="editClassName" name="name" class="form-input" required>
            </div>
            <div class="form-group">
              <label for="editAcademicYear" class="form-label required">Academic Year</label>
              <input type="text" id="editAcademicYear" name="academic_year" class="form-input" required>
            </div>
            <div class="form-group">
              <label for="editDepartment" class="form-label">Department</label>
              <input type="text" id="editDepartment" name="department" class="form-input">
            </div>
            <div class="form-group">
              <label for="editDescription" class="form-label">Description</label>
              <textarea id="editDescription" name="description" class="form-textarea"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline" id="cancelEditClassBtn">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>

    <script>
        // Toggle action menu
        function toggleActionMenu(classId) {
            const allMenus = document.querySelectorAll('.action-dropdown');
            allMenus.forEach(menu => {
                if (menu.id !== `actionMenu${classId}`) {
                    menu.parentElement.classList.remove('active');
                }
            });
            
            const menu = document.getElementById(`actionMenu${classId}`).parentElement;
            menu.classList.toggle('active');
        }

        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            sidebar.classList.toggle('show');
            body.classList.toggle('sidebar-open');
        }

        // Close menus when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.action-menu')) {
                document.querySelectorAll('.action-menu').forEach(menu => {
                    menu.classList.remove('active');
                });
            }
        });

        // Prevent menu close when clicking inside the dropdown
        document.querySelectorAll('.action-dropdown').forEach(dropdown => {
            dropdown.addEventListener('click', function(event) {
                event.stopPropagation();
            });
        });

        // Close sidebar when clicking overlay
        document.querySelector('.sidebar-overlay').addEventListener('click', function() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            sidebar.classList.remove('show');
            body.classList.remove('sidebar-open');
        });

        document.addEventListener('DOMContentLoaded', function() {
            window.classToDelete = null;
            document.querySelectorAll('.delete-class-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const classId = this.getAttribute('data-class-id');
                    const className = this.getAttribute('data-class-name');
                    
                    Swal.fire({
                        title: 'Delete Class',
                        text: `Are you sure you want to delete ${className}? This action cannot be undone and will delete all associated sections and data.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Delete',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#ef4444',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('class_actions.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                body: `action=delete&class_id=${encodeURIComponent(classId)}`
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire({
                                        title: 'Deleted!',
                                        text: data.message || 'Class deleted successfully',
                                        icon: 'success',
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(() => {
                                        const card = document.querySelector(`.class-card[data-class-id='${classId}']`);
                                        if (card) card.remove();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data.message || 'Failed to delete class',
                                        icon: 'error'
                                    });
                                }
                            })
                            .catch(() => {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Network or server error occurred',
                                    icon: 'error'
                                });
                            });
                        }
                    });
                });
            });

            // Filter functionality with notifications
            const classFilter = document.getElementById('searchClasses');
            const resetFiltersBtn = document.getElementById('resetFilters');
            
            function showFilterNotification(count) {
                const toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });

                toast.fire({
                    icon: 'success',
                    title: `Showing ${count} ${count === 1 ? 'class' : 'classes'}`
                });
            }

            function applyFilters() {
                const classValue = classFilter.value;
                const classGroups = document.querySelectorAll('.class-card');
                let visibleCount = 0;
                
                classGroups.forEach(group => {
                    if (!classValue || group.getAttribute('data-class-id') === classValue) {
                        group.style.display = '';
                        visibleCount++;
                    } else {
                        group.style.display = 'none';
                    }
                });

                if (classValue) {
                    showFilterNotification(visibleCount);
                }
            }

            classFilter.addEventListener('change', applyFilters);
            
            resetFiltersBtn.addEventListener('click', function() {
                classFilter.value = '';
                applyFilters();
                
                Swal.fire({
                    title: 'Filters Reset',
                    text: 'All filters have been cleared',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            // Export functionality with confirmation
            document.getElementById('exportData').addEventListener('click', function() {
                Swal.fire({
                    title: 'Export Data',
                    text: 'Do you want to export all class data?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Export',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'export_classes.php';
                    }
                });
            });
        });
    </script>
</body>
</html>