<?php
require_once 'con.php';
session_start();

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$teacher_id = $_SESSION['user_id'];

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

switch ($action) {
    case 'create_assessment':
        createAssessment($data);
        break;
    case 'record_results':
        recordResults($data);
        break;
    case 'get_assessment':
        getAssessment($data['assessment_id']);
        break;
    case 'delete_assessment':
        deleteAssessment($data['assessment_id']);
        break;
    case 'list_assessments':
        listAssessments();
        break;
    case 'list_assessments_by_class':
        listAssessmentsByClass();
        break;
    case 'get_student_list_for_assessment':
        getStudentListForAssessment($data['assessment_id']);
        break;
    case 'get_next_upcoming_exam':
        getNextUpcomingExam();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        exit();
}

// Create new assessment
function createAssessment($data) {
    global $conn, $teacher_id;
    
    try {
        $conn->begin_transaction();
        
        // Insert into assessments table (now with subject_id)
        $stmt = $conn->prepare("INSERT INTO assessments (class_id, section_id, teacher_user_id, title, type, total_marks, date, subject_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiissssi", 
            $data['class_id'], 
            $data['section_id'], 
            $teacher_id,
            $data['title'],
            $data['type'],
            $data['total_marks'],
            $data['date'],
            $data['subject_id']
        );
        $stmt->execute();
        
        $assessment_id = $conn->insert_id;
        
        // Get class/section names for message
        $class_name = '';
        $section_name = '';
        $stmt2 = $conn->prepare("SELECT c.name as class_name, s.name as section_name FROM classes c JOIN sections s ON s.id = ? WHERE c.id = ?");
        $stmt2->bind_param("ii", $data['section_id'], $data['class_id']);
        $stmt2->execute();
        $stmt2->bind_result($class_name, $section_name);
        $stmt2->fetch();
        $stmt2->close();
        if (!$class_name) $class_name = $data['class_id'];
        if (!$section_name) $section_name = $data['section_id'];
        $msg = "New exam scheduled: {$data['title']} for $class_name - $section_name on {$data['date']}";

        // Notify all students in this class/section
        $student_ids = [];
        $stmt3 = $conn->prepare("SELECT user_id FROM students WHERE class_id = ? AND section_id = ?");
        $stmt3->bind_param("ii", $data['class_id'], $data['section_id']);
        $stmt3->execute();
        $res = $stmt3->get_result();
        while ($row = $res->fetch_assoc()) {
            $student_ids[] = $row['user_id'];
        }
        $stmt3->close();
        if (!empty($student_ids)) {
            $stmt4 = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            foreach ($student_ids as $sid) {
                $stmt4->bind_param("is", $sid, $msg);
                $stmt4->execute();
            }
            $stmt4->close();
        }
        // Notify the teacher as well
        $stmt5 = $conn->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt5->bind_param("is", $teacher_id, $msg);
        $stmt5->execute();
        $stmt5->close();
        
        $conn->commit();
        echo json_encode(['success' => true, 'assessment_id' => $assessment_id]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Record exam results
function recordResults($data) {
    global $conn, $teacher_id;
    
    try {
        $conn->begin_transaction();
        
        // Verify teacher owns this assessment
        $stmt = $conn->prepare("SELECT id FROM assessments WHERE id = ? AND teacher_user_id = ?");
        $stmt->bind_param("ii", $data['assessment_id'], $teacher_id);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            throw new Exception('Unauthorized access to assessment');
        }
        
        // Delete existing results
        $stmt = $conn->prepare("DELETE FROM exam_results WHERE assessment_id = ?");
        $stmt->bind_param("i", $data['assessment_id']);
        $stmt->execute();
        
        // Insert new results
        $stmt = $conn->prepare("INSERT INTO exam_results (assessment_id, student_user_id, marks_obtained, grade_code, remark) 
                               VALUES (?, ?, ?, ?, ?)");
        
        foreach ($data['results'] as $result) {
            $stmt->bind_param("iidss", 
                $data['assessment_id'],
                $result['student_id'],
                $result['marks'],
                $result['grade'],
                $result['remark']
            );
            $stmt->execute();
        }
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Get assessment details
function getAssessment($assessment_id) {
    global $conn, $teacher_id;
    
    try {
        // Get assessment details, including teacher's name
        $stmt = $conn->prepare("SELECT a.*, c.name as class_name, s.name as section_name, u.full_name as teacher_name 
                               FROM assessments a 
                               JOIN classes c ON a.class_id = c.id 
                               JOIN sections s ON a.section_id = s.id 
                               JOIN users u ON a.teacher_user_id = u.id 
                               WHERE a.id = ? AND a.teacher_user_id = ?");
        $stmt->bind_param("ii", $assessment_id, $teacher_id);
        $stmt->execute();
        $assessment = $stmt->get_result()->fetch_assoc();
        
        if (!$assessment) {
            throw new Exception('Assessment not found');
        }
        
        // Get results
        $stmt = $conn->prepare("SELECT er.*, s.full_name, s.roll_number 
                               FROM exam_results er 
                               JOIN students s ON er.student_user_id = s.user_id 
                               WHERE er.assessment_id = ?");
        $stmt->bind_param("i", $assessment_id);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $assessment['results'] = $results;
        
        echo json_encode(['success' => true, 'data' => $assessment]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Delete assessment
function deleteAssessment($assessment_id) {
    global $conn, $teacher_id;
    
    try {
        $conn->begin_transaction();
        
        // Verify teacher owns this assessment
        $stmt = $conn->prepare("SELECT id FROM assessments WHERE id = ? AND teacher_user_id = ?");
        $stmt->bind_param("ii", $assessment_id, $teacher_id);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            throw new Exception('Unauthorized access to assessment');
        }
        
        // Delete results first (due to foreign key constraint)
        $stmt = $conn->prepare("DELETE FROM exam_results WHERE assessment_id = ?");
        $stmt->bind_param("i", $assessment_id);
        $stmt->execute();
        
        // Delete assessment
        $stmt = $conn->prepare("DELETE FROM assessments WHERE id = ?");
        $stmt->bind_param("i", $assessment_id);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Generate performance report
function generatePerformanceReport($class_id, $section_id, $subject_id) {
    global $conn, $teacher_id;
    
    $stmt = $conn->prepare("SELECT 
                               s.full_name,
                               s.admission_number,
                               s.roll_number,
                               COUNT(DISTINCT a.id) as total_assessments,
                               AVG(er.marks_obtained) as average_marks,
                               MAX(er.marks_obtained) as highest_marks,
                               MIN(er.marks_obtained) as lowest_marks
                           FROM students s
                           LEFT JOIN exam_results er ON s.user_id = er.student_user_id
                           LEFT JOIN assessments a ON er.assessment_id = a.id
                           WHERE s.class_id = ? 
                           AND s.section_id = ?
                           AND a.teacher_user_id = ?
                           AND er.subject_id = ?
                           GROUP BY s.user_id
                           ORDER BY s.roll_number");
                           
    $stmt->bind_param("iiii", $class_id, $section_id, $teacher_id, $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $report = [];
    while ($row = $result->fetch_assoc()) {
        $report[] = $row;
    }
    
    return ['success' => true, 'data' => $report];
}

function listAssessments() {
    global $conn, $teacher_id;
    try {
        $stmt = $conn->prepare("SELECT a.*, c.name as class_name, s.name as section_name 
                                FROM assessments a 
                                JOIN classes c ON a.class_id = c.id 
                                JOIN sections s ON a.section_id = s.id 
                                WHERE a.teacher_user_id = ? 
                                ORDER BY a.date DESC");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $assessments = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $assessments]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function listAssessmentsByClass() {
    global $conn, $teacher_id;
    try {
        $sql = "SELECT a.*, c.id as class_id, c.name as class_name, s.id as section_id, s.name as section_name
                FROM assessments a
                JOIN classes c ON a.class_id = c.id
                JOIN sections s ON a.section_id = s.id
                WHERE a.teacher_user_id = ?
                ORDER BY c.name, s.name, a.date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $grouped = [];
        while ($row = $result->fetch_assoc()) {
            $key = $row['class_id'] . '-' . $row['section_id'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'class_id' => $row['class_id'],
                    'class_name' => $row['class_name'],
                    'section_id' => $row['section_id'],
                    'section_name' => $row['section_name'],
                    'assessments' => []
                ];
            }
            $grouped[$key]['assessments'][] = $row;
        }
        // Re-index for JSON
        $resultArr = array_values($grouped);
        echo json_encode(['success' => true, 'data' => $resultArr]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getStudentListForAssessment($assessment_id) {
    global $conn, $teacher_id;
    try {
        // Get the class_id and section_id for this assessment, and verify teacher owns it
        $stmt = $conn->prepare("SELECT class_id, section_id FROM assessments WHERE id = ? AND teacher_user_id = ?");
        $stmt->bind_param("ii", $assessment_id, $teacher_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) throw new Exception('Assessment not found or unauthorized');
        $class_id = $row['class_id'];
        $section_id = $row['section_id'];

        // Get all students in this class/section
        $stmt = $conn->prepare("SELECT user_id, full_name, roll_number FROM students WHERE class_id = ? AND section_id = ? ORDER BY roll_number, full_name");
        $stmt->bind_param("ii", $class_id, $section_id);
        $stmt->execute();
        $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Get marks for this assessment
        $stmt = $conn->prepare("SELECT student_user_id, marks_obtained, grade_code, remark FROM exam_results WHERE assessment_id = ?");
        $stmt->bind_param("i", $assessment_id);
        $stmt->execute();
        $results = [];
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $results[$r['student_user_id']] = $r;
        }

        // Merge marks into students
        foreach ($students as &$student) {
            $sid = $student['user_id'];
            $student['marks_obtained'] = isset($results[$sid]) ? $results[$sid]['marks_obtained'] : null;
            $student['grade_code'] = isset($results[$sid]) ? $results[$sid]['grade_code'] : null;
            $student['remark'] = isset($results[$sid]) ? $results[$sid]['remark'] : null;
        }

        echo json_encode(['success' => true, 'data' => $students]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function getNextUpcomingExam() {
    global $conn, $teacher_id;
    try {
        $today = date('Y-m-d');
        $stmt = $conn->prepare("SELECT a.title, c.name as class_name, s.name as section_name, a.date
            FROM assessments a
            JOIN classes c ON a.class_id = c.id
            JOIN sections s ON a.section_id = s.id
            WHERE a.teacher_user_id = ? AND a.date >= ?
            ORDER BY a.date ASC LIMIT 1");
        $stmt->bind_param("is", $teacher_id, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $exam = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $exam ?: null]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} 