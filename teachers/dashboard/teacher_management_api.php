<?php
/**
 * Teacher Management API Backend - Teacher Portal Version
 * Handles assignment operations for headmasters in teacher portal
 * Restricted permissions: No teacher addition/deletion
 */

require_once __DIR__ . '/../../includes/functions.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check authentication and role - only headmasters in teacher portal
if (!isLoggedIn() || !hasRole(['teacher', 'headmaster'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Only headmasters can access this API
if ($_SESSION['role'] !== 'headmaster') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Only headmasters can access this functionality']);
    exit;
}

// Include database connection
require_once 'con.php';

// Set JSON content type
header('Content-Type: application/json');

// Get request data
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

try {
    switch ($action) {
        case 'get_teachers':
            handleGetTeachers();
            break;
            
        case 'assign_class_teacher':
            handleAssignClassTeacher();
            break;
            
        case 'get_class_assignments':
            handleGetClassAssignments();
            break;
            
        case 'update_subject_assignments':
            handleUpdateSubjectAssignments();
            break;
            
        case 'get_teacher_subjects':
            handleGetTeacherSubjects();
            break;
            
        case 'get_statistics':
            handleGetStatistics();
            break;
            
        case 'reassign_class_teacher':
            handleReassignClassTeacher();
            break;
            
        // Restricted actions for headmasters
        case 'add_teacher':
        case 'delete_teacher':
        case 'update_teacher_status':
            throw new Exception('Headmasters cannot add, delete, or modify teacher accounts. Please contact the administrator.');
            
        default:
            throw new Exception('Invalid action specified');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Get all teachers with their details
 */
function handleGetTeachers() {
    $search = $_GET['search'] ?? '';
    
    $sql = "SELECT 
                u.id, u.full_name, u.email, u.status, u.last_login,
                t.employee_number, t.phone, t.qualification, t.joined_date
            FROM users u
            JOIN teachers t ON u.id = t.user_id
            WHERE u.role IN ('teacher', 'headmaster')";
    
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR t.employee_number LIKE ?)";
        $search_param = "%$search%";
        $params = [$search_param, $search_param, $search_param];
        $types = "sss";
    }
    
    $sql .= " ORDER BY u.full_name";
    
    $teachers = executeQuery($sql, $types, $params);
    
    echo json_encode([
        'success' => true,
        'data' => $teachers ?: []
    ]);
}

/**
 * Assign class teacher
 */
function handleAssignClassTeacher() {
    global $conn;
    
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $class_id = (int)($_POST['class_id'] ?? 0);
    $section_id = (int)($_POST['section_id'] ?? 0);
    
    if (!$teacher_id || !$class_id || !$section_id) {
        throw new Exception('Teacher, class, and section are required');
    }
    
    // Verify teacher exists
    $teacher_check = executeQuery(
        "SELECT u.full_name FROM users u JOIN teachers t ON u.id = t.user_id WHERE u.id = ?",
        "i",
        [$teacher_id]
    );
    
    if (empty($teacher_check)) {
        throw new Exception('Invalid teacher selected');
    }
    
    // Verify section exists and belongs to the class
    $section_check = executeQuery(
        "SELECT s.name as section_name, c.name as class_name 
         FROM sections s 
         JOIN classes c ON s.class_id = c.id 
         WHERE s.id = ? AND s.class_id = ?",
        "ii",
        [$section_id, $class_id]
    );
    
    if (empty($section_check)) {
        throw new Exception('Invalid class/section combination');
    }
    
    // Check if section already has a class teacher
    $existing_assignment = executeQuery(
        "SELECT class_teacher_user_id FROM sections WHERE id = ?",
        "i",
        [$section_id]
    );
    
    if (!empty($existing_assignment) && $existing_assignment[0]['class_teacher_user_id']) {
        if ($existing_assignment[0]['class_teacher_user_id'] == $teacher_id) {
            throw new Exception('This teacher is already assigned as class teacher for this section');
        }
        // Will update the assignment
    }
      // Update section with new class teacher
    $result = executeQuery(
        "UPDATE sections SET class_teacher_user_id = ? WHERE id = ?",
        "ii",
        [$teacher_id, $section_id]
    );
    
    if (!$result) {
        throw new Exception('Failed to assign class teacher');
    }
    
    // Log the action
    $teacher_name = $teacher_check[0]['full_name'];
    $class_name = $section_check[0]['class_name'];
    $section_name = $section_check[0]['section_name'];
    
    logActivity("Assigned class teacher: $teacher_name to $class_name - $section_name");
    
    echo json_encode([
        'success' => true,
        'message' => 'Class teacher assigned successfully'
    ]);
}

/**
 * Get current class teacher assignments
 */
function handleGetClassAssignments() {
    $assignments = executeQuery("
        SELECT 
            s.id as section_id,
            s.name as section_name,
            c.id as class_id,
            c.name as class_name,
            u.id as teacher_id,
            u.full_name as teacher_name,
            t.employee_number
        FROM sections s
        JOIN classes c ON s.class_id = c.id
        LEFT JOIN users u ON s.class_teacher_user_id = u.id
        LEFT JOIN teachers t ON u.id = t.user_id
        WHERE s.class_teacher_user_id IS NOT NULL
        ORDER BY c.name, s.name
    ");
    
    echo json_encode([
        'success' => true,
        'data' => $assignments ?: []
    ]);
}

/**
 * Update teacher subject assignments
 */
function handleUpdateSubjectAssignments() {
    global $conn;
    
    $teacher_id = (int)($_POST['teacher_id'] ?? 0);
    $subject_ids = json_decode($_POST['subject_ids'] ?? '[]', true);
    
    if (!$teacher_id) {
        throw new Exception('Teacher ID is required');
    }
    
    // Verify teacher exists
    $teacher_check = executeQuery(
        "SELECT u.full_name FROM users u WHERE u.id = ? AND u.role IN ('teacher', 'headmaster')",
        "i",
        [$teacher_id]
    );
    
    if (empty($teacher_check)) {
        throw new Exception('Invalid teacher selected');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete existing assignments
        executeQuery(
            "DELETE FROM teacher_subjects WHERE teacher_user_id = ?",
            "i",
            [$teacher_id]
        );
        
        // Insert new assignments
        if (!empty($subject_ids)) {
            foreach ($subject_ids as $subject_id) {
                $subject_id = (int)$subject_id;
                if ($subject_id > 0) {
                    executeQuery(
                        "INSERT INTO teacher_subjects (teacher_user_id, subject_id) VALUES (?, ?)",
                        "ii",
                        [$teacher_id, $subject_id]
                    );
                }
            }
        }
        
        $conn->commit();
        
        // Log the action
        $teacher_name = $teacher_check[0]['full_name'];
        $subject_count = count($subject_ids);
        logActivity("Updated subject assignments for $teacher_name ($subject_count subjects)");
        
        echo json_encode([
            'success' => true,
            'message' => 'Subject assignments updated successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Get teacher's current subject assignments
 */
function handleGetTeacherSubjects() {
    $teacher_id = (int)($_GET['teacher_id'] ?? 0);
    
    if (!$teacher_id) {
        throw new Exception('Teacher ID is required');
    }
    
    // Get assigned subject IDs
    $assignments = executeQuery(
        "SELECT subject_id FROM teacher_subjects WHERE teacher_user_id = ?",
        "i",
        [$teacher_id]
    );
    
    $subject_ids = array_column($assignments ?: [], 'subject_id');
    
    // Get subject details
    $subjects_details = [];
    if (!empty($subject_ids)) {
        $placeholders = str_repeat('?,', count($subject_ids) - 1) . '?';
        $subjects_details = executeQuery(
            "SELECT id, name, code FROM subjects WHERE id IN ($placeholders) ORDER BY name",
            str_repeat('i', count($subject_ids)),
            $subject_ids
        );
    }
    
    echo json_encode([
        'success' => true,
        'data' => $subject_ids,
        'subjects_details' => $subjects_details ?: []
    ]);
}

/**
 * Get system statistics
 */
function handleGetStatistics() {
    // Total teachers
    $total_teachers = executeQuery("SELECT COUNT(*) as count FROM users WHERE role IN ('teacher', 'headmaster')");
    $total_teachers = $total_teachers[0]['count'] ?? 0;
    
    // Active teachers
    $active_teachers = executeQuery("SELECT COUNT(*) as count FROM users WHERE role IN ('teacher', 'headmaster') AND status = 'active'");
    $active_teachers = $active_teachers[0]['count'] ?? 0;
    
    // Classes with assigned teachers
    $assigned_classes = executeQuery("SELECT COUNT(DISTINCT class_id) as count FROM sections WHERE class_teacher_user_id IS NOT NULL");
    $assigned_classes = $assigned_classes[0]['count'] ?? 0;
    
    // Total subject assignments
    $subject_assignments = executeQuery("SELECT COUNT(*) as count FROM teacher_subjects");
    $subject_assignments = $subject_assignments[0]['count'] ?? 0;
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_teachers' => $total_teachers,
            'active_teachers' => $active_teachers,
            'assigned_classes' => $assigned_classes,
            'subject_assignments' => $subject_assignments
        ]
    ]);
}

/**
 * Reassign class teacher
 */
function handleReassignClassTeacher() {
    $section_id = (int)($_POST['section_id'] ?? 0);
    $new_teacher_id = (int)($_POST['new_teacher_id'] ?? 0);
    
    if (!$section_id || !$new_teacher_id) {
        throw new Exception('Section ID and new teacher ID are required');
    }
      // Update assignment
    $result = executeQuery(
        "UPDATE sections SET class_teacher_user_id = ? WHERE id = ?",
        "ii",
        [$new_teacher_id, $section_id]
    );
    
    if (!$result) {
        throw new Exception('Failed to reassign class teacher');
    }
    
    logActivity("Reassigned class teacher for section ID: $section_id");
    
    echo json_encode([
        'success' => true,
        'message' => 'Class teacher reassigned successfully'
    ]);
}

/**
 * Log activity
 */
function logActivity($description) {
    global $user_id;
    
    // For now, use simplified logging with the existing audit_logs structure
    // Map activity types to appropriate ENUM values
    $action = 'UPDATE'; // Default to UPDATE for most teacher management actions
    
    if (strpos($description, 'Added') !== false) {
        $action = 'INSERT';
    } elseif (strpos($description, 'Removed') !== false || strpos($description, 'Deleted') !== false) {
        $action = 'DELETE';
    }
    
    executeQuery(
        "INSERT INTO audit_logs (user_id, action, table_name, timestamp) VALUES (?, ?, 'teachers', NOW())",
        "is",
        [$user_id, $action]
    );
}
?>
