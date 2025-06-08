<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Notifications & Announcements</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/notifications.css">
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
        <h1 class="header-title">Notifications & Announcements</h1>
        <p class="header-subtitle">Create and manage important communications</p>
    </header>

    <main class="dashboard-content">
        <!-- Main Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Announcements & Notifications</h2>
                <div>
                    <button class="btn btn-primary" id="createNewBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Create New
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="tabs">
                    <div class="tab active" data-tab="all">All</div>
                    <div class="tab" data-tab="sent">Sent</div>
                    <div class="tab" data-tab="received">Received</div>
                    <div class="tab" data-tab="drafts">Drafts</div>
                </div>
                
                <!-- Search & Filters -->
                <div class="search-container">
                    <input type="text" placeholder="Search notifications..." class="search-input">
                    <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                
                <div class="filters-container">
                    <div class="filter">
                        <select class="form-select">
                            <option value="">All Recipients</option>
                            <option value="students">Students</option>
                            <option value="parents">Parents</option>
                            <option value="teachers">Teachers</option>
                            <option value="class">Specific Class</option>
                        </select>
                    </div>
                    <div class="filter">
                        <select class="form-select">
                            <option value="">All Types</option>
                            <option value="announcement">Announcements</option>
                            <option value="reminder">Reminders</option>
                            <option value="event">Events</option>
                            <option value="notice">Notices</option>
                        </select>
                    </div>
                    <div class="filter">
                        <select class="form-select">
                            <option value="">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="older">Older</option>
                        </select>
                    </div>
                </div>
                
                <!-- All Tab Content -->
                <div class="tab-content active" id="all-content">
                    <div class="notification-list">
                        <!-- Notification Item 1 (Urgent & Unread) -->
                        <div class="notification-item urgent unread">
                            <div class="notification-header">
                                <h3 class="notification-title">Parent-Teacher Meeting Schedule</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-urgent">Urgent</span>
                                    <span class="notification-date">Today, 9:30 AM</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>The parent-teacher meeting schedule has been updated. Please make note of your assigned slots and ensure you are available for the meetings.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">Sarah Thompson, Principal</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                    <button class="btn btn-primary btn-sm">Mark Read</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Item 2 (Unread) -->
                        <div class="notification-item unread">
                            <div class="notification-header">
                                <h3 class="notification-title">Staff Meeting - Math Department</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-teachers">Teachers</span>
                                    <span class="notification-date">Yesterday, 4:15 PM</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>There will be a Math Department staff meeting on Friday at 3:30 PM in the Conference Room. Please bring your curriculum plans for the next quarter.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">Robert Wilson, Department Head</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                    <button class="btn btn-primary btn-sm">Mark Read</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Item 3 (Read) -->
                        <div class="notification-item">
                            <div class="notification-header">
                                <h3 class="notification-title">Annual Sports Day Announcement</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-all">All</span>
                                    <span class="notification-date">Mar 10, 2025</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>The annual sports day will be held on April 5, 2025. Please inform all students and help with the preparations. A detailed schedule will be shared next week.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/men/55.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">Michael Brown, Sports Coordinator</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Item 4 (Class Specific & Read) -->
                        <div class="notification-item">
                            <div class="notification-header">
                                <h3 class="notification-title">Science Project Submission Extension</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-class">Class 9B</span>
                                    <span class="notification-date">Mar 8, 2025</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>The deadline for the science project submission has been extended to March 20, 2025. Please inform all students in Class 9B about this change.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/women/67.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">Jennifer Davis, Science Coordinator</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Item 5 (Created by you) -->
                        <div class="notification-item">
                            <div class="notification-header">
                                <h3 class="notification-title">Math Quiz Postponed</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-class">Class 8A</span>
                                    <span class="notification-date">Mar 7, 2025</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>The Math quiz scheduled for March 9 has been postponed to March 14 due to the school event. Students should use this additional time to prepare well.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">You</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                    <button class="btn btn-secondary btn-sm">Edit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="pagination">
                        <button class="page-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button class="page-button active">1</button>
                        <button class="page-button">2</button>
                        <button class="page-button">3</button>
                        <span class="page-ellipsis">...</span>
                        <button class="page-button">10</button>
                        <button class="page-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Sent Tab Content -->
                <div class="tab-content" id="sent-content">
                    <div class="notification-list">
                        <!-- Notification Item (Created by you) -->
                        <div class="notification-item">
                            <div class="notification-header">
                                <h3 class="notification-title">Math Quiz Postponed</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-class">Class 8A</span>
                                    <span class="notification-date">Mar 7, 2025</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>The Math quiz scheduled for March 9 has been postponed to March 14 due to the school event. Students should use this additional time to prepare well.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">You</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                    <button class="btn btn-secondary btn-sm">Edit</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Item (Created by you) -->
                        <div class="notification-item">
                            <div class="notification-header">
                                <h3 class="notification-title">Homework Submission Reminder</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-class">Class 9B</span>
                                    <span class="notification-date">Mar 5, 2025</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>This is a reminder that the science homework is due tomorrow. Please ensure all experiments are documented with proper observations and conclusions.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">You</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                    <button class="btn btn-secondary btn-sm">Edit</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Item (Created by you) -->
                        <div class="notification-item">
                            <div class="notification-header">
                                <h3 class="notification-title">Parent Meeting Request</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-parents">Parents</span>
                                    <span class="notification-date">Feb 28, 2025</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>I would like to request a meeting with the parents of students who scored below 60% in the recent mathematics test to discuss strategies for improvement.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">You</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                    <button class="btn btn-secondary btn-sm">Edit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Received Tab Content -->
                <div class="tab-content" id="received-content">
                    <div class="notification-list">
                        <!-- Notification Item 1 (Urgent & Unread) -->
                        <div class="notification-item urgent unread">
                            <div class="notification-header">
                                <h3 class="notification-title">Parent-Teacher Meeting Schedule</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-urgent">Urgent</span>
                                    <span class="notification-date">Today, 9:30 AM</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>The parent-teacher meeting schedule has been updated. Please make note of your assigned slots and ensure you are available for the meetings.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">Sarah Thompson, Principal</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                    <button class="btn btn-primary btn-sm">Mark Read</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Item 2 (Unread) -->
                        <div class="notification-item unread">
                            <div class="notification-header">
                                <h3 class="notification-title">Staff Meeting - Math Department</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-teachers">Teachers</span>
                                    <span class="notification-date">Yesterday, 4:15 PM</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>There will be a Math Department staff meeting on Friday at 3:30 PM in the Conference Room. Please bring your curriculum plans for the next quarter.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">Robert Wilson, Department Head</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                    <button class="btn btn-primary btn-sm">Mark Read</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Notification Item 3 (Read) -->
                        <div class="notification-item">
                            <div class="notification-header">
                                <h3 class="notification-title">Annual Sports Day Announcement</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-all">All</span>
                                    <span class="notification-date">Mar 10, 2025</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>The annual sports day will be held on April 5, 2025. Please inform all students and help with the preparations. A detailed schedule will be shared next week.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/men/55.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">Michael Brown, Sports Coordinator</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">View</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Drafts Tab Content -->
                <div class="tab-content" id="drafts-content">
                    <div class="notification-list">
                        <!-- Notification Item (Draft) -->
                        <div class="notification-item">
                            <div class="notification-header">
                                <h3 class="notification-title">Field Trip Permission Forms</h3>
                                <div class="notification-meta">
                                    <span class="badge badge-class">Class 8A</span>
                                    <span class="notification-date">Draft</span>
                                </div>
                            </div>
                            <div class="notification-content">
                                <p>Please remind students to submit their field trip permission forms by March 20. The trip to the Science Museum is scheduled for March 25.</p>
                            </div>
                            <div class="notification-footer">
                                <div class="notification-sender">
                                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Sender" class="sender-avatar">
                                    <span class="sender-name">You (Draft)</span>
                                </div>
                                <div class="notification-actions">
                                    <button class="btn btn-secondary btn-sm">Edit</button>
                                    <button class="btn btn-primary btn-sm">Send</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty State for when there are no drafts -->
                        <div class="empty-state" style="display: none;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="empty-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            <h3 class="empty-title">No Draft Notifications</h3>
                            <p class="empty-description">You don't have any draft notifications. Create a new notification to get started.</p>
                            <button class="btn btn-primary">Create New</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Create New Notification Form -->
        <div class="card" id="createNotificationForm" style="display: none;">
            <div class="card-header">
                <h2 class="card-title">Create New Notification</h2>
            </div>
            <div class="card-body">
                <form>
                    <div class="form-group">
                        <label for="notificationTitle" class="form-label">Title*</label>
                        <input type="text" id="notificationTitle" class="form-input" placeholder="Enter notification title" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notificationType" class="form-label">Type*</label>
                        <select id="notificationType" class="form-select" required>
                            <option value="">Select notification type</option>
                            <option value="announcement">Announcement</option>
                            <option value="reminder">Reminder</option>
                            <option value="event">Event</option>
                            <option value="notice">Notice</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notificationContent" class="form-label">Content*</label>
                        <textarea id="notificationContent" class="form-textarea" placeholder="Enter notification content" required></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Recipients*</label>
                        <select id="recipientType" class="form-select" required>
                            <option value="">Select recipient type</option>
                            <option value="all">All (School-wide)</option>
                            <option value="teachers">All Teachers</option>
                            <option value="students">All Students</option>
                            <option value="parents">All Parents</option>
                            <option value="class">Specific Class</option>
                            <option value="custom">Custom Recipients</option>
                        </select>
                        
                        <div id="classSelector" style="display: none; margin-top: 1rem;">
                            <select class="form-select">
                                <option value="">Select class</option>
                                <option value="8a">Class 8A - Mathematics</option>
                                <option value="9b">Class 9B - Science</option>
                                <option value="10c">Class 10C - Science</option>
                                <option value="7d">Class 7D - Social Studies</option>
                            </select>
                        </div>
                        
                        <div id="customRecipients" style="display: none; margin-top: 1rem;">
                            <input type="text" class="form-input" placeholder="Search users to add...">
                            <div class="recipients-list">
                                <div class="recipient-item">
                                    Class 8A Students
                                    <svg xmlns="http://www.w3.org/2000/svg" class="remove-recipient" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <div class="recipient-item">
                                    John Smith
                                    <svg xmlns="http://www.w3.org/2000/svg" class="remove-recipient" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notificationPriority" class="form-label">Priority</label>
                        <select id="notificationPriority" class="form-select">
                            <option value="normal">Normal</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Attachment (Optional)</label>
                        <div class="file-upload">
                            <label class="file-upload-label">
                                <svg xmlns="http://www.w3.org/2000/svg" class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="upload-text">Click to upload or drag and drop</span>
                            </label>
                            <input type="file" class="file-upload-input">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Options</label>
                        <div class="checkbox-group">
                            <div class="checkbox-item">
                                <input type="checkbox" id="sendEmail">
                                <label for="sendEmail" class="checkbox-label">Send email notification</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="requireConfirmation">
                                <label for="requireConfirmation" class="checkbox-label">Require read confirmation</label>
                            </div>
                            <div class="checkbox-item">
                                <input type="checkbox" id="scheduleNotification">
                                <label for="scheduleNotification" class="checkbox-label">Schedule for later</label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="scheduleOptions" style="display: none; margin-top: 1rem;">
                        <div class="two-col">
                            <div class="form-group">
                                <label for="scheduleDate" class="form-label">Date</label>
                                <input type="date" id="scheduleDate" class="form-input">
                            </div>
                            <div class="form-group">
                                <label for="scheduleTime" class="form-label">Time</label>
                                <input type="time" id="scheduleTime" class="form-input">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Preview</label>
                        <div class="notification-preview">
                            <div class="preview-header">Field Trip Permission Forms</div>
                            <div class="preview-content">
                                Please remind students to submit their field trip permission forms by March 20. The trip to the Science Museum is scheduled for March 25.
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                        <button type="button" class="btn btn-secondary" id="saveAsDraft">Save as Draft</button>
                        <button type="button" class="btn btn-secondary" id="cancelNotification">Cancel</button>
                        <button type="submit" class="btn btn-primary">Send Notification</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab contents
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Show the corresponding tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(`${tabId}-content`).classList.add('active');
            });
        });
        
        // Create New Button
        const createNewBtn = document.getElementById('createNewBtn');
        const createNotificationForm = document.getElementById('createNotificationForm');
        const cancelNotificationBtn = document.getElementById('cancelNotification');
        
        createNewBtn.addEventListener('click', function() {
            createNotificationForm.style.display = 'block';
            // Scroll to form
            createNotificationForm.scrollIntoView({ behavior: 'smooth' });
        });
        
        cancelNotificationBtn.addEventListener('click', function() {
            createNotificationForm.style.display = 'none';
        });
        
        // Recipient Type Change
        const recipientType = document.getElementById('recipientType');
        const classSelector = document.getElementById('classSelector');
        const customRecipients = document.getElementById('customRecipients');
        
        recipientType.addEventListener('change', function() {
            if (this.value === 'class') {
                classSelector.style.display = 'block';
                customRecipients.style.display = 'none';
            } else if (this.value === 'custom') {
                classSelector.style.display = 'none';
                customRecipients.style.display = 'block';
            } else {
                classSelector.style.display = 'none';
                customRecipients.style.display = 'none';
            }
        });
        
        // Schedule Options
        const scheduleCheckbox = document.getElementById('scheduleNotification');
        const scheduleOptions = document.getElementById('scheduleOptions');
        
        scheduleCheckbox.addEventListener('change', function() {
            if (this.checked) {
                scheduleOptions.style.display = 'block';
            } else {
                scheduleOptions.style.display = 'none';
            }
        });
        
        // Mark as Read Button
        const markReadButtons = document.querySelectorAll('.btn-primary');
        
        markReadButtons.forEach(button => {
            if (button.textContent === 'Mark Read') {
                button.addEventListener('click', function() {
                    const notificationItem = this.closest('.notification-item');
                    notificationItem.classList.remove('unread');
                    this.style.display = 'none';
                });
            }
        });
        
        // Function to toggle the sidebar (defined in the sidebar.php)
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            sidebar.classList.toggle('show');
            body.classList.toggle('sidebar-open');
        };
    });
</script>
</body>
</html>