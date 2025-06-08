<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fee Reports</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/fee_reports_charts.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Add Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .dashboard-container {
            margin-left: 280px;
            transition: all 0.3s ease;
            position: relative;
            height: 100vh;
            overflow-y: auto;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                margin-left: 0;
            }
            
            body.sidebar-open .dashboard-container {
                margin-left: 0;
            }
        }
        
        .dashboard-content {
            padding: 20px;
        }
        
        .action-bar {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        @media (min-width: 768px) {
            .action-bar {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }
        }
        
        .search-bar {
            position: relative;
            width: 100%;
        }
        
        @media (min-width: 768px) {
            .search-bar {
                width: 350px;
            }
        }
        
        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: #777;
        }
        
        .search-input {
            width: 100%;
            padding: 8px 12px 8px 36px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .search-input:focus {
            border-color: #4a6cf7;
            outline: none;
        }
        
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            width: 100%;
        }
        
        @media (min-width: 768px) {
            .action-buttons {
                width: auto;
            }
        }
        
        /* Filter Panel Styles */
        .filter-panel {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .filter-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0 0 15px 0;
        }
        
        .filter-form {
            margin-bottom: 15px;
        }
        
        .filter-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        @media (min-width: 768px) {
            .filter-actions {
                justify-content: flex-end;
            }
        }
        
        .filter-btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        
        .filter-btn-reset {
            background-color: #f5f5f5;
            color: #555;
        }
        
        .filter-btn-reset:hover {
            background-color: #e0e0e0;
        }
        
        .filter-btn-apply {
            background-color: #4a6cf7;
            color: white;
        }
        
        .filter-btn-apply:hover {
            background-color: #3a5bd9;
        }
        
        /* Tab System Styles */
        .results-tabs {
            display: flex;
            flex-wrap: wrap;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            gap: 5px;
        }
        
        @media (max-width: 767px) {
            .results-tabs {
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 5px;
            }
        }
        
        .results-tab {
            padding: 10px 15px;
            font-weight: 500;
            color: #555;
            cursor: pointer;
            position: relative;
            transition: all 0.2s;
            font-size: 14px;
        }
        
        @media (min-width: 768px) {
            .results-tab {
                padding: 12px 20px;
                font-size: 16px;
            }
        }
        
        .results-tab:hover {
            color: #4a6cf7;
        }
        
        .results-tab.active {
            color: #4a6cf7;
            font-weight: 600;
        }
        
        .results-tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #4a6cf7;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        @media (min-width: 576px) {
            .form-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 992px) {
            .form-row {
                grid-template-columns: repeat(3, 1fr);
                gap: 20px;
                margin-bottom: 20px;
            }
        }
        
        @media (min-width: 1200px) {
            .form-row {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
        
        .form-group {
            margin-bottom: 15px;
            width: 100%;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        
        .form-select,
        .form-input,
        .form-textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .form-select:focus,
        .form-input:focus,
        .form-textarea:focus {
            border-color: #4a6cf7;
            outline: none;
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            border: none;
        }
        
        .btn-icon {
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        .btn-primary {
            background-color: #4a6cf7;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3a5bd9;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid #ddd;
            color: #555;
        }
        
        .btn-outline:hover {
            background-color: #f5f5f5;
        }
        
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #0ea271;
        }
        
        .btn-danger {
            background-color: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        .btn-warning {
            background-color: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #d97706;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        @media (max-width: 991px) {
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
        }
        
        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        
        th {
            font-weight: 600;
            color: #333;
            background-color: #f9fafb;
        }
        
        tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success {
            background-color: #d1fae5;
            color: #065f46;
        }
        
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .badge-danger {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            width: 100%;
        }
        
        @media (min-width: 768px) {
            .action-buttons {
                width: auto;
            }
        }
        
        .summary-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        @media (min-width: 640px) {
            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1024px) {
            .summary-cards {
                grid-template-columns: repeat(4, 1fr);
                gap: 20px;
                margin-bottom: 30px;
            }
        }
        
        .summary-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 24px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .summary-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
        }
        
        .summary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background-color: #4a6cf7;
        }
        
        .summary-card:nth-child(1)::before {
            background-color: #4a6cf7;
        }
        
        .summary-card:nth-child(2)::before {
            background-color: #10b981;
        }
        
        .summary-card:nth-child(3)::before {
            background-color: #f59e0b;
        }
        
        .summary-card:nth-child(4)::before {
            background-color: #ef4444;
        }
        
        .summary-title {
            font-size: 15px;
            color: #666;
            margin-bottom: 12px;
            font-weight: 500;
        }
        
        .summary-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
        }
        
        .summary-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-left: auto;
            margin-top: auto;
            position: absolute;
            right: 20px;
            top: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .summary-icon.blue {
            background-color: #4a6cf7;
        }
        
        .summary-icon.green {
            background-color: #10b981;
        }
        
        .summary-icon.yellow {
            background-color: #f59e0b;
        }
        
        .summary-icon.red {
            background-color: #ef4444;
        }
        
        .summary-indicator {
            display: flex;
            align-items: center;
            font-size: 13px;
            font-weight: 500;
            margin-top: 5px;
        }
        
        .indicator-positive {
            color: #10b981;
            fill: #10b981;
        }
        
        .indicator-negative {
            color: #ef4444;
            fill: #ef4444;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        
        .page-link {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #555;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .page-link:hover {
            background-color: #f5f5f5;
        }
        
        .page-link.active {
            background-color: #4a6cf7;
            color: white;
            border-color: #4a6cf7;
        }
        
        .export-buttons {
            display: flex;
            gap: 10px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 0;
            color: #777;
        }
        
        .empty-state-icon {
            width: 60px;
            height: 60px;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        /* Chart Styles */
        .charts-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        @media (min-width: 992px) {
            .charts-container {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                margin-bottom: 30px;
            }
        }
        
        .chart-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            padding: 24px;
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
        }
        
        .chart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.12);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .chart-filters {
            display: flex;
            gap: 10px;
        }
        
        .chart-filter {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 4px;
            background-color: #f5f5f5;
            color: #666;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .chart-filter:hover {
            background-color: #e0e0e0;
        }
        
        .chart-filter.active {
            background-color: #4a6cf7;
            color: white;
        }
        
        .chart-body {
            height: 300px;
            position: relative;
            background-color: #fff !important;
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
            <h1 class="header-title">Fee Reports</h1>
            <span class="header-path">Dashboard > Fee Management > Fee Reports</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="feeSearch" class="search-input" placeholder="Search by student name, class, receipt...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="filterToggleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        Filter
                    </button>
                    <button class="btn btn-outline" id="exportExcelBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Export Excel
                    </button>
                    <button class="btn btn-outline" id="exportPdfBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Export PDF
                    </button>
                    <button class="btn btn-primary" id="refresh-stats">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Refresh Data
                    </button>
                </div>
            </div>
            
            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel" style="display: none;">
                <h3 class="filter-title">Filter Fee Reports</h3>
                <form class="filter-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="startDate">Start Date</label>
                            <input type="date" class="form-input" id="startDate">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="endDate">End Date</label>
                            <input type="date" class="form-input" id="endDate">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="classFilter">Class</label>
                            <select class="form-select" id="classFilter">
                                <option value="">All Classes</option>
                                <!-- Will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="sectionFilter">Section</label>
                            <select class="form-select" id="sectionFilter">
                                <option value="">All Sections</option>
                                <!-- Will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="feeTypeFilter">Fee Type</label>
                            <select class="form-select" id="feeTypeFilter">
                                <option value="">All Fee Types</option>
                                <!-- Will be populated dynamically -->
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="statusFilter">Payment Status</label>
                            <select class="form-select" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="paid">Paid</option>
                                <option value="partial">Partially Paid</option>
                                <option value="pending">Pending</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                    </div>
                </form>
                <div class="filter-actions">
                    <button class="filter-btn filter-btn-reset">Reset</button>
                    <button class="filter-btn filter-btn-apply" id="applyFilterBtn">Apply Filters</button>
                </div>
            </div>
            
            <!-- Performance Metrics Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <div class="summary-title">Total Fee Amount</div>
                    <div class="summary-value" id="totalFeesValue">₹0</div>
                    <div class="summary-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive" style="margin-right: 5px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        <span class="indicator-positive">8% from last month</span>
                    </div>
                    <div class="summary-icon blue">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-title">Total Collected</div>
                    <div class="summary-value" id="totalCollectedValue">₹0</div>
                    <div class="summary-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive" style="margin-right: 5px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        <span class="indicator-positive">12% from last month</span>
                    </div>
                    <div class="summary-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-title">Pending Amount</div>
                    <div class="summary-value" id="pendingFeesValue">₹0</div>
                    <div class="summary-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-negative" style="margin-right: 5px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        <span class="indicator-negative">5% from last month</span>
                    </div>
                    <div class="summary-icon yellow">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="summary-card">
                    <div class="summary-title">Overdue Amount</div>
                    <div class="summary-value" id="overdueFeesValue">₹0</div>
                    <div class="summary-indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-negative" style="margin-right: 5px;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                        </svg>
                        <span class="indicator-negative">3% from last month</span>
                    </div>
                    <div class="summary-icon red">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
            
            <script>
                // Refresh button functionality
                document.getElementById('refresh-stats').addEventListener('click', function() {
                    // Clear any existing charts
                    if (window.collectionChart) window.collectionChart.destroy();
                    if (window.monthlyChart) window.monthlyChart.destroy();
                    if (window.classwiseChart) window.classwiseChart.destroy();
                    if (window.methodsChart) window.methodsChart.destroy();
                    
                    // Force recreate chart canvases
                    document.querySelectorAll('.chart-body').forEach((container, index) => {
                        // Get the right chart ID based on position/index
                        let chartId;
                        if (index === 0) chartId = 'collectionProgressChart';
                        else if (index === 1) chartId = 'monthlyCollectionChart';
                        else if (index === 2) chartId = 'classwiseComparisonChart';
                        else if (index === 3) chartId = 'paymentMethodsChart';
                        else return;
                        
                        // Clear any existing content including "no data" messages
                        container.innerHTML = '';
                        
                        // Create fresh canvas with proper ID
                        const canvas = document.createElement('canvas');
                        canvas.id = chartId;
                        container.appendChild(canvas);
                    });
                    
                    // Reload data
                    loadSummary();
                    
                    // Force white backgrounds after a short delay to ensure rendering completes
                    setTimeout(function() {
                        if (typeof applyWhiteBackgrounds === 'function') {
                            applyWhiteBackgrounds();
                        }
                    }, 500);
                });
                
                // Filter toggle button functionality
                document.getElementById('filterToggleBtn').addEventListener('click', function() {
                    const filterPanel = document.getElementById('filterPanel');
                    filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
                });
                
                // Reset filters button
                document.querySelector('.filter-btn-reset').addEventListener('click', function() {
                    document.getElementById('startDate').valueAsDate = new Date(new Date().setDate(new Date().getDate() - 30));
                    document.getElementById('endDate').valueAsDate = new Date();
                    document.getElementById('classFilter').value = '';
                    document.getElementById('sectionFilter').value = '';
                    document.getElementById('feeTypeFilter').value = '';
                    document.getElementById('statusFilter').value = '';
                });
            </script>
            
            <!-- Tab System -->
            <div class="results-tabs">
                <div class="results-tab active" data-tab="overview">Dashboard Overview</div>
                <div class="results-tab" data-tab="collection">Collection Details</div>
                <div class="results-tab" data-tab="classwise">Class Analysis</div>
                <div class="results-tab" data-tab="methods">Payment Methods</div>
                <div class="results-tab" data-tab="records">Payment Records</div>
            </div>
            
            <!-- Overview Tab Content -->
            <div class="tab-content active" id="overview-tab">
                <div class="charts-container">
                    <!-- Collection Progress Chart -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Collection Progress</h3>
                            <div class="chart-filters">
                                <div class="chart-filter active" data-period="all">All Time</div>
                                <div class="chart-filter" data-period="year">This Year</div>
                                <div class="chart-filter" data-period="month">This Month</div>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="collectionProgressChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Monthly Collection Trend -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Monthly Collection Trend</h3>
                            <div class="chart-filters">
                                <div class="chart-filter active" data-period="6months">Last 6 Months</div>
                                <div class="chart-filter" data-period="year">This Year</div>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="monthlyCollectionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Collection Tab Content -->
            <div class="tab-content" id="collection-tab">
                <div class="charts-container">
                    <!-- Detailed Collection Information -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Collection Breakdown</h3>
                            <div class="chart-filters">
                                <div class="chart-filter active" data-period="all">All Time</div>
                                <div class="chart-filter" data-period="year">This Year</div>
                                <div class="chart-filter" data-period="month">This Month</div>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="collectionProgressChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Monthly Detailed Trend -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Monthly Collection Analysis</h3>
                            <div class="chart-filters">
                                <div class="chart-filter active" data-period="6months">Last 6 Months</div>
                                <div class="chart-filter" data-period="year">This Year</div>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="monthlyCollectionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Classwise Tab Content -->
            <div class="tab-content" id="classwise-tab">
                <div class="charts-container">
                    <!-- Classwise Collection Comparison -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Classwise Collection</h3>
                            <div class="chart-filters">
                                <div class="chart-filter active" data-period="percent">Percentage</div>
                                <div class="chart-filter" data-period="amount">Amount</div>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="classwiseComparisonChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payment Methods Tab Content -->
            <div class="tab-content" id="methods-tab">
                <div class="charts-container">
                    <!-- Payment Methods Distribution -->
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Payment Methods</h3>
                            <div class="chart-filters">
                                <div class="chart-filter active" data-period="all">All Time</div>
                                <div class="chart-filter" data-period="month">This Month</div>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="paymentMethodsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
                // Force white backgrounds on all chart cards
                document.addEventListener('DOMContentLoaded', function() {
                    // Apply white background to all chart cards
                    document.querySelectorAll('.chart-card').forEach(function(card) {
                        card.style.backgroundColor = '#ffffff';
                        card.style.color = '#333333';
                    });
                    
                    document.querySelectorAll('.chart-header').forEach(function(header) {
                        header.style.backgroundColor = '#ffffff';
                        header.style.color = '#333333';
                    });
                    
                    document.querySelectorAll('.chart-title').forEach(function(title) {
                        title.style.color = '#333333';
                    });
                    
                    document.querySelectorAll('.chart-body').forEach(function(body) {
                        body.style.backgroundColor = '#ffffff';
                    });
                });
            </script>
            
            <!-- Records Tab Content -->
            <div class="tab-content" id="records-tab">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payment Records</h3>
                    </div>
                    <div id="reportsTableContainer">
                        <div class="empty-state">
                            <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p>Loading payment records...</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

<script src="js/fee_reports_charts.js"></script>
<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        sidebar.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');

        if (!overlay.hasEventListener) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
            overlay.hasEventListener = true;
        }
    }
    
    // Set initial state on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Check if sidebar is already visible (from sidebar.php)
        const sidebar = document.getElementById('sidebar');
        if (sidebar && sidebar.classList.contains('show')) {
            document.body.classList.add('sidebar-open');
        }
    });
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const classFilter = document.getElementById('classFilter');
    const sectionFilter = document.getElementById('sectionFilter');
    const feeTypeFilter = document.getElementById('feeTypeFilter');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    const statusFilter = document.getElementById('statusFilter');
    const applyFilterBtn = document.getElementById('applyFilterBtn');
    const exportExcelBtn = document.getElementById('exportExcelBtn');
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    const reportsTableContainer = document.getElementById('reportsTableContainer');
    
    // Set default date values
    const today = new Date();
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(today.getDate() - 30);
    
    endDate.valueAsDate = today;
    startDate.valueAsDate = thirtyDaysAgo;
    
    // Event Listeners
    applyFilterBtn.addEventListener('click', loadReports);
    exportExcelBtn.addEventListener('click', function() {
        exportReports('excel');
    });
    exportPdfBtn.addEventListener('click', function() {
        exportReports('pdf');
    });
    
    classFilter.addEventListener('change', function() {
        loadSections(this.value);
    });
    
    // Initial loading
    loadClasses();
    loadFeeTypes();
    loadReports();
    
    /**
     * Load classes for the filter
     */
    function loadClasses() {
        fetch('fee_reports_action.php?action=get_classes')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    classFilter.innerHTML = '<option value="">All Classes</option>';
                    
                    data.classes.forEach(cls => {
                        const option = document.createElement('option');
                        option.value = cls.id;
                        option.textContent = cls.name;
                        classFilter.appendChild(option);
                    });
                } else {
                    console.error('Failed to load classes:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    /**
     * Load sections for the selected class
     */
    function loadSections(classId) {
        sectionFilter.innerHTML = '<option value="">All Sections</option>';
        
        if (!classId) return;
        
        fetch(`fee_reports_action.php?action=get_sections&class_id=${classId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.sections.forEach(section => {
                        const option = document.createElement('option');
                        option.value = section.id;
                        option.textContent = section.name;
                        sectionFilter.appendChild(option);
                    });
                } else {
                    console.error('Failed to load sections:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    /**
     * Load fee types for the filter
     */
    function loadFeeTypes() {
        fetch('fee_reports_action.php?action=get_fee_types')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    feeTypeFilter.innerHTML = '<option value="">All Fee Types</option>';
                    
                    data.fee_types.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = type.title;
                        feeTypeFilter.appendChild(option);
                    });
                } else {
                    console.error('Failed to load fee types:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    /**
     * Load reports based on filters
     */
    function loadReports(page = 1) {
        // Show loading state
        reportsTableContainer.innerHTML = `
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>Loading payment records...</p>
            </div>
        `;
        
        // Update summary statistics
        loadSummary();
        
        // Build query params
        let params = new URLSearchParams();
        params.append('action', 'get_reports');
        params.append('page', page);
        params.append('items_per_page', 10);
        
        if (startDate.value) params.append('start_date', startDate.value);
        if (endDate.value) params.append('end_date', endDate.value);
        if (classFilter.value) params.append('class_id', classFilter.value);
        if (sectionFilter.value) params.append('section_id', sectionFilter.value);
        if (feeTypeFilter.value) params.append('fee_type_id', feeTypeFilter.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        
        fetch('fee_reports_action.php?' + params.toString())
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.reports.length === 0) {
                        reportsTableContainer.innerHTML = `
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" class="empty-state-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p>No payment records found for the selected filters.</p>
                                <p>Try adjusting your filters or adding payments.</p>
                            </div>
                        `;
                    } else {
                        // Create table
                        const table = document.createElement('table');
                        table.innerHTML = `
                            <thead>
                                <tr>
                                    <th>Receipt No.</th>
                                    <th>Student</th>
                                    <th>Class</th>
                                    <th>Fee Type</th>
                                    <th>Payment Date</th>
                                    <th>Amount Paid</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        `;
                        
                        const tbody = table.querySelector('tbody');
                        
                        data.reports.forEach(report => {
                            const row = document.createElement('tr');
                            
                            // Format receipt number
                            const receiptNumber = `RCPT-${report.payment_id.toString().padStart(5, '0')}`;
                            
                            // Format date
                            const paymentDate = new Date(report.payment_date);
                            const formattedDate = paymentDate.toLocaleDateString();
                            
                            // Format status badge
                            let statusBadge = '';
                            switch (report.status) {
                                case 'paid':
                                    statusBadge = '<span class="badge badge-success">Paid</span>';
                                    break;
                                case 'partial':
                                    statusBadge = '<span class="badge badge-warning">Partial</span>';
                                    break;
                                default:
                                    statusBadge = '<span class="badge badge-danger">Pending</span>';
                                    break;
                            }
                            
                            row.innerHTML = `
                                <td>${receiptNumber}</td>
                                <td>${report.student_name}</td>
                                <td>${report.class_name} ${report.section_name}</td>
                                <td>${report.fee_title}</td>
                                <td>${formattedDate}</td>
                                <td>₹${parseFloat(report.amount_paid).toFixed(2)}</td>
                                <td>₹${parseFloat(report.remaining_amount).toFixed(2)}</td>
                                <td>${statusBadge}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="fee_receipt.php?payment_id=${report.payment_id}" class="btn btn-sm btn-outline" target="_blank">
                                            <i class="fas fa-print"></i> Receipt
                                        </a>
                                    </div>
                                </td>
                            `;
                            
                            tbody.appendChild(row);
                        });
                        
                        reportsTableContainer.innerHTML = '';
                        reportsTableContainer.appendChild(table);
                        
                        // Add pagination
                        if (data.total_items > 10) {
                            const totalPages = Math.ceil(data.total_items / 10);
                            const pagination = document.createElement('div');
                            pagination.className = 'pagination';
                            
                            // Previous page
                            if (page > 1) {
                                const prevLink = document.createElement('a');
                                prevLink.href = '#';
                                prevLink.className = 'page-link';
                                prevLink.textContent = 'Previous';
                                prevLink.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    loadReports(page - 1);
                                });
                                pagination.appendChild(prevLink);
                            }
                            
                            // Page numbers
                            for (let i = 1; i <= totalPages; i++) {
                                if (totalPages > 7 && i > 3 && i < totalPages - 2) {
                                    // If we have many pages, show ellipsis
                                    if (i === 4) {
                                        const ellipsis = document.createElement('span');
                                        ellipsis.className = 'page-link';
                                        ellipsis.textContent = '...';
                                        pagination.appendChild(ellipsis);
                                    }
                                    continue;
                                }
                                
                                const pageLink = document.createElement('a');
                                pageLink.href = '#';
                                pageLink.className = 'page-link' + (page === i ? ' active' : '');
                                pageLink.textContent = i;
                                pageLink.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    loadReports(i);
                                });
                                pagination.appendChild(pageLink);
                            }
                            
                            // Next page
                            if (page < totalPages) {
                                const nextLink = document.createElement('a');
                                nextLink.href = '#';
                                nextLink.className = 'page-link';
                                nextLink.textContent = 'Next';
                                nextLink.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    loadReports(page + 1);
                                });
                                pagination.appendChild(nextLink);
                            }
                            
                            reportsTableContainer.appendChild(pagination);
                        }
                    }
                } else {
                    reportsTableContainer.innerHTML = `
                        <div class="empty-state">
                            <p>Error: ${data.message}</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                reportsTableContainer.innerHTML = `
                    <div class="empty-state">
                        <p>Error: Failed to load reports. Please try again.</p>
                    </div>
                `;
            });
    }
    
    /**
     * Load summary statistics
     */
    function loadSummary() {
        // Build query params
        let params = new URLSearchParams();
        params.append('action', 'get_summary');
        
        if (startDate.value) params.append('start_date', startDate.value);
        if (endDate.value) params.append('end_date', endDate.value);
        if (classFilter.value) params.append('class_id', classFilter.value);
        if (sectionFilter.value) params.append('section_id', sectionFilter.value);
        if (feeTypeFilter.value) params.append('fee_type_id', feeTypeFilter.value);
        
        const apiUrl = 'fee_reports_action.php?' + params.toString();
        
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update summary values
                    document.getElementById('totalFeesValue').textContent = formatCurrency(data.total_fees);
                    document.getElementById('totalCollectedValue').textContent = formatCurrency(data.total_collected);
                    document.getElementById('pendingFeesValue').textContent = formatCurrency(data.pending_fees);
                    document.getElementById('overdueFeesValue').textContent = formatCurrency(data.overdue_fees);
                    
                    // Initialize charts with this data
                    window.feeSummaryData = data;
                    
                    // Dispatch event to initialize charts
                    const event = new CustomEvent('feeSummaryLoaded', { detail: data });
                    document.dispatchEvent(event);
                } else {
                    console.error('Failed to load summary:', data.message);
                }
            })
            .catch(error => {
                console.error('Error loading summary data:', error);
            });
    }
    
    /**
     * Export reports based on current filters
     */
    function exportReports(format) {
        // Build query params
        let params = new URLSearchParams();
        params.append('action', 'export_reports');
        params.append('format', format);
        
        if (startDate.value) params.append('start_date', startDate.value);
        if (endDate.value) params.append('end_date', endDate.value);
        if (classFilter.value) params.append('class_id', classFilter.value);
        if (sectionFilter.value) params.append('section_id', sectionFilter.value);
        if (feeTypeFilter.value) params.append('fee_type_id', feeTypeFilter.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        
        // Open in new window for download
        window.open('fee_reports_action.php?' + params.toString(), '_blank');
    }
    
    /**
     * Helper function to format currency
     */
    function formatCurrency(amount) {
        return '₹' + new Intl.NumberFormat('en-IN').format(parseFloat(amount) || 0);
    }
    
    // Tab Switching Functionality
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
});
</script>

</body>
</html> 