<?php
/**
 * Academic Management API Backend - Admin Dashboard
 * Unified API for managing academic structure (excluding exams and timetables)
 * Handles: Academic Years, Classes, Sections, Subjects, Class-Subject Mapping
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

// Get request data
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Set content type based on action (CSV for export, JSON for others)
if ($action === 'bulk_export') {
    // Headers will be set in the export function
} else {
    header('Content-Type: application/json');
}
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

try {
    switch ($action) {
        // Academic Years Management
        case 'get_academic_years':
            handleGetAcademicYears();
            break;
        case 'add_academic_year':
            handleAddAcademicYear();
            break;
        case 'update_academic_year':
            handleUpdateAcademicYear();
            break;
        case 'delete_academic_year':
            handleDeleteAcademicYear();
            break;
        case 'set_current_academic_year':
            handleSetCurrentAcademicYear();
            break;

        // Classes Management
        case 'get_classes':
            handleGetClasses();
            break;
        case 'add_class':
            handleAddClass();
            break;
        case 'update_class':
            handleUpdateClass();
            break;
        case 'delete_class':
            handleDeleteClass();
            break;

        // Sections Management
        case 'get_sections':
            handleGetSections();
            break;
        case 'add_section':
            handleAddSection();
            break;
        case 'update_section':
            handleUpdateSection();
            break;
        case 'delete_section':
            handleDeleteSection();
            break;
        case 'get_sections_by_class':
            handleGetSectionsByClass();
            break;

        // Subjects Management
        case 'get_subjects':
            handleGetSubjects();
            break;
        case 'add_subject':
            handleAddSubject();
            break;
        case 'update_subject':
            handleUpdateSubject();
            break;
        case 'delete_subject':
            handleDeleteSubject();
            break;

        // Class-Subject Mapping
        case 'get_class_subjects':
            handleGetClassSubjects();
            break;
        case 'assign_subject_to_class':
            handleAssignSubjectToClass();
            break;
        case 'remove_subject_from_class':
            handleRemoveSubjectFromClass();
            break;
        case 'bulk_assign_subjects':
            handleBulkAssignSubjects();
            break;
        case 'get_curriculum_overview':
            handleGetCurriculumOverview();
            break;

        // Statistics & Reports
        case 'get_academic_statistics':
            handleGetAcademicStatistics();
            break;        case 'get_structure_overview':
            handleGetStructureOverview();
            break;
        case 'export_structure_overview':
            handleExportStructureOverview();
            break;
        
        // Bulk Operations
        case 'get_bulk_stats':
            echo json_encode([
                'success' => true,
                'stats' => [
                    'total_records' => getTotalRecordsCount(),
                    'pending_imports' => 0,
                    'completed_today' => getCompletedOperationsToday(),
                    'failed_operations' => getFailedOperationsCount()
                ]
            ]);
            break;
        case 'bulk_import':
            handleBulkImport();
            break;
        case 'bulk_export':
            handleBulkExport();
            break;
        case 'batch_assign_subjects':
            handleBatchAssignSubjects();
            break;
        case 'duplicate_year_structure':
            handleDuplicateYearStructure();
            break;
        case 'validate_data_integrity':
            handleDataValidation();
            break;
        case 'cleanup_orphaned_records':
            handleCleanupOrphanedRecords();
            break;
        case 'bulk_import_subjects':
            handleBulkImportSubjects();
            break;        case 'validate_academic_data':
            handleValidateAcademicData();
            break;
        case 'get_operation_history':
            echo json_encode([
                'success' => true,
                'operations' => getOperationHistory()
            ]);
            break;

        default:
            if (empty($action)) {
                // API documentation
                echo json_encode([
                    'success' => true,
                    'message' => 'Academic Management API',
                    'version' => '1.0',
                    'endpoints' => [
                        'academic_years' => ['get_academic_years', 'add_academic_year', 'update_academic_year', 'delete_academic_year', 'set_current_academic_year'],
                        'classes' => ['get_classes', 'add_class', 'update_class', 'delete_class'],
                        'sections' => ['get_sections', 'add_section', 'update_section', 'delete_section', 'get_sections_by_class'],
                        'subjects' => ['get_subjects', 'add_subject', 'update_subject', 'delete_subject'],
                        'mapping' => ['get_class_subjects', 'assign_subject_to_class', 'remove_subject_from_class', 'bulk_assign_subjects', 'get_curriculum_overview'],
                        'reports' => ['get_academic_statistics', 'get_structure_overview'],
                        'bulk' => ['bulk_import_subjects', 'validate_academic_data']
                    ]
                ]);
            } else {
                throw new Exception('Invalid action: ' . $action);
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// ========== ACADEMIC YEARS MANAGEMENT ==========

/**
 * Get all academic years
 */
function handleGetAcademicYears() {
    $sql = "SELECT 
                id, name, start_date, end_date, is_current, created_at, updated_at,
                DATEDIFF(end_date, start_date) as duration_days
            FROM academic_years 
            ORDER BY start_date DESC";
    
    $academic_years = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $academic_years ?: []
    ]);
}

/**
 * Add new academic year
 */
