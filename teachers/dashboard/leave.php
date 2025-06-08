<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Leave Requests</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/leave.css">
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
        <h1 class="header-title">Leave Requests</h1>
        <span class="header-subtitle">Apply for leaves and view your leave status</span>
    </header>

    <main class="dashboard-content">
        <!-- Leave Statistics Overview -->
        <section class="leave-overview-section">
            <div class="leave-stats-grid">
                <div class="leave-stat-card">
                    <h3 class="leave-stat-title">Total Leave Balance</h3>
                    <div class="leave-stat-value">16</div>
                    <div class="leave-stat-progress">
                        <div class="progress-info">
                            <span class="progress-text">Used: 14 days</span>
                            <span class="progress-text">47%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill fill-primary" style="width: 47%;"></div>
                        </div>
                    </div>
                </div>
                <div class="leave-stat-card">
                    <h3 class="leave-stat-title">Pending Requests</h3>
                    <div class="leave-stat-value">2</div>
                    <div class="leave-stat-progress">
                        <div class="progress-info">
                            <span class="progress-text">Processing time</span>
                            <span class="progress-text">1-2 days</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill fill-warning" style="width: 25%;"></div>
                        </div>
                    </div>
                </div>
                <div class="leave-stat-card">
                    <h3 class="leave-stat-title">Approved Requests</h3>
                    <div class="leave-stat-value">7</div>
                    <div class="leave-stat-progress">
                        <div class="progress-info">
                            <span class="progress-text">This academic year</span>
                            <span class="progress-text">70%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill fill-success" style="width: 70%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Leave Balance</h2>
                </div>
                <div class="card-body">
                    <div class="leave-balance-grid">
                        <div class="leave-balance-card">
                            <h3 class="leave-balance-title">Sick Leave</h3>
                            <div class="leave-balance-value">8 / 12 days</div>
                            <div class="leave-progress-bar">
                                <div class="leave-progress-fill leave-progress-sick" style="width: 66.67%;"></div>
                            </div>
                        </div>
                        <div class="leave-balance-card">
                            <h3 class="leave-balance-title">Casual Leave</h3>
                            <div class="leave-balance-value">3 / 10 days</div>
                            <div class="leave-progress-bar">
                                <div class="leave-progress-fill leave-progress-casual" style="width: 30%;"></div>
                            </div>
                        </div>
                        <div class="leave-balance-card">
                            <h3 class="leave-balance-title">Emergency Leave</h3>
                            <div class="leave-balance-value">2 / 5 days</div>
                            <div class="leave-progress-bar">
                                <div class="leave-progress-fill leave-progress-emergency" style="width: 40%;"></div>
                            </div>
                        </div>
                        <div class="leave-balance-card">
                            <h3 class="leave-balance-title">Vacation Leave</h3>
                            <div class="leave-balance-value">3 / 3 days</div>
                            <div class="leave-progress-bar">
                                <div class="leave-progress-fill leave-progress-vacation" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Leave Requests Table -->
        <section class="leave-table-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Leave Applications</h2>
                    <button class="btn btn-primary" onclick="showCreateLeaveModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Apply for Leave
                    </button>
                </div>
                <div class="card-body">
                    <div class="tabs-container">
                        <div class="tab active" onclick="changeTab(this, 'all-leaves')">All</div>
                        <div class="tab" onclick="changeTab(this, 'pending-leaves')">Pending</div>
                        <div class="tab" onclick="changeTab(this, 'approved-leaves')">Approved</div>
                        <div class="tab" onclick="changeTab(this, 'rejected-leaves')">Rejected</div>
                    </div>

                    <table class="leave-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Days</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Example Leave Request 1 -->
                            <tr data-status="approved">
                                <td>LR-2025-001</td>
                                <td>
                                    <span class="leave-type-badge leave-sick">Sick Leave</span>
                                </td>
                                <td>Feb 15, 2025</td>
                                <td>Feb 17, 2025</td>
                                <td>3</td>
                                <td>Medical Appointment</td>
                                <td>
                                    <span class="status-badge status-approved">Approved</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="View Details" onclick="showLeaveDetailsModal(1)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Example Leave Request 2 -->
                            <tr data-status="pending">
                                <td>LR-2025-002</td>
                                <td>
                                    <span class="leave-type-badge leave-casual">Casual Leave</span>
                                </td>
                                <td>March 25, 2025</td>
                                <td>March 25, 2025</td>
                                <td>1</td>
                                <td>Personal Commitment</td>
                                <td>
                                    <span class="status-badge status-pending">Pending</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="View Details" onclick="showLeaveDetailsModal(2)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Edit Request" onclick="showEditLeaveModal(2)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Cancel Request" onclick="confirmCancelLeave(2)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Example Leave Request 3 -->
                            <tr data-status="pending">
                                <td>LR-2025-003</td>
                                <td>
                                    <span class="leave-type-badge leave-vacation">Vacation Leave</span>
                                </td>
                                <td>April 10, 2025</td>
                                <td>April 12, 2025</td>
                                <td>3</td>
                                <td>Family Vacation</td>
                                <td>
                                    <span class="status-badge status-pending">Pending</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="View Details" onclick="showLeaveDetailsModal(3)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Edit Request" onclick="showEditLeaveModal(3)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Cancel Request" onclick="confirmCancelLeave(3)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Example Leave Request 4 -->
                            <tr data-status="rejected">
                                <td>LR-2025-004</td>
                                <td>
                                    <span class="leave-type-badge leave-emergency">Emergency Leave</span>
                                </td>
                                <td>Jan 28, 2025</td>
                                <td>Jan 29, 2025</td>
                                <td>2</td>
                                <td>Family Emergency</td>
                                <td>
                                    <span class="status-badge status-rejected">Rejected</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="View Details" onclick="showLeaveDetailsModal(4)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                        <button class="action-btn" title="Apply Again" onclick="applyAgain(4)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Example Leave Request 5 -->
                            <tr data-status="approved">
                                <td>LR-2025-005</td>
                                <td>
                                    <span class="leave-type-badge leave-casual">Casual Leave</span>
                                </td>
                                <td>Feb 05, 2025</td>
                                <td>Feb 05, 2025</td>
                                <td>1</td>
                                <td>Personal Work</td>
                                <td>
                                    <span class="status-badge status-approved">Approved</span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="action-btn" title="View Details" onclick="showLeaveDetailsModal(5)">
                                            <svg class="action-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <h3 class="empty-title">No Leave Requests Found</h3>
                        <p class="empty-description">You don't have any leave requests matching the selected filter.</p>
                        <button class="btn btn-primary" onclick="showCreateLeaveModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Apply for Leave
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Leave Calendar -->
        <section class="calendar-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Leave Calendar</h2>
                    <div class="calendar-nav">
                        <button class="calendar-btn" onclick="prevMonth()">
                            <svg class="calendar-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <span class="calendar-month">March 2025</span>
                        <button class="calendar-btn" onclick="nextMonth()">
                            <svg class="calendar-btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="calendar-grid">
                        <!-- Day Headers -->
                        <div class="calendar-day-header">Sun</div>
                        <div class="calendar-day-header">Mon</div>
                        <div class="calendar-day-header">Tue</div>
                        <div class="calendar-day-header">Wed</div>
                        <div class="calendar-day-header">Thu</div>
                        <div class="calendar-day-header">Fri</div>
                        <div class="calendar-day-header">Sat</div>
                        
                        <!-- Week 1 -->
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">23</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">24</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">25</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">26</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">27</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">28</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">1</div>
                        </div>
                        
                        <!-- Week 2 -->
                        <div class="calendar-day">
                            <div class="calendar-day-number">2</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">3</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">4</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">5</div>
                            <div class="calendar-day-event casual">Casual Leave</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">6</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">7</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">8</div>
                        </div>
                        
                        <!-- Week 3 -->
                        <div class="calendar-day">
                            <div class="calendar-day-number">9</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">10</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">11</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">12</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">13</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">14</div>
                        </div>
                        <div class="calendar-day today">
                            <div class="calendar-day-number">15</div>
                            <div class="calendar-day-event sick">Sick Leave</div>
                        </div>
                        
                        <!-- Week 4 -->
                        <div class="calendar-day">
                            <div class="calendar-day-number">16</div>
                            <div class="calendar-day-event sick">Sick Leave</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">17</div>
                            <div class="calendar-day-event sick">Sick Leave</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">18</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">19</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">20</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">21</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">22</div>
                        </div>
                        
                        <!-- Week 5 -->
                        <div class="calendar-day">
                            <div class="calendar-day-number">23</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">24</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">25</div>
                            <div class="calendar-day-event casual">Casual Leave</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">26</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">27</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">28</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">29</div>
                        </div>
                        
                        <!-- Week 6 -->
                        <div class="calendar-day">
                            <div class="calendar-day-number">30</div>
                        </div>
                        <div class="calendar-day">
                            <div class="calendar-day-number">31</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">1</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">2</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">3</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">4</div>
                        </div>
                        <div class="calendar-day other-month">
                            <div class="calendar-day-number">5</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Create Leave Modal -->
