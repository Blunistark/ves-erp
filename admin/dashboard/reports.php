<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/reports.css">
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
            <h1 class="header-title">Reports Dashboard</h1>
            <span class="header-path">Dashboard > Admin > Reports</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="reportSearch" class="search-input" placeholder="Search reports by name, category, date...">
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
                    <button class="btn btn-primary" id="generateReportBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Generate Report
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel" style="display: none;">
                <h3 class="filter-title">Filter Reports</h3>
                <form class="filter-form">
                    <div class="filter-group">
                        <label class="filter-label">Report Type</label>
                        <select class="filter-select" id="reportTypeFilter">
                            <option value="">All Types</option>
                            <option value="financial">Financial</option>
                            <option value="academic">Academic</option>
                            <option value="attendance">Attendance</option>
                            <option value="inventory">Inventory</option>
                            <option value="hr">Human Resources</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Department</label>
                        <select class="filter-select" id="departmentFilter">
                            <option value="">All Departments</option>
                            <option value="admin">Administration</option>
                            <option value="finance">Finance</option>
                            <option value="academic">Academic</option>
                            <option value="hr">HR</option>
                            <option value="it">IT</option>
                            <option value="operations">Operations</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="archived">Archived</option>
                            <option value="scheduled">Scheduled</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Period</label>
                        <select class="filter-select" id="periodFilter">
                            <option value="2024-q1">2024 - Q1</option>
                            <option value="2024-q2">2024 - Q2</option>
                            <option value="2024-q3">2024 - Q3</option>
                            <option value="2024-q4">2024 - Q4</option>
                            <option value="2023-q4">2023 - Q4</option>
                            <option value="2023-q3">2023 - Q3</option>
                            <option value="2023-q2">2023 - Q2</option>
                            <option value="2023-q1">2023 - Q1</option>
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
                        <label class="filter-label">Created By</label>
                        <select class="filter-select" id="creatorFilter">
                            <option value="">All Users</option>
                            <option value="admin">Admin Users</option>
                            <option value="manager">Managers</option>
                            <option value="staff">Staff</option>
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
                <div class="results-tab active" data-tab="overview">Reports Overview</div>
                <div class="results-tab" data-tab="recent">Recent Reports</div>
                <div class="results-tab" data-tab="scheduled">Scheduled Reports</div>
                <div class="results-tab" data-tab="archived">Archived Reports</div>
            </div>

            <!-- Report Generation Form -->
            <div class="report-form-container" id="reportForm" style="display: none;">
                <h2 class="form-title">Generate New Report</h2>
                <form id="createReportForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="reportTypeSelect">Report Type</label>
                            <select class="form-select" id="reportTypeSelect" name="reportTypeSelect" required>
