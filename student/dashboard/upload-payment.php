<?php
// Start session first thing
session_start();

// Check if user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../login.php');
    exit;
}

// Include necessary files
require_once '../../includes/config.php';

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get the student ID from the session
        $student_id = $_SESSION['user_id'];
        
        // Get the form data
        $fee_payment_id = isset($_POST['fee_payment_id']) && !empty($_POST['fee_payment_id']) ? intval($_POST['fee_payment_id']) : 0;
        $fee_structure_id = isset($_POST['fee_structure_id']) ? intval($_POST['fee_structure_id']) : 0;
        $amount_paid = isset($_POST['amount_paid']) ? floatval($_POST['amount_paid']) : 0;
        $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
        $reference_number = isset($_POST['reference_number']) ? $_POST['reference_number'] : '';
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';
        
        if ($fee_structure_id <= 0) {
            throw new Exception('Invalid fee structure ID');
        }
        
        if ($amount_paid <= 0) {
            throw new Exception('Amount paid must be greater than zero');
        }
        
        // Check if a proof image was uploaded
        if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] != 0) {
            throw new Exception('Please upload a payment proof image');
        }
        
        // Check file type
        $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf');
        $file_type = $_FILES['proof_image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            throw new Exception('Only JPG, PNG, GIF, and PDF files are allowed');
        }
        
        // Check file size (max 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB in bytes
        if ($_FILES['proof_image']['size'] > $max_size) {
            throw new Exception('File size should not exceed 5MB');
        }
        
        // Create the uploads directory if it doesn't exist
        $upload_dir = '../../uploads/payment_proofs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate a unique filename
        $file_ext = pathinfo($_FILES['proof_image']['name'], PATHINFO_EXTENSION);
        $filename = 'payment_proof_' . time() . '_' . $student_id . '_' . $fee_structure_id . '.' . $file_ext;
        $upload_path = $upload_dir . $filename;
        
        // Move the uploaded file
        if (!move_uploaded_file($_FILES['proof_image']['tmp_name'], $upload_path)) {
            throw new Exception('Failed to upload file. Please try again.');
        }
        
        // Get database connection
        $conn = getDbConnection();
        
        // Start a transaction
        $conn->begin_transaction();
        
        // Check if this is a new payment or an update to an existing one
        if ($fee_payment_id > 0) {
            // Update existing payment
            $update_payment_sql = "UPDATE fee_payments SET 
                amount_paid = amount_paid + ?, 
                remaining_amount = remaining_amount - ?,
                payment_method = ?,
                reference_number = ?,
                remarks = CONCAT(IFNULL(remarks, ''), '\n', ?),
                status = 'under_verification'
                WHERE id = ? AND student_user_id = ?";
            
            $stmt = $conn->prepare($update_payment_sql);
            $stmt->bind_param("ddsssii", $amount_paid, $amount_paid, $payment_method, $reference_number, $remarks, $fee_payment_id, $student_id);
            $stmt->execute();
            
            if ($stmt->affected_rows == 0) {
                $conn->rollback();
                throw new Exception('Failed to update payment. Invalid payment ID.');
            }
        } else {
            // Get fee structure details
            $structure_sql = "SELECT amount FROM fee_structures WHERE id = ?";
            $stmt = $conn->prepare($structure_sql);
            $stmt->bind_param("i", $fee_structure_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                $conn->rollback();
                throw new Exception('Fee structure not found');
            }
            
            $structure = $result->fetch_assoc();
            $total_amount = $structure['amount'];
            $remaining_amount = $total_amount - $amount_paid;
            
            // Determine status based on payment
            $status = 'under_verification';
            
            // Insert new payment record
            $insert_payment_sql = "INSERT INTO fee_payments (student_user_id, fee_structure_id, payment_date, amount_paid, remaining_amount, status, payment_method, reference_number, remarks) 
                VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($insert_payment_sql);
            $stmt->bind_param("iiddssss", $student_id, $fee_structure_id, $amount_paid, $remaining_amount, $status, $payment_method, $reference_number, $remarks);
            $stmt->execute();
            
            if ($stmt->affected_rows == 0) {
                $conn->rollback();
                throw new Exception('Failed to record payment');
            }
            
            $fee_payment_id = $conn->insert_id;
        }
        
        // Create a new fee_payment_proofs record
        $insert_proof_sql = "INSERT INTO fee_payment_proofs (fee_payment_id, proof_image, verification_status, remarks) 
            VALUES (?, ?, 'pending', ?)";
        
        $stmt = $conn->prepare($insert_proof_sql);
        $stmt->bind_param("iss", $fee_payment_id, $filename, $remarks);
        $stmt->execute();
        
        if ($stmt->affected_rows == 0) {
            $conn->rollback();
            throw new Exception('Failed to record payment proof');
        }
        
        // Commit the transaction
        $conn->commit();
        
        $message = "Payment proof uploaded successfully. Your payment is now under verification and will be reviewed by a teacher or administrator.";
        $messageType = "success";
        
        // Redirect back to fees page after a short delay
        header("refresh:2;url=fees.php");
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}

