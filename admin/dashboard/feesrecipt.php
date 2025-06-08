<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fee Receipts</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/feesrecipt.css">
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
            <h1 class="header-title">Fee Receipts</h1>
            <span class="header-date"><?php echo date('F j, Y'); ?></span>
        </header>

        <main class="dashboard-content">
            <div class="receipts-container">
                <div class="receipts-header">
                    <h2 class="receipts-title">Fee Receipts Management</h2>
                    <div class="receipts-actions">
                        <button class="button button-outline">Export PDF</button>
                        <button class="button">Generate New Receipt</button>
                    </div>
                </div>

                <div class="search-filter-section">
                    <div class="search-container">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search by receipt ID, student name...">
                    </div>
                    
                    <div class="filter-container">
                        <select class="filter-select">
                            <option value="">All Classes</option>
                            <option value="class-1">Class 1</option>
                            <option value="class-2">Class 2</option>
                            <option value="class-3">Class 3</option>
                            <option value="class-4">Class 4</option>
                        </select>
                        
                        <select class="filter-select">
                            <option value="">Payment Status</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="overdue">Overdue</option>
                        </select>
                        
                        <select class="filter-select">
                            <option value="">Date Range</option>
                            <option value="today">Today</option>
                            <option value="this-week">This Week</option>
                            <option value="this-month">This Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                </div>

                <div class="receipts-table-container">
                    <table class="receipts-table">
                        <thead>
                            <tr>
                                <th>Receipt ID</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>REC-001234</td>
                                <td>John Doe</td>
                                <td>Class 5A</td>
                                <td>Tuition Fee</td>
                                <td>$500.00</td>
                                <td>Mar 10, 2025</td>
                                <td><span class="status-badge status-paid">Paid</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-button" onclick="openReceiptModal()">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 10v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V10M5 6V3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v3M9 22V12M15 22V12M3 10h18"/>
                                            </svg>
                                        </button>
                                        <button class="action-button">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8z"/>
                                                <path d="M14 2v4h4"/>
                                                <path d="M16 13H8"/>
                                                <path d="M16 17H8"/>
                                                <path d="M10 9H8"/>
                                            </svg>
                                        </button>
                                        <button class="action-button">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>REC-001235</td>
                                <td>Jane Smith</td>
                                <td>Class 3B</td>
                                <td>Library Fee</td>
                                <td>$150.00</td>
                                <td>Mar 12, 2025</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-button" onclick="openReceiptModal()">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 10v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V10M5 6V3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v3M9 22V12M15 22V12M3 10h18"/>
                                            </svg>
                                        </button>
                                        <button class="action-button">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8z"/>
                                                <path d="M14 2v4h4"/>
                                                <path d="M16 13H8"/>
                                                <path d="M16 17H8"/>
                                                <path d="M10 9H8"/>
                                            </svg>
                                        </button>
                                        <button class="action-button">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>REC-001236</td>
                                <td>Michael Brown</td>
                                <td>Class 6C</td>
                                <td>Sports Fee</td>
                                <td>$75.00</td>
                                <td>Mar 05, 2025</td>
                                <td><span class="status-badge status-overdue">Overdue</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-button" onclick="openReceiptModal()">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 10v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V10M5 6V3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v3M9 22V12M15 22V12M3 10h18"/>
                                            </svg>
                                        </button>
                                        <button class="action-button">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M14 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h8z"/>
                                                <path d="M14 2v4h4"/>
                                                <path d="M16 13H8"/>
                                                <path d="M16 17H8"/>
                                                <path d="M10 9H8"/>
                                            </svg>
                                        </button>
                                        <button class="action-button">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>REC-001237</td>
                                <td>Emily Johnson</td>
                                <td>Class 4A</td>
                                <td>Annual Fee</td>
                                <td>$800.00</td>
                                <td>Mar 15, 2025</td>
                                <td><span class="status-badge status-paid">Paid</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-button" onclick="openReceiptModal()">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21 10v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V10M5 6V3a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v3M9 22V12M15 22V12M3 10h18"/>
                                            </svg>
                                        </button>
                                        <button class="action-button">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor