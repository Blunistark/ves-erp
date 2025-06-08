<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Online Class Management</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/online-classes.css">
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
        <h1 class="header-title">Online Class Management</h1>
        <span class="header-subtitle">Manage online class schedules and links</span>
    </header>

    <main class="dashboard-content">
        <!-- Statistics Overview -->
        <section class="stats-grid">
            <div class="stat-card">
                <h3 class="stat-title">Total Classes</h3>
                <div class="stat-value">42</div>
                <div class="stat-trend trend-up">
                    <svg class="trend-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    <span>12% increase</span>
                </div>
            </div>
            <div class="stat-card">
                <h3 class="stat-title">Upcoming Classes</h3>
                <div class="stat-value">8</div>
                <div class="stat-trend trend-neutral">
                    <svg class="trend-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                    </svg>
                    <span>Same as last week</span>
                </div>
            </div>
            <div class="stat-card">
                <h3 class="stat-title">Average Attendance</h3>
                <div class="stat-value">87%</div>
                <div class="stat-trend trend-up">
                    <svg class="trend-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    <span>5% increase</span>
                </div>
            </div>
            <div class="stat-card">
                <h3 class="stat-title">Total Students</h3>
                <div class="stat-value">124</div>
                <div class="stat-trend trend-up">
                    <svg class="trend-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                    </svg>
                    <span>3 new students</span>
                </div>
            </div>
        </section>

        <!-- Upcoming Class Reminder -->
        <section class="class-reminder">
            <div class="reminder-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <div class="reminder-content">
                <h3 class="reminder-title">Your next class is starting soon</h3>
                <p class="reminder-text">Physics - Class 10-A is scheduled to begin in <span class="reminder-time">45 minutes</span>. <a href="#" onclick="joinClass(3)">Click here</a> to join the class early and prepare your materials.</p>
            </div>
            <button class="btn btn-primary" onclick="joinClass(3)">Join Now</button>
        </section>

        <!-- Filter and Controls -->
        <section class="filter-bar">
            <div class="filter-group">
                <div class="search-container">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" placeholder="Search classes..." class="search-input" id="classSearch">
                </div>
                <div class="select-container">
                    <select class="filter-select" id="classFilter">
                        <option value="all">All Status</option>
                        <option value="live">Live</option>
                        <option value="upcoming">Upcoming</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <svg class="select-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
            <button class="btn btn-primary" onclick="showCreateClassModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Schedule New Class
            </button>
        </section>

        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tab active" onclick="changeTab(this, 'all-classes')">All Classes</div>
            <div class="tab" onclick="changeTab(this, 'my-schedule')">My Schedule</div>
            <div class="tab" onclick="changeTab(this, 'calendar-view')">Calendar View</div>
        </div>

        <!-- Online Classes Grid -->
        <section class="online-classes-grid" id="classes-container">
            <!-- Live Online Class Example -->
            <div class="online-class-card" data-status="live">
                <div class="class-card-header">
                    <span class="class-status status-live">LIVE</span>
                    <h3 class="class-title">Mathematics</h3>
                    <p class="class-subtitle">Class 9-A</p>
                </div>
                <div class="class-card-body">
                    <ul class="class-detail-list">
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Time</span>
                                <span class="detail-value">March 15, 2025 • 09:00 - 10:30 AM</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Topic</span>
                                <span class="detail-value">Quadratic Equations</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Attendance</span>
                                <span class="detail-value">26/28 students joined</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Platform</span>
                                <span class="detail-value">
                                    <span class="platform-badge platform-zoom">Zoom</span>
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="class-card-footer">
                    <div class="attendees-list">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Attendee" class="attendee-avatar">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Attendee" class="attendee-avatar">
                        <img src="https://randomuser.me/api/portraits/men/86.jpg" alt="Attendee" class="attendee-avatar">
                        <span class="attendee-count">+23</span>
                    </div>
                    <button class="btn btn-primary btn-sm" onclick="joinClass(1)">Join Now</button>
                </div>
            </div>

            <!-- Upcoming Online Class Example -->
            <div class="online-class-card" data-status="upcoming">
                <div class="class-card-header">
                    <span class="class-status status-upcoming">UPCOMING</span>
                    <h3 class="class-title">Physics</h3>
                    <p class="class-subtitle">Class 10-A</p>
                </div>
                <div class="class-card-body">
                    <ul class="class-detail-list">
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Time</span>
                                <span class="detail-value">March 15, 2025 • 11:00 AM - 12:30 PM</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Topic</span>
                                <span class="detail-value">Laws of Motion</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Students</span>
                                <span class="detail-value">30 students enrolled</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Platform</span>
                                <span class="detail-value">
                                    <span class="platform-badge platform-teams">MS Teams</span>
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="class-card-footer">
                    <div class="class-time-badge">
                        <svg class="time-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Starts in 45 minutes
                    </div>
                    <button class="btn btn-secondary btn-sm" onclick="editClass(2)">Edit</button>
                </div>
            </div>

            <!-- Completed Online Class Example -->
            <div class="online-class-card" data-status="completed">
                <div class="class-card-header">
                    <span class="class-status status-completed">COMPLETED</span>
                    <h3 class="class-title">Biology</h3>
                    <p class="class-subtitle">Class 10-B</p>
                </div>
                <div class="class-card-body">
                    <ul class="class-detail-list">
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Time</span>
                                <span class="detail-value">March 14, 2025 • 09:00 - 10:30 AM</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Topic</span>
                                <span class="detail-value">Cell Structure and Functions</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Attendance</span>
                                <span class="detail-value">28/32 students attended</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Platform</span>
                                <span class="detail-value">
                                    <span class="platform-badge platform-meet">Google Meet</span>
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="class-card-footer">
                    <div class="attendees-list">
                        <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Attendee" class="attendee-avatar">
                        <img src="https://randomuser.me/api/portraits/men/45.jpg" alt="Attendee" class="attendee-avatar">
                        <img src="https://randomuser.me/api/portraits/women/22.jpg" alt="Attendee" class="attendee-avatar">
                        <span class="attendee-count">+25</span>
                    </div>
                    <button class="btn btn-secondary btn-sm" onclick="viewClassReport(3)">View Report</button>
                </div>
            </div>

            <!-- Cancelled Online Class Example -->
            <div class="online-class-card" data-status="cancelled">
                <div class="class-card-header">
                    <span class="class-status status-cancelled">CANCELLED</span>
                    <h3 class="class-title">Chemistry</h3>
                    <p class="class-subtitle">Class 9-B</p>
                </div>
                <div class="class-card-body">
                    <ul class="class-detail-list">
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Time</span>
                                <span class="detail-value">March 14, 2025 • 02:00 - 03:30 PM</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Topic</span>
                                <span class="detail-value">Periodic Table of Elements</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Students</span>
                                <span class="detail-value">26 students notified</span>
                            </div>
                        </li>
                        <li class="class-detail-item">
                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                            </svg>
                            <div class="detail-content">
                                <span class="detail-label">Cancellation Reason</span>
                                <span class="detail-value">Technical difficulties with the video conferencing platform</span>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="class-card-footer">
                    <div class="class-time-badge">
                        <svg class="time-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Rescheduled to March 16
                    </div>
                    <button class="btn btn-secondary btn-sm" onclick="rescheduleClass(4)">Reschedule</button>
                </div>
            </div>
        </section>

        <!-- Calendar View (Hidden initially) -->
        <section id="calendar-view" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Class Schedule Calendar</h2>
                    <div class="calendar-nav">
                        <button class="calendar-btn" onclick="prevMonth()">
                            <svg class="calendar-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <span class="calendar-month">March 2025</span>
                        <button class="calendar-btn" onclick="nextMonth()">
                            <svg class="calendar-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="calendar-grid">
                        <!-- Day Headers -->
                        <div class="calendar-day-header">Sun</div>
                        <div class="calendar-day-header">Mon</div>
                        <div class="calendar-day-header">Tue</div>
                        <div class="calendar-day-header">Wed</div>
                        <div class="calendar-day-header">Thu</div>
                        <div class="calendar-day-header">Fri</div>
                        <div class="calendar-day-header">Sat</div>
                        
                        <!-- Week 1 -->
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">23</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">24</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">25</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">26</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">27</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">28</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">1</div>
                        </div>
                        
                        <!-- Week 2 -->
                        <div class="calendar-day">
                            <div class="calendar-day-number">2</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">3</div>
                            <div class="calendar-event event-completed" onclick="showClassDetails(5)">
                                Math - 9A (9:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">4</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">5</div>
                            <div class="calendar-event event-completed" onclick="showClassDetails(6)">
                                Physics - 10A (11:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">6</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">7</div>
                            <div class="calendar-event event-completed" onclick="showClassDetails(7)">
                                Bio - 10B (9:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">8</div>
                        </div>
                        
                        <!-- Week 3 -->
                        <div class="calendar-day">
                            <div class="calendar-day-number">9</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">10</div>
                            <div class="calendar-event event-completed" onclick="showClassDetails(8)">
                                Math - 9A (9:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">11</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">12</div>
                            <div class="calendar-event event-completed" onclick="showClassDetails(9)">
                                Physics - 10A (11:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">13</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">14</div>
                            <div class="calendar-event event-completed" onclick="showClassDetails(3)">
                                Bio - 10B (9:00 AM)
                            </div>
                            <div class="calendar-event event-completed" onclick="showClassDetails(4)">
                                Chem - 9B (2:00 PM)
                            </div>
                        </div>
                        <div class="calendar-day today">
                            <div class="calendar-day-number">15</div>
                            <div class="calendar-event event-live" onclick="showClassDetails(1)">
                                Math - 9A (9:00 AM)
                            </div>
                            <div class="calendar-event event-upcoming" onclick="showClassDetails(2)">
                                Physics - 10A (11:00 AM)
                            </div>
                        </div>
                        
                        <!-- Week 4 (and remaining weeks would follow the same pattern) -->
                        <div class="calendar-day">
                            <div class="calendar-day-number">16</div>
                            <div class="calendar-event event-upcoming" onclick="showClassDetails(10)">
                                Chem - 9B (10:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">17</div>
                            <div class="calendar-event event-upcoming" onclick="showClassDetails(11)">
                                Math - 9A (9:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">18</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">19</div>
                            <div class="calendar-event event-upcoming" onclick="showClassDetails(12)">
                                Physics - 10A (11:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">20</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">21</div>
                            <div class="calendar-event event-upcoming" onclick="showClassDetails(13)">
                                Bio - 10B (9:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">22</div>
                        </div>
                        
                        <!-- Remaining days of the month -->
                        <div class="calendar-day">
                            <div class="calendar-day-number">23</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">24</div>
                            <div class="calendar-event event-upcoming" onclick="showClassDetails(14)">
                                Math - 9A (9:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">25</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">26</div>
                            <div class="calendar-event event-upcoming" onclick="showClassDetails(15)">
                                Physics - 10A (11:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">27</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">28</div>
                            <div class="calendar-event event-upcoming" onclick="showClassDetails(16)">
                                Bio - 10B (9:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">29</div>
                        </div>
                        
                        <!-- Final days -->
                        <div class="calendar-day">
                            <div class="calendar-day-number">30</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">31</div>
                            <div class="calendar-event event-upcoming" onclick="showClassDetails(17)">
                                Math - 9A (9:00 AM)
                            </div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">1</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">2</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">3</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">4</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">5</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Empty State (Hidden by default) -->
        <div class="empty-state" id="emptyState" style="display: none;">
            <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h3 class="empty-title">No Classes Found</h3>
            <p class="empty-description">There are no classes matching your current filters or search criteria.</p>
            <button class="btn btn-primary" onclick="showCreateClassModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Schedule New Class
            </button>
        </div>
    </main>
</div>

<!-- Create/Edit Class Modal -->
<div class="modal-overlay" id="classFormModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title" id="classModalTitle">Schedule New Online Class</h3>
            <button class="modal-close" onclick="hideClassModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="classForm">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Class Name <span class="form-required">*</span></label>
                        <select class="form-select" id="className" required>
                            <option value="">Select Subject</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Physics">Physics</option>
                            <option value="Chemistry">Chemistry</option>
                            <option value="Biology">Biology</option>
                            <option value="English">English</option>
                            <option value="History">History</option>
                            <option value="Geography">Geography</option>
                            <option value="Computer Science">Computer Science</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Class/Section <span class="form-required">*</span></label>
                        <select class="form-select" id="classSection" required>
                            <option value="">Select Class</option>
                            <option value="Class 9-A">Class 9-A</option>
                            <option value="Class 9-B">Class 9-B</option>
                            <option value="Class 10-A">Class 10-A</option>
                            <option value="Class 10-B">Class 10-B</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date <span class="form-required">*</span></label>
                        <input type="date" class="form-input" id="classDate" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Platform <span class="form-required">*</span></label>
                        <select class="form-select" id="classPlatform" required>
                            <option value="">Select Platform</option>
                            <option value="zoom">Zoom</option>
                            <option value="teams">Microsoft Teams</option>
                            <option value="meet">Google Meet</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Time <span class="form-required">*</span></label>
                        <input type="time" class="form-input" id="classStartTime" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time <span class="form-required">*</span></label>
                        <input type="time" class="form-input" id="classEndTime" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Topic <span class="form-required">*</span></label>
                    <input type="text" class="form-input" id="classTopic" placeholder="Enter class topic" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meeting Link <span class="form-required">*</span></label>
                    <input type="url" class="form-input" id="classMeetingLink" placeholder="https://" required>
                    <span class="form-hint">Provide the full URL for joining the online class</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meeting ID</label>
                    <input type="text" class="form-input" id="classMeetingId" placeholder="Meeting ID (if applicable)">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meeting Password</label>
                    <input type="text" class="form-input" id="classMeetingPassword" placeholder="Password (if applicable)">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea class="form-textarea" id="classDescription" placeholder="Enter additional details, requirements, or instructions for students"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Notifications</label>
                    <div class="form-check">
                        <input type="checkbox" id="notifyStudents" class="form-checkbox" checked>
                        <label for="notifyStudents" class="form-check-label">Send notification to students</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="sendReminder" class="form-checkbox" checked>
                        <label for="sendReminder" class="form-check-label">Send reminder 15 minutes before class</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="recordClass" class="form-checkbox">
                        <label for="recordClass" class="form-check-label">Record this class</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="hideClassModal()">Cancel</button>
            <button class="btn btn-primary" id="saveClassBtn" onclick="saveClass()">Schedule Class</button>
        </div>
    </div>
</div>

<!-- Class Details Modal -->
<div class="modal-overlay" id="classDetailsModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Class Details</h3>
            <button class="modal-close" onclick="hideClassDetailsModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="classDetailsContent">
                <div class="form-group">
                    <label class="form-label">Class Name</label>
                    <p id="detailClassName">Mathematics</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Class/Section</label>
                    <p id="detailClassSection">Class 9-A</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date & Time</label>
                    <p id="detailClassDateTime">March 15, 2025 • 09:00 - 10:30 AM</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Topic</label>
                    <p id="detailClassTopic">Quadratic Equations</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Platform</label>
                    <p id="detailClassPlatform"><span class="platform-badge platform-zoom">Zoom</span></p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meeting Link</label>
                    <p><a href="#" id="detailClassLink" target="_blank">https://zoom.us/j/1234567890</a></p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meeting ID</label>
                    <p id="detailClassMeetingId">123 456 7890</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meeting Password</label>
                    <p id="detailClassPassword">123456</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <p id="detailClassDescription">In this class, we will cover solving quadratic equations using multiple methods including factoring, the quadratic formula, and completing the square. Please review your notes on factoring before class.</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <p id="detailClassStatus"><span class="status-badge status-live">Live</span></p>
                </div>
                
                <div class="form-group" id="attendanceSection">
                    <label class="form-label">Attendance</label>
                    <p id="detailClassAttendance">26/28 students (92.8%)</p>
                </div>
            </div>
        </div>
        <div class="modal-footer" id="detailClassButtons">
            <button class="btn btn-secondary" onclick="hideClassDetailsModal()">Close</button>
            <button class="btn btn-danger" id="cancelClassBtn" onclick="confirmCancelClass()">Cancel Class</button>
            <button class="btn btn-primary" id="joinClassBtn" onclick="joinClass(1)">Join Class</button>
        </div>
    </div>
</div>

<script>
    // Function to toggle sidebar
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const dashboardContainer = document.querySelector('.dashboard-container');
        const body = document.querySelector('body');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-open');
        dashboardContainer.classList.toggle('sidebar-open');
    }
    
    // Function to handle tab changes
    function changeTab(tab, tabId) {
        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(t => t.classList.remove('active'));
        
        // Add active class to selected tab
        tab.classList.add('active');
        
        // Hide all tab contents
        document.getElementById('classes-container').style.display = 'none';
        document.getElementById('calendar-view').style.display = 'none';
        
        // Show selected tab content
        if (tabId === 'all-classes') {
            document.getElementById('classes-container').style.display = 'grid';
            filterClasses(); // Make sure filters are applied
        } else if (tabId === 'my-schedule') {
            document.getElementById('classes-container').style.display = 'grid';
            // Filter to show only classes for current teacher
            filterClasses('my'); 
        } else if (tabId === 'calendar-view') {
            document.getElementById('calendar-view').style.display = 'block';
        }
    }
    
    // Function to filter classes
    function filterClasses(teacher = 'all') {
        const statusFilter = document.getElementById('classFilter').value;
        const searchText = document.getElementById('classSearch').value.toLowerCase();
        
        const classCards = document.querySelectorAll('.online-class-card');
        let matchFound = false;
        
        classCards.forEach(card => {
            const cardStatus = card.getAttribute('data-status');
            const cardTitle = card.querySelector('.class-title').textContent.toLowerCase();
            const cardSubtitle = card.querySelector('.class-subtitle').textContent.toLowerCase();
            
            // Filter based on status
            const statusMatch = statusFilter === 'all' || cardStatus === statusFilter;
            
            // Filter based on search text
            const searchMatch = searchText === '' || 
                cardTitle.includes(searchText) || 
                cardSubtitle.includes(searchText);
            
            // Filter based on teacher (for "My Schedule" tab)
            // In a real app, you would use actual teacher ID to filter
            const teacherMatch = teacher === 'all' || true; // Assuming all cards belong to current teacher
            
            if (statusMatch && searchMatch && teacherMatch) {
                card.style.display = '';
                matchFound = true;
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show empty state if no matches found
        const emptyState = document.getElementById('emptyState');
        emptyState.style.display = matchFound ? 'none' : 'flex';
    }
    
    // Function to show class form modal for creating new class
    function showCreateClassModal() {
        const modal = document.getElementById('classFormModal');
        document.getElementById('classModalTitle').textContent = 'Schedule New Online Class';
        document.getElementById('saveClassBtn').textContent = 'Schedule Class';
        
        // Reset form
        document.getElementById('classForm').reset();
        
        // Set default date to today
        const today = new Date();
        const formattedDate = today.toISOString().substr(0, 10);
        document.getElementById('classDate').value = formattedDate;
        
        // Show modal
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    // Function to show class form modal for editing class
    function editClass(classId) {
        const modal = document.getElementById('classFormModal');
        document.getElementById('classModalTitle').textContent = 'Edit Online Class';
        document.getElementById('saveClassBtn').textContent = 'Update Class';
        
        // In a real application, you would fetch the class data
        // For this example, we'll pre-fill with example data
        if (classId === 2) {
            document.getElementById('className').value = 'Physics';
            document.getElementById('classSection').value = 'Class 10-A';
            document.getElementById('classDate').value = '2025-03-15';
            document.getElementById('classPlatform').value = 'teams';
            document.getElementById('classStartTime').value = '11:00';
            document.getElementById('classEndTime').value = '12:30';
            document.getElementById('classTopic').value = 'Laws of Motion';
            document.getElementById('classMeetingLink').value = 'https://teams.microsoft.com/l/meetup-join/meeting_id123';
            document.getElementById('classMeetingId').value = 'MeetingID123';
            document.getElementById('classMeetingPassword').value = 'Pass123';
            document.getElementById('classDescription').value = 'In this class, we will cover Newton\'s laws of motion. Please review the textbook chapter 5 before class.';
            document.getElementById('notifyStudents').checked = true;
            document.getElementById('sendReminder').checked = true;
            document.getElementById('recordClass').checked = false;
        }
        
        // Show modal
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    // Function to hide class form modal
    function hideClassModal() {
        const modal = document.getElementById('classFormModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Function to save class (create or update)
    function saveClass() {
        // Get form values
        const className = document.getElementById('className').value;
        const classSection = document.getElementById('classSection').value;
        const classDate = document.getElementById('classDate').value;
        const classPlatform = document.getElementById('classPlatform').value;
        
        // Validate form
        if (!className || !classSection || !classDate || !classPlatform) {
            alert('Please fill all required fields.');
            return;
        }
        
        // In a real application, this would submit the form data via AJAX
        const isEdit = document.getElementById('classModalTitle').textContent.includes('Edit');
        
        if (isEdit) {
            alert('Class has been updated successfully!');
        } else {
            alert('New class has been scheduled successfully!');
        }
        
        hideClassModal();
        
        // Optionally refresh the classes list
        // location.reload();
    }
    
    // Function to show class details modal
    function showClassDetails(classId) {
        const modal = document.getElementById('classDetailsModal');
        
        // In a real application, you would fetch the class details
        // For this example, we'll update with pre-defined data
        if (classId === 1) {
            document.getElementById('detailClassName').textContent = 'Mathematics';
            document.getElementById('detailClassSection').textContent = 'Class 9-A';
            document.getElementById('detailClassDateTime').textContent = 'March 15, 2025 • 09:00 - 10:30 AM';
            document.getElementById('detailClassTopic').textContent = 'Quadratic Equations';
            document.getElementById('detailClassPlatform').innerHTML = '<span class="platform-badge platform-zoom">Zoom</span>';
            document.getElementById('detailClassLink').textContent = 'https://zoom.us/j/1234567890';
            document.getElementById('detailClassLink').href = 'https://zoom.us/j/1234567890';
            document.getElementById('detailClassMeetingId').textContent = '123 456 7890';
            document.getElementById('detailClassPassword').textContent = '123456';
            document.getElementById('detailClassDescription').textContent = 'In this class, we will cover solving quadratic equations using multiple methods including factoring, the quadratic formula, and completing the square. Please review your notes on factoring before class.';
            document.getElementById('detailClassStatus').innerHTML = '<span class="status-badge status-live">Live</span>';
            document.getElementById('attendanceSection').style.display = 'block';
            document.getElementById('detailClassAttendance').textContent = '26/28 students (92.8%)';
            
            // Update buttons
            document.getElementById('cancelClassBtn').style.display = 'none';
            document.getElementById('joinClassBtn').style.display = 'block';
        } else if (classId === 2) {
            document.getElementById('detailClassName').textContent = 'Physics';
            document.getElementById('detailClassSection').textContent = 'Class 10-A';
            document.getElementById('detailClassDateTime').textContent = 'March 15, 2025 • 11:00 AM - 12:30 PM';
            document.getElementById('detailClassTopic').textContent = 'Laws of Motion';
            document.getElementById('detailClassPlatform').innerHTML = '<span class="platform-badge platform-teams">MS Teams</span>';
            document.getElementById('detailClassLink').textContent = 'https://teams.microsoft.com/l/meetup-join/meeting_id123';
            document.getElementById('detailClassLink').href = 'https://teams.microsoft.com/l/meetup-join/meeting_id123';
            document.getElementById('detailClassMeetingId').textContent = 'MeetingID123';
            document.getElementById('detailClassPassword').textContent = 'Pass123';
            document.getElementById('detailClassDescription').textContent = 'In this class, we will cover Newton\'s laws of motion. Please review the textbook chapter 5 before class.';
            document.getElementById('detailClassStatus').innerHTML = '<span class="status-badge status-upcoming">Upcoming</span>';
            document.getElementById('attendanceSection').style.display = 'none';
            
            // Update buttons
            document.getElementById('cancelClassBtn').style.display = 'block';
            document.getElementById('joinClassBtn').style.display = 'block';
        }
        
        // Show modal
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    // Function to hide class details modal
    function hideClassDetailsModal() {
        const modal = document.getElementById('classDetailsModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Function to join a class
    function joinClass(classId) {
        // In a real application, this would redirect to the meeting URL
        // For this example, we'll just show an alert and simulate opening a new tab
        
        let meetingUrl = '';
        
        if (classId === 1) {
            meetingUrl = 'https://zoom.us/j/1234567890';
        } else if (classId === 2 || classId === 3) {
            meetingUrl = 'https://teams.microsoft.com/l/meetup-join/meeting_id123';
        }
        
        // Alert user about joining
        alert('Joining class. You will be redirected to the meeting platform.');
        
        // Open in new tab (in a real app)
        //window.open(meetingUrl, '_blank');
        
        // Or navigate directly (replacing current window)
        //window.location.href = meetingUrl;
    }
    
    // Function to view class report
    function viewClassReport(classId) {
        // In a real application, this would open a detailed report page
        alert('Viewing report for class ID: ' + classId);
    }
    
    // Function to reschedule a cancelled class
    function rescheduleClass(classId) {
        // Show the class form modal with pre-filled data
        editClass(classId);
    }
    
    // Function to confirm class cancellation
    function confirmCancelClass() {
        if (confirm('Are you sure you want to cancel this class? Students will be notified of the cancellation.')) {
            alert('Class has been cancelled successfully. Students have been notified.');
            hideClassDetailsModal();
        }
    }
    
    // Calendar navigation functions
    function prevMonth() {
        // In a real application, this would update the calendar to show the previous month
        alert('Navigating to previous month');
    }
    
    function nextMonth() {
        // In a real application, this would update the calendar to show the next month
        alert('Navigating to next month');
    }
    
    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Add click event listeners to all navigation links
        const navLinks = document.querySelectorAll('a[href]:not([href^="#"])');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.href.includes(window.location.hostname)) {
                    e.preventDefault();
                    
                    // Add exit animation class
                    document.body.classList.add('fade-out');
                    
                    // Navigate to new page after animation completes
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 500); // Match animation duration
                }
            });
        });
        
        // Initialize search functionality
        const searchInput = document.getElementById('classSearch');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterClasses();
            });
        }
        
        // Initialize status filter
        const statusFilter = document.getElementById('classFilter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                filterClasses();
            });
        }
        
        // Apply animation delay to class cards
        const cards = document.querySelectorAll('.online-class-card');
        cards.forEach((card, index) => {
            card.style.animationDelay = `${0.1 * index}s`;
        });
    });
</script>
</body>
</html>