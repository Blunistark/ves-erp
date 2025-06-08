<?php 
// Add session check and role verification
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

include 'sidebar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Class Teachers</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/teachersassign.css">
    <!-- Add SweetAlert for better notifications -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    
    <style>
        /* Scrolling styles for teachersassign.php */
        body {
            margin: 0;
            padding: 0;
            background: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            height: 100vh;
            overflow: hidden; /* Prevent body scroll */
        }

        /* Main content area that will scroll */
        .dashboard-container {
            padding: 20px;
            max-width: none; /* Remove max-width constraint */
            margin: 0;
            height: 100vh;
            overflow-y: auto; /* Enable vertical scrolling */
            overflow-x: hidden; /* Hide horizontal scrollbar */
            padding-left: 260px; /* Reduced from 280px */
            padding-right: 20px;
            box-sizing: border-box;
            width: 100%;
        }

        /* Ensure sidebar stays fixed with reduced width */
        .sidebar {
            position: fixed !important;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            width: 240px; /* Reduced sidebar width */
        }

        /* Custom scrollbar styling */
        .dashboard-container::-webkit-scrollbar {
            width: 8px;
        }

        .dashboard-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .dashboard-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .dashboard-container::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        /* Wider content area */
        .dashboard-content {
            padding-bottom: 40px;
            max-width: none;
            width: 100%;
        }

        /* Optimize header spacing */
        .dashboard-header {
            position: sticky;
            top: 0;
            background: #f8fafc;
            z-index: 100;
            padding: 15px 0 10px 0; /* Reduced top padding */
            margin: -20px 0 20px 0; /* Reduced bottom margin */
            border-bottom: 1px solid #e2e8f0;
        }

        /* Optimize action bar spacing */
        .action-bar {
            position: sticky;
            top: 60px; /* Adjusted for smaller header */
            background: #f8fafc;
            z-index: 99;
            padding: 10px 0;
            margin-bottom: 20px; /* Reduced margin */
            border-bottom: 1px solid #e2e8f0;
        }

        /* Adjust for mobile when sidebar is collapsed */
        @media (max-width: 768px) {
            .dashboard-container {
                padding-left: 15px; /* Reduced padding on mobile */
                padding-right: 15px;
            }
            
            .sidebar.collapsed + .dashboard-container {
                padding-left: 15px;
            }
            
            .dashboard-header {
                position: static;
                border-bottom: none;
                padding: 10px 0;
            }
            
            .action-bar {
                position: static;
                border-bottom: none;
                padding: 10px 0;
            }

            .assignment-cards {
                grid-template-columns: 1fr; /* Single column on mobile */
            }

            .form-row {
                grid-template-columns: 1fr; /* Single column form on mobile */
            }
        }

        /* Larger screens optimization */
        @media (min-width: 1400px) {
            .dashboard-container {
                padding-left: 260px;
                padding-right: 40px; /* More padding on larger screens */
            }

            .assignment-cards {
                grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); /* Larger cards */
                gap: 25px;
            }

            .assignments-table-container {
                max-height: 75vh; /* More height on larger screens */
            }
        }

        /* Ultra-wide screens */
        @media (min-width: 1920px) {
            .dashboard-container {
                padding-left: 260px;
                padding-right: 60px;
            }

            .assignment-cards {
                grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                gap: 30px;
            }
        }

        /* Hamburger button positioning */
        .hamburger-btn {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1001; /* Higher than sidebar */
            display: none;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .hamburger-icon {
            width: 20px;
            height: 20px;
            color: #374151;
        }

        @media (max-width: 768px) {
            .hamburger-btn {
                display: block;
            }
            
            .dashboard-container {
                padding-top: 60px; /* Add space for hamburger button */
            }
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Smooth scrolling */
        .dashboard-container {
            scroll-behavior: smooth;
        }

        /* Loading spinner positioning */
        #loadingSpinner {
            position: sticky;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 50;
        }

        /* Wider assignment cards grid */
        .assignment-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Smaller min-width */
            gap: 20px;
            margin-bottom: 25px;
        }

        /* Optimize table container */
        .assignments-table-container {
            overflow-x: auto;
            margin-bottom: 20px;
            max-height: 70vh; /* Increased from 60vh */
            overflow-y: auto;
            width: 100%;
        }

        /* Make table use full width */
        .assignments-table {
            width: 100%;
            min-width: 800px; /* Ensure minimum width for readability */
        }

        /* Optimize form layout */
        .assignment-form-container {
            margin-top: 15px; /* Reduced margin */
            position: relative;
            z-index: 10;
            max-width: none;
            width: 100%;
        }

        /* Better form row layout */
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Wider columns */
            gap: 20px;
            margin-bottom: 20px;
        }

        /* Optimize filter panel */
        .filter-panel {
            margin-bottom: 15px; /* Reduced margin */
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        /* Scrollbar styles */
        .assignments-table-container::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .assignments-table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .assignments-table-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        /* Optimize card spacing */
        .assignment-card {
            padding: 15px; /* Reduced from default */
            margin-bottom: 0; /* Remove extra margin */
        }

        /* Better table cell spacing */
        .assignments-table th,
        .assignments-table td {
            padding: 10px 12px; /* Optimized padding */
            font-size: 14px;
        }

        /* Compact pagination */
        .pagination {
            margin-top: 15px; /* Reduced margin */
            padding: 10px 0;
        }

        /* Optimize section titles */
        .section-title {
            margin: 20px 0 15px 0; /* Reduced margins */
            font-size: 1.25rem;
        }

        /* Better button spacing in action bar */
        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Optimize search bar */
        .search-bar {
            flex: 1;
            max-width: 400px;
            min-width: 250px;
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
            <h1 class="header-title">Assign Class Teachers</h1>
            <span class="header-path">Dashboard > Teachers > Class Assignment</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="assignmentSearch" class="search-input" placeholder="Search assignments, classes, or teachers...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="filterToggleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        Filter
                    </button>
                    <button class="btn btn-outline" id="exportBtn" title="Export to Excel">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Export
                    </button>
                    <button class="btn btn-primary" id="newAssignmentBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Assign Class Teacher
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div id="filterPanel" class="filter-panel" style="display:none;">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="filterTeacher">Teacher:</label>
                        <select id="filterTeacher" class="form-select">
                            <option value="">All Teachers</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterClass">Class:</label>
                        <select id="filterClass" class="form-select">
                            <option value="">All Classes</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="filterStatus">Status:</label>
                        <select id="filterStatus" class="form-select">
                            <option value="">All Status</option>
                            <option value="assigned">Assigned</option>
                            <option value="unassigned">Unassigned</option>
                    </select>
                    </div>
                    <div class="filter-actions">
                        <button id="applyFilters" class="btn btn-primary">Apply</button>
                <button id="clearFilters" class="btn btn-outline">Clear</button>
                    </div>
                </div>
            </div>

            <!-- Assignment Form -->
            <div class="assignment-form-container" id="assignmentForm" style="display: none;">
                <div class="form-header">
                    <h2 class="form-title">Assign Class Teacher</h2>
                    <button type="button" class="close-btn" id="closeFormBtn" aria-label="Close">×</button>
                </div>
                <form id="teacherAssignmentForm" novalidate>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="teacher">Teacher *</label>
                            <select class="form-select" id="teacher" name="teacher" required>
                                <option value="">Select a teacher</option>
                            </select>
                            <div class="invalid-feedback">Please select a teacher</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="class">Class *</label>
                            <select class="form-select" id="class" name="class" required>
                                <option value="">Select Class</option>
                            </select>
                            <div class="invalid-feedback">Please select a class</div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="section">Section *</label>
                            <select class="form-select" id="section" name="section" required>
                                <option value="">Select Section</option>
                            </select>
                            <div class="invalid-feedback">Please select a section</div>
                        </div>

                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="btn-text">Assign Class Teacher</span>
                            <span class="btn-loader" style="display: none;">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="assignments-section">
            <h2 class="section-title">Class Teacher Assignments</h2>
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner"></div>
                    <div class="spinner-text">Loading assignments...</div>
                </div>
            <div class="assignment-cards">
                <!-- Dynamic assignment cards will be rendered here by JS -->
            </div>

                <div class="assignments-table-container">
                <table class="assignments-table">
                    <thead>
                        <tr>
                                <th>Class</th>
                            <th>Section</th>
                                <th>Class Teacher</th>
                            <th>Students</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Table rows will be dynamically populated here -->
                    </tbody>
                </table>
                </div>

                <div class="no-assignments" style="display: none;">
                    <div class="no-data-message">
                        <svg class="no-data-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p>No assignments found</p>
                        <button class="btn btn-primary btn-sm" id="createFirstAssignment">Assign First Class Teacher</button>
                    </div>
                </div>
            </div>

            <div class="pagination">
                <div class="pagination-info">
                    Showing <span id="showing-start">0</span>-<span id="showing-end">0</span> of <span id="total-items">0</span> assignments
                </div>
                <div class="pagination-buttons">
                    <!-- Pagination buttons will be added dynamically -->
                </div>
            </div>
        </main>
    </div>

    <!-- Add SweetAlert for better notifications -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script src="js/teachersassign.js?v=<?php echo time(); ?>"></script>
</body>
</html>