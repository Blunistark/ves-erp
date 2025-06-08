<?php
require_once 'con.php';
require_once 'clear_cache.php'; // Include cache clearing utility

function log_error($msg) {
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0777, true);
    }
    file_put_contents($log_dir . '/teacher_actions_debug.log', date('Y-m-d H:i:s') . ' ' . $msg . "\n", FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Method not allowed";
    exit;
}

$action = $_POST['action'] ?? '';
$teacher_id = $_POST['id'] ?? '';

if (empty($action) || empty($teacher_id)) {
    http_response_code(400);
    echo "Missing required parameters";
    exit;
}

try {
    switch ($action) {
        case 'delete':
            // Start transaction
            $conn->begin_transaction();
            
            // First, check if teacher exists
            $check_stmt = $conn->prepare("SELECT user_id FROM teachers WHERE user_id = ?");
            $check_stmt->bind_param('i', $teacher_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            
            if ($result->num_rows === 0) {
                $check_stmt->close();
                $conn->rollback();
                http_response_code(404);
                echo "Teacher not found";
                exit;
            }
            $check_stmt->close();
            
            // Delete related records first (to handle foreign key constraints)
            
            // 1. Delete teacher subjects
            $stmt1 = $conn->prepare("DELETE FROM teacher_subjects WHERE teacher_user_id = ?");
            $stmt1->bind_param('i', $teacher_id);
            $stmt1->execute();
            $stmt1->close();
            
            // 2. Delete teacher assignments
            $stmt2 = $conn->prepare("DELETE FROM teacher_assignments WHERE teacher_user_id = ?");
            $stmt2->bind_param('i', $teacher_id);
            $stmt2->execute();
            $stmt2->close();
            
            // 3. Delete teacher notes
            $stmt3 = $conn->prepare("DELETE FROM teacher_notes WHERE teacher_user_id = ?");
            $stmt3->bind_param('i', $teacher_id);
            $stmt3->execute();
            $stmt3->close();
            
            // 4. Update sections to remove class teacher reference
            $stmt4 = $conn->prepare("UPDATE sections SET class_teacher_user_id = NULL WHERE class_teacher_user_id = ?");
            $stmt4->bind_param('i', $teacher_id);
            $stmt4->execute();
            $stmt4->close();
            
            // 5. Delete from audit_logs
            $stmt5 = $conn->prepare("DELETE FROM audit_logs WHERE user_id = ?");
            $stmt5->bind_param('i', $teacher_id);
            $stmt5->execute();
            $stmt5->close();
            
            // 6. Delete from login_attempts
            $stmt6 = $conn->prepare("DELETE FROM login_attempts WHERE user_id = ?");
            $stmt6->bind_param('i', $teacher_id);
            $stmt6->execute();
            $stmt6->close();
            
            // 7. Delete from teachers table
            $stmt7 = $conn->prepare("DELETE FROM teachers WHERE user_id = ?");
            $stmt7->bind_param('i', $teacher_id);
            $stmt7->execute();
            $stmt7->close();
            
            // 8. Finally, delete from users table
            $stmt8 = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt8->bind_param('i', $teacher_id);
            $stmt8->execute();
            $stmt8->close();
            
            // Commit transaction
            $conn->commit();
            
            // Clear all teacher-related cache after successful deletion
            clearTeacherCache();
            
            echo "Teacher deleted successfully";
            break;
            
        case 'view':
            // Redirect to profile page
            header("Location: teacher_profile.php?id=" . urlencode($teacher_id));
            exit;
            
        case 'edit':
            // Redirect to edit page
            header("Location: teacher_view_edit.php?id=" . urlencode($teacher_id) . "&mode=edit");
            exit;
            
        default:
            http_response_code(400);
            echo "Invalid action";
            break;
    }
    
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    log_error('Error in teacher_actions: ' . $e->getMessage());
    http_response_code(500);
    echo "Error: " . $e->getMessage();
}
?> 