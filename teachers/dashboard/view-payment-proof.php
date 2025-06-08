<?php
// Start the session first if not already started
session_start();

// Check if user is logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../index.php');
    exit;
}

// Include necessary files
require_once '../../includes/config.php';

// Get database connection
$conn = getDbConnection();

// Check if payment proof ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: payment-verifications.php');
    exit;
}

$proof_id = intval($_GET['id']);

// Get payment proof details
$proofQuery = "
    SELECT 
        pp.id as proof_id, 
        pp.fee_payment_id, 
        pp.proof_image, 
        pp.verification_status,
        pp.verified_by, 
        pp.verification_date as verified_at, 
        pp.remarks as proof_remarks,
        pp.rejection_reason as verification_remarks,
        fp.student_user_id, 
        fp.fee_structure_id, 
        fp.amount_paid, 
        fp.payment_method, 
        fp.reference_number,
        fp.payment_date,
        fp.remarks as payment_remarks,
        fp.status as payment_status,
        s.full_name as student_name,
        s.admission_number,
        s.contact_email as email,
        s.mobile as phone,
        c.name as class_name,
        sec.name as section_name,
        fs.title as fee_title,
        fs.description as fee_description,
        fs.amount as fee_amount,
        fs.due_date,
        u.full_name as verified_by_name
    FROM fee_payment_proofs pp
    JOIN fee_payments fp ON pp.fee_payment_id = fp.id
    JOIN students s ON fp.student_user_id = s.user_id
    JOIN classes c ON s.class_id = c.id
    JOIN sections sec ON s.section_id = sec.id
    JOIN fee_structures fs ON fp.fee_structure_id = fs.id
    LEFT JOIN users u ON pp.verified_by = u.id
    WHERE pp.id = ?
";

$stmt = $conn->prepare($proofQuery);
$stmt->bind_param('i', $proof_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    header('Location: payment-verifications.php');
    exit;
}

$proofData = $result->fetch_assoc();

// Process verification action if submitted
$message = '';
$messageType = '';

if (isset($_POST['action']) && ($_POST['action'] === 'approve' || $_POST['action'] === 'reject') && isset($_POST['proof_id'])) {
    $teacher_id = $_SESSION['user_id'];
    $proof_id = intval($_POST['proof_id']);
    $new_status = ($_POST['action'] === 'approve') ? 'verified' : 'rejected';
    $verification_remarks = isset($_POST['verification_remarks']) ? $_POST['verification_remarks'] : '';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update the proof status
        $updateQuery = "UPDATE fee_payment_proofs SET 
            verification_status = ?, 
            verified_by = ?,
            verification_date = NOW(),
            rejection_reason = ?
            WHERE id = ?";
            
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('sisi', $new_status, $teacher_id, $verification_remarks, $proof_id);
        $updateStmt->execute();
        
        // If approved, update the payment status in fee_payments
        if ($new_status === 'verified') {
            // Get the fee_payment_id for this proof
            $fee_payment_id = $proofData['fee_payment_id'];
            
            // Compare amount_paid with fee_amount to determine if partial or full payment
            $amount_paid = floatval($proofData['amount_paid']);
            $fee_amount = floatval($proofData['fee_amount']);
            
            // Set status based on payment amount
            $payment_status = $amount_paid >= $fee_amount ? 'paid' : 'partial';
            
            // Update the payment status
            $updatePaymentQuery = "UPDATE fee_payments SET status = ? WHERE id = ?";
            $updatePaymentStmt = $conn->prepare($updatePaymentQuery);
            $updatePaymentStmt->bind_param('si', $payment_status, $fee_payment_id);
            $updatePaymentStmt->execute();
        }
        
        $conn->commit();
        $message = ($new_status === 'verified') ? 'Payment verified and approved.' : 'Payment verification rejected.';
        $messageType = 'success';
        
        // Redirect to refresh the page
        header("Location: view-payment-proof.php?id={$proof_id}&message={$message}&type={$messageType}");
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $message = 'Error updating payment verification: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle message parameters from redirect
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = $_GET['message'];
    $messageType = $_GET['type'];
}

// Get formatted dates
$paymentDate = date('F j, Y', strtotime($proofData['payment_date']));
$dueDate = date('F j, Y', strtotime($proofData['due_date']));
$verifiedAt = !empty($proofData['verified_at']) ? date('F j, Y g:i A', strtotime($proofData['verified_at'])) : 'Not verified yet';

