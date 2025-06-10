<?php
/**
 * Enhanced Schedule Handler for Exam Management System
 * Integrates schedule.php with exam_session_management.php
 */

// Prevent any HTML output that could break JSON responses
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set proper content type for JSON responses
header('Content-Type: application/json');

// Start output buffering to catch any unwanted output
ob_start();

// Start session for authentication
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

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

try {
    switch ($action) {
        case 'create_exam_schedule':
            createExamSchedule($conn, $_POST, $user_id, $user_role);
            break;
            
        case 'get_exam_sessions':
            getExamSessions($conn);
            break;
            
        case 'get_available_sessions':
            getAvailableExamSessions($conn, $_POST, $user_id, $user_role);
            break;
            
        case 'get_schedule_data':
            getScheduleData($conn, $_GET);
            break;
            
        case 'update_exam_schedule':
            updateExamSchedule($conn, $_POST, $user_id, $user_role);
            break;
            
        case 'delete_exam_schedule':
            deleteExamSchedule($conn, $_POST, $user_id, $user_role);
            break;
            
        case 'get_calendar_events':
            getCalendarEvents($conn, $_GET);
            break;
            
        case 'get_classes':
            getClasses($conn);
            break;
            
        case 'get_subjects':
            getSubjects($conn);
            break;
            
        case 'get_exam_details':
            getExamDetails($conn, $_GET['id']);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    // Clear any output buffer to prevent HTML errors in JSON
    if (ob_get_level()) {
        ob_clean();
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    // End output buffering
    if (ob_get_level()) {
        ob_end_flush();
    }
}

/**
 * Create exam schedule entry following existing exam session workflow
 * Modified to integrate with exam_session_management.php system
 */
function createExamSchedule($conn, $data, $user_id, $user_role) {
    // Follow proper workflow: Work with existing sessions, don't create automatically
    if (!empty($data['examSessionId'])) {
        // Option 1: Add subject to existing session (preferred workflow)
        return addSubjectToExistingSession($conn, $data, $user_id, $user_role);
    } else {
        // Option 2: Guide user to create session first, then add subjects
        // Instead of auto-creating, return available sessions for user to choose
        return getAvailableExamSessions($conn, $data, $user_id, $user_role);
    }
}

/**
 * Add subject to existing exam session following proper workflow
 * Uses same pattern as exam_session_actions.php for consistency
 */
function addSubjectToExistingSession($conn, $data, $user_id, $user_role) {
    $required_fields = ['examSessionId', 'examSubject', 'examDate'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
    
    // Verify session exists and user has access
    $session_sql = "SELECT * FROM exam_sessions WHERE id = ? AND status = 'active'";
    $stmt = $conn->prepare($session_sql);
    $stmt->bind_param("i", $data['examSessionId']);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    
    if (!$session) {
        throw new Exception("Exam session not found or inactive");
    }
    
    // Get subject ID
    $subject_sql = "SELECT id FROM subjects WHERE name = ? OR code = ?";
    $stmt = $conn->prepare($subject_sql);
    $stmt->bind_param("ss", $data['examSubject'], $data['examSubject']);
    $stmt->execute();
    $subject_result = $stmt->get_result();
    
    if ($subject_result->num_rows === 0) {
        throw new Exception("Subject not found: " . $data['examSubject']);
    }
    
    $subject_row = $subject_result->fetch_assoc();
    $subject_id = $subject_row['id'];
    
    // Check for existing subject in this session
    $duplicate_check = "SELECT id FROM exam_subjects WHERE exam_session_id = ? AND subject_id = ?";
    $stmt = $conn->prepare($duplicate_check);
    $stmt->bind_param("ii", $data['examSessionId'], $subject_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("This subject is already scheduled for this exam session");
    }
    
    // Find or create assessment for this subject and session type
    $assessment_title = $session['session_type'] . " - " . $data['examSubject'] . " Assessment";
    $assessment_sql = "SELECT id FROM assessments WHERE assessment_type = ? AND subject_id = ? ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($assessment_sql);
    $stmt->bind_param("si", $session['session_type'], $subject_id);
    $stmt->execute();
    $assessment_result = $stmt->get_result();
    
    $assessment_id = null;
    if ($assessment_result->num_rows > 0) {
        $assessment_row = $assessment_result->fetch_assoc();
        $assessment_id = $assessment_row['id'];
    } else {
        // Create new assessment following same pattern
        $create_assessment_sql = "INSERT INTO assessments (
            class_id, section_id, teacher_user_id, title, type, 
            total_marks, date, subject_id, assessment_type
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($create_assessment_sql);
        $section_id = $session['section_id'] ?? null;
        $legacy_type = 'exam';
        $total_marks = $data['maxMarks'] ?? 100;
        
        $stmt->bind_param("iissisiss", 
            $session['class_id'], $section_id, $user_id, $assessment_title, 
            $legacy_type, $total_marks, $data['examDate'], $subject_id, $session['session_type']
        );
        $stmt->execute();
        $assessment_id = $conn->insert_id;
    }
    
    // Insert exam subject using same pattern as exam_session_actions.php
    $exam_subject_sql = "INSERT INTO exam_subjects (
        exam_session_id, subject_id, assessment_id, exam_date, 
        total_marks, status, created_by, created_at";
    
    $values_part = "VALUES (?, ?, ?, ?, ?, 'active', ?, NOW()";
    $bind_types = "iiisii";
    $bind_values = [
        $data['examSessionId'], 
        $subject_id, 
        $assessment_id, 
        $data['examDate'],
        $data['maxMarks'] ?? 100,
        $user_id
    ];
    
    // Add optional time fields if provided (for schedule.php compatibility)
    if (!empty($data['examStartTime'])) {
        $exam_subject_sql .= ", exam_time";
        $values_part .= ", ?";
        $bind_types .= "s";
        $bind_values[] = $data['examStartTime'];
    }
    
    if (!empty($data['examStartTime']) && !empty($data['examEndTime'])) {
        $start_time = new DateTime($data['examStartTime']);
        $end_time = new DateTime($data['examEndTime']);
        $duration = $end_time->diff($start_time);
        $duration_minutes = ($duration->h * 60) + $duration->i;
        
        $exam_subject_sql .= ", duration_minutes";
        $values_part .= ", ?";
        $bind_types .= "i";
        $bind_values[] = $duration_minutes;
    }
    
    if (!empty($data['examInstructions'])) {
        $exam_subject_sql .= ", instructions";
        $values_part .= ", ?";
        $bind_types .= "s";
        $bind_values[] = $data['examInstructions'];
    }
    
    $exam_subject_sql .= ") " . $values_part . ")";
    
    $stmt = $conn->prepare($exam_subject_sql);
    $stmt->bind_param($bind_types, ...$bind_values);
    $stmt->execute();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Subject added to exam session successfully',
        'exam_session_id' => $data['examSessionId'],
        'exam_subject_id' => $conn->insert_id,
        'workflow_note' => 'Subject added following existing exam session workflow'
    ]);
}

/**
 * Get available exam sessions instead of auto-creating
 * Guides user to use proper workflow: Session → Subjects → Marks
 */
function getAvailableExamSessions($conn, $data, $user_id, $user_role) {
    // Get current academic year
    $academic_year = '2024-25';
    
    // Check for existing active sessions that could accommodate this subject
    $session_sql = "SELECT es.*, c.name as class_name
                   FROM exam_sessions es
                   LEFT JOIN classes c ON es.class_id = c.id
                   WHERE es.status = 'active' 
                   AND es.academic_year = ?
                   ORDER BY es.created_at DESC";
    
    $stmt = $conn->prepare($session_sql);
    $stmt->bind_param("s", $academic_year);
    $stmt->execute();
    $sessions_result = $stmt->get_result();
    
    $available_sessions = [];
    while ($session = $sessions_result->fetch_assoc()) {
        $available_sessions[] = [
            'id' => $session['id'],
            'session_name' => $session['session_name'],
            'session_type' => $session['session_type'],
            'class_name' => $session['class_name'],
            'start_date' => $session['start_date'],
            'end_date' => $session['end_date']
        ];
    }
    
    // Return guidance message with available sessions
    echo json_encode([
        'success' => false,
        'message' => 'Please select an existing exam session or create a new one first',
        'workflow_guidance' => 'To schedule exams: 1) Create exam session, 2) Add subjects to session, 3) Enter marks',
        'available_sessions' => $available_sessions,
        'redirect_url' => 'exam_session_management.php',
        'action_required' => 'create_or_select_session'
    ]);
}

/**
 * Get all exam sessions for dropdown
 */
function getExamSessions($conn) {
    $sql = "SELECT es.*, c.name as class_name, s.name as section_name 
            FROM exam_sessions es
            LEFT JOIN classes c ON es.class_id = c.id
            LEFT JOIN sections s ON es.section_id = s.id
            WHERE es.status IN ('active', 'draft')
            ORDER BY es.start_date DESC";
    
    $result = $conn->query($sql);
    $sessions = [];
    
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $sessions]);
}

