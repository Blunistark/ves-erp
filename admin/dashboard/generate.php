<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Attendance Reports</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/generate.css">
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
            <h1 class="header-title">Generate Attendance Reports</h1>
            <span class="header-path">Dashboard > Attendance > Generate</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <h2 style="margin: 0; font-size: 1.25rem; color: #1a1a1a;">Create and Export Attendance Reports</h2>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="scheduleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Schedule Reports
                    </button>
                    <a href="view.php" class="btn btn-outline">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        View Attendance
                    </a>
                </div>
            </div>
            
            <!-- Report Tabs -->
            <div class="report-tabs">
                <div class="report-tab active" data-tab="templates">Report Templates</div>
                <div class="report-tab" data-tab="custom">Custom Report</div>
                <div class="report-tab" data-tab="scheduled">Scheduled Reports</div>
            </div>
            
            <!-- Templates Tab -->
            <div class="tab-content active" id="templates-tab">
                <div class="report-templates">
                    <!-- Daily Attendance Report -->
                    <div class="report-card" onclick="selectTemplate(this, 'daily')">
                        <div class="report-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 class="report-title">Daily Attendance Report</h3>
                        <p class="report-description">Comprehensive daily attendance report showing present, absent, and leave status for each student in a specific class.</p>
                        <ul class="report-features">
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Student-wise attendance status
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Classroom attendance summary
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Attendance percentage calculation
                            </li>
                        </ul>
                        <button class="btn btn-outline" style="width: 100%;">Generate Report</button>
                    </div>
                    
                    <!-- Monthly Summary Report -->
                    <div class="report-card" onclick="selectTemplate(this, 'monthly')">
                        <div class="report-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <h3 class="report-title">Monthly Summary Report</h3>
                        <p class="report-description">Monthly attendance overview showing attendance trends, patterns, and statistics for classes or the entire school.</p>
                        <ul class="report-features">
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Monthly attendance trends
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Class-wise comparison
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Month-to-month comparison
                            </li>
                        </ul>
                        <button class="btn btn-outline" style="width: 100%;">Generate Report</button>
                    </div>
                    
                    <!-- Student Attendance Report -->
                    <div class="report-card" onclick="selectTemplate(this, 'student')">
                        <div class="report-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                            </svg>
                        </div>
                        <h3 class="report-title">Student Attendance Report</h3>
                        <p class="report-description">Individual student attendance report detailing attendance history, patterns, and statistics for a specific time period.</p>
                        <ul class="report-features">
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Individual attendance records
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Absence patterns and trends
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Send directly to parents
                            </li>
                        </ul>
                        <button class="btn btn-outline" style="width: 100%;">Generate Report</button>
                    </div>
                    
                    <!-- Attendance Analysis Report -->
                    <div class="report-card" onclick="selectTemplate(this, 'analysis')">
                        <div class="report-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                        <h3 class="report-title">Attendance Analysis Report</h3>
                        <p class="report-description">Advanced analytics report with detailed insights, trends, and patterns in school-wide attendance data.</p>
                        <ul class="report-features">
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Trend analysis and forecasting
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Visualized attendance data
                            </li>
                            <li>
                                <svg class="feature-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Correlation with academic performance
                            </li>
                        </ul>
                        <button class="btn btn-outline" style="width: 100%;">Generate Report</button>
                    </div>
                </div>
            </div>
            
            <!-- Custom Report Tab -->
            <div class="tab-content" id="custom-tab">
                <div class="report-generator">
                    <h3 class="generator-title">Configure Custom Report</h3>
                    <form class="generator-form" id="reportForm">
                        <div class="form-group">
                            <label class="form-label">Report Type</label>
                            <select class="form-select" id="reportType">
                                <option value="daily">Daily Attendance</option>
                                <option value="summary">Summary Report</option>
                                <option value="student">Student-specific</option>
                                <option value="class">Class-wise</option>
                                <option value="analysis">Advanced Analysis</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Class/Grade</label>
                            <select class="form-select" id="classGrade">
                                <option value="">All Classes</option>
                                <option value="7">Grade 7</option>
                                <option value="8">Grade 8</option>
                                <option value="9">Grade 9</option>
                                <option value="10">Grade 10</option>
                                <option value="11">Grade 11</option>
                                <option value="12">Grade 12</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Section</label>
                            <select class="form-select" id="section">
                                <option value="">All Sections</option>
                                <option value="A">Section A</option>
                                <option value="B">Section B</option>
                                <option value="C">Section C</option>
                                <option value="D">Section D</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date Range</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="date" class="form-input" id="startDate" style="flex: 1;">
                                <span style="align-self: center;">to</span>
                                <input type="date" class="form-input" id="endDate" style="flex: 1;">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Include Status</label>
                            <div class="checkbox-group">
                                <label class="checkbox-item">
                                    <input type="checkbox" name="status" value="present" checked> Present
                                </label>                                <label class="checkbox-item">
                                    <input type="checkbox" name="status" value="absent" checked> Absent
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="status" value="leave" checked> On Leave
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Include Data</label>
                            <div class="checkbox-group">
                                <label class="checkbox-item">
                                    <input type="checkbox" name="data" value="summary" checked> Summary Statistics
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="data" value="details" checked> Detailed Records
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="data" value="charts" checked> Charts & Graphs
                                </label>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="data" value="comparison"> Historical Comparison
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Report Format</label>
                            <select class="form-select" id="reportFormat">
                                <option value="pdf">PDF Document</option>
                                <option value="excel">Excel Spreadsheet</option>
                                <option value="csv">CSV File</option>
                                <option value="html">HTML Document</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-outline" id="previewBtn">Preview Report</button>
                            <button type="button" class="btn btn-primary" id="generateBtn">Generate Report</button>
                        </div>
                    </form>
                </div>
                
                <!-- Report Preview -->
                <div class="report-preview" id="reportPreview" style="display: none;">
                    <div class="preview-header">
                        <h3 class="preview-title">Report Preview</h3>
                        <div class="preview-buttons">
                            <button class="btn btn-outline" id="editReportBtn">
                                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                                Edit Report
                            </button>
                            <button class="btn btn-success" id="downloadReportBtn">
                                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download Report
                            </button>
                        </div>
                    </div>
                    <div class="preview-content">
                        <div id="previewHeaderData">
                            <h3>Daily Attendance Report - Grade 9A</h3>
                            <p><strong>Date:</strong> March 15, 2025</p>
                            <p><strong>Generated By:</strong> Administrator</p>
                            <p><strong>School:</strong> Example High School</p>
                            <hr>
                        </div>
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Status</th>
                                    <th>Check-in Time</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>ST001</td>
                                    <td>Alex Brown</td>
                                    <td>Present</td>
                                    <td>08:25 AM</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>ST002</td>
                                    <td>Emma Smith</td>
                                    <td>Present</td>
                                    <td>08:15 AM</td>                                <td>-</td>
                                </tr>
                                <tr>
                                    <td>ST004</td>
                                    <td>Sophia Davis</td>
                                    <td>Present</td>
                                    <td>08:30 AM</td>
                                    <td>-</td>
                                </tr>
                                <tr>
                                    <td>ST005</td>
                                    <td>William Miller</td>
                                    <td>Absent</td>
                                    <td>-</td>
                                    <td>Medical leave (doctor's note provided)</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div style="margin-top: 2rem;">
                            <h4>Summary</h4>
                            <ul>
                                <li>Total Students: 5</li>                                <li>Present: 3 (75%)</li>
                                <li>Absent: 1 (25%)</li>
                                <li>On Leave: 0 (0%)</li>
                            </ul>
                        </div>
                        
                        <div class="export-options">
                            <button class="export-option">
                                <svg class="export-option-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                PDF
                            </button>
                            <button class="export-option">
                                <svg class="export-option-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Excel
                            </button>
                            <button class="export-option">
                                <svg class="export-option-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                </svg>
                                CSV
                            </button>
                            <button class="export-option">
                                <svg class="export-option-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                HTML
                            </button>
                            <button class="export-option">
                                <svg class="export-option-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                Email
                            </button>
                            <button class="export-option">
                                <svg class="export-option-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                                Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Scheduled Reports Tab -->
            <div class="tab-content" id="scheduled-tab">
                <div class="scheduled-reports">
                    <div class="scheduled-header">
                        <h3 class="scheduled-title">Scheduled Reports</h3>
                        <button class="btn btn-primary" id="newScheduleBtn">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            New Schedule
                        </button>
                    </div>
                    
                    <table class="scheduled-table">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Report Type</th>
                                <th>Recipients</th>
                                <th>Frequency</th>
                                <th>Next Run</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="schedule-info">
                                        <span class="schedule-name">Daily Class Attendance</span>
                                        <span class="schedule-details">Grade 9A - Homeroom</span>
                                    </div>
                                </td>
                                <td>Daily Attendance</td>
                                <td>Class Teachers, Principal</td>
                                <td>Daily (5:00 PM)</td>
                                <td>Mar 15, 2025</td>
                                <td><span class="schedule-status status-active">Active</span></td>
                                <td class="schedule-actions">
                                    <button class="action-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Pause">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                    <div class="schedule-info">
                                        <span class="schedule-name">Weekly Attendance Summary</span>
                                        <span class="schedule-details">All Classes</span>
                                    </div>
                                </td>
                                <td>Summary Report</td>
                                <td>All Teachers, Admin Staff</td>
                                <td>Weekly (Monday, 8:00 AM)</td>
                                <td>Mar 17, 2025</td>
                                <td><span class="schedule-status status-active">Active</span></td>
                                <td class="schedule-actions">
                                    <button class="action-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Pause">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                    <div class="schedule-info">
                                        <span class="schedule-name">Monthly Attendance Report</span>
                                        <span class="schedule-details">School-wide Analysis</span>
                                    </div>
                                </td>
                                <td>Analysis Report</td>
                                <td>Principal, Department Heads</td>
                                <td>Monthly (1st day, 9:00 AM)</td>
                                <td>Apr 1, 2025</td>
                                <td><span class="schedule-status status-active">Active</span></td>
                                <td class="schedule-actions">
                                    <button class="action-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Pause">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                    <div class="schedule-info">
                                        <span class="schedule-name">Parent Attendance Report</span>
                                        <span class="schedule-details">Individual Student Reports</span>
                                    </div>
                                </td>
                                <td>Student Report</td>
                                <td>Parents, Class Teachers</td>
                                <td>Weekly (Friday, 4:00 PM)</td>
                                <td>Mar 21, 2025</td>
                                <td><span class="schedule-status status-paused">Paused</span></td>
                                <td class="schedule-actions">
                                    <button class="action-btn" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Resume">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
        
        function selectTemplate(card, templateType) {
            // Remove selected class from all cards
            document.querySelectorAll('.report-card').forEach(c => {
                c.classList.remove('selected');
            });
            
            // Add selected class to clicked card
            card.classList.add('selected');
            
            // Here you could set form values based on the selected template
            alert(`Selected the ${templateType} report template`);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Tab Switching
            const tabs = document.querySelectorAll('.report-tab');
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
            
            // Report Preview Toggle
            const previewBtn = document.getElementById('previewBtn');
            const reportForm = document.getElementById('reportForm');
            const reportPreview = document.getElementById('reportPreview');
            const editReportBtn = document.getElementById('editReportBtn');
            
            previewBtn.addEventListener('click', function() {
                reportForm.style.display = 'none';
                reportPreview.style.display = 'block';
            });
            
            editReportBtn.addEventListener('click', function() {
                reportPreview.style.display = 'none';
                reportForm.style.display = 'grid';
            });
            
            // Generate Report
            const generateBtn = document.getElementById('generateBtn');
            generateBtn.addEventListener('click', function() {
                // Get form values
                const reportType = document.getElementById('reportType').value;
                const classGrade = document.getElementById('classGrade').value;
                const section = document.getElementById('section').value;
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const reportFormat = document.getElementById('reportFormat').value;
                
                // Display report generation message
                let message = `Generating ${reportType} report`;
                if (classGrade) message += ` for Grade ${classGrade}`;
                if (section) message += ` Section ${section}`;
                if (startDate || endDate) {
                    message += ` for the period`;
                    if (startDate) message += ` from ${startDate}`;
                    if (endDate) message += ` to ${endDate}`;
                }
                message += ` in ${reportFormat.toUpperCase()} format.`;
                
                alert(message);
                
                // Show the preview
                reportForm.style.display = 'none';
                reportPreview.style.display = 'block';
            });
            
            // Download Report
            const downloadReportBtn = document.getElementById('downloadReportBtn');
            downloadReportBtn.addEventListener('click', function() {
                alert('Downloading report...');
            });
            
            // Export Options
            const exportOptions = document.querySelectorAll('.export-option');
            exportOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const format = this.textContent.trim();
                    alert(`Exporting report in ${format} format...`);
                });
            });
            
            // Schedule Reports
            const scheduleBtn = document.getElementById('scheduleBtn');
            const newScheduleBtn = document.getElementById('newScheduleBtn');
            
            scheduleBtn.addEventListener('click', function() {
                // Switch to scheduled reports tab
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                document.querySelector('.report-tab[data-tab="scheduled"]').classList.add('active');
                document.getElementById('scheduled-tab').classList.add('active');
            });
            
            newScheduleBtn.addEventListener('click', function() {
                alert('Creating a new scheduled report...');
            });
            
            // Scheduled Reports Actions
            const scheduleActions = document.querySelectorAll('.schedule-actions .action-btn');
            
            scheduleActions.forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.getAttribute('title');
                    const reportName = this.closest('tr').querySelector('.schedule-name').textContent;
                    
                    alert(`${action} scheduled report: ${reportName}`);
                });
            });
        });
    </script>
</body>
</html>