// Format payment method name
$paymentMethodNames = [
    'bank_transfer' => 'Bank Transfer',
    'upi' => 'UPI Payment',
    'cash' => 'Cash',
    'cheque' => 'Cheque',
    'other' => 'Other'
];
$paymentMethodName = isset($paymentMethodNames[$proofData['payment_method']]) ? 
    $paymentMethodNames[$proofData['payment_method']] : 
    ucfirst($proofData['payment_method']);

// Include sidebar after all headers have been set
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payment Proof</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f9fc;
        }
        
        .dashboard-container {
            padding: 20px;
            margin-left: 250px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e1e5ea;
        }
        
        .header-title {
            font-size: 1.5rem;
            color: #333;
            margin: 0;
        }
        
        .nav-links {
            display: flex;
            gap: 10px;
        }
        
        .nav-link {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            background-color: #f1f5f9;
            color: #334155;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        
        .nav-link:hover {
            background-color: #e2e8f0;
        }
        
        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        
        .message.error {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        
        .proof-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        
        .info-card-header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-card-title {
            font-size: 1.1rem;
            color: #334155;
            margin: 0;
        }
        
        .info-card-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: #64748b;
            margin-bottom: 4px;
        }
        
        .info-value {
            font-weight: 500;
            color: #334155;
        }
        
        .info-col-full {
            grid-column: 1 / -1;
        }
        
        .proof-image-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .proof-image {
            max-width: 100%;
            max-height: 700px;
            display: block;
            margin: 0 auto;
            border-radius: 4px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-top: 5px;
        }
        
        .status-pending {
            background-color: #fef9c3;
            color: #854d0e;
        }
        
        .status-approved, .status-verified {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .status-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .verification-actions {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #334155;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-family: inherit;
            resize: vertical;
        }
        
        .verification-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn-reject {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        
        .btn-reject:hover {
            background-color: #fecaca;
        }
        
        .btn-approve {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .btn-approve:hover {
            background-color: #bbf7d0;
        }
        
        @media (max-width: 768px) {
            .proof-details {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="header-title">Payment Proof Details</h1>
            
            <div class="nav-links">
                <a href="payment-verifications.php" class="nav-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Back to Verifications
                </a>
            </div>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="proof-details">
            <div class="info-card">
                <div class="info-card-header">
                    <h2 class="info-card-title">Student Information</h2>
                </div>
                <div class="info-card-content">
                    <div class="info-item">
                        <div class="info-label">Name</div>
                        <div class="info-value"><?php echo htmlspecialchars($proofData['student_name']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Admission Number</div>
                        <div class="info-value"><?php echo htmlspecialchars($proofData['admission_number']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Class</div>
                        <div class="info-value"><?php echo htmlspecialchars($proofData['class_name'] . ' ' . $proofData['section_name']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Email</div>
                        <div class="info-value"><?php echo htmlspecialchars($proofData['email']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Phone</div>
                        <div class="info-value"><?php echo htmlspecialchars($proofData['phone']); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-card-header">
                    <h2 class="info-card-title">Fee Information</h2>
                </div>
                <div class="info-card-content">
                    <div class="info-item">
                        <div class="info-label">Fee Title</div>
                        <div class="info-value"><?php echo htmlspecialchars($proofData['fee_title']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Due Date</div>
                        <div class="info-value"><?php echo $dueDate; ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Total Amount</div>
                        <div class="info-value">₹<?php echo number_format($proofData['fee_amount'], 2); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Amount Paid</div>
                        <div class="info-value">₹<?php echo number_format($proofData['amount_paid'], 2); ?></div>
                    </div>
                    
                    <div class="info-item info-col-full">
                        <div class="info-label">Description</div>
                        <div class="info-value"><?php echo nl2br(htmlspecialchars($proofData['fee_description'] ?? 'No description available')); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-card-header">
                    <h2 class="info-card-title">Payment Information</h2>
                </div>
                <div class="info-card-content">
                    <div class="info-item">
                        <div class="info-label">Payment Date</div>
                        <div class="info-value"><?php echo $paymentDate; ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Payment Method</div>
                        <div class="info-value"><?php echo htmlspecialchars($paymentMethodName); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Reference Number</div>
                        <div class="info-value">
                            <?php echo !empty($proofData['reference_number']) ? 
                                htmlspecialchars($proofData['reference_number']) : 
                                '<span style="color: #94a3b8; font-style: italic;">Not provided</span>'; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Payment Status</div>
                        <div class="info-value">
                            <?php 
                            $paymentStatusClass = '';
                            $paymentStatusText = ucfirst($proofData['payment_status']);
                            
                            switch ($proofData['payment_status']) {
                                case 'paid':
                                    $paymentStatusClass = 'status-approved';
                                    break;
                                case 'pending':
                                case 'under_verification':
                                    $paymentStatusClass = 'status-pending';
                                    $paymentStatusText = 'Under Verification';
                                    break;
                                default:
                                    $paymentStatusClass = 'status-pending';
                            }
                            ?>
                            <span class="status-badge <?php echo $paymentStatusClass; ?>"><?php echo $paymentStatusText; ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item info-col-full">
                        <div class="info-label">Student's Remarks</div>
                        <div class="info-value">
                            <?php echo !empty($proofData['payment_remarks']) ? 
                                nl2br(htmlspecialchars($proofData['payment_remarks'])) : 
                                '<span style="color: #94a3b8; font-style: italic;">No remarks provided</span>'; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="info-card">
                <div class="info-card-header">
                    <h2 class="info-card-title">Verification Status</h2>
                </div>
                <div class="info-card-content">
                    <div class="info-item">
                        <div class="info-label">Current Status</div>
                        <div class="info-value">
                            <?php 
                            $verificationStatusClass = 'status-' . $proofData['verification_status'];
                            $verificationStatusText = ucfirst($proofData['verification_status']);
                            ?>
                            <span class="status-badge <?php echo $verificationStatusClass; ?>"><?php echo $verificationStatusText; ?></span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Verified By</div>
                        <div class="info-value">
                            <?php echo !empty($proofData['verified_by_name']) ? 
                                htmlspecialchars($proofData['verified_by_name']) : 
                                '<span style="color: #94a3b8; font-style: italic;">Not verified yet</span>'; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Verified At</div>
                        <div class="info-value"><?php echo $verifiedAt; ?></div>
                    </div>
                    
                    <div class="info-item info-col-full">
                        <div class="info-label">Verification Remarks</div>
                        <div class="info-value">
                            <?php echo !empty($proofData['verification_remarks']) ? 
                                nl2br(htmlspecialchars($proofData['verification_remarks'])) : 
                                '<span style="color: #94a3b8; font-style: italic;">No verification remarks</span>'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="proof-image-container">
            <h2 style="margin-top: 0; margin-bottom: 15px; color: #334155; font-size: 1.1rem;">Payment Proof Image</h2>
            <?php
            $proofImagePath = '../../uploads/payment_proofs/' . $proofData['proof_image'];
            $extension = pathinfo($proofData['proof_image'], PATHINFO_EXTENSION);
            
            if (strtolower($extension) === 'pdf') {
                echo '<embed src="' . $proofImagePath . '" type="application/pdf" width="100%" height="600px" />';
            } else {
                echo '<img src="' . $proofImagePath . '" alt="Payment Proof" class="proof-image">';
            }
            ?>
        </div>
        
        <?php if ($proofData['verification_status'] === 'pending'): ?>
        <div class="verification-actions">
            <h2 style="margin-top: 0; margin-bottom: 15px; color: #334155; font-size: 1.1rem;">Verification Actions</h2>
            
            <form method="post" id="verificationForm">
                <input type="hidden" name="proof_id" value="<?php echo $proofData['proof_id']; ?>">
                <input type="hidden" name="action" id="verificationAction">
                
                <div class="form-group">
                    <label for="verification_remarks">Verification Remarks (optional):</label>
                    <textarea name="verification_remarks" id="verification_remarks" rows="4" placeholder="Enter any remarks about this payment verification"></textarea>
                </div>
                
                <div class="verification-buttons">
                    <button type="button" class="btn btn-reject" onclick="submitVerification('reject')">Reject Payment</button>
                    <button type="button" class="btn btn-approve" onclick="submitVerification('approve')">Approve Payment</button>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function submitVerification(action) {
            if (action === 'approve') {
                if (!confirm('Are you sure you want to approve this payment? This will mark the fee as paid.')) {
                    return;
                }
            } else if (action === 'reject') {
                if (!confirm('Are you sure you want to reject this payment?')) {
                    return;
                }
            }
            
            document.getElementById('verificationAction').value = action;
            document.getElementById('verificationForm').submit();
        }
    </script>
</body>
</html> 