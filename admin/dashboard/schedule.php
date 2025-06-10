<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes, maximum-scale=5.0">
    <title>Exam Schedule</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/schedule.css">
</head>
<body>
    <!-- Scroll Progress Bar -->
    <div class="scroll-progress"></div>
    
    <!-- Quick Navigation Panel -->
    <div class="quick-nav" id="quickNav">
        <div class="quick-nav-item" data-tooltip="Search & Filters" onclick="scrollToSection('action-bar')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <div class="quick-nav-item" data-tooltip="Add New Exam" onclick="scrollToSection('form-container')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
        </div>
        <div class="quick-nav-item" data-tooltip="Exam Calendar" onclick="scrollToSection('calendar-container')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
        <div class="quick-nav-item" data-tooltip="Exam Table" onclick="scrollToSection('table-container')">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
            </svg>
        </div>
    </div>
    
    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" onclick="scrollToTop()">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>
    
    <!-- Keyboard Hints -->
    <div class="keyboard-hint" id="keyboardHint">
        Press Ctrl+Home for top, Ctrl+End for bottom
    </div>

    <div class="sidebar-overlay"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Exam Schedule</h1>
            <span class="header-path">Dashboard > Exams > Schedule</span>
        </header>

        <main class="dashboard-content">
            <!-- Mobile-friendly action bar -->
            <div class="action-bar" id="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="examSearch" class="search-input" placeholder="Search by exam name, subject, or class...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="filterToggleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        <span class="btn-text">Filter</span>
                    </button>
                    <button class="btn btn-outline">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span class="btn-text">Export</span>
                    </button>
                    <button class="btn btn-primary" id="newExamBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <span class="btn-text">Schedule</span>
                    </button>
                    
                    <!-- Workflow Helper Buttons -->
                    <div class="workflow-buttons" style="margin-left: auto; display: flex; gap: 0.5rem;">
                        <button class="btn btn-outline btn-sm" id="createSessionBtn">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="btn-text">Session</span>
                        </button>
                        <button class="btn btn-outline btn-sm" id="manageSubjectsBtn">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Manage Subjects
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel" style="display: none;">
                <h3 class="filter-title">Filter Exams</h3>
                <form class="filter-form">                    <div class="filter-group">
                        <label class="filter-label">Assessment Type</label>
                        <select class="filter-select" id="examTypeFilter">
                            <option value="">All Types</option>
                            <option value="FA">FA (Formative Assessment)</option>
                            <option value="SA">SA (Summative Assessment)</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Class/Grade</label>
                        <select class="filter-select" id="classFilter">
                            <option value="">All Classes</option>
                            <!-- Dynamic options will be loaded here -->
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Subject</label>
                        <select class="filter-select" id="subjectFilter">
                            <option value="">All Subjects</option>
                            <!-- Dynamic options will be loaded here -->
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Date Range</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" class="filter-input" id="startDateFilter" style="flex: 1;">
                            <span style="align-self: center;">to</span>
                            <input type="date" class="filter-input" id="endDateFilter" style="flex: 1;">
                        </div>
                    </div>
                </form>
                <div class="filter-actions">
                    <button class="filter-btn filter-btn-reset">Reset</button>
                    <button class="filter-btn filter-btn-apply">Apply Filters</button>
                </div>
            </div>

            <!-- Create/Edit Exam Form -->
            <div class="exam-form-container" id="examForm" style="display: none;">
                <h2 class="form-title">Schedule New Exam</h2>
                <form id="createExamForm">                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="examSessionId">Exam Session</label>
                            <select class="form-select" id="examSessionId" name="examSessionId">
                                <option value="">Select Existing Session (Recommended)</option>
                                <!-- Dynamic options will be loaded here -->
                            </select>
                            <small class="form-help">Select an existing exam session to add this subject to</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="examType">Assessment Type</label>
                            <select class="form-select" id="examType" name="examType">
                                <option value="">Select Assessment Type</option>
                                <option value="FA">FA (Formative Assessment)</option>
                                <option value="SA">SA (Summative Assessment)</option>
                            </select>
                            <small class="form-help">Only needed if creating without existing session</small>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="examSubject">Subject</label>
                            <select class="form-select" id="examSubject" name="examSubject">
                                <option value="">Select Subject</option>
                                <!-- Dynamic options will be loaded here -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="examClass">Class/Grade</label>
                            <select class="form-select" id="examClass" name="examClass">
                                <option value="">Select Class</option>
                                <!-- Dynamic options will be loaded here -->
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="examDate">Date</label>
                            <input type="date" class="form-input" id="examDate" name="examDate">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="examTime">Time</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="time" class="form-input" id="examStartTime" name="examStartTime" style="flex: 1;">
                                <span style="align-self: center;">to</span>
                                <input type="time" class="form-input" id="examEndTime" name="examEndTime" style="flex: 1;">
                            </div>
                        </div>
                    </div>
                    <!-- Proctor field removed - managed by exam session workflow -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="examDuration">Duration (minutes)</label>
                            <input type="number" class="form-input" id="examDuration" name="examDuration" placeholder="e.g. 90" min="15" max="240">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="maxMarks">Maximum Marks</label>
                            <input type="number" class="form-input" id="maxMarks" name="maxMarks" placeholder="e.g. 100" min="1">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label" for="examInstructions">Special Instructions</label>
                            <textarea class="form-textarea" id="examInstructions" name="examInstructions" placeholder="Enter any special instructions for this exam..."></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelExamBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Schedule Exam</button>
                    </div>
                </form>
            </div>

            <!-- View Tabs -->
            <div class="exam-tabs">
                <div class="exam-tab active" data-tab="calendar">Calendar View</div>
                <div class="exam-tab" data-tab="list">List View</div>
                <div class="exam-tab" data-tab="table">Table View</div>
            </div>

            <!-- Calendar View Tab -->
            <div class="tab-content active" id="calendar-tab">
                <div class="calendar-view">
                    <div class="calendar-header">
                        <h3 class="calendar-title">Current Month</h3>
                        <div class="calendar-navigation">
                            <button class="calendar-nav-btn">
                                <svg class="calendar-nav-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <select class="calendar-month-select">
                                <!-- Dynamic month options will be loaded here -->
                            </select>
                            <button class="calendar-nav-btn">
                                <svg class="calendar-nav-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Calendar Legend -->
                    <div class="calendar-legend" id="calendarLegend" style="display: none;">
                        <h4>📚 Subject Legend</h4>
                        <div class="legend-grid" id="legendGrid">
                            <!-- Legend items will be dynamically populated -->
                        </div>
                    </div>
                    
                    <div class="calendar-grid">
                        <div class="calendar-weekday">Sun</div>
                        <div class="calendar-weekday">Mon</div>
                        <div class="calendar-weekday">Tue</div>
                        <div class="calendar-weekday">Wed</div>
                        <div class="calendar-weekday">Thu</div>
                        <div class="calendar-weekday">Fri</div>
                        <div class="calendar-weekday">Sat</div>
                        
                        <!-- Week 1 -->
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">24</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">25</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">26</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">27</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">28</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">1</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">2</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        
                        <!-- Week 2 -->
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">3</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">4</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">5</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">6</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">7</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">8</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">9</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        
                        <!-- Week 3 -->
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">10</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">11</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">12</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">13</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">14</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-current">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">15</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">16</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        
                        <!-- Week 4 -->
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">17</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">18</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">19</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">20</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">21</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">22</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">23</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        
                        <!-- Week 5 -->
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">24</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">25</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">26</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">27</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">28</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">29</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">30</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        
                        <!-- Week 6 -->
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">31</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">1</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">2</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">3</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">4</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">5</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                        <div class="calendar-day calendar-day-other-month">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">6</div>
                            </div>
                            <div class="calendar-day-events"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List View Tab -->
            <div class="tab-content" id="list-tab">
                <div class="upcoming-exams">
                    <h3 class="upcoming-title">Upcoming Exams</h3>
                    
                    <div class="exam-timeline">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                </div>
            </div>
            </div>

            <!-- Table View Tab -->
            <div class="tab-content" id="table-tab">
                <div class="table-view">
                    <table class="exams-table">                        <thead>
                            <tr>
                                <th>Assessment</th>
                                <th>Date & Time</th>
                                <th>Class</th>                                <th>Proctor</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic content will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the page
            initializePage();
            loadExamSessions();
            loadScheduleData();
            
            // Tab Switching
            const tabs = document.querySelectorAll('.exam-tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                    
                    // Reload data for the selected tab
                    loadScheduleData(tabId);
                });
            });
            
            // Filter Panel Toggle
            const filterToggleBtn = document.getElementById('filterToggleBtn');
            const filterPanel = document.getElementById('filterPanel');
            
            filterToggleBtn.addEventListener('click', function() {
                filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
            });
            
            // Schedule New Exam Form Toggle
            const newExamBtn = document.getElementById('newExamBtn');
            const examForm = document.getElementById('examForm');
            const cancelExamBtn = document.getElementById('cancelExamBtn');
            
            newExamBtn.addEventListener('click', function() {
                // Reset form and change title/button text
                document.getElementById('createExamForm').reset();
                document.querySelector('.form-title').textContent = 'Schedule New Exam';
                document.querySelector('.form-actions .btn-primary').textContent = 'Schedule Exam';
                
                // Load dynamic dropdown data
                loadDropdownData();
                
                // Show form
                examForm.style.display = 'block';
                
                // Add workflow guidance
                showWorkflowGuidance();
                
                // Scroll to form
                examForm.scrollIntoView({ behavior: 'smooth' });
            });
            
            // Exam Session Selection Handler
            document.addEventListener('change', function(e) {
                if (e.target.id === 'examSessionId') {
                    const sessionId = e.target.value;
                    if (sessionId) {
                        // Session selected - simplify form
                        document.getElementById('examType').disabled = true;
                        document.getElementById('examClass').disabled = true;
                        document.querySelector('label[for="examType"]').style.opacity = '0.5';
                        document.querySelector('label[for="examClass"]').style.opacity = '0.5';
                        document.querySelector('.form-help').textContent = 'Adding subject to existing session';
                    } else {
                        // No session - enable manual fields
                        document.getElementById('examType').disabled = false;
                        document.getElementById('examClass').disabled = false;
                        document.querySelector('label[for="examType"]').style.opacity = '1';
                        document.querySelector('label[for="examClass"]').style.opacity = '1';
                        document.querySelector('.form-help').textContent = 'Only needed if creating without existing session';
                    }
                }
            });
            
            cancelExamBtn.addEventListener('click', function() {
                examForm.style.display = 'none';
            });
            
            // Workflow Helper Buttons
            const createSessionBtn = document.getElementById('createSessionBtn');
            const manageSubjectsBtn = document.getElementById('manageSubjectsBtn');
            
            createSessionBtn.addEventListener('click', function() {
                // Visual feedback
                this.innerHTML = '<svg class="btn-icon animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg> Redirecting...';
                this.disabled = true;
                
                // Navigate to exam session management in same tab
                window.location.href = 'exam_session_management.php';
            });
            
            manageSubjectsBtn.addEventListener('click', function() {
                // Check if we have any exam sessions first
                fetch('schedule_handler.php?action=get_exam_sessions')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.sessions && data.sessions.length > 0) {
                            // Show session selection dialog
                            showSessionSelectionForSubjects(data.sessions);
                        } else {
                            alert('No exam sessions available. Please create an exam session first.');
                        }
                    })
                    .catch(error => {
                        console.error('Error checking sessions:', error);
                        alert('Error checking available sessions. Please try again.');
                    });
            });
            
            // Form Submission
            const createExamForm = document.getElementById('createExamForm');
              createExamForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Show loading state
                const submitBtn = this.querySelector('.btn-primary');
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Saving...';
                submitBtn.disabled = true;
                
                // Get form data
                const formData = new FormData(this);
                
                // Check if this is an update operation
                const examId = this.getAttribute('data-exam-id');
                if (examId) {
                    formData.append('action', 'update_exam_schedule');
                    formData.append('exam_id', examId);
                } else {
                    formData.append('action', 'create_exam_schedule');
                }                  // Validate form fields (updated for exam session workflow)
                const requiredFields = ['examSessionId', 'examSubject', 'examDate', 
                    'examStartTime', 'examEndTime', 'examDuration'];
                
                for (let field of requiredFields) {
                    if (!formData.get(field)) {
                        alert('Please fill in all required fields');
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                        return;
                    }
                }
                
                // Check if start time is before end time
                const startTime = formData.get('examStartTime');
                const endTime = formData.get('examEndTime');
                if (startTime >= endTime) {
                    alert('Start time must be before end time');
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                    return;
                }
                
                // Submit form via AJAX
                fetch('schedule_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const action = examId ? 'updated' : 'scheduled';
                        alert(`Exam ${action} successfully!`);
                        examForm.style.display = 'none';
                        this.reset();
                        this.removeAttribute('data-exam-id');
                        loadScheduleData(); // Reload the schedule data
                    } else if (data.action_required === 'create_or_select_session') {
                        // Handle workflow guidance response
                        showSessionSelectionDialog(data);
                    } else {
                        alert('Error: ' + (data.message || 'Failed to save exam'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while saving the exam');
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });
            // Calendar Navigation
            const calendarNavBtns = document.querySelectorAll('.calendar-nav-btn');
            const calendarMonthSelect = document.querySelector('.calendar-month-select');
            
            calendarNavBtns[0].addEventListener('click', function() {
                // Previous month
                const currentIndex = calendarMonthSelect.selectedIndex;
                if (currentIndex > 0) {
                    calendarMonthSelect.selectedIndex = currentIndex - 1;
                    calendarMonthSelect.dispatchEvent(new Event('change'));
                }
            });
            
            calendarNavBtns[1].addEventListener('click', function() {
                // Next month
                const currentIndex = calendarMonthSelect.selectedIndex;
                if (currentIndex < calendarMonthSelect.options.length - 1) {
                    calendarMonthSelect.selectedIndex = currentIndex + 1;
                    calendarMonthSelect.dispatchEvent(new Event('change'));
                }
            });
            
            calendarMonthSelect.addEventListener('change', function() {
                const selectedMonth = this.options[this.selectedIndex].text;
                document.querySelector('.calendar-title').textContent = selectedMonth;
                loadScheduleData('calendar');
            });
            
            // Search functionality
            const searchInput = document.getElementById('examSearch');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                filterScheduleData(searchTerm);
            });
            
            // Filter functionality
            const filterApplyBtn = document.querySelector('.filter-btn-apply');
            const filterResetBtn = document.querySelector('.filter-btn-reset');
            
            filterApplyBtn.addEventListener('click', applyFilters);
            filterResetBtn.addEventListener('click', resetFilters);
        });
        
        // Dynamic action button handling using event delegation
        document.addEventListener('click', function(e) {
            // Handle view details buttons
            if (e.target.closest('.action-btn[title="View Details"]')) {
                const button = e.target.closest('.action-btn[title="View Details"]');
                const examId = button.getAttribute('data-exam-id');
                if (examId) {
                    showExamDetails(examId);
                }
            }
            
            // Handle edit buttons
            if (e.target.closest('.action-btn[title="Edit"], .card-action-btn.action-edit')) {
                const button = e.target.closest('.action-btn[title="Edit"], .card-action-btn.action-edit');
                const examId = button.getAttribute('data-exam-id');
                if (examId) {
                    editExam(examId);
                }
            }
            
            // Handle delete buttons
            if (e.target.closest('.action-btn[title="Delete"], .card-action-btn.action-delete')) {
                const button = e.target.closest('.action-btn[title="Delete"], .card-action-btn.action-delete');
                const examId = button.getAttribute('data-exam-id');
                const examName = button.getAttribute('data-exam-name') || 'this exam';
                if (examId) {
                    deleteExam(examId, examName);
                }
            }
            
            // Handle calendar event clicks
            if (e.target.closest('.calendar-event')) {
                const event = e.target.closest('.calendar-event');
                const examId = event.getAttribute('data-exam-id');
                if (examId) {
                    showExamDetails(examId);
                }
            }
        });
        
        // Helper Functions
        function initializePage() {
            // Set up initial page state
            const currentDate = new Date();
            const currentMonth = currentDate.getMonth() + 1;
            const currentYear = currentDate.getFullYear();
            
            // Set default date inputs
            document.getElementById('examDate').setAttribute('min', new Date().toISOString().split('T')[0]);
            
            // Populate calendar month selector
            populateCalendarMonths(currentYear, currentMonth);
            
            // Set initial calendar title
            const currentMonthName = currentDate.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            document.querySelector('.calendar-title').textContent = currentMonthName;
            
            // Load dynamic dropdown data for filters
            loadFilterDropdownData();
        }
        
        function loadExamSessions() {
            fetch('schedule_handler.php?action=get_exam_sessions')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.sessions) {
                        populateExamSessionDropdown(data.sessions);
                    }
                })
                .catch(error => console.error('Error loading exam sessions:', error));
        }
        
        function loadScheduleData(view = 'calendar') {
            const selectedDate = document.querySelector('.calendar-month-select')?.value || 
                                `${new Date().getFullYear()}-${(new Date().getMonth() + 1).toString().padStart(2, '0')}`;
            
            const params = new URLSearchParams({
                action: 'get_schedule_data',
                view: view,
                month: selectedDate.split('-')[1],
                year: selectedDate.split('-')[0]
            });
            
            fetch(`schedule_handler.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (view === 'calendar') {
                            updateCalendarView(data.events, data.sessions);
                            // Show/hide legend based on events
                            const legend = document.getElementById('calendarLegend');
                            if (data.events.length > 0) {
                                legend.style.display = 'block';
                            } else {
                                legend.style.display = 'none';
                            }
                        } else if (view === 'list') {
                            updateListView(data.events);
                        } else if (view === 'table') {
                            updateTableView(data.events);
                        }
                    }
                })
                .catch(error => console.error('Error loading schedule data:', error));
        }
                   function loadDropdownData() {
            // Load exam sessions, classes, and subjects from the database
            Promise.all([
                fetch('schedule_handler.php?action=get_exam_sessions'),
                fetch('schedule_handler.php?action=get_classes'),
                fetch('schedule_handler.php?action=get_subjects')
            ])
            .then(responses => Promise.all(responses.map(r => r.json())))
            .then(data => {
                populateDropdowns(data);
            })
            .catch(error => console.error('Error loading dropdown data:', error));
        }
          function populateDropdowns(data) {
            const [examSessions, classes, subjects] = data;
            
            if (examSessions.success) {
                populateExamSessions('examSessionId', examSessions.data);
            }
            if (classes.success) {
                populateSelect('examClass', classes.data, 'class_id', 'class_name');
            }
            if (subjects.success) {
                populateSelect('examSubject', subjects.data, 'subject_id', 'subject_name');
            }
        }
        
        function populateExamSessions(selectId, sessions) {
            const select = document.getElementById(selectId);
            const defaultOption = select.querySelector('option[value=""]');
            select.innerHTML = '';
            if (defaultOption) {
                select.appendChild(defaultOption);
            }
            
            sessions.forEach(session => {
                const option = document.createElement('option');
                option.value = session.id;
                option.textContent = `${session.session_name} (${session.session_type}) - ${session.class_name || 'Multiple Classes'}`;
                select.appendChild(option);
            });
        }
        
        function populateSelect(selectId, data, valueField, textField) {
            const select = document.getElementById(selectId);
            const defaultOption = select.querySelector('option[value=""]');
            select.innerHTML = '';
            if (defaultOption) {
                select.appendChild(defaultOption);
            }
            
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item[valueField];
                option.textContent = item[textField];
                select.appendChild(option);
            });
        }
        
        function populateCalendarMonths(currentYear, currentMonth) {
            const monthSelect = document.querySelector('.calendar-month-select');
            if (!monthSelect) return;
            
            monthSelect.innerHTML = '';
            
            // Generate options for 6 months (3 previous, current, 2 future)
            for (let i = -3; i <= 2; i++) {
                const date = new Date(currentYear, currentMonth - 1 + i, 1);
                const monthValue = date.getMonth() + 1;
                const yearValue = date.getFullYear();
                const monthName = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                
                const option = document.createElement('option');
                option.value = `${yearValue}-${monthValue.toString().padStart(2, '0')}`;
                option.textContent = monthName;
                if (i === 0) option.selected = true; // Select current month
                
                monthSelect.appendChild(option);
            }
        }
        
        function loadFilterDropdownData() {
            // Load data for filter dropdowns
            Promise.all([
                fetch('schedule_handler.php?action=get_classes'),
                fetch('schedule_handler.php?action=get_subjects')
            ])
            .then(responses => Promise.all(responses.map(r => r.json())))
            .then(data => {
                const [classes, subjects] = data;
                
                if (classes.success) {
                    populateFilterSelect('classFilter', classes.data, 'class_id', 'class_name', 'All Classes');
                }
                if (subjects.success) {
                    populateFilterSelect('subjectFilter', subjects.data, 'subject_id', 'subject_name', 'All Subjects');
                }
            })
            .catch(error => console.error('Error loading filter dropdown data:', error));
        }
        
        function populateFilterSelect(selectId, data, valueField, textField, defaultText) {
            const select = document.getElementById(selectId);
            if (!select) return;
            
            select.innerHTML = `<option value="">${defaultText}</option>`;
            
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item[valueField];
                option.textContent = item[textField];
                select.appendChild(option);
            });
        }
        
        function updateCalendarView(events, sessions) {
            // Clear existing events
            document.querySelectorAll('.calendar-event').forEach(event => event.remove());
            
            // Add session information to calendar header if available
            if (sessions && sessions.length > 0) {
                updateCalendarHeader(sessions);
            }
            
            // Collect subjects for legend (will be deduplicated in updateCalendarLegend)
            const subjects = [];
            
            // Add new events to calendar
            events.forEach(event => {
                const eventDate = new Date(event.exam_date);
                const dayElement = findCalendarDay(eventDate.getDate());
                
                if (dayElement) {
                    const eventElement = document.createElement('div');
                    
                    // Get color scheme from loaded subjects or generate default
                    let colorScheme = null;
                    if (window.subjectColorMap && window.subjectColorMap.has(event.subject_name)) {
                        colorScheme = window.subjectColorMap.get(event.subject_name).colors;
                    }
                    
                    // Generate subject-specific class name
                    const subjectClass = generateSubjectClass(event.subject_name);
                    eventElement.className = `calendar-event ${subjectClass}`;
                    
                    // Apply dynamic colors if available
                    if (colorScheme) {
                        eventElement.style.cssText = `
                            background: ${colorScheme.bg};
                            color: ${colorScheme.color};
                            border-left: 3px solid ${colorScheme.border};
                            padding: 0.25rem 0.5rem;
                            margin-bottom: 0.25rem;
                            border-radius: 4px;
                            font-size: 0.65rem;
                            font-weight: 500;
                            cursor: pointer;
                            transition: all 0.2s ease;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                        `;
                        
                        // Add hover effect
                        eventElement.addEventListener('mouseenter', function() {
                            this.style.transform = 'translateX(2px)';
                            this.style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.15)';
                        });
                        
                        eventElement.addEventListener('mouseleave', function() {
                            this.style.transform = 'translateX(0)';
                            this.style.boxShadow = 'none';
                        });
                    }
                    
                    // Add to subjects array (deduplication will happen in legend function)
                    subjects.push({
                        name: event.subject_name,
                        class: subjectClass
                    });
                    
                    // Use responsive text based on screen size
                    const eventText = getResponsiveEventText({
                        subject_name: event.subject_name,
                        subject_abbr: event.subject_name.substring(0, 3).toUpperCase(),
                        class_name: event.class_name
                    });
                    eventElement.textContent = eventText;
                    
                    // Store event data for tooltip
                    eventElement.dataset.eventData = JSON.stringify({
                        subject: event.subject_name,
                        class: event.class_name,
                        session: event.session_name,
                        type: event.session_type,
                        date: event.exam_date,
                        time: event.exam_time,
                        duration: event.duration_minutes || 60,
                        totalMarks: event.total_marks,
                        passingMarks: event.passing_marks
                    });
                    
                    // Store event data for responsive text updates
                    eventElement.dataset.event = JSON.stringify({
                        subject_name: event.subject_name,
                        subject_abbr: event.subject_name.substring(0, 3).toUpperCase(),
                        class_name: event.class_name
                    });
                    
                    // Add enhanced event listeners
                    eventElement.addEventListener('mouseenter', showEventTooltip);
                    eventElement.addEventListener('mouseleave', hideEventTooltip);
                    eventElement.addEventListener('click', () => showExamDetails(event.id));
                    
                    dayElement.querySelector('.calendar-day-events').appendChild(eventElement);
                }
            });
            
            // Update legend
            updateCalendarLegend(subjects);
        }
        
        function updateCalendarHeader(sessions) {
            // Update calendar title with session information
            const titleElement = document.querySelector('.calendar-title');
            if (titleElement && sessions.length > 0) {
                const currentMonth = titleElement.textContent;
                const activeSession = sessions.find(s => s.status === 'active') || sessions[0];
                
                if (activeSession) {
                    const sessionSpan = `${new Date(activeSession.start_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})} - ${new Date(activeSession.end_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}`;
                    
                    // Add session info below calendar title
                    let sessionInfoDiv = document.querySelector('.calendar-session-info');
                    if (!sessionInfoDiv) {
                        sessionInfoDiv = document.createElement('div');
                        sessionInfoDiv.className = 'calendar-session-info';
                        sessionInfoDiv.style.cssText = `
                            font-size: 14px; 
                            color: #667eea; 
                            margin-top: 5px; 
                            font-weight: 500;
                        `;
                        titleElement.parentNode.appendChild(sessionInfoDiv);
                    }
                    
                    sessionInfoDiv.innerHTML = `
                        <div style="margin-bottom: 3px;">
                            📅 <strong>${activeSession.session_name}</strong> (${activeSession.session_type})
                        </div>
                        <div style="font-size: 12px; color: #718096;">
                            Exam Period: ${sessionSpan}
                        </div>
                    `;
                }
            }
        }
        
        function findCalendarDay(dayNumber) {
            const calendarDays = document.querySelectorAll('.calendar-day:not(.calendar-day-other-month)');
            return Array.from(calendarDays).find(day => {
                const dayNum = day.querySelector('.calendar-day-number');
                return dayNum && parseInt(dayNum.textContent) === dayNumber;
            });
        }
        
        function generateSubjectClass(subjectName) {
            // Generate a consistent class name based on subject name
            // Handle cases where subjectName might be undefined, null, or not a string
            if (!subjectName || typeof subjectName !== 'string') {
                console.warn('Invalid subject name provided to generateSubjectClass:', subjectName);
                return 'event-subject-unknown';
            }
            
            const normalized = subjectName.toLowerCase()
                .replace(/[^a-z0-9\s]/g, '')
                .replace(/\s+/g, '_')
                .substring(0, 15); // Limit length
            
            return `event-subject-${normalized}`;
        }
        
        // Fetch subjects directly from database and create legend
        function loadSubjectsForLegend() {
            fetch('schedule_handler.php?action=get_all_subjects')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.subjects) {
                        console.log('Subjects loaded for legend:', data.subjects);
                        createSubjectLegend(data.subjects);
                    } else {
                        console.error('Failed to load subjects for legend:', data.message || 'Unknown error');
                        // Hide legend if no subjects are available
                        const legend = document.getElementById('calendarLegend');
                        if (legend) {
                            legend.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading subjects for legend:', error);
                    // Hide legend on error
                    const legend = document.getElementById('calendarLegend');
                    if (legend) {
                        legend.style.display = 'none';
                    }
                });
        }
        
        function createSubjectLegend(subjects) {
            const legend = document.getElementById('calendarLegend');
            const legendGrid = document.getElementById('legendGrid');
            
            if (!subjects || subjects.length === 0) {
                legend.style.display = 'none';
                return;
            }
            
            legend.style.display = 'block';
            legendGrid.innerHTML = '';
            
            // Predefined color schemes for different subjects
            const subjectColors = [
                { bg: '#fef2f2', color: '#dc2626', border: '#ef4444' }, // Red
                { bg: '#f0f9ff', color: '#0284c7', border: '#0ea5e9' }, // Blue
                { bg: '#f0fdf4', color: '#16a34a', border: '#22c55e' }, // Green
                { bg: '#fefce8', color: '#ca8a04', border: '#eab308' }, // Yellow
                { bg: '#fdf4ff', color: '#c026d3', border: '#d946ef' }, // Magenta
                { bg: '#f1f5f9', color: '#475569', border: '#64748b' }, // Slate
                { bg: '#fff7ed', color: '#ea580c', border: '#f97316' }, // Orange
                { bg: '#ecfdf5', color: '#059669', border: '#10b981' }, // Emerald
                { bg: '#fdf2f8', color: '#be185d', border: '#ec4899' }, // Pink
                { bg: '#fefbeb', color: '#b45309', border: '#d97706' }, // Amber
                { bg: '#f0fdfa', color: '#0d9488', border: '#14b8a6' }, // Teal
                { bg: '#f6ffed', color: '#389e0d', border: '#52c41a' }, // Lime
                { bg: '#fff2e8', color: '#d4380d', border: '#ff7a45' }, // Red-Orange
                { bg: '#e6f7ff', color: '#0958d9', border: '#1890ff' }, // Light Blue
                { bg: '#f9f0ff', color: '#722ed1', border: '#9254de' }, // Purple
            ];
            
            // Create legend items for all subjects with assigned colors
            subjects.forEach((subject, index) => {
                // Validate subject data - check for both 'name' and 'subject_name' fields
                const subjectName = subject?.name || subject?.subject_name;
                if (!subject || !subjectName) {
                    console.warn('Invalid subject data:', subject);
                    return; // Skip this subject
                }
                
                const colorScheme = subjectColors[index % subjectColors.length];
                const subjectClass = generateSubjectClass(subjectName);
                
                // Store color mapping for use in calendar events
                if (!window.subjectColorMap) {
                    window.subjectColorMap = new Map();
                }
                window.subjectColorMap.set(subjectName, {
                    class: subjectClass,
                    colors: colorScheme
                });
                
                const legendItem = document.createElement('div');
                legendItem.className = 'legend-item';
                legendItem.style.cssText = `
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    padding: 4px 8px;
                    border-radius: 4px;
                    background: ${colorScheme.bg};
                    color: ${colorScheme.color};
                    border-left: 3px solid ${colorScheme.border};
                    font-size: 0.75rem;
                    font-weight: 500;
                `;
                
                const colorBox = document.createElement('div');
                colorBox.style.cssText = `
                    width: 12px;
                    height: 12px;
                    border-radius: 2px;
                    background: ${colorScheme.color};
                    border: 1px solid ${colorScheme.border};
                    flex-shrink: 0;
                `;
                
                const label = document.createElement('span');
                label.textContent = subjectName;
                label.style.cssText = `
                    font-size: 0.75rem;
                    font-weight: 500;
                    color: ${colorScheme.color};
                `;
                
                legendItem.appendChild(colorBox);
                legendItem.appendChild(label);
                legendGrid.appendChild(legendItem);
            });
        }
        
        function updateCalendarLegend(subjects) {
            // This function is now primarily for compatibility
            // The main legend is loaded from database
            loadSubjectsForLegend();
        }
        
        let currentTooltip = null;
        
        function showEventTooltip(event) {
            // Remove existing tooltip
            if (currentTooltip) {
                currentTooltip.remove();
            }
            
            const element = event.target;
            const eventData = JSON.parse(element.dataset.eventData);
            
            // Create tooltip
            const tooltip = document.createElement('div');
            tooltip.className = 'calendar-event-tooltip show';
            
            const formatTime = (time) => {
                if (!time) return 'Not specified';
                return new Date(`2000-01-01 ${time}`).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            };
            
            const formatDate = (date) => {
                return new Date(date).toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            };
            
            tooltip.innerHTML = `
                <div class="tooltip-title">${eventData.type} - ${eventData.subject}</div>
                <div class="tooltip-details">
                    <div>📚 <strong>Subject:</strong> ${eventData.subject}</div>
                    <div>🏫 <strong>Class:</strong> ${eventData.class}</div>
                    <div>📋 <strong>Session:</strong> ${eventData.session}</div>
                    <div>📅 <strong>Date:</strong> ${formatDate(eventData.date)}</div>
                    <div>⏰ <strong>Time:</strong> ${formatTime(eventData.time)}</div>
                    <div>⏱️ <strong>Duration:</strong> ${eventData.duration} minutes</div>
                    ${eventData.totalMarks ? `<div>📝 <strong>Total Marks:</strong> ${eventData.totalMarks}</div>` : ''}
                    ${eventData.passingMarks ? `<div>✅ <strong>Passing Marks:</strong> ${eventData.passingMarks}</div>` : ''}
                </div>
            `;
            
            document.body.appendChild(tooltip);
            currentTooltip = tooltip;
            
            // Position tooltip
            const rect = element.getBoundingClientRect();
            const tooltipRect = tooltip.getBoundingClientRect();
            
            let left = rect.left + (rect.width / 2) - (tooltipRect.width / 2);
            let top = rect.top - tooltipRect.height - 12;
            
            // Adjust if tooltip goes off screen
            if (left < 10) left = 10;
            if (left + tooltipRect.width > window.innerWidth - 10) {
                left = window.innerWidth - tooltipRect.width - 10;
            }
            if (top < 10) {
                top = rect.bottom + 12;
                tooltip.style.transform = 'translateY(8px)';
            }
            
            tooltip.style.left = `${left}px`;
            tooltip.style.top = `${top}px`;
        }
        
        function hideEventTooltip() {
            if (currentTooltip) {
                currentTooltip.classList.remove('show');
                setTimeout(() => {
                    if (currentTooltip) {
                        currentTooltip.remove();
                        currentTooltip = null;
                    }
                }, 200);
            }
        }
        
        function showExamDetails(examId) {
            fetch(`schedule_handler.php?action=get_exam_details&id=${examId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayExamDetails(data.exam);
                    } else {
                        alert('Error loading exam details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading exam details');
                });
        }
        
        function displayExamDetails(exam) {
            const details = `
                Assessment: ${exam.exam_type} - ${exam.subject_name} Assessment
                Subject: ${exam.subject_name}
                Class: ${exam.class_name}
                Date: ${exam.exam_date}
                Time: ${exam.start_time} - ${exam.end_time}
                Duration: ${exam.duration} minutes
                Max Marks: ${exam.max_marks}
                Instructions: ${exam.instructions || 'None'}
            `;
            alert(details);
        }
        
        function editExam(examId) {
            fetch(`schedule_handler.php?action=get_exam_details&id=${examId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        populateEditForm(data.exam);
                        document.getElementById('examForm').style.display = 'block';
                        document.getElementById('examForm').scrollIntoView({ behavior: 'smooth' });
                    } else {
                        alert('Error loading exam details for editing');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading exam details');
                });
        }
        
        function populateEditForm(exam) {
            document.getElementById('examType').value = exam.exam_type;
            document.getElementById('examSubject').value = exam.subject_id;
            document.getElementById('examClass').value = exam.class_id;
            document.getElementById('examDate').value = exam.exam_date;
            document.getElementById('examStartTime').value = exam.start_time;
            document.getElementById('examEndTime').value = exam.end_time;
            // Proctor field removed - managed by exam session workflow
            document.getElementById('examDuration').value = exam.duration;
            document.getElementById('maxMarks').value = exam.max_marks;
            document.getElementById('examInstructions').value = exam.instructions;
            
            // Update form title and button
            document.querySelector('.form-title').textContent = `Edit ${exam.exam_type} Assessment`;
            document.querySelector('.form-actions .btn-primary').textContent = 'Update Exam';
            
            // Store exam ID for update
            document.getElementById('createExamForm').setAttribute('data-exam-id', exam.id);
        }
        
        function deleteExam(examId, examName) {
            if (confirm(`Are you sure you want to cancel ${examName}?`)) {
                const formData = new FormData();
                formData.append('action', 'delete_exam_schedule');
                formData.append('exam_id', examId);
                
                fetch('schedule_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`${examName} has been cancelled.`);
                        loadScheduleData(); // Reload the schedule
                    } else {
                        alert('Error: ' + (data.message || 'Failed to cancel exam'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while cancelling the exam');
                });
            }
        }
        
        function filterScheduleData(searchTerm) {
            const tableRows = document.querySelectorAll('.exams-table tbody tr');
            const examCards = document.querySelectorAll('.timeline-exam-card');
            const calendarEvents = document.querySelectorAll('.calendar-event');
            
            // Filter table rows
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
            
            // Filter exam cards
            examCards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? '' : 'none';
            });
            
            // Highlight calendar events
            calendarEvents.forEach(event => {
                const text = event.textContent.toLowerCase();
                if (searchTerm && text.includes(searchTerm)) {
                    event.style.background = '#ffff00';
                    event.style.color = '#000000';
                } else {
                    event.style.background = '';
                    event.style.color = '';
                }
            });
        }
        
        function applyFilters() {
            const filters = {
                examType: document.getElementById('examTypeFilter').value,
                class: document.getElementById('classFilter').value,
                subject: document.getElementById('subjectFilter').value,
                startDate: document.getElementById('startDateFilter').value,
                endDate: document.getElementById('endDateFilter').value
            };
            
            const params = new URLSearchParams({
                action: 'get_schedule_data',
                ...filters
            });
            
            fetch(`schedule_handler.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const activeTab = document.querySelector('.exam-tab.active').getAttribute('data-tab');
                        if (activeTab === 'calendar') {
                            updateCalendarView(data.events);
                        } else if (activeTab === 'list') {
                            updateListView(data.events);
                        } else if (activeTab === 'table') {
                            updateTableView(data.events);
                        }
                    }
                })
                .catch(error => console.error('Error applying filters:', error));
            
            // Hide filter panel
            document.getElementById('filterPanel').style.display = 'none';
        }
          function resetFilters() {
            document.querySelector('.filter-form').reset();
            loadScheduleData();
        }
        
        function updateListView(events) {
            const listContainer = document.getElementById('list-tab');
            if (!listContainer) return;
            
            let html = `
            <div class="upcoming-exams">
                <h3 class="upcoming-title">Upcoming Exams</h3>
                <div class="exam-timeline">`;
            
            if (events.length === 0) {
                html += '<p style="text-align: center; padding: 2rem;">No exams scheduled</p>';
            } else {
                // Group events by date
                const eventsByDate = {};
                events.forEach(event => {
                    const date = event.exam_date;
                    if (!eventsByDate[date]) {
                        eventsByDate[date] = [];
                    }
                    eventsByDate[date].push(event);
                });
                
                // Generate HTML for each date group
                Object.keys(eventsByDate).forEach(date => {
                    const formattedDate = new Date(date).toLocaleDateString('en-US', { 
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
                    });
                    
                    html += `
                    <div class="timeline-date-group">
                        <h4 class="timeline-date">${formattedDate}</h4>
                        <div class="timeline-exams">`;
                    
                    eventsByDate[date].forEach(event => {
                        html += `
                            <div class="timeline-exam-card">
                                <div class="exam-card-header">
                                    <h5 class="exam-card-title">${event.exam_type} - ${event.subject_name} Assessment</h5>
                                    <span class="exam-card-badge badge-${event.exam_type.toLowerCase()}">${event.exam_type}</span>
                                    <div class="exam-card-actions">
                                        <button class="card-action-btn action-edit" data-exam-id="${event.id}" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button class="card-action-btn action-delete" data-exam-id="${event.id}" data-exam-name="${event.exam_type} - ${event.subject_name} Assessment" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="exam-card-details">
                                    <div class="exam-detail">
                                        <span class="exam-detail-label">Subject:</span>
                                        <span class="exam-detail-value">${event.subject_name}</span>
                                    </div>
                                    <div class="exam-detail">
                                        <span class="exam-detail-label">Class:</span>
                                        <span class="exam-detail-value">${event.class_name}</span>
                                    </div>
                                    <div class="exam-detail">
                                        <span class="exam-detail-label">Time:</span>
                                        <span class="exam-detail-value">${event.start_time} - ${event.end_time}</span>
                                    </div>
                                    <div class="exam-detail">
                                        <span class="exam-detail-label">Duration:</span>
                                        <span class="exam-detail-value">${event.duration} minutes</span>
                                    </div>
                                </div>
                            </div>`;
                    });
                    
                    html += '</div></div>';
                });
            }
            
            html += '</div></div>';
            listContainer.innerHTML = html;
        }
        
        function updateTableView(events) {
            const tableContainer = document.getElementById('table-tab');
            if (!tableContainer) return;
            
            let html = `
            <div class="table-view">
                <table class="exams-table">
                    <thead>                        <tr>
                            <th>Assessment</th>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>`;
              if (events.length === 0) {
                html += '<tr><td colspan="7" style="text-align: center; padding: 2rem;">No exams scheduled</td></tr>';
            } else {
                events.forEach(event => {
                    const formattedDate = new Date(event.exam_date).toLocaleDateString();
                    html += `                    <tr>
                        <td class="exam-name">${event.exam_type} - ${event.subject_name} Assessment</td>
                        <td class="exam-subject">${event.subject_name}</td>
                        <td>${event.class_name}</td>
                        <td>${formattedDate}</td>
                        <td>${event.start_time} - ${event.end_time}</td>
                        <td>${event.duration} min</td>
                        <td class="actions">
                            <button class="action-btn" data-exam-id="${event.id}" title="View Details">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <button class="action-btn" data-exam-id="${event.id}" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button class="action-btn" data-exam-id="${event.id}" data-exam-name="${event.exam_type} - ${event.subject_name} Assessment" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>`;
                });
            }
            
            html += `
                    </tbody>
                </table>
            </div>
            
            <div class="pagination">
                <div class="pagination-info">
                    Showing ${events.length > 0 ? '1' : '0'}-${events.length} of ${events.length} exams
                </div>
                <div class="pagination-buttons">
                    <button class="page-btn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>`;
            
            tableContainer.innerHTML = html;
        }
        
        // Workflow guidance functions
        function showWorkflowGuidance() {
            const guidanceDiv = document.createElement('div');
            guidanceDiv.id = 'workflow-guidance';
            guidanceDiv.style.cssText = `
                background: #e3f2fd;
                border: 1px solid #2196f3;
                border-radius: 8px;
                padding: 15px;
                margin-bottom: 20px;
                color: #1976d2;
            `;
            guidanceDiv.innerHTML = `
                <h4>📋 Recommended Workflow</h4>
                <p><strong>Option 1 (Recommended):</strong> Select an existing exam session above to add subjects to it</p>
                <p><strong>Option 2:</strong> Use the "Create Session" button above to create a new exam session, then return here</p>
                <p><strong>Option 3:</strong> Use the "Manage Subjects" button to manage exam subjects for existing sessions</p>
                <p><strong>Option 4:</strong> Leave session blank to get guidance on available sessions</p>
                <div style="margin-top: 10px; padding: 8px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
                    <small><strong>💡 Quick Access:</strong> The "Create Session" and "Manage Subjects" buttons in the top toolbar provide direct access to exam management features.</small>
                </div>
            `;
            
            const form = document.getElementById('createExamForm');
            form.insertBefore(guidanceDiv, form.firstChild);
        }
        
        function showSessionSelectionDialog(data) {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            
            const content = document.createElement('div');
            content.style.cssText = `
                background: white;
                padding: 30px;
                border-radius: 10px;
                max-width: 600px;
                max-height: 80vh;
                overflow-y: auto;
            `;
            
            let sessionsHtml = '';
            if (data.available_sessions && data.available_sessions.length > 0) {
                sessionsHtml = data.available_sessions.map(session => `
                    <div style="padding: 10px; border: 1px solid #ddd; margin: 5px 0; border-radius: 5px;">
                        <strong>${session.session_name}</strong> (${session.session_type})<br>
                        <small>Class: ${session.class_name || 'Multiple'} | ${session.start_date} to ${session.end_date}</small>
                    </div>
                `).join('');
            } else {
                sessionsHtml = '<p>No active exam sessions found.</p>';
            }
            
            content.innerHTML = `
                <h3>🔄 ${data.workflow_guidance}</h3>
                <p><strong>Message:</strong> ${data.message}</p>
                
                <h4>Available Exam Sessions:</h4>
                ${sessionsHtml}
                
                <div style="margin-top: 20px;">
                    <p><strong>Next Steps:</strong></p>
                    <ol>
                        <li>Close this dialog and select an existing session from the dropdown</li>
                        <li>Or <a href="${data.redirect_url}" target="_blank">create a new exam session first</a></li>
                        <li>Then return here to add subjects to the session</li>
                    </ol>
                </div>
                
                <div style="text-align: center; margin-top: 20px;">
                    <button onclick="this.closest('[style*=fixed]').remove()" 
                            style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Got It - Close
                    </button>
                </div>
            `;
            
            modal.appendChild(content);
            document.body.appendChild(modal);
        }
        
        function showSessionSelectionForSubjects(sessions) {
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            
            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: white;
                padding: 2rem;
                border-radius: 8px;
                max-width: 500px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
            `;
            
            modalContent.innerHTML = `
                <h3 style="margin-top: 0; color: #333;">Select Exam Session</h3>
                <p style="color: #666; margin-bottom: 1.5rem;">Choose an exam session to manage its subjects:</p>
                <div style="max-height: 300px; overflow-y: auto; margin-bottom: 1.5rem;">
                    ${sessions.map(session => `
                        <div style="border: 1px solid #ddd; border-radius: 4px; padding: 1rem; margin-bottom: 0.5rem; cursor: pointer; transition: background 0.2s;" 
                             onclick="navigateToSubjects(${session.id})" 
                             onmouseover="this.style.background='#f5f5f5'" 
                             onmouseout="this.style.background='white'">
                            <div style="font-weight: 600; color: #333;">${session.session_name}</div>
                            <div style="font-size: 0.9rem; color: #666;">
                                ${session.session_type} • Class ${session.class_name} 
                                ${session.section_name ? '(' + session.section_name + ')' : ''}
                            </div>
                            <div style="font-size: 0.8rem; color: #888;">
                                ${new Date(session.start_date).toLocaleDateString()} - ${new Date(session.end_date).toLocaleDateString()}
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button onclick="this.closest('.modal').remove()" 
                            style="padding: 0.5rem 1rem; border: 1px solid #ddd; background: white; border-radius: 4px; cursor: pointer;">
                        Cancel
                    </button>
                </div>
            `;
            
            modal.className = 'modal';
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
            
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }
        
        function navigateToSubjects(sessionId) {
            // Navigate to manage subjects page with session ID
            window.location.href = `manage_exam_subjects.php?session_id=${sessionId}`;
        }
        
        // Responsive event text function
        function getResponsiveEventText(event) {
            const screenWidth = window.innerWidth;
            const isMobile = screenWidth <= 768;
            const isSmallMobile = screenWidth <= 480;
            
            if (isSmallMobile) {
                // Very small screens: Show only abbreviations
                return `${event.subject_abbr || event.subject_name.substring(0, 3).toUpperCase()}`;
            } else if (isMobile) {
                // Mobile screens: Show abbreviated subject name
                return `${event.subject_name.length > 8 ? event.subject_name.substring(0, 8) + '...' : event.subject_name}`;
            } else {
                // Desktop screens: Show full subject name
                return `${event.subject_name} - ${event.class_name}`;
            }
        }
        
        // Window resize handler for responsive adjustments
        window.addEventListener('resize', function() {
            // Debounce resize events
            clearTimeout(window.resizeTimer);
            window.resizeTimer = setTimeout(function() {
                // Re-render calendar events with appropriate text size
                const events = document.querySelectorAll('.calendar-event');
                events.forEach(eventElement => {
                    const eventData = JSON.parse(eventElement.dataset.event || '{}');
                    if (eventData.subject_name) {
                        eventElement.textContent = getResponsiveEventText(eventData);
                    }
                });
                
                // Adjust legend visibility based on screen size
                const legend = document.getElementById('calendarLegend');
                const screenWidth = window.innerWidth;
                
                if (screenWidth <= 480 && legend.style.display !== 'none') {
                    // Hide legend on very small screens
                    legend.style.display = 'none';
                } else if (screenWidth > 480 && legend.style.display === 'none') {
                    // Show legend on larger screens
                    legend.style.display = 'block';
                }
                
                // Hide tooltips on resize to prevent positioning issues
                hideEventTooltip();
            }, 250);
        });
        
        // Touch device improvements
        if ('ontouchstart' in window) {
            // Add touch-friendly event handling
            document.addEventListener('touchstart', function(e) {
                // Close tooltips when touching elsewhere
                if (!e.target.closest('.calendar-event') && !e.target.closest('.event-tooltip')) {
                    hideEventTooltip();
                }
            });
            
            // Prevent zoom on double tap for buttons
            document.querySelectorAll('button, .btn').forEach(btn => {
                btn.addEventListener('touchend', function(e) {
                    e.preventDefault();
                    this.click();
                });
            });
        }
        
        // Initialize responsive features on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial responsive state
            window.dispatchEvent(new Event('resize'));
            
            // Add responsive classes to calendar container
            const calendarContainer = document.querySelector('.calendar-view');
            if (calendarContainer) {
                calendarContainer.classList.add('responsive-calendar');
            }
        });
    </script>
</body>
</html>