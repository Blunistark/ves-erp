<?php
/**
 * Teacher Management API Backend - Admin Dashboard
 * Enhanced with timetable management functionality
 * Working implementation using executeQuery() pattern
 */

require_once __DIR__ . '/../../includes/functions.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check authentication and role
if (!isLoggedIn() || !hasRole(['admin', 'headmaster'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
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
        case 'add_teacher':
            handleAddTeacher();
            break;
            
        case 'get_teachers':
            handleGetTeachers();
            break;
            
        case 'get_available_teachers':
            handleGetAvailableTeachers();
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
            
        case 'bulk_assignment':
            handleBulkAssignment();
            break;
            
        case 'get_statistics':
            handleGetStatistics();
            break;
            
        case 'get_sections':
            handleGetSections();
            break;
            
        case 'check_conflicts':
            handleCheckConflicts();
            break;        case 'reassign_class_teacher':
            if (!isset($_POST['section_id']) || !isset($_POST['teacher_id'])) {
                echo json_encode(['success' => false, 'message' => 'Section ID and Teacher ID are required']);
                exit;
            }
            
            $section_id = (int)$_POST['section_id'];
            $teacher_id = (int)$_POST['teacher_id'];
            $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
            
            try {
                // Validate that teacher exists and is active
                $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE id = ? AND role IN ('teacher', 'headmaster') AND status = 'active'");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("i", $teacher_id);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                $teacher = $result->fetch_assoc();
                $stmt->close();
                
                if (!$teacher) {
                    echo json_encode(['success' => false, 'message' => 'Invalid teacher selected']);
                    exit;
                }
                
                // Validate that section exists
                $stmt = $conn->prepare("SELECT id, name FROM sections WHERE id = ?");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("i", $section_id);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                $section = $result->fetch_assoc();
                $stmt->close();
                
                if (!$section) {
                    echo json_encode(['success' => false, 'message' => 'Invalid section selected']);
                    exit;
                }
                
                // Update the section's class teacher
                $stmt = $conn->prepare("UPDATE sections SET class_teacher_user_id = ? WHERE id = ?");
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("ii", $teacher_id, $section_id);
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                $stmt->close();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Class teacher reassigned successfully',
                    'data' => [
                        'teacher_name' => $teacher['full_name'],
                        'section_name' => $section['name']
                    ]
                ]);
                
            } catch (Exception $e) {
                error_log("Error reassigning class teacher: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Failed to reassign class teacher: ' . $e->getMessage()]);
            }
            break;
        case 'delete_teacher':
            handleDeleteTeacher();
            break;
            
        case 'update_teacher_status':
            handleUpdateTeacherStatus();
            break;
            
        case 'get_teacher_workload':
            handleGetTeacherWorkload();
            break;
              case 'remove_class_teacher':
            handleRemoveClassTeacher();
            break;
            
        case 'get_teachers_with_workload':
            handleGetTeachersWithWorkload();
            break;
            
        // Timetable Management Actions
        case 'get_timetable_conflicts':
            handleGetTimetableConflicts();
            break;
              case 'get_teacher_timetables':
            handleGetTeacherTimetables();
            break;
            
        case 'get_teacher_schedules':
            handleGetTeacherSchedules();
            break;
            
        case 'get_class_timetable_status':
            handleGetClassTimetableStatus();
            break;
            
        case 'auto_resolve_conflicts':
            handleAutoResolveConflicts();
            break;
            
        case 'resolve_single_conflict':
            handleResolveSingleConflict();
            break;
            
        case 'bulk_timetable_update':
            handleBulkTimetableUpdate();
            break;
            
        case 'get_classes_with_sections':
            handleGetClassesWithSections();
            break;
        case 'get_all_subject_assignments':
            handleGetAllSubjectAssignments();
            break;
              case 'remove_subject_assignment':
            handleRemoveSubjectAssignment();
            break;
            
        // Individual Teacher Schedule Management Actions
        case 'get_teacher_schedule':
            handleGetTeacherSchedule();
            break;
            
        case 'update_teacher_period':
            handleUpdateTeacherPeriod();
            break;
            
        case 'check_teacher_conflicts':
            handleCheckTeacherConflicts();
            break;
            
        case 'get_available_slots':
            handleGetAvailableSlots();
            break;
            
        case 'bulk_assign_teacher_periods':
            handleBulkAssignTeacherPeriods();
            break;
              case 'delete_teacher_period':
            handleDeleteTeacherPeriod();
            break;
            
        case 'get_published_timetables':
            handleGetPublishedTimetables();
            break;
              case 'get_subjects':
            handleGetSubjects();
            break;
            
        case 'save_teacher_period':
            handleSaveTeacherPeriod();
            break;
            
        case 'delete_teacher_period':
            handleDeleteTeacherPeriod();
            break;
            
        case 'check_schedule_conflicts':
            handleCheckScheduleConflicts();
            break;
            
        case 'get_classes':
            handleGetClasses();
            break;        case 'get_section_info':
            if (!isset($_GET['section_id'])) {
                echo json_encode(['success' => false, 'message' => 'Section ID is required']);
                exit;
            }
            
            $section_id = (int)$_GET['section_id'];
            
            try {
                $sql = "
                    SELECT 
                        s.id as section_id,
                        s.name as section_name,
                        c.name as class_name,
                        c.id as class_id,
                        s.class_teacher_user_id as current_teacher_id,
                        u.full_name as current_teacher
                    FROM sections s
                    JOIN classes c ON s.class_id = c.id
                    LEFT JOIN users u ON s.class_teacher_user_id = u.id
                    WHERE s.id = ?
                ";
                
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt->bind_param("i", $section_id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row) {
                    echo json_encode(['success' => true, 'data' => $row]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Section not found']);
                }
                
                $stmt->close();
                
            } catch (Exception $e) {
                error_log("Error fetching section info: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
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
 * Get all teachers with filtering options
 */
function handleGetTeachers() {
    $sql = "SELECT 
                u.id, 
                u.full_name, 
                u.email, 
                t.phone, 
                t.employee_number,
                u.status,
                u.created_at,
                COUNT(s.id) as class_count,
                GROUP_CONCAT(DISTINCT CONCAT(c.name, ' - ', s.name) ORDER BY c.name SEPARATOR '<br>') as classes
            FROM users u
            LEFT JOIN teachers t ON u.id = t.user_id
            LEFT JOIN sections s ON u.id = s.class_teacher_user_id
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE u.role = 'teacher'
            GROUP BY u.id, u.full_name, u.email, t.phone, t.employee_number, u.status, u.created_at
            ORDER BY u.full_name";
    
    $teachers = executeQuery($sql);
    
    echo json_encode([
    'success' => true,
    'data' => $teachers ?: [],
    'teachers' => $teachers ?: [] // for compatibility with code expecting response.teachers
    ]);
}

/**
 * Get available teachers for assignment
 */
function handleGetAvailableTeachers() {
    $sql = "SELECT 
                u.id, 
                u.full_name,
                COUNT(s.id) as current_classes
            FROM users u
            LEFT JOIN sections s ON u.id = s.class_teacher_user_id
            WHERE u.role = 'teacher' AND u.status = 'active'
            GROUP BY u.id, u.full_name
            ORDER BY current_classes ASC, u.full_name";
    
    $teachers = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $teachers ?: []
    ]);
}

/**
 * Add a new teacher
 */
function handleAddTeacher() {
    global $user_id;
    
    $full_name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password)) {
        throw new Exception('Name, email, and password are required');
    }
    
    // Check if email already exists
    $existing = executeQuery("SELECT id FROM users WHERE email = ?", "s", [$email]);
    if ($existing) {
        throw new Exception('Email already exists');
    }
    
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new teacher
    $sql = "INSERT INTO users (full_name, email, phone, password, role, status, created_at, created_by) 
            VALUES (?, ?, ?, ?, 'teacher', 'active', NOW(), ?)";
    
    $result = executeQuery($sql, "ssssi", [$full_name, $email, $phone, $password_hash, $user_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Teacher added successfully'
        ]);
    } else {
        throw new Exception('Failed to add teacher');
    }
}

/**
 * Assign class teacher
 */
function handleAssignClassTeacher() {
    $section_id = $_POST['section_id'] ?? null;
    $teacher_id = $_POST['teacher_id'] ?? null;
    
    if (!$section_id || !$teacher_id) {
        throw new Exception('Section and teacher are required');
    }
      // Check if section already has a teacher
    $current = executeQuery("SELECT class_teacher_user_id FROM sections WHERE id = ?", "i", [$section_id]);
    if ($current && $current[0]['class_teacher_user_id']) {
        throw new Exception('This section already has a class teacher');
    }
    
    // Assign teacher
    $sql = "UPDATE sections SET class_teacher_user_id = ? WHERE id = ?";
    $result = executeQuery($sql, "ii", [$teacher_id, $section_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Class teacher assigned successfully'
        ]);
    } else {
        throw new Exception('Failed to assign class teacher');
    }
}

/**
 * Get class assignments
 */