/**
 * Get schedule data for calendar, list, and table views
 */
function getScheduleData($conn, $params) {
    try {        // Build the base query to get scheduled exams
        $sql = "SELECT 
                    es.id,
                    es.session_name as exam_name,
                    es.session_type as exam_type,
                    esub.exam_date,
                    esub.exam_time as start_time,
                    TIME_FORMAT(ADDTIME(esub.exam_time, SEC_TO_TIME(esub.duration_minutes * 60)), '%H:%i:%s') as end_time,
                    esub.total_marks as max_marks,
                    esub.duration_minutes as duration,
                    s.name as subject_name,
                    s.id as subject_id,
                    c.name as class_name,
                    c.id as class_id
                FROM exam_sessions es
                INNER JOIN exam_subjects esub ON es.id = esub.exam_session_id
                INNER JOIN subjects s ON esub.subject_id = s.id
                INNER JOIN classes c ON es.class_id = c.id
                INNER JOIN assessments a ON esub.assessment_id = a.id
                WHERE es.status = 'active'";
        
        $bind_params = [];
        $param_types = "";
        
        // Add filters if provided
        if (!empty($params['examType'])) {
            $sql .= " AND es.session_type = ?";
            $bind_params[] = $params['examType'];
            $param_types .= "s";
        }
        
        if (!empty($params['class'])) {
            $sql .= " AND es.class_id = ?";
            $bind_params[] = $params['class'];
            $param_types .= "i";
        }
        
        if (!empty($params['subject'])) {
            $sql .= " AND esub.subject_id = ?";
            $bind_params[] = $params['subject'];
            $param_types .= "i";
        }
        
        if (!empty($params['startDate'])) {
            $sql .= " AND esub.exam_date >= ?";
            $bind_params[] = $params['startDate'];
            $param_types .= "s";
        }
        
        if (!empty($params['endDate'])) {
            $sql .= " AND esub.exam_date <= ?";
            $bind_params[] = $params['endDate'];
            $param_types .= "s";
        }
        
        // Add month/year filter for calendar view
        if (!empty($params['month']) && !empty($params['year'])) {
            $sql .= " AND MONTH(esub.exam_date) = ? AND YEAR(esub.exam_date) = ?";
            $bind_params[] = $params['month'];
            $bind_params[] = $params['year'];
            $param_types .= "ii";
        }
        
        // Order by date and time
        $sql .= " ORDER BY esub.exam_date ASC, esub.exam_time ASC";
        
        // Prepare and execute query
        $stmt = $conn->prepare($sql);
        if (!empty($bind_params)) {
            $stmt->bind_param($param_types, ...$bind_params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'events' => $events,
            'count' => count($events)
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching schedule data: ' . $e->getMessage(),
            'events' => []
        ]);
    }
}

