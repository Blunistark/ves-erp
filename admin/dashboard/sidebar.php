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
$pending_approvals = 0;

// Connect to the database
require_once 'con.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Count unread notifications
    $query = "SELECT COUNT(*) as count FROM notifications n 
              WHERE n.user_id = ? AND n.is_read = 0 AND n.is_active = 1
              AND (n.expires_at IS NULL OR n.expires_at > NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $unread_notifications = $row['count'];
    }
    
    // Count unread announcements
    $announce_query = "SELECT COUNT(*) as count FROM announcements a
                      LEFT JOIN notification_read_status nrs ON a.id = nrs.notification_id 
                      AND nrs.user_id = ? AND nrs.notification_type = 'announcement'
                      WHERE nrs.notification_id IS NULL 
                      AND a.is_active = 1 
                      AND (a.expiry_date IS NULL OR a.expiry_date >= CURDATE())
                      AND (a.target_audience = 'all' OR a.target_audience = 'teachers')";
    $stmt2 = $conn->prepare($announce_query);
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    if ($row2 = $result2->fetch_assoc()) {
        $unread_announcements = $row2['count'];
    }
    
    // Count pending notification approvals
    $approval_query = "SELECT COUNT(*) as count FROM notification_approvals 
                      WHERE status = 'pending'";
    $stmt3 = $conn->prepare($approval_query);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    if ($row3 = $result3->fetch_assoc()) {
        $pending_approvals = $row3['count'];
    }
}
?>

<style>
/* Hamburger Menu Button */
.hamburger-btn {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: none;
}

.hamburger-btn:hover {
    background: #f9fafb;
}

.hamburger-icon {
    width: 24px;
    height: 24px;
    color: #374151;
}

/* Show hamburger on mobile */
@media (max-width: 1024px) {
    .hamburger-btn {
        display: block;
    }
}

/* Sidebar Specific Styles - Isolated to prevent interference */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 280px;
    height: 100vh;
    background: white;
    color: #374151;
    overflow-y: auto;
    z-index: 1000;
    transition: transform 0.3s ease;
    border-right: 1px solid #e5e7eb;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.sidebar.collapsed {
    transform: translateX(-280px);
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #e5e7eb;
    background: white;
}

.logo-container {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo {
    border-radius: 4px;
}

.logo-text {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
}

.sidebar-content {
    flex: 1;
    overflow-y: auto;
    background: white;
}

.sidebar-nav {
    padding: 16px 0;
}

.nav-section {
    padding: 0 16px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    margin: 2px 0;
    color: #6b7280;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 14px;
    position: relative;
}

.nav-item:hover {
    background: #f3f4f6;
    color: #374151;
}

.nav-item.active {
    background: #dbeafe;
    color: #1d4ed8;
}

.nav-icon {
    width: 20px;
    height: 20px;
    stroke-width: 1.5;
}

.nav-group {
    margin: 4px 0;
}

.nav-group-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 12px 16px;
    background: none;
    border: none;
    color: #6b7280;
    text-align: left;
    cursor: pointer;
    border-radius: 8px;
    transition: all 0.2s ease;
    font-size: 14px;
}

.nav-group-toggle:hover {
    background: #f3f4f6;
    color: #374151;
}

.nav-group-toggle .nav-icon {
    width: 20px;
    height: 20px;
    stroke-width: 1.5;
}

.arrow-icon {
    width: 16px;
    height: 16px;
    transition: transform 0.2s ease;
}

.nav-group.active .arrow-icon {
    transform: rotate(180deg);
}

.nav-group-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    margin-left: 16px;
    border-left: 2px solid #e5e7eb;
}

.nav-group.active .nav-group-content {
    max-height: 500px;
}

.nav-subitem {
    display: block;
    padding: 8px 16px;
    margin: 2px 0;
    color: #9ca3af;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s ease;
    font-size: 13px;
    position: relative;
}

.nav-subitem:hover {
    background: #f3f4f6;
    color: #374151;
}

.nav-subitem.active {
    background: #dbeafe;
    color: #1d4ed8;
}

.notification-badge {
    background: #ef4444;
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    min-width: 16px;
    height: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.sidebar-footer {
    padding: 16px;
    border-top: 1px solid #e5e7eb;
    margin-top: auto;
    background: white;
}

/* Mobile Responsive */
@media (max-width: 1024px) {
    .sidebar {
        transform: translateX(-100%);
    }
    
    .sidebar.show {
        transform: translateX(0);
    }
}

/* Overlay for mobile */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    display: none;
}

.sidebar.show ~ .sidebar-overlay {
    display: block;
}

/* Scrollbar styles for sidebar */
.sidebar::-webkit-scrollbar {
    width: 6px;
}

.sidebar::-webkit-scrollbar-track {
    background: #f9fafb;
}

.sidebar::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

.sidebar::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}
</style>

