<?php
// Start output buffering to catch any stray output
ob_start();

require_once __DIR__ . '/../../includes/functions.php';

// DEBUG: Enable error reporting for debugging but prevent HTML output
error_reporting(E_ALL);
ini_set('display_errors', 0); // CRITICAL: Disable display_errors to prevent HTML in JSON response
ini_set('display_startup_errors', 0);
ini_set('log_errors', 1);

// Set JSON content type FIRST
header('Content-Type: application/json');

// Custom error handler to prevent HTML output
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    global $debug_log_file;
    $error_msg = "PHP Error [$errno]: $errstr in $errfile on line $errline";
    file_put_contents($debug_log_file, "[" . date('Y-m-d H:i:s') . "] $error_msg\n", FILE_APPEND | LOCK_EX);
    return true; // Don't execute PHP's internal error handler
});

// Custom exception handler
set_exception_handler(function($exception) {
    global $debug_log_file;
    $error_msg = "Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    file_put_contents($debug_log_file, "[" . date('Y-m-d H:i:s') . "] $error_msg\n", FILE_APPEND | LOCK_EX);
    
    // Clear any output buffer
    if (ob_get_level()) {
        ob_clean();
    }
    
    echo json_encode(['success' => false, 'message' => 'Internal server error', 'debug' => 'Check server logs']);
    exit;
});

// DEBUG: Log file for debugging
$debug_log_file = __DIR__ . '/debug_student_api.log';

