<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Leave Requests Management</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/leavemanage.css">
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
            <h1 class="header-title">Leave Requests Management</h1>
            <span class="header-date"><?php echo date('F j, Y'); ?></span>
        </header>

        <main class="dashboard-content">
            <!-- Tabs Navigation -->
            <div class="tabs-container">
                <div class="tabs">
                    <div class="tab active" data-tab="pending-requests">Pending Requests</div>
                    <div class="tab" data-tab="approved-leaves">Approved Leaves</div>
                    <div class="tab" data-tab="rejected-leaves">Rejected Leaves</div>
                </div>
            </div>

            <!-- Pending Requests Tab -->
            <div class="tab-content active" id="pending-requests">
            <div class="search-filter-bar">
                    <div class="search-container">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search by employee name, ID or department...">
                    </div>
                    <div class="filter-container">
                        <select class="form-select">
                            <option value="">All Departments</option>
                            <option value="teaching">Teaching</option>
                            <option value="administrative">Administrative</option>
                            <option value="operations">Operations</option>
                            <option value="it">IT</option>
                            <option value="finance">Finance</option>
                        </select>
                        <select class="form-select">
                            <option value="">All Leave Types</option>
                            <option value="annual">Annual Leave</option>
                            <option value="sick">Sick Leave</option>
                            <option value="personal">Personal Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="bereavement">Bereavement Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                        </select>
                        <select class="form-select">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="days-asc">Duration (Low to High)</option>
                            <option value="days-desc">Duration (High to Low)</option>
                        </select>
                    </div>
                </div>

                <div class="leave-request-grid">
                    <!-- Pending Leave Request 1 -->
                    <div class="leave-request-card">
                        <div class="leave-request-header">
                            <h3 class="leave-request-title">Leave Request #LR-2345</h3>
                            <div class="leave-request-employee">
                                <div class="employee-avatar">JS</div>
                                <div class="employee-details">
                                    <div class="employee-name">John Smith</div>
                                    <div class="employee-position">Mathematics Teacher</div>
                                </div>
                            </div>
                            <div class="leave-status">
                                <span class="status-tag status-pending">Pending</span>
                            </div>
                        </div>
                        <div class="leave-request-body">
                            <div class="leave-request-details">
                                <div class="leave-request-detail">
                                    <span class="detail-label">Start Date:</span>
                                    <span class="detail-value">Mar 25, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">End Date:</span>
                                    <span class="detail-value">Mar 28, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">4 Days</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Requested On:</span>
                                    <span class="detail-value">Mar 15, 2025</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-request-footer">
                            <div class="leave-type">
                                <span class="leave-type-badge leave-type-annual">Annual Leave</span>
                            </div>
                            <div class="leave-request-actions">
                                <button class="leave-request-action" onclick="viewLeaveDetails(2345)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="leave-request-action approve" onclick="approveLeave(2345)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </button>
                                <button class="leave-request-action reject" onclick="rejectLeave(2345)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Leave Request 2 -->
                    <div class="leave-request-card">
                        <div class="leave-request-header">
                            <h3 class="leave-request-title">Leave Request #LR-2346</h3>
                            <div class="leave-request-employee">
                                <div class="employee-avatar">EJ</div>
                                <div class="employee-details">
                                    <div class="employee-name">Emily Johnson</div>
                                    <div class="employee-position">Science Teacher</div>
                                </div>
                            </div>
                            <div class="leave-status">
                                <span class="status-tag status-pending">Pending</span>
                            </div>
                        </div>
                        <div class="leave-request-body">
                            <div class="leave-request-details">
                                <div class="leave-request-detail">
                                    <span class="detail-label">Start Date:</span>
                                    <span class="detail-value">Mar 20, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">End Date:</span>
                                    <span class="detail-value">Mar 22, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">3 Days</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Requested On:</span>
                                    <span class="detail-value">Mar 13, 2025</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-request-footer">
                            <div class="leave-type">
                                <span class="leave-type-badge leave-type-sick">Sick Leave</span>
                            </div>
                            <div class="leave-request-actions">
                                <button class="leave-request-action" onclick="viewLeaveDetails(2346)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="leave-request-action approve" onclick="approveLeave(2346)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </button>
                                <button class="leave-request-action reject" onclick="rejectLeave(2346)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Leave Request 3 -->
                    <div class="leave-request-card">
                        <div class="leave-request-header">
                            <h3 class="leave-request-title">Leave Request #LR-2347</h3>
                            <div class="leave-request-employee">
                                <div class="employee-avatar">MB</div>
                                <div class="employee-details">
                                    <div class="employee-name">Michael Brown</div>
                                    <div class="employee-position">English Teacher</div>
                                </div>
                            </div>
                            <div class="leave-status">
                                <span class="status-tag status-pending">Pending</span>
                            </div>
                        </div>
                        <div class="leave-request-body">
                            <div class="leave-request-details">
                                <div class="leave-request-detail">
                                    <span class="detail-label">Start Date:</span>
                                    <span class="detail-value">Apr 05, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">End Date:</span>
                                    <span class="detail-value">Apr 09, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">5 Days</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Requested On:</span>
                                    <span class="detail-value">Mar 14, 2025</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-request-footer">
                            <div class="leave-type">
                                <span class="leave-type-badge leave-type-personal">Personal Leave</span>
                            </div>
                            <div class="leave-request-actions">
                                <button class="leave-request-action" onclick="viewLeaveDetails(2347)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="leave-request-action approve" onclick="approveLeave(2347)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </button>
                                <button class="leave-request-action reject" onclick="rejectLeave(2347)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Leave Request 4 -->
                    <div class="leave-request-card">
                        <div class="leave-request-header">
                            <h3 class="leave-request-title">Leave Request #LR-2348</h3>
                            <div class="leave-request-employee">
                                <div class="employee-avatar">SW</div>
                                <div class="employee-details">
                                    <div class="employee-name">Sarah Wilson</div>
                                    <div class="employee-position">History Teacher</div>
                                </div>
                            </div>
                            <div class="leave-status">
                                <span class="status-tag status-pending">Pending</span>
                            </div>
                        </div>
                        <div class="leave-request-body">
                            <div class="leave-request-details">
                                <div class="leave-request-detail">
                                    <span class="detail-label">Start Date:</span>
                                    <span class="detail-value">Mar 18, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">End Date:</span>
                                    <span class="detail-value">Mar 19, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">2 Days</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Requested On:</span>
                                    <span class="detail-value">Mar 16, 2025</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-request-footer">
                            <div class="leave-type">
                                <span class="leave-type-badge leave-type-personal">Personal Leave</span>
                            </div>
                            <div class="leave-request-actions">
                                <button class="leave-request-action" onclick="viewLeaveDetails(2348)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="leave-request-action approve" onclick="approveLeave(2348)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </button>
                                <button class="leave-request-action reject" onclick="rejectLeave(2348)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Leave Request 5 -->
                    <div class="leave-request-card">
                        <div class="leave-request-header">
                            <h3 class="leave-request-title">Leave Request #LR-2349</h3>
                            <div class="leave-request-employee">
                                <div class="employee-avatar">JT</div>
                                <div class="employee-details">
                                    <div class="employee-name">Jennifer Taylor</div>
                                    <div class="employee-position">Chemistry Teacher</div>
                                </div>
                            </div>
                            <div class="leave-status">
                                <span class="status-tag status-pending">Pending</span>
                            </div>
                        </div>
                        <div class="leave-request-body">
                            <div class="leave-request-details">
                                <div class="leave-request-detail">
                                    <span class="detail-label">Start Date:</span>
                                    <span class="detail-value">Mar 30, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">End Date:</span>
                                    <span class="detail-value">Apr 10, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">12 Days</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Requested On:</span>
                                    <span class="detail-value">Mar 10, 2025</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-request-footer">
                            <div class="leave-type">
                                <span class="leave-type-badge leave-type-unpaid">Unpaid Leave</span>
                            </div>
                            <div class="leave-request-actions">
                                <button class="leave-request-action" onclick="viewLeaveDetails(2349)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="leave-request-action approve" onclick="approveLeave(2349)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </button>
                                <button class="leave-request-action reject" onclick="rejectLeave(2349)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Leave Request 6 -->
                    <div class="leave-request-card">
                        <div class="leave-request-header">
                            <h3 class="leave-request-title">Leave Request #LR-2350</h3>
                            <div class="leave-request-employee">
                                <div class="employee-avatar">AW</div>
                                <div class="employee-details">
                                    <div class="employee-name">Amanda White</div>
                                    <div class="employee-position">Computer Science Teacher</div>
                                </div>
                            </div>
                            <div class="leave-status">
                                <span class="status-tag status-pending">Pending</span>
                            </div>
                        </div>
                        <div class="leave-request-body">
                            <div class="leave-request-details">
                                <div class="leave-request-detail">
                                    <span class="detail-label">Start Date:</span>
                                    <span class="detail-value">Mar 22, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">End Date:</span>
                                    <span class="detail-value">Mar 23, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">2 Days</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Requested On:</span>
                                    <span class="detail-value">Mar 16, 2025</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-request-footer">
                            <div class="leave-type">
                                <span class="leave-type-badge leave-type-annual">Annual Leave</span>
                            </div>
                            <div class="leave-request-actions">
                                <button class="leave-request-action" onclick="viewLeaveDetails(2350)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="leave-request-action approve" onclick="approveLeave(2350)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </button>
                                <button class="leave-request-action reject" onclick="rejectLeave(2350)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pagination">
                    <div class="pagination-info">
                        Showing 1 to 6 of 18 pending requests
                    </div>
                    <div class="pagination-buttons">
                        <button class="pagination-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        <button class="pagination-button active">1</button>
                        <button class="pagination-button">2</button>
                        <button class="pagination-button">3</button>
                        <button class="pagination-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Approved Leaves Tab -->
            <div class="tab-content" id="approved-leaves">
                <div class="search-filter-bar">
                    <div class="search-container">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search by employee name, ID or department...">
                    </div>
                    <div class="filter-container">
                        <select class="form-select">
                            <option value="">All Departments</option>
                            <option value="teaching">Teaching</option>
                            <option value="administrative">Administrative</option>
                            <option value="operations">Operations</option>
                            <option value="it">IT</option>
                            <option value="finance">Finance</option>
                        </select>
                        <select class="form-select">
                            <option value="">All Leave Types</option>
                            <option value="annual">Annual Leave</option>
                            <option value="sick">Sick Leave</option>
                            <option value="personal">Personal Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="bereavement">Bereavement Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                        </select>
                        <select class="form-select">
                            <option value="">Date Approved</option>
                            <option value="today">Today</option>
                            <option value="this-week">This Week</option>
                            <option value="this-month">This Month</option>
                            <option value="last-month">Last Month</option>
                        </select>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Approved Leave Requests</h2>
                            <p class="card-subtitle">All leave requests that have been approved</p>
                        </div>
                        <div>
                            <button class="button button-sm">
                                <svg class="button-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Export
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Employee</th>
                                        <th>Leave Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Duration</th>
                                        <th>Approved By</th>
                                        <th>Approved On</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>LR-2340</td>
                                        <td>Robert Davis</td>
                                        <td><span class="leave-type-badge leave-type-personal">Personal</span></td>
                                        <td>Mar 12, 2025</td>
                                        <td>Mar 14, 2025</td>
                                        <td>3 Days</td>
                                        <td>Admin Jones</td>
                                        <td>Mar 10, 2025</td>
                                        <td>
                                            <div class="actions-dropdown">
                                                <button class="actions-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="12" cy="5" r="1"></circle>
                                                        <circle cx="12" cy="19" r="1"></circle>
                                                    </svg>
                                                </button>
                                                <div class="actions-menu">
                                                    <div class="actions-item" onclick="viewLeaveDetails(2340)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    View Details
                                                </div>
                                                <div class="actions-item">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                        <polyline points="7 10 12 15 17 10"></polyline>
                                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                                    </svg>
                                                    Download PDF
                                                </div>
                                                <div class="actions-item danger">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M10 11V6"></path>
                                                        <path d="M14 11V6"></path>
                                                        <path d="M4 7h16"></path>
                                                        <path d="M6 7v13a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7"></path>
                                                        <path d="M9 4h6"></path>
                                                    </svg>
                                                    Cancel Leave
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>LR-2338</td>
                                        <td>Lisa Thomas</td>
                                        <td><span class="leave-type-badge leave-type-sick">Sick</span></td>
                                        <td>Mar 08, 2025</td>
                                        <td>Mar 09, 2025</td>
                                        <td>2 Days</td>
                                        <td>Admin Jones</td>
                                        <td>Mar 07, 2025</td>
                                        <td>
                                            <div class="actions-dropdown">
                                                <button class="actions-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="12" cy="5" r="1"></circle>
                                                        <circle cx="12" cy="19" r="1"></circle>
                                                    </svg>
                                                </button>
                                                <div class="actions-menu">
                                                    <div class="actions-item" onclick="viewLeaveDetails(2338)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                            <polyline points="7 10 12 15 17 10"></polyline>
                                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                                        </svg>
                                                        Download PDF
                                                    </div>
                                                    <div class="actions-item danger">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M10 11V6"></path>
                                                            <path d="M14 11V6"></path>
                                                            <path d="M4 7h16"></path>
                                                            <path d="M6 7v13a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7"></path>
                                                            <path d="M9 4h6"></path>
                                                        </svg>
                                                        Cancel Leave
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>LR-2336</td>
                                        <td>David Miller</td>
                                        <td><span class="leave-type-badge leave-type-annual">Annual</span></td>
                                        <td>Mar 20, 2025</td>
                                        <td>Mar 24, 2025</td>
                                        <td>5 Days</td>
                                        <td>Admin Smith</td>
                                        <td>Mar 05, 2025</td>
                                        <td>
                                            <div class="actions-dropdown">
                                                <button class="actions-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="12" cy="5" r="1"></circle>
                                                        <circle cx="12" cy="19" r="1"></circle>
                                                    </svg>
                                                </button>
                                                <div class="actions-menu">
                                                    <div class="actions-item" onclick="viewLeaveDetails(2336)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                            <polyline points="7 10 12 15 17 10"></polyline>
                                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                                        </svg>
                                                        Download PDF
                                                    </div>
                                                    <div class="actions-item danger">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M10 11V6"></path>
                                                            <path d="M14 11V6"></path>
                                                            <path d="M4 7h16"></path>
                                                            <path d="M6 7v13a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7"></path>
                                                            <path d="M9 4h6"></path>
                                                        </svg>
                                                        Cancel Leave
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>LR-2334</td>
                                        <td>James Anderson</td>
                                        <td><span class="leave-type-badge leave-type-bereavement">Bereavement</span></td>
                                        <td>Mar 02, 2025</td>
                                        <td>Mar 04, 2025</td>
                                        <td>3 Days</td>
                                        <td>Admin Wilson</td>
                                        <td>Mar 01, 2025</td>
                                        <td>
                                            <div class="actions-dropdown">
                                                <button class="actions-button">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <circle cx="12" cy="12" r="1"></circle>
                                                        <circle cx="12" cy="5" r="1"></circle>
                                                        <circle cx="12" cy="19" r="1"></circle>
                                                    </svg>
                                                </button>
                                                <div class="actions-menu">
                                                    <div class="actions-item" onclick="viewLeaveDetails(2334)">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                        View Details
                                                    </div>
                                                    <div class="actions-item">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                            <polyline points="7 10 12 15 17 10"></polyline>
                                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                                        </svg>
                                                        Download PDF
                                                    </div>
                                                    <div class="actions-item danger">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M10 11V6"></path>
                                                            <path d="M14 11V6"></path>
                                                            <path d="M4 7h16"></path>
                                                            <path d="M6 7v13a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V7"></path>
                                                            <path d="M9 4h6"></path>
                                                        </svg>
                                                        Cancel Leave
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="pagination">
                        <div class="pagination-info">
                            Showing 1 to 4 of 22 approved leaves
                        </div>
                        <div class="pagination-buttons">
                            <button class="pagination-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                            </button>
                            <button class="pagination-button active">1</button>
                            <button class="pagination-button">2</button>
                            <button class="pagination-button">3</button>
                            <button class="pagination-button">4</button>
                            <button class="pagination-button">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rejected Leaves Tab -->
            <div class="tab-content" id="rejected-leaves">
                <div class="search-filter-bar">
                    <div class="search-container">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" class="search-input" placeholder="Search by employee name, ID or department...">
                    </div>
                    <div class="filter-container">
                        <select class="form-select">
                            <option value="">All Departments</option>
                            <option value="teaching">Teaching</option>
                            <option value="administrative">Administrative</option>
                            <option value="operations">Operations</option>
                            <option value="it">IT</option>
                            <option value="finance">Finance</option>
                        </select>
                        <select class="form-select">
                            <option value="">All Leave Types</option>
                            <option value="annual">Annual Leave</option>
                            <option value="sick">Sick Leave</option>
                            <option value="personal">Personal Leave</option>
                            <option value="maternity">Maternity Leave</option>
                            <option value="bereavement">Bereavement Leave</option>
                            <option value="unpaid">Unpaid Leave</option>
                        </select>
                        <select class="form-select">
                            <option value="">Date Rejected</option>
                            <option value="today">Today</option>
                            <option value="this-week">This Week</option>
                            <option value="this-month">This Month</option>
                            <option value="last-month">Last Month</option>
                        </select>
                    </div>
                </div>

                <div class="leave-request-grid">
                    <!-- Rejected Leave Request 1 -->
                    <div class="leave-request-card">
                        <div class="leave-request-header">
                            <h3 class="leave-request-title">Leave Request #LR-2344</h3>
                            <div class="leave-request-employee">
                                <div class="employee-avatar">RD</div>
                                <div class="employee-details">
                                    <div class="employee-name">Richard Davis</div>
                                    <div class="employee-position">Physics Teacher</div>
                                </div>
                            </div>
                            <div class="leave-status">
                                <span class="status-tag status-rejected">Rejected</span>
                            </div>
                        </div>
                        <div class="leave-request-body">
                            <div class="leave-request-details">
                                <div class="leave-request-detail">
                                    <span class="detail-label">Start Date:</span>
                                    <span class="detail-value">Mar 10, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">End Date:</span>
                                    <span class="detail-value">Mar 20, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">11 Days</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Rejected On:</span>
                                    <span class="detail-value">Mar 08, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Rejected By:</span>
                                    <span class="detail-value">Admin Jones</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Reason:</span>
                                    <span class="detail-value">Insufficient staffing during exam period</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-request-footer">
                            <div class="leave-type">
                                <span class="leave-type-badge leave-type-unpaid">Unpaid Leave</span>
                            </div>
                            <div class="leave-request-actions">
                                <button class="leave-request-action" onclick="viewLeaveDetails(2344)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="leave-request-action approve" onclick="approveLeave(2344)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Rejected Leave Request 2 -->
                    <div class="leave-request-card">
                        <div class="leave-request-header">
                            <h3 class="leave-request-title">Leave Request #LR-2343</h3>
                            <div class="leave-request-employee">
                                <div class="employee-avatar">PW</div>
                                <div class="employee-details">
                                    <div class="employee-name">Patricia Williams</div>
                                    <div class="employee-position">Biology Teacher</div>
                                </div>
                            </div>
                            <div class="leave-status">
                                <span class="status-tag status-rejected">Rejected</span>
                            </div>
                        </div>
                        <div class="leave-request-body">
                            <div class="leave-request-details">
                                <div class="leave-request-detail">
                                    <span class="detail-label">Start Date:</span>
                                    <span class="detail-value">Mar 15, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">End Date:</span>
                                    <span class="detail-value">Mar 16, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">2 Days</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Rejected On:</span>
                                    <span class="detail-value">Mar 14, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Rejected By:</span>
                                    <span class="detail-value">Admin Smith</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Reason:</span>
                                    <span class="detail-value">Late submission, critical lab session scheduled</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-request-footer">
                            <div class="leave-type">
                                <span class="leave-type-badge leave-type-personal">Personal Leave</span>
                            </div>
                            <div class="leave-request-actions">
                                <button class="leave-request-action" onclick="viewLeaveDetails(2343)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="leave-request-action approve" onclick="approveLeave(2343)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Rejected Leave Request 3 -->
                    <div class="leave-request-card">
                        <div class="leave-request-header">
                            <h3 class="leave-request-title">Leave Request #LR-2341</h3>
                            <div class="leave-request-employee">
                                <div class="employee-avatar">KJ</div>
                                <div class="employee-details">
                                    <div class="employee-name">Kevin Johnson</div>
                                    <div class="employee-position">Art Teacher</div>
                                </div>
                            </div>
                            <div class="leave-status">
                                <span class="status-tag status-rejected">Rejected</span>
                            </div>
                        </div>
                        <div class="leave-request-body">
                            <div class="leave-request-details">
                                <div class="leave-request-detail">
                                    <span class="detail-label">Start Date:</span>
                                    <span class="detail-value">Mar 07, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">End Date:</span>
                                    <span class="detail-value">Mar 11, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Duration:</span>
                                    <span class="detail-value">5 Days</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Rejected On:</span>
                                    <span class="detail-value">Mar 06, 2025</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Rejected By:</span>
                                    <span class="detail-value">Admin Wilson</span>
                                </div>
                                <div class="leave-request-detail">
                                    <span class="detail-label">Reason:</span>
                                    <span class="detail-value">Annual leave quota exceeded for the quarter</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-request-footer">
                            <div class="leave-type">
                                <span class="leave-type-badge leave-type-annual">Annual Leave</span>
                            </div>
                            <div class="leave-request-actions">
                                <button class="leave-request-action" onclick="viewLeaveDetails(2341)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="leave-request-action approve" onclick="approveLeave(2341)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="leave-request-action-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pagination">
                    <div class="pagination-info">
                        Showing 1 to 3 of 9 rejected leaves
                    </div>
                    <div class="pagination-buttons">
                        <button class="pagination-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        <button class="pagination-button active">1</button>
                        <button class="pagination-button">2</button>
                        <button class="pagination-button">3</button>
                        <button class="pagination-button">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Leave Request Detail Modal -->
    <div id="leave-detail-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Leave Request Details</h3>
                <button class="modal-close" onclick="closeModal('leave-detail-modal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="employee-info" style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                    <div class="employee-avatar" style="width: 48px; height: 48px; font-size: 1rem;">JS</div>
                    <div>
                        <h4 style="margin: 0 0 0.25rem 0; font-size: 1.1rem;">John Smith</h4>
                        <p style="margin: 0; font-size: 0.875rem; color: #6b7280;">Mathematics Teacher</p>
                        <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                            <span>Employee ID: EMP-1001</span>
                            <span style="margin-left: 1rem;">Department: Teaching</span>
                        </div>
                    </div>
                </div>

                <div class="leave-balance-grid">
                    <div class="leave-balance-item">
                        <div class="leave-balance-value">12</div>
                        <div class="leave-balance-type">Annual Leave</div>
                    </div>
                    <div class="leave-balance-item">
                        <div class="leave-balance-value">8</div>
                        <div class="leave-balance-type">
                        <div class="leave-balance-type">Sick Leave</div>
                    </div>
                    <div class="leave-balance-item">
                        <div class="leave-balance-value">3</div>
                        <div class="leave-balance-type">Personal Leave</div>
                    </div>
                    <div class="leave-balance-item">
                        <div class="leave-balance-value">2</div>
                        <div class="leave-balance-type">Remaining</div>
                    </div>
                </div>

                <div style="background-color: #f9fafb; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem;">
                    <h4 style="margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 600;">Leave Request #LR-2345</h4>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem 2rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280;">Leave Type</div>
                            <div style="font-size: 0.875rem; font-weight: 500;">Annual Leave</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280;">Status</div>
                            <div style="font-size: 0.875rem; font-weight: 500;"><span class="status-tag status-pending">Pending</span></div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280;">Start Date</div>
                            <div style="font-size: 0.875rem; font-weight: 500;">Mar 25, 2025</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280;">End Date</div>
                            <div style="font-size: 0.875rem; font-weight: 500;">Mar 28, 2025</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280;">Duration</div>
                            <div style="font-size: 0.875rem; font-weight: 500;">4 Days</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280;">Requested On</div>
                            <div style="font-size: 0.875rem; font-weight: 500;">Mar 15, 2025</div>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <h4 style="margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 600;">Reason for Leave</h4>
                    <p style="margin: 0; font-size: 0.875rem; line-height: 1.5; color: #4b5563;">
                        I am requesting annual leave for a family vacation. All current assignments and grading will be completed before the leave period. Lesson plans for substitute teacher will be submitted by March 20th.
                    </p>
                </div>

                <div style="margin-bottom: 0.5rem;">
                    <h4 style="margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 600;">Approval Flow</h4>
                    <div class="approval-flow">
                        <div class="flow-line"></div>
                        <div class="flow-step">
                            <div class="flow-icon completed">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div class="flow-content">
                                <div class="flow-title">Request Submitted</div>
                                <div class="flow-detail">
                                    <span>Mar 15, 2025, 09:42 AM</span>
                                    <span>John Smith</span>
                                </div>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="flow-icon current">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                            </div>
                            <div class="flow-content">
                                <div class="flow-title">Department Head Approval</div>
                                <div class="flow-detail">
                                    <span>Pending</span>
                                    <span>Dr. Williams</span>
                                </div>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="flow-icon pending">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                </svg>
                            </div>
                            <div class="flow-content">
                                <div class="flow-title">Principal Approval</div>
                                <div class="flow-detail">
                                    <span>Not Started</span>
                                    <span>Mrs. Thompson</span>
                                </div>
                            </div>
                        </div>
                        <div class="flow-step">
                            <div class="flow-icon pending">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="10"></circle>
                                </svg>
                            </div>
                            <div class="flow-content">
                                <div class="flow-title">HR Processing</div>
                                <div class="flow-detail">
                                    <span>Not Started</span>
                                    <span>HR Department</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="background-color: #f9fafb; border-radius: 8px; padding: 1rem; margin-top: 1.5rem;">
                    <h4 style="margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 600;">Comments</h4>
                    <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem;">
                        <div class="employee-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">AW</div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.875rem; font-weight: 500;">Admin Wilson</div>
                            <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem;">Mar 15, 2025, 10:15 AM</div>
                            <div style="font-size: 0.875rem; color: #4b5563;">Please confirm if all student grades will be submitted before your leave period.</div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 0.75rem;">
                        <div class="employee-avatar" style="width: 32px; height: 32px; font-size: 0.75rem;">JS</div>
                        <div style="flex: 1;">
                            <div style="font-size: 0.875rem; font-weight: 500;">John Smith</div>
                            <div style="font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem;">Mar 15, 2025, 11:30 AM</div>
                            <div style="font-size: 0.875rem; color: #4b5563;">Yes, all grades for the term will be submitted by March 24th, one day before my leave begins.</div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 1rem;">
                        <h5 style="margin: 0 0 0.5rem 0; font-size: 0.875rem; font-weight: 500;">Add Comment</h5>
                        <textarea class="form-control" placeholder="Write your comment here..." style="margin-bottom: 0.75rem;"></textarea>
                        <button class="button button-sm">Add Comment</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-outline" onclick="closeModal('leave-detail-modal')">Close</button>
                <button type="button" class="button button-danger">Reject</button>
                <button type="button" class="button button-success">Approve</button>
            </div>
        </div>
    </div>

    <!-- Leave Rejection Modal -->
    <div id="leave-rejection-modal" class="modal">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 class="modal-title">Reject Leave Request</h3>
                <button class="modal-close" onclick="closeModal('leave-rejection-modal')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p style="margin-top: 0; margin-bottom: 1rem; font-size: 0.875rem; color: #4b5563;">
                    You are about to reject the leave request <strong>#LR-2345</strong> from <strong>John Smith</strong>.
                </p>
                
                <div class="form-group">
                    <label for="rejection-reason" class="form-label">Reason for Rejection *</label>
                    <select id="rejection-reason" class="form-select" required>
                        <option value="">Select a reason</option>
                        <option value="staffing">Insufficient staffing</option>
                        <option value="workload">High workload/critical period</option>
                        <option value="quota">Leave quota exceeded</option>
                        <option value="notice">Insufficient notice period</option>
                        <option value="documentation">Incomplete documentation</option>
                        <option value="other">Other reason</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="rejection-comment" class="form-label">Additional Comments *</label>
                    <textarea id="rejection-comment" class="form-control" rows="4" placeholder="Please provide more details about the rejection reason..." required></textarea>
                </div>
                
                <div class="form-group" style="margin-bottom: 0;">
                    <div class="form-check">
                        <input type="checkbox" id="suggest-reschedule" class="form-check-input">
                        <label for="suggest-reschedule" class="form-check-label">Suggest a different time period for the leave</label>
                    </div>
                </div>
                
                <div id="reschedule-suggestion" style="display: none; margin-top: 1rem; padding: 1rem; background-color: #f9fafb; border-radius: 8px;">
                    <div class="form-row">
                        <div class="form-col">
                            <label for="suggested-start-date" class="form-label">Suggested Start Date</label>
                            <input type="date" id="suggested-start-date" class="form-control">
                        </div>
                        <div class="form-col">
                            <label for="suggested-end-date" class="form-label">Suggested End Date</label>
                            <input type="date" id="suggested-end-date" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="button button-outline" onclick="closeModal('leave-rejection-modal')">Cancel</button>
                <button type="button" class="button button-danger" onclick="confirmRejectLeave()">Reject Leave</button>
            </div>
        </div>
    </div>

    <script>
        // DOM ready function
        document.addEventListener('DOMContentLoaded', function() {
            initializeTabs();
            initializeSearch();
            initializeDropdowns();
            
            // Handle the reschedule suggestion toggle
            const suggestReschedule = document.getElementById('suggest-reschedule');
            const rescheduleSection = document.getElementById('reschedule-suggestion');
            
            if (suggestReschedule && rescheduleSection) {
                suggestReschedule.addEventListener('change', function() {
                    rescheduleSection.style.display = this.checked ? 'block' : 'none';
                });
            }
        });

        // Toggle sidebar visibility
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
            
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            if (sidebar.classList.contains('show')) {
                hamburgerBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
            } else {
                hamburgerBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                `;
            }
        }

        // Tabs functionality
        function initializeTabs() {
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs
                    tabs.forEach(t => t.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Hide all tab contents
                    const tabContents = document.querySelectorAll('.tab-content');
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Show the corresponding tab content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        }

        // Search functionality
        function initializeSearch() {
            const searchInputs = document.querySelectorAll('.search-input');
            
            searchInputs.forEach(input => {
                input.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const tabContent = this.closest('.tab-content');
                    
                    // Search in cards if present
                    const cards = tabContent.querySelectorAll('.leave-request-card');
                    if (cards.length > 0) {
                        cards.forEach(card => {
                            const employeeName = card.querySelector('.employee-name').textContent.toLowerCase();
                            const employeePosition = card.querySelector('.employee-position').textContent.toLowerCase();
                            const requestId = card.querySelector('.leave-request-title').textContent.toLowerCase();
                            
                            if (employeeName.includes(searchTerm) || employeePosition.includes(searchTerm) || requestId.includes(searchTerm)) {
                                card.style.display = '';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    }
                    
                    // Search in table if present
                    const table = tabContent.querySelector('.table');
                    if (table) {
                        const rows = table.querySelectorAll('tbody tr');
                        
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            if (text.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }
                });
            });
        }

        // Initialize dropdown actions
        function initializeDropdowns() {
            const actionButtons = document.querySelectorAll('.actions-button');
            
            document.addEventListener('click', function(e) {
                // Close all open action menus when clicking outside
                if (!e.target.closest('.actions-dropdown')) {
                    document.querySelectorAll('.actions-menu').forEach(menu => {
                        menu.classList.remove('show');
                    });
                }
            });
            
            actionButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const menu = this.nextElementSibling;
                    
                    // Close all other open menus
                    document.querySelectorAll('.actions-menu').forEach(m => {
                        if (m !== menu) m.classList.remove('show');
                    });
                    
                    // Toggle this menu
                    menu.classList.toggle('show');
                });
            });
        }

        // View leave details
        function viewLeaveDetails(leaveId) {
            // In a real application, you would fetch leave details via AJAX
            // For this demo, we'll just open the modal with static data
            document.getElementById('leave-detail-modal').classList.add('show');
        }

        // Approve leave
        function approveLeave(leaveId) {
            // In a real application, you would send a request to approve the leave
            // For this demo, we'll just show an alert
            if (confirm(`Are you sure you want to approve leave request #LR-${leaveId}?`)) {
                alert(`Leave request #LR-${leaveId} has been approved.`);
                
                // Refresh the page or update the UI
                // For demo purposes, we'll just reload after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        }

        // Reject leave
        function rejectLeave(leaveId) {
            // Open the rejection modal
            document.getElementById('leave-rejection-modal').classList.add('show');
        }

        // Confirm reject leave
        function confirmRejectLeave() {
            const reason = document.getElementById('rejection-reason').value;
            const comment = document.getElementById('rejection-comment').value;
            
            if (!reason || !comment) {
                alert('Please provide both a reason and comment for the rejection.');
                return;
            }
            
            // In a real application, you would send a request to reject the leave
            // For this demo, we'll just show an alert
            alert(`Leave request has been rejected.\nReason: ${reason}\nComment: ${comment}`);
            
            // Close the modal
            closeModal('leave-rejection-modal');
            
            // Refresh the page or update the UI
            // For demo purposes, we'll just reload after a short delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Page transitions
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a:not([href^="#"])');
            
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!this.getAttribute('href').startsWith('javascript:')) {
                        e.preventDefault();
                        document.body.classList.add('fade-out');
                        
                        setTimeout(() => {
                            window.location.href = this.getAttribute('href');
                        }, 500);
                    }
                });
            });
        });
    </script>
</body>
</html>