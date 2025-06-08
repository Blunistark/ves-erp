<?php include '../../sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Receipts</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/feerecipt.css">
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
            <h1 class="header-title">Fee Receipts</h1>
            <span class="header-path">Dashboard > Admin > Fees > Receipts</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="receiptSearch" class="search-input" placeholder="Search by receipt number, student name, payment method...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="filterToggleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        Filter
                    </button>
                    <button class="btn btn-outline" id="printBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </button>
                    <button class="btn btn-primary" id="createReceiptBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Receipt
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel" style="display: none;">
                <h3 class="filter-title">Filter Receipts</h3>
                <form class="filter-form">
                    <div class="filter-group">
                        <label class="filter-label">Date Range</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="date" class="filter-input" id="startDateFilter" style="flex: 1;">
                            <span style="align-self: center;">to</span>
                            <input type="date" class="filter-input" id="endDateFilter" style="flex: 1;">
                        </div>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Class/Grade</label>
                        <select class="filter-select" id="classFilter">
                            <option value="">All Classes</option>
                            <option value="7a">Grade 7A</option>
                            <option value="7b">Grade 7B</option>
                            <option value="8a">Grade 8A</option>
                            <option value="8b">Grade 8B</option>
                            <option value="9a">Grade 9A</option>
                            <option value="9b">Grade 9B</option>
                            <option value="10a">Grade 10A</option>
                            <option value="10b">Grade 10B</option>
                            <option value="11a">Grade 11A</option>
                            <option value="11b">Grade 11B</option>
                            <option value="12a">Grade 12A</option>
                            <option value="12b">Grade 12B</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Fee Type</label>
                        <select class="filter-select" id="feeTypeFilter">
                            <option value="">All Types</option>
                            <option value="tuition">Tuition Fee</option>
                            <option value="admission">Admission Fee</option>
                            <option value="exam">Examination Fee</option>
                            <option value="library">Library Fee</option>
                            <option value="transportation">Transportation Fee</option>
                            <option value="laboratory">Laboratory Fee</option>
                            <option value="sports">Sports Fee</option>
                            <option value="other">Other Fees</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Payment Method</label>
                        <select class="filter-select" id="paymentMethodFilter">
                            <option value="">All Methods</option>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="check">Check</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="debit_card">Debit Card</option>
                            <option value="online">Online Payment</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Statuses</option>
                            <option value="success">Success</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Amount Range</label>
                        <div style="display: flex; gap: 0.5rem;">
                            <input type="number" class="filter-input" id="minAmountFilter" placeholder="Min" style="flex: 1;">
                            <span style="align-self: center;">to</span>
                            <input type="number" class="filter-input" id="maxAmountFilter" placeholder="Max" style="flex: 1;">
                        </div>
                    </div>
                </form>
                <div class="filter-actions">
                    <button class="filter-btn filter-btn-reset">Reset</button>
                    <button class="filter-btn filter-btn-apply">Apply Filters</button>
                </div>
            </div>

            <!-- Tab System -->
            <div class="receipts-tabs">
                <div class="receipts-tab active" data-tab="all">All Receipts</div>
                <div class="receipts-tab" data-tab="recent">Recent Receipts</div>
                <div class="receipts-tab" data-tab="print">Print Receipts</div>
                <div class="receipts-tab" data-tab="analytics">Analytics</div>
            </div>

            <!-- Receipt Form -->
            <div class="receipt-form-container" id="receiptForm" style="display: none;">
                <h2 class="form-title">Create New Receipt</h2>
                <form id="createReceiptForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="studentSelect">Select Student</label>
                            <select class="form-select" id="studentSelect" name="studentSelect" required>
                                <option value="">Select Student</option>
                                <option value="1">Alex Brown (ST001) - Grade 10A</option>
                                <option value="2">Emma Smith (ST002) - Grade 10A</option>
                                <option value="3">Michael Johnson (ST003) - Grade 10A</option>
                                <option value="4">Sophia Davis (ST004) - Grade 10A</option>
                                <option value="5">William Miller (ST005) - Grade 10A</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="feeSelect">Select Fee</label>
                            <select class="form-select" id="feeSelect" name="feeSelect" required>
                                <option value="">Select Fee</option>
                                <option value="tuition">Tuition Fee (2024-2025)</option>
                                <option value="admission">Admission Fee</option>
                                <option value="exam">Examination Fee</option>
                                <option value="library">Library Fee</option>
                                <option value="transportation">Transportation Fee</option>
                                <option value="laboratory">Laboratory Fee</option>
                                <option value="sports">Sports Fee</option>
                                <option value="other">Other Fee</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="paymentAmount">Payment Amount</label>
                            <input type="number" class="form-input" id="paymentAmount" name="paymentAmount" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="paymentDate">Payment Date</label>
                            <input type="date" class="form-input" id="paymentDate" name="paymentDate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="paymentMethodSelect">Payment Method</label>
                            <select class="form-select" id="paymentMethodSelect" name="paymentMethodSelect" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="online">Online Payment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="transactionId">Transaction ID/Reference</label>
                            <input type="text" class="form-input" id="transactionId" name="transactionId" placeholder="Enter transaction reference number">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="receivedBy">Received By</label>
                            <input type="text" class="form-input" id="receivedBy" name="receivedBy" placeholder="Enter name of receiver">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label" for="receiptNotes">Notes</label>
                            <textarea class="form-textarea" id="receiptNotes" name="receiptNotes" placeholder="Enter any additional information about this payment..."></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelReceiptBtn">Cancel</button>
                        <button type="button" class="btn btn-outline" id="previewReceiptBtn">Preview</button>
                        <button type="submit" class="btn btn-primary">Create Receipt</button>
                    </div>
                </form>
            </div>

            <!-- Receipt Preview -->
            <div class="receipt-preview-container" id="receiptPreview" style="display: none; position: relative;">
                <div class="receipt-watermark">PREVIEW</div>
                
                <div class="receipt-school-header">
                    <h2 class="school-name">Vinod Henglish School</h2>
                    <p class="school-address">123 Education Lane, Academic District, City, 12345</p>
                </div>
                
                <div class="receipt-title-section">
                    <h3 class="receipt-title">PAYMENT RECEIPT</h3>
                    <p class="receipt-subtitle">Official Receipt for Fee Payment</p>
                </div>
                
                <div class="receipt-details">
                    <div class="receipt-student-details">
                        <h4 style="margin-bottom: 0.5rem; color: #4b5563;">Student Information</h4>
                        <div class="receipt-detail-item">
                            <span class="receipt-detail-label">Student Name:</span>
                            <span class="receipt-detail-value" id="previewStudentName">Alex Brown</span>
                        </div>
                        <div class="receipt-detail-item">
                            <span class="receipt-detail-label">Student ID:</span>
                            <span class="receipt-detail-value" id="previewStudentId">ST001</span>
                        </div>
                        <div class="receipt-detail-item">
                            <span class="receipt-detail-label">Class/Grade:</span>
                            <span class="receipt-detail-value" id="previewClass">Grade 10A</span>
                        </div>
                        <div class="receipt-detail-item">
                            <span class="receipt-detail-label">Academic Year:</span>
                            <span class="receipt-detail-value" id="previewAcademicYear">2024-2025</span>
                        </div>
                    </div>
                    
                    <div class="receipt-payment-details">
                        <h4 style="margin-bottom: 0.5rem; color: #4b5563;">Payment Information</h4>
                        <div class="receipt-detail-item">
                            <span class="receipt-detail-label">Receipt Number:</span>
                            <span class="receipt-detail-value" id="previewReceiptNumber">RCPT-2025-10001</span>
                        </div>
                        <div class="receipt-detail-item">
                            <span class="receipt-detail-label">Payment Date:</span>
                            <span class="receipt-detail-value" id="previewPaymentDate">March 15, 2025</span>
                        </div>
                        <div class="receipt-detail-item">
                            <span class="receipt-detail-label">Payment Method:</span>
                            <span class="receipt-detail-value" id="previewPaymentMethod">Bank Transfer</span>
                        </div>
                        <div class="receipt-detail-item">
                            <span class="receipt-detail-label">Transaction ID:</span>
                            <span class="receipt-detail-value" id="previewTransactionId">TRX123456789</span>
                        </div>
                    </div>
                </div>
                
                <div class="receipt-items">
                    <h4 class="receipt-items-title">Payment Details</h4>
                    <table class="receipt-items-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>Fee Period</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td id="previewFeeDescription">Tuition Fee</td>
                                <td id="previewFeePeriod">2024-2025 Academic Year</td>
                                <td id="previewFeeAmount">$5,025.00</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="receipt-total-section">
                        <div class="receipt-total-item receipt-subtotal">
                            <span class="receipt-total-label">Subtotal:</span>
                            <span id="previewSubtotal">$5,025.00</span>
                        </div>
                        <div class="receipt-total-item receipt-discount" id="previewDiscountRow" style="display: none;">
                            <span class="receipt-total-label">Discount:</span>
                            <span id="previewDiscount">$0.00 (0%)</span>
                        </div>
                        <div class="receipt-total-item receipt-grand-total">
                            <span class="receipt-total-label">Total Paid:</span>
                            <span id="previewGrandTotal">$5,025.00</span>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <div style="font-weight: 500; color: #4b5563; margin-bottom: 0.5rem;">Amount in Words:</div>
                    <div id="previewAmountInWords" style="border: 1px solid #e5e7eb; padding: 0.75rem; border-radius: 6px; background: #f9fafb;">
                        Five Thousand Twenty-Five Dollars Only
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <div style="font-weight: 500; color: #4b5563; margin-bottom: 0.5rem;">Notes:</div>
                    <div id="previewNotes" style="border: 1px solid #e5e7eb; padding: 0.75rem; border-radius: 6px; background: #f9fafb; min-height: 60px;">
                        First installment payment for annual tuition fees.
                    </div>
                </div>
                
                <div class="receipt-signature">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-name" id="previewReceivedBy">John Doe</div>
                        <div class="signature-title">Received By</div>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <div class="signature-name">Parent/Guardian</div>
                        <div class="signature-title">Signature</div>
                    </div>
                </div>
                
                <div class="receipt-footer">
                    <p>This is a computer-generated receipt and does not require a physical signature.</p>
                    <p>Thank you for your payment. For any queries, please contact the finance office.</p>
                </div>
                
                <div class="form-actions" style="margin-top: 2rem;">
                    <button type="button" class="btn btn-outline" id="backToFormBtn">Back to Form</button>
                    <button type="button" class="btn btn-outline" id="printPreviewBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </button>
                    <button type="button" class="btn btn-primary" id="saveReceiptBtn">Save Receipt</button>
                </div>
            </div>

            <!-- All Receipts Tab Content -->
            <div class="tab-content active" id="all-tab">
                <div class="performance-metrics">
                    <div class="metric-card metric-total">
                        <h3 class="metric-title">Total Receipts</h3>
                        <div class="metric-value">2,548</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">7.5% from last month</span>
                        </div>
                    </div>
                    <div class="metric-card metric-pending">
                        <h3 class="metric-title">Total Collected</h3>
                        <div class="metric-value">$256,482</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">12.3% from last month</span>
                        </div>
                    </div>
                    <div class="metric-card metric-today">
                        <h3 class="metric-title">Today's Receipts</h3>
                        <div class="metric-value">38</div>
                        <div class="metric-indicator">
                            <span style="color: #6b7280;">$5,845 collected today</span>
                        </div>
                    </div>
                    <div class="metric-card metric-online">
                        <h3 class="metric-title">Online Payments</h3>
                        <div class="metric-value">65%</div>
                        <div class="metric-indicator">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="indicator-positive">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                            </svg>
                            <span class="indicator-positive">8.2% from last month</span>
                        </div>
                    </div>
                </div>

                <!-- Bulk Action Panel -->
                <div class="bulk-action-panel">
                    <div class="bulk-action-left">
                        <input type="checkbox" id="selectAllReceipts">
                        <span class="bulk-action-count">0 receipts selected</span>
                    </div>
                    <div class="bulk-action-right">
                        <button class="btn btn-outline" disabled>
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Email
                        </button>
                        <button class="btn btn-outline" disabled>
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Print
                        </button>
                        <button class="btn btn-outline" disabled>
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export
                        </button>
                    </div>
                </div>
                
                <div class="receipts-table-container">
                    <table class="receipts-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllInTable"></th>
                                <th>Receipt #</th>
                                <th>Student</th>
                                <th>Fee Type</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" class="receipt-checkbox"></td>
                                <td>RCPT-2025-10035</td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">AB</div>
                                        <div class="student-details">
                                            <span class="student-name">Alex Brown</span>
                                            <span class="student-id">ID: ST001</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Tuition Fee</td>
                                <td>$5,025.00</td>
                                <td>Bank Transfer</td>
                                <td>Mar 15, 2025</td>
                                <td><span class="status-badge status-success">Success</span></td>
                                <td class="receipt-actions">
                                    <button class="action-btn" title="View Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Print Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Email Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download PDF">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="receipt-checkbox"></td>
                                <td>RCPT-2025-10034</td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">ES</div>
                                        <div class="student-details">
                                            <span class="student-name">Emma Smith</span>
                                            <span class="student-id">ID: ST002</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Tuition Fee</td>
                                <td>$5,025.00</td>
                                <td>Credit Card</td>
                                <td>Mar 15, 2025</td>
                                <td><span class="status-badge status-success">Success</span></td>
                                <td class="receipt-actions">
                                    <button class="action-btn" title="View Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Print Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Email Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download PDF">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="receipt-checkbox"></td>
                                <td>RCPT-2025-10033</td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">MJ</div>
                                        <div class="student-details">
                                            <span class="student-name">Michael Johnson</span>
                                            <span class="student-id">ID: ST003</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Library Fee</td>
                                <td>$500.00</td>
                                <td>Cash</td>
                                <td>Mar 14, 2025</td>
                                <td><span class="status-badge status-success">Success</span></td>
                                <td class="receipt-actions">
                                    <button class="action-btn" title="View Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Print Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Email Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Download PDF">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="receipt-checkbox"></td>
                                <td>RCPT-2025-10032</td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">SD</div>
                                        <div class="student-details">
                                            <span class="student-name">Sophia Davis</span>
                                            <span class="student-id">ID: ST004</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Transportation Fee</td>
                                <td>$600.00</td>
                                <td>Online</td>
                                <td>Mar 14, 2025</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td class="receipt-actions">
                                    <button class="action-btn" title="View Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Verify Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Cancel Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="receipt-checkbox"></td>
                                <td>RCPT-2025-10031</td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">WM</div>
                                        <div class="student-details">
                                            <span class="student-name">William Miller</span>
                                            <span class="student-id">ID: ST005</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Tuition Fee</td>
                                <td>$5,025.00</td>
                                <td>Check</td>
                                <td>Mar 13, 2025</td>
                                <td><span class="status-badge status-failed">Failed</span></td>
                                <td class="receipt-actions">
                                    <button class="action-btn" title="View Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Retry Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Create New Receipt">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="pagination">
                    <div class="pagination-info">
                        Showing 1-5 of 2,548 receipts
                    </div>
                    <div class="pagination-buttons">
                        <button class="page-btn" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <button class="page-btn">4</button>
                        <button class="page-btn">5</button>
                        <button class="page-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Receipts Tab Content -->
            <div class="tab-content" id="recent-tab">
                <div class="receipt-cards">
                    <div class="receipt-card">
                        <div class="receipt-card-header">
                            <div>
                                <div class="receipt-number">RCPT-2025-10035</div>
                                <div class="receipt-date">Mar 15, 2025</div>
                            </div>
                            <span class="status-badge status-success">Success</span>
                        </div>
                        
                        <div class="receipt-card-content">
                            <div class="receipt-student">
                                <div class="receipt-avatar">AB</div>
                                <div class="receipt-student-info">
                                    <div class="receipt-student-name">Alex Brown</div>
                                    <div class="receipt-student-details">ID: ST001 • Grade 10A</div>
                                </div>
                            </div>
                            
                            <div class="receipt-details-list">
                                <div class="receipt-detail">
                                    <span class="receipt-detail-key">Fee Type:</span>
                                    <span class="receipt-detail-val">Tuition Fee</span>
                                </div>
                                <div class="receipt-detail">
                                    <span class="receipt-detail-key">Payment Method:</span>
                                    <span class="receipt-detail-val">Bank Transfer</span>
                                </div>
                                <div class="receipt-detail">
                                    <span class="receipt-detail-key">Transaction ID:</span>
                                    <span class="receipt-detail-val">TRX123456789</span>
                                </div>
                            </div>
                            
                            <div class="receipt-amount">
                                <span class="receipt-amount-label">Amount Paid:</span>
                                <span class="receipt-amount-value">$5,025.00</span>
                            </div>
                        </div>
                        
                        <div class="receipt-card-actions">
                            <div class="receipt-method">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span>Bank Transfer</span>
                            </div>
                            <div class="receipt-card-buttons">
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">View</button>
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Print</button>
                            </div>
                        </div>
                    </div>

                    <div class="receipt-card">
                        <div class="receipt-card-header">
                            <div>
                                <div class="receipt-number">RCPT-2025-10034</div>
                                <div class="receipt-date">Mar 15, 2025</div>
                            </div>
                            <span class="status-badge status-success">Success</span>
                        </div>
                        
                        <div class="receipt-card-content">
                            <div class="receipt-student">
                                <div class="receipt-avatar">ES</div>
                                <div class="receipt-student-info">
                                    <div class="receipt-student-name">Emma Smith</div>
                                    <div class="receipt-student-details">ID: ST002 • Grade 10A</div>
                                </div>
                            </div>
                            
                            <div class="receipt-details-list">
                                <div class="receipt-detail">
                                    <span class="receipt-detail-key">Fee Type:</span>
                                    <span class="receipt-detail-val">Tuition Fee</span>
                                </div>
                                <div class="receipt-detail">
                                    <span class="receipt-detail-key">Payment Method:</span>
                                    <span class="receipt-detail-val">Credit Card</span>
                                </div>
                                <div class="receipt-detail">
                                    <span class="receipt-detail-key">Transaction ID:</span>
                                    <span class="receipt-detail-val">CC98765432</span>
                                </div>
                            </div>
                            
                            <div class="receipt-amount">
                                <span class="receipt-amount-label">Amount Paid:</span>
                                <span class="receipt-amount-value">$5,025.00</span>
                            </div>
                        </div>
                        
                        <div class="receipt-card-actions">
                            <div class="receipt-method">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span>Credit Card</span>
                            </div>
                            <div class="receipt-card-buttons">
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">View</button>
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Print</button>
                            </div>
                        </div>
                    </div>

                    <div class="receipt-card">
                        <div class="receipt-card-header">
                            <div>
                                <div class="receipt-number">RCPT-2025-10033</div>
                                <div class="receipt-date">Mar 14, 2025</div>
                            </div>
                            <span class="status-badge status-success">Success</span>
                        </div>
                        
                        <div class="receipt-card-content">
                            <div class="receipt-student">
                                <div class="receipt-avatar">MJ</div>
                                <div class="receipt-student-info">
                                    <div class="receipt-student-name">Michael Johnson</div>
                                    <div class="receipt-student-details">ID: ST003 • Grade 10A</div>
                                </div>
                            </div>
                            
                            <div class="receipt-details-list">
                                <div class="receipt-detail">
                                    <span class="receipt-detail-key">Fee Type:</span>
                                    <span class="receipt-detail-val">Library Fee</span>
                                </div>
                                <div class="receipt-detail">
                                    <span class="receipt-detail-key">Payment Method:</span>
                                    <span class="receipt-detail-val">Cash</span>
                                </div>
                                <div class="receipt-detail">
                                    <span class="receipt-detail-key">Received By:</span>
                                    <span class="receipt-detail-val">Jane Doe</span>
                                </div>
                            </div>
                            
                            <div class="receipt-amount">
                                <span class="receipt-amount-label">Amount Paid:</span>
                                <span class="receipt-amount-value">$500.00</span>
                            </div>
                        </div>
                        
                        <div class="receipt-card-actions">
                            <div class="receipt-method">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span>Cash</span>
                            </div>
                            <div class="receipt-card-buttons">
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">View</button>
                                <button class="btn btn-outline" style="padding: 0.4rem 0.75rem; font-size: 0.875rem;">Print</button>
                            </div>
                        </div>
                    </div>

                    <div class="receipt-card">
                        <div class="receipt-card-header">
                            <div>
                                <div class="receipt-number">RCPT-2025-10032</div>
                                <div class="receipt-date">Mar 14, 2025</div>
                            </div>
                            <span class="status-badge status-pending">Pending</span>
                        </div>
                        
                        <div class="receipt-card-content">
                            <div class="receipt