function debug_log($message) {
    global $debug_log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($debug_log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

debug_log("=== STUDENT API CALLED ===");
debug_log("REQUEST METHOD: " . $_SERVER['REQUEST_METHOD']);
debug_log("REQUEST URI: " . $_SERVER['REQUEST_URI']);

// Initialize connection variable
$conn = null;

try {
    require_once 'con.php';
    debug_log("DATABASE CONNECTION: Success");
} catch (Exception $e) {
    debug_log("DATABASE CONNECTION ERROR: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database connection failed', 'error' => $e->getMessage()]);
    exit;
}
debug_log("GET DATA: " . json_encode($_GET));
debug_log("POST DATA: " . json_encode($_POST));
debug_log("REQUEST DATA: " . json_encode($_REQUEST));
debug_log("FILES DATA: " . json_encode($_FILES));

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    try {
        startSecureSession();
        debug_log("SESSION STARTED: Success");
    } catch (Exception $e) {
        debug_log("SESSION START ERROR: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Session start failed', 'error' => $e->getMessage()]);
        exit;
    }
}

debug_log("SESSION STATUS: " . session_status());
debug_log("SESSION DATA: " . json_encode($_SESSION));

// Check authentication and role (basic placeholder)
$is_logged_in = function_exists('isLoggedIn') ? isLoggedIn() : false;
$has_role = function_exists('hasRole') ? hasRole(['admin', 'headmaster']) : false;

debug_log("IS_LOGGED_IN: " . ($is_logged_in ? 'true' : 'false'));
debug_log("HAS_ROLE: " . ($has_role ? 'true' : 'false'));

if (!$is_logged_in || !$has_role) {
    debug_log("AUTHORIZATION FAILED - User not logged in or doesn't have required role");
    echo json_encode(['success' => false, 'message' => 'Unauthorized_access']);
    exit;
}

// Parse JSON input if it's a POST request with potential JSON content
$json_input = null;
$raw_input = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_input = file_get_contents('php://input');
    debug_log("RAW INPUT: " . $raw_input);
    
    // Try to parse as JSON if it looks like JSON or if Content-Type suggests JSON
    $content_type = $_SERVER['CONTENT_TYPE'] ?? '';
    $looks_like_json = (strlen($raw_input) > 0 && (substr(trim($raw_input), 0, 1) === '{' || substr(trim($raw_input), 0, 1) === '['));
    $is_json_content_type = strpos($content_type, 'application/json') !== false;
    
    if ($looks_like_json || $is_json_content_type) {
        $json_input = json_decode($raw_input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            debug_log("JSON PARSE ERROR: " . json_last_error_msg());
        } else {
            debug_log("JSON INPUT: " . json_encode($json_input));
        }
    }
}

// Get action from JSON input, POST data, or GET data (in that order of preference)
$action = null;
if ($json_input && isset($json_input['action'])) {
    $action = $json_input['action'];
    debug_log("ACTION SOURCE: JSON input");
} elseif (!empty($_POST['action'])) {
    $action = $_POST['action'];
    debug_log("ACTION SOURCE: POST data");
} elseif (!empty($_GET['action'])) {
    $action = $_GET['action'];
    debug_log("ACTION SOURCE: GET data");
} else {
    debug_log("ACTION SOURCE: Not found in any source");
    
    // If it's a GET request without action, provide API info
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode([
            'success' => false, 
            'message' => 'Missing action parameter',
            'info' => 'This is the Student Management API. Use POST requests with JSON body containing an action parameter.',
            'available_actions' => ['get_students', 'get_student_details', 'add_student', 'update_student', 'update_student_status'],
            'example' => ['action' => 'get_students', 'page' => 1, 'limit' => 20]
        ]);
        exit;
    }
}

$user_role = $_SESSION['role'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

debug_log("ACTION: " . ($action ?? 'NULL'));
debug_log("USER_ROLE: " . ($user_role ?? 'NULL'));
debug_log("USER_ID: " . ($user_id ?? 'NULL'));

$response = ['success' => false, 'message' => 'Invalid_action'];

// Function to safely get a value from an array
if (!function_exists('safe_get')) {
    function safe_get($array, $key, $default = null) {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}

define('STUDENT_PHOTO_UPLOAD_DIR', __DIR__ . '/../../uploads/student_photos/');

debug_log("ENTERING SWITCH STATEMENT WITH ACTION: " . ($action ?? 'NULL'));

switch ($action) {
    // Student CRUD
    case 'get_students':
        debug_log("CASE: get_students - Starting execution");
        try {
            // Use JSON input if available, otherwise use $_REQUEST
            $params = $json_input ?? $_REQUEST;
            debug_log("CASE: get_students - Using parameters: " . json_encode($params));
            $response = getStudentsList($conn, $params);
            debug_log("CASE: get_students - Success: " . json_encode($response));
        } catch (Exception $e) {
            debug_log("CASE: get_students - Exception: " . $e->getMessage());
            $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
        break;    case 'get_student_details':
        debug_log("CASE: get_student_details - Starting execution");
        try {
            // Get student ID from JSON input or regular input
            $studentId = null;
            if ($json_input && isset($json_input['student_id'])) {
                $studentId = $json_input['student_id'];
                debug_log("CASE: get_student_details - Student ID from JSON: " . $studentId);
            } elseif (!empty($_POST['student_id'])) {
                $studentId = $_POST['student_id'];
                debug_log("CASE: get_student_details - Student ID from POST: " . $studentId);
            } elseif (!empty($_GET['student_id'])) {
                $studentId = $_GET['student_id'];
                debug_log("CASE: get_student_details - Student ID from GET: " . $studentId);
            }
            
            if (empty($studentId)) {
                debug_log("CASE: get_student_details - No student ID provided");
                $response = ['success' => false, 'message' => 'Student ID is required.'];
            } else {
                debug_log("CASE: get_student_details - Calling getStudentDetails with ID: " . $studentId);
                $response = getStudentDetails($conn, $studentId);
                debug_log("CASE: get_student_details - Response: " . json_encode($response));
            }
        } catch (Exception $e) {
            debug_log("CASE: get_student_details - Exception: " . $e->getMessage());
            $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
        break;
    case 'add_student':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $response = addStudent($conn, $_POST, $_FILES);
        } else {
            $response = ['success' => false, 'message' => 'Invalid request method for add_student.'];
        }
        break;
    case 'update_student':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentUserId = safe_get($_POST, 'user_id'); // Assuming user_id of the student is passed
            if (empty($studentUserId)) {
                $response = ['success' => false, 'message' => 'Student User ID is required for update.'];
            } else {
                $response = updateStudent($conn, $studentUserId, $_POST, $_FILES);
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid request method for update_student.'];
        }
        break;
    case 'update_student_status':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
           $studentUserId = safe_get($_POST, 'user_id');
           $status = safe_get($_POST, 'status'); // Expected: 'active', 'inactive', 'suspended', 'graduated', etc.
           if (empty($studentUserId) || empty($status)) {
               $response = ['success' => false, 'message' => 'Student User ID and Status are required.'];
           } else {
               $response = updateStudentUserStatus($conn, $studentUserId, $status);
           }
        } else {
            $response = ['success' => false, 'message' => 'Invalid request method for update_student_status.'];
        }
        break;

    // Enrollment & Class Management
    case 'get_student_enrollment_history':
        $studentUserId = safe_get($_GET, 'user_id');
        if (empty($studentUserId)) {
            $response = ['success' => false, 'message' => 'Student User ID is required.'];
        } else {
            $response = getStudentEnrollmentHistory($conn, $studentUserId);
        }
        break;
    case 'assign_student_to_class_section':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentUserId = safe_get($_POST, 'user_id');
            $classId = safe_get($_POST, 'class_id');
            $sectionId = safe_get($_POST, 'section_id');
            $academicYearId = safe_get($_POST, 'academic_year_id');
            $enrollmentDate = safe_get($_POST, 'enrollment_date', date('Y-m-d')); // Default to today

            if (empty($studentUserId) || empty($classId) || empty($sectionId) || empty($academicYearId)) {
                $response = ['success' => false, 'message' => 'Student User ID, Class ID, Section ID, and Academic Year ID are required.'];
            } else {
                $response = assignStudentToClassSection($conn, $studentUserId, $classId, $sectionId, $academicYearId, $enrollmentDate);
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid request method.'];
        }
        break;

    // Parent Management
    case 'link_parent_to_student':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentUserId = safe_get($_POST, 'student_user_id');
            $parentUserId = safe_get($_POST, 'parent_user_id');
            $relationshipType = safe_get($_POST, 'relationship_type'); // e.g., 'Father', 'Mother', 'Guardian'

            if (empty($studentUserId) || empty($parentUserId) || empty($relationshipType)) {
                $response = ['success' => false, 'message' => 'Student User ID, Parent User ID, and Relationship Type are required.'];
            } else {
                $response = linkParentToStudent($conn, $studentUserId, $parentUserId, $relationshipType);
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid request method.'];
        }
        break;
    case 'get_student_parents':
        $studentUserId = safe_get($_GET, 'student_user_id');
        if (empty($studentUserId)) {
            $response = ['success' => false, 'message' => 'Student User ID is required.'];
        } else {
            $response = getStudentParents($conn, $studentUserId);
        }
        break;

    // Student Transfers/Promotions
    case 'transfer_student':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentUserId = safe_get($_POST, 'student_user_id');
            $fromClassId = safe_get($_POST, 'from_class_id'); // Current class_id of student
            $fromSectionId = safe_get($_POST, 'from_section_id'); // Current section_id of student
            $toClassId = safe_get($_POST, 'to_class_id');
            $toSectionId = safe_get($_POST, 'to_section_id');
            $transferAcademicYearId = safe_get($_POST, 'transfer_academic_year_id'); // The AY in which transfer occurs
            $transferDate = safe_get($_POST, 'transfer_date', date('Y-m-d'));
            $reason = safe_get($_POST, 'reason');
            $notes = safe_get($_POST, 'notes');

            if (empty($studentUserId) || empty($toClassId) || empty($toSectionId) || empty($transferAcademicYearId)) {
                $response = ['success' => false, 'message' => 'Student User ID, To Class ID, To Section ID, and Transfer Academic Year ID are required.'];
            } else {
                // fromClassId and fromSectionId can be fetched if not provided, or taken as is.
                // For this implementation, we assume they are provided or can be derived from student's current record if needed by the function.
                $response = transferStudent(
                    $conn, $studentUserId, 
                    $fromClassId, $fromSectionId, 
                    $toClassId, $toSectionId, 
                    $transferAcademicYearId, $transferDate, 
                    $reason, $notes
                );
            }
        } else {
            $response = ['success' => false, 'message' => 'Invalid request method.'];
        }
        break;
    case 'get_transfer_history':
        $studentUserId = safe_get($_GET, 'student_user_id');
        if (empty($studentUserId)) {
            $response = ['success' => false, 'message' => 'Student User ID is required.'];
        } else {
            $response = getTransferHistory($conn, $studentUserId);
        }
        break;

    // Bulk Operations
    case 'bulk_import_students':
        // Placeholder
        $response = bulkImportStudents($conn, $_FILES);
        break;
    case 'bulk_promote_students':
        // Placeholder
        $response = bulkPromoteStudents($conn, $_POST);
        break;

    // Data Fetching for UI
    case 'get_classes_sections':
        // Placeholder
        break;
    case 'get_academic_years':
        // Placeholder
        break;
      default:
        debug_log("DEFAULT CASE REACHED - Unknown action: " . ($action ?? 'NULL'));
        debug_log("Available actions should be: get_students, get_student_details, add_student, update_student, etc.");
        $response = ['success' => false, 'message' => 'Unknown_action', 'received_action' => $action, 'debug' => 'Check console/logs for details'];
        break;
}

debug_log("FINAL RESPONSE: " . json_encode($response));

// Clear any output that might have been generated
if (ob_get_level()) {
    ob_clean();
}

echo json_encode($response);
exit;

function getStudentsList($conn, $params) {
    $page = isset($params['page']) ? (int)$params['page'] : 1;
    $limit = isset($params['limit']) ? (int)$params['limit'] : 20; // Default 20 students per page
    $offset = ($page - 1) * $limit;    
    $sql = "SELECT s.user_id as id, s.admission_number, s.full_name, s.admission_date, 
                   s.roll_number, s.photo, u.email, s.mobile as phone,
                   c.name AS class_name, se.name AS section_name, u.status, s.gender_code as gender,
                   s.dob as date_of_birth, s.mother_name, s.father_name
            FROM students s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN sections se ON s.section_id = se.id
            WHERE u.role = 'student'"; // Ensure we only fetch users with student role

    $countSql = "SELECT COUNT(s.user_id) as total
                 FROM students s
                 JOIN users u ON s.user_id = u.id
                 LEFT JOIN classes c ON s.class_id = c.id
                 LEFT JOIN sections se ON s.section_id = se.id
                 WHERE u.role = 'student'";

    $whereClauses = [];
    $bindParams = [];
    $bindTypes = '';

    // Filter by class_id
    if (!empty($params['class_id'])) {
        $whereClauses[] = "s.class_id = ?";
        $bindParams[] = $params['class_id'];
        $bindTypes .= 'i';
    }

    // Filter by section_id
    if (!empty($params['section_id'])) {
        $whereClauses[] = "s.section_id = ?";
        $bindParams[] = $params['section_id'];
        $bindTypes .= 'i';
    }

    // Filter by status (from users table)
    if (!empty($params['status'])) {
        $whereClauses[] = "u.status = ?";
        $bindParams[] = $params['status'];
        $bindTypes .= 's';
    }    // Search term (student name, admission number, email)
    if (!empty($params['search'])) {
        $searchTerm = '%' . $params['search'] . '%';
        $whereClauses[] = "(s.full_name LIKE ? OR s.admission_number LIKE ? OR u.email LIKE ?)";
        $bindParams[] = $searchTerm;
        $bindParams[] = $searchTerm;
        $bindParams[] = $searchTerm;
        $bindTypes .= 'sss';
    }

    if (!empty($whereClauses)) {
        $sql .= " AND " . implode(" AND ", $whereClauses);
        $countSql .= " AND " . implode(" AND ", $whereClauses);
    }

    // Get total count for pagination
    $totalStudents = 0;
    if (empty($bindParams)) {
        $countResult = $conn->query($countSql);
        if ($countResult) {
            $totalStudents = $countResult->fetch_assoc()['total'];
        }
    } else {
        $stmtCount = $conn->prepare($countSql);
        if ($stmtCount) {
            $stmtCount->bind_param($bindTypes, ...$bindParams);
            $stmtCount->execute();
            $countResult = $stmtCount->get_result();
            if ($countResult) {
                $totalStudents = $countResult->fetch_assoc()['total'];
            }
            $stmtCount->close();
        } else {
            return ['success' => false, 'message' => 'Failed to prepare count statement: ' . $conn->error, 'query' => $countSql];
        }
    }
    
    $sql .= " ORDER BY s.full_name ASC LIMIT ? OFFSET ?";
    $bindTypes .= 'ii';
    $bindParams[] = $limit;
    $bindParams[] = $offset;

    $students = [];
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        if (!empty($bindParams)) {
            $stmt->bind_param($bindTypes, ...$bindParams);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }        $stmt->close();
    } else {
        return ['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error, 'query' => $sql];
    }

    // Get basic stats
    $stats = [
        'active' => 0,
        'male' => 0,
        'female' => 0
    ];

    // Count active students
    $activeStmt = $conn->prepare("SELECT COUNT(*) as count FROM students s JOIN users u ON s.user_id = u.id WHERE u.role = 'student' AND u.status = 'active'");
    if ($activeStmt) {
        $activeStmt->execute();
        $result = $activeStmt->get_result();
        if ($result) {
            $stats['active'] = $result->fetch_assoc()['count'];
        }
        $activeStmt->close();
    }

    // Count male/female students
    $genderStmt = $conn->prepare("SELECT gender_code, COUNT(*) as count FROM students s JOIN users u ON s.user_id = u.id WHERE u.role = 'student' GROUP BY gender_code");
    if ($genderStmt) {
        $genderStmt->execute();
        $result = $genderStmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if ($row['gender_code'] === 'M') {
                $stats['male'] = $row['count'];
            } elseif ($row['gender_code'] === 'F') {
                $stats['female'] = $row['count'];
            }
        }
        $genderStmt->close();
    }

    return [
        'success' => true,
        'students' => $students,
        'pagination' => [
            'total' => (int)$totalStudents,
            'currentPage' => $page,
            'totalPages' => ceil($totalStudents / $limit),
            'limit' => $limit
        ],
        'stats' => [
            'total' => (int)$totalStudents,
            'active' => (int)$stats['active'],
            'male' => (int)$stats['male'],
            'female' => (int)$stats['female']
        ]
        ];
}

function getStudentDetails($conn, $studentId) {
    debug_log("getStudentDetails - Starting with student ID: " . $studentId);
    
    $sql = "SELECT 
                s.user_id, s.admission_number, s.full_name, s.admission_date, s.roll_number, s.photo,
                s.gender_code, g.label as gender_name, s.dob, s.blood_group_code, bg.label as blood_group_name,
                s.nationality, s.mobile, s.contact_email, s.address, s.pincode, s.alt_mobile,
                s.father_name, s.father_aadhar_number,
                s.mother_name, s.mother_aadhar_number, s.mother_tongue,
                s.aadhar_card_number, s.medical_conditions, s.student_state_code,
                s.created_at AS student_created_at, s.updated_at AS student_updated_at,
                u.email AS user_email, u.status AS user_status, u.created_at AS user_created_at, u.last_login,
                c.id AS class_id, c.name AS class_name, 
                se.id AS section_id, se.name AS section_name,
                ay.id AS academic_year_id, ay.name AS academic_year_name
            FROM students s
            JOIN users u ON s.user_id = u.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN sections se ON s.section_id = se.id
            LEFT JOIN genders g ON s.gender_code = g.code
            LEFT JOIN blood_groups bg ON s.blood_group_code = bg.code
            LEFT JOIN academic_years ay ON s.academic_year_id = ay.id 
            WHERE s.user_id = ? AND u.role = 'student'";

    debug_log("getStudentDetails - SQL Query: " . $sql);
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        debug_log("getStudentDetails - Failed to prepare statement: " . $conn->error);
        return ['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error, 'query' => $sql];
    }

    $stmt->bind_param('i', $studentId);
    debug_log("getStudentDetails - Executing query with student ID: " . $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $studentDetails = $result->fetch_assoc();
    $stmt->close();

    debug_log("getStudentDetails - Query result: " . json_encode($studentDetails));

    if ($studentDetails) {
        // Further enrichment, e.g., parent details, enrollment history could be added here if needed
        debug_log("getStudentDetails - Success, returning student details");
        return ['success' => true, 'data' => $studentDetails];
    } else {
        debug_log("getStudentDetails - No student found with ID: " . $studentId);
        return ['success' => false, 'message' => 'Student not found or is not a student.'];
    }
}

function updateStudent($conn, $studentUserId, $postData, $filesData) {
    $conn->begin_transaction();

    try {
        // --- Validate student exists ---
        $stmtCheckStudent = $conn->prepare("SELECT s.user_id, s.photo, u.email FROM students s JOIN users u ON s.user_id = u.id WHERE s.user_id = ? AND u.role = 'student'");
        if (!$stmtCheckStudent) {
            throw new Exception("Failed to prepare student check statement: " . $conn->error);
        }
        $stmtCheckStudent->bind_param("i", $studentUserId);
        $stmtCheckStudent->execute();
        $resultCheckStudent = $stmtCheckStudent->get_result();
        $existingStudent = $resultCheckStudent->fetch_assoc();
        $stmtCheckStudent->close();

        if (!$existingStudent) {
            throw new Exception("Student not found or user is not a student.");
        }
        $currentPhotoPath = $existingStudent['photo'];
        $currentEmail = $existingStudent['email'];

        // --- User Data Update (Email, Username, Status) ---
        $updateUserFields = [];
        $userBindParams = [];
        $userBindTypes = '';

        if (!empty($postData['email']) && $postData['email'] !== $currentEmail) {
            // Check if new email already exists for another user
            $stmtCheckEmail = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmtCheckEmail->bind_param("si", $postData['email'], $studentUserId);
            $stmtCheckEmail->execute();
            if ($stmtCheckEmail->get_result()->num_rows > 0) {
                throw new Exception("New email address is already in use by another account.");
            }
            $stmtCheckEmail->close();
            $updateUserFields[] = "email = ?";
            $userBindParams[] = $postData['email'];
            $userBindTypes .= 's';
        }

        if (!empty($postData['username'])) {
            $updateUserFields[] = "username = ?";
            $userBindParams[] = $postData['username'];
            $userBindTypes .= 's';
        }
        if (!empty($postData['status'])) {
            $updateUserFields[] = "status = ?";
            $userBindParams[] = $postData['status'];
            $userBindTypes .= 's';
        }

        if (!empty($updateUserFields)) {
            $updateUserFields[] = "updated_at = NOW()";
            $sqlUserUpdate = "UPDATE users SET " . implode(", ", $updateUserFields) . " WHERE id = ?";
            $userBindParams[] = $studentUserId;
            $userBindTypes .= 'i';

            $stmtUserUpdate = $conn->prepare($sqlUserUpdate);
            if (!$stmtUserUpdate) {
                throw new Exception("Failed to prepare user update statement: " . $conn->error);
            }
            $stmtUserUpdate->bind_param($userBindTypes, ...$userBindParams);
            if (!$stmtUserUpdate->execute()) {
                throw new Exception("Failed to update user details: " . $stmtUserUpdate->error);
            }
            $stmtUserUpdate->close();
        }

        // --- Password Update (if provided) ---
        if (!empty($postData['password'])) {
            $password_hash = password_hash($postData['password'], PASSWORD_DEFAULT);
            $stmtPassUpdate = $conn->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            if (!$stmtPassUpdate) {
                throw new Exception("Failed to prepare password update statement: " . $conn->error);
            }
            $stmtPassUpdate->bind_param('si', $password_hash, $studentUserId);
            if (!$stmtPassUpdate->execute()) {
                throw new Exception("Failed to update password: " . $stmtPassUpdate->error);
            }
            $stmtPassUpdate->close();
        }

        // --- Student Photo Handling ---
        $newPhotoPath = $currentPhotoPath;
        if (isset($filesData['photo']) && $filesData['photo']['error'] == UPLOAD_ERR_OK) {
            if (!file_exists(STUDENT_PHOTO_UPLOAD_DIR)) {
                mkdir(STUDENT_PHOTO_UPLOAD_DIR, 0777, true);
            }
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($filesData['photo']['type'], $allowedTypes)) {
                throw new Exception("Invalid photo file type. Allowed: JPG, PNG, GIF.");
            }
            if ($filesData['photo']['size'] > 2097152) { // 2MB limit
                throw new Exception("Photo file size exceeds 2MB limit.");
            }

            // Delete old photo if it exists and is not a placeholder
            if ($currentPhotoPath && file_exists(__DIR__ . '/../../' . $currentPhotoPath) && strpos($currentPhotoPath, 'default') === false) {
                unlink(__DIR__ . '/../../' . $currentPhotoPath);
            }

            $photoExtension = pathinfo($filesData['photo']['name'], PATHINFO_EXTENSION);
            $photoFilename = 'student_' . $studentUserId . '_' . time() . '.' . $photoExtension;
            $uploadedPhotoFullPath = STUDENT_PHOTO_UPLOAD_DIR . $photoFilename;

            if (!move_uploaded_file($filesData['photo']['tmp_name'], $uploadedPhotoFullPath)) {
                throw new Exception("Failed to upload new student photo.");
            }
            $newPhotoPath = 'uploads/student_photos/' . $photoFilename;
        }        // --- Student Data Update ---
        // Define fields that can be updated in the students table
        $studentUpdateFields = [];
        $studentBindParams = [];
        $studentBindTypes = '';
        
        $allowedStudentFields = [
            'full_name', 'class_id', 'section_id', 'academic_year_id', 'roll_number', 'gender_code', 'dob', 
            'blood_group_code', 'nationality', 'mobile', 'contact_email', 'address', 'pincode', 'alt_mobile',
            'father_name', 'father_aadhar_number', 'mother_name', 'mother_aadhar_number', 'mother_tongue',
            'aadhar_card_number', 'medical_conditions', 'student_state_code', 'admission_date'
        ];
        // Admission number is generally not updated, but can be if logic requires.

        foreach ($allowedStudentFields as $field) {
            if (isset($postData[$field])) {
                $studentUpdateFields[] = "{$field} = ?";
                $studentBindParams[] = $postData[$field];
                // Determine type (simplified: i for ids, d for date/dob, s for others)
                if (in_array($field, ['class_id', 'section_id', 'academic_year_id'])) {
                    $studentBindTypes .= 'i';
                } elseif (in_array($field, ['dob', 'admission_date'])) {
                    $studentBindTypes .= 's'; // Assuming dates are passed as strings in YYYY-MM-DD format
                } else {
                    $studentBindTypes .= 's';
                }
            }
        }

        // Update photo path if it changed
        if ($newPhotoPath !== $currentPhotoPath) {
            $studentUpdateFields[] = "photo = ?";
            $studentBindParams[] = $newPhotoPath;
            $studentBindTypes .= 's';
        }

        if (!empty($studentUpdateFields)) {
            $studentUpdateFields[] = "updated_at = NOW()";
            $sqlStudentUpdate = "UPDATE students SET " . implode(", ", $studentUpdateFields) . " WHERE user_id = ?";
            $studentBindParams[] = $studentUserId;
            $studentBindTypes .= 'i';

            $stmtStudentUpdate = $conn->prepare($sqlStudentUpdate);
            if (!$stmtStudentUpdate) {
                throw new Exception("Failed to prepare student update statement: " . $conn->error);
            }
            $stmtStudentUpdate->bind_param($studentBindTypes, ...$studentBindParams);
            if (!$stmtStudentUpdate->execute()) {
                throw new Exception("Failed to update student details: " . $stmtStudentUpdate->error);
            }
            $stmtStudentUpdate->close();
        }

        $conn->commit();
        return ['success' => true, 'message' => 'Student updated successfully.', 'user_id' => $studentUserId];

    } catch (Exception $e) {
        $conn->rollback();
        // If a new photo was uploaded and transaction failed, delete the newly uploaded photo
        if (isset($uploadedPhotoFullPath) && $newPhotoPath !== $currentPhotoPath && file_exists($uploadedPhotoFullPath)) {
            unlink($uploadedPhotoFullPath);
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function transferStudent($conn, $studentUserId, $fromClassId, $fromSectionId, $toClassId, $toSectionId, $transferAcademicYearId, $transferDate, $reason = null, $notes = null) {
    $conn->begin_transaction();
    try {
        // 1. Validate student exists and get current class/section if not provided
        $stmtCheckStudent = $conn->prepare("SELECT class_id, section_id FROM students WHERE user_id = ?");
        if (!$stmtCheckStudent) throw new Exception("Failed to prepare student check: " . $conn->error);
        $stmtCheckStudent->bind_param("i", $studentUserId);
        $stmtCheckStudent->execute();
        $studentData = $stmtCheckStudent->get_result()->fetch_assoc();
        if (!$studentData) {
            throw new Exception("Student with User ID {$studentUserId} not found.");
        }
        $stmtCheckStudent->close();

        // Use fetched current class/section if fromClassId/fromSectionId are not explicitly passed or are empty
        $currentClassId = empty($fromClassId) ? $studentData['class_id'] : $fromClassId;
        $currentSectionId = empty($fromSectionId) ? $studentData['section_id'] : $fromSectionId;

        // Basic validation for target class and section (existence checks are good practice)
        // For brevity, skipping detailed existence checks for to_class_id, to_section_id, transfer_academic_year_id

        // 2. Update student's current class, section, and academic year in `students` table
        $sqlUpdateStudent = "UPDATE students SET class_id = ?, section_id = ?, academic_year_id = ?, updated_at = NOW() WHERE user_id = ?";
        $stmtUpdateStudent = $conn->prepare($sqlUpdateStudent);
        if (!$stmtUpdateStudent) throw new Exception("Failed to prepare student update statement: " . $conn->error);
        $stmtUpdateStudent->bind_param("iiii", $toClassId, $toSectionId, $transferAcademicYearId, $studentUserId);
        if (!$stmtUpdateStudent->execute()) {
            throw new Exception("Failed to update student's class/section for transfer: " . $stmtUpdateStudent->error);
        }
        $stmtUpdateStudent->close();

        // 3. Add a record to `student_transfers` table
        $sqlInsertTransfer = "INSERT INTO student_transfers (student_user_id, from_class_id, from_section_id, to_class_id, to_section_id, academic_year_id, transfer_date, reason, notes, created_at, updated_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmtInsertTransfer = $conn->prepare($sqlInsertTransfer);
        if (!$stmtInsertTransfer) throw new Exception("Failed to prepare transfer record statement: " . $conn->error);
        $stmtInsertTransfer->bind_param("iiiiissss", $studentUserId, $currentClassId, $currentSectionId, $toClassId, $toSectionId, $transferAcademicYearId, $transferDate, $reason, $notes);
        if (!$stmtInsertTransfer->execute()) {
            throw new Exception("Failed to create student transfer record: " . $stmtInsertTransfer->error);
        }
        $transferId = $stmtInsertTransfer->insert_id;
        $stmtInsertTransfer->close();

        // 4. Optionally, manage enrollments: Deactivate old, activate new (similar to assignStudentToClassSection)
        // Deactivate any active enrollment for the student in the *transferAcademicYearId* before creating a new one.
        $sqlUpdateEnrollmentStatus = "UPDATE enrollments SET status = 'transferred', updated_at = NOW() WHERE student_id = ? AND academic_year_id = ? AND status = 'active'";
        $stmtUpdateOldEnrollment = $conn->prepare($sqlUpdateEnrollmentStatus);
        if (!$stmtUpdateOldEnrollment) throw new Exception("Failed to prepare old enrollment update: " . $conn->error);
        $stmtUpdateOldEnrollment->bind_param("ii", $studentUserId, $transferAcademicYearId);
        $stmtUpdateOldEnrollment->execute(); // Execute even if no rows are affected (student might not have an active enrollment for that AY)
        $stmtUpdateOldEnrollment->close();

        // Add new enrollment record for the new class/section
        $newEnrollmentStatus = 'active';
        $sqlInsertNewEnrollment = "INSERT INTO enrollments (student_id, class_id, section_id, academic_year_id, enrollment_date, status, created_at, updated_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmtInsertNewEnrollment = $conn->prepare($sqlInsertNewEnrollment);
        if (!$stmtInsertNewEnrollment) throw new Exception("Failed to prepare new enrollment statement: " . $conn->error);
        $stmtInsertNewEnrollment->bind_param("iiiiss", $studentUserId, $toClassId, $toSectionId, $transferAcademicYearId, $transferDate, $newEnrollmentStatus);
        if (!$stmtInsertNewEnrollment->execute()) {
            throw new Exception("Failed to create new enrollment record post-transfer: " . $stmtInsertNewEnrollment->error);
        }
        $stmtInsertNewEnrollment->close();

        $conn->commit();
        return ['success' => true, 'message' => 'Student transferred successfully.', 'transfer_id' => $transferId];

    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getTransferHistory($conn, $studentUserId) {
    $sql = "SELECT 
                st.id AS transfer_id, st.transfer_date, st.reason, st.notes AS transfer_notes,
                s.full_name AS student_name, s.admission_number,
                fc.name AS from_class_name, fs.name AS from_section_name,
                tc.name AS to_class_name, ts.name AS to_section_name,
                ay.year_name AS academic_year_name,
                st.created_at AS transfer_logged_at
            FROM student_transfers st
            JOIN students s ON st.student_user_id = s.user_id
            LEFT JOIN classes fc ON st.from_class_id = fc.id
            LEFT JOIN sections fs ON st.from_section_id = fs.id
            JOIN classes tc ON st.to_class_id = tc.id
            JOIN sections ts ON st.to_section_id = ts.id
            JOIN academic_years ay ON st.academic_year_id = ay.id
            WHERE st.student_user_id = ?
            ORDER BY st.transfer_date DESC, st.created_at DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error, 'query' => $sql];
    }

    $stmt->bind_param('i', $studentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    $stmt->close();

    return ['success' => true, 'data' => $history];
}

function linkParentToStudent($conn, $studentUserId, $parentUserId, $relationshipType) {
    $conn->begin_transaction();
    try {
        // Validate student exists (user_id from students table)
        $stmtCheckStudent = $conn->prepare("SELECT user_id FROM students WHERE user_id = ?");
        if (!$stmtCheckStudent) throw new Exception("Failed to prepare student check: " . $conn->error);
        $stmtCheckStudent->bind_param("i", $studentUserId);
        $stmtCheckStudent->execute();
        if ($stmtCheckStudent->get_result()->num_rows === 0) {
            throw new Exception("Student with User ID {$studentUserId} not found.");
        }
        $stmtCheckStudent->close();

        // Validate parent exists (id from users table, assuming parents are also users with role 'parent')
        $stmtCheckParent = $conn->prepare("SELECT id, role FROM users WHERE id = ?");
        if (!$stmtCheckParent) throw new Exception("Failed to prepare parent check: " . $conn->error);
        $stmtCheckParent->bind_param("i", $parentUserId);
        $stmtCheckParent->execute();
        $parentUser = $stmtCheckParent->get_result()->fetch_assoc();
        if (!$parentUser) {
            throw new Exception("Parent with User ID {$parentUserId} not found.");
        }
        // Optionally, enforce parent role if you have one defined, e.g., if ($parentUser['role'] !== 'parent') { ... }
        $stmtCheckParent->close();

        // Check if the link already exists
        $stmtCheckLink = $conn->prepare("SELECT id FROM parent_accounts WHERE student_user_id = ? AND parent_user_id = ?");
        if (!$stmtCheckLink) throw new Exception("Failed to prepare link check: " . $conn->error);
        $stmtCheckLink->bind_param("ii", $studentUserId, $parentUserId);
        $stmtCheckLink->execute();
        if ($stmtCheckLink->get_result()->num_rows > 0) {
            throw new Exception("This parent is already linked to this student.");
        }
        $stmtCheckLink->close();

        $sql = "INSERT INTO parent_accounts (student_user_id, parent_user_id, relationship_type, created_at, updated_at) 
                VALUES (?, ?, ?, NOW(), NOW())";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare parent link statement: " . $conn->error);
        }
        $stmt->bind_param("iis", $studentUserId, $parentUserId, $relationshipType);
        if (!$stmt->execute()) {
            throw new Exception("Failed to link parent to student: " . $stmt->error);
        }
        $linkId = $stmt->insert_id;
        $stmt->close();

        $conn->commit();
        return ['success' => true, 'message' => 'Parent successfully linked to student.', 'link_id' => $linkId];

    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getStudentParents($conn, $studentUserId) {
    // Validate student exists (user_id from students table)
    $stmtCheckStudent = $conn->prepare("SELECT user_id FROM students WHERE user_id = ?");
    if (!$stmtCheckStudent) {
         return ['success' => false, 'message' => "Failed to prepare student check: " . $conn->error];
    }
    $stmtCheckStudent->bind_param("i", $studentUserId);
    $stmtCheckStudent->execute();
    if ($stmtCheckStudent->get_result()->num_rows === 0) {
        $stmtCheckStudent->close();
        return ['success' => false, 'message' => "Student with User ID {$studentUserId} not found."];
    }
    $stmtCheckStudent->close();

    $sql = "SELECT 
                pa.id AS link_id, pa.relationship_type,
                u.id AS parent_user_id, u.username AS parent_username, u.email AS parent_email, u.status AS parent_status,
                p.full_name AS parent_full_name, p.mobile AS parent_mobile, p.occupation AS parent_occupation, p.photo AS parent_photo
            FROM parent_accounts pa
            JOIN users u ON pa.parent_user_id = u.id
            LEFT JOIN parents p ON u.id = p.user_id -- Assuming a 'parents' table similar to 'students' for parent-specific details
            WHERE pa.student_user_id = ?
            ORDER BY pa.relationship_type ASC"; 
            // If you don't have a separate `parents` table for details like full_name, mobile, etc., 
            // you might need to adjust the query or rely on information directly in the `users` table or student's own record for parent names.

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error, 'query' => $sql];
    }

    $stmt->bind_param('i', $studentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $parents = [];
    while ($row = $result->fetch_assoc()) {
        $parents[] = $row;
    }
    $stmt->close();

    return ['success' => true, 'data' => $parents];
}

function getStudentEnrollmentHistory($conn, $studentUserId) {
    $sql = "SELECT 
                e.id AS enrollment_id, e.enrollment_date, e.status AS enrollment_status, e.notes AS enrollment_notes,
                c.id AS class_id, c.name AS class_name,
                s.id AS section_id, s.name AS section_name,
                ay.id AS academic_year_id, ay.year_name AS academic_year_name, ay.start_date AS academic_year_start, ay.end_date AS academic_year_end,
                e.created_at AS enrollment_created_at, e.updated_at AS enrollment_updated_at
            FROM enrollments e
            JOIN classes c ON e.class_id = c.id
            JOIN sections s ON e.section_id = s.id
            JOIN academic_years ay ON e.academic_year_id = ay.id
            WHERE e.student_id = ?
            ORDER BY ay.start_date DESC, e.enrollment_date DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return ['success' => false, 'message' => 'Failed to prepare statement: ' . $conn->error, 'query' => $sql];
    }

    $stmt->bind_param('i', $studentUserId);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    $stmt->close();

    return ['success' => true, 'data' => $history];
}

function assignStudentToClassSection($conn, $studentUserId, $classId, $sectionId, $academicYearId, $enrollmentDate) {
    $conn->begin_transaction();
    try {
        // Validate student exists
        $stmtCheckStudent = $conn->prepare("SELECT user_id FROM students WHERE user_id = ?");
        if (!$stmtCheckStudent) throw new Exception("Failed to prepare student check: " . $conn->error);
        $stmtCheckStudent->bind_param("i", $studentUserId);
        $stmtCheckStudent->execute();
        if ($stmtCheckStudent->get_result()->num_rows === 0) {
            throw new Exception("Student with User ID {$studentUserId} not found.");
        }
        $stmtCheckStudent->close();

        // Validate class, section, academic year exist (optional, but good practice)
        // For brevity, skipping detailed existence checks for class, section, academic_year here
        // but they should be implemented in a production system.

        // 1. Update the student's current class, section, and academic year in the `students` table
        $sqlUpdateStudent = "UPDATE students SET class_id = ?, section_id = ?, academic_year_id = ?, updated_at = NOW() WHERE user_id = ?";
        $stmtUpdateStudent = $conn->prepare($sqlUpdateStudent);
        if (!$stmtUpdateStudent) {
            throw new Exception("Failed to prepare student update statement: " . $conn->error);
        }
        $stmtUpdateStudent->bind_param("iiii", $classId, $sectionId, $academicYearId, $studentUserId);
        if (!$stmtUpdateStudent->execute()) {
            throw new Exception("Failed to update student's class and section: " . $stmtUpdateStudent->error);
        }
        $stmtUpdateStudent->close();

        // 2. Add a new record to the `enrollments` table
        // Consider deactivating previous enrollments for the same academic year if needed.
        // For now, we just add a new one. A status field in enrollments can manage this.
        $enrollmentStatus = 'active'; // Default new enrollments to active
        $sqlInsertEnrollment = "INSERT INTO enrollments (student_id, class_id, section_id, academic_year_id, enrollment_date, status, created_at, updated_at) 
                                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $stmtInsertEnrollment = $conn->prepare($sqlInsertEnrollment);
        if (!$stmtInsertEnrollment) {
            throw new Exception("Failed to prepare enrollment insert statement: " . $conn->error);
        }
        $stmtInsertEnrollment->bind_param("iiiiss", $studentUserId, $classId, $sectionId, $academicYearId, $enrollmentDate, $enrollmentStatus);
        if (!$stmtInsertEnrollment->execute()) {
            throw new Exception("Failed to create enrollment record: " . $stmtInsertEnrollment->error);
        }
        $newEnrollmentId = $stmtInsertEnrollment->insert_id;
        $stmtInsertEnrollment->close();

        $conn->commit();
        return ['success' => true, 'message' => 'Student successfully assigned to class and section.', 'enrollment_id' => $newEnrollmentId];

    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function updateStudentUserStatus($conn, $studentUserId, $status) {
    // Validate status value against a predefined list if necessary
    $allowedStatuses = ['active', 'inactive', 'suspended', 'graduated', 'transferred_out', 'left'];
    if (!in_array($status, $allowedStatuses)) {
        return ['success' => false, 'message' => 'Invalid status value provided.'];
    }

    $conn->begin_transaction();
    try {
        // Check if the user is indeed a student
        $stmtCheck = $conn->prepare("SELECT role FROM users WHERE id = ?");
        if (!$stmtCheck) {
            throw new Exception("Failed to prepare user check statement: " . $conn->error);
        }
        $stmtCheck->bind_param("i", $studentUserId);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $user = $resultCheck->fetch_assoc();
        $stmtCheck->close();

        if (!$user) {
            throw new Exception("User not found.");
        }
        if ($user['role'] !== 'student') {
            throw new Exception("Cannot update status: User is not a student.");
        }

        $sql = "UPDATE users SET status = ?, updated_at = NOW() WHERE id = ? AND role = 'student'";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare status update statement: " . $conn->error);
        }
        $stmt->bind_param('si', $status, $studentUserId);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $conn->commit();
                return ['success' => true, 'message' => 'Student status updated successfully.'];
            } else {
                // This could mean student not found with role student, or status was already the same.
                // For simplicity, we assume student exists due to prior check, so status might be unchanged.
                $conn->commit(); // Commit even if no rows affected if status was already set to the new value
                return ['success' => true, 'message' => 'Student status is already set to the provided value or student not found as a student.'];
            }
        } else {
            throw new Exception("Failed to update student status: " . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function generateAdmissionNumber($conn) {
    // Simple admission number generation: ADM-<YEAR>-<SEQUENTIAL_ID>
    // This is a basic example and might need to be more robust for production
    $year = date('Y');
    $prefix = 'ADM-' . $year . '-';

    // Find the last admission number for the current year to determine the next sequence
    $sql = "SELECT admission_number FROM students WHERE admission_number LIKE ? ORDER BY admission_number DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $likePattern = $prefix . '%';
    $stmt->bind_param('s', $likePattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $lastAdmissionNumber = $result->fetch_assoc();
    $stmt->close();

    $nextSequentialId = 1;
    if ($lastAdmissionNumber && isset($lastAdmissionNumber['admission_number'])) {
        $parts = explode('-', $lastAdmissionNumber['admission_number']);
        $lastSequentialId = end($parts);
        if (is_numeric($lastSequentialId)) {
            $nextSequentialId = (int)$lastSequentialId + 1;
        }
    }
    return $prefix . str_pad($nextSequentialId, 4, '0', STR_PAD_LEFT); // e.g., ADM-2023-0001
}

function addStudent($conn, $postData, $filesData) {
    $conn->begin_transaction();

    try {
        // --- Input Validation (Basic) ---
        $requiredFields = [
            'full_name', 'email', 'password', 'class_id', 'section_id', 'academic_year_id',
            'gender_code', 'dob', 'admission_date', 'mobile', 'father_name'
        ];
        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                throw new Exception("Field '{$field}' is required.");
            }
        }

        // --- User Creation ---
        $email = $postData['email'];
        $password = $postData['password'];
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $role = 'student';
        $status = safe_get($postData, 'status', 'active'); // Default to active
        $full_name = safe_get($postData, 'full_name');

        // Check if email already exists
        $stmtCheckEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCheckEmail->bind_param("s", $email);
        $stmtCheckEmail->execute();
        $resultCheckEmail = $stmtCheckEmail->get_result();
        if ($resultCheckEmail->num_rows > 0) {
            throw new Exception("Email already exists.");
        }
        $stmtCheckEmail->close();

        $sqlUser = "INSERT INTO users (email, password_hash, full_name, role, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
        $stmtUser = $conn->prepare($sqlUser);
        if (!$stmtUser) {
            throw new Exception("Failed to prepare user statement: " . $conn->error);
        }
        $stmtUser->bind_param('sssss', $email, $password_hash, $full_name, $role, $status);
        if (!$stmtUser->execute()) {
            throw new Exception("Failed to create user: " . $stmtUser->error);
        }
        $userId = $stmtUser->insert_id;
        $stmtUser->close();

        // --- Student Photo Handling ---
        $photoPath = null;
        if (isset($filesData['photo']) && $filesData['photo']['error'] == UPLOAD_ERR_OK) {
            if (!file_exists(STUDENT_PHOTO_UPLOAD_DIR)) {
                mkdir(STUDENT_PHOTO_UPLOAD_DIR, 0777, true);
            }
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($filesData['photo']['type'], $allowedTypes)) {
                throw new Exception("Invalid photo file type. Allowed: JPG, PNG, GIF.");
            }
            if ($filesData['photo']['size'] > 2097152) { // 2MB limit
                throw new Exception("Photo file size exceeds 2MB limit.");
            }

            $photoExtension = pathinfo($filesData['photo']['name'], PATHINFO_EXTENSION);
            $photoFilename = 'student_' . $userId . '_' . time() . '.' . $photoExtension;
            $photoPath = STUDENT_PHOTO_UPLOAD_DIR . $photoFilename;

            if (!move_uploaded_file($filesData['photo']['tmp_name'], $photoPath)) {
                throw new Exception("Failed to upload student photo.");
            }
            // Store relative path for database if your web server serves from a parent directory of uploads
            $photoPath = 'uploads/student_photos/' . $photoFilename; 
        }

        // --- Generate Admission Number ---
        $admissionNumber = generateAdmissionNumber($conn);        // --- Student Data Insertion ---
        $sqlStudent = "INSERT INTO students (
            user_id, admission_number, full_name, class_id, section_id, academic_year_id, roll_number, 
            gender_code, dob, blood_group_code, nationality, mobile, contact_email, 
            address, pincode, alt_mobile, father_name, father_aadhar_number,
            mother_name, mother_aadhar_number, mother_tongue,
            aadhar_card_number, medical_conditions, student_state_code,
            photo, admission_date, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmtStudent = $conn->prepare($sqlStudent);
        if (!$stmtStudent) {
            throw new Exception("Failed to prepare student statement: " . $conn->error);
        }

        // Bind parameters - prepare variables first since bind_param requires variables by reference
        $fullName = safe_get($postData, 'full_name');
        $classId = safe_get($postData, 'class_id');
        $sectionId = safe_get($postData, 'section_id');
        $academicYearId = safe_get($postData, 'academic_year_id');
        $rollNumber = safe_get($postData, 'roll_number');
        $genderCode = safe_get($postData, 'gender_code');
        $dob = safe_get($postData, 'dob');
        $bloodGroupCode = safe_get($postData, 'blood_group_code');
        $nationality = safe_get($postData, 'nationality');
        $mobile = safe_get($postData, 'mobile');
        $contactEmail = safe_get($postData, 'contact_email', $email);
        $address = safe_get($postData, 'address');
        $pincode = safe_get($postData, 'pincode');
        $altMobile = safe_get($postData, 'alt_mobile');
        $fatherName = safe_get($postData, 'father_name');
        $fatherAadhar = safe_get($postData, 'father_aadhar_number');
        $motherName = safe_get($postData, 'mother_name');
        $motherAadhar = safe_get($postData, 'mother_aadhar_number');
        $motherTongue = safe_get($postData, 'mother_tongue');
        $aadharNumber = safe_get($postData, 'aadhar_card_number');
        $medicalConditions = safe_get($postData, 'medical_conditions');
        $studentStateCode = safe_get($postData, 'student_state_code');
        $admissionDate = safe_get($postData, 'admission_date');

        // Debug: Count parameters
        $paramCount = 26;
        $typeString = 'issiiissssssssssssssssssss';
        debug_log("BIND PARAM DEBUG: Type string: '$typeString', Length: " . strlen($typeString) . ", Expected params: $paramCount");

        $stmtStudent->bind_param('issiiissssssssssssssssssss',
            $userId, $admissionNumber, $fullName, $classId, $sectionId, 
            $academicYearId, $rollNumber, $genderCode, 
            $dob, $bloodGroupCode, $nationality, $mobile, 
            $contactEmail, $address, $pincode, $altMobile,
            $fatherName, $fatherAadhar,
            $motherName, $motherAadhar, $motherTongue,
            $aadharNumber, $medicalConditions, $studentStateCode,
            $photoPath, $admissionDate
        );

        if (!$stmtStudent->execute()) {
            // If student insertion fails, attempt to delete the created user
            $stmtDeleteUser = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmtDeleteUser->bind_param("i", $userId);
            $stmtDeleteUser->execute();
            $stmtDeleteUser->close();
            throw new Exception("Failed to create student details: " . $stmtStudent->error . " User creation rolled back.");
        }
        $stmtStudent->close();

        $conn->commit();
        return ['success' => true, 'message' => 'Student added successfully.', 'user_id' => $userId, 'admission_number' => $admissionNumber];

    } catch (Exception $e) {
        $conn->rollback();
        // Clean up uploaded photo if transaction failed and photo was moved
        if (isset($photoPath) && file_exists(STUDENT_PHOTO_UPLOAD_DIR . basename($photoPath)) && strpos($photoPath, 'uploads/student_photos/') === 0) {
             unlink(STUDENT_PHOTO_UPLOAD_DIR . basename($photoPath));
        }
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function bulkImportStudents($conn, $fileData) {
    if (!isset($fileData['students_csv']) || $fileData['students_csv']['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Student CSV file not uploaded or upload error.'];
    }

    $fileName = $fileData['students_csv']['tmp_name'];
    $fileType = $fileData['students_csv']['type'];
    $allowedMimeTypes = ['text/csv', 'application/vnd.ms-excel', 'application/csv'];

    if (!in_array($fileType, $allowedMimeTypes)) {
         return ['success' => false, 'message' => 'Invalid file type. Please upload a CSV file. Detected type: ' . $fileType];
    }

    // Placeholder for actual implementation
    // TODO: Implement CSV parsing (e.g., using fgetcsv).
    // TODO: For each row:
    //          - Validate data.
    //          - Check for existing user/student if necessary.
    //          - Create user account (generate username, hash password).
    //          - Generate admission number.
    //          - Insert into `students` table.
    //          - Insert into `enrollments` table.
    //          - Handle transactions (per student or batch).
    //          - Collect results (successes, failures with reasons).
    
    // For now, returning a placeholder response
    return ['success' => false, 'message' => 'Bulk import students functionality is not yet implemented.', 'file_details' => ['name' => $fileData['students_csv']['name'], 'type' => $fileType]];
}

function bulkPromoteStudents($conn, $postData) {
    $fromAcademicYearId = safe_get($postData, 'from_academic_year_id');
    $toAcademicYearId = safe_get($postData, 'to_academic_year_id');
    $promotionsInput = safe_get($postData, 'promotions'); // Expecting JSON string or array of promotion objects
    $promotionDate = safe_get($postData, 'promotion_date', date('Y-m-d')); // Default to today

    $promotions = [];
    if (is_string($promotionsInput)) {
        $promotions = json_decode($promotionsInput, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Invalid JSON in promotions data: ' . json_last_error_msg()];
        }
    } elseif (is_array($promotionsInput)) {
        $promotions = $promotionsInput;
    }

    if (empty($toAcademicYearId) || empty($promotions) || !is_array($promotions)) {
        return ['success' => false, 'message' => 'Invalid data for bulk promotion. Required: to_academic_year_id, and a non-empty promotions array.'];
    }

    $successCount = 0;
    $failureCount = 0;
    $errors = [];
    $processedCount = 0;

    foreach ($promotions as $index => $promo) {
        $processedCount++;
        $studentUserId = safe_get($promo, 'student_user_id');
        $fromClassId = safe_get($promo, 'from_class_id'); // Current class of student (optional, can be fetched)
        $fromSectionId = safe_get($promo, 'from_section_id'); // Current section of student (optional, can be fetched)
        $toClassId = safe_get($promo, 'to_class_id');
        $toSectionId = safe_get($promo, 'to_section_id');
        $rollNumber = safe_get($promo, 'new_roll_number'); // Optional: new roll number in the new class

        if (empty($studentUserId) || empty($toClassId) || empty($toSectionId)) {
            $failureCount++;
            $errors[] = "Promotion #{$index}: Missing student_user_id, to_class_id, or to_section_id.";
            continue;
        }

        $conn->begin_transaction();
        try {
            // 1. Get current student details (including current class/section if not provided)
            $stmtFetchStudent = $conn->prepare("SELECT class_id, section_id, academic_year_id FROM students WHERE user_id = ?");
            if (!$stmtFetchStudent) throw new Exception("Promotion #{$index} ({$studentUserId}): Failed to prepare student fetch statement: " . $conn->error);
            $stmtFetchStudent->bind_param("i", $studentUserId);
            $stmtFetchStudent->execute();
            $studentCurrentDetails = $stmtFetchStudent->get_result()->fetch_assoc();
            $stmtFetchStudent->close();

            if (!$studentCurrentDetails) {
                throw new Exception("Promotion #{$index}: Student with User ID {$studentUserId} not found.");
            }

            $actualFromClassId = !empty($fromClassId) ? $fromClassId : $studentCurrentDetails['class_id'];
            $actualFromSectionId = !empty($fromSectionId) ? $fromSectionId : $studentCurrentDetails['section_id'];
            $effectiveFromAcademicYearId = !empty($fromAcademicYearId) ? $fromAcademicYearId : $studentCurrentDetails['academic_year_id'];

            // 2. Update student's record in `students` table
            $updateStudentFieldsArr = ["class_id = ?", "section_id = ?", "academic_year_id = ?", "updated_at = NOW()"];
            $updateStudentParams = [$toClassId, $toSectionId, $toAcademicYearId];
            $updateStudentTypes = 'iii';

            if ($rollNumber !== null) {
                $updateStudentFieldsArr[] = "roll_number = ?";
                $updateStudentParams[] = $rollNumber;
                $updateStudentTypes .= 's';
            }
            $updateStudentParams[] = $studentUserId;
            $updateStudentTypes .= 'i';
            $updateStudentFieldsStr = implode(", ", $updateStudentFieldsArr);

            $sqlUpdateStudent = "UPDATE students SET {$updateStudentFieldsStr} WHERE user_id = ?";
            $stmtUpdateStudent = $conn->prepare($sqlUpdateStudent);
            if (!$stmtUpdateStudent) throw new Exception("Promotion #{$index} ({$studentUserId}): Failed to prepare student update: " . $conn->error);
            $stmtUpdateStudent->bind_param($updateStudentTypes, ...$updateStudentParams);
            if (!$stmtUpdateStudent->execute()) {
                throw new Exception("Promotion #{$index} ({$studentUserId}): Failed to update student record: " . $stmtUpdateStudent->error);
            }
            $stmtUpdateStudent->close();

            // 3. Update old enrollment status in `enrollments` table for the *fromAcademicYearId*
            $sqlUpdateOldEnrollment = "UPDATE enrollments SET status = 'promoted', updated_at = NOW() 
                                       WHERE student_id = ? AND academic_year_id = ? AND class_id = ? AND section_id = ? AND status = 'active'";
            $stmtUpdateOldEnrollment = $conn->prepare($sqlUpdateOldEnrollment);
            if (!$stmtUpdateOldEnrollment) throw new Exception("Promotion #{$index} ({$studentUserId}): Failed to prepare old enrollment update: " . $conn->error);
            $stmtUpdateOldEnrollment->bind_param("iiii", $studentUserId, $effectiveFromAcademicYearId, $actualFromClassId, $actualFromSectionId);
            $stmtUpdateOldEnrollment->execute(); 
            $stmtUpdateOldEnrollment->close();

            // 4. Create new enrollment record in `enrollments` table for the *toAcademicYearId*
            $newEnrollmentStatus = 'active';
            $sqlInsertNewEnrollment = "INSERT INTO enrollments (student_id, class_id, section_id, academic_year_id, enrollment_date, status, created_at, updated_at) 
                                       VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmtInsertNewEnrollment = $conn->prepare($sqlInsertNewEnrollment);
            if (!$stmtInsertNewEnrollment) throw new Exception("Promotion #{$index} ({$studentUserId}): Failed to prepare new enrollment: " . $conn->error);
            $stmtInsertNewEnrollment->bind_param("iiiiss", $studentUserId, $toClassId, $toSectionId, $toAcademicYearId, $promotionDate, $newEnrollmentStatus);
            if (!$stmtInsertNewEnrollment->execute()) {
                throw new Exception("Promotion #{$index} ({$studentUserId}): Failed to create new enrollment: " . $stmtInsertNewEnrollment->error);
            }
            $stmtInsertNewEnrollment->close();

            // 5. Log in `student_transfers` table
            $reason = 'Promotion';
            $notes = safe_get($promo, 'notes', 'Bulk promotion to new academic year.');
            $sqlInsertTransfer = "INSERT INTO student_transfers (student_user_id, from_class_id, from_section_id, to_class_id, to_section_id, academic_year_id, transfer_date, reason, notes, created_at, updated_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmtInsertTransfer = $conn->prepare($sqlInsertTransfer);
            if (!$stmtInsertTransfer) throw new Exception("Promotion #{$index} ({$studentUserId}): Failed to prepare transfer log: " . $conn->error);
            $stmtInsertTransfer->bind_param("iiiiissss", $studentUserId, $actualFromClassId, $actualFromSectionId, $toClassId, $toSectionId, $toAcademicYearId, $promotionDate, $reason, $notes);
            if (!$stmtInsertTransfer->execute()) {
                throw new Exception("Promotion #{$index} ({$studentUserId}): Failed to log promotion as transfer: " . $stmtInsertTransfer->error);
            }
            $stmtInsertTransfer->close();

            $conn->commit();
            $successCount++;
        } catch (Exception $e) {
            $conn->rollback();
            $failureCount++;
            $errors[] = $e->getMessage();
        }
    }

    return [
        'success' => true, 
        'message' => "Bulk promotion process completed. Processed {$processedCount} students.",
        'results' => [
            'successful_promotions' => $successCount,
            'failed_promotions' => $failureCount,
            'errors' => $errors
        ]
        ];
}

?>
