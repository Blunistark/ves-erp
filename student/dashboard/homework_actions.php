<?php
// Prevent display of errors and warnings
// error_reporting(0);
// ini_set('display_errors', 0);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'con.php';
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Content-Type: application/json');
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

$student_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Log the received action
error_log("Received action: " . $action);

// Test database connection directly
function testDatabaseConnection() {
    try {
        $conn = getDbConnection();
        if (!$conn) {
            $conn = getStudentDbConnection(); // Try the backup method
            if (!$conn) {
                return ['status' => false, 'message' => 'Failed to connect to database using both methods'];
            }
        }
        
        // Check MySQL version
        $version_result = $conn->query("SELECT VERSION() as version");
        $mysql_version = "Unknown";
        if ($version_result && $row = $version_result->fetch_assoc()) {
            $mysql_version = $row['version'];
        }
        
        // Test a simple query
        $result = $conn->query("SELECT 1 as test_value");
        if (!$result) {
            return [
                'status' => false, 
                'message' => 'Database query failed: ' . $conn->error,
                'mysql_version' => $mysql_version
            ];
        }
        
        // Get the test value
        $test_value = null;
        if ($row = $result->fetch_assoc()) {
            $test_value = $row['test_value'];
        }
        
        // Check data tables existence
        $tables_exist = true;
        $results = [
            'homework' => false,
            'students' => false,
            'subjects' => false,
            'homework_submissions' => false
        ];
        
        // Check each table
        foreach (array_keys($results) as $table) {
            $table_check = $conn->query("SHOW TABLES LIKE '$table'");
            $results[$table] = ($table_check && $table_check->num_rows > 0);
            if (!$results[$table]) {
                $tables_exist = false;
            }
        }
        
        $conn->close();
        return [
            'status' => true, 
            'message' => 'Database connection successful',
            'mysql_version' => $mysql_version,
            'test_value' => $test_value,
            'tables' => $results,
            'all_tables_exist' => $tables_exist
        ];
    } catch (Exception $e) {
        error_log("testDatabaseConnection Exception: " . $e->getMessage());
        return ['status' => false, 'message' => 'Exception: ' . $e->getMessage()];
    }
}

// Get student details
function getStudentDetails($student_id) {
    $conn = getDbConnection();
    if (!$conn) {
        error_log("getStudentDetails: Database connection failed.");
        return null;
    }
    $sql = "SELECT s.*, c.id as class_id, c.name as class_name, sec.id as section_id, sec.name as section_name 
            FROM students s 
            JOIN classes c ON s.class_id = c.id 
            JOIN sections sec ON s.section_id = sec.id 
            WHERE s.user_id = ?";
    error_log("getStudentDetails: SQL query - " . $sql);
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("getStudentDetails: Prepare failed - " . $conn->error);
        $conn->close();
        return null;
    }
    $stmt->bind_param('i', $student_id);
    if (!$stmt->execute()) {
        error_log("getStudentDetails: Execute failed - " . $stmt->error);
        $stmt->close();
        $conn->close();
        return null;
    }
    $result = $stmt->get_result()->fetch_assoc();
    error_log("getStudentDetails: Result - " . print_r($result, true));
    $stmt->close();
    $conn->close();
    return $result;
}