/**
 * Get calendar events for the integrated calendar
 */
function getCalendarEvents($conn, $params) {
    $start_date = $params['start'] ?? date('Y-m-01');
    $end_date = $params['end'] ?? date('Y-m-t');        $sql = "SELECT 
                es.id,
                es.exam_date as start,
                es.exam_time,
                es.duration_minutes,
                s.name as subject_name,
                c.name as class_name,
                sess.session_type,
                CONCAT(s.name, ' - ', c.name) as title,
                CASE 
                    WHEN sess.session_type = 'SA' THEN '#1976d2'
                    WHEN sess.session_type = 'FA' THEN '#7b1fa2'
                    ELSE '#ff9800'
                END as color
            FROM exam_subjects es
            JOIN exam_sessions sess ON es.exam_session_id = sess.id
            JOIN subjects s ON es.subject_id = s.id
            JOIN classes c ON sess.class_id = c.id
            WHERE es.exam_date BETWEEN ? AND ?
            AND sess.status = 'active'
            ORDER BY es.exam_date, es.exam_time";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $end_time = date('H:i:s', strtotime($row['exam_time'] . ' +' . $row['duration_minutes'] . ' minutes'));
        
        $events[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'start' => $row['start'] . 'T' . $row['exam_time'],
            'end' => $row['start'] . 'T' . $end_time,
            'color' => $row['color'],
            'extendedProps' => [
                'subject' => $row['subject_name'],
                'class' => $row['class_name'],
                'type' => $row['session_type'],
                'duration' => $row['duration_minutes']
            ]
        ];
    }
    
    echo json_encode($events);
}

