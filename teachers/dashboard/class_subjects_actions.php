<?php
require_once 'con.php';
require_once '../../includes/functions.php';

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin', 'headmaster')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Set JSON header
header('Content-Type: application/json');

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_class_subjects':
            getClassSubjects();
            break;
            
        case 'add_subjects_to_class':
            addSubjectsToClass();
            break;
            
        case 'remove_subject_from_class':
            removeSubjectFromClass();
            break;
            
        case 'bulk_assign_subjects':
            bulkAssignSubjects();
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Class Subjects Actions Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
}

/**
 * Get subjects assigned to a specific class
 */
function getClassSubjects() {
    global $conn;
    
    $class_id = (int)($_GET['class_id'] ?? 0);
    
    if (!$class_id) {
        echo json_encode(['success' => false, 'message' => 'Class ID is required']);
        return;
    }
    
    $query = "SELECT subject_id FROM class_subjects WHERE class_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = (int)$row['subject_id'];
    }
    
    echo json_encode(['success' => true, 'subjects' => $subjects]);
}

/**
 * Add subjects to a class
 */
function addSubjectsToClass() {
    global $conn;
    
    $class_id = (int)($_POST['class_id'] ?? 0);
    $subject_ids = json_decode($_POST['subject_ids'] ?? '[]', true);
    
    if (!$class_id || empty($subject_ids)) {
        echo json_encode(['success' => false, 'message' => 'Class ID and subjects are required']);
        return;
    }
    
    // Validate class exists
    $class_check = $conn->prepare("SELECT name FROM classes WHERE id = ?");
    $class_check->bind_param('i', $class_id);
    $class_check->execute();
    $class_result = $class_check->get_result();
    
    if ($class_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid class selected']);
        return;
    }
    
    $class_info = $class_result->fetch_assoc();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $success_count = 0;
        $skipped_count = 0;
        
        foreach ($subject_ids as $subject_id) {
            $subject_id = (int)$subject_id;
            
            // Check if mapping already exists
            $check_query = "SELECT 1 FROM class_subjects WHERE class_id = ? AND subject_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param('ii', $class_id, $subject_id);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                $skipped_count++;
                continue;
            }
            
            // Validate subject exists
            $subject_check = $conn->prepare("SELECT name FROM subjects WHERE id = ?");
            $subject_check->bind_param('i', $subject_id);
            $subject_check->execute();
            
            if ($subject_check->get_result()->num_rows === 0) {
                continue;
            }
            
            // Insert mapping
            $insert_query = "INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param('ii', $class_id, $subject_id);
            
            if ($insert_stmt->execute()) {
                $success_count++;
            }
        }
        
        $conn->commit();
        
        $message = "Successfully added {$success_count} subjects to Class {$class_info['name']}";
        if ($skipped_count > 0) {
            $message .= " ({$skipped_count} already assigned)";
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error adding subjects to class: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error adding subjects to class']);
    }
}

/**
 * Remove a subject from a class
 */
function removeSubjectFromClass() {
    global $conn;
    
    $class_id = (int)($_POST['class_id'] ?? 0);
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    
    if (!$class_id || !$subject_id) {
        echo json_encode(['success' => false, 'message' => 'Class ID and Subject ID are required']);
        return;
    }
    
    // Get class and subject names for the response message
    $info_query = "SELECT c.name as class_name, s.name as subject_name 
                   FROM classes c, subjects s 
                   WHERE c.id = ? AND s.id = ?";
    $info_stmt = $conn->prepare($info_query);
    $info_stmt->bind_param('ii', $class_id, $subject_id);
    $info_stmt->execute();
    $info_result = $info_stmt->get_result();
    
    if ($info_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid class or subject']);
        return;
    }
    
    $info = $info_result->fetch_assoc();
    
    // Delete the mapping
    $delete_query = "DELETE FROM class_subjects WHERE class_id = ? AND subject_id = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param('ii', $class_id, $subject_id);
    
    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true, 
                'message' => "Successfully removed {$info['subject_name']} from Class {$info['class_name']}"
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Subject was not assigned to this class']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error removing subject from class']);
    }
}

/**
 * Bulk assign subjects to multiple classes
 */
function bulkAssignSubjects() {
    global $conn;
    
    $class_ids = json_decode($_POST['class_ids'] ?? '[]', true);
    $subject_ids = json_decode($_POST['subject_ids'] ?? '[]', true);
    
    if (empty($class_ids) || empty($subject_ids)) {
        echo json_encode(['success' => false, 'message' => 'Classes and subjects are required']);
        return;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $success_count = 0;
        $skipped_count = 0;
        
        foreach ($class_ids as $class_id) {
            $class_id = (int)$class_id;
            
            // Validate class exists
            $class_check = $conn->prepare("SELECT 1 FROM classes WHERE id = ?");
            $class_check->bind_param('i', $class_id);
            $class_check->execute();
            
            if ($class_check->get_result()->num_rows === 0) {
                continue;
            }
            
            foreach ($subject_ids as $subject_id) {
                $subject_id = (int)$subject_id;
                
                // Check if mapping already exists
                $check_query = "SELECT 1 FROM class_subjects WHERE class_id = ? AND subject_id = ?";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bind_param('ii', $class_id, $subject_id);
                $check_stmt->execute();
                
                if ($check_stmt->get_result()->num_rows > 0) {
                    $skipped_count++;
                    continue;
                }
                
                // Validate subject exists
                $subject_check = $conn->prepare("SELECT 1 FROM subjects WHERE id = ?");
                $subject_check->bind_param('i', $subject_id);
                $subject_check->execute();
                
                if ($subject_check->get_result()->num_rows === 0) {
                    continue;
                }
                
                // Insert mapping
                $insert_query = "INSERT INTO class_subjects (class_id, subject_id) VALUES (?, ?)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bind_param('ii', $class_id, $subject_id);
                
                if ($insert_stmt->execute()) {
                    $success_count++;
                }
            }
        }
        
        $conn->commit();
        
        $class_count = count($class_ids);
        $subject_count = count($subject_ids);
        
        $message = "Successfully created {$success_count} subject assignments";
        if ($skipped_count > 0) {
            $message .= " ({$skipped_count} already existed)";
        }
        $message .= " across {$class_count} classes for {$subject_count} subjects";
        
        echo json_encode(['success' => true, 'message' => $message]);
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error in bulk assign: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error performing bulk assignment']);
    }
}
?>
