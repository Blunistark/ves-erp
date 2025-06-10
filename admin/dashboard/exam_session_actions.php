<?php
/**
 * Backend Actions for Normalized Exam Session Management
 * Handles CRUD operations for exam sessions, subjects, and marks
 */

// Start session before any output
session_start();

require_once 'con.php';
require_once '../../includes/functions.php';
require_once '../../includes/grading_functions.php';

// Check if user has permission
if (!hasRole(['admin', 'headmaster', 'teacher'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get JSON input or query parameters
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    // If no JSON input, try to get from GET/POST parameters
    $input = array_merge($_GET, $_POST);
}

if (empty($input) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No action specified']);
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
            
        case 'get_session_counts':
            getSessionCounts($conn, $input);
            break;
            
        case 'get_classes_in_session':
            getClassesInSession($conn, $input);
            break;
            
        case 'get_subjects_in_class':
            getSubjectsInClass($conn, $input);
            break;
            
        case 'get_all_subjects':
            getAllSubjects($conn);
            break;
            
        case 'get_assessments':
            getAssessments($conn, $input);
            break;
            
        case 'get_sessions':
            getAllSessions($conn);
            break;
            
        case 'get_sections':
            getSections($conn, $input);
            break;
            
        case 'get_classes':
            getClasses($conn);
            break;
            
        case 'get_academic_years':
            getAcademicYears($conn);
            break;
            
        case 'get_teacher_exam_sessions':
            getTeacherExamSessions($conn);
            break;
            
        case 'get_student_list_for_exam':
            getStudentListForExam($conn, $input);
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
    
    // Validate classes data
    if (empty($data['classes_data'])) {
        throw new Exception("At least one class must be selected");
    }
    
    $classes_data = json_decode($data['classes_data'], true);
    if (!$classes_data || !is_array($classes_data)) {
        throw new Exception("Invalid classes data format");
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
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert exam session (without class_id for now, keeping for backward compatibility)
        $sql = "INSERT INTO exam_sessions (
            session_name, session_type, academic_year, start_date, end_date, 
            description, status, class_id, created_by, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        $description = $data['description'] ?? '';
        $status = $data['status'] ?? 'active';
        $first_class_id = $classes_data[0]['class_id']; // Keep first class for backward compatibility
        
        $stmt->bind_param('sssssssii', 
            $data['sessionName'],
            $data['sessionType'],
            $data['academicYear'],
            $start_date,
            $end_date,
            $description,
            $status,
            $first_class_id,
            $user_id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to create exam session: " . $conn->error);
        }
        
        $session_id = $conn->insert_id;
        
        // Insert class-session relationships
        $class_sql = "INSERT INTO exam_session_classes (exam_session_id, class_id, section_id) VALUES (?, ?, ?)";
        $class_stmt = $conn->prepare($class_sql);
        
        foreach ($classes_data as $class_data) {
            $class_id = $class_data['class_id'];
            $section_id = isset($class_data['section_id']) && $class_data['section_id'] !== null && $class_data['section_id'] !== 'null' ? (int)$class_data['section_id'] : null;
            
            if ($section_id === null) {
                $class_stmt = $conn->prepare("INSERT INTO exam_session_classes (exam_session_id, class_id, section_id) VALUES (?, ?, NULL)");
                $class_stmt->bind_param('ii', $session_id, $class_id);
            } else {
                $class_stmt = $conn->prepare("INSERT INTO exam_session_classes (exam_session_id, class_id, section_id) VALUES (?, ?, ?)");
                $class_stmt->bind_param('iii', $session_id, $class_id, $section_id);
            }
            
            if (!$class_stmt->execute()) {
                throw new Exception("Failed to assign class to session: " . $conn->error);
            }
        }
        
        // Commit transaction
        $conn->commit();
        
        // Log the action
        logAudit('exam_sessions', $session_id, 'INSERT');
        
        echo json_encode([
            'success' => true, 
            'message' => 'Exam session created successfully with selected classes',
            'session_id' => $session_id
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Update an existing exam session
 */
function updateExamSession($conn, $data, $user_id, $user_role) {
    $session_id = $data['session_id'] ?? $data['sessionId'] ?? 0;
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
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Build update query dynamically
        $update_fields = [];
        $params = [];
        $types = '';
        
        $allowed_fields = [
            'sessionName' => 'session_name', 
            'sessionType' => 'session_type',
            'academicYear' => 'academic_year',
            'description' => 'description', 
            'startDate' => 'start_date', 
            'endDate' => 'end_date', 
            'status' => 'status'
        ];
        
        foreach ($allowed_fields as $input_field => $db_field) {
            if (isset($data[$input_field])) {
                $update_fields[] = "{$db_field} = ?";
                $params[] = $data[$input_field];
                $types .= 's';
            }
        }
        
        if (!empty($update_fields)) {
            $update_fields[] = "updated_at = NOW()";
            $sql = "UPDATE exam_sessions SET " . implode(', ', $update_fields) . " WHERE id = ?";
            $params[] = $session_id;
            $types .= 'i';
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
        }
        
        // Update classes if provided
        if (isset($data['classes_data'])) {
            $classes_data = json_decode($data['classes_data'], true);
            if ($classes_data && is_array($classes_data)) {
                // Delete existing class assignments
                $delete_sql = "DELETE FROM exam_session_classes WHERE exam_session_id = ?";
                $delete_stmt = $conn->prepare($delete_sql);
                $delete_stmt->bind_param('i', $session_id);
                $delete_stmt->execute();
                
                // Insert new class assignments
                $insert_sql = "INSERT INTO exam_session_classes (exam_session_id, class_id, section_id) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                
                foreach ($classes_data as $class_data) {
                    $class_id = $class_data['class_id'];
                    $section_id = $class_data['section_id'] ?: null;
                    
                    $insert_stmt->bind_param('iii', $session_id, $class_id, $section_id);
                    $insert_stmt->execute();
                }
            }
        }
        
        $conn->commit();
        logAudit('exam_sessions', $session_id, 'UPDATE');
        
        echo json_encode(['success' => true, 'message' => 'Exam session updated successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Delete an exam session
 */
function deleteExamSession($conn, $data, $user_id, $user_role) {
    $session_id = $data['session_id'] ?? $data['sessionId'] ?? 0;
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
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete from junction table first
        $junction_sql = "DELETE FROM exam_session_classes WHERE exam_session_id = ?";
        $junction_stmt = $conn->prepare($junction_sql);
        $junction_stmt->bind_param('i', $session_id);
        $junction_stmt->execute();
        
        // Delete the session
        $sql = "DELETE FROM exam_sessions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $session_id);
        
        if ($stmt->execute()) {
            $conn->commit();
            logAudit('exam_sessions', $session_id, 'DELETE');
            
            echo json_encode(['success' => true, 'message' => 'Exam session deleted successfully']);
        } else {
            throw new Exception("Failed to delete exam session: " . $conn->error);
        }
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
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
        total_marks, exam_time, duration_minutes, passing_marks
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $max_marks = $data['maxMarks'] ?? 100;
    $exam_time = $data['examTime'] ?? null;
    $duration = $data['durationMinutes'] ?? 60;
    $passing_marks = $data['passingMarks'] ?? ($max_marks * 0.4); // 40% as default passing marks
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiisisii', 
        $data['sessionId'],
        $data['subjectId'],
        $data['assessmentId'],
        $data['examDate'],
        $max_marks,
        $exam_time,
        $duration,
        $passing_marks
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
    
    // Note: No status check needed as exam_subjects table doesn't have status column
    // All exam subjects can be updated by authorized users
    
    // Build update query dynamically
    $update_fields = [];
    $params = [];
    $types = '';
    
    $allowed_fields = [
        'examDate' => 'exam_date', 
        'maxMarks' => 'total_marks', 
        'examTime' => 'exam_time',
        'durationMinutes' => 'duration_minutes',
        'passingMarks' => 'passing_marks',
        'instructions' => 'instructions'
    ];
    
    foreach ($allowed_fields as $input_field => $db_field) {
        if (isset($data[$input_field])) {
            $update_fields[] = "{$db_field} = ?";
            $params[] = $data[$input_field];
            if ($input_field === 'maxMarks' || $input_field === 'durationMinutes' || $input_field === 'passingMarks') {
                $types .= 'i';
            } else {
                $types .= 's';
            }
        }
    }
    
    if (empty($update_fields)) {
        throw new Exception("No fields to update");
    }
    
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
    $session_id = $data['session_id'] ?? $data['sessionId'] ?? 0;
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
    
    echo json_encode(['success' => true, 'session' => $session]);
}

/**
 * Get subject data for editing
 */
function getSubjectData($conn, $data) {
    $subject_id = $data['subjectId'] ?? 0;
    if (!$subject_id) {
        throw new Exception("Subject ID is required");
    }
    
    $sql = "SELECT es.*, s.name as subject_name, a.title as assessment_name 
            FROM exam_subjects es
            JOIN subjects s ON es.subject_id = s.id
            JOIN assessments a ON es.assessment_id = a.id
            WHERE es.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $subject_id);
    $stmt->execute();
    $subject = $stmt->get_result()->fetch_assoc();
    
    if (!$subject) {
        throw new Exception("Exam subject not found");
    }
    
    echo json_encode(['success' => true, 'data' => $subject]);
}

/**
 * Get session counts (classes and subjects)
 */
function getSessionCounts($conn, $data) {
    $session_id = $data['session_id'] ?? 0;
    if (!$session_id) {
        throw new Exception("Session ID is required");
    }
    
    // Get distinct classes in this session using the new junction table
    $classes_sql = "SELECT COUNT(DISTINCT esc.class_id) as classes_count
                    FROM exam_session_classes esc
                    WHERE esc.exam_session_id = ?";
    
    $stmt = $conn->prepare($classes_sql);
    $stmt->bind_param('i', $session_id);
    $stmt->execute();
    $classes_result = $stmt->get_result()->fetch_assoc();
    
    // Get total subjects count
    $subjects_sql = "SELECT COUNT(*) as subjects_count
                     FROM exam_subjects 
                     WHERE exam_session_id = ?";
    
    $stmt = $conn->prepare($subjects_sql);
    $stmt->bind_param('i', $session_id);
    $stmt->execute();
    $subjects_result = $stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'classes_count' => $classes_result['classes_count'] ?? 0,
        'subjects_count' => $subjects_result['subjects_count'] ?? 0
    ]);
}

/**
 * Get sections for a specific class
 */
function getSections($conn, $data) {
    $class_id = $data['class_id'] ?? 0;
    if (!$class_id) {
        throw new Exception("Class ID is required");
    }
    
    $sql = "SELECT id, name FROM sections WHERE class_id = ? ORDER BY name";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
    
    echo json_encode(['success' => true, 'sections' => $sections]);
}

/**
 * Get classes in a specific session
 */
function getClassesInSession($conn, $data) {
    $session_id = $data['session_id'] ?? 0;
    if (!$session_id) {
        throw new Exception("Session ID is required");
    }
    
    $sql = "SELECT DISTINCT 
                c.id as class_id,
                c.name as class_name,
                esc.section_id,
                s.name as section_name,
                COUNT(DISTINCT esub.id) as subjects_count,
                COUNT(DISTINCT st.user_id) as students_count
            FROM exam_session_classes esc
            JOIN classes c ON esc.class_id = c.id
            LEFT JOIN sections s ON esc.section_id = s.id
            LEFT JOIN exam_subjects esub ON esc.exam_session_id = esub.exam_session_id 
                AND EXISTS (
                    SELECT 1 FROM assessments a 
                    WHERE a.id = esub.assessment_id 
                    AND a.class_id = c.id 
                    AND (a.section_id = esc.section_id OR (a.section_id IS NULL AND esc.section_id IS NULL))
                )
            LEFT JOIN students st ON c.id = st.class_id AND (esc.section_id IS NULL OR esc.section_id = st.section_id)
            WHERE esc.exam_session_id = ?
            GROUP BY c.id, c.name, esc.section_id, s.name
            ORDER BY c.name, s.name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }

    echo json_encode(['success' => true, 'classes' => $classes]);
}

/**
 * Get subjects in a specific class for a session
 */
function getSubjectsInClass($conn, $data) {
    $session_id = $data['session_id'] ?? 0;
    $class_id = $data['class_id'] ?? 0;
    
    if (!$session_id || !$class_id) {
        throw new Exception("Session ID and Class ID are required");
    }
    
    $sql = "SELECT 
                esub.*,
                s.name as subject_name,
                s.code as subject_code,
                a.title as assessment_name,
                a.class_id,
                a.section_id,
                COUNT(DISTINCT er.id) as marks_count,
                ROUND(AVG(er.marks_obtained), 2) as avg_marks
            FROM exam_subjects esub
            JOIN subjects s ON esub.subject_id = s.id
            JOIN assessments a ON esub.assessment_id = a.id
            LEFT JOIN exam_results er ON a.id = er.assessment_id
            WHERE esub.exam_session_id = ? AND a.class_id = ?
            GROUP BY esub.id, s.name, s.code, a.title, a.class_id, a.section_id
            ORDER BY esub.exam_date, s.name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $session_id, $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    
    echo json_encode(['success' => true, 'subjects' => $subjects]);
}

/**
 * Get all available subjects
 */
function getAllSubjects($conn) {
    $sql = "SELECT id, name, code FROM subjects ORDER BY name";
    $result = $conn->query($sql);
    
    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $subjects]);
}

/**
 * Get assessments by session type
 */
function getAssessments($conn, $data) {
    $session_type = $data['session_type'] ?? '';
    
    if ($session_type) {
        $sql = "SELECT id, title, assessment_type, total_marks, date, class_id, section_id
                FROM assessments 
                WHERE assessment_type = ? 
                ORDER BY date DESC, title";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $session_type);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $sql = "SELECT id, title, assessment_type, total_marks, date, class_id, section_id
                FROM assessments 
                ORDER BY date DESC, title";
        $result = $conn->query($sql);
    }
    
    $assessments = [];
    while ($row = $result->fetch_assoc()) {
        $assessments[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $assessments]);
}

/**
 * Get all exam sessions
 */
function getAllSessions($conn) {
    $sql = "SELECT 
                es.*,
                GROUP_CONCAT(DISTINCT c.name ORDER BY c.name) as class_names,
                COUNT(DISTINCT esc.class_id) as class_count,
                COUNT(DISTINCT esub.id) as subject_count,
                COUNT(DISTINCT er.id) as marks_entered,
                ROUND(AVG(er.marks_obtained), 2) as avg_marks
            FROM exam_sessions es
            LEFT JOIN exam_session_classes esc ON es.id = esc.exam_session_id
            LEFT JOIN classes c ON esc.class_id = c.id
            LEFT JOIN exam_subjects esub ON es.id = esub.exam_session_id
            LEFT JOIN assessments a ON esub.assessment_id = a.id
            LEFT JOIN exam_results er ON a.id = er.assessment_id
            GROUP BY es.id
            ORDER BY es.created_at DESC";
    
    $result = $conn->query($sql);
    $sessions = [];
    
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
    
    echo json_encode(['success' => true, 'sessions' => $sessions]);
}

/**
 * Get all classes
 */
function getClasses($conn) {
    $sql = "SELECT id, name FROM classes ORDER BY name";
    $result = $conn->query($sql);
    
    $classes = [];
    while ($row = $result->fetch_assoc()) {
        $classes[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $classes]);
}

/**
 * Get academic years
 */
function getAcademicYears($conn) {
    $sql = "SELECT id, name, is_current FROM academic_years ORDER BY name DESC";
    $result = $conn->query($sql);
    
    $years = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $years[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'is_current' => $row['is_current']
            ];
        }
    }
    
    // If no academic years in table, provide default
    if (empty($years)) {
        $years = [
            ['id' => '2024-25', 'name' => '2024-25', 'is_current' => true],
            ['id' => '2025-26', 'name' => '2025-26', 'is_current' => false]
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $years]);
}

/**
 * Get exam sessions for teachers dashboard
 * Provides exam data in format expected by teachers dashboard
 */
function getTeacherExamSessions($conn) {
    // Get current user role and ID for access control
    $user_role = $_SESSION['role'] ?? '';
    $user_id = $_SESSION['user_id'] ?? 0;
    
    // Build query to get exam subjects with session and class details
    $sql = "SELECT 
                esub.id,
                sess.session_name,
                sess.session_type,
                sess.start_date,
                sess.end_date,
                sess.academic_year,
                sess.status,
                esub.exam_date,
                esub.exam_time,
                esub.total_marks,
                esub.duration_minutes,
                esub.instructions,
                s.name as subject_name,
                s.code as subject_code,
                a.title as assessment_name,
                c.name as class_name,
                sec.name as section_name,
                u.full_name as teacher_name
            FROM exam_sessions sess
            LEFT JOIN exam_subjects esub ON sess.id = esub.exam_session_id
            LEFT JOIN subjects s ON esub.subject_id = s.id
            LEFT JOIN assessments a ON esub.assessment_id = a.id
            LEFT JOIN exam_session_classes esc ON sess.id = esc.exam_session_id
            LEFT JOIN classes c ON esc.class_id = c.id
            LEFT JOIN sections sec ON esc.section_id = sec.id
            LEFT JOIN users u ON a.teacher_user_id = u.id
            WHERE sess.status IN ('active', 'draft')";
    
    // If user is a teacher, only show exams they're responsible for
    if ($user_role === 'teacher') {
        $sql .= " AND a.teacher_user_id = ?";
        $stmt = $conn->prepare($sql . " ORDER BY esub.exam_date ASC, sess.start_date ASC");
        $stmt->bind_param('i', $user_id);
    } else {
        // For admin/headmaster, show all active exam sessions
        $stmt = $conn->prepare($sql . " ORDER BY esub.exam_date ASC, sess.start_date ASC");
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $exams = [];
    while ($row = $result->fetch_assoc()) {
        // Skip rows without subject data (sessions without subjects added yet)
        if (!$row['subject_name']) {
            continue;
        }
        
        $exams[] = [
            'id' => $row['id'],
            'session_name' => $row['session_name'],
            'session_type' => $row['session_type'],
            'exam_date' => $row['exam_date'],
            'exam_time' => $row['exam_time'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date'],
            'total_marks' => $row['total_marks'],
            'duration_minutes' => $row['duration_minutes'],
            'instructions' => $row['instructions'],
            'subject_name' => $row['subject_name'],
            'subject_code' => $row['subject_code'],
            'assessment_name' => $row['assessment_name'],
            'class_name' => $row['class_name'],
            'section_name' => $row['section_name'],
            'teacher_name' => $row['teacher_name'],
            'status' => $row['status'],
            'academic_year' => $row['academic_year']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $exams]);
}

/**
 * Get student list for a specific exam subject
 * Provides student data with marks if available
 */
function getStudentListForExam($conn, $data) {
    $exam_subject_id = $data['exam_subject_id'] ?? 0;
    
    if (!$exam_subject_id) {
        echo json_encode(['success' => false, 'message' => 'Exam subject ID is required']);
        return;
    }
    
    // Get exam subject details to find the session and classes
    $exam_subject_sql = "
        SELECT es.*, sess.id as session_id
        FROM exam_subjects es
        JOIN exam_sessions sess ON es.exam_session_id = sess.id
        WHERE es.id = ?
    ";
    $stmt = $conn->prepare($exam_subject_sql);
    $stmt->bind_param('i', $exam_subject_id);
    $stmt->execute();
    $exam_subject = $stmt->get_result()->fetch_assoc();
    
    if (!$exam_subject) {
        echo json_encode(['success' => false, 'message' => 'Exam subject not found']);
        return;
    }
    
    // Get all students from classes/sections associated with this exam session
    $students_sql = "
        SELECT DISTINCT st.user_id, st.full_name, st.roll_number, st.admission_number,
               sem.marks_obtained, sem.grade_code, sem.remark,
               c.name as class_name, s.name as section_name
        FROM exam_session_classes esc
        JOIN classes c ON esc.class_id = c.id
        LEFT JOIN sections s ON esc.section_id = s.id
        JOIN students st ON c.id = st.class_id AND (esc.section_id IS NULL OR esc.section_id = st.section_id)
        LEFT JOIN student_exam_marks sem ON st.user_id = sem.student_user_id AND sem.exam_subject_id = ?
        WHERE esc.exam_session_id = ?
        ORDER BY st.roll_number, st.full_name
    ";
    
    $stmt = $conn->prepare($students_sql);
    $stmt->bind_param('ii', $exam_subject_id, $exam_subject['session_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = [
            'user_id' => $row['user_id'],
            'full_name' => $row['full_name'],
            'roll_number' => $row['roll_number'],
            'admission_number' => $row['admission_number'],
            'class_name' => $row['class_name'],
            'section_name' => $row['section_name'],
            'marks_obtained' => $row['marks_obtained'],
            'grade_code' => $row['grade_code'],
            'remark' => $row['remark']
        ];
    }
    
    echo json_encode(['success' => true, 'data' => $students]);
}
?>
