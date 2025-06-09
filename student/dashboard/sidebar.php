<?php
// Include functions file
require_once __DIR__ . '/../../includes/functions.php';

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is a student or parent
if (!isLoggedIn() || !hasRole(['student', 'parent'])) {
    // Redirect to login page
    header("Location: ../index.php");
    exit;
}

// Get user role for conditional UI elements
$userRole = $_SESSION['role'];

// Get unread notification counts
$unread_notifications = 0;
$unread_announcements = 0;

// Connect to the database
include 'con.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Count unread announcements
    $query = "SELECT COUNT(*) as count FROM announcements a 
              WHERE (a.target_audience = 'all' OR a.target_audience = 'students') 
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
?>
<!-- sidebar.php -->

<div class="sidebar <?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true' ? 'collapsed' : ''; ?>" id="sidebar">
<div class="sidebar-header">
    <div class="logo-container">
    <img src="https://lh3.googleusercontent.com/-aDvI5uiAJ4pvFRPUoTjzNYt5LH4UvFiSM6OGAL5dQMlzNrzWqVhSFRTRXZ3UWrzDHvR9az0dkffr9t0P39bHbAJb0pbfG-sahO2oKU" height="20px" width="20px" alt="Logo" class="logo">
    <span class="logo-text">VES Portal</span>
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

    <!-- Attendance Tracker -->
    <a href="attendance.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <span>Attendance Tracker</span>
    </a>

    <!-- Digital ID Card -->
    <div class="nav-group">
        <button class="nav-group-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
            </svg>
            <span>Digital ID Cards</span>
            <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>
        <div class="nav-group-content">
            <a href="id-card_student.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'id-card_student.php' ? 'active' : ''; ?>">Student ID Card</a>
            <a href="id-card_parent.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'id-card_parent.php' ? 'active' : ''; ?>">Parent ID Card</a>
        </div>
    </div>

    <!-- Homework -->
    <a href="homework.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'homework.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span>My Homework</span>
    </a>

    <!-- Student Subjects -->
    <a href="subjects.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'subjects.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <span>Student Subjects</span>
    </a>

    <!-- Class Timetable -->
    <a href="timetable.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'timetable.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span>Class Timetable</span>
    </a>

    <!-- School Notifications -->
    <a href="notifications.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span>School Notifications</span>
        <?php if ($unread_notifications > 0): ?>
            <span class="notification-badge"><?php echo $unread_notifications; ?></span>
        <?php endif; ?>
    </a>

    <!-- Announcements -->
    <a href="announcements.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'announcements.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.2349.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
        </svg>
        <span>Announcements</span>
        <?php if ($unread_announcements > 0): ?>
            <span class="notification-badge"><?php echo $unread_announcements; ?></span>
        <?php endif; ?>
    </a>

    <!-- Class Notice Board -->
    <a href="notice.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'notice.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <span>Class Notice Board</span>
    </a>

    <!-- Student Results -->
    <a href="results.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
        </svg>
        <span>Student Results</span>
    </a>

    <!-- Test/Exam Timetable -->
    <a href="exams.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'exams_timetable.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m-6-8h6M9 20h6M7 20h10a2 2 0 002-2V6a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <span>Exam Timetable</span>
    </a>

    <!-- Fees Tracking -->
    <a href="fees.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'fees.php' ? 'active' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Fees Tracking</span>
    </a>

</div>
            </div>

            <div class="sidebar-footer">
                <a href="profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
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
        document.body.classList.toggle('sidebar-open');
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
    
    // Check if any nav-group should be initially active
    navGroups.forEach(group => {
        const subItems = group.querySelectorAll('.nav-subitem');
        subItems.forEach(subItem => {
            if (subItem.classList.contains('active')) {
                group.classList.add('active');
            }
        });
    });
    
    // Expose toggle function for hamburger button
    window.toggleSidebar = toggleSidebar;
});
</script>