// List homework for student
function listHomework($student_id) {
    try {
        error_log("listHomework: Start");
        $student = getStudentDetails($student_id);
        if (!$student) {
            error_log("listHomework: Failed to get student details");
            return ['status' => 'error', 'message' => 'Failed to retrieve student details'];
        }
        error_log("listHomework: Got student details");

        $conn = getDbConnection();
        if (!$conn) {
            error_log("listHomework: Database connection failed");
            return ['status' => 'error', 'message' => 'Database connection failed'];
        }
        error_log("listHomework: Database connected");
        $status = $_GET['status'] ?? 'all';
        $subject = $_GET['subject'] ?? '';
        error_log("listHomework: Filters - status: " . $status . ", subject: " . $subject);

        // Base query with subject information
        $sql = "SELECT h.*, s.name as subject_name, s.id as subject_id, u.full_name as teacher_name,
                hs.id as submission_id, hs.file_path, hs.status as submission_status,
                hs.grade_code, hs.feedback, hs.submitted_at
                FROM homework h
                JOIN subjects s ON h.subject_id = s.id
                JOIN teachers t ON h.teacher_user_id = t.user_id
                JOIN users u ON t.user_id = u.id
                LEFT JOIN homework_submissions hs ON h.id = hs.homework_id AND hs.student_user_id = ?
                WHERE h.class_id = ? AND h.section_id = ?";
        error_log("listHomework: Base SQL query: " . $sql);

        // Add filters
        if ($subject) {
            $sql .= " AND h.subject_id = ?";
            error_log("listHomework: Added subject filter");
        }
        
        if ($status !== 'all') {
            switch ($status) {
                case 'pending':
                    $sql .= " AND hs.id IS NULL";
                    break;
                case 'submitted':
                    $sql .= " AND hs.status = 'submitted'";
                    break;
                case 'graded':
                    $sql .= " AND hs.status = 'graded'";
                    break;
            }
            error_log("listHomework: Added status filter: " . $status);
        }
        
        // Sort primarily by subject, then by due date
        $sql .= " ORDER BY s.name ASC, h.due_date DESC";
        error_log("listHomework: Final SQL query: " . $sql);

        try {
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("listHomework: Prepare failed - " . $conn->error);
                return ['status' => 'error', 'message' => 'Failed to prepare SQL statement: ' . $conn->error];
            }
            error_log("listHomework: SQL query prepared");
            
            if ($subject) {
                error_log("listHomework: Binding 4 params - student_id: $student_id, class_id: {$student['class_id']}, section_id: {$student['section_id']}, subject_id: $subject");
                $stmt->bind_param('iiii', $student_id, $student['class_id'], $student['section_id'], $subject);
                error_log("listHomework: Bound params (subject)");
            } else {
                error_log("listHomework: Binding 3 params - student_id: $student_id, class_id: {$student['class_id']}, section_id: {$student['section_id']}");
                $stmt->bind_param('iii', $student_id, $student['class_id'], $student['section_id']);
                error_log("listHomework: Bound params (no subject)");
            }
            
            if (!$stmt->execute()) {
                error_log("listHomework: Statement execute failed - " . $stmt->error);
                $stmt->close();
                $conn->close();
                return ['status' => 'error', 'message' => 'Statement execute failed: ' . $stmt->error];
            }
            error_log("listHomework: Statement executed successfully");

            $result = $stmt->get_result();
            if (!$result) {
                error_log("listHomework: get_result failed - " . $stmt->error);
                $stmt->close();
                $conn->close();
                return ['status' => 'error', 'message' => 'Failed to get result: ' . $stmt->error];
            }
            error_log("listHomework: Got result");
            
            $homework = [];
            
            while ($row = $result->fetch_assoc()) {
                $submission = null;
                if ($row['submission_id']) {
                    $submission = [
                        'id' => $row['submission_id'],
                        'file_path' => $row['file_path'],
                        'status' => $row['submission_status'],
                        'grade_code' => $row['grade_code'],
                        'feedback' => $row['feedback'],
                        'submitted_at' => $row['submitted_at']
                    ];
                }
                
                $homework[] = [
                    'id' => $row['id'],
                    'title' => $row['title'],
                    'description' => $row['description'],
                    'subject_id' => $row['subject_id'],
                    'subject_name' => $row['subject_name'],
                    'teacher_name' => $row['teacher_name'],
                    'due_date' => $row['due_date'],
                    'attachment' => $row['attachment'],
                    'submission_type' => $row['submission_type'],
                    'submission' => $submission
                ];
            }
            error_log("listHomework: Processed all homework rows. Count: " . count($homework));

            // Get statistics
            error_log("listHomework: Calling getStudentStats");
            try {
                $stats = getStudentStats($student_id, $conn);
                error_log("listHomework: Returned from getStudentStats");
            } catch (Exception $e) {
                error_log("listHomework: Exception in getStudentStats - " . $e->getMessage());
                $stats = [
                    'pending' => 0,
                    'submitted' => 0,
                    'graded' => 0,
                    'average_grade' => 0
                ];
            }
            
            $stmt->close();
            $conn->close();
            error_log("listHomework: Database connections closed");
            
            $response = [
                'status' => 'success',
                'homework' => $homework,
                'stats' => $stats
            ];
            error_log("listHomework: Return response built. Exiting function.");
            return $response;
        } catch (Exception $e) {
            error_log("listHomework: Exception preparing or executing SQL - " . $e->getMessage());
            if (isset($stmt)) $stmt->close();
            if (isset($conn)) $conn->close();
            return ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
    } catch (Exception $e) {
        error_log("listHomework: Unexpected exception - " . $e->getMessage() . "\n" . $e->getTraceAsString());
        return ['status' => 'error', 'message' => 'Unexpected error: ' . $e->getMessage()];
    }
}

