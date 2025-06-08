<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fee Clearance Status</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/fees.css">
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
        <h1 class="header-title">Fee Clearance Status</h1>
        <span class="header-subtitle">Monitor class/student fee payments</span>
    </header>

    <main class="dashboard-content">
        <!-- Fee Overview Section -->
        <section class="fee-overview-section">
            <!-- Fee Statistics -->
            <div class="fee-stats-grid">
                <div class="fee-stat-card">
                    <h3 class="fee-stat-title">Total Students</h3>
                    <div class="fee-stat-value fee-stat-total">98</div>
                    <div class="fee-stat-secondary">Across all assigned classes</div>
                </div>
                <div class="fee-stat-card">
                    <h3 class="fee-stat-title">Fully Paid</h3>
                    <div class="fee-stat-value fee-stat-paid">72</div>
                    <div class="fee-stat-secondary">73.5% of total students</div>
                </div>
                <div class="fee-stat-card">
                    <h3 class="fee-stat-title">Partially Paid</h3>
                    <div class="fee-stat-value fee-stat-partial">18</div>
                    <div class="fee-stat-secondary">18.4% of total students</div>
                </div>
                <div class="fee-stat-card">
                    <h3 class="fee-stat-title">Unpaid</h3>
                    <div class="fee-stat-value fee-stat-unpaid">8</div>
                    <div class="fee-stat-secondary">8.1% of total students</div>
                </div>
            </div>

            <!-- Reminder Notice -->
            <div class="reminder-notice">
                <svg class="reminder-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <div class="reminder-content">
                    <h3 class="reminder-title">Fee Reminder Notice</h3>
                    <p class="reminder-text">Term 2 fees are due by April 15, 2025. 8 students have outstanding fees. You can send reminders to help ensure timely payments.</p>
                    <div class="reminder-actions">
                        <button class="btn btn-primary btn-sm" onclick="showSendReminderModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.375rem;">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                            Send Reminders
                        </button>
                        <button class="btn btn-secondary btn-sm" onclick="downloadFeeReport()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.375rem;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            Download Report
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Class Selector Section -->
        <section class="class-selector">
            <div class="class-pill active" onclick="selectClass('all')">All Classes</div>
            <div class="class-pill" onclick="selectClass('class-9a')">Class 9-A</div>
            <div class="class-pill" onclick="selectClass('class-9b')">Class 9-B</div>
            <div class="class-pill" onclick="selectClass('class-10a')">Class 10-A</div>
            <div class="class-pill" onclick="selectClass('class-10b')">Class 10-B</div>
        </section>

        <!-- Fee Table Section -->
        <section class="fee-table-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Student Fee Status</h2>
                    <div class="filter-group">
                        <div class="search-container">
                            <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" placeholder="Search by student name..." class="search-input" id="studentSearch">
                        </div>
                        <div class="select-container">
                            <select class="filter-select" id="feeStatusFilter">
                                <option value="all">All Status</option>
                                <option value="paid">Fully Paid</option>
                                <option value="partial">Partially Paid</option>
                                <option value="unpaid">Unpaid</option>
                                <option value="overdue">Overdue</option>
                            </select>
                            <svg class="select-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="fee-table">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Class</th>
                                <th>Total Fees</th>
                                <th>Paid Amount</th>
                                <th>Due Amount</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Example Student 1 -->
                            <tr data-class="class-9a" data-status="paid">
                                <td>STD-2025-001</td>
                                <td>John Smith</td>
                                <td>Class 9-A</td>
                                <td>$1,250.00</td>
                                <td>$1,250.00</td>
                                <td>$0.00</td>
                                <td>
                                    <span class="status-badge status-paid">Paid</span>
                                </td>
                                <td>Feb 15, 2025</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="View Details" onclick="showFeeDetailsModal(1)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Download Receipt" onclick="downloadReceipt(1)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Example Student 2 -->
                            <tr data-class="class-9a" data-status="partial">
                                <td>STD-2025-002</td>
                                <td>Emily Johnson</td>
                                <td>Class 9-A</td>
                                <td>$1,250.00</td>
                                <td>$750.00</td>
                                <td>$500.00</td>
                                <td>
                                    <span class="status-badge status-partial">Partial</span>
                                </td>
                                <td>April 15, 2025</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="View Details" onclick="showFeeDetailsModal(2)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Send Reminder" onclick="sendSingleReminder(2)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Example Student 3 -->
                            <tr data-class="class-9b" data-status="unpaid">
                                <td>STD-2025-003</td>
                                <td>Michael Davis</td>
                                <td>Class 9-B</td>
                                <td>$1,250.00</td>
                                <td>$0.00</td>
                                <td>$1,250.00</td>
                                <td>
                                    <span class="status-badge status-unpaid">Unpaid</span>
                                </td>
                                <td>April 15, 2025</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="View Details" onclick="showFeeDetailsModal(3)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Send Reminder" onclick="sendSingleReminder(3)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Example Student 4 -->
                            <tr data-class="class-10a" data-status="overdue">
                                <td>STD-2025-004</td>
                                <td>Sarah Martinez</td>
                                <td>Class 10-A</td>
                                <td>$1,350.00</td>
                                <td>$350.00</td>
                                <td>$1,000.00</td>
                                <td>
                                    <span class="status-badge status-overdue">Overdue</span>
                                </td>
                                <td>Feb 15, 2025</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="View Details" onclick="showFeeDetailsModal(4)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Send Reminder" onclick="sendSingleReminder(4)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Example Student 5 -->
                            <tr data-class="class-10b" data-status="paid">
                                <td>STD-2025-005</td>
                                <td>Robert Wilson</td>
                                <td>Class 10-B</td>
                                <td>$1,350.00</td>
                                <td>$1,350.00</td>
                                <td>$0.00</td>
                                <td>
                                    <span class="status-badge status-paid">Paid</span>
                                </td>
                                <td>Feb 15, 2025</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="View Details" onclick="showFeeDetailsModal(5)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Download Receipt" onclick="downloadReceipt(5)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State (hidden by default) -->
                    <div class="empty-state" id="emptyState" style="display: none;">
                        <svg class="empty-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="empty-title">No Fee Records Found</h3>
                        <p class="empty-description">There are no fee records matching your current filters or search criteria.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Class Fee Summary Section -->
        <section class="fee-details-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Class Fee Summary</h2>
                    <button class="btn btn-primary btn-sm" onclick="downloadClassReport()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.375rem;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Download Report
                    </button>
                </div>
                <div class="card-body">
                    <div class="fee-details-grid">
                        <!-- Class 9-A Fee Details -->
                        <div class="fee-detail-card">
                            <div class="fee-detail-header">
                                <h3 class="fee-detail-title">Class 9-A</h3>
                                <div class="fee-detail-amount fee-amount-partial">75% Collected</div>
                            </div>
                            <div class="fee-detail-info">
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Total Students:</span>
                                    <span class="fee-info-value">25</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Fully Paid:</span>
                                    <span class="fee-info-value">18</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Partially Paid:</span>
                                    <span class="fee-info-value">5</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Unpaid:</span>
                                    <span class="fee-info-value">2</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Total Amount:</span>
                                    <span class="fee-info-value">$31,250.00</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Collected:</span>
                                    <span class="fee-info-value">$23,437.50</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Due:</span>
                                    <span class="fee-info-value">$7,812.50</span>
                                </div>
                            </div>
                            <div class="fee-detail-progress">
                                <div class="fee-progress-header">
                                    <span>Collection Progress</span>
                                    <span>75%</span>
                                </div>
                                <div class="fee-progress-bar">
                                    <div class="fee-progress-fill fee-progress-partial" style="width: 75%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Class 9-B Fee Details -->
                        <div class="fee-detail-card">
                            <div class="fee-detail-header">
                                <h3 class="fee-detail-title">Class 9-B</h3>
                                <div class="fee-detail-amount fee-amount-partial">82% Collected</div>
                            </div>
                            <div class="fee-detail-info">
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Total Students:</span>
                                    <span class="fee-info-value">24</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Fully Paid:</span>
                                    <span class="fee-info-value">19</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Partially Paid:</span>
                                    <span class="fee-info-value">3</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Unpaid:</span>
                                    <span class="fee-info-value">2</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Total Amount:</span>
                                    <span class="fee-info-value">$30,000.00</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Collected:</span>
                                    <span class="fee-info-value">$24,600.00</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Due:</span>
                                    <span class="fee-info-value">$5,400.00</span>
                                </div>
                            </div>
                            <div class="fee-detail-progress">
                                <div class="fee-progress-header">
                                    <span>Collection Progress</span>
                                    <span>82%</span>
                                </div>
                                <div class="fee-progress-bar">
                                    <div class="fee-progress-fill fee-progress-partial" style="width: 82%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Class 10-A Fee Details -->
                        <div class="fee-detail-card">
                            <div class="fee-detail-header">
                                <h3 class="fee-detail-title">Class 10-A</h3>
                                <div class="fee-detail-amount fee-amount-partial">68% Collected</div>
                            </div>
                            <div class="fee-detail-info">
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Total Students:</span>
                                    <span class="fee-info-value">26</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Fully Paid:</span>
                                    <span class="fee-info-value">17</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Partially Paid:</span>
                                    <span class="fee-info-value">6</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Unpaid:</span>
                                    <span class="fee-info-value">3</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Total Amount:</span>
                                    <span class="fee-info-value">$35,100.00</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Collected:</span>
                                    <span class="fee-info-value">$23,868.00</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Due:</span>
                                    <span class="fee-info-value">$11,232.00</span>
                                </div>
                            </div>
                            <div class="fee-detail-progress">
                                <div class="fee-progress-header">
                                    <span>Collection Progress</span>
                                    <span>68%</span>
                                </div>
                                <div class="fee-progress-bar">
                                    <div class="fee-progress-fill fee-progress-partial" style="width: 68%;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Class 10-B Fee Details -->
                        <div class="fee-detail-card">
                            <div class="fee-detail-header">
                                <h3 class="fee-detail-title">Class 10-B</h3>
                                <div class="fee-detail-amount fee-amount-full">92% Collected</div>
                            </div>
                            <div class="fee-detail-info">
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Total Students:</span>
                                    <span class="fee-info-value">23</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Fully Paid:</span>
                                    <span class="fee-info-value">18</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Partially Paid:</span>
                                    <span class="fee-info-value">4</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Unpaid:</span>
                                    <span class="fee-info-value">1</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Total Amount:</span>
                                    <span class="fee-info-value">$31,050.00</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Collected:</span>
                                    <span class="fee-info-value">$28,566.00</span>
                                </div>
                                <div class="fee-info-item">
                                    <span class="fee-info-label">Due:</span>
                                    <span class="fee-info-value">$2,484.00</span>
                                </div>
                            </div>
                            <div class="fee-detail-progress">
                                <div class="fee-progress-header">
                                    <span>Collection Progress</span>
                                    <span>92%</span>
                                </div>
                                <div class="fee-progress-bar">
                                    <div class="fee-progress-fill fee-progress-full" style="width: 92%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Fee Details Modal -->
