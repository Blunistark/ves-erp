<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Collection</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        .dashboard-content {
            padding: 20px;
        }
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
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
        
        table {
            width: 100%;
            border-collapse: collapse;
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
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .close {
            font-size: 24px;
            font-weight: bold;
            color: #555;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
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
        
        .fee-summary {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 4px;
        }
        
        .fee-summary-item {
            flex: 1;
            min-width: 150px;
            padding: 10px;
            background-color: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        
        .fee-summary-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .fee-summary-value {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        
        .fee-summary-value.due {
            color: #ef4444;
        }
        
        .fee-summary-value.paid {
            color: #10b981;
        }
        
        .payment-history {
            margin-top: 20px;
        }
        
        .payment-history h3 {
            margin-bottom: 10px;
        }
        
        .payment-history table {
            margin-top: 10px;
        }
        
        .search-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-container .form-input {
            flex: 1;
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
            <h1 class="header-title">Fee Collection</h1>
            <span class="header-path">Dashboard > Fee Management > Fee Collection</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div>
                    <h2>Student Fee Collection</h2>
                    <p>Record fee payments and track student payment status</p>
                </div>
                <button class="btn btn-primary" id="recordPaymentBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Record Payment
                </button>
            </div>
            
            <!-- Student Search Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Search Student</h3>
                </div>
                <div class="search-container">
                    <input type="text" id="studentSearch" class="form-input" placeholder="Search student by name, admission number or class...">
                    <button class="btn btn-primary" id="searchStudentBtn">Search</button>
                </div>
            </div>
            
            <!-- Fee Information Card -->
            <div class="card" id="feeInfoCard" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">Fee Information</h3>
                </div>
                <div class="fee-summary" id="feeSummary"></div>
                
                <div class="payment-history">
                    <h3>Payment History</h3>
                    <div id="paymentHistoryContainer"></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Record Payment Modal -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Record Fee Payment</h2>
                <span class="close">&times;</span>
            </div>
            <form id="paymentForm">
                <div class="form-group">
                    <label class="form-label" for="studentSelect">Select Student</label>
                    <select class="form-select" id="studentSelect" name="student_id" required>
                        <option value="">Select Student</option>
                        <!-- Will be populated dynamically -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="feeStructureSelect">Fee Structure</label>
                    <select class="form-select" id="feeStructureSelect" name="fee_structure_id" required>
                        <option value="">Select Fee Structure</option>
                        <!-- Will be populated dynamically after selecting student -->
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="amountPaid">Amount Paid</label>
                        <input type="number" class="form-input" id="amountPaid" name="amount_paid" min="0" step="0.01" required placeholder="Enter payment amount">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="paymentDate">Payment Date</label>
                        <input type="date" class="form-input" id="paymentDate" name="payment_date" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="paymentMethod">Payment Method</label>
                    <select class="form-select" id="paymentMethod" name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="cash">Cash</option>
                        <option value="cheque">Cheque</option>
                        <option value="online">Online Transfer</option>
                        <option value="upi">UPI</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="referenceNumber">Reference Number (if applicable)</label>
                    <input type="text" class="form-input" id="referenceNumber" name="reference_number" placeholder="Cheque number, transaction ID, etc.">
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="remarks">Remarks</label>
                    <textarea class="form-textarea" id="remarks" name="remarks" placeholder="Add any additional information about this payment"></textarea>
                </div>
                
                <div style="display: flex; justify-content: flex-end; margin-top: 20px; gap: 10px;">
                    <button type="button" class="btn btn-outline close-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const paymentModal = document.getElementById('paymentModal');
    const paymentForm = document.getElementById('paymentForm');
    const recordPaymentBtn = document.getElementById('recordPaymentBtn');
    const searchStudentBtn = document.getElementById('searchStudentBtn');
    const studentSearch = document.getElementById('studentSearch');
    const feeInfoCard = document.getElementById('feeInfoCard');
    const feeSummary = document.getElementById('feeSummary');
    const paymentHistoryContainer = document.getElementById('paymentHistoryContainer');
    const studentSelect = document.getElementById('studentSelect');
    const feeStructureSelect = document.getElementById('feeStructureSelect');
    
    // Set today's date as default for payment date
    document.getElementById('paymentDate').valueAsDate = new Date();
    
    // Event Listeners
    recordPaymentBtn.addEventListener('click', function() {
        paymentModal.style.display = 'block';
    });
    
    searchStudentBtn.addEventListener('click', searchStudent);
    
    // Close modal buttons
    document.querySelectorAll('.close, .close-modal').forEach(button => {
        button.addEventListener('click', function() {
            paymentModal.style.display = 'none';
        });
    });
    
    // When clicked outside of modal, close it
    window.addEventListener('click', function(event) {
        if (event.target === paymentModal) {
            paymentModal.style.display = 'none';
        }
    });
    
    // Form submission
    paymentForm.addEventListener('submit', recordPayment);
    
    // Student selection change
    studentSelect.addEventListener('change', loadStudentFeeStructures);
    
    // Load students on page load
    loadStudents();
    
    // Functions
    function loadStudents() {
        fetch('fee_collection_action.php?action=get_students')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    studentSelect.innerHTML = '<option value="">Select Student</option>';
                    
                    data.students.forEach(student => {
                        const option = document.createElement('option');
                        option.value = student.user_id;
                        option.textContent = `${student.admission_number} - ${student.full_name} (${student.class_name} ${student.section_name})`;
                        studentSelect.appendChild(option);
                    });
                } else {
                    console.error('Failed to load students:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    function loadStudentFeeStructures() {
        const studentId = studentSelect.value;
        if (!studentId) {
            feeStructureSelect.innerHTML = '<option value="">Select Fee Structure</option>';
            return;
        }
        
        fetch(`fee_collection_action.php?action=get_student_fee_structures&student_id=${studentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    feeStructureSelect.innerHTML = '<option value="">Select Fee Structure</option>';
                    
                    data.structures.forEach(structure => {
                        const option = document.createElement('option');
                        option.value = structure.id;
                        option.textContent = `${structure.title} - ${structure.class_name} (₹${structure.amount})`;
                        // If there's remaining amount, add it to the option text
                        if (structure.remaining_amount > 0) {
                            option.textContent += ` - Due: ₹${structure.remaining_amount}`;
                        }
                        feeStructureSelect.appendChild(option);
                    });
                } else {
                    console.error('Failed to load fee structures:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }
    
    function searchStudent() {
        const query = studentSearch.value.trim();
        if (!query) {
            alert('Please enter a search term');
            return;
        }
        
        fetch(`fee_collection_action.php?action=search_student&query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.student) {
                    displayStudentFeeInfo(data.student);
                } else {
                    alert('No student found with this search term');
                    feeInfoCard.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while searching for student');
            });
    }
    
    function displayStudentFeeInfo(student) {
        // Display fee summary
        feeSummary.innerHTML = `
            <div class="fee-summary-item">
                <div class="fee-summary-label">Student Name</div>
                <div class="fee-summary-value">${student.full_name}</div>
            </div>
            <div class="fee-summary-item">
                <div class="fee-summary-label">Admission Number</div>
                <div class="fee-summary-value">${student.admission_number}</div>
            </div>
            <div class="fee-summary-item">
                <div class="fee-summary-label">Class</div>
                <div class="fee-summary-value">${student.class_name} ${student.section_name}</div>
            </div>
            <div class="fee-summary-item">
                <div class="fee-summary-label">Total Fees</div>
                <div class="fee-summary-value">₹${student.total_fees.toFixed(2)}</div>
            </div>
            <div class="fee-summary-item">
                <div class="fee-summary-label">Paid Amount</div>
                <div class="fee-summary-value paid">₹${student.paid_amount.toFixed(2)}</div>
            </div>
            <div class="fee-summary-item">
                <div class="fee-summary-label">Due Amount</div>
                <div class="fee-summary-value due">₹${student.due_amount.toFixed(2)}</div>
            </div>
        `;
        
        // Display payment history
        if (student.payment_history && student.payment_history.length > 0) {
            const table = document.createElement('table');
            table.innerHTML = `
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Fee Structure</th>
                        <th>Amount Paid</th>
                        <th>Remaining</th>
                        <th>Status</th>
                        <th>Receipt</th>
                    </tr>
                </thead>
                <tbody></tbody>
            `;
            
            const tbody = table.querySelector('tbody');
            
            student.payment_history.forEach(payment => {
                const row = document.createElement('tr');
                
                // Format date
                const paymentDate = new Date(payment.payment_date);
                const formattedDate = paymentDate.toLocaleDateString();
                
                // Determine status badge class
                let statusBadgeClass = '';
                switch(payment.status) {
                    case 'paid':
                        statusBadgeClass = 'badge-success';
                        break;
                    case 'partial':
                        statusBadgeClass = 'badge-warning';
                        break;
                    case 'pending':
                        statusBadgeClass = 'badge-danger';
                        break;
                }
                
                row.innerHTML = `
                    <td>${formattedDate}</td>
                    <td>${payment.fee_title}</td>
                    <td>₹${payment.amount_paid}</td>
                    <td>₹${payment.remaining_amount}</td>
                    <td><span class="badge ${statusBadgeClass}">${payment.status.charAt(0).toUpperCase() + payment.status.slice(1)}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline" onclick="generateReceipt(${payment.id})">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Receipt
                        </button>
                    </td>
                `;
                
                tbody.appendChild(row);
            });
            
            paymentHistoryContainer.innerHTML = '';
            paymentHistoryContainer.appendChild(table);
        } else {
            paymentHistoryContainer.innerHTML = `
                <div class="empty-state">
                    <p>No payment history found for this student.</p>
                </div>
            `;
        }
        
        // Show the fee info card
        feeInfoCard.style.display = 'block';
    }
    
    function recordPayment(event) {
        event.preventDefault();
        
        // Validate form
        if (!paymentForm.checkValidity()) {
            return;
        }
        
        // Collect form data
        const formData = new FormData(paymentForm);
        formData.append('action', 'record_payment');
        
        // Send request
        fetch('fee_collection_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                paymentModal.style.display = 'none';
                paymentForm.reset();
                document.getElementById('paymentDate').valueAsDate = new Date();
                alert(data.message);
                
                // If we were viewing a student's details, refresh them
                if (feeInfoCard.style.display !== 'none') {
                    searchStudent();
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while recording the payment.');
        });
    }
});

// Function to generate receipt - needs to be in global scope
function generateReceipt(paymentId) {
    window.open(`fee_receipt.php?payment_id=${paymentId}`, '_blank');
}
</script>

</body>
</html> 