// Get student statistics
function getStudentStats($student_id, $conn) {
    try {
        error_log("getStudentStats: Start");
        $stats = [
            'pending' => 0,
            'submitted' => 0,
            'graded' => 0,
            'average_grade' => 0
        ];

        // Count pending assignments
        $sql = "SELECT COUNT(*) as count FROM homework h 
                LEFT JOIN homework_submissions hs ON h.id = hs.homework_id AND hs.student_user_id = ?
                WHERE h.class_id = (SELECT class_id FROM students WHERE user_id = ?)
                AND h.section_id = (SELECT section_id FROM students WHERE user_id = ?)
                AND hs.id IS NULL AND h.due_date > NOW()";
        error_log("getStudentStats: Pending query - " . $sql);
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("getStudentStats: Prepare failed for pending query - " . $conn->error);
            return $stats;
        }
        $stmt->bind_param('iii', $student_id, $student_id, $student_id);
        if (!$stmt->execute()) {
            error_log("getStudentStats: Execute failed for pending query - " . $stmt->error);
            $stmt->close();
            return $stats;
        }
        $result = $stmt->get_result();
        if (!$result) {
            error_log("getStudentStats: get_result failed for pending query - " . $stmt->error);
            $stmt->close();
            return $stats;
        }
        $row = $result->fetch_assoc();
        if ($row) {
            $stats['pending'] = $row['count'];
        }
        $stmt->close();
        error_log("getStudentStats: Pending count: " . $stats['pending']);

        // Count submitted assignments
        $sql = "SELECT COUNT(*) as submitted, 
                COUNT(CASE WHEN status = 'graded' THEN 1 END) as graded,
                AVG(CASE WHEN grade_code REGEXP '^[0-9]+$' THEN CAST(grade_code AS DECIMAL) END) as avg_grade
                FROM homework_submissions WHERE student_user_id = ?";
        error_log("getStudentStats: Submissions query - " . $sql);
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("getStudentStats: Prepare failed for submissions query - " . $conn->error);
            return $stats;
        }
        $stmt->bind_param('i', $student_id);
        if (!$stmt->execute()) {
            error_log("getStudentStats: Execute failed for submissions query - " . $stmt->error);
            $stmt->close();
            return $stats;
        }
        $result = $stmt->get_result();
        if (!$result) {
            error_log("getStudentStats: get_result failed for submissions query - " . $stmt->error);
            $stmt->close();
            return $stats;
        }
        $row = $result->fetch_assoc();
        if ($row) {
            $stats['submitted'] = $row['submitted'];
            $stats['graded'] = $row['graded'];
            $stats['average_grade'] = $row['avg_grade'] ? round($row['avg_grade'], 1) : 0;
        }
        $stmt->close();
        error_log("getStudentStats: Submitted: " . $stats['submitted'] . ", Graded: " . $stats['graded'] . ", Avg Grade: " . $stats['average_grade']);
        error_log("getStudentStats: End");
        return $stats;
    } catch (Exception $e) {
        error_log("getStudentStats: Exception - " . $e->getMessage() . "\n" . $e->getTraceAsString());
        return [
            'pending' => 0,
            'submitted' => 0,
            'graded' => 0,
            'average_grade' => 0
        ];
    }
}