function handleGetClassAssignments() {
    $sql = "SELECT 
                s.id as section_id,
                c.name as class_name,
                s.name as section_name,
                u.id as teacher_id,
                u.full_name as teacher_name,
                u.status as teacher_status,
                t.employee_number
            FROM sections s
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN users u ON s.class_teacher_user_id = u.id
            LEFT JOIN teachers t ON u.id = t.user_id
            ORDER BY c.name, s.name";
    
    $assignments = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $assignments ?: []
    ]);
}

/**
 * Get teacher subjects
 */
function handleGetTeacherSubjects() {
    $teacher_id = $_GET['teacher_id'] ?? null;
    
    if (!$teacher_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Teacher ID is required'
        ]);
        return;
    }
    
    try {
        $sql = "SELECT 
                    ts.id,
                    ts.subject_id,
                    sub.name as subject_name,
                    c.name as class_name,
                    s.name as section_name
                FROM teacher_subjects ts
                JOIN subjects sub ON ts.subject_id = sub.id
                LEFT JOIN classes c ON ts.class_id = c.id
                LEFT JOIN sections s ON ts.section_id = s.id
                WHERE ts.teacher_user_id = ?
                ORDER BY sub.name, c.name, s.name";
        
        $subjects = executeQuery($sql, "i", [$teacher_id]);
        
        echo json_encode([
            'success' => true,
            'data' => $subjects ?: []
        ]);
    } catch (Exception $e) {
        error_log("Error in handleGetTeacherSubjects: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load teacher subjects: ' . $e->getMessage()
        ]);
    }
}

/**
 * Update subject assignments
 */
function handleUpdateSubjectAssignments() {
    $teacher_id = $_POST['teacher_id'] ?? null;
    $subject_ids = json_decode($_POST['subject_ids'] ?? '[]', true);
    
    if (!$teacher_id) {
        echo json_encode([
            'success' => false,
            'message' => 'Teacher ID is required'
        ]);
        return;
    }
    
    try {
        // Delete existing assignments
        executeQuery("DELETE FROM teacher_subjects WHERE teacher_user_id = ?", "i", [$teacher_id]);
        
        // Insert new assignments
        if (!empty($subject_ids)) {
            foreach ($subject_ids as $subject_id) {
                $sql = "INSERT INTO teacher_subjects (teacher_user_id, subject_id, class_id, section_id) VALUES (?, ?, NULL, NULL)";
                executeQuery($sql, "ii", [$teacher_id, intval($subject_id)]);
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Subject assignments updated successfully'
        ]);
    } catch (Exception $e) {
        error_log("Error in handleUpdateSubjectAssignments: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update subject assignments: ' . $e->getMessage()
        ]);
    }
}

/**
 * Get sections for dropdown - FIXED VERSION
 */
function handleGetSections() {
    $class_id = $_GET['class_id'] ?? null;
    
    try {
        $sql = "SELECT 
                    s.id,
                    s.name,
                    s.class_id,
                    c.name as class_name
                FROM sections s
                JOIN classes c ON s.class_id = c.id";
        
        $params = [];
        $types = "";
        
        if ($class_id) {
            $sql .= " WHERE s.class_id = ?";
            $params[] = $class_id;
            $types = "i";
        }
        
        $sql .= " ORDER BY c.name, s.name";
        
        $sections = $params ? executeQuery($sql, $types, $params) : executeQuery($sql);
        
        if ($sections === false || $sections === null) {
            throw new Exception('Database query failed');
        }
        
        if (!is_array($sections)) {
            $sections = [];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $sections,
            'sections' => $sections, // for compatibility
            'count' => count($sections)
        ]);
        
    } catch (Exception $e) {
        error_log("Error in handleGetSections: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load sections: ' . $e->getMessage(),
            'data' => [],
            'sections' => []
        ]);
    }
}

/**
 * Get teacher workload
 */
function handleGetTeacherWorkload() {
    $teacher_id = $_GET['teacher_id'] ?? null;
    
    if (!$teacher_id) {
        throw new Exception('Teacher ID is required');
    }
      // Get class teacher assignments
    $class_assignments = executeQuery(
        "SELECT COUNT(*) as count FROM sections WHERE class_teacher_user_id = ?", 
        "i", 
        [$teacher_id]
    );
      // Get subject assignments
    $subject_assignments = executeQuery(
        "SELECT COUNT(*) as count FROM teacher_subjects WHERE teacher_user_id = ?", 
        "i", 
        [$teacher_id]
    );
    
    // Get timetable periods
    $timetable_periods = executeQuery(
        "SELECT COUNT(tp.id) as count 
         FROM timetable_periods tp 
         JOIN timetables tt ON tp.timetable_id = tt.id 
         WHERE tp.teacher_id = ? AND tt.status = 'published'", 
        "i", 
        [$teacher_id]
    );
    
    $workload = [
        'class_assignments' => $class_assignments[0]['count'] ?? 0,
        'subject_assignments' => $subject_assignments[0]['count'] ?? 0,
        'timetable_periods' => $timetable_periods[0]['count'] ?? 0
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $workload
    ]);
}

/**
 * Remove class teacher
 */
function handleRemoveClassTeacher() {
    $section_id = $_POST['section_id'] ?? null;
    
    if (!$section_id) {
        throw new Exception('Section ID is required');
    }
    
    $sql = "UPDATE sections SET class_teacher_user_id = NULL WHERE id = ?";
    $result = executeQuery($sql, "i", [$section_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Class teacher removed successfully'
        ]);
    } else {
        throw new Exception('Failed to remove class teacher');
    }
}

/**
 * Delete teacher
 */
