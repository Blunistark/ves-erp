<?php
// Start the session first if not already started
session_start();

// Check if user is logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../../login.php');
    exit;
}

// Include necessary files
require_once '../../includes/config.php';
include 'sidebar.php';

// Get database connection
$conn = getDbConnection();

// Get verification status filter (default to 'pending')
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';

// Define valid status options$valid_statuses = ['pending', 'verified', 'rejected', 'all'];if (!in_array($status_filter, $valid_statuses)) {    $status_filter = 'pending';}

// Get all payment proofs with student information
$paymentProofsQuery = "
    SELECT 
        pp.id as proof_id, 
        pp.fee_payment_id, 
        pp.proof_image, 
        pp.verification_status,
        pp.verified_by, 
        pp.verification_date as verified_at, 
        pp.remarks as proof_remarks,
        fp.student_user_id, 
        fp.fee_structure_id, 
        fp.amount_paid, 
        fp.payment_method, 
        fp.reference_number,
        fp.payment_date,
        s.full_name as student_name,
        s.admission_number,
        c.name as class_name,
        sec.name as section_name,
        fs.title as fee_title,
        fs.amount as fee_amount
    FROM fee_payment_proofs pp
    JOIN fee_payments fp ON pp.fee_payment_id = fp.id
    JOIN students s ON fp.student_user_id = s.user_id
    JOIN classes c ON s.class_id = c.id
    JOIN sections sec ON s.section_id = sec.id
    JOIN fee_structures fs ON fp.fee_structure_id = fs.id
";

// Apply status filter
if ($status_filter !== 'all') {
    $paymentProofsQuery .= " WHERE pp.verification_status = ?";
}

$paymentProofsQuery .= " ORDER BY pp.id DESC";

