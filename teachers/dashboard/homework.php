<?php
require_once 'con.php';
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../index.php');
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
    <link rel="stylesheet" href="css/homework.css">
    <script src="js/homework-fixed.js" defer></script>
    
    <style>
        /* Hamburger button styles */
        .hamburger-btn {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1000;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 8px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.2s ease;
        }

        .hamburger-btn:hover {
            background: #f9fafb;
        }

        .hamburger-icon {
            width: 24px;
            height: 24px;
            color: #374151;
        }

        /* Dashboard container adjustments for sidebar - Better screen usage */
        .dashboard-container {
            margin-left: 260px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
            max-width: calc(100% - 260px);
        }

        /* Better screen real estate usage */
        .dashboard-content {
            padding: 1.5rem;
            max-width: 100%;
        }

        /* Header adjustments */
        .dashboard-header {
            margin-bottom: 1.5rem;
        }

        /* Stats grid - better responsive layout */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        /* Card adjustments for better space usage */
        .card {
            width: 100%;
            box-sizing: border-box;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .dashboard-container {
                margin-left: 0;
                max-width: 100%;
            }
            
            .dashboard-content {
                padding: 1rem;
            }
            
            .hamburger-btn {
                display: block;
            }

            /* Mobile tabs - make them scrollable horizontally */
            .tabs {
                display: flex;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                -ms-overflow-style: none;
                border-bottom: 1px solid #e5e7eb;
                background: white;
                padding: 0;
                margin: 0 -1rem;
                padding: 0 1rem;
            }

            .tabs::-webkit-scrollbar {
                display: none;
            }

            .tab {
                padding: 0.75rem 1rem;
                cursor: pointer;
                color: #6b7280;
                font-weight: 500;
                border-bottom: 2px solid transparent;
                transition: all 0.2s ease;
                white-space: nowrap;
                flex-shrink: 0;
                font-size: 0.875rem;
            }

            /* Mobile filters - stack vertically and full width */
            .filters-container {
                display: flex !important;
                flex-direction: column !important;
                gap: 0.75rem !important;
                align-items: stretch !important;
            }

            .filter {
                width: 100% !important;
            }

            .form-select {
                width: 100% !important;
                padding: 0.75rem !important;
                font-size: 0.875rem !important;
            }

            #applyFiltersBtn {
                margin-left: 0 !important;
                width: 100% !important;
                min-width: auto !important;
                justify-content: center;
            }

            /* Stats grid mobile */
            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            /* Card header mobile */
            .card-header {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .card-header .btn {
                width: 100%;
                justify-content: center;
            }

            /* Search container mobile */
            .search-container {
                margin: 0 -1rem 1rem -1rem;
                padding: 0 1rem;
            }

            /* Homework cards mobile spacing */
            .homework-card {
                margin: 0 -1rem 1rem -1rem;
                padding: 1rem;
                border-radius: 0;
                border-left: none;
                border-right: none;
            }
        }

        /* Tablet adjustments */
        @media (min-width: 769px) and (max-width: 1024px) {
            .dashboard-container {
                margin-left: 260px;
            }

            .filters-container {
                flex-wrap: wrap !important;
            }

            .filter {
                min-width: 180px;
                flex: 1;
            }
        }

        /* Desktop - better use of space */
        @media (min-width: 1025px) {
            .dashboard-content {
                padding: 2rem;
            }

            .quick-stats {
                grid-template-columns: repeat(4, 1fr);
                gap: 1.5rem;
            }

            /* Ensure full width usage */
            .card {
                width: 100%;
            }

            .filters-container {
                display: flex !important;
                gap: 1rem !important;
                align-items: flex-end !important;
                flex-wrap: nowrap !important;
            }

            .filter {
                flex: 1;
                min-width: 150px;
            }
        }

        /* When sidebar is collapsed */
        .sidebar.collapsed + .dashboard-container {
            margin-left: 80px;
            max-width: calc(100% - 80px);
        }

        /* Three-dot menu styles */
        .homework-card {
            position: relative;
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
            border: 1px solid #e5e7eb;
        }

        .card-header-with-menu {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .three-dot-menu {
            position: relative;
            display: inline-block;
        }

        .three-dot-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.2s;
            color: #6b7280;
        }

        .three-dot-btn:hover {
            background-color: #f3f4f6;
            color: #374151;
        }

        .three-dot-btn:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5);
        }

        .dots-icon {
            width: 20px;
            height: 20px;
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            min-width: 160px;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            text-decoration: none;
            color: #374151;
            font-size: 14px;
            transition: background-color 0.2s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .dropdown-item:hover {
            background-color: #f9fafb;
        }

        .dropdown-item:first-child {
            border-radius: 6px 6px 0 0;
        }

        .dropdown-item:last-child {
            border-radius: 0 0 6px 6px;
        }

        .dropdown-item.danger {
            color: #dc2626;
        }

        .dropdown-item.danger:hover {
            background-color: #fef2f2;
        }

        .dropdown-icon {
            width: 16px;
            height: 16px;
            margin-right: 8px;
        }

        .homework-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #111827;
            margin: 0 0 0.5rem 0;
        }

        .homework-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .homework-description {
            color: #374151;
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .homework-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-grading {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Sample homework cards to replace "No assignments found" */
        .card-grid:empty::after {
            content: '';
            display: none;
        }

        /* Add sample cards when grid is empty */
        .card-grid {
            min-height: 200px;
        }

        /* Override the empty state when we add sample cards via JS */
        .has-content {
            min-height: auto;
        }

        /* Overlay to close dropdown when clicking outside */
        .dropdown-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 999;
            background: transparent;
            display: none;
        }

        .dropdown-overlay.show {
            display: block;
        }

        /* Make sure the sidebar overlay works properly */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile sidebar adjustments */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }

            /* Desktop tabs behavior */
            .tabs {
                display: flex;
                border-bottom: 1px solid #e5e7eb;
                background: white;
                padding: 0 1rem;
            }

            .tab.active {
                color: #3b82f6;
                border-bottom-color: #3b82f6;
                background: white;
            }

            .tab:hover {
                color: #374151;
                background: #f9fafb;
            }
        }

        /* Large desktop screens */
        @media (min-width: 1400px) {
            .dashboard-content {
                padding: 2.5rem;
                max-width: none;
            }

            .quick-stats {
                gap: 2rem;
            }
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
                            <option value="10a">Class 10A</option>
                            <option value="10b">Class 10B</option>
                            <option value="11a">Class 11A</option>
                        </select>
                    </div>
                    <div class="filter">
                        <select id="filterSection" class="form-select">
                            <option value="">All Sections</option>
                            <option value="a">Section A</option>
                            <option value="b">Section B</option>
                            <option value="c">Section C</option>
                        </select>
                    </div>
                    <div class="filter">
                        <select id="filterSubject" class="form-select">
                            <option value="">All Subjects</option>
                            <option value="math">Mathematics</option>
                            <option value="english">English</option>
                            <option value="science">Science</option>
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
                        <!-- Sample homework cards with three-dot menu -->
                        <div class="homework-card">
                            <div class="card-header-with-menu">
                                <div>
                                    <h3 class="homework-title">Mathematics Quiz Chapter 5</h3>
                                    <div class="homework-meta">
                                        <span>Class 10A</span>
                                        <span>Mathematics</span>
                                        <span>Due: Dec 15, 2024</span>
                                    </div>
                                </div>
                                <div class="three-dot-menu">
                                    <button class="three-dot-btn" onclick="toggleDropdown(this)">
                                        <svg class="dots-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zM12 13a1 1 0 110-2 1 1 0 010 2zM12 20a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu">
                                        <button class="dropdown-item" onclick="viewHomework(1)">
                                            <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View Details
                                        </button>
                                        <button class="dropdown-item" onclick="editHomework(1)">
                                            <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                        <button class="dropdown-item" onclick="duplicateHomework(1)">
                                            <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            Duplicate
                                        </button>
                                        <button class="dropdown-item danger" onclick="deleteHomework(1)">
                                            <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="homework-description">Complete exercises 1-20 from Chapter 5. Focus on quadratic equations and their applications.</p>
                            <span class="homework-status status-pending">Pending</span>
                        </div>

                        <div class="homework-card">
                            <div class="card-header-with-menu">
                                <div>
                                    <h3 class="homework-title">English Essay: Shakespeare Analysis</h3>
                                    <div class="homework-meta">
                                        <span>Class 11B</span>
                                        <span>English</span>
                                        <span>Due: Dec 18, 2024</span>
                                    </div>
                                </div>
                                <div class="three-dot-menu">
                                    <button class="three-dot-btn" onclick="toggleDropdown(this)">
                                        <svg class="dots-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zM12 13a1 1 0 110-2 1 1 0 010 2zM12 20a1 1 0 110-2 1 1 0 010 2z" />
                                        </svg>
                                    </button>
                                    <div class="dropdown-menu">
                                        <button class="dropdown-item" onclick="viewHomework(2)">
                                            <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            View Details
                                        </button>
                                        <button class="dropdown-item" onclick="editHomework(2)">
                                            <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </button>
                                        <button class="dropdown-item" onclick="duplicateHomework(2)">
                                            <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            Duplicate
                                        </button>
                                        <button class="dropdown-item danger" onclick="deleteHomework(2)">
                                            <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="homework-description">Write a 1000-word essay analyzing the themes in Hamlet. Include character development and symbolism.</p>
                           <span class="homework-status status-grading">Need Grading</span>
                       </div>
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

<script>
// Three-dot menu functionality
function toggleDropdown(button) {
   const dropdown = button.nextElementSibling;
   const overlay = document.querySelector('.dropdown-overlay');
   
   // Close all other dropdowns first
   document.querySelectorAll('.dropdown-menu').forEach(menu => {
       if (menu !== dropdown) {
           menu.classList.remove('show');
       }
   });
   
   // Toggle current dropdown
   if (!dropdown.classList.contains('show')) {
       dropdown.classList.add('show');
       if (overlay) {
           overlay.classList.add('show');
       }
       
       // Position dropdown if it goes off screen
       const rect = dropdown.getBoundingClientRect();
       if (rect.right > window.innerWidth) {
           dropdown.style.right = '0';
           dropdown.style.left = 'auto';
       }
       if (rect.bottom > window.innerHeight) {
           dropdown.style.top = 'auto';
           dropdown.style.bottom = '100%';
       }
   } else {
       dropdown.classList.remove('show');
       if (overlay) {
           overlay.classList.remove('show');
       }
   }
}

function closeAllDropdowns() {
   const dropdowns = document.querySelectorAll('.dropdown-menu');
   const overlay = document.querySelector('.dropdown-overlay');
   
   dropdowns.forEach(dropdown => {
       dropdown.classList.remove('show');
   });
   if (overlay) {
       overlay.classList.remove('show');
   }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
   if (!event.target.closest('.three-dot-menu')) {
       closeAllDropdowns();
   }
});