<option value="">Select Report Type</option>
                                <option value="financial">Financial Report</option>
                                <option value="academic">Academic Performance Report</option>
                                <option value="attendance">Attendance Summary</option>
                                <option value="inventory">Inventory Status</option>
                                <option value="hr">HR Analytics</option>
                                <option value="custom">Custom Report</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="reportNameInput">Report Name</label>
                            <input type="text" class="form-input" id="reportNameInput" name="reportNameInput" placeholder="Enter a descriptive name" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="departmentSelect">Department</label>
                            <select class="form-select" id="departmentSelect" name="departmentSelect">
                                <option value="">Select Department</option>
                                <option value="admin">Administration</option>
                                <option value="finance">Finance</option>
                                <option value="academic">Academic</option>
                                <option value="hr">HR</option>
                                <option value="it">IT</option>
                                <option value="operations">Operations</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="periodSelect">Time Period</label>
                            <select class="form-select" id="periodSelect" name="periodSelect">
                                <option value="custom">Custom Date Range</option>
                                <option value="last_week">Last Week</option>
                                <option value="last_month">Last Month</option>
                                <option value="last_quarter">Last Quarter</option>
                                <option value="last_year">Last Year</option>
                                <option value="ytd">Year to Date</option>
                                <option value="all_time">All Time</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="formatSelect">Output Format</label>
                            <select class="form-select" id="formatSelect" name="formatSelect">
                                <option value="pdf">PDF Document</option>
                                <option value="excel">Excel Spreadsheet</option>
                                <option value="csv">CSV File</option>
                                <option value="dashboard">Dashboard View</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" id="dateRangeGroup" style="display: none;">
                            <label class="form-label">Date Range</label>
                            <div style="display: flex; gap: 0.5rem;">
                                <input type="date" class="form-input" id="startDateInput" name="startDateInput" style="flex: 1;">
                                <span style="align-self: center;">to</span>
                                <input type="date" class="form-input" id="endDateInput" name="endDateInput" style="flex: 1;">
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label" for="reportDescription">Description</label>
                            <textarea class="form-textarea" id="reportDescription" name="reportDescription" placeholder="Enter a brief description of the report purpose..."></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelReportBtn">Cancel</button>
                        <button type="button" class="btn btn-outline" id="scheduleReportBtn">Schedule Report</button>
                        <button type="submit" class="btn btn-primary">Generate Now</button>
                    </div>
                </form>
            </div>

            <!-- Overview Tab Content -->
            <div class="tab-content active" id="overview-tab">
                <div class="performance-metrics">
                    <div class="metric-card metric-a">
                        <h3 class="metric-title">Reports Generated</h3>
                        <div class="metric-value">248</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">12% from last month</span>
                        </div>
                    </div>
                    <div class="metric-card metric-b">
                        <h3 class="metric-title">Scheduled Reports</h3>
                        <div class="metric-value">36</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">5% from last month</span>
                        </div>
                    </div>
                    <div class="metric-card metric-c">
                        <h3 class="metric-title">Most Popular Report</h3>
                        <div class="metric-value">Financial</div>
                        <div class="metric-indicator">
                            <span>42% of all reports</span>
                        </div>
                    </div>
                    <div class="metric-card metric-d">
                        <h3 class="metric-title">Storage Usage</h3>
                        <div class="metric-value">4.2 GB</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-negative">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                            <span class="indicator-negative">8% from last month</span>
                        </div>
                    </div>
                </div>

                <div class="report-cards">
                    <div class="report-card">
                        <div class="report-card-header">
                            <div class="report-card-icon icon-financial">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="report-card-info">
                                <div class="report-card-name">Q1 Financial Summary</div>
                                <div class="report-card-details">Finance Department • PDF</div>
                            </div>
                        </div>
                        <p>Comprehensive financial analysis for Q1 2025, including revenue, expenses, and projected forecasts.</p>
                        <div class="report-card-footer">
                            <div class="report-update">Generated: Mar 12, 2025</div>
                            <div class="report-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">View</button>
                            </div>
                        </div>
                    </div>

                    <div class="report-card">
                        <div class="report-card-header">
                            <div class="report-card-icon icon-academic">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                                </svg>
                            </div>
                            <div class="report-card-info">
                                <div class="report-card-name">Student Performance Analysis</div>
                                <div class="report-card-details">Academic Department • Excel</div>
                            </div>
                        </div>
                        <p>Analysis of student performance across all grade levels for the winter semester with comparative data.</p>
                        <div class="report-card-footer">
                            <div class="report-update">Generated: Mar 10, 2025</div>
                            <div class="report-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">View</button>
                            </div>
                        </div>
                    </div>

                    <div class="report-card">
                        <div class="report-card-header">
                            <div class="report-card-icon icon-attendance">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="report-card-info">
                                <div class="report-card-name">Staff Attendance Summary</div>
                                <div class="report-card-details">HR Department • Dashboard</div>
                            </div>
                        </div>
                        <p>Monthly attendance report showing staff presence, absences, and overall attendance trends.</p>
                        <div class="report-card-footer">
                            <div class="report-update">Generated: Mar 8, 2025</div>
                            <div class="report-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">View</button>
                            </div>
                        </div>
                    </div>

                    <div class="report-card">
                        <div class="report-card-header">
                            <div class="report-card-icon icon-inventory">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="report-card-info">
                                <div class="report-card-name">Inventory Status Report</div>
                                <div class="report-card-details">Operations Department • CSV</div>
                            </div>
                        </div>
                        <p>Current inventory status with stock levels, reorder points, and items requiring replenishment.</p>
                        <div class="report-card-footer">
                            <div class="report-update">Generated: Mar 5, 2025</div>
                            <div class="report-actions">
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">View</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Reports Tab Content -->
            <div class="tab-content" id="recent-tab">
                <div class="results-table-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Type</th>
                                <th>Department</th>
                                <th>Format</th>
                                <th>Generated By</th>
                                <th>Date</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Q1 Financial Summary</td>
                                <td>Financial</td>
                                <td>Finance</td>
                                <td>PDF</td>
                                <td>John Doe</td>
                                <td>Mar 12, 2025</td>
                                <td>1.2 MB</td>
                                <td class="result-actions">
                                    <button class="action-btn" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Share">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
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
                                <td>Student Performance Analysis</td>
                                <td>Academic</td>
                                <td>Academic</td>
                                <td>Excel</td>
                                <td>Sarah Johnson</td>
                                <td>Mar 10, 2025</td>
                                <td>3.5 MB</td>
                                <td class="result-actions">
                                    <button class="action-btn" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Share">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
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
                                <td>Staff Attendance Summary</td>
                                <td>Attendance</td>
                                <td>HR</td>
                                <td>Dashboard</td>
                                <td>Michael Brown</td>
                                <td>Mar 8, 2025</td>
                                <td>0.8 MB</td>
                                <td class="result-actions">
                                    <button class="action-btn" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Share">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
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
                                <td>Inventory Status Report</td>
                                <td>Inventory</td>
                                <td>Operations</td>
                                <td>CSV</td>
                                <td>Jessica Miller</td>
                                <td>Mar 5, 2025</td>
                                <td>2.1 MB</td>
                                <td class="result-actions">
                                    <button class="action-btn" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Share">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
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
                                <td>IT Infrastructure Assessment</td>
                                <td>IT</td>
                                <td>IT</td>
                                <td>PDF</td>
                                <td>Robert Wilson</td>
                                <td>Mar 2, 2025</td>
                                <td>4.5 MB</td>
                                <td class="result-actions">
                                    <button class="action-btn" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Share">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
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
                        Showing 1-5 of 32 reports
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

            <!-- Scheduled Reports Tab Content -->
            <div class="tab-content" id="scheduled-tab">
                <div class="schedule-container">
                    <div class="schedule-header">
                        <h3 class="schedule-title">Scheduled Report Status</h3>
                        <div class="schedule-options">
                            <select class="filter-select">
                                <option value="all">All Frequencies</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                            </select>
                            <button class="btn btn-outline">
                                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                Notifications
                            </button>
                            <button class="btn btn-primary">
                                <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Schedule New
                            </button>
                        </div>
                    </div>
                    
                    <div class="results-table-container">
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th>Report Name</th>
                                    <th>Type</th>
                                    <th>Department</th>
                                    <th>Frequency</th>
                                    <th>Next Run</th>
                                    <th>Created By</th>
                                    <th>Recipients</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Monthly Financial Statement</td>
                                    <td>Financial</td>
                                    <td>Finance</td>
                                    <td>Monthly</td>
                                    <td>Apr 1, 2025</td>
                                    <td>John Doe</td>
                                    <td>5 recipients</td>
                                    <td class="result-actions">
                                        <button class="action-btn" title="Edit Schedule">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Run Now">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                    <td>Weekly Attendance Report</td>
                                    <td>Attendance</td>
                                    <td>HR</td>
                                    <td>Weekly</td>
                                    <td>Mar 22, 2025</td>
                                    <td>Michael Brown</td>
                                    <td>3 recipients</td>
                                    <td class="result-actions">
                                        <button class="action-btn" title="Edit Schedule">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Run Now">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                    <td>Daily Inventory Status</td>
                                    <td>Inventory</td>
                                    <td>Operations</td>
                                    <td>Daily</td>
                                    <td>Mar 16, 2025</td>
                                    <td>Jessica Miller</td>
                                    <td>7 recipients</td>
                                    <td class="result-actions">
                                        <button class="action-btn" title="Edit Schedule">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Run Now">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                                    <td>Quarterly Budget Analysis</td>
                                    <td>Financial</td>
                                    <td>Finance</td>
                                    <td>Quarterly</td>
                                    <td>Jun 30, 2025</td>
                                    <td>John Doe</td>
                                    <td>4 recipients</td>
                                    <td class="result-actions">
                                        <button class="action-btn" title="Edit Schedule">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Run Now">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Archived Reports Tab Content -->
            <div class="tab-content" id="archived-tab">
                <div class="results-table-container">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Type</th>
                                <th>Department</th>
                                <th>Format</th>
                                <th>Generated By</th>
                                <th>Date</th>
                                <th>Size</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Q4 2024 Financial Summary</td>
                                <td>Financial</td>
                                <td>Finance</td>
                                <td>PDF</td>
                                <td>John Doe</td>
                                <td>Dec 31, 2024</td>
                                <td>1.5 MB</td>
                                <td class="result-actions">
                                    <button class="action-btn" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Restore">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete Permanently">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Annual Academic Performance 2024</td>
                                <td>Academic</td>
                                <td>Academic</td>
                                <td>Excel</td>
                                <td>Sarah Johnson</td>
                                <td>Dec 20, 2024</td>
                                <td>5.2 MB</td>
                                <td class="result-actions">
                                    <button class="action-btn" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Restore">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete Permanently">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2024 Annual HR Report</td>
                                <td>HR</td>
                                <td>HR</td>
                                <td>PDF</td>
                                <td>Michael Brown</td>
                                <td>Dec 15, 2024</td>
                                <td>3.7 MB</td>
                                <td class="result-actions">
                                    <button class="action-btn" title="View">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Restore">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete Permanently">
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
                        Showing 1-3 of 24 archived reports
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
            
            // Generate Report Form Toggle
            const generateReportBtn = document.getElementById('generateReportBtn');
            const reportForm = document.getElementById('reportForm');
            const cancelReportBtn = document.getElementById('cancelReportBtn');
            
            generateReportBtn.addEventListener('click', function() {
                // Reset form
                document.getElementById('createReportForm').reset();
                
                // Show form
                reportForm.style.display = 'block';
                
                // Scroll to form
                reportForm.scrollIntoView({ behavior: 'smooth' });
            });
            
            cancelReportBtn.addEventListener('click', function() {
                reportForm.style.display = 'none';
            });
            
            // Toggle date range input based on period selection
            const periodSelect = document.getElementById('periodSelect');
            const dateRangeGroup = document.getElementById('dateRangeGroup');
            
            periodSelect.addEventListener('change', function() {
                dateRangeGroup.style.display = this.value === 'custom' ? 'block' : 'none';
            });
            
            // Form Submission
            const createReportForm = document.getElementById('createReportForm');
            
            createReportForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form values
                const reportType = document.getElementById('reportTypeSelect').value;
                const reportName = document.getElementById('reportNameInput').value;
                
                // Validate form fields
                if (!reportType || !reportName) {
                    alert('Please fill in all required fields');
                    return;
                }
                
                // Here you would typically submit the form via AJAX or redirect
                alert('Report generation initiated!');
                
                // Hide form
                reportForm.style.display = 'none';
                
                // Switch to Recent Reports tab
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                document.querySelector('.results-tab[data-tab="recent"]').classList.add('active');
                document.getElementById('recent-tab').classList.add('active');
            });
            
            // Schedule Report Button
            const scheduleReportBtn = document.getElementById('scheduleReportBtn');
            
            scheduleReportBtn.addEventListener('click', function() {
                // Get form values
                const reportType = document.getElementById('reportTypeSelect').value;
                const reportName = document.getElementById('reportNameInput').value;
                
                // Validate form fields
                if (!reportType || !reportName) {
                    alert('Please fill in all required fields before scheduling');
                    return;
                }
                
                // Here you would typically show a scheduling modal or form
                alert('Report scheduled successfully!');
                
                // Hide form
                reportForm.style.display = 'none';
                
                // Switch to Scheduled Reports tab
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                document.querySelector('.results-tab[data-tab="scheduled"]').classList.add('active');
                document.getElementById('scheduled-tab').classList.add('active');
            });
            
            // Action Buttons
            const viewButtons = document.querySelectorAll('.action-btn[title="View"]');
            const downloadButtons = document.querySelectorAll('.action-btn[title="Download"]');
            const shareButtons = document.querySelectorAll('.action-btn[title="Share"]');
            const deleteButtons = document.querySelectorAll('.action-btn[title="Delete"]');
            const restoreButtons = document.querySelectorAll('.action-btn[title="Restore"]');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const reportName = this.closest('tr').querySelector('td:first-child').textContent;
                    alert(`Viewing report: ${reportName}`);
                });
            });
            
            downloadButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const reportName = this.closest('tr').querySelector('td:first-child').textContent;
                    alert(`Downloading report: ${reportName}`);
                });
            });
            
            shareButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const reportName = this.closest('tr').querySelector('td:first-child').textContent;
                    alert(`Share options for: ${reportName}`);
                });
            });
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const reportName = this.closest('tr').querySelector('td:first-child').textContent;
                    
                    if (confirm(`Are you sure you want to delete the report: ${reportName}?`)) {
                        // Here you would send a delete request to the server
                        
                        // For demonstration purposes, hide the row
                        this.closest('tr').style.display = 'none';
                        
                        alert(`Report "${reportName}" has been deleted.`);
                    }
                });
            });
            
            restoreButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const reportName = this.closest('tr').querySelector('td:first-child').textContent;
                    
                    if (confirm(`Are you sure you want to restore the report: ${reportName}?`)) {
                        // Here you would send a restore request to the server
                        
                        // For demonstration purposes, hide the row
                        this.closest('tr').style.display = 'none';
                        
                        alert(`Report "${reportName}" has been restored.`);
                    }
                });
            });
            
            // Search functionality
            const searchInput = document.getElementById('reportSearch');
            const tableRows = document.querySelectorAll('.results-table tbody tr');
            const reportCards = document.querySelectorAll('.report-card');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                // Search in table rows
                if (tableRows.length > 0) {
                    tableRows.forEach(row => {
                        const reportName = row.querySelector('td:first-child').textContent.toLowerCase();
                        const reportType = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                        const department = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                        
                        const matchFound = 
                            reportName.includes(searchTerm) || 
                            reportType.includes(searchTerm) || 
                            department.includes(searchTerm);
                        
                        row.style.display = matchFound ? '' : 'none';
                    });
                }
                
                // Search in report cards
                if (reportCards.length > 0) {
                    reportCards.forEach(card => {
                        const reportName = card.querySelector('.report-card-name').textContent.toLowerCase();
                        const reportDetails = card.querySelector('.report-card-details').textContent.toLowerCase();
                        const reportDescription = card.querySelector('p').textContent.toLowerCase();
                        
                        const matchFound = 
                            reportName.includes(searchTerm) || 
                            reportDetails.includes(searchTerm) || 
                            reportDescription.includes(searchTerm);
                        
                        card.style.display = matchFound ? '' : 'none';
                    });
                }
            });
            
            // Pagination
            const pageButtons = document.querySelectorAll('.page-btn:not(:first-child):not(:last-child)');
            
            pageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const parentPagination = this.closest('.pagination');
                    const pageButtons = parentPagination.querySelectorAll('.page-btn:not(:first-child):not(:last-child)');
                    
                    pageButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // In a real implementation, this would load the next page of data
                    if (this.textContent !== '1') {
                        alert(`Loading page ${this.textContent} of reports...`);
                    }
                });
            });
        });
    </script>
</body>
</html>