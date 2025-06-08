<?php
// Start session and include required files at the very top
require_once __DIR__ . '/../../includes/functions.php';

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../../index.php");
    exit;
}

// Get unread notification counts
$unread_notifications = 0;
$unread_announcements = 0;

// Connect to the database
require_once 'con.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Count unread notifications
    $query = "SELECT COUNT(*) as count FROM notifications n 
              WHERE n.user_id = ? 
              AND n.is_read = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $unread_notifications = $row['count'];
    }
}

// Only start HTML output after all headers and session handling
?>
<!-- sidebar.php -->



<div class="sidebar <?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true' ? 'collapsed' : ''; ?>" id="sidebar">
<div class="sidebar-header">
    <div class="logo-container">
    <img src="https://lh3.googleusercontent.com/-aDvI5uiAJ4pvFRPUoTjzNYt5LH4UvFiSM6OGAL5dQMlzNrzWqVhSFRTRXZ3UWrzDHvR9az0dkffr9t0P39bHbAJb0pbfG-sahO2oKU" height="20px" width="20px" alt="Logo" class="logo">
    <span class="logo-text">VES Admin</span>
    </div>
</div>

    <div class="sidebar-content">
        <nav class="sidebar-nav">
        <div class="nav-section">
    <!-- Dashboard -->
    <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span>Dashboard</span>
    </a>

    <!-- Profile Management -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>Profile</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="adminprofile.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'adminprofile.php' ? 'active' : ''; ?>">Update Admin Details</a>
        </div>
    </div>

    <!-- User Management -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span>User Management</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="users.php" class="nav-subitem">Manage Users</a>
            <a href="user-add.php" class="nav-subitem">Add User</a>
        </div>
    </div>

    <!-- Student Management -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Student Management</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="classessections.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'classessections.php' ? 'active' : ''; ?>">View Students by Class</a>
            <a href="students.php?class=3" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : ''; ?>">Quick View - Class II</a>
            <a href="add_student.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'add_student.php' ? 'active' : ''; ?>">Add Student</a>
            <a href="import_student.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'import_student.php' ? 'active' : ''; ?>">Import/Export Data</a>
            <a href="student_transfer.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'student_transfer.php' ? 'active' : ''; ?>">Transfer/Promotion</a>
            <a href="student_transfer_records.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'student_transfer_records.php' ? 'active' : ''; ?>">Transfer Records</a>
            <a href="manage_student.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'manage_student.php' ? 'active' : ''; ?>">Manage Students</a>
        </div>
    </div>

    <!-- Teacher Management -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            <span>Teacher Management</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="teachersadd.php" class="nav-subitem">Add Teacher</a>
            <a href="teachersmanage.php" class="nav-subitem">Manage Teachers</a>
            <a href="teachersassign.php" class="nav-subitem">Assign Class Teachers</a>
            <a href="import_teacher.php" class="nav-subitem">Import Teachers</a>
        </div>
    </div>

    <!-- Academic Structure -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span>Academic Structure</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="academic_years.php" class="nav-subitem">Academic Years & Terms</a>
            <a href="classesmanage.php" class="nav-subitem">Manage Classes</a>
            <a href="classessections.php" class="nav-subitem">Manage Sections</a>
            <a href="subject_manage.php" class="nav-subitem">Manage Subjects</a>
            <a href="teacher_subject_assign.php" class="nav-subitem">Teacher Subject Assignment</a>
        </div>
    </div>

    <!-- Attendance -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>Attendance Reports</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="view.php" class="nav-subitem">View Attendance</a>
            <a href="generate.php" class="nav-subitem">Generate Reports</a>
            <a href="track.php" class="nav-subitem">Track Attendance</a>
        </div>
    </div>

    <!-- Exam Management -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>Exam & Results</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="schedule.php" class="nav-subitem">Create Schedule</a>
            <a href="results.php" class="nav-subitem">Input Results</a>
            <a href="reports.php" class="nav-subitem">Generate Reports</a>
        </div>
    </div>

    <!-- Fee Management -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Fee Management</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="fee_reports.php" class="nav-subitem">Track Payments</a>
            <a href="fee_structure.php" class="nav-subitem">Fee Structure</a>
            <a href="fee_collection.php" class="nav-subitem">Collect Fees</a>
        </div>
    </div>

    <!-- School Communications -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span>School Communications</span>
            <?php if ($unread_notifications > 0): ?>
                <span class="notification-badge"><?php echo $unread_notifications; ?></span>
            <?php endif; ?>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="announcements.php" class="nav-subitem">Announcements</a>
            <a href="notifications.php" class="nav-subitem">
                Send Notifications
                <?php if ($unread_notifications > 0): ?>
                    <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>

    <!-- School Events -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>School Events</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="schedule.php" class="nav-subitem">Event Schedule</a>
        </div>
    </div>

    <!-- Timetable -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Timetable Management</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="createtimetable.php" class="nav-subitem">Create Timetable</a>
            <a href="timetablemanage.php" class="nav-subitem">Manage Timetables</a>
        </div>
    </div>

    <!-- Online Classes -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <span>Online Classes</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
       
            <a href="onlineclassmanage.php" class="nav-subitem">Schedule & Manage</a>
        </div>
    </div>

    <!-- Resource Library -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
            </svg>
            <span>Resource Library</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
           
            <a href="resourcemanage.php" class="nav-subitem">Manage Resources</a>
        </div>
    </div>

    <!-- Leave Management -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <span>Leave Requests</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="leavemanage.php" class="nav-subitem">Manage Requests</a>
       
        </div>
    </div>

    <!-- 
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>System Settings</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="settings/general.php" class="nav-subitem">General Settings</a>
            <a href="settings/permissions.php" class="nav-subitem">Roles & Permissions</a>
            <a href="settings/customize.php" class="nav-subitem">Customize Portal</a>
        </div>
    </div>
System Settings -->
    <!-- Language Settings -->
    <a href="language.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
        </svg>
        <span>Language Settings</span>
    </a>
</div>
                
            </div>

            <div class="sidebar-footer">
               <!-- 
                <a href="settings.php" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Settings</span>
                </a>  Add more nav groups following the same pattern -->
                <a href="logout.php" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span>Logout</span>
                </a>
            </div>
        </nav>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const navGroups = document.querySelectorAll('.nav-group');
    
    // Mobile menu toggle
    function toggleSidebar() {
        sidebar.classList.toggle('show');
    }
    
    // Toggle submenu
    navGroups.forEach(group => {
        const toggle = group.querySelector('.nav-group-toggle');
        toggle.addEventListener('click', () => {
            // Close other open groups
            navGroups.forEach(otherGroup => {
                if (otherGroup !== group && otherGroup.classList.contains('active')) {
                    otherGroup.classList.remove('active');
                }
            });
            
            // Toggle current group
            group.classList.toggle('active');
        });
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768 && 
            !sidebar.contains(e.target) && 
            !e.target.classList.contains('hamburger-btn') &&
            sidebar.classList.contains('show')) {
            toggleSidebar();
        }
    });
    
    // Expose toggle function for hamburger button
    window.toggleSidebar = toggleSidebar;
});
</script>