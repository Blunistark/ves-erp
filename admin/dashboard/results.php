<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/results.css">
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
            <h1 class="header-title">Exam Results</h1>
            <span class="header-path">Dashboard > Exams > Results</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="resultSearch" class="search-input" placeholder="Search by student name, ID, class, or subject...">
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
                    <button class="btn btn-primary" id="addResultBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add Result
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel" style="display: none;">
                <h3 class="filter-title">Filter Results</h3>
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
                        <label class="filter-label">Academic Year</label>
                        <select class="filter-select" id="yearFilter">
                            <option value="2024-25">2024-2025</option>
                            <option value="2023-24">2023-2024</option>
                            <option value="2022-23">2022-2023</option>
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
                    <div class="filter-group">
                        <label class="filter-label">Grade</label>
                        <select class="filter-select" id="gradeFilter">
                            <option value="">All Grades</option>
                            <option value="a">A (90-100%)</option>
                            <option value="b">B (80-89%)</option>
                            <option value="c">C (70-79%)</option>
                            <option value="d">D (60-69%)</option>
                            <option value="f">F (Below 60%)</option>
                        </select>
                    </div>
                </form>
                <div class="filter-actions">
                    <button class="filter-btn filter-btn-reset">Reset</button>
                    <button class="filter-btn filter-btn-apply">Apply Filters</button>
                </div>
            </div>

            <!-- Tab System -->
            <div class="results-tabs">
                <div class="results-tab active" data-tab="overview">Results Overview</div>
                <div class="results-tab" data-tab="manage">Manage Results</div>
                <div class="results-tab" data-tab="analysis">Result Analysis</div>
                <div class="results-tab" data-tab="publish">Publish Results</div>
            </div>

            <!-- Results Entry Form -->
            <div class="result-form-container" id="resultForm" style="display: none;">
                <h2 class="form-title">Add New Result</h2>
                <form id="createResultForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="examSelect">Select Exam</label>
                            <select class="form-select" id="examSelect" name="examSelect">
                                <option value="">Select Exam</option>
                                <option value="1">Mathematics Midterm (Grade 10A)</option>
                                <option value="2">English Midterm (Grade 9A)</option>
                                <option value="3">Science Midterm (Grade 10A)</option>
                                <option value="4">Physics Final (Grade 12A)</option>
                                <option value="5">Chemistry Final (Grade 12A)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="studentSelect">Select Student</label>
                            <select class="form-select" id="studentSelect" name="studentSelect">
                                <option value="">Select Student</option>
                                <option value="1">Alex Brown (ST001)</option>
                                <option value="2">Emma Smith (ST002)</option>
                                <option value="3">Michael Johnson (ST003)</option>
                                <option value="4">Sophia Davis (ST004)</option>
                                <option value="5">William Miller (ST005)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="marksObtained">Marks Obtained</label>
                            <input type="number" class="form-input" id="marksObtained" name="marksObtained" min="0" max="100" placeholder="e.g. 85">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="totalMarks">Total Marks</label>
                            <input type="number" class="form-input" id="totalMarks" name="totalMarks" min="1" value="100">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="gradeSelect">Grade</label>
                            <select class="form-select" id="gradeSelect" name="gradeSelect">
                                <option value="a">A (90-100%)</option>
                                <option value="b">B (80-89%)</option>
                                <option value="c">C (70-79%)</option>
                                <option value="d">D (60-69%)</option>
                                <option value="f">F (Below 60%)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label" for="remarks">Remarks</label>
                            <textarea class="form-textarea" id="remarks" name="remarks" placeholder="Enter any remarks or comments about the student's performance..."></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelResultBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Result</button>
                    </div>
                </form>
            </div>

            <!-- Overview Tab Content -->
            <div class="tab-content active" id="overview-tab">
                <div class="performance-metrics">
                    <div class="metric-card metric-a">
                        <h3 class="metric-title">Average Score</h3>
                        <div class="metric-value">78.5%</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">3.2% from last term</span>
                        </div>
                    </div>
                    <div class="metric-card metric-b">
                        <h3 class="metric-title">Pass Rate</h3>
                        <div class="metric-value">92.3%</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">1.8% from last term</span>
                        </div>
                    </div>
                    <div class="metric-card metric-c">
                        <h3 class="metric-title">Grade A Students</h3>
                        <div class="metric-value">32.7%</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">4.5% from last term</span>
                        </div>
                    </div>
                    <div class="metric-card metric-d">
                        <h3 class="metric-title">Improvement Needed</h3>
                        <div class="metric-value">7.7%</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-negative">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <span class="indicator-negative">1.8% from last term</span>
                        </div>
                    </div>
                </div>

                <div class="student-cards">
                    <div class="student-card">
                        <div class="student-card-header">
                            <div class="student-card-avatar">AB</div>
                            <div class="student-card-info">
                                <div class="student-card-name">Alex Brown</div>
                                <div class="student-card-details">ID: ST001 | Grade 10A</div>
                            </div>
                        </div>
                        <div class="student-card-performance">
                            <div class="result-item">
                                <div class="result-subject">Mathematics</div>
                                <div class="result-score">
                                    <div class="result-mark">92/100</div>
                                    <span class="grade-badge grade-a">A</span>
                                </div>
                            </div>
                            <div class="result-item">
                                <div class="result-subject">English</div>
                                <div class="result-score">
                                    <div class="result-mark">85/100</div>
                                    <span class="grade-badge grade-b">B</span>
                                </div>
                            </div>
                            <div class="result-item">
                                <div class="result-subject">Science</div>
                                <div class="result-score">
                                    <div class="result-mark">88/100</div>
                                    <span class="grade-badge grade-b">B</span>
                                </div>
                            </div>
                        </div>
                        <div class="student-card-footer">
                            <div class="student-average">Average: 88.3%</div>
                            <div class="student-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">View Details</button>
                            </div>
                        </div>
                    </div>

                    <div class="student-card">
                        <div class="student-card-header">
                            <div class="student-card-avatar">ES</div>
                            <div class="student-card-info">
                                <div class="student-card-name">Emma Smith</div>
                                <div class="student-card-details">ID: ST002 | Grade 10A</div>
                            </div>
                        </div>
                        <div class="student-card-performance">
                            <div class="result-item">
                                <div class="result-subject">Mathematics</div>
                                <div class="result-score">
                                    <div class="result-mark">78/100</div>
                                    <span class="grade-badge grade-c">C</span>
                                </div>
                            </div>
                            <div class="result-item">
                                <div class="result-subject">English</div>
                                <div class="result-score">
                                    <div class="result-mark">95/100</div>
                                    <span class="grade-badge grade-a">A</span>
                                </div>
                            </div>
                            <div class="result-item">
                                <div class="result-subject">Science</div>
                                <div class="result-score">
                                    <div class="result-mark">72/100</div>
                                    <span class="grade-badge grade-c">C</span>
                                </div>
                            </div>
                        </div>
                        <div class="student-card-footer">
                            <div class="student-average">Average: 81.7%</div>
                            <div class="student-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">View Details</button>
                            </div>
                        </div>
                    </div>

                    <div class="student-card">
                        <div class="student-card-header">
                            <div class="student-card-avatar">MJ</div>
                            <div class="student-card-info">
                                <div class="student-card-name">Michael Johnson</div>
                                <div class="student-card-details">ID: ST003 | Grade 10A</div>
                            </div>
                        </div>
                        <div class="student-card-performance">
                            <div class="result-item">
                                <div class="result-subject">Mathematics</div>
                                <div class="result-score">
                                    <div class="result-mark">65/100</div>
                                    <span class="grade-badge grade-d">D</span>
                                </div>
                            </div>
                            <div class="result-item">
                                <div class="result-subject">English</div>
                                <div class="result-score">
                                    <div class="result-mark">72/100</div>
                                    <span class="grade-badge grade-c">C</span>
                                </div>
                            </div>
                            <div class="result-item">
                                <div class="result-subject">Science</div>
                                <div class="result-score">
                                    <div class="result-mark">68/100</div>
                                    <span class="grade-badge grade-d">D</span>
                                </div>
                            </div>
                        </div>
                        <div class="student-card-footer">
                            <div class="student-average">Average: 68.3%</div>
                            <div class="student-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">View Details</button>
                            </div>
                        </div>
                    </div>

                    <div class="student-card">
                        <div class="student-card-header">
                            <div class="student-card-avatar">SD</div>
                            <div class="student-card-info">
                                <div class="student-card-name">Sophia Davis</div>
                                <div class="student-card-details">ID: ST004 | Grade 10A</div>
                            </div>
                        </div>
                        <div class="student-card-performance">
                            <div class="result-item">
                                <div class="result-subject">Mathematics</div>
                                <div class="result-score">
                                    <div class="result-mark">98/100</div>
                                    <span class="grade-badge grade-a">A</span>
                                </div>
                            </div>
                            <div class="result-item">
                                <div class="result-subject">English</div>
                                <div class="result-score">
                                    <div class="result-mark">92/100</div>
                                    <span class="grade-badge grade-a">A</span>
                                </div>
                            </div>
                            <div class="result-item">
                                <div class="result-subject">Science</div>
                                <div class="result-score">
                                    <div class="result-mark">96/100</div>
                                    <span class="grade-badge grade-a">A</span>
                                </div>
                            </div>
                        </div>
                        <div class="student-card-footer">
                            <div class="student-average">Average: 95.3%</div>
                            <div class="student-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">View Details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Manage Results Tab Content -->
            <div class="tab-content" id="manage-tab">
                <div class="results-table-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Exam</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Score</th>
                                <th>Grade</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">AB</div>
                                        <div class="student-details">
                                            <span class="student-name">Alex Brown</span>
                                            <span class="student-id">ID: ST001</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Midterm Exam</td>
                                <td>Mathematics</td>
                                <td>Grade 10A</td>
                                <td class="mark-cell">92/100</td>
                                <td><span class="grade-badge grade-a">A</span></td>
                                <td>Mar 12, 2025</td>
                                <td class="result-actions">
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
                                    <div class="student-info">
                                        <div class="student-avatar">ES</div>
                                        <div class="student-details">
                                            <span class="student-name">Emma Smith</span>
                                            <span class="student-id">ID: ST002</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Midterm Exam</td>
                                <td>Mathematics</td>
                                <td>Grade 10A</td>
                                <td class="mark-cell">78/100</td>
                                <td><span class="grade-badge grade-c">C</span></td>
                                <td>Mar 12, 2025</td>
                                <td class="result-actions">
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
                                    <div class="student-info">
                                        <div class="student-avatar">MJ</div>
                                        <div class="student-details">
                                            <span class="student-name">Michael Johnson</span>
                                            <span class="student-id">ID: ST003</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Midterm Exam</td>
                                <td>Mathematics</td>
                                <td>Grade 10A</td>
                                <td class="mark-cell">65/100</td>
                                <td><span class="grade-badge grade-d">D</span></td>
                                <td>Mar 12, 2025</td>
                                <td class="result-actions">
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
                                    <div class="student-info">
                                        <div class="student-avatar">SD</div>
                                        <div class="student-details">
                                            <span class="student-name">Sophia Davis</span>
                                            <span class="student-id">ID: ST004</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Midterm Exam</td>
                                <td>Mathematics</td>
                                <td>Grade 10A</td>
                                <td class="mark-cell">98/100</td>
                                <td><span class="grade-badge grade-a">A</span></td>
                                <td>Mar 12, 2025</td>
                                <td class="result-actions">
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
                                    <div class="student-info">
                                        <div class="student-avatar">WM</div>
                                        <div class="student-details">
                                            <span class="student-name">William Miller</span>
                                            <span class="student-id">ID: ST005</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Midterm Exam</td>
                                <td>Mathematics</td>
                                <td>Grade 10A</td>
                                <td class="mark-cell">85/100</td>
                                <td><span class="grade-badge grade-b">B</span></td>
                                <td>Mar 12, 2025</td>
                                <td class="result-actions">
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
                        Showing 1-5 of 25 results
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
                        <button class="page-btn">4</button>
                        <button class="page-btn">5</button>
                        <button class="page-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results Analysis Tab Content -->
            <div class="tab-content" id="analysis-tab">
                <div class="analysis-container">
                    <div class="analysis-header">
                        <h3 class="analysis-title">Performance Analytics</h3>
                        <div class="analysis-controls">
                            <select class="filter-select">
                                <option value="midterm">Midterm Exams</option>
                                <option value="final">Final Exams</option>
                                <option value="all">All Exams</option>
                            </select>
                            <select class="filter-select">
                                <option value="2024-25">Academic Year 2024-25</option>
                                <option value="2023-24">Academic Year 2023-24</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="analysis-grid">
                        <div class="analysis-chart">
                            <h4 class="chart-title">Grade Distribution</h4>
                            <div class="chart-container">
                                <div class="chart-placeholder">Grade Distribution Chart</div>
                            </div>
                        </div>
                        <div class="analysis-chart">
                            <h4 class="chart-title">Subject Performance</h4>
                            <div class="chart-container">
                                <div class="chart-placeholder">Subject Performance Chart</div>
                            </div>
                        </div>
                        <div class="analysis-chart">
                            <h4 class="chart-title">Class Comparison</h4>
                            <div class="chart-container">
                                <div class="chart-placeholder">Class Comparison Chart</div>
                            </div>
                        </div>
                        <div class="analysis-chart">
                            <h4 class="chart-title">Term Comparison</h4>
                            <div class="chart-container">
                                <div class="chart-placeholder">Term Comparison Chart</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Publication Tab Content -->
            <div class="tab-content" id="publish-tab">
                <div class="publication-container">
                    <div class="publication-header">
                        <h3 class="publication-title">Results Publication Status</h3>
                        <div class="publication-options">
                            <select class="filter-select">
                                <option value="midterm">Midterm Exams</option>
                                <option value="final">Final Exams</option>
                                <option value="all">All Exams</option>
                            </select>
                            <button class="btn btn-outline">
                                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Email to Parents
                            </button>
                            <button class="btn btn-primary">
                                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Publish Results
                            </button>
                        </div>
                    </div>
                    
                    <div class="publication-status">
                        <div class="status-info">
                            <h4 class="status-title">Midterm Exam Results - March 2025</h4>
                            <p class="status-description">Results are ready to be published for all classes. Once published, students and parents will be able to view the results.</p>
                        </div>
                        <span class="status-badge status-draft">Draft</span>
                    </div>
                    
                    <div class="publication-classes">
                        <div class="class-card">
                            <div class="class-info">
                                <div>
                                    <div class="class-name">Grade 10A</div>
                                    <div class="class-details">35 Students</div>
                                </div>
                                <span class="class-status status-draft">Draft</span>
                            </div>
                            <div class="class-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Preview</button>
                                <button class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Publish</button>
                            </div>
                        </div>
                        <div class="class-card">
                            <div class="class-info">
                                <div>
                                    <div class="class-name">Grade 10B</div>
                                    <div class="class-details">32 Students</div>
                                </div>
                                <span class="class-status status-draft">Draft</span>
                            </div>
                            <div class="class-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Preview</button>
                                <button class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Publish</button>
                            </div>
                        </div>
                        <div class="class-card">
                            <div class="class-info">
                                <div>
                                    <div class="class-name">Grade 9A</div>
                                    <div class="class-details">38 Students</div>
                                </div>
                                <span class="class-status status-draft">Draft</span>
                            </div>
                            <div class="class-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Preview</button>
                                <button class="btn btn-primary" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Publish</button>
                            </div>
                        </div>
                        <div class="class-card">
                            <div class="class-info">
                                <div>
                                    <div class="class-name">Grade 9B</div>
                                    <div class="class-details">36 Students</div>
                                </div>
                                <span class="class-status status-published">Published</span>
                            </div>
                            <div class="class-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">View</button>
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">Unpublish</button>
                            </div>
                        </div>
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
            const tabs = document.querySelectorAll('.results-tab');
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
            
            // Add Result Form Toggle
            const addResultBtn = document.getElementById('addResultBtn');
            const resultForm = document.getElementById('resultForm');
            const cancelResultBtn = document.getElementById('cancelResultBtn');
            
            addResultBtn.addEventListener('click', function() {
                // Reset form and change title/button text
                document.getElementById('createResultForm').reset();
                document.querySelector('.form-title').textContent = 'Add New Result';
                document.querySelector('.form-actions .btn-primary').textContent = 'Save Result';
                
                // Show form
                resultForm.style.display = 'block';
                
                // Scroll to form
                resultForm.scrollIntoView({ behavior: 'smooth' });
            });
            
            cancelResultBtn.addEventListener('click', function() {
                resultForm.style.display = 'none';
            });
            
            // Auto Calculate Grade Based on Marks
            const marksObtainedInput = document.getElementById('marksObtained');
            const totalMarksInput = document.getElementById('totalMarks');
            const gradeSelect = document.getElementById('gradeSelect');
            
            function updateGrade() {
                const marks = parseFloat(marksObtainedInput.value) || 0;
                const totalMarks = parseFloat(totalMarksInput.value) || 100;
                const percentage = (marks / totalMarks) * 100;
                
                let grade = 'f';
                if (percentage >= 90) grade = 'a';
                else if (percentage >= 80) grade = 'b';
                else if (percentage >= 70) grade = 'c';
                else if (percentage >= 60) grade = 'd';
                
                gradeSelect.value = grade;
            }
            
            marksObtainedInput.addEventListener('input', updateGrade);
            totalMarksInput.addEventListener('input', updateGrade);
            
            // Form Submission
            const createResultForm = document.getElementById('createResultForm');
            
            createResultForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form values
                const examSelect = document.getElementById('examSelect').value;
                const studentSelect = document.getElementById('studentSelect').value;
                const marksObtained = marksObtainedInput.value;
                const totalMarks = totalMarksInput.value;
                const grade = gradeSelect.value;
                
                // Validate form fields
                if (!examSelect || !studentSelect || !marksObtained || !totalMarks || !grade) {
                    alert('Please fill in all required fields');
                    return;
                }
                
                // Check if marks obtained are valid
                if (parseFloat(marksObtained) > parseFloat(totalMarks)) {
                    alert('Marks obtained cannot exceed total marks');
                    return;
                }
                
                // Here you would typically submit the form via AJAX or redirect
                alert('Result saved successfully!');
                
                // Hide form
                resultForm.style.display = 'none';
                
                // Switch to Manage Results tab
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                document.querySelector('.results-tab[data-tab="manage"]').classList.add('active');
                document.getElementById('manage-tab').classList.add('active');
            });
            
            // Action Buttons
            const viewButtons = document.querySelectorAll('.action-btn[title="View Details"]');
            const editButtons = document.querySelectorAll('.action-btn[title="Edit"]');
            const deleteButtons = document.querySelectorAll('.action-btn[title="Delete"]');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const studentName = this.closest('tr').querySelector('.student-name').textContent;
                    alert(`Viewing details for: ${studentName}`);
                });
            });
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const studentName = this.closest('tr').querySelector('.student-name').textContent;
                    
                    // In a real implementation, this would load the result data into the form
                    
                    // Change form title and button text
                    document.querySelector('.form-title').textContent = `Edit Result: ${studentName}`;
                    document.querySelector('.form-actions .btn-primary').textContent = 'Update Result';
                    
                    // Show form
                    resultForm.style.display = 'block';
                    resultForm.scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const studentName = this.closest('tr').querySelector('.student-name').textContent;
                    
                    if (confirm(`Are you sure you want to delete the result for ${studentName}?`)) {
                        // Here you would send a delete request to the server
                        
                        // For demonstration purposes, hide the row
                        this.closest('tr').style.display = 'none';
                        
                        alert(`Result for ${studentName} has been deleted.`);
                    }
                });
            });
            
            // Search functionality
            const searchInput = document.getElementById('resultSearch');
            const tableRows = document.querySelectorAll('.results-table tbody tr');
            const studentCards = document.querySelectorAll('.student-card');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                // Search in table
                if (tableRows.length > 0) {
                    tableRows.forEach(row => {
                        const studentName = row.querySelector('.student-name').textContent.toLowerCase();
                        const studentId = row.querySelector('.student-id').textContent.toLowerCase();
                        const examType = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                        const subject = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                        const classInfo = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                        
                        const matchFound = 
                            studentName.includes(searchTerm) || 
                            studentId.includes(searchTerm) || 
                            examType.includes(searchTerm) || 
                            subject.includes(searchTerm) || 
                            classInfo.includes(searchTerm);
                        
                        row.style.display = matchFound ? '' : 'none';
                    });
                }
                
                // Search in cards
                if (studentCards.length > 0) {
                    studentCards.forEach(card => {
                        const studentName = card.querySelector('.student-card-name').textContent.toLowerCase();
                        const studentDetails = card.querySelector('.student-card-details').textContent.toLowerCase();
                        const resultItems = card.querySelector('.student-card-performance').textContent.toLowerCase();
                        
                        const matchFound = 
                            studentName.includes(searchTerm) || 
                            studentDetails.includes(searchTerm) || 
                            resultItems.includes(searchTerm);
                        
                        card.style.display = matchFound ? '' : 'none';
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
                const yearFilter = document.getElementById('yearFilter').value;
                const startDateFilter = document.getElementById('startDateFilter').value;
                const endDateFilter = document.getElementById('endDateFilter').value;
                const gradeFilter = document.getElementById('gradeFilter').value;
                
                // In a real implementation, this would update the view based on the filters
                
                // For this demo, just show an alert with the selected filters
                let filterMessage = 'Applied filters:\n';
                filterMessage += examTypeFilter ? `Exam Type: ${examTypeFilter}\n` : 'Exam Type: All\n';
                filterMessage += classFilter ? `Class: ${classFilter}\n` : 'Class: All\n';
                filterMessage += subjectFilter ? `Subject: ${subjectFilter}\n` : 'Subject: All\n';
                filterMessage += yearFilter ? `Academic Year: ${yearFilter}\n` : 'Academic Year: All\n';
                filterMessage += startDateFilter ? `Start Date: ${startDateFilter}\n` : 'Start Date: -\n';
                filterMessage += endDateFilter ? `End Date: ${endDateFilter}\n` : 'End Date: -\n';
                filterMessage += gradeFilter ? `Grade: ${gradeFilter.toUpperCase()}\n` : 'Grade: All\n';
                
                alert(filterMessage);
                
                // Hide the filter panel
                filterPanel.style.display = 'none';
            });
            
            filterResetBtn.addEventListener('click', function() {
                filterForm.reset();
            });
            
            // Publication Actions
            const publishButtons = document.querySelectorAll('.btn.btn-primary');
            const previewButtons = document.querySelectorAll('.btn.btn-outline');
            
            publishButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const buttonText = this.textContent.trim();
                    
                    if (buttonText === 'Publish Results') {
                        alert('Publishing results for all selected classes...');
                    } else if (buttonText === 'Publish') {
                        const className = this.closest('.class-card').querySelector('.class-name').textContent;
                        alert(`Publishing results for ${className}...`);
                        
                        // Update status badge
                        this.closest('.class-card').querySelector('.class-status').className = 'class-status status-published';
                        this.closest('.class-card').querySelector('.class-status').textContent = 'Published';
                        
                        // Update buttons
                        this.textContent = 'Unpublish';
                        this.classList.remove('btn-primary');
                        this.classList.add('btn-outline');
                        
                        this.previousElementSibling.textContent = 'View';
                    } else if (buttonText === 'Unpublish') {
                        const className = this.closest('.class-card').querySelector('.class-name').textContent;
                        alert(`Unpublishing results for ${className}...`);
                        
                        // Update status badge
                        this.closest('.class-card').querySelector('.class-status').className = 'class-status status-draft';
                        this.closest('.class-card').querySelector('.class-status').textContent = 'Draft';
                        
                        // Update buttons
                        this.textContent = 'Publish';
                        this.classList.remove('btn-outline');
                        this.classList.add('btn-primary');
                        
                        this.previousElementSibling.textContent = 'Preview';
                    }
                });
            });
            
            previewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const buttonText = this.textContent.trim();
                    
                    if (buttonText === 'Preview' || buttonText === 'View') {
                        const className = this.closest('.class-card').querySelector('.class-name').textContent;
                        alert(`Previewing results for ${className}...`);
                    } else if (buttonText === 'Email to Parents') {
                        alert('Sending email notifications to parents...');
                    }
                });
            });
            
            // Pagination
            const pageButtons = document.querySelectorAll('.page-btn:not(:first-child):not(:last-child)');
            
            pageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    pageButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // In a real implementation, this would load the next page of data
                    if (this.textContent !== '1') {
                        alert(`Loading page ${this.textContent} of results...`);
                    }
                });
            });
        });
    </script>
</body>
</html>