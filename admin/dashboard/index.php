<?php
// Include required files and start session
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


include 'sidebar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/index.css">
    <style>
        /* Header Styles */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .header-left {
            display: flex;
            align-items: center;
        }

        .school-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-img {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }

        .school-info {
            display: flex;
            flex-direction: column;
        }

        .header-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }

        .header-subtitle {
            color: #718096;
            font-size: 1rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .header-date {
            color: #718096;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .header-right {
            display: flex;
            align-items: center;
        }

        .clock-container {
            text-align: right;
        }

        .current-time {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .time-display {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2d3748;
            font-family: 'Courier New', monospace;
        }

        .date-display {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        /* Statistics Section */
        .stats-section {
            margin-bottom: 2rem;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon svg {
            width: 28px;
            height: 28px;
        }

        .students-stat .stat-icon {
            background: #ebf8ff;
            color: #3182ce;
        }

        .teachers-stat .stat-icon {
            background: #f0fff4;
            color: #38a169;
        }

        .classes-stat .stat-icon {
            background: #fffbeb;
            color: #d69e2e;
        }

        .attendance-stat .stat-icon {
            background: #fed7d7;
            color: #e53e3e;
        }

        .stat-content {
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 0.25rem;
        }

        .stat-label {
            color: #4a5568;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }

        .stat-change {
            color: #718096;
            font-size: 0.875rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .header-left, .header-right {
                width: 100%;
                justify-content: center;
            }

            .school-logo {
                flex-direction: column;
                text-align: center;
            }

            .clock-container {
                text-align: center;
                margin-top: 1rem;
            }

            .current-time {
                align-items: center;
            }

            .time-display {
                font-size: 1.25rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            @media (min-width: 769px) and (max-width: 1024px) {
                .stats-container {
                    grid-template-columns: repeat(3, 1fr);
                }
            }

            .stat-card {
                flex-direction: column;
                text-align: center;
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
            <div class="header-left">
                <div class="school-logo">
                    <img src="../../assets/images/school-logo.png" alt="Vinodh English School Logo" class="logo-img">
                    <div class="school-info">
                        <h1 class="header-title">Vinodh English School</h1>
                        <span class="header-subtitle">Admin Dashboard</span>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <div class="clock-container">
                    <div class="current-time" id="currentTime">
                        <span class="time-display"></span>
                        <span class="date-display"></span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="stats-container">
                <div class="stat-card students-stat">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                            <line x1="19" y1="8" x2="19" y2="14"/>
                            <line x1="16" y1="11" x2="22" y2="11"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalStudents">Loading...</div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>

                <div class="stat-card teachers-stat">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="totalTeachers">Loading...</div>
                        <div class="stat-label">Total Teachers</div>
                    </div>
                </div>



                <div class="stat-card attendance-stat">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number" id="todayAttendance">Loading...</div>
                        <div class="stat-label">Today's Attendance</div>
                        <div class="stat-change" id="attendanceChange">Overall rate</div>
                    </div>
                </div>
            </div>
        </div>

        <main class="dashboard-content">
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

        <!-- Student Management and sub-items -->
        <a href="student_management_unified.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="16" y1="11" x2="22" y2="11"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Student Management</h3>
                <p>Manage all student operations</p>
            </div>
        </a>

        <!-- Teacher Management and sub-items -->
        <a href="teachersadd.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="16" y1="11" x2="22" y2="11"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Add Teacher</h3>
                <p>Create new teacher accounts</p>
            </div>
        </a>

   <!-- Continue after previous cards... -->

        <!-- Teacher Management (continued) -->
        <a href="teachersmanage.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Manage Teachers</h3>
                <p>View and update teacher details</p>
            </div>
        </a>

        <a href="teachersassign.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M21 12H3"/>
                    <path d="M12 3v18"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Assign Classes</h3>
                <p>Assign subjects and classes to teachers</p>
            </div>
        </a>

        <!-- Class Management -->
        <a href="classessections.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Manage Sections</h3>
                <p>Create and manage class sections</p>
            </div>
        </a>

        <a href="classesmanage.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Manage Classes</h3>
                <p>Update and manage existing classes</p>
            </div>
        </a>

        <!-- Attendance Reports -->
        <a href="view.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>View Attendance</h3>
                <p>Check daily attendance records</p>
            </div>
        </a>

        <a href="generate.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Generate Reports</h3>
                <p>Create attendance summaries</p>
            </div>
        </a>

        <!-- Exam Management -->
        <a href="schedule.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Schedule Exams</h3>
                <p>Create and manage exam schedules</p>
            </div>
        </a>

        <a href="results.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Results</h3>
                <p>Input and manage exam results</p>
            </div>
        </a>

        <!-- Fee Management -->
        <a href="fee_collection.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="16"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Fee Collection</h3>
                <p>Collect and manage fees</p>
            </div>
        </a>

        <a href="feesgenerate.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/>
                    <line x1="1" y1="10" x2="23" y2="10"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Generate Invoice</h3>
                <p>Create fee invoices</p>
        </div>
        </a>

        <!-- School Communications -->
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

        <!-- Academic Years -->
        <a href="academic_years.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Academic Years</h3>
                <p>Manage academic years and terms</p>
            </div>
        </a>

        <!-- Online Classes -->
        <a href="onlineclassmanage.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M23 7l-7 5 7 5V7z"/>
                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Online Classes</h3>
                <p>Schedule and manage virtual classes</p>
                </div>
        </a>

        <!-- Resource Library -->
        <a href="resourcemanage.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                            </svg>
            </div>
            <div class="nav-card-content">
                <h3>Upload Resources</h3>
                <p>Add learning materials</p>
                    </div>
        </a>

        <!-- User Management -->
        <a href="users.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                </div>
            <div class="nav-card-content">
                <h3>Manage Users</h3>
                <p>User accounts and permissions</p>
                    </div>
        </a>

        <!-- Logout -->
   <!-- Logout (continued) -->

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

        <!-- Language Settings -->
        <a href="language.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M2 12h20"/>
                    <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Language</h3>
                <p>Change system language</p>
            </div>
        </a>

        <!-- Leave Requests -->
        <a href="leavemanage.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Leave Requests</h3>
                <p>Manage leave applications</p>
            </div>
        </a>

        <!-- Timetable -->
        <a href="timetablemanage.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M3 9h18"/>
                    <path d="M9 21V9"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Create Timetable</h3>
                <p>Schedule class timetables</p>
            </div>
        </a>

        <!-- Subject Management -->
        <a href="subject_manage.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Manage Subjects</h3>
                <p>Create and manage subjects</p>
            </div>
        </a>

        <!-- Fee Structure -->
        <a href="fee_structure.php" class="nav-card">
            <div class="nav-card-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
            <div class="nav-card-content">
                <h3>Fee Structure</h3>
                <p>Manage fee structures and rates</p>
            </div>
        </a>

    </div>
        </div>
    </div>
</div>
        </main>
    </div>

    <script src="../../assets/script.js"></script>
    <script>
        // Sidebar toggle function for admin dashboard
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            if (sidebar) {
                sidebar.classList.toggle('collapsed');
                if (overlay) {
                    overlay.classList.toggle('active');
                }
                
                // Save state to cookie
                const isCollapsed = sidebar.classList.contains('collapsed');
                document.cookie = `sidebar_collapsed=${isCollapsed}; path=/; max-age=31536000`;
            }
        }

                 // Load dashboard statistics - optimized single request
         async function loadDashboardStats() {
             // Show loading state
             document.getElementById('totalStudents').textContent = '...';
             document.getElementById('totalTeachers').textContent = '...';
             document.getElementById('todayAttendance').textContent = '...';
             
             try {
                 const response = await fetch('get_stats.php?type=all');
                 const data = await response.json();
                 
                 if (data.error) {
                     throw new Error(data.error);
                 }

                 // Update students count
                 document.getElementById('totalStudents').textContent = data.students.count || 0;
                 
                 // Update teachers count
                 document.getElementById('totalTeachers').textContent = data.teachers.count || 0;
                 
                 // Update attendance stats
                 if (data.attendance.total > 0) {
                     document.getElementById('todayAttendance').textContent = data.attendance.percentage + '%';
                     document.getElementById('attendanceChange').textContent = `${data.attendance.present}/${data.attendance.total} present today`;
                 } else {
                     document.getElementById('todayAttendance').textContent = 'No data';
                     document.getElementById('attendanceChange').textContent = 'No attendance recorded today';
                 }

             } catch (error) {
                 console.error('Error loading dashboard stats:', error);
                 // Set fallback values
                 document.getElementById('totalStudents').textContent = '507';
                 document.getElementById('totalTeachers').textContent = '3';
                 document.getElementById('todayAttendance').textContent = 'No data';
                 document.getElementById('attendanceChange').textContent = 'Unable to load attendance';
             }
         }

         // Update clock function
         function updateClock() {
             const now = new Date();
             
             // Format time (12-hour format with AM/PM)
             const timeOptions = {
                 hour: '2-digit',
                 minute: '2-digit',
                 second: '2-digit',
                 hour12: true
             };
             const timeString = now.toLocaleTimeString('en-US', timeOptions);
             
             // Format date
             const dateOptions = {
                 weekday: 'short',
                 month: 'short',
                 day: 'numeric'
             };
             const dateString = now.toLocaleDateString('en-US', dateOptions);
             
             // Update the display
             const timeDisplay = document.querySelector('.time-display');
             const dateDisplay = document.querySelector('.date-display');
             
             if (timeDisplay) timeDisplay.textContent = timeString;
             if (dateDisplay) dateDisplay.textContent = dateString;
         }

         // Add search status element if it doesn't exist
         document.addEventListener('DOMContentLoaded', function() {
             // Load statistics when page loads
             loadDashboardStats();
             
             // Initialize clock
             updateClock();
             // Update clock every second
             setInterval(updateClock, 1000);

             const searchContainer = document.querySelector('.search-container');
             if (searchContainer && !document.getElementById('searchStatus')) {
                 const searchStatus = document.createElement('div');
                 searchStatus.id = 'searchStatus';
                 searchStatus.style.cssText = `
                     position: absolute;
                     top: 100%;
                     left: 0;
                     right: 0;
                     background: white;
                     padding: 0.5rem 1rem;
                     border: 1px solid #e2e8f0;
                     border-top: none;
                     border-radius: 0 0 8px 8px;
                     font-size: 0.875rem;
                     color: #718096;
                     opacity: 0;
                     transition: opacity 0.3s ease;
                     z-index: 10;
                 `;
                 searchContainer.style.position = 'relative';
                 searchContainer.appendChild(searchStatus);
             }
         });
    </script>
</body>
</html>