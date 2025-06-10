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
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
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
</script>
</body>
</html>