// Get available fee structures for the student
$feeStructures = [];
try {
    $conn = getDbConnection();
    $studentId = $_SESSION['user_id'];
    
    // Get student class and academic year
    $studentQuery = "SELECT class_id, academic_year_id FROM students WHERE user_id = ?";
    $stmt = $conn->prepare($studentQuery);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $classId = $row['class_id'];
        $academicYearId = $row['academic_year_id'];
        
        // Get fee structures
        $feeQuery = "SELECT id, title, amount, due_date FROM fee_structures 
                    WHERE class_id = ? AND academic_year_id = ? 
                    ORDER BY due_date";
        $stmt = $conn->prepare($feeQuery);
        $stmt->bind_param("ii", $classId, $academicYearId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $feeStructures[] = $row;
        }
    }
} catch (Exception $e) {
    // Ignore errors, we'll just show an empty list
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Payment Proof</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f7fb;
            color: #333;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            text-align: center;
            color: #4f46e5;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .required {
            color: #e53e3e;
        }
        
        .form-hint {
            font-size: 0.85em;
            color: #6b7280;
            margin-top: 5px;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        button {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: #4f46e5;
            color: white;
        }
        
        .btn-secondary {
            background-color: #e5e7eb;
            color: #1f2937;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Payment Proof</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="fee_structure_id">Fee Category <span class="required">*</span></label>
                <select name="fee_structure_id" id="fee_structure_id" required>
                    <option value="">-- Select Fee Category --</option>
                    <?php foreach ($feeStructures as $fee): ?>
                        <option value="<?php echo $fee['id']; ?>" <?php echo (isset($_GET['fee_id']) && $_GET['fee_id'] == $fee['id']) ? 'selected' : ''; ?>>
                            <?php echo $fee['title'] . ' - Due: ' . date('M d, Y', strtotime($fee['due_date'])); ?>
                        </option>
                    <?php endforeach; ?>
                    <!-- Fallback option if no structures found -->
                    <?php if (empty($feeStructures)): ?>
                        <option value="2">Default Fee Structure</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="proof_image">Payment Slip/Receipt Image <span class="required">*</span></label>
                <input type="file" name="proof_image" id="proof_image" accept="image/*,.pdf" required>
                <p class="form-hint">Upload a clear image of your payment receipt or slip (JPG, PNG, GIF, or PDF)</p>
            </div>
            
            <div class="form-group">
                <label for="payment_method">Payment Method <span class="required">*</span></label>
                <select name="payment_method" id="payment_method" required>
                    <option value="">-- Select Payment Method --</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="upi">UPI Payment</option>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                    <option value="other">Other</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="reference_number">Reference/Transaction Number</label>
                <input type="text" name="reference_number" id="reference_number" placeholder="Enter transaction reference number if available">
            </div>
            
            <div class="form-group">
                <label for="amount_paid">Amount Paid <span class="required">*</span></label>
                <input type="number" name="amount_paid" id="amount_paid" min="1" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="remarks">Additional Information</label>
                <textarea name="remarks" id="remarks" rows="3" placeholder="Add any additional details about your payment (optional)"></textarea>
            </div>
            
            <input type="hidden" name="fee_payment_id" value="0">
            
            <div class="button-group">
                <a href="fees.php" class="btn-secondary" style="text-decoration: none; text-align: center;">Cancel</a>
                <button type="submit" class="btn-primary">Submit Payment Proof</button>
            </div>
        </form>
    </div>
</body>
</html> 