function handleDeleteTeacher() {
    $teacher_id = $_POST['teacher_id'] ?? null;
    
    if (!$teacher_id) {
        throw new Exception('Teacher ID is required');
    }
      // Check if teacher has assignments
    $assignments = executeQuery("SELECT COUNT(*) as count FROM sections WHERE class_teacher_user_id = ?", "i", [$teacher_id]);
    if ($assignments[0]['count'] > 0) {
        throw new Exception('Cannot delete teacher with active class assignments');
    }
    
    // Remove subject assignments first
    executeQuery("DELETE FROM teacher_subjects WHERE teacher_id = ?", "i", [$teacher_id]);
    
    // Delete teacher
    $sql = "DELETE FROM users WHERE id = ? AND role = 'teacher'";
    $result = executeQuery($sql, "i", [$teacher_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Teacher deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete teacher');
    }
}

/**
 * Update teacher status
 */
function handleUpdateTeacherStatus() {
    $teacher_id = $_POST['teacher_id'] ?? null;
    $status = $_POST['status'] ?? null;
    
    if (!$teacher_id || !$status) {
        throw new Exception('Teacher ID and status are required');
    }
    
    if (!in_array($status, ['active', 'inactive'])) {
        throw new Exception('Invalid status');
    }
    
    $sql = "UPDATE users SET status = ? WHERE id = ? AND role = 'teacher'";
    $result = executeQuery($sql, "si", [$status, $teacher_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Teacher status updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update teacher status');
    }
}

/**
 * Get statistics
 */
function handleGetStatistics() {
    $stats = [
        'total_teachers' => 0,
        'active_teachers' => 0,
        'assigned_teachers' => 0,
        'unassigned_teachers' => 0
    ];
    
    // Get teacher counts
    $teacher_stats = executeQuery("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
        FROM users WHERE role = 'teacher'
    ");
    
    if ($teacher_stats) {
        $stats['total_teachers'] = $teacher_stats[0]['total'];
        $stats['active_teachers'] = $teacher_stats[0]['active'];
    }
      // Get assignment counts
    $assignment_stats = executeQuery("
        SELECT COUNT(DISTINCT class_teacher_user_id) as assigned
        FROM sections WHERE class_teacher_user_id IS NOT NULL
    ");
    
    if ($assignment_stats) {
        $stats['assigned_teachers'] = $assignment_stats[0]['assigned'];
        $stats['unassigned_teachers'] = $stats['active_teachers'] - $stats['assigned_teachers'];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

/**
 * Check conflicts
 */
function handleCheckConflicts() {
    $conflicts = [];
      // Check for teachers with multiple class assignments
    $class_conflicts = executeQuery("
        SELECT 
            u.full_name as teacher_name,
            COUNT(s.id) as class_count,
            GROUP_CONCAT(CONCAT(c.name, '-', s.name) SEPARATOR ', ') as classes
        FROM users u
        JOIN sections s ON u.id = s.class_teacher_user_id
        JOIN classes c ON s.class_id = c.id
        GROUP BY u.id, u.full_name
        HAVING COUNT(s.id) > 1
    ");
    
    if ($class_conflicts) {
        foreach ($class_conflicts as $conflict) {
            $conflicts[] = [
                'type' => 'multiple_classes',
                'message' => $conflict['teacher_name'] . ' is assigned to ' . $conflict['class_count'] . ' classes: ' . $conflict['classes']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $conflicts
    ]);
}

/**
 * Bulk assignment
 */
function handleBulkAssignment() {
    $assignments = json_decode($_POST['assignments'] ?? '[]', true);
    
    if (empty($assignments)) {
        throw new Exception('No assignments provided');
    }
    
    $success_count = 0;
    $errors = [];
    
    foreach ($assignments as $assignment) {        try {
            $sql = "UPDATE sections SET class_teacher_user_id = ? WHERE id = ?";
            $result = executeQuery($sql, "ii", [$assignment['teacher_id'], $assignment['section_id']]);
            if ($result) {
                $success_count++;
            }
        } catch (Exception $e) {
            $errors[] = "Failed to assign section {$assignment['section_id']}: " . $e->getMessage();
        }
    }
    
    echo json_encode([
        'success' => true,
        'assigned' => $success_count,
        'errors' => $errors,
        'message' => "$success_count assignments completed"
    ]);
}

/**
 * Reassign class teacher
 */
function handleReassignClassTeacher() {
    $section_id = $_POST['section_id'] ?? null;
    $new_teacher_id = $_POST['new_teacher_id'] ?? null;
    
    if (!$section_id || !$new_teacher_id) {
        throw new Exception('Section and new teacher are required');
    }
      $sql = "UPDATE sections SET class_teacher_user_id = ? WHERE id = ?";
    $result = executeQuery($sql, "ii", [$new_teacher_id, $section_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Class teacher reassigned successfully'
        ]);
    } else {
        throw new Exception('Failed to reassign class teacher');
    }
}

// ========= TIMETABLE MANAGEMENT FUNCTIONS =========

/**
 * Get timetable conflicts
 */
function handleGetTimetableConflicts() {
    $sql = "
        SELECT 
            'teacher_conflict' as type,
            'critical' as severity,
            tp1.id as conflict_id,
            CONCAT('Teacher ', u.full_name, ' has overlapping periods on ', 
                   UPPER(tp1.day_of_week), ' period ', tp1.period_number, 
                   ' (', tp1.start_time, ' - ', tp1.end_time, ')') as message,
            GROUP_CONCAT(DISTINCT CONCAT(c.name, '-', s.name) SEPARATOR ' and ') as details
        FROM timetable_periods tp1
        JOIN timetable_periods tp2 ON tp1.teacher_id = tp2.teacher_id 
            AND tp1.day_of_week = tp2.day_of_week
            AND tp1.period_number = tp2.period_number
            AND tp1.id < tp2.id
        JOIN timetables tt1 ON tp1.timetable_id = tt1.id
        JOIN timetables tt2 ON tp2.timetable_id = tt2.id
        JOIN classes c ON tt1.class_id = c.id
        JOIN sections s ON tt1.section_id = s.id
        JOIN users u ON tp1.teacher_id = u.id
        WHERE tt1.status = 'published' AND tt2.status = 'published'
        GROUP BY tp1.id, tp1.teacher_id, tp1.day_of_week, tp1.period_number, tp1.start_time, tp1.end_time, u.full_name
    ";
    
    $conflicts = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $conflicts ?: []
    ]);
}

/**
 * Get teacher timetables with filtering
 */
function handleGetTeacherTimetables() {
    $teacher_id = $_GET['teacher_id'] ?? null;
    $day = $_GET['day'] ?? null;
    
    // Build WHERE clause conditions
    $where_conditions = ["u.role = 'teacher'", "u.status = 'active'"];
    $params = [];
    $types = "";
    
    if ($teacher_id) {
        $where_conditions[] = "u.id = ?";
        $params[] = $teacher_id;
        $types .= "i";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    $sql = "
        SELECT 
            u.id as teacher_id,
            u.full_name as teacher_name,
            COUNT(tp.id) as total_periods
        FROM users u
        LEFT JOIN timetable_periods tp ON u.id = tp.teacher_id
        LEFT JOIN timetables tt ON tp.timetable_id = tt.id AND tt.status = 'published'
        WHERE $where_clause
        GROUP BY u.id, u.full_name
        ORDER BY u.full_name
    ";
    
    $teachers = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    
    if ($teachers) {
        foreach ($teachers as &$teacher) {
            $teacher['schedule'] = getTeacherScheduleGrid($teacher['teacher_id'], $day);
            
            // Determine workload status
            if ($teacher['total_periods'] > 30) {
                $teacher['workload_status'] = 'overloaded';
            } elseif ($teacher['total_periods'] > 20) {
                $teacher['workload_status'] = 'heavy';
            } elseif ($teacher['total_periods'] < 10) {
                $teacher['workload_status'] = 'light';
            } else {
                $teacher['workload_status'] = 'normal';
            }
        }    }
    
    echo json_encode([
        'success' => true,
        'data' => $teachers ?: []
    ]);
}

/**
 * Get teacher schedules (admin version)
 */
function handleGetTeacherSchedules() {
    global $conn;
    
    $sql = "
        SELECT 
            u.id as teacher_id,
            u.full_name as teacher_name,
            t.employee_number
        FROM users u
        INNER JOIN teachers t ON u.id = t.user_id
        WHERE u.role = 'teacher' AND u.status = 'active'
        ORDER BY u.full_name
    ";
      $teachers = executeQuery($sql);
    
    if ($teachers) {
        foreach ($teachers as &$teacher) {
            // Get schedule for each teacher
            $schedule_sql = "
                SELECT 
                    tp.day_of_week,
                    CONCAT(tp.start_time, ' - ', tp.end_time) as time_slot,
                    c.name as class_name,
                    s.name as section_name,
                    sub.name as subject_name
                FROM timetable_periods tp
                JOIN timetables tt ON tp.timetable_id = tt.id
                JOIN classes c ON tt.class_id = c.id
                JOIN sections s ON tt.section_id = s.id
                JOIN subjects sub ON tp.subject_id = sub.id
                WHERE tp.teacher_id = ? AND tt.status = 'published'
                ORDER BY tp.day_of_week, tp.start_time
            ";
            
            $schedule = executeQuery($schedule_sql, "i", [$teacher['teacher_id']]);
            $teacher['schedule'] = $schedule ?: [];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $teachers ?: []
    ]);
}

/**
 * Get teacher schedule grid for display
 */
function getTeacherScheduleGrid($teacher_id, $day_filter = null) {
    $where_conditions = ["tp.teacher_id = ?", "tt.status = 'published'"];
    $params = [$teacher_id];
    $types = "i";
    
    if ($day_filter) {
        $where_conditions[] = "tp.day_of_week = ?";
        $params[] = $day_filter;
        $types .= "s";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    $sql = "
        SELECT 
            tp.day_of_week,
            tp.period_number,
            sub.name as subject,
            CONCAT(c.name, '-', sec.name) as class,
            tp.start_time,
            tp.end_time,
            CASE 
                WHEN EXISTS (
                    SELECT 1 FROM timetable_periods tp2
                    JOIN timetables tt2 ON tp2.timetable_id = tt2.id
                    WHERE tp2.teacher_id = tp.teacher_id
                    AND tp2.day_of_week = tp.day_of_week 
                    AND tp2.period_number = tp.period_number
                    AND tp2.id != tp.id
                    AND tt2.status = 'published'
                ) THEN 1 ELSE 0
            END as conflict
        FROM timetable_periods tp
        JOIN timetables tt ON tp.timetable_id = tt.id
        JOIN subjects sub ON tp.subject_id = sub.id
        JOIN classes c ON tt.class_id = c.id
        JOIN sections sec ON tt.section_id = sec.id
        WHERE $where_clause
        ORDER BY 
            FIELD(tp.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
            tp.period_number
    ";
    
    $periods = executeQuery($sql, $types, $params);
    $schedule = [];
    
    if ($periods) {
        foreach ($periods as $period) {
            $day = $period['day_of_week'];
            $period_num = $period['period_number'];
            
            if (!isset($schedule[$day])) {
                $schedule[$day] = [];
            }
            
            $schedule[$day][$period_num] = [
                'subject' => $period['subject'],
                'class' => $period['class'],
                'time' => $period['start_time'] . '-' . $period['end_time'],
                'conflict' => (bool)$period['conflict']
            ];
        }
    }
    
    return $schedule;
}

/**
 * Get class timetable completion status
 */
function handleGetClassTimetableStatus() {
    $sql = "
        SELECT 
            c.id as class_id,
            c.name as class_name,
            sec.name as section_name,
            tt.id as timetable_id,
            COALESCE(tt.status, 'none') as status,
            COUNT(tp.id) as filled_periods,
            40 as total_periods,
            ROUND((COUNT(tp.id) / 40) * 100) as completion_percentage
        FROM classes c
        JOIN sections sec ON c.id = sec.class_id
        LEFT JOIN timetables tt ON c.id = tt.class_id AND sec.id = tt.section_id
        LEFT JOIN timetable_periods tp ON tt.id = tp.timetable_id
        GROUP BY c.id, c.name, sec.name, tt.id, tt.status
        ORDER BY c.name, sec.name
    ";
    
    $classes = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $classes ?: []
    ]);
}

/**
 * Auto-resolve timetable conflicts
 */
function handleAutoResolveConflicts() {
    try {
        // Find all teacher conflicts
        $conflicts = executeQuery("
            SELECT 
                tp1.id as period1_id,
                tp2.id as period2_id,
                tp1.teacher_id,
                u.full_name as teacher_name,
                tp1.day_of_week,
                tp1.period_number
            FROM timetable_periods tp1
            JOIN timetable_periods tp2 ON tp1.teacher_id = tp2.teacher_id 
                AND tp1.day_of_week = tp2.day_of_week
                AND tp1.period_number = tp2.period_number
                AND tp1.id < tp2.id
            JOIN timetables tt1 ON tp1.timetable_id = tt1.id
            JOIN timetables tt2 ON tp2.timetable_id = tt2.id
            JOIN users u ON tp1.teacher_id = u.id
            WHERE tt1.status = 'published' AND tt2.status = 'published'
        ");
        
        $resolved_count = 0;
        
        if (!empty($conflicts)) {
            foreach ($conflicts as $conflict) {
                // Strategy: Remove the second occurrence (later created)
                $result = executeQuery(
                    "DELETE FROM timetable_periods WHERE id = ?",
                    "i",
                    [$conflict['period2_id']]
                );
                if ($result) {
                    $resolved_count++;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'resolved_count' => $resolved_count,
            'message' => "$resolved_count conflicts resolved successfully"
        ]);
        
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Resolve single conflict
 */
function handleResolveSingleConflict() {
    $conflict_id = $_POST['conflict_id'] ?? null;
    
    if (!$conflict_id) {
        throw new Exception('Conflict ID is required');
    }
    
    $result = executeQuery(
        "DELETE FROM timetable_periods WHERE id = ?",
        "i",
        [$conflict_id]
    );
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Conflict resolved successfully'
        ]);
    } else {
        throw new Exception('Failed to resolve conflict');
    }
}

/**
 * Handle bulk timetable updates
 */
function handleBulkTimetableUpdate() {
    $bulk_action = $_POST['bulk_action'] ?? null;
    $class_ids = json_decode($_POST['class_ids'] ?? '[]', true);
    
    if (!$bulk_action || empty($class_ids)) {
        throw new Exception('Bulk action and class selection required');
    }
    
    $updated_count = 0;
    
    switch ($bulk_action) {
        case 'clear_periods':
            foreach ($class_ids as $class_id) {
                $sql = "
                    DELETE tp FROM timetable_periods tp
                    JOIN timetables tt ON tp.timetable_id = tt.id
                    WHERE tt.class_id = ?
                ";
                $result = executeQuery($sql, "i", [$class_id]);
                if ($result) {
                    $updated_count++;
                }
            }
            break;
            
        default:
            throw new Exception('Invalid bulk action');
    }
    
    echo json_encode([
        'success' => true,
        'updated_count' => $updated_count,
        'message' => "Bulk update completed successfully"
    ]);
}

/**
 * Get classes with sections for bulk operations
 */
function handleGetClassesWithSections() {
    $sql = "
        SELECT 
            c.id,
            c.name,
            s.name as section_name,
            CASE 
                WHEN tt.id IS NOT NULL THEN 'has_timetable'
                ELSE 'no_timetable'
            END as timetable_status
        FROM classes c
        JOIN sections s ON c.id = s.class_id
        LEFT JOIN timetables tt ON c.id = tt.class_id AND s.id = tt.section_id AND tt.status = 'published'
        ORDER BY c.name, s.name
    ";
    
    $classes = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $classes ?: []
    ]);
}

/**
 * Get teachers with comprehensive workload information
 */
function handleGetTeachersWithWorkload() {
    $sql = "SELECT 
                u.id,
                u.full_name,
                u.email,
                t.phone,
                u.status as user_status,
                t.employee_number,
                t.qualification,
                t.department,
                t.position,
                t.experience_years,
                -- Class teacher assignments
                COUNT(DISTINCT s.id) as class_teacher_count,
                GROUP_CONCAT(DISTINCT CONCAT(c.name, '-', s.name) ORDER BY c.name, s.name SEPARATOR ', ') as class_teacher_assignments,
                -- Subject specializations
                GROUP_CONCAT(DISTINCT sub.name ORDER BY sub.name SEPARATOR ', ') as subject_specializations,
                -- Timetable workload
                COUNT(DISTINCT tp.id) as timetable_periods_count
            FROM users u
            INNER JOIN teachers t ON u.id = t.user_id
            LEFT JOIN sections s ON u.id = s.class_teacher_user_id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_user_id
            LEFT JOIN subjects sub ON ts.subject_id = sub.id
            LEFT JOIN timetable_periods tp ON u.id = tp.teacher_id
            WHERE u.role = 'teacher'
            GROUP BY u.id, u.full_name, u.email, t.phone, u.status, t.employee_number, t.qualification, t.department, t.position, t.experience_years
            ORDER BY u.full_name";
    
    $teachers = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $teachers ?: []
    ]);
}

/**
 * Add this new function to your teacher_management_api.php file
 * This function gets all subject assignments for all teachers
 */

/**
 * Get all subject assignments for all teachers
 */
function handleGetAllSubjectAssignments() {
    $search = $_GET['search'] ?? '';
    $teacher_id = $_GET['teacher_id'] ?? null;
    $subject_id = $_GET['subject_id'] ?? null;
    
    // Build WHERE clause for filtering
    $where_conditions = ["u.role = 'teacher'"];
    $params = [];
    $types = "";
    
    if (!empty($search)) {
        $where_conditions[] = "(u.full_name LIKE ? OR t.employee_number LIKE ? OR u.email LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
        $types .= "sss";
    }
    
    if ($teacher_id) {
        $where_conditions[] = "u.id = ?";
        $params[] = $teacher_id;
        $types .= "i";
    }
    
    if ($subject_id) {
        $where_conditions[] = "ts.subject_id = ?";
        $params[] = $subject_id;
        $types .= "i";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
      $sql = "SELECT 
                u.id as teacher_id,
                u.full_name as teacher_name,
                u.email as teacher_email,
                t.employee_number,
                u.status as teacher_status,
                ts.id as assignment_id,
                ts.subject_id,
                sub.name as subject_name,
                sub.code as subject_code,
                c.name as class_name,
                s.name as section_name,
                u.created_at as assignment_date,
                CASE 
                    WHEN c.id IS NOT NULL AND s.id IS NOT NULL 
                    THEN CONCAT(c.name, ' - ', s.name)
                    ELSE 'All Classes'
                END as assignment_scope
            FROM users u
            INNER JOIN teachers t ON u.id = t.user_id
            LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_user_id
            LEFT JOIN subjects sub ON ts.subject_id = sub.id
            LEFT JOIN classes c ON ts.class_id = c.id
            LEFT JOIN sections s ON ts.section_id = s.id
            WHERE $where_clause
            ORDER BY u.full_name, sub.name, c.name, s.name";
    
    $assignments = !empty($params) ? executeQuery($sql, $types, $params) : executeQuery($sql);
    
    // Group assignments by teacher for better display
    $grouped_assignments = [];
    $teacher_summary = [];
    
    if ($assignments) {
        foreach ($assignments as $assignment) {
            $teacher_id = $assignment['teacher_id'];
            
            // Initialize teacher if not exists
            if (!isset($grouped_assignments[$teacher_id])) {
                $grouped_assignments[$teacher_id] = [
                    'teacher_info' => [
                        'id' => $assignment['teacher_id'],
                        'name' => $assignment['teacher_name'],
                        'email' => $assignment['teacher_email'],
                        'employee_number' => $assignment['employee_number'],
                        'status' => $assignment['teacher_status']
                    ],
                    'assignments' => []
                ];
                
                $teacher_summary[$teacher_id] = [
                    'teacher_name' => $assignment['teacher_name'],
                    'employee_number' => $assignment['employee_number'],
                    'total_assignments' => 0,
                    'subjects_count' => 0,
                    'subjects_list' => []
                ];
            }
            
            // Add assignment if subject exists
            if ($assignment['subject_id']) {
                $grouped_assignments[$teacher_id]['assignments'][] = [
                    'assignment_id' => $assignment['assignment_id'],
                    'subject_id' => $assignment['subject_id'],
                    'subject_name' => $assignment['subject_name'],
                    'subject_code' => $assignment['subject_code'],
                    'class_name' => $assignment['class_name'],
                    'section_name' => $assignment['section_name'],
                    'assignment_scope' => $assignment['assignment_scope'],
                    'assignment_date' => $assignment['assignment_date']
                ];
                
                // Update summary
                $teacher_summary[$teacher_id]['total_assignments']++;
                if (!in_array($assignment['subject_name'], $teacher_summary[$teacher_id]['subjects_list'])) {
                    $teacher_summary[$teacher_id]['subjects_list'][] = $assignment['subject_name'];
                    $teacher_summary[$teacher_id]['subjects_count']++;
                }
            }
        }
    }
      // Get statistics
    $all_subjects = [];
    foreach ($teacher_summary as $teacher) {
        if (!empty($teacher['subjects_list'])) {
            $all_subjects = array_merge($all_subjects, $teacher['subjects_list']);
        }
    }
    
    $stats = [
        'total_teachers' => count($grouped_assignments),
        'teachers_with_assignments' => count(array_filter($grouped_assignments, function($teacher) {
            return !empty($teacher['assignments']);
        })),
        'total_assignments' => array_sum(array_column($teacher_summary, 'total_assignments')),
        'unique_subjects' => count(array_unique($all_subjects))
    ];
      echo json_encode([
        'success' => true,
        'data' => [
            'assignments' => array_values($grouped_assignments),
            'summary' => array_values($teacher_summary),
            'statistics' => $stats
        ]
    ]);
}

/**
 * Remove specific subject assignment
 */
function handleRemoveSubjectAssignment() {
    $assignment_id = $_POST['assignment_id'] ?? null;
    
    if (!$assignment_id) {
        throw new Exception('Assignment ID is required');
    }
    
    // Get assignment details before deletion for logging
    $assignment = executeQuery(
        "SELECT ts.*, u.full_name as teacher_name, sub.name as subject_name 
         FROM teacher_subjects ts 
         JOIN users u ON ts.teacher_user_id = u.id 
         JOIN subjects sub ON ts.subject_id = sub.id 
         WHERE ts.id = ?", 
        "i", 
        [$assignment_id]
    );
    
    if (!$assignment) {
        throw new Exception('Assignment not found');
    }
    
    $result = executeQuery("DELETE FROM teacher_subjects WHERE id = ?", "i", [$assignment_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Subject assignment removed successfully',
            'removed_assignment' => $assignment[0]
        ]);
    } else {
        throw new Exception('Failed to remove subject assignment');
    }
}

/**
 * Add this case to your main switch statement in teacher_management_api.php:
 */

// Add these cases to your existing switch statement:

// ========= INDIVIDUAL TEACHER SCHEDULE MANAGEMENT FUNCTIONS =========

/**
 * Get individual teacher's complete schedule
 */
function handleGetTeacherSchedule() {
    $teacher_id = $_POST['teacher_id'] ?? $_GET['teacher_id'] ?? null;
    
    if (!$teacher_id) {
        throw new Exception('Teacher ID is required');
    }
    
    try {
        // First, get teacher information
        $teacher_sql = "
            SELECT u.id, u.full_name, u.email, t.employee_number, t.department, t.position
            FROM users u 
            LEFT JOIN teachers t ON u.id = t.user_id 
            WHERE u.id = ? AND u.role IN ('teacher', 'headmaster')
        ";
        $teacher_info = executeQuery($teacher_sql, "i", [$teacher_id]);
        
        if (empty($teacher_info)) {
            throw new Exception('Teacher not found');
        }
        
        // Check if timetable_periods table exists and get schedule
        $schedule_sql = "
            SELECT 
                tp.id as period_id,
                tp.day_of_week,
                tp.period_number,
                tp.start_time,
                tp.end_time,
                tp.notes,
                COALESCE(sub.name, 'Unassigned Subject') as subject_name,
                COALESCE(sub.code, '') as subject_code,
                COALESCE(sub.id, 0) as subject_id,
                COALESCE(c.name, 'Unknown Class') as class_name,
                COALESCE(sec.name, 'Unknown Section') as section_name,
                COALESCE(c.id, 0) as class_id,
                COALESCE(sec.id, 0) as section_id,
                tt.id as timetable_id,
                COALESCE(tt.description, 'Timetable') as timetable_description,
                COALESCE(tt.status, 'active') as timetable_status,
                0 as has_conflict
            FROM timetable_periods tp
            LEFT JOIN timetables tt ON tp.timetable_id = tt.id
            LEFT JOIN subjects sub ON tp.subject_id = sub.id
            LEFT JOIN classes c ON tt.class_id = c.id
            LEFT JOIN sections sec ON tt.section_id = sec.id
            WHERE tp.teacher_id = ?
            ORDER BY 
                FIELD(tp.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
                tp.period_number
        ";
        
        $schedule_periods = executeQuery($schedule_sql, "i", [$teacher_id]);
        
        // If no periods found, try alternative approach - check if table exists
        if (empty($schedule_periods)) {
            // Check if we have any schedule data at all
            $check_sql = "SHOW TABLES LIKE 'timetable_periods'";
            $table_exists = executeQuery($check_sql);
            
            if (empty($table_exists)) {
                // Table doesn't exist, return empty schedule
                $schedule_periods = [];
            } else {
                // Table exists but no data for this teacher
                $schedule_periods = [];
            }
        }
        
        // Structure schedule by day and period
        $structured_schedule = [];
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        // Initialize all days
        foreach ($days as $day) {
            $structured_schedule[$day] = [];
        }
        
        // Populate with actual schedule data
        if (!empty($schedule_periods)) {
            foreach ($schedule_periods as $period) {
                $day = strtolower($period['day_of_week']);
                if (!in_array($day, $days)) {
                    continue; // Skip invalid days
                }
                
                $period_data = [
                    'period' => (int)$period['period_number'],
                    'period_id' => $period['period_id'],
                    'subject_name' => $period['subject_name'] ?: 'Free Period',
                    'subject_code' => $period['subject_code'] ?: '',
                    'subject_id' => $period['subject_id'] ?: null,
                    'class_info' => trim($period['class_name'] . ' - ' . $period['section_name']),
                    'class_id' => $period['class_id'] ?: null,
                    'section_id' => $period['section_id'] ?: null,
                    'notes' => $period['notes'] ?: '',
                    'start_time' => $period['start_time'] ?: '',
                    'end_time' => $period['end_time'] ?: '',
                    'has_conflict' => (bool)($period['has_conflict'] ?? false)
                ];
                
                $structured_schedule[$day][] = $period_data;
            }
        }
        
        // Sort periods within each day
        foreach ($structured_schedule as $day => &$periods) {
            if (!empty($periods)) {
                usort($periods, function($a, $b) {
                    return $a['period'] <=> $b['period'];
                });
            }
        }
        
        echo json_encode([
            'success' => true,
            'schedule' => $structured_schedule,
            'teacher_info' => $teacher_info[0],
            'total_periods' => count($schedule_periods),
            'message' => count($schedule_periods) > 0 ? 
                'Schedule loaded successfully' : 
                'No schedule found for this teacher'
        ]);
        
    } catch (Exception $e) {
        error_log("Error in handleGetTeacherSchedule: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load teacher schedule: ' . $e->getMessage(),
            'schedule' => [
                'monday' => [], 'tuesday' => [], 'wednesday' => [], 
                'thursday' => [], 'friday' => [], 'saturday' => [], 'sunday' => []
            ],
            'teacher_info' => null,
            'total_periods' => 0
        ]);
    }
}

/**
 * Update or assign individual teacher period
 */
function handleUpdateTeacherPeriod() {    $teacher_id = $_POST['teacher_id'] ?? null;
    $period_id = $_POST['period_id'] ?? null; // null for new period
    $timetable_id = $_POST['timetable_id'] ?? null;
    $day_of_week = $_POST['day_of_week'] ?? null;
    $period_number = $_POST['period_number'] ?? null;
    $start_time = $_POST['start_time'] ?? null;
    $end_time = $_POST['end_time'] ?? null;
    $subject_id = $_POST['subject_id'] ?? null;
    $notes = $_POST['notes'] ?? null;
    
    // Validation
    if (!$teacher_id || !$timetable_id || !$day_of_week || !$period_number || !$start_time || !$end_time || !$subject_id) {
        throw new Exception('Required fields: teacher_id, timetable_id, day_of_week, period_number, start_time, end_time, subject_id');
    }
    
    // Check for conflicts before saving
    $conflict_check_sql = "
        SELECT COUNT(*) as conflicts
        FROM timetable_periods tp
        JOIN timetables tt ON tp.timetable_id = tt.id
        WHERE tp.teacher_id = ? 
        AND tp.day_of_week = ? 
        AND tp.period_number = ?
        AND tt.status = 'published'
        AND tp.id != COALESCE(?, 0)
    ";
    
    $conflicts = executeQuery($conflict_check_sql, "isii", [$teacher_id, $day_of_week, $period_number, $period_id]);
    
    if ($conflicts && $conflicts[0]['conflicts'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Conflict detected: Teacher already has a class at this time',
            'conflict' => true
        ]);
        return;
    }
    
    if ($period_id) {
        // Update existing period
        $sql = "
            UPDATE timetable_periods 
            SET day_of_week = ?, period_number = ?, start_time = ?, end_time = ?, 
                subject_id = ?, notes = ?
            WHERE id = ? AND teacher_id = ?
        ";
        $result = executeQuery($sql, "sisssi", [
            $day_of_week, $period_number, $start_time, $end_time, 
            $subject_id, $notes, $period_id, $teacher_id
        ]);
    } else {
        // Create new period
        $sql = "
            INSERT INTO timetable_periods 
            (timetable_id, day_of_week, period_number, start_time, end_time, 
             subject_id, teacher_id, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $result = executeQuery($sql, "isisssis", [
            $timetable_id, $day_of_week, $period_number, $start_time, $end_time, 
            $subject_id, $teacher_id, $notes
        ]);
    }
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => $period_id ? 'Period updated successfully' : 'Period created successfully',
            'period_id' => $period_id ?: $result
        ]);
    } else {
        throw new Exception('Failed to save period');
    }
}