/**
 * Update exam schedule
 */
function updateExamSchedule($conn, $data, $user_id, $user_role) {
    $exam_subject_id = $data['exam_subject_id'] ?? null;
    if (!$exam_subject_id) {
        throw new Exception("Exam subject ID is required");
    }
    
    // Check if user has permission to update this exam
    $check_sql = "SELECT es.*, sess.created_by 
                  FROM exam_subjects es
                  JOIN exam_sessions sess ON es.exam_session_id = sess.id
                  WHERE es.id = ?";
    
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $exam_subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Exam not found");
    }
    
    $exam = $result->fetch_assoc();
    
    // Only admin, headmaster, or the creator can update
    if (!hasRole(['admin', 'headmaster']) && $exam['created_by'] != $user_id) {
        throw new Exception("You don't have permission to update this exam");
    }
    
    $update_fields = [];
    $update_values = [];
    $types = "";
    
    if (!empty($data['examDate'])) {
        $update_fields[] = "exam_date = ?";
        $update_values[] = $data['examDate'];
        $types .= "s";
    }
    
    if (!empty($data['examStartTime'])) {
        $update_fields[] = "exam_time = ?";
        $update_values[] = $data['examStartTime'];
        $types .= "s";
    }
    
    if (!empty($data['examStartTime']) && !empty($data['examEndTime'])) {
        $start_time = new DateTime($data['examStartTime']);
        $end_time = new DateTime($data['examEndTime']);
        $duration = $end_time->diff($start_time);
        $duration_minutes = ($duration->h * 60) + $duration->i;
        
        $update_fields[] = "duration_minutes = ?";
        $update_values[] = $duration_minutes;        $types .= "i";
    }
    
    // Room field removed - no longer processing examRoom
    
    if (!empty($data['maxMarks'])) {
        $update_fields[] = "total_marks = ?";
        $update_values[] = $data['maxMarks'];
        $types .= "i";
        
        $update_fields[] = "passing_marks = ?";
        $update_values[] = round($data['maxMarks'] * 0.4);
        $types .= "i";
    }
    
    if (!empty($data['examInstructions'])) {
        $update_fields[] = "instructions = ?";
        $update_values[] = $data['examInstructions'];
        $types .= "s";
    }
    
    if (empty($update_fields)) {
        throw new Exception("No fields to update");
    }
    
    $update_values[] = $exam_subject_id;
    $types .= "i";
    
    $sql = "UPDATE exam_subjects SET " . implode(', ', $update_fields) . " WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$update_values);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Exam updated successfully']);
}

/**
 * Delete exam schedule
 */
