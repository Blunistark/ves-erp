
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
                <form class="filter-form">
                    <div class="filter-group">
                        <label class="filter-label">Exam Type</label>
                        <select class="filter-select" id="examTypeFilter">
                            <option value="">All Types</option>
                            <option value="midterm">Midterm Exam</option>
                            <option value="final">Final Exam</option>
                            <option value="quiz">Quiz</option>
                            <option value="practical">Practical Exam</option>
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
                <form id="createExamForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="examName">Exam Name</label>
                            <input type="text" class="form-input" id="examName" name="examName" placeholder="e.g. Midterm Mathematics Exam">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="examType">Exam Type</label>
                            <select class="form-select" id="examType" name="examType">
                                <option value="">Select Exam Type</option>
                                <option value="midterm">Midterm Exam</option>
                                <option value="final">Final Exam</option>
                                <option value="quiz">Quiz</option>
                                <option value="practical">Practical Exam</option>
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
                            <label class="form-label" for="examRoom">Exam Room</label>
                            <select class="form-select" id="examRoom" name="examRoom">
                                <option value="">Select Room</option>
                                <option value="101">Room 101</option>
                                <option value="102">Room 102</option>
                                <option value="103">Room 103</option>
                                <option value="104">Room 104</option>
                                <option value="105">Room 105</option>
                                <option value="201">Room 201</option>
                                <option value="202">Room 202</option>
                                <option value="hall1">Examination Hall 1</option>
                                <option value="hall2">Examination Hall 2</option>
                                <option value="lab1">Science Lab 1</option>
                                <option value="lab2">Computer Lab</option>
                            </select>
                        </div>
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
                            <div class="timeline-exams">
                                <div class="timeline-exam-card">
                                    <div class="exam-card-header">
                                        <h5 class="exam-card-title">Geography Midterm</h5>
                                        <span class="exam-card-badge badge-midterm">Midterm</span>
                                    </div>
                                    <div class="exam-card-details">
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                            </svg>
                                            <span>Grade 9B</span>
                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>10:00 AM - 11:30 AM (90 min)</span>
                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span>Room 103</span>
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
                        
                        <div>
                            <h4 class="timeline-date">March 17, 2025</h4>
                            <div class="timeline-exams">
                                <div class="timeline-exam-card">
                                    <div class="exam-card-header">
                                        <h5 class="exam-card-title">Math Quiz</h5>
                                        <span class="exam-card-badge badge-quiz">Quiz</span>
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
                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span>Room 101</span>
                                        </div>
                                        <div class="exam-card-detail">
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
                            <div class="timeline-exams">
                                <div class="timeline-exam-card">
                                    <div class="exam-card-header">
                                        <h5 class="exam-card-title">English Quiz</h5>
                                        <span class="exam-card-badge badge-quiz">Quiz</span>
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
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                            <span>Room 102</span>
                                        </div>
                                        <div class="exam-card-detail">
                                            <svg class="detail-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            <div class="timeline-exams">
                                <div class="timeline-exam-card">
                                    <div class="exam-card-header">
                                        <h5 class="exam-card-title">Physics Final Exam</h5>
                                        <span class="exam-card-badge badge-final">Final</span>
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
                    <table class="exams-table">
                        <thead>
                            <tr>
                                <th>Exam</th>
                                <th>Date & Time</th>
                                <th>Class</th>
                                <th>Room</th>
                                <th>Proctor</th>
                                <th>Type</th>
                                <th>Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="exam-info">
                                        <span class="exam-name">Geography Midterm</span>
                                        <span class="exam-subject">Geography</span>
                                    </div>
                                </td>
                                <td>Mar 15, 2025<br>10:00 AM - 11:30 AM</td>
                                <td>Grade 9B</td>
                                <td>Room 103</td>
                                <td>Sarah Parker</td>
                                <td><span class="exam-badge badge-midterm">Midterm</span></td>
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
                            </tr>
                            <tr>
                                <td>
                                    <div class="exam-info">
                                        <span class="exam-name">Math Quiz</span>
                                        <span class="exam-subject">Mathematics</span>
                                    </div>
                                </td>
                                <td>Mar 17, 2025<br>9:00 AM - 9:45 AM</td>
                                <td>Grade 8A</td>
                                <td>Room 101</td>
                                <td>John Davis</td>
                                <td><span class="exam-badge badge-quiz">Quiz</span></td>
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
                            </tr>
                            <tr>
                                <td>
                                    <div class="exam-info">
                                        <span class="exam-name">English Quiz</span>
                                        <span class="exam-subject">English</span>
                                    </div>
                                </td>
                                <td>Mar 18, 2025<br>10:00 AM - 10:45 AM</td>
                                <td>Grade 7A</td>
                                <td>Room 102</td>
                                <td>Robert Johnson</td>
                                <td><span class="exam-badge badge-quiz">Quiz</span></td>
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
                            </tr>
                            <tr>
                                <td>
                                    <div class="exam-info">
                                        <span class="exam-name">Physics Final Exam</span>
                                        <span class="exam-subject">Physics</span>
                                    </div>
                                </td>
                                <td>Mar 19, 2025<br>9:00 AM - 11:00 AM</td>
                                <td>Grade 12A</td>
                                <td>Examination Hall 1</td>
                                <td>Sarah Parker</td>
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
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
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
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
            document.body.classList.toggle('sidebar-open');

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
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
                
                // Get form values
                const examName = document.getElementById('examName').value;
                const examType = document.getElementById('examType').value;
                const examSubject = document.getElementById('examSubject').value;
                const examClass = document.getElementById('examClass').value;
                const examDate = document.getElementById('examDate').value;
                const examStartTime = document.getElementById('examStartTime').value;
                const examEndTime = document.getElementById('examEndTime').value;
                const examRoom = document.getElementById('examRoom').value;
                const examProctor = document.getElementById('examProctor').value;
                const examDuration = document.getElementById('examDuration').value;
                
                // Validate form fields
                if (!examName || !examType || !examSubject || !examClass || !examDate || 
                    !examStartTime || !examEndTime || !examRoom || !examProctor || !examDuration) {
                    alert('Please fill in all required fields');
                    return;
                }
                
                // Check if start time is before end time
                if (examStartTime >= examEndTime) {
                    alert('Start time must be before end time');
                    return;
                }
                
                // Here you would typically submit the form via AJAX or redirect
                alert('Exam scheduled successfully!');
                
                // Hide form
                examForm.style.display = 'none';
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
                // Here you would update the calendar with new month data
            });
            
            // Calendar Events
            const calendarEvents = document.querySelectorAll('.calendar-event');
            
            calendarEvents.forEach(event => {
                event.addEventListener('click', function() {
                    const eventTitle = this.getAttribute('title');
                    alert(`Event Details: ${eventTitle}`);
                    // Here you could show a modal with full event details
                });
            });
            
            // Action Buttons
            const viewButtons = document.querySelectorAll('.action-btn[title="View Details"]');
            const editButtons = document.querySelectorAll('.action-btn[title="Edit"], .card-action-btn.action-edit');
            const deleteButtons = document.querySelectorAll('.action-btn[title="Delete"], .card-action-btn.action-delete');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const examName = this.closest('tr').querySelector('.exam-name').textContent;
                    alert(`Viewing details for: ${examName}`);
                });
            });
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    let examName;
                    
                    // Handle both table and card edit buttons
                    if (this.closest('tr')) {
                        examName = this.closest('tr').querySelector('.exam-name').textContent;
                    } else {
                        examName = this.closest('.timeline-exam-card').querySelector('.exam-card-title').textContent;
                    }
                    
                    // In a real implementation, this would load the exam data into the form
                    
                    // Change form title and button text
                    document.querySelector('.form-title').textContent = `Edit Exam: ${examName}`;
                    document.querySelector('.form-actions .btn-primary').textContent = 'Update Exam';
                    
                    // Show form
                    examForm.style.display = 'block';
                    examForm.scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    let examName;
                    
                    // Handle both table and card delete buttons
                    if (this.closest('tr')) {
                        examName = this.closest('tr').querySelector('.exam-name').textContent;
                    } else {
                        examName = this.closest('.timeline-exam-card').querySelector('.exam-card-title').textContent;
                    }
                    
                    if (confirm(`Are you sure you want to cancel ${examName}?`)) {
                        // Here you would send a delete request to the server
                        
                        // For demonstration purposes, hide the row or card
                        if (this.closest('tr')) {
                            this.closest('tr').style.display = 'none';
                        } else {
                            this.closest('.timeline-exam-card').style.display = 'none';
                        }
                        
                        alert(`${examName} has been cancelled.`);
                    }
                });
            });
            
            // Search functionality
            const searchInput = document.getElementById('examSearch');
            const tableRows = document.querySelectorAll('.exams-table tbody tr');
            const examCards = document.querySelectorAll('.timeline-exam-card');
            const calendarDays = document.querySelectorAll('.calendar-day');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                // Search in table
                if (tableRows.length > 0) {
                    tableRows.forEach(row => {
                        const examName = row.querySelector('.exam-name').textContent.toLowerCase();
                        const examSubject = row.querySelector('.exam-subject').textContent.toLowerCase();
                        const examClass = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                        
                        const matchFound = 
                            examName.includes(searchTerm) || 
                            examSubject.includes(searchTerm) || 
                            examClass.includes(searchTerm);
                        
                        row.style.display = matchFound ? '' : 'none';
                    });
                }
                
                // Search in cards
                if (examCards.length > 0) {
                    examCards.forEach(card => {
                        const examTitle = card.querySelector('.exam-card-title').textContent.toLowerCase();
                        const examDetails = card.querySelector('.exam-card-details').textContent.toLowerCase();
                        
                        const matchFound = 
                            examTitle.includes(searchTerm) || 
                            examDetails.includes(searchTerm);
                        
                        card.style.display = matchFound ? '' : 'none';
                    });
                }
                
                // Search in calendar (only show days with matching events)
                if (calendarDays.length > 0 && searchTerm) {
                    calendarDays.forEach(day => {
                        const events = day.querySelectorAll('.calendar-event');
                        let dayHasMatch = false;
                        
                        events.forEach(event => {
                            const eventTitle = event.getAttribute('title').toLowerCase();
                            const eventText = event.textContent.toLowerCase();
                            
                            if (eventTitle.includes(searchTerm) || eventText.includes(searchTerm)) {
                                dayHasMatch = true;
                                event.style.background = '#ffff00';
                                event.style.color = '#000000';
                            } else {
                                event.style.background = '';
                                event.style.color = '';
                            }
                        });
                    });
                } else if (calendarDays.length > 0) {
                    // Reset calendar events to original colors
                    document.querySelectorAll('.calendar-event').forEach(event => {
                        event.style.background = '';
                        event.style.color = '';
                    });
                }
            });
            
            // Filter functionality
            const filterForm = document.querySelector('.filter-form');
            const filterApplyBtn = document.querySelector('.filter-btn-apply');
            const filterResetBtn = document.querySelector('.filter-btn-reset');
            
            filterApplyBtn.addEventListener('click', function() {
                // Get filter values
                const examTypeFilter = document.getElementById('examTypeFilter').value;
                const classFilter = document.getElementById('classFilter').value;
                const subjectFilter = document.getElementById('subjectFilter').value;
                const startDateFilter = document.getElementById('startDateFilter').value;
                const endDateFilter = document.getElementById('endDateFilter').value;
                
                // In a real implementation, this would update the view based on the filters
                
                // For this demo, just show an alert with the selected filters
                let filterMessage = 'Applied filters:\n';
                filterMessage += examTypeFilter ? `Exam Type: ${examTypeFilter}\n` : 'Exam Type: All\n';
                filterMessage += classFilter ? `Class: Grade ${classFilter}\n` : 'Class: All\n';
                filterMessage += subjectFilter ? `Subject: ${subjectFilter}\n` : 'Subject: All\n';
                filterMessage += startDateFilter ? `Start Date: ${startDateFilter}\n` : 'Start Date: -\n';
                filterMessage += endDateFilter ? `End Date: ${endDateFilter}\n` : 'End Date: -\n';
                
                alert(filterMessage);
                
                // Hide the filter panel
                filterPanel.style.display = 'none';
            });
            
            filterResetBtn.addEventListener('click', function() {
                filterForm.reset();
            });
            
            // Pagination
            const pageButtons = document.querySelectorAll('.page-btn:not(:first-child):not(:last-child)');
            
            pageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    pageButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // In a real implementation, this would load the next page of data
                    if (this.textContent !== '1') {
                        alert(`Loading page ${this.textContent} of exams...`);
                    }
                });
            });
        });
    </script>
</body>
</html>