/**
 * Check for teacher conflicts in real-time
 */
function handleCheckTeacherConflicts() {
    $teacher_id = $_GET['teacher_id'] ?? null;
    $day_of_week = $_GET['day_of_week'] ?? null;
    $period_number = $_GET['period_number'] ?? null;
    $exclude_period_id = $_GET['exclude_period_id'] ?? null;
    
    if (!$teacher_id || !$day_of_week || !$period_number) {
        throw new Exception('Required parameters: teacher_id, day_of_week, period_number');
    }
    
    $sql = "
        SELECT 
            tp.id as period_id,
            tp.start_time,
            tp.end_time,
            sub.name as subject_name,
            c.name as class_name,
            sec.name as section_name,
            tt.academic_year,
            tt.term
        FROM timetable_periods tp
        JOIN timetables tt ON tp.timetable_id = tt.id
        JOIN subjects sub ON tp.subject_id = sub.id
        JOIN classes c ON tt.class_id = c.id
        JOIN sections sec ON tt.section_id = sec.id
        WHERE tp.teacher_id = ? 
        AND tp.day_of_week = ? 
        AND tp.period_number = ?
        AND tt.status = 'published'
        AND tp.id != COALESCE(?, 0)
    ";
    
    $conflicts = executeQuery($sql, "isii", [$teacher_id, $day_of_week, $period_number, $exclude_period_id]);
    
    echo json_encode([
        'success' => true,
        'has_conflict' => !empty($conflicts),
        'conflicts' => $conflicts ?: [],
        'message' => empty($conflicts) ? 'No conflicts found' : 'Conflicts detected'
    ]);
}