function deleteExamSchedule($conn, $data, $user_id, $user_role) {
    $exam_subject_id = $data['exam_subject_id'] ?? null;
    if (!$exam_subject_id) {
        throw new Exception("Exam subject ID is required");
    }
    
    // Check if user has permission to delete this exam
    $check_sql = "SELECT es.*, sess.created_by 
                  FROM exam_subjects es
                  JOIN exam_sessions sess ON es.exam_session_id = sess.id
                  WHERE es.id = ?";
    
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $exam_subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Exam not found");
    }
    
    $exam = $result->fetch_assoc();
    
    // Only admin, headmaster, or the creator can delete
    if (!hasRole(['admin', 'headmaster']) && $exam['created_by'] != $user_id) {
        throw new Exception("You don't have permission to delete this exam");
    }
    
    // Check if there are any marks recorded for this exam
    $marks_check_sql = "SELECT COUNT(*) as count FROM student_exam_marks WHERE exam_subject_id = ?";
    $stmt = $conn->prepare($marks_check_sql);
    $stmt->bind_param("i", $exam_subject_id);
    $stmt->execute();
    $marks_result = $stmt->get_result();
    $marks_count = $marks_result->fetch_assoc()['count'];
    
    if ($marks_count > 0) {
        throw new Exception("Cannot delete exam with recorded marks. Please remove all marks first.");
    }
    
    $sql = "DELETE FROM exam_subjects WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $exam_subject_id);
    $stmt->execute();
    
    echo json_encode(['success' => true, 'message' => 'Exam deleted successfully']);
}

/**
 * Get all classes for dropdown
 */
function getClasses($conn) {
    $sql = "SELECT id, name FROM classes ORDER BY name";
    $result = $conn->query($sql);
    
    $classes = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $classes[] = [
                'class_id' => $row['id'],
                'class_name' => $row['name']
            ];
        }
    }
    
    echo json_encode(['success' => true, 'data' => $classes]);
}

/**
 * Get all subjects for dropdown
 */
function getSubjects($conn) {
    $sql = "SELECT id, name, code FROM subjects ORDER BY name";
    $result = $conn->query($sql);
    
    $subjects = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $subjects[] = [
                'subject_id' => $row['id'],
                'subject_name' => $row['name'],
                'subject_code' => $row['code']
            ];
        }
    }
    
    echo json_encode(['success' => true, 'data' => $subjects]);
}

/**
 * Get detailed information about a specific exam
 */
function getExamDetails($conn, $exam_id) {
    $sql = "SELECT es.*, 
                   sess.session_name, sess.session_type, sess.academic_year, sess.class_id,
                   c.name as class_name, 
                   s.name as subject_name
            FROM exam_subjects es
            JOIN exam_sessions sess ON es.exam_session_id = sess.id
            LEFT JOIN classes c ON sess.class_id = c.id
            LEFT JOIN subjects s ON es.subject_id = s.id
            WHERE es.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $exam = $result->fetch_assoc();
        
        // Format the exam data for frontend
        $formatted_exam = [
            'id' => $exam['id'],
            'exam_name' => $exam['session_name'],
            'exam_type' => strtolower($exam['session_type']),
            'exam_date' => $exam['exam_date'],
            'start_time' => $exam['exam_time'],
            'end_time' => date('H:i', strtotime($exam['exam_time'] . ' +' . $exam['duration_minutes'] . ' minutes')),
            'class_id' => $exam['class_id'], // From exam session
            'subject_id' => $exam['subject_id'],
            'duration' => $exam['duration_minutes'],
            'max_marks' => $exam['total_marks'],
            'instructions' => $exam['instructions'] ?? ''
        ];
        
        echo json_encode(['success' => true, 'exam' => $formatted_exam]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Exam not found']);
    }
}

/**
 * Helper function to get current term
 */
function getCurrentTerm() {
    $month = date('n');
    if ($month >= 4 && $month <= 9) {
        return 'Term 1';
    } else {
        return 'Term 2';
    }
}
?>