<div id="feeDetailsModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Student Fee Details</h3>
            <button type="button" class="close-modal" onclick="closeFeeDetailsModal()">×</button>
        </div>
        <div class="modal-body" id="feeDetailsModalContent">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<!-- Send Fee Reminder Modal -->
<div class="modal-overlay" id="sendReminderModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Send Fee Reminders</h3>
            <span class="close-modal" onclick="closeReminderModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="reminderStudentSelection">Select Students</label>
                <select id="reminderStudentSelection" class="form-select">
                    <option value="all_unpaid">All Students with Unpaid Fees</option>
                    <option value="all_overdue">All Students with Overdue Fees</option>
                    <option value="selected">Selected Students Only</option>
                </select>
                    </div>
            <div class="form-group">
                <label for="reminderMessage">Reminder Message</label>
                <textarea id="reminderMessage" class="form-textarea" rows="4">This is a reminder that you have pending fee payments. Please clear your dues at the earliest.</textarea>
                    </div>
                    </div>
        <div class="modal-footer">
            <button class="btn btn-secondary close-modal" onclick="closeReminderModal()">Cancel</button>
            <button class="btn btn-primary" id="sendReminderBtn">Send Reminders</button>
                    </div>
                    </div>
                </div>

<script>
// Global variables
let classData = [];
let studentsData = [];
let selectedStudentIds = [];