/**
 * Get available time slots for teacher
 */
function handleGetAvailableSlots() {
    $teacher_id = $_GET['teacher_id'] ?? null;
    $day_of_week = $_GET['day_of_week'] ?? null;
    
    if (!$teacher_id) {
        throw new Exception('Teacher ID is required');
    }
    
    // Define standard time slots (configurable)
    $standard_periods = [
        1 => ['start' => '08:00:00', 'end' => '08:45:00', 'label' => '8:00 - 8:45 AM'],
        2 => ['start' => '08:50:00', 'end' => '09:35:00', 'label' => '8:50 - 9:35 AM'],
        3 => ['start' => '09:40:00', 'end' => '10:25:00', 'label' => '9:40 - 10:25 AM'],
        4 => ['start' => '10:40:00', 'end' => '11:25:00', 'label' => '10:40 - 11:25 AM'],
        5 => ['start' => '11:30:00', 'end' => '12:15:00', 'label' => '11:30 - 12:15 PM'],
        6 => ['start' => '12:20:00', 'end' => '13:05:00', 'label' => '12:20 - 1:05 PM'],
        7 => ['start' => '14:00:00', 'end' => '14:45:00', 'label' => '2:00 - 2:45 PM'],
        8 => ['start' => '14:50:00', 'end' => '15:35:00', 'label' => '2:50 - 3:35 PM']
    ];
    
    // Get teacher's occupied slots
    $where_conditions = ["tp.teacher_id = ?", "tt.status = 'published'"];
    $params = [$teacher_id];
    $types = "i";
    
    if ($day_of_week) {
        $where_conditions[] = "tp.day_of_week = ?";
        $params[] = $day_of_week;
        $types .= "s";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    $sql = "
        SELECT 
            tp.day_of_week,
            tp.period_number,
            tp.start_time,
            tp.end_time,
            sub.name as subject_name,
            c.name as class_name,
            sec.name as section_name
        FROM timetable_periods tp
        JOIN timetables tt ON tp.timetable_id = tt.id
        JOIN subjects sub ON tp.subject_id = sub.id
        JOIN classes c ON tt.class_id = c.id
        JOIN sections sec ON tt.section_id = sec.id
        WHERE $where_clause
        ORDER BY tp.day_of_week, tp.period_number
    ";
    
    $occupied_slots = executeQuery($sql, $types, $params);
    
    // Calculate available slots
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    $available_slots = [];
    
    foreach ($days as $day) {
        if ($day_of_week && $day !== $day_of_week) {
            continue;
        }
        
        $available_slots[$day] = [];
        
        foreach ($standard_periods as $period_num => $period_info) {
            $is_occupied = false;
            
            foreach ($occupied_slots as $occupied) {
                if ($occupied['day_of_week'] === $day && $occupied['period_number'] == $period_num) {
                    $is_occupied = true;
                    break;
                }
            }
            
            if (!$is_occupied) {
                $available_slots[$day][] = [
                    'period_number' => $period_num,
                    'start_time' => $period_info['start'],
                    'end_time' => $period_info['end'],
                    'label' => $period_info['label']
                ];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'available_slots' => $available_slots,
            'occupied_slots' => $occupied_slots ?: [],
            'standard_periods' => $standard_periods
        ]
    ]);
}

/**
 * Bulk assign teacher periods
 */
function handleBulkAssignTeacherPeriods() {
    $teacher_id = $_POST['teacher_id'] ?? null;
    $periods = json_decode($_POST['periods'] ?? '[]', true);
    
    if (!$teacher_id || empty($periods)) {
        throw new Exception('Teacher ID and periods array are required');
    }
    
    $success_count = 0;
    $error_count = 0;
    $errors = [];
    
    foreach ($periods as $period) {
        try {
            // Validate required fields for each period
            if (!isset($period['timetable_id'], $period['day_of_week'], $period['period_number'], 
                       $period['start_time'], $period['end_time'], $period['subject_id'])) {
                throw new Exception('Missing required period fields');
            }
            
            // Check for conflicts
            $conflict_check_sql = "
                SELECT COUNT(*) as conflicts
                FROM timetable_periods tp
                JOIN timetables tt ON tp.timetable_id = tt.id
                WHERE tp.teacher_id = ? 
                AND tp.day_of_week = ? 
                AND tp.period_number = ?
                AND tt.status = 'published'
            ";
            
            $conflicts = executeQuery($conflict_check_sql, "isi", [
                $teacher_id, $period['day_of_week'], $period['period_number']
            ]);
            
            if ($conflicts && $conflicts[0]['conflicts'] > 0) {
                $errors[] = "Conflict on {$period['day_of_week']} period {$period['period_number']}";
                $error_count++;
                continue;
            }
            
            // Insert period
            $sql = "
                INSERT INTO timetable_periods 
                (timetable_id, day_of_week, period_number, start_time, end_time, 
                 subject_id, teacher_id, room, notes, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ";
            
            $result = executeQuery($sql, "issississ", [
                $period['timetable_id'], $period['day_of_week'], $period['period_number'],
                $period['start_time'], $period['end_time'], $period['subject_id'],
                $teacher_id, $period['room'] ?? null, $period['notes'] ?? null
            ]);
            
            if ($result) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = "Failed to insert period on {$period['day_of_week']} period {$period['period_number']}";
            }
            
        } catch (Exception $e) {
            $error_count++;
            $errors[] = $e->getMessage();
        }
    }
    
    echo json_encode([
        'success' => $success_count > 0,
        'success_count' => $success_count,
        'error_count' => $error_count,
        'errors' => $errors,
        'message' => "$success_count periods assigned successfully" . 
                    ($error_count > 0 ? ", $error_count failed" : "")
    ]);
}
function handleSaveTeacherPeriod() {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        die(json_encode(['success' => false, 'message' => 'Invalid JSON input']));
    }

    // Extract and validate fields
    $teacher_id = $input['teacher_id'] ?? null;
    $day_of_week = $input['day_of_week'] ?? null;
    $period_number = $input['period_number'] ?? null;
    $start_time = $input['start_time'] ?? null;
    $end_time = $input['end_time'] ?? null;
    $subject_id = $input['subject_id'] ?? null;
    $class_id = $input['class_id'] ?? null;
    $section_id = $input['section_id'] ?? null;
    $notes = $input['notes'] ?? null;

    if (!$teacher_id || !$day_of_week || !$period_number || !$subject_id || !$class_id || !$section_id) {
        die(json_encode(['success' => false, 'message' => 'Missing required fields']));
    }

    global $conn;
    $effective_date = date('Y-m-d'); // current date or pass from input
    $insert = "INSERT INTO timetables (class_id, section_id, effective_date) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($insert);
    $stmt_insert->bind_param("iis", $class_id, $section_id, $effective_date);
    if (!$stmt_insert->execute()) {
        die(json_encode(['success' => false, 'message' => 'Failed to create timetable']));
    }
    $timetable_id = $stmt_insert->insert_id;

    // Step 1: Get or create timetable_id
    $query = "SELECT id FROM timetables WHERE class_id = ? AND section_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $class_id, $section_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $timetable_id = $row['id'];
    } else {
        $insert = "INSERT INTO timetables (class_id, section_id) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($insert);
        $stmt_insert->bind_param("ii", $class_id, $section_id);
        if (!$stmt_insert->execute()) {
            die(json_encode(['success' => false, 'message' => 'Failed to create timetable']));
        }
        $timetable_id = $stmt_insert->insert_id;
    }

    // Step 2: Check for existing period for the teacher
    $conflict_sql = "SELECT id FROM timetable_periods WHERE teacher_id = ? AND day_of_week = ? AND period_number = ?";
    $stmt = $conn->prepare($conflict_sql);
    $stmt->bind_param("isi", $teacher_id, $day_of_week, $period_number);
    $stmt->execute();
    $conflict_result = $stmt->get_result();

    if ($conflict_row = $conflict_result->fetch_assoc()) {
        // Update existing period
        $period_id = $conflict_row['id'];
        $update_sql = "UPDATE timetable_periods SET timetable_id = ?, subject_id = ?, start_time = ?, end_time = ?, notes = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("iisssi", $timetable_id, $subject_id, $start_time, $end_time, $notes, $period_id);
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Period updated successfully',
                'period_id' => $period_id,
                'action' => 'updated'
            ]);
        } else {
            die(json_encode(['success' => false, 'message' => 'Failed to update period']));
        }
    } else {
        // Insert new period
        $insert_sql = "INSERT INTO timetable_periods (timetable_id, day_of_week, period_number, start_time, end_time, subject_id, teacher_id, notes)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("isisssis", $timetable_id, $day_of_week, $period_number, $start_time, $end_time, $subject_id, $teacher_id, $notes);
        if ($stmt->execute()) {
            $new_period_id = $stmt->insert_id;
            echo json_encode([
                'success' => true,
                'message' => 'Period created successfully',
                'period_id' => $new_period_id,
                'action' => 'created'
            ]);
        } else {
            die(json_encode(['success' => false, 'message' => 'Failed to create period']));
        }
    }
}