function handleAddAcademicYear() {
    global $user_id;
    
    $name = trim($_POST['name'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    // Validation
    if (empty($name) || empty($start_date) || empty($end_date)) {
        throw new Exception('Name, start date, and end date are required');
    }
    
    if (strtotime($start_date) >= strtotime($end_date)) {
        throw new Exception('End date must be after start date');
    }
    
    // Check for name conflicts
    $existing = executeQuery("SELECT id FROM academic_years WHERE name = ?", "s", [$name]);
    if ($existing) {
        throw new Exception('Academic year with this name already exists');
    }
    
    // Check for date overlaps
    $overlap_check = executeQuery(
        "SELECT id FROM academic_years WHERE 
         (start_date <= ? AND end_date >= ?) OR 
         (start_date <= ? AND end_date >= ?) OR
         (start_date >= ? AND end_date <= ?)",
        "ssssss", 
        [$end_date, $start_date, $start_date, $end_date, $start_date, $end_date]
    );
    
    if ($overlap_check) {
        throw new Exception('Date range overlaps with existing academic year');
    }
    
    $sql = "INSERT INTO academic_years (name, start_date, end_date, created_at) 
            VALUES (?, ?, ?, NOW())";
    
    $result = executeQuery($sql, "sss", [$name, $start_date, $end_date]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Academic year added successfully'
        ]);
    } else {
        throw new Exception('Failed to add academic year');
    }
}

/**
 * Update academic year
 */
function handleUpdateAcademicYear() {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    
    if (!$id || empty($name) || empty($start_date) || empty($end_date)) {
        throw new Exception('ID, name, start date, and end date are required');
    }
    
    if (strtotime($start_date) >= strtotime($end_date)) {
        throw new Exception('End date must be after start date');
    }
    
    // Check for name conflicts (excluding current record)
    $existing = executeQuery("SELECT id FROM academic_years WHERE name = ? AND id != ?", "si", [$name, $id]);
    if ($existing) {
        throw new Exception('Academic year with this name already exists');
    }
    
    $sql = "UPDATE academic_years SET name = ?, start_date = ?, end_date = ?, updated_at = NOW() WHERE id = ?";
    $result = executeQuery($sql, "sssi", [$name, $start_date, $end_date, $id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Academic year updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update academic year');
    }
}

/**
 * Delete academic year
 */
function handleDeleteAcademicYear() {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Academic year ID is required');
    }
    
    // Check if academic year is in use
    $in_use = executeQuery("SELECT COUNT(*) as count FROM enrollments WHERE academic_year_id = ?", "i", [$id]);
    if ($in_use && $in_use[0]['count'] > 0) {
        throw new Exception('Cannot delete academic year that has student enrollments');
    }
    
    $sql = "DELETE FROM academic_years WHERE id = ?";
    $result = executeQuery($sql, "i", [$id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Academic year deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete academic year');
    }
}

/**
 * Set current academic year
 */
function handleSetCurrentAcademicYear() {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Academic year ID is required');
    }
    
    // Start transaction
    global $conn;
    $conn->begin_transaction();
    
    try {
        // Clear current flag from all academic years
        executeQuery("UPDATE academic_years SET is_current = 0");
        
        // Set current flag for specified academic year
        executeQuery("UPDATE academic_years SET is_current = 1 WHERE id = ?", "i", [$id]);
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Current academic year updated successfully'
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

// ========== CLASSES MANAGEMENT ==========

/**
 * Get all classes with section counts
 */
function handleGetClasses() {
    $sql = "SELECT 
                c.id, c.name,
                COUNT(s.id) as section_count,
                GROUP_CONCAT(s.name ORDER BY s.name) as sections
            FROM classes c
            LEFT JOIN sections s ON c.id = s.class_id
            GROUP BY c.id, c.name
            ORDER BY c.name";
    
    $classes = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $classes ?: []
    ]);
}

/**
 * Add new class
 */
function handleAddClass() {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        throw new Exception('Class name is required');
    }
    
    // Check for existing class name
    $existing = executeQuery("SELECT id FROM classes WHERE name = ?", "s", [$name]);
    if ($existing) {
        throw new Exception('Class with this name already exists');
    }
    
    $sql = "INSERT INTO classes (name) VALUES (?)";
    $result = executeQuery($sql, "s", [$name]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Class added successfully'
        ]);
    } else {
        throw new Exception('Failed to add class');
    }
}

/**
 * Update class
 */
function handleUpdateClass() {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    
    if (!$id || empty($name)) {
        throw new Exception('ID and name are required');
    }
    
    // Check for name conflicts (excluding current record)
    $existing = executeQuery("SELECT id FROM classes WHERE name = ? AND id != ?", "si", [$name, $id]);
    if ($existing) {
        throw new Exception('Class with this name already exists');
    }
    
    $sql = "UPDATE classes SET name = ? WHERE id = ?";
    $result = executeQuery($sql, "si", [$name, $id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Class updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update class');
    }
}

/**
 * Delete class
 */
function handleDeleteClass() {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Class ID is required');
    }
    
    // Check if class has sections
    $sections = executeQuery("SELECT COUNT(*) as count FROM sections WHERE class_id = ?", "i", [$id]);
    if ($sections && $sections[0]['count'] > 0) {
        throw new Exception('Cannot delete class that has sections. Delete sections first.');
    }
    
    // Check if class has students
    $students = executeQuery("SELECT COUNT(*) as count FROM enrollments WHERE class_id = ?", "i", [$id]);
    if ($students && $students[0]['count'] > 0) {
        throw new Exception('Cannot delete class that has student enrollments');
    }
    
    $sql = "DELETE FROM classes WHERE id = ?";
    $result = executeQuery($sql, "i", [$id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Class deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete class');
    }
}

// ========== SECTIONS MANAGEMENT ==========

/**
 * Get all sections with class information
 */
function handleGetSections() {
    $sql = "SELECT 
                s.id, s.name, s.capacity, s.class_teacher_user_id,
                c.name as class_name, c.id as class_id,
                u.full_name as class_teacher_name,
                COUNT(st.user_id) as student_count
            FROM sections s
            JOIN classes c ON s.class_id = c.id
            LEFT JOIN users u ON s.class_teacher_user_id = u.id
            LEFT JOIN students st ON s.id = st.section_id
            GROUP BY s.id, s.name, s.capacity, s.class_teacher_user_id, c.name, c.id, u.full_name
            ORDER BY c.name, s.name";
    
    $sections = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $sections ?: []
    ]);
}

