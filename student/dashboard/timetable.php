<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Class Timetable</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/timetable.css">
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
        <h1 class="header-title">Class Timetable</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div class="class-selector">
            <div class="class-option active">Class X-A</div>
            <div class="class-option">Semester 1</div>
            <div class="class-option">Semester 2</div>
        </div>

        <div class="card todays-schedule">
            <div class="schedule-header">
                <div class="schedule-title">
                    Today's Schedule
                    <div class="current-class-indicator">
                        <div class="current-class-dot"></div>
                        Current Class
                    </div>
                </div>
                
                <div style="color: #4b5563; font-size: 0.95rem;">
                    <?php echo date('l, F j, Y'); ?>
                </div>
            </div>
            
            <div class="schedule-list">
                <!-- Period 1 -->
                <div class="schedule-item">
                    <div class="schedule-time">
                        <div class="time-start">08:00</div>
                        <div class="time-end">08:45</div>
                    </div>
                    
                    <div class="schedule-content">
                        <div class="schedule-subject">
                            <div class="subject-icon math-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            
                            <div class="subject-details">
                                <div class="subject-name">Mathematics</div>
                                <div class="subject-teacher">Mrs. Johnson</div>
                            </div>
                        </div>
                        
                        <div class="schedule-location">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Classroom 301
                        </div>
                    </div>
                </div>
                
                <!-- Period 2 -->
                <div class="schedule-item">
                    <div class="schedule-time">
                        <div class="time-start">08:50</div>
                        <div class="time-end">09:35</div>
                    </div>
                    
                    <div class="schedule-content">
                        <div class="schedule-subject">
                            <div class="subject-icon science-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </div>
                            
                            <div class="subject-details">
                                <div class="subject-name">Science</div>
                                <div class="subject-teacher">Mr. Wilson</div>
                            </div>
                        </div>
                        
                        <div class="schedule-location">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Science Lab 102
                        </div>
                    </div>
                </div>
                
                <!-- Break -->
                <div class="schedule-item">
                    <div class="schedule-time">
                        <div class="time-start">09:35</div>
                        <div class="time-end">09:50</div>
                    </div>
                    
                    <div class="schedule-content">
                        <div class="schedule-subject">
                            <div class="subject-icon break-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            
                            <div class="subject-details">
                                <div class="subject-name">Short Break</div>
                                <div class="subject-teacher">-</div>
                            </div>
                        </div>
                        
                        <div class="schedule-location">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            School Courtyard
                        </div>
                    </div>
                </div>
                
                <!-- Period 3 -->
                <div class="schedule-item current">
                    <div class="schedule-time">
                        <div class="time-start">09:50</div>
                        <div class="time-end">10:35</div>
                    </div>
                    
                    <div class="schedule-content">
                        <div class="schedule-subject">
                            <div class="subject-icon language-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                                </svg>
                            </div>
                            
                            <div class="subject-details">
                                <div class="subject-name">English Language</div>
                                <div class="subject-teacher">Ms. Adams</div>
                            </div>
                        </div>
                        
                        <div class="schedule-location">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Classroom 301
                        </div>
                    </div>
                </div>
                
                <!-- Period 4 -->
                <div class="schedule-item">
                    <div class="schedule-time">
                        <div class="time-start">10:40</div>
                        <div class="time-end">11:25</div>
                    </div>
                    
                    <div class="schedule-content">
                        <div class="schedule-subject">
                            <div class="subject-icon history-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            
                            <div class="subject-details">
                                <div class="subject-name">History</div>
                                <div class="subject-teacher">Mr. Peterson</div>
                            </div>
                        </div>
                        
                        <div class="schedule-location">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Classroom 301
                        </div>
                    </div>
                </div>
                
                <!-- Lunch Break -->
                <div class="schedule-item">
                    <div class="schedule-time">
                        <div class="time-start">11:25</div>
                        <div class="time-end">12:10</div>
                    </div>
                    
                    <div class="schedule-content">
                        <div class="schedule-subject">
                            <div class="subject-icon break-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            
                            <div class="subject-details">
                                <div class="subject-name">Lunch Break</div>
                                <div class="subject-teacher">-</div>
                            </div>
                        </div>
                        
                        <div class="schedule-location">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            Cafeteria
                        </div>
                    </div>
                </div>
                
                <!-- More periods would follow... -->
            </div>
        </div>
        
        <div class="card">
            <h2 class="card-title">Weekly Timetable</h2>
            
            <div class="timetable-container">
                <table class="timetable">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Monday</th>
                            <th>Tuesday</th>
                            <th>Wednesday</th>
                            <th>Thursday</th>
                            <th>Friday</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 8:00 - 8:45 -->
                        <tr>
                            <td>8:00<br>8:45</td>
                            <td>
                                <div class="timetable-slot slot-math">
                                    <div class="timetable-subject">Mathematics</div>
                                    <div class="timetable-teacher">Mrs. Johnson</div>
                                    <div class="timetable-location">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-science">
                                    <div class="timetable-subject">Science</div>
                                    <div class="timetable-teacher">Mr. Wilson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Lab 102
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-math">
                                    <div class="timetable-subject">Mathematics</div>
                                    <div class="timetable-teacher">Mrs. Johnson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-language">
                                    <div class="timetable-subject">English</div>
                                    <div class="timetable-teacher">Ms. Adams</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-arts">
                                    <div class="timetable-subject">Arts</div>
                                    <div class="timetable-teacher">Mrs. Rivera</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Art Studio
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- 8:50 - 9:35 -->
                        <tr>
                            <td>8:50<br>9:35</td>
                            <td>
                                <div class="timetable-slot slot-science">
                                    <div class="timetable-subject">Science</div>
                                    <div class="timetable-teacher">Mr. Wilson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Lab 102
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-language">
                                    <div class="timetable-subject">English</div>
                                    <div class="timetable-teacher">Ms. Adams</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-history">
                                    <div class="timetable-subject">History</div>
                                    <div class="timetable-teacher">Mr. Peterson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-math">
                                    <div class="timetable-subject">Mathematics</div>
                                    <div class="timetable-teacher">Mrs. Johnson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-science">
                                    <div class="timetable-subject">Science</div>
                                    <div class="timetable-teacher">Mr. Wilson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Lab 102
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- 9:35 - 9:50 (Break) -->
                        <tr>
                            <td>9:35<br>9:50</td>
                            <td colspan="5">
                                <div class="timetable-slot slot-break">
                                    Short Break
                                </div>
                            </td>
                        </tr>
                        
                        <!-- 9:50 - 10:35 -->
                        <tr>
                            <td>9:50<br>10:35</td>
                            <td>
                                <div class="timetable-slot slot-language">
                                    <div class="timetable-subject">English</div>
                                    <div class="timetable-teacher">Ms. Adams</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-history">
                                    <div class="timetable-subject">History</div>
                                    <div class="timetable-teacher">Mr. Peterson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-language">
                                    <div class="timetable-subject">English</div>
                                    <div class="timetable-teacher">Ms. Adams</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-science">
                                    <div class="timetable-subject">Science</div>
                                    <div class="timetable-teacher">Mr. Wilson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Lab 102
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-language">
                                    <div class="timetable-subject">English</div>
                                    <div class="timetable-teacher">Ms. Adams</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- 10:40 - 11:25 -->
                        <tr>
                            <td>10:40<br>11:25</td>
                            <td>
                                <div class="timetable-slot slot-history">
                                    <div class="timetable-subject">History</div>
                                    <div class="timetable-teacher">Mr. Peterson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-math">
                                    <div class="timetable-subject">Mathematics</div>
                                    <div class="timetable-teacher">Mrs. Johnson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-science">
                                    <div class="timetable-subject">Science</div>
                                    <div class="timetable-teacher">Mr. Wilson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Lab 102
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-history">
                                    <div class="timetable-subject">History</div>
                                    <div class="timetable-teacher">Mr. Peterson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="timetable-slot slot-math">
                                    <div class="timetable-subject">Mathematics</div>
                                    <div class="timetable-teacher">Mrs. Johnson</div>
                                    <div class="timetable-location">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        301
                                    </div>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- 11:25 - 12:10 (Lunch) -->
                        <tr>
                            <td>11:25<br>12:10</td>
                            <td colspan="5">
                                <div class="timetable-slot slot-break">
                                    Lunch Break
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Afternoon periods would continue... -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    // Global variables to store timetable data
    let currentTimetable = null;

    // Script to handle class selector
    document.querySelectorAll('.class-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.class-option').forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Function to load student's timetable data
    async function loadStudentTimetable() {
        try {
            // Show loading state
            document.querySelector('.todays-schedule .schedule-list').innerHTML = 
                '<div class="loading-message">Loading your schedule...</div>';
            document.querySelector('.timetable tbody').innerHTML = 
                '<tr><td colspan="6" class="loading-message">Loading timetable data...</td></tr>';
            
            // Use the dedicated student-timetable endpoint
            const response = await axios.get('../../backend/api/student-timetable');
            
            if (response.data.success) {
                const { student, timetable } = response.data;
                
                // Update class selector with student's class
                updateClassSelector(student, timetable);
                
                if (timetable && timetable.periods && timetable.periods.length > 0) {
                    // Store timetable data
                    currentTimetable = timetable;
                    
                    // Render timetable data
                    renderTodaySchedule(currentTimetable);
                    renderWeeklyTimetable(currentTimetable);
                } else {
                    // No timetable found
                    document.querySelector('.todays-schedule .schedule-list').innerHTML = 
                        '<div class="no-classes">No timetable found for your class.</div>';
                    document.querySelector('.timetable tbody').innerHTML = 
                        '<tr><td colspan="6" class="no-classes">No timetable found for your class.</td></tr>';
                }
            } else {
                showError(response.data.error || 'Could not load timetable data');
            }
        } catch (error) {
            console.error('Error loading timetable:', error);
            showError('An error occurred while loading the timetable. Please try again later.');
        }
    }
    
    // Function to update the class selector based on available data
    function updateClassSelector(student, timetable) {
        const classSelector = document.querySelector('.class-selector');
        classSelector.innerHTML = ''; // Clear existing options
        
        // Add option for student's own class (always first and active by default)
        const ownClassOption = document.createElement('div');
        ownClassOption.className = 'class-option active';
        ownClassOption.textContent = `Class ${student.class_name}${student.section_name}`;
        classSelector.appendChild(ownClassOption);
        
        // Add semester options if available - could be added later when multiple timetables are supported
        
        // Add click event handler for class options (mainly for future expansion)
        classSelector.querySelectorAll('.class-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.class-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                // Future functionality can be added here for multiple timetables
            });
        });
    }
    
    // Function to render today's schedule based on the timetable data
    function renderTodaySchedule(timetable) {
        const scheduleList = document.querySelector('.todays-schedule .schedule-list');
        const today = new Date();
        const dayOfWeek = today.getDay(); // 0 (Sunday) to 6 (Saturday)
        
        // Map JavaScript day number to day name as stored in API
        const dayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        const todayName = dayNames[dayOfWeek];
        
        // Get the current time in minutes (since midnight)
        const currentHour = today.getHours();
        const currentMinute = today.getMinutes();
        const currentTimeInMinutes = currentHour * 60 + currentMinute;
        
        // Filter periods for today
        const todayPeriods = timetable.periods.filter(period => 
            period.day_of_week.toLowerCase() === todayName
        );
        
        // Sort periods by start time
        todayPeriods.sort((a, b) => {
            const aTime = convertTimeToMinutes(a.start_time);
            const bTime = convertTimeToMinutes(b.start_time);
            return aTime - bTime;
        });
        
        if (todayPeriods.length === 0) {
            scheduleList.innerHTML = '<div class="no-classes">No classes scheduled for today.</div>';
            return;
        }
        
        // Prepare HTML for schedule items
        let scheduleHTML = '';
        let breakAdded = false;
        
        // Process each period and add breaks if necessary
        for (let i = 0; i < todayPeriods.length; i++) {
            const period = todayPeriods[i];
            const startTime = convertTimeToMinutes(period.start_time);
            const endTime = convertTimeToMinutes(period.end_time);
            
            // Determine if this is the current period
            const isCurrent = currentTimeInMinutes >= startTime && currentTimeInMinutes < endTime;
            
            // Add short break if there's a gap between periods
            if (i > 0 && !breakAdded) {
                const prevPeriod = todayPeriods[i-1];
                const prevEndTime = convertTimeToMinutes(prevPeriod.end_time);
                
                if (startTime - prevEndTime > 5) { // More than 5 minutes gap
                    const breakStartTime = formatTime(prevEndTime);
                    const breakEndTime = formatTime(startTime);
                    
                    // Only add break if it's not lunch (we'll handle lunch separately)
                    if (prevEndTime < 720 && startTime > 780) { // 12:00 - 13:00 is typical lunch
                        // This is likely a lunch break, will be added later
                    } else {
                        scheduleHTML += generateBreakHTML(breakStartTime, breakEndTime, 'Short Break');
                    }
                }
            }
            
            // Generate the period HTML
            scheduleHTML += generatePeriodHTML(period, isCurrent);
            
            // Check if next period is after lunch time, add lunch break
            if (i < todayPeriods.length - 1) {
                const nextPeriod = todayPeriods[i+1];
                const nextStartTime = convertTimeToMinutes(nextPeriod.start_time);
                
                // If gap is more than 30 minutes in typical lunch time
                if (endTime < 780 && nextStartTime > 830 && nextStartTime - endTime > 30) {
                    const lunchStartTime = formatTime(endTime);
                    const lunchEndTime = formatTime(nextStartTime);
                    scheduleHTML += generateBreakHTML(lunchStartTime, lunchEndTime, 'Lunch Break', 'Cafeteria');
                    breakAdded = true;
                } else {
                    breakAdded = false;
                }
            }
        }
        
        scheduleList.innerHTML = scheduleHTML;
    }
    
    // Function to generate HTML for a period in today's schedule
    function generatePeriodHTML(period, isCurrent) {
        // Map subject names to icon classes
        const subjectIconMap = {
            'MATHEMATICS': 'math-icon',
            'ENGLISH': 'language-icon',
            'SCIENCE': 'science-icon',
            'HISTORY': 'history-icon',
            'GEOGRAPHY': 'geography-icon',
            'ART': 'arts-icon',
            'PHYSICAL EDUCATION': 'pe-icon',
            'COMPUTER': 'computer-icon'
        };
        
        // Default icon if subject not in map
        const iconClass = subjectIconMap[period.subject_name] || 'subject-icon';
        
        // Format times
        const startTime = period.start_time.substring(0, 5);
        const endTime = period.end_time.substring(0, 5);
        
        // Get teacher name
        const teacherName = period.teacher_name || 'Not assigned';
        
        // Get room information
        const roomInfo = period.room_number || 'Classroom';
        
        return `
            <div class="schedule-item ${isCurrent ? 'current' : ''}">
                <div class="schedule-time">
                    <div class="time-start">${startTime}</div>
                    <div class="time-end">${endTime}</div>
                </div>
                
                <div class="schedule-content">
                    <div class="schedule-subject">
                        <div class="subject-icon ${iconClass}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        
                        <div class="subject-details">
                            <div class="subject-name">${period.subject_name}</div>
                            <div class="subject-teacher">${teacherName}</div>
                        </div>
                    </div>
                    
                    <div class="schedule-location">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        ${roomInfo}
                    </div>
                </div>
            </div>
        `;
    }
    
    // Function to generate HTML for a break in today's schedule
    function generateBreakHTML(startTime, endTime, breakName, location = 'School Courtyard') {
        return `
            <div class="schedule-item">
                <div class="schedule-time">
                    <div class="time-start">${startTime}</div>
                    <div class="time-end">${endTime}</div>
                </div>
                
                <div class="schedule-content">
                    <div class="schedule-subject">
                        <div class="subject-icon break-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        
                        <div class="subject-details">
                            <div class="subject-name">${breakName}</div>
                            <div class="subject-teacher">-</div>
                        </div>
                    </div>
                    
                    <div class="schedule-location">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        ${location}
                    </div>
                </div>
            </div>
        `;
    }
    
    // Function to render weekly timetable
    function renderWeeklyTimetable(timetable) {
        const timetableBody = document.querySelector('.timetable tbody');
        timetableBody.innerHTML = ''; // Clear existing content
        
        // Organize periods by time slot and day
        const timeSlots = extractTimeSlots(timetable.periods);
        const periodsByDay = organizeByDayAndTime(timetable.periods);
        
        // For each time slot
        timeSlots.forEach(slot => {
            const row = document.createElement('tr');
            
            // Time cell
            const timeCell = document.createElement('td');
            timeCell.innerHTML = `${formatTime(slot.start)}<br>${formatTime(slot.end)}`;
            row.appendChild(timeCell);
            
            // Check if this is a break slot
            if (slot.isBreak) {
                const breakCell = document.createElement('td');
                breakCell.colSpan = 5; // Monday to Friday
                breakCell.innerHTML = `<div class="timetable-slot slot-break">${slot.breakName}</div>`;
                row.appendChild(breakCell);
            } else {
                // Add cells for each day
                ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'].forEach(day => {
                    const dayCell = document.createElement('td');
                    
                    // Find period for this day and time slot
                    const period = findPeriod(periodsByDay[day], slot.start, slot.end);
                    
                    if (period) {
                        // Determine slot CSS class based on subject
                        const slotClass = getSubjectSlotClass(period.subject_name);
                        
                        dayCell.innerHTML = `
                            <div class="timetable-slot ${slotClass}">
                                <div class="timetable-subject">${period.subject_name}</div>
                                <div class="timetable-teacher">${period.teacher_name || 'Not assigned'}</div>
                                <div class="timetable-location">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    ${period.room_number || 'Classroom'}
                                </div>
                            </div>
                        `;
                    } else {
                        dayCell.innerHTML = '<div class="no-class">No Class</div>';
                    }
                    
                    row.appendChild(dayCell);
                });
            }
            
            timetableBody.appendChild(row);
        });
    }
    
    // Function to extract all unique time slots from periods
    function extractTimeSlots(periods) {
        const slots = new Map();
        
        // Extract start and end times from all periods
        periods.forEach(period => {
            const start = convertTimeToMinutes(period.start_time);
            const end = convertTimeToMinutes(period.end_time);
            
            // Use start time as key to avoid duplicates
            if (!slots.has(start)) {
                slots.set(start, { start, end });
            }
        });
        
        // Add break slots (assuming standard breaks)
        // Morning break around 9:30-9:45
        slots.set(9 * 60 + 35, { 
            start: 9 * 60 + 35, 
            end: 9 * 60 + 50, 
            isBreak: true, 
            breakName: 'Short Break' 
        });
        
        // Lunch break around 12:00-12:45
        slots.set(11 * 60 + 25, { 
            start: 11 * 60 + 25, 
            end: 12 * 60 + 10, 
            isBreak: true, 
            breakName: 'Lunch Break' 
        });
        
        // Sort slots by start time
        return Array.from(slots.values()).sort((a, b) => a.start - b.start);
    }
    
    // Function to organize periods by day and time
    function organizeByDayAndTime(periods) {
        const result = {
            'monday': [],
            'tuesday': [],
            'wednesday': [],
            'thursday': [],
            'friday': []
        };
        
        periods.forEach(period => {
            const day = period.day_of_week.toLowerCase();
            if (result[day]) {
                result[day].push(period);
            }
        });
        
        return result;
    }
    
    // Function to find a period that matches a time slot
    function findPeriod(dayPeriods, startTime, endTime) {
        if (!dayPeriods) return null;
        
        return dayPeriods.find(period => {
            const periodStart = convertTimeToMinutes(period.start_time);
            const periodEnd = convertTimeToMinutes(period.end_time);
            
            return periodStart === startTime && periodEnd === endTime;
        });
    }
    
    // Function to get CSS class for subject
    function getSubjectSlotClass(subjectName) {
        const subjectMap = {
            'MATHEMATICS': 'slot-math',
            'ENGLISH': 'slot-language',
            'SCIENCE': 'slot-science',
            'HISTORY': 'slot-history',
            'GEOGRAPHY': 'slot-geography',
            'ART': 'slot-arts',
            'PHYSICAL EDUCATION': 'slot-pe',
            'COMPUTER': 'slot-computer'
        };
        
        return subjectMap[subjectName] || 'slot-default';
    }
    
    // Helper function to convert HH:MM:SS to minutes since midnight
    function convertTimeToMinutes(timeStr) {
        const [hours, minutes] = timeStr.substring(0, 5).split(':').map(Number);
        return hours * 60 + minutes;
    }
    
    // Helper function to format minutes to HH:MM
    function formatTime(minutes) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        return `${hours.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
    }
    
    // Function to display error messages
    function showError(message) {
        document.querySelector('.todays-schedule .schedule-list').innerHTML = 
            `<div class="error-message">${message}</div>`;
        document.querySelector('.timetable tbody').innerHTML = 
            `<tr><td colspan="6" class="error-message">${message}</td></tr>`;
    }
    
    // Highlight current day in timetable
    function highlightCurrentDay() {
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const today = new Date().getDay();
        if (today > 0 && today < 6) { // Monday to Friday
            const cells = document.querySelectorAll('.timetable th');
            cells.forEach((cell, index) => {
                if (index > 0 && cell.textContent.trim() === dayNames[today]) {
                    cell.style.backgroundColor = '#f0fdf4';
                    cell.style.borderBottom = '2px solid #10b981';
                }
            });
        }
    }
    
    // Add styles for error and loading messages
    const style = document.createElement('style');
    style.innerHTML = `
        .loading-message {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
        
        .error-message {
            text-align: center;
            padding: 20px;
            color: #ef4444;
            font-weight: 500;
        }
        
        .no-classes {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-style: italic;
        }
        
        .no-class {
            text-align: center;
            color: #9ca3af;
            font-size: 0.875rem;
            padding: 10px;
        }
    `;
    document.head.appendChild(style);

    // Initialize everything when document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Load timetable data
        loadStudentTimetable();
        
        // Highlight current day
        highlightCurrentDay();
    });
</script>
</body>
</html>