<?php
require_once 'con.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

$student_id = $_SESSION['user_id'];

// Get student details
$conn = getDbConnection();
$sql = "SELECT s.*, c.name AS class_name, sec.name AS section_name
        FROM students s
        JOIN classes c ON s.class_id = c.id
        JOIN sections sec ON s.section_id = sec.id
        WHERE s.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    header('Location: ../login.php');
    exit;
}

$stmt->close();
$conn->close();
?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Homework</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/homework.css">
    <style>
        /* Subject section styles */
        .subject-section {
            margin-bottom: 2rem;
        }
        
        .subject-header {
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        /* Tab styles */
        .tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }

        .tab {
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            font-weight: 500;
            color: #6b7280;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }

        .tab.active {
            color: #4f46e5;
            border-bottom-color: #4f46e5;
        }

        .tab:hover:not(.active) {
            color: #4b5563;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
        
        /* Make homework cards clickable */
        .homework-card {
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .homework-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        /* Status styles */
        .status-pending {
            background-color: #e0e7ff;
            color: #3730a3;
        }
        
        .status-submitted {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-graded {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .status-overdue {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        /* Loading and error message styles */
        .loading, .error, .no-data {
            padding: 2rem;
            text-align: center;
            color: #4b5563;
        }
        
        .error {
            color: #b91c1c;
        }

        /* Ensure dashboard container behaves like attendance page */
        .dashboard-container {
            margin-left: 280px;
            transition: all 0.3s ease;
            position: relative;
            height: 100vh;
            overflow-y: auto;
            /* Keep some padding if needed, or rely on child elements */
            padding: 2rem;
        }
    </style>
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
            <h1 class="header-title">My Homework</h1>
            <p class="header-subtitle">View and submit your assignments</p>
        </header>

        <main class="dashboard-content">
            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-title">Pending</div>
                    <div class="stat-value" id="pendingCount">-</div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 40%"></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Submitted</div>
                    <div class="stat-value" id="submittedCount">-</div>
                    <div class="stat-trend">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                        Total submissions
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Graded</div>
                    <div class="stat-value" id="gradedCount">-</div>
                    <div class="stat-trend">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                        Graded work
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Average Grade</div>
                    <div class="stat-value" id="averageGrade">-</div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: 80%"></div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Assignments</h2>
                </div>
                <div class="card-body">
                    <div class="tabs">
                        <div class="tab active" data-tab="all">All Assignments</div>
                        <div class="tab" data-tab="pending">Pending</div>
                        <div class="tab" data-tab="submitted">Submitted</div>
                        <div class="tab" data-tab="graded">Graded</div>
                    </div>
                    
                    <!-- Search & Filters -->
                    <div class="search-container">
                        <input type="text" placeholder="Search assignments..." class="search-input" id="searchInput">
                        <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    
                    <div class="filters-container" style="display: flex; gap: 1rem; align-items: flex-end;">
                        <div class="filter">
                            <select id="filterSubject" class="form-select">
                                <option value="">All Subjects</option>
                            </select>
                        </div>
                        <div class="filter">
                            <select id="filterTime" class="form-select">
                                <option value="">All Time</option>
                                <option value="today">Due Today</option>
                                <option value="week">Due This Week</option>
                                <option value="month">Due This Month</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                        <button id="applyFiltersBtn" class="btn btn-primary" style="min-width: 120px; margin-left: 1rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="vertical-align: middle;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707l-7 7V21a1 1 0 01-1.447.894l-4-2A1 1 0 017 19v-5.293l-7-7A1 1 0 010 6V4z" />
                            </svg>
                            Filter
                        </button>
                    </div>
                    
                    <div class="tab-content active" id="all-content">
                        <div class="card-grid">
                            <!-- Dynamic homework cards will be rendered here by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="tab-content" id="pending-content">
                        <div class="card-grid">
                            <!-- Pending assignments will be rendered here -->
                        </div>
                    </div>
                    
                    <div class="tab-content" id="submitted-content">
                        <div class="card-grid">
                            <!-- Submitted assignments will be rendered here -->
                        </div>
                    </div>
                    
                    <div class="tab-content" id="graded-content">
                        <div class="card-grid">
                            <!-- Graded assignments will be rendered here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Global variables
        let allHomework = [];
        let currentTab = 'all';

        // Load homework list on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadHomeworkList();
            loadSubjects();
            setupEventListeners();
        });

        // Setup event listeners
        function setupEventListeners() {
            // Tab switching
            document.querySelectorAll('.tab').forEach(tab => {
                tab.addEventListener('click', function() {
                    switchTab(this.dataset.tab);
                });
            });

            // Filter inputs
            document.getElementById('filterSubject').addEventListener('change', applyFilters);
            document.getElementById('filterTime').addEventListener('change', applyFilters);
            document.getElementById('searchInput').addEventListener('input', function() {
                const searchTimeout = setTimeout(() => {
                    applyFilters();
                }, 300);
            });

            // Apply filters button
            document.getElementById('applyFiltersBtn').addEventListener('click', applyFilters);
        }

        // Switch tab
        function switchTab(tabName) {
            currentTab = tabName;
            
            // Update active tab
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.dataset.tab === tabName) {
                    tab.classList.add('active');
                }
            });
            
            // Update tab content visibility
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            const tabContent = document.getElementById(`${tabName}-content`);
            if (tabContent) {
                tabContent.classList.add('active');
            }
            
            // Re-render homework for this tab
            renderHomeworkCards();
        }

        // Load subjects for filter
        function loadSubjects() {
            fetch('homework_actions.php?action=get_subjects')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const select = document.getElementById('filterSubject');
                        data.subjects.forEach(subject => {
                            const option = document.createElement('option');
                            option.value = subject.id;
                            option.textContent = subject.name;
                            select.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading subjects:', error));
        }

        // Load homework list
        function loadHomeworkList() {
            const homeworkGrid = document.querySelector(`#${currentTab}-content .card-grid`);
            
            // Show loading state
            if (homeworkGrid) {
                homeworkGrid.innerHTML = '<div class="loading">Loading assignments...</div>';
            }

            // Add debug information to the request
            fetch('homework_actions.php?action=list_homework&debug=1')
                .then(response => {
                    console.log('Response status:', response.status);
                    
                    // Check if the response is ok
                    if (!response.ok) {
                        throw new Error(`Server responded with status: ${response.status}`);
                    }
                    
                    // Try to parse the JSON response
                    return response.text().then(text => {
                        console.log('Raw response:', text);
                        try {
                            return JSON.parse(text);
                        } catch (error) {
                            console.error('JSON parse error:', error);
                            throw new Error('Invalid JSON response: ' + error.message);
                        }
                    });
                })
                .then(data => {
                    if (data.status === 'success') {
                        // Store all homework for filtering
                        allHomework = data.homework;
                        
                        // Update statistics
                        updateStats(data.stats);
                        
                        // Render the homework cards
                        renderHomeworkCards();
                    } else {
                        const errorMsg = data.message || 'Error loading assignments';
                        console.error('Server error:', errorMsg);
                        document.querySelectorAll('.card-grid').forEach(grid => {
                            grid.innerHTML = `<div class="error">${errorMsg}</div>`;
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading homework:', error);
                    document.querySelectorAll('.card-grid').forEach(grid => {
                        grid.innerHTML = `<div class="error">Error loading assignments: ${error.message}</div>`;
                    });
                });
        }

        // Render homework cards based on current tab and filters
        function renderHomeworkCards() {
            // Get filtered homework
            const filteredHomework = filterHomework();
            
            // Group by subject
            const homeworkBySubject = groupHomeworkBySubject(filteredHomework);
            
            // Get the grid for the current tab
            const grid = document.querySelector(`#${currentTab}-content .card-grid`);
            if (!grid) return;
            
            // Clear the grid
            grid.innerHTML = '';
            
            // If no homework, show message
            if (Object.keys(homeworkBySubject).length === 0) {
                grid.innerHTML = '<div class="no-data">No assignments found</div>';
                return;
            }
            
            // For each subject, create a section with assignments
            Object.keys(homeworkBySubject).forEach(subject => {
                const subjectHomework = homeworkBySubject[subject];
                
                // Create a subject section
                const subjectSection = document.createElement('div');
                subjectSection.className = 'subject-section';
                
                // Add subject header
                const subjectHeader = document.createElement('h3');
                subjectHeader.className = 'subject-header';
                subjectHeader.textContent = subject;
                subjectSection.appendChild(subjectHeader);
                
                // Add homework cards for this subject
                subjectHomework.forEach(homework => {
                    const card = createHomeworkCard(homework);
                    subjectSection.appendChild(card);
                });
                
                // Add the subject section to the grid
                grid.appendChild(subjectSection);
            });
        }
        
        // Create a homework card element
        function createHomeworkCard(homework) {
            const card = document.createElement('div');
            card.className = `homework-card ${getHomeworkStatus(homework)}`;
            card.dataset.id = homework.id;
            card.onclick = function() {
                window.location.href = `view_homework.php?id=${homework.id}`;
            };
            
            // Determine status class and text
            const statusClass = getStatusClass(homework);
            const statusText = getStatusText(homework);
            
            // Create card content
            card.innerHTML = `
                <div class="homework-card-header">
                    <h3 class="homework-card-title">${escapeHtml(homework.title)}</h3>
                    <div class="homework-card-class">${escapeHtml(homework.subject_name)}</div>
                    <div class="homework-status ${statusClass}">${statusText}</div>
                </div>
                <div class="homework-card-body">
                    <div class="homework-card-description">${escapeHtml(truncateText(homework.description, 100))}</div>
                    <div class="homework-card-details">
                        <div class="detail-label">Teacher:</div>
                        <div class="detail-value">${escapeHtml(homework.teacher_name)}</div>
                        <div class="detail-label">Due Date:</div>
                        <div class="detail-value">${formatDate(homework.due_date)}</div>
                    </div>
                </div>
            `;
            
            return card;
        }
        
        // Group homework by subject
        function groupHomeworkBySubject(homeworkList) {
            const grouped = {};
            
            homeworkList.forEach(homework => {
                const subjectName = homework.subject_name;
                if (!grouped[subjectName]) {
                    grouped[subjectName] = [];
                }
                grouped[subjectName].push(homework);
            });
            
            return grouped;
        }
        
        // Filter homework based on current tab and filter settings
        function filterHomework() {
            if (!allHomework || !Array.isArray(allHomework)) return [];
            
            const now = new Date();
            const subjectFilter = document.getElementById('filterSubject').value;
            const timeFilter = document.getElementById('filterTime').value;
            const searchQuery = document.getElementById('searchInput').value.toLowerCase();
            
            return allHomework.filter(homework => {
                // Tab filter
                const dueDate = new Date(homework.due_date);
                
                // Filter by tab
                if (currentTab === 'pending') {
                    if (homework.submission || isOverdue(homework.due_date)) {
                        return false;
                    }
                } else if (currentTab === 'submitted') {
                    if (!homework.submission || homework.submission.status === 'graded') {
                        return false;
                    }
                } else if (currentTab === 'graded') {
                    if (!homework.submission || homework.submission.status !== 'graded') {
                        return false;
                    }
                }
                
                // Subject filter
                if (subjectFilter && homework.subject_id !== subjectFilter) {
                    return false;
                }
                
                // Time filter
                if (timeFilter) {
                    if (timeFilter === 'today' && !isSameDay(dueDate, now)) {
                        return false;
                    } else if (timeFilter === 'week' && !isThisWeek(dueDate)) {
                        return false;
                    } else if (timeFilter === 'month' && !isThisMonth(dueDate)) {
                        return false;
                    } else if (timeFilter === 'overdue' && dueDate >= now) {
                        return false;
                    }
                }
                
                // Search filter
                if (searchQuery) {
                    return (
                        homework.title.toLowerCase().includes(searchQuery) ||
                        homework.description.toLowerCase().includes(searchQuery) ||
                        homework.subject_name.toLowerCase().includes(searchQuery) ||
                        homework.teacher_name.toLowerCase().includes(searchQuery)
                    );
                }
                
                return true;
            });
        }
        
        // Apply filters and re-render
        function applyFilters() {
            renderHomeworkCards();
        }

        // Update statistics
        function updateStats(stats) {
            document.getElementById('pendingCount').textContent = stats.pending;
            document.getElementById('submittedCount').textContent = stats.submitted;
            document.getElementById('gradedCount').textContent = stats.graded;
            document.getElementById('averageGrade').textContent = stats.average_grade;
            
            // Update progress bars
            // Pending progress (assuming max of all homework)
            const totalHomework = parseFloat(stats.pending) + parseFloat(stats.submitted) + parseFloat(stats.graded);
            if (totalHomework > 0) {
                const pendingPercent = (parseFloat(stats.pending) / totalHomework) * 100;
                document.querySelector('.stat-card:nth-child(1) .progress-bar').style.width = pendingPercent + '%';
                
                // Average grade progress (assuming max of 100)
                const avgGrade = parseFloat(stats.average_grade) || 0;
                document.querySelector('.stat-card:nth-child(4) .progress-bar').style.width = avgGrade + '%';
            }
        }

        // Helper functions
        function getHomeworkStatus(homework) {
            if (homework.submission) {
                return homework.submission.status === 'graded' ? 'completed' : 'submitted';
            }
            return isOverdue(homework.due_date) ? 'overdue' : 'pending';
        }
        
        function getStatusClass(homework) {
            if (homework.submission) {
                return homework.submission.status === 'graded' ? 'status-graded' : 'status-submitted';
            }
            return isOverdue(homework.due_date) ? 'status-overdue' : 'status-pending';
        }
        
        function getStatusText(homework) {
            if (homework.submission) {
                return homework.submission.status === 'graded' ? 'Graded' : 'Submitted';
            }
            return isOverdue(homework.due_date) ? 'Overdue' : 'Pending';
        }
        
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return date.toLocaleDateString('en-US', options);
        }
        
        function isOverdue(dateStr) {
            const dueDate = new Date(dateStr);
            dueDate.setHours(23, 59, 59); // End of the day
            const now = new Date();
            return dueDate < now;
        }
        
        function isSameDay(date1, date2) {
            return date1.getFullYear() === date2.getFullYear() &&
                   date1.getMonth() === date2.getMonth() &&
                   date1.getDate() === date2.getDate();
        }
        
        function isThisWeek(date) {
            const now = new Date();
            const firstDay = new Date(now);
            firstDay.setDate(now.getDate() - now.getDay()); // First day of current week (Sunday)
            firstDay.setHours(0, 0, 0, 0);
            
            const lastDay = new Date(firstDay);
            lastDay.setDate(firstDay.getDate() + 6); // Last day of current week (Saturday)
            lastDay.setHours(23, 59, 59, 999);
            
            return date >= firstDay && date <= lastDay;
        }
        
        function isThisMonth(date) {
            const now = new Date();
            return date.getMonth() === now.getMonth() &&
                   date.getFullYear() === now.getFullYear();
        }
        
        // Truncate text with ellipsis
        function truncateText(text, maxLength) {
            if (!text) return '';
            return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
        }
        
        // Escape HTML to prevent XSS
        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;')
                     .replace(/</g, '&lt;')
                     .replace(/>/g, '&gt;')
                     .replace(/"/g, '&quot;')
                     .replace(/'/g, '&#039;');
        }
    </script>
</body>
</html> 