<div class="modal-overlay" id="createLeaveModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Apply for Leave</h3>
            <button class="modal-close" onclick="hideCreateLeaveModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="createLeaveForm">
                <div class="form-group">
                    <label class="form-label">Leave Type <span class="form-required">*</span></label>
                    <select class="form-select" id="leaveType" required>
                        <option value="">Select leave type</option>
                        <option value="sick">Sick Leave</option>
                        <option value="casual">Casual Leave</option>
                        <option value="emergency">Emergency Leave</option>
                        <option value="vacation">Vacation Leave</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-date-group">
                    <div class="form-group">
                        <label class="form-label">From Date <span class="form-required">*</span></label>
                        <input type="date" class="form-input" id="leaveFromDate" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To Date <span class="form-required">*</span></label>
                        <input type="date" class="form-input" id="leaveToDate" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Reason for Leave <span class="form-required">*</span></label>
                    <textarea class="form-textarea" id="leaveReason" placeholder="Enter detailed reason for your leave request" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Supporting Documents (if any)</label>
                    <div class="form-file">
                        <input type="file" id="leaveDocuments" class="file-input" multiple>
                        <div class="file-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                                <polyline points="13 2 13 9 20 9"></polyline>
                            </svg>
                        </div>
                        <div class="file-text">Drag and drop files here, or click to browse</div>
                        <div class="file-hint">Supported files: PDF, DOC, DOCX, JPG, PNG (Max size: 5MB)</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Additional Information</label>
                    <div class="form-check">
                        <input type="checkbox" id="informSubstitute" class="form-checkbox">
                        <label for="informSubstitute" class="form-check-label">I have arranged for a substitute teacher</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="submitLessonPlan" class="form-checkbox">
                        <label for="submitLessonPlan" class="form-check-label">I will submit lesson plans for the absent days</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="emergencyContact" class="form-checkbox">
                        <label for="emergencyContact" class="form-check-label">I can be contacted in case of emergencies</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="hideCreateLeaveModal()">Cancel</button>
            <button class="btn btn-primary" onclick="submitLeaveRequest()">Submit Request</button>
        </div>
    </div>
