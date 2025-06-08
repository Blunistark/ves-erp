<?php
// Include database connection
include 'con.php';

// Get action from request
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Response array
$response = [
    'success' => false,
    'message' => 'Invalid action'
];

// Handle different actions
switch ($action) {
    case 'get_students':
        // Get all students with their class and section
        $sql = "SELECT s.user_id, s.admission_number, s.full_name, c.name as class_name, sec.name as section_name
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                LEFT JOIN sections sec ON s.section_id = sec.id
                ORDER BY s.full_name";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $students = [];
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
            
            $response = [
                'success' => true,
                'students' => $students
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No students found'
            ];
        }
        break;
    
    case 'get_student_fee_structures':
        // Get fee structures applicable to a specific student
        if (isset($_REQUEST['student_id']) && !empty($_REQUEST['student_id'])) {
            $studentId = $_REQUEST['student_id'];
            
            // Get student's class and academic year
            $studentInfoSql = "SELECT class_id, academic_year_id FROM students WHERE user_id = ?";
            $studentInfoStmt = $conn->prepare($studentInfoSql);
            $studentInfoStmt->bind_param('i', $studentId);
            $studentInfoStmt->execute();
            $studentInfoResult = $studentInfoStmt->get_result();
            
            if ($studentInfoResult && $studentInfoResult->num_rows > 0) {
                $studentInfo = $studentInfoResult->fetch_assoc();
                $classId = $studentInfo['class_id'];
                $academicYearId = $studentInfo['academic_year_id'];
                
                // Get fee structures for this class and academic year
                $sql = "SELECT fs.*, c.name as class_name 
                        FROM fee_structures fs
                        LEFT JOIN classes c ON fs.class_id = c.id
                        WHERE fs.class_id = ? AND fs.academic_year_id = ?
                        ORDER BY fs.due_date";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ii', $classId, $academicYearId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result && $result->num_rows > 0) {
                    $structures = [];
                    while ($row = $result->fetch_assoc()) {
                        // Check if there are any payments for this fee structure
                        $paymentSql = "SELECT SUM(amount_paid) as total_paid, SUM(remaining_amount) as total_remaining 
                                       FROM fee_payments 
                                       WHERE student_user_id = ? AND fee_structure_id = ?";
                        $paymentStmt = $conn->prepare($paymentSql);
                        $paymentStmt->bind_param('ii', $studentId, $row['id']);
                        $paymentStmt->execute();
                        $paymentResult = $paymentStmt->get_result();
                        $payment = $paymentResult->fetch_assoc();
                        
                        // Calculate remaining amount
                        $totalPaid = $payment['total_paid'] ?: 0;
                        $remainingAmount = $row['amount'] - $totalPaid;
                        
                        // Add payment info to the structure
                        $row['paid_amount'] = $totalPaid;
                        $row['remaining_amount'] = $remainingAmount;
                        
                        $structures[] = $row;
                    }
                    
                    $response = [
                        'success' => true,
                        'structures' => $structures
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'No fee structures found for this student'
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Student information not found'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Missing student ID'
            ];
        }
        break;
    
    case 'search_student':
        // Search student by name, admission number, or class
        if (isset($_REQUEST['query']) && !empty($_REQUEST['query'])) {
            $query = '%' . $_REQUEST['query'] . '%';
            
            $sql = "SELECT s.*, c.name as class_name, sec.name as section_name
                    FROM students s
                    LEFT JOIN classes c ON s.class_id = c.id
                    LEFT JOIN sections sec ON s.section_id = sec.id
                    WHERE s.full_name LIKE ? OR s.admission_number LIKE ? OR c.name LIKE ?
                    LIMIT 1";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $query, $query, $query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $student = $result->fetch_assoc();
                
                // Get fee structures and payments for this student
                $feeStructuresSql = "SELECT fs.*, c.name as class_name 
                                    FROM fee_structures fs
                                    LEFT JOIN classes c ON fs.class_id = c.id
                                    WHERE fs.class_id = ? AND fs.academic_year_id = ?";
                
                $feeStructuresStmt = $conn->prepare($feeStructuresSql);
                $feeStructuresStmt->bind_param('ii', $student['class_id'], $student['academic_year_id']);
                $feeStructuresStmt->execute();
                $feeStructuresResult = $feeStructuresStmt->get_result();
                
                $totalFees = 0;
                $paidAmount = 0;
                $dueAmount = 0;
                
                if ($feeStructuresResult && $feeStructuresResult->num_rows > 0) {
                    while ($structure = $feeStructuresResult->fetch_assoc()) {
                        $totalFees += $structure['amount'];
                    }
                }
                
                // Get payment history
                $paymentHistorySql = "SELECT fp.*, fs.title as fee_title
                                     FROM fee_payments fp
                                     LEFT JOIN fee_structures fs ON fp.fee_structure_id = fs.id
                                     WHERE fp.student_user_id = ?
                                     ORDER BY fp.payment_date DESC";
                
                $paymentHistoryStmt = $conn->prepare($paymentHistorySql);
                $paymentHistoryStmt->bind_param('i', $student['user_id']);
                $paymentHistoryStmt->execute();
                $paymentHistoryResult = $paymentHistoryStmt->get_result();
                
                $paymentHistory = [];
                
                if ($paymentHistoryResult && $paymentHistoryResult->num_rows > 0) {
                    while ($payment = $paymentHistoryResult->fetch_assoc()) {
                        $paidAmount += $payment['amount_paid'];
                        $paymentHistory[] = $payment;
                    }
                }
                
                $dueAmount = $totalFees - $paidAmount;
                
                // Add fee summary to student data
                $student['total_fees'] = $totalFees;
                $student['paid_amount'] = $paidAmount;
                $student['due_amount'] = $dueAmount;
                $student['payment_history'] = $paymentHistory;
                
                $response = [
                    'success' => true,
                    'student' => $student
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No student found'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Missing search query'
            ];
        }
        break;
    
    case 'record_payment':
        // Record a new fee payment
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Begin transaction
            $conn->begin_transaction();
            
            try {
                // Get form data
                $studentId = $_POST['student_id'];
                $feeStructureId = $_POST['fee_structure_id'];
                $amountPaid = $_POST['amount_paid'];
                $paymentDate = $_POST['payment_date'];
                $paymentMethod = $_POST['payment_method'];
                $referenceNumber = $_POST['reference_number'] ?? '';
                $remarks = $_POST['remarks'] ?? '';
                
                // Get fee structure details
                $structureSql = "SELECT * FROM fee_structures WHERE id = ?";
                $structureStmt = $conn->prepare($structureSql);
                $structureStmt->bind_param('i', $feeStructureId);
                $structureStmt->execute();
                $structureResult = $structureStmt->get_result();
                $structure = $structureResult->fetch_assoc();
                
                // Check for existing payments
                $existingPaymentSql = "SELECT SUM(amount_paid) as total_paid 
                                      FROM fee_payments 
                                      WHERE student_user_id = ? AND fee_structure_id = ?";
                $existingPaymentStmt = $conn->prepare($existingPaymentSql);
                $existingPaymentStmt->bind_param('ii', $studentId, $feeStructureId);
                $existingPaymentStmt->execute();
                $existingPaymentResult = $existingPaymentStmt->get_result();
                $existingPayment = $existingPaymentResult->fetch_assoc();
                
                $totalPaid = $existingPayment['total_paid'] ?: 0;
                $totalPaid += $amountPaid;
                $remainingAmount = $structure['amount'] - $totalPaid;
                
                // Determine payment status
                $status = 'pending';
                if ($remainingAmount <= 0) {
                    $status = 'paid';
                    $remainingAmount = 0;
                } else if ($totalPaid > 0) {
                    $status = 'partial';
                }
                
                // Insert payment record
                $paymentSql = "INSERT INTO fee_payments 
                              (student_user_id, fee_structure_id, payment_date, amount_paid, remaining_amount, status, payment_method, reference_number, remarks) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $paymentStmt = $conn->prepare($paymentSql);
                $paymentStmt->bind_param('iisddssss', $studentId, $feeStructureId, $paymentDate, $amountPaid, $remainingAmount, $status, $paymentMethod, $referenceNumber, $remarks);
                $paymentStmt->execute();
                
                // Log the payment in audit log
                $paymentId = $conn->insert_id;
                $auditSql = "INSERT INTO audit_logs (user_id, table_name, record_id, action) 
                           VALUES (?, 'fee_payments', ?, 'INSERT')";
                $auditStmt = $conn->prepare($auditSql);
                $currentUserId = $_SESSION['id'] ?? 0; // Assuming you have user ID in session
                $auditStmt->bind_param('ii', $currentUserId, $paymentId);
                $auditStmt->execute();
                
                // Commit transaction
                $conn->commit();
                
                $response = [
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'payment_id' => $paymentId
                ];
            } catch (Exception $e) {
                // Rollback on error
                $conn->rollback();
                
                $response = [
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Invalid request method'
            ];
        }
        break;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 