<?php include "sidebar.php";
require_once "con.php";

// Fetch all classes
$classes = [];
$res = $conn->query("SELECT * FROM classes ORDER BY id");
while ($row = $res->fetch_assoc()) {
    $classes[$row['id']] = $row;
}

// Fetch all sections
$sections = [];
$res = $conn->query("SELECT s.*, c.name AS class_name FROM sections s JOIN classes c ON s.class_id = c.id ORDER BY c.id, s.name");
while ($row = $res->fetch_assoc()) {
    $sections[] = $row;
}

// Fetch all teachers
$teachers = [];
$res = $conn->query("SELECT t.*, u.full_name, u.status FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.status='active' ORDER BY u.full_name");
while ($row = $res->fetch_assoc()) {
    $teachers[] = $row;
}

// Fetch available section names from the DB
$available_section_names = [];
$res = $conn->query("SELECT DISTINCT name FROM sections ORDER BY name;");
while ($row = $res->fetch_assoc()) {
    $available_section_names[] = strtoupper($row['name']);
}

// Fetch all sections with boys, girls, teacher, and room info
$section_stats = [];
$res = $conn->query("SELECT s.id AS section_id, s.class_id, s.name AS section_name, s.class_teacher_user_id, 
                      COUNT(CASE WHEN st.gender_code IN ('M', 'MALE') THEN 1 END) AS boys, 
                      COUNT(CASE WHEN st.gender_code IN ('F', 'FEMALE') THEN 1 END) AS girls 
                     FROM sections s 
                     LEFT JOIN students st ON s.id = st.section_id 
                     GROUP BY s.id, s.class_id, s.name, s.class_teacher_user_id 
                     ORDER BY s.class_id, s.name;");
while ($row = $res->fetch_assoc()) {
    $section_stats[$row['section_id']] = $row;
}

// Fetch teacher names
$teacher_names = [];
$res = $conn->query("SELECT t.user_id, u.full_name FROM teachers t JOIN users u ON t.user_id = u.id WHERE u.status='active'");
while ($row = $res->fetch_assoc()) {
    $teacher_names[$row['user_id']] = $row['full_name'];
}

// Get class_id from query param if present
$class_id_filter = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

// Fetch total sections and gender counts
$total_sections = 0;
$total_boys = 0;
$total_girls = 0;
$stats_res = $conn->query("SELECT 
    COUNT(DISTINCT s.id) as total_sections,
    SUM(CASE WHEN st.gender_code IN ('M', 'MALE') THEN 1 ELSE 0 END) as total_boys,
    SUM(CASE WHEN st.gender_code IN ('F', 'FEMALE') THEN 1 ELSE 0 END) as total_girls
FROM sections s
LEFT JOIN students st ON s.id = st.section_id");
if ($stats_row = $stats_res->fetch_assoc()) {
    $total_sections = $stats_row['total_sections'];
    $total_boys = $stats_row['total_boys'];
    $total_girls = $stats_row['total_girls'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Sections</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/classessections.css">
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
            <h1 class="header-title">Manage Sections</h1>
            <span class="header-path">Dashboard > Classes > Sections</span>
        </header>

        <main class="dashboard-content">
            <!-- Filter and Actions Section -->
            <section class="filter-section">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="classFilter" class="filter-label">Filter by Class</label>
                        <select id="classFilter" class="filter-select">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
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
                        <button type="button" class="btn btn-primary" id="createSection" onclick="openSectionModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create New Section
                        </button>
                    </div>
                </div>
            </section>

            <!-- Overview Stats -->
            <div class="overview-cards-row">
                <div class="overview-card">
                    <svg xmlns="http://www.w3.org/2000/svg" class="overview-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <h2 class="overview-value" id="totalSectionsCard"><?php echo $total_sections; ?></h2>
                    <p class="overview-label">Total Sections</p>
                </div>
                <div class="overview-card">
                    <svg xmlns="http://www.w3.org/2000/svg" class="overview-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h2 class="overview-value"><?php echo $total_boys; ?></h2>
                    <p class="overview-label">Total Boys</p>
                </div>
                <div class="overview-card">
                    <svg xmlns="http://www.w3.org/2000/svg" class="overview-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h2 class="overview-value"><?php echo $total_girls; ?></h2>
                    <p class="overview-label">Total Girls</p>
                </div>
            </div>

            <!-- Sections By Class -->
            <div class="sections-container" id="sectionsContainer">
                <?php foreach ($classes as $classId => $class): ?>
                <?php if ($class_id_filter && $classId != $class_id_filter) continue; ?>
                <div class="class-group" id="class<?php echo $classId; ?>Group">
                    <div class="class-header" onclick="toggleClassGroup('class<?php echo $classId; ?>Group')">
                        <div class="class-info">
                            <div class="class-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <h3 class="class-title"><?php echo htmlspecialchars($class['name']); ?> <span class="class-badge badge-primary"><?php echo count(array_filter($sections, function($s) use ($classId) { return $s['class_id'] == $classId; })); ?> Sections</span></h3>
                        </div>
                        <div class="class-toggle">
                            <svg xmlns="http://www.w3.org/2000/svg" class="toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div class="sections-grid">
                        <?php foreach ($sections as $section): if ($section['class_id'] != $classId) continue; $stats = $section_stats[$section['id']] ?? null; $total_students = ($stats ? ($stats['boys'] + $stats['girls']) : 0); ?>
                        <div class="section-card" data-section-id="<?php echo $section['id']; ?>">
                            <div class="section-header">
                                <div class="section-info">
                                    <h3 class="section-name">Section <?php echo htmlspecialchars(strtoupper($section['name'] ?? '')); ?> <span class="capacity-badge">Student Strength: <?php echo $total_students; ?></span></h3>
                                </div>
                                <div class="section-status status-active">Active</div>
                            </div>
                            <div class="section-content">
                                <div class="section-grid">
                                    <div class="detail-group">
                                        <p class="detail-label">Class Teacher</p>
                                        <p class="detail-value"><?php echo htmlspecialchars($teacher_names[$section['class_teacher_user_id']] ?? ''); ?></p>
                                    </div>
                                    <?php /* Remove room number display since rooms table doesn't exist
                                    <div class="detail-group">
                                        <p class="detail-label">Room Number</p>
                                        <p class="detail-value"><?php echo htmlspecialchars($room_names[$section['room_id']] ?? ''); ?></p>
                                    </div>
                                    */ ?>
                                    <div class="detail-group">
                                        <p class="detail-label">Boys</p>
                                        <p class="detail-value"><?php echo $stats ? $stats['boys'] : 0; ?></p>
                                    </div>
                                    <div class="detail-group">
                                        <p class="detail-label">Girls</p>
                                        <p class="detail-value"><?php echo $stats ? $stats['girls'] : 0; ?></p>
                                    </div>
                                </div>
                                <div class="section-stats">
                                    <div class="stat-item">
                                        <p class="stat-value"><?php echo $stats ? $stats['boys'] : 0; ?></p>
                                        <p class="stat-label">Boys</p>
                                    </div>
                                    <div class="stat-item">
                                        <p class="stat-value"><?php echo $stats ? $stats['girls'] : 0; ?></p>
                                        <p class="stat-label">Girls</p>
                                    </div>
                                    <div class="stat-item">
                                        <p class="stat-value"><?php echo $total_students; ?></p>
                                        <p class="stat-label">Student Strength</p>
                                    </div>
                                </div>
                            </div>
                            <div class="section-footer">
                                <span class="update-info">Updated 2 days ago</span>
                                <div class="section-actions">
                                    <button class="action-btn edit" title="Edit Section" data-section-id="<?php echo $section['id']; ?>" data-teacher-id="<?php echo $section['class_teacher_user_id']; ?>" onclick="openSectionModal('<?php echo htmlspecialchars(strtoupper($section['name'] ?? '')); ?>', '<?php echo htmlspecialchars($class['name'] ?? ''); ?>', '<?php echo $section['class_teacher_user_id']; ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="View Students" onclick="location.href='student_management_unified.php?class=<?php echo $classId; ?>&section=<?php echo htmlspecialchars(strtolower($section['name'] ?? '')); ?>'">>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn delete" title="Delete Section" data-section-id="<?php echo $section['id']; ?>" onclick="openDeleteModal('<?php echo htmlspecialchars(strtoupper($section['name'] ?? '')); ?>', '<?php echo htmlspecialchars($class['name'] ?? ''); ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Add/Edit Section Modal -->
    <div class="modal-overlay" id="sectionModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title" id="sectionModalTitle">Add New Section</h3>
                <button class="close-modal" onclick="closeSectionModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="close-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <form id="sectionForm">
                    <div class="form-group">
                        <label for="sectionClass" class="form-label required">Class</label>
                        <select id="sectionClass" name="sectionClass" class="form-select" required>
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sectionName" class="form-label required">Section Name</label>
                        <select id="sectionName" name="sectionName" class="form-select" required>
                            <option value="">Select Section</option>
                            <?php foreach ($available_section_names as $secName): ?>
                                <option value="<?php echo $secName; ?>"><?php echo $secName; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="help-text">Choose a section from existing names</span>
                    </div>
                    <div class="form-group">
                        <label for="sectionCapacity" class="form-label required">Student Strength</label>
                        <input type="number" id="sectionCapacity" name="sectionCapacity" class="form-input" min="1" max="150" value="32" required>
                    </div>
                    <div class="form-group">
                        <label for="classTeacher" class="form-label">Class Teacher</label>
                        <select id="classTeacher" name="classTeacher" class="form-select">
                            <option value="">Select Class Teacher</option>
                            <?php foreach ($teachers as $teacher): ?>
                                <option value="<?php echo $teacher['user_id']; ?>"><?php echo htmlspecialchars($teacher['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sectionStatus" class="form-label required">Status</label>
                        <select id="sectionStatus" name="sectionStatus" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sectionDescription" class="form-label">Description</label>
                        <textarea id="sectionDescription" name="sectionDescription" class="form-textarea" rows="3"></textarea>
                        <span class="help-text">Additional notes about this section (optional)</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline" onclick="closeSectionModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Delete Section</h3>
                <button class="close-modal" onclick="closeDeleteModal()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="close-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteSectionName">Section A</strong> from <strong id="deleteClassName">Class 10</strong>?</p>
                <p>This action cannot be undone. All data associated with this section including student assignments and attendance records will be permanently removed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-primary" style="background-color: #ef4444;" onclick="confirmDelete()">Delete Section</button>
            </div>
        </div>
    </div>

    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            if (sidebar) {
                sidebar.classList.toggle('show');
                document.body.classList.toggle('sidebar-open');
            }
            
            overlay.addEventListener('click', () => {
                if (sidebar) {
                    sidebar.classList.remove('show');
                }
                document.body.classList.remove('sidebar-open');
            });
        }
        
        // Toggle class group to show/hide sections
        function toggleClassGroup(groupId) {
            const group = document.getElementById(groupId);
            if (group) {
                group.classList.toggle('active');
                
                const header = group.querySelector('.class-header');
                header.classList.toggle('active');
            }
        }
        
        // Filter functionality with notifications
        document.addEventListener('DOMContentLoaded', function() {
            const classFilter = document.getElementById('classFilter');
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
                    title: `Showing ${count} ${count === 1 ? 'section' : 'sections'}`
                });
            }

            function applyFilters() {
                const classValue = classFilter.value;
                const classGroups = document.querySelectorAll('.class-group');
                let visibleCount = 0;
                
                classGroups.forEach(group => {
                    const sections = group.querySelectorAll('.section-card');
                    if (!classValue || group.id === `class${classValue}Group`) {
                        group.style.display = '';
                        visibleCount += sections.length;
                    } else {
                        group.style.display = 'none';
                    }
                });

                if (classValue) {
                    showFilterNotification(visibleCount);
                }
                
                // Update overview stats
                document.getElementById('totalSectionsCard').textContent = visibleCount;
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
                    text: 'Do you want to export all section data?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Export',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'export_sections.php';
                    }
                });
            });
        });
        
        // Section Modal Functions
        function openSectionModal(sectionName = '', className = '', teacherId = '') {
            const isEdit = sectionName && className;
            
            Swal.fire({
                title: isEdit ? `Edit Section ${sectionName}` : 'Add New Section',
                html: `
                    <form id="sectionForm" class="swal2-form">
                        <div class="form-group">
                            <label for="sectionClass" class="form-label required">Class</label>
                            <select id="sectionClass" class="form-select" required>
                                <option value="">Select Class</option>
                                ${Array.from(document.getElementById('sectionClass').options)
                                    .map(opt => `<option value="${opt.value}" ${opt.value === className ? 'selected' : ''}>${opt.text}</option>`)
                                    .join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="sectionName" class="form-label required">Section Name</label>
                            <select id="sectionName" class="form-select" required>
                                <option value="">Select Section</option>
                                ${Array.from(document.getElementById('sectionName').options)
                                    .map(opt => `<option value="${opt.value}" ${opt.value === sectionName ? 'selected' : ''}>${opt.text}</option>`)
                                    .join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="classTeacher" class="form-label">Class Teacher</label>
                            <select id="classTeacher" class="form-select">
                                <option value="">Select Class Teacher</option>
                                ${Array.from(document.getElementById('classTeacher').options)
                                    .map(opt => `<option value="${opt.value}" ${opt.value === teacherId ? 'selected' : ''}>${opt.text}</option>`)
                                    .join('')}
                            </select>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: isEdit ? 'Save Changes' : 'Add Section',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                preConfirm: () => {
                    const form = document.getElementById('sectionForm');
                    if (!form.checkValidity()) {
                        form.reportValidity();
                        return false;
                    }
                    return {
                        class_id: document.getElementById('sectionClass').value,
                        name: document.getElementById('sectionName').value,
                        teacher_id: document.getElementById('classTeacher').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;
                    const action = isEdit ? 'update' : 'add';
                    
                    fetch('section_actions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=${action}&class_id=${encodeURIComponent(data.class_id)}&name=${encodeURIComponent(data.name)}&teacher_id=${encodeURIComponent(data.teacher_id)}`
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: isEdit ? 'Section updated successfully' : 'Section added successfully',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Operation failed',
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
        }

        function openDeleteModal(sectionName, className) {
            Swal.fire({
                title: 'Delete Section',
                text: `Are you sure you want to delete Section ${sectionName} from ${className}? This action cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#ef4444',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    const sectionId = document.querySelector(`[data-section-name="${sectionName}"]`).getAttribute('data-section-id');
                    
                    fetch('section_actions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `action=delete&id=${encodeURIComponent(sectionId)}`
                    })
                    .then(res => res.json())
                    .then(response => {
                        if (response.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: 'Section has been deleted successfully',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: response.message || 'Failed to delete section',
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
        }

        function closeSectionModal() {
            const modal = document.getElementById('sectionModal');
            modal.classList.remove('show');
        }
        
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('show');
        }
        
        function confirmDelete() {
            const sectionName = document.getElementById('deleteSectionName').textContent;
            const className = document.getElementById('deleteClassName').textContent;
            
            // Here you would normally submit the delete request via AJAX
            // For demo purposes, we'll just close the modal
            alert(`${sectionName} from ${className} has been deleted successfully!`);
            closeDeleteModal();
        }
    </script>
</body>
</html>