// Homework action functions
function viewHomework(id) {
   console.log('Viewing homework:', id);
   closeAllDropdowns();
   // Add your view homework logic here
}

function editHomework(id) {
   console.log('Editing homework:', id);
   closeAllDropdowns();
   // Add your edit homework logic here
}

function duplicateHomework(id) {
   console.log('Duplicating homework:', id);
   closeAllDropdowns();
   // Add your duplicate homework logic here
}

function deleteHomework(id) {
   if (confirm('Are you sure you want to delete this homework assignment?')) {
       console.log('Deleting homework:', id);
       closeAllDropdowns();
       // Add your delete homework logic here
   }
}

// Sidebar toggle function
function toggleSidebar() {
   const sidebar = document.querySelector('.sidebar');
   const overlay = document.querySelector('.sidebar-overlay');
   
   if (sidebar) {
       sidebar.classList.toggle('show');
   }
   if (overlay) {
       overlay.classList.toggle('active');
   }
}

// Close sidebar when clicking overlay
document.querySelector('.sidebar-overlay')?.addEventListener('click', function() {
   const sidebar = document.querySelector('.sidebar');
   if (sidebar) {
       sidebar.classList.remove('show');
   }
   this.classList.remove('active');
});

// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
   const tabs = document.querySelectorAll('.tab');
   const tabContents = document.querySelectorAll('.tab-content');
   
   tabs.forEach(tab => {
       tab.addEventListener('click', () => {
           const tabId = tab.getAttribute('data-tab');
           
           // Remove active class from all tabs and contents
           tabs.forEach(t => t.classList.remove('active'));
           tabContents.forEach(content => content.classList.remove('active'));
           
           // Add active class to clicked tab and corresponding content
           tab.classList.add('active');
           document.getElementById(tabId + '-content').classList.add('active');
       });
   });
});
</script>

</body>
</html>
