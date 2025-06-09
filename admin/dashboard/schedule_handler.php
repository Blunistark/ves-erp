<?php
/**
 * Enhanced Schedule Handler for Exam Management System
 * Integrates schedule.php with exam_session_management.php
 */

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
      
            
        case 'get_proctors':
            getProctors($conn);
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
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Create exam schedule entry linked to exam session
 */
function createExamSchedule($conn, $data, $user_id, $user_role) {
    // Validate required fields - removed examName
    $required_fields = ['examType', 'examSubject', 'examClass', 'examDate', 'examStartTime', 'examEndTime'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }
    
    $conn->begin_transaction();
    
    try {
        // Check if we have an existing exam session for this class/type
        $session_check_sql = "SELECT id FROM exam_sessions 
                             WHERE class_id = ? AND session_type = ? 
                             AND academic_year = ? AND status = 'active'
                             ORDER BY created_at DESC LIMIT 1";
        
        $stmt = $conn->prepare($session_check_sql);
        $academic_year = '2024-25'; // Current academic year
        $session_type = $data['examType']; // Now directly FA or SA
        $stmt->bind_param("iss", $data['examClass'], $session_type, $academic_year);
        $stmt->execute();
        $session_result = $stmt->get_result();
        
        $exam_session_id = null;
        if ($session_result->num_rows > 0) {
            $session_row = $session_result->fetch_assoc();
            $exam_session_id = $session_row['id'];
        } else {
            // Create new exam session with simplified naming
            $session_name = $session_type . " Assessment - " . date('Y');
            $term = getCurrentTerm(); // You'll need to implement this function
            $start_date = $data['examDate'];
            $end_date = date('Y-m-d', strtotime($start_date . ' +7 days')); // Default 7-day exam period
            
            $create_session_sql = "INSERT INTO exam_sessions (
                session_name, session_type, academic_year, term, 
                start_date, end_date, class_id, created_by, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            
            $stmt = $conn->prepare($create_session_sql);
            $stmt->bind_param("ssssssii", 
                $session_name, $session_type, $academic_year, $term,
                $start_date, $end_date, $data['examClass'], $user_id
            );
            $stmt->execute();
            $exam_session_id = $conn->insert_id;
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
          // Get or create assessment entry with auto-generated title
        $assessment_title = $session_type . " - " . $data['examSubject'] . " Assessment";
        $assessment_sql = "SELECT id FROM assessments WHERE title = ? AND class_id = ? AND subject_id = ? AND assessment_type = ?";
        $stmt = $conn->prepare($assessment_sql);
        $stmt->bind_param("siis", $assessment_title, $data['examClass'], $subject_id, $session_type);
        $stmt->execute();
        $assessment_result = $stmt->get_result();
        
        $assessment_id = null;
        if ($assessment_result->num_rows > 0) {
            $assessment_row = $assessment_result->fetch_assoc();
            $assessment_id = $assessment_row['id'];
        } else {
            // Create new assessment entry
            $create_assessment_sql = "INSERT INTO assessments (
                class_id, section_id, teacher_user_id, title, type, 
                total_marks, date, subject_id, assessment_type
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($create_assessment_sql);
            $section_id = $data['examSection'] ?? null;
            $legacy_type = 'exam'; // Simplified to just 'exam'
            $total_marks = $data['maxMarks'] ?? 100;
            
            $stmt->bind_param("iiissisis", 
                $data['examClass'], $section_id, $user_id, $assessment_title, 
                $legacy_type, $total_marks, $data['examDate'], $subject_id, $session_type
            );
            $stmt->execute();
            $assessment_id = $conn->insert_id;
        }
        
        // Calculate duration
        $start_time = new DateTime($data['examStartTime']);
        $end_time = new DateTime($data['examEndTime']);
        $duration = $end_time->diff($start_time);
        $duration_minutes = ($duration->h * 60) + $duration->i;
        
        // Insert exam subject entry
        $exam_subject_sql = "INSERT INTO exam_subjects (
            exam_session_id, subject_id, assessment_id, exam_date, 
            exam_time, duration_minutes, total_marks, passing_marks, 
            venue, instructions
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
          $stmt = $conn->prepare($exam_subject_sql);
        $total_marks = $data['maxMarks'] ?? 100;
        $passing_marks = round($total_marks * 0.4); // 40% passing
        $venue = 'TBD'; // Default venue since rooms are no longer used
        $instructions = $data['examInstructions'] ?? '';
        
        $stmt->bind_param("iiiisiisss", 
            $exam_session_id, $subject_id, $assessment_id, $data['examDate'],
            $data['examStartTime'], $duration_minutes, $total_marks, 
            $passing_marks, $venue, $instructions
        );
        $stmt->execute();
        
        $conn->commit();
        
        echo json_encode([
            'success' => true, 
            'message' => 'Exam scheduled successfully',
            'exam_session_id' => $exam_session_id,
            'exam_subject_id' => $conn->insert_id
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
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
                    esub.start_time,
                    esub.end_time,
                    esub.total_marks as max_marks,
                    esub.duration,
                    s.name as subject_name,
                    s.id as subject_id,
                    c.name as class_name,
                    c.id as class_id,
                    u.full_name as proctor_name,
                    u.id as teacher_id
                FROM exam_sessions es
                INNER JOIN exam_subjects esub ON es.id = esub.session_id
                INNER JOIN subjects s ON esub.subject_id = s.id
                INNER JOIN classes c ON es.class_id = c.id
                LEFT JOIN users u ON esub.teacher_user_id = u.id
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
        $sql .= " ORDER BY esub.exam_date ASC, esub.start_time ASC";
        
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
    $end_date = $params['end'] ?? date('Y-m-t');
    
    $sql = "SELECT 
                es.id,
                es.exam_date as start,
                es.exam_time,
                es.duration_minutes,
                es.venue,
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
                'venue' => $row['venue'],
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
 * Get all teachers/proctors for dropdown
 */
function getProctors($conn) {
    $sql = "SELECT id as teacher_id, full_name 
            FROM users WHERE role = 'teacher' AND status = 'active' 
            ORDER BY full_name";
    $result = $conn->query($sql);
    
    $proctors = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $proctors[] = $row;
        }
    }
    
    echo json_encode(['success' => true, 'data' => $proctors]);
}

/**
 * Get detailed information about a specific exam
 */
function getExamDetails($conn, $exam_id) {    $sql = "SELECT es.*, 
                   sess.session_name, sess.session_type, sess.academic_year, sess.class_id,
                   c.name as class_name, 
                   s.name as subject_name,
                   u.full_name as proctor_name
            FROM exam_subjects es
            JOIN exam_sessions sess ON es.exam_session_id = sess.id
            LEFT JOIN classes c ON sess.class_id = c.id
            LEFT JOIN subjects s ON es.subject_id = s.id
            LEFT JOIN users u ON sess.created_by = u.id
            WHERE es.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $exam = $result->fetch_assoc();
        
        // Format the exam data for frontend
        $formatted_exam = [
            'id' => $exam['id'],            'exam_name' => $exam['session_name'],
            'exam_type' => strtolower($exam['session_type']),
            'exam_date' => $exam['exam_date'],
            'start_time' => $exam['exam_time'],
            'end_time' => date('H:i', strtotime($exam['exam_time'] . ' +' . $exam['duration_minutes'] . ' minutes')),
            'class_id' => $exam['class_id'], // From exam session
            'subject_id' => $exam['subject_id'],
            'duration' => $exam['duration_minutes'],
            'max_marks' => $exam['total_marks'],
            'instructions' => $exam['instructions'] ?? '',
            'proctor_name' => $exam['proctor_name'] ?? 'Not assigned'
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