/**
 * Get sections by class ID
 */
function handleGetSectionsByClass() {
    $class_id = $_GET['class_id'] ?? null;
    
    if (!$class_id) {
        throw new Exception('Class ID is required');
    }
    
    $sql = "SELECT id, name, capacity FROM sections WHERE class_id = ? ORDER BY name";
    $sections = executeQuery($sql, "i", [$class_id]);
    
    echo json_encode([
        'success' => true,
        'data' => $sections ?: []
    ]);
}

/**
 * Add new section
 */
function handleAddSection() {
    $class_id = $_POST['class_id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $capacity = $_POST['capacity'] ?? null;
    
    if (!$class_id || empty($name)) {
        throw new Exception('Class ID and section name are required');
    }
    
    // Check for existing section in the same class
    $existing = executeQuery("SELECT id FROM sections WHERE class_id = ? AND name = ?", "is", [$class_id, $name]);
    if ($existing) {
        throw new Exception('Section with this name already exists in this class');
    }
    
    $sql = "INSERT INTO sections (class_id, name, capacity) VALUES (?, ?, ?)";
    $result = executeQuery($sql, "isi", [$class_id, $name, $capacity]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Section added successfully'
        ]);
    } else {
        throw new Exception('Failed to add section');
    }
}

/**
 * Update section
 */
function handleUpdateSection() {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $capacity = $_POST['capacity'] ?? null;
    $class_teacher_user_id = $_POST['class_teacher_user_id'] ?? null;
    
    if (!$id || empty($name)) {
        throw new Exception('ID and name are required');
    }
    
    // Check for name conflicts within the same class
    $existing = executeQuery(
        "SELECT s1.id FROM sections s1 
         JOIN sections s2 ON s1.class_id = s2.class_id 
         WHERE s1.name = ? AND s2.id = ? AND s1.id != ?", 
        "sii", [$name, $id, $id]
    );
    
    if ($existing) {
        throw new Exception('Section with this name already exists in this class');
    }
    
    $sql = "UPDATE sections SET name = ?, capacity = ?, class_teacher_user_id = ? WHERE id = ?";
    $result = executeQuery($sql, "siii", [$name, $capacity, $class_teacher_user_id, $id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Section updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update section');
    }
}

/**
 * Delete section
 */
function handleDeleteSection() {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Section ID is required');
    }
    
    // Check if section has students
    $students = executeQuery("SELECT COUNT(*) as count FROM students WHERE section_id = ?", "i", [$id]);
    if ($students && $students[0]['count'] > 0) {
        throw new Exception('Cannot delete section that has students');
    }
    
    $sql = "DELETE FROM sections WHERE id = ?";
    $result = executeQuery($sql, "i", [$id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Section deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete section');
    }
}

// ========== SUBJECTS MANAGEMENT ==========

/**
 * Get all subjects with class mappings
 */
function handleGetSubjects() {
    $sql = "SELECT 
                s.id, s.name, s.code,
                COUNT(cs.class_id) as class_count,
                GROUP_CONCAT(c.name ORDER BY c.name) as classes
            FROM subjects s
            LEFT JOIN class_subjects cs ON s.id = cs.subject_id
            LEFT JOIN classes c ON cs.class_id = c.id
            GROUP BY s.id, s.name, s.code
            ORDER BY s.name";
    
    $subjects = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $subjects ?: []
    ]);
}

/**
 * Add new subject
 */
function handleAddSubject() {
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    
    if (empty($name) || empty($code)) {
        throw new Exception('Subject name and code are required');
    }
    
    // Check for existing subject name or code
    $existing_name = executeQuery("SELECT id FROM subjects WHERE name = ?", "s", [$name]);
    $existing_code = executeQuery("SELECT id FROM subjects WHERE code = ?", "s", [$code]);
    
    if ($existing_name) {
        throw new Exception('Subject with this name already exists');
    }
    
    if ($existing_code) {
        throw new Exception('Subject with this code already exists');
    }
    
    $sql = "INSERT INTO subjects (name, code) VALUES (?, ?)";
    $result = executeQuery($sql, "ss", [$name, $code]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Subject added successfully'
        ]);
    } else {
        throw new Exception('Failed to add subject');
    }
}

/**
 * Update subject
 */
