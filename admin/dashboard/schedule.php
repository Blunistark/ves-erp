<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <div class="action-bar">
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
                        Filter
                    </button>
                    <button class="btn btn-outline">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Export
                    </button>
                    <button class="btn btn-primary" id="newExamBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Schedule Exam
                    </button>
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
                            <option value="7">Grade 7</option>
                            <option value="8">Grade 8</option>
                            <option value="9">Grade 9</option>
                            <option value="10">Grade 10</option>
                            <option value="11">Grade 11</option>
                            <option value="12">Grade 12</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Subject</label>
                        <select class="filter-select" id="subjectFilter">
                            <option value="">All Subjects</option>
                            <option value="math">Mathematics</option>
                            <option value="science">Science</option>
                            <option value="english">English</option>
                            <option value="history">History</option>
                            <option value="geography">Geography</option>
                            <option value="computer">Computer Science</option>
                            <option value="physics">Physics</option>
                            <option value="chemistry">Chemistry</option>
                            <option value="biology">Biology</option>
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
                            <label class="form-label" for="examType">Assessment Type</label>
                            <select class="form-select" id="examType" name="examType">
                                <option value="">Select Assessment Type</option>
                                <option value="FA">FA (Formative Assessment)</option>
                                <option value="SA">SA (Summative Assessment)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="examSubject">Subject</label>
                            <select class="form-select" id="examSubject" name="examSubject">
                                <option value="">Select Subject</option>
                                <option value="math">Mathematics</option>
                                <option value="science">Science</option>
                                <option value="english">English</option>
                                <option value="history">History</option>
                                <option value="geography">Geography</option>
                                <option value="computer">Computer Science</option>
                                <option value="physics">Physics</option>
                                <option value="chemistry">Chemistry</option>
                                <option value="biology">Biology</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="examClass">Class/Grade</label>
                            <select class="form-select" id="examClass" name="examClass">
                                <option value="">Select Class</option>
                                <option value="7a">Grade 7A</option>
                                <option value="7b">Grade 7B</option>
                                <option value="8a">Grade 8A</option>
                                <option value="8b">Grade 8B</option>
                                <option value="9a">Grade 9A</option>
                                <option value="9b">Grade 9B</option>
                                <option value="10a">Grade 10A</option>
                                <option value="10b">Grade 10B</option>
                                <option value="11a">Grade 11A</option>
                                <option value="11b">Grade 11B</option>
                                <option value="12a">Grade 12A</option>
                                <option value="12b">Grade 12B</option>
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
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="examProctor">Proctor</label>
                            <select class="form-select" id="examProctor" name="examProctor">
                                <option value="">Select Proctor</option>
                                <option value="1">John Davis</option>
                                <option value="2">Sarah Parker</option>
                                <option value="3">Robert Johnson</option>
                                <option value="4">Emily Smith</option>
                                <option value="5">David Wilson</option>
                                <option value="6">Maria Rodriguez</option>
                                <option value="7">James Carter</option>
                            </select>
                        </div>
                    </div>
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
                        <h3 class="calendar-title">March 2025</h3>
                        <div class="calendar-navigation">
                            <button class="calendar-nav-btn">
                                <svg class="calendar-nav-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <select class="calendar-month-select">
                                <option value="3">March 2025</option>
                                <option value="4">April 2025</option>
                                <option value="5">May 2025</option>
                                <option value="6">June 2025</option>
                            </select>
                            <button class="calendar-nav-btn">
                                <svg class="calendar-nav-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
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
                            <div class="calendar-day-events">
                                <div class="calendar-event event-midterm" title="Mathematics Midterm - Grade 10A">
                                    Math Midterm (10A)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">11</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-midterm" title="English Midterm - Grade 9A">
                                    English Midterm (9A)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">12</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-midterm" title="Science Midterm - Grade 10A">
                                    Science Midterm (10A)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">13</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-midterm" title="History Midterm - Grade 11A">
                                    History Midterm (11A)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">14</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-practical" title="Computer Science Practical - Grade 11B">
                                    CS Practical (11B)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day calendar-day-current">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">15</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-midterm" title="Geography Midterm - Grade 9B">
                                    Geography Midterm (9B)
                                </div>
                            </div>
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
                            <div class="calendar-day-events">
                                <div class="calendar-event event-quiz" title="Math Quiz - Grade 8A">
                                    Math Quiz (8A)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">18</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-quiz" title="English Quiz - Grade 7A">
                                    English Quiz (7A)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">19</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-final" title="Physics Final - Grade 12A">
                                    Physics Final (12A)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">20</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-final" title="Chemistry Final - Grade 12A">
                                    Chemistry Final (12A)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">21</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-final" title="Biology Final - Grade 12B">
                                    Biology Final (12B)
                                </div>
                            </div>
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
                            <div class="calendar-day-events">
                                <div class="calendar-event event-final" title="Math Final - Grade 12A">
                                    Math Final (12A)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">25</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-final" title="English Final - Grade 12B">
                                    English Final (12B)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">26</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-practical" title="Biology Practical - Grade 11A">
                                    Biology Practical (11A)
                                </div>
                            </div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-header">
                                <div class="calendar-day-number">27</div>
                            </div>
                            <div class="calendar-day-events">
                                <div class="calendar-event event-practical" title="Chemistry Practical - Grade 11B">
                                    Chemistry Practical (11B)
                                </div>
                            </div>
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
                        <div>
                            <h4 class="timeline-date">Today (March 15, 2025)</h4>
                            <div class="timeline-exams">                                <div class="timeline-exam-card">
                                    <div class="exam-card-header">
                                        <h5 class="exam-card-title">FA - Geography Assessment</h5>
                                        <span class="exam-card-badge badge-fa">FA</span>
                                    </div>
                                    <div class="exam-card-details">
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span>Grade 9B</span>
                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>10:00 AM - 11:30 AM (90 min)</span>                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span>Proctor: Sarah Parker</span>
                                        </div>
                                    </div>
                                    <div class="exam-card-actions">
                                        <button class="card-action-btn action-edit">Edit</button>
                                        <button class="card-action-btn action-delete">Cancel Exam</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="timeline-date">March 17, 2025</h4>
                            <div class="timeline-exams">                                <div class="timeline-exam-card">
                                    <div class="exam-card-header">
                                        <h5 class="exam-card-title">FA - Math Assessment</h5>
                                        <span class="exam-card-badge badge-fa">FA</span>
                                    </div>
                                    <div class="exam-card-details">
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                            </svg>
                                            <span>Grade 8A</span>
                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>9:00 AM - 9:45 AM (45 min)</span>
                                        </div>                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span>Proctor: John Davis</span>
                                        </div>
                                    </div>
                                    <div class="exam-card-actions">
                                        <button class="card-action-btn action-edit">Edit</button>
                                        <button class="card-action-btn action-delete">Cancel Exam</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="timeline-date">March 18, 2025</h4>
                            <div class="timeline-exams">                                <div class="timeline-exam-card">
                                    <div class="exam-card-header">
                                        <h5 class="exam-card-title">FA - English Assessment</h5>
                                        <span class="exam-card-badge badge-fa">FA</span>
                                    </div>
                                    <div class="exam-card-details">
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                            </svg>
                                            <span>Grade 7A</span>
                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>10:00 AM - 10:45 AM (45 min)</span>
                                        </div>
                                        <div class="exam-card-detail">                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span>Proctor: Robert Johnson</span>
                                        </div>
                                    </div>
                                    <div class="exam-card-actions">
                                        <button class="card-action-btn action-edit">Edit</button>
                                        <button class="card-action-btn action-delete">Cancel Exam</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="timeline-date">March 19, 2025</h4>
                            <div class="timeline-exams">                                <div class="timeline-exam-card">
                                    <div class="exam-card-header">
                                        <h5 class="exam-card-title">SA - Physics Assessment</h5>
                                        <span class="exam-card-badge badge-sa">SA</span>
                                    </div>
                                    <div class="exam-card-details">
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998a12.078 12.078 0 01.665-6.479L12 14z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                            </svg>
                                            <span>Grade 12A</span>
                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>9:00 AM - 11:00 AM (120 min)</span>
                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span>Examination Hall 1</span>
                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                            <span>Proctor: Sarah Parker</span>
                                        </div>
                                    </div>
                                    <div class="exam-card-actions">
                                        <button class="card-action-btn action-edit">Edit</button>
                                        <button class="card-action-btn action-delete">Cancel Exam</button>
                                    </div>
                                </div>
                            </div>
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
                        <tbody>                            <tr>
                                <td>
                                    <div class="exam-info">
                                        <span class="exam-name">FA - Geography Assessment</span>
                                        <span class="exam-subject">Geography</span>
                                    </div>
                                </td>
                                <td>Mar 15, 2025<br>10:00 AM - 11:30 AM</td>
                                <td>Grade 9B</td>                                <td>Sarah Parker</td>
                                <td><span class="exam-badge badge-fa">FA</span></td>
                                <td>90 min</td>
                                <td class="exam-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>                            <tr>
                                <td>
                                    <div class="exam-info">
                                        <span class="exam-name">FA - Math Assessment</span>
                                        <span class="exam-subject">Mathematics</span>
                                    </div>                                </td>
                                <td>Mar 17, 2025<br>9:00 AM - 9:45 AM</td>
                                <td>Grade 8A</td>
                                <td>John Davis</td>
                                <td><span class="exam-badge badge-fa">FA</span></td>
                                <td>45 min</td>
                                <td class="exam-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>                            <tr>
                                <td>
                                    <div class="exam-info">
                                        <span class="exam-name">FA - English Assessment</span>
                                        <span class="exam-subject">English</span>
                                    </div>                                </td>
                                <td>Mar 18, 2025<br>10:00 AM - 10:45 AM</td>
                                <td>Grade 7A</td>
                                <td>Robert Johnson</td>
                                <td><span class="exam-badge badge-fa">FA</span></td>
                                <td>45 min</td>
                                <td class="exam-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>                            <tr>
                                <td>
                                    <div class="exam-info">
                                        <span class="exam-name">SA - Physics Assessment</span>
                                        <span class="exam-subject">Physics</span>
                                    </div>
                                </td>
                                <td>Mar 19, 2025<br>9:00 AM - 11:00 AM</td>
                                <td>Grade 12A</td>
                                <td>Examination Hall 1</td>
                                <td>Sarah Parker</td>
                                <td><span class="exam-badge badge-sa">SA</span></td>
                                <td>120 min</td>
                                <td class="exam-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="exam-info">
                                        <span class="exam-name">Chemistry Final Exam</span>
                                        <span class="exam-subject">Chemistry</span>
                                    </div>
                                </td>
                                <td>Mar 20, 2025<br>9:00 AM - 11:00 AM</td>
                                <td>Grade 12A</td>
                                <td>Examination Hall 1</td>
                                <td>David Wilson</td>
                                <td><span class="exam-badge badge-final">Final</span></td>
                                <td>120 min</td>
                                <td class="exam-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="pagination">
                    <div class="pagination-info">
                        Showing 1-5 of 15 exams
                    </div>
                    <div class="pagination-buttons">
                        <button class="page-btn" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <button class="page-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
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
                
                // Scroll to form
                examForm.scrollIntoView({ behavior: 'smooth' });
            });
            
            cancelExamBtn.addEventListener('click', function() {
                examForm.style.display = 'none';
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
                }                  // Validate form fields
                const requiredFields = ['examType', 'examSubject', 'examClass', 'examDate', 
                    'examStartTime', 'examEndTime', 'examProctor', 'examDuration'];
                
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
                if (currentIndex < calendarMonthSelect.options.length - 1) {
                    calendarMonthSelect.selectedIndex = currentIndex + 1;
                    calendarMonthSelect.dispatchEvent(new Event('change'));
                }
            });
            
            calendarNavBtns[1].addEventListener('click', function() {
                // Next month
                const currentIndex = calendarMonthSelect.selectedIndex;
                if (currentIndex > 0) {
                    calendarMonthSelect.selectedIndex = currentIndex - 1;
                    calendarMonthSelect.dispatchEvent(new Event('change'));
                }
            });
            
            calendarMonthSelect.addEventListener('change', function() {
                const selectedMonth = this.options[this.selectedIndex].text;
                document.querySelector('.calendar-title').textContent = selectedMonth;
                loadCalendarEvents(this.value);
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
            const params = new URLSearchParams({
                action: 'get_schedule_data',
                view: view,
                month: document.querySelector('.calendar-month_select')?.value || new Date().getMonth() + 1,
                year: new Date().getFullYear()
            });
            
            fetch(`schedule_handler.php?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (view === 'calendar') {
                            updateCalendarView(data.events);
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
            // Load classes, subjects, and proctors from the database
            Promise.all([
                fetch('schedule_handler.php?action=get_classes'),
                fetch('schedule_handler.php?action=get_subjects'),
                fetch('schedule_handler.php?action=get_proctors')
            ])
            .then(responses => Promise.all(responses.map(r => r.json())))
            .then(data => {
                populateDropdowns(data);
            })
            .catch(error => console.error('Error loading dropdown data:', error));
        }
          function populateDropdowns(data) {
            const [classes, subjects, proctors] = data;
            
            if (classes.success) {
                populateSelect('examClass', classes.data, 'class_id', 'class_name');
            }
            if (subjects.success) {
                populateSelect('examSubject', subjects.data, 'subject_id', 'subject_name');
            }
            if (proctors.success) {
                populateSelect('examProctor', proctors.data, 'teacher_id', 'full_name');
            }
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
        
        function updateCalendarView(events) {
            // Clear existing events
            document.querySelectorAll('.calendar-event').forEach(event => event.remove());
            
            // Add new events to calendar
            events.forEach(event => {
                const eventDate = new Date(event.exam_date);
                const dayElement = findCalendarDay(eventDate.getDate());
                  if (dayElement) {
                    const eventElement = document.createElement('div');
                    eventElement.className = `calendar-event exam-${event.exam_type}`;
                    eventElement.textContent = `${event.exam_type} - ${event.subject_name}`;
                    eventElement.setAttribute('title', `${event.exam_type} - ${event.subject_name} Assessment (${event.class_name})`);
                    eventElement.setAttribute('data-exam-id', event.id);
                    
                    eventElement.addEventListener('click', () => showExamDetails(event.id));
                    
                    dayElement.querySelector('.calendar-day-events').appendChild(eventElement);
                }
            });
        }
        
        function findCalendarDay(dayNumber) {
            const calendarDays = document.querySelectorAll('.calendar-day:not(.calendar-day-other-month)');
            return Array.from(calendarDays).find(day => {
                const dayNum = day.querySelector('.calendar-day-number');
                return dayNum && parseInt(dayNum.textContent) === dayNumber;
            });
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
        }        function displayExamDetails(exam) {
            const details = `
                Assessment: ${exam.exam_type} - ${exam.subject_name} Assessment
                Subject: ${exam.subject_name}
                Class: ${exam.class_name}
                Date: ${exam.exam_date}
                Time: ${exam.start_time} - ${exam.end_time}
                Duration: ${exam.duration} minutes
                Max Marks: ${exam.max_marks}
                Proctor: ${exam.proctor_name}
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
        }        function populateEditForm(exam) {
            document.getElementById('examType').value = exam.exam_type;
            document.getElementById('examSubject').value = exam.subject_id;
            document.getElementById('examClass').value = exam.class_id;
            document.getElementById('examDate').value = exam.exam_date;
            document.getElementById('examStartTime').value = exam.start_time;
            document.getElementById('examEndTime').value = exam.end_time;
            document.getElementById('examProctor').value = exam.teacher_id;
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
                                </div>                                <div class="exam-detail">
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
                            </button>                        </td>
                    </tr>`;
                });
            }
            
            html += `
                    </tbody>
                </table>
            </div>`;
            
            tableContainer.innerHTML = html;
        }
    </script>
</body>
</html>