</div>

<!-- Edit Leave Modal -->
<div class="modal-overlay" id="editLeaveModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Edit Leave Request</h3>
            <button class="modal-close" onclick="hideEditLeaveModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="editLeaveForm">
                <div class="form-group">
                    <label class="form-label">Leave Type <span class="form-required">*</span></label>
                    <select class="form-select" id="editLeaveType" required>
                        <option value="">Select leave type</option>
                        <option value="sick">Sick Leave</option>
                        <option value="casual">Casual Leave</option>
                        <option value="emergency">Emergency Leave</option>
                        <option value="vacation">Vacation Leave</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-date-group">
                    <div class="form-group">
                        <label class="form-label">From Date <span class="form-required">*</span></label>
                        <input type="date" class="form-input" id="editLeaveFromDate" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To Date <span class="form-required">*</span></label>
                        <input type="date" class="form-input" id="editLeaveToDate" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Reason for Leave <span class="form-required">*</span></label>
                    <textarea class="form-textarea" id="editLeaveReason" placeholder="Enter detailed reason for your leave request" required></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Supporting Documents</label>
                    <div class="form-file">
                        <input type="file" id="editLeaveDocuments" class="file-input" multiple>
                        <div class="file-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                                <polyline points="13 2 13 9 20 9"></polyline>
                            </svg>
                        </div>
                        <div class="file-text">Current files: medical_certificate.pdf</div>
                        <div class="file-hint">Supported files: PDF, DOC, DOCX, JPG, PNG (Max size: 5MB)</div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Additional Information</label>
                    <div class="form-check">
                        <input type="checkbox" id="editInformSubstitute" class="form-checkbox">
                        <label for="editInformSubstitute" class="form-check-label">I have arranged for a substitute teacher</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="editSubmitLessonPlan" class="form-checkbox">
                        <label for="editSubmitLessonPlan" class="form-check-label">I will submit lesson plans for the absent days</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="editEmergencyContact" class="form-checkbox">
                        <label for="editEmergencyContact" class="form-check-label">I can be contacted in case of emergencies</label>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="hideEditLeaveModal()">Cancel</button>
            <button class="btn btn-primary" onclick="updateLeaveRequest()">Update Request</button>
        </div>
    </div>
