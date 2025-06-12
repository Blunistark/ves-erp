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
if (!isLoggedIn() || !hasRole(['admin', 'superadmin'])) {
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
            break;
            
        case 'reassign_class_teacher':
            handleReassignClassTeacher();
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
function handleGetTeachers() {    $sql = "SELECT 
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
        'data' => $teachers ?: []
    ]);
}

/**
 * Get available teachers for assignment
 */
function handleGetAvailableTeachers() {    $sql = "SELECT 
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
function handleGetClassAssignments() {    $sql = "SELECT 
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
 * Get sections for dropdown
 */
function handleGetSections() {
    $class_id = $_GET['class_id'] ?? null;
    
    $sql = "SELECT 
                s.id,
                s.name,
                c.name as class_name,
                u.full_name as current_teacher_name
            FROM sections s
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN users u ON s.class_teacher_user_id = u.id";
    
    $params = [];
    $types = "";
    
    if ($class_id) {
        $sql .= " WHERE c.id = ?";
        $params[] = $class_id;
        $types = "i";
    }
    
    $sql .= " ORDER BY c.name, s.name";
    
    $sections = $params ? executeQuery($sql, $types, $params) : executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $sections ?: []
    ]);
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
    $stats = [
        'total_teachers' => count($grouped_assignments),
        'teachers_with_assignments' => count(array_filter($grouped_assignments, function($teacher) {
            return !empty($teacher['assignments']);
        })),
        'total_assignments' => array_sum(array_column($teacher_summary, 'total_assignments')),
        'unique_subjects' => count(array_unique(array_merge(...array_column($teacher_summary, 'subjects_list'))))
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

?>