/**
 * Delete teacher period - FIXED VERSION
 */
function handleDeleteTeacherPeriod() {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON input');
    }
    
    $teacher_id = $input['teacher_id'] ?? null;
    $day_of_week = $input['day_of_week'] ?? null;
    $period_number = $input['period_number'] ?? null;
    
    if (!$teacher_id || !$day_of_week || !$period_number) {
        throw new Exception('Required fields: teacher_id, day_of_week, period_number');
    }
    
    try {
        // Check if timetable_periods table exists
        $table_check = executeQuery("SHOW TABLES LIKE 'timetable_periods'");
        if (empty($table_check)) {
            throw new Exception('Timetable periods table does not exist');
        }
        
        $sql = "DELETE FROM timetable_periods 
                WHERE teacher_id = ? AND day_of_week = ? AND period_number = ?";
        
        $result = executeQuery($sql, "isi", [$teacher_id, $day_of_week, $period_number]);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Period deleted successfully'
            ]);
        } else {
            throw new Exception('Failed to delete period - period may not exist');
        }
        
    } catch (Exception $e) {
        error_log("Error in handleDeleteTeacherPeriod: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete period: ' . $e->getMessage()
        ]);
    }
}

/**
 * Enhanced handleGetTeacherSchedule that works with simplified structure
 */
