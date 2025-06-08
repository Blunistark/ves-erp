<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>School Events Management</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/eventmanager.css">
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
            <h1 class="header-title">School Events Management</h1>
            <span class="header-date"><?php echo date('F j, Y'); ?></span>
        </header>

        <main class="dashboard-content">
            <!-- Tabs Navigation -->
            <div class="tabs-container">
                <div class="tabs">
                    <div class="tab active" data-tab="create-event">Create Event</div>
                    <div class="tab" data-tab="manage-events">Manage Events</div>
                    <div class="tab" data-tab="send-reminders">Send Reminders</div>
                </div>
            </div>

            <!-- Create Event Tab -->
            <div class="tab-content active" id="create-event">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Create New Event</h2>
                            <p class="card-subtitle">Fill in the details to create a new school event</p>
                        </div>
                    </div>
                    <form id="create-event-form" action="process_event.php" method="post">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="event-title" class="form-label">Event Title *</label>
                                        <input type="text" id="event-title" name="event_title" class="form-control" placeholder="Enter event title" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="event-type" class="form-label">Event Type *</label>
                                        <select id="event-type" name="event_type" class="form-select" required>
                                            <option value="">Select event type</option>
                                            <option value="academic">Academic</option>
                                            <option value="sports">Sports</option>
                                            <option value="cultural">Cultural</option>
                                            <option value="holiday">Holiday</option>
                                            <option value="exam">Examination</option>
                                            <option value="meeting">Meeting</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="event-date" class="form-label">Event Date *</label>
                                        <input type="date" id="event-date" name="event_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
      <label for="event-time" class="form-label">Event Time</label>
                                        <input type="time" id="event-time" name="event_time" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="end-date" class="form-label">End Date</label>
                                        <input type="date" id="end-date" name="end_date" class="form-control">
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="end-time" class="form-label">End Time</label>
                                        <input type="time" id="end-time" name="end_time" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="event-description" class="form-label">Event Description *</label>
                                <textarea id="event-description" name="event_description" class="form-control" placeholder="Enter event details..." required></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="event-venue" class="form-label">Event Venue *</label>
                                        <input type="text" id="event-venue" name="event_venue" class="form-control" placeholder="Enter venue" required>
                                    </div>
                                </div>
                                <div class="form-col">
                                    <div class="form-group">
                                        <label for="event-organizer" class="form-label">Organizer</label>
                                        <input type="text" id="event-organizer" name="event_organizer" class="form-control" placeholder="Enter organizer">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Event For</label>
                                <div class="form-check">
                                    <input type="checkbox" id="for-students" name="for_students" class="form-check-input" value="1" checked>
                                    <label for="for-students" class="form-check-label">Students</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="for-teachers" name="for_teachers" class="form-check-input" value="1" checked>
                                    <label for="for-teachers" class="form-check-label">Teachers</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="for-parents" name="for_parents" class="form-check-input" value="1">
                                    <label for="for-parents" class="form-check-label">Parents</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Classes/Grades</label>
                                <div class="form-check">
                                    <input type="checkbox" id="all-classes" name="all_classes" class="form-check-input" value="1">
                                    <label for="all-classes" class="form-check-label">All Classes</label>
                                </div>
                                <div class="form-row" style="margin-top: 0.5rem;">
                                    <div class="form-col">
                                        <div class="form-check">
                                            <input type="checkbox" id="class-1" name="classes[]" class="form-check-input class-checkbox" value="1">
                                            <label for="class-1" class="form-check-label">Class 1</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="class-2" name="classes[]" class="form-check-input class-checkbox" value="2">
                                            <label for="class-2" class="form-check-label">Class 2</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="class-3" name="classes[]" class="form-check-input class-checkbox" value="3">
                                            <label for="class-3" class="form-check-label">Class 3</label>
                                        </div>
                                    </div>
                                    <div class="form-col">
                                        <div class="form-check">
                                            <input type="checkbox" id="class-4" name="classes[]" class="form-check-input class-checkbox" value="4">
                                            <label for="class-4" class="form-check-label">Class 4</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="class-5" name="classes[]" class="form-check-input class-checkbox" value="5">
                                            <label for="class-5" class="form-check-label">Class 5</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="class-6" name="classes[]" class="form-check-input class-checkbox" value="6">
                                            <label for="class-6" class="form-check-label">Class 6</label>
                                        </div>
                                    </div>
                                    <div class="form-col">
                                        <div class="form-check">
                                            <input type="checkbox" id="class-7" name="classes[]" class="form-check-input class-checkbox" value="7">
                                            <label for="class-7" class="form-check-label">Class 7</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="class-8" name="classes[]" class="form-check-input class-checkbox" value="8">
                                            <label for="class-8" class="form-check-label">Class 8</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="class-9" name="classes[]" class="form-check-input class-checkbox" value="9">
                                            <label for="class-9" class="form-check-label">Class 9</label>
                                        </div>
                                    </div>
                                    <div class="form-col">
                                        <div class="form-check">
                                            <input type="checkbox" id="class-10" name="classes[]" class="form-check-input class-checkbox" value="10">
                                            <label for="class-10" class="form-check-label">Class 10</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="class-11" name="classes[]" class="form-check-input class-checkbox" value="11">
                                            <label for="class-11" class="form-check-label">Class 11</label>
                                        </div>
                                        <div class="form-check">
                                            <input type="checkbox" id="class-12" name="classes[]" class="form-check-input class-checkbox" value="12">
                                            <label for="class-12" class="form-check-label">Class 12</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="event-registration" class="form-label">Registration Required</label>
                                <div class="form-check">
                                    <input type="checkbox" id="event-registration" name="registration_required" class="form-check-input" value="1">
                                    <label for="event-registration" class="form-check-label">Yes, participants need to register</label>
                                </div>
                            </div>

                            <div id="registration-details" style="display: none;">
                                <div class="form-row">
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="reg-start-date" class="form-label">Registration Start</label>
                                            <input type="date" id="reg-start-date" name="reg_start_date" class="form-control">
                                        </div>
                                    </div>
                                    <div class="form-col">
                                        <div class="form-group">
                                            <label for="reg-end-date" class="form-label">Registration End</label>
                                            <input type="date" id="reg-end-date" name="reg_end_date" class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="max-participants" class="form-label">Max Participants</label>
                                    <input type="number" id="max-participants" name="max_participants" class="form-control" min="1">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Notification</label>
                                <div class="form-check">
                                    <input type="checkbox" id="send-email" name="send_email" class="form-check-input" value="1" checked>
                                    <label for="send-email" class="form-check-label">Send email notification</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="send-sms" name="send_sms" class="form-check-input" value="1">
                                    <label for="send-sms" class="form-check-label">Send SMS notification</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="show-portal" name="show_portal" class="form-check-input" value="1" checked>
                                    <label for="show-portal" class="form-check-label">Show on school portal</label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="button button-outline">Cancel</button>
                            <button type="submit" class="button">Create Event</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Manage Events Tab -->
            <div class="tab-content" id="manage-events">
                <div class="search-filter-bar">
                    <div class="search-container">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search events...">
                    </div>
                    <div class="filter-container">
                        <select class="form-select">
                            <option value="">All Event Types</option>
                            <option value="academic">Academic</option>
                            <option value="sports">Sports</option>
                            <option value="cultural">Cultural</option>
                            <option value="holiday">Holiday</option>
                            <option value="exam">Examination</option>
                            <option value="meeting">Meeting</option>
                            <option value="other">Other</option>
                        </select>
                        <select class="form-select">
                            <option value="">All Status</option>
                            <option value="upcoming">Upcoming</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">All Events</h2>
                            <p class="card-subtitle">Manage school events</p>
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
                                        <th>Event Title</th>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Venue</th>
                                        <th>Participants</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Annual Sports Day</td>
                                        <td>Mar 25, 2025</td>
                                        <td>Sports</td>
                                        <td>School Ground</td>
                                        <td>All Students</td>
                                        <td><span class="status-tag status-upcoming">Upcoming</span></td>
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
                                                    <div class="actions-item" onclick="viewEventDetails(1)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item" onclick="editEvent(1)">
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
                                                    <div class="actions-item danger" onclick="confirmDeleteEvent(1)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                        Delete
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Parent-Teacher Meeting</td>
                                        <td>Mar 18, 2025</td>
                                        <td>Meeting</td>
                                        <td>School Hall</td>
                                        <td>Parents & Teachers</td>
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
                                                    <div class="actions-item" onclick="viewEventDetails(2)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item" onclick="editEvent(2)">
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
                                                    <div class="actions-item danger" onclick="confirmDeleteEvent(2)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                        Delete
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Science Exhibition</td>
                                        <td>Mar 10, 2025</td>
                                        <td>Academic</td>
                                        <td>Science Block</td>
                                        <td>Class 8-12</td>
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
                                                    <div class="actions-item" onclick="viewEventDetails(3)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item" onclick="editEvent(3)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M12 20h9"></path>
                                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                        </svg>
                                                        Edit
                                                    </div>
                                                    <div class="actions-item danger" onclick="confirmDeleteEvent(3)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                        Delete
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Inter-School Debate</td>
                                        <td>Mar 05, 2025</td>
                                        <td>Cultural</td>
                                        <td>Auditorium</td>
                                        <td>Selected Students</td>
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
                                                    <div class="actions-item" onclick="viewEventDetails(4)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item" onclick="editEvent(4)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M12 20h9"></path>
                                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                                        </svg>
                                                        Edit
                                                    </div>
                                                    <div class="actions-item danger" onclick="confirmDeleteEvent(4)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                        Delete
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
                            Showing 1 to 4 of 12 events
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
                            <button class="pagination-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Send Reminders Tab -->
            <div class="tab-content" id="send-reminders">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Send Event Reminders</h2>
                            <p class="card-subtitle">Notify participants about upcoming events</p>
                        </div>
                    </div>
                    
              
                    <div class="card-body">
                        <form id="reminder-form" action="send_reminders.php" method="post">
                            <div class="form-group">
                                <label class="form-label">Select Event</label>
                                <select class="form-select" name="event_id" required>
                                    <option value="">Choose an event</option>
                                    <option value="1">Annual Sports Day (Mar 25, 2025)</option>
                                    <option value="2">Parent-Teacher Meeting (Mar 18, 2025)</option>
                                    <option value="5">Career Counseling Session (Apr 02, 2025)</option>
                                    <option value="6">Music Competition (Apr 10, 2025)</option>
                                </select>
                                <small class="form-text">Only upcoming events are listed</small>
                            </div>

                            <div class="reminder-form-group">
                                <label class="form-label">Reminder Type</label>
                                <div class="reminder-options">
                                    <div class="reminder-option selected" data-type="general">
                                        <div class="reminder-option-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                            </svg>
                                        </div>
                                        <div class="reminder-option-text">
                                            <div><strong>General Reminder</strong></div>
                                            <div>Notify about the upcoming event</div>
                                        </div>
                                    </div>
                                    <div class="reminder-option" data-type="schedule">
                                        <div class="reminder-option-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                            </svg>
                                        </div>
                                        <div class="reminder-option-text">
                                            <div><strong>Schedule Change</strong></div>
                                            <div>Notify about changes in timing or venue</div>
                                        </div>
                                    </div>
                                    <div class="reminder-option" data-type="requirements">
                                        <div class="reminder-option-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                                <polyline points="10 9 9 9 8 9"></polyline>
                                            </svg>
                                        </div>
                                        <div class="reminder-option-text">
                                            <div><strong>Requirements</strong></div>
                                            <div>Details about what to bring or prepare</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="reminder-form-group">
                                <label class="form-label">Reminder Message</label>
                                <textarea class="form-control" name="message" rows="5" required>This is a reminder about the upcoming event. Please make sure to attend on time.</textarea>
                                <div class="dynamic-templates" style="margin-top: 0.75rem;">
                                    <button type="button" class="button button-sm button-outline" onclick="insertTemplate('general')">General Template</button>
                                    <button type="button" class="button button-sm button-outline" onclick="insertTemplate('schedule')">Schedule Change Template</button>
                                    <button type="button" class="button button-sm button-outline" onclick="insertTemplate('requirements')">Requirements Template</button>
                                </div>
                            </div>

                            <div class="reminder-form-group">
                                <label class="form-label">Recipient Selection</label>
                                <div class="recipient-selection">
                                    <div class="recipient-option">
                                        <div class="form-check">
                                            <input type="checkbox" id="all-participants" name="all_participants" class="form-check-input" value="1" checked>
                                            <label for="all-participants" class="form-check-label"><strong>All Registered Participants</strong></label>
                                        </div>
                                    </div>
                                    <div class="recipient-option">
                                        <div class="form-check">
                                            <input type="checkbox" id="students" name="send_to_students" class="form-check-input" value="1">
                                            <label for="students" class="form-check-label">Students</label>
                                        </div>
                                    </div>
                                    <div class="recipient-option">
                                        <div class="form-check">
                                            <input type="checkbox" id="teachers" name="send_to_teachers" class="form-check-input" value="1">
                                            <label for="teachers" class="form-check-label">Teachers</label>
                                        </div>
                                    </div>
                                    <div class="recipient-option">
                                        <div class="form-check">
                                            <input type="checkbox" id="parents" name="send_to_parents" class="form-check-input" value="1">
                                            <label for="parents" class="form-check-label">Parents</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="reminder-form-group">
                                <label class="form-label">Delivery Method</label>
                                <div class="form-check">
                                    <input type="checkbox" id="reminder-email" name="send_email" class="form-check-input" value="1" checked>
                                    <label for="reminder-email" class="form-check-label">Email</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="reminder-sms" name="send_sms" class="form-check-input" value="1">
                                    <label for="reminder-sms" class="form-check-label">SMS</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="reminder-notification" name="send_notification" class="form-check-input" value="1" checked>
                                    <label for="reminder-notification" class="form-check-label">In-App Notification</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Schedule Reminder</label>
                                <div class="form-check">
                                    <input type="checkbox" id="send-now" name="send_now" class="form-check-input" value="1" checked>
                                    <label for="send-now" class="form-check-label">Send immediately</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" id="schedule-reminder" name="schedule_reminder" class="form-check-input" value="1">
                                    <label for="schedule-reminder" class="form-check-label">Schedule for later</label>
                                </div>
                                <div id="schedule-fields" style="display: none; margin-top: 1rem;">
                                    <div class="form-row">
                                        <div class="form-col">
                                            <input type="date" name="schedule_date" class="form-control">
                                        </div>
                                        <div class="form-col">
                                            <input type="time" name="schedule_time" class="form-control">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <button type="button" class="button button-outline">Cancel</button>
                        <button type="submit" form="reminder-form" class="button">Send Reminders</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Event Details Modal -->
    <div id="event-details-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Event Details</h3>
                <button class="modal-close" onclick="closeModal('event-details-modal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="event-details-grid">
                    <div class="event-detail-item">
                        <div class="event-detail-label">Event Title</div>
                        <div class="event-detail-value" id="modal-event-title">Annual Sports Day</div>
                    </div>
                    <div class="event-detail-item">
                        <div class="event-detail-label">Event Type</div>
                        <div class="event-detail-value" id="modal-event-type">Sports</div>
                    </div>
                    <div class="event-detail-item">
                        <div class="event-detail-label">Date</div>
                        <div class="event-detail-value" id="modal-event-date">March 25, 2025</div>
                    </div>
                    <div class="event-detail-item">
                        <div class="event-detail-label">Time</div>
                        <div class="event-detail-value" id="modal-event-time">08:00 AM - 04:00 PM</div>
                    </div>
                    <div class="event-detail-item">
                        <div class="event-detail-label">Venue</div>
                        <div class="event-detail-value" id="modal-event-venue">School Ground</div>
                    </div>
                    <div class="event-detail-item">
                        <div class="event-detail-label">Organizer</div>
                        <div class="event-detail-value" id="modal-event-organizer">Physical Education Department</div>
                    </div>
                </div>
                <div class="event-detail-item" style="margin-top: 1.5rem;">
                    <div class="event-detail-label">Description</div>
                    <div class="event-detail-value" id="modal-event-description">
                        Annual sports day celebration featuring track and field events, team sports competitions, and various athletic activities. All students are expected to participate in at least one event. Parents are invited to attend and cheer for their children.
                    </div>
                </div>
                <div class="event-detail-item" style="margin-top: 1.5rem;">
                    <div class="event-detail-label">Participants</div>
                    <div class="event-detail-value" id="modal-event-participants">All Students (Classes 1-12)</div>
                </div>
                <div class="event-detail-item" style="margin-top: 1.5rem;">
                    <div class="event-detail-label">Registration</div>
                    <div class="event-detail-value" id="modal-event-registration">Required (Deadline: March 20, 2025)</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-outline" onclick="closeModal('event-details-modal')">Close</button>
                <button type="button" class="button" onclick="editEvent(1)">Edit Event</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="delete-event-modal" class="modal">
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3 class="modal-title">Delete Event</h3>
                <button class="modal-close" onclick="closeModal('delete-event-modal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this event? This action cannot be undone.</p>
                <p><strong>Event: </strong><span id="delete-event-name">Annual Sports Day</span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-outline" onclick="closeModal('delete-event-modal')">Cancel</button>
                <button type="button" class="button" style="background-color: #ef4444;" onclick="deleteEvent()">Delete</button>
            </div>
        </div>
    </div>

    <script>
        // DOM ready function
        document.addEventListener('DOMContentLoaded', function() {
            initializeTabs();
            initializeRegistrationToggle();
            initializeReminderOptions();
            initializeDropdownActions();
            initializeScheduleToggle();
            initializeAllClassesToggle();
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
                    </svg>
                `;
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

        // Toggle registration fields
        function initializeRegistrationToggle() {
            const regCheckbox = document.getElementById('event-registration');
            const regDetails = document.getElementById('registration-details');
            
            if (regCheckbox && regDetails) {
                regCheckbox.addEventListener('change', function() {
                    regDetails.style.display = this.checked ? 'block' : 'none';
                });
            }
        }

        // Initialize All Classes Toggle
        function initializeAllClassesToggle() {
            const allClassesCheckbox = document.getElementById('all-classes');
            const classCheckboxes = document.querySelectorAll('.class-checkbox');
            
            if (allClassesCheckbox && classCheckboxes.length > 0) {
                allClassesCheckbox.addEventListener('change', function() {
                    classCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                        checkbox.disabled = this.checked;
                    });
                });
                
                classCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        if (!this.checked) {
                            allClassesCheckbox.checked = false;
                        }
                    });
                });
            }
        }

        // Reminder options functionality
        function initializeReminderOptions() {
            const reminderOptions = document.querySelectorAll('.reminder-option');
            
            if (reminderOptions.length > 0) {
                reminderOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        // Remove selected class from all options
                        reminderOptions.forEach(opt => opt.classList.remove('selected'));
                        // Add selected class to clicked option
                        this.classList.add('selected');
                        
                        // Set the reminder type in a hidden input
                        const reminderType = this.getAttribute('data-type');
                        document.getElementById('reminder-form').innerHTML += `<input type="hidden" name="reminder_type" value="${reminderType}">`;
                    });
                });
            }
        }

        // Schedule reminder toggle
        function initializeScheduleToggle() {
            const scheduleCheck = document.getElementById('schedule-reminder');
            const sendNowCheck = document.getElementById('send-now');
            const scheduleFields = document.getElementById('schedule-fields');
            
            if (scheduleCheck && sendNowCheck && scheduleFields) {
                scheduleCheck.addEventListener('change', function() {
                    if (this.checked) {
                        scheduleFields.style.display = 'block';
                        sendNowCheck.checked = false;
                    } else {
                        scheduleFields.style.display = 'none';
                    }
                });
                
                sendNowCheck.addEventListener('change', function() {
                    if (this.checked) {
                        scheduleCheck.checked = false;
                        scheduleFields.style.display = 'none';
                    }
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

        // View event details
        function viewEventDetails(eventId) {
            // In a real application, you would fetch event details via AJAX
            // For this demo, we'll just populate with static data
            document.getElementById('event-details-modal').classList.add('show');
            
            // Close any open action menus
            document.querySelectorAll('.actions-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        // Edit event
        function editEvent(eventId) {
            // In a real application, you would redirect to edit page or load edit modal
            // For this demo, we'll simulate by switching to the create tab with pre-filled data
            const createTab = document.querySelector('[data-tab="create-event"]');
            createTab.click();
            
            // Pre-fill form with event data (simulated)
            if (eventId === 1) {
                document.getElementById('event-title').value = 'Annual Sports Day';
                document.getElementById('event-type').value = 'sports';
                document.getElementById('event-date').value = '2025-03-25';
                document.getElementById('event-venue').value = 'School Ground';
                document.getElementById('event-description').value = 'Annual sports day celebration featuring track and field events, team sports competitions, and various athletic activities.';
            }
            
            // Close any open modals
            closeAllModals();
            
            // Close any open action menus
            document.querySelectorAll('.actions-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        // Confirm delete event
        function confirmDeleteEvent(eventId) {
            document.getElementById('delete-event-modal').classList.add('show');
            
            // Store event ID for delete operation
            document.getElementById('delete-event-modal').setAttribute('data-event-id', eventId);
            
            // Update event name in confirmation modal
            if (eventId === 1) {
                document.getElementById('delete-event-name').textContent = 'Annual Sports Day';
            } else if (eventId === 2) {
                document.getElementById('delete-event-name').textContent = 'Parent-Teacher Meeting';
            } else if (eventId === 3) {
                document.getElementById('delete-event-name').textContent = 'Science Exhibition';
            } else if (eventId === 4) {
                document.getElementById('delete-event-name').textContent = 'Inter-School Debate';
            }
            
            // Close any open action menus
            document.querySelectorAll('.actions-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        // Delete event
        function deleteEvent() {
            const eventId = document.getElementById('delete-event-modal').getAttribute('data-event-id');
            
            // In a real application, you would send a DELETE request to your server
            // For this demo, we'll just show an alert
            alert(`Event ID ${eventId} has been deleted.`);
            
            // Close the modal
            closeModal('delete-event-modal');
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

        // Insert reminder template
        function insertTemplate(type) {
            let template = '';
            
            if (type === 'general') {
                template = "This is a reminder about the upcoming [Event Name] scheduled on [Event Date] at [Event Time]. The event will take place at [Venue]. We look forward to your participation.";
            } else if (type === 'schedule') {
                template = "Important Notice: There has been a change in the schedule for [Event Name]. The event will now take place on [New Date] at [New Time] at [New Venue]. We apologize for any inconvenience caused.";
            } else if (type === 'requirements') {
                template = "For the upcoming [Event Name] on [Event Date], please ensure you bring the following items:\n\n1. [Item 1]\n2. [Item 2]\n3. [Item 3]\n\nPlease arrive 15 minutes before the scheduled time for registration.";
            }
            
            document.querySelector('[name="message"]').value = template;
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
    </script>
</body>
</html>