document.addEventListener('DOMContentLoaded', function() {
    // Initialize modals
    const modals = document.querySelectorAll('.modal-overlay');
    const closeButtons = document.querySelectorAll('.close-modal');
    
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modalOverlay = this.closest('.modal-overlay');
            if (modalOverlay) {
                modalOverlay.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('studentSearch');
    searchInput.addEventListener('input', filterStudents);
    
    // Status filter
    const statusFilter = document.getElementById('feeStatusFilter');
    statusFilter.addEventListener('change', filterStudents);
    
    // Load initial data
    loadAssignedClasses();
    loadFeeStatistics();
    
    // Setup reminder button
    const sendReminderButton = document.getElementById('sendReminderBtn');
    if (sendReminderButton) {
        sendReminderButton.addEventListener('click', sendFeeReminders);
    }
});

// Load classes assigned to the teacher
function loadAssignedClasses() {
    fetch('fees_actions.php?action=get_assigned_classes')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            // Check if the response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // For debugging, let's get the text of the response
                return response.text().then(text => {
                    console.error('Received non-JSON response:', text);
                    throw new Error('Expected JSON response but got HTML or text');
                });
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                classData = data.classes;
                
                // Populate class pills
                const classPillsContainer = document.querySelector('.class-selector');
                classPillsContainer.innerHTML = '<div class="class-pill active" onclick="selectClass(\'all\')">All Classes</div>';
                
                classData.forEach(cls => {
                    const classPill = document.createElement('div');
                    classPill.className = 'class-pill';
                    classPill.setAttribute('onclick', `selectClass('class-${cls.id}-${cls.section_id}')`);
                    classPill.textContent = `${cls.name} ${cls.section_name}`;
                    classPillsContainer.appendChild(classPill);
                });
            } else {
                showError('Failed to load assigned classes: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('An error occurred while loading classes. Check the console for details.');
        });
}

// Load fee statistics for all assigned classes
function loadFeeStatistics(classId = null, sectionId = null) {
    // Show loading states
    document.querySelector('.fee-stat-total').textContent = '...';
    document.querySelector('.fee-stat-paid').textContent = '...';
    document.querySelector('.fee-stat-partial').textContent = '...';
    document.querySelector('.fee-stat-unpaid').textContent = '...';
    
    const tableBody = document.querySelector('.fee-table tbody');
    tableBody.innerHTML = `
        <tr>
            <td colspan="9" class="text-center">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Loading fee data...</p>
                </div>
            </td>
                        </tr>
    `;
    
    // Build query parameters
    let url = 'fees_actions.php?action=get_fee_statistics';
    if (classId) url += `&class_id=${classId}`;
    if (sectionId) url += `&section_id=${sectionId}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            // Check if the response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // For debugging, let's get the text of the response
                return response.text().then(text => {
                    console.error('Received non-JSON response:', text);
                    throw new Error('Expected JSON response but got HTML or text');
                });
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update global students data
                studentsData = data.students;
                
                // Update statistics
                const stats = data.statistics;
                document.querySelector('.fee-stat-total').textContent = stats.total_students;
                document.querySelector('.fee-stat-paid').textContent = stats.fully_paid;
                document.querySelector('.fee-stat-partial').textContent = stats.partially_paid;
                document.querySelector('.fee-stat-unpaid').textContent = stats.unpaid;
                
                // Update percentages
                const fullyPaidPercentage = stats.total_students > 0 ? ((stats.fully_paid / stats.total_students) * 100).toFixed(1) : 0;
                const partialPaidPercentage = stats.total_students > 0 ? ((stats.partially_paid / stats.total_students) * 100).toFixed(1) : 0;
                const unpaidPercentage = stats.total_students > 0 ? ((stats.unpaid / stats.total_students) * 100).toFixed(1) : 0;
                
                document.querySelectorAll('.fee-stat-secondary')[1].textContent = `${fullyPaidPercentage}% of total students`;
                document.querySelectorAll('.fee-stat-secondary')[2].textContent = `${partialPaidPercentage}% of total students`;
                document.querySelectorAll('.fee-stat-secondary')[3].textContent = `${unpaidPercentage}% of total students`;
                
                // Update reminder notice
                const overdueCount = stats.overdue || 0;
                const reminderElement = document.querySelector('.reminder-notice .reminder-text');
                if (reminderElement) {
                    reminderElement.textContent = `There are ${overdueCount} students with overdue fees. You can send reminders to help ensure timely payments.`;
                }
                
                // Populate student table
                populateStudentTable(data.students);
                
                // Update class fee summaries
                updateClassSummaries();
            } else {
                showError('Failed to load fee statistics: ' + data.message);
                tableBody.innerHTML = `<tr><td colspan="9" class="text-center">No fee data available</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('An error occurred while loading fee data. Check the console for details.');
            tableBody.innerHTML = `<tr><td colspan="9" class="text-center">Error loading data: ${error.message}</td></tr>`;
        });
}

// Populate student table with data
function populateStudentTable(students) {
    const tableBody = document.querySelector('.fee-table tbody');
    tableBody.innerHTML = '';
    
    if (students.length === 0) {
        document.getElementById('emptyState').style.display = 'flex';
        tableBody.style.display = 'none';
        return;
    }
    
    document.getElementById('emptyState').style.display = 'none';
    tableBody.style.display = '';
    
    students.forEach(student => {
        const row = document.createElement('tr');
        row.setAttribute('data-class', `class-${student.class_id}-${student.section_id}`);
        row.setAttribute('data-status', student.status);
        
        // Format currency
        const totalFees = formatCurrency(student.total_fees);
        const paidAmount = formatCurrency(student.paid_amount);
        const dueAmount = formatCurrency(student.due_amount);
        
        // Format status badge
        let statusBadge = '';
        switch (student.status) {
            case 'paid':
                statusBadge = '<span class="status-badge status-paid">Paid</span>';
                break;
            case 'partial':
                statusBadge = '<span class="status-badge status-partial">Partial</span>';
                break;
            case 'unpaid':
                statusBadge = '<span class="status-badge status-unpaid">Unpaid</span>';
                break;
            case 'overdue':
                statusBadge = '<span class="status-badge status-overdue">Overdue</span>';
                break;
        }
        
        // Determine action buttons based on status
        let actionButtons = `
            <button class="action-btn" title="View Details" onclick="showFeeDetailsModal(${student.user_id})">
                <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
            </button>
        `;
        
        if (student.status === 'paid') {
            actionButtons += `
                <button class="action-btn" title="Download Receipt" onclick="downloadReceipt(${student.user_id})">
                    <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </button>
            `;
        } else {
            actionButtons += `
                <button class="action-btn" title="Send Reminder" onclick="sendSingleReminder(${student.user_id})">
                    <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button>
            `;
        }
        
        // Create row
        row.innerHTML = `
            <td>${student.admission_number}</td>
            <td>${student.full_name}</td>
            <td>${student.class_name} ${student.section_name}</td>
            <td>${totalFees}</td>
            <td>${paidAmount}</td>
            <td>${dueAmount}</td>
            <td>${statusBadge}</td>
            <td>-</td>
            <td>
                <div class="action-buttons">
                    ${actionButtons}
                </div>
            </td>
        `;
        
        tableBody.appendChild(row);
    });
}

// Update class fee summaries
function updateClassSummaries() {
    // Group students by class
    const classSummaries = {};
    
    studentsData.forEach(student => {
        const classKey = `${student.class_id}-${student.section_id}`;
        if (!classSummaries[classKey]) {
            classSummaries[classKey] = {
                className: `${student.class_name} ${student.section_name}`,
                totalStudents: 0,
                fullyPaid: 0,
                partiallyPaid: 0,
                unpaid: 0,
                totalAmount: 0,
                collectedAmount: 0,
                dueAmount: 0
            };
        }
        
        const summary = classSummaries[classKey];
        summary.totalStudents++;
        summary.totalAmount += student.total_fees;
        summary.collectedAmount += student.paid_amount;
        summary.dueAmount += student.due_amount;
        
        switch (student.status) {
            case 'paid':
                summary.fullyPaid++;
                break;
            case 'partial':
            case 'overdue':
                summary.partiallyPaid++;
                break;
            case 'unpaid':
                summary.unpaid++;
                break;
        }
    });
    
    // Update summary cards
    const summaryContainer = document.querySelector('.fee-details-grid');
    if (!summaryContainer) return;
    
    summaryContainer.innerHTML = '';
    
    Object.values(classSummaries).forEach(summary => {
        const collectionPercentage = summary.totalAmount > 0
            ? Math.round((summary.collectedAmount / summary.totalAmount) * 100)
            : 0;
        
        const amountClass = collectionPercentage >= 90
            ? 'fee-amount-full'
            : (collectionPercentage >= 60 ? 'fee-amount-partial' : 'fee-amount-low');
        
        const progressClass = collectionPercentage >= 90
            ? 'fee-progress-full'
            : (collectionPercentage >= 60 ? 'fee-progress-partial' : 'fee-progress-low');
        
        const card = document.createElement('div');
        card.className = 'fee-detail-card';
        card.innerHTML = `
            <div class="fee-detail-header">
                <h3 class="fee-detail-title">${summary.className}</h3>
                <div class="fee-detail-amount ${amountClass}">${collectionPercentage}% Collected</div>
            </div>
            <div class="fee-detail-info">
                    <div class="fee-info-item">
                    <span class="fee-info-label">Total Students:</span>
                    <span class="fee-info-value">${summary.totalStudents}</span>
                    </div>
                    <div class="fee-info-item">
                    <span class="fee-info-label">Fully Paid:</span>
                    <span class="fee-info-value">${summary.fullyPaid}</span>
                    </div>
                    <div class="fee-info-item">
                    <span class="fee-info-label">Partially Paid:</span>
                    <span class="fee-info-value">${summary.partiallyPaid}</span>
                    </div>
                    <div class="fee-info-item">
                    <span class="fee-info-label">Unpaid:</span>
                    <span class="fee-info-value">${summary.unpaid}</span>
                    </div>
                    <div class="fee-info-item">
                    <span class="fee-info-label">Total Amount:</span>
                    <span class="fee-info-value">${formatCurrency(summary.totalAmount)}</span>
                </div>
                <div class="fee-info-item">
                    <span class="fee-info-label">Collected:</span>
                    <span class="fee-info-value">${formatCurrency(summary.collectedAmount)}</span>
                </div>
                <div class="fee-info-item">
                    <span class="fee-info-label">Due:</span>
                    <span class="fee-info-value">${formatCurrency(summary.dueAmount)}</span>
                </div>
                    </div>
                    <div class="fee-detail-progress">
                        <div class="fee-progress-header">
                    <span>Collection Progress</span>
                    <span>${collectionPercentage}%</span>
                        </div>
                        <div class="fee-progress-bar">
                    <div class="fee-progress-fill ${progressClass}" style="width: ${collectionPercentage}%;"></div>
                        </div>
                    </div>
        `;
        
        summaryContainer.appendChild(card);
    });
}

