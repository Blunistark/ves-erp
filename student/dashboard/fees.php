<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fees Tracking</title>
    
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
        <h1 class="header-title">Fees Tracking</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div id="loading-container" class="loading-container">
            <div class="loading-spinner"></div>
            <p>Loading fee information...</p>
            </div>
            
        <div id="fee-content" class="fee-content" style="display:none;">
            <div class="fees-summary" id="fees-summary">
                <!-- Fee statistics will be populated here -->
        </div>
        
        <div class="card">
                <div class="payment-progress" id="payment-progress">
                    <!-- Payment progress will be populated here -->
            </div>
        </div>
        
        <div class="card">
            <h2 class="card-title">Fee Breakdown</h2>
            
                <div class="fee-table-container" id="fee-breakdown-container">
                    <!-- Fee breakdown table will be populated here -->
            </div>
        </div>
        
        <div class="card">
            <div class="payment-history-header">
                <h2 class="history-title">Recent Payment History</h2>
                    <div class="view-all" id="view-all-btn">
                    View All Transactions
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </div>
            </div>
            
                <div class="history-list" id="payment-history-list">
                    <!-- Payment history will be populated here -->
            </div>
        </div>
        
        <div class="card">
            <h2 class="card-title">Payment Methods</h2>
            
            <div class="payment-methods">
                <div class="payment-method">
                    <div class="method-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                    </div>
                    <div class="method-name">Credit Card</div>
                </div>
                
                <div class="payment-method">
                    <div class="method-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="method-name">Bank Transfer</div>
                </div>
                
                <div class="payment-method">
                    <div class="method-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="method-name">Cash Payment</div>
                </div>
                
                <div class="payment-method">
                    <div class="method-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="method-name">Online Banking</div>
                </div>
                
                <div class="payment-method" id="uploadReceiptBtn">
                    <div class="method-icon upload-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                    </div>
                    <div class="method-name">Upload Receipt</div>
                </div>
            </div>
            
            <div class="pay-now-container">
                    <button class="pay-now-button" id="pay-now-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pay Now
                </button>
            </div>
            </div>
        </div>
        
        <div id="error-container" class="error-container" style="display:none;">
            <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 class="error-title">Unable to Load Fee Data</h2>
            <p class="error-message" id="error-message"></p>
            <button class="retry-button" id="retry-btn">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Retry
            </button>
        </div>
    </main>
</div>

<!-- Receipt Modal -->
<div id="receipt-modal" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Fee Receipt</h2>
            <span class="modal-close" id="close-receipt-modal">&times;</span>
        </div>
        <div class="modal-body">
            <iframe id="receipt-frame" style="width:100%; height:500px; border:none;"></iframe>
        </div>
    </div>
</div>

<!-- Payment Proof Modal -->
<div id="paymentProofModal" class="modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
    <div class="modal-dialog" style="width: 90%; max-width: 500px; margin: 30px auto;">
        <div class="modal-content" style="background: white; border-radius: 12px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); max-height: 90vh; overflow-y: auto;">
            <div class="modal-header" style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
                <h3 class="modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 600;">Upload Payment Proof</h3>
                <button type="button" class="close-modal" onclick="closePaymentProofModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <form id="paymentProofForm" enctype="multipart/form-data">
                    <input type="hidden" id="paymentProofFeeId" name="fee_payment_id">
                    <input type="hidden" id="paymentProofStructureId" name="fee_structure_id">
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="paymentProofFile" style="display: block; margin-bottom: 8px; font-weight: 500;">Payment Slip/Receipt Image <span style="color: #ef4444;">*</span></label>
                        <input type="file" id="paymentProofFile" name="proof_image" accept="image/*,.pdf" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                        <p class="form-hint" style="margin-top: 5px; font-size: 0.85em; color: #6b7280;">Upload a clear image of your payment receipt or slip (JPG, PNG, GIF, or PDF)</p>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="paymentMethod" style="display: block; margin-bottom: 8px; font-weight: 500;">Payment Method <span style="color: #ef4444;">*</span></label>
                        <select id="paymentMethod" name="payment_method" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                            <option value="">-- Select Payment Method --</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="upi">UPI Payment</option>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="referenceNumber" style="display: block; margin-bottom: 8px; font-weight: 500;">Reference/Transaction Number</label>
                        <input type="text" id="referenceNumber" name="reference_number" placeholder="Enter transaction reference number if available" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="amountPaid" style="display: block; margin-bottom: 8px; font-weight: 500;">Amount Paid <span style="color: #ef4444;">*</span></label>
                        <input type="number" id="amountPaid" name="amount_paid" min="1" step="0.01" required style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label for="paymentProofRemarks" style="display: block; margin-bottom: 8px; font-weight: 500;">Additional Information</label>
                        <textarea id="paymentProofRemarks" name="remarks" placeholder="Add any additional details about your payment (optional)" style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px;"></textarea>
                    </div>
                    
                    <div id="imagePreview" class="preview-container" style="display: none; margin-top: 20px; padding: 10px; border: 1px dashed #d1d5db; border-radius: 6px;">
                        <p>Image Preview:</p>
                        <img id="proofImagePreview" src="" alt="Payment proof preview" style="max-width: 100%; max-height: 200px; display: block; margin: 0 auto;">
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="padding: 15px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #e5e7eb;">
                <button type="button" class="btn-secondary" onclick="closePaymentProofModal()" style="padding: 10px 20px; background-color: #f3f4f6; color: #1f2937; border: 1px solid #d1d5db; border-radius: 6px; font-weight: 500; cursor: pointer;">Cancel</button>
                <button type="button" class="btn-primary" onclick="submitPaymentProof()" style="padding: 10px 20px; background-color: #4f46e5; color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">Submit Payment Proof</button>
            </div>
        </div>
    </div>
