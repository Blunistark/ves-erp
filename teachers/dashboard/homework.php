<?php
require_once 'con.php';
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../login.php');
    exit;
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Homework Manager</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/homework.css">
    <script src="js/homework-fixed.js" defer></script>
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
        <h1 class="header-title">Homework Manager</h1>
        <p class="header-subtitle">Assign, track, and review student homework</p>
    </header>

    <main class="dashboard-content">
        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-card">
                <div class="stat-title">Pending Assignments</div>
                <div class="stat-value">8</div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: 40%"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Due This Week</div>
                <div class="stat-value">5</div>
                <div class="stat-trend trend-up">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                    Up 2 from last week
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Submissions to Grade</div>
                <div class="stat-value">12</div>
                <div class="stat-trend trend-down">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    Down 3 from last week
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Graded This Month</div>
                <div class="stat-value">48</div>
                <div class="progress-container">
                    <div class="progress-bar" style="width: 80%"></div>
                </div>
            </div>
        </div>

        <!-- Main Tabs -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Manage Homework</h2>
                <div>
                    <button class="btn btn-primary" id="newAssignmentBtn">
                        <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        New Assignment
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="tabs">
                    <div class="tab active" data-tab="all">All Assignments</div>
                    <div class="tab" data-tab="pending">Pending</div>
                    <div class="tab" data-tab="grading">Need Grading</div>
                    <div class="tab" data-tab="completed">Completed</div>
                </div>
                
                <!-- Search & Filters -->
                <div class="search-container">
                    <input type="text" placeholder="Search assignments..." class="search-input">
                    <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                
                <div class="filters-container" style="display: flex; gap: 1rem; align-items: flex-end;">
                    <div class="filter">
                        <select id="filterClass" class="form-select">
                            <option value="">All Classes</option>
                        </select>
                    </div>
                    <div class="filter">
                        <select id="filterSection" class="form-select">
                            <option value="">All Sections</option>
                        </select>
                    </div>
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
                    <div class="card-grid"></div>
                </div>
                
                <div class="tab-content" id="grading-content">
                    <div class="card-grid"></div>
                </div>
                
                <div class="tab-content" id="completed-content">
                    <div class="card-grid"></div>
                </div>
            </div>
        </div>

        <!-- Create New Assignment Modal -->
        <div class="card hidden-form" id="newAssignmentForm">
            <div class="card-header">
                <h2 class="card-title">Create New Assignment</h2>
            </div>
            <div class="card-body">
                <form id="assignmentForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_homework">
                    <div class="form-group">
                        <label for="assignmentTitle" class="form-label">Assignment Title*</label>
                        <input type="text" id="assignmentTitle" name="title" class="form-input" placeholder="Enter a title for this assignment" required>
                    </div>
                    
                    <div class="two-col">
                        <div class="form-group">
                            <label for="assignmentClass" class="form-label">Class*</label>
                            <select id="assignmentClass" name="class_id" class="form-select" required>
                                <option value="">Select a class</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="assignmentSection" class="form-label">Section*</label>
                            <select id="assignmentSection" name="section_id" class="form-select" required>
                                <option value="">Select a section</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="assignmentSubject" class="form-label">Subject*</label>
                            <select id="assignmentSubject" name="subject_id" class="form-select" required>
                                <option value="">Select a subject</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="assignmentDescription" class="form-label">Description*</label>
                        <textarea id="assignmentDescription" name="description" class="form-textarea" placeholder="Provide detailed instructions for students" required></textarea>
                    </div>
                    
                    <div class="two-col">
                        <div class="form-group">
                            <label for="assignmentDueDate" class="form-label">Due Date*</label>
                            <div class="date-picker">
                                <input type="date" id="assignmentDueDate" name="due_date" class="form-input" required>
                                <svg xmlns="http://www.w3.org/2000/svg" class="date-picker-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="assignmentPoints" class="form-label">Maximum Points</label>
                            <input type="number" id="assignmentPoints" name="total_marks" class="form-input" placeholder="e.g. 100" min="0" value="100">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="assignmentSubmissionType" class="form-label">Submission Type*</label>
                        <select id="assignmentSubmissionType" name="submission_type" class="form-select" required>
                            <option value="online">Online Submission</option>
                            <option value="physical">Physical Submission</option>
                            <option value="both">Both Online and Physical</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Attachment (Optional)</label>
                        <div class="file-upload">
                            <label class="file-upload-label">
                                <svg xmlns="http://www.w3.org/2000/svg" class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="upload-text">Click to upload or drag and drop</span>
                            </label>
                            <input type="file" name="attachment" class="file-upload-input">
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem;">
                        <button type="button" class="btn btn-secondary" id="cancelAssignment">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
</body>
</html>