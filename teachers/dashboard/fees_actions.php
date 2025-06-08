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

// Start session and log session data
session_start();
error_log("Session data: " . print_r($_SESSION, true));

// Check if user is logged in as a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
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

// Log teacher ID
$teacherId = $_SESSION['user_id'];
error_log("Teacher ID: " . $teacherId);

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
        case 'get_assigned_classes':
            // Get classes assigned to this teacher
            $classesSql = "SELECT c.id, c.name, sec.id as section_id, sec.name as section_name,
                          COUNT(s.user_id) as student_count
                          FROM classes c
                          JOIN sections sec ON sec.class_id = c.id
                          LEFT JOIN teacher_assignments ta ON ta.class_id = c.id AND ta.section_id = sec.id
                          LEFT JOIN students s ON s.class_id = c.id AND s.section_id = sec.id
                          WHERE ta.teacher_user_id = ?
                          GROUP BY c.id, sec.id
                          ORDER BY c.name, sec.name";
            
            $classesStmt = $conn->prepare($classesSql);
            $classesStmt->bind_param('i', $teacherId);
            $classesStmt->execute();
            $classesResult = $classesStmt->get_result();
            
            $classes = [];
            if ($classesResult && $classesResult->num_rows > 0) {
                while ($class = $classesResult->fetch_assoc()) {
                    $classes[] = $class;
                }
                
                $response = [
                    'success' => true,
                    'classes' => $classes
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No assigned classes found'
                ];
            }
            break;
        
        case 'get_fee_statistics':
            // Get fee payment statistics for teacher's assigned classes
            // This can be for all classes or a specific class if class_id is provided
            $classId = isset($_REQUEST['class_id']) ? intval($_REQUEST['class_id']) : null;
            $sectionId = isset($_REQUEST['section_id']) ? intval($_REQUEST['section_id']) : null;
            
            // Base query to get assigned classes
            $whereClause = "WHERE ta.teacher_user_id = ?";
            $params = [$teacherId];
            $types = "i";
            
            if ($classId) {
                $whereClause .= " AND c.id = ?";
                $params[] = $classId;
                $types .= "i";
                
                if ($sectionId) {
                    $whereClause .= " AND sec.id = ?";
                    $params[] = $sectionId;
                    $types .= "i";
                }
            }
            
            // Query to get all students from assigned classes
            $studentsSql = "SELECT s.user_id, s.admission_number, s.full_name, c.id as class_id, c.name as class_name, 
                           sec.id as section_id, sec.name as section_name, s.academic_year_id
                           FROM students s
                           JOIN classes c ON s.class_id = c.id
                           JOIN sections sec ON s.section_id = sec.id
                           JOIN teacher_assignments ta ON ta.class_id = c.id AND ta.section_id = sec.id
                           $whereClause
                           ORDER BY c.name, sec.name, s.full_name";
            
            $studentsStmt = $conn->prepare($studentsSql);
            $studentsStmt->bind_param($types, ...$params);
            $studentsStmt->execute();
            $studentsResult = $studentsStmt->get_result();
            
            $students = [];
            $totalStudents = 0;
            $fullyPaid = 0;
            $partiallyPaid = 0;
            $unpaid = 0;
            $overdue = 0;
            
            if ($studentsResult && $studentsResult->num_rows > 0) {
                while ($student = $studentsResult->fetch_assoc()) {
                    $totalStudents++;
                    
                    // Get fee structures for this student's class
                    $feeStructuresSql = "SELECT fs.* FROM fee_structures fs
                                       WHERE fs.class_id = ? AND fs.academic_year_id = ?";
                    $feeStructuresStmt = $conn->prepare($feeStructuresSql);
                    $feeStructuresStmt->bind_param('ii', $student['class_id'], $student['academic_year_id']);
                    $feeStructuresStmt->execute();
                    $feeStructuresResult = $feeStructuresStmt->get_result();
                    
                    $totalFees = 0;
                    $paidAmount = 0;
                    $dueAmount = 0;
                    $hasOverdue = false;
                    $today = new DateTime();
                    
                    if ($feeStructuresResult && $feeStructuresResult->num_rows > 0) {
                        while ($structure = $feeStructuresResult->fetch_assoc()) {
                            $totalFees += $structure['amount'];
                            
                            // Check if due date has passed
                            $dueDate = new DateTime($structure['due_date']);
                            if ($dueDate < $today) {
                                $hasOverdue = true;
                            }
                            
                            // Get payments for this structure
                            $paymentsSql = "SELECT SUM(amount_paid) as paid FROM fee_payments
                                          WHERE student_user_id = ? AND fee_structure_id = ?";
                            $paymentsStmt = $conn->prepare($paymentsSql);
                            $paymentsStmt->bind_param('ii', $student['user_id'], $structure['id']);
                            $paymentsStmt->execute();
                            $paymentsResult = $paymentsStmt->get_result();
                            $payment = $paymentsResult->fetch_assoc();
                            
                            $paidAmount += ($payment['paid'] ?: 0);
                        }
                    }
                    
                    $dueAmount = $totalFees - $paidAmount;
                    
                    // Determine payment status
                    $student['total_fees'] = $totalFees;
                    $student['paid_amount'] = $paidAmount;
                    $student['due_amount'] = $dueAmount;
                    
                    if ($totalFees > 0) {
                        $paidPercentage = ($paidAmount / $totalFees) * 100;
                        
                        if ($paidPercentage >= 100) {
                            $student['status'] = 'paid';
                            $fullyPaid++;
                        } else if ($paidPercentage > 0) {
                            $student['status'] = $hasOverdue ? 'overdue' : 'partial';
                            $partiallyPaid++;
                            if ($hasOverdue) $overdue++;
                        } else {
                            $student['status'] = $hasOverdue ? 'overdue' : 'unpaid';
                            $unpaid++;
                            if ($hasOverdue) $overdue++;
                        }
                    } else {
                        $student['status'] = 'no_fees';
                    }
                    
                    $students[] = $student;
                }
                
                $response = [
                    'success' => true,
                    'statistics' => [
                        'total_students' => $totalStudents,
                        'fully_paid' => $fullyPaid,
                        'partially_paid' => $partiallyPaid,
                        'unpaid' => $unpaid,
                        'overdue' => $overdue,
                        'fully_paid_percentage' => $totalStudents > 0 ? round(($fullyPaid / $totalStudents) * 100, 1) : 0,
                        'partially_paid_percentage' => $totalStudents > 0 ? round(($partiallyPaid / $totalStudents) * 100, 1) : 0,
                        'unpaid_percentage' => $totalStudents > 0 ? round(($unpaid / $totalStudents) * 100, 1) : 0,
                        'overdue_percentage' => $totalStudents > 0 ? round(($overdue / $totalStudents) * 100, 1) : 0
                    ],
                    'students' => $students
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No students found in assigned classes'
                ];
            }
            break;
        
        case 'get_student_fee_details':
            // Get detailed fee information for a specific student
            if (isset($_REQUEST['student_id']) && !empty($_REQUEST['student_id'])) {
                $studentId = intval($_REQUEST['student_id']);
                
                // Verify student is in teacher's assigned class
                $verifyStudentSql = "SELECT s.user_id, s.admission_number, s.full_name, s.class_id, s.section_id, 
                                   s.academic_year_id, c.name as class_name, sec.name as section_name
                                   FROM students s
                                   JOIN classes c ON s.class_id = c.id
                                   JOIN sections sec ON s.section_id = sec.id
                                   JOIN teacher_assignments ta ON ta.class_id = c.id AND ta.section_id = sec.id
                                   WHERE s.user_id = ? AND ta.teacher_user_id = ?";
                
                $verifyStudentStmt = $conn->prepare($verifyStudentSql);
                $verifyStudentStmt->bind_param('ii', $studentId, $teacherId);
                $verifyStudentStmt->execute();
                $verifyStudentResult = $verifyStudentStmt->get_result();
                
                if ($verifyStudentResult && $verifyStudentResult->num_rows > 0) {
                    $student = $verifyStudentResult->fetch_assoc();
                    
                    // Get fee structures for this student's class
                    $feeStructuresSql = "SELECT fs.* FROM fee_structures fs
                                       WHERE fs.class_id = ? AND fs.academic_year_id = ?
                                       ORDER BY fs.due_date";
                    $feeStructuresStmt = $conn->prepare($feeStructuresSql);
                    $feeStructuresStmt->bind_param('ii', $student['class_id'], $student['academic_year_id']);
                    $feeStructuresStmt->execute();
                    $feeStructuresResult = $feeStructuresStmt->get_result();
                    
                    $feeStructures = [];
                    $totalFees = 0;
                    $paidAmount = 0;
                    $dueAmount = 0;
                    
                    if ($feeStructuresResult && $feeStructuresResult->num_rows > 0) {
                        while ($structure = $feeStructuresResult->fetch_assoc()) {
                            $totalFees += $structure['amount'];
                            
                            // Get components for this structure
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
                            
                            // Get payments for this structure
                            $paymentsSql = "SELECT * FROM fee_payments
                                          WHERE student_user_id = ? AND fee_structure_id = ?
                                          ORDER BY payment_date DESC";
                            $paymentsStmt = $conn->prepare($paymentsSql);
                            $paymentsStmt->bind_param('ii', $studentId, $structure['id']);
                            $paymentsStmt->execute();
                            $paymentsResult = $paymentsStmt->get_result();
                            
                            $payments = [];
                            $structurePaid = 0;
                            
                            if ($paymentsResult && $paymentsResult->num_rows > 0) {
                                while ($payment = $paymentsResult->fetch_assoc()) {
                                    $payments[] = $payment;
                                    $structurePaid += $payment['amount_paid'];
                                }
                            }
                            
                            $paidAmount += $structurePaid;
                            
                            // Determine structure status
                            $structureRemaining = $structure['amount'] - $structurePaid;
                            $dueDate = new DateTime($structure['due_date']);
                            $today = new DateTime();
                            
                            if ($structureRemaining <= 0) {
                                $status = 'paid';
                            } else if ($structurePaid > 0) {
                                $status = $dueDate < $today ? 'overdue' : 'partial';
                            } else {
                                $status = $dueDate < $today ? 'overdue' : 'unpaid';
                            }
                            
                            $structure['components'] = $components;
                            $structure['payments'] = $payments;
                            $structure['paid_amount'] = $structurePaid;
                            $structure['remaining_amount'] = $structureRemaining;
                            $structure['status'] = $status;
                            
                            $feeStructures[] = $structure;
                        }
                    }
                    
                    $dueAmount = $totalFees - $paidAmount;
                    
                    // Get payment history
                    $paymentHistorySql = "SELECT fp.*, fs.title as fee_title
                                       FROM fee_payments fp
                                       JOIN fee_structures fs ON fp.fee_structure_id = fs.id
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
                    
                    // Get payment proofs pending verification
                    $pendingProofsSql = "SELECT fpp.*, fp.fee_structure_id, fp.payment_date, fp.amount_paid, fp.payment_method, 
                                          fp.reference_number, fp.remarks as payment_remarks, fs.title as fee_title
                                       FROM fee_payment_proofs fpp
                                       JOIN fee_payments fp ON fpp.fee_payment_id = fp.id
                                       JOIN fee_structures fs ON fp.fee_structure_id = fs.id
                                       WHERE fp.student_user_id = ? AND fpp.verification_status = 'pending'
                                       ORDER BY fpp.upload_date DESC";
                                       
                    $pendingProofsStmt = $conn->prepare($pendingProofsSql);
                    $pendingProofsStmt->bind_param("i", $studentId);
                    $pendingProofsStmt->execute();
                    $pendingProofsResult = $pendingProofsStmt->get_result();
                    
                    $pendingProofs = [];
                    if ($pendingProofsResult && $pendingProofsResult->num_rows > 0) {
                        while ($proof = $pendingProofsResult->fetch_assoc()) {
                            $pendingProofs[] = $proof;
                        }
                    }
                    
                    // Calculate summary
                    $totalPercentage = $totalFees > 0 ? round(($paidAmount / $totalFees) * 100, 2) : 0;
                    
                    $summary = [
                        'total_fees' => $totalFees,
                        'paid_amount' => $paidAmount,
                        'due_amount' => $dueAmount,
                        'paid_percentage' => $totalPercentage
                    ];
                    
                    $response = [
                        'success' => true,
                        'student_info' => $student,
                        'fee_structures' => $feeStructures,
                        'payment_history' => $paymentHistory,
                        'pending_proofs' => $pendingProofs,
                        'summary' => $summary
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Student not found in your assigned classes'
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Student ID is required'
                ];
            }
            break;
        
        case 'verify_payment_proof':
            // Verify payment proof
            $teacher_id = $_SESSION['user_id']; // Current logged in teacher
            $proof_id = isset($_POST['proof_id']) ? intval($_POST['proof_id']) : 0;
            $payment_id = isset($_POST['payment_id']) ? intval($_POST['payment_id']) : 0;
            $status = isset($_POST['status']) ? $_POST['status'] : '';
            $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';
            
            if (!$proof_id || !$payment_id) {
                echo json_encode(array('success' => false, 'message' => 'Invalid proof ID or payment ID'));
                exit;
            }
            
            if ($status != 'verified' && $status != 'rejected') {
                echo json_encode(array('success' => false, 'message' => 'Invalid verification status'));
                exit;
            }
            
            // Start a transaction
            $conn->begin_transaction();
            
            try {
                // Update the payment proof status
                $update_proof_sql = "UPDATE fee_payment_proofs 
                                   SET verification_status = ?, 
                                       verified_by = ?, 
                                       verification_date = NOW(), 
                                       rejection_reason = ? 
                                   WHERE id = ?";
                                   
                $stmt = $conn->prepare($update_proof_sql);
                $stmt->bind_param("sisi", $status, $teacher_id, $remarks, $proof_id);
                $stmt->execute();
                
                if ($stmt->affected_rows === 0) {
                    throw new Exception("Failed to update payment proof status");
                }
                
                // If verified, update the payment status to 'paid'
                if ($status === 'verified') {
                    $update_payment_sql = "UPDATE fee_payments SET status = 'paid' WHERE id = ?";
                    $stmt = $conn->prepare($update_payment_sql);
                    $stmt->bind_param("i", $payment_id);
                    $stmt->execute();
                    
                    if ($stmt->affected_rows === 0) {
                        throw new Exception("Failed to update payment status");
                    }
                }
                
                // Commit the transaction
                $conn->commit();
                
                echo json_encode(array('success' => true, 'message' => 'Payment proof successfully ' . $status));
            } catch (Exception $e) {
                // Rollback the transaction on error
                $conn->rollback();
                error_log("Payment verification error: " . $e->getMessage());
                echo json_encode(array('success' => false, 'message' => $e->getMessage()));
            }
            
            exit;
            break;
        
        case 'send_fee_reminder':
            // Send fee reminder to a student or multiple students
            $studentIds = isset($_POST['student_ids']) ? $_POST['student_ids'] : null;
            $message = isset($_POST['message']) ? $_POST['message'] : 'This is a reminder that you have pending fee payments. Please clear your dues at the earliest.';
            
            if ($studentIds && is_array($studentIds) && !empty($studentIds)) {
                $successCount = 0;
                $errors = [];
                
                foreach ($studentIds as $studentId) {
                    // Verify student is in teacher's assigned class
                    $verifyStudentSql = "SELECT s.user_id, s.full_name, s.class_id, s.section_id
                                       FROM students s
                                       JOIN teacher_assignments ta ON ta.class_id = s.class_id AND ta.section_id = s.section_id
                                       WHERE s.user_id = ? AND ta.teacher_user_id = ?";
                    
                    $verifyStudentStmt = $conn->prepare($verifyStudentSql);
                    $verifyStudentStmt->bind_param('ii', $studentId, $teacherId);
                    $verifyStudentStmt->execute();
                    $verifyStudentResult = $verifyStudentStmt->get_result();
                    
                    if ($verifyStudentResult && $verifyStudentResult->num_rows > 0) {
                        $student = $verifyStudentResult->fetch_assoc();
                        
                        // Create a notification
                        $notificationSql = "INSERT INTO notifications (user_id, from_user_id, title, message, type, related_to)
                                          VALUES (?, ?, 'Fee Payment Reminder', ?, 'fee_reminder', 'fees')";
                        
                        $notificationStmt = $conn->prepare($notificationSql);
                        $title = 'Fee Payment Reminder';
                        $notificationStmt->bind_param('iiss', $studentId, $teacherId, $title, $message);
                        
                        if ($notificationStmt->execute()) {
                            $successCount++;
                        } else {
                            $errors[] = "Failed to send reminder to student ID {$studentId}: " . $conn->error;
                        }
                    } else {
                        $errors[] = "Student ID {$studentId} is not in your assigned classes";
                    }
                }
                
                if ($successCount > 0) {
                    $response = [
                        'success' => true,
                        'message' => "Successfully sent {$successCount} fee reminder(s)",
                        'errors' => $errors
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Failed to send any reminders',
                        'errors' => $errors
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Student IDs are required'
                ];
            }
            break;
    }

    // Return JSON response
    echo json_encode($response);
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