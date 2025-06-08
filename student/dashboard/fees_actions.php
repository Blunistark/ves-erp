<?php
// Prevent any output before JSON response
ob_start();

// Enable error reporting but log to file instead of output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../../logs/php_errors.log');

// Set JSON content type header
header('Content-Type: application/json');

// Include database connection
require_once '../../includes/config.php';

// Start session
session_start();

// Check if user is logged in as a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $response = [
        'success' => false,
        'message' => 'Unauthorized access'
    ];
    ob_end_clean(); // Clear any output
    echo json_encode($response);
    exit;
}

// Get database connection
$conn = getDbConnection();
if (!$conn) {
    error_log("Database connection failed");
    $response = [
        'success' => false,
        'message' => 'Database connection failed'
    ];
    ob_end_clean(); // Clear any output
    echo json_encode($response);
    exit;
}

$studentId = $_SESSION['user_id'];
error_log("Student ID: " . $studentId);

// Get action from request
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
error_log("Requested action: " . $action);

// Response array
$response = [
    'success' => false,
    'message' => 'Invalid action'
];

try {
    // Handle different actions
    switch ($action) {
        case 'get_student_fees':
            // Get student class and academic year info
            $studentInfoSql = "SELECT s.class_id, s.academic_year_id, s.full_name, s.admission_number, 
                              c.name as class_name, sec.name as section_name
                              FROM students s
                              LEFT JOIN classes c ON s.class_id = c.id
                              LEFT JOIN sections sec ON s.section_id = sec.id
                              WHERE s.user_id = ?";
            
            $studentInfoStmt = $conn->prepare($studentInfoSql);
            $studentInfoStmt->bind_param('i', $studentId);
            $studentInfoStmt->execute();
            $studentInfoResult = $studentInfoStmt->get_result();
            
            if ($studentInfoResult && $studentInfoResult->num_rows > 0) {
                $studentInfo = $studentInfoResult->fetch_assoc();
                $classId = $studentInfo['class_id'];
                $academicYearId = $studentInfo['academic_year_id'];
                
                // Get applicable fee structures
                $feeStructuresSql = "SELECT fs.*, c.name as class_name 
                                  FROM fee_structures fs
                                  LEFT JOIN classes c ON fs.class_id = c.id
                                  WHERE fs.class_id = ? AND fs.academic_year_id = ?
                                  ORDER BY fs.due_date";
                
                $feeStructuresStmt = $conn->prepare($feeStructuresSql);
                $feeStructuresStmt->bind_param('ii', $classId, $academicYearId);
                $feeStructuresStmt->execute();
                $feeStructuresResult = $feeStructuresStmt->get_result();
                
                $feeStructures = [];
                $totalFees = 0;
                $paidAmount = 0;
                $dueAmount = 0;
                $nextPayment = null;
                $nextPaymentDate = null;
                
                if ($feeStructuresResult && $feeStructuresResult->num_rows > 0) {
                    while ($structure = $feeStructuresResult->fetch_assoc()) {
                        // Get fee components for this structure
                        $componentsSql = "SELECT * FROM fee_components WHERE fee_structure_id = ?";
                        $componentsStmt = $conn->prepare($componentsSql);
                        $componentsStmt->bind_param('i', $structure['id']);
                        $componentsStmt->execute();
                        $componentsResult = $componentsStmt->get_result();
                        
                        $components = [];
                        if ($componentsResult && $componentsResult->num_rows > 0) {
                            while ($component = $componentsResult->fetch_assoc()) {
                                $components[] = $component;
                            }
                        }
                        
                        // Get payment info for this fee structure
                        $paymentSql = "SELECT * FROM fee_payments 
                                    WHERE student_user_id = ? AND fee_structure_id = ?
                                    ORDER BY payment_date DESC";
                        $paymentStmt = $conn->prepare($paymentSql);
                        $paymentStmt->bind_param('ii', $studentId, $structure['id']);
                        $paymentStmt->execute();
                        $paymentResult = $paymentStmt->get_result();
                        
                        $payments = [];
                        $structurePaid = 0;
                        $structureRemaining = $structure['amount'];
                        $status = 'pending';
                        
                        if ($paymentResult && $paymentResult->num_rows > 0) {
                            while ($payment = $paymentResult->fetch_assoc()) {
                                $payments[] = $payment;
                                $structurePaid += $payment['amount_paid'];
                            }
                            
                            // Calculate remaining amount
                            $structureRemaining = $structure['amount'] - $structurePaid;
                            
                            // Determine status
                            if ($structureRemaining <= 0) {
                                $status = 'paid';
                            } else if ($structurePaid > 0) {
                                $dueDate = new DateTime($structure['due_date']);
                                $today = new DateTime();
                                $status = ($dueDate < $today) ? 'overdue' : 'partial';
                            } else {
                                $dueDate = new DateTime($structure['due_date']);
                                $today = new DateTime();
                                $status = ($dueDate < $today) ? 'overdue' : 'pending';
                            }
                        }
                        
                        // Track total fees and payments
                        $totalFees += $structure['amount'];
                        $paidAmount += $structurePaid;
                        
                        // Check if this is the next upcoming payment
                        $dueDate = new DateTime($structure['due_date']);
                        $today = new DateTime();
                        
                        if ($structureRemaining > 0 && ($nextPaymentDate === null || $dueDate < $nextPaymentDate)) {
                            $nextPayment = $structureRemaining;
                            $nextPaymentDate = $dueDate;
                        }
                        
                        // Add data to structure record
                        $structure['components'] = $components;
                        $structure['payments'] = $payments;
                        $structure['paid_amount'] = $structurePaid;
                        $structure['remaining_amount'] = $structureRemaining;
                        $structure['status'] = $status;
                        
                        $feeStructures[] = $structure;
                    }
                }
                
                // Calculate due amount
                $dueAmount = $totalFees - $paidAmount;
                
                // Get payment history
                $paymentHistorySql = "SELECT fp.*, fs.title as fee_title
                                  FROM fee_payments fp
                                  LEFT JOIN fee_structures fs ON fp.fee_structure_id = fs.id
                                  WHERE fp.student_user_id = ?
                                  ORDER BY fp.payment_date DESC";
                
                $paymentHistoryStmt = $conn->prepare($paymentHistorySql);
                $paymentHistoryStmt->bind_param('i', $studentId);
                $paymentHistoryStmt->execute();
                $paymentHistoryResult = $paymentHistoryStmt->get_result();
                
                $paymentHistory = [];
                if ($paymentHistoryResult && $paymentHistoryResult->num_rows > 0) {
                    while ($payment = $paymentHistoryResult->fetch_assoc()) {
                        $paymentHistory[] = $payment;
                    }
                }
                
                // Format next payment date if exists
                $formattedNextPaymentDate = null;
                if ($nextPaymentDate !== null) {
                    $formattedNextPaymentDate = $nextPaymentDate->format('M d, Y');
                }
                
                // Prepare response
                $response = [
                    'success' => true,
                    'student_info' => $studentInfo,
                    'fee_structures' => $feeStructures,
                    'payment_history' => $paymentHistory,
                    'summary' => [
                        'total_fees' => $totalFees,
                        'paid_amount' => $paidAmount,
                        'due_amount' => $dueAmount,
                        'paid_percentage' => ($totalFees > 0) ? round(($paidAmount / $totalFees) * 100, 1) : 0,
                        'next_payment' => $nextPayment,
                        'next_payment_date' => $formattedNextPaymentDate
                    ]
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Student information not found'
                ];
            }
            break;
            
        case 'download_receipt':
            // Check if payment ID is provided
            if (isset($_REQUEST['payment_id']) && !empty($_REQUEST['payment_id'])) {
                $paymentId = intval($_REQUEST['payment_id']);
                
                // Verify this payment belongs to the student
                $verifySql = "SELECT id FROM fee_payments 
                            WHERE id = ? AND student_user_id = ?";
                $verifyStmt = $conn->prepare($verifySql);
                $verifyStmt->bind_param('ii', $paymentId, $studentId);
                $verifyStmt->execute();
                $verifyResult = $verifyStmt->get_result();
                
                if ($verifyResult && $verifyResult->num_rows > 0) {
                    $response = [
                        'success' => true,
                        'receipt_url' => '../admin/dashboard/fee_receipt.php?payment_id=' . $paymentId
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Invalid payment receipt requested'
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Payment ID is required'
                ];
            }
            break;
            
        case 'upload_payment_proof':
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
                echo json_encode(array('success' => false, 'message' => 'Invalid fee structure ID'));
                exit;
            }
            
            if ($amount_paid <= 0) {
                echo json_encode(array('success' => false, 'message' => 'Amount paid must be greater than zero'));
                exit;
            }
            
            // Check if a proof image was uploaded
            if (!isset($_FILES['proof_image']) || $_FILES['proof_image']['error'] != 0) {
                echo json_encode(array('success' => false, 'message' => 'Please upload a payment proof image'));
                exit;
            }
            
            // Check file type
            $allowed_types = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf');
            $file_type = $_FILES['proof_image']['type'];
            
            if (!in_array($file_type, $allowed_types)) {
                echo json_encode(array('success' => false, 'message' => 'Only JPG, PNG, GIF, and PDF files are allowed'));
                exit;
            }
            
            // Check file size (max 5MB)
            $max_size = 5 * 1024 * 1024; // 5MB in bytes
            if ($_FILES['proof_image']['size'] > $max_size) {
                echo json_encode(array('success' => false, 'message' => 'File size should not exceed 5MB'));
                exit;
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
                echo json_encode(array('success' => false, 'message' => 'Failed to upload file. Please try again.'));
                exit;
            }
            
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
                    status = 'pending'
                    WHERE id = ? AND student_user_id = ?";
                
                $stmt = $conn->prepare($update_payment_sql);
                $stmt->bind_param("ddsssii", $amount_paid, $amount_paid, $payment_method, $reference_number, $remarks, $fee_payment_id, $student_id);
                $stmt->execute();
                
                if ($stmt->affected_rows == 0) {
                    $conn->rollback();
                    echo json_encode(array('success' => false, 'message' => 'Failed to update payment. Invalid payment ID.'));
                    exit;
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
                    echo json_encode(array('success' => false, 'message' => 'Fee structure not found'));
                    exit;
                }
                
                $structure = $result->fetch_assoc();
                $total_amount = $structure['amount'];
                $remaining_amount = $total_amount - $amount_paid;
                
                // Determine status based on payment
                $status = 'pending';
                
                // Insert new payment record
                $insert_payment_sql = "INSERT INTO fee_payments (student_user_id, fee_structure_id, payment_date, amount_paid, remaining_amount, status, payment_method, reference_number, remarks) 
                    VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($insert_payment_sql);
                $stmt->bind_param("iiddsss", $student_id, $fee_structure_id, $amount_paid, $remaining_amount, $status, $payment_method, $reference_number, $remarks);
                $stmt->execute();
                
                if ($stmt->affected_rows == 0) {
                    $conn->rollback();
                    echo json_encode(array('success' => false, 'message' => 'Failed to record payment'));
                    exit;
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
                echo json_encode(array('success' => false, 'message' => 'Failed to record payment proof'));
                exit;
            }
            
            // Commit the transaction
            $conn->commit();
            
            echo json_encode(array('success' => true, 'message' => 'Payment proof uploaded successfully. It will be reviewed by a teacher or administrator.'));
            break;
    }
} catch (Exception $e) {
    error_log("Error in fees_actions.php: " . $e->getMessage());
    $response = [
        'success' => false,
        'message' => 'An error occurred while processing your request'
    ];
}

// Clear any output and send JSON response
ob_end_clean();
echo json_encode($response);
?> 