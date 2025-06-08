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

// DEBUG: Log all incoming data
error_log("Class Teacher Assignment Debug - Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Class Teacher Assignment Debug - POST data: " . print_r($_POST, true));
error_log("Class Teacher Assignment Debug - GET data: " . print_r($_GET, true));

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

            case 'assignments':
                // Get class teacher assignments from sections table
                $sql = "SELECT s.id, 
                       c.name as class_name,
                       s.name as section_name,
                       u.full_name as teacher_name,
                       COUNT(st.user_id) as student_count,
                       CASE WHEN s.class_teacher_user_id IS NOT NULL THEN 'Assigned' ELSE 'Unassigned' END as status
                FROM sections s
                JOIN classes c ON s.class_id = c.id
                LEFT JOIN users u ON s.class_teacher_user_id = u.id
                LEFT JOIN students st ON s.id = st.section_id
                GROUP BY s.id, c.name, s.name, u.full_name
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
        $section_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if (!$section_id) {
            echo json_encode(['status' => 'error', 'message' => 'Section ID is required']);
            exit;
        }

        try {
            // Remove class teacher assignment by setting class_teacher_user_id to NULL
            $delete_sql = "UPDATE sections SET class_teacher_user_id = NULL WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            if (!$delete_stmt) {
                throw new Exception($conn->error);
            }
            $delete_stmt->bind_param('i', $section_id);
            
            if ($delete_stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Class teacher assignment removed successfully']);
            } else {
                throw new Exception('Failed to remove class teacher assignment');
            }
            exit;
        } catch (Exception $e) {
            error_log($e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'An error occurred while removing the assignment: ' . $e->getMessage()]);
            exit;
        }
    }

    // Get form data for class teacher assignment
    $teacher_user_id = filter_input(INPUT_POST, 'teacher', FILTER_VALIDATE_INT);
    $class_id = filter_input(INPUT_POST, 'class', FILTER_VALIDATE_INT);
    $section_id = filter_input(INPUT_POST, 'section', FILTER_VALIDATE_INT);

    // DEBUG: Log the extracted values
    error_log("Class Teacher Assignment Debug - Extracted values:");
    error_log("teacher_user_id: " . var_export($teacher_user_id, true));
    error_log("class_id: " . var_export($class_id, true));
    error_log("section_id: " . var_export($section_id, true));

    // Validate required fields (only teacher, class, and section for class teacher assignment)
    if (!$teacher_user_id || !$class_id || !$section_id) {
        echo json_encode([
            'status' => 'error', 
            'message' => 'All fields are required (Teacher, Class, and Section)',
            'debug' => [
                'teacher' => $teacher_user_id,
                'class' => $class_id,
                'section' => $section_id,
                'raw_post' => $_POST
            ]
        ]);
        exit;
    }

    try {
        // Check if the section exists and belongs to the specified class
        $check_section_sql = "SELECT id FROM sections WHERE id = ? AND class_id = ?";
        $check_section_stmt = $conn->prepare($check_section_sql);
        if (!$check_section_stmt) {
            throw new Exception($conn->error);
        }
        
        $check_section_stmt->bind_param('ii', $section_id, $class_id);
        $check_section_stmt->execute();
        $section_result = $check_section_stmt->get_result();

        if ($section_result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid section for the selected class']);
            exit;
        }

        // Check if the teacher is already assigned to this section
        $check_assignment_sql = "SELECT class_teacher_user_id FROM sections WHERE id = ?";
        $check_assignment_stmt = $conn->prepare($check_assignment_sql);
        if (!$check_assignment_stmt) {
            throw new Exception($conn->error);
        }
        
        $check_assignment_stmt->bind_param('i', $section_id);
        $check_assignment_stmt->execute();
        $assignment_result = $check_assignment_stmt->get_result();
        $current_assignment = $assignment_result->fetch_assoc();

        if ($current_assignment['class_teacher_user_id'] == $teacher_user_id) {
            echo json_encode(['status' => 'error', 'message' => 'This teacher is already assigned to this class/section']);
            exit;
        }

        // Update the section with the new class teacher
        $update_sql = "UPDATE sections SET class_teacher_user_id = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        if (!$update_stmt) {
            throw new Exception($conn->error);
        }
        
        $update_stmt->bind_param('ii', $teacher_user_id, $section_id);
        
        if ($update_stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Class teacher assigned successfully']);
        } else {
            throw new Exception('Failed to assign class teacher');
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred while assigning the class teacher: ' . $e->getMessage()]);
    }
    exit;
}

// Close the database connection
$conn->close();
?> 