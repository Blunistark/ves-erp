<?php
/**
 * Unified Teacher Management System - Teacher Portal Version
 * For headmasters who log into teacher portal
 * Role-based access control: Only assignment/reassignment operations
 */

require_once __DIR__ . '/../../includes/functions.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check authentication and role - only teachers and headmasters
if (!isLoggedIn() || !hasRole(['teacher', 'headmaster'])) {
    header("Location: ../index.php");
    exit;
}

// Include database connection
require_once 'con.php';

// Get current user role for permission checks
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Only headmasters can access this page in teacher portal
if ($user_role !== 'headmaster') {
    header("Location: index.php");
    exit;
}

// Fetch required data for dropdowns
$classes = executeQuery("SELECT id, name FROM classes ORDER BY name");
$sections = executeQuery("SELECT id, name, class_id FROM sections ORDER BY class_id, name");
$subjects = executeQuery("SELECT id, name, code FROM subjects ORDER BY name");
$teachers = executeQuery("
    SELECT u.id, u.full_name, u.email, t.employee_number, u.status
    FROM users u 
    JOIN teachers t ON u.id = t.user_id 
    WHERE u.role IN ('teacher', 'headmaster')
    ORDER BY u.full_name
");

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management - Headmaster Portal</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #fd5d5d;
            --secondary-color: #5856d6;
            --accent-color: #26e7a6;
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --border-color: #e2e8f0;
            --bg-light: #f7fafc;
            --bg-white: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg-light);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
        }

        .unified-container {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .header-section {
            background: var(--bg-white);
            padding: 24px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .header-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .info-banner {
            background: linear-gradient(135deg, #fef3cd 0%, #fed7aa 100%);
            border: 1px solid #f59e0b;
            border-radius: var(--border-radius);
            padding: 16px;
            margin-bottom: 24px;
        }

        .info-banner h4 {
            color: #92400e;
            margin-bottom: 8px;
        }

        .info-banner p {
            color: #a16207;
            margin: 0;
        }

        .tabs-container {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .tabs-nav {
            display: flex;
            background: var(--bg-light);
            border-bottom: 2px solid var(--border-color);
        }

        .tab-button {
            flex: 1;
            padding: 16px 24px;
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-button:hover {
            background: rgba(253, 93, 93, 0.05);
            color: var(--primary-color);
        }

        .tab-button.active {
            background: var(--bg-white);
            color: var(--primary-color);
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary-color);
        }

        .tab-content {
            display: none;
            padding: 24px;
        }

        .tab-content.active {
            display: block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-label.required::after {
            content: ' *';
            color: #e53e3e;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(253, 93, 93, 0.1);
        }

        .help-text {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #e53e3e;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            background: var(--bg-light);
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .card-body {
            padding: 24px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .data-table th, .data-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-primary);
        }

        .data-table tr:hover {
            background: rgba(253, 93, 93, 0.02);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(38, 231, 166, 0.1);
            color: #059669;
        }

        .status-inactive {
            background: rgba(229, 62, 62, 0.1);
            color: #dc2626;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }

        .search-container {
            position: relative;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 48px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .notification {
            padding: 16px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: none;
        }

        .notification.success {
            background: rgba(38, 231, 166, 0.1);
            color: #059669;
            border: 1px solid rgba(38, 231, 166, 0.3);
        }

        .notification.error {
            background: rgba(229, 62, 62, 0.1);
            color: #dc2626;
            border: 1px solid rgba(229, 62, 62, 0.3);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }

        .spinner {
            display: inline-block;
            width: 32px;
            height: 32px;
            border: 3px solid var(--border-color);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .unified-container {
                margin-left: 0;
                padding: 16px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .tabs-nav {
                flex-direction: column;
            }

            .tab-button {
                flex: none;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <i class="fas fa-bars hamburger-icon"></i>
    </button>

    <div class="unified-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1 class="header-title">
                <i class="fas fa-users-cog"></i>
                Teacher Management - Headmaster Portal
            </h1>
            <p class="header-subtitle">
                Assignment and reassignment operations for teachers, classes, and subjects
            </p>
        </div>

        <!-- Info Banner -->
        <div class="info-banner">
            <h4><i class="fas fa-info-circle"></i> Headmaster Access Level</h4>
            <p>As a headmaster, you can assign/reassign teachers to classes and subjects, but cannot add new teachers. Contact the administrator to add new teaching staff.</p>
        </div>

        <!-- Notification Area -->
        <div id="notification" class="notification"></div>

        <!-- Main Content Tabs -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" data-tab="manage-teachers">
                    <i class="fas fa-users"></i>
                    Teacher Directory
                </button>
                <button class="tab-button" data-tab="class-assignments">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Class Teacher Assignment
                </button>
                <button class="tab-button" data-tab="subject-assignments">
                    <i class="fas fa-book-open"></i>
                    Subject Assignments
                </button>
                <button class="tab-button" data-tab="overview">
                    <i class="fas fa-chart-bar"></i>
                    Overview & Statistics
                </button>
            </div>

            <!-- Tab Content: Teacher Directory -->
            <div id="manage-teachers" class="tab-content active">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Teacher Directory</h3>
                    </div>
                    <div class="card-body">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="teacherSearch" class="search-input" placeholder="Search by name, employee ID, or email...">
                        </div>
                        
                        <div id="teachersTable">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Employee ID</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="teachersTableBody">
                                    <!-- Dynamic content will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div id="teachersLoading" class="loading">
                            <div class="spinner"></div>
                            <p>Loading teachers...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Class Teacher Assignment -->
            <div id="class-assignments" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Class Teacher Assignments</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="assignClass" class="form-label required">Class</label>
                                <select id="assignClass" name="assignClass" class="form-select" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assignSection" class="form-label required">Section</label>
                                <select id="assignSection" name="assignSection" class="form-select" required disabled>
                                    <option value="">Select Section</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assignTeacher" class="form-label required">Teacher</label>
                                <select id="assignTeacher" name="assignTeacher" class="form-select" required>
                                    <option value="">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>">
                                            <?php echo htmlspecialchars($teacher['full_name']); ?> (<?php echo htmlspecialchars($teacher['employee_number']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="actions">
                            <button type="button" id="assignClassTeacher" class="btn btn-primary">
                                <i class="fas fa-user-check"></i>
                                Assign Class Teacher
                            </button>
                        </div>

                        <!-- Current Assignments Table -->
                        <h4 style="margin-top: 32px; margin-bottom: 16px;">Current Class Teacher Assignments</h4>
                        <div id="classAssignmentsTable">
                            <!-- Dynamic content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Subject Assignments -->
            <div id="subject-assignments" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Subject Assignments</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="subjectTeacher" class="form-label required">Teacher</label>
                                <select id="subjectTeacher" name="subjectTeacher" class="form-select" required>
                                    <option value="">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>">
                                            <?php echo htmlspecialchars($teacher['full_name']); ?> (<?php echo htmlspecialchars($teacher['employee_number']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Assign Subjects</label>
                            <div id="subjectsCheckboxes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-top: 12px;">
                                <?php foreach ($subjects as $subject): ?>
                                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer;">
                                        <input type="checkbox" name="subjects[]" value="<?php echo $subject['id']; ?>" style="margin: 0;">
                                        <span><?php echo htmlspecialchars($subject['name']); ?> (<?php echo htmlspecialchars($subject['code']); ?>)</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="actions">
                            <button type="button" id="updateSubjectAssignments" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Update Assignments
                            </button>
                            <button type="button" id="clearSubjectAssignments" class="btn btn-outline">
                                <i class="fas fa-times"></i>
                                Clear All
                            </button>
                        </div>

                        <!-- Current Subject Assignments -->
                        <h4 style="margin-top: 32px; margin-bottom: 16px;">Current Subject Assignments</h4>
                        <div id="subjectAssignmentsDisplay">
                            <p class="help-text">Select a teacher to view their subject assignments</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Overview & Statistics -->
            <div id="overview" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">System Overview</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 32px;">
                            <!-- Statistics Cards -->
                            <div class="card">
                                <div class="card-body" style="text-align: center;">
                                    <h4 style="color: var(--primary-color); margin-bottom: 8px;">Total Teachers</h4>
                                    <div id="totalTeachers" style="font-size: 2rem; font-weight: bold;">-</div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-body" style="text-align: center;">
                                    <h4 style="color: var(--accent-color); margin-bottom: 8px;">Active Teachers</h4>
                                    <div id="activeTeachers" style="font-size: 2rem; font-weight: bold;">-</div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-body" style="text-align: center;">
                                    <h4 style="color: var(--secondary-color); margin-bottom: 8px;">Assigned Classes</h4>
                                    <div id="assignedClasses" style="font-size: 2rem; font-weight: bold;">-</div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-body" style="text-align: center;">
                                    <h4 style="color: var(--text-primary); margin-bottom: 8px;">Subject Assignments</h4>
                                    <div id="subjectAssignments" style="font-size: 2rem; font-weight: bold;">-</div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activities -->
                        <h4 style="margin-bottom: 16px;">Recent Assignment Activities</h4>
                        <div id="recentActivities">
                            <p class="help-text">Recent activities will be displayed here</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Debug: Log script loading
        console.log('Teacher Management Script Loading - Version: <?php echo time(); ?>');
        
        // Error handling wrapper
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error, 'File:', e.filename, 'Line:', e.lineno);
        });
        
        // Global variables
        const userRole = '<?php echo $user_role; ?>';
        const sectionsData = <?php echo json_encode($sections); ?>;
        
        // Ensure jQuery is loaded
        if (typeof $ === 'undefined') {
            console.error('jQuery is not loaded!');
            alert('Error: jQuery is required but not loaded. Please refresh the page.');
        }
        
        // Initialize the application
        $(document).ready(function() {
            console.log('Document ready - initializing application');
            try {
                initializeTabs();
                initializeFormHandlers();
                loadTeachers();
                loadClassAssignments();
                loadStatistics();
                console.log('Application initialized successfully');
            } catch (error) {
                console.error('Error during initialization:', error);
                alert('Error during application initialization. Please check the console for details.');
            }
        });

        // Tab management
        function initializeTabs() {
            $('.tab-button').click(function() {
                const tabId = $(this).data('tab');
                switchTab(tabId);
            });
        }

        function switchTab(tabId) {
            // Update active states
            $('.tab-button').removeClass('active');
            $('.tab-content').removeClass('active');
            
            $(`[data-tab="${tabId}"]`).addClass('active');
            $(`#${tabId}`).addClass('active');
            
            // Load tab-specific data
            switch(tabId) {
                case 'manage-teachers':
                    loadTeachers();
                    break;
                case 'class-assignments':
                    loadClassAssignments();
                    break;
                case 'subject-assignments':
                    // Loaded when teacher is selected
                    break;
                case 'overview':
                    loadStatistics();
                    break;
            }
        }

        // Form handlers
        function initializeFormHandlers() {
            // Class assignment
            $('#assignClass').change(updateSections);
            $('#assignClassTeacher').click(handleClassAssignment);
            
            // Subject assignment
            $('#subjectTeacher').change(loadTeacherSubjects);
            $('#updateSubjectAssignments').click(handleSubjectAssignment);
            $('#clearSubjectAssignments').click(clearSubjectSelections);
            
            // Search
            $('#teacherSearch').on('input', debounce(searchTeachers, 300));
        }

        // Load teachers
        function loadTeachers() {
            $('#teachersLoading').show();
            $('#teachersTableBody').empty();
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_teachers' },
                success: function(response) {
                    $('#teachersLoading').hide();
                    
                    if (response.success) {
                        displayTeachers(response.data);
                    } else {
                        showNotification('Failed to load teachers', 'error');
                    }
                },
                error: function() {
                    $('#teachersLoading').hide();
                    showNotification('An error occurred while loading teachers', 'error');
                }
            });
        }

        function displayTeachers(teachers) {
            const tbody = $('#teachersTableBody');
            tbody.empty();
            
            teachers.forEach(teacher => {
                const row = `
                    <tr>
                        <td>${escapeHtml(teacher.full_name)}</td>
                        <td>${escapeHtml(teacher.employee_number)}</td>
                        <td>${escapeHtml(teacher.email)}</td>
                        <td><span class="status-badge status-${teacher.status}">${teacher.status}</span></td>
                        <td class="actions">
                            <button class="btn btn-outline btn-sm" onclick="viewTeacherAssignments(${teacher.id})">
                                <i class="fas fa-eye"></i> View Assignments
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Class assignment functionality
        function updateSections() {
            const classId = $('#assignClass').val();
            const sectionSelect = $('#assignSection');
            
            sectionSelect.empty().append('<option value="">Select Section</option>');
            
            if (classId) {
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        showNotification('Class teacher assigned successfully!', 'success');
                        loadClassAssignments();
                        // Reset form
                        $('#assignClass, #assignSection, #assignTeacher').val('');
                        $('#assignSection').prop('disabled', true);
                    } else {
                        showNotification(response.message || 'Failed to assign class teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred while assigning class teacher', 'error');
                }
            });
        }

        function loadClassAssignments() {
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_class_assignments' },
                success: function(response) {
                    if (response.success) {
                        displayClassAssignments(response.data);
                    }
                }
            });
        }

        function displayClassAssignments(assignments) {
            const container = $('#classAssignmentsTable');
            
            if (assignments.length === 0) {
                container.html('<p class="help-text">No class teacher assignments found</p>');
                return;
            }
            
            let html = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Teacher</th>
                            <th>Employee ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            assignments.forEach(assignment => {
                html += `
                    <tr>
                        <td>${escapeHtml(assignment.class_name)}</td>
                        <td>${escapeHtml(assignment.section_name)}</td>
                        <td>${escapeHtml(assignment.teacher_name)}</td>
                        <td>${escapeHtml(assignment.employee_number)}</td>
                        <td class="actions">
                            <button class="btn btn-outline btn-sm" onclick="reassignClassTeacher(${assignment.section_id})">
                                <i class="fas fa-exchange-alt"></i> Reassign
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.html(html);
        }

        // Subject assignment functionality
        function loadTeacherSubjects() {
            const teacherId = $('#subjectTeacher').val();
            
            if (!teacherId) {
                $('#subjectAssignmentsDisplay').html('<p class="help-text">Select a teacher to view their subject assignments</p>');
                $('input[name="subjects[]"]').prop('checked', false);
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_teacher_subjects', teacher_id: teacherId },
                success: function(response) {
                    if (response.success) {
                        // Update checkboxes
                        $('input[name="subjects[]"]').prop('checked', false);
                        response.data.forEach(subjectId => {
                            $(`input[name="subjects[]"][value="${subjectId}"]`).prop('checked', true);
                        });
                        
                        // Display current assignments
                        displayTeacherSubjects(response.subjects_details || []);
                    }
                }
            });
        }

        function displayTeacherSubjects(subjects) {
            const container = $('#subjectAssignmentsDisplay');
            
            if (subjects.length === 0) {
                container.html('<p class="help-text">No subjects assigned to this teacher</p>');
                return;
            }
            
            let html = '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';
            subjects.forEach(subject => {
                html += `<span class="status-badge status-active">${escapeHtml(subject.name)} (${escapeHtml(subject.code)})</span>`;
            });
            html += '</div>';
            
            container.html(html);
        }

        function handleSubjectAssignment() {
            const teacherId = $('#subjectTeacher').val();
            const selectedSubjects = $('input[name="subjects[]"]:checked').map(function() {
                return this.value;
            }).get();
            
            if (!teacherId) {
                showNotification('Please select a teacher', 'error');
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'POST',
                data: {
                    action: 'update_subject_assignments',
                    teacher_id: teacherId,
                    subject_ids: JSON.stringify(selectedSubjects)
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Subject assignments updated successfully!', 'success');
                        loadTeacherSubjects(); // Refresh display
                        loadStatistics(); // Update statistics
                    } else {
                        showNotification(response.message || 'Failed to update subject assignments', 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred while updating subject assignments', 'error');
                }
            });
        }

        function clearSubjectSelections() {
            $('input[name="subjects[]"]').prop('checked', false);
        }

        function loadStatistics() {
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_statistics' },
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;
                        $('#totalTeachers').text(stats.total_teachers);
                        $('#activeTeachers').text(stats.active_teachers);
                        $('#assignedClasses').text(stats.assigned_classes);
                        $('#subjectAssignments').text(stats.subject_assignments);
                    }
                }
            });
        }

        // Utility functions
        function searchTeachers() {
            const query = $('#teacherSearch').val().toLowerCase();
            $('#teachersTableBody tr').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(query));
            });
        }

        function showNotification(message, type) {
            const notification = $('#notification');
            notification.removeClass('success error').addClass(type);
            notification.text(message);
            notification.show();
            
            setTimeout(() => {
                notification.fadeOut();
            }, 5000);
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // External functions for button clicks
        function viewTeacherAssignments(teacherId) {
            // Switch to subject assignments tab and load teacher
            switchTab('subject-assignments');
            $('#subjectTeacher').val(teacherId);
            loadTeacherSubjects();
        }

        function reassignClassTeacher(sectionId) {
            // Implementation for reassigning class teacher
            showNotification('Please use the assignment form above to reassign the class teacher', 'error');
        }
    </script>
</body>
</html>
