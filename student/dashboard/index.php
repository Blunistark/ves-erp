<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Include sidebar which handles authentication
include 'sidebar.php';

// Get user information
$user_name = $_SESSION['full_name'] ?? 'Student';
$user_role = $_SESSION['role'] ?? 'student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo ucfirst($user_role); ?> Dashboard</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
       .user-welcome {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-text {
            flex: 1;
        }
        
        .welcome-text h2 {
            margin: 0;
            color: #333;
            font-size: 1.5rem;
        }
        
        .welcome-text p {
            margin: 5px 0 0;
            color: #666;
            font-size: 1rem;
        }
        
        .date-time {
            background-color: #f0f0f0;
            padding: 10px 15px;
            border-radius: 6px;
            text-align: center;
            min-width: 180px;
        }
        
        .date-time .time {
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .date-time .date {
            font-size: 0.9rem;
            color: #666;
        }

        /* MOBILE RESPONSIVE FOR WELCOME SECTION */
        @media (max-width: 768px) {
            .user-welcome {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding: 20px;
            }
            
            .welcome-text h2 {
                font-size: 1.25rem;
            }
            
            .welcome-text p {
                font-size: 0.9rem;
            }
            
            .date-time {
                min-width: auto;
                width: 100%;
                max-width: 250px;
            }
            
            .date-time .time {
                font-size: 1.1rem;
            }
            
            .date-time .date {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 480px) {
            .user-welcome {
                padding: 15px;
                gap: 12px;
            }
            
            .welcome-text h2 {
                font-size: 1.1rem;
            }
            
            .welcome-text p {
                font-size: 0.85rem;
            }
            
            .date-time {
                padding: 8px 12px;
            }
            
            .date-time .time {
                font-size: 1rem;
            }
            
            .date-time .date {
                font-size: 0.8rem;
            }
        }

        /* NOTIFICATION BELL STYLES */
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
        }

        .notification-bell {
            position: relative;
            order: 2;
        }

        .notification-icon {
            position: relative;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4285f4, #0d47a1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(66, 133, 244, 0.3);
        }

        .notification-icon:hover {
            background: linear-gradient(135deg, #3367d6, #0b3d91);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(66, 133, 244, 0.4);
        }

        .notification-icon svg {
            width: 24px;
            height: 24px;
            color: white;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #ff4444, #cc0000);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            min-width: 24px;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .notification-badge.hidden {
            display: none;
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 380px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            border: 1px solid #e0e0e0;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .notification-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
            background: #f8f9fa;
            border-radius: 12px 12px 0 0;
        }

        .notification-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }

        .mark-all-read {
            background: none;
            border: none;
            color: #4285f4;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .mark-all-read:hover {
            background: #e3f2fd;
            color: #1976d2;
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 16px 20px;
            border-bottom: 1px solid #f5f5f5;
            transition: background-color 0.2s ease;
            cursor: pointer;
            position: relative;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item.unread {
            background: linear-gradient(90deg, #e3f2fd 0%, #ffffff 8%);
            border-left: 4px solid #4285f4;
            padding-left: 16px;
        }

        .notification-content {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .notification-type-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .notification-type-icon.system {
            background: #e3f2fd;
            color: #1976d2;
        }

        .notification-type-icon.admin {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .notification-type-icon.teacher {
            background: #e8f5e8;
            color: #388e3c;
        }

        .notification-type-icon.announcement {
            background: #fff3e0;
            color: #f57c00;
        }

        .notification-type-icon.notice {
            background: #fce4ec;
            color: #c2185b;
        }

        .notification-details {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-weight: 600;
            font-size: 14px;
            color: #333;
            margin: 0 0 4px 0;
            line-height: 1.3;
        }

        .notification-message {
            font-size: 13px;
            color: #666;
            margin: 0 0 6px 0;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notification-meta {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notification-time {
            font-size: 12px;
            color: #999;
        }

        .notification-priority {
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .notification-priority.normal {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .notification-priority.important {
            background: #fff3e0;
            color: #ef6c00;
        }

        .notification-priority.urgent {
            background: #ffebee;
            color: #c62828;
        }

        .notification-footer {
            padding: 12px 20px;
            border-top: 1px solid #f0f0f0;
            background: #f8f9fa;
            border-radius: 0 0 12px 12px;
        }

        .view-all-btn {
            display: block;
            text-align: center;
            color: #4285f4;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .view-all-btn:hover {
            background: #e3f2fd;
            color: #1976d2;
        }

        .notification-loading, .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #999;
            font-size: 14px;
        }

        /* RESPONSIVE STYLES FOR NOTIFICATION */
        @media (max-width: 768px) {
            .header-right {
                gap: 15px;
            }
            
            .notification-icon {
                width: 44px;
                height: 44px;
            }
            
            .notification-icon svg {
                width: 22px;
                height: 22px;
            }
            
            .notification-badge {
                width: 22px;
                height: 22px;
                font-size: 11px;
                top: -6px;
                right: -6px;
            }

            .notification-dropdown {
                width: 350px;
                right: -10px;
            }
        }

        @media (max-width: 480px) {
            .header-right {
                gap: 10px;
            }
            
            .notification-icon {
                width: 40px;
                height: 40px;
            }
            
            .notification-icon svg {
                width: 20px;
                height: 20px;
            }
            
            .notification-badge {
                width: 20px;
                height: 20px;
                font-size: 10px;
                top: -5px;
                right: -5px;
            }

            .notification-dropdown {
                width: calc(100vw - 40px);
                right: -20px;
                max-width: 320px;
            }
            
            .notification-header {
                padding: 12px 16px;
            }
            
            .notification-item {
                padding: 12px 16px;
            }
            
            .notification-footer {
                padding: 10px 16px;
            }
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
        <h1 class="header-title"><?php echo ucfirst($user_role); ?> Dashboard</h1>
        <div class="header-right">
            <span class="header-date"><?php echo date('F j, Y'); ?></span>
            
            <!-- Notification Bell -->
            <div class="notification-bell" id="notificationBell">
                <div class="notification-icon" onclick="toggleNotificationDropdown()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="notification-badge" id="notificationBadge">0</span>
                </div>
                
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                        <button class="mark-all-read" onclick="markAllAsRead()">Mark all as read</button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="notification-loading">Loading notifications...</div>
                    </div>
                    <div class="notification-footer">
                        <a href="notifications.php" class="view-all-btn">View All Notifications</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="dashboard-content">
        <!-- User Welcome Section -->
        <div class="user-welcome">
            <div class="welcome-text">
                <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
                <p><?php echo $user_role === 'student' ? 'Access your academic information and resources below.' : 'Monitor your child\'s academic progress and activities.'; ?></p>
            </div>
            <div class="date-time">
                <div class="time" id="current-time">00:00:00</div>
                <div class="date"><?php echo date('l, F j, Y'); ?></div>
            </div>
        </div>
        
        <!-- Search and Navigation Cards Section -->
        <div class="nav-cards-section">
            <div class="search-container">
                <input type="text" id="cardSearch" class="search-input" placeholder="Search any feature...">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>

            <div class="nav-cards-grid">
                <!-- Attendance Tracker -->
                <a href="attendance.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Attendance Tracker</h3>
                        <p>Monitor student attendance</p>
                    </div>
                </a>

                <!-- Digital ID Card -->
                <a href="id-card_student.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="16" rx="2"/>
                            <circle cx="12" cy="10" r="3"/>
                            <path d="M8 16a4 4 0 0 1 8 0"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Student ID Card</h3>
                        <p>Generate digital ID cards for students</p>
                    </div>
                </a>

                <!-- Parent ID Card -->
                <a href="id-card_parent.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <rect x="15" y="8" width="7" height="10" rx="1"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Parent ID Card</h3>
                        <p>Generate digital ID cards for parents</p>
                    </div>
                </a>

                <!-- Student Subjects -->
                <a href="subjects.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                            <line x1="8" y1="7" x2="15" y2="7"/>
                            <line x1="8" y1="11" x2="15" y2="11"/>
                            <line x1="8" y1="15" x2="12" y2="15"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Student Subjects</h3>
                        <p>Manage student subject assignments</p>
                    </div>
                </a>

                <!-- Class Timetable -->
                <a href="timetable.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M3 9h18"/>
                            <path d="M9 21V9"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Class Timetable</h3>
                        <p>View and manage class schedules</p>
                    </div>
                </a>

                <!-- School Notifications -->
                <a href="notifications.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>School Notifications</h3>
                        <p>Send and manage school-wide alerts</p>
                    </div>
                </a>

                <!-- Announcements -->
                <a href="announcements.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Announcements</h3>
                        <p>Create and manage announcements</p>
                    </div>
                </a>

                <!-- Class Notice Board -->
                <a href="notice.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/>
                            <path d="M12 8v8"/>
                            <path d="M8 12h8"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Class Notice Board</h3>
                        <p>Manage class-specific notices</p>
                    </div>
                </a>

                <!-- Student Results -->
                <a href="results.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <path d="M16 13l-4 4-4-4"/>
                            <path d="M12 17V9"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Student Results</h3>
                        <p>Manage and publish exam results</p>
                    </div>
                </a>

                <!-- Test/Exam Timetable -->
                <a href="exams.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                            <path d="M8 14h2"/>
                            <path d="M14 14h2"/>
                            <path d="M8 18h2"/>
                            <path d="M14 18h2"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Exam Timetable</h3>
                        <p>Schedule and manage exam dates</p>
                    </div>
                </a>

                <!-- Fees Tracking -->
                <a href="fees.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="M12 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                            <path d="M12 21v-3"/>
                            <path d="M12 12v3"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Fees Tracking</h3>
                        <p>Monitor student fee payments</p>
                    </div>
                </a>

                <!-- Online Class Announcements -->
                <a href="online-classes.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 7l-7 5 7 5V7z"/>
                            <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                            <circle cx="5" cy="12" r="1"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Online Classes</h3>
                        <p>Manage virtual class announcements</p>
                    </div>
                </a>

                <!-- Profile Management -->
                <a href="adminprofile.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Profile</h3>
                        <p>Update admin details</p>
                    </div>
                </a>

                <!-- Settings -->
                <a href="settings.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Settings</h3>
                        <p>System configuration</p>
                    </div>
                </a>

                <!-- Logout -->
                <a href="logout.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Logout</h3>
                        <p>Sign out from the system</p>
                    </div>
                </a>
            </div>
        </div>
    </main>
</div>
    
<script>
    // Update current time
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString();
        document.getElementById('current-time').textContent = timeString;
    }
    
    // Update time every second
    setInterval(updateTime, 1000);
    updateTime(); // Initial call
    
    // Sidebar toggle function
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
        
        // Save state to cookie
        document.cookie = "sidebar_collapsed=" + sidebar.classList.contains('collapsed') + ";path=/;max-age=31536000";
        
        // Toggle overlay
        const overlay = document.querySelector('.sidebar-overlay');
        if (!sidebar.classList.contains('collapsed')) {
            overlay.style.display = 'block';
            overlay.addEventListener('click', toggleSidebar);
        } else {
            overlay.style.display = 'none';
            overlay.removeEventListener('click', toggleSidebar);
        }
    }
    
    // Card search functionality
    document.getElementById('cardSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.nav-card');
        
        cards.forEach(card => {
            const content = card.textContent.toLowerCase();
            if (content.includes(searchTerm)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // NOTIFICATION SYSTEM JAVASCRIPT
    let notificationDropdownOpen = false;

    // Toggle notification dropdown
    function toggleNotificationDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        notificationDropdownOpen = !notificationDropdownOpen;
        
        if (notificationDropdownOpen) {
            dropdown.classList.add('show');
            loadNotifications();
        } else {
            dropdown.classList.remove('show');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const bell = document.getElementById('notificationBell');
        const dropdown = document.getElementById('notificationDropdown');
        
        if (!bell.contains(event.target) && notificationDropdownOpen) {
            dropdown.classList.remove('show');
            notificationDropdownOpen = false;
        }
    });

    // Load notifications from API
    async function loadNotifications() {
        try {
            const response = await fetch('notification_api.php?action=get_notifications&limit=10');
            const data = await response.json();
            
            if (data.notifications) {
                displayNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            document.getElementById('notificationList').innerHTML = 
                '<div class="notification-empty">Failed to load notifications</div>';
        }
    }

    // Display notifications in the dropdown
    function displayNotifications(notifications) {
        const listContainer = document.getElementById('notificationList');
        
        if (notifications.length === 0) {
            listContainer.innerHTML = '<div class="notification-empty">No notifications yet</div>';
            return;
        }
        
        const notificationsHTML = notifications.map(notification => {
            const typeIcon = getNotificationTypeIcon(notification.type);
            const unreadClass = notification.is_read == '0' ? 'unread' : '';
            
            return `
                <div class="notification-item ${unreadClass}" onclick="markAsRead(${notification.id})">
                    <div class="notification-content">
                        <div class="notification-type-icon ${notification.type}">
                            ${typeIcon}
                        </div>
                        <div class="notification-details">
                            <div class="notification-title">${notification.title || 'Notification'}</div>
                            <div class="notification-message">${notification.message}</div>
                            <div class="notification-meta">
                                <span class="notification-time">${notification.time_ago}</span>
                                <span class="notification-priority ${notification.priority}">${notification.priority}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        listContainer.innerHTML = notificationsHTML;
    }

    // Get icon for notification type
    function getNotificationTypeIcon(type) {
        const icons = {
            system: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
            admin: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>',
            teacher: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            announcement: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
            notice: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>'
        };
        return icons[type] || icons.system;
    }

    // Mark single notification as read
    async function markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('notification_id', notificationId);
            
            const response = await fetch('notification_api.php?action=mark_as_read', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                loadNotifications(); // Reload to update UI
                updateUnreadCount(); // Update badge
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Mark all notifications as read
    async function markAllAsRead() {
        try {
            const response = await fetch('notification_api.php?action=mark_all_as_read', {
                method: 'POST'
            });
            
            if (response.ok) {
                loadNotifications(); // Reload to update UI
                updateUnreadCount(); // Update badge
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    // Update unread count badge
    async function updateUnreadCount() {
        try {
            const response = await fetch('notification_api.php?action=get_unread_count');
            const data = await response.json();
            
            const badge = document.getElementById('notificationBadge');
            const count = parseInt(data.unread_count) || 0;
            
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error updating unread count:', error);
        }
    }

    // Initialize notification system
    document.addEventListener('DOMContentLoaded', function() {
        updateUnreadCount();
        
        // Update unread count every 30 seconds
        setInterval(updateUnreadCount, 30000);
    });
</script>
</body>
</html>