function handleUpdateSubject() {
    $id = $_POST['id'] ?? null;
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    
    if (!$id || empty($name) || empty($code)) {
        throw new Exception('ID, name, and code are required');
    }
    
    // Check for conflicts (excluding current record)
    $existing_name = executeQuery("SELECT id FROM subjects WHERE name = ? AND id != ?", "si", [$name, $id]);
    $existing_code = executeQuery("SELECT id FROM subjects WHERE code = ? AND id != ?", "si", [$code, $id]);
    
    if ($existing_name) {
        throw new Exception('Subject with this name already exists');
    }
    
    if ($existing_code) {
        throw new Exception('Subject with this code already exists');
    }
    
    $sql = "UPDATE subjects SET name = ?, code = ? WHERE id = ?";
    $result = executeQuery($sql, "ssi", [$name, $code, $id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Subject updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update subject');
    }
}

/**
 * Delete subject
 */
function handleDeleteSubject() {
    $id = $_POST['id'] ?? null;
    
    if (!$id) {
        throw new Exception('Subject ID is required');
    }
    
    // Check if subject is assigned to classes
    $assignments = executeQuery("SELECT COUNT(*) as count FROM class_subjects WHERE subject_id = ?", "i", [$id]);
    if ($assignments && $assignments[0]['count'] > 0) {
        throw new Exception('Cannot delete subject that is assigned to classes. Remove assignments first.');
    }
    
    $sql = "DELETE FROM subjects WHERE id = ?";
    $result = executeQuery($sql, "i", [$id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Subject deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete subject');
    }
}

// ========== CLASS-SUBJECT MAPPING ==========

/**
 * Get class-subject mappings
 */
function handleGetClassSubjects() {
    $class_id = $_GET['class_id'] ?? null;
    
    if ($class_id) {
        // Get subjects for specific class
        $sql = "SELECT s.id, s.name, s.code 
                FROM subjects s
                JOIN class_subjects cs ON s.id = cs.subject_id
                WHERE cs.class_id = ?
                ORDER BY s.name";
        $subjects = executeQuery($sql, "i", [$class_id]);
    } else {
        // Get all class-subject mappings
        $sql = "SELECT 
                    c.id as class_id, c.name as class_name,
                    s.id as subject_id, s.name as subject_name, s.code as subject_code
                FROM class_subjects cs
                JOIN classes c ON cs.class_id = c.id
                JOIN subjects s ON cs.subject_id = s.id
                ORDER BY c.name, s.name";
        $subjects = executeQuery($sql);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $subjects ?: []
    ]);
}

/**
 * Assign subject to class
 */
function handleAssignSubjectToClass() {
    $class_id = $_POST['class_id'] ?? null;
    $subject_id = $_POST['subject_id'] ?? null;
    
    if (!$class_id || !$subject_id) {
        throw new Exception('Class ID and Subject ID are required');
    }
    
    // Check if mapping already exists
    $existing = executeQuery("SELECT 1 FROM class_subjects WHERE class_id = ? AND subject_id = ?", "ii", [$class_id, $subject_id]);
    if ($existing) {
        throw new Exception('Subject is already assigned to this class');
    }
    
    $sql = "INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)";
    $result = executeQuery($sql, "ii", [$class_id, $subject_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Subject assigned to class successfully'
        ]);
    } else {
        throw new Exception('Failed to assign subject to class');
    }
}

/**
 * Remove subject from class
 */
function handleRemoveSubjectFromClass() {
    $class_id = $_POST['class_id'] ?? null;
    $subject_id = $_POST['subject_id'] ?? null;
    
    if (!$class_id || !$subject_id) {
        throw new Exception('Class ID and Subject ID are required');
    }
    
    $sql = "DELETE FROM class_subjects WHERE class_id = ? AND subject_id = ?";
    $result = executeQuery($sql, "ii", [$class_id, $subject_id]);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'Subject removed from class successfully'
        ]);
    } else {
        throw new Exception('Failed to remove subject from class');
    }
}

/**
 * Bulk assign subjects to classes
 */
function handleBulkAssignSubjects() {
    $assignments = json_decode($_POST['assignments'] ?? '[]', true);
    
    if (empty($assignments)) {
        throw new Exception('No assignments provided');
    }
    
    global $conn;
    $conn->begin_transaction();
    
    try {
        $success_count = 0;
        
        foreach ($assignments as $assignment) {
            $class_id = $assignment['class_id'] ?? null;
            $subject_id = $assignment['subject_id'] ?? null;
            
            if (!$class_id || !$subject_id) {
                continue;
            }
            
            // Check if mapping already exists
            $existing = executeQuery("SELECT 1 FROM class_subjects WHERE class_id = ? AND subject_id = ?", "ii", [$class_id, $subject_id]);
            if (!$existing) {
                executeQuery("INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)", "ii", [$class_id, $subject_id]);
                $success_count++;
            }
        }
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "$success_count subject assignments completed successfully"
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

/**
 * Get curriculum overview (matrix view)
 */
function handleGetCurriculumOverview() {
    $sql = "SELECT 
                c.id as class_id, c.name as class_name,
                s.id as subject_id, s.name as subject_name, s.code as subject_code,
                CASE WHEN cs.class_id IS NOT NULL THEN 1 ELSE 0 END as is_assigned
            FROM classes c
            CROSS JOIN subjects s
            LEFT JOIN class_subjects cs ON c.id = cs.class_id AND s.id = cs.subject_id
            ORDER BY c.name, s.name";
    
    $data = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $data ?: []
    ]);
}

// ========== STATISTICS & REPORTS ==========

/**
 * Get academic statistics
 */
