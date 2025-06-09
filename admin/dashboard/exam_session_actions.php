<?php
/**
 * Backend Actions for Normalized Exam Session Management
 * Handles CRUD operations for exam sessions, subjects, and marks
 */

// Start session before any output
session_start();

require_once '../../con.php';
require_once '../../includes/functions.php';
require_once '../../includes/grading_functions.php';

// Check if user has permission
if (!hasRole(['admin', 'headmaster', 'teacher'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

$action = $input['action'] ?? '';
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

try {
    switch ($action) {
        case 'create_session':
            createExamSession($conn, $input, $user_id, $user_role);
            break;
            
        case 'update_session':
            updateExamSession($conn, $input, $user_id, $user_role);
            break;
            
        case 'delete_session':
            deleteExamSession($conn, $input, $user_id, $user_role);
            break;
            
        case 'add_subject':
            addExamSubject($conn, $input, $user_id, $user_role);
            break;
            
        case 'update_subject':
            updateExamSubject($conn, $input, $user_id, $user_role);
            break;
            
        case 'delete_subject':
            deleteExamSubject($conn, $input, $user_id, $user_role);
            break;
            
        case 'record_marks':
            recordStudentMarks($conn, $input, $user_id, $user_role);
            break;
            
        case 'update_marks':
            updateStudentMarks($conn, $input, $user_id, $user_role);
            break;
            
        case 'get_session_data':
            getSessionData($conn, $input);
            break;
            
        case 'get_subject_data':
            getSubjectData($conn, $input);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Create a new exam session
 */
function createExamSession($conn, $data, $user_id, $user_role) {
    // Validate required fields
    $required_fields = ['sessionName', 'sessionType', 'academicYear', 'startDate', 'endDate'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
      // Validate session type access
    if ($data['sessionType'] === 'FA' && !hasRole(['admin', 'teacher', 'headmaster'])) {
        throw new Exception("Only admins, teachers and headmasters can create FA assessments");
    }
    
    // Validate dates
    $start_date = $data['startDate'];
    $end_date = $data['endDate'];
    if (strtotime($start_date) >= strtotime($end_date)) {
        throw new Exception("End date must be after start date");
    }
    
    // Insert exam session
    $sql = "INSERT INTO exam_sessions (
        session_name, session_type, academic_year, start_date, end_date, 
        description, status, created_by, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, 'active', ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    $description = $data['description'] ?? '';
    
    $stmt->bind_param('ssssssi', 
        $data['sessionName'],
        $data['sessionType'],
        $data['academicYear'],
        $start_date,
        $end_date,
        $description,
        $user_id
    );
    
    if ($stmt->execute()) {
        $session_id = $conn->insert_id;
        
        // Log the action        logAudit('exam_sessions', $session_id, 'INSERT');
        
        echo json_encode([
            'success' => true, 
            'message' => 'Exam session created successfully',
            'session_id' => $session_id
        ]);
    } else {
        throw new Exception("Failed to create exam session: " . $conn->error);
    }
}

/**
 * Update an existing exam session
 */
function updateExamSession($conn, $data, $user_id, $user_role) {
    $session_id = $data['sessionId'] ?? 0;
    if (!$session_id) {
        throw new Exception("Session ID is required");
    }
    
    // Check if session exists and user has permission
    $check_sql = "SELECT * FROM exam_sessions WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $session_id);
    $check_stmt->execute();
    $session = $check_stmt->get_result()->fetch_assoc();
    
    if (!$session) {
        throw new Exception("Exam session not found");
    }
    
    // Only allow updates if session is not completed or user is admin/headmaster
    if ($session['status'] === 'completed' && !hasRole(['admin', 'headmaster'])) {
        throw new Exception("Cannot modify completed exam sessions");
    }
    
    // Build update query dynamically
    $update_fields = [];
    $params = [];
    $types = '';
    
    $allowed_fields = ['sessionName' => 'session_name', 'description' => 'description', 
                      'startDate' => 'start_date', 'endDate' => 'end_date', 'status' => 'status'];
    
    foreach ($allowed_fields as $input_field => $db_field) {
        if (isset($data[$input_field])) {
            $update_fields[] = "{$db_field} = ?";
            $params[] = $data[$input_field];
            $types .= 's';
        }
    }
    
    if (empty($update_fields)) {
        throw new Exception("No fields to update");
    }
    
    $update_fields[] = "updated_at = NOW()";
    $sql = "UPDATE exam_sessions SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $params[] = $session_id;
    $types .= 'i';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {        logAudit('exam_sessions', $session_id, 'UPDATE');
        
        echo json_encode(['success' => true, 'message' => 'Exam session updated successfully']);
    } else {
        throw new Exception("Failed to update exam session: " . $conn->error);
    }
}

/**
 * Delete an exam session
 */
function deleteExamSession($conn, $data, $user_id, $user_role) {
    $session_id = $data['sessionId'] ?? 0;
    if (!$session_id) {
        throw new Exception("Session ID is required");
    }
    
    // Only admins and headmasters can delete sessions
    if (!hasRole(['admin', 'headmaster'])) {
        throw new Exception("Only admins and headmasters can delete exam sessions");
    }
    
    // Check if session has any data
    $check_sql = "SELECT COUNT(*) as subject_count FROM exam_subjects WHERE exam_session_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $session_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result()->fetch_assoc();
    
    if ($result['subject_count'] > 0) {
        throw new Exception("Cannot delete exam session with existing subjects. Please remove all subjects first.");
    }
    
    // Delete the session
    $sql = "DELETE FROM exam_sessions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $session_id);
    
    if ($stmt->execute()) {        logAudit('exam_sessions', $session_id, 'DELETE');
        
        echo json_encode(['success' => true, 'message' => 'Exam session deleted successfully']);
    } else {
        throw new Exception("Failed to delete exam session: " . $conn->error);
    }
}

/**
 * Add a subject to an exam session
 */
function addExamSubject($conn, $data, $user_id, $user_role) {
    $required_fields = ['sessionId', 'subjectId', 'assessmentId', 'examDate'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
    
    // Validate session exists
    $session_sql = "SELECT session_type FROM exam_sessions WHERE id = ?";
    $session_stmt = $conn->prepare($session_sql);
    $session_stmt->bind_param('i', $data['sessionId']);
    $session_stmt->execute();
    $session = $session_stmt->get_result()->fetch_assoc();
    
    if (!$session) {
        throw new Exception("Exam session not found");
    }
    
    // Validate subject and assessment exist
    $subject_sql = "SELECT name FROM subjects WHERE id = ?";
    $subject_stmt = $conn->prepare($subject_sql);
    $subject_stmt->bind_param('i', $data['subjectId']);
    $subject_stmt->execute();
    $subject = $subject_stmt->get_result()->fetch_assoc();
      if (!$subject) {
        throw new Exception("Subject not found");
    }
    
    $assessment_sql = "SELECT title FROM assessments WHERE id = ?";
    $assessment_stmt = $conn->prepare($assessment_sql);
    $assessment_stmt->bind_param('i', $data['assessmentId']);
    $assessment_stmt->execute();
    $assessment = $assessment_stmt->get_result()->fetch_assoc();
    
    if (!$assessment) {
        throw new Exception("Assessment not found");
    }
    
    // Check for duplicates
    $duplicate_sql = "SELECT id FROM exam_subjects WHERE exam_session_id = ? AND subject_id = ? AND assessment_id = ?";
    $duplicate_stmt = $conn->prepare($duplicate_sql);
    $duplicate_stmt->bind_param('iii', $data['sessionId'], $data['subjectId'], $data['assessmentId']);
    $duplicate_stmt->execute();
    
    if ($duplicate_stmt->get_result()->num_rows > 0) {
        throw new Exception("This subject-assessment combination already exists for this exam session");
    }
      // Insert exam subject
    $sql = "INSERT INTO exam_subjects (
        exam_session_id, subject_id, assessment_id, exam_date, 
        total_marks, status, created_by, created_at
    ) VALUES (?, ?, ?, ?, ?, 'active', ?, NOW())";
    
    $max_marks = $data['maxMarks'] ?? 100;
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiisii', 
        $data['sessionId'],
        $data['subjectId'],
        $data['assessmentId'],
        $data['examDate'],
        $max_marks,
        $user_id
    );
    
    if ($stmt->execute()) {
        $exam_subject_id = $conn->insert_id;
          logAudit('exam_subjects', $exam_subject_id, 'INSERT');
        
        echo json_encode([
            'success' => true, 
            'message' => 'Subject added to exam session successfully',
            'exam_subject_id' => $exam_subject_id
        ]);
    } else {
        throw new Exception("Failed to add subject to exam session: " . $conn->error);
    }
}

/**
 * Update an existing exam subject
 */
function updateExamSubject($conn, $data, $user_id, $user_role) {
    $subject_id = $data['subjectId'] ?? 0;
    if (!$subject_id) {
        throw new Exception("Subject ID is required");
    }
    
    // Check if exam subject exists
    $check_sql = "SELECT * FROM exam_subjects WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param('i', $subject_id);
    $check_stmt->execute();
    $subject = $check_stmt->get_result()->fetch_assoc();
    
    if (!$subject) {
        throw new Exception("Exam subject not found");
    }
    
    // Only allow updates if not completed or user is admin/headmaster
    if ($subject['status'] === 'completed' && !hasRole(['admin', 'headmaster'])) {
        throw new Exception("Cannot modify completed exam subjects");
    }
    
    // Build update query dynamically
    $update_fields = [];
    $params = [];    $types = '';
    
    $allowed_fields = ['examDate' => 'exam_date', 'maxMarks' => 'total_marks', 'status' => 'status'];
    
    foreach ($allowed_fields as $input_field => $db_field) {
        if (isset($data[$input_field])) {
            $update_fields[] = "{$db_field} = ?";
            $params[] = $data[$input_field];
            $types .= $input_field === 'maxMarks' ? 'i' : 's';
        }
    }
    
    if (empty($update_fields)) {
        throw new Exception("No fields to update");
    }
    
    $update_fields[] = "updated_at = NOW()";
    $sql = "UPDATE exam_subjects SET " . implode(', ', $update_fields) . " WHERE id = ?";
    $params[] = $subject_id;
    $types .= 'i';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {        logAudit('exam_subjects', $subject_id, 'UPDATE');
        
        echo json_encode(['success' => true, 'message' => 'Exam subject updated successfully']);
    } else {
        throw new Exception("Failed to update exam subject: " . $conn->error);
    }
}

/**
 * Delete an exam subject
 */
function deleteExamSubject($conn, $data, $user_id, $user_role) {
    $subject_id = $data['subjectId'] ?? 0;
    if (!$subject_id) {
        throw new Exception("Subject ID is required");
    }
    
    // Only admins and headmasters can delete subjects
    if (!hasRole(['admin', 'headmaster'])) {
        throw new Exception("Only admins and headmasters can delete exam subjects");
    }
    
    // Check if subject has any marks
    $marks_sql = "SELECT COUNT(*) as mark_count FROM student_exam_marks WHERE exam_subject_id = ?";
    $marks_stmt = $conn->prepare($marks_sql);
    $marks_stmt->bind_param('i', $subject_id);
    $marks_stmt->execute();
    $result = $marks_stmt->get_result()->fetch_assoc();
    
    if ($result['mark_count'] > 0) {
        throw new Exception("Cannot delete exam subject with existing student marks. Please remove all marks first.");
    }
    
    // Delete the subject
    $sql = "DELETE FROM exam_subjects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $subject_id);
    
    if ($stmt->execute()) {        logAudit('exam_subjects', $subject_id, 'DELETE');
        
        echo json_encode(['success' => true, 'message' => 'Exam subject deleted successfully']);
    } else {
        throw new Exception("Failed to delete exam subject: " . $conn->error);
    }
}

/**
 * Record student marks for an exam subject
 */
function recordStudentMarks($conn, $data, $user_id, $user_role) {
    $required_fields = ['examSubjectId', 'studentId', 'marksObtained'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
    
    // Validate exam subject exists
    $exam_subject_sql = "SELECT es.*, s.name as subject_name, sess.session_type 
                        FROM exam_subjects es 
                        JOIN subjects s ON es.subject_id = s.id
                        JOIN exam_sessions sess ON es.exam_session_id = sess.id
                        WHERE es.id = ?";
    $exam_subject_stmt = $conn->prepare($exam_subject_sql);
    $exam_subject_stmt->bind_param('i', $data['examSubjectId']);
    $exam_subject_stmt->execute();
    $exam_subject = $exam_subject_stmt->get_result()->fetch_assoc();
      if (!$exam_subject) {
        throw new Exception("Exam subject not found");
    }
    
    // Validate marks
    $marks_obtained = floatval($data['marksObtained']);
    if ($marks_obtained < 0 || $marks_obtained > $exam_subject['total_marks']) {
        throw new Exception("Marks must be between 0 and {$exam_subject['total_marks']}");
    }
    
    // Check if marks already exist
    $existing_sql = "SELECT id FROM student_exam_marks WHERE exam_subject_id = ? AND student_id = ?";
    $existing_stmt = $conn->prepare($existing_sql);
    $existing_stmt->bind_param('ii', $data['examSubjectId'], $data['studentId']);
    $existing_stmt->execute();
      if ($existing_stmt->get_result()->num_rows > 0) {
        throw new Exception("Marks already recorded for this student. Use update action instead.");
    }
      // Calculate percentage and grade
    $percentage = ($marks_obtained / $exam_subject['total_marks']) * 100;
    
    // Determine grade based on session type
    if ($exam_subject['session_type'] === 'SA') {
        // For SA, use percentage-based grading
        require_once '../../includes/grading_functions.php';
        $grade_code = calculateSAGrade($percentage);
        $grade_points = getGradePoints($grade_code);
    } else {
        // For FA, use marks-based grading (normalized to 25)
        require_once '../../includes/grading_functions.php';
        $normalized_marks = ($marks_obtained / $exam_subject['total_marks']) * 25;
        $grade_code = calculateFAGrade($normalized_marks);
        $grade_points = getGradePoints($grade_code);
    }
    
    // Insert marks
    $sql = "INSERT INTO student_exam_marks (
        exam_subject_id, student_id, marks_obtained, grade_code, grade_points, 
        remarks, marked_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $remarks = $data['remarks'] ?? '';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iidsdsi', 
        $data['examSubjectId'],
        $data['studentId'],
        $marks_obtained,
        $grade_code,
        $grade_points,
        $remarks,
        $user_id
    );    if ($stmt->execute()) {
        $mark_id = $conn->insert_id;
        
        logAudit('student_exam_marks', $mark_id, 'INSERT');
        
        echo json_encode([
            'success' => true, 
            'message' => 'Student marks recorded successfully',
            'mark_id' => $mark_id,
            'percentage' => round($percentage, 2),
            'grade_code' => $grade_code,
            'grade_points' => $grade_points
        ]);
    } else {
        throw new Exception("Failed to record marks: " . $conn->error);
    }
}

/**
 * Update existing student marks
 */
function updateStudentMarks($conn, $data, $user_id, $user_role) {
    $required_fields = ['markId', 'marksObtained'];
    foreach ($required_fields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
      // Get existing mark record
    $mark_sql = "
        SELECT sem.*, es.total_marks, sess.session_type
        FROM student_exam_marks sem
        JOIN exam_subjects es ON sem.exam_subject_id = es.id
        JOIN exam_sessions sess ON es.exam_session_id = sess.id
        WHERE sem.id = ?
    ";
    $mark_stmt = $conn->prepare($mark_sql);
    $mark_stmt->bind_param('i', $data['markId']);
    $mark_stmt->execute();
    $mark = $mark_stmt->get_result()->fetch_assoc();
    
    if (!$mark) {
        throw new Exception("Mark record not found");
    }
    
    // Validate marks
    $marks_obtained = floatval($data['marksObtained']);
    if ($marks_obtained < 0 || $marks_obtained > $mark['total_marks']) {
        throw new Exception("Marks must be between 0 and {$mark['total_marks']}");
    }
      // Calculate percentage and grade
    $percentage = ($marks_obtained / $mark['total_marks']) * 100;
    
    if ($mark['session_type'] === 'SA') {
        // For SA, use percentage-based grading
        require_once '../../includes/grading_functions.php';
        $grade_code = calculateSAGrade($percentage);
        $grade_points = getGradePoints($grade_code);
    } else {
        // For FA, use marks-based grading (normalized to 25)
        require_once '../../includes/grading_functions.php';
        $normalized_marks = ($marks_obtained / $mark['total_marks']) * 25;
        $grade_code = calculateFAGrade($normalized_marks);
        $grade_points = getGradePoints($grade_code);
    }
    
    // Update marks
    $sql = "UPDATE student_exam_marks SET 
            marks_obtained = ?, grade_code = ?, grade_points = ?, remarks = ?
            WHERE id = ?";
    
    $remarks = $data['remarks'] ?? $mark['remarks'];
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('dsdsi', 
        $marks_obtained,
        $grade_code,
        $grade_points,
        $remarks,
        $data['markId']
    );    if ($stmt->execute()) {
        logAudit('student_exam_marks', $data['markId'], 'UPDATE');
        
        echo json_encode([
            'success' => true, 
            'message' => 'Student marks updated successfully',
            'percentage' => round($percentage, 2),
            'grade_code' => $grade_code,
            'grade_points' => $grade_points
        ]);
    } else {
        throw new Exception("Failed to update marks: " . $conn->error);
    }
}

/**
 * Get session data for editing
 */
function getSessionData($conn, $data) {
    $session_id = $data['sessionId'] ?? 0;
    if (!$session_id) {
        throw new Exception("Session ID is required");
    }
    
    $sql = "SELECT * FROM exam_sessions WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $session_id);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    
    if (!$session) {
        throw new Exception("Exam session not found");
    }
      echo json_encode(['success' => true, 'data' => $session]);
}
?>