// Select class and filter students
function selectClass(classId) {
    // Update active class pill
    const classPills = document.querySelectorAll('.class-pill');
    classPills.forEach(pill => {
        pill.classList.remove('active');
        if (classId === 'all' && pill.textContent === 'All Classes') {
            pill.classList.add('active');
        } else if (classId !== 'all' && pill.getAttribute('onclick').includes(classId)) {
            pill.classList.add('active');
        }
    });
    
    // Filter table rows
    const rows = document.querySelectorAll('.fee-table tbody tr');
    rows.forEach(row => {
        if (classId === 'all' || row.getAttribute('data-class') === classId) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Check if we need to show empty state
    checkEmptyState();
    
    // If classId is specific, load data for just that class
    if (classId !== 'all') {
        const [, classIdNum, sectionIdNum] = classId.split('-');
        loadFeeStatistics(classIdNum, sectionIdNum);
    } else {
        loadFeeStatistics();
    }
}

// Filter students by search term and status
function filterStudents() {
    const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
    const statusFilter = document.getElementById('feeStatusFilter').value;
        
    const rows = document.querySelectorAll('.fee-table tbody tr');
    rows.forEach(row => {
        const studentName = row.cells[1].textContent.toLowerCase();
        const studentStatus = row.getAttribute('data-status');
        
        const matchesSearch = searchTerm === '' || studentName.includes(searchTerm);
        const matchesStatus = statusFilter === 'all' || studentStatus === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Check if we need to show empty state
    checkEmptyState();
}

// Check if there are visible rows, if not show empty state
function checkEmptyState() {
    const visibleRows = document.querySelectorAll('.fee-table tbody tr[style=""]').length;
    if (visibleRows === 0) {
        document.getElementById('emptyState').style.display = 'flex';
    } else {
        document.getElementById('emptyState').style.display = 'none';
    }
}

// Function to close fee details modal
function closeFeeDetailsModal() {
    const modal = document.getElementById('feeDetailsModal');
    modal.classList.remove('show');
    
    // Enable scrolling on body
    document.body.style.overflow = 'auto';
}

// Function to close reminder modal
function closeReminderModal() {
    const modal = document.getElementById('sendReminderModal');
    modal.classList.remove('show');
    
    // Enable scrolling on body
    document.body.style.overflow = 'auto';
}

// When the user clicks anywhere outside of the modal content, close it
window.onclick = function(event) {
    const feeModal = document.getElementById('feeDetailsModal');
    const reminderModal = document.getElementById('sendReminderModal');
    
    if (event.target == feeModal) {
        closeFeeDetailsModal();
    }
    
    if (event.target == reminderModal) {
        closeReminderModal();
    }
};

// Show fee details modal for a student
function showFeeDetailsModal(studentId) {
    const modal = document.getElementById('feeDetailsModal');
    const modalContent = document.getElementById('feeDetailsModalContent');
    
    // Disable scrolling on body
    document.body.style.overflow = 'hidden';
    
    // Show modal with loading state
    modal.classList.add('show');
    modalContent.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Loading fee details...</p>
                </div>
    `;
    
    // Fetch student fee details
    fetch(`fees_actions.php?action=get_student_fee_details&student_id=${studentId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            // Check if the response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // For debugging, let's get the text of the response
                return response.text().then(text => {
                    console.error('Received non-JSON response:', text);
                    throw new Error('Expected JSON response but got HTML or text');
                });
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const student = data.student_info;
                const summary = data.summary;
                const structures = data.fee_structures;
                const payments = data.payment_history;
                const pendingProofs = data.pending_proofs || [];
                
                // Format payment status
                let statusColor;
                let statusLabel;
                
                if (summary.paid_percentage >= 100) {
                    statusColor = 'var(--color-success)';
                    statusLabel = 'Fully Paid';
                } else if (summary.paid_percentage > 0) {
                    statusColor = 'var(--color-warning)';
                    statusLabel = 'Partially Paid';
                } else {
                    statusColor = 'var(--color-danger)';
                    statusLabel = 'Unpaid';
                }
                
                // Update modal title with student name
                document.querySelector('.modal-title').textContent = `${student.full_name} - Fee Details`;
                
                // Create content HTML
                let html = `
                    <div class="student-fee-details">
                        <div class="student-info-header">
                            <div class="student-meta">
                                <span>ID: ${student.admission_number}</span>
                                <span>Class: ${student.class_name} ${student.section_name}</span>
                            </div>
                        </div>
                        
                        <div class="fee-summary-card">
                            <div class="fee-summary-row">
                                <div class="fee-summary-item">
                                    <div class="summary-label">Total Fees</div>
                                    <div class="summary-value">${formatCurrency(summary.total_fees)}</div>
                            </div>
                                <div class="fee-summary-item">
                                    <div class="summary-label">Paid Amount</div>
                                    <div class="summary-value">${formatCurrency(summary.paid_amount)}</div>
                        </div>
                                <div class="fee-summary-item">
                                    <div class="summary-label">Due Amount</div>
                                    <div class="summary-value">${formatCurrency(summary.due_amount)}</div>
                    </div>
                                <div class="fee-summary-item">
                                    <div class="summary-label">Status</div>
                                    <div class="summary-value" style="color: ${statusColor};">${statusLabel}</div>
                </div>
            </div>
                            
                            <div class="payment-progress">
                                <div class="progress-label">
                                    <span>Payment Progress</span>
                                    <span>${summary.paid_percentage}%</span>
        </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: ${summary.paid_percentage}%; background-color: ${statusColor};"></div>
        </div>
    </div>
</div>

                        <div class="detail-tabs">
                            <div class="tab-header">
                                <div class="tab-button active" data-tab="structures">Fee Structures</div>
                                <div class="tab-button" data-tab="payments">Payment History</div>
                                <div class="tab-button ${pendingProofs.length > 0 ? 'notification' : ''}" data-tab="proofs">
                                    Payment Proofs
                                    ${pendingProofs.length > 0 ? `<span class="badge">${pendingProofs.length}</span>` : ''}
        </div>
                </div>
                
                            <div class="tab-content active" id="structures-tab">
                                <div class="fee-structures-list">
                `;
                
                // Add fee structures
                if (structures.length === 0) {
                    html += `<div class="empty-info">No fee structures assigned</div>`;
                } else {
                    structures.forEach(structure => {
                        // Format status badge
                        let statusBadge = '';
                        switch (structure.status) {
                            case 'paid':
                                statusBadge = '<span class="status-badge status-paid">Paid</span>';
                                break;
                            case 'partial':
                                statusBadge = '<span class="status-badge status-partial">Partial</span>';
                                break;
                            case 'unpaid':
                                statusBadge = '<span class="status-badge status-unpaid">Unpaid</span>';
                                break;
                            case 'overdue':
                                statusBadge = '<span class="status-badge status-overdue">Overdue</span>';
                                break;
                            case 'pending':
                                statusBadge = '<span class="status-badge status-pending">Pending Verification</span>';
                                break;
                        }
                        
                        const dueDate = new Date(structure.due_date).toLocaleDateString();
                        
                        html += `
                            <div class="fee-structure-item">
                                <div class="structure-header">
                                    <div class="structure-title">${structure.title}</div>
                                    <div class="structure-status">${statusBadge}</div>
                </div>
                
                                <div class="structure-details">
                                    <div class="structure-meta">
                                        <div class="meta-item">
                                            <span class="meta-label">Due Date:</span>
                                            <span class="meta-value">${dueDate}</span>
                    </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Amount:</span>
                                            <span class="meta-value">${formatCurrency(structure.amount)}</span>
                    </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Paid:</span>
                                            <span class="meta-value">${formatCurrency(structure.paid_amount)}</span>
                    </div>
                                        <div class="meta-item">
                                            <span class="meta-label">Remaining:</span>
                                            <span class="meta-value">${formatCurrency(structure.remaining_amount)}</span>
                </div>
                </div>
                
                                    <div class="structure-components">
                                        <h4 class="components-title">Components</h4>
                        `;
                        
                        if (structure.components && structure.components.length > 0) {
                            html += `<div class="component-list">`;
                            structure.components.forEach(component => {
                                html += `
                                    <div class="component-item">
                                        <span class="component-name">${component.name}</span>
                                        <span class="component-amount">${formatCurrency(component.amount)}</span>
                </div>
                                `;
                            });
                            html += `</div>`;
                        } else {
                            html += `<div class="empty-info">No components defined</div>`;
                        }
                        
                        html += `
                    </div>
                </div>
        </div>
                        `;
                    });
                }
                
                html += `
    </div>
</div>

                <div class="tab-content" id="payments-tab">
                    <table class="payment-history-table">
                        <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Date</th>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Reference</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                // Add payment history
                if (payments.length === 0) {
                    html += `<tr><td colspan="7" class="text-center">No payment records found</td></tr>`;
                } else {
                    payments.forEach(payment => {
                        const paymentDate = new Date(payment.payment_date).toLocaleDateString();
                        const receiptNumber = `RCPT-${payment.id.toString().padStart(5, '0')}`;
                        
                        let statusBadge = '';
                        switch (payment.status) {
                            case 'paid':
                                statusBadge = '<span class="status-badge status-paid">Verified</span>';
                                break;
                            case 'partial':
                                statusBadge = '<span class="status-badge status-partial">Partial</span>';
                                break;
                            case 'pending':
                                statusBadge = '<span class="status-badge status-pending">Pending</span>';
                                break;
                        }
                        
                        html += `
                            <tr>
                                <td>${receiptNumber}</td>
                                <td>${paymentDate}</td>
                                <td>${payment.fee_title}</td>
                                <td>${formatCurrency(payment.amount_paid)}</td>
                                <td>${payment.payment_method || 'Cash'}</td>
                                <td>${payment.reference_number || '-'}</td>
                                <td>${statusBadge}</td>
                            </tr>
                        `;
                    });
                }
                
                html += `
                        </tbody>
                    </table>
                </div>
                
                <div class="tab-content" id="proofs-tab">
                    <div class="proof-verification-section">
                `;
                
                // Add payment proofs that need verification
                if (pendingProofs.length === 0) {
                    html += `<div class="empty-info">No payment proofs pending verification</div>`;
            } else {
                    html += `
                        <div class="alert alert-info">
                            <p>There are ${pendingProofs.length} payment proofs waiting for your verification. Please review them and mark as verified or rejected.</p>
                        </div>
                        
                        <div class="proof-list">
                    `;
                    
                    pendingProofs.forEach(proof => {
                        const uploadDate = new Date(proof.upload_date).toLocaleDateString();
                        const paymentDate = new Date(proof.payment_date).toLocaleDateString();
                        
                        html += `
                            <div class="proof-item" id="proof-${proof.id}">
                                <div class="proof-header">
                                    <div class="proof-title">
                                        <h4>${proof.fee_title}</h4>
                                        <div class="proof-details">
                                            <span>Amount: ${formatCurrency(proof.amount_paid)}</span>
                                            <span>Uploaded: ${uploadDate}</span>
                                            <span>Payment Date: ${paymentDate}</span>
                                        </div>
                                    </div>
                                    <div class="proof-status">
                                        <span class="status-badge status-pending">Pending Verification</span>
                                    </div>
                                </div>
                                
                                <div class="proof-content">
                                    <div class="proof-image">
                                        <img src="../../uploads/payment_proofs/${proof.proof_image}" alt="Payment Proof" onclick="openImageInNewTab('../../uploads/payment_proofs/${proof.proof_image}')">
                    </div>
                                    <div class="proof-info">
                                        <div class="info-row">
                                            <span class="info-label">Payment Method:</span>
                                            <span class="info-value">${proof.payment_method || 'Not specified'}</span>
                </div>
                                        <div class="info-row">
                                            <span class="info-label">Reference Number:</span>
                                            <span class="info-value">${proof.reference_number || 'Not provided'}</span>
                                        </div>
                                        <div class="info-row">
                                            <span class="info-label">Student Remarks:</span>
                                            <span class="info-value">${proof.remarks || 'No remarks'}</span>
                                        </div>
                                        
                                        <div class="verification-form">
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label for="verification-remarks-${proof.id}">Verification Remarks</label>
                                                    <textarea id="verification-remarks-${proof.id}" class="form-textarea" placeholder="Add any remarks about this payment proof..."></textarea>
                                                </div>
                                            </div>
                                            <div class="verification-actions">
                                                <button class="btn btn-success btn-sm" onclick="verifyPaymentProof(${proof.id}, ${proof.fee_payment_id}, 'verified')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="action-icon">
                                                        <path d="M20 6L9 17l-5-5"></path>
                                                    </svg>
                                                    Verify Payment
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="verifyPaymentProof(${proof.id}, ${proof.fee_payment_id}, 'rejected')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="action-icon">
                                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                                    </svg>
                                                    Reject Payment
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    html += `</div>`; // Close proof-list
                }
                
                html += `
                    </div>
                </div>
                </div>
            `;
            
                modalContent.innerHTML = html;
                
                // Tab switching functionality
                const tabButtons = document.querySelectorAll('.tab-button');
                const tabContents = document.querySelectorAll('.tab-content');
                
                tabButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const tab = this.getAttribute('data-tab');
                        
                        // Update active button
                        tabButtons.forEach(btn => btn.classList.remove('active'));
                        this.classList.add('active');
                        
                        // Update active content
                        tabContents.forEach(content => content.classList.remove('active'));
                        document.getElementById(`${tab}-tab`).classList.add('active');
                    });
                });
            } else {
                modalContent.innerHTML = `
                    <div class="error-message">
                        <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p>${data.message || 'Failed to load student fee details'}</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalContent.innerHTML = `
                <div class="error-message">
                    <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>An error occurred while loading fee details: ${error.message}</p>
                </div>
            `;
        });
}

// Function to open an image in a new tab
function openImageInNewTab(imageSrc) {
    window.open(imageSrc, '_blank');
}

// Function to verify or reject a payment proof
function verifyPaymentProof(proofId, paymentId, status) {
    if (!confirm(`Are you sure you want to ${status === 'verified' ? 'verify' : 'reject'} this payment proof?`)) {
        return;
    }
    
    const remarks = document.getElementById(`verification-remarks-${proofId}`).value;
    
    // Disable buttons to prevent double submission
    const proofItem = document.getElementById(`proof-${proofId}`);
    const buttons = proofItem.querySelectorAll('button');
    buttons.forEach(btn => btn.disabled = true);
    
    // Show loading state
    proofItem.classList.add('loading');
    
    // Send verification request
    fetch('fees_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=verify_payment_proof&proof_id=${proofId}&payment_id=${paymentId}&status=${status}&remarks=${encodeURIComponent(remarks)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Update the UI to show the verification result
            const proofHeader = proofItem.querySelector('.proof-status');
            proofHeader.innerHTML = `<span class="status-badge status-${status === 'verified' ? 'paid' : 'rejected'}">${status === 'verified' ? 'Verified' : 'Rejected'}</span>`;
            
            // Disable the verification form
            const verificationForm = proofItem.querySelector('.verification-form');
            verificationForm.innerHTML = `
                <div class="alert alert-${status === 'verified' ? 'success' : 'danger'}">
                    <p>Payment proof has been ${status}. ${remarks ? `Remarks: ${remarks}` : ''}</p>
                </div>
            `;
            
            // Remove the loading state
            proofItem.classList.remove('loading');
            
            // Show success message
            alert(`Payment proof has been ${status === 'verified' ? 'verified' : 'rejected'} successfully.`);
        } else {
            // Re-enable buttons
            buttons.forEach(btn => btn.disabled = false);
            
            // Remove loading state
            proofItem.classList.remove('loading');
            
            // Show error message
            alert(`Error: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Re-enable buttons
        buttons.forEach(btn => btn.disabled = false);
        
        // Remove loading state
        proofItem.classList.remove('loading');
        
        // Show error message
        alert(`An error occurred: ${error.message}`);
    });
}