function handleGetAcademicStatistics() {
    $stats = [];
    
    // Academic years count
    $academic_years = executeQuery("SELECT COUNT(*) as count FROM academic_years");
    $stats['academic_years'] = $academic_years[0]['count'] ?? 0;
    
    // Classes count
    $classes = executeQuery("SELECT COUNT(*) as count FROM classes");
    $stats['classes'] = $classes[0]['count'] ?? 0;
    
    // Sections count
    $sections = executeQuery("SELECT COUNT(*) as count FROM sections");
    $stats['sections'] = $sections[0]['count'] ?? 0;
    
    // Subjects count
    $subjects = executeQuery("SELECT COUNT(*) as count FROM subjects");
    $stats['subjects'] = $subjects[0]['count'] ?? 0;
    
    // Class-subject mappings count
    $mappings = executeQuery("SELECT COUNT(*) as count FROM class_subjects");
    $stats['subject_assignments'] = $mappings[0]['count'] ?? 0;
    
    // Current academic year
    $current_year = executeQuery("SELECT name FROM academic_years WHERE is_current = 1");
    $stats['current_academic_year'] = $current_year[0]['name'] ?? 'None set';
    
    // Average sections per class
    $avg_sections = executeQuery("SELECT AVG(section_count) as avg_sections FROM (SELECT COUNT(*) as section_count FROM sections GROUP BY class_id) as subquery");
    $stats['avg_sections_per_class'] = round($avg_sections[0]['avg_sections'] ?? 0, 1);
    
    // Average subjects per class
    $avg_subjects = executeQuery("SELECT AVG(subject_count) as avg_subjects FROM (SELECT COUNT(*) as subject_count FROM class_subjects GROUP BY class_id) as subquery");
    $stats['avg_subjects_per_class'] = round($avg_subjects[0]['avg_subjects'] ?? 0, 1);
    
    echo json_encode([
        'success' => true,
        'data' => $stats
    ]);
}

/**
 * Get structure overview
 */
function handleGetStructureOverview() {
    $sql = "SELECT 
                c.id as class_id, c.name as class_name,
                COUNT(DISTINCT s.id) as section_count,
                COUNT(DISTINCT cs.subject_id) as subject_count,
                GROUP_CONCAT(DISTINCT s.name ORDER BY s.name) as sections,
                GROUP_CONCAT(DISTINCT sub.code ORDER BY sub.name) as subject_codes
            FROM classes c
            LEFT JOIN sections s ON c.id = s.class_id
            LEFT JOIN class_subjects cs ON c.id = cs.class_id
            LEFT JOIN subjects sub ON cs.subject_id = sub.id
            GROUP BY c.id, c.name
            ORDER BY c.name";
    
    $overview = executeQuery($sql);
    
    echo json_encode([
        'success' => true,
        'data' => $overview ?: []
    ]);
}

// ========== BULK OPERATIONS ==========

/**
 * Bulk import subjects from CSV
 */
function handleBulkImportSubjects() {
    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('CSV file is required');
    }
    
    $file = $_FILES['csv_file']['tmp_name'];
    $handle = fopen($file, 'r');
    
    if (!$handle) {
        throw new Exception('Unable to read CSV file');
    }
    
    global $conn;
    $conn->begin_transaction();
    
    try {
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        
        // Skip header row
        fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                continue;
            }
            
            $name = trim($row[0]);
            $code = trim($row[1]);
            
            if (empty($name) || empty($code)) {
                $errors[] = "Row skipped: Name and code are required";
                $error_count++;
                continue;
            }
            
            // Check for existing subject
            $existing = executeQuery("SELECT id FROM subjects WHERE name = ? OR code = ?", "ss", [$name, $code]);
            if ($existing) {
                $errors[] = "Subject '$name' or code '$code' already exists";
                $error_count++;
                continue;
            }
            
            // Insert subject
            $result = executeQuery("INSERT INTO subjects (name, code) VALUES (?, ?)", "ss", [$name, $code]);
            if ($result) {
                $success_count++;
            } else {
                $errors[] = "Failed to insert subject '$name'";
                $error_count++;
            }
        }
        
        fclose($handle);
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => "Import completed. $success_count subjects added, $error_count errors.",
            'details' => [
                'success_count' => $success_count,
                'error_count' => $error_count,
                'errors' => $errors
            ]
        ]);
    } catch (Exception $e) {
        fclose($handle);
        $conn->rollback();
        throw $e;
    }
}

/**
 * Validate academic data integrity
 */
function handleValidateAcademicData() {
    $issues = [];
    
    // Check for classes without sections
    $classes_without_sections = executeQuery(
        "SELECT c.id, c.name FROM classes c 
         LEFT JOIN sections s ON c.id = s.class_id 
         WHERE s.id IS NULL"
    );
    
    if ($classes_without_sections) {
        $issues['classes_without_sections'] = $classes_without_sections;
    }
    
    // Check for classes without subjects
    $classes_without_subjects = executeQuery(
        "SELECT c.id, c.name FROM classes c 
         LEFT JOIN class_subjects cs ON c.id = cs.class_id 
         WHERE cs.class_id IS NULL"
    );
    
    if ($classes_without_subjects) {
        $issues['classes_without_subjects'] = $classes_without_subjects;
    }
    
    // Check for subjects not assigned to any class
    $unassigned_subjects = executeQuery(
        "SELECT s.id, s.name, s.code FROM subjects s 
         LEFT JOIN class_subjects cs ON s.id = cs.subject_id 
         WHERE cs.subject_id IS NULL"
    );
    
    if ($unassigned_subjects) {
        $issues['unassigned_subjects'] = $unassigned_subjects;
    }
    
    // Check for sections without class teachers
    $sections_without_teachers = executeQuery(
        "SELECT s.id, s.name, c.name as class_name 
         FROM sections s 
         JOIN classes c ON s.class_id = c.id 
         WHERE s.class_teacher_user_id IS NULL"
    );
    
    if ($sections_without_teachers) {
        $issues['sections_without_teachers'] = $sections_without_teachers;
    }
    
    $total_issues = count($issues);
    
    echo json_encode([
        'success' => true,
        'message' => $total_issues > 0 ? "$total_issues validation issues found" : "No validation issues found",
        'data' => $issues
    ]);
}