</div>

<!-- Add CSS for loading spinner and error states -->
<style>
    .loading-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 300px;
    }
    
    .loading-spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #4f46e5;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .error-container {
        text-align: center;
        padding: 40px 20px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        
    }
    
    .error-icon {
        width: 60px;
        height: 60px;
        color: #ef4444;
        margin-bottom: 20px;
    }
    
    .error-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 10px;
        color: #1a1a1a;
    }
    
    .error-message {
        font-size: 1rem;
        color: #6b7280;
        margin-bottom: 20px;
    }
    
    .retry-button {
        display: inline-flex;
        align-items: center;
        padding: 10px 20px;
        background: #4f46e5;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .retry-button svg {
        width: 20px;
        height: 20px;
        margin-right: 8px;
    }
    
    .retry-button:hover {
        background: #4338ca;
    }
    
    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        border-radius: 12px;
        width: 90%;
        max-width: 800px;
        max-height: 90vh;
        overflow: auto;
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #e5e7eb;
    }
    
    .modal-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin: 0;
    }
    
    .modal-close {
        font-size: 1.5rem;
        font-weight: 700;
        cursor: pointer;
        color: #6b7280;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .status-paid {
        background-color: #dcfce7;
        color: #166534;
    }
    
    .status-pending {
        background-color: #fef9c3;
        color: #854d0e;
    }
    
    .status-overdue {
        background-color: #fee2e2;
        color: #b91c1c;
    }
    
    .status-verification {
        background-color: #e0f2fe;
        color: #075985;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const feeContent = document.getElementById('fee-content');
        const loadingContainer = document.getElementById('loading-container');
        const errorContainer = document.getElementById('error-container');
        const errorMessage = document.getElementById('error-message');
        const retryBtn = document.getElementById('retry-btn');
        const receiptModal = document.getElementById('receipt-modal');
        const closeReceiptModal = document.getElementById('close-receipt-modal');
        const receiptFrame = document.getElementById('receipt-frame');
        
        // Elements to be populated with data
        const feesSummary = document.getElementById('fees-summary');
        const paymentProgress = document.getElementById('payment-progress');
        const feeBreakdownContainer = document.getElementById('fee-breakdown-container');
        const paymentHistoryList = document.getElementById('payment-history-list');
        const viewAllBtn = document.getElementById('view-all-btn');
        const payNowBtn = document.getElementById('pay-now-btn');
        
        // Load fee data on page load
        loadFeeData();
        
        // Event listeners
        retryBtn.addEventListener('click', loadFeeData);
        closeReceiptModal.addEventListener('click', function() {
            receiptModal.style.display = 'none';
        });
        
        viewAllBtn.addEventListener('click', function() {
            // Scroll to the payment history section
            document.querySelector('.payment-history-header').scrollIntoView({ behavior: 'smooth' });
        });
        
        payNowBtn.addEventListener('click', function() {
            alert('Online payment gateway is not available at the moment. Please visit the school office to make a payment.');
        });
        
        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === receiptModal) {
                receiptModal.style.display = 'none';
            }
        });
        
        // Function to load fee data from the server
        function loadFeeData() {
            // Show loading state
            feeContent.style.display = 'none';
            errorContainer.style.display = 'none';
            loadingContainer.style.display = 'flex';
            
            // Fetch data from the server
            fetch('fees_actions.php?action=get_student_fees')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        renderFeeData(data);
                        loadingContainer.style.display = 'none';
                        feeContent.style.display = 'block';
                    } else {
                        showError(data.message || 'Failed to load fee data');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred while loading fee data. Please try again.');
                });
        }
        
        // Function to render fee data
        function renderFeeData(data) {
            // Render fee summary
            renderFeeSummary(data.summary, data.student_info);
    
            // Render payment progress
            renderPaymentProgress(data.summary);
            
            // Render fee breakdown
            renderFeeBreakdown(data.fee_structures);
            
            // Render payment history
            renderPaymentHistory(data.payment_history);
        }
        
        // Function to render fee summary
        function renderFeeSummary(summary, studentInfo) {
            // Format currency
            const formatCurrency = (amount) => {
                return new Intl.NumberFormat('en-IN', { 
                    style: 'currency', 
                    currency: 'INR',
                    maximumFractionDigits: 2
                }).format(amount);
            };
            
            const totalFees = formatCurrency(summary.total_fees);
            const paidAmount = formatCurrency(summary.paid_amount);
            const dueAmount = formatCurrency(summary.due_amount);
            const nextPayment = summary.next_payment ? formatCurrency(summary.next_payment) : 'No upcoming payments';
            
            let feeSummaryHTML = `
                <div class="fee-stat">
                    <div class="stat-header">
                        <div class="stat-title">Total Fees</div>
                        <div class="stat-icon total-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">${totalFees}</div>
                    <div class="stat-description">Annual school fees for ${studentInfo.class_name}</div>
                </div>
                
                <div class="fee-stat">
                    <div class="stat-header">
                        <div class="stat-title">Paid Amount</div>
                        <div class="stat-icon paid-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">${paidAmount}</div>
                    <div class="stat-description">${summary.paid_percentage}% of total fees paid</div>
                </div>
                
                <div class="fee-stat">
                    <div class="stat-header">
                        <div class="stat-title">Due Amount</div>
                        <div class="stat-icon due-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">${dueAmount}</div>
                    <div class="stat-description">Remaining amount to be paid</div>
                </div>
                
                <div class="fee-stat">
                    <div class="stat-header">
                        <div class="stat-title">Next Payment</div>
                        <div class="stat-icon overdue-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="stat-value">${nextPayment}</div>
                    <div class="stat-description">${summary.next_payment_date ? `Due on ${summary.next_payment_date}` : 'All payments completed'}</div>
                </div>
            `;
            
            feesSummary.innerHTML = feeSummaryHTML;
        }
        
        // Function to render payment progress
        function renderPaymentProgress(summary) {
            const progressPercentage = summary.paid_percentage;
            
            let progressHTML = `
                <div class="progress-header">
                    <div class="progress-title">Payment Progress</div>
                    <div class="progress-percentage">${progressPercentage}%</div>
                </div>
                
                <div class="progress-bar">
                    <div class="progress-value" style="width: ${progressPercentage}%;"></div>
                </div>
                
                <div class="payment-categories">
                    <div class="payment-category">
                        <div class="category-color color-paid"></div>
                        <div>Paid: ${new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(summary.paid_amount)}</div>
                    </div>
                    
                    <div class="payment-category">
                        <div class="category-color color-pending"></div>
                        <div>Pending: ${new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(summary.due_amount)}</div>
                    </div>
                </div>
            `;
            
            paymentProgress.innerHTML = progressHTML;
        }
        
        // Function to render fee breakdown
        function renderFeeBreakdown(feeStructures) {
            if (!feeStructures || feeStructures.length === 0) {
                feeBreakdownContainer.innerHTML = '<p class="empty-message">No fee structures found.</p>';
                return;
            }
            
            let tableHTML = `
                <table class="fee-table">
                    <thead>
                        <tr>
                            <th>Fee Category</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            feeStructures.forEach(structure => {
                // Format date
                const dueDate = new Date(structure.due_date);
                const formattedDate = dueDate.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric' 
                });
                
                // Format currency
                const amount = new Intl.NumberFormat('en-IN', { 
                    style: 'currency', 
                    currency: 'INR'
                }).format(structure.amount);
                
                // Status badge class
                let statusClass = '';
                let statusText = '';
                
                switch(structure.status) {
                    case 'paid':
                        statusClass = 'status-paid';
                        statusText = 'Paid';
                        break;
                    case 'partial':
                        statusClass = 'status-pending';
                        statusText = 'Partial';
                        break;
                    case 'under_verification':
                        statusClass = 'status-verification';
                        statusText = 'Under Verification';
                        break;
                    default:
                        // Check if overdue
                        if (dueDate < new Date()) {
                            statusClass = 'status-overdue';
                            statusText = 'Overdue';
                        } else {
                            statusClass = 'status-pending';
                            statusText = 'Pending';
                        }
                        break;
                }
                
                // Action buttons
                let actionButtons = '';
                if (structure.status === 'paid' || structure.status === 'partial') {
                    // If there are payments, show receipt button
                    actionButtons += `
                        <button class="fee-action view-receipt" data-payments='${JSON.stringify(structure.payments)}'>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </button>
                    `;
                }
                
                if (structure.status !== 'paid') {
                    // If not fully paid, show pay now button
                    actionButtons += `
                        <button class="fee-action pay-now" data-structure-id="${structure.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </button>
                        <a href="upload-payment.php?fee_id=${structure.id}" class="fee-action upload-proof" data-structure-id="${structure.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </a>
                    `;
                }
                
                tableHTML += `
                    <tr>
                        <td>
                            <div class="fee-category">${structure.title}</div>
                            <div class="fee-description">${structure.description || 'No description available'}</div>
                        </td>
                        <td class="fee-amount">${amount}</td>
                        <td><span class="fee-status ${statusClass}">${statusText}</span></td>
                        <td class="due-date">${formattedDate}</td>
                        <td>
                            <div class="fee-actions">
                                ${actionButtons}
                            </div>
                        </td>
                    </tr>
                `;
                
                // Add components if available
                if (structure.components && structure.components.length > 0) {
                    structure.components.forEach(component => {
                        const componentAmount = new Intl.NumberFormat('en-IN', { 
                            style: 'currency', 
                            currency: 'INR'
                        }).format(component.amount);
                        
                        tableHTML += `
                            <tr class="component-row">
                                <td>
                                    <div class="component-name">- ${component.name}</div>
                                </td>
                                <td class="component-amount">${componentAmount}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        `;
                    });
                }
            });
            
            tableHTML += `
                    </tbody>
                </table>
            `;
            
            feeBreakdownContainer.innerHTML = tableHTML;
            
            // Add event listeners for receipt view
            document.querySelectorAll('.view-receipt').forEach(button => {
        button.addEventListener('click', function() {
                    const payments = JSON.parse(this.getAttribute('data-payments'));
                    if (payments && payments.length > 0) {
                        // Show the most recent payment receipt
                        showReceipt(payments[0].id);
                    }
        });
    });
    
            // Add event listeners for pay now buttons
            document.querySelectorAll('.pay-now').forEach(button => {
        button.addEventListener('click', function() {
                    alert('Online payment gateway is not available at the moment. Please visit the school office to make a payment.');
                });
            });
        }
        
        // Function to render payment history
        function renderPaymentHistory(payments) {
            if (!payments || payments.length === 0) {
                paymentHistoryList.innerHTML = '<p class="empty-message">No payment history found.</p>';
                return;
            }
            
            let historyHTML = '';
            
            // Take only the most recent 5 payments
            const recentPayments = payments.slice(0, 5);
            
            recentPayments.forEach(payment => {
                // Format date
                const paymentDate = new Date(payment.payment_date);
                const formattedDate = paymentDate.toLocaleDateString('en-US', { 
                    month: 'long', 
                    day: 'numeric', 
                    year: 'numeric' 
    });
    
                // Format amount
                const amount = new Intl.NumberFormat('en-IN', { 
                    style: 'currency', 
                    currency: 'INR'
                }).format(payment.amount_paid);
                
                historyHTML += `
                    <div class="history-item">
                        <div class="history-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        
                        <div class="history-content">
                            <div class="history-title">${payment.fee_title} Payment</div>
                            <div class="history-date">${formattedDate}</div>
                        </div>
                        
                        <div class="history-amount">${amount}</div>
                    </div>
                `;
            });
            
            paymentHistoryList.innerHTML = historyHTML;
            
            // Add event listeners for payment history items
            document.querySelectorAll('.history-item').forEach((item, index) => {
                item.addEventListener('click', function() {
                    showReceipt(recentPayments[index].id);
                });
            });
        }
        
        // Function to show error message
        function showError(message) {
            loadingContainer.style.display = 'none';
            feeContent.style.display = 'none';
            errorContainer.style.display = 'block';
            errorMessage.textContent = message;
        }
        
        // Function to show receipt
        function showReceipt(paymentId) {
            // Load the receipt in an iframe
            receiptFrame.src = `fees_actions.php?action=download_receipt&payment_id=${paymentId}`;
            receiptModal.style.display = 'block';
        }

        // Ensure the upload button in the payment methods section works correctly
        const uploadReceipt = document.getElementById('uploadReceiptBtn');
        if (uploadReceipt) {
            console.log("Adding click event to Upload Receipt button");
            uploadReceipt.addEventListener('click', function(e) {
                e.preventDefault();
                console.log("Upload Receipt button clicked");
                window.location.href = 'upload-payment.php';
                return false;
            });
        }

        // Function to open payment proof modal
        window.showPaymentProofModal = function(feePaymentId, feeStructureId, dueAmount) {
            console.log("Opening modal with params:", { 
                feePaymentId: feePaymentId, 
                feeStructureId: feeStructureId, 
                dueAmount: dueAmount 
            });
            
            // Reset the form
            document.getElementById('paymentProofForm').reset();
            document.getElementById('imagePreview').style.display = 'none';
            
            // Check if we need to fetch available fee structures
            if (!feeStructureId || feeStructureId === 0) {
                // If called from the general button, try to find the first unpaid fee structure
                const feeStructureElements = document.querySelectorAll('.upload-proof');
                if (feeStructureElements.length > 0) {
                    // Get the first available fee structure ID
                    feeStructureId = feeStructureElements[0].getAttribute('data-structure-id');
                    console.log("Using first available fee structure ID:", feeStructureId);
                } else {
                    console.log("No fee structures found in the DOM, using default value 2");
                    feeStructureId = 2; // Hardcoding to 2 as a fallback since that's what worked in your logs
                }
            }
            
            // Set the fee payment ID and structure ID
            document.getElementById('paymentProofFeeId').value = feePaymentId || 0;
            document.getElementById('paymentProofStructureId').value = feeStructureId;
            console.log("Set fee structure ID to:", feeStructureId);
            
            // Set the amount if available
            if (dueAmount && dueAmount > 0) {
                document.getElementById('amountPaid').value = dueAmount;
            }
            
            // Show the modal
            var modal = document.getElementById('paymentProofModal');
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        };

        // Function to close payment proof modal
        window.closePaymentProofModal = function() {
            var modal = document.getElementById('paymentProofModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Re-enable scrolling
        };

        // Close modal when clicking outside
        document.addEventListener('click', function(event) {
            var modal = document.getElementById('paymentProofModal');
            if (event.target === modal) {
                closePaymentProofModal();
            }
        });

        // Handle form submission
        document.getElementById('paymentProofForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitPaymentProof();
        });

        // Function to preview the selected image
        document.getElementById('paymentProofFile').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('proofImagePreview').src = event.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('imagePreview').style.display = 'none';
            }
        });

        // Function to submit payment proof
        window.submitPaymentProof = function() {
            const form = document.getElementById('paymentProofForm');
            const formData = new FormData(form);
            formData.append('action', 'upload_payment_proof');
            
            console.log("Preparing to submit payment proof with action: upload_payment_proof");
            
            // Check fee structure ID
            const feeStructureId = formData.get('fee_structure_id');
            if (!feeStructureId || feeStructureId === '0') {
                console.log("Warning: Fee structure ID is missing or zero. This may cause errors.");
            }
            
            // Disable submit button to prevent double submission
            const submitBtn = document.querySelector('.modal-footer .btn-primary');
            submitBtn.disabled = true;
            submitBtn.innerText = 'Uploading...';
            
            // Check if file is selected
            const fileInput = document.getElementById('paymentProofFile');
            if (fileInput.files.length === 0) {
                alert('Please select a payment proof image to upload');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Submit Payment Proof';
                return;
            }
            
            // Validate file size and type
            const file = fileInput.files[0];
            const maxFileSize = 5 * 1024 * 1024; // 5MB
            if (file.size > maxFileSize) {
                alert('File size exceeds 5MB limit. Please select a smaller file.');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Submit Payment Proof';
                return;
            }
            
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                alert('Only JPG, PNG, GIF, and PDF files are allowed.');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Submit Payment Proof';
                return;
            }
            
            // Check payment method
            const paymentMethod = document.getElementById('paymentMethod').value;
            if (!paymentMethod) {
                alert('Please select a payment method');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Submit Payment Proof';
                return;
            }
            
            // Check amount paid
            const amountPaid = document.getElementById('amountPaid').value;
            if (!amountPaid || parseFloat(amountPaid) <= 0) {
                alert('Please enter a valid amount paid');
                submitBtn.disabled = false;
                submitBtn.innerText = 'Submit Payment Proof';
                return;
            }
            
            console.log("Submitting form with data:", {
                action: formData.get('action'),
                fee_payment_id: formData.get('fee_payment_id'),
                fee_structure_id: formData.get('fee_structure_id'),
                payment_method: formData.get('payment_method'),
                amount_paid: formData.get('amount_paid'),
                file: {
                    name: file.name,
                    type: file.type,
                    size: file.size + ' bytes (' + (file.size / 1024 / 1024).toFixed(2) + 'MB)'
                }
            });
            
            // Try diagnosing the server environment
            fetch('diagnose.php?check=upload_permissions')
                .then(resp => resp.text())
                .then(text => {
                    console.log("Server environment check:", text);
                })
                .catch(err => {
                    console.log("Failed to check server environment:", err);
                });
            
            // Send the form data to the server
            fetch('fees_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log("Server response status:", response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        try {
                            // Try to parse as JSON
                            const data = JSON.parse(text);
                            console.log("Error response data:", data);
                            throw new Error(data.message || `HTTP error! Status: ${response.status}`);
                        } catch (e) {
                            // If it's not valid JSON, just show the text
                            console.log("Raw server error response:", text || "(empty response)");
                            
                            // Try to diagnose common server errors
                            let errorMessage = `HTTP error! Status: ${response.status}`;
                            if (response.status === 500) {
                                errorMessage = "Server error (500): This could be due to file upload permissions, PHP memory limits, or database issues. Please contact your administrator with this information.";
                            } else if (response.status === 413) {
                                errorMessage = "File too large (413): The server rejected the file as too large. Please use a smaller image.";
                            }
                            
                            throw new Error(errorMessage);
                        }
                    });
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.log("Raw server response (not JSON):", text || "(empty response)");
                        return { success: false, message: "Server returned invalid JSON response" };
                    }
                });
            })
            .then(data => {
                console.log("Success response:", data);
                if (data.success) {
                    alert('Payment proof uploaded successfully. It will be reviewed by a teacher or administrator.');
                    closePaymentProofModal();
                    // Reload the page to show the updated payment status
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to upload payment proof'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading the payment proof: ' + error.message);
            })
            .finally(() => {
                // Re-enable the submit button
                submitBtn.disabled = false;
                submitBtn.innerText = 'Submit Payment Proof';
            });
        };
    });
</script>

<!-- Add CSS for component rows -->
<style>
    .component-row {
        background-color: #f9fafb;
        font-size: 0.9em;
    }
    
    .component-name {
        padding-left: 20px;
        color: #6b7280;
    }
    
    .component-amount {
        color: #6b7280;
    }
    
    .empty-message {
        text-align: center;
        padding: 20px;
        color: #6b7280;
    }
    
    /* Hide the extra form */
    form:not(#paymentProofForm) > div > label[for^="payment"],
    form:not(#paymentProofForm) input[name="proof_image"],
    h3:not(.modal-title):contains("Upload Payment Proof"),
    body > div:not(.dashboard-container):not(.sidebar-overlay):not(.hamburger-btn):not(#receipt-modal):not(#paymentProofModal):not(.modal):not(.modal-overlay) {
        display: none !important;
    }
</style>

<script>
    // This script will hide any duplicate Payment Proof forms on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Hide any static "Upload Payment Proof" forms
        const mainElements = document.querySelectorAll('body > *:not(.dashboard-container):not(.sidebar-overlay):not(.hamburger-btn):not(#receipt-modal):not(#paymentProofModal):not(.modal):not(.modal-overlay)');
        mainElements.forEach(function(el) {
            if (el.textContent.includes('Upload Payment Proof') || 
                el.textContent.includes('Payment Slip/Receipt Image') ||
                el.textContent.includes('Submit Payment Proof')) {
                el.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>