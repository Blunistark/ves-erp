<?php
/**
 * Teacher Details View with Editing Capabilities
 * Displays comprehensive teacher information including subjects and timetable
 * Includes editing functionality for subject assignments, class teacher assignments, and timetable
 */

require_once __DIR__ . '/../../includes/functions.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check authentication and role
if (!isLoggedIn() || !hasRole(['admin', 'headmaster'])) {
    header("Location: ../../index.php");
    exit;
}

// Get teacher ID from URL
$teacher_id = $_GET['id'] ?? null;
if (!$teacher_id) {
    header("Location: teacher_management_unified.php");
    exit;
}

// Get current user role for permission checks
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch required data for dropdowns
$classes_raw = executeQuery("SELECT id, name FROM classes ORDER BY name");
$sections_raw = executeQuery("SELECT id, name, class_id, class_teacher_user_id FROM sections ORDER BY class_id, name");
$subjects_raw = executeQuery("SELECT id, name, code FROM subjects ORDER BY name");

// Ensure data is array for json_encode to prevent JS errors
$classes = is_array($classes_raw) ? $classes_raw : [];
$sections = is_array($sections_raw) ? $sections_raw : [];
$subjects = is_array($subjects_raw) ? $subjects_raw : [];

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Details - ERP System</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/teacher_management_unified.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .detail-section {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 24px;
            overflow: hidden;
        }
        
        .detail-header {
            background: var(--bg-light);
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .detail-body {
            padding: 24px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 4px;
            font-weight: 500;
        }
        
        .info-value {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .timetable-grid {
            overflow-x: auto;
        }
        
        .timetable-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        .timetable-table th,
        .timetable-table td {
            padding: 12px 8px;
            text-align: center;
            border: 1px solid var(--border-color);
            font-size: 0.875rem;
        }
        
        .timetable-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .period-cell {
            background: rgba(253, 93, 93, 0.1);
            color: var(--text-primary);
            line-height: 1.4;
        }
        
        .period-cell .subject {
            font-weight: 600;
        }
          .period-cell .class {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }
        
        .period-cell .time-slot {
            font-size: 0.7rem;
            color: #666;
            margin-top: 2px;
            font-weight: 400;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
          .stat-card {
            background: var(--bg-white);
            padding: 16px;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary-color);
            text-align: center;
        }
        
        .stat-card.leisure {
            border-left-color: #10b981;
        }
        
        .stat-card.leisure .stat-number {
            color: #10b981;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
          .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }
        
        .stat-details {
            font-size: 0.75rem;
            color: var(--text-secondary);
            margin-top: 4px;
            font-weight: 500;
            line-height: 1.2;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 20px;
            padding: 8px 12px;
            border-radius: var(--border-radius);
            transition: background-color 0.2s ease;
        }
        
        .back-button:hover {
            background: rgba(253, 93, 93, 0.1);
        }
        
        .loading-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 400px;
            flex-direction: column;
            gap: 16px;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--border-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
          .error-message {
            background: rgba(229, 62, 62, 0.1);
            color: #dc2626;
            padding: 16px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(229, 62, 62, 0.3);
            text-align: center;
        }
        
        /* Edit Mode Styles */
        .edit-button, .save-button, .cancel-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.875rem;
            margin-left: 8px;
            transition: background-color 0.2s ease;
        }
        
        .edit-button:hover, .save-button:hover {
            background: #e53e3e;
        }
        
        .cancel-button {
            background: #6b7280;
        }
        
        .cancel-button:hover {
            background: #4b5563;
        }
        
        .edit-mode {
            background: rgba(253, 93, 93, 0.05);
            border: 1px solid var(--primary-color);
        }
        
        .subject-selector, .class-selector {
            margin: 16px 0;
        }
        
        .subject-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 12px;
        }
        
        .subject-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: var(--bg-light);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
        }
        
        .subject-item input[type="checkbox"] {
            margin: 0;
        }
        
        .timetable-cell {
            position: relative;
            cursor: pointer;
            min-height: 60px;
            vertical-align: top;
        }
        
        .timetable-cell:hover {
            background: rgba(253, 93, 93, 0.1);
        }
        
        .period-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 60px;
            padding: 4px;
        }
        
        .add-period-btn {
            background: rgba(253, 93, 93, 0.1);
            color: var(--primary-color);
            border: 2px dashed var(--primary-color);
            width: 100%;
            height: 60px;
            border-radius: var(--border-radius);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            transition: all 0.2s ease;
        }
        
        .add-period-btn:hover {
            background: rgba(253, 93, 93, 0.2);
        }
        
        .remove-period-btn {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #dc2626;
            color: white;
            border: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 10px;
            cursor: pointer;
            display: none;
        }
          .period-cell:hover .remove-period-btn {
            display: block;
        }
        
        /* Conflict indicator styles */
        .conflict-indicator {
            position: absolute;
            top: 2px;
            left: 2px;
            background: #dc2626;
            color: white;
            font-size: 10px;
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }
        
        .period-cell.has-conflict {
            border: 2px solid #dc2626;
            background: rgba(220, 38, 38, 0.1);
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 24px;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            position: relative;
        }
        
        .close {
            position: absolute;
            right: 16px;
            top: 16px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 4px;
            font-weight: 500;
            color: var(--text-primary);
        }
          .form-group select,
        .form-group input[type="time"] {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 0.875rem;
        }
        
        /* Subject Assignment Styles */
        .subject-assignment-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px;
            margin-bottom: 8px;
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
        }
        
        .assignment-details {
            flex: 1;
        }
        
        .assignment-subject {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .assignment-class-section {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 2px;
        }
        
        .remove-assignment-btn {
            background: #dc2626;
            color: white;
            border: none;
            padding: 6px 8px;
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: 0.75rem;
        }
        
        .remove-assignment-btn:hover {
            background: #b91c1c;
        }
        
        /* Conflict warning styles */
        .conflict-warning {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .disabled-due-conflict {
            cursor: not-allowed !important;
            pointer-events: none;
            opacity: 0.6 !important;
        }
        
        .class-section-conflict {
            border-left: 4px solid #dc2626;
        }
        
        .teacher-conflict {
            border-left: 4px solid #f59e0b;
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <i class="fas fa-bars hamburger-icon"></i>
    </button>

    <div class="unified-container">
        <a href="teacher_management_unified.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Teacher Management
        </a>
        
        <div id="loadingContainer" class="loading-container">
            <div class="spinner"></div>
            <p>Loading teacher details...</p>
        </div>
        
        <div id="errorContainer" class="error-message" style="display: none;">
            <h4>Error Loading Teacher Details</h4>
            <p id="errorMessage"></p>
        </div>
        
        <div id="teacherDetailsContainer" style="display: none;">
            <!-- Teacher Basic Information -->
            <div class="detail-section">                <div class="detail-header">
                    <i class="fas fa-user"></i>
                    <h3>Teacher Information</h3>
                    <button class="edit-button" onclick="exportTeacherDetails()" style="background: #10b981;">
                        <i class="fas fa-download"></i> Export Details
                    </button>
                </div>
                <div class="detail-body">
                    <div id="teacherBasicInfo" class="info-grid">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
            
            <!-- Workload Statistics -->
            <div class="detail-section">
                <div class="detail-header">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Workload Overview</h3>
                </div>
                <div class="detail-body">
                    <div id="workloadStats" class="stats-grid">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
              <!-- Assigned Subjects -->
            <div class="detail-section">
                <div class="detail-header">
                    <i class="fas fa-book"></i>
                    <h3>Assigned Subjects</h3>
                    <button class="edit-button" onclick="toggleSubjectEdit()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="detail-body">
                    <div id="assignedSubjects">
                        <!-- Will be populated by JavaScript -->
                    </div>                    <div id="subjectEditMode" style="display: none;" class="edit-mode">
                        <h4>Assign Subjects with Classes and Sections:</h4>
                        <div style="margin-bottom: 16px;">
                            <button type="button" class="edit-button" onclick="addSubjectAssignment()">
                                <i class="fas fa-plus"></i> Add Subject Assignment
                            </button>
                        </div>
                        <div id="subjectAssignmentsList">
                            <!-- Will be populated with current assignments -->
                        </div>
                        <div style="margin-top: 16px;">
                            <button class="save-button" onclick="saveSubjectAssignments()">
                                <i class="fas fa-save"></i> Save Changes
                            </button>
                            <button class="cancel-button" onclick="cancelSubjectEdit()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Class Teacher Assignments -->
            <div class="detail-section">
                <div class="detail-header">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h3>Class Teacher Assignments</h3>
                    <button class="edit-button" onclick="toggleClassTeacherEdit()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="detail-body">
                    <div id="classAssignments">
                        <!-- Will be populated by JavaScript -->
                    </div>
                    <div id="classEditMode" style="display: none;" class="edit-mode">
                        <h4>Assign as Class Teacher:</h4>
                        <div class="form-group">
                            <label for="classTeacherSection">Select Section:</label>
                            <select id="classTeacherSection">
                                <option value="">Select a section...</option>
                            </select>
                        </div>
                        <div style="margin-top: 16px;">
                            <button class="save-button" onclick="saveClassTeacherAssignment()">
                                <i class="fas fa-save"></i> Assign
                            </button>
                            <button class="cancel-button" onclick="cancelClassTeacherEdit()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
              <!-- Weekly Timetable -->
            <div class="detail-section">                <div class="detail-header">
                    <i class="fas fa-calendar-alt"></i>
                    <h3>Weekly Timetable</h3>
                    <button class="edit-button" onclick="exportTimetable()" style="background: #10b981; margin-right: 8px;">
                        <i class="fas fa-download"></i> Export Timetable
                    </button>
                    <button class="edit-button" id="timetableEditBtn" onclick="toggleTimetableEdit()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
                <div class="detail-body">
                    <div id="timetableEditInfo" style="display: none; background: rgba(253, 93, 93, 0.1); padding: 12px; border-radius: 6px; margin-bottom: 16px; border: 1px solid var(--primary-color);">
                        <i class="fas fa-info-circle"></i> <strong>Timetable Edit Mode:</strong> Click on periods to edit them, or click "Add Period" buttons to add new periods.
                    </div>
                    <div id="weeklyTimetable" class="timetable-grid">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Subject Assignment Modal -->
    <div id="subjectAssignmentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeSubjectAssignmentModal()">&times;</span>
            <h3 id="subjectModalTitle">Add Subject Assignment</h3>
            <form id="subjectAssignmentForm">
                <div class="form-group">
                    <label for="assignSubject">Subject:</label>
                    <select id="assignSubject" required>
                        <option value="">Select Subject...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="assignClass">Class:</label>
                    <select id="assignClass" onchange="loadSectionsForSubjectAssignment(this.value)" required>
                        <option value="">Select Class...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="assignSection">Section:</label>
                    <select id="assignSection" required>
                        <option value="">Select Section...</option>
                    </select>
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" class="cancel-button" onclick="closeSubjectAssignmentModal()">Cancel</button>
                    <button type="submit" class="save-button">Add Assignment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Period Edit Modal -->
    <div id="periodModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closePeriodModal()">&times;</span>
            <h3 id="modalTitle">Add Period</h3>
            <form id="periodForm">
                <div class="form-group">
                    <label for="modalSubject">Subject:</label>
                    <select id="modalSubject" required>
                        <option value="">Select Subject...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="modalClass">Class:</label>
                    <select id="modalClass" onchange="loadSectionsForClass(this.value)" required>
                        <option value="">Select Class...</option>
                    </select>
                </div>                <div class="form-group">
                    <label for="modalSection">Section:</label>
                    <select id="modalSection" required>
                        <option value="">Select Section...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="modalTimeslot">Time Slot:</label>
                    <select id="modalTimeslot" required>
                        <option value="">Select Time Slot...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="modalNotes">Notes (Optional):</label>
                    <input type="text" id="modalNotes" placeholder="Additional notes...">
                </div>
                <div style="margin-top: 20px; text-align: right;">
                    <button type="button" class="cancel-button" onclick="closePeriodModal()">Cancel</button>
                    <button type="submit" class="save-button">Save Period</button>
                </div>
            </form>
        </div>
    </div>    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://unpkg.com/jspdf@latest/dist/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>        const teacherId = <?php echo json_encode($teacher_id); ?>;
        const availableClasses = <?php echo json_encode($classes); ?>;
        const availableSections = <?php echo json_encode($sections); ?>;
        const availableSubjects = <?php echo json_encode($subjects); ?>;
        let availableTimeslots = [];
          let currentTeacherData = null;
        let isSubjectEditMode = false;
        let isClassEditMode = false;
        let isTimetableEditMode = false;
        let currentEditPeriod = null;
        let currentSubjectAssignments = [];
          $(document).ready(function() {
            loadTimeslots();
            loadTeacherDetails();
            setupEventListeners();
            
            // Test jsPDF loading
            setTimeout(function() {
                if (typeof window.jspdf === 'undefined') {
                    console.warn('jsPDF library failed to load from CDN');
                } else {
                    console.log('jsPDF library loaded successfully');
                }
            }, 2000);
        });        function setupEventListeners() {
            // Period form submission
            $('#periodForm').on('submit', function(e) {
                e.preventDefault();
                savePeriod();
            });
            
            // Subject assignment form submission
            $('#subjectAssignmentForm').on('submit', function(e) {
                e.preventDefault();
                addSubjectAssignmentToList();
            });
            
            // Real-time conflict detection on dropdown changes
            $('#modalClass, #modalSection, #modalTimeslot').on('change', function() {
                checkRealTimeConflicts();
            });
        }
        
        function loadTimeslots() {
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: {
                    action: 'get_predefined_timeslots'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        availableTimeslots = response.data;
                        console.log('Timeslots loaded:', availableTimeslots);
                    } else {
                        console.error('Failed to load timeslots:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading timeslots:', error);
                }
            });
        }
          function loadTeacherDetails() {
            // Add cache-busting parameter to force fresh data
            const cacheBuster = new Date().getTime();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: {
                    action: 'get_teacher_details',
                    teacher_id: teacherId,
                    _t: cacheBuster  // Cache-busting parameter
                },
                cache: false,  // Disable jQuery caching
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayTeacherDetails(response.data);
                    } else {
                        showError(response.message || 'Failed to load teacher details');
                    }
                },
                error: function(xhr, status, error) {
                    showError('Error loading teacher details: ' + error);
                },
                complete: function() {
                    $('#loadingContainer').hide();
                }
            });
        }
          function displayTeacherDetails(data) {
            const { teacher, subjects, class_assignments, timetable, workload } = data;
            currentTeacherData = data;
            
            // Display basic teacher information
            const basicInfoHtml = `
                <div class="info-item">
                    <span class="info-label">Employee ID</span>
                    <span class="info-value">${teacher.employee_number || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Full Name</span>
                    <span class="info-value">${teacher.full_name}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Email</span>
                    <span class="info-value">${teacher.email}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Phone</span>
                    <span class="info-value">${teacher.phone || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Gender</span>
                    <span class="info-value">${teacher.gender || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Date of Birth</span>
                    <span class="info-value">${teacher.date_of_birth || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Qualification</span>
                    <span class="info-value">${teacher.qualification || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Experience</span>
                    <span class="info-value">${teacher.experience_years ? teacher.experience_years + ' years' : 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Joining Date</span>
                    <span class="info-value">${teacher.joined_date || 'N/A'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="status-badge status-${teacher.status}">${teacher.status}</span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Address</span>
                    <span class="info-value">${teacher.address || 'N/A'}</span>
                </div>
            `;
            $('#teacherBasicInfo').html(basicInfoHtml);            // Display workload statistics
            const totalPeriodsPerWeek = 48; // 8 periods × 6 days (Mon-Sat)
            const assignedPeriods = workload.total_periods_per_week || 0;
            const freePeriods = Math.max(0, totalPeriodsPerWeek - assignedPeriods);
            
            const workloadHtml = `
                <div class="stat-card">
                    <div class="stat-number">${workload.total_subjects || 0}</div>
                    <div class="stat-label">Subjects Assigned</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${workload.total_classes || 0}</div>
                    <div class="stat-label">Classes Teaching</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">${assignedPeriods}</div>
                    <div class="stat-label">Periods per Week</div>
                </div>
                <div class="stat-card leisure">
                    <div class="stat-number">${freePeriods}</div>
                    <div class="stat-label">Free Periods</div>
                </div>                <div class="stat-card">
                    <div class="stat-number">${class_assignments.length}</div>
                    <div class="stat-label">Class Teacher Of</div>
                    ${class_assignments.length > 0 ? `<div class="stat-details">${class_assignments.map(a => a.class_section).join(', ')}</div>` : ''}
                </div>
            `;
            $('#workloadStats').html(workloadHtml);
            
            // Display assigned subjects
            displaySubjects(subjects);
            
            // Display class teacher assignments
            displayClassAssignments(class_assignments);
            
            // Display timetable
            displayTimetable(timetable);
            
            $('#teacherDetailsContainer').show();
        }
          function displayTimetable(timetable) {
            const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            const dayLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const periods = [1, 2, 3, 4, 5, 6, 7, 8];
            
            let timetableHtml = `
                <table class="timetable-table">
                    <thead>
                        <tr>
                            <th>Period/Day</th>
                            ${dayLabels.map(day => `<th>${day}</th>`).join('')}
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            periods.forEach(period => {
                timetableHtml += `<tr><td><strong>Period ${period}</strong></td>`;
                
                days.forEach((day, dayIndex) => {
                    const dayPeriods = timetable[day] || [];
                    const periodData = dayPeriods.find(p => p.period_number == period);
                      if (periodData) {
                        // Check if this period has conflicts
                        const hasConflict = periodData.conflict || false;
                        const conflictClass = hasConflict ? ' has-conflict' : '';
                        const conflictIndicator = hasConflict ? '<div class="conflict-indicator">!</div>' : '';
                          timetableHtml += `
                            <td class="period-cell timetable-cell${conflictClass}" onclick="editPeriod('${day}', ${period}, ${JSON.stringify(periodData).replace(/"/g, '&quot;')})">
                                <div class="period-content">
                                    <div class="subject">${periodData.subject_name || 'Unknown Subject'}</div>
                                    <div class="class">${periodData.class_name || ''} - ${periodData.section_name || ''}</div>
                                    <div class="time-slot" style="font-size: 0.7rem; color: #666; margin-top: 2px;">
                                        ${periodData.start_time ? formatTime(periodData.start_time) : ''} - ${periodData.end_time ? formatTime(periodData.end_time) : ''}
                                    </div>
                                </div>
                                ${conflictIndicator}
                                ${isTimetableEditMode ? `<button class="remove-period-btn" onclick="removePeriod(event, '${day}', ${period})">&times;</button>` : ''}
                            </td>
                        `;
                    } else {
                        if (isTimetableEditMode) {
                            timetableHtml += `
                                <td class="timetable-cell">
                                    <button class="add-period-btn" onclick="addPeriod('${day}', ${period})">
                                        <i class="fas fa-plus"></i> Add Period
                                    </button>
                                </td>
                            `;
                        } else {
                            timetableHtml += '<td>-</td>';
                        }
                    }
                });
                
                timetableHtml += '</tr>';
            });
            
            timetableHtml += '</tbody></table>';
            
            if (Object.keys(timetable).length === 0 && !isTimetableEditMode) {
                $('#weeklyTimetable').html('<p class="text-muted">No timetable data available</p>');
            } else {
                $('#weeklyTimetable').html(timetableHtml);
            }
        }
        
        // Subject Assignment Functions
        function displaySubjects(subjects) {
            if (subjects.length > 0) {
                let subjectsHtml = '<div class="data-table"><table><thead><tr><th>Subject</th><th>Code</th><th>Classes/Sections</th></tr></thead><tbody>';
                subjects.forEach(subject => {
                    subjectsHtml += `
                        <tr>
                            <td>${subject.subject_name}</td>
                            <td>${subject.subject_code}</td>
                            <td>${subject.classes_sections || 'All Classes'}</td>
                        </tr>
                    `;
                });
                subjectsHtml += '</tbody></table></div>';
                $('#assignedSubjects').html(subjectsHtml);
            } else {
                $('#assignedSubjects').html('<p class="text-muted">No subjects assigned</p>');
            }
        }
          function toggleSubjectEdit() {
            if (isSubjectEditMode) {
                cancelSubjectEdit();
            } else {
                isSubjectEditMode = true;
                initializeSubjectAssignments();
                $('#subjectEditMode').show();
                $('.edit-button').text('Cancel Edit');
            }
        }
        
        function initializeSubjectAssignments() {
            // Copy current assignments to working array
            currentSubjectAssignments = [...(currentTeacherData.subjects || [])];
            displaySubjectAssignmentsList();
        }
        
        function displaySubjectAssignmentsList() {
            let listHtml = '';
            
            if (currentSubjectAssignments.length > 0) {
                currentSubjectAssignments.forEach((assignment, index) => {
                    listHtml += `
                        <div class="subject-assignment-item" data-index="${index}">
                            <div class="assignment-details">
                                <div class="assignment-subject">${assignment.subject_name}</div>
                                <div class="assignment-class-section">${assignment.classes_sections || 'All Classes'}</div>
                            </div>
                            <button class="remove-assignment-btn" onclick="removeSubjectAssignment(${index})">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    `;
                });
            } else {
                listHtml = '<p class="text-muted">No subject assignments</p>';
            }
            
            $('#subjectAssignmentsList').html(listHtml);
        }
        
        function addSubjectAssignment() {
            // Populate subject assignment modal
            populateSubjectAssignmentModal();
            $('#subjectAssignmentModal').show();
        }
        
        function populateSubjectAssignmentModal() {
            // Populate subjects dropdown
            let subjectOptions = '<option value="">Select Subject...</option>';
            availableSubjects.forEach(subject => {
                subjectOptions += `<option value="${subject.id}">${subject.name} (${subject.code || ''})</option>`;
            });
            $('#assignSubject').html(subjectOptions);
            
            // Populate classes dropdown
            let classOptions = '<option value="">Select Class...</option>';
            availableClasses.forEach(cls => {
                classOptions += `<option value="${cls.id}">${cls.name}</option>`;
            });
            $('#assignClass').html(classOptions);
            
            // Clear sections
            $('#assignSection').html('<option value="">Select Section...</option>');
        }
        
        function loadSectionsForSubjectAssignment(classId) {
            let sectionOptions = '<option value="">Select Section...</option>';
            if (classId) {
                availableSections.forEach(section => {
                    if (section.class_id == classId) {
                        sectionOptions += `<option value="${section.id}">${section.name}</option>`;
                    }
                });
            }
            $('#assignSection').html(sectionOptions);
        }
        
        function addSubjectAssignmentToList() {
            const subjectId = $('#assignSubject').val();
            const classId = $('#assignClass').val();
            const sectionId = $('#assignSection').val();
            
            if (!subjectId || !classId || !sectionId) {
                alert('Please select subject, class, and section');
                return;
            }
            
            // Get names for display
            const subject = availableSubjects.find(s => s.id == subjectId);
            const className = availableClasses.find(c => c.id == classId)?.name;
            const sectionName = availableSections.find(s => s.id == sectionId)?.name;
            
            // Check if assignment already exists
            const existingAssignment = currentSubjectAssignments.find(a => 
                a.subject_id == subjectId && a.class_id == classId && a.section_id == sectionId
            );
            
            if (existingAssignment) {
                alert('This subject is already assigned to this class and section');
                return;
            }
            
            // Add to assignments array
            const newAssignment = {
                subject_id: subjectId,
                class_id: classId,
                section_id: sectionId,
                subject_name: subject.name,
                subject_code: subject.code,
                classes_sections: `${className} - ${sectionName}`
            };
            
            currentSubjectAssignments.push(newAssignment);
            displaySubjectAssignmentsList();
            closeSubjectAssignmentModal();
        }
        
        function removeSubjectAssignment(index) {
            if (confirm('Are you sure you want to remove this subject assignment?')) {
                currentSubjectAssignments.splice(index, 1);
                displaySubjectAssignmentsList();
            }
        }
        
        function closeSubjectAssignmentModal() {
            $('#subjectAssignmentModal').hide();
            $('#subjectAssignmentForm')[0].reset();
        }
          function saveSubjectAssignments() {
            // Prepare assignments data for API
            const assignmentsData = currentSubjectAssignments.map(assignment => ({
                subject_id: assignment.subject_id,
                class_id: assignment.class_id,
                section_id: assignment.section_id
            }));
            
            const formData = new FormData();
            formData.append('action', 'update_subject_assignments');
            formData.append('teacher_id', teacherId);
            formData.append('assignments', JSON.stringify(assignmentsData));
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Subject assignments updated successfully');
                        loadTeacherDetails();
                        cancelSubjectEdit();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to update subject assignments'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error updating subject assignments: ' + error);
                }
            });
        }
        
        function cancelSubjectEdit() {
            isSubjectEditMode = false;
            $('#subjectEditMode').hide();
            $('.edit-button').text('Edit');
        }
        
        // Class Teacher Assignment Functions
        function displayClassAssignments(class_assignments) {
            if (class_assignments.length > 0) {
                let classHtml = '<div class="data-table"><table><thead><tr><th>Class</th><th>Section</th><th>Actions</th></tr></thead><tbody>';
                class_assignments.forEach(assignment => {
                    classHtml += `
                        <tr>
                            <td>${assignment.class_name}</td>
                            <td>${assignment.section_name}</td>
                            <td>
                                <button class="cancel-button" onclick="removeClassTeacher('${assignment.section_id}')">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </td>
                        </tr>
                    `;
                });
                classHtml += '</tbody></table></div>';
                $('#classAssignments').html(classHtml);
            } else {
                $('#classAssignments').html('<p class="text-muted">No class teacher assignments</p>');
            }
        }
        
        function toggleClassTeacherEdit() {
            if (isClassEditMode) {
                cancelClassTeacherEdit();
            } else {
                isClassEditMode = true;
                populateClassTeacherSelector();
                $('#classEditMode').show();
                $('.edit-button').text('Cancel Edit');
            }
        }
        
        function populateClassTeacherSelector() {
            let selectorHtml = '<option value="">Select a section...</option>';
            
            availableSections.forEach(section => {
                if (!section.class_teacher_user_id) { // Only show unassigned sections
                    const className = availableClasses.find(c => c.id == section.class_id)?.name || 'Unknown';
                    selectorHtml += `<option value="${section.id}">${className} - ${section.name}</option>`;
                }
            });
            
            $('#classTeacherSection').html(selectorHtml);
        }
        
        function saveClassTeacherAssignment() {
            const sectionId = $('#classTeacherSection').val();
            
            if (!sectionId) {
                alert('Please select a section');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'assign_class_teacher');
            formData.append('teacher_id', teacherId);
            formData.append('section_id', sectionId);
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        alert('Class teacher assigned successfully');
                        loadTeacherDetails();
                        cancelClassTeacherEdit();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to assign class teacher'));
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error assigning class teacher: ' + error);
                }
            });
        }
          function removeClassTeacher(sectionId) {
            console.log('removeClassTeacher called with sectionId:', sectionId);
            
            if (!confirm('Are you sure you want to remove this class teacher assignment?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'remove_class_teacher');
            formData.append('section_id', sectionId);
            
            console.log('Sending remove class teacher request for section_id:', sectionId);
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',                success: function(response) {
                    console.log('Remove class teacher response:', response);
                    if (response.success) {
                        alert('Class teacher removed successfully');
                        // Force a complete reload of teacher details
                        $('#loadingContainer').show();
                        $('#teacherDetailsContainer').hide();
                        loadTeacherDetails();
                    } else {
                        alert('Error: ' + (response.message || 'Failed to remove class teacher'));
                    }
                },                error: function(xhr, status, error) {
                    console.error('Remove class teacher error:', {xhr, status, error});
                    console.error('Response text:', xhr.responseText);
                    alert('Error removing class teacher: ' + error);
                }
            });
        }
        
        function cancelClassTeacherEdit() {
            isClassEditMode = false;
            $('#classEditMode').hide();
            $('.edit-button').text('Edit');
        }        // Timetable Edit Functions
        function toggleTimetableEdit() {
            if (isTimetableEditMode) {
                isTimetableEditMode = false;
                $('#timetableEditBtn').html('<i class="fas fa-edit"></i> Edit');
                $('#timetableEditInfo').hide();
                displayTimetable(currentTeacherData.timetable);
            } else {
                isTimetableEditMode = true;
                $('#timetableEditBtn').html('<i class="fas fa-times"></i> Cancel Edit');
                $('#timetableEditInfo').show();
                
                // Ensure teacher data is loaded before populating dropdowns
                if (currentTeacherData) {
                    populateModalDropdowns();
                    displayTimetable(currentTeacherData.timetable);
                } else {
                    console.error('Teacher data not loaded yet');
                    alert('Please wait for teacher data to load completely');
                    isTimetableEditMode = false;
                    $('#timetableEditBtn').html('<i class="fas fa-edit"></i> Edit');
                    $('#timetableEditInfo').hide();
                }
            }
        }        function populateModalDropdowns() {
            // Populate subjects dropdown with teacher's assigned subjects
            let subjectOptions = '<option value="">Select Subject...</option>';
            
            // Check if currentTeacherData and subjects exist
            if (currentTeacherData && currentTeacherData.subjects && currentTeacherData.subjects.length > 0) {
                currentTeacherData.subjects.forEach(subject => {
                    subjectOptions += `<option value="${subject.subject_id}">${subject.subject_name} (${subject.subject_code || ''})</option>`;
                });
            } else {
                // Fallback: use all available subjects if no specific assignments found
                console.log('No assigned subjects found, using all available subjects');
                availableSubjects.forEach(subject => {
                    subjectOptions += `<option value="${subject.id}">${subject.name} (${subject.code || ''})</option>`;
                });
            }
            $('#modalSubject').html(subjectOptions);
            
            // Populate classes dropdown
            let classOptions = '<option value="">Select Class...</option>';
            availableClasses.forEach(cls => {
                classOptions += `<option value="${cls.id}">${cls.name}</option>`;
            });
            $('#modalClass').html(classOptions);
              // Populate timeslots dropdown
            let timeslotOptions = '<option value="">Select Time Slot...</option>';
            availableTimeslots.forEach(timeslot => {
                timeslotOptions += `<option value="${timeslot.period_number}" data-start="${timeslot.start_time}" data-end="${timeslot.end_time}">${timeslot.display_name}</option>`;
            });
            $('#modalTimeslot').html(timeslotOptions);
        }
        
        function loadSectionsForClass(classId) {
            let sectionOptions = '<option value="">Select Section...</option>';
            if (classId) {
                availableSections.forEach(section => {
                    if (section.class_id == classId) {
                        sectionOptions += `<option value="${section.id}">${section.name}</option>`;
                    }
                });
            }
            $('#modalSection').html(sectionOptions);
        }        function addPeriod(day, period) {
            if (!isTimetableEditMode) {
                alert('Please click "Edit" button first to enable timetable editing mode');
                return;
            }
              currentEditPeriod = { day: day, period: period, isNew: true };
            $('#modalTitle').text(`Add Period - ${day.charAt(0).toUpperCase() + day.slice(1)} Period ${period}`);
            
            // Clear form and any existing warnings
            $('#periodForm')[0].reset();
            $('#modalSection').html('<option value="">Select Section...</option>');
            $('#conflictWarning').remove();
            
            // Ensure dropdowns are populated
            populateModalDropdowns();
            
            // Pre-select the timeslot that matches the period number
            setTimeout(() => {
                const matchingTimeslot = availableTimeslots.find(ts => ts.period_number == period);
                if (matchingTimeslot) {
                    $('#modalTimeslot').val(matchingTimeslot.period_number);
                }
            }, 100);
            
            $('#periodModal').show();
        }function editPeriod(day, period, periodData) {
            if (!isTimetableEditMode) {
                alert('Please click "Edit" button first to enable timetable editing mode');
                return;
            }
              currentEditPeriod = { day: day, period: period, isNew: false, data: periodData };
            $('#modalTitle').text(`Edit Period - ${day.charAt(0).toUpperCase() + day.slice(1)} Period ${period}`);
            
            // Clear any existing warnings
            $('#conflictWarning').remove();
            
            // Ensure dropdowns are populated first
            populateModalDropdowns();
            
            // Small delay to ensure dropdowns are populated before setting values
            setTimeout(() => {
                // Populate form with existing data
                $('#modalSubject').val(periodData.subject_id);
                $('#modalClass').val(periodData.class_id);
                loadSectionsForClass(periodData.class_id);
                
                setTimeout(() => {
                    $('#modalSection').val(periodData.section_id);
                }, 100);
                
                // Set timeslot based on period number (if available in timeslots)
                const matchingTimeslot = availableTimeslots.find(ts => 
                    ts.period_number == period || 
                    (ts.start_time === periodData.start_time && ts.end_time === periodData.end_time)
                );
                if (matchingTimeslot) {
                    $('#modalTimeslot').val(matchingTimeslot.period_number);
                } else {
                    // If no matching timeslot found, try to match by period number
                    $('#modalTimeslot').val(period);
                }
                
                $('#modalNotes').val(periodData.notes || '');
            }, 100);
            
            $('#periodModal').show();
        }
        
        function removePeriod(event, day, period) {
            event.stopPropagation();
            
            if (!confirm('Are you sure you want to remove this period?')) {
                return;
            }
            
            const periodData = {
                teacher_id: teacherId,
                day_of_week: day,
                period_number: period
            };
            
            fetch('teacher_management_api.php?action=delete_teacher_period', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(periodData)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Period removed successfully');
                    loadTeacherDetails();
                } else {
                    alert('Error: ' + (result.message || 'Failed to remove period'));
                }
            })
            .catch(error => {
                alert('Error removing period: ' + error);
            });
        }        function savePeriod() {
            const selectedTimeslot = $('#modalTimeslot option:selected');
            const timeslotPeriod = selectedTimeslot.val();
            const startTime = selectedTimeslot.data('start');
            const endTime = selectedTimeslot.data('end');
            
            // Validate that the selected timeslot period matches the grid period
            if (timeslotPeriod != currentEditPeriod.period) {
                if (!confirm(`You selected Period ${timeslotPeriod} timeslot for Period ${currentEditPeriod.period}. This might cause confusion. Do you want to continue?`)) {
                    return;
                }
            }
            
            const periodData = {
                teacher_id: teacherId,
                day_of_week: currentEditPeriod.day,
                period_number: currentEditPeriod.period, // Use the grid period, not the timeslot period
                start_time: startTime,
                end_time: endTime,
                subject_id: $('#modalSubject').val(),
                class_id: $('#modalClass').val(),
                section_id: $('#modalSection').val(),
                notes: $('#modalNotes').val()
            };
            
            // Validate required fields
            if (!periodData.subject_id || !periodData.class_id || !periodData.section_id || 
                !timeslotPeriod || !startTime || !endTime) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Check for conflicts before saving
            checkConflictsBeforeSave(periodData);
        }        function checkConflictsBeforeSave(periodData) {
            // Get the period ID to exclude from conflict check
            let excludePeriodId = 0;
            if (!currentEditPeriod.isNew && currentEditPeriod.data && currentEditPeriod.data.period_id) {
                excludePeriodId = currentEditPeriod.data.period_id;
            } else if (!currentEditPeriod.isNew && currentEditPeriod.data && currentEditPeriod.data.id) {
                excludePeriodId = currentEditPeriod.data.id;
            }
            
            console.log('Checking conflicts for:', periodData);
            console.log('Excluding period ID:', excludePeriodId);
            
            // Check for teacher conflicts
            const checkUrl = `teacher_management_api.php?action=check_teacher_conflicts&teacher_id=${periodData.teacher_id}&day_of_week=${periodData.day_of_week}&period_number=${periodData.period_number}&exclude_period_id=${excludePeriodId}`;
            
            fetch(checkUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Conflict check response:', text);
                
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse conflict check JSON:', text);
                    // If conflict check fails, ask user if they want to proceed anyway
                    if (confirm('Unable to check for conflicts. Do you want to proceed anyway?')) {
                        savePerformActualSave(periodData);
                    }
                    return;
                }
                
                if (result.success && result.has_conflict && result.conflicts && result.conflicts.length > 0) {
                    showConflictDialog(result.conflicts, periodData);
                } else {
                    // No conflicts, proceed with saving
                    savePerformActualSave(periodData);
                }
            })
            .catch(error => {
                console.error('Error checking conflicts:', error);
                // If conflict check fails, ask user if they want to proceed anyway
                if (confirm('Unable to check for conflicts due to an error. Do you want to proceed anyway?\n\nError: ' + error.message)) {
                    savePerformActualSave(periodData);
                }
            });
        }
          function showConflictDialog(conflicts, periodData) {
            let conflictMessage = '⚠️ SCHEDULING CONFLICT DETECTED!\n\n';
            conflictMessage += `You are trying to assign ${periodData.teacher_id === teacherId ? 'this teacher' : 'a teacher'} to:\n`;
            conflictMessage += `Day: ${periodData.day_of_week.charAt(0).toUpperCase() + periodData.day_of_week.slice(1)}\n`;
            conflictMessage += `Period: ${periodData.period_number}\n`;
            conflictMessage += `Time: ${periodData.start_time} - ${periodData.end_time}\n\n`;
            
            conflictMessage += 'But this teacher is ALREADY assigned to:\n\n';
            
            conflicts.forEach((conflict, index) => {
                conflictMessage += `${index + 1}. Subject: ${conflict.subject_name} (${conflict.subject_code})\n`;
                conflictMessage += `   Class: ${conflict.class_name} - Section: ${conflict.section_name}\n`;
                conflictMessage += `   Time: ${conflict.start_time} - ${conflict.end_time}\n`;
                conflictMessage += `   Status: ${conflict.timetable_status}\n\n`;
            });
            
            conflictMessage += '❌ This will create a DOUBLE-BOOKING!\n\n';
            conflictMessage += '❓ Do you want to OVERRIDE the existing assignment?\n';
            conflictMessage += '⚠️  This may cause scheduling issues and confusion.';
            
            const result = confirm(conflictMessage);
            
            if (result) {
                console.log('User chose to override conflict');
                savePerformActualSave(periodData);
            } else {
                console.log('User cancelled due to conflict');
                alert('Operation cancelled. No changes were made.');
            }
        }
          function savePerformActualSave(periodData) {
            fetch('teacher_management_api.php?action=save_teacher_period', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(periodData)
            })
            .then(response => {
                // Check if response is ok first
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                // Get response text first to debug
                return response.text();
            })
            .then(text => {
                console.log('Raw response:', text);
                
                // Try to parse as JSON
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', text);
                    throw new Error('Server returned invalid JSON. Response: ' + text.substring(0, 200));
                }
                  if (result.success) {
                    alert(currentEditPeriod.isNew ? 'Period added successfully' : 'Period updated successfully');
                    closePeriodModal();
                    loadTeacherDetails();
                } else if (result.duplicate) {
                    alert('Scheduling Conflict!\n\n' + (result.message || 'This teacher already has a class at this time slot'));
                } else if (result.conflict) {
                    alert('Conflict detected: ' + (result.message || 'This time slot is already occupied'));
                } else {
                    alert('Error: ' + (result.message || 'Failed to save period'));
                }
            })
            .catch(error => {
                console.error('Error saving period:', error);
                alert('Error saving period: ' + error.message);
            });
        }
          function closePeriodModal() {
            $('#periodModal').hide();
            $('#conflictWarning').remove();
            currentEditPeriod = null;
        }
          function showError(message) {
            $('#errorMessage').text(message);
            $('#errorContainer').show();
        }
        
        function formatTime(timeString) {
            if (!timeString) return '';
            // Convert 24-hour format to 12-hour format
            const time = new Date('1970-01-01T' + timeString + 'Z');
            return time.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true,
                timeZone: 'UTC'
            });
        }
          function checkRealTimeConflicts() {
            const classId = $('#modalClass').val();
            const sectionId = $('#modalSection').val();
            const selectedTimeslot = $('#modalTimeslot option:selected');
            const timeslotPeriod = selectedTimeslot.val();
            
            // Clear any existing conflict warnings first
            $('#conflictWarning').remove();
            $('#periodForm .save-button').prop('disabled', false).removeClass('disabled-due-conflict').css({
                'background-color': '',
                'opacity': ''
            });
            
            if (!classId || !sectionId || !timeslotPeriod || !currentEditPeriod) {
                return;
            }
            
            // Add a small delay to prevent too many rapid API calls
            clearTimeout(window.conflictCheckTimeout);
            window.conflictCheckTimeout = setTimeout(() => {
                // Check for class/section conflicts (same class-section at same time)
                checkClassSectionConflicts(classId, sectionId, timeslotPeriod);
                
                // Check for teacher conflicts (same teacher at same time) - with slight delay
                setTimeout(() => {
                    checkTeacherConflictsRealTime();
                }, 100);
            }, 300);
        }
        
        function checkClassSectionConflicts(classId, sectionId, periodNumber) {
            const day = currentEditPeriod.day;
            const currentPeriodId = currentEditPeriod.isNew ? 0 : (currentEditPeriod.data?.period_id || 0);
            
            fetch(`teacher_management_api.php?action=check_class_section_conflicts&class_id=${classId}&section_id=${sectionId}&day_of_week=${day}&period_number=${periodNumber}&exclude_period_id=${currentPeriodId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.has_conflict && result.conflicts.length > 0) {
                    showRealTimeConflictWarning('Class/Section Conflict', result.conflicts, 'class-section');
                }
            })
            .catch(error => {
                console.error('Error checking class/section conflicts:', error);
            });
        }
        
        function checkTeacherConflictsRealTime() {
            const day = currentEditPeriod.day;
            const period = currentEditPeriod.period;
            const currentPeriodId = currentEditPeriod.isNew ? 0 : (currentEditPeriod.data?.period_id || 0);
            
            fetch(`teacher_management_api.php?action=check_teacher_conflicts&teacher_id=${teacherId}&day_of_week=${day}&period_number=${period}&exclude_period_id=${currentPeriodId}`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.has_conflict && result.conflicts.length > 0) {
                    showRealTimeConflictWarning('Teacher Conflict', result.conflicts, 'teacher');
                }
            })
            .catch(error => {
                console.error('Error checking teacher conflicts:', error);
            });
        }
        
        function showRealTimeConflictWarning(conflictType, conflicts, warningType) {
            // Remove any existing warnings
            $('#conflictWarning').remove();
            
            let warningHtml = `
                <div id="conflictWarning" class="conflict-warning ${warningType}-conflict" style="
                    background: #fef2f2; 
                    border: 1px solid #fecaca; 
                    border-radius: 6px; 
                    padding: 12px; 
                    margin: 16px 0;
                    color: #dc2626;
                ">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <i class="fas fa-exclamation-triangle" style="color: #dc2626;"></i>
                        <strong>${conflictType} Detected!</strong>
                    </div>
                    <div style="font-size: 0.875rem; line-height: 1.4;">
            `;
            
            conflicts.forEach((conflict, index) => {
                if (warningType === 'class-section') {
                    warningHtml += `
                        <div style="margin-bottom: 6px;">
                            • Class ${conflict.class_name}-${conflict.section_name} already has <strong>${conflict.subject_name}</strong> 
                            with ${conflict.teacher_name} at this time
                        </div>
                    `;
                } else {
                    warningHtml += `
                        <div style="margin-bottom: 6px;">
                            • Teacher ${conflict.teacher_name} is already teaching <strong>${conflict.subject_name}</strong> 
                            to ${conflict.class_name}-${conflict.section_name} at this time
                        </div>
                    `;
                }
            });
            
            warningHtml += `
                    </div>
                    <div style="font-size: 0.75rem; margin-top: 8px; font-style: italic;">
                        ${warningType === 'class-section' ? 
                            'This will overwrite the existing class schedule.' : 
                            'This will create a double-booking for the teacher.'}
                    </div>
                </div>
            `;
            
            // Insert warning before the form buttons
            $('#periodForm').find('[style*="margin-top: 20px"]').before(warningHtml);
            
            // Optionally disable save button for critical conflicts
            if (warningType === 'class-section') {
                $('#periodForm .save-button').addClass('disabled-due-conflict').css({
                    'background-color': '#dc2626',
                    'opacity': '0.7'
                });
            }
        }
        
        // Export Functions
        function exportTeacherDetails() {
            if (!currentTeacherData) {
                alert('Teacher data not loaded yet. Please wait and try again.');
                return;
            }
            
            // Check if jsPDF is loaded
            if (typeof window.jspdf === 'undefined') {
                alert('PDF library is still loading. Please try again in a moment.');
                return;
            }
            
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Set up the document
            doc.setFontSize(20);
            doc.text('Teacher Details Report', 20, 20);
            
            // Teacher basic information
            const teacher = currentTeacherData.teacher;
            doc.setFontSize(14);
            doc.text('Personal Information', 20, 40);
            
            doc.setFontSize(10);
            let yPosition = 50;
            const lineHeight = 8;
            
            // Add teacher details
            const details = [
                `Employee ID: ${teacher.employee_number || 'N/A'}`,
                `Full Name: ${teacher.full_name || 'N/A'}`,
                `Email: ${teacher.email || 'N/A'}`,
                `Phone: ${teacher.phone || 'N/A'}`,
                `Gender: ${teacher.gender || 'N/A'}`,
                `Date of Birth: ${teacher.date_of_birth || 'N/A'}`,
                `Qualification: ${teacher.qualification || 'N/A'}`,
                `Experience: ${teacher.experience || 'N/A'}`,
                `Joining Date: ${teacher.joining_date || 'N/A'}`,
                `Status: ${teacher.status || 'N/A'}`,
                `Address: ${teacher.address || 'N/A'}`
            ];
            
            details.forEach(detail => {
                doc.text(detail, 20, yPosition);
                yPosition += lineHeight;
            });
            
            // Workload Statistics
            yPosition += 10;
            doc.setFontSize(14);
            doc.text('Workload Overview', 20, yPosition);
            yPosition += 10;
            
            doc.setFontSize(10);
            const workload = currentTeacherData.workload;
            const totalPeriodsPerWeek = 48;
            const assignedPeriods = workload.total_periods_per_week || 0;
            const freePeriods = Math.max(0, totalPeriodsPerWeek - assignedPeriods);
            
            const workloadDetails = [
                `Subjects Assigned: ${workload.total_subjects || 0}`,
                `Classes Teaching: ${workload.total_classes || 0}`,
                `Periods per Week: ${assignedPeriods}`,
                `Free Periods: ${freePeriods}`,
                `Class Teacher Of: ${currentTeacherData.class_assignments.length}`
            ];
            
            workloadDetails.forEach(detail => {
                doc.text(detail, 20, yPosition);
                yPosition += lineHeight;
            });
            
            // Assigned Subjects
            if (currentTeacherData.subjects && currentTeacherData.subjects.length > 0) {
                yPosition += 10;
                doc.setFontSize(14);
                doc.text('Assigned Subjects', 20, yPosition);
                yPosition += 10;
                
                doc.setFontSize(10);
                currentTeacherData.subjects.forEach(subject => {
                    doc.text(`• ${subject.subject_name} (${subject.subject_code || ''}) - ${subject.classes_sections || 'All Classes'}`, 20, yPosition);
                    yPosition += lineHeight;
                });
            }
            
            // Class Teacher Assignments
            if (currentTeacherData.class_assignments && currentTeacherData.class_assignments.length > 0) {
                yPosition += 10;
                doc.setFontSize(14);
                doc.text('Class Teacher Assignments', 20, yPosition);
                yPosition += 10;
                
                doc.setFontSize(10);
                currentTeacherData.class_assignments.forEach(assignment => {
                    doc.text(`• ${assignment.class_name} - ${assignment.section_name}`, 20, yPosition);
                    yPosition += lineHeight;
                });
            }
            
            // Add timestamp
            doc.setFontSize(8);
            doc.text(`Generated on: ${new Date().toLocaleString()}`, 20, 280);
            
            // Save the PDF
            doc.save(`${teacher.full_name || 'Teacher'}_Details_${new Date().toISOString().split('T')[0]}.pdf`);
        }
        
        function exportTimetable() {
            if (!currentTeacherData) {
                alert('Teacher data not loaded yet. Please wait and try again.');
                return;
            }
            
            // Check if jsPDF is loaded
            if (typeof window.jspdf === 'undefined') {
                alert('PDF library is still loading. Please try again in a moment.');
                return;
            }
            
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('landscape'); // Use landscape for better timetable layout
            
            // Set up the document
            doc.setFontSize(16);
            const teacher = currentTeacherData.teacher;
            doc.text(`Weekly Timetable - ${teacher.full_name || 'Teacher'}`, 20, 20);
            
            doc.setFontSize(9);
            doc.text(`Employee ID: ${teacher.employee_number || 'N/A'}`, 20, 30);
            doc.text(`Generated on: ${new Date().toLocaleString()}`, 20, 38);
            
            // Timetable data
            const timetable = currentTeacherData.timetable;
            const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            const dayLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const periods = [1, 2, 3, 4, 5, 6, 7, 8];
            
            // Table setup - better aligned and centered
            const pageWidth = doc.internal.pageSize.getWidth();
            const totalTableWidth = 7 * 40; // 7 columns × 40px width
            const startX = (pageWidth - totalTableWidth) / 2; // Center the table
            const startY = 50;
            const cellWidth = 40;
            const cellHeight = 18;
            const headerHeight = 22;
            
            // Draw table headers
            doc.setFontSize(10);
            doc.setFont(undefined, 'bold');
            
            // Time column header
            doc.rect(startX, startY, cellWidth, headerHeight);
            doc.text('Period', startX + 12, startY + 14);
            
            // Day headers
            dayLabels.forEach((day, index) => {
                const x = startX + cellWidth + (index * cellWidth);
                doc.rect(x, startY, cellWidth, headerHeight);
                // Center the day text
                const textWidth = doc.getTextWidth(day);
                const centerX = x + (cellWidth - textWidth) / 2;
                doc.text(day, centerX, startY + 14);
            });
            
            // Draw table rows
            doc.setFont(undefined, 'normal');
            periods.forEach((period, periodIndex) => {
                const y = startY + headerHeight + (periodIndex * cellHeight);
                
                // Period number cell
                doc.rect(startX, y, cellWidth, cellHeight);
                doc.setFontSize(9);
                const periodText = `P${period}`;
                const periodTextWidth = doc.getTextWidth(periodText);
                const periodCenterX = startX + (cellWidth - periodTextWidth) / 2;
                doc.text(periodText, periodCenterX, y + 11);
                
                // Day cells
                days.forEach((day, dayIndex) => {
                    const x = startX + cellWidth + (dayIndex * cellWidth);
                    doc.rect(x, y, cellWidth, cellHeight);
                    
                    // Find period data for this day and period
                    const dayPeriods = timetable[day] || [];
                    const periodData = dayPeriods.find(p => p.period_number == period);
                    
                    if (periodData) {
                        // Add subject and class info - better aligned
                        doc.setFontSize(7);
                        let subjectText = (periodData.subject_name || 'N/A');
                        // Truncate if too long
                        if (subjectText.length > 12) {
                            subjectText = subjectText.substring(0, 12) + '..';
                        }
                        const classText = `${periodData.class_name || ''}-${periodData.section_name || ''}`;
                        
                        // Center align the text
                        const subjectWidth = doc.getTextWidth(subjectText);
                        const classWidth = doc.getTextWidth(classText);
                        const subjectCenterX = x + (cellWidth - subjectWidth) / 2;
                        const classCenterX = x + (cellWidth - classWidth) / 2;
                        
                        doc.text(subjectText, subjectCenterX, y + 7);
                        doc.text(classText, classCenterX, y + 13);
                    } else {
                        doc.setFontSize(8);
                        const freeText = 'Free';
                        const freeTextWidth = doc.getTextWidth(freeText);
                        const freeCenterX = x + (cellWidth - freeTextWidth) / 2;
                        doc.text(freeText, freeCenterX, y + 11);
                    }
                });
            });
            
            // Add legend at a safe position
            const legendY = startY + headerHeight + (periods.length * cellHeight) + 15;
            doc.setFontSize(8);
            doc.text('Legend: Each cell shows Subject and Class-Section (P1-P8 = Period 1-8)', startX, legendY);
            
            // Add footer
            doc.setFontSize(7);
            const footerText = 'VES School Management System';
            const footerWidth = doc.getTextWidth(footerText);
            const footerCenterX = (pageWidth - footerWidth) / 2;
            doc.text(footerText, footerCenterX, legendY + 12);
            
            // Save the PDF
            doc.save(`${teacher.full_name || 'Teacher'}_Timetable_${new Date().toISOString().split('T')[0]}.pdf`);
        }
    </script>
</body>
</html>