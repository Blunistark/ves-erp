<?php
// Disable error reporting for API responses
error_reporting(0);
ini_set('display_errors', 0);

// Include necessary files
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in and has admin role
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Get database connection
$conn = getDbConnection();
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Use FILTER_UNSAFE_RAW instead of deprecated FILTER_SANITIZE_STRING
    $type = filter_input(INPUT_GET, 'type', FILTER_UNSAFE_RAW);
    $type = trim(strip_tags($type));
    
    try {
        switch ($type) {
            case 'teachers':
                $sql = "SELECT u.id, u.full_name as name 
                       FROM users u 
                       JOIN teachers t ON u.id = t.user_id 
                       WHERE u.role = 'teacher' AND u.status = 'active'
                       ORDER BY u.full_name";
                $result = $conn->query($sql);
                if (!$result) {
                    throw new Exception($conn->error);
                }
                $teachers = [];
                while ($row = $result->fetch_assoc()) {
                    $teachers[] = $row;
                }
                echo json_encode(['status' => 'success', 'data' => $teachers]);
                break;

            case 'classes':
                $sql = "SELECT id, name FROM classes ORDER BY name";
                $result = $conn->query($sql);
                if (!$result) {
                    throw new Exception($conn->error);
                }
                $classes = [];
                while ($row = $result->fetch_assoc()) {
                    $classes[] = $row;
                }
                echo json_encode(['status' => 'success', 'data' => $classes]);
                break;

            case 'sections':
                $class_id = filter_input(INPUT_GET, 'class_id', FILTER_VALIDATE_INT);
                if (!$class_id) {
                    echo json_encode(['status' => 'error', 'message' => 'Class ID is required']);
                    exit;
                }
                $sql = "SELECT id, name FROM sections WHERE class_id = ? ORDER BY name";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception($conn->error);
                }
                $stmt->bind_param('i', $class_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $sections = [];
                while ($row = $result->fetch_assoc()) {
                    $sections[] = $row;
                }
                echo json_encode(['status' => 'success', 'data' => $sections]);
                break;

            case 'subjects':
                $sql = "SELECT id, name FROM subjects ORDER BY name";
                $result = $conn->query($sql);
                if (!$result) {
                    throw new Exception($conn->error);
                }
                $subjects = [];
                while ($row = $result->fetch_assoc()) {
                    $subjects[] = $row;
                }
                echo json_encode(['status' => 'success', 'data' => $subjects]);
                break;

            case 'assignments':
                $sql = "SELECT ta.id, 
                       u.full_name as teacher_name,
                       c.name as class_name,
                       s.name as section_name,
                       sub.name as subject_name,
                       ay.name as academic_year
                FROM teacher_assignments ta
                JOIN users u ON ta.teacher_user_id = u.id
                JOIN classes c ON ta.class_id = c.id
                JOIN sections s ON ta.section_id = s.id
                JOIN subjects sub ON ta.subject_id = sub.id
                JOIN academic_years ay ON ta.academic_year_id = ay.id
                ORDER BY c.name, s.name";
                
                $result = $conn->query($sql);
                if (!$result) {
                    throw new Exception($conn->error);
                }
                $assignments = [];
                while ($row = $result->fetch_assoc()) {
                    $assignments[] = $row;
                }
                echo json_encode(['status' => 'success', 'data' => $assignments]);
                break;

            default:
                echo json_encode(['status' => 'error', 'message' => 'Invalid request type']);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while fetching data: ' . $e->getMessage()]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Use FILTER_UNSAFE_RAW instead of deprecated FILTER_SANITIZE_STRING
    $action = filter_input(INPUT_POST, 'action', FILTER_UNSAFE_RAW);
    $action = trim(strip_tags($action));

    if ($action === 'delete') {
        $assignment_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$assignment_id) {
            echo json_encode(['status' => 'error', 'message' => 'Assignment ID is required']);
            exit;
        }

        try {
            $delete_sql = "DELETE FROM teacher_assignments WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            if (!$delete_stmt) {
                throw new Exception($conn->error);
            }
            $delete_stmt->bind_param('i', $assignment_id);
            
            if ($delete_stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Assignment deleted successfully']);
            } else {
                throw new Exception('Failed to delete assignment');
            }
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'An error occurred while deleting the assignment: ' . $e->getMessage()]);
            exit;
        }
    }

    // Get form data for assignment creation
    $teacher_user_id = filter_input(INPUT_POST, 'teacher', FILTER_VALIDATE_INT);
    $class_id = filter_input(INPUT_POST, 'class', FILTER_VALIDATE_INT);
    $section_id = filter_input(INPUT_POST, 'section', FILTER_VALIDATE_INT);
    $subject_id = filter_input(INPUT_POST, 'subject', FILTER_VALIDATE_INT);

    // Validate required fields
    if (!$teacher_user_id || !$class_id || !$section_id || !$subject_id) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'All fields are required',
            'debug' => [
                'teacher' => $teacher_user_id,
                'class' => $class_id,
                'section' => $section_id,
                'subject' => $subject_id
            ]
        ]);
        exit;
    }

    try {
       // Get current academic year
       $academic_year_sql = "SELECT id FROM academic_years WHERE is_current = 1 LIMIT 1";
       $academic_year_result = $conn->query($academic_year_sql);
       if (!$academic_year_result) {
           throw new Exception($conn->error);
       }
       
       if ($academic_year_result->num_rows === 0) {
           echo json_encode(['status' => 'error', 'message' => 'No active academic year found. Please set a current academic year first.']);
           exit;
       }
        
        $academic_year = $academic_year_result->fetch_assoc();
        $academic_year_id = $academic_year['id'];

        // Check if assignment already exists
        $check_sql = "SELECT id FROM teacher_assignments 
                     WHERE teacher_user_id = ? AND class_id = ? AND section_id = ? 
                     AND subject_id = ? AND academic_year_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        if (!$check_stmt) {
            throw new Exception($conn->error);
        }
        
        $check_stmt->bind_param('iiiii', $teacher_user_id, $class_id, $section_id, $subject_id, $academic_year_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'This assignment already exists']);
            exit;
        }

        // Insert new assignment
        $insert_sql = "INSERT INTO teacher_assignments (teacher_user_id, class_id, section_id, subject_id, academic_year_id, created_at) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
        $insert_stmt = $conn->prepare($insert_sql);
        if (!$insert_stmt) {
            throw new Exception($conn->error);
        }
        
        $insert_stmt->bind_param('iiiii', $teacher_user_id, $class_id, $section_id, $subject_id, $academic_year_id);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert the assignment
            if (!$insert_stmt->execute()) {
                throw new Exception('Failed to create assignment');
            }

            // Update the section's class teacher if this is an English subject assignment
            // Assuming English subject has a specific ID or is the main subject
            $update_section_sql = "UPDATE sections SET class_teacher_user_id = ? WHERE id = ? AND class_teacher_user_id IS NULL";
            $update_section_stmt = $conn->prepare($update_section_sql);
            if (!$update_section_stmt) {
                throw new Exception($conn->error);
            }
            
            $update_section_stmt->bind_param('ii', $teacher_user_id, $section_id);
            $update_section_stmt->execute();

            // Commit the transaction
            $conn->commit();
            
            echo json_encode(['status' => 'success', 'message' => 'Assignment created successfully and class teacher updated']);
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while creating the assignment: ' . $e->getMessage()]);
    }
    exit;
}

// Close the database connection
$conn->close(); 