<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendance</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/view.css">
    <style>
        /* Class and Section selection styles */
        .selection-container {
            margin-bottom: 2rem;
        }
        
        .selection-grid {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }
        
        .selection-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 1.5rem;
            color: #2d3748;
        }
        
        .selection-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .back-button {
            display: flex;
            align-items: center;
            background: none;
            border: none;
            color: #4a5568;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 1rem;
        }
        
        .back-button:hover {
            background: #f7fafc;
        }
        
        .back-button svg {
            margin-right: 0.5rem;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .class-card, .section-card {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        
        .class-card:hover, .section-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-color: #cbd5e0;
        }
        
        .class-card {
            background: #ebf8ff;
            border-color: #bee3f8;
        }
        
        .section-card {
            background: #e6fffa;
            border-color: #b2f5ea;
        }
        
        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2d3748;
        }
        
        .card-subtitle {
            font-size: 0.875rem;
            color: #718096;
            margin-bottom: 1rem;
        }
        
        .card-stats {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }
        
        .card-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .stat-value {
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .stat-value.value-present {
            color: #2f855a;
        }
        
        .stat-value.value-absent {
            color: #c53030;
        }
        
        .stat-label {
            font-size: 0.75rem;
            color: #718096;
        }
        
        .card-loading {
            grid-column: 1 / -1;
            text-align: center;
            padding: 2rem;
            color: #718096;
        }
        
        /* Hide the default views initially */
        #attendanceOverviewContainer, 
        #calendarNavContainer, 
        #attendanceSummaryContainer, 
        #attendanceTableContainer {
            display: none;
        }
    </style>
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
            <h1 class="header-title">View Attendance</h1>
            <span class="header-path">Dashboard > Attendance > View</span>
        </header>

        <main class="dashboard-content">
            <!-- Attendance Overview (moved to top) -->
            <div id="attendanceOverviewContainer" class="attendance-view-container" style="display: none;">
                <div class="attendance-overview">
                    <div class="overview-card present-card">
                        <h3 class="overview-title">Present</h3>
                        <div class="overview-value">85.7%</div>
                        <div class="overview-percentage">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="percentage-increase">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="percentage-increase">2.3% from last month</span>
                        </div>
                    </div>
                    <div class="overview-card absent-card">
                        <h3 class="overview-title">Absent</h3>
                        <div class="overview-value">8.2%</div>
                        <div class="overview-percentage">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="percentage-decrease">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <span class="percentage-decrease">1.4% from last month</span>
                        </div>
                    </div>
                    <div class="overview-card leave-card">
                        <h3 class="overview-title">On Leave</h3>
                        <div class="overview-value">1.6%</div>
                        <div class="overview-percentage">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="percentage-decrease">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <span class="percentage-decrease">0.1% from last month</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Summary (moved to top) -->
            <div id="attendanceSummaryContainer" class="attendance-summary" style="display: none;">
                <h3 class="summary-title">March 2025 Summary</h3>
                <div class="summary-stats">
                    <div class="stat-item">
                        <div class="stat-value">85.7%</div>
                        <div class="stat-label">Average Present</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">8.2%</div>
                        <div class="stat-label">Average Absent</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">1.6%</div>
                        <div class="stat-label">On Leave</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">22</div>
                        <div class="stat-label">School Days</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value">32</div>
                        <div class="stat-label">Students</div>
                    </div>
                </div>
            </div>

            <!-- Students Container (moved to top after statistics) -->
            <div id="studentsContainer" class="students-container" style="display: none;">
                <h3 class="section-title">Students</h3>
                <div id="studentsGrid" class="cards-grid">
                    <!-- Student cards will be dynamically added here -->
                </div>
            </div>

            <!-- Student Attendance Details Container (moved to top) -->
            <div id="studentAttendanceDetailsContainer" class="attendance-details-container" style="display: none;">
                <div class="details-header">
                    <button id="backToStudentsButton" class="back-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Students
                    </button>
                    <h3 id="studentAttendanceDetailsTitle" class="details-title">Attendance Details for Student</h3>
                </div>
                <div class="date-filter">
                    <label for="attendanceStartDate">From:</label>
                    <input type="date" id="attendanceStartDate" class="date-input">
                    <label for="attendanceEndDate">To:</label>
                    <input type="date" id="attendanceEndDate" class="date-input">
                    <button id="applyDateFilterButton" class="filter-btn filter-btn-apply">Apply</button>
                </div>
                <div class="attendance-details-content">
                    <div class="attendance-status-summary">
                        <div class="status-card present-card">
                            <h4>Present Days</h4>
                            <div class="status-count" id="presentDaysCount">0</div>
                            <div class="status-percent" id="presentDaysPercent">0%</div>
                        </div>
                        <div class="status-card absent-card">
                            <h4>Absent Days</h4>
                            <div class="status-count" id="absentDaysCount">0</div>
                            <div class="status-percent" id="absentDaysPercent">0%</div>
                        </div>
                    </div>
                    <div class="attendance-records">
                        <h4>Attendance Records</h4>
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceRecordsTableBody">
                                <!-- Records will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="studentSearch" class="search-input" placeholder="Search by student name or ID...">
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
                    <button class="btn btn-primary" id="editAttendanceBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        Edit Attendance
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel">
                <h3 class="filter-title">Filter Attendance Records</h3>
                <form class="filter-form">
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
                        <label class="filter-label">Section</label>
                        <select class="filter-select" id="sectionFilter">
                            <option value="">All Sections</option>
                            <option value="A">Section A</option>
                            <option value="B">Section B</option>
                            <option value="C">Section C</option>
                            <option value="D">Section D</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Date Range</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" class="filter-input" id="startDate" style="flex: 1;">
                            <span style="align-self: center;">to</span>
                            <input type="date" class="filter-input" id="endDate" style="flex: 1;">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Attendance Status</label>
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="leave">On Leave</option>
                        </select>
                    </div>
                </form>
                <div class="filter-actions">
                    <button class="filter-btn filter-btn-reset">Reset</button>
                    <button class="filter-btn filter-btn-apply">Apply Filters</button>
                </div>
            </div>

            <!-- Class & Section Selection Cards -->
            <div class="selection-container">
                <!-- Class selection initially visible -->
                <div id="classSelectionContainer" class="selection-grid">
                    <h3 class="selection-title">Select a Class</h3>
                    <div id="classCards" class="cards-grid">
                        <!-- Class cards will be dynamically added here -->
                        <div class="card-loading">Loading classes...</div>
                    </div>
                </div>

                <!-- Section selection (initially hidden) -->
                <div id="sectionSelectionContainer" class="selection-grid" style="display: none;">
                    <div class="selection-header">
                        <button class="back-button" id="backToClassButton">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Back to Classes
                        </button>
                        <h3 class="selection-title" id="sectionSelectionTitle">Sections for Class</h3>
                    </div>
                    <div id="sectionCards" class="cards-grid">
                        <!-- Section cards will be dynamically added here -->
                    </div>
                </div>
                
                <!-- Student selection (initially hidden) -->
                <div id="studentSelectionContainer" class="selection-container">
                    <div class="selection-grid">
                        <div class="selection-header">
                            <button id="backToSectionButton" class="back-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Back to Sections
                            </button>
                            <h3 class="selection-title" id="studentSelectionTitle">Students in Section</h3>
                        </div>
                        <div id="studentCards" class="cards-grid">
                            <!-- Student cards will be dynamically added here -->
                            <div class="card-loading">Loading students...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Navigation -->
            <div id="calendarNavContainer" class="calendar-nav" style="display: none;">
                <h3 class="calendar-title">Grade 9A Attendance</h3>
                <div class="calendar-controls">
                    <div class="calendar-date-selector">
                        <button class="calendar-btn">
                            <svg class="calendar-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <label for="monthSelector" style="margin-right: 0.5rem;">Month:</label>
                        <select id="monthSelector" class="filter-select" style="min-width: 150px;">
                            <option value="3">March 2025</option>
                            <option value="2">February 2025</option>
                            <option value="1">January 2025</option>
                            <option value="12">December 2024</option>
                        </select>
                        <button class="calendar-btn">
                            <svg class="calendar-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Students Container (moved after monthly statistics) -->
            <div id="studentsContainer" class="students-container" style="display: none;">
                <h3 class="section-title">Students</h3>
                <div id="studentsGrid" class="cards-grid">
                    <!-- Student cards will be dynamically added here -->
                </div>
            </div>

            <!-- Student Attendance Details Container -->
            <div id="studentAttendanceDetailsContainer" class="attendance-details-container" style="display: none;">
                <div class="details-header">
                    <button id="backToStudentsButton" class="back-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Students
                    </button>
                    <h3 id="studentAttendanceDetailsTitle" class="details-title">Attendance Details for Student</h3>
                </div>
                <div class="date-filter">
                    <label for="attendanceStartDate">From:</label>
                    <input type="date" id="attendanceStartDate" class="date-input">
                    <label for="attendanceEndDate">To:</label>
                    <input type="date" id="attendanceEndDate" class="date-input">
                    <button id="applyDateFilterButton" class="filter-btn filter-btn-apply">Apply</button>
                </div>
                <div class="attendance-details-content">
                    <div class="attendance-status-summary">
                        <div class="status-card present-card">
                            <h4>Present</h4>
                            <div id="presentDaysCount" class="status-count">0</div>
                            <div id="presentDaysPercent" class="status-percent">0%</div>
                        </div>
                        <div class="status-card absent-card">
                            <h4>Absent</h4>
                            <div id="absentDaysCount" class="status-count">0</div>
                            <div id="absentDaysPercent" class="status-percent">0%</div>
                        </div>
                    </div>
                    <div class="attendance-records">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceRecordsTableBody">
                                <!-- Attendance records will be added here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Attendance Table (Daily View) -->
                <div id="attendanceTableContainer" class="attendance-table-container">
                <table class="attendance-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                                <!-- Date headers will be dynamically generated -->
                            <th>Present %</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                        <tbody id="attendanceTableBody">
                        <!-- Attendance data will be dynamically populated here -->
                            <tr>
                                <td colspan="10" class="loading-message">Loading attendance data...</td>
                            </tr>
                    </tbody>
                </table>
                </div>
            </div>

            <div class="pagination">
                <div class="pagination-info">
                    Showing 1-5 of 32 students
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
            // Filter panel toggle
            const filterToggleBtn = document.getElementById('filterToggleBtn');
            const filterPanel = document.getElementById('filterPanel');
            
            // Initially hide the filter panel
            filterPanel.style.display = 'none';
            
            filterToggleBtn.addEventListener('click', () => {
                if (filterPanel.style.display === 'none') {
                    filterPanel.style.display = 'block';
                } else {
                    filterPanel.style.display = 'none';
                }
            });
            
            // Filter functionality
            const filterForm = document.querySelector('.filter-form');
            const filterApplyBtn = document.querySelector('.filter-btn-apply');
            const filterResetBtn = document.querySelector('.filter-btn-reset');
            
            filterApplyBtn.addEventListener('click', () => {
                // Get filter values
                const classFilter = document.getElementById('classFilter').value;
                const sectionFilter = document.getElementById('sectionFilter').value;
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const statusFilter = document.getElementById('statusFilter').value;
                
                // For demo purposes, just show an alert with the selected filters
                let filterMessage = 'Applied filters:\n';
                filterMessage += classFilter ? `Class: Grade ${classFilter}\n` : 'Class: All\n';
                filterMessage += sectionFilter ? `Section: ${sectionFilter}\n` : 'Section: All\n';
                filterMessage += startDate ? `Start Date: ${startDate}\n` : 'Start Date: -\n';
                filterMessage += endDate ? `End Date: ${endDate}\n` : 'End Date: -\n';
                filterMessage += statusFilter ? `Status: ${statusFilter.charAt(0).toUpperCase() + statusFilter.slice(1)}\n` : 'Status: All\n';
                
                alert(filterMessage);
                
                // In a real implementation, this would filter the data accordingly
                filterPanel.style.display = 'none';
            });
            
            filterResetBtn.addEventListener('click', () => {
                // Reset all form fields
                filterForm.reset();
            });
            
            // Search functionality
            const searchInput = document.getElementById('studentSearch');
            const tableRows = document.querySelectorAll('.attendance-table tbody tr');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                tableRows.forEach(row => {
                    const studentName = row.querySelector('.student-name').textContent.toLowerCase();
                    const studentId = row.querySelector('.student-id').textContent.toLowerCase();
                    
                    const matchFound = 
                        studentName.includes(searchTerm) || 
                        studentId.includes(searchTerm);
                    
                    row.style.display = matchFound ? '' : 'none';
                });
            });
            
            // Calendar navigation
            const monthSelector = document.getElementById('monthSelector');
            const prevMonthBtn = document.querySelector('.calendar-btn:first-child');
            const nextMonthBtn = document.querySelector('.calendar-btn:last-child');
            
            monthSelector.addEventListener('change', function() {
                // In a real implementation, this would load data for the selected month
                const selectedMonth = this.options[this.selectedIndex].text;
                alert(`Loading attendance data for ${selectedMonth}...`);
            });
            
            prevMonthBtn.addEventListener('click', function() {
                // Get current selected index
                const currentIndex = monthSelector.selectedIndex;
                
                // If not at the first option, select the previous one
                if (currentIndex < monthSelector.options.length - 1) {
                    monthSelector.selectedIndex = currentIndex + 1;
                    monthSelector.dispatchEvent(new Event('change'));
                }
            });
            
            nextMonthBtn.addEventListener('click', function() {
                // Get current selected index
                const currentIndex = monthSelector.selectedIndex;
                
                // If not at the last option, select the next one (previous month)
                if (currentIndex > 0) {
                    monthSelector.selectedIndex = currentIndex - 1;
                    monthSelector.dispatchEvent(new Event('change'));
                }
            });
            
            // Pagination functionality
            const pageButtons = document.querySelectorAll('.page-btn:not(:first-child):not(:last-child)');
            
            pageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    pageButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // In a real implementation, this would load the next page of data
                    if (this.textContent !== '1') {
                        alert(`Loading page ${this.textContent} of students...`);
                    }
                });
            });
            
            // Action buttons
            const viewButtons = document.querySelectorAll('.action-btn[title="View Details"]');
            const editButtons = document.querySelectorAll('.action-btn[title="Edit"]');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const studentName = this.closest('tr').querySelector('.student-name').textContent;
                    alert(`Viewing detailed attendance records for ${studentName}`);
                });
            });
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const studentName = this.closest('tr').querySelector('.student-name').textContent;
                    alert(`Editing attendance records for ${studentName}`);
                });
            });

            // Global variables to store attendance data
            let currentClassId = '';
            let currentSectionId = '';
            let currentMonth = new Date().getMonth() + 1; // Current month (1-12)
            let currentYear = new Date().getFullYear();
            let allStudents = {};
            let calendarData = {};
            let daysInMonth = [];
            let allClasses = [];
            let allSections = [];
            
            // Load class dropdown and class cards
            function loadClasses() {
                fetch('attendance_actions.php?get_classes=1')
                    .then(res => res.json())
                    .then(classes => {
                        // Store classes globally
                        allClasses = classes;
                        
                        // Populate class filter dropdown
                        const classFilter = document.getElementById('classFilter');
                        classFilter.innerHTML = '<option value="">All Classes</option>';
                        
                        classes.forEach(cls => {
                            const option = document.createElement('option');
                            option.value = cls.id;
                            option.textContent = `Grade ${cls.name}`;
                            classFilter.appendChild(option);
                        });
                        
                        // Event listener for class selection change
                        classFilter.addEventListener('change', function() {
                            currentClassId = this.value;
                            loadSections(currentClassId);
                            
                            // Update attendance view if a class is selected
                            if (currentClassId) {
                                fetchAttendanceStats();
                                fetchCalendarData();
                                
                                // Show the section selection if a class is selected from dropdown
                                if (currentClassId) {
                                    document.getElementById('classSelectionContainer').style.display = 'none';
                                    document.getElementById('sectionSelectionContainer').style.display = 'block';
                                    const className = classFilter.options[classFilter.selectedIndex].text;
                                    document.getElementById('sectionSelectionTitle').textContent = `Sections for ${className}`;
                                    renderSectionCards(currentClassId);
                                }
                            }
                        });
                        
                        // Load class cards
                        renderClassCards(classes);
                    });
            }
            
            // Render class cards
            function renderClassCards(classes) {
                const classCardsContainer = document.getElementById('classCards');
                classCardsContainer.innerHTML = '';
                
                if (classes.length === 0) {
                    classCardsContainer.innerHTML = '<div class="card-loading">No classes found</div>';
                    return;
                }
                
                // Create a card for each class
                classes.forEach(cls => {
                    // Create class card element
                    const card = document.createElement('div');
                    card.className = 'class-card';
                    card.dataset.classId = cls.id;
                    
                    // Add class info
                    card.innerHTML = `
                        <div class="card-title">Grade ${cls.name}</div>
                        <div class="card-subtitle">${cls.students || 0} Students</div>
                        <div class="card-stats">
                            <div class="card-stat">
                                <div class="stat-value value-present">${cls.attendance?.present || 0}%</div>
                                <div class="stat-label">Present</div>
                            </div>
                            <div class="card-stat">
                                <div class="stat-value value-absent">${cls.attendance?.absent || 0}%</div>
                                <div class="stat-label">Absent</div>
                            </div>
                        </div>
                    `;
                    
                    // Add click event to view class sections
                    card.addEventListener('click', function() {
                        const classId = this.dataset.classId;
                        currentClassId = classId;
                        
                        // Update class filter dropdown
                        document.getElementById('classFilter').value = classId;
                        
                        // Show section selection view
                        document.getElementById('classSelectionContainer').style.display = 'none';
                        document.getElementById('sectionSelectionContainer').style.display = 'block';
                        document.getElementById('sectionSelectionTitle').textContent = `Sections for Grade ${cls.name}`;
                        
                        // Load sections for this class
                        renderSectionCards(classId);
                    });
                    
                    classCardsContainer.appendChild(card);
                });
                
                // Fetch attendance stats for each class to update the cards
                fetchClassAttendanceStats();
            }
            
            // Fetch class attendance stats for cards
            function fetchClassAttendanceStats() {
                // Batch request for all classes attendance stats
                fetch('attendance_actions.php?get_class_stats=1')
                    .then(res => res.json())
                    .then(stats => {
                        // Update each class card with stats
                        stats.forEach(stat => {
                            const card = document.querySelector(`.class-card[data-class-id="${stat.class_id}"]`);
                            if (card) {
                                const studentCount = card.querySelector('.card-subtitle');
                                studentCount.textContent = `${stat.student_count} Students`;
                                
                                const presentValue = card.querySelector('.stat-value.value-present');
                                presentValue.textContent = `${stat.percentages.present}%`;
                                
                                const absentValue = card.querySelector('.stat-value.value-absent');
                                absentValue.textContent = `${stat.percentages.absent}%`;
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching class stats:', error);
                    });
            }
            
            // Render section cards for a class
            function renderSectionCards(classId) {
                const sectionCardsContainer = document.getElementById('sectionCards');
                sectionCardsContainer.innerHTML = '<div class="card-loading">Loading sections...</div>';
                
                // Get the class name
                const selectedClass = allClasses.find(cls => cls.id == classId);
                const className = selectedClass ? selectedClass.name : classId;
                
                // Fetch sections for this class
                fetch(`attendance_actions.php?get_sections=1&class_id=${classId}`)
                    .then(res => res.json())
                    .then(sections => {
                        // Store sections globally
                        allSections = sections;
                        
                        // Update dropdown
                        loadSections(classId, sections);
                        
                        // Clear container
                        sectionCardsContainer.innerHTML = '';
                        
                        if (sections.length === 0) {
                            sectionCardsContainer.innerHTML = '<div class="card-loading">No sections found for this class</div>';
                            return;
                        }
                        
                        // Create a card for each section
                        sections.forEach(section => {
                            // Create section card element
                            const card = document.createElement('div');
                            card.className = 'section-card';
                            card.dataset.sectionId = section.id;
                            
                            // Add section info
                            card.innerHTML = `
                                <div class="card-title">Section ${section.name}</div>
                                <div class="card-subtitle">${section.students || 0} Students</div>
                                <div class="card-stats">
                                    <div class="card-stat">
                                        <div class="stat-value value-present">${section.attendance?.present || 0}%</div>
                                        <div class="stat-label">Present</div>
                                    </div>
                                    <div class="card-stat">
                                        <div class="stat-value value-absent">${section.attendance?.absent || 0}%</div>
                                        <div class="stat-label">Absent</div>
                                    </div>
                                </div>
                            `;
                            
                            // Add click event to view section attendance
                            card.addEventListener('click', function() {
                                const sectionId = this.dataset.sectionId;
                                currentSectionId = sectionId;
                                
                                // Update section filter dropdown
                                document.getElementById('sectionFilter').value = sectionId;
                                
                                // Update title and show section selection
                                updateClassTitle();
                                
                                // Load students in this section
                                loadStudentsInSection(sectionId);
                                
                                // Show attendance statistics
                                showAttendanceStats();
                                
                                // Update student selection title
                                const sectionName = document.querySelector(`[data-section-id="${sectionId}"] .card-title`).textContent;
                                document.getElementById('studentSelectionTitle').textContent = `Students in ${sectionName}`;
                            });
                            
                            sectionCardsContainer.appendChild(card);
                        });
                        
                        // Also add a card for all sections (combined view)
                        const allSectionsCard = document.createElement('div');
                        allSectionsCard.className = 'section-card';
                        allSectionsCard.style.background = '#edf2f7';
                        allSectionsCard.style.borderColor = '#cbd5e0';
                        
                        allSectionsCard.innerHTML = `
                            <div class="card-title">All Sections</div>
                            <div class="card-subtitle">Combined View</div>
                            <div class="card-stats">
                                <div class="card-stat">
                                    <div class="stat-value">—</div>
                                    <div class="stat-label">View All</div>
                                </div>
                            </div>
                        `;
                        
                        // Add click event to view all sections for this class
                        allSectionsCard.addEventListener('click', function() {
                            currentSectionId = '';
                            
                            // Update section filter dropdown
                            document.getElementById('sectionFilter').value = '';
                            
                            // Update title and show attendance view
                            updateClassTitle();
                            showAttendanceView();
                            
                            // Load attendance data for all sections of this class
                            fetchAttendanceStats();
                            fetchCalendarData();
                        });
                        
                        sectionCardsContainer.appendChild(allSectionsCard);
                        
                        // Fetch section attendance stats for cards
                        fetchSectionAttendanceStats(classId);
                    })
                    .catch(error => {
                        console.error('Error fetching sections:', error);
                        sectionCardsContainer.innerHTML = '<div class="card-loading">Error loading sections</div>';
                    });
            }
            
            // Fetch section attendance stats for cards
            function fetchSectionAttendanceStats(classId) {
                // Batch request for all sections attendance stats for this class
                fetch(`attendance_actions.php?get_section_stats=1&class_id=${classId}`)
                    .then(res => res.json())
                    .then(stats => {
                        // Update each section card with stats
                        stats.forEach(stat => {
                            const card = document.querySelector(`.section-card[data-section-id="${stat.section_id}"]`);
                            if (card) {
                                const studentCount = card.querySelector('.card-subtitle');
                                studentCount.textContent = `${stat.student_count} Students`;
                                
                                const presentValue = card.querySelector('.stat-value.value-present');
                                presentValue.textContent = `${stat.percentages.present}%`;
                                
                                const absentValue = card.querySelector('.stat-value.value-absent');
                                absentValue.textContent = `${stat.percentages.absent}%`;
                            }
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching section stats:', error);
                    });
            }
            
            // Function to show the attendance view
            function showAttendanceView() {
                // Hide selection containers
                document.getElementById('classSelectionContainer').style.display = 'none';
                document.getElementById('sectionSelectionContainer').style.display = 'none';
                document.getElementById('studentSelectionContainer').style.display = 'none';
                
                // Show attendance containers
                document.getElementById('attendanceOverviewContainer').style.display = 'block';
                document.getElementById('calendarNavContainer').style.display = 'flex';
                document.getElementById('attendanceSummaryContainer').style.display = 'block';
                document.getElementById('attendanceTableContainer').style.display = 'block';
            }
            
            // Function to show only attendance statistics (without calendar view)
            function showAttendanceStats() {
                // Hide selection containers
                document.getElementById('classSelectionContainer').style.display = 'none';
                
                // Show attendance overview and summary containers
                document.getElementById('attendanceOverviewContainer').style.display = 'block';
                document.getElementById('attendanceSummaryContainer').style.display = 'block';
                
                // Hide calendar nav and attendance table
                document.getElementById('calendarNavContainer').style.display = 'none';
                document.getElementById('attendanceTableContainer').style.display = 'none';
                
                // Show section selection and student selection
                document.getElementById('sectionSelectionContainer').style.display = 'block';
                document.getElementById('studentSelectionContainer').style.display = 'block';
                
                // Update statistics
                fetchAttendanceStats();
            }
            
            // Function to go back to class selection
            function backToClassSelection() {
                // Hide section selection, student selection, and attendance view
                document.getElementById('sectionSelectionContainer').style.display = 'none';
                document.getElementById('studentSelectionContainer').style.display = 'none';
                document.getElementById('attendanceOverviewContainer').style.display = 'none';
                document.getElementById('calendarNavContainer').style.display = 'none';
                document.getElementById('attendanceSummaryContainer').style.display = 'none';
                document.getElementById('attendanceTableContainer').style.display = 'none';
                
                // Show class selection
                document.getElementById('classSelectionContainer').style.display = 'block';
                
                // Reset selection
                currentClassId = '';
                currentSectionId = '';
                document.getElementById('classFilter').value = '';
                document.getElementById('sectionFilter').innerHTML = '<option value="">All Sections</option>';
            }
            
            // Function to go back to section selection
            function backToSectionSelection() {
                // Hide student selection
                document.getElementById('studentSelectionContainer').style.display = 'none';
                
                // Show section selection
                document.getElementById('sectionSelectionContainer').style.display = 'block';
                
                // Show statistics summary without calendar view
                showAttendanceStats();
            }
            
            // Function to load students in a section
            function loadStudentsInSection(sectionId) {
                // Get the section name and class name
                const sectionName = document.querySelector(`[data-section-id="${sectionId}"] .card-title`).textContent;
                const className = document.getElementById('sectionSelectionTitle').textContent.replace('Sections for ', '');
                
                // Use our new function that loads students in a separate container
                loadStudentsForSection(sectionId, sectionName, currentClassId, className);
                
                // Keep this for backward compatibility
                const studentCardsContainer = document.getElementById('studentCards');
                studentCardsContainer.innerHTML = '<div class="card-loading">Students are displayed in the new layout</div>';
                
                // Make sure student selection container is shown (but it will be hidden by our new function)
                document.getElementById('studentSelectionContainer').style.display = 'block';
                
                // Only show debug info if debug=1 is in URL
                const showDebug = window.location.search.includes('debug=1');
                let debugContainer;
                
                if (showDebug) {
                    debugContainer = document.createElement('div');
                    debugContainer.id = 'debugInfo';
                    debugContainer.style.background = '#ffe8e8';
                    debugContainer.style.padding = '10px';
                    debugContainer.style.margin = '10px 0';
                    debugContainer.style.border = '1px solid #ff9999';
                    debugContainer.innerHTML = '<strong>Debug:</strong> Fetching students for section ID: ' + sectionId;
                    
                    studentCardsContainer.appendChild(debugContainer);
                }
                
                // Fetch students for this section
                fetch(`attendance_actions.php?get_students_by_section=1&section_id=${sectionId}&class_id=${currentClassId}`)
                    .then(res => {
                        // Add debug info about response if debugging is enabled
                        if (showDebug) {
                            debugContainer.innerHTML += '<br>Response status: ' + res.status;
                        }
                        return res.json();
                    })
                    .then(students => {
                        // Add debug info about data if debugging is enabled
                        if (showDebug) {
                            debugContainer.innerHTML += '<br>Received data type: ' + typeof students;
                            debugContainer.innerHTML += '<br>Is array: ' + Array.isArray(students);
                            debugContainer.innerHTML += '<br>Length: ' + (Array.isArray(students) ? students.length : 'N/A');
                            debugContainer.innerHTML += '<br>Data: ' + JSON.stringify(students).substring(0, 100) + '...';
                        }
                        
                        // Clear container
                        studentCardsContainer.innerHTML = '';
                        
                        // Re-add debug info if debugging is enabled
                        if (showDebug) {
                            studentCardsContainer.appendChild(debugContainer);
                        }
                        
                        if (!Array.isArray(students) || students.length === 0) {
                            // If no students returned or invalid data, use hardcoded sample data for Class II Section A
                            if (showDebug) {
                                debugContainer.innerHTML += '<br><strong>Using sample data since no students were returned from API</strong>';
                            }
                            
                            const sampleStudents = [
                                {
                                    user_id: 58,
                                    full_name: "SHREENIKA SHARMA",
                                    admission_number: "00",
                                    roll_number: 0,
                                    profile_image: "../../assets/img/default-profile.png",
                                    attendance: {
                                        percentages: { present: 95.6, absent: 4.4, holiday: 0 },
                                        counts: { present: 11, absent: 1, holiday: 0 },
                                        total_days: 12
                                    }
                                },
                                {
                                    user_id: 27,
                                    full_name: "HARSHINI A P",
                                    admission_number: "01/2024-25",
                                    roll_number: 1,
                                    profile_image: "../../assets/img/default-profile.png",
                                    attendance: {
                                        percentages: { present: 90.9, absent: 9.1, holiday: 0 },
                                        counts: { present: 10, absent: 1, holiday: 0 },
                                        total_days: 11
                                    }
                                },
                                {
                                    user_id: 65,
                                    full_name: "VIRAT A M",
                                    admission_number: "02/2024-25",
                                    roll_number: 2,
                                    profile_image: "../../assets/img/default-profile.png",
                                    attendance: {
                                        percentages: { present: 100, absent: 0, holiday: 0 },
                                        counts: { present: 11, absent: 0, holiday: 0 },
                                        total_days: 11
                                    }
                                },
                                {
                                    user_id: 9,
                                    full_name: "ANVITHA M",
                                    admission_number: "03/2024-25",
                                    roll_number: 3,
                                    profile_image: "../../assets/img/default-profile.png",
                                    attendance: {
                                        percentages: { present: 88.9, absent: 11.1, holiday: 0 },
                                        counts: { present: 8, absent: 1, holiday: 0 },
                                        total_days: 9
                                    }
                                },
                                {
                                    user_id: 15,
                                    full_name: "CHINMAYI",
                                    admission_number: "04/2024-25",
                                    roll_number: 4,
                                    profile_image: "../../assets/img/default-profile.png",
                                    attendance: {
                                        percentages: { present: 100, absent: 0, holiday: 0 },
                                        counts: { present: 12, absent: 0, holiday: 0 },
                                        total_days: 12
                                    }
                                },
                                {
                                    user_id: 12,
                                    full_name: "CHANDAN S",
                                    admission_number: "05/2024-25",
                                    roll_number: 5,
                                    profile_image: "../../assets/img/default-profile.png",
                                    attendance: {
                                        percentages: { present: 100, absent: 0, holiday: 0 },
                                        counts: { present: 12, absent: 0, holiday: 0 },
                                        total_days: 12
                                    }
                                }
                            ];
                            
                            students = sampleStudents;
                            
                            // Show a general message when not in debug mode
                            if (!showDebug) {
                                const infoMessage = document.createElement('div');
                                infoMessage.className = 'card-loading';
                                infoMessage.textContent = 'Using sample data - real student data will appear here when available';
                                infoMessage.style.marginBottom = '15px';
                                studentCardsContainer.appendChild(infoMessage);
                            }
                        }
                        
                        // Create a card for each student
                        students.forEach(student => {
                            // Create student card element
                            const card = document.createElement('div');
                            card.className = 'student-card';
                            card.dataset.studentId = student.user_id;
                            
                            // Calculate attendance summary
                            const presentPercent = student.attendance?.percentages?.present || 0;
                            const absentPercent = student.attendance?.percentages?.absent || 0;
                            
                            // Determine status color
                            let statusClass = 'status-good';
                            if (presentPercent < 75) {
                                statusClass = 'status-warning';
                            }
                            if (presentPercent < 60) {
                                statusClass = 'status-danger';
                            }
                            
                            // Generate avatar (image or initials)
                            const nameParts = student.full_name.trim().split(' ');
                            const avatar = nameParts.length >= 2 ? (nameParts[0][0] + nameParts[1][0]).toUpperCase() : nameParts[0] ? nameParts[0][0].toUpperCase() : 'S';
                            
                            // Add student info
                            card.innerHTML = `
                                <div class="student-card-header ${statusClass}">
                                    ${student.profile_image ? 
                                        `<img src="${student.profile_image}" alt="${student.full_name}" class="student-avatar">` :
                                        `<div class="student-avatar">${avatar}</div>`
                                    }
                                    <div class="student-info">
                                        <div class="student-name">${student.full_name}</div>
                                        <div class="student-id">ID: ${student.admission_number || student.user_id}</div>
                                        ${student.roll_number ? `<div class="student-roll">Roll: ${student.roll_number}</div>` : ''}
                                    </div>
                                </div>
                                <div class="student-card-body">
                                    <div class="attendance-summary">
                                        <div class="attendance-stat">
                                            <div class="stat-value value-present">${presentPercent}%</div>
                                            <div class="stat-label">Present</div>
                                        </div>
                                        <div class="attendance-stat">
                                            <div class="stat-value value-absent">${absentPercent}%</div>
                                            <div class="stat-label">Absent</div>
                                        </div>
                                    </div>
                                    <div class="student-card-actions">
                                        <button class="card-btn view-btn" title="View Attendance">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </button>
                                        <button class="card-btn edit-btn" title="Edit Attendance">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            Edit
                                        </button>
                                    </div>
                                </div>
                            `;
                            
                            // Add event listeners
                            card.querySelector('.view-btn').addEventListener('click', function(e) {
                                e.stopPropagation();
                                const studentId = card.dataset.studentId;
                                
                                // Use our new function to show detailed attendance view
                                showStudentAttendanceDetails(studentId, student.full_name);
                            });
                            
                            card.querySelector('.edit-btn').addEventListener('click', function(e) {
                                e.stopPropagation();
                                const studentId = card.dataset.studentId;
                                
                                // Show prompt for selecting date
                                const today = new Date().toISOString().split('T')[0];
                                const date = prompt('Enter date to edit attendance (YYYY-MM-DD):', today);
                                
                                if (date) {
                                    // Validate date format
                                    if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                                        alert('Invalid date format. Please use YYYY-MM-DD format.');
                                        return;
                                    }
                                    
                                    // Open individual edit modal for this student
                                    openIndividualEditModal(studentId, student.full_name, date);
                                }
                            });
                            
                            // Add click event to view student attendance
                            card.addEventListener('click', function() {
                                const studentId = this.dataset.studentId;
                                // Use our new function to show detailed attendance view
                                showStudentAttendanceDetails(studentId, student.full_name);
                            });
                            
                            studentCardsContainer.appendChild(card);
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching students:', error);
                        studentCardsContainer.innerHTML = '<div class="card-loading">Error loading students. Please try again.</div>';
                        
                        if (showDebug) {
                            const errorDetails = document.createElement('div');
                            errorDetails.id = 'errorDetails';
                            errorDetails.style.color = 'red';
                            errorDetails.style.marginTop = '10px';
                            errorDetails.style.padding = '10px';
                            errorDetails.style.border = '1px solid #ffcccc';
                            errorDetails.style.background = '#fff8f8';
                            errorDetails.textContent = error.toString();
                            studentCardsContainer.appendChild(errorDetails);
                        }
                    });
            }
            
            // Function to open individual edit modal for a student
            function openIndividualEditModal(studentId, studentName, date) {
                // Set up a similar flow to the bulk edit but focused on one student
                // Create modal if it doesn't exist
                if (!document.getElementById('individualEditModal')) {
                    const modal = document.createElement('div');
                    modal.id = 'individualEditModal';
                    modal.className = 'modal';
                    modal.innerHTML = `
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2>Edit Student Attendance</h2>
                                <span class="close-modal">&times;</span>
                            </div>
                            <div class="modal-body">
                                <div class="loading-spinner">Loading...</div>
                                <form id="individualEditForm">
                                    <input type="hidden" id="editStudentId" name="student_id">
                                    <input type="hidden" id="editDate" name="date">
                                    
                                    <div class="form-row">
                                        <label for="editStatus">Attendance Status:</label>
                                        <select id="editStatus" name="status" class="filter-select">
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="holiday">On Leave/Holiday</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-row">
                                        <label for="editRemark">Remarks:</label>
                                        <textarea id="editRemark" name="remark" class="filter-input" rows="3" placeholder="Optional remarks"></textarea>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="button" class="filter-btn close-modal">Cancel</button>
                                        <button type="submit" class="filter-btn filter-btn-apply">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(modal);
                    
                    // Add modal styles if not already added
                    if (!document.getElementById('modalStyles')) {
                        const style = document.createElement('style');
                        style.id = 'modalStyles';
                        style.textContent += `
                            .form-row {
                                margin-bottom: 1rem;
                            }
                            .form-row label {
                                display: block;
                                margin-bottom: 0.5rem;
                                font-weight: 500;
                            }
                            .filter-input {
                                width: 100%;
                                padding: 0.5rem;
                                border: 1px solid #e2e8f0;
                                border-radius: 4px;
                                font-size: 0.875rem;
                            }
                        `;
                        document.head.appendChild(style);
                    }
                    
                    // Close modal events
                    document.querySelectorAll('#individualEditModal .close-modal').forEach(element => {
                        element.addEventListener('click', function() {
                            document.getElementById('individualEditModal').style.display = 'none';
                        });
                    });
                    
                    // Handle form submission
                    document.getElementById('individualEditForm').addEventListener('submit', function(e) {
                        e.preventDefault();
                        saveIndividualAttendance();
                    });
                }
                
                // Update modal with student info
                const modal = document.getElementById('individualEditModal');
                const editStudentId = document.getElementById('editStudentId');
                const editDate = document.getElementById('editDate');
                const modalTitle = modal.querySelector('.modal-header h2');
                
                editStudentId.value = studentId;
                editDate.value = date;
                modalTitle.textContent = `Edit Attendance: ${studentName} - ${date}`;
                
                // Show loading
                modal.querySelector('.loading-spinner').style.display = 'block';
                document.getElementById('individualEditForm').style.display = 'none';
                
                // Show the modal
                modal.style.display = 'block';
                
                // Fetch current attendance status
                fetch(`attendance_actions.php?get_students_for_attendance=1&class_id=${currentClassId}&section_id=${currentSectionId}&date=${date}`)
                    .then(res => res.json())
                    .then(data => {
                        // Find this student
                        const student = data.find(s => s.user_id == studentId);
                        
                        // Hide loading and show form
                        modal.querySelector('.loading-spinner').style.display = 'none';
                        document.getElementById('individualEditForm').style.display = 'block';
                        
                        if (student) {
                            // Set current values
                            document.getElementById('editStatus').value = student.status || 'present';
                            document.getElementById('editRemark').value = student.remark || '';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching student attendance:', error);
                        // Hide loading and show form anyway
                        modal.querySelector('.loading-spinner').style.display = 'none';
                        document.getElementById('individualEditForm').style.display = 'block';
                    });
            }
            
            // Function to save individual attendance changes
            function saveIndividualAttendance() {
                const form = document.getElementById('individualEditForm');
                const studentId = document.getElementById('editStudentId').value;
                const date = document.getElementById('editDate').value;
                const status = document.getElementById('editStatus').value;
                const remark = document.getElementById('editRemark').value;
                
                // Build form data
                const formData = new FormData();
                formData.append('save_individual_attendance', '1');
                formData.append('class_id', currentClassId);
                formData.append('section_id', currentSectionId);
                formData.append('date', date);
                formData.append('student_id', studentId);
                formData.append('status', status);
                formData.append('remark', remark);
                
                // Show loading
                const modal = document.getElementById('individualEditModal');
                modal.querySelector('.loading-spinner').style.display = 'block';
                form.style.display = 'none';
                
                // Submit the data
                fetch('attendance_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        alert('Error: ' + data.error);
                    } else {
                        // Close the modal
                        modal.style.display = 'none';
                        
                        // Success message
                        alert('Attendance saved successfully!');
                        
                        // Refresh student data
                        loadStudentsInSection(currentSectionId);
                    }
                })
                .catch(error => {
                    console.error('Error saving attendance:', error);
                    alert('An error occurred while saving attendance. Please try again.');
                    
                    // Hide loading and show form
                    modal.querySelector('.loading-spinner').style.display = 'none';
                    form.style.display = 'block';
                });
            }
            
            // Load section dropdown based on selected class
            function loadSections(classId, sectionsData) {
                if (!classId) {
                    const sectionFilter = document.getElementById('sectionFilter');
                    sectionFilter.innerHTML = '<option value="">All Sections</option>';
                    return;
                }
                
                // If sections data was provided, use it directly
                if (sectionsData) {
                    populateSectionDropdown(sectionsData);
                    return;
                }
                
                // Otherwise fetch from server
                fetch(`attendance_actions.php?get_sections=1&class_id=${classId}`)
                    .then(res => res.json())
                    .then(sections => {
                        populateSectionDropdown(sections);
                    });
                
                function populateSectionDropdown(sections) {
                    const sectionFilter = document.getElementById('sectionFilter');
                    sectionFilter.innerHTML = '<option value="">All Sections</option>';
                    
                    sections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.id;
                        option.textContent = `Section ${section.name}`;
                        sectionFilter.appendChild(option);
                    });
                    
                    // Event listener for section selection change
                    sectionFilter.addEventListener('change', function() {
                        currentSectionId = this.value;
                        
                        // Update title
                        updateClassTitle();
                        
                        // Show attendance view if a section is selected
                        if (currentClassId) {
                            showAttendanceView();
                            
                            // Update attendance view
                            fetchAttendanceStats();
                            fetchCalendarData();
                        }
                    });
                }
            }
            
            // Update the class title in the attendance view
            function updateClassTitle() {
                const classFilter = document.getElementById('classFilter');
                const sectionFilter = document.getElementById('sectionFilter');
                const calendarTitle = document.querySelector('.calendar-title');
                
                let title = 'All Classes Attendance';
                
                if (currentClassId) {
                    const className = classFilter.options[classFilter.selectedIndex].text;
                    title = className;
                    
                    if (currentSectionId) {
                        const sectionName = sectionFilter.options[sectionFilter.selectedIndex].text;
                        title = `${className} ${sectionName} Attendance`;
                    }
                }
                
                calendarTitle.textContent = title;
            }
            
            // Fetch attendance statistics based on selected filters
            function fetchAttendanceStats() {
                // Build query string with filters
                let url = 'attendance_actions.php?stats=1';
                
                if (currentClassId) {
                    url += `&class_id=${currentClassId}`;
                }
                
                if (currentSectionId) {
                    url += `&section_id=${currentSectionId}`;
                }
                
                // Add month filter for current view
                const startDate = `${currentYear}-${currentMonth.toString().padStart(2, '0')}-01`;
                const endDate = new Date(currentYear, currentMonth, 0).toISOString().split('T')[0]; // Last day of month
                url += `&start_date=${startDate}&end_date=${endDate}`;
                
                fetch(url)
                    .then(res => res.json())
                    .then(stats => {
                        // Update overview cards
                        updateOverviewCards(stats);
                        
                        // Update summary section
                        updateSummarySection(stats);
                    })
                    .catch(error => {
                        console.error('Error fetching statistics:', error);
                    });
            }
            
            // Update overview cards with attendance statistics
            function updateOverviewCards(stats) {
                // Present card
                document.querySelector('.present-card .overview-value').textContent = stats.percentages.present || 0 + '%';
                const presentTrend = document.querySelector('.present-card .overview-percentage span');
                presentTrend.textContent = `${Math.abs(stats.trends.present || 0)}% from last month`;
                presentTrend.className = stats.trends.present >= 0 ? 'percentage-increase' : 'percentage-decrease';
                document.querySelector('.present-card .overview-percentage svg').className = stats.trends.present >= 0 ? 'percentage-increase' : 'percentage-decrease';
                
                // Absent card
                document.querySelector('.absent-card .overview-value').textContent = stats.percentages.absent || 0 + '%';
                const absentTrend = document.querySelector('.absent-card .overview-percentage span');
                absentTrend.textContent = `${Math.abs(stats.trends.absent || 0)}% from last month`;
                absentTrend.className = stats.trends.absent <= 0 ? 'percentage-decrease' : 'percentage-increase';
                document.querySelector('.absent-card .overview-percentage svg').className = stats.trends.absent <= 0 ? 'percentage-decrease' : 'percentage-increase';
                
                // Leave/Holiday card
                document.querySelector('.leave-card .overview-value').textContent = stats.percentages.holiday || 0 + '%';
                const leaveTrend = document.querySelector('.leave-card .overview-percentage span');
                leaveTrend.textContent = `${Math.abs(stats.trends.holiday || 0)}% from last month`;
                leaveTrend.className = stats.trends.holiday <= 0 ? 'percentage-decrease' : 'percentage-increase';
                document.querySelector('.leave-card .overview-percentage svg').className = stats.trends.holiday <= 0 ? 'percentage-decrease' : 'percentage-increase';
            }
            
            // Update summary section with attendance statistics
            function updateSummarySection(stats) {
                // Update month/year in title
                const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                document.querySelector('.summary-title').textContent = `${monthNames[currentMonth-1]} ${currentYear} Summary`;
                
                // Update statistics
                document.querySelector('.stat-item:nth-child(1) .stat-value').textContent = (stats.percentages.present || 0) + '%';
                document.querySelector('.stat-item:nth-child(2) .stat-value').textContent = (stats.percentages.absent || 0) + '%';
                document.querySelector('.stat-item:nth-child(3) .stat-value').textContent = (stats.percentages.holiday || 0) + '%';
                document.querySelector('.stat-item:nth-child(4) .stat-value').textContent = stats.school_days || 0;
                document.querySelector('.stat-item:nth-child(5) .stat-value').textContent = stats.student_count || 0;
            }
            
            // Fetch calendar attendance data for the current month
            function fetchCalendarData(studentId) {
                // Build query string with filters
                let url = `attendance_actions.php?calendar=1&month=${currentMonth}&year=${currentYear}`;
                
                if (currentClassId) {
                    url += `&class_id=${currentClassId}`;
                }
                
                if (currentSectionId) {
                    url += `&section_id=${currentSectionId}`;
                }
                
                if (studentId) {
                    url += `&student_id=${studentId}`;
                }
                
                fetch(url)
                    .then(res => res.json())
                    .then(data => {
                        calendarData = data.calendar_data;
                        allStudents = data.students;
                        
                        // Generate days array for the month
                        generateDaysInMonth(currentYear, currentMonth);
                        
                        // Render the attendance table
                        renderAttendanceTable();
                    })
                    .catch(error => {
                        console.error('Error fetching calendar data:', error);
                    });
            }
            
            // Generate array of days in the current month
            function generateDaysInMonth(year, month) {
                daysInMonth = [];
                const totalDays = new Date(year, month, 0).getDate();
                
                for (let day = 1; day <= totalDays; day++) {
                    const date = new Date(year, month - 1, day);
                    const dayOfWeek = date.toLocaleDateString('en-US', { weekday: 'short' });
                    const formattedDate = `${dayOfWeek}, ${month}/${day}`;
                    
                    // Only include weekdays (Monday-Friday)
                    if (date.getDay() > 0 && date.getDay() < 6) {
                        const dateStr = date.toISOString().split('T')[0]; // YYYY-MM-DD format
                        daysInMonth.push({
                            day: day,
                            dayOfWeek: dayOfWeek,
                            formattedDate: formattedDate,
                            dateStr: dateStr
                        });
                    }
                }
            }
            
            // Render the attendance table with current data
            function renderAttendanceTable() {
                const thead = document.querySelector('.attendance-table thead tr');
                const tbody = document.getElementById('attendanceTableBody');
                
                // Clear existing table rows
                        tbody.innerHTML = '';
                
                // Rebuild the header row with dynamic date columns
                thead.innerHTML = '<th>Student</th>';
                
                // Add date headers (limit to 10 days to fit on screen)
                const displayDays = daysInMonth.slice(0, 10);
                
                displayDays.forEach(day => {
                    thead.innerHTML += `
                        <th class="date-header">
                            <div class="date-day">${day.dayOfWeek}</div>
                            <div class="date-full">${day.day}/${currentMonth}</div>
                        </th>
                    `;
                });
                
                // Add final columns
                thead.innerHTML += '<th>Present %</th><th>Actions</th>';
                
                // No students to display
                if (Object.keys(allStudents).length === 0) {
                    tbody.innerHTML = '<tr><td colspan="' + (displayDays.length + 3) + '">No students found for the selected class/section.</td></tr>';
                    return;
                }
                
                // Add a row for each student
                Object.entries(allStudents).forEach(([studentId, studentName]) => {
                            const tr = document.createElement('tr');
                    
                    // Student name cell
                    tr.innerHTML = `
                        <td>
                            <div class="student-name">${studentName}</div>
                            <div class="student-id">ID: ${studentId}</div>
                        </td>
                    `;
                    
                    // Calculate present percentage
                    let presentCount = 0;
                    let totalDays = 0;
                    
                    // Add cells for each day
                    displayDays.forEach(day => {
                        let status = '';
                        let statusClass = '';
                        
                        // Check if we have attendance data for this student on this day
                        if (calendarData[day.dateStr] && calendarData[day.dateStr][studentId]) {
                            status = calendarData[day.dateStr][studentId].status;
                            totalDays++;
                            
                            if (status === 'present') {
                                presentCount++;
                                statusClass = 'status-present';
                            } else if (status === 'absent') {
                                statusClass = 'status-absent';
                            } else if (status === 'holiday') {
                                statusClass = 'status-leave';
                            }
                        }
                        
                        // Create the day cell with status
                        tr.innerHTML += `
                            <td class="status-cell ${statusClass}">
                                ${status ? status.charAt(0).toUpperCase() : '-'}
                            </td>
                        `;
                    });
                    
                    // Calculate present percentage
                    const presentPercentage = totalDays > 0 ? Math.round((presentCount / totalDays) * 100) : 0;
                    
                    // Add present percentage cell
                    tr.innerHTML += `
                        <td class="percentage-cell">${presentPercentage}%</td>
                    `;
                    
                    // Add actions cell
                    tr.innerHTML += `
                        <td class="actions-cell">
                            <button class="action-btn" title="View Details">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <button class="action-btn" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                        </td>
                    `;
                    
                            tbody.appendChild(tr);
                        });
                
                // Add event listeners to action buttons
                addActionButtonListeners();
                
                // Update pagination info
                updatePagination(Object.keys(allStudents).length);
            }
            
            // Add event listeners to action buttons
            function addActionButtonListeners() {
                const viewButtons = document.querySelectorAll('.action-btn[title="View Details"]');
                const editButtons = document.querySelectorAll('.action-btn[title="Edit"]');
                
                viewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const studentName = this.closest('tr').querySelector('.student-name').textContent;
                        alert(`Viewing detailed attendance records for ${studentName}`);
                    });
                });
                
                editButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const studentName = this.closest('tr').querySelector('.student-name').textContent;
                        alert(`Editing attendance records for ${studentName}`);
                        });
                    });
            }
            
            // Update pagination info
            function updatePagination(total) {
                const paginationInfo = document.querySelector('.pagination-info');
                
                if (total === 0) {
                    paginationInfo.textContent = 'No students found';
                } else {
                    paginationInfo.textContent = `Showing 1-${total} of ${total} students`;
                }
            }
            
            // Initialize month selector with proper event listeners
            function initMonthSelector() {
                const monthSelector = document.getElementById('monthSelector');
                monthSelector.innerHTML = ''; // Clear existing options
                
                // Add options for the current month and previous months
                const currentDate = new Date();
                for (let i = 0; i < 12; i++) {
                    const month = new Date(currentDate.getFullYear(), currentDate.getMonth() - i, 1);
                    const option = document.createElement('option');
                    option.value = month.getMonth() + 1;
                    option.dataset.year = month.getFullYear();
                    option.textContent = month.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                    monthSelector.appendChild(option);
                }
                
                // Set the current month as default
                monthSelector.selectedIndex = 0;
                
                // Month selector change event
                monthSelector.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    currentMonth = parseInt(selectedOption.value);
                    currentYear = parseInt(selectedOption.dataset.year);
                    
                    // Update the view with the new month
                    fetchAttendanceStats();
                    fetchCalendarData();
                });
                
                // Previous/Next month buttons
                const prevMonthBtn = document.querySelector('.calendar-btn:first-child');
                const nextMonthBtn = document.querySelector('.calendar-btn:last-child');
                
                prevMonthBtn.addEventListener('click', function() {
                    // Get current selected index
                    const currentIndex = monthSelector.selectedIndex;
                    
                    // If not at the last option, select the next one (previous month)
                    if (currentIndex < monthSelector.options.length - 1) {
                        monthSelector.selectedIndex = currentIndex + 1;
                        monthSelector.dispatchEvent(new Event('change'));
                    }
                });
                
                nextMonthBtn.addEventListener('click', function() {
                    // Get current selected index
                    const currentIndex = monthSelector.selectedIndex;
                    
                    // If not at the first option, select the previous one (next month)
                    if (currentIndex > 0) {
                        monthSelector.selectedIndex = currentIndex - 1;
                        monthSelector.dispatchEvent(new Event('change'));
                    }
                });
            }
            
            // Initialize the page
            function initPage() {
                // Load filter dropdowns and class cards
                loadClasses();
                
                // Initialize month selector
                initMonthSelector();
                
                // Set up filter form submission
                const filterApplyBtn = document.querySelector('.filter-btn-apply');
                filterApplyBtn.addEventListener('click', function() {
                    // Get selected class and section
                    currentClassId = document.getElementById('classFilter').value;
                    currentSectionId = document.getElementById('sectionFilter').value;
                    
                    // Get date range if specified
                    const startDate = document.getElementById('startDate').value;
                    const endDate = document.getElementById('endDate').value;
                    
                    // If dates are specified, use them to set month/year
                    if (startDate) {
                        const date = new Date(startDate);
                        currentMonth = date.getMonth() + 1;
                        currentYear = date.getFullYear();
                        
                        // Update month selector
                        updateMonthSelector(currentMonth, currentYear);
                    }
                    
                    // Update class title
                    updateClassTitle();
                    
                    // Show attendance view
                    if (currentClassId) {
                        showAttendanceView();
                    }
                    
                    // Update attendance data
                    fetchAttendanceStats();
                    fetchCalendarData();
                    
                    // Hide filter panel
                    document.getElementById('filterPanel').style.display = 'none';
                });
                
                // Reset filter form
                const filterResetBtn = document.querySelector('.filter-btn-reset');
                filterResetBtn.addEventListener('click', function() {
                    document.querySelector('.filter-form').reset();
                });
                
                // Set up back to class button
                document.getElementById('backToClassButton').addEventListener('click', function() {
                    backToClassSelection();
                });
                
                // Set up back to section button
                document.getElementById('backToSectionButton').addEventListener('click', function() {
                    backToSectionSelection();
                });
                
                // Hide attendance view containers initially
                document.getElementById('attendanceOverviewContainer').style.display = 'none';
                document.getElementById('calendarNavContainer').style.display = 'none';
                document.getElementById('attendanceSummaryContainer').style.display = 'none';
                document.getElementById('attendanceTableContainer').style.display = 'none';
            }
            
            // Update month selector to highlight a specific month/year
            function updateMonthSelector(month, year) {
                const monthSelector = document.getElementById('monthSelector');
                for (let i = 0; i < monthSelector.options.length; i++) {
                    const option = monthSelector.options[i];
                    if (parseInt(option.value) === month && parseInt(option.dataset.year) === year) {
                        monthSelector.selectedIndex = i;
                        break;
                    }
                }
            }
            
            // Add custom CSS for status colors
            function addCustomStyles() {
                const style = document.createElement('style');
                style.textContent = `
                    .status-present { background-color: rgba(72, 187, 120, 0.2); color: #2f855a; }
                    .status-absent { background-color: rgba(245, 101, 101, 0.2); color: #c53030; }
                    .status-leave, .status-holiday { background-color: rgba(144, 205, 244, 0.2); color: #2b6cb0; }
                    
                    .loading-message {
                        text-align: center;
                        padding: 2rem;
                        color: #718096;
                    }
                    
                    .percentage-cell {
                        font-weight: bold;
                    }

                    .student-card {
                        background: white;
                        border: 1px solid #e2e8f0;
                        border-radius: 8px;
                        overflow: hidden;
                        transition: all 0.2s ease;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
                    }
                    
                    .student-card:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    }
                    
                    .student-card-header {
                        display: flex;
                        align-items: center;
                        padding: 1rem;
                        background: #f0fff4;
                        border-bottom: 1px solid #c6f6d5;
                    }
                    
                    .student-card-header.status-good {
                        background: #f0fff4;
                        border-color: #c6f6d5;
                    }
                    
                    .student-card-header.status-warning {
                        background: #fffaf0;
                        border-color: #feebc8;
                    }
                    
                    .student-card-header.status-danger {
                        background: #fff5f5;
                        border-color: #fed7d7;
                    }
                    
                    .student-avatar {
                        width: 48px;
                        height: 48px;
                        border-radius: 50%;
                        object-fit: cover;
                        margin-right: 0.75rem;
                        border: 2px solid white;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    
                    .student-info {
                        flex: 1;
                    }
                    
                    .student-card .student-name {
                        font-size: 1rem;
                        font-weight: 600;
                        margin-bottom: 0.25rem;
                        color: #2d3748;
                    }
                    
                    .student-card .student-id,
                    .student-card .student-roll {
                        font-size: 0.75rem;
                        color: #718096;
                    }
                    
                    .student-card-body {
                        padding: 1rem;
                    }
                    
                    .attendance-summary {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 1rem;
                    }
                    
                    .attendance-stat {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                    }
                    
                    .attendance-stat .stat-value {
                        font-size: 1rem;
                        font-weight: 600;
                    }
                    
                    .attendance-stat .stat-label {
                        font-size: 0.75rem;
                        color: #718096;
                    }
                    
                    .student-card-actions {
                        display: flex;
                        justify-content: space-between;
                        gap: 0.5rem;
                    }
                    
                    .card-btn {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex: 1;
                        padding: 0.5rem;
                        border: none;
                        border-radius: 4px;
                        background: #edf2f7;
                        color: #4a5568;
                        font-size: 0.75rem;
                        cursor: pointer;
                        transition: all 0.2s ease;
                    }
                    
                    .card-btn svg {
                        margin-right: 0.25rem;
                    }
                    
                    .card-btn.view-btn:hover {
                        background: #e2e8f0;
                    }
                    
                    .card-btn.edit-btn:hover {
                        background: #bee3f8;
                        color: #2b6cb0;
                    }
                    
                    #studentSelectionContainer {
                        display: none;
                    }
                `;
                document.head.appendChild(style);
            }
            
                         // Initialize everything when DOM is loaded
             addCustomStyles();
             initPage();
             
             // Add event listener for Edit Attendance button
             document.getElementById('editAttendanceBtn').addEventListener('click', function() {
                 // Check if a class and section are selected
                 if (!currentClassId) {
                     alert('Please select a class before editing attendance.');
                     return;
                 }
                 
                 // If no specific day is selected, use today's date
                 const today = new Date().toISOString().split('T')[0];
                 
                 // Show prompt for selecting date
                 const date = prompt('Enter date to edit attendance (YYYY-MM-DD):', today);
                 
                 if (date) {
                     // Validate date format
                     if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                         alert('Invalid date format. Please use YYYY-MM-DD format.');
                         return;
                     }
                     
                     // Open bulk edit modal for the selected class/section and date
                     openBulkEditModal(currentClassId, currentSectionId, date);
                 }
             });
             
             // Function to open the bulk edit modal
             function openBulkEditModal(classId, sectionId, date) {
                 // Create modal if it doesn't exist
                 if (!document.getElementById('bulkEditModal')) {
                     const modal = document.createElement('div');
                     modal.id = 'bulkEditModal';
                     modal.className = 'modal';
                     modal.innerHTML = `
                         <div class="modal-content">
                             <div class="modal-header">
                                 <h2>Edit Attendance</h2>
                                 <span class="close-modal">&times;</span>
                             </div>
                             <div class="modal-body">
                                 <div class="loading-spinner">Loading students...</div>
                                 <form id="bulkEditForm">
                                     <table class="edit-attendance-table">
                                         <thead>
                                             <tr>
                                                 <th>Student</th>
                                                 <th>Status</th>
                                                 <th>Remarks</th>
                                             </tr>
                                         </thead>
                                         <tbody id="bulkEditTableBody">
                                             <!-- Student rows will be added here -->
                                         </tbody>
                                     </table>
                                     <div class="bulk-actions">
                                         <button type="button" id="markAllPresent" class="filter-btn filter-btn-apply">Mark All Present</button>
                                         <button type="button" id="markAllAbsent" class="filter-btn">Mark All Absent</button>
                                     </div>
                                     <div class="form-actions">
                                         <button type="button" class="filter-btn close-modal">Cancel</button>
                                         <button type="submit" class="filter-btn filter-btn-apply">Save Changes</button>
                                     </div>
                                 </form>
                             </div>
                         </div>
                     `;
                     document.body.appendChild(modal);
                     
                     // Add modal styles
                     const style = document.createElement('style');
                     style.textContent = `
                         .modal {
                             display: none;
                             position: fixed;
                             z-index: 9999;
                             left: 0;
                             top: 0;
                             width: 100%;
                             height: 100%;
                             background-color: rgba(0,0,0,0.5);
                         }
                         .modal-content {
                             background-color: white;
                             margin: 5% auto;
                             padding: 0;
                             width: 80%;
                             max-width: 800px;
                             border-radius: 8px;
                             box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                         }
                         .modal-header {
                             display: flex;
                             justify-content: space-between;
                             align-items: center;
                             padding: 1rem 1.5rem;
                             border-bottom: 1px solid #e2e8f0;
                         }
                         .modal-body {
                             padding: 1.5rem;
                             max-height: 70vh;
                             overflow-y: auto;
                         }
                         .close-modal {
                             color: #718096;
                             font-size: 1.5rem;
                             font-weight: bold;
                             cursor: pointer;
                         }
                         .close-modal:hover {
                             color: #000;
                         }
                         .edit-attendance-table {
                             width: 100%;
                             border-collapse: collapse;
                             margin-bottom: 1.5rem;
                         }
                         .edit-attendance-table th,
                         .edit-attendance-table td {
                             padding: 0.75rem;
                             border: 1px solid #e2e8f0;
                         }
                         .edit-attendance-table th {
                             background-color: #f7fafc;
                             text-align: left;
                         }
                         .form-actions {
                             display: flex;
                             justify-content: flex-end;
                             gap: 0.75rem;
                             margin-top: 1rem;
                         }
                         .bulk-actions {
                             display: flex;
                             gap: 0.75rem;
                             margin-bottom: 1rem;
                         }
                         .loading-spinner {
                             text-align: center;
                             padding: 2rem;
                             color: #718096;
                         }
                     `;
                     document.head.appendChild(style);
                     
                     // Close modal events
                     document.querySelectorAll('.close-modal').forEach(element => {
                         element.addEventListener('click', function() {
                             document.getElementById('bulkEditModal').style.display = 'none';
                         });
                     });
                     
                     // Handle form submission
                     document.getElementById('bulkEditForm').addEventListener('submit', function(e) {
                         e.preventDefault();
                         saveAttendanceChanges();
                     });
                     
                     // Handle bulk action buttons
                     document.getElementById('markAllPresent').addEventListener('click', function() {
                         setAllAttendanceStatus('present');
                     });
                     
                     document.getElementById('markAllAbsent').addEventListener('click', function() {
                         setAllAttendanceStatus('absent');
                     });
                 }
                 
                 // Store current values in the modal
                 const modal = document.getElementById('bulkEditModal');
                 modal.dataset.classId = classId;
                 modal.dataset.sectionId = sectionId || '';
                 modal.dataset.date = date;
                 
                 // Update modal title
                 const modalTitle = modal.querySelector('.modal-header h2');
                 const className = document.getElementById('classFilter').options[document.getElementById('classFilter').selectedIndex].text;
                 let title = `Edit Attendance: ${className}`;
                 
                 if (sectionId) {
                     const sectionName = document.getElementById('sectionFilter').options[document.getElementById('sectionFilter').selectedIndex].text;
                     title += ` ${sectionName}`;
                 }
                 
                 title += ` - ${date}`;
                 modalTitle.textContent = title;
                 
                 // Show loading spinner
                 modal.querySelector('.loading-spinner').style.display = 'block';
                 document.getElementById('bulkEditTableBody').innerHTML = '';
                 
                 // Show the modal
                 modal.style.display = 'block';
                 
                 // Fetch students for this class/section
                 fetchStudentsForAttendance(classId, sectionId, date);
             }
             
             // Function to fetch students for attendance editing
             function fetchStudentsForAttendance(classId, sectionId, date) {
                 // Build query string
                 let url = `attendance_actions.php?get_students_for_attendance=1&class_id=${classId}&date=${date}`;
                 
                 if (sectionId) {
                     url += `&section_id=${sectionId}`;
                 }
                 
                 fetch(url)
                     .then(res => res.json())
                     .then(data => {
                         // Hide loading spinner
                         document.querySelector('.loading-spinner').style.display = 'none';
                         
                         // Populate table with students
                         const tableBody = document.getElementById('bulkEditTableBody');
                         tableBody.innerHTML = '';
                         
                         data.forEach(student => {
                             const tr = document.createElement('tr');
                             
                             // Student info cell
                             const studentCell = document.createElement('td');
                             studentCell.innerHTML = `
                                 <div class="student-name">${student.full_name}</div>
                                 <div class="student-id">ID: ${student.user_id}</div>
                             `;
                             
                             // Status select cell
                             const statusCell = document.createElement('td');
                             statusCell.innerHTML = `
                                 <select name="statuses[]" class="status-select">
                                     <option value="present" ${student.status === 'present' ? 'selected' : ''}>Present</option>
                                     <option value="absent" ${student.status === 'absent' ? 'selected' : ''}>Absent</option>
                                     <option value="holiday" ${student.status === 'holiday' ? 'selected' : ''}>Holiday/Leave</option>
                                 </select>
                             `;
                             
                             // Remarks cell
                             const remarksCell = document.createElement('td');
                             remarksCell.innerHTML = `
                                 <input type="text" name="remarks[]" class="filter-input" value="${student.remark || ''}" placeholder="Optional remarks">
                             `;
                             
                             tr.appendChild(studentCell);
                             tr.appendChild(statusCell);
                             tr.appendChild(remarksCell);
                             tableBody.appendChild(tr);
                         });
                     })
                     .catch(error => {
                         console.error('Error fetching students:', error);
                         // Hide loading spinner and show error
                         document.querySelector('.loading-spinner').style.display = 'none';
                         document.getElementById('bulkEditTableBody').innerHTML = '<tr><td colspan="3">Error loading students. Please try again.</td></tr>';
                     });
             }
             
             // Function to set all attendance statuses to a given value
             function setAllAttendanceStatus(status) {
                 const statusSelects = document.querySelectorAll('.status-select');
                 statusSelects.forEach(select => {
                     select.value = status;
                 });
             }
             
             // Function to save attendance changes
             function saveAttendanceChanges() {
                 const modal = document.getElementById('bulkEditModal');
                 const classId = modal.dataset.classId;
                 const sectionId = modal.dataset.sectionId;
                 const date = modal.dataset.date;
                 
                 // Get form data
                 const form = document.getElementById('bulkEditForm');
                 const formData = new FormData(form);
                 
                 // Add class, section, and date
                 formData.append('class_id', classId);
                 formData.append('section_id', sectionId);
                 formData.append('date', date);
                 formData.append('save_attendance', '1');
                 
                 // Show loading spinner
                 form.style.opacity = '0.5';
                 form.style.pointerEvents = 'none';
                 
                 // Send data to server
                 fetch('attendance_actions.php', {
                     method: 'POST',
                     body: formData
                 })
                 .then(res => res.json())
                 .then(data => {
                     if (data.success) {
                         alert('Attendance saved successfully!');
                         modal.style.display = 'none';
                         
                         // Refresh attendance data
                         fetchAttendanceStats();
                         fetchCalendarData();
                     } else {
                         alert('Error saving attendance: ' + data.message);
                     }
                     
                     // Reset form state
                     form.style.opacity = '1';
                     form.style.pointerEvents = 'auto';
                 })
                 .catch(error => {
                     console.error('Error saving attendance:', error);
                     alert('Error saving attendance. Please try again.');
                     
                     // Reset form state
                     form.style.opacity = '1';
                     form.style.pointerEvents = 'auto';
                 });
             }

            // Show students in the new container after selecting a section
            function loadStudentsForSection(sectionId, sectionName, currentClassId, className) {
                // Hide the section selection
                document.getElementById('sectionSelectionContainer').style.display = 'none';
                
                // Update the displayed selection path
                document.getElementById('studentSelectionTitle').textContent = `Students in ${className} - ${sectionName}`;
                
                // Show the students container
                document.getElementById('studentsContainer').style.display = 'block';
                
                // Clear any existing students
                const studentsGrid = document.getElementById('studentsGrid');
                studentsGrid.innerHTML = '<div class="card-loading">Loading students...</div>';
                
                // Fetch students for this section
                fetch(`attendance_actions.php?get_students_by_section=1&section_id=${sectionId}&class_id=${currentClassId}`)
                    .then(res => res.json())
                    .then(students => {
                        // Clear container
                        studentsGrid.innerHTML = '';
                        
                        if (!Array.isArray(students) || students.length === 0) {
                            studentsGrid.innerHTML = '<div class="card-loading">No students found in this section</div>';
                            return;
                        }
                        
                        // Create a card for each student
                        students.forEach(student => {
                            // Create student card element
                            const card = document.createElement('div');
                            card.className = 'student-card';
                            card.dataset.studentId = student.user_id;
                            
                            // Calculate attendance summary
                            const presentPercent = student.attendance?.percentages?.present || 0;
                            const absentPercent = student.attendance?.percentages?.absent || 0;
                            
                            // Determine status color
                            let statusClass = 'status-good';
                            if (presentPercent < 75) {
                                statusClass = 'status-warning';
                            }
                            if (presentPercent < 60) {
                                statusClass = 'status-danger';
                            }
                            
                            // Generate avatar (image or initials)
                            const nameParts = student.full_name.trim().split(' ');
                            const avatar = nameParts.length >= 2 ? (nameParts[0][0] + nameParts[1][0]).toUpperCase() : nameParts[0] ? nameParts[0][0].toUpperCase() : 'S';
                            
                            // Add student info
                            card.innerHTML = `
                                <div class="student-card-header ${statusClass}">
                                    ${student.profile_image ? 
                                        `<img src="${student.profile_image}" alt="${student.full_name}" class="student-avatar">` :
                                        `<div class="student-avatar">${avatar}</div>`
                                    }
                                    <div class="student-info">
                                        <div class="student-name">${student.full_name}</div>
                                        <div class="student-id">ID: ${student.admission_number || student.user_id}</div>
                                        ${student.roll_number ? `<div class="student-roll">Roll: ${student.roll_number}</div>` : ''}
                                    </div>
                                </div>
                                <div class="student-card-body">
                                    <div class="attendance-summary">
                                        <div class="attendance-stat">
                                            <div class="stat-value value-present">${presentPercent}%</div>
                                            <div class="stat-label">Present</div>
                                        </div>
                                        <div class="attendance-stat">
                                            <div class="stat-value value-absent">${absentPercent}%</div>
                                            <div class="stat-label">Absent</div>
                                        </div>
                                    </div>
                                    <div class="student-card-actions">
                                        <button class="card-btn view-btn" title="View Attendance">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View
                                        </button>
                                        <button class="card-btn edit-btn" title="Edit Attendance">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            Edit
                                        </button>
                                    </div>
                                </div>
                            `;
                            
                            // Add event listeners
                            card.querySelector('.view-btn').addEventListener('click', function(e) {
                                e.stopPropagation();
                                const studentId = card.dataset.studentId;
                                showStudentAttendanceDetails(studentId, student.full_name);
                            });
                            
                            card.querySelector('.edit-btn').addEventListener('click', function(e) {
                                e.stopPropagation();
                                const studentId = card.dataset.studentId;
                                
                                // Show prompt for selecting date
                                const today = new Date().toISOString().split('T')[0];
                                const date = prompt('Enter date to edit attendance (YYYY-MM-DD):', today);
                                
                                if (date) {
                                    // Validate date format
                                    if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
                                        alert('Invalid date format. Please use YYYY-MM-DD format.');
                                        return;
                                    }
                                    
                                    // Open individual edit modal for this student
                                    openIndividualEditModal(studentId, student.full_name, date);
                                }
                            });
                            
                            studentsGrid.appendChild(card);
                        });
                    })
                    .catch(error => {
                        console.error('Error loading students:', error);
                        studentsGrid.innerHTML = '<div class="card-loading error">Error loading students. Please try again.</div>';
                    });
            }

            // Function to show student attendance details
            function showStudentAttendanceDetails(studentId, studentName) {
                // Hide students container
                document.getElementById('studentsContainer').style.display = 'none';
                
                // Update the title
                document.getElementById('studentAttendanceDetailsTitle').textContent = `Attendance Details for ${studentName}`;
                
                // Show details container
                const detailsContainer = document.getElementById('studentAttendanceDetailsContainer');
                detailsContainer.style.display = 'block';
                detailsContainer.dataset.studentId = studentId;
                detailsContainer.dataset.studentName = studentName;
                
                // Set default date range (last 30 days)
                const today = new Date();
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(today.getDate() - 30);
                
                document.getElementById('attendanceEndDate').value = today.toISOString().split('T')[0];
                document.getElementById('attendanceStartDate').value = thirtyDaysAgo.toISOString().split('T')[0];
                
                // Fetch attendance data
                fetchStudentAttendanceDates(studentId);
                
                // Add event listener to back button
                document.getElementById('backToStudentsButton').onclick = function() {
                    document.getElementById('studentAttendanceDetailsContainer').style.display = 'none';
                    document.getElementById('studentsContainer').style.display = 'block';
                };
                
                // Add event listener to date filter button
                document.getElementById('applyDateFilterButton').onclick = function() {
                    fetchStudentAttendanceDates(studentId);
                };
            }
            
            // Function to fetch student attendance dates
            function fetchStudentAttendanceDates(studentId) {
                const startDate = document.getElementById('attendanceStartDate').value;
                const endDate = document.getElementById('attendanceEndDate').value;
                
                // Show loading state
                document.getElementById('attendanceRecordsTableBody').innerHTML = `
                    <tr>
                        <td colspan="4" class="loading-cell">Loading attendance records...</td>
                    </tr>
                `;
                
                // Reset counters
                document.getElementById('presentDaysCount').textContent = '0';
                document.getElementById('absentDaysCount').textContent = '0';
                document.getElementById('presentDaysPercent').textContent = '0%';
                document.getElementById('absentDaysPercent').textContent = '0%';
                
                // Fetch data from API
                fetch(`attendance_actions.php?get_student_attendance_dates=1&student_id=${studentId}&start_date=${startDate}&end_date=${endDate}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            throw new Error(data.error || 'Failed to fetch attendance data');
                        }
                        
                        const records = data.attendance_dates;
                        const tableBody = document.getElementById('attendanceRecordsTableBody');
                        
                        // Clear table
                        tableBody.innerHTML = '';
                        
                        // Count status types
                        let presentCount = 0;
                        let absentCount = 0;
                        
                        if (records.length === 0) {
                            tableBody.innerHTML = `
                                <tr>
                                    <td colspan="4" class="empty-cell">No attendance records found for this period</td>
                                </tr>
                            `;
                        } else {
                            // Add each record to table
                            records.forEach(record => {
                                // Count by status
                                if (record.status === 'present') presentCount++;
                                else if (record.status === 'absent') absentCount++;
                                
                                const row = document.createElement('tr');
                                row.className = `status-${record.status}`;
                                
                                // Format date for display
                                const recordDate = new Date(record.date);
                                const formattedDate = recordDate.toLocaleDateString('en-US', {
                                    weekday: 'short',
                                    year: 'numeric',
                                    month: 'short',
                                    day: 'numeric'
                                });
                                
                                row.innerHTML = `
                                    <td>${formattedDate}</td>
                                    <td class="status-cell">
                                        <span class="status-badge status-${record.status}">
                                            ${record.status.charAt(0).toUpperCase() + record.status.slice(1)}
                                        </span>
                                    </td>
                                    <td>${record.remark || '-'}</td>
                                    <td class="actions-cell">
                                        <button class="edit-record-btn" data-date="${record.date}" title="Edit this record">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                    </td>
                                `;
                                
                                // Add event listener to edit button
                                row.querySelector('.edit-record-btn').addEventListener('click', function() {
                                    const date = this.dataset.date;
                                    const studentId = document.getElementById('studentAttendanceDetailsContainer').dataset.studentId;
                                    const studentName = document.getElementById('studentAttendanceDetailsContainer').dataset.studentName;
                                    
                                    openIndividualEditModal(studentId, studentName, date);
                                });
                                
                                tableBody.appendChild(row);
                            });
                        }
                        
                        // Calculate totals
                        const totalDays = presentCount + absentCount;
                        
                        // Update counters
                        document.getElementById('presentDaysCount').textContent = presentCount;
                        document.getElementById('absentDaysCount').textContent = absentCount;
                        
                        // Update percentages
                        if (totalDays > 0) {
                            document.getElementById('presentDaysPercent').textContent = 
                                `${Math.round((presentCount / totalDays) * 100)}%`;
                            document.getElementById('absentDaysPercent').textContent = 
                                `${Math.round((absentCount / totalDays) * 100)}%`;
                        } else {
                            document.getElementById('presentDaysPercent').textContent = '0%';
                            document.getElementById('absentDaysPercent').textContent = '0%';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching attendance dates:', error);
                        document.getElementById('attendanceRecordsTableBody').innerHTML = `
                            <tr>
                                <td colspan="4" class="error-cell">Error loading attendance records: ${error.message}</td>
                            </tr>
                        `;
                    });
            }
            
            // Function to open modal for editing an individual student's attendance
            function openIndividualEditModal(studentId, studentName, date) {
                // Create modal element
                const modal = document.createElement('div');
                modal.className = 'attendance-edit-modal';
                
                // Create modal content
                modal.innerHTML = `
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Edit Attendance</h3>
                            <button class="close-btn">&times;</button>
                        </div>
                        <div class="modal-body">
                            <div class="student-info">
                                <p><strong>Student:</strong> ${studentName}</p>
                                <p><strong>Date:</strong> ${date}</p>
                            </div>
                            <div class="form-group">
                                <label>Attendance Status:</label>
                                <select id="editAttendanceStatus" class="form-control">
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="holiday">Holiday</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Remarks:</label>
                                <textarea id="editAttendanceRemarks" class="form-control" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="cancelEditBtn" class="btn btn-secondary">Cancel</button>
                            <button id="saveEditBtn" class="btn btn-primary">Save Changes</button>
                        </div>
                    </div>
                `;
                
                // Add modal to document
                document.body.appendChild(modal);
                
                // Handle close button
                modal.querySelector('.close-btn').addEventListener('click', function() {
                    document.body.removeChild(modal);
                });
                
                // Handle cancel button
                modal.querySelector('#cancelEditBtn').addEventListener('click', function() {
                    document.body.removeChild(modal);
                });
                
                // Handle save button
                modal.querySelector('#saveEditBtn').addEventListener('click', function() {
                    const status = document.getElementById('editAttendanceStatus').value;
                    const remark = document.getElementById('editAttendanceRemarks').value;
                    
                    // Create form data
                    const formData = new FormData();
                    formData.append('save_individual_attendance', '1');
                    formData.append('student_id', studentId);
                    formData.append('date', date);
                    formData.append('status', status);
                    formData.append('remark', remark);
                    
                    // Get class and section IDs from URL parameters
                    const urlParams = new URLSearchParams(window.location.search);
                    formData.append('class_id', urlParams.get('class_id') || '3');
                    formData.append('section_id', urlParams.get('section_id') || '3');
                    
                    // Submit form
                    fetch('attendance_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Close modal
                            document.body.removeChild(modal);
                            
                            // If we're in the details view, refresh the data
                            if (document.getElementById('studentAttendanceDetailsContainer').style.display !== 'none') {
                                fetchStudentAttendanceDates(studentId);
                            }
                            
                            alert('Attendance updated successfully');
                        } else {
                            alert('Error updating attendance: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        console.error('Error saving attendance:', error);
                        alert('Error saving attendance. Please try again.');
                    });
                });
                
                // Check if there's an existing record and pre-fill the form
                fetch(`attendance_actions.php?get_student_attendance_dates=1&student_id=${studentId}&start_date=${date}&end_date=${date}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.attendance_dates.length > 0) {
                            const record = data.attendance_dates[0];
                            document.getElementById('editAttendanceStatus').value = record.status;
                            document.getElementById('editAttendanceRemarks').value = record.remark || '';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching existing record:', error);
                    });
            }

            // Modify the existing section click handler to use our new function
            document.addEventListener('click', function(e) {
                if (e.target.closest('.section-card')) {
                    const card = e.target.closest('.section-card');
                    const sectionId = card.dataset.sectionId;
                    const sectionName = card.querySelector('.section-name').textContent;
                    const currentClassId = document.querySelector('#sectionSelectionContainer').dataset.classId;
                    const className = document.getElementById('sectionSelectionTitle').textContent.replace('Sections for ', '');
                    
                    // Load students using our new function
                    loadStudentsForSection(sectionId, sectionName, currentClassId, className);
                }
            });

            // Add styles for the new components
            const styleElement = document.createElement('style');
            styleElement.textContent = `
                .students-container {
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    margin-bottom: 20px;
                    padding: 20px;
                }
                
                .section-title {
                    color: #333;
                    font-size: 1.25rem;
                    margin-bottom: 15px;
                }
                
                .attendance-details-container {
                    background: #fff;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    margin-bottom: 20px;
                    padding: 20px;
                }
                
                .details-header {
                    display: flex;
                    align-items: center;
                    margin-bottom: 20px;
                }
                
                .details-title {
                    margin: 0 0 0 15px;
                    font-size: 1.25rem;
                    color: #333;
                }
                
                .date-filter {
                    display: flex;
                    align-items: center;
                    margin-bottom: 20px;
                    gap: 10px;
                }
                
                .date-input {
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                }
                
                .attendance-details-content {
                    display: flex;
                    flex-direction: column;
                    gap: 20px;
                }
                
                .attendance-status-summary {
                    display: flex;
                    gap: 15px;
                    margin-bottom: 20px;
                }
                
                .status-card {
                    flex: 1;
                    padding: 15px;
                    border-radius: 8px;
                    text-align: center;
                    color: white;
                }
                
                .status-card h4 {
                    margin: 0 0 10px 0;
                    font-size: 1.1rem;
                }
                
                .status-count {
                    font-size: 2rem;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                
                .status-percent {
                    font-size: 1.1rem;
                }
                
                .present-card {
                    background-color: #4caf50;
                }
                
                .absent-card {
                    background-color: #f44336;
                }
                
                
                .attendance-table {
                    width: 100%;
                    border-collapse: collapse;
                }
                
                .attendance-table th,
                .attendance-table td {
                    padding: 12px 15px;
                    text-align: left;
                    border-bottom: 1px solid #eee;
                }
                
                .attendance-table th {
                    background-color: #f8f9fa;
                    font-weight: 600;
                }
                
                .status-badge {
                    display: inline-block;
                    padding: 5px 10px;
                    border-radius: 15px;
                    font-size: 0.85rem;
                    color: white;
                }
                
                .status-badge.status-present {
                    background-color: #4caf50;
                }
                
                .status-badge.status-absent {
                    background-color: #f44336;
                }
                
                .status-badge.status-holiday {
                    background-color: #2196f3;
                }
                
                .actions-cell {
                    text-align: center;
                }
                
                .edit-record-btn {
                    background: none;
                    border: none;
                    color: #007bff;
                    cursor: pointer;
                    padding: 5px;
                }
                
                .edit-record-btn:hover {
                    color: #0056b3;
                }
                
                .loading-cell, .empty-cell, .error-cell {
                    text-align: center;
                    padding: 20px !important;
                }
                
                .error-cell {
                    color: #f44336;
                }
                
                .attendance-edit-modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background-color: rgba(0, 0, 0, 0.5);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    z-index: 1000;
                }
                
                .modal-content {
                    background-color: white;
                    border-radius: 8px;
                    width: 500px;
                    max-width: 90%;
                    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
                }
                
                .modal-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 15px 20px;
                    border-bottom: 1px solid #eee;
                }
                
                .modal-header h3 {
                    margin: 0;
                    font-size: 1.25rem;
                }
                
                .close-btn {
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    cursor: pointer;
                    color: #999;
                }
                
                .modal-body {
                    padding: 20px;
                }
                
                .student-info {
                    margin-bottom: 20px;
                }
                
                .student-info p {
                    margin: 5px 0;
                }
                
                .form-group {
                    margin-bottom: 15px;
                }
                
                .form-group label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: 500;
                }
                
                .form-control {
                    width: 100%;
                    padding: 8px 12px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                }
                
                .modal-footer {
                    padding: 15px 20px;
                    border-top: 1px solid #eee;
                    display: flex;
                    justify-content: flex-end;
                    gap: 10px;
                }
                
                .btn {
                    padding: 8px 15px;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
                
                .btn-primary {
                    background-color: #007bff;
                    color: white;
                }
                
                .btn-secondary {
                    background-color: #6c757d;
                    color: white;
                }
                
                tr.status-present {
                    background-color: rgba(76, 175, 80, 0.1);
                }
                
                tr.status-absent {
                    background-color: rgba(244, 67, 54, 0.1);
                }
            `;
            document.head.appendChild(styleElement);
            
        });
    </script>
</body>
</html>