function handleGetTeacherScheduleMinimal() {
    $teacher_id = $_POST['teacher_id'] ?? $_GET['teacher_id'] ?? null;
    
    if (!$teacher_id) {
        throw new Exception('Teacher ID is required');
    }
    
    try {
        // Get teacher information
        $teacher_sql = "
            SELECT u.id, u.full_name, u.email, t.employee_number
            FROM users u 
            LEFT JOIN teachers t ON u.id = t.user_id 
            WHERE u.id = ? AND u.role IN ('teacher', 'headmaster')
        ";
        $teacher_info = executeQuery($teacher_sql, "i", [$teacher_id]);
        
        if (empty($teacher_info)) {
            throw new Exception('Teacher not found');
        }
        
        // Check if timetable_periods table exists
        $table_check = executeQuery("SHOW TABLES LIKE 'timetable_periods'");
        $schedule_periods = [];
        
        if (!empty($table_check)) {
            // Get schedule periods
            $schedule_sql = "
                SELECT 
                    tp.id as period_id,
                    tp.day_of_week,
                    tp.period_number,
                    COALESCE(tp.start_time, '00:00:00') as start_time,
                    COALESCE(tp.end_time, '00:00:00') as end_time,
                    COALESCE(tp.notes, '') as notes,
                    COALESCE(tp.room, '') as room,
                    COALESCE(sub.name, 'Free Period') as subject_name,
                    COALESCE(sub.code, '') as subject_code,
                    COALESCE(sub.id, 0) as subject_id,
                    COALESCE(c.name, '') as class_name,
                    COALESCE(sec.name, '') as section_name,
                    COALESCE(c.id, 0) as class_id,
                    COALESCE(sec.id, 0) as section_id
                FROM timetable_periods tp
                LEFT JOIN subjects sub ON tp.subject_id = sub.id
                LEFT JOIN classes c ON tp.class_id = c.id
                LEFT JOIN sections sec ON t.section_id = sec.id
                WHERE tp.teacher_id = ?
                ORDER BY 
                    FIELD(tp.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
                    tp.period_number
            ";
            
            $schedule_periods = executeQuery($schedule_sql, "i", [$teacher_id]);
        }
        
        // Structure schedule by day
        $structured_schedule = [
            'monday' => [],
            'tuesday' => [],
            'wednesday' => [],
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => []
        ];
        
        if (!empty($schedule_periods)) {
            foreach ($schedule_periods as $period) {
                $day = strtolower($period['day_of_week']);
                if (array_key_exists($day, $structured_schedule)) {
                    $structured_schedule[$day][] = [
                        'period' => (int)$period['period_number'],
                        'period_id' => $period['period_id'],
                        'subject_name' => $period['subject_name'],
                        'subject_code' => $period['subject_code'],
                        'subject_id' => $period['subject_id'],
                        'class_info' => trim($period['class_name'] . ' - ' . $period['section_name']),
                        'class_id' => $period['class_id'],
                        'section_id' => $period['section_id'],
                        'notes' => $period['notes'],
                        'room' => $period['room'],
                        'start_time' => $period['start_time'],
                        'end_time' => $period['end_time'],
                        'has_conflict' => false
                    ];
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'schedule' => $structured_schedule,
            'teacher_info' => $teacher_info[0],
            'total_periods' => count($schedule_periods),
            'message' => count($schedule_periods) > 0 ? 
                'Schedule loaded successfully' : 
                'No schedule found - you can start adding periods'
        ]);
        
    } catch (Exception $e) {
        error_log("Error in handleGetTeacherScheduleMinimal: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load teacher schedule: ' . $e->getMessage(),
            'schedule' => [
                'monday' => [], 'tuesday' => [], 'wednesday' => [], 
                'thursday' => [], 'friday' => [], 'saturday' => [], 'sunday' => []
            ],
            'teacher_info' => null,
            'total_periods' => 0
        ]);
    }
}

/**
 * Check schedule conflicts - FIXED VERSION
 */
function handleCheckScheduleConflicts() {
    $teacher_id = $_GET['teacher_id'] ?? null;
    
    if (!$teacher_id) {
        throw new Exception('Teacher ID is required');
    }
    
    try {
        // Check if timetable_periods table exists
        $table_check = executeQuery("SHOW TABLES LIKE 'timetable_periods'");
        if (empty($table_check)) {
            echo json_encode([
                'success' => true,
                'conflicts' => [],
                'count' => 0,
                'message' => 'No timetable system configured'
            ]);
            return;
        }
        
        $sql = "SELECT 
            tp.day_of_week,
            tp.period_number,
            tp.start_time,
            tp.end_time,
            s.name as subject_name,
            c.name as class_name,
            sec.name as section_name,
            COUNT(*) as conflict_count
        FROM timetable_periods tp
        LEFT JOIN timetables t ON tp.timetable_id = t.id
        LEFT JOIN subjects s ON tp.subject_id = s.id
        LEFT JOIN classes c ON t.class_id = c.id
        LEFT JOIN sections sec ON t.section_id = sec.id
        WHERE tp.teacher_id = ?
        GROUP BY tp.day_of_week, tp.period_number, tp.start_time, tp.end_time, s.name, c.name, sec.name
        HAVING COUNT(*) > 1
        ORDER BY 
            CASE tp.day_of_week
                WHEN 'monday' THEN 1
                WHEN 'tuesday' THEN 2
                WHEN 'wednesday' THEN 3
                WHEN 'thursday' THEN 4
                WHEN 'friday' THEN 5
                WHEN 'saturday' THEN 6
                WHEN 'sunday' THEN 7
                ELSE 8
            END,
            tp.period_number";
        
        $conflicts = executeQuery($sql, "i", [$teacher_id]);
        
        $conflict_messages = [];
        if (!empty($conflicts)) {
            foreach ($conflicts as $conflict) {
                $day = ucfirst($conflict['day_of_week']);
                $conflict_messages[] = "Multiple assignments on {$day}, Period {$conflict['period_number']} ({$conflict['start_time']}-{$conflict['end_time']})";
            }
        }
        
        echo json_encode([
            'success' => true,
            'conflicts' => $conflict_messages,
            'count' => count($conflict_messages)
        ]);
        
    } catch (Exception $e) {
        error_log("Error in handleCheckScheduleConflicts: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to check conflicts: ' . $e->getMessage(),
            'conflicts' => [],
            'count' => 0
        ]);
    }
}
/**
 * Get all classes for dropdown - FIXED VERSION
 */