// Prepare and execute query
$stmt = $conn->prepare($paymentProofsQuery);
if ($status_filter !== 'all') {
    $stmt->bind_param('s', $status_filter);
}
$stmt->execute();
$result = $stmt->get_result();

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
        if ($new_status === 'approved') {
            // Get the fee_payment_id for this proof
            $paymentIdQuery = "SELECT fee_payment_id FROM fee_payment_proofs WHERE id = ?";
            $paymentIdStmt = $conn->prepare($paymentIdQuery);
            $paymentIdStmt->bind_param('i', $proof_id);
            $paymentIdStmt->execute();
            $paymentIdResult = $paymentIdStmt->get_result();
            
            if ($row = $paymentIdResult->fetch_assoc()) {
                $fee_payment_id = $row['fee_payment_id'];
                
                // Update the payment status
                $updatePaymentQuery = "UPDATE fee_payments SET status = 'paid' WHERE id = ?";
                $updatePaymentStmt = $conn->prepare($updatePaymentQuery);
                $updatePaymentStmt->bind_param('i', $fee_payment_id);
                $updatePaymentStmt->execute();
            }
        }
        
        $conn->commit();
        $message = ($new_status === 'verified') ? 'Payment verified and approved.' : 'Payment verification rejected.';
        $messageType = 'success';
        
        // Redirect to refresh the page
        header("Location: payment-verifications.php?status={$status_filter}&message={$message}&type={$messageType}");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verifications</title>
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
        
        .filter-section {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .status-filter {
            padding: 8px 16px;
            border-radius: 5px;
            border: 1px solid #cbd5e1;
            background-color: #fff;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .status-filter.active {
            background-color: #4f46e5;
            color: white;
            border-color: #4f46e5;
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
        
        .verification-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .verification-table th {
            background-color: #f1f5f9;
            padding: 12px 15px;
            text-align: left;
            color: #334155;
        }
        
        .verification-table td {
            padding: 12px 15px;
            border-top: 1px solid #e2e8f0;
        }
        
        .verification-table tr:hover {
            background-color: #f8fafc;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: 500;
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
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.85rem;
            transition: background-color 0.2s;
        }
        
        .btn-view {
            background-color: #e0f2fe;
            color: #0369a1;
        }
        
        .btn-view:hover {
            background-color: #bae6fd;
        }
        
        .btn-verify {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .btn-verify:hover {
            background-color: #bbf7d0;
        }
        
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #64748b;
            font-size: 1.1rem;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 1.2rem;
            margin: 0;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #64748b;
        }
        
        .modal-body {
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            resize: vertical;
        }
        
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .img-thumbnail {
            max-width: 80px;
            max-height: 80px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 500px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1 class="header-title">Payment Verifications</h1>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="filter-section">
            <a href="?status=pending" class="status-filter <?php echo ($status_filter === 'pending') ? 'active' : ''; ?>">Pending</a>
            <a href="?status=verified" class="status-filter <?php echo ($status_filter === 'verified') ? 'active' : ''; ?>">Approved</a>
            <a href="?status=rejected" class="status-filter <?php echo ($status_filter === 'rejected') ? 'active' : ''; ?>">Rejected</a>
            <a href="?status=all" class="status-filter <?php echo ($status_filter === 'all') ? 'active' : ''; ?>">All</a>
        </div>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <table class="verification-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Fee Category</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Proof</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div><?php echo htmlspecialchars($row['student_name']); ?></div>
                                <div style="font-size: 0.8rem; color: #64748b;"><?php echo htmlspecialchars($row['admission_number']); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars($row['class_name'] . ' ' . $row['section_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['fee_title']); ?></td>
                            <td>₹<?php echo number_format($row['amount_paid'], 2); ?></td>
                            <td><?php echo date('d M Y', strtotime($row['payment_date'])); ?></td>
                            <td>
                                <img src="../../uploads/payment_proofs/<?php echo htmlspecialchars($row['proof_image']); ?>" 
                                     alt="Payment Proof" 
                                     class="img-thumbnail"
                                     onclick="showImagePreview('../../uploads/payment_proofs/<?php echo htmlspecialchars($row['proof_image']); ?>')">
                            </td>
                            <td>
                                <?php
                                $statusClass = 'status-' . $row['verification_status'];
                                $statusText = ucfirst($row['verification_status']);
                                echo "<span class='status-badge $statusClass'>$statusText</span>";
                                ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="view-payment-proof.php?id=<?php echo $row['proof_id']; ?>" class="btn btn-view">View Details</a>
                                    
                                    <?php if ($row['verification_status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-verify" 
                                                onclick="showVerificationModal(<?php echo $row['proof_id']; ?>, 
                                                                             '<?php echo htmlspecialchars($row['student_name']); ?>', 
                                                                             '₹<?php echo number_format($row['amount_paid'], 2); ?>')">
                                            Verify
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-message">
                <p>No payment verifications found for the selected filter.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Verification Modal -->
    <div id="verificationModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Verify Payment</h2>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Student: <span id="studentName"></span></p>
                <p>Amount: <span id="paymentAmount"></span></p>
                
                <form id="verificationForm" method="post">
                    <input type="hidden" name="proof_id" id="proofId">
                    <input type="hidden" name="action" id="verificationAction">
                    
                    <div class="form-group">
                        <label for="verification_remarks">Remarks (optional):</label>
                        <textarea name="verification_remarks" id="verification_remarks" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" style="background-color: #e5e7eb; color: #1f2937;" onclick="closeModal()">Cancel</button>
                <button type="button" class="btn" style="background-color: #fee2e2; color: #b91c1c;" onclick="submitVerification('reject')">Reject</button>
                <button type="button" class="btn" style="background-color: #dcfce7; color: #166534;" onclick="submitVerification('approve')">Approve</button>
            </div>
        </div>
    </div>
    
    <!-- Image Preview Modal -->
    <div id="imagePreviewModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2 class="modal-title">Payment Proof</h2>
                <button class="close-modal" onclick="closeImagePreview()">&times;</button>
            </div>
            <div class="modal-body">
                <img id="previewImage" src="" alt="Payment Proof" class="preview-image">
            </div>
        </div>
    </div>
    
    <script>
        function showVerificationModal(proofId, studentName, amount) {
            document.getElementById('proofId').value = proofId;
            document.getElementById('studentName').textContent = studentName;
            document.getElementById('paymentAmount').textContent = amount;
            
            document.getElementById('verificationModal').style.display = 'flex';
        }
        
        function closeModal() {
            document.getElementById('verificationModal').style.display = 'none';
            document.getElementById('verificationForm').reset();
        }
        
        function submitVerification(action) {
            document.getElementById('verificationAction').value = action;
            document.getElementById('verificationForm').submit();
        }
        
        function showImagePreview(imageSrc) {
            document.getElementById('previewImage').src = imageSrc;
            document.getElementById('imagePreviewModal').style.display = 'flex';
        }
        
        function closeImagePreview() {
            document.getElementById('imagePreviewModal').style.display = 'none';
        }
        
        // Close modals if clicking outside
        window.onclick = function(event) {
            const verificationModal = document.getElementById('verificationModal');
            const imagePreviewModal = document.getElementById('imagePreviewModal');
            
            if (event.target === verificationModal) {
                verificationModal.style.display = 'none';
            }
            
            if (event.target === imagePreviewModal) {
                imagePreviewModal.style.display = 'none';
            }
        };
    </script>
</body>
</html> 