// Submit homework
function submitHomework($student_id) {
    $homework_id = $_POST['homework_id'] ?? null;
    if (!$homework_id) {
        return ['status' => 'error', 'message' => 'Homework ID is required'];
    }

    // Check if homework exists and belongs to student's class/section
    $conn = getDbConnection();
    $sql = "SELECT h.* FROM homework h
            JOIN students s ON h.class_id = s.class_id AND h.section_id = s.section_id
            WHERE h.id = ? AND s.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $homework_id, $student_id);
    $stmt->execute();
    $homework = $stmt->get_result()->fetch_assoc();
    if (!$homework) {
        $stmt->close();
        $conn->close();
        return ['status' => 'error', 'message' => 'Invalid homework assignment'];
    }

    // Check if already submitted
    $sql = "SELECT id FROM homework_submissions WHERE homework_id = ? AND student_user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $homework_id, $student_id);
    $stmt->execute();
    if ($stmt->get_result()->fetch_assoc()) {
        $stmt->close();
        $conn->close();
        return ['status' => 'error', 'message' => 'Homework already submitted'];
    }

    // Handle file upload
    $file_path = null;
    $has_file = isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK;
    $is_offline = isset($_POST['offline_submission']) && $_POST['offline_submission'] == '1';
    
    // Validate submission based on homework submission type
    if ($homework['submission_type'] === 'online' && !$has_file) {
        $stmt->close();
        $conn->close();
        return ['status' => 'error', 'message' => 'This assignment requires online submission. Please upload a file.'];
    }
    
    if ($homework['submission_type'] === 'physical' && !$is_offline) {
        $stmt->close();
        $conn->close();
        return ['status' => 'error', 'message' => 'This assignment requires physical submission. Please mark it as submitted offline.'];
    }
    
    if ($homework['submission_type'] === 'both' && !$has_file && !$is_offline) {
        $stmt->close();
        $conn->close();
        return ['status' => 'error', 'message' => 'Please either upload a file or mark this assignment as submitted offline.'];
    }
    
    if ($has_file) {
        $upload_dir = '../../uploads/homework_submissions/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt', 'zip'];
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $stmt->close();
            $conn->close();
            return ['status' => 'error', 'message' => 'Invalid file type. Allowed types: PDF, DOC, DOCX, JPG, PNG, TXT, ZIP'];
        }
        
        $file_name = uniqid('submission_') . '.' . $file_ext;
        $target_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
            $file_path = 'uploads/homework_submissions/' . $file_name;
        } else {
            $stmt->close();
            $conn->close();
            return ['status' => 'error', 'message' => 'Failed to upload file. Please try again.'];
        }
    }

    // Insert submission
    $sql = "INSERT INTO homework_submissions (homework_id, student_user_id, file_path, status) 
            VALUES (?, ?, ?, 'submitted')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iis', $homework_id, $student_id, $file_path);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return ['status' => 'success', 'message' => 'Homework submitted successfully'];
    } else {
        $stmt->close();
        $conn->close();
        return ['status' => 'error', 'message' => 'Failed to submit homework: ' . $conn->error];
    }
}

// Handle actions
header('Content-Type: application/json');
$response = [];

// Check for debug mode
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';
if ($debug) {
    error_log("Debug mode enabled");
}

switch ($action) {
    case 'test_connection':
        $response = testDatabaseConnection();
        break;
    case 'list_homework':
        try {
            // Basic environment check before executing main function
            if ($debug) {
                $dbTest = testDatabaseConnection();
                if (!$dbTest['status']) {
                    error_log("Database connection test failed: " . $dbTest['message']);
                    $response = ['status' => 'error', 'message' => 'Database connection failed: ' . $dbTest['message']];
                    break;
                }
                error_log("Database connection test passed");
            }
            
            $response = listHomework($student_id);
            
            // In debug mode, add PHP version and other environment info
            if ($debug) {
                $response['debug'] = [
                    'php_version' => PHP_VERSION,
                    'time' => date('Y-m-d H:i:s'),
                    'extensions' => [
                        'mysqli' => extension_loaded('mysqli'),
                        'json' => extension_loaded('json')
                    ]
                ];
            }
        } catch (Exception $e) {
            error_log("Unexpected exception in action handler: " . $e->getMessage());
            $response = ['status' => 'error', 'message' => 'An unexpected error occurred: ' . $e->getMessage()];
        }
        break;
    case 'submit_homework':
        $response = submitHomework($student_id);
        break;
    case 'get_subjects':
        $student = getStudentDetails($student_id);
        if (!$student) {
            $response = ['status' => 'error', 'message' => 'Failed to retrieve student details'];
            break;
        }
        $conn = getDbConnection();
        if (!$conn) {
            $response = ['status' => 'error', 'message' => 'Database connection failed'];
            break;
        }
        $sql = "SELECT DISTINCT s.* FROM subjects s
                JOIN homework h ON s.id = h.subject_id
                WHERE h.class_id = ? AND h.section_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $student['class_id'], $student['section_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $subjects = [];
        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }
        $response = ['status' => 'success', 'subjects' => $subjects];
        $stmt->close();
        $conn->close();
        break;
    default:
        $response = ['status' => 'error', 'message' => 'Invalid action'];
}

// Ensure we have a clean output buffer before sending JSON
if (ob_get_length()) {
    ob_clean();
}

echo json_encode($response);