function handleGetClasses() {
    try {
        $sql = "SELECT 
                    id,
                    name
                FROM classes
                ORDER BY name";
        
        $classes = executeQuery($sql);
        
        // Debug: Check what we actually got
        error_log("Classes query result: " . print_r($classes, true));
        
        if ($classes === false || $classes === null) {
            throw new Exception('Database query failed');
        }
        
        // Ensure we have an array
        if (!is_array($classes)) {
            $classes = [];
        }
        
        // Format the classes properly
        $formatted_classes = [];
        foreach ($classes as $class) {
            $formatted_classes[] = [
                'id' => $class['id'],
                'name' => $class['name'],
                'class_name' => $class['name'], // for compatibility
                'class_code' => $class['name'], // fallback
                'description' => $class['name'], // fallback
                'created_at' => date('Y-m-d H:i:s') // fallback
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $formatted_classes,
            'classes' => $formatted_classes, // for compatibility
            'count' => count($formatted_classes)
        ]);
        
    } catch (Exception $e) {
        error_log("Error in handleGetClasses: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load classes: ' . $e->getMessage(),
            'data' => [],
            'classes' => []
        ]);
    }
}


/**
 * Helper function to get or create timetable for class/section
 */
function getOrCreateTimetable($class_id, $section_id) {
    // First, try to find existing timetable
    $find_sql = "SELECT id FROM timetables 
                 WHERE class_id = ? AND section_id = ? 
                 AND academic_year_id = (SELECT id FROM academic_years WHERE status = 'active' LIMIT 1)
                 ORDER BY created_at DESC LIMIT 1";
    
    $existing = executeQuery($find_sql, "ii", [$class_id, $section_id]);
    
    if (!empty($existing)) {
        return $existing[0]['id'];
    }
    
    // Create new timetable if none exists
    $academic_year_sql = "SELECT id FROM academic_years WHERE status = 'active' LIMIT 1";
    $academic_year = executeQuery($academic_year_sql, "", []);
    
    if (empty($academic_year)) {
        throw new Exception('No active academic year found');
    }
    
    $academic_year_id = $academic_year[0]['id'];
    
    $create_sql = "INSERT INTO timetables 
                   (class_id, section_id, academic_year_id, description, status, effective_date, created_at)
                   VALUES (?, ?, ?, 'Auto-created for individual teacher schedule', 'draft', CURDATE(), NOW())";
    
    $result = executeQuery($create_sql, "iii", [$class_id, $section_id, $academic_year_id]);
    
    if ($result) {
        // Get the newly created timetable ID
        global $conn;
        return $conn->insert_id;
    } else {
        throw new Exception('Failed to create timetable');
    }
}

function handleGetPublishedTimetables() {
    global $conn;
    $sql = "SELECT 
                t.id,
                t.class_id,
                t.section_id,
                t.academic_year_id,
                c.name as class_name,
                s.name as section_name,
                ay.name as academic_year,
                t.description,
                t.effective_date,
                t.created_at,
                t.updated_at
            FROM timetables t
            JOIN classes c ON t.class_id = c.id
            JOIN sections s ON t.section_id = s.id
            JOIN academic_years ay ON t.academic_year_id = ay.id
            WHERE t.status = 'published'
            ORDER BY c.name, s.name, t.effective_date DESC";
    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception('Database query failed: ' . $conn->error);
    }
    $timetables = [];
    while ($row = $result->fetch_assoc()) {
        $timetables[] = $row;
    }
    echo json_encode([
        'success' => true,
        'data' => $timetables
    ]);
}

/**
 * Get all subjects for dropdown - FIXED VERSION
 */
function handleGetSubjects() {
    try {
        $sql = "SELECT 
                    id,
                    name,
                    code
                FROM subjects
                ORDER BY name";
        
        $subjects = executeQuery($sql);
        
        if ($subjects === false || $subjects === null) {
            throw new Exception('Database query failed');
        }
        
        if (!is_array($subjects)) {
            $subjects = [];
        }
        
        // Format the subjects properly
        $formatted_subjects = [];
        foreach ($subjects as $subject) {
            $formatted_subjects[] = [
                'id' => $subject['id'],
                'name' => $subject['name'],
                'code' => $subject['code'] ?? $subject['name'], // fallback if code is null
                'subject_name' => $subject['name'], // for compatibility
                'subject_code' => $subject['code'] ?? $subject['name'] // for compatibility
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $formatted_subjects,
            'subjects' => $formatted_subjects, // for compatibility
            'count' => count($formatted_subjects)
        ]);
        
    } catch (Exception $e) {
        error_log("Error in handleGetSubjects: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to load subjects: ' . $e->getMessage(),
            'data' => [],
            'subjects' => []
        ]);
    }
}
/**
 * Debug function to check database structure
 * Add this as a new case in your switch statement: case 'debug_teacher_schedule':
 */
function handleDebugTeacherSchedule() {
    $teacher_id = $_GET['teacher_id'] ?? null;
    
    if (!$teacher_id) {
        throw new Exception('Teacher ID is required');
    }
    
    $debug_info = [];
    
    try {
        // Check if teacher exists
        $teacher_check = executeQuery("SELECT id, full_name FROM users WHERE id = ? AND role IN ('teacher', 'headmaster')", "i", [$teacher_id]);
        $debug_info['teacher_exists'] = !empty($teacher_check);
        $debug_info['teacher_info'] = $teacher_check[0] ?? null;
        
        // Check if timetable_periods table exists
        $table_check = executeQuery("SHOW TABLES LIKE 'timetable_periods'");
        $debug_info['timetable_periods_table_exists'] = !empty($table_check);
        
        if (!empty($table_check)) {
            // Check table structure
            $structure = executeQuery("DESCRIBE timetable_periods");
            $debug_info['table_structure'] = $structure;
            
            // Check if teacher has any periods
            $period_count = executeQuery("SELECT COUNT(*) as count FROM timetable_periods WHERE teacher_id = ?", "i", [$teacher_id]);
            $debug_info['teacher_period_count'] = $period_count[0]['count'] ?? 0;
            
            // Get sample periods if any exist
            if ($debug_info['teacher_period_count'] > 0) {
                $sample_periods = executeQuery("SELECT * FROM timetable_periods WHERE teacher_id = ? LIMIT 3", "i", [$teacher_id]);
                $debug_info['sample_periods'] = $sample_periods;
            }
        }
        
        // Check related tables
        $timetables_check = executeQuery("SHOW TABLES LIKE 'timetables'");
        $debug_info['timetables_table_exists'] = !empty($timetables_check);
        
        if (!empty($timetables_check)) {
            $timetable_count = executeQuery("SELECT COUNT(*) as count FROM timetables");
            $debug_info['total_timetables'] = $timetable_count[0]['count'] ?? 0;
        }
        
        $subjects_check = executeQuery("SHOW TABLES LIKE 'subjects'");
        $debug_info['subjects_table_exists'] = !empty($subjects_check);
        
        $classes_check = executeQuery("SHOW TABLES LIKE 'classes'");
        $debug_info['classes_table_exists'] = !empty($classes_check);
        
        $sections_check = executeQuery("SHOW TABLES LIKE 'sections'");
        $debug_info['sections_table_exists'] = !empty($sections_check);
        
    } catch (Exception $e) {
        $debug_info['error'] = $e->getMessage();
    }
    
    echo json_encode([
        'success' => true,
        'debug_info' => $debug_info
    ]);
}

/**
 * Simple fallback function if timetable system is not set up
 * This creates a basic schedule structure for testing
 */
function handleGetBasicTeacherSchedule() {
    $teacher_id = $_GET['teacher_id'] ?? null;
    
    if (!$teacher_id) {
        throw new Exception('Teacher ID is required');
    }
    
    // Get teacher info
    $teacher_sql = "SELECT u.id, u.full_name, u.email, t.employee_number 
                    FROM users u 
                    LEFT JOIN teachers t ON u.id = t.user_id 
                    WHERE u.id = ?";
    $teacher_info = executeQuery($teacher_sql, "i", [$teacher_id]);
    
    if (empty($teacher_info)) {
        throw new Exception('Teacher not found');
    }
    
    // Create empty schedule structure
    $empty_schedule = [
        'monday' => [],
        'tuesday' => [],
        'wednesday' => [],
        'thursday' => [],
        'friday' => [],
        'saturday' => [],
        'sunday' => []
    ];
    
    echo json_encode([
        'success' => true,
        'schedule' => $empty_schedule,
        'teacher_info' => $teacher_info[0],
        'total_periods' => 0,
        'message' => 'Empty schedule loaded - timetable system may not be configured'
    ]);
}

?>