// Show send reminder modal
function showSendReminderModal() {
    document.getElementById('sendReminderModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Send reminder to a single student
function sendSingleReminder(studentId) {
    selectedStudentIds = [studentId];
    showSendReminderModal();
}

// Send fee reminders
function sendFeeReminders() {
    const reminderType = document.getElementById('reminderStudentSelection').value;
    const message = document.getElementById('reminderMessage').value;
    
    let studentIds = [];
    
    if (reminderType === 'selected') {
        studentIds = selectedStudentIds;
    } else if (reminderType === 'all_unpaid') {
        studentIds = studentsData
            .filter(student => student.status === 'unpaid' || student.status === 'partial')
            .map(student => student.user_id);
    } else if (reminderType === 'all_overdue') {
        studentIds = studentsData
            .filter(student => student.status === 'overdue')
            .map(student => student.user_id);
    }
    
    if (studentIds.length === 0) {
        alert('No students selected for reminders');
        return;
    }
    
    // Send reminders
    const sendReminderBtn = document.getElementById('sendReminderBtn');
    sendReminderBtn.disabled = true;
    sendReminderBtn.innerHTML = 'Sending...';
    
    fetch('fees_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=send_fee_reminder&message=${encodeURIComponent(message)}&student_ids[]=${studentIds.join('&student_ids[]=')}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        sendReminderBtn.disabled = false;
        sendReminderBtn.innerHTML = 'Send Reminders';
        
        if (data.success) {
            alert(data.message);
            document.getElementById('sendReminderModal').style.display = 'none';
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        sendReminderBtn.disabled = false;
        sendReminderBtn.innerHTML = 'Send Reminders';
        alert('An error occurred while sending reminders. Check the console for details.');
    });
}

// Download fee receipt
function downloadReceipt(studentId) {
    // Find payments for this student
    const student = studentsData.find(s => s.user_id === studentId);
    if (!student) {
        alert('Student not found');
        return;
    }
    
    // Fetch payment details first
    fetch(`fees_actions.php?action=get_student_fee_details&student_id=${studentId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            // Check if the response is JSON
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                // For debugging, let's get the text of the response
                return response.text().then(text => {
                    console.error('Received non-JSON response:', text);
                    throw new Error('Expected JSON response but got HTML or text');
                });
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success && data.payment_history && data.payment_history.length > 0) {
                // Get the latest payment ID
                const latestPayment = data.payment_history[0];
                
                // Open receipt in new window
                window.open(`../admin/dashboard/fee_receipt.php?payment_id=${latestPayment.id}`, '_blank');
                } else {
                alert('No payment records found for this student');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while fetching payment details. Check the console for details.');
        });
}

// Download class fee report
function downloadClassReport() {
    alert('Report downloading functionality will be implemented in a future update.');
}

// Download overall fee report
function downloadFeeReport() {
    alert('Report downloading functionality will be implemented in a future update.');
}

// Format currency value
function formatCurrency(value) {
    return '₹' + parseFloat(value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Show error message
function showError(message) {
    alert(message);
}
</script>
</body>
</html>