<?php 
// Include sidebar for authentication and navigation
include 'sidebar.php'; 

// Include database connection
include 'con.php';

// Get current date for default value
$today = date('Y-m-d');

// Get teacher ID from session
$teacher_user_id = $_SESSION['user_id'] ?? 0;

// Fetch classes assigned to this teacher (where they are class teacher or teach subjects)
$classQuery = "SELECT DISTINCT c.id, c.name, s.id AS section_id, s.name AS section_name 
               FROM classes c 
               JOIN sections s ON c.id = s.class_id 
               LEFT JOIN teacher_subjects ts ON ts.teacher_user_id = ? 
               LEFT JOIN class_subjects cs ON cs.subject_id = ts.subject_id AND cs.class_id = c.id
               WHERE s.class_teacher_user_id = ? OR cs.class_id IS NOT NULL
               ORDER BY c.name, s.name";

$stmt = $conn->prepare($classQuery);
$stmt->bind_param("ii", $teacher_user_id, $teacher_user_id);
$stmt->execute();
$classResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Attendance Management</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/attendance.css">
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
        <h1 class="header-title">Attendance Management</h1>
        <span class="header-subtitle">Track and manage student attendance records</span>
    </header>

    <main class="dashboard-content">
        <!-- Quick Stats Section -->
        <div class="stats-grid">
            <div class="stat-card" id="todayAttendanceCard">
                <div class="stat-title">Today's Attendance</div>
                <div class="stat-value">--%</div>
                <div class="stat-trend">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <span class="trend-text">Loading...</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar progress-excellent" style="width: 0%"></div>
                </div>
            </div>
            <div class="stat-card" id="weeklyAttendanceCard">
                <div class="stat-title">Weekly Average</div>
                <div class="stat-value">--%</div>
                <div class="stat-trend">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd" />
                    </svg>
                    <span class="trend-text">Loading...</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar progress-good" style="width: 0%"></div>
                </div>
            </div>
            <div class="stat-card" id="monthlyAttendanceCard">
                <div class="stat-title">Monthly Average</div>
                <div class="stat-value">--%</div>
                <div class="stat-trend">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M12 13a1 1 0 100 2h5a1 1 0 001-1V9a1 1 0 10-2 0v2.586l-4.293-4.293a1 1 0 00-1.414 0L8 9.586 3.707 5.293a1 1 0 00-1.414 1.414l5 5a1 1 0 001.414 0L11 9.414 14.586 13H12z" clip-rule="evenodd" />
                    </svg>
                    <span class="trend-text">Loading...</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar progress-good" style="width: 0%"></div>
                </div>
            </div>
            <div class="stat-card" id="absentStudentsCard">
                <div class="stat-title">Absent Students Today</div>
                <div class="stat-value">--</div>
                <div class="stat-trend">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    <span class="trend-text">Loading...</span>
                </div>
                <div class="progress-bar-container">
                    <div class="progress-bar progress-needs-improvement" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Manage Attendance</h2>
            </div>
            <div class="card-body">
                <div class="tabs">
                    <div class="tab active" data-tab="history">Attendance History</div>
                    <div class="tab" data-tab="reports">Reports</div>
                </div>
                
                <div class="tab-content active" id="history-content">
                    <div class="two-col" style="margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="historyClassSelect" class="form-label">Select Class</label>
                            <select id="historyClassSelect" class="form-select">
                                <option value="">All Classes</option>
                                <?php 
                                // Reset result pointer to beginning
                                if ($classResult) {
                                    $classResult->data_seek(0);
                                    while ($row = $classResult->fetch_assoc()) {
                                        echo "<option value='" . $row['id'] . "-" . $row['section_id'] . "'>" .
                                            htmlspecialchars($row['name'] . " " . $row['section_name']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="historyMonth" class="form-label">Select Month</label>
                            <select id="historyMonth" class="form-select">
                                <?php
                                $current_month = date('m');
                                $current_year = date('Y');
                                
                                // Show current month and 5 previous months
                                for ($i = 0; $i <= 5; $i++) {
                                    $month_num = $current_month - $i;
                                    $year = $current_year;
                                    
                                    if ($month_num <= 0) {
                                        $month_num += 12;
                                        $year--;
                                    }
                                    
                                    $month_name = date('F', mktime(0, 0, 0, $month_num, 1, $year));
                                    $value = date('Y-m', mktime(0, 0, 0, $month_num, 1, $year));
                                    $selected = ($i == 0) ? 'selected' : '';
                                    
                                    echo "<option value='$value' $selected>$month_name $year</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group text-right" style="margin-bottom: 1.5rem;">
                        <button id="historyFilterBtn" class="btn btn-primary">Apply Filter</button>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="attendance-table">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th>Present</th>
                                    <th>Absent</th>
                                    <th>Percentage</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="historyTableBody">
                                <!-- Will be populated dynamically -->
                                <tr>
                                    <td colspan="7" class="no-data">Select a class to view attendance history</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-content" id="reports-content">
                    <div class="two-col" style="margin-bottom: 1.5rem;">
                        <div class="form-group">
                            <label for="reportClassSelect" class="form-label">Select Class</label>
                            <select id="reportClassSelect" class="form-select">
                                <option value="">Select a class</option>
                                <?php 
                                // Reset result pointer to beginning
                                if ($classResult) {
                                    $classResult->data_seek(0);
                                    while ($row = $classResult->fetch_assoc()) {
                                        echo "<option value='" . $row['id'] . "-" . $row['section_id'] . "'>" .
                                            htmlspecialchars($row['name'] . " " . $row['section_name']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reportPeriod" class="form-label">Select Period</label>
                            <select id="reportPeriod" class="form-select">
                                <option value="week">Last 7 Days</option>
                                <option value="month" selected>This Month</option>
                                <option value="term">This Term</option>
                            </select>
                        </div>
                        <div class="form-group text-right" style="align-self: flex-end; display: flex; gap: 0.5rem;">
                            <button id="reportFilterBtn" class="btn btn-primary">Apply Filter</button>
                            <button id="downloadCsvBtn" class="btn btn-primary" style="display:none;">Generate CSV Report</button>
                        </div>
                    </div>
                    
                    <div id="reportContent" style="display: none;">
                        <!-- Will be populated dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Global variables
    const historyClassSelect = document.getElementById('historyClassSelect');
    const historyMonth = document.getElementById('historyMonth');
    const historyTableBody = document.getElementById('historyTableBody');
    const historyFilterBtn = document.getElementById('historyFilterBtn');
    
    const reportClassSelect = document.getElementById('reportClassSelect');
    const reportPeriod = document.getElementById('reportPeriod');
    const reportContent = document.getElementById('reportContent');
    const reportFilterBtn = document.getElementById('reportFilterBtn');
    
        // Tab switching
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
                
            // Add active class to current tab and content
                this.classList.add('active');
            document.getElementById(`${tabId}-content`).classList.add('active');
            
            // No longer automatically load data when switching tabs
            // User needs to click the filter button instead
        });
    });
                
    // Load dashboard statistics
    loadDashboardStats();
    
    // Event listeners for history filter button
    historyFilterBtn.addEventListener('click', function() {
    if (historyClassSelect.value) {
        loadAttendanceHistory(historyClassSelect.value, historyMonth.value);
        } else {
            historyTableBody.innerHTML = '<tr><td colspan="7" class="no-data">Select a class to view attendance history</td></tr>';
        }
    });
    
    // Event listeners for reports filter button
    reportFilterBtn.addEventListener('click', function() {
        if (reportClassSelect.value) {
            loadAttendanceReport(reportClassSelect.value, reportPeriod.value);
        } else {
            reportContent.style.display = 'none';
        }
    });
    
    // Functions
    function loadDashboardStats() {
        console.log('Loading dashboard statistics...');
        
        // Get the dashboard statistics elements
        const statCards = {
            today: document.getElementById('todayAttendanceCard'),
            weekly: document.getElementById('weeklyAttendanceCard'),
            monthly: document.getElementById('monthlyAttendanceCard'),
            absent: document.getElementById('absentStudentsCard')
        };
        
        // Make sure all elements exist
        if (!statCards.today || !statCards.weekly || !statCards.monthly || !statCards.absent) {
            console.error('One or more stat cards not found in the DOM');
            return;
        }
        
        // Set default/loading states
        for (const key in statCards) {
            const card = statCards[key];
            if (card) {
                card.querySelector('.stat-value').textContent = 'Loading...';
                if (card.querySelector('.trend-text')) {
                    card.querySelector('.trend-text').textContent = 'Loading...';
                }
            }
        }
        
        // Fetch dashboard statistics from the server
        fetch('attendance_action.php?action=get_dashboard_stats')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.text();
            })
            .then(text => {
                // Debug: Log the raw response
                console.log('Raw API response:', text);
                
                    try {
                        // Try to parse the text as JSON
                        return JSON.parse(text);
                    } catch (e) {
                        console.error("Invalid JSON response:", text);
                    throw new Error("Server returned invalid JSON");
                    }
            })
            .then(data => {
                console.log('Dashboard stats data:', data);
                
                // Check if data has the expected structure
                if (!data || typeof data !== 'object') {
                    throw new Error('Invalid data structure received from server');
                }
                
                // Update stats for each card using safe defaults if data is missing
                
                // Today's attendance
                if (data.today && statCards.today) {
                    const percentage = data.today.percentage || '0';
                    const trend = data.today.trend || 'No trend data';
                    
                    statCards.today.querySelector('.stat-value').textContent = percentage + '%';
                    if (statCards.today.querySelector('.trend-text')) {
                        statCards.today.querySelector('.trend-text').textContent = trend;
                    }
                    
                    // Update progress bar
                    const progressBar = statCards.today.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.style.width = percentage + '%';
                    }
                    
                    // Update trend icon
                    updateTrendIcon(statCards.today, data.today.trend_direction || 0);
                }
                
                // Weekly average
                if (data.weekly && statCards.weekly) {
                    const percentage = data.weekly.percentage || '0';
                    const trend = data.weekly.trend || 'No trend data';
                    
                    statCards.weekly.querySelector('.stat-value').textContent = percentage + '%';
                    if (statCards.weekly.querySelector('.trend-text')) {
                        statCards.weekly.querySelector('.trend-text').textContent = trend;
                    }
                    
                    // Update progress bar
                    const progressBar = statCards.weekly.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.style.width = percentage + '%';
                    }
                    
                    // Update trend icon
                    updateTrendIcon(statCards.weekly, data.weekly.trend_direction || 0);
                }
                
                // Monthly average
                if (data.monthly && statCards.monthly) {
                    const percentage = data.monthly.percentage || '0';
                    const trend = data.monthly.trend || 'No trend data';
                    
                    statCards.monthly.querySelector('.stat-value').textContent = percentage + '%';
                    if (statCards.monthly.querySelector('.trend-text')) {
                        statCards.monthly.querySelector('.trend-text').textContent = trend;
                }
                
                    // Update progress bar
                    const progressBar = statCards.monthly.querySelector('.progress-bar');
                    if (progressBar) {
                        progressBar.style.width = percentage + '%';
                    }
                    
                    // Update trend icon
                    updateTrendIcon(statCards.monthly, data.monthly.trend_direction || 0);
                }
                
                // Absent students
                if (data.absent && statCards.absent) {
                    const count = data.absent.count || '0';
                    const noReason = data.absent.no_reason || '0';
                    
                    statCards.absent.querySelector('.stat-value').textContent = count;
                    if (statCards.absent.querySelector('.trend-text')) {
                        statCards.absent.querySelector('.trend-text').textContent = noReason + ' without reason';
                    }
                    
                    // Update progress bar - using percentage for absent if available
                    const progressBar = statCards.absent.querySelector('.progress-bar');
                    if (progressBar && data.absent.percentage) {
                        progressBar.style.width = data.absent.percentage + '%';
                    }
                    
                    // Absent is always a negative trend
                    updateTrendIcon(statCards.absent, -1);
                }
            })
            .catch(error => {
                console.error('Error loading dashboard statistics:', error);
                
                // Show error in the stats cards
                for (const key in statCards) {
                    const card = statCards[key];
                    if (card) {
                        card.querySelector('.stat-value').textContent = 'Error';
                        if (card.querySelector('.trend-text')) {
                            card.querySelector('.trend-text').textContent = 'Data error';
                        }
                    }
                }
            });
    }
    
    function updateTrendIcon(card, trendDirection) {
        if (!card) return;
        
        const trendSvg = card.querySelector('.stat-trend svg');
        if (trendSvg) {
            if (trendDirection > 0) {
                // Positive trend (up arrow)
                trendSvg.style.transform = 'rotate(0deg)';
                trendSvg.style.color = '#4CAF50'; // Green
            } else if (trendDirection < 0) {
                // Negative trend (down arrow)
                trendSvg.style.transform = 'rotate(180deg)';
                trendSvg.style.color = '#F44336'; // Red
            } else {
                // Neutral trend
                trendSvg.style.transform = 'rotate(0deg)';
                trendSvg.style.color = '#757575'; // Gray
            }
        }
    }
    
    // Functions for history and reports tab
    
    function loadAttendanceHistory(classData, month) {
        const [classId, sectionId] = classData.split('-');
        
        // Show loading state
        historyTableBody.innerHTML = '<tr><td colspan="7" class="no-data">Loading attendance history...</td></tr>';
        
        // Fetch attendance history for the selected class and month
        fetch(`attendance_action.php?action=get_attendance_history&class_id=${classId}&section_id=${sectionId}&month=${month}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                // First get the response as text
                return response.text().then(text => {
                    try {
                        // Try to parse the text as JSON
                        return JSON.parse(text);
                    } catch (e) {
                        // If parsing fails, throw a better error
                        console.error("Invalid JSON response:", text);
                        throw new Error("Server returned invalid JSON. Please try again.");
                    }
                });
            })
            .then(data => {
                if (data.success) {
                    // Clear previous data
                    historyTableBody.innerHTML = '';
                    
                    if (data.history && data.history.length > 0) {
                        data.history.forEach(record => {
                            const row = document.createElement('tr');
            
                            // Format date display
                            const recordDate = new Date(record.date);
                            const formattedDate = recordDate.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
                            
                            // Calculate today, yesterday
                            const today = new Date();
                            today.setHours(0, 0, 0, 0);
                            const yesterday = new Date(today);
                            yesterday.setDate(yesterday.getDate() - 1);
            
                            // Check if date is today/yesterday
                            let dateLabel = '';
                            if (recordDate.toDateString() === today.toDateString()) {
                                dateLabel = 'Today';
                            } else if (recordDate.toDateString() === yesterday.toDateString()) {
                                dateLabel = 'Yesterday';
                            }
                            
                            // Calculate attendance percentage with fallback values for null/undefined
                            const present = parseInt(record.present) || 0;
                            const absent = parseInt(record.absent) || 0;
                            const total = present + absent;
                            const percentage = total > 0 ? (present / total * 100).toFixed(1) : '0.0';
                            
                            // Use safe default values for missing data
                            const className = record.class_name || 'Unknown';
                            const sectionName = record.section_name || '';
                            
                            row.innerHTML = `
                                <td>${className} ${sectionName}</td>
                                <td>
                                    <div>${formattedDate}</div>
                                    ${dateLabel ? `<div class="attendance-date">${dateLabel}</div>` : ''}
                                </td>
                                <td>${present}</td>
                                <td>${absent}</td>
                                <td>${percentage}%</td>
                                <td>
                                    <a href="attendance_details.php?class_id=${record.class_id}&section_id=${record.section_id}&date=${record.date}" class="btn btn-secondary btn-sm">View</a>
                                </td>
                            `;
                            
                            historyTableBody.appendChild(row);
                        });
                    } else {
                        historyTableBody.innerHTML = '<tr><td colspan="7" class="no-data">No attendance records found for this month</td></tr>';
                    }
                } else {
                    console.error('API Error:', data.message);
                    historyTableBody.innerHTML = '<tr><td colspan="7" class="no-data">Error: ' + (data.message || 'Failed to load attendance history') + '</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error loading attendance history:', error);
                historyTableBody.innerHTML = '<tr><td colspan="7" class="no-data">Error loading attendance history. Please check browser console for details.</td></tr>';
            });
    }
    
    let lastReportData = null; // Store last loaded report for CSV
    function loadAttendanceReport(classData, period) {
        const [classId, sectionId] = classData.split('-');
        
        // Show loading state
        reportContent.innerHTML = '<p class="no-data">Loading attendance report...</p>';
        reportContent.style.display = 'block';
        
        // Fetch attendance report for the selected class and period
        fetch(`attendance_action.php?action=get_attendance_report&class_id=${classId}&section_id=${sectionId}&period=${period}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                
                // First get the response as text
                return response.text().then(text => {
                    try {
                        // Try to parse the text as JSON
                        return JSON.parse(text);
                    } catch (e) {
                        // If parsing fails, throw a better error
                        console.error("Invalid JSON response:", text);
                        throw new Error("Server returned invalid JSON. Please try again.");
                    }
                });
            })
            .then(data => {
                if (data.success && data.report) {
                    lastReportData = data; // Store for CSV
                    // Display the report container
                    reportContent.style.display = 'block';
                    
                    // Safe default values
                    const className = data.class_name || 'Selected Class';
                    const sectionName = data.section_name || '';
                    const periodLabel = data.period_label || period;
                    const totalDays = data.report.total_days || 0;
                    const avgPercentage = data.report.average_percentage || 0;
                    const totalStudents = data.report.total_students || 0;
                    
                    // Generate report HTML
                    let reportHtml = `
                        <div class="report-summary">
                            <h3>Attendance Summary for ${className} ${sectionName}</h3>
                            <p>Period: ${periodLabel}</p>
                            
                            <div class="summary-cards">
                                <div class="summary-card">
                                    <div class="summary-value">${totalDays}</div>
                                    <div class="summary-label">Total Days</div>
                                </div>
                                <div class="summary-card">
                                    <div class="summary-value">${avgPercentage}%</div>
                                    <div class="summary-label">Average Attendance</div>
                                </div>
                                <div class="summary-card">
                                    <div class="summary-value">${totalStudents}</div>
                                    <div class="summary-label">Total Students</div>
                                </div>
                            </div>
                            
                            <h3>Student-wise Attendance</h3>`;
                            
                    if (data.report.students && data.report.students.length > 0) {
                        reportHtml += `
                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Roll Number</th>
                                        <th>Present Days</th>
                                        <th>Absent Days</th>
                                        <th>Attendance %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${data.report.students.map(student => `
                                        <tr>
                                            <td>${student.full_name || 'Unknown'}</td>
                                            <td>${student.roll_number || 'N/A'}</td>
                                            <td>${student.present_days || 0}</td>
                                            <td>${student.absent_days || 0}</td>
                                            <td>${student.attendance_percentage || 0}%</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>`;
                    } else {
                        reportHtml += `<p class="no-data">No student attendance data available for this period.</p>`;
                    }
                    
                    reportHtml += `</div>`;
                    reportContent.innerHTML = reportHtml;
                    document.getElementById('downloadCsvBtn').style.display = 'inline-block';
                } else {
                    console.error('API Error:', data);
                    reportContent.innerHTML = `<p class="no-data">${data.message || 'Error loading attendance report. Please try again.'}</p>`;
                    reportContent.style.display = 'block';
                    document.getElementById('downloadCsvBtn').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error loading attendance report:', error);
                reportContent.innerHTML = `<p class="no-data">Error loading attendance report: ${error.message}. Please check browser console for details.</p>`;
                reportContent.style.display = 'block';
                document.getElementById('downloadCsvBtn').style.display = 'none';
            });
    }
    // CSV download logic
    document.getElementById('downloadCsvBtn').addEventListener('click', function() {
        if (!lastReportData || !lastReportData.report || !lastReportData.report.students) {
            alert('No report data to export.');
            return;
        }
        const students = lastReportData.report.students;
        let csv = '';
        // Header
        csv += 'Student Name,Roll Number,Present Days,Absent Days,Attendance %\n';
        students.forEach(student => {
            csv += `"${student.full_name || ''}","${student.roll_number || ''}",${student.present_days || 0},${student.absent_days || 0},${student.attendance_percentage || 0}\n`;
        });
        // Download
        const blob = new Blob([csv], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${(lastReportData.class_name || 'Class')}_${(lastReportData.section_name || '')}_${(lastReportData.period_label || 'Report').replace(/\s+/g, '_')}.csv`;
        document.body.appendChild(a);
        a.click();
        setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 100);
    });
});
</script>

</body>
</html>