</div>

<!-- Leave Details Modal -->
<div class="modal-overlay" id="leaveDetailsModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Leave Request Details</h3>
            <button class="modal-close" onclick="hideLeaveDetailsModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="leaveDetailsContent">
                <div class="form-group">
                    <label class="form-label">Leave ID</label>
                    <p id="detailLeaveId">LR-2025-002</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Leave Type</label>
                    <p id="detailLeaveType"><span class="leave-type-badge leave-casual">Casual Leave</span></p>
                </div>
                
                <div class="form-date-group">
                    <div class="form-group">
                        <label class="form-label">From Date</label>
                        <p id="detailLeaveFromDate">March 25, 2025</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">To Date</label>
                        <p id="detailLeaveToDate">March 25, 2025</p>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Total Days</label>
                    <p id="detailLeaveDays">1</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Reason for Leave</label>
                    <p id="detailLeaveReason">Personal Commitment</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <p id="detailLeaveStatus"><span class="status-badge status-pending">Pending</span></p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Applied On</label>
                    <p id="detailLeaveAppliedOn">March 7, 2025</p>
                </div>
                
                <div class="form-group" id="detailLeaveReviewSection">
                    <label class="form-label">Reviewed By</label>
                    <p id="detailLeaveReviewedBy">Principal - Dr. Sarah Thompson</p>
                </div>
                
                <div class="form-group" id="detailLeaveCommentsSection">
                    <label class="form-label">Comments</label>
                    <p id="detailLeaveComments">Your leave request is pending approval. Please ensure your classes are covered during your absence.</p>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Supporting Documents</label>
                    <div id="detailLeaveDocuments">
                        <div class="attachment-item">
                            <svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            personal_commitment.pdf
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="hideLeaveDetailsModal()">Close</button>
        </div>
    </div>
</div>

