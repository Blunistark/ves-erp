<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
// Include functions file
require_once __DIR__ . '/../../includes/functions.php';

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is a teacher or headmaster
if (!isLoggedIn() || !hasRole(['teacher', 'headmaster'])) {
    // If it's an AJAX request, return a JSON error
    if (isset($is_ajax) && $is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required. Please log in.',
            'redirect' => '../index.php'
        ]);
        exit;
    }
    
    // For regular page requests, redirect to login
    header("Location: ../index.php");
    exit;
}


// Connect to the database
include 'con.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Count unread announcements
    $query = "SELECT COUNT(*) as count FROM announcements a 
              WHERE (a.target_audience = 'all' OR a.target_audience = 'teachers') 
              AND a.is_active = 1
              AND (a.expiry_date IS NULL OR a.expiry_date >= CURDATE())
              AND NOT EXISTS (
                  SELECT 1 FROM notification_read_status r 
                  WHERE r.user_id = ? 
                  AND r.notification_type = 'announcement' 
                  AND r.notification_id = a.id
              )";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $unread_announcements = $row['count'];
    }
    
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

// If this is an AJAX request, don't output the HTML sidebar
if (isset($is_ajax) && $is_ajax) {
    return; // Just return without outputting HTML
}
?>
<!-- sidebar.php -->

<div class="sidebar <?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true' ? 'collapsed' : ''; ?>" id="sidebar">
<div class="sidebar-header">
    <div class="logo-container">
    <img src="../../assets/images/school-logo.png" height="20px" width="20px" alt="Logo" class="logo">
    <span class="logo-text">Teacher Portal</span>
    </div>
</div>

    <div class="sidebar-content">
        <nav class="sidebar-nav">
        <div class="nav-section">

    
    <!-- Dashboard -->
    <a href="./" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == '/' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <span>Dashboard</span>
    </a>

    <?php if ($_SESSION['role'] === 'headmaster'): ?>
    <!-- Head Master Administrative Features -->
    <div class="nav-group">
        <div class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v13" />
            </svg>
            <span>Administrative Tools</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
        <div class="nav-submenu">
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

            <!-- Teacher Management (Headmaster) -->
            <div class="nav-group">
                <button class="nav-group-toggle">
                    <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    <span>Teacher Management</span>
                    <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="nav-group-content">
                    <a href="teacher_management_unified.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'teacher_management_unified.php' ? 'active' : ''; ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; margin-right: 8px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Teacher Assignments
                    </a>
                </div>
            </div>

              <!-- Academic Management -->
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Academic Management</span>
                        </div>
                        <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="nav-group-content">
                        <a href="academic_management_unified.php" class="nav-subitem">Academic Management</a>
                        <a href="academic_management_unified.php?tab=academic-years" class="nav-subitem">Academic Years</a>
                        <a href="academic_management_unified.php?tab=classes-sections" class="nav-subitem">Classes & Sections</a>
                        <a href="academic_management_unified.php?tab=subjects" class="nav-subitem">Subjects</a>
                        <a href="academic_management_unified.php?tab=curriculum" class="nav-subitem">Curriculum Mapping</a>
                        <a href="academic_management_unified.php?tab=reports" class="nav-subitem">Reports</a>
                        <a href="academic_management_unified.php?tab=bulk-operations" class="nav-subitem">Bulk Operations</a>
                    </div>
                </div>
            
            
            <!-- School Reports -->
            <a href="school-reports.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>School Reports</span>
            </a>
            
            <!-- Admin Notifications -->
            <a href="admin-notifications.php" class="nav-item">
                <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                <span>Admin Notifications</span>
            </a>
    </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Attendance Management -->
    <a href="attendance.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <span>Attendance Management</span>
    </a>

    <!-- Digital ID Card -->
    <a href="id-card.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
        </svg>
        <span>Digital ID Card</span>
    </a>

    <!-- Class Timetable -->
    <a href="timetable.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span>Class Timetable</span>
    </a>

    <!-- Student Performance Tracker -->
    <a href="performance.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <span>Student Performance</span>
    </a>

    <!-- Homework Manager -->
    <a href="homework.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span>Homework Manager</span>
    </a>

    <!-- Exam & Test Schedules -->
    <a href="exams.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-6-8h6M9 20h6M7 20h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span>Exam & Test Schedules</span>
    </a>

    <!-- Notifications & Announcements -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span>Notifications</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="notifications.php" class="nav-subitem">Class Notifications</a>
            <a href="school-notifications.php" class="nav-subitem">School Notifications</a>

        </div>
    </div>

    <!-- Class Notice Board -->
    <a href="notice.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <span>Class Notice Board</span>
    </a>

    <!-- Leave Requests -->
    <a href="leave.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span>Leave Requests</span>
    </a>

    <!-- Fee Clearance Status -->
    <a href="fees.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Fee Clearance Status</span>
    </a>

    <!-- Online Class Management -->
    <a href="online-classes.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <span>Online Class Management</span>
    </a>

    <!-- Resource Library -->
    <a href="resources.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <span>Resource Library</span>
    </a>

    <!-- Marks Entry Manager -->
    <a href="marks.php" class="nav-item">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        <span>Marks Entry Manager</span>
    </a>
</div>
            <!-- </div> -->

            <div class="sidebar-footer">
                <a href="profile.php" class="nav-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>My Profile</span>
                </a>
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