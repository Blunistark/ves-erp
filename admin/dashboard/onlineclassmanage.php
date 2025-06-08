<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Online Classes Management</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/onlineclassmanage.css">
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
            <h1 class="header-title">Online Classes Management</h1>
            <span class="header-date"><?php echo date('F j, Y'); ?></span>
        </header>

        <main class="dashboard-content">
            <!-- Tabs Navigation -->
            <div class="tabs-container">
                <div class="tabs">
                    <div class="tab active" data-tab="schedule-class">Schedule Class</div>
                    <div class="tab" data-tab="manage-sessions">Manage Sessions</div>
                    <div class="tab" data-tab="session-reports">Session Reports</div>
                </div>
            </div>

            <!-- Schedule Class Tab -->
            <div class="tab-content active" id="schedule-class">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Schedule New Online Class</h2>
                            <p class="card-subtitle">Create a new virtual class session for students</p>
                        </div>
                    </div>
                    <form id="schedule-class-form" action="process_class_schedule.php" method="post">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="class-title" class="form-label">Class Title *</label>
                                        <input type="text" id="class-title" name="class_title" class="form-control" placeholder="Enter class title" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="subject" class="form-label">Subject *</label>
                                        <select id="subject" name="subject" class="form-select" required>
                                            <option value="">Select subject</option>
                                            <option value="mathematics">Mathematics</option>
                                            <option value="science">Science</option>
                                            <option value="english">English</option>
                                            <option value="history">History</option>
                                            <option value="geography">Geography</option>
                                            <option value="physics">Physics</option>
                                            <option value="chemistry">Chemistry</option>
                                            <option value="biology">Biology</option>
                                            <option value="computer_science">Computer Science</option>
                                            <option value="physical_education">Physical Education</option>
                                            <option value="art">Art</option>
                                            <option value="music">Music</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="grade-class" class="form-label">Grade/Class *</label>
                                        <select id="grade-class" name="grade_class" class="form-select" required>
                                            <option value="">Select grade/class</option>
                                            <option value="1A">Class 1A</option>
                                            <option value="1B">Class 1B</option>
                                            <option value="2A">Class 2A</option>
                                            <option value="2B">Class 2B</option>
                                            <option value="3A">Class 3A</option>
                                            <option value="3B">Class 3B</option>
                                            <option value="4A">Class 4A</option>
                                            <option value="4B">Class 4B</option>
                                            <option value="5A">Class 5A</option>
                                            <option value="5B">Class 5B</option>
                                            <option value="6A">Class 6A</option>
                                            <option value="6B">Class 6B</option>
                                            <option value="7A">Class 7A</option>
                                            <option value="7B">Class 7B</option>
                                            <option value="8A">Class 8A</option>
                                            <option value="8B">Class 8B</option>
                                            <option value="9A">Class 9A</option>
                                            <option value="9B">Class 9B</option>
                                            <option value="10A">Class 10A</option>
                                            <option value="10B">Class 10B</option>
                                            <option value="11A">Class 11A</option>
                                            <option value="11B">Class 11B</option>
                                            <option value="12A">Class 12A</option>
                                            <option value="12B">Class 12B</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="teacher" class="form-label">Teacher *</label>
                                        <select id="teacher" name="teacher" class="form-select" required>
                                            <option value="">Select teacher</option>
                                            <option value="1">John Smith (Mathematics)</option>
                                            <option value="2">Emily Johnson (Science)</option>
                                            <option value="3">Michael Brown (English)</option>
                                            <option value="4">Sarah Wilson (History)</option>
                                            <option value="5">Robert Davis (Physics)</option>
                                            <option value="6">Jennifer Taylor (Chemistry)</option>
                                            <option value="7">David Miller (Biology)</option>
                                            <option value="8">Amanda White (Computer Science)</option>
                                            <option value="9">James Anderson (Geography)</option>
                                            <option value="10">Lisa Thomas (Art)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="class-date" class="form-label">Class Date *</label>
                                        <input type="date" id="class-date" name="class_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="class-time" class="form-label">Class Time *</label>
                                        <input type="time" id="class-time" name="class_time" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="duration" class="form-label">Duration (minutes) *</label>
                                        <select id="duration" name="duration" class="form-select" required>
                                            <option value="30">30 minutes</option>
                                            <option value="45">45 minutes</option>
                                            <option value="60" selected>60 minutes (1 hour)</option>
                                            <option value="90">90 minutes (1.5 hours)</option>
                                            <option value="120">120 minutes (2 hours)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="recurring-schedule" class="form-label">Recurring Schedule</label>
                                <div class="form-check">
                                    <input type="checkbox" id="recurring-schedule" name="recurring_schedule" class="form-check-input" value="1">
                                    <label for="recurring-schedule" class="form-check-label">Make this a recurring class</label>
                                </div>
                                <div id="recurring-options-container" style="display: none; margin-top: 0.75rem;">
                                    <div class="recurring-options">
                                        <div class="recurring-option" data-value="daily">Daily</div>
                                        <div class="recurring-option selected" data-value="weekly">Weekly</div>
                                        <div class="recurring-option" data-value="biweekly">Bi-weekly</div>
                                        <div class="recurring-option" data-value="monthly">Monthly</div>
                                    </div>
                                    <div class="form-row" style="margin-top: 1rem;">
                                        <div class="form-col">
                                            <label for="recurring-end-date" class="form-label">End Date</label>
                                            <input type="date" id="recurring-end-date" name="recurring_end_date" class="form-control">
                                        </div>
                                        <div class="form-col">
                                            <label for="recurring-count" class="form-label">Number of Sessions</label>
                                            <input type="number" id="recurring-count" name="recurring_count" class="form-control" min="2" max="52" value="10">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="class-description" class="form-label">Class Description</label>
                                <textarea id="class-description" name="class_description" class="form-control" placeholder="Enter class details, topics to be covered, etc."></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Meeting Platform *</label>
                                <div class="platform-options">
                                    <div class="platform-option selected" data-platform="zoom">
                                        <img src="../../assets/img/zoom-logo.png" alt="Zoom" class="platform-option-icon">
                                        <div class="platform-option-name">Zoom</div>
                                    </div>
                                    <div class="platform-option" data-platform="google-meet">
                                        <img src="../../assets/img/meet-logo.png" alt="Google Meet" class="platform-option-icon">
                                        <div class="platform-option-name">Google Meet</div>
                                    </div>
                                    <div class="platform-option" data-platform="microsoft-teams">
                                        <img src="../../assets/img/teams-logo.png" alt="Microsoft Teams" class="platform-option-icon">
                                        <div class="platform-option-name">MS Teams</div>
                                    </div>
                                    <div class="platform-option" data-platform="webex">
                                        <img src="../../assets/img/webex-logo.png" alt="Webex" class="platform-option-icon">
                                        <div class="platform-option-name">Webex</div>
                                    </div>
                                    <div class="platform-option" data-platform="other">
                                        <div class="platform-option-icon" style="background-color: #f3f4f6; display: flex; justify-content: center; align-items: center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                                <line x1="8" y1="12" x2="16" y2="12"></line>
                                            </svg>
                                        </div>
                                        <div class="platform-option-name">Other</div>
                                    </div>
                                </div>
                                <input type="hidden" id="selected-platform" name="platform" value="zoom">
                            </div>

                            <div id="platform-details-container">
                                <div id="zoom-details" class="platform-details">
                                    <div class="form-row">
                                        <div class="form-col">
                                            <div class="form-group">
                                                <label for="meeting-id" class="form-label">Meeting ID</label>
                                                <input type="text" id="meeting-id" name="meeting_id" class="form-control" placeholder="Enter meeting ID">
                                            </div>
                                        </div>
                                        <div class="form-col">
                                            <div class="form-group">
                                                <label for="meeting-password" class="form-label">Password</label>
                                                <input type="text" id="meeting-password" name="meeting_password" class="form-control" placeholder="Enter meeting password">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="meeting-link" class="form-label">Meeting Link</label>
                                        <input type="url" id="meeting-link" name="meeting_link" class="form-control" placeholder="Enter meeting URL">
                                    </div>
                                    <div class="form-group">
                                        <button type="button" id="generate-meeting" class="button button-outline button-sm">
                                            <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                                            </svg>
                                            Generate Meeting
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div id="generated-meeting-info" class="meeting-info" style="display: none;">
                                <div class="meeting-info-item">
                                    <span class="meeting-info-label">Platform:</span>
                                    <span class="meeting-info-value">Zoom</span>
                                </div>
                                <div class="meeting-info-item">
                                    <span class="meeting-info-label">Meeting ID:</span>
                                    <span class="meeting-info-value">123 456 7890</span>
                                </div>
                                <div class="meeting-info-item">
                                    <span class="meeting-info-label">Password:</span>
                                    <span class="meeting-info-value">abc123</span>
                                </div>
                                <div class="meeting-info-item">
                                    <span class="meeting-info-label">Join URL:</span>
                                    <span class="meeting-info-value">https://zoom.us/j/1234567890?pwd=abc123</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Notification</label>
                                <div class="form-check">
                                    <input type="checkbox" id="notify-students" name="notify_students" class="form-check-input" value="1" checked>
                                    <label for="notify-students" class="form-check-label">Notify students</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="notify-parents" name="notify_parents" class="form-check-input" value="1">
                                    <label for="notify-parents" class="form-check-label">Notify parents</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Additional Options</label>
                                <div class="form-check">
                                    <input type="checkbox" id="record-session" name="record_session" class="form-check-input" value="1" checked>
                                    <label for="record-session" class="form-check-label">Record session automatically</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="attendance-tracking" name="attendance_tracking" class="form-check-input" value="1" checked>
                                    <label for="attendance-tracking" class="form-check-label">Enable attendance tracking</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="allow-screen-sharing" name="allow_screen_sharing" class="form-check-input" value="1" checked>
                                    <label for="allow-screen-sharing" class="form-check-label">Allow student screen sharing</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="mute-on-entry" name="mute_on_entry" class="form-check-input" value="1" checked>
                                    <label for="mute-on-entry" class="form-check-label">Mute participants on entry</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="button button-outline">Cancel</button>
                            <button type="submit" class="button">Schedule Class</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Manage Sessions Tab -->
            <div class="tab-content" id="manage-sessions">
                <div class="search-filter-bar">
                    <div class="search-container">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search classes...">
                    </div>
                    <div class="filter-container">
                        <select class="form-select">
                            <option value="">All Subjects</option>
                            <option value="mathematics">Mathematics</option>
                            <option value="science">Science</option>
                            <option value="english">English</option>
                            <option value="history">History</option>
                            <option value="physics">Physics</option>
                            <option value="chemistry">Chemistry</option>
                            <option value="biology">Biology</option>
                            <option value="computer_science">Computer Science</option>
                        </select>
                        <select class="form-select">
                            <option value="">All Grades</option>
                            <option value="1">Class 1</option>
                            <option value="2">Class 2</option>
                            <option value="3">Class 3</option>
                            <option value="4">Class 4</option>
                            <option value="5">Class 5</option>
                            <option value="6">Class 6</option>
                            <option value="7">Class 7</option>
                            <option value="8">Class 8</option>
                            <option value="9">Class 9</option>
                            <option value="10">Class 10</option>
                            <option value="11">Class 11</option>
                            <option value="12">Class 12</option>
                        </select>
                        <select class="form-select">
                            <option value="">All Status</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Upcoming & Recent Classes</h2>
                            <p class="card-subtitle">Manage your scheduled online classes</p>
                        </div>
                        <div>
                            <button class="button button-sm">
                                <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Class Title</th>
                                        <th>Subject</th>
                                        <th>Grade</th>
                                        <th>Date & Time</th>
                                        <th>Platform</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Advanced Algebra Concepts</td>
                                        <td>Mathematics</td>
                                        <td>Class 10A</td>
                                        <td>Mar 18, 2025 • 09:00 AM</td>
                                        <td><span class="platform-badge platform-zoom">Zoom</span></td>
                                        <td><span class="status-tag status-scheduled">Scheduled</span></td>
                                        <td>
                                            <div class="actions-dropdown">
                                                <button class="actions-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="12" cy="5" r="1"></circle>
                                                        <circle cx="12" cy="19" r="1"></circle>
                                                    </svg>
                                                </button>
                                                <div class="actions-menu">
                                                    <div class="actions-item" onclick="viewClassDetails(1)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M15 10l5 5-5 5"></path>
                                                            <path d="M4 4v7a4 4 0 0 0 4 4h12"></path>
                                                        </svg>
                                                        Start Class
                                                    </div>
                                                    <div class="actions-item" onclick="editClass(1)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M12 20h9"></path>
                                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                        </svg>
                                                        Edit
                                                    </div>
                                                    <div class="actions-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                                        </svg>
                                                        Send Reminder
                                                    </div>
                                                    <div class="actions-item danger" onclick="confirmCancelClass(1)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <circle cx="12" cy="12" r="10"></circle>
                                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                                        </svg>
                                                        Cancel Class
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Chemical Reactions Lab</td>
                                        <td>Chemistry</td>
                                        <td>Class 9B</td>
                                        <td>Mar 17, 2025 • 11:30 AM</td>
                                        <td><span class="platform-badge platform-google">Google Meet</span></td>
                                        <td><span class="status-tag status-active">Active</span></td>
                                        <td>
                                            <div class="actions-dropdown">
                                                <button class="actions-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="12" cy="5" r="1"></circle>
                                                        <circle cx="12" cy="19" r="1"></circle>
                                                    </svg>
                                                </button>
                                                <div class="actions-menu">
                                                    <div class="actions-item" onclick="viewClassDetails(2)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                          <path d="M15 10l5 5-5 5"></path>
                                                            <path d="M4 4v7a4 4 0 0 0 4 4h12"></path>
                                                        </svg>
                                                        Join Class
                                                    </div>
                                                    <div class="actions-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                            <polyline points="21 15 16 10 5 21"></polyline>
                                                        </svg>
                                                        Take Attendance
                                                    </div>
                                                    <div class="actions-item danger">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                                        </svg>
                                                        End Class
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>English Literature: Shakespeare</td>
                                        <td>English</td>
                                        <td>Class 11A</td>
                                        <td>Mar 16, 2025 • 02:00 PM</td>
                                        <td><span class="platform-badge platform-teams">MS Teams</span></td>
                                        <td><span class="status-tag status-completed">Completed</span></td>
                                        <td>
                                            <div class="actions-dropdown">
                                                <button class="actions-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="12" cy="5" r="1"></circle>
                                                        <circle cx="12" cy="19" r="1"></circle>
                                                    </svg>
                                                </button>
                                                <div class="actions-menu">
                                                    <div class="actions-item" onclick="viewClassDetails(3)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                                        </svg>
                                                        View Recording
                                                    </div>
                                                    <div class="actions-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                            <polyline points="14 2 14 8 20 8"></polyline>
                                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                                            <polyline points="10 9 9 9 8 9"></polyline>
                                                        </svg>
                                                        View Report
                                                    </div>
                                                    <div class="actions-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="8.5" cy="7" r="4"></circle>
                                                            <polyline points="17 11 19 13 23 9"></polyline>
                                                        </svg>
                                                        Attendance
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Introduction to Web Development</td>
                                        <td>Computer Science</td>
                                        <td>Class 8A</td>
                                        <td>Mar 15, 2025 • 10:00 AM</td>
                                        <td><span class="platform-badge platform-zoom">Zoom</span></td>
                                        <td><span class="status-tag status-cancelled">Cancelled</span></td>
                                        <td>
                                            <div class="actions-dropdown">
                                                <button class="actions-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="12" cy="5" r="1"></circle>
                                                        <circle cx="12" cy="19" r="1"></circle>
                                                    </svg>
                                                </button>
                                                <div class="actions-menu">
                                                    <div class="actions-item" onclick="viewClassDetails(4)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item" onclick="rescheduleClass(4)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                                        </svg>
                                                        Reschedule
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="pagination">
                        <div class="pagination-info">
                            Showing 1 to 4 of 24 classes
                        </div>
                        <div class="pagination-buttons">
                            <button class="pagination-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                            </button>
                            <button class="pagination-button active">1</button>
                            <button class="pagination-button">2</button>
                            <button class="pagination-button">3</button>
                            <button class="pagination-button">4</button>
                            <button class="pagination-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Reports Tab -->
            <div class="tab-content" id="session-reports">
                <div class="search-filter-bar">
                    <div class="search-container">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search session reports...">
                    </div>
                    <div class="filter-container">
                        <select class="form-select">
                            <option value="">All Subjects</option>
                            <option value="mathematics">Mathematics</option>
                            <option value="science">Science</option>
                            <option value="english">English</option>
                            <option value="history">History</option>
                            <option value="physics">Physics</option>
                            <option value="chemistry">Chemistry</option>
                            <option value="biology">Biology</option>
                            <option value="computer_science">Computer Science</option>
                        </select>
                        <select class="form-select">
                            <option value="">Date Range</option>
                            <option value="today">Today</option>
                            <option value="this_week">This Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                        <button class="button button-sm">
                            <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Export Reports
                        </button>
                    </div>
                </div>

                <div class="session-stats">
                    <div class="stat-card">
                        <div class="stat-value">146</div>
                        <div class="stat-label">Total Sessions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">92%</div>
                        <div class="stat-label">Avg. Attendance</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">53 min</div>
                        <div class="stat-label">Avg. Duration</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">124</div>
                        <div class="stat-label">Active Students</div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">English Literature: Shakespeare</h2>
                            <p class="card-subtitle">Session Report • Mar 16, 2025</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="class-details-grid">
                            <div class="class-detail-item">
                                <div class="class-detail-label">Subject</div>
                                <div class="class-detail-value">English</div>
                            </div>
                            <div class="class-detail-item">
                                <div class="class-detail-label">Grade/Class</div>
                                <div class="class-detail-value">Class 11A</div>
                            </div>
                            <div class="class-detail-item">
                                <div class="class-detail-label">Teacher</div>
                                <div class="class-detail-value">Michael Brown</div>
                            </div>
                            <div class="class-detail-item">
                                <div class="class-detail-label">Duration</div>
                                <div class="class-detail-value">58 minutes</div>
                            </div>
                            <div class="class-detail-item">
                                <div class="class-detail-label">Platform</div>
                                <div class="class-detail-value">Microsoft Teams</div>
                            </div>
                            <div class="class-detail-item">
                                <div class="class-detail-label">Recording</div>
                                <div class="class-detail-value">
                                    <a href="#" class="button button-sm button-outline">
                                        <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                        </svg>
                                        View Recording
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 2rem;">
                            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Session Timeline</h3>
                            <div class="session-timeline">
                                <div class="timeline-line"></div>
                                <div class="timeline-item">
                                    <div class="timeline-point"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-time">2:00 PM</div>
                                        <div class="timeline-title">Session Started</div>
                                        <div class="timeline-description">Teacher started the session and began with attendance check.</div>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-point"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-time">2:10 PM</div>
                                        <div class="timeline-title">Presentation Shared</div>
                                        <div class="timeline-description">Teacher shared presentation on Shakespeare's Hamlet.</div>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-point"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-time">2:25 PM</div>
                                        <div class="timeline-title">Group Discussion</div>
                                        <div class="timeline-description">Students were divided into breakout rooms for group discussion.</div>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-point"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-time">2:45 PM</div>
                                        <div class="timeline-title">Class Discussion</div>
                                        <div class="timeline-description">Students returned from breakout rooms to discuss findings with the whole class.</div>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-point"></div>
                                    <div class="timeline-content">
                                        <div class="timeline-time">2:58 PM</div>
                                        <div class="timeline-title">Session Ended</div>
                                        <div class="timeline-description">Teacher assigned homework and concluded the session.</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top: 2rem;">
                            <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: 1rem;">Attendance (28/30 students)</h3>
                            <div class="attendance-list">
                                <div class="attendance-item">
                                    <div class="attendance-avatar">JS</div>
                                    <div class="attendance-info">
                                        <div class="attendance-name">John Smith</div>
                                        <div class="attendance-details">
                                            <span>Roll: 01</span>
                                            <span>Join Time: 1:58 PM</span>
                                            <span>Duration: 58 min</span>
                                        </div>
                                    </div>
                                    <span class="attendance-status status-present">Present</span>
                                </div>
                                <div class="attendance-item">
                                    <div class="attendance-avatar">EJ</div>
                                    <div class="attendance-info">
                                        <div class="attendance-name">Emily Johnson</div>
                                        <div class="attendance-details">
                                            <span>Roll: 02</span>
                                            <span>Join Time: 2:05 PM</span>
                                            <span>Duration: 53 min</span>
                                        </div>
                                    </div>
                                    <span class="attendance-status status-late">Late</span>
                                </div>
                                <div class="attendance-item">
                                    <div class="attendance-avatar">MD</div>
                                    <div class="attendance-info">
                                        <div class="attendance-name">Michael Davis</div>
                                        <div class="attendance-details">
                                            <span>Roll: 03</span>
                                            <span>Join Time: 1:59 PM</span>
                                            <span>Duration: 58 min</span>
                                        </div>
                                    </div>
                                    <span class="attendance-status status-present">Present</span>
                                </div>
                                <div class="attendance-item">
                                    <div class="attendance-avatar">SW</div>
                                    <div class="attendance-info">
                                        <div class="attendance-name">Sarah Wilson</div>
                                        <div class="attendance-details">
                                            <span>Roll: 04</span>
                                            <span>Join Time: 1:57 PM</span>
                                            <span>Duration: 58 min</span>
                                        </div>
                                    </div>
                                    <span class="attendance-status status-present">Present</span>
                                </div>
                                <div class="attendance-item">
                                    <div class="attendance-avatar">RD</div>
                                    <div class="attendance-info">
                                        <div class="attendance-name">Robert Davis</div>
                                        <div class="attendance-details">
                                            <span>Roll: 05</span>
                                            <span>Join Time: N/A</span>
                                            <span>Duration: 0 min</span>
                                        </div>
                                    </div>
                                    <span class="attendance-status status-absent">Absent</span>
                                </div>
                            </div>
                            <div style="text-align: center; margin-top: 1rem;">
                                <button class="button button-outline button-sm">View All Students</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Class Details Modal -->
    <div id="class-details-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Class Details</h3>
                <button class="modal-close" onclick="closeModal('class-details-modal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="class-details-grid">
                    <div class="class-detail-item">
                        <div class="class-detail-label">Class Title</div>
                        <div class="class-detail-value" id="modal-class-title">Advanced Algebra Concepts</div>
                    </div>
                    <div class="class-detail-item">
                        <div class="class-detail-label">Subject</div>
                        <div class="class-detail-value" id="modal-subject">Mathematics</div>
                    </div>
                    <div class="class-detail-item">
                        <div class="class-detail-label">Grade/Class</div>
                        <div class="class-detail-value" id="modal-grade">Class 10A</div>
                    </div>
                    <div class="class-detail-item">
                        <div class="class-detail-label">Teacher</div>
                        <div class="class-detail-value" id="modal-teacher">John Smith</div>
                    </div>
                    <div class="class-detail-item">
                        <div class="class-detail-label">Date</div>
                        <div class="class-detail-value" id="modal-date">March 18, 2025</div>
                    </div>
                    <div class="class-detail-item">
                        <div class="class-detail-label">Time</div>
                        <div class="class-detail-value" id="modal-time">09:00 AM - 10:00 AM</div>
                    </div>
                    <div class="class-detail-item">
                        <div class="class-detail-label">Platform</div>
                        <div class="class-detail-value" id="modal-platform">Zoom</div>
                    </div>
                    <div class="class-detail-item">
                        <div class="class-detail-label">Status</div>
                        <div class="class-detail-value" id="modal-status"><span class="status-tag status-scheduled">Scheduled</span></div>
                    </div>
                </div>
                
                <div class="class-detail-item" style="margin-top: 1.5rem;">
                    <div class="class-detail-label">Description</div>
                    <div class="class-detail-value" id="modal-description">
                        This class will cover advanced algebra concepts including quadratic equations, polynomial functions, and complex numbers. Students are expected to have completed the assigned readings before class.
                    </div>
                </div>
                
                <div class="meeting-info" style="margin-top: 1.5rem;">
                    <div class="meeting-info-item">
                        <span class="meeting-info-label">Meeting ID:</span>
                        <span class="meeting-info-value" id="modal-meeting-id">123 456 7890</span>
                    </div>
                    <div class="meeting-info-item">
                        <span class="meeting-info-label">Password:</span>
                        <span class="meeting-info-value" id="modal-password">abc123</span>
                    </div>
                    <div class="meeting-info-item">
                        <span class="meeting-info-label">Join URL:</span>
                        <span class="meeting-info-value" id="modal-join-url">https://zoom.us/j/1234567890?pwd=abc123</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-outline" onclick="closeModal('class-details-modal')">Close</button>
                <button type="button" class="button" id="modal-action-button">Start Class</button>
            </div>
        </div>
    </div>

    <!-- Cancel Class Modal -->
    <div id="cancel-class-modal" class="modal">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h3 class="modal-title">Cancel Class</h3>
                <button class="modal-close" onclick="closeModal('cancel-class-modal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to cancel this class? This action cannot be undone.</p>
                <p><strong>Class: </strong><span id="cancel-class-name">Advanced Algebra Concepts</span></p>
                <p><strong>Scheduled Time: </strong><span id="cancel-class-time">March 18, 2025 • 09:00 AM</span></p>
                
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="cancellation-reason" class="form-label">Reason for Cancellation</label>
                    <select id="cancellation-reason" class="form-select">
                        <option value="">Select a reason</option>
                        <option value="teacher_unavailable">Teacher Unavailable</option>
                        <option value="technical_issues">Technical Issues</option>
                        <option value="rescheduled">Being Rescheduled</option>
                        <option value="holiday">School Holiday</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="cancellation-message" class="form-label">Notification Message (Optional)</label>
                    <textarea id="cancellation-message" class="form-control" placeholder="Enter a message to send to participants"></textarea>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="notify-students-cancel" class="form-check-input" checked>
                    <label for="notify-students-cancel" class="form-check-label">Notify students about cancellation</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-outline" onclick="closeModal('cancel-class-modal')">Cancel</button>
                <button type="button" class="button" style="background-color: #ef4444;" onclick="cancelClass()">Confirm Cancellation</button>
            </div>
        </div>
    </div>

    <script>
        // DOM ready function
        document.addEventListener('DOMContentLoaded', function() {
            initializeTabs();
            initializePlatformSelector();
            initializeRecurringToggle();
            initializeDropdownActions();
            initializeGenerateMeeting();
        });

        // Toggle sidebar visibility
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
            
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            if (sidebar.classList.contains('show')) {
                hamburgerBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
            } else {
                hamburgerBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>`;
            }
        }

        // Tabs functionality
        function initializeTabs() {
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all tab contents
                    const tabContents = document.querySelectorAll('.tab-content');
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Show the corresponding tab content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        }

        // Platform selector functionality
        function initializePlatformSelector() {
            const platformOptions = document.querySelectorAll('.platform-option');
            const platformDetailsContainers = document.querySelectorAll('.platform-details');
            const selectedPlatformInput = document.getElementById('selected-platform');
            
            platformOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    platformOptions.forEach(opt => opt.classList.remove('selected'));
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Update hidden input with selected platform
                    const platformValue = this.getAttribute('data-platform');
                    selectedPlatformInput.value = platformValue;
                    
                    // Hide all platform details containers
                    platformDetailsContainers.forEach(container => {
                        container.style.display = 'none';
                    });
                    
                    // Show the corresponding platform details container
                    const detailsContainer = document.getElementById(`${platformValue}-details`);
                    if (detailsContainer) {
                        detailsContainer.style.display = 'block';
                    }
                });
            });
        }

        // Recurring toggle functionality
        function initializeRecurringToggle() {
            const recurringCheckbox = document.getElementById('recurring-schedule');
            const recurringOptionsContainer = document.getElementById('recurring-options-container');
            const recurringOptions = document.querySelectorAll('.recurring-option');
            
            if (recurringCheckbox && recurringOptionsContainer) {
                recurringCheckbox.addEventListener('change', function() {
                    recurringOptionsContainer.style.display = this.checked ? 'block' : 'none';
                });
            }
            
            if (recurringOptions) {
                recurringOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        // Remove selected class from all options
                        recurringOptions.forEach(opt => opt.classList.remove('selected'));
                        // Add selected class to clicked option
                        this.classList.add('selected');
                        
                        // Update hidden input with selected recurring pattern
                        const form = document.getElementById('schedule-class-form');
                        if (form) {
                            let hiddenInput = form.querySelector('input[name="recurring_pattern"]');
                            if (!hiddenInput) {
                                hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'recurring_pattern';
                                form.appendChild(hiddenInput);
                            }
                            hiddenInput.value = this.getAttribute('data-value');
                        }
                    });
                });
            }
        }

        // Initialize dropdown actions
        function initializeDropdownActions() {
            const actionButtons = document.querySelectorAll('.actions-button');
            
            document.addEventListener('click', function(e) {
                // Close all open action menus when clicking outside
                if (!e.target.closest('.actions-dropdown')) {
                    document.querySelectorAll('.actions-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
            
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const menu = this.nextElementSibling;
                    
                    // Close all other open menus
                    document.querySelectorAll('.actions-menu').forEach(m => {
                        if (m !== menu) m.classList.remove('show');
                    });
                    
                    // Toggle this menu
                    menu.classList.toggle('show');
                });
            });
        }

        // Generate meeting functionality
        function initializeGenerateMeeting() {
            const generateMeetingBtn = document.getElementById('generate-meeting');
            const generatedMeetingInfo = document.getElementById('generated-meeting-info');
            
            if (generateMeetingBtn && generatedMeetingInfo) {
                generateMeetingBtn.addEventListener('click', function() {
                    // Show loading state
                    this.disabled = true;
                    this.innerHTML = `
                        <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                        Generating...
                    `;
                    
                    // In a real application, you would make an API call to generate a meeting
                    // For this demo, we'll simulate the API call with a timeout
                    setTimeout(() => {
                        // Reset button state
                        this.disabled = false;
                        this.innerHTML = `
                            <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                            </svg>
                            Generate Meeting
                        `;
                        
                        // Generate random meeting ID and password
                        const meetingId = Math.floor(Math.random() * 1000000000).toString().padStart(9, '0');
                        const meetingIdFormatted = `${meetingId.substring(0, 3)} ${meetingId.substring(3, 6)} ${meetingId.substring(6, 9)}`;
                        const password = Math.random().toString(36).substring(2, 8);
                        const joinUrl = `https://zoom.us/j/${meetingId}?pwd=${password}`;
                        
                        // Update form fields
                        document.getElementById('meeting-id').value = meetingIdFormatted;
                        document.getElementById('meeting-password').value = password;
                        document.getElementById('meeting-link').value = joinUrl;
                        
                        // Update generated meeting info section
                        const platformInfoValue = generatedMeetingInfo.querySelector('.meeting-info-item:nth-child(1) .meeting-info-value');
                        const meetingIdInfoValue = generatedMeetingInfo.querySelector('.meeting-info-item:nth-child(2) .meeting-info-value');
                        const passwordInfoValue = generatedMeetingInfo.querySelector('.meeting-info-item:nth-child(3) .meeting-info-value');
                        const joinUrlInfoValue = generatedMeetingInfo.querySelector('.meeting-info-item:nth-child(4) .meeting-info-value');
                        
                        if (platformInfoValue) platformInfoValue.textContent = 'Zoom';
                        if (meetingIdInfoValue) meetingIdInfoValue.textContent = meetingIdFormatted;
                        if (passwordInfoValue) passwordInfoValue.textContent = password;
                        if (joinUrlInfoValue) joinUrlInfoValue.textContent = joinUrl;
                        
                        // Show the generated meeting info section
                        generatedMeetingInfo.style.display = 'block';
                    }, 1500);
                });
            }
        }

        // View class details
        function viewClassDetails(classId) {
            // In a real application, you would fetch class details via AJAX
            // For this demo, we'll just populate with static data
            
            // Update modal content based on class ID
            let classTitle, subject, grade, teacher, classDate, classTime, platform, status, description, meetingId, password, joinUrl;
            let actionButtonText = 'Start Class';
            
            if (classId === 1) {
                classTitle = 'Advanced Algebra Concepts';
                subject = 'Mathematics';
                grade = 'Class 10A';
                teacher = 'John Smith';
                classDate = 'March 18, 2025';
                classTime = '09:00 AM - 10:00 AM';
                platform = 'Zoom';
                status = '<span class="status-tag status-scheduled">Scheduled</span>';
                description = 'This class will cover advanced algebra concepts including quadratic equations, polynomial functions, and complex numbers. Students are expected to have completed the assigned readings before class.';
                meetingId = '123 456 7890';
                password = 'abc123';
                joinUrl = 'https://zoom.us/j/1234567890?pwd=abc123';
                actionButtonText = 'Start Class';
            } else if (classId === 2) {
                classTitle = 'Chemical Reactions Lab';
                subject = 'Chemistry';
                grade = 'Class 9B';
                teacher = 'Jennifer Taylor';
                classDate = 'March 17, 2025';
                classTime = '11:30 AM - 12:30 PM';
                platform = 'Google Meet';
                status = '<span class="status-tag status-active">Active</span>';
                description = 'Virtual lab session covering chemical reactions. Students will observe and analyze several reaction types and record their observations in their lab notebooks.';
                meetingId = 'N/A';
                password = 'N/A';
                joinUrl = 'https://meet.google.com/abc-defg-hij';
                actionButtonText = 'Join Class';
            } else if (classId === 3) {
                classTitle = 'English Literature: Shakespeare';
                subject = 'English';
                grade = 'Class 11A';
                teacher = 'Michael Brown';
                classDate = 'March 16, 2025';
                classTime = '02:00 PM - 03:00 PM';
                platform = 'Microsoft Teams';
                status = '<span class="status-tag status-completed">Completed</span>';
                description = 'Discussion of Hamlet, focusing on character analysis and themes. Students were expected to have read Acts 3 and 4 before class.';
                meetingId = 'N/A';
                password = 'N/A';
                joinUrl = 'https://teams.microsoft.com/l/meetup-join/abc123';
                actionButtonText = 'View Recording';
            } else if (classId === 4) {
                classTitle = 'Introduction to Web Development';
                subject = 'Computer Science';
                grade = 'Class 8A';
                teacher = 'Amanda White';
                classDate = 'March 15, 2025';
                classTime = '10:00 AM - 11:00 AM';
                platform = 'Zoom';
                status = '<span class="status-tag status-cancelled">Cancelled</span>';
                description = 'Introduction to basic web development concepts including HTML, CSS, and JavaScript. This class was cancelled due to technical issues.';
                meetingId = '987 654 321';
                password = 'webdev123';
                joinUrl = 'https://zoom.us/j/987654321?pwd=webdev123';
                actionButtonText = 'Reschedule';
            }
            
            // Update modal elements
            document.getElementById('modal-class-title').textContent = classTitle;
            document.getElementById('modal-subject').textContent = subject;
            document.getElementById('modal-grade').textContent = grade;
            document.getElementById('modal-teacher').textContent = teacher;
            document.getElementById('modal-date').textContent = classDate;
            document.getElementById('modal-time').textContent = classTime;
            document.getElementById('modal-platform').textContent = platform;
            document.getElementById('modal-status').innerHTML = status;
            document.getElementById('modal-description').textContent = description;
            document.getElementById('modal-meeting-id').textContent = meetingId;
            document.getElementById('modal-password').textContent = password;
            document.getElementById('modal-join-url').textContent = joinUrl;
            
            // Update action button
            const actionButton = document.getElementById('modal-action-button');
            actionButton.textContent = actionButtonText;
            
            // Show the modal
            document.getElementById('class-details-modal').classList.add('show');
            
            // Close any open action menus
            document.querySelectorAll('.actions-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        // Edit class
        function editClass(classId) {
            // In a real application, you would redirect to edit page or load edit modal
            // For this demo, we'll simulate by switching to the schedule tab with pre-filled data
            const scheduleTab = document.querySelector('[data-tab="schedule-class"]');
            scheduleTab.click();
            
            // Pre-fill form with class data (simulated)
            if (classId === 1) {
                document.getElementById('class-title').value = 'Advanced Algebra Concepts';
                document.getElementById('subject').value = 'mathematics';
                document.getElementById('grade-class').value = '10A';
                document.getElementById('teacher').value = '1';
                document.getElementById('class-date').value = '2025-03-18';
                document.getElementById('class-time').value = '09:00';
                document.getElementById('duration').value = '60';
                document.getElementById('class-description').value = 'This class will cover advanced algebra concepts including quadratic equations, polynomial functions, and complex numbers. Students are expected to have completed the assigned readings before class.';
                document.getElementById('meeting-id').value = '123 456 7890';
                document.getElementById('meeting-password').value = 'abc123';
                document.getElementById('meeting-link').value = 'https://zoom.us/j/1234567890?pwd=abc123';
            }
            
            // Close any open modals
            closeAllModals();
            
            // Close any open action menus
            document.querySelectorAll('.actions-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        // Reschedule class
        function rescheduleClass(classId) {
            // Similar to edit class, but focus on date/time fields
            editClass(classId);
            
            // Focus on date field
            setTimeout(() => {
                document.getElementById('class-date').focus();
            }, 100);
        }

        // Confirm cancel class
        function confirmCancelClass(classId) {
            // Populate cancel modal with class info
            let classTitle, classTime;
            
            if (classId === 1) {
                classTitle = 'Advanced Algebra Concepts';
                classTime = 'March 18, 2025 • 09:00 AM';
            } else if (classId === 2) {
                classTitle = 'Chemical Reactions Lab';
                classTime = 'March 17, 2025 • 11:30 AM';
            } else if (classId === 3) {
                classTitle = 'English Literature: Shakespeare';
                classTime = 'March 16, 2025 • 02:00 PM';
            } else if (classId === 4) {
                classTitle = 'Introduction to Web Development';
                classTime = 'March 15, 2025 • 10:00 AM';
            }
            
            document.getElementById('cancel-class-name').textContent = classTitle;
            document.getElementById('cancel-class-time').textContent = classTime;
            
            // Store class ID for cancel operation
            document.getElementById('cancel-class-modal').setAttribute('data-class-id', classId);
            
            // Show the cancel modal
            document.getElementById('cancel-class-modal').classList.add('show');
            
            // Close any open action menus
            document.querySelectorAll('.actions-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        // Cancel class
        function cancelClass() {
            const classId = document.getElementById('cancel-class-modal').getAttribute('data-class-id');
            const reason = document.getElementById('cancellation-reason').value;
            const message = document.getElementById('cancellation-message').value;
            const notifyStudents = document.getElementById('notify-students-cancel').checked;
            
            // In a real application, you would send a request to the server
            // For this demo, we'll just show an alert
            alert(`Class ID ${classId} has been cancelled.\nReason: ${reason || 'Not specified'}\nNotify students: ${notifyStudents ? 'Yes' : 'No'}`);
            
            // Close the modal
            closeModal('cancel-class-modal');
            
            // Update the class status in the table (simulated)
            const tableRow = document.querySelector(`.table tbody tr:nth-child(${classId})`);
            if (tableRow) {
                const statusCell = tableRow.querySelector('td:nth-child(6)');
                if (statusCell) {
                    statusCell.innerHTML = '<span class="status-tag status-cancelled">Cancelled</span>';
                }
            }
        }

        // Close specific modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Close all modals
        function closeAllModals() {
            document.querySelectorAll('.modal').forEach(modal => {
                modal.classList.remove('show');
            });
        }

        // Page transitions
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a:not([href^="#"])');
            
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!this.getAttribute('href').startsWith('javascript:')) {
                        e.preventDefault();
                        document.body.classList.add('fade-out');
                        
                        setTimeout(() => {
                            window.location.href = this.getAttribute('href');
                        }, 500);
                    }
                });
            });
        });

        // Helper function to format date
        function formatDate(date) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return new Date(date).toLocaleDateString('en-US', options);
        }

        // Helper function to format time
        function formatTime(time) {
            const options = { hour: 'numeric', minute: '2-digit', hour12: true };
            return new Date(`2025-01-01T${time}`).toLocaleTimeString('en-US', options);
        }
    </script>
</body>
</html>