<script>
    // Function to toggle sidebar
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const dashboardContainer = document.querySelector('.dashboard-container');
        const body = document.querySelector('body');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-open');
        dashboardContainer.classList.toggle('sidebar-open');
    }
    
    // Function to handle tab changes
    function changeTab(tab, tabId) {
        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(t => t.classList.remove('active'));
        
        // Add active class to the selected tab
        tab.classList.add('active');
        
        // Show leave requests based on tab
        if (tabId === 'all-leaves') {
            filterLeaveRequests('all');
        } else if (tabId === 'pending-leaves') {
            filterLeaveRequests('pending');
        } else if (tabId === 'approved-leaves') {
            filterLeaveRequests('approved');
        } else if (tabId === 'rejected-leaves') {
            filterLeaveRequests('rejected');
        }
    }
    
    // Function to filter leave requests
    function filterLeaveRequests(statusFilter) {
        const rows = document.querySelectorAll('.leave-table tbody tr');
        let matchFound = false;
        
        rows.forEach(row => {
            const status = row.getAttribute('data-status');
            
            if (statusFilter === 'all' || status === statusFilter) {
                row.style.display = '';
                matchFound = true;
            } else {
                row.style.display = 'none';
            }
        });
        
        // Show empty state if no matching rows
        const emptyState = document.getElementById('emptyState');
        if (!matchFound) {
            emptyState.style.display = 'flex';
        } else {
            emptyState.style.display = 'none';
        }
    }
    
    // Calendar Navigation Functions
    function prevMonth() {
        // This would normally update the calendar display
        alert('Navigating to the previous month');
    }
    
    function nextMonth() {
        // This would normally update the calendar display
        alert('Navigating to the next month');
    }
    
    // Create Leave Modal Functions
    function showCreateLeaveModal() {
        const modal = document.getElementById('createLeaveModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    function hideCreateLeaveModal() {
        const modal = document.getElementById('createLeaveModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    function submitLeaveRequest() {
        // Get form values
        const leaveType = document.getElementById('leaveType').value;
        const fromDate = document.getElementById('leaveFromDate').value;
        const toDate = document.getElementById('leaveToDate').value;
        const reason = document.getElementById('leaveReason').value;
        
        // Validate form
        if (!leaveType || !fromDate || !toDate || !reason) {
            alert('Please fill all required fields.');
            return;
        }
        
        // In a real application, this would submit the form data via AJAX
        alert('Leave request submitted successfully!');
        hideCreateLeaveModal();
        
        // Optionally refresh the leave requests table
        // location.reload();
    }
    
    // Edit Leave Modal Functions
    function showEditLeaveModal(leaveId) {
        const modal = document.getElementById('editLeaveModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // In a real application, you would fetch the leave request data
        // For this example, we'll pre-fill with example data
        if (leaveId === 2) {
            document.getElementById('editLeaveType').value = 'casual';
            document.getElementById('editLeaveFromDate').value = '2025-03-25';
            document.getElementById('editLeaveToDate').value = '2025-03-25';
            document.getElementById('editLeaveReason').value = 'Personal Commitment';
            document.getElementById('editInformSubstitute').checked = true;
            document.getElementById('editSubmitLessonPlan').checked = true;
            document.getElementById('editEmergencyContact').checked = false;
        } else if (leaveId === 3) {
            document.getElementById('editLeaveType').value = 'vacation';
            document.getElementById('editLeaveFromDate').value = '2025-04-10';
            document.getElementById('editLeaveToDate').value = '2025-04-12';
            document.getElementById('editLeaveReason').value = 'Family Vacation';
            document.getElementById('editInformSubstitute').checked = true;
            document.getElementById('editSubmitLessonPlan').checked = true;
            document.getElementById('editEmergencyContact').checked = true;
        }
    }
    
    function hideEditLeaveModal() {
        const modal = document.getElementById('editLeaveModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    function updateLeaveRequest() {
        // Get form values
        const leaveType = document.getElementById('editLeaveType').value;
        const fromDate = document.getElementById('editLeaveFromDate').value;
        const toDate = document.getElementById('editLeaveToDate').value;
        const reason = document.getElementById('editLeaveReason').value;
        
        // Validate form
        if (!leaveType || !fromDate || !toDate || !reason) {
            alert('Please fill all required fields.');
            return;
        }
        
        // In a real application, this would submit the form data via AJAX
        alert('Leave request updated successfully!');
        hideEditLeaveModal();
        
        // Optionally refresh the leave requests table
        // location.reload();
    }
    
    // Leave Details Modal Functions
    function showLeaveDetailsModal(leaveId) {
        const modal = document.getElementById('leaveDetailsModal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        // In a real application, you would fetch the leave details
        // For this example, we'll update with pre-defined data
        if (leaveId === 1) {
            document.getElementById('detailLeaveId').textContent = 'LR-2025-001';
            document.getElementById('detailLeaveType').innerHTML = '<span class="leave-type-badge leave-sick">Sick Leave</span>';
            document.getElementById('detailLeaveFromDate').textContent = 'Feb 15, 2025';
            document.getElementById('detailLeaveToDate').textContent = 'Feb 17, 2025';
            document.getElementById('detailLeaveDays').textContent = '3';
            document.getElementById('detailLeaveReason').textContent = 'Medical Appointment';
            document.getElementById('detailLeaveStatus').innerHTML = '<span class="status-badge status-approved">Approved</span>';
            document.getElementById('detailLeaveAppliedOn').textContent = 'Feb 10, 2025';
            document.getElementById('detailLeaveReviewSection').style.display = 'block';
            document.getElementById('detailLeaveReviewedBy').textContent = 'Vice Principal - Mr. Robert Chen';
            document.getElementById('detailLeaveCommentsSection').style.display = 'block';
            document.getElementById('detailLeaveComments').textContent = 'Your leave request has been approved. Please ensure your substitute has all necessary materials.';
            document.getElementById('detailLeaveDocuments').innerHTML = '<div class="attachment-item"><svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>medical_certificate.pdf</div>';
        } else if (leaveId === 2) {
            document.getElementById('detailLeaveId').textContent = 'LR-2025-002';
            document.getElementById('detailLeaveType').innerHTML = '<span class="leave-type-badge leave-casual">Casual Leave</span>';
            document.getElementById('detailLeaveFromDate').textContent = 'March 25, 2025';
            document.getElementById('detailLeaveToDate').textContent = 'March 25, 2025';
            document.getElementById('detailLeaveDays').textContent = '1';
            document.getElementById('detailLeaveReason').textContent = 'Personal Commitment';
            document.getElementById('detailLeaveStatus').innerHTML = '<span class="status-badge status-pending">Pending</span>';
            document.getElementById('detailLeaveAppliedOn').textContent = 'March 7, 2025';
            document.getElementById('detailLeaveReviewSection').style.display = 'none';
            document.getElementById('detailLeaveCommentsSection').style.display = 'block';
            document.getElementById('detailLeaveComments').textContent = 'Your leave request is pending approval. Please ensure your classes are covered during your absence.';
            document.getElementById('detailLeaveDocuments').innerHTML = '<div class="attachment-item"><svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>personal_commitment.pdf</div>';
        } else if (leaveId === 3) {
            document.getElementById('detailLeaveId').textContent = 'LR-2025-003';
            document.getElementById('detailLeaveType').innerHTML = '<span class="leave-type-badge leave-vacation">Vacation Leave</span>';
            document.getElementById('detailLeaveFromDate').textContent = 'April 10, 2025';
            document.getElementById('detailLeaveToDate').textContent = 'April 12, 2025';
            document.getElementById('detailLeaveDays').textContent = '3';
            document.getElementById('detailLeaveReason').textContent = 'Family Vacation';
            document.getElementById('detailLeaveStatus').innerHTML = '<span class="status-badge status-pending">Pending</span>';
            document.getElementById('detailLeaveAppliedOn').textContent = 'March 12, 2025';
            document.getElementById('detailLeaveReviewSection').style.display = 'none';
            document.getElementById('detailLeaveCommentsSection').style.display = 'none';
            document.getElementById('detailLeaveDocuments').innerHTML = '<div class="empty-state" style="padding: 1rem; margin: 0;">No documents attached</div>';
        } else if (leaveId === 4) {
            document.getElementById('detailLeaveId').textContent = 'LR-2025-004';
            document.getElementById('detailLeaveType').innerHTML = '<span class="leave-type-badge leave-emergency">Emergency Leave</span>';
            document.getElementById('detailLeaveFromDate').textContent = 'Jan 28, 2025';
            document.getElementById('detailLeaveToDate').textContent = 'Jan 29, 2025';
            document.getElementById('detailLeaveDays').textContent = '2';
            document.getElementById('detailLeaveReason').textContent = 'Family Emergency';
            document.getElementById('detailLeaveStatus').innerHTML = '<span class="status-badge status-rejected">Rejected</span>';
            document.getElementById('detailLeaveAppliedOn').textContent = 'Jan 27, 2025';
            document.getElementById('detailLeaveReviewSection').style.display = 'block';
            document.getElementById('detailLeaveReviewedBy').textContent = 'Principal - Dr. Sarah Thompson';
            document.getElementById('detailLeaveCommentsSection').style.display = 'block';
            document.getElementById('detailLeaveComments').textContent = 'Your leave request has been rejected due to the important school event scheduled during these dates. Please consult with the principal for alternative arrangements.';
            document.getElementById('detailLeaveDocuments').innerHTML = '<div class="attachment-item"><svg class="attachment-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>emergency_proof.pdf</div>';
        } else if (leaveId === 5) {
            document.getElementById('detailLeaveId').textContent = 'LR-2025-005';
            document.getElementById('detailLeaveType').innerHTML = '<span class="leave-type-badge leave-casual">Casual Leave</span>';
            document.getElementById('detailLeaveFromDate').textContent = 'Feb 05, 2025';
            document.getElementById('detailLeaveToDate').textContent = 'Feb 05, 2025';
            document.getElementById('detailLeaveDays').textContent = '1';
            document.getElementById('detailLeaveReason').textContent = 'Personal Work';
            document.getElementById('detailLeaveStatus').innerHTML = '<span class="status-badge status-approved">Approved</span>';
            document.getElementById('detailLeaveAppliedOn').textContent = 'Feb 01, 2025';
            document.getElementById('detailLeaveReviewSection').style.display = 'block';
            document.getElementById('detailLeaveReviewedBy').textContent = 'Vice Principal - Mr. Robert Chen';
            document.getElementById('detailLeaveCommentsSection').style.display = 'block';
            document.getElementById('detailLeaveComments').textContent = 'Your leave request has been approved.';
            document.getElementById('detailLeaveDocuments').innerHTML = '<div class="empty-state" style="padding: 1rem; margin: 0;">No documents attached</div>';
        }
    }
    
    function hideLeaveDetailsModal() {
        const modal = document.getElementById('leaveDetailsModal');
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
    
    // Cancel Leave Request Function
    function confirmCancelLeave(leaveId) {
        if (confirm('Are you sure you want to cancel this leave request?')) {
            // In a real application, this would send a request to cancel the leave
            alert('Leave request cancelled successfully!');
            
            // Optionally refresh the leave requests table
            // location.reload();
        }
    }
    
    // Apply Again Function
    function applyAgain(leaveId) {
        // Show the create leave modal and pre-fill with existing data
        showCreateLeaveModal();
        
        // In a real application, you would fetch the leave details and pre-fill the form
        if (leaveId === 4) {
            document.getElementById('leaveType').value = 'emergency';
            document.getElementById('leaveReason').value = 'Family Emergency - Reapplying with additional details';
        }
    }
    
    // Initialize event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Add click event listeners to all navigation links
        const navLinks = document.querySelectorAll('a[href]:not([href^="#"])');
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.href.includes(window.location.hostname)) {
                    e.preventDefault();
                    
                    // Add exit animation class
                    document.body.classList.add('fade-out');
                    
                    // Navigate to new page after animation completes
                    setTimeout(() => {
                        window.location.href = this.href;
                    }, 500); // Match animation duration
                }
            });
        });
        
        // Initialize file upload styling
        const fileInputs = document.querySelectorAll('.form-file');
        fileInputs.forEach(fileInput => {
            const input = fileInput.querySelector('input[type="file"]');
            fileInput.addEventListener('click', () => {
                input.click();
            });
            
            input.addEventListener('change', function() {
                const fileNames = Array.from(this.files).map(file => file.name).join(', ');
                const fileText = fileInput.querySelector('.file-text');
                
                if (fileNames) {
                    fileText.textContent = 'Selected files: ' + fileNames;
                } else {
                    fileText.textContent = 'Drag and drop files here, or click to browse';
                }
            });
        });
        
        // Calculate days between dates when selecting dates
        const fromDateInput = document.getElementById('leaveFromDate');
        const toDateInput = document.getElementById('leaveToDate');
        
        if (fromDateInput && toDateInput) {
            const calculateDays = () => {
                const fromDate = new Date(fromDateInput.value);
                const toDate = new Date(toDateInput.value);
                
                if (fromDate && toDate && !isNaN(fromDate) && !isNaN(toDate)) {
                    // Calculate difference in days
                    const diffTime = Math.abs(toDate - fromDate);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 to include end date
                    
                    // You could display this somewhere on the form
                    console.log(`Selected ${diffDays} day(s)`);
                }
            };
            
            fromDateInput.addEventListener('change', calculateDays);
            toDateInput.addEventListener('change', calculateDays);
        }
    });
</script>
</body>
</html>