<!-- Hamburger Menu Button -->
<button class="hamburger-btn" onclick="toggleSidebar()">
    <svg class="hamburger-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<div class="sidebar <?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] === 'true' ? 'collapsed' : ''; ?>" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <img src="../../assets/images/school-logo.png" height="20px" width="20px" alt="Logo" class="logo">
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
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>Profile</span>
                        </div>
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
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>User Management</span>
                        </div>
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
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>Student Management</span>
                        </div>
                        <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="nav-group-content">
                        <a href="student_management_unified.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'student_management_unified.php' ? 'active' : ''; ?>">Student Management</a>
                    <!--    <a href="student_transfer.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'student_transfer.php' ? 'active' : ''; ?>">Transfer/Promotion</a>-->
                    <!--    <a href="student_transfer_records.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'student_transfer_records.php' ? 'active' : ''; ?>">Transfer Records</a>-->
                    </div>
                </div>

                <!-- Teacher Management -->
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            <span>Teacher Management</span>
                        </div>
                        <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="nav-group-content">
                        <a href="teacher_management_unified.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'teacher_management_unified.php' ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; margin-right: 8px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Unified Management
                        </a>
                        <a href="import_teacher.php" class="nav-subitem">
                            <svg xmlns="http://www.w3.org/2000/svg" style="width: 14px; height: 14px; margin-right: 8px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                            </svg>
                            Import Teachers
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
                        <a href="academic_management_unified.php?tab=academic-years" class="nav-subitem">Academic Years</a>
                        <a href="academic_management_unified.php?tab=classes-sections" class="nav-subitem">Classes & Sections</a>
                        <a href="academic_management_unified.php?tab=subjects" class="nav-subitem">Subjects</a>
                        <a href="academic_management_unified.php?tab=curriculum" class="nav-subitem">Curriculum Mapping</a>
                        <!--<a href="academic_management_unified.php?tab=reports" class="nav-subitem">Reports</a>-->
                        <!--<a href="academic_management_unified.php?tab=bulk-operations" class="nav-subitem">Bulk Operations</a>-->
                    </div>
                </div>

                <!-- Attendance -->
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>Attendance Reports</span>
                        </div>
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
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>Exam & Results</span>
                        </div>
                        <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div class="nav-group-content">
                        <a href="schedule.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'active' : ''; ?>">Create Schedule</a>
                        <a href="exam_session_management.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'exam_session_management.php' ? 'active' : ''; ?>">Manage Exam Sessions</a>
                        <a href="manage_exam_subjects.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'manage_exam_subjects.php' ? 'active' : ''; ?>">Manage Exam Subjects</a>
                        <a href="view_exam_marks.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'view_exam_marks.php' ? 'active' : ''; ?>">View Exam Marks</a>
                        <a href="exam_report.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'exam_report.php' ? 'active' : ''; ?>">Exam Reports</a>
                        <a href="results.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'results.php' ? 'active' : ''; ?>">Input Results</a>
                        <a href="reports.php" class="nav-subitem <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">Generate Reports</a>
                    </div>
                </div>

                <!-- Fee Management -->
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Fee Management</span>
                        </div>
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
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span>School Communications</span>
                            <?php if ($unread_notifications > 0): ?>
                                <span class="notification-badge"><?php echo $unread_notifications; ?></span>
                            <?php endif; ?>
                        </div>
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
                        <a href="notification_approvals.php" class="nav-subitem">
                            Approve Notifications
                            <?php if ($pending_approvals > 0): ?>
                                <span class="notification-badge"><?php echo $pending_approvals; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>

                <!-- School Events -->
                <div class="nav-group">
                    <button class="nav-group-toggle">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 012 2z" />
                           </svg>
                           <span>School Events</span>
                       </div>
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
                       <div style="display: flex; align-items: center; gap: 12px;">
                           <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                           </svg>
                           <span>Timetable Management</span>
                       </div>
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
                       <div style="display: flex; align-items: center; gap: 12px;">
                           <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                           </svg>
                           <span>Online Classes</span>
                       </div>
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
                       <div style="display: flex; align-items: center; gap: 12px;">
                           <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                           </svg>
                           <span>Resource Library</span>
                       </div>
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
                       <div style="display: flex; align-items: center; gap: 12px;">
                           <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                           </svg>
                           <span>Leave Requests</span>
                       </div>
                       <svg class="arrow-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                       </svg>
                   </button>
                   <div class="nav-group-content">
                       <a href="leavemanage.php" class="nav-subitem">Manage Requests</a>
                   </div>
               </div>

               <!-- Language Settings -->
               <a href="language.php" class="nav-item">
                   <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                   </svg>
                   <span>Language Settings</span>
               </a>
           </div>
       </nav>
   </div>

   <div class="sidebar-footer">
       <a href="logout.php" class="nav-item">
           <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
           </svg>
           <span>Logout</span>
       </a>
   </div>
</div>

<!-- Sidebar overlay for mobile -->
<div class="sidebar-overlay" onclick="closeSidebar()"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
   const sidebar = document.getElementById('sidebar');
   const navGroups = document.querySelectorAll('.nav-group');
   const overlay = document.querySelector('.sidebar-overlay');
   
   // Mobile menu toggle
   window.toggleSidebar = function() {
       sidebar.classList.toggle('show');
       overlay.style.display = sidebar.classList.contains('show') ? 'block' : 'none';
       document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
   }
   
   // Close sidebar
   window.closeSidebar = function() {
       sidebar.classList.remove('show');
       overlay.style.display = 'none';
       document.body.style.overflow = '';
   }
   
   // Toggle submenu
   navGroups.forEach(group => {
       const toggle = group.querySelector('.nav-group-toggle');
       toggle.addEventListener('click', (e) => {
           e.preventDefault();
           e.stopPropagation();
           
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
       if (window.innerWidth <= 1024 && 
           !sidebar.contains(e.target) && 
           !e.target.classList.contains('hamburger-btn') &&
           !e.target.closest('.hamburger-btn') &&
           sidebar.classList.contains('show')) {
           closeSidebar();
       }
   });
   
   // Handle window resize
   window.addEventListener('resize', function() {
       if (window.innerWidth > 1024) {
           sidebar.classList.remove('show');
           overlay.style.display = 'none';
           document.body.style.overflow = '';
       }
   });
   
   // Auto-expand nav group if current page is in it
   navGroups.forEach(group => {
       const activeSubitem = group.querySelector('.nav-subitem.active');
       if (activeSubitem) {
           group.classList.add('active');
       }
   });
});
</script>
