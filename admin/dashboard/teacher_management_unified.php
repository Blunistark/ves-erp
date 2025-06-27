<?php

/**
 * Teacher Management System (Without Add Teacher Functionality)
 * Handles teacher management operations excluding adding new teachers
 * Role-based access control: Admin/Headmaster can manage assignments and reassignments
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

// Include database connection
require_once 'con.php';

// Get current user role for permission checks
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch required data for dropdowns
$classes_raw = executeQuery("SELECT id, name FROM classes ORDER BY name");
$sections_raw = executeQuery("SELECT id, name, class_id, class_teacher_user_id FROM sections ORDER BY class_id, name");
$subjects_raw = executeQuery("SELECT id, name, code FROM subjects ORDER BY name");
$teachers_raw = executeQuery("
    SELECT u.id, u.full_name, u.email, t.employee_number, u.status
    FROM users u 
    JOIN teachers t ON u.id = t.user_id 
    WHERE u.role IN ('teacher', 'headmaster')
    ORDER BY u.full_name
");

// Ensure data is array for json_encode to prevent JS errors
$classes = is_array($classes_raw) ? $classes_raw : [];
$sections = is_array($sections_raw) ? $sections_raw : [];
$subjects = is_array($subjects_raw) ? $subjects_raw : [];
$teachers = is_array($teachers_raw) ? $teachers_raw : [];

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management - Unified System</title>

    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/teacher_management_unified.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <i class="fas fa-bars hamburger-icon"></i>
    </button>

    <div class="unified-container">
        <!-- Notification Element -->
        <div id="notification" class="notification"></div>

        <!-- Header Section -->
        <div class="header-section">
            <h1 class="header-title">
                <i class="fas fa-users-cog"></i>
                Teacher Management System
            </h1>
            <p class="header-subtitle">
                Unified interface for all teacher-related operations
                <?php if ($user_role === 'headmaster'): ?>
                    (Assignment/Reassignment Only)
                <?php endif; ?>
            </p>
        </div> <!-- Main Content Tabs -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" data-tab="manage-teachers">
                    <i class="fas fa-users"></i>
                    Manage Teachers
                </button>
                <button class="tab-button active admin-only" data-tab="add-teacher">
                    <i class="fas fa-user-plus"></i>
                    Add Teacher
                </button>
                <button class="tab-button" data-tab="class-assignments">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Class Teacher Assignment
                </button>
                <button class="tab-button" data-tab="subject-assignments">
                    <i class="fas fa-book-open"></i>
                    Subject Assignments
                </button>
                <button class="tab-button" data-tab="timetable-management">
                    <i class="fas fa-calendar-alt"></i>
                    Timetable Management
                </button>
            </div>

            <!-- Tab Content: Add Teacher -->
            <div id="add-teacher" class="tab-content <?php echo ($user_role === 'admin') ? 'active' : ''; ?> admin-only">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Add New Teacher</h3>
                    </div>
                    <div class="card-body">
                        <form id="addTeacherForm">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="fullName" class="form-label required">Full Name</label>
                                    <input type="text" id="fullName" name="fullName" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="form-label required">Email</label>
                                    <input type="email" id="email" name="email" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="employeeNumber" class="form-label">Employee Number</label>
                                    <input type="text" id="employeeNumber" name="employeeNumber" class="form-input" readonly>
                                    <div class="help-text">Next available employee ID (e.g., VES2025T001)</div>
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label for="qualification" class="form-label">Qualification</label>
                                    <input type="text" id="qualification" name="qualification" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label for="dateOfBirth" class="form-label required">Date of Birth</label>
                                    <input type="date" id="dateOfBirth" name="dateOfBirth" class="form-input" required>
                                    <div class="help-text">Will be used to generate password (DDMMYYYY format)</div>
                                </div>
                                <div class="form-group">
                                    <label for="joinedDate" class="form-label required">Joined Date</label>
                                    <input type="date" id="joinedDate" name="joinedDate" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="role" class="form-label required">Role</label>
                                    <select id="role" name="role" class="form-select" required>
                                        <option value="">Select Role</option>
                                        <option value="teacher">Teacher</option>
                                        <option value="headmaster">Head Master</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status" class="form-label required">Status</label>
                                    <select id="status" name="status" class="form-select" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group full-width">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea id="address" name="address" class="form-textarea" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Add Teacher
                                </button>
                                <button type="reset" class="btn btn-outline">
                                    <i class="fas fa-undo"></i>
                                    Reset Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Manage Teachers -->
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
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
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

                    <!-- All Subject Assignments Display Section -->


                    <!-- Main Assignments Table -->
                    <div id="allAssignmentsContainer" class="card">
                        <div class="card-body" style="padding: 0;">

                            <!-- Loading State -->
                            <div id="allAssignmentsLoading" class="loading" style="display: none; padding: 40px;">
                                <div class="spinner"></div>
                                <p>Loading all subject assignments...</p>
                            </div>

                            <!-- Assignments Table -->
                            <div id="allAssignmentsTable" style="overflow-x: auto;">
                                <table class="data-table" style="margin: 0;">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 120px;">Employee ID</th>
                                            <th style="min-width: 180px;">Teacher Name</th>
                                            <th style="min-width: 150px;">Subject</th>
                                            <th style="min-width: 100px;">Subject Code</th>
                                            <th style="min-width: 120px;">Assignment Scope</th>
                                            <th style="min-width: 80px;">Status</th>
                                            <th style="min-width: 100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="allAssignmentsTableBody">
                                        <!-- Dynamic content will be loaded here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Empty State -->
                            <div id="allAssignmentsEmpty" class="empty-state" style="display: none; text-align: center; padding: 60px 20px;">
                                <i class="fas fa-users" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                                <h5 style="color: var(--text-primary); margin-bottom: 8px;">No Subject Assignments Found</h5>
                                <p class="help-text">No teachers have been assigned to subjects yet, or your search didn't match any assignments.</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: Timetable Management -->
        <div id="timetable-management" class="tab-content">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Timetable & Schedule Management</h3>
                </div>
                <div class="card-body">
                    <!-- Individual Teacher Schedule Editor -->
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; margin-top: 32px;">
                        <h4 class="section-title" style="font-size: 1.1rem; font-weight: 600; margin:0;">
                            <i class="fas fa-user-edit"></i>
                            Individual Teacher Schedule Editor
                        </h4>
                        <button class="btn btn-primary btn-sm" id="teacherScheduleEditorToggle">
                            <i class="fas fa-calendar-plus"></i>
                            Open Schedule Editor
                        </button>
                    </div>
                    <div id="teacherScheduleEditor" style="display: none;"> <!-- Teacher Selection -->
                        <div class="form-row" style="margin-bottom: 20px;">
                            <div class="form-group" style="margin-right: 20px;">
                                <label for="selectedTeacher" class="form-label">Select Teacher</label>
                                <select id="selectedTeacher" class="form-select" style="min-width: 250px;">
                                    <option value="">Choose a teacher...</option>
                                </select>
                            </div>
                            <div class="form-group" style="display: flex; align-items: end;">
                                <button class="btn btn-outline" id="loadTeacherSchedule">
                                    <i class="fas fa-download"></i>
                                    Load Complete Schedule
                                </button>
                            </div>
                        </div>

                        <!-- Teacher Schedule Grid -->
                        <div id="teacherScheduleGrid" style="display: none;">
                            <div class="schedule-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                <h5 id="currentTeacherName" style="margin: 0; color: #374151;">Teacher Schedule</h5>
                                <div class="schedule-actions">
                                    <button class="btn btn-sm btn-outline" id="scheduleConflictCheck">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Check Conflicts
                                    </button>
                                    <button class="btn btn-sm btn-success" id="saveScheduleChanges" style="display: none;">
                                        <i class="fas fa-save"></i>
                                        Save Changes
                                    </button>
                                </div>
                            </div>

                            <!-- Schedule Grid Table -->
                            <div class="schedule-grid-container">
                                <table class="schedule-grid-table" style="width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb;">
                                    <thead>
                                        <tr style="background-color: #f9fafb;">
                                            <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Time</th>
                                            <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Monday</th>
                                            <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Tuesday</th>
                                            <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Wednesday</th>
                                            <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Thursday</th>
                                            <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Friday</th>
                                            <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Saturday</th>
                                        </tr>
                                    </thead>
                                    <tbody id="scheduleGridBody">
                                        <!-- Schedule periods will be populated here -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Period Editor Form -->
                            <div id="periodEditorForm" style="display: none; margin-top: 20px; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; background-color: #f9fafc;">
                                <h6 style="margin-bottom: 16px; color: #374151;">Edit Period: <span id="editingPeriodInfo">Monday, Period 1</span></h6>
                                <div class="form-row" style="margin-bottom: 15px;">
                                    <div class="form-group" style="margin-right: 15px;">
                                        <label for="periodClass" class="form-label">Class</label>
                                        <select id="periodClass" class="form-select" style="min-width: 150px;">
                                            <option value="">Select class...</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin-right: 15px;">
                                        <label for="periodSection" class="form-label">Section</label>
                                        <select id="periodSection" class="form-select" style="min-width: 150px;">
                                            <option value="">Select section...</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin-right: 15px;">
                                        <label for="periodSubject" class="form-label">Subject</label>
                                        <select id="periodSubject" class="form-select" style="min-width: 200px;">
                                            <option value="">Select subject...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="margin-right: 15px;">
                                        <label for="periodNotes" class="form-label">Notes</label>
                                        <input type="text" id="periodNotes" class="form-input" placeholder="Optional notes" style="width: 300px;">
                                    </div>
                                    <div class="form-group" style="display: flex; align-items: end; gap: 10px;">
                                        <button class="btn btn-primary btn-sm" id="savePeriodChanges">
                                            <i class="fas fa-check"></i>
                                            Save
                                        </button>
                                        <button class="btn btn-outline btn-sm" id="clearPeriod">
                                            <i class="fas fa-trash"></i>
                                            Clear
                                        </button>
                                        <button class="btn btn-outline btn-sm" id="cancelPeriodEdit">
                                            <i class="fas fa-times"></i>
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="quick-actions" style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                                <button class="btn btn-outline btn-sm" id="viewAvailableSlots">
                                    <i class="fas fa-clock"></i>
                                    View Available Slots
                                </button>
                                <button class="btn btn-outline btn-sm" id="bulkAssignPeriods">
                                    <i class="fas fa-layer-group"></i>
                                    Bulk Assign
                                </button>
                                <button class="btn btn-outline btn-sm" id="exportTeacherSchedule">
                                    <i class="fas fa-download"></i>
                                    Export Schedule
                                </button>
                                <button class="btn btn-outline btn-sm" id="resetScheduleChanges">
                                    <i class="fas fa-undo"></i>
                                    Reset Changes
                                </button>
                            </div> <!-- Conflict Display -->
                            <div id="scheduleConflicts" style="display: none; margin-top: 20px;">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Schedule Conflicts Detected</h6>
                                    <ul id="conflictsList" style="margin-bottom: 0;">
                                        <!-- Conflicts will be listed here -->
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Teacher Schedule Placeholder -->
                        <div id="teacher-schedule-placeholder" style="text-align: center; padding: 40px; color: #6b7280;">
                            <i class="fas fa-calendar-alt" style="font-size: 48px; margin-bottom: 16px;"></i>
                            <h5 style="margin-bottom: 8px;">Select a teacher to view their schedule</h5>
                            <p class="help-text">Use the dropdown above to choose a teacher and load their schedule.</p>
                        </div>
                    </div>




                    <!-- Timetable Conflicts Section -->
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h4 class="section-title" style="font-size: 1.1rem; font-weight: 600; margin:0;">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            Schedule Conflicts
                        </h4>
                        <button class="btn btn-outline btn-sm" id="adminRefreshConflictsBtn">
                            <i class="fas fa-sync"></i>
                            Refresh Conflicts
                        </button>
                    </div>
                    <div id="adminTimetableConflicts" style="margin-bottom: 32px;">
                        <p class="help-text">Loading conflicts...</p>
                    </div>
                    <div id="adminTimetableConflictsLoading" class="loading" style="display:none;">
                        <div class="spinner"></div>
                        <p>Loading Conflicts...</p>
                    </div>

                    <!-- Teacher Timetable Overview -->
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; margin-top: 32px;">
                        <h4 class="section-title" style="font-size: 1.1rem; font-weight: 600; margin:0;">
                            <i class="fas fa-users"></i>
                            Teacher Schedule Overview
                        </h4>
                    </div>
                    <div id="adminTeacherTimetableOverview" style="margin-bottom: 32px;">
                        <p class="help-text">Loading teacher schedules...</p>
                    </div>
                    <div id="adminTeacherTimetableOverviewLoading" class="loading" style="display:none;">
                        <div class="spinner"></div>
                        <p>Loading Teacher Schedules...</p>
                    </div>

                </div>
            </div>
        </div>

        <!-- Edit Teacher Modal -->
        <div id="editTeacherModal" class="modal" style="display: none;">
            <div class="modal-content" style="max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto;">
                <div class="modal-header">
                    <h3>Edit Teacher</h3>
                    <span class="close" onclick="closeEditTeacherModal()">&times;</span>
                </div>
                <div class="modal-body">
                    <form id="editTeacherForm">
                        <input type="hidden" id="editTeacherId" name="teacher_id">

                        <!-- Basic Information -->
                        <div class="form-section">
                            <h4>Basic Information</h4>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="editTeacherName">Full Name:</label>
                                    <input type="text" id="editTeacherName" name="full_name" required readonly>
                                    <small class="form-note">Contact admin to change name</small>
                                </div>
                                <div class="form-group">
                                    <label for="editTeacherEmail">Email:</label>
                                    <input type="email" id="editTeacherEmail" name="email" required readonly>
                                    <small class="form-note">Contact admin to change email</small>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="editEmployeeNumber">Employee Number:</label>
                                    <input type="text" id="editEmployeeNumber" name="employee_number" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="editTeacherStatus">Status:</label>
                                    <select id="editTeacherStatus" name="status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Class Teacher Assignment -->
                        <div class="form-section">
                            <h4>Class Teacher Assignment</h4>
                            <div class="form-group">
                                <label for="editClassTeacherSection">Assign as Class Teacher:</label>
                                <select id="editClassTeacherSection" name="class_teacher_section">
                                    <option value="">Not a Class Teacher</option>
                                </select>
                                <small class="form-note">Select a section to assign this teacher as class teacher</small>
                            </div>
                            <div id="currentClassTeacherInfo" style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px; display: none;">
                                <strong>Currently Class Teacher of:</strong> <span id="currentClassTeacherText"></span>
                            </div>
                        </div>

                        <!-- Subject Assignments -->
                        <div class="form-section">
                            <h4>Subject Assignments</h4>
                            <div class="form-group">
                                <label>Assigned Subjects:</label>
                                <div id="subjectAssignmentContainer" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                    <!-- Subject assignments will be loaded here -->
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="button" class="btn btn-secondary" onclick="addSubjectAssignment()">
                                    <i class="fas fa-plus"></i> Add Subject Assignment
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditTeacherModal()">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveTeacherChanges()">Save Changes</button>
                </div>
            </div>
        </div>
        <!-- JavaScript -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            // Global variables
            const userRole = <?php echo json_encode($user_role); ?>;
            const sectionsData = <?php echo json_encode($sections, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            const classes = <?php echo json_encode($classes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            const sections = <?php echo json_encode($sections, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            const subjects = <?php echo json_encode($subjects, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
            let teachersData = [];
            let currentTab = null;


            function switchTab(tabId) {
                console.log(`Attempting to switch to tab: ${tabId}`);
                const $targetContent = $('#' + tabId);
                const $targetButton = $('.tab-button[data-tab="' + tabId + '"]');

                if ($targetContent.length === 0) {
                    console.error(`Tab content not found for ID: #${tabId}`);
                    showNotification(`Error: Content for tab ${tabId} not found.`, 'error');
                    // Do not proceed if content area doesn't exist
                    // Attempt to switch to a default valid tab if the current one is problematic
                    // This prevents getting stuck on a non-existent tab from localStorage
                    if (localStorage.getItem('activeTeacherManagementTab') === tabId) {
                        localStorage.removeItem('activeTeacherManagementTab');
                        // Try to re-initialize with default if this was an attempt to load a bad stored tab
                        console.warn(`Invalid tab ${tabId} was stored. Clearing and attempting to re-initialize.`);
                        // Find a sensible default (first non-admin or first overall)
                        const defaultFallbackTab = $('<?php echo $user_role; ?>' === 'admin' ? '.tab-button' : '.tab-button:not(.admin-only)').first().data('tab');
                        if (defaultFallbackTab && defaultFallbackTab !== tabId) { // Avoid recursion if the default is also bad
                            return switchTab(defaultFallbackTab); // Recursive call, ensure base case
                        }
                    }
                    return false;
                }
                // Role-based access check for tabs marked as 'admin-only'
                if ($targetButton.hasClass('admin-only') && userRole !== 'admin') {
                    console.warn(`Access denied: Tab "${tabId}" is admin-only. Current role: "${userRole}".`);
                    showNotification('Access denied to this section.', 'error');
                    // Fallback to a default accessible tab
                    const fallbackTab = $('<?php echo $user_role; ?>' === 'admin' ? '.tab-button' : '.tab-button:not(.admin-only)').first().data('tab');
                    if (fallbackTab && fallbackTab !== tabId) { // Avoid recursion
                        return switchTab(fallbackTab);
                    }
                    return false; // Could not find a fallback
                } // Hide all tab content and remove active class from buttons
                $('.tab-content').removeClass('active').hide();
                $('.tab-button').removeClass('active');

                // Show target content and add active classes
                $targetContent.addClass('active').show();
                $targetButton.addClass('active');

                // Store the successfully switched tabId
                try {
                    localStorage.setItem('activeTeacherManagementTab', tabId);
                } catch (e) {
                    console.warn("Could not save active tab to localStorage after switch:", e);
                }
                console.log(`Loading content for tab: ${tabId}`);
                try {
                    switch (tabId) {
                        case 'add-teacher':
                            if (typeof generateEmployeeNumber === 'function') {
                                const addTeacherForm = $('#addTeacherForm');
                                if (addTeacherForm.length) addTeacherForm[0].reset();
                                generateEmployeeNumber();
                            }
                            break;
                        case 'manage-teachers':
                            if (typeof loadTeachers === 'function') loadTeachers();
                            break;
                        case 'class-assignments':
                            if (typeof loadClassAssignments === 'function') loadClassAssignments();
                            if (typeof loadTeachersForDropdowns === 'function') loadTeachersForDropdowns('assignTeacher', false); // For assign class teacher
                            if (typeof loadClassesForDropdowns === 'function') loadClassesForDropdowns('assignClass', false); // For assign class
                            // Initial call to updateSections if a class might be pre-selected or to set initial state
                            if (typeof updateSections === 'function') updateSections($('#assignClass').val(), 'assignSection');
                            break;
                        case 'subject-assignments':
                            if (typeof loadTeachersForDropdowns === 'function') {
                                loadTeachersForDropdowns('subjectTeacher', false); // For individual assignment
                                loadTeachersForDropdowns('filterTeacher'); // For 'All Assignments' filter
                            }
                            if (typeof loadSubjectsForDropdowns === 'function') {
                                // loadSubjectsForDropdowns('assignSubjectSubject'); // This was for a direct subject dropdown, now using checkboxes
                                loadSubjectsForDropdowns('filterSubject'); // For 'All Assignments' filter
                            }
                            // Populate checkboxes when tab is switched, not just on teacher selection
                            if (typeof populateSubjectCheckboxes === 'function') {
                                // Pass null or undefined if no teacher is initially selected, or the ID of a default/selected teacher
                                populateSubjectCheckboxes($('#subjectTeacher').val() || null);
                            }
                            if (typeof loadAssignmentFilters === 'function') loadAssignmentFilters(); // This might be redundant if dropdowns are loaded directly
                            if (typeof loadAllSubjectAssignments === 'function') loadAllSubjectAssignments();
                            break;
                        case 'timetable-management':
                            if (typeof loadAdminTimetableManagementData === 'function') loadAdminTimetableManagementData();
                            break;
                        case 'bulk-operations':
                            if (typeof loadBulkOperationsData === 'function') loadBulkOperationsData();
                            if (typeof loadStatisticsForBulk === 'function') loadStatisticsForBulk();
                            break;
                        default:
                            console.warn('Unknown tab ID in switchTab:', tabId);
                            return false; // Unknown tab
                    }
                    console.log(`Successfully processed logic for tab: ${tabId}`);
                    return true; // Signal success
                } catch (error) {
                    console.error(`Error loading content for tab ${tabId}:`, error);
                    showNotification(`Error loading tab ${tabId}: ${error.message}`, 'error');
                    return false; // Signal failure
                }
            } // --- IMPLEMENTED FUNCTIONS ---

            /**
             * Load and display class teacher assignments
             */
            function loadClassAssignments() {
                console.log('Loading class assignments...');
                const container = $('#classAssignmentsTable');
                const loading = $('<div class="loading"><div class="spinner"></div><p>Loading assignments...</p></div>');

                container.html(loading);

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_class_assignments'
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Class assignments loaded:', response);

                        if (response.success && response.data) {
                            displayClassAssignments(response.data, container);
                        } else {
                            container.html('<p class="help-text">No class assignments found</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading class assignments:', error);
                        container.html('<p class="help-text text-danger">Error loading assignments</p>');
                        showNotification('Failed to load class assignments', 'error');
                    }
                });
            }

            /**
             * Display class assignments in a table
             */
            function displayClassAssignments(assignments, container) {
                if (!assignments || assignments.length === 0) {
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
                            <th>Status</th>
                            <th class="admin-only">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

                assignments.forEach(assignment => {
                    // Handle null/empty teacher values
                    const teacherName = assignment.teacher_name && assignment.teacher_name !== 'null' && assignment.teacher_name.trim() !== '' ?
                        assignment.teacher_name :
                        'Not Assigned';

                    const employeeNumber = assignment.employee_number && assignment.employee_number !== 'null' && assignment.employee_number.trim() !== '' ?
                        assignment.employee_number :
                        'N/A';

                    const teacherStatus = assignment.teacher_status && assignment.teacher_status !== 'null' && assignment.teacher_status.trim() !== '' ?
                        assignment.teacher_status :
                        'unassigned';

                    const statusText = teacherStatus === 'unassigned' ? 'Not Assigned' : teacherStatus;

                    // Determine if teacher is assigned for action buttons
                    const isAssigned = assignment.teacher_name && assignment.teacher_name !== 'null' && assignment.teacher_name.trim() !== '';

                    html += `
                    <tr>
                        <td><strong>${escapeHtml(assignment.class_name)}</strong></td>
                        <td><span class="section-badge">${escapeHtml(assignment.section_name)}</span></td>
                        <td>
                            <div class="teacher-info">
                                <span class="teacher-name ${!isAssigned ? 'text-muted' : ''}">${escapeHtml(teacherName)}</span>
                            </div>
                        </td>
                        <td><code class="${employeeNumber === 'N/A' ? 'text-muted' : ''}">${escapeHtml(employeeNumber)}</code></td>
                        <td><span class="status-badge status-${teacherStatus}">${statusText}</span></td>
                        <td class="actions admin-only">
                            ${isAssigned ? `
                                <button class="btn btn-sm btn-outline" onclick="reassignClassTeacher(${assignment.section_id})">
                                    <i class="fas fa-exchange-alt"></i> Reassign
                                </button>
                                <button class="btn btn-sm btn-secondary" onclick="removeClassTeacher(${assignment.section_id})">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            ` : `
                                <button class="btn btn-sm btn-primary" onclick="assignClassTeacher(${assignment.section_id})">
                                    <i class="fas fa-plus"></i> Assign Teacher
                                </button>
                            `}
                        </td>
                    </tr>
                `;
                });

                html += '</tbody></table>';
                container.html(html);
            }

            /**
             * Load admin timetable management data
             */
            function loadAdminTimetableManagementData() {
                console.log('Loading admin timetable management data...');

                // Load conflicts
                loadTimetableConflicts();

                // Load teacher schedules overview
                loadTeacherSchedulesOverview();

                // Load class timetable status
                loadClassTimetableStatus();

                // Load teacher dropdown for schedule editor
                loadTeachersForDropdowns('selectedTeacher', false);
            }

            /**
             * Load timetable conflicts
             */
            function loadTimetableConflicts() {
                const container = $('#adminTimetableConflicts');
                const loading = $('#adminTimetableConflictsLoading');

                container.hide();
                loading.show();

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_timetable_conflicts'
                    },
                    dataType: 'json',
                    success: function(response) {
                        loading.hide();
                        container.show();

                        if (response.success && response.data && response.data.length > 0) {
                            displayTimetableConflicts(response.data, container);
                        } else {
                            container.html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> No timetable conflicts detected</div>');
                        }
                    },
                    error: function() {
                        loading.hide();
                        container.show().html('<div class="alert alert-error">Error loading conflicts</div>');
                    }
                });
            }

            /**
             * Display timetable conflicts
             */
            function displayTimetableConflicts(conflicts, container) {
                let html = '<div class="alert alert-warning"><h6><i class="fas fa-exclamation-triangle"></i> Conflicts Detected</h6><ul>';

                conflicts.forEach(conflict => {
                    html += `<li>${conflict.message} - ${conflict.details || ''}</li>`;
                });

                html += '</ul></div>';
                container.html(html);
            }

            /**
             * Load teacher schedules overview
             */
            function loadTeacherSchedulesOverview() {
                const container = $('#adminTeacherTimetableOverview');
                const loading = $('#adminTeacherTimetableOverviewLoading');

                container.hide();
                loading.show();

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_teacher_schedules'
                    },
                    dataType: 'json',
                    success: function(response) {
                        loading.hide();
                        container.show();

                        if (response.success && response.data) {
                            displayTeacherSchedulesOverview(response.data, container);
                        } else {
                            container.html('<p class="help-text">No teacher schedules found</p>');
                        }
                    },
                    error: function() {
                        loading.hide();
                        container.show().html('<p class="help-text text-danger">Error loading teacher schedules</p>');
                    }
                });
            }

            /**
             * Display teacher schedules overview
             */
            function displayTeacherSchedulesOverview(schedules, container) {
                let html = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Employee ID</th>
                            <th>Schedule Summary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

                schedules.forEach(schedule => {
                    const scheduleCount = schedule.schedule ? schedule.schedule.length : 0;
                    html += `
                    <tr>
                        <td>${escapeHtml(schedule.teacher_name)}</td>
                        <td><code>${escapeHtml(schedule.employee_number)}</code></td>
                        <td>${scheduleCount} periods assigned</td>
                        <td>
                            <button class="btn btn-sm btn-outline" onclick="viewTeacherSchedule(${schedule.teacher_id})">
                                <i class="fas fa-calendar-alt"></i> View Schedule
                            </button>
                        </td>
                    </tr>
                `;
                });

                html += '</tbody></table>';
                container.html(html);
            }

            /**
             * Load class timetable status
             */
            function loadClassTimetableStatus() {
                const container = $('#adminClassTimetableStatus');
                const loading = $('#adminClassTimetableStatusLoading');

                container.hide();
                loading.show();

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_class_timetable_status'
                    },
                    dataType: 'json',
                    success: function(response) {
                        loading.hide();
                        container.show();

                        if (response.success && response.data) {
                            displayClassTimetableStatus(response.data, container);
                        } else {
                            container.html('<p class="help-text">No class timetable data found</p>');
                        }
                    },
                    error: function() {
                        loading.hide();
                        container.show().html('<p class="help-text text-danger">Error loading class timetable status</p>');
                    }
                });
            }

            /**
             * Display class timetable status
             */
            function displayClassTimetableStatus(classes, container) {
                let html = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Completion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

                classes.forEach(cls => {
                    const statusClass = cls.status === 'published' ? 'status-active' : 'status-inactive';
                    const completionPercent = cls.completion_percentage || 0;

                    html += `
                    <tr>
                        <td><strong>${escapeHtml(cls.class_name)}</strong></td>
                        <td><span class="section-badge">${escapeHtml(cls.section_name)}</span></td>
                        <td><span class="status-badge ${statusClass}">${cls.status || 'Draft'}</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; background: #f0f0f0; border-radius: 4px; height: 6px;">
                                    <div style="background: var(--primary-color); height: 100%; border-radius: 4px; width: ${completionPercent}%;"></div>
                                </div>
                                <span style="font-size: 0.875rem;">${completionPercent}%</span>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline" onclick="editClassTimetable(${cls.class_id}, ${cls.section_id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </td>
                    </tr>
                `;
                });

                html += '</tbody></table>';
                container.html(html);
            }

            /**
             * Load bulk operations data
             */
            function loadBulkOperationsData() {
                console.log('Loading bulk operations data...');
                loadStatisticsForBulk();
            }

            /**
             * Load statistics for bulk operations
             */
            function loadStatisticsForBulk() {
                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_statistics'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            displayBulkStatistics(response.data);
                        }
                    },
                    error: function() {
                        console.error('Error loading bulk statistics');
                    }
                });
            }

            /**
             * Display bulk statistics
             */
            function displayBulkStatistics(stats) {
                const container = $('#statisticsContent');
                const html = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <div class="stat-item">
                        <div class="stat-label">Total Teachers</div>
                        <div class="stat-value">${stats.total_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Active Teachers</div>
                        <div class="stat-value">${stats.active_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assigned Teachers</div>
                        <div class="stat-value">${stats.assigned_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Unassigned Teachers</div>
                        <div class="stat-value">${stats.unassigned_teachers}</div>
                    </div>
                </div>
            `;
                container.html(html);
            }

            /**
             * Handle add teacher form submission
             */
            function handleAddTeacher(e) {
                e.preventDefault();

                // Get date of birth and format as DDMMYYYY for password
                const dobValue = $('#dateOfBirth').val();
                let password = '';

                if (dobValue) {
                    // Convert YYYY-MM-DD to DDMMYYYY
                    const dateParts = dobValue.split('-');
                    if (dateParts.length === 3) {
                        password = dateParts[2] + dateParts[1] + dateParts[0]; // DD + MM + YYYY
                    }
                }

                if (!password) {
                    showNotification('Please provide a valid date of birth', 'error');
                    return;
                }

                const formData = {
                    action: 'add_teacher',
                    full_name: $('#fullName').val(),
                    email: $('#email').val(),
                    phone: $('#phone').val(),
                    qualification: $('#qualification').val(),
                    experience_years: $('#experienceYears').val() || 0,
                    date_of_birth: $('#dateOfBirth').val(),
                    gender: $('#gender').val(),
                    joined_date: $('#joinedDate').val(),
                    role: $('#role').val(),
                    status: $('#status').val(),
                    address: $('#address').val(),
                    password: password
                };

                // Show loading state
                const submitBtn = $('#addTeacherForm button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding Teacher...');

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Teacher added successfully! Employee ID: ' + response.data.employee_number, 'success');
                            $('#addTeacherForm')[0].reset();
                            generateEmployeeNumber(); // Generate new employee number for next teacher

                            // Refresh teacher list if on manage teachers tab
                            if (typeof loadTeachers === 'function') {
                                loadTeachers();
                            }
                        } else {
                            showNotification(response.message || 'Failed to add teacher', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error adding teacher:', error);
                        let errorMessage = 'Error adding teacher';

                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                        } catch (e) {
                            // Use default error message
                        }

                        showNotification(errorMessage, 'error');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            }

            /**
             * Load and display the next employee number
             */
            function generateEmployeeNumber() {
                const employeeInput = $('#employeeNumber');

                if (employeeInput.length === 0) {
                    console.warn('Employee number input field not found');
                    return;
                }

                // Show loading state
                employeeInput.val('Loading...');

                // Fetch next employee number from server
                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_next_employee_number'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            employeeInput.val(response.data.next_employee_number);
                            console.log('Next employee number:', response.data.next_employee_number);
                        } else {
                            employeeInput.val('Error loading');
                            console.error('Failed to get next employee number:', response.message);
                            showNotification('Failed to load employee number', 'warning');
                        }
                    },
                    error: function(xhr, status, error) {
                        employeeInput.val('Error loading');
                        console.error('Error fetching next employee number:', error);
                        showNotification('Error loading employee number', 'error');
                    }
                });
            }


            /**
             * Initialize form handlers
             */
            function initializeFormHandlers() {
                console.log('Initializing form handlers...');

                initializeAddTeacherForm();

                // Class assignment form
                $('#assignClass').on('change', function() {
                    updateSections($(this).val(), 'assignSection');
                });
                $('#assignClassTeacher').on('click', handleClassTeacherAssignment);

                // Subject assignment form  
                $('#subjectTeacher').on('change', handleTeacherSubjectSelection);
                $('#updateSubjectAssignments').on('click', handleSubjectAssignmentUpdate);

                // Refresh buttons
                $('#adminRefreshConflictsBtn').on('click', loadTimetableConflicts);
                $('#refreshAllAssignments').on('click', loadAllSubjectAssignments);

                // Teacher schedule editor
                $('#teacherScheduleEditorToggle').on('click', toggleScheduleEditor);
                $('#loadTeacherSchedule').on('click', loadSelectedTeacherSchedule);


                console.log('Form handlers initialized');
            }

            /**
             * Filter all subject assignments table based on selected filters
             */
            function filterAllAssignments() {
                const teacherId = $('#filterTeacher').val();
                const subjectId = $('#filterSubject').val();
                const searchInput = $('#assignmentSearchInput').val().toLowerCase();

                $('#allAssignmentsTableBody tr').each(function() {
                    const $row = $(this);
                    let show = true;

                    if (teacherId && !$row.text().toLowerCase().includes($('#filterTeacher option:selected').text().toLowerCase())) {
                        show = false;
                    }
                    if (subjectId && !$row.text().toLowerCase().includes($('#filterSubject option:selected').text().toLowerCase())) {
                        show = false;
                    }
                    if (searchInput && !$row.text().toLowerCase().includes(searchInput)) {
                        show = false;
                    }

                    $row.toggle(show);
                });
            }

            function searchAllAssignments() {
                const searchInput = $('#assignmentSearchInput').val().toLowerCase();

                $('#allAssignmentsTableBody tr').each(function() {
                    const $row = $(this);
                    const rowText = $row.text().toLowerCase();
                    $row.toggle(rowText.includes(searchInput));
                });
            }

            /**
             * Clear all filters for the subject assignments table
             */
            function clearAllAssignmentFilters() {
                // Reset the filter dropdowns to their default values
                $('#filterTeacher').val('');
                $('#filterSubject').val('');

                // Clear the search input
                $('#assignmentSearchInput').val('');

                // Show all rows in the table
                $('#allAssignmentsTableBody tr').show();
            }

            /**
             * Initialize search handlers
             */
            function initializeSearchHandlers() {
                console.log('Initializing search handlers...');

                // Teacher search
                $('#teacherSearch').on('input', debounce(searchTeachers, 300));

                // Assignment filters
                $('#filterTeacher, #filterSubject').on('change', filterAllAssignments);
                $('#assignmentSearchInput').on('input', debounce(searchAllAssignments, 300));
                $('#clearAllFilters').on('click', clearAllAssignmentFilters);

                console.log('Search handlers initialized');
            }
            /**
             * Handle class teacher assignment
             */
            function handleClassTeacherAssignment() {
                const classId = $('#assignClass').val();
                const sectionId = $('#assignSection').val();
                const teacherId = $('#assignTeacher').val();

                if (!classId || !sectionId || !teacherId) {
                    showNotification('Please select class, section, and teacher', 'error');
                    return;
                }

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'POST',
                    data: {
                        action: 'assign_class_teacher',
                        section_id: sectionId,
                        teacher_id: teacherId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Class teacher assigned successfully!', 'success');
                            $('#assignClass, #assignSection, #assignTeacher').val('');
                            $('#assignSection').prop('disabled', true);
                            loadClassAssignments(); // Refresh assignments
                        } else {
                            showNotification(response.message || 'Failed to assign teacher', 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error assigning class teacher', 'error');
                    }
                });
            }

            /**
             * Handle teacher selection for subject assignments
             */
            function handleTeacherSubjectSelection() {
                const teacherId = $(this).val();

                if (!teacherId) {
                    $('#subjectAssignmentsDisplay').html('<p class="help-text">Select a teacher to view their subject assignments</p>');
                    return;
                }

                loadTeacherSubjects(teacherId);
            }

            /**
             * Load teacher subjects for assignment
             */
            function loadTeacherSubjects(teacherId) {
                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_teacher_subjects',
                        teacher_id: teacherId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            displayTeacherSubjects(response.data, teacherId);
                        } else {
                            showNotification('Error loading teacher subjects', 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error loading teacher subjects', 'error');
                    }
                });
            }

            /**
             * Display teacher subjects with checkboxes
             */
            function displayTeacherSubjects(assignedSubjects, teacherId) {
                let html = '<div class="subject-checkboxes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin: 16px 0;">';

                // Get all subjects and mark assigned ones
                const allSubjects = <?php echo json_encode($subjects); ?>;

                allSubjects.forEach(subject => {
                    const isAssigned = assignedSubjects.includes(subject.id);
                    html += `
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                        <input type="checkbox" name="subjects[]" value="${subject.id}" ${isAssigned ? 'checked' : ''}>
                        <span>${escapeHtml(subject.name)} (${escapeHtml(subject.code)})</span>
                    </label>
                `;
                });

                html += '</div>';
                html += `
                <div class="actions" style="margin-top: 16px;">
                    <button class="btn btn-primary" id="updateSubjectAssignments">
                        <i class="fas fa-save"></i> Update Assignments
                    </button>
                    <button class="btn btn-outline" onclick="clearSubjectSelections()">
                        <i class="fas fa-times"></i> Clear All
                    </button>
                </div>
            `;

                $('#subjectAssignmentsDisplay').html(html);

                // Re-bind the update button
                $('#updateSubjectAssignments').off('click').on('click', function() {
                    handleSubjectAssignmentUpdate(teacherId);
                });
            }

            /**
             * Handle subject assignment update
             */
            function handleSubjectAssignmentUpdate(teacherId) {
                const selectedSubjects = [];
                $('input[name="subjects[]"]:checked').each(function() {
                    selectedSubjects.push($(this).val());
                });

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'POST',
                    data: {
                        action: 'update_subject_assignments',
                        teacher_id: teacherId,
                        subject_ids: JSON.stringify(selectedSubjects)
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Subject assignments updated successfully!', 'success');
                            loadAllSubjectAssignments(); // Refresh all assignments view
                        } else {
                            showNotification(response.message || 'Failed to update assignments', 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error updating subject assignments', 'error');
                    }
                });
            }

            /**
             * Clear subject selections
             */
            function clearSubjectSelections() {
                $('input[name="subjects[]"]').prop('checked', false);
            }

            /**
             * Load all subject assignments
             */
            function loadAllSubjectAssignments() {
                const container = $('#allAssignmentsTable');
                const loading = $('#allAssignmentsLoading');
                const empty = $('#allAssignmentsEmpty');

                container.hide();
                empty.hide();
                loading.show();

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_all_subject_assignments'
                    },
                    dataType: 'json',
                    success: function(response) {
                        loading.hide();

                        if (response.success && response.data && response.data.assignments && response.data.assignments.length > 0) {
                            displayAllSubjectAssignments(response.data.assignments);
                            displayAssignmentStatistics(response.data.statistics);
                            container.show();
                        } else {
                            empty.show();
                        }
                    },
                    error: function() {
                        loading.hide();
                        container.show();
                        $('#allAssignmentsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading assignments</td></tr>');
                    }
                });
            }

            /**
             * Display all subject assignments
             */
            function displayAllSubjectAssignments(assignments) {
                const tbody = $('#allAssignmentsTableBody');
                tbody.empty();

                assignments.forEach(teacher => {
                    const teacherInfo = teacher.teacher_info;
                    const teacherAssignments = teacher.assignments;

                    if (teacherAssignments.length === 0) {
                        // Show teacher with no assignments
                        tbody.append(`
                        <tr>
                            <td><code>${escapeHtml(teacherInfo.employee_number)}</code></td>
                            <td>${escapeHtml(teacherInfo.name)}</td>
                            <td colspan="3"><em>No subject assignments</em></td>
                            <td><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>
                            <td>-</td>
                        </tr>
                    `);
                    } else {
                        // Show each assignment
                        teacherAssignments.forEach((assignment, index) => {
                            tbody.append(`
                            <tr>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><code>${escapeHtml(teacherInfo.employee_number)}</code></td>` : ''}
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}">${escapeHtml(teacherInfo.name)}</td>` : ''}
                                <td>${escapeHtml(assignment.subject_name)}</td>
                                <td><code>${escapeHtml(assignment.subject_code)}</code></td>
                                <td><span class="assignment-scope-badge ${assignment.assignment_scope}">${assignment.assignment_scope}</span></td>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>` : ''}
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="removeSubjectAssignment(${index},${assignment.assignment_id})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                        });
                    }
                });
            }

            /**
             * Display assignment statistics
             */
            function displayAssignmentStatistics(stats) {
                const container = $('#assignmentStatistics');
                const html = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <div class="stat-item">
                        <div class="stat-label">Total Teachers</div>
                        <div class="stat-value">${stats.total_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Active Teachers</div>
                        <div class="stat-value">${stats.active_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assigned Teachers</div>
                        <div class="stat-value">${stats.assigned_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Unassigned Teachers</div>
                        <div class="stat-value">${stats.unassigned_teachers}</div>
                    </div>
                </div>
            `;
                container.html(html);
            }

            // --- INDIVIDUAL TEACHER SCHEDULE MANAGEMENT ---

            // Global variables for teacher schedule management
            let selectedTeacherId = null;
            let currentTeacherSchedule = [];
            let currentEditingCell = null;
            let scheduleChanges = [];
            let periodTimeSlots = [{
                    period: 1,
                    start: '08:00',
                    end: '08:45'
                },
                {
                    period: 2,
                    start: '08:50',
                    end: '09:35'
                },
                {
                    period: 3,
                    start: '09:40',
                    end: '10:25'
                },
                {
                    period: 4,
                    start: '10:40',
                    end: '11:25'
                },
                {
                    period: 5,
                    start: '11:30',
                    end: '12:15'
                },
                {
                    period: 6,
                    start: '12:20',
                    end: '13:05'
                },
                {
                    period: 7,
                    start: '13:45',
                    end: '14:30'
                },
                {
                    period: 8,
                    start: '14:35',
                    end: '15:20'
                }
            ];

            // Initialize teacher schedule editor
            function initializeTeacherScheduleEditor() {
                console.log('Initializing teacher schedule editor...');

                // Load teachers into dropdown
                loadTeachersForSchedule();

                // Event listeners - make sure to unbind first to avoid duplicates
                $('#teacherScheduleEditorToggle').off('click').on('click', toggleScheduleEditor);
                $('#selectedTeacher').off('change').on('change', handleTeacherSelection);
                $('#loadTeacherSchedule').off('click').on('click', loadSelectedTeacherSchedule);
                $('#loadTeacherSchedule').off('click').on('click', loadSelectedTeacherScheduleWithDebug);
                $('#scheduleConflictCheck').off('click').on('click', checkScheduleConflicts);
                $('#saveScheduleChanges').off('click').on('click', saveAllScheduleChanges);
                $('#savePeriodChanges').off('click').on('click', savePeriodEdit);
                $('#clearPeriod').off('click').on('click', clearCurrentPeriod);
                $('#cancelPeriodEdit').off('click').on('click', cancelPeriodEdit);
                $('#viewAvailableSlots').off('click').on('click', showAvailableSlots);
                $('#bulkAssignPeriods').off('click').on('click', openBulkAssignModal);
                $('#exportTeacherSchedule').off('click').on('click', exportTeacherSchedule);
                $('#resetScheduleChanges').off('click').on('click', resetScheduleChanges);

                // Class selection change handler
                $('#periodClass').off('change').on('change', handlePeriodClassChange);

                console.log('Teacher schedule editor initialized successfully');
            }

            // Toggle schedule editor visibility
            function toggleScheduleEditor() {
                const $editor = $('#teacherScheduleEditor');
                const $toggle = $('#teacherScheduleEditorToggle');

                if ($editor.is(':visible')) {
                    $editor.hide();
                    $toggle.html('<i class="fas fa-calendar-plus"></i> Open Schedule Editor');
                } else {
                    $editor.show();
                    $toggle.html('<i class="fas fa-calendar-minus"></i> Close Schedule Editor');
                }
            }

            // Load teachers for schedule dropdown
            function loadTeachersForSchedule() {
                console.log('Loading teachers for schedule editor...');

                fetch('teacher_management_api.php?action=get_teachers')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const $dropdown = $('#selectedTeacher');
                            $dropdown.empty().append('<option value="">Choose a teacher...</option>');

                            data.teachers.forEach(teacher => {
                                $dropdown.append(`<option value="${teacher.id}">${teacher.first_name} ${teacher.last_name} - ${teacher.employee_number}</option>`);
                            });

                            console.log(`Loaded ${data.teachers.length} teachers for schedule editor`);
                        } else {
                            showNotification('Failed to load teachers: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading teachers:', error);
                        showNotification('Error loading teachers', 'error');
                    });
            }

            // Handle teacher selection
            // REPLACE the existing handleTeacherSelection function with this:
            function handleTeacherSelection() {
                selectedTeacherId = $('#selectedTeacher').val();
                const teacherName = $('#selectedTeacher option:selected').text();

                if (selectedTeacherId) {
                    $('#currentTeacherName').text(`${teacherName} Schedule`);
                    $('#teacher-schedule-placeholder').hide();
                    $('#loadTeacherSchedule').prop('disabled', false);
                    console.log(`Selected teacher: ${teacherName} (ID: ${selectedTeacherId})`);

                    // Clear any existing schedule display
                    $('#teacherScheduleGrid').hide();
                    $('#scheduleGridBody').empty();

                } else {
                    $('#teacherScheduleGrid').hide();
                    $('#teacher-schedule-placeholder').show();
                    $('#loadTeacherSchedule').prop('disabled', true);
                    selectedTeacherId = null;
                }
            }
            // Load selected teacher's complete schedule
            function loadSelectedTeacherSchedule() {
                if (!selectedTeacherId) {
                    showNotification('Please select a teacher first', 'warning');
                    return;
                }

                console.log(`Loading complete schedule for teacher ID: ${selectedTeacherId}`);
                showNotification('Loading teacher schedule...', 'info');

                // Show loading state
                $('#teacherScheduleGrid').hide();
                const loadingHtml = '<div class="loading" style="text-align: center; padding: 40px;"><div class="spinner"></div><p>Loading schedule...</p></div>';
                $('#scheduleGridBody').html(loadingHtml);

                fetch(`teacher_management_api.php?action=get_teacher_schedule&teacher_id=${selectedTeacherId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Schedule API response:', data);

                        if (data.success) {
                            // Convert structured schedule to flat array for compatibility
                            currentTeacherSchedule = [];
                            const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

                            if (data.schedule) {
                                days.forEach(day => {
                                    if (data.schedule[day] && data.schedule[day].length > 0) {
                                        data.schedule[day].forEach(period => {
                                            currentTeacherSchedule.push({
                                                day_of_week: day,
                                                period_number: period.period,
                                                period_id: period.period_id,
                                                subject_name: period.subject_name,
                                                subject_code: period.subject_code,
                                                subject_id: period.subject_id,
                                                class_name: period.class_info ? period.class_info.split(' - ')[0] : '',
                                                section_name: period.class_info ? period.class_info.split(' - ')[1] : '',
                                                class_id: period.class_id,
                                                section_id: period.section_id,
                                                notes: period.notes,
                                                start_time: period.start_time,
                                                end_time: period.end_time,
                                                has_conflict: period.has_conflict
                                            });
                                        });
                                    }
                                });
                            }

                            renderTeacherScheduleGrid();
                            $('#teacherScheduleGrid').show();
                            $('#teacher-schedule-placeholder').hide();

                            const totalPeriods = data.total_periods || currentTeacherSchedule.length;
                            showNotification(`Loaded schedule with ${totalPeriods} periods`, 'success');

                            // Update teacher name in grid header
                            if (data.teacher_info) {
                                $('#currentTeacherName').text(`${data.teacher_info.full_name} - Schedule`);
                            }
                        } else {
                            showNotification('Failed to load teacher schedule: ' + (data.message || 'Unknown error'), 'error');
                            $('#scheduleGridBody').html('<tr><td colspan="7" class="text-center">Failed to load schedule</td></tr>');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading teacher schedule:', error);
                        showNotification('Error loading teacher schedule', 'error');
                        $('#scheduleGridBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading schedule</td></tr>');
                    });
            }

            // Render the teacher schedule grid
            function renderTeacherScheduleGrid() {
                console.log('Rendering teacher schedule grid...');
                const $tbody = $('#scheduleGridBody');
                $tbody.empty();

                const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

                // Create grid rows for each period
                periodTimeSlots.forEach(timeSlot => {
                    const row = $('<tr>');

                    // Time column
                    row.append(`<td style="border: 1px solid #e5e7eb; padding: 8px; background-color: #f9fafb; font-weight: 500; text-align: center; min-width: 120px;">
                    ${timeSlot.start} - ${timeSlot.end}<br><small>Period ${timeSlot.period}</small>
                </td>`);

                    // Day columns
                    days.forEach(day => {
                        const period = findPeriodForDayAndTime(day, timeSlot.period);
                        const cellId = `cell_${day}_${timeSlot.period}`;

                        const cell = $(`<td id="${cellId}" 
                        style="border: 1px solid #e5e7eb; padding: 8px; min-height: 60px; cursor: pointer; text-align: center; vertical-align: middle; min-width: 120px;"
                        data-day="${day}" 
                        data-period="${timeSlot.period}"
                        onclick="openPeriodEditor('${day}', ${timeSlot.period})">
                    </td>`);

                        if (period) {
                            cell.html(`
                            <div class="schedule-period" style="background-color: #e0f2fe; border-radius: 4px; padding: 6px; font-size: 12px; line-height: 1.3;">
                                <div style="font-weight: 600; color: #01579b;">${period.class_name || 'N/A'} - ${period.section_name || 'N/A'}</div>
                                <div style="color: #0277bd; margin-top: 2px;">${period.subject_name || 'N/A'}</div>
                                ${period.notes ? `<div style="color: #455a64; font-style: italic; margin-top: 2px; font-size: 10px;">${period.notes}</div>` : ''}
                            </div>
                        `);
                            cell.addClass('period-cell-filled');
                        } else {
                            cell.html(`
                            <div style="color: #9ca3af; font-size: 12px; padding: 10px;">
                                <i class="fas fa-plus-circle"></i><br>
                                <small>Click to add</small>
                            </div>
                        `);
                            cell.addClass('period-cell-empty');
                        }

                        row.append(cell);
                    });

                    $tbody.append(row);

                    // Add break rows after specific periods
                    if (timeSlot.period === 3) {
                        addBreakRow($tbody, 'Morning Break', '10:25 - 10:40');
                    } else if (timeSlot.period === 6) {
                        addBreakRow($tbody, 'Lunch Break', '13:05 - 13:45');
                    }
                });

                console.log('Teacher schedule grid rendered successfully');
            }

            // Helper function to find period for specific day and time
            function findPeriodForDayAndTime(day, period) {
                return currentTeacherSchedule.find(p =>
                    p.day_of_week === day && p.period_number === period
                );
            }

            // Add break row to schedule grid
            function addBreakRow($tbody, breakName, breakTime) {
                const breakRow = $('<tr>');
                breakRow.append(`<td style="border: 1px solid #e5e7eb; padding: 8px; background-color: #f3f4f6; font-weight: 500; text-align: center;">
                ${breakTime}<br><small>${breakName}</small>
            </td>`);
                breakRow.append(`<td colspan="6" style="border: 1px solid #e5e7eb; padding: 8px; background-color: #f3f4f6; text-align: center; font-weight: 500; color: #6b7280;">
                ${breakName}
            </td>`);

                $tbody.append(breakRow);
            }

            // Open period editor for specific day and period
            function openPeriodEditor(day, period) {
                if (!selectedTeacherId) {
                    showNotification('Please select a teacher first', 'warning');
                    return;
                }

                console.log(`Opening period editor for ${day}, period ${period}`);
                currentEditingCell = {
                    day,
                    period
                };

                // Update the editing info
                const dayName = day.charAt(0).toUpperCase() + day.slice(1);
                $('#editingPeriodInfo').text(`${dayName}, Period ${period}`);

                // Load current period data if exists
                const existingPeriod = findPeriodForDayAndTime(day, period);

                if (existingPeriod) {
                    $('#periodClass').val(existingPeriod.class_id);
                    handlePeriodClassChange().then(() => {
                        $('#periodSection').val(existingPeriod.section_id);
                        $('#periodSubject').val(existingPeriod.subject_id);
                        $('#periodNotes').val(existingPeriod.notes || '');
                    });
                } else {
                    // Clear form
                    $('#periodClass').val('');
                    $('#periodSection').val('').prop('disabled', true);
                    $('#periodSubject').val('').prop('disabled', true);
                    $('#periodNotes').val('');
                }

                // Show period editor
                $('#periodEditorForm').show();
                loadClassesForPeriodEditor();
            }

            // Load classes for period editor
            function loadClassesForPeriodEditor() {
                fetch('teacher_management_api.php?action=get_classes')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const $classSelect = $('#periodClass');
                            $classSelect.empty().append('<option value="">Select class...</option>');

                            data.classes.forEach(cls => {
                                $classSelect.append(`<option value="${cls.id}">${cls.name}</option>`);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error loading classes:', error);
                        showNotification('Error loading classes', 'error');
                    });
            }

            // Handle period class change
            function handlePeriodClassChange() {
                const classId = $('#periodClass').val();
                const $sectionSelect = $('#periodSection');
                const $subjectSelect = $('#periodSubject');

                if (!classId) {
                    $sectionSelect.val('').prop('disabled', true);
                    $subjectSelect.val('').prop('disabled', true);
                    return Promise.resolve();
                }

                // Load sections for selected class
                return fetch(`teacher_management_api.php?action=get_sections&class_id=${classId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $sectionSelect.empty().append('<option value="">Select section...</option>');

                            data.sections.forEach(section => {
                                $sectionSelect.append(`<option value="${section.id}">${section.name}</option>`);
                            });

                            $sectionSelect.prop('disabled', false);

                            // Load subjects
                            return loadSubjectsForPeriodEditor();
                        }
                    });
            }

            // Load subjects for period editor
            function loadSubjectsForPeriodEditor() {
                return fetch('teacher_management_api.php?action=get_subjects')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const $subjectSelect = $('#periodSubject');
                            $subjectSelect.empty().append('<option value="">Select subject...</option>');

                            data.subjects.forEach(subject => {
                                $subjectSelect.append(`<option value="${subject.id}">${subject.name}</option>`);
                            });

                            $subjectSelect.prop('disabled', false);
                        }
                    });
            }

            // Save period edit
            function savePeriodEdit() {
                if (!currentEditingCell || !selectedTeacherId) {
                    showNotification('Invalid editing state', 'error');
                    return;
                }

                const classId = $('#periodClass').val();
                const sectionId = $('#periodSection').val();
                const subjectId = $('#periodSubject').val();
                const notes = $('#periodNotes').val();

                if (!classId || !sectionId || !subjectId) {
                    showNotification('Please select class, section, and subject', 'warning');
                    return;
                }

                const {
                    day,
                    period
                } = currentEditingCell;
                const timeSlot = periodTimeSlots.find(t => t.period === period);

                const periodData = {
                    teacher_id: selectedTeacherId,
                    day_of_week: day,
                    period_number: period,
                    class_id: classId,
                    section_id: sectionId,
                    subject_id: subjectId,
                    start_time: timeSlot.start + ':00',
                    end_time: timeSlot.end + ':00',
                    notes: notes || null
                };

                console.log('Saving period data:', periodData);

                fetch('teacher_management_api.php?action=save_teacher_period', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(periodData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Period saved successfully', 'success');

                            // Update local schedule data
                            updateLocalScheduleData(periodData);

                            // Re-render the grid
                            renderTeacherScheduleGrid();

                            // Hide editor
                            cancelPeriodEdit();
                        } else {
                            showNotification('Failed to save period: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error saving period:', error);
                        showNotification('Error saving period', 'error');
                    });
            }

            // Update local schedule data
            function updateLocalScheduleData(periodData) {
                // Remove existing period for this day/time
                currentTeacherSchedule = currentTeacherSchedule.filter(p =>
                    !(p.day_of_week === periodData.day_of_week && p.period_number === periodData.period_number)
                );

                // Add new period data
                currentTeacherSchedule.push(periodData);
            }

            // Clear current period
            function clearCurrentPeriod() {
                if (!currentEditingCell || !selectedTeacherId) {
                    showNotification('Invalid editing state', 'error');
                    return;
                }

                const {
                    day,
                    period
                } = currentEditingCell;

                if (confirm(`Are you sure you want to clear the period for ${day.charAt(0).toUpperCase() + day.slice(1)}, Period ${period}?`)) {
                    fetch('teacher_management_api.php?action=delete_teacher_period', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                teacher_id: selectedTeacherId,
                                day_of_week: day,
                                period_number: period
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showNotification('Period cleared successfully', 'success');

                                // Remove from local data
                                currentTeacherSchedule = currentTeacherSchedule.filter(p =>
                                    !(p.day_of_week === day && p.period_number === period)
                                );

                                // Re-render the grid
                                renderTeacherScheduleGrid();

                                // Hide editor
                                cancelPeriodEdit();
                            } else {
                                showNotification('Failed to clear period: ' + data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error clearing period:', error);
                            showNotification('Error clearing period', 'error');
                        });
                }
            }

            // Cancel period edit
            function cancelPeriodEdit() {
                $('#periodEditorForm').hide();
                currentEditingCell = null;
            }

            // Check schedule conflicts
            function checkScheduleConflicts() {
                if (!selectedTeacherId) {
                    showNotification('Please select a teacher first', 'warning');
                    return;
                }

                console.log('Checking schedule conflicts...');

                fetch(`teacher_management_api.php?action=check_schedule_conflicts&teacher_id=${selectedTeacherId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.conflicts && data.conflicts.length > 0) {
                                showScheduleConflicts(data.conflicts);
                            } else {
                                showNotification('No schedule conflicts found', 'success');
                                $('#scheduleConflicts').hide();
                            }
                        } else {
                            showNotification('Failed to check conflicts: ' + data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error checking conflicts:', error);
                        showNotification('Error checking conflicts', 'error');
                    });
            }

            // Show schedule conflicts
            function showScheduleConflicts(conflicts) {
                const $conflictsList = $('#conflictsList');
                $conflictsList.empty();

                conflicts.forEach(conflict => {
                    $conflictsList.append(`<li>${conflict}</li>`);
                });

                $('#scheduleConflicts').show();
                showNotification(`${conflicts.length} conflicts detected`, 'warning');
            }

            // Show available slots
            function showAvailableSlots() {
                if (!selectedTeacherId) {
                    showNotification('Please select a teacher first', 'warning');
                    return;
                }

                const availableSlots = [];
                const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

                days.forEach(day => {
                    periodTimeSlots.forEach(timeSlot => {
                        const period = findPeriodForDayAndTime(day, timeSlot.period);
                        if (!period) {
                            availableSlots.push(`${day.charAt(0).toUpperCase() + day.slice(1)} - Period ${timeSlot.period} (${timeSlot.start}-${timeSlot.end})`);
                        }
                    });
                });

                if (availableSlots.length > 0) {
                    alert(`Available time slots:\n${availableSlots.join('\n')}`);
                } else {
                    showNotification('No available time slots', 'info');
                }
            }

            // Export teacher schedule
            function exportTeacherSchedule() {
                if (!selectedTeacherId) {
                    showNotification('Please select a teacher first', 'warning');
                    return;
                }

                const teacherName = $('#selectedTeacher option:selected').text().split(' - ')[0];
                const csv = generateScheduleCSV();
                downloadCSV(csv, `${teacherName}_schedule.csv`);
            }

            // Generate schedule CSV
            function generateScheduleCSV() {
                let csv = 'Day,Period,Time,Class,Section,Subject,Notes\n';

                const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

                days.forEach(day => {
                    periodTimeSlots.forEach(timeSlot => {
                        const period = findPeriodForDayAndTime(day, timeSlot.period);
                        if (period) {
                            csv += `${day},${timeSlot.period},${timeSlot.start}-${timeSlot.end},${period.class_name || ''},${period.section_name || ''},${period.subject_name || ''},"${period.notes || ''}"\n`;
                        }
                    });
                });

                return csv;
            }

            // Download CSV file
            function downloadCSV(csv, filename) {
                const blob = new Blob([csv], {
                    type: 'text/csv'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.setAttribute('hidden', '');
                a.setAttribute('href', url);
                a.setAttribute('download', filename);
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }

            // Reset schedule changes
            function resetScheduleChanges() {
                if (confirm('Are you sure you want to reset all unsaved changes?')) {
                    loadSelectedTeacherSchedule();
                    cancelPeriodEdit();
                    showNotification('Schedule changes reset', 'info');
                }
            }

            // Save all schedule changes
            function saveAllScheduleChanges() {
                if (scheduleChanges.length === 0) {
                    showNotification('No changes to save', 'info');
                    return;
                }

                // Implementation would batch save all changes
                showNotification('Bulk save functionality will be implemented', 'info');
            }

            // Bulk assign periods
            function openBulkAssignModal() {
                showNotification('Bulk assignment functionality will be implemented', 'info');
            }

            // ...existing code...

            // --- TAB SWITCHING INITIALIZATION ---
            function initializeTabSwitching() {
                console.log('Initializing tab switching...');

                // Attach click handlers to tab buttons
                $('.tab-button').on('click', function(e) {
                    e.preventDefault();
                    const tabId = $(this).data('tab');
                    console.log('Tab button clicked:', tabId);
                    if (tabId) {
                        switchTab(tabId);
                    } else {
                        console.error('No data-tab attribute found on button');
                    }
                });

                // Initialize with default tab or from localStorage
                let initialTab = null;

                try {
                    initialTab = localStorage.getItem('activeTeacherManagementTab');
                } catch (e) {
                    console.warn('Could not read from localStorage:', e);
                }

                // Validate that the stored tab exists and is accessible
                if (initialTab) {
                    const tabExists = $(`#${initialTab}-content`).length > 0;
                    const buttonExists = $(`.tab-button[data-tab="${initialTab}"]`).length > 0;
                    if (!tabExists || !buttonExists) {
                        console.warn(`Stored tab ${initialTab} does not exist, using default`);
                        initialTab = null;
                    }
                }

                // If no valid stored tab, use the first available tab
                if (!initialTab) {
                    const firstTab = $('.tab-button').first();
                    if (firstTab.length > 0) {
                        initialTab = firstTab.data('tab');
                        console.log('Using first available tab:', initialTab);
                    } else {
                        console.error('No tab buttons found');
                        return;
                    }
                }

                // Activate the initial tab
                if (initialTab) {
                    console.log('Activating initial tab:', initialTab);
                    switchTab(initialTab);
                }
            } // --- MISSING FUNCTIONS ---

            /**
             * Load teachers from API and display them
             */
            function loadTeachers() {
                console.log('Loading teachers...');
                const container = $('#teachersTableBody');
                const loading = $('#teachersLoading');

                // Show loading state
                loading.show();
                container.parent().hide();

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_teachers'
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Teachers API response:', response);
                        loading.hide();
                        container.parent().show();

                        if (response.success && response.data) {
                            displayTeachers(response.data);
                        } else {
                            container.html('<tr><td colspan="6" class="text-center">No teachers found</td></tr>');
                            console.warn('No teachers data in response:', response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading teachers:', error);
                        loading.hide();
                        container.parent().show();
                        container.html('<tr><td colspan="6" class="text-center text-danger">Error loading teachers</td></tr>');
                        showNotification('Failed to load teachers', 'error');
                    }
                });
            }

            /**
             * Display teachers in the table
             */
            function displayTeachers(teachers) {
                teachersData = teachers;
                console.log('Displaying', teachers.length, 'teachers');
                const tbody = $('#teachersTableBody');
                tbody.empty();


                if (!teachers || teachers.length === 0) {
                    tbody.html('<tr><td colspan="6" class="text-center">No teachers found</td></tr>');
                    return;
                }

                teachers.forEach(teacher => {
                    const row = `
                    <tr>
                        <td><code>${escapeHtml(teacher.employee_number || 'N/A')}</code></td>
                        <td>
                            <div class="teacher-info-cell">
                                <div class="teacher-name">${escapeHtml(teacher.full_name || 'N/A')}</div>
                                <div class="teacher-email">${escapeHtml(teacher.email || 'N/A')}</div>
                            </div>
                        </td>
                        <td>${escapeHtml(teacher.email || 'N/A')}</td>
                        <td>${escapeHtml(teacher.role || 'teacher')}</td>
                        <td><span class="status-badge status-${teacher.status || 'active'}">${teacher.status || 'active'}</span></td>
                        <td class="actions">
                            <button class="btn btn-outline btn-sm" onclick="editTeacher(${teacher.id})" title="Edit Teacher">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="viewTeacherDetails(${teacher.id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
                    tbody.append(row);
                });

                console.log('Teachers displayed successfully');
            }
            /**
             * Update sections dropdown based on selected class
             */
            function updateSections(classId, targetSectionId) {
                console.log('Updating sections for class:', classId);
                const sectionSelect = $('#' + targetSectionId);

                // Reset section dropdown
                sectionSelect.empty().append('<option value="">Select Section</option>');

                if (!classId) {
                    sectionSelect.prop('disabled', true);
                    return;
                }

                // Filter sections by class ID
                const classSections = sectionsData.filter(section => section.class_id == classId);

                if (classSections.length > 0) {
                    classSections.forEach(section => {
                        sectionSelect.append(`<option value="${section.id}">${escapeHtml(section.name)}</option>`);
                    });
                    sectionSelect.prop('disabled', false);
                    console.log('Found', classSections.length, 'sections for class', classId);
                } else {
                    sectionSelect.append('<option value="">No sections found</option>');
                    sectionSelect.prop('disabled', true);
                    console.log('No sections found for class', classId);
                }
            }

            /**
             * Load teachers for dropdown population
             */
            function loadTeachersForDropdowns(selectId, includeEmpty = true) {
                console.log('Loading teachers for dropdown:', selectId);
                const select = $('#' + selectId);

                if (select.length === 0) {
                    console.warn('Dropdown not found:', selectId);
                    return;
                }

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_teachers'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            select.empty();
                            if (includeEmpty) {
                                select.append('<option value="">Select Teacher</option>');
                            }

                            response.data.forEach(teacher => {
                                select.append(`<option value="${teacher.id}">${escapeHtml(teacher.full_name)} (${escapeHtml(teacher.employee_number)})</option>`);
                            });

                            console.log('Teachers loaded for dropdown:', selectId);
                        }
                    },
                    error: function() {
                        console.error('Failed to load teachers for dropdown:', selectId);
                    }
                });
            }

            /**
             * Load classes for dropdown population  
             */
            function loadClassesForDropdowns(selectId, includeEmpty = true) {
                console.log('Loading classes for dropdown:', selectId);
                const select = $('#' + selectId);

                if (select.length === 0) {
                    console.warn('Dropdown not found:', selectId);
                    return;
                }

                // Classes are already available in the PHP-generated dropdown
                console.log('Classes already populated in dropdown:', selectId);
            }

            /**
             * Load subjects for dropdown population
             */
            function loadSubjectsForDropdowns(selectId, includeEmpty = true) {
                console.log('Loading subjects for dropdown:', selectId);
                const select = $('#' + selectId);

                if (select.length === 0) {
                    console.warn('Dropdown not found:', selectId);
                    return;
                }

                // Subjects are already available in the PHP-generated dropdown
                console.log('Subjects already populated in dropdown:', selectId);
            }

            /**
             * Populate subject checkboxes based on teacher selection
             */
            function populateSubjectCheckboxes(teacherId) {
                console.log('Populating subject checkboxes for teacher:', teacherId);

                if (!teacherId) {
                    $('input[name="subjects[]"]').prop('checked', false);
                    return;
                }

                // Load teacher's current subject assignments
                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_teacher_subjects',
                        teacher_id: teacherId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            // Uncheck all first
                            $('input[name="subjects[]"]').prop('checked', false);

                            // Check assigned subjects
                            response.data.forEach(subjectId => {
                                $(`input[name="subjects[]"][value="${subjectId}"]`).prop('checked', true);
                            });

                            console.log('Subject checkboxes updated for teacher:', teacherId);
                        }
                    },
                    error: function() {
                        console.error('Failed to load teacher subjects for checkboxes');
                    }
                });
            }

            /**
             * Load assignment filters
             */
            function loadAssignmentFilters() {
                console.log('Loading assignment filters...');
                // This is handled by the loadTeachersForDropdowns and loadSubjectsForDropdowns functions
            }

            /**
             * Search teachers
             */
            function searchTeachers() {
                const query = $('#teacherSearch').val().toLowerCase();
                console.log('Searching teachers with query:', query);

                $('#teachersTableBody tr').each(function() {
                    const text = $(this).text().toLowerCase();
                    if (text.includes(query)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }

            /**
             * Debounce function for search
             */
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

            /**
             * Schedule editor functions (placeholders)
             */
            function toggleScheduleEditor() {
                const $editor = $('#teacherScheduleEditor');
                const $toggle = $('#teacherScheduleEditorToggle');

                if ($editor.is(':visible')) {
                    $editor.hide();
                    $toggle.html('<i class="fas fa-calendar-plus"></i> Open Schedule Editor');
                } else {
                    $editor.show();
                    $toggle.html('<i class="fas fa-calendar-minus"></i> Close Schedule Editor');
                }
            }

            function loadSelectedTeacherSchedule() {
                const teacherId = $('#selectedTeacher').val();
                if (!teacherId) {
                    showNotification('Please select a teacher first', 'error');
                    return;
                }

                console.log('Loading schedule for teacher:', teacherId);
                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_teacher_schedule',
                        teacher_id: teacherId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            // Load the schedule into the editor
                            loadScheduleIntoEditor(response.data);
                        }
                    },
                    error: function() {
                        console.error('Failed to load teacher schedule');
                    }
                });
            }

            /**
             * Utility functions
             */
            function escapeHtml(text) {
                if (typeof text !== 'string') {
                    return text;
                }
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) {
                    return map[m];
                });
            }

            function showNotification(message, type = 'info') {
                console.log(`Notification [${type}]:`, message);

                const notification = $('#notification');
                if (notification.length === 0) {
                    // Create notification element if it doesn't exist
                    $('body').append(`<div id="notification" class="notification"></div>`);
                }

                const notificationEl = $('#notification');
                notificationEl
                    .removeClass('success error warning info')
                    .addClass(type)
                    .text(message)
                    .fadeIn();

                // Auto-hide after 3 seconds
                setTimeout(() => {
                    notificationEl.fadeOut();
                }, 3000);
            }

            /**
             * Functions for teacher actions
             */

            function editTeacher(teacherId) {
                console.log('Opening edit modal for teacher:', teacherId);

                // Check if teachers data is loaded
                if (!teachersData || teachersData.length === 0) {
                    showNotification('Teachers data not loaded yet. Please wait and try again.', 'warning');
                    return;
                }

                // Find teacher data
                const teacher = teachersData.find(t => t.id == teacherId);
                if (!teacher) {
                    showNotification('Teacher not found', 'error');
                    return;
                }

                // Populate basic information
                document.getElementById('editTeacherId').value = teacher.id;
                document.getElementById('editTeacherName').value = teacher.full_name || '';
                document.getElementById('editTeacherEmail').value = teacher.email || '';
                document.getElementById('editEmployeeNumber').value = teacher.employee_number || '';
                document.getElementById('editTeacherStatus').value = teacher.status || 'active';

                // Load class teacher assignment options
                loadClassTeacherOptions(teacherId);

                // Load current subject assignments
                loadTeacherSubjectAssignments(teacherId);

                // Show modal with proper class management
                const modal = document.getElementById('editTeacherModal');
                document.body.classList.add('modal-open');
                modal.classList.add('show');

                // Focus on the modal for accessibility
                modal.focus();
            }

            function closeEditTeacherModal() {
                const modal = document.getElementById('editTeacherModal');
                modal.classList.remove('show');
                document.body.classList.remove('modal-open');
                document.getElementById('editTeacherForm').reset();
                document.getElementById('subjectAssignmentContainer').innerHTML = '';
            }

            function loadClassTeacherOptions(teacherId) {
                const select = document.getElementById('editClassTeacherSection');
                select.innerHTML = '<option value="">Not a Class Teacher</option>';

                let currentAssignment = null;

                // Get sections that are either unassigned or assigned to this teacher
                classes.forEach(cls => {
                    const classSections = sections.filter(s => s.class_id == cls.id);
                    classSections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.id;
                        option.textContent = `${cls.name} - ${section.name}`;

                        // Check if this teacher is already assigned to this section
                        if (section.class_teacher_user_id == teacherId) {
                            option.selected = true;
                            currentAssignment = `${cls.name} - ${section.name}`;
                        }

                        select.appendChild(option);
                    });
                });

                // Show current assignment info if exists
                if (currentAssignment) {
                    showCurrentClassTeacherInfo(currentAssignment);
                } else {
                    hideCurrentClassTeacherInfo();
                }
            }

            function showCurrentClassTeacherInfo(sectionName) {
                const infoDiv = document.getElementById('currentClassTeacherInfo');
                const textSpan = document.getElementById('currentClassTeacherText');
                if (infoDiv && textSpan) {
                    textSpan.textContent = sectionName;
                    infoDiv.style.display = 'block';
                }
            }

            function hideCurrentClassTeacherInfo() {
                const infoDiv = document.getElementById('currentClassTeacherInfo');
                if (infoDiv) {
                    infoDiv.style.display = 'none';
                }
            }

            function loadTeacherSubjectAssignments(teacherId) {
                const container = document.getElementById('subjectAssignmentContainer');
                container.innerHTML = '<div class="loading">Loading subject assignments...</div>';

                // Fetch current subject assignments
                fetch(`teacher_management_api.php?action=get_teacher_subjects&teacher_id=${teacherId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displaySubjectAssignments(data.assignments || []);
                        } else {
                            container.innerHTML = '<div class="error">Failed to load subject assignments</div>';
                            console.error('API Error:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading subject assignments:', error);
                        container.innerHTML = '<div class="error">Error loading subject assignments</div>';
                    });
            }

            function displaySubjectAssignments(assignments) {
                const container = document.getElementById('subjectAssignmentContainer');

                if (assignments.length === 0) {
                    container.innerHTML = '<div class="no-assignments">No subject assignments found</div>';
                    // Add one empty assignment form
                    addSubjectAssignment();
                    return;
                }

                let html = '';
                assignments.forEach((assignment, index) => {
                    html += createSubjectAssignmentHTML(assignment, index);
                });

                container.innerHTML = html;
            }

            function createSubjectAssignmentHTML(assignment, index) {
                // Handle null values from API
                const classId = assignment.class_id || '';
                const sectionId = assignment.section_id || '';
                const subjectId = assignment.subject_id || '';

                let classOptions = '<option value="">Select Class</option>';
                classes.forEach(cls => {
                    const selected = classId == cls.id ? 'selected' : '';
                    classOptions += `<option value="${cls.id}" ${selected}>${cls.name}</option>`;
                });

                let sectionOptions = '<option value="">Select Section</option>';
                if (classId) {
                    const classSections = sections.filter(s => s.class_id == classId);
                    classSections.forEach(section => {
                        const selected = sectionId == section.id ? 'selected' : '';
                        sectionOptions += `<option value="${section.id}" ${selected}>${section.name}</option>`;
                    });
                }

                let subjectOptions = '<option value="">Select Subject</option>';
                subjects.forEach(subject => {
                    const selected = subjectId == subject.id ? 'selected' : '';
                    subjectOptions += `<option value="${subject.id}" ${selected}>${subject.name}</option>`;
                });

                // Add note for general assignments (when no class/section is specified)
                const isGeneralAssignment = !classId && !sectionId && subjectId;
                const generalNote = isGeneralAssignment ?
                    '<small class="general-assignment-note">General subject assignment (all classes)</small>' : '';

                return `
                <div class="subject-assignment-item" data-index="${index}">
                    <select name="assignment_class_${index}" onchange="updateSectionOptions(${index}, this.value)">
                        ${classOptions}
                    </select>
                    <select name="assignment_section_${index}">
                        ${sectionOptions}
                    </select>
                    <select name="assignment_subject_${index}">
                        ${subjectOptions}
                    </select>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSubjectAssignment(${index}, ${assignment.id || null})">
                        <i class="fas fa-trash"></i>
                    </button>
                    ${generalNote}
                </div>
            `;
            }

            function addSubjectAssignment() {
                const container = document.getElementById('subjectAssignmentContainer');
                const existingItems = container.querySelectorAll('.subject-assignment-item');
                const index = existingItems.length;

                const newAssignment = {
                    class_id: '',
                    section_id: '',
                    subject_id: ''
                };

                const newHTML = createSubjectAssignmentHTML(newAssignment, index);
                container.insertAdjacentHTML('beforeend', newHTML);
            }


            function updateSectionOptions(index, classId) {
                const sectionSelect = document.querySelector(`select[name="assignment_section_${index}"]`);
                sectionSelect.innerHTML = '<option value="">Select Section</option>';

                if (classId) {
                    const classSections = sections.filter(s => s.class_id == classId);
                    classSections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.id;
                        option.textContent = section.name;
                        sectionSelect.appendChild(option);
                    });
                }
            }

            function saveTeacherChanges() {
                const formData = new FormData();
                formData.append('action', 'update_teacher');
                formData.append('teacher_id', document.getElementById('editTeacherId').value);
                formData.append('status', document.getElementById('editTeacherStatus').value);
                formData.append('class_teacher_section', document.getElementById('editClassTeacherSection').value);

                // Collect subject assignments
                const assignmentItems = document.querySelectorAll('.subject-assignment-item');
                const assignments = [];

                assignmentItems.forEach((item, index) => {
                    const classId = item.querySelector(`select[name="assignment_class_${index}"]`).value;
                    const sectionId = item.querySelector(`select[name="assignment_section_${index}"]`).value;
                    const subjectId = item.querySelector(`select[name="assignment_subject_${index}"]`).value;

                    if (classId && sectionId && subjectId) {
                        assignments.push({
                            class_id: classId,
                            section_id: sectionId,
                            subject_id: subjectId
                        });
                    }
                });

                formData.append('subject_assignments', JSON.stringify(assignments));

                // Show loading state
                const saveBtn = document.querySelector('#editTeacherModal .btn-primary');
                const originalText = saveBtn.textContent;
                saveBtn.textContent = 'Saving...';
                saveBtn.disabled = true;

                fetch('teacher_management_api.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showNotification('Teacher updated successfully', 'success');
                            closeEditTeacherModal();
                            loadTeachers(); // Reload teachers table
                            loadClassAssignments(); // Reload class assignments
                        } else {
                            showNotification(data.message || 'Failed to update teacher', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating teacher:', error);
                        showNotification('Error updating teacher', 'error');
                    })
                    .finally(() => {
                        saveBtn.textContent = originalText;
                        saveBtn.disabled = false;
                    });
            }

            function viewTeacherDetails(teacherId) {
                console.log('View teacher details:', teacherId);
                // Redirect to teacher details page
                window.location.href = `teacher_details.php?id=${teacherId}`;
            }

            function viewTeacherAssignments(teacherId) {
                console.log('View teacher assignments:', teacherId);
                showNotification('Teacher assignments view will be implemented', 'info');
            }

            function assignClassTeacher(sectionId) {
                console.log('Assign class teacher for section:', sectionId);

                // Get section info first
                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_section_info',
                        section_id: sectionId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            openAssignModal(response.data);
                        } else {
                            showNotification('Failed to load section information', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', {
                            xhr,
                            status,
                            error
                        });
                        console.error('Response text:', xhr.responseText);
                        showNotification('Error loading section information: ' + error, 'error');
                    }
                });
            }

            function reassignClassTeacher(sectionId) {
                console.log('Reassign class teacher for section:', sectionId);

                // First, get current section info
                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_section_info',
                        section_id: sectionId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.data) {
                            openReassignModal(response.data);
                        } else {
                            showNotification('Failed to load section information', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', {
                            xhr,
                            status,
                            error
                        });
                        console.error('Response text:', xhr.responseText);
                        showNotification('Error loading section information: ' + error, 'error');
                    }
                });
            }

            function removeClassTeacher(sectionId) {
                console.log('Remove class teacher for section:', sectionId);
                if (confirm('Are you sure you want to remove the class teacher for this section?')) {
                    showNotification('Class teacher removal functionality will be implemented', 'info');
                }
            }

            function removeSubjectAssignment(index, assignmentId) {
                // If assignmentId exists, remove from backend
                if (assignmentId) {
                    if (!confirm('Are you sure you want to remove this subject assignment?')) return;
                    fetch('teacher_management_api.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `action=remove_subject_assignment&assignment_id=${assignmentId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.removed_assignment) {
                                showNotification('Subject assignment removed successfully', 'success');
                                // Reload assignments for the current teacher
                                const teacherId = document.getElementById('editTeacherId').value;
                                loadTeacherSubjectAssignments(teacherId);
                            } else {
                                showNotification('Failed to remove subject assignment', 'error');
                            }
                        })
                        .catch(() => showNotification('Error removing subject assignment', 'error'));
                } else {
                    // If not saved yet, just remove from DOM
                    const item = document.querySelector(`[data-index="${index}"]`);
                    if (item) item.remove();
                }
            }

            function viewTeacherSchedule(teacherId) {
                console.log('View teacher schedule:', teacherId);
                showNotification('Teacher schedule view will be implemented', 'info');
            }

            function editClassTimetable(classId, sectionId) {
                console.log('Edit class timetable:', classId, sectionId);
                showNotification('Class timetable editing will be implemented', 'info');
            }

            // --- DOCUMENT READY ---
            $(document).ready(function() {
                console.log('Document ready - initializing teacher management interface...');

                // Initialize tab switching
                initializeTabSwitching();

                // Initialize form handlers (when implemented)
                if (typeof initializeFormHandlers === 'function') {
                    initializeFormHandlers();
                }

                // Initialize search handlers (when implemented)
                if (typeof initializeSearchHandlers === 'function') {
                    initializeSearchHandlers();
                }

                // Initialize teacher schedule editor
                initializeTeacherScheduleEditor();

                console.log('Teacher management interface initialization complete.');
            });

            $('#teacherScheduleEditorToggle').off('click').on('click', function() {
                $('#teacherScheduleEditor').toggle();
            });

            function openAssignModal(sectionData) {
                const modal = `
                <div id="assignModal" class="modal-overlay">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3>Assign Class Teacher</h3>
                            <button class="modal-close" onclick="closeAssignModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label><strong>Class:</strong> ${sectionData.class_name}</label>
                            </div>
                            <div class="form-group">
                                <label><strong>Section:</strong> ${sectionData.section_name}</label>
                            </div>
                            <div class="form-group">
                                <label><strong>Current Status:</strong> No class teacher assigned</label>
                            </div>
                            <hr style="margin: 20px 0;">
                            <div class="form-group">
                                <label for="assignTeacherId">Select Class Teacher:</label>
                                <select id="assignTeacherId" class="form-control" required>
                                    <option value="">-- Select Teacher --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assignReason">Notes (Optional):</label>
                                <textarea id="assignReason" class="form-control" rows="3" placeholder="Enter any notes about this assignment (optional)"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeAssignModal()">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="confirmAssignment(${sectionData.section_id})">Assign Teacher</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                $('body').append(modal);
                $('#assignModal').css('display', 'flex').hide().fadeIn(300);

                // Populate teachers dropdown for assignment
                populateTeachersDropdown(null, 'assignTeacherId');
            }

            function openReassignModal(sectionData) {
                const modal = `
                <div id="reassignModal" class="modal-overlay">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3>Reassign Class Teacher</h3>
                            <button class="modal-close" onclick="closeReassignModal()">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label><strong>Class:</strong> ${sectionData.class_name}</label>
                            </div>
                            <div class="form-group">
                                <label><strong>Section:</strong> ${sectionData.section_name}</label>
                            </div>
                            <div class="form-group">
                                <label><strong>Current Class Teacher:</strong> ${sectionData.current_teacher || 'Not assigned'}</label>
                            </div>
                            <hr style="margin: 20px 0;">
                            <div class="form-group">
                                <label for="newTeacherId">Select New Class Teacher:</label>
                                <select id="newTeacherId" class="form-control" required>
                                    <option value="">-- Select Teacher --</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="reassignReason">Reason for Reassignment:</label>
                                <textarea id="reassignReason" class="form-control" rows="3" placeholder="Enter reason for reassignment (optional)"></textarea>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="closeReassignModal()">Cancel</button>
                                <button type="button" class="btn btn-primary" onclick="confirmReassignment(${sectionData.section_id})">Reassign Teacher</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                // Remove existing modal if any
                $('#reassignModal').remove();

                // Add modal to page
                $('body').append(modal);

                // Populate teachers dropdown
                populateTeachersDropdown(sectionData.current_teacher_id);

                // Show modal
                $('#reassignModal').fadeIn(300);
            }


            function populateTeachersDropdown(currentTeacherId, dropdownId = 'newTeacherId') {
                const dropdown = $(`#${dropdownId}`);
                dropdown.empty().append('<option value="">-- Select Teacher --</option>');

                // Use the global teachers data
                if (window.teachersData && window.teachersData.length > 0) {
                    window.teachersData.forEach(teacher => {
                        if (teacher.status === 'active') {
                            const isSelected = teacher.id == currentTeacherId ? ' (Current)' : '';
                            dropdown.append(`<option value="${teacher.id}">${teacher.full_name}${isSelected}</option>`);
                        }
                    });
                } else {
                    // Fallback: load teachers via AJAX
                    $.ajax({
                        url: 'teacher_management_api.php',
                        type: 'GET',
                        data: {
                            action: 'get_teachers'
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success && response.data) {
                                response.data.forEach(teacher => {
                                    if (teacher.status === 'active') {
                                        const isSelected = teacher.id == currentTeacherId ? ' (Current)' : '';
                                        dropdown.append(`<option value="${teacher.id}">${teacher.full_name}${isSelected}</option>`);
                                    }
                                });
                            }
                        }
                    });
                }
            }

            function confirmReassignment(sectionId) {
                const newTeacherId = $('#newTeacherId').val();
                const reason = $('#reassignReason').val().trim();

                if (!newTeacherId) {
                    showNotification('Please select a teacher', 'error');
                    return;
                }

                const confirmMsg = `Are you sure you want to reassign the class teacher for this section?\n\nThis action will update the class teacher assignment immediately.`;

                if (!confirm(confirmMsg)) {
                    return;
                }

                // Show loading state
                $('.btn-primary', '#reassignModal').prop('disabled', true).text('Reassigning...');

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'POST',
                    data: {
                        action: 'reassign_class_teacher',
                        section_id: sectionId,
                        teacher_id: newTeacherId,
                        reason: reason
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Class teacher reassigned successfully', 'success');
                            closeReassignModal();

                            // Refresh the current view
                            if (typeof loadClassTeacherAssignments === 'function') {
                                loadClassTeacherAssignments();
                            }
                            if (typeof loadTeacherAssignments === 'function') {
                                loadTeacherAssignments();
                            }
                        } else {
                            showNotification('Failed to reassign class teacher: ' + (response.message || 'Unknown error'), 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Reassignment error:', error);
                        showNotification('Error reassigning class teacher: ' + error, 'error');
                    },
                    complete: function() {
                        $('.btn-primary', '#reassignModal').prop('disabled', false).text('Reassign Teacher');
                    }
                });
            }

            function closeReassignModal() {
                $('#reassignModal').fadeOut(300, function() {
                    $(this).remove();
                });
            }

            function closeAssignModal() {
                $('#assignModal').fadeOut(300, function() {
                    $(this).remove();
                });
            }

            function confirmAssignment(sectionId) {
                const teacherId = $('#assignTeacherId').val();
                const reason = $('#assignReason').val().trim();

                if (!teacherId) {
                    showNotification('Please select a teacher to assign', 'error');
                    return;
                }

                const confirmMsg = `Are you sure you want to assign this teacher as the class teacher for this section?\n\nThis action will create a new class teacher assignment.`;

                if (!confirm(confirmMsg)) {
                    return;
                }

                // Show loading state
                $('.btn-primary', '#assignModal').prop('disabled', true).text('Assigning...');

                $.ajax({
                    url: 'teacher_management_api.php',
                    type: 'POST',
                    data: {
                        action: 'assign_class_teacher',
                        section_id: sectionId,
                        teacher_id: teacherId,
                        reason: reason
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Class teacher assigned successfully!', 'success');
                            closeAssignModal();
                            loadClassAssignments(); // Refresh the assignments table
                        } else {
                            showNotification(response.message || 'Failed to assign class teacher', 'error');
                            $('.btn-primary', '#assignModal').prop('disabled', false).text('Assign Teacher');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ajax error:', {
                            xhr,
                            status,
                            error
                        });
                        console.error('Response text:', xhr.responseText);
                        showNotification('Error assigning class teacher: ' + error, 'error');
                        $('.btn-primary', '#assignModal').prop('disabled', false).text('Assign Teacher');
                    }
                });
            }


            // Enhanced debug function for API troubleshooting
            function debugTeacherScheduleAPI() {
                if (!selectedTeacherId) {
                    console.log('No teacher selected for debugging');
                    return;
                }

                console.log('=== DEBUGGING TEACHER SCHEDULE API ===');
                console.log('Selected Teacher ID:', selectedTeacherId);

                // Test the debug endpoint first
                fetch(`teacher_management_api.php?action=debug_teacher_schedule&teacher_id=${selectedTeacherId}`)
                    .then(response => {
                        console.log('Debug API Response Status:', response.status);
                        console.log('Debug API Response Headers:', Object.fromEntries(response.headers.entries()));
                        return response.text();
                    })
                    .then(text => {
                        console.log('Raw Debug API Response:', text);
                        try {
                            const data = JSON.parse(text);
                            console.log('=== DEBUG INFO ===');
                            console.log('Teacher exists:', data.debug_info?.teacher_exists);
                            console.log('Teacher info:', data.debug_info?.teacher_info);
                            console.log('Timetable periods table exists:', data.debug_info?.timetable_periods_table_exists);
                            console.log('Teacher period count:', data.debug_info?.teacher_period_count);
                            console.log('Sample periods:', data.debug_info?.sample_periods);
                            console.log('Related tables:', {
                                timetables: data.debug_info?.timetables_table_exists,
                                subjects: data.debug_info?.subjects_table_exists,
                                classes: data.debug_info?.classes_table_exists,
                                sections: data.debug_info?.sections_table_exists
                            });

                            if (data.debug_info?.error) {
                                console.error('Debug API Error:', data.debug_info.error);
                            }
                        } catch (e) {
                            console.error('Failed to parse debug API response as JSON:', e);
                        }
                    })
                    .catch(error => {
                        console.error('Debug API Request Error:', error);
                    });

                // Also test the main schedule API
                console.log('=== TESTING MAIN SCHEDULE API ===');
                fetch(`teacher_management_api.php?action=get_teacher_schedule&teacher_id=${selectedTeacherId}`)
                    .then(response => {
                        console.log('Main API Response Status:', response.status);
                        console.log('Main API Response Headers:', Object.fromEntries(response.headers.entries()));
                        return response.text();
                    })
                    .then(text => {
                        console.log('Raw Main API Response:', text);
                        try {
                            const data = JSON.parse(text);
                            console.log('Parsed Main API Response:', data);

                            if (data.success) {
                                console.log('Schedule data:', data.schedule);
                                console.log('Teacher info:', data.teacher_info);
                                console.log('Total periods:', data.total_periods);
                            } else {
                                console.error('API returned error:', data.message);
                            }
                        } catch (e) {
                            console.error('Failed to parse main API response as JSON:', e);
                        }
                    })
                    .catch(error => {
                        console.error('Main API Request Error:', error);
                    });
            }


            // Test basic teacher schedule API (fallback)
            function testBasicScheduleAPI() {
                if (!selectedTeacherId) {
                    console.log('No teacher selected for testing');
                    return;
                }

                console.log('Testing basic schedule API...');

                fetch(`teacher_management_api.php?action=get_basic_teacher_schedule&teacher_id=${selectedTeacherId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Basic Schedule API Response:', data);
                        if (data.success) {
                            console.log('Basic schedule loaded successfully');
                            // You can use this data to populate the grid for testing
                            currentTeacherSchedule = [];
                            renderTeacherScheduleGrid();
                            $('#teacherScheduleGrid').show();
                            $('#teacher-schedule-placeholder').hide();
                        }
                    })
                    .catch(error => {
                        console.error('Basic Schedule API Error:', error);
                    });
            }


            // Enhanced error handling for loadSelectedTeacherSchedule
            function loadSelectedTeacherScheduleWithDebug() {
                if (!selectedTeacherId) {
                    showNotification('Please select a teacher first', 'warning');
                    return;
                }

                console.log(`Loading complete schedule for teacher ID: ${selectedTeacherId}`);
                showNotification('Loading teacher schedule...', 'info');

                // Show loading state
                $('#teacherScheduleGrid').hide();
                const loadingHtml = '<div class="loading" style="text-align: center; padding: 40px;"><div class="spinner"></div><p>Loading schedule...</p></div>';
                $('#scheduleGridBody').html(loadingHtml);

                fetch(`teacher_management_api.php?action=get_teacher_schedule&teacher_id=${selectedTeacherId}`)
                    .then(response => {
                        console.log('API Response Status:', response.status);
                        console.log('API Response OK:', response.ok);

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }

                        return response.text();
                    })
                    .then(text => {
                        console.log('Raw API Response:', text);

                        // Try to parse JSON
                        let data;
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            console.error('JSON Parse Error:', e);
                            console.error('Response text:', text);
                            throw new Error('Invalid JSON response from server');
                        }

                        console.log('Parsed API Response:', data);

                        if (data.success) {
                            // Process successful response
                            currentTeacherSchedule = [];
                            const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

                            if (data.schedule) {
                                days.forEach(day => {
                                    if (data.schedule[day] && data.schedule[day].length > 0) {
                                        data.schedule[day].forEach(period => {
                                            currentTeacherSchedule.push({
                                                day_of_week: day,
                                                period_number: period.period,
                                                period_id: period.period_id,
                                                subject_name: period.subject_name,
                                                subject_code: period.subject_code,
                                                subject_id: period.subject_id,
                                                class_name: period.class_info ? period.class_info.split(' - ')[0] : '',
                                                section_name: period.class_info ? period.class_info.split(' - ')[1] : '',
                                                class_id: period.class_id,
                                                section_id: period.section_id,
                                                notes: period.notes,
                                                start_time: period.start_time,
                                                end_time: period.end_time,
                                                has_conflict: period.has_conflict
                                            });
                                        });
                                    }
                                });
                            }

                            renderTeacherScheduleGrid();
                            $('#teacherScheduleGrid').show();
                            $('#teacher-schedule-placeholder').hide();

                            const totalPeriods = data.total_periods || currentTeacherSchedule.length;
                            showNotification(`Loaded schedule with ${totalPeriods} periods`, 'success');

                            // Update teacher name in grid header
                            if (data.teacher_info) {
                                $('#currentTeacherName').text(`${data.teacher_info.full_name} - Schedule`);
                            }
                        } else {
                            console.error('API Error:', data.message);
                            showNotification('Failed to load teacher schedule: ' + (data.message || 'Unknown error'), 'error');
                            $('#scheduleGridBody').html('<tr><td colspan="7" class="text-center">Failed to load schedule: ' + (data.message || 'Unknown error') + '</td></tr>');

                            // Offer debug option
                            console.log('Schedule loading failed. Run debugTeacherScheduleAPI() for more details.');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading teacher schedule:', error);
                        showNotification('Error loading teacher schedule: ' + error.message, 'error');
                        $('#scheduleGridBody').html('<tr><td colspan="7" class="text-center text-danger">Error: ' + error.message + '</td></tr>');

                        // Auto-run debug on error
                        console.log('Auto-running debug due to error...');
                        setTimeout(() => debugTeacherScheduleAPI(), 1000);
                    });
            }
        </script>
</body>

</html>