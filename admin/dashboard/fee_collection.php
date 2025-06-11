<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Collection</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
   <style>
        /* Dashboard container responsive styles */
        .dashboard-container {
            margin-left: 280px;
            transition: all 0.3s ease;
            position: relative;
            min-height: 100vh;
            overflow-y: auto;
            background-color: #f8fafc;
        }

        /* Header styles */
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
            border-radius: 0 0 12px 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header-title {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .header-path {
            opacity: 0.9;
            font-size: 0.875rem;
        }

        .dashboard-content {
            padding: 2rem;
        }
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            gap: 1rem;
        }

        .action-bar h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #1f2937;
        }

        .action-bar p {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
        }
        
        .form-select,
        .form-input,
        .form-textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s ease;
            background-color: white;
            min-height: 44px;
            box-sizing: border-box;
        }
        
        .form-select:focus,
        .form-input:focus,
        .form-textarea:focus {
            border-color: #4f46e5;
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
            min-height: 44px;
            gap: 0.5rem;
        }
        
        .btn-icon {
            width: 1rem;
            height: 1rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
            min-height: 36px;
        }

        .btn-sm .btn-icon {
            width: 0.875rem;
            height: 0.875rem;
        }
        
        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.4);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid #e5e7eb;
            color: #6b7280;
        }
        
        .btn-outline:hover {
            background-color: #f9fafb;
            border-color: #d1d5db;
        }
        
        .btn-success {
            background-color: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background-color: #059669;
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
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        th, td {
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #f3f4f6;
            text-align: left;
            font-size: 0.875rem;
        }
        
        th {
            font-weight: 600;
            color: #374151;
            background-color: #f9fafb;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        tbody tr:hover {
            background-color: #f9fafb;
        }
        
        .badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
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
            overflow-y: auto;
        }
        
        .modal-content {
            background-color: #fff;
            margin: 2rem auto;
            padding: 2rem;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        
        .close {
            font-size: 1.5rem;
            font-weight: bold;
            color: #6b7280;
            cursor: pointer;
            padding: 0.25rem;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        
        .close:hover {
            color: #374151;
            background-color: #f3f4f6;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }
        
        .empty-state-icon {
            width: 3.75rem;
            height: 3.75rem;
            margin: 0 auto 1.5rem;
            color: #d1d5db;
        }
        
        .fee-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            padding: 1.5rem;
            background-color: #f9fafb;
            border-radius: 8px;
            border: 1px solid #f3f4f6;
        }
        
        .fee-summary-item {
            padding: 1rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        .fee-summary-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
        }
        
        .fee-summary-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        
        .fee-summary-value.due {
            color: #ef4444;
        }
        
        .fee-summary-value.paid {
            color: #10b981;
        }
        
        .payment-history {
            margin-top: 1.5rem;
        }
        
        .payment-history h3 {
            margin-bottom: 1rem;
            font-size: 1.125rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .search-container {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .search-container .form-input {
            flex: 1;
        }

        /* Hamburger button */
        .hamburger-btn {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            padding: 0.5rem;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .hamburger-icon {
            width: 1.5rem;
            height: 1.5rem;
            color: #374151;
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 998;
        }

        /* Ensure sidebar is in front of overlay */
        .sidebar {
            z-index: 1000;
        }

        .sidebar.show {
            z-index: 1000;
        }

        /* Tablet responsive styles */
        @media (max-width: 1024px) {
            .dashboard-container {
                margin-left: 60px;
            }

            .dashboard-content {
                padding: 1.5rem;
            }

            .dashboard-header {
                padding: 1.5rem;
            }

            .action-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .fee-summary {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 0.75rem;
                padding: 1rem;
            }

            .modal-content {
                width: 95%;
                margin: 1rem auto;
                padding: 1.5rem;
            }
        }

        /* Mobile responsive styles */
        @media (max-width: 768px) {
            .hamburger-btn {
                display: block;
            }

            body.sidebar-open .sidebar-overlay {
                display: block;
            }

            .dashboard-container {
                margin-left: 0;
                padding-top: 4rem; /* Account for hamburger button */
            }

            .dashboard-content {
                padding: 1rem;
            }

            .dashboard-header {
                padding: 1rem;
                margin-bottom: 1rem;
                border-radius: 0;
            }

            .header-title {
                font-size: 1.5rem;
            }

            .action-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                margin-bottom: 1.5rem;
            }

            .action-bar h2 {
                font-size: 1.25rem;
            }

            .card {
                padding: 1rem;
                margin-bottom: 1rem;
                border-radius: 8px;
            }

            .card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
                margin-bottom: 1rem;
                padding-bottom: 0.75rem;
            }

            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .fee-summary {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
                padding: 1rem;
            }

            .fee-summary-item {
                padding: 0.75rem;
            }

            .fee-summary-label {
                font-size: 0.625rem;
            }

            .fee-summary-value {
                font-size: 1rem;
            }

            .search-container {
                flex-direction: column;
                gap: 0.75rem;
            }

            .search-container .form-input,
            .search-container .btn {
                width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .modal-content {
                width: 95%;
                margin: 0.5rem auto;
                padding: 1rem;
                border-radius: 8px;
            }

            .modal-header {
                margin-bottom: 1rem;
                padding-bottom: 0.75rem;
            }

            .modal-title {
                font-size: 1.125rem;
            }

            /* Table responsive */
            table {
                font-size: 0.75rem;
            }

            th, td {
                padding: 0.5rem 0.75rem;
            }

            /* Make table horizontally scrollable */
            .payment-history {
                overflow-x: auto;
            }

            .payment-history table {
                min-width: 600px;
            }

            .form-control {
                font-size: 16px; /* Prevent zoom on iOS */
            }
        }

        /* Small mobile responsive styles */
        @media (max-width: 480px) {
            .dashboard-container {
                padding-top: 3.5rem;
            }

            .dashboard-content {
                padding: 0.75rem;
            }

            .dashboard-header {
                padding: 0.75rem;
            }

            .header-title {
                font-size: 1.25rem;
            }

            .header-path {
                font-size: 0.75rem;
            }

            .action-bar h2 {
                font-size: 1.125rem;
            }

            .action-bar p {
                font-size: 0.75rem;
            }

            .card {
                padding: 0.75rem;
            }

            .card-title {
                font-size: 1rem;
            }

            .fee-summary {
                grid-template-columns: 1fr;
                gap: 0.5rem;
                padding: 0.75rem;
            }

            .fee-summary-item {
                padding: 0.5rem;
            }

            .fee-summary-label {
                font-size: 0.625rem;
                margin-bottom: 0.25rem;
            }

            .fee-summary-value {
                font-size: 0.875rem;
            }

            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.8125rem;
            }

            .btn-sm {
                padding: 0.5rem 0.75rem;
                font-size: 0.6875rem;
                min-height: 32px;
            }

            .modal-content {
                margin: 0.25rem auto;
                padding: 0.75rem;
            }

            .modal-title {
                font-size: 1rem;
            }

            .form-label {
                font-size: 0.75rem;
            }

            .form-select,
            .form-input,
            .form-textarea {
                padding: 0.625rem;
                font-size: 0.8125rem;
            }

            table {
                font-size: 0.6875rem;
            }

            th, td {
                padding: 0.375rem 0.5rem;
            }

            .badge {
                font-size: 0.625rem;
                padding: 0.125rem 0.5rem;
            }
        }

        /* Fix for very small screens */
        @media (max-width: 320px) {
            .dashboard-container {
                padding-top: 3rem;
            }

            .dashboard-content {
                padding: 0.5rem;
            }

            .fee-summary {
                grid-template-columns: 1fr;
                padding: 0.5rem;
            }

            .fee-summary-item {
                padding: 0.375rem;
            }

            .btn {
                padding: 0.625rem 0.75rem;
                font-size: 0.75rem;
            }

            .modal-content {
                margin: 0.125rem auto;
                padding: 0.5rem;
            }
        }

        /* Ensure proper text wrapping */
        .card-title,
        .modal-title,
        .action-bar h2,
        .fee-summary-label,
        .fee-summary-value {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Loading and empty states */
        @media (max-width: 768px) {
            .empty-state {
                padding: 2rem 1rem;
                font-size: 0.875rem;
            }

            .empty-state-icon {
                width: 3rem;
                height: 3rem;
                margin-bottom: 1rem;
            }
        }

        /* Form validation styles */
        .form-select:invalid,
        .form-input:invalid {
            border-color: #ef4444;
        }

        .form-select:valid,
        .form-input:valid {
            border-color: #10b981;
        }

        /* Button loading state */
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Responsive modal for very small screens */
        @media (max-height: 600px) {
            .modal-content {
                margin: 0.5rem auto;
                max-height: 95vh;
                overflow-y: auto;
            }
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