// Bulk Operations Functions
function getTotalRecordsCount() {
    global $conn;
    $total = 0;
    
    $tables = ['academic_years', 'classes', 'sections', 'subjects', 'class_subjects'];
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            $total += $row['count'];
        }
    }
    
    return $total;
}

function getCompletedOperationsToday() {
    // This would require an operation_logs table
    // For now, return 0
    return 0;
}

function getFailedOperationsCount() {
    // This would require an operation_logs table
    // For now, return 0
    return 0;
}

function handleBulkImport() {
    global $conn;
    
    if (!isset($_FILES['file']) || !isset($_POST['type'])) {
        echo json_encode(['success' => false, 'message' => 'Missing file or type parameter']);
        return;
    }
    
    $file = $_FILES['file'];
    $type = $_POST['type'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'File upload error']);
        return;
    }
    
    $csvData = file_get_contents($file['tmp_name']);
    $lines = explode("\n", $csvData);
    $imported_count = 0;
    
    try {
        $conn->begin_transaction();
        
        switch ($type) {
            case 'academic_years':
                $imported_count = importAcademicYears($lines);
                break;
            case 'classes':
                $imported_count = importClasses($lines);
                break;
            case 'subjects':
                $imported_count = importSubjects($lines);
                break;
            default:
                throw new Exception('Invalid import type');
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'imported_count' => $imported_count]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function importAcademicYears($lines) {
    global $conn;
    $imported = 0;
    
    // Skip header row
    for ($i = 1; $i < count($lines); $i++) {
        $data = str_getcsv(trim($lines[$i]));
        if (count($data) >= 4) {
            $stmt = $conn->prepare("INSERT INTO academic_years (name, start_date, end_date, is_current) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $data[0], $data[1], $data[2], $data[3]);
            if ($stmt->execute()) {
                $imported++;
            }
        }
    }
    
    return $imported;
}

function importClasses($lines) {
    global $conn;
    $imported = 0;
    
    // Skip header row
    for ($i = 1; $i < count($lines); $i++) {
        $data = str_getcsv(trim($lines[$i]));
        if (count($data) >= 3) {
            // Import class
            $stmt = $conn->prepare("INSERT INTO classes (name, academic_year_id) VALUES (?, ?)");
            $stmt->bind_param("si", $data[0], $data[1]);
            if ($stmt->execute()) {
                $class_id = $conn->insert_id;
                
                // Import sections if provided
                if (isset($data[2]) && !empty($data[2])) {
                    $sections = explode(',', $data[2]);
                    foreach ($sections as $section_name) {
                        $section_name = trim($section_name);
                        $stmt2 = $conn->prepare("INSERT INTO sections (name, class_id) VALUES (?, ?)");
                        $stmt2->bind_param("si", $section_name, $class_id);
                        $stmt2->execute();
                    }
                }
                $imported++;
            }
        }
    }
    
    return $imported;
}

function importSubjects($lines) {
    global $conn;
    $imported = 0;
    
    // Skip header row
    for ($i = 1; $i < count($lines); $i++) {
        $data = str_getcsv(trim($lines[$i]));
        if (count($data) >= 2) {
            $stmt = $conn->prepare("INSERT INTO subjects (name, code) VALUES (?, ?)");
            $stmt->bind_param("ss", $data[0], $data[1]);
            if ($stmt->execute()) {
                $imported++;
            }
        }
    }
    
    return $imported;
}

function handleBulkExport() {
    global $conn;
    
    $tables = $_GET['tables'] ?? [];
    if (empty($tables)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No tables selected']);
        exit;
    }
    
    $filename = 'academic_data_export_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    $output = fopen('php://output', 'w');
    
    foreach ($tables as $table) {
        switch ($table) {
            case 'academic_years':
                exportAcademicYears($output);
                break;
            case 'classes':
                exportClasses($output);
                break;
            case 'subjects':
                exportSubjects($output);
                break;
            case 'curriculum':
                exportCurriculum($output);
                break;
        }
    }
    
    fclose($output);
    exit; // Important: exit after export to prevent any other output
}

function exportAcademicYears($output) {
    global $conn;
    
    fputcsv($output, ['=== ACADEMIC YEARS ===']);
    fputcsv($output, ['Year Name', 'Start Date', 'End Date', 'Is Current']);
    
    $result = $conn->query("SELECT name as year_name, start_date, end_date, is_current FROM academic_years ORDER BY start_date");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fputcsv($output, []); // Empty row
}

function exportClasses($output) {
    global $conn;
    
    fputcsv($output, ['=== CLASSES & SECTIONS ===']);
    fputcsv($output, ['Class Name', 'Academic Year', 'Sections']);
    
    $result = $conn->query("
        SELECT c.name as class_name, ay.name as year_name,
               GROUP_CONCAT(s.name ORDER BY s.name) as sections
        FROM classes c
        JOIN academic_years ay ON c.academic_year_id = ay.id
        LEFT JOIN sections s ON c.id = s.class_id
        GROUP BY c.id
        ORDER BY ay.name, c.name
    ");
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fputcsv($output, []); // Empty row
}

function exportSubjects($output) {
    global $conn;
    
    fputcsv($output, ['=== SUBJECTS ===']);
    fputcsv($output, ['Subject Name', 'Subject Code']);
    
    $result = $conn->query("SELECT name as subject_name, code as subject_code FROM subjects ORDER BY name");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
    fputcsv($output, []); // Empty row
}

function exportCurriculum($output) {
    global $conn;
    
    fputcsv($output, ['=== CURRICULUM MAPPING ===']);
    fputcsv($output, ['Academic Year', 'Class', 'Section', 'Subject', 'Subject Code']);
    
    $result = $conn->query("
        SELECT ay.name as year_name, c.name as class_name, s.name as section_name, 
               subj.name as subject_name, subj.code as subject_code
        FROM class_subjects cs
        JOIN classes c ON cs.class_id = c.id
        JOIN academic_years ay ON c.academic_year_id = ay.id
        LEFT JOIN sections s ON cs.section_id = s.id
        JOIN subjects subj ON cs.subject_id = subj.id
        ORDER BY ay.name, c.name, s.name, subj.name
    ");
      while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

function handleExportStructureOverview() {
    global $conn;
    
    $filename = 'academic_structure_overview_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    
    $output = fopen('php://output', 'w');
    
    // Export structure overview
    fputcsv($output, ['=== ACADEMIC STRUCTURE OVERVIEW ===']);
    fputcsv($output, []);
    
    // Academic Years Summary
    fputcsv($output, ['ACADEMIC YEARS SUMMARY']);
    fputcsv($output, ['Year', 'Start Date', 'End Date', 'Status']);
    
    $years_result = $conn->query("
        SELECT 
            name as year_name,
            start_date,
            end_date,
            is_current
        FROM academic_years
        ORDER BY start_date DESC
    ");
    
    while ($year = $years_result->fetch_assoc()) {
        $status = $year['is_current'] ? 'Current' : 'Inactive';
        fputcsv($output, [
            $year['year_name'],
            $year['start_date'],
            $year['end_date'],
            $status
        ]);
    }
    
    fputcsv($output, []);
      // Classes and Sections Detail
    fputcsv($output, ['CLASSES AND SECTIONS DETAIL']);
    fputcsv($output, ['Class', 'Section Count', 'Sections', 'Subject Count']);
    
    $classes_result = $conn->query("
        SELECT 
            c.name as class_name,
            COUNT(DISTINCT s.id) as section_count,
            GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') as sections,
            (SELECT COUNT(DISTINCT cs.subject_id) 
             FROM class_subjects cs 
             WHERE cs.class_id = c.id) as subject_count
        FROM classes c
        LEFT JOIN sections s ON c.id = s.class_id
        GROUP BY c.id, c.name
        ORDER BY c.name
    ");
    
    while ($class = $classes_result->fetch_assoc()) {
        fputcsv($output, [
            $class['class_name'],
            $class['section_count'] ?: 0,
            $class['sections'] ?: 'No sections',
            $class['subject_count'] ?: 0
        ]);
    }
    
    fputcsv($output, []);
    
    // Subject Distribution
    fputcsv($output, ['SUBJECT DISTRIBUTION']);
    fputcsv($output, ['Subject', 'Code', 'Classes Assigned', 'Total Assignments']);
    
    $subjects_result = $conn->query("
        SELECT 
            s.name as subject_name,
            s.code as subject_code,
            COUNT(DISTINCT cs.class_id) as class_count,
            COUNT(cs.class_id) as assignment_count
        FROM subjects s
        LEFT JOIN class_subjects cs ON s.id = cs.subject_id
        GROUP BY s.id
        ORDER BY s.name
    ");
    
    while ($subject = $subjects_result->fetch_assoc()) {
        fputcsv($output, [
            $subject['subject_name'],
            $subject['subject_code'],
            $subject['class_count'] ?: 0,
            $subject['assignment_count'] ?: 0
        ]);
    }
    
    fputcsv($output, []);
    
    // Statistics Summary
    fputcsv($output, ['STATISTICS SUMMARY']);
    fputcsv($output, ['Metric', 'Count']);
    
    // Get total counts
    $stats = [];
    $stats['Total Academic Years'] = $conn->query("SELECT COUNT(*) as count FROM academic_years")->fetch_assoc()['count'];
    $stats['Total Classes'] = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];
    $stats['Total Sections'] = $conn->query("SELECT COUNT(*) as count FROM sections")->fetch_assoc()['count'];
    $stats['Total Subjects'] = $conn->query("SELECT COUNT(*) as count FROM subjects")->fetch_assoc()['count'];
    $stats['Total Subject Assignments'] = $conn->query("SELECT COUNT(*) as count FROM class_subjects")->fetch_assoc()['count'];
    
    foreach ($stats as $metric => $count) {
        fputcsv($output, [$metric, $count]);
    }
    
    fclose($output);
    exit;
}

function handleBatchAssignSubjects() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['year_id']) || !isset($input['class_ids']) || !isset($input['subject_ids'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        $conn->begin_transaction();
        
        $assignments_created = 0;
        
        foreach ($input['class_ids'] as $class_id) {
            foreach ($input['subject_ids'] as $subject_id) {
                // Check if assignment already exists
                $check_stmt = $conn->prepare("SELECT id FROM class_subjects WHERE class_id = ? AND subject_id = ?");
                $check_stmt->bind_param("ii", $class_id, $subject_id);
                $check_stmt->execute();
                
                if ($check_stmt->get_result()->num_rows == 0) {
                    $stmt = $conn->prepare("INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)");
                    $stmt->bind_param("ii", $class_id, $subject_id);
                    if ($stmt->execute()) {
                        $assignments_created++;
                    }
                }
            }
        }
        
        $conn->commit();
        echo json_encode(['success' => true, 'assignments_created' => $assignments_created]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleDuplicateYearStructure() {
    global $conn;
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['source_year_id']) || !isset($input['target_year_id'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        return;
    }
    
    try {
        $conn->begin_transaction();
        
        $classes_copied = 0;
        $assignments_copied = 0;
        
        if ($input['copy_classes']) {
            // Copy classes and sections
            $result = $conn->query("SELECT * FROM classes WHERE academic_year_id = " . $input['source_year_id']);
            
            while ($class = $result->fetch_assoc()) {
                $stmt = $conn->prepare("INSERT INTO classes (class_name, academic_year_id) VALUES (?, ?)");
                $stmt->bind_param("si", $class['class_name'], $input['target_year_id']);
                
                if ($stmt->execute()) {
                    $new_class_id = $conn->insert_id;
                    $classes_copied++;
                    
                    // Copy sections
                    $sections_result = $conn->query("SELECT * FROM sections WHERE class_id = " . $class['id']);
                    while ($section = $sections_result->fetch_assoc()) {
                        $section_stmt = $conn->prepare("INSERT INTO sections (section_name, class_id) VALUES (?, ?)");
                        $section_stmt->bind_param("si", $section['section_name'], $new_class_id);
                        $section_stmt->execute();
                    }
                    
                    if ($input['copy_curriculum']) {
                        // Copy subject assignments
                        $assignments_result = $conn->query("SELECT * FROM class_subjects WHERE class_id = " . $class['id']);
                        while ($assignment = $assignments_result->fetch_assoc()) {
                            $assignment_stmt = $conn->prepare("INSERT INTO class_subjects (class_id, subject_id, section_id) VALUES (?, ?, ?)");
                            $assignment_stmt->bind_param("iii", $new_class_id, $assignment['subject_id'], $assignment['section_id']);
                            if ($assignment_stmt->execute()) {
                                $assignments_copied++;
                            }
                        }
                    }
                }
            }
        }
        
        $conn->commit();
        echo json_encode([
            'success' => true, 
            'classes_copied' => $classes_copied,
            'assignments_copied' => $assignments_copied
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function handleDataValidation() {
    global $conn;
    
    $validation_results = [];
    
    // Check for orphaned sections
    $result = $conn->query("SELECT COUNT(*) as count FROM sections s LEFT JOIN classes c ON s.class_id = c.id WHERE c.id IS NULL");
    $orphaned_sections = $result->fetch_assoc()['count'];
    $validation_results[] = [
        'check' => 'Orphaned Sections',
        'status' => $orphaned_sections > 0 ? 'warning' : 'success',
        'message' => $orphaned_sections > 0 ? 'Found orphaned sections' : 'No orphaned sections found',
        'count' => $orphaned_sections
    ];
    
    // Check for orphaned class_subjects
    $result = $conn->query("SELECT COUNT(*) as count FROM class_subjects cs LEFT JOIN classes c ON cs.class_id = c.id WHERE c.id IS NULL");
    $orphaned_assignments = $result->fetch_assoc()['count'];
    $validation_results[] = [
        'check' => 'Orphaned Subject Assignments',
        'status' => $orphaned_assignments > 0 ? 'warning' : 'success',
        'message' => $orphaned_assignments > 0 ? 'Found orphaned subject assignments' : 'No orphaned assignments found',
        'count' => $orphaned_assignments
    ];
    
    // Check for classes without sections
    $result = $conn->query("SELECT COUNT(*) as count FROM classes c LEFT JOIN sections s ON c.id = s.class_id WHERE s.id IS NULL");
    $classes_without_sections = $result->fetch_assoc()['count'];
    $validation_results[] = [
        'check' => 'Classes Without Sections',
        'status' => $classes_without_sections > 0 ? 'warning' : 'success',
        'message' => $classes_without_sections > 0 ? 'Found classes without sections' : 'All classes have sections',
        'count' => $classes_without_sections
    ];
    
    echo json_encode(['success' => true, 'validation_results' => $validation_results]);
}

function handleCleanupOrphanedRecords() {
    global $conn;
    
    try {
        $conn->begin_transaction();
        
        $cleanup_results = [];
        
        // Delete orphaned sections
        $result = $conn->query("DELETE s FROM sections s LEFT JOIN classes c ON s.class_id = c.id WHERE c.id IS NULL");
        $cleanup_results[] = [
            'table' => 'sections',
            'deleted_count' => $conn->affected_rows
        ];
        
        // Delete orphaned class_subjects
        $result = $conn->query("DELETE cs FROM class_subjects cs LEFT JOIN classes c ON cs.class_id = c.id WHERE c.id IS NULL");
        $cleanup_results[] = [
            'table' => 'class_subjects',
            'deleted_count' => $conn->affected_rows
        ];
        
        // Delete orphaned class_subjects with invalid subject_id
        $result = $conn->query("DELETE cs FROM class_subjects cs LEFT JOIN subjects s ON cs.subject_id = s.id WHERE s.id IS NULL");
        $cleanup_results[] = [
            'table' => 'class_subjects (invalid subjects)',
            'deleted_count' => $conn->affected_rows
        ];
        
        $conn->commit();
        echo json_encode(['success' => true, 'cleanup_results' => $cleanup_results]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getOperationHistory() {
    // This would require an operation_logs table
    // For now, return empty array
    return [];
}

?>
